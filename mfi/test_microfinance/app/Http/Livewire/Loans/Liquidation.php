<?php

namespace App\Http\Livewire\Loans;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Loan_sub_products;
use App\Services\TransactionPostingService;
use App\Services\LoanRepaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;

class Liquidation extends Component
{
    use WithPagination;

    // Search and Filters
    public $searchType = 'loan_id';
    public $searchValue = '';
    public $branchFilter = '';
    public $productFilter = '';
    public $statusFilter = 'ACTIVE';
    
    // Pagination
    public $perPage = 20;
    
    // Selected Loan
    public $selectedLoan = null;
    public $showLiquidationModal = false;
    
    // Liquidation Details
    public $liquidationAmount = 0;
    public $liquidationPenalty = 0;
    public $totalLiquidationAmount = 0;
    public $paymentMethod = 'SAVINGS';
    public $sourceAccountNumber = '';
    public $liquidationReason = '';
    public $liquidationNotes = '';

    // Early Settlement
    public $earlySettlementAmount = 0;
    public $penaltyWaived = false;
    public $waiverReason = '';

    // Client Accounts
    public $clientAccounts = [];
    
    protected $listeners = [
        'refreshLiquidationTable' => '$refresh',
        'liquidateLoan' => 'openLiquidationModal'
    ];

    protected $rules = [
        'liquidationAmount' => 'required|numeric|min:0',
        'sourceAccountNumber' => 'required|string',
        'liquidationReason' => 'required|string|max:500'
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Load any initial statistics if needed
    }

    public function updatedSearchValue()
    {
        $this->resetPage();
    }

    public function searchLoan()
    {
        $this->resetPage();
    }

