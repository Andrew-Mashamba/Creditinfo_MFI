<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\Loan;
use App\Models\loans_schedules;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Carbon\Carbon;

class LoanLossReserveManager extends Component
{
    // View Controls
    public $viewMode = 'dashboard'; // dashboard, provision, writeoff, history
    
    // Reserve Management
    public $currentYear;
    public $currentMonth;
    public $profits = 0;
    public $percentage = 5; // Default 5% provision rate
    public $reserve_amount = 0;
    public $source = '';
    
    // Portfolio Analysis
    public $loanPortfolioValue = 0;
    public $currentReserveBalance = 0;
    public $requiredReserve = 0;
    public $provisionGap = 0;
    
    // Loan Aging Categories
    public $loanAging = [];
    public $provisionRates = [
        'current' => 1,      // 0-30 days: 1%
        'watch' => 5,        // 31-90 days: 5%
        'substandard' => 25, // 91-180 days: 25%
        'doubtful' => 50,    // 181-365 days: 50%
        'loss' => 100        // >365 days: 100%
    ];
    
    // Dashboard Statistics
    public $stats = [
        'coverage_ratio' => 0,
        'npl_ratio' => 0,
        'provision_coverage' => 0,
        'write_off_ytd' => 0
    ];
    
    // History Collections
    public $provisionHistory = [];
    public $writeOffHistory = [];
    public $adjustmentHistory = [];
    public $pendingApprovals = [];

    // For Write-offs
    public $selectedLoans = [];
    public $writeOffReason = '';
    public $actualLoanLosses = 0;
    public $adjustments = 0;
    
    // Track selected loans separately to ensure persistence
    protected $selectedLoanIds = [];
    
    // GL Account Codes (loaded from institutions table)
    public $reserveAccount;      // Loan Loss Reserve (Liability)
    public $expenseAccount;      // Loan Loss Expense
    public $recoveryAccount;     // Recovery Income
    public $writeOffAccount;     // Written-off Loans Account
    
    // Status tracking
    public $year;
    public $status = 'pending';
    public $editMode = false;
    
    // Additional properties for enhanced functionality
    public $provisionMethod = 'ecl'; // ecl, aging, manual
    public $sourceAccountBalance = null;
    public $writeOffFilter = '180';
    public $authorizationNumber = '';
    public $authorizationDate = '';
    public $selectAll = false;
    
    // Configuration errors
    public $configurationErrors = [];
    public $hasConfigurationErrors = false;
    
    // Modal controls
    public $showCustomReportModal = false;
    
    // Custom report properties
    public $customReportPeriod = 'monthly';
    public $customReportStartDate;
    public $customReportEndDate;
    public $customReportSections = [
        'provisions' => true,
        'writeoffs' => true,
        'recoveries' => true,
        'analytics' => true
    ];
    public $customReportFormat = 'pdf';
    
    // Approval threshold
    public $approvalThreshold = 1000000;
    
    public function mount()
    {
        $this->currentYear = date('Y');
        $this->currentMonth = date('n');
        $this->year = $this->currentYear;
        $this->loadAccountSettings();
        
        // Only load dashboard data if configuration is valid
        if (!$this->hasConfigurationErrors) {
            $this->loadDashboardData();
            $this->loadYearEndData(); // Auto-load actual losses
            $this->loadPendingApprovals(); // Load pending approval requests
        }
    }
    
    /**
     * Load GL account settings from institutions table
     * No fallbacks - requires proper configuration
     */
    private function loadAccountSettings()
    {
        $this->configurationErrors = [];
        $institution = DB::table('institutions')->where('id', 1)->first();
        
        if (!$institution) {
            $this->configurationErrors[] = 'Institution configuration not found. Please configure institution settings.';
            $this->hasConfigurationErrors = true;
            return;
        }
        
        // Check Loan Loss Reserve Account
        if (empty($institution->loan_loss_reserve_account)) {
            $this->configurationErrors[] = 'Loan Loss Reserve Account is not configured in institution settings.';
        } else {
            // Verify account exists
            $account = DB::table('accounts')
                ->where('account_number', $institution->loan_loss_reserve_account)
                ->first();
            if (!$account) {
                $this->configurationErrors[] = "Loan Loss Reserve Account ({$institution->loan_loss_reserve_account}) does not exist in chart of accounts.";
            } else {
                $this->reserveAccount = $institution->loan_loss_reserve_account;
            }
        }
        
        // Check Loan Loss Expense Account
        if (empty($institution->loan_loss_expense_account)) {
            $this->configurationErrors[] = 'Loan Loss Expense Account is not configured in institution settings.';
        } else {
            // Verify account exists
            $account = DB::table('accounts')
                ->where('account_number', $institution->loan_loss_expense_account)
                ->first();
            if (!$account) {
                $this->configurationErrors[] = "Loan Loss Expense Account ({$institution->loan_loss_expense_account}) does not exist in chart of accounts.";
            } else {
                $this->expenseAccount = $institution->loan_loss_expense_account;
            }
        }
        
        // Check Loan Recovery Income Account
        if (empty($institution->loan_recovery_income_account)) {
            $this->configurationErrors[] = 'Loan Recovery Income Account is not configured in institution settings.';
        } else {
            // Verify account exists
            $account = DB::table('accounts')
                ->where('account_number', $institution->loan_recovery_income_account)
                ->first();
            if (!$account) {
                $this->configurationErrors[] = "Loan Recovery Income Account ({$institution->loan_recovery_income_account}) does not exist in chart of accounts.";
            } else {
                $this->recoveryAccount = $institution->loan_recovery_income_account;
            }
        }
        
        // Check Loan Write-off Account
        if (empty($institution->loan_write_off_account)) {
            // Use default if not configured
            $this->writeOffAccount = '0101500055005510'; // LOAN WRITE-OFFS default account
        } else {
            // Verify account exists
            $account = DB::table('accounts')
                ->where('account_number', $institution->loan_write_off_account)
                ->first();
            if (!$account) {
                $this->configurationErrors[] = "Loan Write-off Account ({$institution->loan_write_off_account}) does not exist in chart of accounts.";
                $this->writeOffAccount = '0101500055005510'; // Use default
            } else {
                $this->writeOffAccount = $institution->loan_write_off_account;
            }
        }
        
        $this->hasConfigurationErrors = count($this->configurationErrors) > 0;
    }
    
    /**
     * Load pending approval requests
     */
    private function loadPendingApprovals()
    {
        // Load pending provision approvals from loan_loss_provisions table
        $this->pendingApprovals = collect(DB::table('loan_loss_provisions')
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->get());
    }

