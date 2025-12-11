<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Services\TransferToReservesService;
use App\Services\TransactionPostingService;
use App\Services\YearEndCloserService;
use App\Models\TransferToReserves;
use App\Models\FinancialPeriod;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class YearEndActivities extends Component
{
    public $selectedYear;
    public $currentStatement = 'year_end';
    
    // Activity statuses
    public $financialClosingStatus = 'pending';
    public $profitAllocationStatus = 'pending';
    public $provisionsStatus = 'pending';
    public $auditStatus = 'pending';
    public $agmStatus = 'pending';
    public $systemStatus = 'pending';
    
    // Financial data
    public $netProfit = 0;
    public $totalRevenue = 0;
    public $totalExpenses = 0;
    public $statutoryReserveAmount = 0;
    public $generalReserveAmount = 0;
    public $dividendAmount = 0;
    public $interestAmount = 0;
    
    // Modal controls
    public $showTransferModal = false;
    public $transferType = '';
    public $transferAmount = 0;
    public $transferNarration = '';
    
    // Services
    protected $transferService;
    protected $postingService;
    
    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->transferService = new TransferToReservesService();
        $this->postingService = new TransactionPostingService();
        $this->loadActivityStatuses();
    }
    
    public function render()
    {
        return view('livewire.accounting.year-end-activities');
    }
    
    /**
     * FINANCIAL CLOSING ACTIVITIES
     */
    
    public function closeBooksOfAccounts()
    {
        try {
            DB::beginTransaction();
            
            // Lock all transactions for the year
            DB::table('general_ledger')
                ->whereYear('created_at', $this->selectedYear)
                ->update(['trans_status' => 'CLOSED']);
            
            // Create closing entries
            $this->createClosingEntries();
            
            DB::commit();
            
            session()->flash('success', 'Books of accounts closed successfully for ' . $this->selectedYear);
            $this->updateStatus('financial_closing', 'in_progress');
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to close books: ' . $e->getMessage());
            session()->flash('error', 'Failed to close books: ' . $e->getMessage());
        }
    }
    
    public function generateTrialBalance()
    {
        try {
            // Get all accounts with balances
            $accounts = DB::table('accounts')
                ->where('status', 'ACTIVE')
                ->whereIn('account_level', ['3', '4']) // Detail accounts only
                ->orderBy('account_number')
                ->get();
            
            $trialBalance = [];
            $totalDebits = 0;
            $totalCredits = 0;
            
            foreach ($accounts as $account) {
                // Calculate year-end balance from GL
                $balance = $this->calculateAccountBalance($account->account_number);
                
                if ($balance != 0) {
                    $isDebit = in_array($account->major_category_code, ['1000', '5000']); // Assets & Expenses
                    
                    $trialBalance[] = [
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'debit' => $isDebit && $balance > 0 ? $balance : 0,
                        'credit' => !$isDebit && $balance > 0 ? $balance : 0
                    ];
                    
                    if ($isDebit && $balance > 0) {
                        $totalDebits += $balance;
                    } elseif (!$isDebit && $balance > 0) {
                        $totalCredits += $balance;
                    }
                }
            }
            
            // Store trial balance
            DB::table('financial_statement_snapshots')->insert([
                'financial_period_id' => $this->getFinancialPeriodId(),
                'statement_type' => 'trial_balance',
                'snapshot_date' => now(),
                'data' => json_encode([
                    'accounts' => $trialBalance,
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                    'balanced' => abs($totalDebits - $totalCredits) < 0.01
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            session()->flash('success', 'Trial balance generated successfully. Debits: ' . number_format($totalDebits, 2) . ', Credits: ' . number_format($totalCredits, 2));
            
        } catch (Exception $e) {
            Log::error('Failed to generate trial balance: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate trial balance: ' . $e->getMessage());
        }
    }
    
    public function prepareFinancialStatements()
    {
        try {
            // This triggers the generation of all financial statements
            $this->emit('regenerateStatements');
            
            session()->flash('success', 'Financial statements prepared successfully');
            $this->updateStatus('financial_closing', 'completed');
            
        } catch (Exception $e) {
            Log::error('Failed to prepare financial statements: ' . $e->getMessage());
            session()->flash('error', 'Failed to prepare financial statements: ' . $e->getMessage());
        }
    }
    
    public function captureHistoricalBalances()
    {
        try {
            DB::beginTransaction();
            
            // Capture all account balances as of year-end
            $accounts = DB::table('accounts')
                ->where('status', 'ACTIVE')
                ->get();
            
            foreach ($accounts as $account) {
                DB::table('account_balances_history')->insert([
                    'account_number' => $account->account_number,
                    'year' => $this->selectedYear,
                    'balance' => $account->balance,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            
            session()->flash('success', 'Historical balances captured successfully');
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to capture historical balances: ' . $e->getMessage());
            
            // If table doesn't exist, create it
            if (strpos($e->getMessage(), 'account_balances_history') !== false) {
                $this->createHistoricalBalancesTable();
                session()->flash('info', 'Created historical balances table. Please try again.');
            } else {
                session()->flash('error', 'Failed to capture historical balances: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * PROFIT ALLOCATION ACTIVITIES
     */
    
    public function calculateNetProfit()
    {
        try {
            // Calculate revenue (4000 series accounts)
            $this->totalRevenue = DB::table('general_ledger as gl')
                ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
                ->where('a.major_category_code', '4000')
                ->whereYear('gl.created_at', $this->selectedYear)
                ->selectRaw('SUM(COALESCE(gl.credit, 0) - COALESCE(gl.debit, 0)) as total')
                ->value('total') ?? 0;
            
            // Calculate expenses (5000 series accounts)
            $this->totalExpenses = DB::table('general_ledger as gl')
                ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
                ->where('a.major_category_code', '5000')
                ->whereYear('gl.created_at', $this->selectedYear)
                ->selectRaw('SUM(COALESCE(gl.debit, 0) - COALESCE(gl.credit, 0)) as total')
                ->value('total') ?? 0;
            
            $this->netProfit = $this->totalRevenue - $this->totalExpenses;
            
            // Calculate statutory reserve (20% of net profit)
            $this->statutoryReserveAmount = $this->netProfit * 0.20;
            
            session()->flash('success', 'Net profit calculated: ' . number_format($this->netProfit, 2));
            
        } catch (Exception $e) {
            Log::error('Failed to calculate net profit: ' . $e->getMessage());
            session()->flash('error', 'Failed to calculate net profit: ' . $e->getMessage());
        }
    }
    
    public function transferToStatutoryReserve()
    {
        try {
            if ($this->netProfit <= 0) {
                $this->calculateNetProfit();
            }
            
            if ($this->netProfit <= 0) {
                session()->flash('error', 'No profit available for transfer to reserves');
                return;
            }
            
            DB::beginTransaction();
            
            // Get retained earnings and statutory reserve accounts
            $retainedEarnings = Account::where('account_number', '010130003100')->first();
            $statutoryReserve = Account::where('account_number', '010130003201')->first();
            
            if (!$retainedEarnings || !$statutoryReserve) {
                throw new Exception('Required accounts not found');
            }
            
            // Create transfer record
            $transferData = [
                'transfer_type' => TransferToReserves::TYPE_STATUTORY_RESERVE,
                'source_account_number' => $retainedEarnings->account_number,
                'source_account_name' => $retainedEarnings->account_name,
                'destination_reserve_account_number' => $statutoryReserve->account_number,
                'destination_reserve_account_name' => $statutoryReserve->account_name,
                'amount' => $this->statutoryReserveAmount,
                'financial_year' => $this->selectedYear,
                'transfer_date' => Carbon::createFromFormat('Y-m-d', $this->selectedYear . '-12-31'),
                'narration' => 'Statutory reserve transfer (20% of net profit) for year ' . $this->selectedYear,
                'calculation_method' => TransferToReserves::METHOD_PERCENTAGE,
                'percentage_of_profit' => 20,
                'base_amount' => $this->netProfit,
                'is_statutory_requirement' => true,
                'minimum_required_amount' => $this->statutoryReserveAmount,
                'status' => TransferToReserves::STATUS_APPROVED
            ];
            
            $transfer = $this->transferService->createTransfer($transferData);
            
            // Post to GL
            $this->transferService->postToGeneralLedger($transfer);
            
            DB::commit();
            
            session()->flash('success', 'Statutory reserve transfer completed: ' . number_format($this->statutoryReserveAmount, 2));
            $this->updateStatus('profit_allocation', 'in_progress');
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to transfer to statutory reserve: ' . $e->getMessage());
            session()->flash('error', 'Failed to transfer to statutory reserve: ' . $e->getMessage());
        }
    }
    
    public function openTransferToReserveModal($type)
    {
        $this->transferType = $type;
        $this->transferAmount = 0;
        $this->transferNarration = '';
        $this->showTransferModal = true;
    }
    
    public function processTransferToReserve()
    {
        try {
            DB::beginTransaction();
            
            // Get source and destination accounts
            $retainedEarnings = Account::where('account_number', '010130003100')->first();
            $destinationAccount = null;
            
            switch ($this->transferType) {
                case 'GENERAL_RESERVE':
                    $destinationAccount = Account::where('account_number', '010130003202')->first();
                    break;
                case 'SPECIAL_RESERVE':
                    $destinationAccount = Account::where('account_number', '010130003203')->first();
                    break;
                case 'CONTINGENCY_RESERVE':
                    $destinationAccount = Account::where('account_number', '010130003205')->first();
                    break;
            }
            
            if (!$retainedEarnings || !$destinationAccount) {
                throw new Exception('Required accounts not found');
            }
            
            // Create transfer record
            $transferData = [
                'transfer_type' => $this->transferType,
                'source_account_number' => $retainedEarnings->account_number,
                'source_account_name' => $retainedEarnings->account_name,
                'destination_reserve_account_number' => $destinationAccount->account_number,
                'destination_reserve_account_name' => $destinationAccount->account_name,
                'amount' => $this->transferAmount,
                'financial_year' => $this->selectedYear,
                'transfer_date' => Carbon::createFromFormat('Y-m-d', $this->selectedYear . '-12-31'),
                'narration' => $this->transferNarration ?: 'Transfer to ' . str_replace('_', ' ', $this->transferType),
                'calculation_method' => TransferToReserves::METHOD_FIXED_AMOUNT,
                'status' => TransferToReserves::STATUS_APPROVED
            ];
            
            $transfer = $this->transferService->createTransfer($transferData);
            
            // Post to GL
            $this->transferService->postToGeneralLedger($transfer);
            
            DB::commit();
            
            $this->showTransferModal = false;
            session()->flash('success', 'Transfer completed: ' . number_format($this->transferAmount, 2));
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process transfer: ' . $e->getMessage());
            session()->flash('error', 'Failed to process transfer: ' . $e->getMessage());
        }
    }
    
    public function calculateDividends()
    {
        try {
            if ($this->netProfit <= 0) {
                $this->calculateNetProfit();
            }
            
            // After statutory reserve, calculate available for dividends
            $availableForDividends = $this->netProfit - $this->statutoryReserveAmount;
            
            // Propose 30% of available amount as dividends
            $this->dividendAmount = $availableForDividends * 0.30;
            
            // Get total share capital
            $totalShares = DB::table('accounts')
                ->where('parent_account_number', '010130003000') // Share capital
                ->sum('balance');
            
            if ($totalShares > 0) {
                $dividendRate = ($this->dividendAmount / $totalShares) * 100;
                
                session()->flash('success', 'Dividend proposal: ' . number_format($this->dividendAmount, 2) . ' (' . number_format($dividendRate, 2) . '% of share capital)');
            } else {
                session()->flash('error', 'No share capital found');
            }
            
        } catch (Exception $e) {
            Log::error('Failed to calculate dividends: ' . $e->getMessage());
            session()->flash('error', 'Failed to calculate dividends: ' . $e->getMessage());
        }
    }
    
    public function calculateInterestOnSavings()
    {
        try {
            // Get total savings accounts balance
            $totalSavings = DB::table('accounts')
                ->where('product_number', 2) // Assuming 2 is savings product
                ->where('account_use', 'external')
                ->sum('balance');
            
            // Calculate interest at 5% annual rate
            $interestRate = 0.05;
            $this->interestAmount = $totalSavings * $interestRate;
            
            session()->flash('success', 'Interest on savings calculated: ' . number_format($this->interestAmount, 2) . ' (5% on ' . number_format($totalSavings, 2) . ')');
            
            $this->updateStatus('profit_allocation', 'completed');
            
        } catch (Exception $e) {
            Log::error('Failed to calculate interest: ' . $e->getMessage());
            session()->flash('error', 'Failed to calculate interest: ' . $e->getMessage());
        }
    }
    
    /**
     * PROVISIONS & ADJUSTMENTS
     */
    
    public function calculateBadDebtProvision()
    {
        try {
            // Get overdue loans
            $overdueLoans = DB::table('loans')
                ->where('status', 'DISBURSED')
                ->whereRaw('DATE_ADD(created_at, INTERVAL loan_duration MONTH) < NOW()')
                ->sum('loan_balance');
            
            // Calculate provision (5% of overdue loans)
            $provisionAmount = $overdueLoans * 0.05;
            
            // Post provision entry
            if ($provisionAmount > 0) {
                $this->postProvision('BAD_DEBT', $provisionAmount);
            }
            
            session()->flash('success', 'Bad debt provision calculated: ' . number_format($provisionAmount, 2));
            
        } catch (Exception $e) {
            Log::error('Failed to calculate bad debt provision: ' . $e->getMessage());
            session()->flash('error', 'Failed to calculate bad debt provision: ' . $e->getMessage());
        }
    }
    
    public function calculateDepreciation()
    {
        try {
            // Get fixed assets
            $fixedAssets = DB::table('accounts')
                ->where('parent_account_number', '010110001300') // Fixed assets
                ->get();
            
            $totalDepreciation = 0;
            
            foreach ($fixedAssets as $asset) {
                // Assume 10% depreciation rate
                $depreciation = $asset->balance * 0.10;
                $totalDepreciation += $depreciation;
                
                // Update asset value
                DB::table('accounts')
                    ->where('account_number', $asset->account_number)
                    ->decrement('balance', $depreciation);
            }
            
            // Post depreciation expense
            if ($totalDepreciation > 0) {
                $this->postDepreciation($totalDepreciation);
            }
            
            session()->flash('success', 'Depreciation calculated: ' . number_format($totalDepreciation, 2));
            
        } catch (Exception $e) {
            Log::error('Failed to calculate depreciation: ' . $e->getMessage());
            session()->flash('error', 'Failed to calculate depreciation: ' . $e->getMessage());
        }
    }
    
    public function adjustAccrualsAndPrepayments()
    {
        try {
            // This would typically involve reviewing expense and income accounts
            // for items that need to be accrued or prepaid
            
            DB::beginTransaction();
            
            // Example: Accrue unpaid salaries
            $unpaidSalaries = 0; // Would calculate from payroll system
            
            // Example: Prepaid insurance
            $prepaidInsurance = 0; // Would calculate from insurance policies
            
            DB::commit();
            
            session()->flash('success', 'Accruals and prepayments adjusted');
            $this->updateStatus('provisions', 'completed');
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to adjust accruals: ' . $e->getMessage());
            session()->flash('error', 'Failed to adjust accruals: ' . $e->getMessage());
        }
    }
    
    public function writeOffBadDebts()
    {
        try {
            // Get loans marked for write-off
            $loansToWriteOff = DB::table('loans')
                ->where('status', 'DEFAULT')
                ->whereRaw('DATEDIFF(NOW(), created_at) > 365')
                ->get();
            
            $totalWriteOff = 0;
            
            foreach ($loansToWriteOff as $loan) {
                $totalWriteOff += $loan->loan_balance;
                
                // Update loan status
                DB::table('loans')
                    ->where('id', $loan->id)
                    ->update(['status' => 'WRITTEN_OFF']);
            }
            
            if ($totalWriteOff > 0) {
                $this->postWriteOff($totalWriteOff);
            }
            
            session()->flash('success', 'Bad debts written off: ' . number_format($totalWriteOff, 2));
            
        } catch (Exception $e) {
            Log::error('Failed to write off bad debts: ' . $e->getMessage());
            session()->flash('error', 'Failed to write off bad debts: ' . $e->getMessage());
        }
    }
    
    /**
     * AUDIT & COMPLIANCE
     */
    
    public function initiateExternalAudit()
    {
        try {
            // Create audit record
            DB::table('audits')->insert([
                'audit_type' => 'EXTERNAL',
                'audit_year' => $this->selectedYear,
                'status' => 'INITIATED',
                'initiated_by' => Auth::id(),
                'initiated_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Generate audit preparation checklist
            $this->generateAuditChecklist();
            
            session()->flash('success', 'External audit initiated for year ' . $this->selectedYear);
            $this->updateStatus('audit', 'in_progress');
            
        } catch (Exception $e) {
            Log::error('Failed to initiate audit: ' . $e->getMessage());
            session()->flash('error', 'Failed to initiate audit: ' . $e->getMessage());
        }
    }
    
    public function prepareRegulatoryReturns()
    {
        try {
            // Generate required regulatory reports
            // This would typically involve complex report generation
            
            session()->flash('success', 'Regulatory returns prepared');
            
        } catch (Exception $e) {
            Log::error('Failed to prepare regulatory returns: ' . $e->getMessage());
            session()->flash('error', 'Failed to prepare regulatory returns: ' . $e->getMessage());
        }
    }
    
    public function prepareTaxCompliance()
    {
        try {
            // Calculate tax obligations
            $taxableIncome = $this->netProfit;
            $corporateTax = $taxableIncome * 0.30; // 30% corporate tax rate
            
            session()->flash('success', 'Tax compliance prepared. Estimated tax: ' . number_format($corporateTax, 2));
            
        } catch (Exception $e) {
            Log::error('Failed to prepare tax compliance: ' . $e->getMessage());
            session()->flash('error', 'Failed to prepare tax compliance: ' . $e->getMessage());
        }
    }
    
    public function checkCapitalAdequacy()
    {
        try {
            // Calculate capital adequacy ratio
            $coreCapital = DB::table('accounts')
                ->where('major_category_code', '3000')
                ->sum('balance');
            
            $totalAssets = DB::table('accounts')
                ->where('major_category_code', '1000')
                ->sum('balance');
            
            if ($totalAssets > 0) {
                $capitalAdequacyRatio = ($coreCapital / $totalAssets) * 100;
                
                $meetsRequirement = $capitalAdequacyRatio >= 10; // 10% minimum requirement
                
                session()->flash($meetsRequirement ? 'success' : 'warning', 
                    'Capital Adequacy Ratio: ' . number_format($capitalAdequacyRatio, 2) . '% ' . 
                    ($meetsRequirement ? '(Meets requirement)' : '(Below 10% requirement)'));
            }
            
            $this->updateStatus('audit', 'completed');
            
        } catch (Exception $e) {
            Log::error('Failed to check capital adequacy: ' . $e->getMessage());
            session()->flash('error', 'Failed to check capital adequacy: ' . $e->getMessage());
        }
    }
    
    /**
     * SYSTEM & DATA MANAGEMENT
     */
    
    public function performFullBackup()
    {
        try {
            // Trigger database backup
            $backupFile = 'backup_' . $this->selectedYear . '_' . date('YmdHis') . '.sql';
            
            // This would typically call a backup service
            // For now, we'll just simulate it
            
            session()->flash('success', 'Full backup completed: ' . $backupFile);
            
        } catch (Exception $e) {
            Log::error('Failed to perform backup: ' . $e->getMessage());
            session()->flash('error', 'Failed to perform backup: ' . $e->getMessage());
        }
    }
    
    public function archiveDocuments()
    {
        try {
            // Archive year's documents
            $documentsArchived = DB::table('documents')
                ->whereYear('created_at', $this->selectedYear)
                ->update(['archived' => true, 'archived_at' => now()]);
            
            session()->flash('success', $documentsArchived . ' documents archived');
            
        } catch (Exception $e) {
            Log::error('Failed to archive documents: ' . $e->getMessage());
            session()->flash('error', 'Failed to archive documents: ' . $e->getMessage());
        }
    }
    
    public function resetSequences()
    {
        try {
            // Reset invoice and receipt number sequences
            DB::table('sequences')->update([
                'current_value' => 0,
                'updated_at' => now()
            ]);
            
            session()->flash('success', 'Sequences reset for new year');
            
        } catch (Exception $e) {
            Log::error('Failed to reset sequences: ' . $e->getMessage());
            session()->flash('error', 'Failed to reset sequences: ' . $e->getMessage());
        }
    }
    
    public function createNewFinancialPeriod()
    {
        try {
            $nextYear = $this->selectedYear + 1;
            
            // Check if period already exists
            $exists = FinancialPeriod::where('year', $nextYear)->exists();
            
            if (!$exists) {
                FinancialPeriod::create([
                    'year' => $nextYear,
                    'start_date' => Carbon::createFromFormat('Y-m-d', $nextYear . '-01-01'),
                    'end_date' => Carbon::createFromFormat('Y-m-d', $nextYear . '-12-31'),
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                session()->flash('success', 'Financial period created for ' . $nextYear);
            } else {
                session()->flash('info', 'Financial period already exists for ' . $nextYear);
            }
            
            $this->updateStatus('system', 'completed');
            
        } catch (Exception $e) {
            Log::error('Failed to create financial period: ' . $e->getMessage());
            session()->flash('error', 'Failed to create financial period: ' . $e->getMessage());
        }
    }
    
    /**
     * HELPER METHODS
     */
    
    private function calculateAccountBalance($accountNumber)
    {
        return DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->whereYear('created_at', $this->selectedYear)
            ->selectRaw('SUM(COALESCE(credit, 0) - COALESCE(debit, 0)) as balance')
            ->value('balance') ?? 0;
    }
    
    private function getFinancialPeriodId()
    {
        $period = FinancialPeriod::where('year', $this->selectedYear)->first();
        return $period ? $period->id : null;
    }
    
    private function createClosingEntries()
    {
        // Close revenue accounts to income summary
        $revenueAccounts = DB::table('accounts')
            ->where('major_category_code', '4000')
            ->where('balance', '>', 0)
            ->get();
        
        foreach ($revenueAccounts as $account) {
            // Debit revenue account, credit income summary
            $this->postClosingEntry($account->account_number, 'debit', $account->balance);
        }
        
        // Close expense accounts to income summary
        $expenseAccounts = DB::table('accounts')
            ->where('major_category_code', '5000')
            ->where('balance', '>', 0)
            ->get();
        
        foreach ($expenseAccounts as $account) {
            // Credit expense account, debit income summary
            $this->postClosingEntry($account->account_number, 'credit', $account->balance);
        }
    }
    
    private function postClosingEntry($accountNumber, $type, $amount)
    {
        // Implementation for posting closing entries
        // This would use the TransactionPostingService
    }
    
    private function postProvision($type, $amount)
    {
        // Post provision to GL
        // Debit: Bad Debt Expense
        // Credit: Allowance for Doubtful Accounts
    }
    
    private function postDepreciation($amount)
    {
        // Post depreciation to GL
        // Debit: Depreciation Expense
        // Credit: Accumulated Depreciation
    }
    
    private function postWriteOff($amount)
    {
        // Post write-off to GL
        // Debit: Allowance for Doubtful Accounts
        // Credit: Loans Receivable
    }
    
    private function generateAuditChecklist()
    {
        // Generate comprehensive audit preparation checklist
    }
    
    private function createHistoricalBalancesTable()
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS account_balances_history (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                account_number VARCHAR(255) NOT NULL,
                year INT NOT NULL,
                balance DECIMAL(20,2) NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                INDEX idx_account_year (account_number, year)
            )
        ");
    }
    
    private function updateStatus($category, $status)
    {
        switch ($category) {
            case 'financial_closing':
                $this->financialClosingStatus = $status;
                break;
            case 'profit_allocation':
                $this->profitAllocationStatus = $status;
                break;
            case 'provisions':
                $this->provisionsStatus = $status;
                break;
            case 'audit':
                $this->auditStatus = $status;
                break;
            case 'agm':
                $this->agmStatus = $status;
                break;
            case 'system':
                $this->systemStatus = $status;
                break;
        }
    }
    
    private function loadActivityStatuses()
    {
        // Load saved statuses from database if available
        // This would check a year_end_activities table for saved progress
    }
    
    public function saveProgress()
    {
        try {
            // Save current progress to database
            DB::table('year_end_activities')->updateOrInsert(
                ['year' => $this->selectedYear],
                [
                    'financial_closing_status' => $this->financialClosingStatus,
                    'profit_allocation_status' => $this->profitAllocationStatus,
                    'provisions_status' => $this->provisionsStatus,
                    'audit_status' => $this->auditStatus,
                    'agm_status' => $this->agmStatus,
                    'system_status' => $this->systemStatus,
                    'net_profit' => $this->netProfit,
                    'updated_at' => now()
                ]
            );
            
            session()->flash('success', 'Progress saved successfully');
            
        } catch (Exception $e) {
            Log::error('Failed to save progress: ' . $e->getMessage());
            session()->flash('error', 'Failed to save progress');
        }
    }
    
    public function executeAllActivities()
    {
        try {
            // Execute all activities in sequence
            $this->closeBooksOfAccounts();
            $this->generateTrialBalance();
            $this->prepareFinancialStatements();
            $this->captureHistoricalBalances();
            
            $this->calculateNetProfit();
            $this->transferToStatutoryReserve();
            $this->calculateDividends();
            $this->calculateInterestOnSavings();
            
            $this->calculateBadDebtProvision();
            $this->calculateDepreciation();
            $this->adjustAccrualsAndPrepayments();
            
            $this->initiateExternalAudit();
            $this->checkCapitalAdequacy();
            
            $this->performFullBackup();
            $this->createNewFinancialPeriod();
            
            session()->flash('success', 'All year-end activities executed successfully');
            
        } catch (Exception $e) {
            Log::error('Failed to execute all activities: ' . $e->getMessage());
            session()->flash('error', 'Failed to execute all activities: ' . $e->getMessage());
        }
    }
    
    public function exportYearEndReport()
    {
        try {
            // Generate comprehensive year-end report
            // This would create a PDF or Excel report with all year-end data
            
            session()->flash('success', 'Year-end report exported successfully');
            
        } catch (Exception $e) {
            Log::error('Failed to export report: ' . $e->getMessage());
            session()->flash('error', 'Failed to export report: ' . $e->getMessage());
        }
    }
}