    public function openLiquidationModal($loanId)
    {
        try {
            // Fetch loan details with client information
            $this->selectedLoan = LoansModel::with(['client'])
                ->where('loan_id', $loanId)
                ->where('status', 'ACTIVE')
                ->first();

            if (!$this->selectedLoan) {
                session()->flash('error', 'Loan not found or not active.');
                return;
            }

            // Get product information
            if ($this->selectedLoan->loan_sub_product) {
                $product = Loan_sub_products::where('sub_product_id', $this->selectedLoan->loan_sub_product)->first();
                $this->selectedLoan->product_name = $product ? $product->sub_product_name : 'N/A';
            } else {
                $this->selectedLoan->product_name = 'N/A';
            }

            // Reset user input fields BEFORE calculation
            $this->resetUserInputFields();

            // Calculate outstanding balance
            $this->calculateOutstandingBalance();

            // Get client savings accounts
            $this->clientAccounts = DB::table('accounts')
                ->where('client_number', $this->selectedLoan->client_number)
                ->where('product_number', '2000')
                ->where('status', 'ACTIVE')
                ->select('account_number', 'account_name', 'balance')
                ->get()
                ->toArray();

            $this->showLiquidationModal = true;
            
        } catch (Exception $e) {
            Log::error('Error opening liquidation modal', [
                'loan_id' => $loanId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error loading loan details: ' . $e->getMessage());
        }
    }

    private function calculateOutstandingBalance()
    {
        if (!$this->selectedLoan) {
            return;
        }

        try {
            // Get loan account balance from accounts table (MOST RELIABLE SOURCE)
            $loanAccount = DB::table('accounts')
                ->where('account_number', $this->selectedLoan->loan_account_number)
                ->first();

            if (!$loanAccount) {
                Log::error('Loan account not found in accounts table', [
                    'loan_id' => $this->selectedLoan->loan_id,
                    'account_number' => $this->selectedLoan->loan_account_number
                ]);
                session()->flash('error', 'Loan account not found. Cannot proceed with liquidation.');
                $this->liquidationAmount = 0;
                $this->liquidationPenalty = 0;
                $this->totalLiquidationAmount = 0;
                return;
            }

            // Outstanding balance is the actual account balance (use absolute value as loan balances can be negative)
            $outstandingBalance = abs($loanAccount->balance ?? 0);

            // Get loan repayment schedule
            $schedule = DB::table('loans_schedules')
                ->where('loan_id', $this->selectedLoan->loan_id)
                ->get();

            // Calculate total expected and total paid
            $totalExpected = 0;
            $totalPaid = 0;
            $remainingMonths = 0;

            if ($schedule->isNotEmpty()) {
                $totalExpected = $schedule->sum('installment');
                $remainingMonths = $schedule->where('completion_status', '!=', 'PAID')->count();
            } else {
                // If no schedule exists, use principal + interest calculation
                $tenure = $this->selectedLoan->tenure ?? 12;
                $interestRate = ($this->selectedLoan->interest_rate ?? 0) / 100;
                $totalInterest = $this->selectedLoan->principle * $interestRate * ($tenure / 12);
                $totalExpected = $this->selectedLoan->principle + $totalInterest;
                $remainingMonths = $tenure;

                Log::warning('Loan has no repayment schedule, using calculated values', [
                    'loan_id' => $this->selectedLoan->loan_id,
                    'calculated_total' => $totalExpected
                ]);
            }

            // Calculate total paid from loan_repayments table
            $totalPaid = DB::table('loan_repayments')
                ->where('loan_id', $this->selectedLoan->loan_id)
                ->where('status', 'COMPLETED')
                ->sum('amount');

            // If no repayments found, calculate from difference between principal and outstanding
            if ($totalPaid == 0 && $outstandingBalance < $totalExpected) {
                $totalPaid = $totalExpected - $outstandingBalance;
            }

            // Calculate early settlement if applicable
            $monthlyInterestRate = ($this->selectedLoan->interest_rate ?? 0) / 100 / 12;

            // Early settlement calculation (principal + current month interest only)
            $outstandingPrincipal = max(0, $this->selectedLoan->principle - ($totalPaid - ($totalExpected - $this->selectedLoan->principle)));
            $currentMonthInterest = $outstandingPrincipal * $monthlyInterestRate;

            $this->earlySettlementAmount = $outstandingPrincipal + $currentMonthInterest;

            // Calculate 5% liquidation penalty - ONLY if liquidating before 1/3 of tenure
            $this->liquidationAmount = $outstandingBalance;

            // Check if loan has zero balance
            if ($outstandingBalance <= 0) {
                Log::warning('Loan has zero or negative outstanding balance', [
                    'loan_id' => $this->selectedLoan->loan_id,
                    'account_balance' => $loanAccount->balance
                ]);
                session()->flash('warning', 'This loan has a zero outstanding balance. It may already be fully paid. Please verify before proceeding.');
            }

            // Check if loan is being liquidated before 1/3 of its tenure
            $disbursementDate = \Carbon\Carbon::parse($this->selectedLoan->disbursement_date);
            $currentDate = \Carbon\Carbon::now();
            $monthsElapsed = max(0, $disbursementDate->diffInMonths($currentDate));
            $tenure = $this->selectedLoan->tenure ?? 12; // Loan tenure in months
            $oneThirdTenure = $tenure / 3;

            if ($monthsElapsed < $oneThirdTenure && $outstandingBalance > 0) {
                // Loan is being liquidated before 1/3 of tenure - apply 5% penalty
                $this->liquidationPenalty = $outstandingBalance * 0.05;
                Log::info('Liquidation penalty applied - Early liquidation', [
                    'loan_id' => $this->selectedLoan->loan_id,
                    'tenure' => $tenure,
                    'months_elapsed' => $monthsElapsed,
                    'one_third_tenure' => $oneThirdTenure,
                    'penalty_amount' => $this->liquidationPenalty
                ]);
            } else {
                // Loan has passed 1/3 of tenure - no penalty
                $this->liquidationPenalty = 0;
                Log::info('No liquidation penalty - Loan past 1/3 tenure', [
                    'loan_id' => $this->selectedLoan->loan_id,
                    'tenure' => $tenure,
                    'months_elapsed' => $monthsElapsed,
                    'one_third_tenure' => $oneThirdTenure
                ]);
            }

            $this->totalLiquidationAmount = $this->liquidationAmount + $this->liquidationPenalty;

            // Store in selectedLoan for display
            $this->selectedLoan->outstanding_balance = $outstandingBalance;
            $this->selectedLoan->total_paid = $totalPaid;
            $this->selectedLoan->total_expected = $totalExpected;
            $this->selectedLoan->outstanding_principal = $outstandingPrincipal;
            $this->selectedLoan->remaining_installments = $remainingMonths;
            $this->selectedLoan->months_elapsed = $monthsElapsed;
            $this->selectedLoan->one_third_tenure = $oneThirdTenure;
            $this->selectedLoan->account_balance = $loanAccount->balance;
            
        } catch (Exception $e) {
            Log::error('Error calculating outstanding balance', [
                'loan_id' => $this->selectedLoan->loan_id,
                'error' => $e->getMessage()
            ]);
            $this->liquidationAmount = 0;
        }
    }

    public function applyEarlySettlement()
    {
        if ($this->earlySettlementAmount > 0) {
            $this->liquidationAmount = $this->earlySettlementAmount;
            session()->flash('info', 'Early settlement amount applied. This includes outstanding principal and current month interest only.');
        }
    }

    public function processLiquidation()
    {
        $rules = [
            'sourceAccountNumber' => 'required|string',
            'liquidationReason' => 'required|string'
        ];

        // Add waiver reason validation if penalty is being waived
        if ($this->penaltyWaived) {
            $rules['waiverReason'] = 'required|string|min:10';
        }

        $this->validate($rules);

        // Calculate final amount based on waiver status
        $finalAmount = $this->penaltyWaived ? $this->liquidationAmount : $this->totalLiquidationAmount;

        if (!$this->selectedLoan) {
            session()->flash('error', 'No loan selected for liquidation.');
            return;
        }

        // Check if outstanding balance is zero or negative
        if ($this->liquidationAmount <= 0) {
            session()->flash('error', 'Cannot liquidate a loan with zero or negative outstanding balance. The loan may already be fully paid.');
            return;
        }

        if (empty($this->sourceAccountNumber)) {
            session()->flash('error', 'Please select a source account.');
            return;
        }

        try {
            // Get source account balance to verify sufficient funds
            $sourceAccount = DB::table('accounts')
                ->where('account_number', $this->sourceAccountNumber)
                ->first();

            if (!$sourceAccount) {
                session()->flash('error', 'Source account not found.');
                return;
            }

            if ($sourceAccount->balance < $finalAmount) {
                session()->flash('error', 'Insufficient funds in source account. Available: TZS ' . number_format($sourceAccount->balance, 2) . ', Required: TZS ' . number_format($finalAmount, 2));
                return;
            }

            Log::info('💰 LOAN LIQUIDATION INITIATED', [
                'loan_id' => $this->selectedLoan->loan_id,
                'client_number' => $this->selectedLoan->client_number,
                'liquidation_amount' => $this->liquidationAmount,
                'penalty_amount' => $this->liquidationPenalty,
                'total_amount' => $finalAmount,
                'penalty_waived' => $this->penaltyWaived,
                'source_account' => $this->sourceAccountNumber,
                'processed_by' => auth()->id()
            ]);

            DB::beginTransaction();

            // Add liquidation penalty to loan schedules if not waived
            if (!$this->penaltyWaived && $this->liquidationPenalty > 0) {
                // Get the first unpaid schedule to attach penalty
                $firstUnpaidSchedule = DB::table('loans_schedules')
                    ->where('loan_id', $this->selectedLoan->loan_id)
                    ->where('completion_status', '!=', 'PAID')
                    ->orderBy('installment_date', 'asc')
                    ->first();

                if ($firstUnpaidSchedule) {
                    // Add liquidation penalty to the schedule
                    DB::table('loans_schedules')
                        ->where('id', $firstUnpaidSchedule->id)
                        ->update([
                            'penalty' => DB::raw('COALESCE(penalty, 0) + ' . $this->liquidationPenalty),
                            'updated_at' => now()
                        ]);

                    Log::info('Added liquidation penalty to schedule', [
                        'loan_id' => $this->selectedLoan->loan_id,
                        'schedule_id' => $firstUnpaidSchedule->id,
                        'penalty_amount' => $this->liquidationPenalty
                    ]);
                }
            }

            // Build narration
            $noteText = 'LIQUIDATION: ' . $this->liquidationReason;
            if ($this->liquidationNotes) {
                $noteText .= ' - ' . $this->liquidationNotes;
            }
            if (!$this->penaltyWaived) {
                $noteText .= ' (Includes 5% liquidation penalty: TZS ' . number_format($this->liquidationPenalty, 2) . ')';
            } else {
                $noteText .= ' (Penalty waived - Reason: ' . $this->waiverReason . ')';
            }

            // Use LoanRepaymentService to process the repayment
            $repaymentService = new LoanRepaymentService();

            $paymentDetails = [
                'narration' => $noteText,
                'source_account' => $this->sourceAccountNumber,
                'reference' => 'LIQUIDATION_' . time(),
                'liquidation' => true,
                'penalty_amount' => $this->penaltyWaived ? 0 : $this->liquidationPenalty,
                'waiver_reason' => $this->waiverReason
            ];

            // Process the repayment using the service (which handles all accounting, schedules, etc.)
            $result = $repaymentService->processRepayment(
                $this->selectedLoan->loan_id,
                $finalAmount,
                'INTERNAL',  // Using INTERNAL payment method for savings transfer
                $paymentDetails
            );

            // Update loan status to CLOSED (loan will be closed by the repayment service if fully paid)
            // The service already handles updating the loan status based on outstanding balance

            DB::commit();

            Log::info('✅ LOAN LIQUIDATION COMPLETED', [
                'loan_id' => $this->selectedLoan->loan_id,
                'receipt_number' => $result['receipt_number'],
                'amount' => $finalAmount,
                'source_account' => $this->sourceAccountNumber,
                'penalty_included' => !$this->penaltyWaived ? $this->liquidationPenalty : 0,
                'new_outstanding' => $result['outstanding_balance']['total'] ?? 0
            ]);

            session()->flash('success', "Loan {$this->selectedLoan->loan_id} has been successfully liquidated. Receipt: {$result['receipt_number']}");

            $this->closeLiquidationModal();
            $this->emit('refreshLiquidationTable');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('❌ LOAN LIQUIDATION FAILED', [
                'loan_id' => $this->selectedLoan->loan_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Liquidation failed: ' . $e->getMessage());
        }
    }

    public function closeLiquidationModal()
    {
        $this->showLiquidationModal = false;
        $this->resetLiquidationForm();
    }

    private function resetUserInputFields()
    {
        // Only reset user input fields, NOT calculated values
        $this->paymentMethod = 'SAVINGS';
        $this->sourceAccountNumber = '';
        $this->liquidationReason = '';
        $this->liquidationNotes = '';
        $this->penaltyWaived = false;
        $this->waiverReason = '';
        $this->resetValidation();
    }

    private function resetLiquidationForm()
    {
        // Reset both calculated values and user inputs (used when closing modal)
        $this->liquidationAmount = 0;
        $this->liquidationPenalty = 0;
        $this->totalLiquidationAmount = 0;
        $this->earlySettlementAmount = 0;
        $this->resetUserInputFields();
    }

    public function getLoansProperty()
    {
        $query = LoansModel::query()
            ->with(['client'])
            ->where('status', $this->statusFilter ?: 'ACTIVE');

        // Apply search
        if (!empty($this->searchValue)) {
            $query->where(function ($q) {
                switch ($this->searchType) {
                    case 'loan_id':
                        $q->where('loan_id', 'like', '%' . $this->searchValue . '%');
                        break;
                    case 'account_number':
                        $q->where('loan_account_number', 'like', '%' . $this->searchValue . '%');
                        break;
                    case 'member_number':
                        $q->where('client_number', 'like', '%' . $this->searchValue . '%');
                        break;
                    case 'member_name':
                        $q->whereHas('client', function ($q) {
                            $q->where('first_name', 'like', '%' . $this->searchValue . '%')
                                ->orWhere('last_name', 'like', '%' . $this->searchValue . '%');
                        });
                        break;
                }
            });
        }

        // Apply filters
        if (!empty($this->branchFilter)) {
            $query->where('branch_id', $this->branchFilter);
        }

        if (!empty($this->productFilter)) {
            $query->where('loan_sub_product', $this->productFilter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function render()
    {
        $loans = $this->loans;

        // Get filter options
        $branches = DB::table('branches')->get();
        $products = Loan_sub_products::get();

        return view('livewire.loans.liquidation', [
            'loans' => $loans,
            'branches' => $branches,
            'products' => $products
        ]);
    }
}