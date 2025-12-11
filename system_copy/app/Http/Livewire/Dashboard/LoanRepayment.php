<?php

namespace App\Http\Livewire\Dashboard;

use App\Services\LoanRepaymentService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Loan Repayment Livewire Component
 * 
 * Handles the UI logic for loan repayments including:
 * - Loan search and selection
 * - Payment amount validation
 * - Payment method selection
 * - Receipt generation and printing
 * 
 * @package App\Http\Livewire\Dashboard
 * @version 2.0
 */
class LoanRepayment extends Component
{
    // Search and Selection
    public $searchType = 'loan_id'; // loan_id, account_number, member_number
    public $searchValue = '';
    public $selectedLoanId = null;  // Store only the loan ID, not the object
    public $loanDetails = [];
    public $outstandingBalance = [];
    public $memberDetails = [];
    public $availableLoans = [];  // Store multiple loans for selection
    public $showLoanSelection = false;  // Show loan selection when multiple loans found
    
    // Payment Details
    public $paymentAmount = '';
    public $paymentMethod = 'CASH';
    public $referenceNumber = '';
    public $bankName = '';
    public $mobileProvider = '';
    public $mobileNumber = '';
    public $sourceAccount = '';
    public $narration = '';
    
    // Payment Allocation Preview
    public $allocationPreview = [];
    public $showAllocationPreview = false;
    
    // Payment History
    public $paymentHistory = [];
    public $showPaymentHistory = false;
    
    // Repayment Schedule
    public $repaymentSchedule = [];
    public $showSchedule = false;
    
    // Receipt
    public $receipt = [];
    public $showReceipt = false;
    
    // UI State
    public $isProcessing = false;
    public $currentStep = 1; // 1: Search, 2: Payment Details, 3: Confirmation, 4: Receipt
    
    // Available Banks and MNO Providers
    public $banks = [];
    public $mnoProviders = [
        'MPESA' => 'M-Pesa',
        'TIGOPESA' => 'Tigo Pesa',
        'AIRTELMONEY' => 'Airtel Money',
        'HALOPESA' => 'Halo Pesa'
    ];
    
    // Validation Rules
    protected $rules = [
        'searchValue' => 'required|string',
        'paymentAmount' => 'required|numeric|min:100',
        'paymentMethod' => 'required|in:CASH,BANK,MOBILE,INTERNAL',
        'referenceNumber' => 'required_if:paymentMethod,BANK',
        'bankName' => 'required_if:paymentMethod,BANK',
        'mobileProvider' => 'required_if:paymentMethod,MOBILE',
        'mobileNumber' => 'required_if:paymentMethod,MOBILE|regex:/^(255|0)[0-9]{9}$/',
        'sourceAccount' => 'required_if:paymentMethod,INTERNAL'
    ];
    
    protected $messages = [
        'searchValue.required' => 'Please enter a loan ID, account number, or member number',
        'paymentAmount.required' => 'Please enter the payment amount',
        'paymentAmount.numeric' => 'Payment amount must be a number',
        'paymentAmount.min' => 'Minimum payment amount is TZS 100',
        'referenceNumber.required_if' => 'Reference number is required for bank transfers',
        'bankName.required_if' => 'Please select a bank',
        'mobileProvider.required_if' => 'Please select mobile money provider',
        'mobileNumber.required_if' => 'Mobile number is required for mobile money payments',
        'mobileNumber.regex' => 'Invalid mobile number format',
        'sourceAccount.required_if' => 'Please select source account for internal transfer'
    ];
    
    private $repaymentService;
    
    public function boot()
    {
        $this->repaymentService = app(LoanRepaymentService::class);
    }
    
    public function mount()
    {
        $this->loadBanks();
    }
    
    /**
     * Reload banks when payment method changes to BANK
     */
    public function updatedPaymentMethod($value)
    {
        if ($value === 'BANK') {
            $this->loadBanks();
        }
    }
    