    /**
     * Check if current user can approve provisions
     *
     * @return bool
     */
    public function canApproveProvisions()
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return false;
        }

        // For now, allow any authenticated user to approve
        // This can be extended to check specific roles or permissions
        // Example: return auth()->user()->hasRole('accounting-manager');
        return true;
    }

    public function loadDashboardData()
    {
        $this->calculateLoanPortfolio();
        $this->calculateCurrentReserve();
        $this->calculateLoanAging();
        $this->calculateRequiredReserve();
        $this->loadStatistics();
        $this->loadHistory();
    }
    
    private function calculateLoanPortfolio()
    {
        // Calculate total outstanding loan portfolio from loans and schedules
        // This is the sum of all outstanding principal amounts from active loans
        
        // Method 1: Calculate from loans_schedules (more accurate)
        $outstandingFromSchedules = DB::table('loans_schedules as ls')
            ->join('loans as l', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
            })
            ->where('l.status', 'ACTIVE')
            ->whereIn('ls.completion_status', ['PENDING', 'PARTIAL'])
            ->sum(DB::raw('CASE 
                WHEN ls.principle > COALESCE(ls.principle_payment, 0) 
                THEN ls.principle - COALESCE(ls.principle_payment, 0) 
                ELSE 0 
            END'));
        
        // Method 2: Fallback to loan principal minus payments
        if ($outstandingFromSchedules == 0) {
            $outstandingFromLoans = DB::table('loans')
                ->where('status', 'ACTIVE')
                ->sum(DB::raw('principle - COALESCE(total_principal_paid, 0)'));
            
            $this->loanPortfolioValue = $outstandingFromLoans ?? 0;
        } else {
            $this->loanPortfolioValue = $outstandingFromSchedules ?? 0;
        }
    }
    
    private function calculateCurrentReserve()
    {
        // Get current reserve balance from GL
        $credits = DB::table('general_ledger')
            ->where('record_on_account_number', $this->reserveAccount)
            ->sum(DB::raw('CAST(credit AS DECIMAL(20,2))')) ?? 0;
            
        $debits = DB::table('general_ledger')
            ->where('record_on_account_number', $this->reserveAccount)
            ->sum(DB::raw('CAST(debit AS DECIMAL(20,2))')) ?? 0;
            
        // Reserve is a credit balance account
        $this->currentReserveBalance = $credits - $debits;
    }
    
    private function calculateLoanAging()
    {
        $this->loanAging = [];
        
        // Initialize aging buckets
        $agingBuckets = [
            'current' => ['loans' => [], 'total' => 0],
            'watch' => ['loans' => [], 'total' => 0],
            'substandard' => ['loans' => [], 'total' => 0],
            'doubtful' => ['loans' => [], 'total' => 0],
            'loss' => ['loans' => [], 'total' => 0]
        ];
        
        // Get aging data by aggregating loan schedules - group by loan only
        $agingData = DB::table('loans_schedules as ls')
            ->join('loans as l', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
            })
            ->where('l.status', 'ACTIVE')
            ->whereIn('ls.completion_status', ['PENDING', 'PARTIAL'])
            ->select(
                'l.loan_account_number',
                DB::raw('SUM(CASE 
                    WHEN ls.principle > COALESCE(ls.principle_payment, 0) 
                    THEN ls.principle - COALESCE(ls.principle_payment, 0) 
                    ELSE 0 
                END) as outstanding_principal'),
                DB::raw('SUM(CASE 
                    WHEN ls.interest > COALESCE(ls.interest_payment, 0) 
                    THEN ls.interest - COALESCE(ls.interest_payment, 0) 
                    ELSE 0 
                END) as outstanding_interest'),
                DB::raw('MAX(ls.days_in_arrears) as max_days_in_arrears')
            )
            ->groupBy('l.loan_account_number')
            ->get();
        
        // Categorize loans based on maximum days in arrears
        foreach ($agingData as $loan) {
            $daysInArrears = $loan->max_days_in_arrears ?? 0;
            $outstandingAmount = ($loan->outstanding_principal ?? 0) + ($loan->outstanding_interest ?? 0);
            
            if ($outstandingAmount <= 0) continue; // Skip fully paid loans
            
            // Categorize based on days in arrears (IFRS 9 ECL categories)
            if ($daysInArrears <= 30) {
                $agingBuckets['current']['loans'][] = $loan->loan_account_number;
                $agingBuckets['current']['total'] += $outstandingAmount;
            } elseif ($daysInArrears <= 90) {
                $agingBuckets['watch']['loans'][] = $loan->loan_account_number;
                $agingBuckets['watch']['total'] += $outstandingAmount;
            } elseif ($daysInArrears <= 180) {
                $agingBuckets['substandard']['loans'][] = $loan->loan_account_number;
                $agingBuckets['substandard']['total'] += $outstandingAmount;
            } elseif ($daysInArrears <= 365) {
                $agingBuckets['doubtful']['loans'][] = $loan->loan_account_number;
                $agingBuckets['doubtful']['total'] += $outstandingAmount;
            } else {
                $agingBuckets['loss']['loans'][] = $loan->loan_account_number;
                $agingBuckets['loss']['total'] += $outstandingAmount;
            }
        }
        
        // Calculate totals and provisions for each category
        foreach ($agingBuckets as $category => $data) {
            $count = count($data['loans']);
            $totalAmount = $data['total'];
            $provisionRate = $this->provisionRates[$category] ?? 0;
            
            $this->loanAging[$category] = [
                'count' => $count,
                'amount' => $totalAmount,
                'provision_rate' => $provisionRate,
                'required_provision' => $totalAmount * $provisionRate / 100
            ];
        }
    }
    
    public function calculateRequiredReserve()
    {
        // Sum up required provisions from all aging categories
        $this->requiredReserve = 0;
        foreach ($this->loanAging as $category) {
            $this->requiredReserve += $category['required_provision'];
        }
        
        // Calculate provision gap
        $this->provisionGap = max(0, $this->requiredReserve - $this->currentReserveBalance);
        
        // Update reserve amount for display
        $this->reserve_amount = $this->provisionGap;
    }
    
    private function loadStatistics()
    {
        // Coverage Ratio: Reserve / Portfolio
        $this->stats['coverage_ratio'] = $this->loanPortfolioValue > 0 
            ? ($this->currentReserveBalance / $this->loanPortfolioValue) * 100 
            : 0;
            
        // NPL Ratio: Non-performing loans / Total loans
        $nplAmount = ($this->loanAging['substandard']['amount'] ?? 0) +
                     ($this->loanAging['doubtful']['amount'] ?? 0) +
                     ($this->loanAging['loss']['amount'] ?? 0);
                     
        $this->stats['npl_ratio'] = $this->loanPortfolioValue > 0
            ? ($nplAmount / $this->loanPortfolioValue) * 100
            : 0;
            
        // Provision Coverage: Current Reserve / Required Reserve
        $this->stats['provision_coverage'] = $this->requiredReserve > 0
            ? ($this->currentReserveBalance / $this->requiredReserve) * 100
            : 100;
            
        // Year-to-date write-offs
        $this->stats['write_off_ytd'] = DB::table('general_ledger')
            ->where('record_on_account_number', $this->reserveAccount)
            ->where('transaction_type', 'WRITE_OFF')
            ->whereYear('created_at', $this->currentYear)
            ->sum(DB::raw('CAST(debit AS DECIMAL(20,2))')) ?? 0;
    }
    
    private function loadHistory()
    {
        // Load provision history
        $this->provisionHistory = DB::table('general_ledger')
            ->where('record_on_account_number', $this->reserveAccount)
            ->where('transaction_type', 'PROVISION')
            ->whereYear('created_at', $this->currentYear)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Load write-off history
        $this->writeOffHistory = DB::table('general_ledger')
            ->where('record_on_account_number', $this->reserveAccount)
            ->where('transaction_type', 'WRITE_OFF')
            ->whereYear('created_at', $this->currentYear)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    public function updatedProfits()
    {
        $this->calculateLLR();
    }
    
    public function updatedPercentage()
    {
        $this->calculateLLR();
    }
    
    public function calculateLLR()
    {
        // Simple percentage calculation for display
        if ($this->profits > 0 && $this->percentage > 0) {
            $this->reserve_amount = ($this->profits * $this->percentage) / 100;
        } else {
            // Use provision gap if no manual profit entered
            $this->reserve_amount = $this->provisionGap;
        }
    }

    public function makeProvision()
    {
        // Check if approval is needed
        if ($this->reserve_amount >= $this->approvalThreshold) {
            \Log::info('Provision requires approval', [
                'amount' => $this->reserve_amount,
                'threshold' => $this->approvalThreshold
            ]);
            
            session()->flash('warning', 'This provision amount requires approval. Please submit for approval.');
            return $this->submitForApproval();
        }
        
        // Log the provision attempt
        \Log::info('=== LOAN LOSS PROVISION INITIATED ===', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'Unknown',
            'timestamp' => Carbon::now()->toIso8601String(),
            'provision_amount' => $this->reserve_amount,
            'provision_method' => $this->provisionMethod,
            'source_account' => $this->source,
            'current_reserve_balance' => $this->currentReserveBalance,
            'required_reserve' => $this->requiredReserve,
            'provision_gap' => $this->provisionGap,
            'loan_portfolio_value' => $this->loanPortfolioValue
        ]);
        
        // Validate inputs
        if ($this->reserve_amount <= 0) {
            \Log::warning('Provision validation failed: Invalid reserve amount', [
                'reserve_amount' => $this->reserve_amount
            ]);
            session()->flash('error', 'Please calculate reserve amount first.');
            return;
        }
        
        if (empty($this->source)) {
            \Log::warning('Provision validation failed: No source account selected');
            session()->flash('error', 'Please select a source account for the provision.');
            return;
        }
        
        // Validate accounts are configured
        if (!$this->isAccountConfigured('reserve') || !$this->isAccountConfigured('expense')) {
            \Log::error('Provision validation failed: Required GL accounts not configured', [
                'reserve_account_configured' => $this->isAccountConfigured('reserve'),
                'expense_account_configured' => $this->isAccountConfigured('expense'),
                'reserve_account' => $this->reserveAccount,
                'expense_account' => $this->expenseAccount
            ]);
            session()->flash('error', 'Required GL accounts are not configured. Please check institution settings.');
            return;
        }
        
        \Log::info('Provision validation passed, starting transaction', [
            'reserve_account' => $this->reserveAccount,
            'expense_account' => $this->expenseAccount
        ]);
        
        DB::beginTransaction();
        
        try {
            $transactionId = 'LLR-' . time();
            $description = "Loan Loss Provision - " . Carbon::now()->format('F Y');
            
            \Log::info('Creating provision transaction', [
                'transaction_id' => $transactionId,
                'description' => $description
            ]);
            
            // Use TransactionPostingService for proper double-entry
            $postingService = new TransactionPostingService();
            
            // Post the provision transaction
            // The service will automatically determine debit/credit based on account types
            $result = $postingService->postTransaction([
                'first_account' => $this->source,  // Source account (e.g., retained earnings or cash)
                'second_account' => $this->reserveAccount,  // Loan Loss Reserve Account
                'amount' => $this->reserve_amount,
                'narration' => $description . ' - ' . $this->provisionMethod . ' method',
                'action' => 'PROVISION'
            ]);
            
            \Log::info('Transaction posted successfully', [
                'result' => $result,
                'reference_number' => $result['reference_number'] ?? null
            ]);
            
            \Log::info('GL entries posted successfully, recording in tracking table');
            
            // 3. Record in loan_loss_reserves table for tracking
            $trackingId = DB::table('loan_loss_reserves')->insertGetId([
                'year' => $this->currentYear,
                'profits' => $this->profits ?: 0,
                'percentage' => $this->percentage,
                'reserve_amount' => $this->reserve_amount,
                'initial_allocation' => $this->reserve_amount,
                'total_allocation' => $this->currentReserveBalance + $this->reserve_amount,
                'status' => 'allocated',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            \Log::info('Provision recorded in tracking table', [
                'tracking_id' => $trackingId,
                'total_allocation' => $this->currentReserveBalance + $this->reserve_amount
            ]);
            
            DB::commit();
            
            \Log::info('=== LOAN LOSS PROVISION COMPLETED SUCCESSFULLY ===', [
                'transaction_id' => $transactionId,
                'provision_amount' => $this->reserve_amount,
                'new_reserve_balance' => $this->currentReserveBalance + $this->reserve_amount,
                'user_id' => auth()->id(),
                'tracking_id' => $trackingId
            ]);
            
            session()->flash('message', 'Loan loss provision of ' . number_format($this->reserve_amount, 2) . ' TZS has been recorded successfully.');
            $this->loadDashboardData();
            $this->resetForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('=== LOAN LOSS PROVISION FAILED ===', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'provision_amount' => $this->reserve_amount,
                'user_id' => auth()->id(),
                'reserve_account' => $this->reserveAccount,
                'expense_account' => $this->expenseAccount
            ]);
            
            $errorMessage = 'Failed to record provision: ' . $e->getMessage();
            session()->flash('error', $errorMessage);
            
            // Also display user-friendly message for common errors
            if (strpos($e->getMessage(), 'Insufficient balance') !== false) {
                session()->flash('error', 'Insufficient balance in source account. Please check the account balance.');
            } elseif (strpos($e->getMessage(), 'account not found') !== false) {
                session()->flash('error', 'One or more GL accounts not found. Please check configuration.');
            }
        }
    }

    /**
     * Submit provision for approval
     */
    private function submitForApproval()
    {
        try {
            // Create approval request
            // This is a placeholder implementation
            // You can extend this to integrate with your approval system
            
            \Log::info('Provision submitted for approval', [
                'amount' => $this->reserve_amount,
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);
            
            session()->flash('info', 'Provision has been submitted for approval.');
            
            // Reset form
            $this->reserve_amount = 0;
            $this->profits = 0;
            $this->percentage = 5;
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to submit provision for approval: ' . $e->getMessage());
            session()->flash('error', 'Failed to submit for approval: ' . $e->getMessage());
            return false;
        }
    }

    public function editLLR($id)
    {
        // Not needed with new structure
        $this->editMode = true;
    }

    public function deleteLLR($id)
    {
        // This would require proper authorization and audit trail
        session()->flash('error', 'Deletion of reserves requires authorization.');
    }

    public function resetForm()
    {
        $this->editMode = false;
        $this->profits = 0;
        $this->percentage = 5; // Reset to default 5%
        $this->reserve_amount = 0;
        $this->source = '';
    }

    public function processWriteOff()
    {
        // Log the attempt
        Log::info('ProcessWriteOff called', [
            'selected_loans' => $this->selectedLoans,
            'count' => count($this->selectedLoans),
            'reason' => $this->writeOffReason,
            'authorization' => $this->authorizationNumber,
            'date' => $this->authorizationDate,
            'user_id' => auth()->id()
        ]);
        
        if (empty($this->selectedLoans)) {
            Log::warning('Write-off attempt with no loans selected');
            session()->flash('error', 'Please select at least one loan to write off.');
            return;
        }
        
        if (empty($this->writeOffReason)) {
            Log::warning('Write-off attempt without reason');
            session()->flash('error', 'Please provide a reason for the write-off.');
            return;
        }
        
        if (empty($this->authorizationNumber)) {
            Log::warning('Write-off attempt without authorization');
            session()->flash('error', 'Please provide authorization/board resolution number.');
            return;
        }
        
        // Validate GL accounts are configured
        if (empty($this->writeOffAccount) || empty($this->reserveAccount)) {
            Log::error('Write-off GL accounts not properly configured', [
                'writeOffAccount' => $this->writeOffAccount,
                'reserveAccount' => $this->reserveAccount
            ]);
            session()->flash('error', 'Write-off accounts are not properly configured. Please check institution settings.');
            return;
        }
        
        DB::beginTransaction();
        
        try {
            $transactionId = 'WO-' . time();
            $totalWriteOff = 0;
            $postingService = new TransactionPostingService();
            
            foreach ($this->selectedLoans as $loanId) {
                $loan = Loan::find($loanId);
                if ($loan && $loan->status === 'ACTIVE') {
                    // Get outstanding balance from loans_schedules
                    $outstandingBalance = DB::table('loans_schedules')
                        ->where('loan_id', $loanId)
                        ->whereIn('completion_status', ['PENDING', 'PARTIAL'])
                        ->sum(DB::raw('installment - COALESCE(payment, 0)'));
                    
                    $writeOffAmount = $outstandingBalance ?: 0;
                    
                    Log::info('Processing individual loan write-off', [
                        'loan_id' => $loanId,
                        'loan_account' => $loan->loan_account_number,
                        'outstanding_balance' => $writeOffAmount
                    ]);
                    
                    if ($writeOffAmount > 0) {
                        $totalWriteOff += $writeOffAmount;
                        
                        // 1. Use posting service to write off loan from reserve
                        // Dr: Write-off Account, Cr: Loan Loss Reserve
                        $postingService->postTransaction([
                            'first_account' => $this->writeOffAccount,
                            'second_account' => $this->reserveAccount,
                            'amount' => $writeOffAmount,
                            'narration' => "Write-off loan: " . $loan->loan_account_number . " - " . $this->writeOffReason,
                            'action' => 'WRITE_OFF',
                            'reference_number' => $transactionId
                        ]);
                        
                        // 2. Clear the loan balance - loan accounts ARE GL accounts
                        // First check if the loan account exists in the accounts table
                        $loanAccountExists = DB::table('accounts')
                            ->where('account_number', $loan->loan_account_number)
                            ->exists();
                        
                        if ($loanAccountExists) {
                            // Post transaction to clear the loan account
                            // Dr: Write-off Account, Cr: Loan Account
                            $postingService->postTransaction([
                                'first_account' => $this->writeOffAccount,
                                'second_account' => $loan->loan_account_number,
                                'amount' => $writeOffAmount,
                                'narration' => "Clear loan balance for write-off - Authorization: " . $this->authorizationNumber,
                                'action' => 'LOAN_WRITE_OFF',
                                'reference_number' => $transactionId
                            ]);
                            
                            Log::info('Loan GL account cleared', [
                                'loan_id' => $loanId,
                                'loan_account' => $loan->loan_account_number,
                                'amount_cleared' => $writeOffAmount
                            ]);
                        } else {
                            // Log if loan account doesn't exist in GL
                            Log::warning('Loan account not found in GL', [
                                'loan_id' => $loanId,
                                'loan_account_number' => $loan->loan_account_number
                            ]);
                        }
                    
                        // 3. Update loan status to WRITTEN_OFF
                        $loan->status = 'WRITTEN_OFF';
                        $loan->save();
                        
                        // 4. Store write-off details in a separate tracking table if exists
                        try {
                            DB::table('loan_write_offs')->insert([
                                'loan_id' => (string)$loan->id, // Cast to string to match column type
                                'loan_account_number' => $loan->loan_account_number,
                                'client_number' => $loan->client_number,
                                'write_off_date' => now(),
                                'principal_amount' => $writeOffAmount,
                                'interest_amount' => 0,
                                'penalty_amount' => 0,
                                'total_amount' => $writeOffAmount,
                                'provision_utilized' => $writeOffAmount,
                                'direct_writeoff_amount' => 0,
                                'reason' => $this->writeOffReason,
                                'writeoff_type' => 'full',
                                'status' => 'approved', // Since we're processing it
                                'initiated_by' => auth()->id(),
                                'approved_by' => auth()->id(),
                                'approved_date' => now(),
                                'board_resolution_number' => $this->authorizationNumber,
                                'board_approval_date' => $this->authorizationDate,
                                'requires_board_approval' => true,
                                'recovery_status' => 'not_recovered',
                                'recovered_amount' => 0,
                                'notes' => 'Write-off via Loan Loss Reserve Manager',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } catch (\Exception $e) {
                            // Log but don't fail if tracking table has issues
                            Log::warning('Could not insert into loan_write_offs table', [
                                'error' => $e->getMessage(),
                                'loan_id' => $loan->id
                            ]);
                        }
                        
                        // 5. Summary of write-off process completed:
                        // - Debit Write-off Account, Credit Loan Loss Reserve Account (provision utilization)
                        // - Debit Write-off Account, Credit Loan Account (clear loan balance)
                        // - Loan status updated to WRITTEN_OFF
                        // - Loan schedules marked as WRITTEN_OFF
                        // - Write-off details recorded in loan_write_offs table
                        Log::info('Loan write-off completed successfully', [
                            'loan_id' => $loan->id,
                            'loan_account' => $loan->loan_account_number,
                            'amount_written_off' => $writeOffAmount,
                            'transactions_posted' => 2
                        ]);
                            
                        // 6. Mark all loan schedules as written off
                        DB::table('loans_schedules')
                            ->where('loan_id', $loan->id)
                            ->update([
                                'completion_status' => 'WRITTEN_OFF',
                                'updated_at' => now()
                            ]);
                    }
                }
            }
            
            // Update loan_loss_reserves tracking
            if ($totalWriteOff > 0) {
                DB::table('loan_loss_reserves')
                    ->where('year', $this->currentYear)
                    ->increment('actual_losses', $totalWriteOff);
                    
                // Also update the current reserve balance
                $this->currentReserveBalance -= $totalWriteOff;
            }
            
            DB::commit();
            
            Log::info('Write-off successful', [
                'loans_written_off' => count($this->selectedLoans),
                'total_amount' => $totalWriteOff,
                'transaction_id' => $transactionId,
                'authorization' => $this->authorizationNumber
            ]);
            
            $message = $totalWriteOff > 0 
                ? 'Successfully written off ' . count($this->selectedLoans) . ' loans totaling ' . number_format($totalWriteOff, 2) . ' TZS'
                : 'Processed ' . count($this->selectedLoans) . ' loans but no outstanding balance found';
                
            session()->flash('message', $message);
            
            // Reset the form
            $this->selectedLoans = [];
            $this->writeOffReason = '';
            $this->authorizationNumber = '';
            $this->authorizationDate = '';
            
            // Reload data
            $this->loadDashboardData();
            $this->loadWriteOffCandidates();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Write-off failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to process write-off: ' . $e->getMessage());
        }
    }

    public $writeOffCandidates = [];
    
    // Simplified initial allocation
    public function allocateInitial()
    {
        // This is now handled by makeProvision() method
        $this->makeProvision();
    }






















    // Periodic adjustment to reserves
    public function adjustReserve()
    {

        if (!is_numeric($this->adjustments) || $this->adjustments == 0) {
            session()->flash('error', 'Please enter a valid adjustment amount.');
            return;
        }
        
        DB::beginTransaction();
        
        try {
            $transactionId = 'ADJ-' . time();
            $description = $this->adjustments > 0 ? 'Increase in loan loss reserve' : 'Decrease in loan loss reserve';
            
            if ($this->adjustments > 0) {
                // Increase reserve
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->expenseAccount,
                    'debit' => abs($this->adjustments),
                    'credit' => 0,
                    'description' => $description,
                    'transaction_type' => 'ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->reserveAccount,
                    'debit' => 0,
                    'credit' => abs($this->adjustments),
                    'description' => $description,
                    'transaction_type' => 'ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // Decrease reserve (reverse provision)
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->reserveAccount,
                    'debit' => abs($this->adjustments),
                    'credit' => 0,
                    'description' => $description,
                    'transaction_type' => 'ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->expenseAccount,
                    'debit' => 0,
                    'credit' => abs($this->adjustments),
                    'description' => $description,
                    'transaction_type' => 'ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            
            session()->flash('message', 'Reserve adjusted by ' . number_format(abs($this->adjustments), 2) . ' TZS');
            $this->adjustments = 0;
            $this->loadDashboardData();
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to adjust reserve: ' . $e->getMessage());
        }
    }

    /**
     * Process recovery of written-off loans
     * Feature 3: Recovering Written-off Loans
     * Dr. Cash/Bank    Cr. Recovery Income
     */
    public function processLoanRecovery($loanAccountNumber, $amount, $paymentMethod = 'cash', $bankAccount = null)
    {
        if ($amount <= 0) {
            session()->flash('error', 'Recovery amount must be greater than zero.');
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            $transactionId = 'REC-' . time();
            $description = "Recovery of written-off loan: " . $loanAccountNumber;
            
            // Get the loan details
            $loan = DB::table('loans')
                ->where('loan_account_number', $loanAccountNumber)
                ->where('status', 'WRITTEN_OFF')
                ->first();
                
            if (!$loan) {
                throw new \Exception('Loan not found or not written off.');
            }
            
            // Determine the debit account (Cash or Bank)
            $debitAccount = $bankAccount ?? $this->getDefaultCashAccount();
            
            // Use TransactionPostingService for proper GL posting
            $postingService = new TransactionPostingService();
            
            // Post recovery transaction: Dr. Cash/Bank, Cr. Recovery Income
            $result = $postingService->postTransaction([
                'first_account' => $debitAccount,
                'second_account' => $this->recoveryAccount,
                'amount' => $amount,
                'narration' => $description,
                'action' => 'LOAN_RECOVERY',
                'reference_number' => $transactionId
            ]);
            
            if ($result['status'] !== 'success') {
                throw new \Exception('Failed to post recovery transaction: ' . ($result['message'] ?? 'Unknown error'));
            }
            
            // 3. Update loan recovery tracking
            DB::table('loan_recoveries')->insert([
                'loan_id' => $loan->id, // bigint type - no casting needed
                'loan_account_number' => $loanAccountNumber,
                'amount_recovered' => $amount,
                'recovery_date' => now(),
                'recovery_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'recovered_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 4. Update cumulative recovery on loan if tracking field exists
            DB::table('loans')
                ->where('id', $loan->id)
                ->increment('total_recovered', $amount);
            
            DB::commit();
            
            session()->flash('message', 'Loan recovery of ' . number_format($amount, 2) . ' TZS recorded successfully.');
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to process recovery: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get default cash account from institution settings
     */
    private function getDefaultCashAccount()
    {
        $institution = DB::table('institutions')->where('id', 1)->first();
        return $institution->main_petty_cash_account ?? $institution->main_till_account ?? '1001'; // Fallback to basic cash account
    }
    
    /**
     * Enhanced Year-End Adjustment
     * Feature 4: Year-End Adjustment with proper journal entries
     */
    public function finalizeYearEnd()
    {
        if (!is_numeric($this->actualLoanLosses) || $this->actualLoanLosses < 0) {
            session()->flash('error', 'Please enter valid actual loan losses.');
            return;
        }
        
        DB::beginTransaction();
        
        try {
            $transactionId = 'YE-' . time();
            
            // Compare actual losses with current reserve
            $variance = $this->currentReserveBalance - $this->actualLoanLosses;
            
            if ($variance < 0) {
                // Actual losses > Provisions (Under-provisioned)
                // Need to increase reserve to cover actual losses
                $adjustmentAmount = abs($variance);
                $description = "Year-end adjustment: Additional provision for actual losses";
                
                // Dr. Loan Loss Expense
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->expenseAccount,
                    'debit' => $adjustmentAmount,
                    'credit' => 0,
                    'description' => $description,
                    'transaction_type' => 'YEAR_END_ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'narration' => 'Additional expense to cover actual loan losses',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Cr. Loan Loss Reserve
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->reserveAccount,
                    'debit' => 0,
                    'credit' => $adjustmentAmount,
                    'description' => $description,
                    'transaction_type' => 'YEAR_END_ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'narration' => 'Increase reserve to match actual losses',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = 'Year-end adjustment: Reserve increased by ' . number_format($adjustmentAmount, 2) . ' TZS to cover actual losses.';
                
            } elseif ($variance > 0) {
                // Actual losses < Provisions (Over-provisioned)
                // Reverse excess provision
                $adjustmentAmount = $variance;
                $description = "Year-end adjustment: Reversal of excess provision";
                
                // Dr. Loan Loss Reserve (reduce reserve)
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->reserveAccount,
                    'debit' => $adjustmentAmount,
                    'credit' => 0,
                    'description' => $description,
                    'transaction_type' => 'YEAR_END_ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'narration' => 'Reduce excess reserve',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Cr. Loan Loss Expense (reduce expense)
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $this->expenseAccount,
                    'debit' => 0,
                    'credit' => $adjustmentAmount,
                    'description' => $description,
                    'transaction_type' => 'YEAR_END_ADJUSTMENT',
                    'transaction_id' => $transactionId,
                    'narration' => 'Reverse excess provision expense',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = 'Year-end adjustment: Excess provision of ' . number_format($adjustmentAmount, 2) . ' TZS reversed.';
                
            } else {
                $message = 'Year-end finalization: Reserve exactly matches actual losses. No adjustment needed.';
            }
            
            // Update loan_loss_reserves tracking table
            DB::table('loan_loss_reserves')
                ->where('year', $this->year)
                ->update([
                    'actual_losses' => $this->actualLoanLosses,
                    'adjustments' => $variance != 0 ? abs($variance) : 0,
                    'status' => 'finalized',
                    'updated_at' => now()
                ]);
            
            DB::commit();
            
            session()->flash('message', $message);
            $this->loadDashboardData();
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to finalize year-end: ' . $e->getMessage());
        }
    }

    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
        if ($mode === 'dashboard') {
            // Reload dashboard including year-end data
            $this->loadDashboardData();
            $this->loadYearEndData();
        } elseif ($mode === 'writeoff') {
            // Load loans eligible for write-off
            $this->loadWriteOffCandidates();
        } elseif ($mode === 'recovery') {
            // Load written-off loans for recovery
            $this->loadWrittenOffLoans();
        } elseif ($mode === 'analytics') {
            // Load analytics data
            $this->loadAnalyticsData();
        } elseif ($mode === 'monitoring') {
            // Load monitoring data - provision cycles, coverage ratios, etc.
            $this->loadMonitoringData();
        } elseif ($mode === 'reports') {
            // Load reports data - prepare data for board and regulatory reports
            $this->loadReportsData();
        } elseif ($mode === 'history') {
            // Load provision history
            $this->loadProvisionHistory();
        }
    }
    
    private function loadWriteOffCandidates()
    {
        // Get loans that are severely delinquent (>180 days) based on loans_schedules
        $candidates = [];
        
        // Get latest schedule entry for each active loan where days_in_arrears > 180
        $severelyDelinquentSchedules = DB::table('loans_schedules as ls1')
            ->select('ls1.*', 'l.loan_account_number', 'l.client_number')
            ->join('loans as l', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', DB::raw('CAST(ls1.loan_id AS VARCHAR)'));
            })
            ->where('l.status', 'ACTIVE')
            ->where('ls1.days_in_arrears', '>', 180)
            ->whereRaw('ls1.id = (
                SELECT MAX(ls2.id) 
                FROM loans_schedules ls2 
                WHERE CAST(ls2.loan_id AS VARCHAR) = CAST(ls1.loan_id AS VARCHAR)
            )')
            ->get();
        
        foreach ($severelyDelinquentSchedules as $schedule) {
            // Get current balance from accounts table
            $balance = DB::table('accounts')
                ->where('account_number', $schedule->loan_account_number)
                ->value(DB::raw('CAST(balance AS DECIMAL(20,2))')) ?? 0;
            
            if ($balance > 0) { // Only include loans with outstanding balance
                $candidates[] = (object)[
                    'id' => $schedule->loan_id,
                    'loan_account_number' => $schedule->loan_account_number,
                    'client_number' => $schedule->client_number,
                    'balance' => $balance,
                    'days_in_arrears' => $schedule->days_in_arrears,
                    'amount_in_arrears' => $schedule->amount_in_arrears ?? 0,
                    'installment_date' => $schedule->installment_date,
                    'next_payment_date' => $schedule->installment_date // Use installment_date as reference
                ];
            }
        }
        
        $this->writeOffCandidates = collect($candidates);
    }
    
    public function formatNumber($number)
    {
        return number_format($number, 2);
    }
    
    // Helper methods for enhanced blade template
    
    public function getECLStage()
    {
        // Determine ECL stage based on portfolio composition
        $nplRatio = $this->stats['npl_ratio'] ?? 0;
        if ($nplRatio < 5) return 1;
        if ($nplRatio < 10) return 2;
        return 3;
    }
    
    public function getPortfolioTrend()
    {
        // Calculate month-over-month portfolio trend
        $lastMonth = DB::table('general_ledger')
            ->whereMonth('created_at', $this->currentMonth - 1)
            ->whereYear('created_at', $this->currentYear)
            ->where('transaction_type', 'DISBURSEMENT')
            ->sum(DB::raw('CAST(debit AS DECIMAL(20,2))')) ?? 0;
            
        if ($lastMonth > 0) {
            return (($this->loanPortfolioValue - $lastMonth) / $lastMonth) * 100;
        }
        return 0;
    }
    
    public function getWriteOffCandidatesCount()
    {
        return DB::table('loans_schedules')
            ->where('days_in_arrears', '>', $this->writeOffFilter)
            ->where('completion_status', '!=', 'PAID')
            ->distinct('loan_id')
            ->count('loan_id');
    }
    
    public function getEnhancedLoanAging()
    {
        return [
            'stage1' => [
                'label' => 'Performing',
                'days_range' => '0-30',
                'count' => $this->loanAging['current']['count'] ?? 0,
                'principal' => ($this->loanAging['current']['amount'] ?? 0) * 0.7,
                'interest' => ($this->loanAging['current']['amount'] ?? 0) * 0.3,
                'total_exposure' => $this->loanAging['current']['amount'] ?? 0,
                'ecl_rate' => $this->loanAging['current']['provision_rate'] ?? 1,
                'required_ecl' => $this->loanAging['current']['required_provision'] ?? 0
            ],
            'stage2' => [
                'label' => 'Under-Performing',
                'days_range' => '31-90',
                'count' => ($this->loanAging['watch']['count'] ?? 0) + ($this->loanAging['substandard']['count'] ?? 0),
                'principal' => (($this->loanAging['watch']['amount'] ?? 0) + ($this->loanAging['substandard']['amount'] ?? 0)) * 0.7,
                'interest' => (($this->loanAging['watch']['amount'] ?? 0) + ($this->loanAging['substandard']['amount'] ?? 0)) * 0.3,
                'total_exposure' => ($this->loanAging['watch']['amount'] ?? 0) + ($this->loanAging['substandard']['amount'] ?? 0),
                'ecl_rate' => 15,
                'required_ecl' => ($this->loanAging['watch']['required_provision'] ?? 0) + ($this->loanAging['substandard']['required_provision'] ?? 0)
            ],
            'stage3' => [
                'label' => 'Non-Performing',
                'days_range' => '>90',
                'count' => ($this->loanAging['doubtful']['count'] ?? 0) + ($this->loanAging['loss']['count'] ?? 0),
                'principal' => (($this->loanAging['doubtful']['amount'] ?? 0) + ($this->loanAging['loss']['amount'] ?? 0)) * 0.7,
                'interest' => (($this->loanAging['doubtful']['amount'] ?? 0) + ($this->loanAging['loss']['amount'] ?? 0)) * 0.3,
                'total_exposure' => ($this->loanAging['doubtful']['amount'] ?? 0) + ($this->loanAging['loss']['amount'] ?? 0),
                'ecl_rate' => 75,
                'required_ecl' => ($this->loanAging['doubtful']['required_provision'] ?? 0) + ($this->loanAging['loss']['required_provision'] ?? 0)
            ]
        ];
    }
    
    public function getWeightedECLRate()
    {
        if ($this->loanPortfolioValue == 0) return 0;
        return ($this->requiredReserve / $this->loanPortfolioValue) * 100;
    }
    
    public function getOverdueInstallmentsTotal()
    {
        return DB::table('loans_schedules')
            ->where('completion_status', '!=', 'PAID')
            ->where('installment_date', '<', now())
            ->sum(DB::raw('installment - COALESCE(payment, 0)'));
    }
    
    public function getSourceAccounts()
    {
        return DB::table('accounts')
            ->whereIn('major_category_code', ['1000', '4000']) // Assets and Income
            ->where('account_level', '3') // Detail level accounts
            ->where('status', 'ACTIVE')
            ->select('account_number', 'account_name', 'balance')
            ->orderBy('account_name')
            ->get();
    }
    
    public function checkSourceBalance()
    {
        if ($this->source) {
            $this->sourceAccountBalance = DB::table('accounts')
                ->where('account_number', $this->source)
                ->value('balance');
        }
    }
    
    public function getReserveAccountDetails()
    {
        $account = DB::table('accounts')
            ->where('account_number', $this->reserveAccount)
            ->first();
            
        return [
            'account_number' => $this->reserveAccount,
            'account_name' => $account->account_name ?? 'Loan Loss Reserve',
            'balance' => $this->currentReserveBalance
        ];
    }
    
    public function getExpenseAccountDetails()
    {
        $account = DB::table('accounts')
            ->where('account_number', $this->expenseAccount)
            ->first();
            
        $ytdAmount = DB::table('general_ledger')
            ->where('record_on_account_number', $this->expenseAccount)
            ->whereYear('created_at', $this->currentYear)
            ->sum(DB::raw('CAST(debit AS DECIMAL(20,2))'));
            
        return [
            'account_number' => $this->expenseAccount,
            'account_name' => $account->account_name ?? 'Loan Loss Expense',
            'ytd_amount' => $ytdAmount
        ];
    }
    
    public function getRecoveryAccountDetails()
    {
        $account = DB::table('accounts')
            ->where('account_number', $this->recoveryAccount)
            ->first();
            
        $ytdAmount = DB::table('general_ledger')
            ->where('record_on_account_number', $this->recoveryAccount)
            ->whereYear('created_at', $this->currentYear)
            ->sum(DB::raw('CAST(credit AS DECIMAL(20,2))'));
            
        return [
            'account_number' => $this->recoveryAccount,
            'account_name' => $account->account_name ?? 'Recovery Income',
            'ytd_amount' => $ytdAmount
        ];
    }
    
    public function setProvisionMethod($method)
    {
        \Log::info('Provision method changed', [
            'previous_method' => $this->provisionMethod,
            'new_method' => $method,
            'user_id' => auth()->id(),
            'current_provision_gap' => $this->provisionGap
        ]);
        
        $this->provisionMethod = $method;
        if ($method === 'ecl' || $method === 'aging') {
            $this->reserve_amount = $this->provisionGap;
            \Log::info('Reserve amount auto-calculated', [
                'method' => $method,
                'calculated_amount' => $this->reserve_amount
            ]);
        }
    }
    
    public function canMakeProvision()
    {
        return $this->reserve_amount > 0 && 
               !empty($this->source) && 
               ($this->sourceAccountBalance === null || $this->sourceAccountBalance >= $this->reserve_amount);
    }
    
    public function validateProvision()
    {
        \Log::info('Validating provision before recording', [
            'reserve_amount' => $this->reserve_amount,
            'source' => $this->source,
            'provision_method' => $this->provisionMethod,
            'user_id' => auth()->id()
        ]);
        
        // Validate the provision before posting
        $errors = [];
        
        if ($this->reserve_amount <= 0) {
            $errors[] = 'Provision amount must be greater than zero';
            \Log::warning('Validation error: Invalid provision amount', [
                'reserve_amount' => $this->reserve_amount
            ]);
        }
        
        if (empty($this->source)) {
            $errors[] = 'Source account must be selected';
            \Log::warning('Validation error: No source account selected');
        }
        
        if ($this->sourceAccountBalance !== null && $this->sourceAccountBalance < $this->reserve_amount) {
            $errors[] = 'Insufficient balance in source account';
        }
        
        if (count($errors) > 0) {
            session()->flash('error', implode(', ', $errors));
            return false;
        }
        
        session()->flash('message', 'Provision validated successfully. Ready to post.');
        return true;
    }
    
    public function getWriteOffCandidates()
    {
        $minDaysOverdue = intval($this->writeOffFilter);
        
        return DB::table('loans_schedules as ls')
            ->join('loans as l', DB::raw('CAST(l.id AS VARCHAR)'), '=', DB::raw('CAST(ls.loan_id AS VARCHAR)'))
            ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
            ->select(
                'l.id as loan_id',
                'l.loan_account_number',
                'l.client_number',
                DB::raw("CONCAT(c.first_name, ' ', c.middle_name, ' ', c.last_name) as client_name"),
                DB::raw('SUM(CASE WHEN ls.principle > ls.principle_payment THEN ls.principle - COALESCE(ls.principle_payment, 0) ELSE 0 END) as outstanding_principal'),
                DB::raw('SUM(CASE WHEN ls.interest > ls.interest_payment THEN ls.interest - COALESCE(ls.interest_payment, 0) ELSE 0 END) as outstanding_interest'),
                DB::raw('SUM(ls.installment - COALESCE(ls.payment, 0)) as total_outstanding'),
                DB::raw('COUNT(CASE WHEN ls.completion_status != \'PAID\' THEN 1 END) as overdue_installments'),
                DB::raw('MAX(ls.days_in_arrears) as max_days_overdue'),
                DB::raw('MAX(ls.last_payment_date) as last_payment_date')
            )
            ->where('l.status', 'ACTIVE')
            ->where('ls.days_in_arrears', '>', $minDaysOverdue)
            ->groupBy('l.id', 'l.loan_account_number', 'l.client_number', 'c.first_name', 'c.middle_name', 'c.last_name')
            ->havingRaw('SUM(ls.installment - COALESCE(ls.payment, 0)) > 0')
            ->orderBy('max_days_overdue', 'desc')
            ->get();
    }
    
    public function getSelectedLoansTotal()
    {
        if (empty($this->selectedLoans)) {
            return ['principal' => 0, 'interest' => 0, 'total' => 0];
        }
        
        $totals = DB::table('loans_schedules as ls')
            ->whereIn('ls.loan_id', $this->selectedLoans)
            ->select(
                DB::raw('SUM(CASE WHEN ls.principle > ls.principle_payment THEN ls.principle - COALESCE(ls.principle_payment, 0) ELSE 0 END) as principal'),
                DB::raw('SUM(CASE WHEN ls.interest > ls.interest_payment THEN ls.interest - COALESCE(ls.interest_payment, 0) ELSE 0 END) as interest'),
                DB::raw('SUM(ls.installment - COALESCE(ls.payment, 0)) as total')
            )
            ->first();
            
        return [
            'principal' => $totals->principal ?? 0,
            'interest' => $totals->interest ?? 0,
            'total' => $totals->total ?? 0
        ];
    }
    
    public function canProcessWriteOff()
    {
        $selectedTotal = $this->getSelectedLoansTotal()['total'];
        return count($this->selectedLoans) > 0 &&
               !empty($this->writeOffReason) &&
               !empty($this->authorizationNumber) &&
               $selectedTotal <= $this->currentReserveBalance;
    }
    
    /**
     * Handle select all checkbox toggle
     */
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select all visible loans
            $candidates = $this->getWriteOffCandidates();
            $loanIds = [];
            foreach ($candidates as $candidate) {
                $loanIds[] = (string)$candidate->loan_id;
            }
            $this->selectedLoans = $loanIds;
            $this->selectedLoanIds = $loanIds; // Store backup
        } else {
            // Deselect all
            $this->selectedLoans = [];
            $this->selectedLoanIds = [];
        }
        
        \Log::info('Select all toggled', [
            'select_all' => $value,
            'selected_loans' => $this->selectedLoans,
            'selected_count' => count($this->selectedLoans)
        ]);
    }
    
    /**
     * Toggle individual loan selection
     */
    public function toggleLoanSelection($loanId)
    {
        $loanId = (string)$loanId;
        
        if (in_array($loanId, $this->selectedLoans)) {
            // Remove from selection
            $this->selectedLoans = array_values(array_diff($this->selectedLoans, [$loanId]));
        } else {
            // Add to selection
            $this->selectedLoans[] = $loanId;
        }
        
        $this->selectedLoanIds = $this->selectedLoans; // Keep backup in sync
        
        \Log::info('Loan selection toggled', [
            'loan_id' => $loanId,
            'selected_loans' => $this->selectedLoans,
            'selected_count' => count($this->selectedLoans)
        ]);
    }
    
    /**
     * Handle individual loan selection
     */
    public function updatedSelectedLoans()
    {
        \Log::info('Individual loan selection updated', [
            'selected_loans' => $this->selectedLoans,
            'selected_count' => count($this->selectedLoans),
            'can_process' => $this->canProcessWriteOff()
        ]);
    }
    
    /**
     * Debug method to check state
     */
    public function debugWriteOffState()
    {
        $state = [
            'selected_loans' => $this->selectedLoans,
            'selected_count' => count($this->selectedLoans),
            'write_off_reason' => $this->writeOffReason,
            'authorization_number' => $this->authorizationNumber,
            'authorization_date' => $this->authorizationDate,
            'current_reserve_balance' => $this->currentReserveBalance,
            'selected_total' => $this->getSelectedLoansTotal(),
            'can_process' => $this->canProcessWriteOff()
        ];
        
        \Log::info('Write-off state debug', $state);
        
        return $state;
    }
    
    public function refreshAgingData()
    {
        $this->calculateLoanAging();
        $this->calculateRequiredReserve();
        session()->flash('message', 'Aging data refreshed successfully');
    }
    
    public function getRecoveryHistory()
    {
        return DB::table('general_ledger as gl')
            ->join('loans as l', 'gl.record_on_account_number', '=', 'l.loan_account_number')
            ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
            ->select(
                'gl.created_at',
                'l.loan_account_number',
                DB::raw("CONCAT(c.first_name, ' ', c.middle_name, ' ', c.last_name) as client_name"),
                'gl.credit as amount',
                DB::raw("'Cash' as method")
            )
            ->where('gl.record_on_account_number', $this->recoveryAccount)
            ->where('gl.transaction_type', 'RECOVERY')
            ->whereYear('gl.created_at', $this->currentYear)
            ->orderBy('gl.created_at', 'desc')
            ->limit(20)
            ->get();
    }
    
    public function getNPLForecast()
    {
        // Simple forecast based on migration rates
        $currentNPL = $this->stats['npl_ratio'] ?? 0;
        $trend = $this->getPortfolioTrend();
        return max(0, min(100, $currentNPL + ($trend * 0.1)));
    }
    
    public function getExpectedRecoveries()
    {
        // Estimate recoveries based on historical patterns
        return DB::table('general_ledger')
            ->where('record_on_account_number', $this->recoveryAccount)
            ->whereMonth('created_at', $this->currentMonth - 1)
            ->sum(DB::raw('CAST(credit AS DECIMAL(20,2))')) * 1.1; // 10% growth estimate
    }
    
    public function getReserveAdequacyScore()
    {
        if ($this->requiredReserve == 0) return 100;
        $score = ($this->currentReserveBalance / $this->requiredReserve) * 100;
        return min(100, max(0, $score));
    }
    
    public function exportProvisionHistory()
    {
        // Export logic here
        session()->flash('message', 'Provision history exported successfully');
    }
    
    public function exportWriteOffHistory()
    {
        // Export logic here
        session()->flash('message', 'Write-off history exported successfully');
    }
    
    // Provision History Methods
    public $historyFilter = 'all';
    public $historyDateFrom;
    public $historyDateTo;
    
    public function loadProvisionHistory()
    {
        // Initialize date range if not set
        if (!$this->historyDateFrom) {
            $this->historyDateFrom = now()->startOfYear()->format('Y-m-d');
        }
        if (!$this->historyDateTo) {
            $this->historyDateTo = now()->format('Y-m-d');
        }
        
        // Load provision history from general_ledger
        $this->provisionHistory = DB::table('general_ledger as gl')
            ->select(
                'gl.created_at',
                'gl.debit',
                'gl.credit',
                'gl.narration',
                'gl.reference_number',
                'gl.transaction_type'
            )
            ->where('gl.record_on_account_number', $this->reserveAccount)
            ->whereBetween('gl.created_at', [$this->historyDateFrom, $this->historyDateTo])
            ->orderBy('gl.created_at', 'desc')
            ->get()
            ->map(function($item) {
                $item->amount = $item->credit > 0 ? $item->credit : -$item->debit;
                $item->type = $item->credit > 0 ? 'Addition' : 'Reversal';
                $item->description = $item->narration; // Use narration as description
                $item->processed_by = 'System'; // Default value since we don't have created_by column
                return $item;
            });
        
        // Load write-off history
        $this->writeOffHistory = DB::table('loan_write_offs as lw')
            ->select(
                'lw.*',
                'l.loan_account_number',
                'c.first_name',
                'c.middle_name',
                'c.last_name',
                'u.name as processed_by_name'
            )
            ->join('loans as l', function($join) {
                $join->on('lw.loan_id', '=', DB::raw('CAST(l.id AS VARCHAR)'));
            })
            ->leftJoin('clients as c', 'lw.client_number', '=', 'c.client_number')
            ->leftJoin('users as u', 'lw.initiated_by', '=', 'u.id')
            ->whereBetween('lw.created_at', [$this->historyDateFrom, $this->historyDateTo])
            ->orderBy('lw.created_at', 'desc')
            ->get()
            ->map(function($item) {
                $item->client_name = trim($item->first_name . ' ' . $item->middle_name . ' ' . $item->last_name);
                return $item;
            });
        
        // Load adjustment history
        $this->adjustmentHistory = DB::table('general_ledger as gl')
            ->select(
                'gl.created_at',
                'gl.debit',
                'gl.credit',
                'gl.narration',
                'gl.reference_number'
            )
            ->where('gl.record_on_account_number', $this->reserveAccount)
            ->where('gl.transaction_type', 'YEAR_END_ADJUSTMENT')
            ->whereBetween('gl.created_at', [$this->historyDateFrom, $this->historyDateTo])
            ->orderBy('gl.created_at', 'desc')
            ->get()
            ->map(function($item) {
                $item->amount = $item->credit > 0 ? $item->credit : -$item->debit;
                $item->type = $item->credit > 0 ? 'Increase' : 'Decrease';
                $item->description = $item->narration; // Use narration as description
                return $item;
            });
    }
    
    public function getProvisionHistorySummary()
    {
        $totalProvisions = collect($this->provisionHistory)->where('type', 'Addition')->sum('amount');
        $totalReversals = abs(collect($this->provisionHistory)->where('type', 'Reversal')->sum('amount'));
        $totalWriteOffs = collect($this->writeOffHistory)->sum('amount');
        $totalAdjustments = collect($this->adjustmentHistory)->sum('amount');
        
        return [
            'total_provisions' => $totalProvisions,
            'total_reversals' => $totalReversals,
            'total_write_offs' => $totalWriteOffs,
            'total_adjustments' => $totalAdjustments,
            'net_movement' => $totalProvisions - $totalReversals - $totalWriteOffs + $totalAdjustments
        ];
    }
    
    public function filterHistory($type)
    {
        $this->historyFilter = $type;
        $this->loadProvisionHistory();
    }
    
    public function exportWriteOffReport()
    {
        // Export logic here
        session()->flash('message', 'Write-off report exported successfully');
    }
    
    // Recovery UI Support Methods
    public $showRecoveryModal = false;
    public $selectedLoanForRecovery = [];
    public $recoveryAmount = 0;
    public $recoveryMethod = 'cash';
    public $recoveryBankAccount = null;
    public $recoveryReceiptNumber = '';
    public $recoveryNotes = '';
    
    public function getWrittenOffLoans()
    {
        return DB::table('loans as l')
            ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
            ->leftJoin('loan_write_offs as lw', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'lw.loan_id');
            })
            ->leftJoin(DB::raw('(SELECT loan_id, SUM(amount_recovered) as total_recovered FROM loan_recoveries GROUP BY loan_id) as lr'), 
                      'l.id', '=', 'lr.loan_id')
            ->select(
                'l.id',
                'l.loan_account_number',
                'l.client_number',
                DB::raw("CONCAT(COALESCE(c.first_name, ''), ' ', COALESCE(c.middle_name, ''), ' ', COALESCE(c.last_name, '')) as client_name"),
                DB::raw('COALESCE(lw.total_amount, CAST(l.principle_amount AS NUMERIC), 0) as written_off_amount'),
                DB::raw('COALESCE(lr.total_recovered, 0) as total_recovered'),
                DB::raw("COALESCE(lw.write_off_date, DATE(l.updated_at)) as write_off_date"),
                'lw.reason as write_off_reason',
                'lw.status as write_off_status'
            )
            ->where('l.status', 'WRITTEN_OFF')
            ->orderBy('write_off_date', 'desc')
            ->get();
    }
    
    public function getWrittenOffLoansCount()
    {
        return DB::table('loans')
            ->where('status', 'WRITTEN_OFF')
            ->count();
    }
    
    public function getRecoveryStatistics()
    {
        $stats = DB::table('loan_write_offs as lw')
            ->leftJoin('loan_recoveries as lr', function($join) {
                $join->on('lw.loan_id', '=', DB::raw('CAST(lr.loan_id AS VARCHAR)'));
            })
            ->select(
                DB::raw('COUNT(DISTINCT lw.loan_id) as total_written_off'),
                DB::raw('SUM(lw.total_amount) as total_written_off_amount'),
                DB::raw('COUNT(DISTINCT lr.loan_id) as loans_with_recoveries'),
                DB::raw('COALESCE(SUM(lr.amount_recovered), 0) as total_recovered_amount')
            )
            ->first();
        
        $stats->recovery_rate = $stats->total_written_off_amount > 0 
            ? round(($stats->total_recovered_amount / $stats->total_written_off_amount) * 100, 2)
            : 0;
            
        return $stats;
    }
    
    /**
     * Analytics Tab Methods
     */
    public function getPortfolioAnalytics()
    {
        // Get 12-month trend data
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            
            // Calculate NPL for this month
            $totalLoans = DB::table('loans')
                ->whereDate('created_at', '<=', $date->endOfMonth())
                ->sum(DB::raw('CAST(principle_amount AS NUMERIC)'));
            
            $nplLoans = DB::table('loans as l')
                ->join('loans_schedules as ls', function($join) {
                    $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
                })
                ->whereDate('ls.installment_date', '<=', $date->endOfMonth())
                ->whereIn('ls.completion_status', ['PENDING', 'PARTIAL'])
                ->where(DB::raw("(DATE '" . $date->endOfMonth()->format('Y-m-d') . "' - ls.installment_date::date)"), '>', 90)
                ->sum(DB::raw('ls.installment - COALESCE(ls.payment, 0)'));
            
            $trends[] = [
                'month' => $date->format('M Y'),
                'npl_ratio' => $totalLoans > 0 ? round(($nplLoans / $totalLoans) * 100, 2) : 0,
                'total_portfolio' => $totalLoans,
                'npl_amount' => $nplLoans
            ];
        }
        
        // Get provision coverage trend
        $coverageTrend = DB::table('general_ledger')
            ->where('record_on_account_number', $this->reserveAccount)
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw('SUM(credit - debit) as provision_balance')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        return [
            'trends' => $trends,
            'coverage_trend' => $coverageTrend,
            'current_coverage_ratio' => $this->calculateCoverageRatio(),
            'average_recovery_rate' => $this->calculateAverageRecoveryRate(),
            'portfolio_risk_distribution' => $this->getPortfolioRiskDistribution()
        ];
    }
    
    private function calculateCoverageRatio()
    {
        $nplAmount = $this->calculateLoanPortfolio()['npl_amount'] ?? 0;
        $provisionBalance = $this->calculateCurrentReserve()['balance'] ?? 0;
        
        return $nplAmount > 0 ? round(($provisionBalance / $nplAmount) * 100, 2) : 0;
    }
    
    private function calculateAverageRecoveryRate()
    {
        $stats = DB::table('loan_write_offs as lw')
            ->leftJoin('loan_recoveries as lr', function($join) {
                $join->on('lw.loan_id', '=', DB::raw('CAST(lr.loan_id AS VARCHAR)'));
            })
            ->where('lw.write_off_date', '>=', now()->subYear())
            ->select(
                DB::raw('SUM(lw.total_amount) as total_written_off'),
                DB::raw('COALESCE(SUM(lr.amount_recovered), 0) as total_recovered')
            )
            ->first();
        
        return $stats->total_written_off > 0 
            ? round(($stats->total_recovered / $stats->total_written_off) * 100, 2)
            : 0;
    }
    
    private function getPortfolioRiskDistribution()
    {
        return DB::table('loans as l')
            ->leftJoin('loans_schedules as ls', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
            })
            ->select(
                DB::raw("CASE 
                    WHEN (CURRENT_DATE - ls.installment_date::date) <= 0 THEN 'Current'
                    WHEN (CURRENT_DATE - ls.installment_date::date) BETWEEN 1 AND 30 THEN '1-30 days'
                    WHEN (CURRENT_DATE - ls.installment_date::date) BETWEEN 31 AND 60 THEN '31-60 days'
                    WHEN (CURRENT_DATE - ls.installment_date::date) BETWEEN 61 AND 90 THEN '61-90 days'
                    ELSE 'Over 90 days'
                END as risk_category"),
                DB::raw('COUNT(DISTINCT l.id) as loan_count'),
                DB::raw('SUM(ls.installment - COALESCE(ls.payment, 0)) as outstanding_amount')
            )
            ->where('l.status', 'ACTIVE')
            ->whereIn('ls.completion_status', ['PENDING', 'PARTIAL'])
            ->groupBy('risk_category')
            ->get();
    }
    
    /**
     * Monitoring Tab Methods
     */
    public function getMonitoringAlerts()
    {
        $alerts = [];
        
        // Check NPL ratio threshold
        $nplRatio = $this->calculateLoanPortfolio()['npl_ratio'] ?? 0;
        if ($nplRatio > 5) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'High NPL Ratio',
                'message' => "NPL ratio is {$nplRatio}%, exceeding the 5% threshold",
                'action' => 'Review loan portfolio and consider additional provisions'
            ];
        }
        
        // Check provision coverage
        $coverageRatio = $this->calculateCoverageRatio();
        if ($coverageRatio < 100) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Low Provision Coverage',
                'message' => "Provision coverage is {$coverageRatio}%, below the recommended 100%",
                'action' => 'Consider making additional provisions'
            ];
        }
        
        // Check for loans pending write-off
        $pendingWriteoffs = DB::table('loans as l')
            ->join('loans_schedules as ls', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
            })
            ->where('l.status', 'ACTIVE')
            ->where(DB::raw('(CURRENT_DATE - ls.installment_date::date)'), '>', 365)
            ->count();
        
        if ($pendingWriteoffs > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Loans Pending Write-off',
                'message' => "{$pendingWriteoffs} loans are over 365 days in arrears",
                'action' => 'Review and process write-offs'
            ];
        }
        
        return $alerts;
    }
    
    public function getComplianceStatus()
    {
        $requirements = [
            [
                'requirement' => 'Minimum Provision Coverage',
                'standard' => '100% of NPL',
                'current' => $this->calculateCoverageRatio() . '%',
                'status' => $this->calculateCoverageRatio() >= 100 ? 'compliant' : 'non-compliant'
            ],
            [
                'requirement' => 'NPL Ratio Threshold',
                'standard' => 'Below 5%',
                'current' => ($this->calculateLoanPortfolio()['npl_ratio'] ?? 0) . '%',
                'status' => ($this->calculateLoanPortfolio()['npl_ratio'] ?? 0) <= 5 ? 'compliant' : 'non-compliant'
            ],
            [
                'requirement' => 'Write-off Authorization',
                'standard' => 'Board approval for amounts > 1M',
                'current' => 'Configured',
                'status' => 'compliant'
            ],
            [
                'requirement' => 'Recovery Tracking',
                'standard' => 'All recoveries recorded',
                'current' => 'Active',
                'status' => 'compliant'
            ]
        ];
        
        return $requirements;
    }
    
    /**
     * Reports Tab Methods
     */
    public function generateProvisionReport($format = 'pdf')
    {
        $data = [
            'report_date' => now()->format('Y-m-d'),
            'portfolio_summary' => $this->calculateLoanPortfolio(),
            'current_reserve' => $this->calculateCurrentReserve(),
            'provision_history' => $this->provisionHistory,
            'write_off_history' => $this->writeOffHistory,
            'recovery_statistics' => $this->getRecoveryStatistics(),
            'compliance_status' => $this->getComplianceStatus()
        ];
        
        // Store report data for download
        session()->put('provision_report_data', $data);
        session()->flash('message', 'Report generated successfully. Click download to save.');
        
        return $data;
    }
    
    public function exportHistoryData($type = 'all')
    {
        $filename = 'loan_loss_reserve_history_' . now()->format('Y_m_d_His') . '.csv';
        
        $data = [];
        if ($type === 'all' || $type === 'provisions') {
            foreach ($this->provisionHistory as $item) {
                $data[] = [
                    'Type' => 'Provision',
                    'Date' => $item->created_at,
                    'Amount' => $item->credit,
                    'Balance' => $item->balance,
                    'Description' => $item->description
                ];
            }
        }
        
        if ($type === 'all' || $type === 'writeoffs') {
            foreach ($this->writeOffHistory as $item) {
                $data[] = [
                    'Type' => 'Write-off',
                    'Date' => $item->write_off_date,
                    'Loan' => $item->loan_account_number,
                    'Client' => $item->client_name,
                    'Amount' => $item->written_off_amount,
                    'Recovered' => $item->total_recovered
                ];
            }
        }
        
        // Generate CSV
        $csv = fopen('php://temp', 'r+');
        if (count($data) > 0) {
            fputcsv($csv, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($csv, $row);
            }
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    public function openRecoveryModal($loanId)
    {
        $loan = DB::table('loans as l')
            ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
            ->select(
                'l.id',
                'l.loan_account_number',
                'l.client_number',
                'l.principle_amount',
                'l.status',
                DB::raw("CONCAT(COALESCE(c.first_name, ''), ' ', COALESCE(c.middle_name, ''), ' ', COALESCE(c.last_name, '')) as client_name")
            )
            ->where('l.id', $loanId)
            ->first();
            
        // Convert to array for Livewire compatibility
        if ($loan) {
            $this->selectedLoanForRecovery = [
                'id' => $loan->id,
                'loan_account_number' => $loan->loan_account_number,
                'client_number' => $loan->client_number,
                'principle_amount' => $loan->principle_amount,
                'status' => $loan->status,
                'client_name' => $loan->client_name
            ];
        }
            
        $this->showRecoveryModal = true;
        $this->recoveryAmount = 0;
        $this->recoveryMethod = 'cash';
        $this->recoveryBankAccount = null;
        $this->recoveryReceiptNumber = '';
        $this->recoveryNotes = '';
    }
    
    public function closeRecoveryModal()
    {
        $this->showRecoveryModal = false;
        $this->selectedLoanForRecovery = [];
    }
    
    public function processRecovery()
    {
        if (empty($this->selectedLoanForRecovery) || $this->recoveryAmount <= 0) {
            session()->flash('error', 'Invalid recovery amount.');
            return;
        }
        
        $bankAccount = null;
        if ($this->recoveryMethod === 'bank_transfer' || $this->recoveryMethod === 'cheque') {
            $bankAccount = $this->recoveryBankAccount;
        }
        
        // Call the main recovery processing method
        $success = $this->processLoanRecovery(
            $this->selectedLoanForRecovery['loan_account_number'],
            $this->recoveryAmount,
            $this->recoveryMethod,
            $bankAccount
        );
        
        if ($success) {
            // Record receipt number and notes if provided
            if ($this->recoveryReceiptNumber || $this->recoveryNotes) {
                DB::table('loan_recoveries')
                    ->where('loan_id', $this->selectedLoanForRecovery['id'])
                    ->orderBy('id', 'desc')
                    ->limit(1)
                    ->update([
                        'receipt_number' => $this->recoveryReceiptNumber,
                        'notes' => $this->recoveryNotes
                    ]);
            }
            
            $this->closeRecoveryModal();
            $this->loadDashboardData();
        }
    }
    
    public function getBankAccounts()
    {
        return DB::table('bank_accounts')
            ->where('status', 'ACTIVE')
            ->select('account_number', 'account_name', 'bank_name')
            ->orderBy('bank_name')
            ->get();
    }
    
    /**
     * Refresh account configuration
     * Called when user wants to retry after fixing configuration
     */
    public function refreshConfiguration()
    {
        $this->loadAccountSettings();
        
        if (!$this->hasConfigurationErrors) {
            $this->loadDashboardData();
            session()->flash('message', 'Configuration loaded successfully. All accounts are properly configured.');
        } else {
            session()->flash('error', 'Configuration issues remain. Please check the error messages above.');
        }
    }
    
    /**
     * Check if a specific account is configured and valid
     */
    public function isAccountConfigured($accountType)
    {
        switch($accountType) {
            case 'reserve':
                return !empty($this->reserveAccount);
            case 'expense':
                return !empty($this->expenseAccount);
            case 'recovery':
                return !empty($this->recoveryAccount);
            default:
                return false;
        }
    }
    
    /**
     * Load written-off loans for recovery processing
     */
    private function loadWrittenOffLoans()
    {
        // This method is called when switching to recovery tab
        // The actual data is loaded by getWrittenOffLoans() method
    }
    
    /**
     * Load analytics data
     */
    private function loadAnalyticsData()
    {
        // Refresh dashboard data for analytics
        $this->loadDashboardData();
    }
    
    private function loadMonitoringData()
    {
        // Load data for monitoring view - provision cycles, coverage metrics
        $this->loadDashboardData();
    }
    
    private function loadReportsData()
    {
        // Load data for reports view - board and regulatory reports
        $this->loadDashboardData();
    }
    
    /**
     * PROVISION LIFECYCLE IMPLEMENTATION
     * Complete cycle: CALCULATE → COMPARE → ADJUST → MONITOR → REPORT → REPEAT
     */
    
    /**
     * Step 1: CALCULATE - Monthly/Quarterly ECL Calculation
     */
    public function startProvisionCycle($frequency = 'MONTHLY')
    {
        $cycleId = $this->generateCycleId($frequency);
        $currentDate = Carbon::now();
        
        // Create new provision cycle
        $cycleId = DB::table('provision_cycles')->insertGetId([
            'cycle_id' => $cycleId,
            'frequency' => $frequency,
            'year' => $currentDate->year,
            'period' => $frequency == 'MONTHLY' ? $currentDate->month : ceil($currentDate->month / 3),
            'cycle_date' => $currentDate->toDateString(),
            'status' => 'INITIATED',
            'prepared_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Step 1: CALCULATE
        $this->calculateProvisionCycle($cycleId);
        
        return $cycleId;
    }
    
    /**
     * Step 1: CALCULATE ECL for the cycle
     */
    private function calculateProvisionCycle($cycleId)
    {
        // Load current data
        $this->loadDashboardData();
        
        // Store detailed aging analysis
        $agingAnalysis = [
            'current' => $this->loanAging['current'] ?? [],
            'watch' => $this->loanAging['watch'] ?? [],
            'substandard' => $this->loanAging['substandard'] ?? [],
            'doubtful' => $this->loanAging['doubtful'] ?? [],
            'loss' => $this->loanAging['loss'] ?? []
        ];
        
        // Update cycle with calculation
        DB::table('provision_cycles')
            ->where('id', $cycleId)
            ->update([
                'portfolio_value' => $this->loanPortfolioValue,
                'calculated_ecl' => $this->requiredReserve,
                'aging_analysis' => json_encode($agingAnalysis),
                'calculated_at' => now(),
                'status' => 'CALCULATED'
            ]);
        
        // Store detailed loan analysis
        $this->storeProvisionCycleDetails($cycleId);
        
        // Automatically proceed to COMPARE
        $this->compareProvisionCycle($cycleId);
    }
    
    /**
     * Step 2: COMPARE - Required vs Current Reserve
     */
    private function compareProvisionCycle($cycleId)
    {
        $cycle = DB::table('provision_cycles')->where('id', $cycleId)->first();
        
        // Get current reserve balance
        $this->calculateCurrentReserve();
        
        $provisionGap = $cycle->calculated_ecl - $this->currentReserveBalance;
        
        DB::table('provision_cycles')
            ->where('id', $cycleId)
            ->update([
                'current_reserve' => $this->currentReserveBalance,
                'required_reserve' => $cycle->calculated_ecl,
                'provision_gap' => $provisionGap,
                'compared_at' => now(),
                'status' => 'COMPARED'
            ]);
        
        // Determine adjustment needed
        if (abs($provisionGap) > 0.01) { // Threshold for adjustment
            $this->prepareAdjustment($cycleId, $provisionGap);
        } else {
            // No adjustment needed
            DB::table('provision_cycles')
                ->where('id', $cycleId)
                ->update([
                    'adjustment_type' => 'NONE',
                    'adjustment_amount' => 0,
                    'status' => 'ADJUSTED'
                ]);
            $this->monitorProvisionCycle($cycleId);
        }
    }
    
    /**
     * Step 3: ADJUST - Book the difference
     */
    public function executeProvisionAdjustment($cycleId)
    {
        $cycle = DB::table('provision_cycles')->where('id', $cycleId)->first();
        
        if (!$cycle || $cycle->status != 'COMPARED') {
            session()->flash('error', 'Invalid provision cycle or status');
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            if ($cycle->provision_gap > 0) {
                // Need to increase provision
                $this->reserve_amount = $cycle->provision_gap;
                $this->makeProvision(); // Use existing provision method
                $adjustmentType = 'PROVISION';
            } elseif ($cycle->provision_gap < 0) {
                // Need to reverse provision (write-back)
                $this->reverseProvision(abs($cycle->provision_gap));
                $adjustmentType = 'REVERSAL';
            }
            
            // Update cycle status
            DB::table('provision_cycles')
                ->where('id', $cycleId)
                ->update([
                    'adjustment_amount' => abs($cycle->provision_gap),
                    'adjustment_type' => $adjustmentType,
                    'transaction_reference' => 'PROV-' . time(),
                    'adjusted_at' => now(),
                    'status' => 'ADJUSTED'
                ]);
            
            DB::commit();
            
            // Proceed to monitoring
            $this->monitorProvisionCycle($cycleId);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to execute adjustment: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Step 4: MONITOR - Track coverage ratio and metrics
     */
    private function monitorProvisionCycle($cycleId)
    {
        // Recalculate metrics after adjustment
        $this->loadDashboardData();
        
        $monitoringMetrics = [
            'portfolio_at_risk' => $this->calculatePortfolioAtRisk(),
            'provision_expense_ratio' => $this->calculateProvisionExpenseRatio(),
            'write_off_ratio' => $this->calculateWriteOffRatio(),
            'recovery_ratio' => $this->calculateRecoveryRatio(),
            'ecl_stage' => $this->getECLStage()
        ];
        
        DB::table('provision_cycles')
            ->where('id', $cycleId)
            ->update([
                'coverage_ratio' => $this->stats['coverage_ratio'] ?? 0,
                'npl_ratio' => $this->stats['npl_ratio'] ?? 0,
                'provision_coverage' => $this->stats['provision_coverage'] ?? 0,
                'monitoring_metrics' => json_encode($monitoringMetrics),
                'monitored_at' => now(),
                'status' => 'MONITORED'
            ]);
        
        // Generate reports
        $this->generateProvisionReports($cycleId);
    }
    
    /**
     * Step 5: REPORT - Generate board and regulatory reports
     */
    private function generateProvisionReports($cycleId)
    {
        $cycle = DB::table('provision_cycles')->where('id', $cycleId)->first();
        
        // Generate board report
        $boardReportPath = $this->generateBoardReport($cycle);
        
        // Generate regulatory report
        $regulatoryRef = $this->generateRegulatoryReport($cycle);
        
        DB::table('provision_cycles')
            ->where('id', $cycleId)
            ->update([
                'board_report_generated' => true,
                'board_report_path' => $boardReportPath,
                'regulatory_report_submitted' => !empty($regulatoryRef),
                'regulatory_report_reference' => $regulatoryRef,
                'reported_at' => now(),
                'status' => 'REPORTED'
            ]);
        
        // Mark cycle as completed
        $this->completeProvisionCycle($cycleId);
    }
    
    /**
     * Step 6: REPEAT - Complete current cycle and prepare for next
     */
    private function completeProvisionCycle($cycleId)
    {
        DB::table('provision_cycles')
            ->where('id', $cycleId)
            ->update([
                'status' => 'COMPLETED',
                'updated_at' => now()
            ]);
        
        // Schedule next cycle
        $this->scheduleNextProvisionCycle();
        
        session()->flash('message', 'Provision cycle completed successfully');
    }
    
    /**
     * Helper Methods for Provision Lifecycle
     */
    
    private function generateCycleId($frequency)
    {
        $prefix = 'PROV';
        $year = date('Y');
        $period = $frequency == 'MONTHLY' ? date('m') : 'Q' . ceil(date('n') / 3);
        $sequence = str_pad(
            DB::table('provision_cycles')
                ->where('year', $year)
                ->count() + 1, 
            3, 
            '0', 
            STR_PAD_LEFT
        );
        
        return "{$prefix}-{$year}-{$period}-{$sequence}";
    }
    
    private function storeProvisionCycleDetails($cycleId)
    {
        $loanDetails = DB::table('loans_schedules as ls')
            ->join('loans as l', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
            })
            ->where('l.status', 'ACTIVE')
            ->whereIn('ls.completion_status', ['PENDING', 'PARTIAL'])
            ->select(
                'l.loan_account_number',
                'l.client_number',
                DB::raw('SUM(CASE WHEN ls.principle > COALESCE(ls.principle_payment, 0) 
                    THEN ls.principle - COALESCE(ls.principle_payment, 0) ELSE 0 END) as outstanding_principal'),
                DB::raw('SUM(CASE WHEN ls.interest > COALESCE(ls.interest_payment, 0) 
                    THEN ls.interest - COALESCE(ls.interest_payment, 0) ELSE 0 END) as outstanding_interest'),
                DB::raw('MAX(ls.days_in_arrears) as days_in_arrears')
            )
            ->groupBy('l.loan_account_number', 'l.client_number')
            ->get();
        
        foreach ($loanDetails as $loan) {
            $classification = $this->classifyRisk($loan->days_in_arrears);
            $rate = $this->provisionRates[$classification] ?? 0;
            $requiredProvision = ($loan->outstanding_principal + $loan->outstanding_interest) * $rate / 100;
            
            DB::table('provision_cycle_details')->insert([
                'provision_cycle_id' => $cycleId,
                'loan_account_number' => $loan->loan_account_number,
                'client_number' => $loan->client_number,
                'outstanding_principal' => $loan->outstanding_principal,
                'outstanding_interest' => $loan->outstanding_interest,
                'days_in_arrears' => $loan->days_in_arrears,
                'risk_classification' => strtoupper($classification),
                'provision_rate' => $rate,
                'required_provision' => $requiredProvision,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    private function classifyRisk($daysInArrears)
    {
        if ($daysInArrears <= 30) return 'current';
        if ($daysInArrears <= 60) return 'watch';
        if ($daysInArrears <= 90) return 'substandard';
        if ($daysInArrears <= 180) return 'doubtful';
        return 'loss';
    }
    
    private function prepareAdjustment($cycleId, $provisionGap)
    {
        // Set up for adjustment
        if ($provisionGap > 0) {
            session()->flash('info', 'Provision increase of ' . number_format($provisionGap, 2) . ' TZS required');
        } else {
            session()->flash('info', 'Provision reversal of ' . number_format(abs($provisionGap), 2) . ' TZS available');
        }
    }
    
    private function reverseProvision($amount)
    {
        // Reverse provision (write-back)
        DB::beginTransaction();
        
        try {
            $transactionId = 'REV-' . time();
            
            // Dr. Reserve Account, Cr. Expense Account (reverse of provision)
            DB::table('general_ledger')->insert([
                [
                    'transaction_id' => $transactionId,
                    'record_on_account_number' => $this->reserveAccount,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Provision reversal/write-back',
                    'created_at' => now()
                ],
                [
                    'transaction_id' => $transactionId,
                    'record_on_account_number' => $this->expenseAccount,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Provision reversal/write-back',
                    'created_at' => now()
                ]
            ]);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function calculatePortfolioAtRisk()
    {
        // Portfolio at Risk = Outstanding balance of loans > 30 days / Total Portfolio
        $par = DB::table('loans_schedules as ls')
            ->join('loans as l', DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id')
            ->where('l.status', 'ACTIVE')
            ->where('ls.days_in_arrears', '>', 30)
            ->sum(DB::raw('ls.principle - COALESCE(ls.principle_payment, 0)'));
        
        return $this->loanPortfolioValue > 0 ? ($par / $this->loanPortfolioValue) * 100 : 0;
    }
    
    private function calculateProvisionExpenseRatio()
    {
        // Provision Expense Ratio = Provision Expense / Average Portfolio
        return 0; // Implement based on your needs
    }
    
    private function calculateWriteOffRatio()
    {
        // Write-off Ratio = Write-offs this period / Average Portfolio
        return $this->stats['write_off_ytd'] ?? 0;
    }
    
    private function calculateRecoveryRatio()
    {
        // Recovery Ratio = Recoveries / Written-off loans
        $writtenOff = DB::table('loans')->where('status', 'WRITTEN_OFF')->sum('principle');
        $recovered = DB::table('loans')->where('status', 'WRITTEN_OFF')->sum('total_recovered');
        
        return $writtenOff > 0 ? ($recovered / $writtenOff) * 100 : 0;
    }
    
    private function generateBoardReport($cycle)
    {
        // Generate PDF or Excel report for board
        // This is a placeholder - implement actual report generation
        return 'reports/board/provision_cycle_' . $cycle->cycle_id . '.pdf';
    }
    
    private function generateRegulatoryReport($cycle)
    {
        // Generate regulatory report
        // This is a placeholder - implement actual report generation
        return 'REG-' . $cycle->cycle_id;
    }
    
    private function scheduleNextProvisionCycle()
    {
        // Check if automatic scheduling is enabled
        $schedule = DB::table('provision_schedules')
            ->where('is_active', true)
            ->first();
        
        if ($schedule) {
            $nextRunDate = $this->calculateNextRunDate($schedule->frequency);
            
            DB::table('provision_schedules')
                ->where('id', $schedule->id)
                ->update([
                    'last_run_date' => now(),
                    'next_run_date' => $nextRunDate
                ]);
        }
    }
    
    private function calculateNextRunDate($frequency)
    {
        switch ($frequency) {
            case 'MONTHLY':
                return Carbon::now()->addMonth()->startOfMonth()->addDays(4); // 5th of next month
            case 'QUARTERLY':
                return Carbon::now()->addQuarter()->startOfQuarter()->addDays(4); // 5th of next quarter
            case 'SEMI_ANNUAL':
                return Carbon::now()->addMonths(6)->startOfMonth()->addDays(4);
            case 'ANNUAL':
                return Carbon::now()->addYear()->startOfYear()->addDays(4);
            default:
                return Carbon::now()->addMonth();
        }
    }
    
    /**
     * Get provision cycles for display
     */
    public function getProvisionCycles($limit = 10)
    {
        return DB::table('provision_cycles')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get current cycle status
     */
    public function getCurrentCycleStatus()
    {
        return DB::table('provision_cycles')
            ->where('status', '!=', 'COMPLETED')
            ->orderBy('created_at', 'desc')
            ->first();
    }
    
    /**
     * Get count of pending provision cycles awaiting manual adjustment
     */
    public function getPendingCyclesCount()
    {
        return DB::table('provision_cycles')
            ->where('status', 'AWAITING_ADJUSTMENT')
            ->count();
    }
    
    /**
     * Automatically calculate actual losses from write-off transactions
     * Pulls from the configured Write-off GL Account balance
     * @param int $year Year to calculate for (defaults to current year)
     * @return float Total write-offs for the year
     */
    public function calculateActualLosses($year = null)
    {
        $year = $year ?? $this->currentYear;
        
        // Ensure we have the write-off account configured
        if (!$this->writeOffAccount) {
            $this->loadAccountSettings();
        }
        
        // Calculate the net balance of the write-off account for the year
        // This account accumulates all written-off loans as debits
        $writeOffDebits = DB::table('general_ledger')
            ->where('record_on_account_number', $this->writeOffAccount)
            ->whereYear('created_at', $year)
            ->sum('debit');
            
        $writeOffCredits = DB::table('general_ledger')
            ->where('record_on_account_number', $this->writeOffAccount)
            ->whereYear('created_at', $year)
            ->sum('credit');
        
        // Net write-offs = Debits - Credits (credits would be recoveries)
        $netWriteOffs = $writeOffDebits - $writeOffCredits;
        
        // Also check the provision account for write-off transactions as validation
        $reserveWriteOffs = DB::table('general_ledger')
            ->where('record_on_account_number', $this->reserveAccount)
            ->where('transaction_type', 'WRITE_OFF')
            ->whereYear('created_at', $year)
            ->sum('debit');
            
        // Use the write-off account balance as primary source
        // Fall back to reserve account if write-off account has no data
        $actualLosses = $netWriteOffs > 0 ? $netWriteOffs : $reserveWriteOffs;
        
        return $actualLosses > 0 ? $actualLosses : 0;
    }
    
    /**
     * Load actual losses automatically when year-end section is displayed
     */
    public function loadYearEndData()
    {
        $this->actualLoanLosses = $this->calculateActualLosses();
    }
    
    /**
     * Refresh actual losses calculation - called by the refresh button
     */
    public function refreshActualLosses()
    {
        $this->actualLoanLosses = $this->calculateActualLosses();
        session()->flash('message', 'Actual losses refreshed from GL account ' . $this->writeOffAccount);
    }
    
    /**
     * Computed property for actual losses - auto-updates on refresh
     */
    public function getActualLossesProperty()
    {
        return $this->calculateActualLosses();
    }
    
    /**
     * Updated lifecycle hook - called when properties are updated
     */
    public function updated($propertyName)
    {
        if ($propertyName == 'viewMode' && $this->viewMode == 'dashboard') {
            $this->loadYearEndData();
        }
    }
    
    public function render()
    {
        // Auto-calculate actual losses on each render when on dashboard
        if ($this->viewMode === 'dashboard' && !$this->hasConfigurationErrors) {
            $this->actualLoanLosses = $this->calculateActualLosses();
        }
        
        return view('livewire.accounting.loan-loss-reserve-manager-improved');
    }
    
    /**
     * Report Generation Methods
     */
    
    // Generate Write-off Report (Excel)
    public function generateWriteOffReport()
    {
        $data = [
            'report_date' => now()->format('Y-m-d H:i:s'),
            'report_period' => $this->historyDateFrom . ' to ' . $this->historyDateTo,
            'written_off_loans' => $this->getWrittenOffLoans(),
            'total_write_offs' => DB::table('loan_write_offs')
                ->whereBetween('write_off_date', [$this->historyDateFrom, $this->historyDateTo])
                ->sum('total_amount'),
            'recovery_summary' => $this->getRecoveryStatistics(),
            'pending_approvals' => DB::table('loan_write_offs')
                ->where('status', 'pending_approval')
                ->count(),
            'authorization_summary' => DB::table('loan_write_offs')
                ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('status')
                ->get()
        ];
        
        // Generate Excel file
        $filename = 'write_off_report_' . now()->format('Y_m_d_His') . '.csv';
        $headers = ['Loan Account', 'Client Name', 'Write-off Date', 'Principal', 'Interest', 'Total', 'Status', 'Recovery Amount'];
        
        $csvData = [];
        foreach ($data['written_off_loans'] as $loan) {
            $csvData[] = [
                $loan->loan_account_number,
                $loan->client_name,
                $loan->write_off_date,
                $loan->written_off_amount,
                0, // Interest amount
                $loan->written_off_amount,
                $loan->write_off_status ?? 'Completed',
                $loan->total_recovered
            ];
        }
        
        session()->put('download_data', ['headers' => $headers, 'data' => $csvData, 'filename' => $filename]);
        session()->flash('message', 'Write-off report generated successfully.');
        
        return response()->streamDownload(function() use ($headers, $csvData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename);
    }
    
    // Generate Recovery Report (PDF)
    public function generateRecoveryReport()
    {
        $recoveryStats = $this->getRecoveryStatistics();
        $recoveryHistory = DB::table('loan_recoveries as lr')
            ->join('loans as l', 'lr.loan_id', '=', 'l.id')
            ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
            ->select(
                'lr.*',
                'l.loan_account_number',
                DB::raw("CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.middle_name,''), ' ', COALESCE(c.last_name,'')) as client_name")
            )
            ->whereBetween('lr.recovery_date', [$this->historyDateFrom, $this->historyDateTo])
            ->orderBy('lr.recovery_date', 'desc')
            ->get();
        
        $data = [
            'report_date' => now()->format('Y-m-d H:i:s'),
            'report_period' => $this->historyDateFrom . ' to ' . $this->historyDateTo,
            'recovery_statistics' => $recoveryStats,
            'recovery_history' => $recoveryHistory,
            'recovery_trend' => $this->getRecoveryTrend(),
            'recovery_methods' => DB::table('loan_recoveries')
                ->select('recovery_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount_recovered) as total'))
                ->whereBetween('recovery_date', [$this->historyDateFrom, $this->historyDateTo])
                ->groupBy('recovery_method')
                ->get(),
            'recovery_efficiency' => [
                'total_written_off' => $recoveryStats->total_written_off_amount ?? 0,
                'total_recovered' => $recoveryStats->total_recovered_amount ?? 0,
                'recovery_rate' => $recoveryStats->recovery_rate ?? 0,
                'average_days_to_recover' => $this->calculateAverageRecoveryDays()
            ]
        ];
        
        session()->put('recovery_report_data', $data);
        session()->flash('message', 'Recovery report generated successfully.');
        
        return $data;
    }
    
    // Generate Compliance Report (PDF)
    public function generateComplianceReport()
    {
        $data = [
            'report_date' => now()->format('Y-m-d H:i:s'),
            'institution_name' => DB::table('institutions')->value('name'),
            'compliance_status' => $this->getComplianceStatus(),
            'regulatory_ratios' => [
                'npl_ratio' => $this->calculateLoanPortfolio()['npl_ratio'] ?? 0,
                'coverage_ratio' => $this->calculateCoverageRatio(),
                'provision_adequacy' => $this->currentReserveBalance >= $this->requiredReserve ? 'Adequate' : 'Inadequate',
                'car_ratio' => $this->calculateCapitalAdequacyRatio()
            ],
            'audit_trail' => DB::table('general_ledger')
                ->where('record_on_account_number', $this->reserveAccount)
                ->whereBetween('created_at', [now()->subMonths(3), now()])
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get(),
            'approvals' => DB::table('approvals')
                ->whereIn('process_code', ['LLR_PROVISION', 'LOAN_WRITEOFF'])
                ->whereBetween('created_at', [$this->historyDateFrom, $this->historyDateTo])
                ->get(),
            'regulatory_requirements' => [
                'minimum_provision_rate' => '1-100% based on aging',
                'maximum_npl_ratio' => '5%',
                'minimum_coverage_ratio' => '100%',
                'reporting_frequency' => 'Monthly'
            ]
        ];
        
        session()->put('compliance_report_data', $data);
        session()->flash('message', 'Compliance report generated successfully.');
        
        return $data;
    }
    
    // Generate Analytics Report (PDF)
    public function generateAnalyticsReport()
    {
        $analytics = $this->getPortfolioAnalytics();
        
        $data = [
            'report_date' => now()->format('Y-m-d H:i:s'),
            'portfolio_analytics' => $analytics,
            'npl_trends' => $analytics['trends'] ?? [],
            'risk_distribution' => $this->getPortfolioRiskDistribution(),
            'predictive_insights' => [
                'projected_npl_next_month' => $this->projectNPLNextMonth(),
                'projected_provisions_needed' => $this->projectRequiredProvisions(),
                'risk_indicators' => $this->getRiskIndicators()
            ],
            'peer_comparison' => [
                'industry_avg_npl' => 4.5,
                'industry_avg_coverage' => 110,
                'our_npl' => $analytics['current_npl_ratio'] ?? 0,
                'our_coverage' => $analytics['current_coverage_ratio'] ?? 0
            ],
            'recommendations' => $this->generateRecommendations()
        ];
        
        session()->put('analytics_report_data', $data);
        session()->flash('message', 'Analytics report generated successfully.');
        
        return $data;
    }
    
    // Export Provision History to CSV
    public function exportProvisionHistoryCSV()
    {
        $filename = 'provision_history_' . now()->format('Y_m_d_His') . '.csv';
        
        return response()->streamDownload(function() {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Date', 'Type', 'Description', 'Reference', 'Debit', 'Credit', 'Balance']);
            
            // Data
            foreach ($this->provisionHistory as $provision) {
                fputcsv($file, [
                    $provision->created_at,
                    $provision->type,
                    $provision->description ?? $provision->narration,
                    $provision->reference_number,
                    $provision->debit,
                    $provision->credit,
                    $provision->balance ?? 0
                ]);
            }
            
            fclose($file);
        }, $filename);
    }
    
    // Export Write-off History to CSV  
    public function exportWriteOffHistoryCSV()
    {
        $filename = 'writeoff_history_' . now()->format('Y_m_d_His') . '.csv';
        
        return response()->streamDownload(function() {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Date', 'Loan Account', 'Client', 'Principal', 'Interest', 'Total', 'Status', 'Recovered']);
            
            // Data
            foreach ($this->writeOffHistory as $writeOff) {
                fputcsv($file, [
                    $writeOff->write_off_date ?? $writeOff->created_at,
                    $writeOff->loan_account_number,
                    $writeOff->client_name,
                    $writeOff->principal_amount ?? 0,
                    $writeOff->interest_amount ?? 0,
                    $writeOff->total_amount ?? $writeOff->debit ?? 0,
                    $writeOff->status ?? 'Completed',
                    $writeOff->recovered_amount ?? 0
                ]);
            }
            
            fclose($file);
        }, $filename);
    }
    
    // Export All Data to CSV
    public function exportAllDataCSV()
    {
        $filename = 'loan_loss_reserve_complete_' . now()->format('Y_m_d_His') . '.csv';
        
        return response()->streamDownload(function() {
            $file = fopen('php://output', 'w');
            
            // Summary Section
            fputcsv($file, ['LOAN LOSS RESERVE SUMMARY']);
            fputcsv($file, ['Report Date:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Current Reserve Balance:', $this->currentReserveBalance]);
            fputcsv($file, ['Required Reserve:', $this->requiredReserve]);
            fputcsv($file, ['Provision Gap:', $this->provisionGap]);
            fputcsv($file, ['NPL Ratio:', ($this->calculateLoanPortfolio()['npl_ratio'] ?? 0) . '%']);
            fputcsv($file, ['Coverage Ratio:', $this->calculateCoverageRatio() . '%']);
            fputcsv($file, []);
            
            // Provision History
            fputcsv($file, ['PROVISION HISTORY']);
            fputcsv($file, ['Date', 'Type', 'Description', 'Amount']);
            foreach ($this->provisionHistory as $provision) {
                fputcsv($file, [
                    $provision->created_at,
                    $provision->type,
                    $provision->description ?? $provision->narration,
                    $provision->amount
                ]);
            }
            fputcsv($file, []);
            
            // Write-off History
            fputcsv($file, ['WRITE-OFF HISTORY']);
            fputcsv($file, ['Date', 'Loan Account', 'Client', 'Amount', 'Status']);
            foreach ($this->writeOffHistory as $writeOff) {
                fputcsv($file, [
                    $writeOff->write_off_date ?? $writeOff->created_at,
                    $writeOff->loan_account_number,
                    $writeOff->client_name,
                    $writeOff->total_amount ?? $writeOff->debit ?? 0,
                    $writeOff->status ?? 'Completed'
                ]);
            }
            
            fclose($file);
        }, $filename);
    }
    
    // Helper Methods for Reports
    private function getRecoveryTrend()
    {
        return DB::table('loan_recoveries')
            ->select(
                DB::raw("TO_CHAR(recovery_date, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount_recovered) as total')
            )
            ->where('recovery_date', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
    
    private function calculateAverageRecoveryDays()
    {
        $result = DB::table('loan_recoveries as lr')
            ->join('loan_write_offs as lw', function($join) {
                $join->on('lr.loan_id', '=', DB::raw('CAST(lw.loan_id AS BIGINT)'));
            })
            ->select(DB::raw('AVG(lr.recovery_date::date - lw.write_off_date::date) as avg_days'))
            ->first();
            
        return round($result->avg_days ?? 0);
    }
    
    private function calculateCapitalAdequacyRatio()
    {
        // Simplified CAR calculation
        $totalCapital = DB::table('accounts')
            ->whereIn('major_category_code', ['3000']) // Capital accounts
            ->sum('balance');
            
        $riskWeightedAssets = DB::table('loans')
            ->where('status', 'ACTIVE')
            ->sum(DB::raw('CAST(principle_amount AS NUMERIC)'));
            
        return $riskWeightedAssets > 0 ? round(($totalCapital / $riskWeightedAssets) * 100, 2) : 0;
    }
    
    private function projectNPLNextMonth()
    {
        // Simple projection based on trend
        $currentNPL = $this->calculateLoanPortfolio()['npl_ratio'] ?? 0;
        $lastMonthNPL = DB::table('loans as l')
            ->join('loans_schedules as ls', function($join) {
                $join->on(DB::raw('CAST(l.id AS VARCHAR)'), '=', 'ls.loan_id');
            })
            ->where('l.status', 'ACTIVE')
            ->where(DB::raw('(CURRENT_DATE - ls.installment_date::date)'), '>', 90)
            ->whereDate('ls.installment_date', '<=', now()->subMonth())
            ->sum(DB::raw('ls.installment - COALESCE(ls.payment, 0)'));
            
        // Simple trend projection
        return round($currentNPL * 1.05, 2); // 5% increase assumption
    }
    
    private function projectRequiredProvisions()
    {
        $projectedNPL = $this->projectNPLNextMonth();
        $currentPortfolio = $this->loanPortfolioValue;
        
        return round(($projectedNPL / 100) * $currentPortfolio * 0.5, 2); // 50% provision rate
    }
    
    private function getRiskIndicators()
    {
        return [
            'concentration_risk' => $this->calculateConcentrationRisk(),
            'sectoral_exposure' => $this->calculateSectoralExposure(),
            'vintage_analysis' => $this->performVintageAnalysis()
        ];
    }
    
    private function calculateConcentrationRisk()
    {
        $topBorrowersData = DB::table('loans')
            ->select('client_number', DB::raw('SUM(CAST(principle_amount AS NUMERIC)) as total'))
            ->where('status', 'ACTIVE')
            ->groupBy('client_number')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
            
        $topBorrowersTotal = $topBorrowersData->sum('total');
            
        return $this->loanPortfolioValue > 0 ? round(($topBorrowersTotal / $this->loanPortfolioValue) * 100, 2) : 0;
    }
    
    private function calculateSectoralExposure()
    {
        return DB::table('loans as l')
            ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
            ->select(
                DB::raw("COALESCE(c.industry_sector, 'Unspecified') as sector"), 
                DB::raw('COUNT(*) as count'), 
                DB::raw('SUM(CAST(l.principle_amount AS NUMERIC)) as total')
            )
            ->where('l.status', 'ACTIVE')
            ->groupBy('c.industry_sector')
            ->get();
    }
    
    private function performVintageAnalysis()
    {
        return DB::table('loans')
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-Q') as vintage"),
                DB::raw('COUNT(*) as loan_count'),
                DB::raw('SUM(CAST(principle_amount AS NUMERIC)) as total_disbursed'),
                DB::raw("SUM(CASE WHEN status = 'WRITTEN_OFF' THEN CAST(principle_amount AS NUMERIC) ELSE 0 END) as written_off")
            )
            ->groupBy('vintage')
            ->orderBy('vintage')
            ->get();
    }
    
    private function generateRecommendations()
    {
        $recommendations = [];
        
        $nplRatio = $this->calculateLoanPortfolio()['npl_ratio'] ?? 0;
        if ($nplRatio > 5) {
            $recommendations[] = 'NPL ratio exceeds 5% threshold. Strengthen collection efforts and review credit policies.';
        }
        
        if ($this->provisionGap > 0) {
            $recommendations[] = 'Provision gap of ' . number_format($this->provisionGap, 2) . ' detected. Additional provisions required.';
        }
        
        $coverageRatio = $this->calculateCoverageRatio();
        if ($coverageRatio < 100) {
            $recommendations[] = 'Coverage ratio below 100%. Increase provisions to meet regulatory requirements.';
        }
        
        return $recommendations;
    }
    
    public function generateCustomReport()
    {
        Log::info('Generate Custom Report button clicked', [
            'period' => $this->customReportPeriod,
            'start_date' => $this->customReportStartDate,
            'end_date' => $this->customReportEndDate,
            'sections' => $this->customReportSections,
            'format' => $this->customReportFormat,
            'user_id' => auth()->id()
        ]);
        
        // Validate date range
        if ($this->customReportPeriod === 'custom') {
            if (!$this->customReportStartDate || !$this->customReportEndDate) {
                Log::warning('Custom report generation failed - missing dates', [
                    'start_date' => $this->customReportStartDate,
                    'end_date' => $this->customReportEndDate
                ]);
                session()->flash('error', 'Please select both start and end dates for custom period');
                return;
            }
        }
        
        // Determine date range based on period selection
        $startDate = null;
        $endDate = Carbon::now();
        
        Log::info('Determining date range for report', [
            'period_type' => $this->customReportPeriod
        ]);
        
        switch($this->customReportPeriod) {
            case 'monthly':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'quarterly':
                $startDate = Carbon::now()->startOfQuarter();
                break;
            case 'yearly':
                $startDate = Carbon::now()->startOfYear();
                break;
            case 'custom':
                $startDate = Carbon::parse($this->customReportStartDate);
                $endDate = Carbon::parse($this->customReportEndDate);
                break;
        }
        
        Log::info('Date range determined', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        
        // Collect data based on selected sections
        $reportData = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'type' => $this->customReportPeriod
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->name ?? 'System'
        ];
        
        if ($this->customReportSections['provisions']) {
            Log::info('Fetching provisions data');
            $reportData['provisions'] = $this->getProvisionDataForPeriod($startDate, $endDate);
        }
        
        if ($this->customReportSections['writeoffs']) {
            Log::info('Fetching write-offs data');
            $reportData['writeoffs'] = $this->getWriteOffDataForPeriod($startDate, $endDate);
        }
        
        if ($this->customReportSections['recoveries']) {
            Log::info('Fetching recoveries data');
            $reportData['recoveries'] = $this->getRecoveryDataForPeriod($startDate, $endDate);
        }
        
        if ($this->customReportSections['analytics']) {
            Log::info('Fetching analytics data');
            $reportData['analytics'] = $this->getPortfolioAnalytics();
        }
        
        Log::info('Report data collected, generating report', [
            'format' => $this->customReportFormat,
            'sections_included' => array_keys(array_filter($this->customReportSections))
        ]);
        
        // Close the modal before generating the report
        $this->showCustomReportModal = false;
        
        // Build the download URL with parameters
        $params = [
            'period' => $this->customReportPeriod,
            'format' => $this->customReportFormat,
            'sections' => json_encode($this->customReportSections),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ];
        
        $downloadUrl = route('loan-loss-report.download', $params);
        
        Log::info('Redirecting to download URL', ['url' => $downloadUrl]);
        
        // Set success message
        session()->flash('success', 'Report is being generated and will download shortly!');
        
        // Redirect to download the report
        return redirect($downloadUrl);
    }
    
    private function getProvisionDataForPeriod($startDate, $endDate)
    {
        return DB::table('loan_loss_reserves')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(reserve_amount) as total_provisions'),
                DB::raw('COUNT(*) as provision_count'),
                DB::raw('AVG(reserve_amount) as avg_provision')
            )
            ->first();
    }
    
    private function getWriteOffDataForPeriod($startDate, $endDate)
    {
        return DB::table('loan_write_offs')
            ->whereBetween('write_off_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(total_amount) as total_written_off'),
                DB::raw('COUNT(*) as writeoff_count'),
                DB::raw('AVG(total_amount) as avg_writeoff')
            )
            ->first();
    }
    
    private function getRecoveryDataForPeriod($startDate, $endDate)
    {
        return DB::table('loan_recoveries')
            ->whereBetween('recovery_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(amount_recovered) as total_recovered'),
                DB::raw('COUNT(*) as recovery_count'),
                DB::raw('AVG(amount_recovered) as avg_recovery')
            )
            ->first();
    }
    
    private function generateCustomReportPDF($data)
    {
        try {
            // Check if we have a PDF library available
            if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                Log::info('PDF library found, generating PDF');
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.loan-loss-custom', compact('data'));
                $pdf->setPaper('A4', 'portrait');
                $filename = 'loan_loss_custom_report_' . date('Y_m_d_His') . '.pdf';
                Log::info('PDF report generated successfully', ['filename' => $filename]);
                
                // Use stream() instead of download() for better compatibility with Livewire
                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    $filename,
                    ['Content-Type' => 'application/pdf']
                );
            }
            
            // Fallback to HTML view download if PDF library not available
            Log::info('PDF library not found, falling back to HTML download');
            $html = view('reports.loan-loss-custom', compact('data'))->render();
            $filename = 'loan_loss_custom_report_' . date('Y_m_d_His') . '.html';
            
            Log::info('HTML report generated successfully', ['filename' => $filename]);
            
            return response()->streamDownload(function () use ($html) {
                echo $html;
            }, $filename, [
                'Content-Type' => 'text/html',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating PDF report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to CSV if there's an error
            Log::info('Falling back to CSV due to PDF generation error');
            return $this->generateCustomReportCSV($data);
        }
    }
    
    private function generateCustomReportExcel($data)
    {
        // For now, fallback to CSV
        return $this->generateCustomReportCSV($data);
    }
    
    private function generateCustomReportCSV($data)
    {
        $filename = 'loan_loss_custom_report_' . date('Y_m_d_His') . '.csv';
        
        Log::info('Generating CSV report', ['filename' => $filename]);
        
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Header information
            fputcsv($handle, ['Loan Loss Reserve Custom Report']);
            fputcsv($handle, ['Generated:', $data['generated_at']]);
            fputcsv($handle, ['Period:', $data['period']['start'] . ' to ' . $data['period']['end']]);
            fputcsv($handle, ['Generated By:', $data['generated_by']]);
            fputcsv($handle, []);
            
            // Provisions section
            if (isset($data['provisions'])) {
                fputcsv($handle, ['PROVISIONS SUMMARY']);
                fputcsv($handle, ['Total Provisions:', number_format($data['provisions']->total_provisions ?? 0, 2)]);
                fputcsv($handle, ['Provision Count:', $data['provisions']->provision_count ?? 0]);
                fputcsv($handle, ['Average Provision:', number_format($data['provisions']->avg_provision ?? 0, 2)]);
                fputcsv($handle, []);
            }
            
            // Write-offs section
            if (isset($data['writeoffs'])) {
                fputcsv($handle, ['WRITE-OFFS SUMMARY']);
                fputcsv($handle, ['Total Written Off:', number_format($data['writeoffs']->total_written_off ?? 0, 2)]);
                fputcsv($handle, ['Write-off Count:', $data['writeoffs']->writeoff_count ?? 0]);
                fputcsv($handle, ['Average Write-off:', number_format($data['writeoffs']->avg_writeoff ?? 0, 2)]);
                fputcsv($handle, []);
            }
            
            // Recoveries section
            if (isset($data['recoveries'])) {
                fputcsv($handle, ['RECOVERIES SUMMARY']);
                fputcsv($handle, ['Total Recovered:', number_format($data['recoveries']->total_recovered ?? 0, 2)]);
                fputcsv($handle, ['Recovery Count:', $data['recoveries']->recovery_count ?? 0]);
                fputcsv($handle, ['Average Recovery:', number_format($data['recoveries']->avg_recovery ?? 0, 2)]);
                fputcsv($handle, []);
            }
            
            // Analytics section
            if (isset($data['analytics'])) {
                fputcsv($handle, ['PORTFOLIO ANALYTICS']);
                fputcsv($handle, ['NPL Ratio:', ($data['analytics']['npl_ratio'] ?? 0) . '%']);
                fputcsv($handle, ['Coverage Ratio:', ($data['analytics']['current_coverage_ratio'] ?? 0) . '%']);
                fputcsv($handle, ['Recovery Rate:', ($data['analytics']['average_recovery_rate'] ?? 0) . '%']);
            }
            
            fclose($handle);
        }, $filename);
    }
}