    /**
     * Search for loan
     */
    public function searchLoan()
    {
        $this->validate([
            'searchValue' => 'required|string'
        ]);
        
        try {
            // Log search attempt
            Log::info('🔍 LOAN SEARCH INITIATED', [
                'search_type' => $this->searchType,
                'search_value' => $this->searchValue,
                'user' => auth()->user()->name ?? 'Unknown',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            $query = DB::table('loans')
                ->join('clients', 'loans.client_number', '=', 'clients.client_number')
                ->select(
                    'loans.*',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'clients.phone_number',
                    'clients.email'
                );
            
            // Apply search filters
            switch ($this->searchType) {
                case 'loan_id':
                    $query->where('loans.loan_id', $this->searchValue)
                          ->whereIn('loans.status', ['ACTIVE', 'RESTRUCTURED']);
                    break;
                case 'account_number':
                    $query->where('loans.loan_account_number', $this->searchValue)
                          ->whereIn('loans.status', ['ACTIVE', 'RESTRUCTURED']);
                    break;
                case 'member_number':
                    // Find ALL ACTIVE loans for the member
                    $query->where('loans.client_number', $this->searchValue)
                          ->whereIn('loans.status', ['ACTIVE', 'RESTRUCTURED']);
                    break;
            }
            
            $loans = $query->get();
            
            if ($loans->isEmpty()) {
                // Check if member exists
                if ($this->searchType == 'member_number') {
                    $memberExists = DB::table('clients')->where('client_number', $this->searchValue)->exists();
                    if ($memberExists) {
                        session()->flash('error', 'Member found but has no active loans.');
                    } else {
                        session()->flash('error', 'Member not found with number: ' . $this->searchValue);
                    }
                } else {
                    session()->flash('error', 'No active loan found with the provided ' . str_replace('_', ' ', $this->searchType) . '.');
                }
                return;
            }
            
            // If only one loan found, proceed directly
            if ($loans->count() === 1) {
                $loan = $loans->first();
                $this->selectedLoanId = $loan->loan_id;
                $this->loadLoanDetails($loan);
                $this->currentStep = 2;
                
                Log::info('✅ Single loan found and selected', [
                    'loan_id' => $loan->loan_id,
                    'status' => $loan->status,
                    'client' => $loan->client_number,
                    'principal' => $loan->principle
                ]);
            } else {
                // Multiple loans found - show selection
                $this->availableLoans = $loans->map(function ($loan) {
                    // Get product details for each loan
                    $product = DB::table('loan_sub_products')
                        ->where('sub_product_id', $loan->loan_sub_product)
                        ->first();
                    
                    // Calculate outstanding balance for each loan
                    $outstanding = $this->repaymentService->calculateOutstandingBalances($loan);
                    
                    return [
                        'loan_id' => $loan->loan_id,
                        'account_number' => $loan->loan_account_number,
                        'product_name' => $product->sub_product_name ?? 'N/A',
                        'principal' => $loan->principle,
                        'status' => $loan->status,
                        'disbursement_date' => $loan->disbursement_date,
                        'outstanding_balance' => $outstanding['total'],
                        'days_in_arrears' => $loan->days_in_arrears ?? 0,
                        'member_name' => trim("{$loan->first_name} {$loan->middle_name} {$loan->last_name}"),
                        'member_number' => $loan->client_number
                    ];
                })->toArray();
                
                $this->showLoanSelection = true;
                
                Log::info('📋 Multiple loans found for selection', [
                    'count' => $loans->count(),
                    'member' => $this->searchValue,
                    'loan_ids' => $loans->pluck('loan_id')->toArray()
                ]);
            }
            
        } catch (Exception $e) {
            Log::error('❌ Loan search failed', [
                'search_type' => $this->searchType,
                'search_value' => $this->searchValue,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
            session()->flash('error', 'Error searching for loan: ' . $e->getMessage());
        }
    }
    
    /**
     * Select a loan from multiple available loans
     */
    public function selectLoan($loanId)
    {
        try {
            // Fetch the selected loan
            $loan = DB::table('loans')
                ->join('clients', 'loans.client_number', '=', 'clients.client_number')
                ->select(
                    'loans.*',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'clients.phone_number',
                    'clients.email'
                )
                ->where('loans.loan_id', $loanId)
                ->first();
            
            if (!$loan) {
                session()->flash('error', 'Selected loan not found.');
                return;
            }
            
            $this->selectedLoanId = $loan->loan_id;
            $this->loadLoanDetails($loan);
            $this->showLoanSelection = false;
            $this->currentStep = 2;
            
            Log::info('👆 Loan selected from multiple options', [
                'loan_id' => $loan->loan_id,
                'client' => $loan->client_number,
                'principal' => $loan->principle,
                'status' => $loan->status
            ]);
            
        } catch (Exception $e) {
            Log::error('Error selecting loan', [
                'loan_id' => $loanId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error selecting loan. Please try again.');
        }
    }
    
    /**
     * Load loan details
     */
    private function loadLoanDetails($loan = null)
    {
        // If loan not passed, fetch it using selectedLoanId
        if (!$loan && $this->selectedLoanId) {
            $loan = DB::table('loans')
                ->join('clients', 'loans.client_number', '=', 'clients.client_number')
                ->select(
                    'loans.*',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'clients.phone_number',
                    'clients.email'
                )
                ->where('loans.loan_id', $this->selectedLoanId)
                ->first();
        }
        
        if (!$loan) {
            return;
        }
        
        // Get loan product details
        $product = DB::table('loan_sub_products')
            ->where('sub_product_id', $loan->loan_sub_product)
            ->first();
        
        // Calculate outstanding balance
        $this->outstandingBalance = $this->repaymentService->calculateOutstandingBalances($loan);
        
        // Get payment history
        $this->paymentHistory = $this->repaymentService->getPaymentHistory($loan->loan_id, 5);
        
        // Get repayment schedule
        $this->repaymentSchedule = DB::table('loans_schedules')
            ->where('loan_id', $loan->loan_id)
            ->orderBy('installment_date', 'asc')
            ->get();
        
        // Set member details
        $this->memberDetails = [
            'name' => trim("{$loan->first_name} {$loan->middle_name} {$loan->last_name}"),
            'member_number' => $loan->client_number,
            'phone' => $loan->phone_number,
            'email' => $loan->email
        ];
        
        // Set loan details
        $this->loanDetails = [
            'loan_id' => $loan->loan_id,
            'account_number' => $loan->loan_account_number,
            'product_name' => $product->sub_product_name ?? 'N/A',
            'principal' => $loan->principle,
            'interest_rate' => $product->interest_value ?? 0,
            'tenure' => $loan->tenure,
            'disbursement_date' => $loan->disbursement_date,
            'status' => $loan->status
        ];
    }
    
    /**
     * Preview payment allocation
     */
    public function previewAllocation()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:100'
        ]);
        
        if (!$this->selectedLoanId) {
            session()->flash('error', 'Please select a loan first');
            return;
        }
        
        Log::info('📊 Payment allocation preview requested', [
            'loan_id' => $this->selectedLoanId,
            'amount' => $this->paymentAmount
        ]);
        
        // Calculate allocation preview
        $this->allocationPreview = $this->calculateAllocationPreview($this->paymentAmount);
        $this->showAllocationPreview = true;
        
        Log::info('📋 Allocation preview calculated', [
            'loan_id' => $this->selectedLoanId,
            'allocation' => $this->allocationPreview
        ]);
    }
    
    /**
     * Calculate allocation preview
     */
    private function calculateAllocationPreview($amount)
    {
        $remaining = $amount;
        $allocation = [
            'penalties' => 0,
            'interest' => 0,
            'principal' => 0,
            'overpayment' => 0
        ];
        
        // Allocate to penalties first
        if ($this->outstandingBalance['penalties'] > 0) {
            $allocation['penalties'] = min($remaining, $this->outstandingBalance['penalties']);
            $remaining -= $allocation['penalties'];
        }
        
        // Then interest
        if ($remaining > 0 && $this->outstandingBalance['interest'] > 0) {
            $allocation['interest'] = min($remaining, $this->outstandingBalance['interest']);
            $remaining -= $allocation['interest'];
        }
        
        // Then principal
        if ($remaining > 0 && $this->outstandingBalance['principal'] > 0) {
            $allocation['principal'] = min($remaining, $this->outstandingBalance['principal']);
            $remaining -= $allocation['principal'];
        }
        
        // Any remainder is overpayment
        if ($remaining > 0) {
            $allocation['overpayment'] = $remaining;
        }
        
        return $allocation;
    }
    
    /**
     * Process repayment
     */
    public function processRepayment()
    {
        $this->validate();
        
        if (!$this->selectedLoanId) {
            Log::warning('⚠️ Repayment attempted without loan selection');
            session()->flash('error', 'Please select a loan first');
            return;
        }
        
        Log::info('💳 PAYMENT PROCESSING STARTED', [
            'loan_id' => $this->selectedLoanId,
            'amount' => $this->paymentAmount,
            'payment_method' => $this->paymentMethod,
            'user' => auth()->user()->name ?? 'Unknown',
            'timestamp' => now()->toDateTimeString()
        ]);
        
        $this->isProcessing = true;
        
        try {
            // Prepare payment details
            $paymentDetails = $this->preparePaymentDetails();
            
            // Process repayment
            $result = $this->repaymentService->processRepayment(
                $this->selectedLoanId,
                $this->paymentAmount,
                $this->paymentMethod,
                $paymentDetails
            );
            
            // Set receipt
            $this->receipt = $result['receipt'];
            $this->showReceipt = true;
            $this->currentStep = 4;
            
            // Reload loan details
            $this->loadLoanDetails();
            
            // Flash success message
            session()->flash('success', 'Payment processed successfully! Receipt: ' . $result['receipt_number']);
            
            Log::info('🎉 PAYMENT PROCESSED VIA UI', [
                'loan_id' => $this->selectedLoanId,
                'receipt' => $result['receipt_number'],
                'amount' => $result['amount_paid'],
                'new_outstanding' => $result['outstanding_balance']['total'] ?? 0
            ]);
            
            // Reset payment fields
            $this->resetPaymentFields();
            
        } catch (Exception $e) {
            Log::error('❌ PAYMENT PROCESSING FAILED', [
                'loan_id' => $this->selectedLoanId,
                'amount' => $this->paymentAmount,
                'payment_method' => $this->paymentMethod,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
                'user' => auth()->user()->name ?? 'Unknown'
            ]);
            
            session()->flash('error', 'Error processing payment: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    
    /**
     * Prepare payment details based on payment method
     */
    private function preparePaymentDetails()
    {
        $details = [
            'narration' => $this->narration ?: "Loan repayment for {$this->selectedLoanId}"
        ];
        
        switch ($this->paymentMethod) {
            case 'BANK':
                $details['reference'] = $this->referenceNumber;
                
                // Get bank details from bank_accounts table
                if ($this->bankName && $this->bankName != '0') {
                    $bankAccount = DB::table('bank_accounts')
                        ->where('id', $this->bankName)
                        ->first();
                    
                    if ($bankAccount) {
                        $details['bank_name'] = $bankAccount->bank_name;
                        $details['bank_account'] = $bankAccount->account_number;
                        $details['bank_account_id'] = $bankAccount->id;
                        $details['bank_account_name'] = $bankAccount->account_name;
                    } else {
                        // Fallback if bank account not found
                        $details['bank_name'] = $this->bankName;
                        $details['bank_account'] = $this->bankName;
                    }
                } else {
                    $details['bank_name'] = 'Not specified';
                    $details['bank_account'] = 'Not specified';
                }
                break;
                
            case 'MOBILE':
                $details['reference'] = 'MNO_' . time();
                $details['mobile_provider'] = $this->mobileProvider;
                $details['mobile_number'] = $this->mobileNumber;
                $details['mobile_account'] = $this->mobileProvider . '_ACCOUNT';
                break;
                
            case 'INTERNAL':
                $details['source_account'] = $this->sourceAccount;
                $details['reference'] = 'INT_' . time();
                break;
                
            default: // CASH
                $details['reference'] = 'CASH_' . time();
                break;
        }
        
        return $details;
    }
    
    /**
     * Calculate early settlement
     */
    public function calculateEarlySettlement()
    {
        if (!$this->selectedLoanId) {
            return;
        }
        
        Log::info('🏦 Early settlement calculation requested', [
            'loan_id' => $this->selectedLoanId,
            'user' => auth()->user()->name ?? 'Unknown'
        ]);
        
        try {
            $settlement = $this->repaymentService->calculateEarlySettlement($this->selectedLoanId);
            
            // Set payment amount to settlement amount
            $this->paymentAmount = $settlement['total_settlement'];
            
            // Show settlement details
            session()->flash('info', sprintf(
                'Early Settlement: Principal: %s, Interest: %s, Waiver: %s, Total: %s',
                number_format($settlement['principal'], 2),
                number_format($settlement['interest'], 2),
                number_format($settlement['waiver'], 2),
                number_format($settlement['total_settlement'], 2)
            ));
            
        } catch (Exception $e) {
            Log::error('❌ Early settlement calculation failed', [
                'loan_id' => $this->selectedLoanId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error calculating early settlement: ' . $e->getMessage());
        }
    }
    
    /**
     * Print receipt
     */
    public function printReceipt()
    {
        if (!$this->receipt) {
            return;
        }
        
        $this->dispatchBrowserEvent('print-receipt', ['receipt' => $this->receipt]);
    }
    
    /**
     * Download receipt as PDF
     */
    public function downloadReceipt()
    {
        if (!$this->receipt) {
            return;
        }
        
        // Generate PDF (implementation depends on PDF library)
        // For now, just dispatch event for JS handling
        $this->dispatchBrowserEvent('download-receipt', ['receipt' => $this->receipt]);
    }
    
    /**
     * Reset form for new payment
     */
    public function newPayment()
    {
        $this->reset([
            'searchValue',
            'selectedLoanId',
            'loanDetails',
            'outstandingBalance',
            'memberDetails',
            'availableLoans',
            'showLoanSelection',
            'paymentAmount',
            'paymentMethod',
            'referenceNumber',
            'bankName',
            'mobileProvider',
            'mobileNumber',
            'sourceAccount',
            'narration',
            'allocationPreview',
            'showAllocationPreview',
            'receipt',
            'showReceipt'
        ]);
        
        $this->currentStep = 1;
    }
    
    /**
     * Reset payment fields only
     */
    private function resetPaymentFields()
    {
        $this->reset([
            'paymentAmount',
            'referenceNumber',
            'bankName',
            'mobileProvider',
            'mobileNumber',
            'sourceAccount',
            'narration',
            'allocationPreview',
            'showAllocationPreview'
        ]);
    }
    
    /**
     * Load banks for dropdown from bank_accounts table
     */
    private function loadBanks()
    {
        try {
            // Fetch banks from bank_accounts table
            $bankAccounts = DB::table('bank_accounts')
                ->select('id', 'bank_name', 'account_name', 'account_number')
                ->where('status', 'ACTIVE')
                ->orderBy('bank_name')
                ->get();
            
            // Format for dropdown display
            $this->banks = [];
            foreach ($bankAccounts as $bank) {
                // Use account ID as value and combine bank name with account info as display
                $displayName = $bank->bank_name;
                if ($bank->account_name) {
                    $displayName .= ' - ' . $bank->account_name;
                }
                if ($bank->account_number) {
                    $displayName .= ' (' . substr($bank->account_number, -4) . ')';
                }
                $this->banks[$bank->id] = $displayName;
            }
            
            // If no banks found, add a default message
            if (empty($this->banks)) {
                $this->banks = ['0' => 'No active bank accounts configured'];
            }
            
        } catch (\Exception $e) {
            Log::error('Error loading banks', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback to common banks if table doesn't exist or error occurs
            $this->banks = [
                'CRDB' => 'CRDB Bank',
                'NMB' => 'NMB Bank',
                'NBC' => 'NBC Bank',
                'STANBIC' => 'Stanbic Bank',
                'EQUITY' => 'Equity Bank',
                'AZANIA' => 'Azania Bank',
                'DTB' => 'Diamond Trust Bank',
                'KCB' => 'KCB Bank',
                'ABSA' => 'ABSA Bank'
            ];
        }
    }
    
    /**
     * Toggle payment history
     */
    public function togglePaymentHistory()
    {
        $this->showPaymentHistory = !$this->showPaymentHistory;
    }
    
    /**
     * Toggle repayment schedule
     */
    public function toggleSchedule()
    {
        $this->showSchedule = !$this->showSchedule;
    }
    
    /**
     * Go to previous step
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    /**
     * Go to next step
     */
    public function nextStep()
    {
        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }
    
    public function render()
    {
        return view('livewire.dashboard.loan-repayment');
    }
}