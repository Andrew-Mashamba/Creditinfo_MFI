<?php

namespace App\Http\Livewire\Accounting;

use App\Services\TransactionPostingService;
use App\Services\TransactionProcessingService;
use App\Services\AccountCreationService;
use App\Services\BillingService;
use App\Services\PaymentLinkService;
use App\Services\SmsTemplateService;
use App\Services\SmsService;
use App\Helper\GenerateAccountNumber;
use App\Jobs\FundsTransfer;
use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\Charges;
use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\Insurances;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\loans_summary;
use App\Models\LoansModel;
use App\Models\MembersModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * LoansDisbursement Component
 * 
 * Handles the complete loan disbursement process for different loan types:
 * - New loans: Standard disbursement with deductions
 * - TopUp loans: Additional funds with old loan closure
 * - Restructuring loans: Term modifications without additional disbursement
 * 
 * Supports multiple payment methods:
 * - Cash disbursement
 * - Internal transfer (NBC accounts)
 * - TIPS MNO (Mobile money)
 * - TIPS Bank (Bank transfers)
 * 
 * Features:
 * - Comprehensive deduction calculations
 * - Multi-channel notifications (Email + SMS)
 * - Control number generation
 * - Repayment schedule creation
 * - Transaction posting and accounting
 * 
 * @package App\Http\Livewire\Accounting
 * @author NBC SACCO System
 * @version 2.0
 */
class LoansDisbursement extends Component
{
    use WithFileUploads;

    // UI State Properties
    public $tab_id;
    public $searchTerm = '';
    public $filterStatus = 'all';
    public $bank_account = '';
    
    // Loan Disbursement Properties
    public $approved_loan_value = 0;
    public $approved_term = 12;
    public $product_account;
    public $narration;
    public $selectedLoan;
    public $firstInterestAmount = 0;
    public $charges = [];
    public $insurance = [];
    public $totalCharges = 0;
    public $totalInsurance = 0;
    public $productParams = null;
    public $accountDetails = [];

    // Payment Method Properties
    public $memberNbcAccount;
    public $memberAccountHolderName;
    public $memberPhoneNumber;
    public $memberMnoProvider;
    public $memberWalletHolderName;
    public $memberBankCode;
    public $memberBankAccountNumber;
    public $memberBankAccountHolderName;
    public $selectedDepositAccount; // For CASH payment method
    public $tipsBanks = []; // For TIPS_BANK payment method
    public $validationErrors = []; // Track validation errors for UI display
    public $processingErrors = []; // Track processing errors during disbursement
    public $networkErrors = []; // Track network/API errors
    public $systemErrors = []; // Track system-level errors
    public $warningMessages = []; // Track warning messages
    public $infoMessages = []; // Track info messages
    public $successMessages = []; // Track success messages
    
    // Processing state
    public $isProcessing = false;
    
    // Control Number Generation Properties
    public $generatedControlNumbers = [];
    public $client_number;

    // Event Listeners
    protected $listeners = ['refreshLoanList' => '$refresh'];

    /**
     * Component initialization
     * Loads TIPS banks configuration on mount
     */
    public function mount()
    {
        $this->loadTipsBanks();
    }

    /**
     * Select a loan for disbursement processing
     * 
     * Loads loan data, member information, charges, insurance, and account details.
     * Validates the loan exists and sets up the disbursement environment.
     * 
     * @param int $id The loan ID to select
     * @return void
     * @throws \Exception If loan data cannot be loaded
     */
    public function loanSelected($id){
        try {
            // Validate that the loan exists
            $loan = DB::table('loans')->where('id', $id)->first();
            if (!$loan) {
                return;
            }
            
            // Set the approved loan value and client number
            $this->approved_loan_value = $loan->approved_loan_value ?? $loan->principle;
            $this->approved_term = $loan->tenure ?? 12;
            $this->client_number = $loan->client_number;
            
            // Load member data for payment method fields
            $this->loadMemberData($loan->client_number);
            
            // Load charges, insurance, and account details - use approved_loan_value for calculations
            $this->loadChargesAndInsurance($loan->loan_sub_product, $this->approved_loan_value, $loan);
            
            // Check for validation errors
            $this->checkValidationErrors();
            
            // Add a small delay to make loading state visible (simulates processing time)
            usleep(300000); // 0.3 seconds
            
            Session::put('currentloanID', $id);
            $this->tab_id = $id;
            $this->emit('loanIdSet');
            
            // Debug logging
            Log::info('Loan selected and data loaded', [
                'loan_id' => $id,
                'client_number' => $this->client_number,
                'approved_loan_value' => $this->approved_loan_value,
                'charges_count' => count($this->charges),
                'insurance_count' => count($this->insurance),
                'total_charges' => $this->totalCharges,
                'total_insurance' => $this->totalInsurance
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in loanSelected: ' . $e->getMessage());
        }
    }

    /**
     * Load member data for payment method validation
     * 
     * @param string $clientNumber The client number to load data for
     * @return void
     */
    private function loadMemberData($clientNumber)
    {
        $member = DB::table('clients')->where('client_number', $clientNumber)->first();
        if ($member) {
            $this->memberPhoneNumber = $member->phone_number;
            $this->memberAccountHolderName = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;
            $this->memberWalletHolderName = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;
            $this->memberBankAccountHolderName = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;
            $this->memberNbcAccount = $member->account_number;
            $this->memberBankAccountNumber = $member->account_number;
        }
    }

    /**
     * Load TIPS banks configuration for bank transfers
     * 
     * @return void
     */
    private function loadTipsBanks()
    {
        // This would typically call the TIPS API to get the list of banks
        // For now, using a static list based on the documentation
        $this->tipsBanks = [
            ['fspId' => '003', 'fspShortNme' => 'CRDB', 'fspFullNme' => 'CRDB BANK PLC', 'fspCode' => 'CRDBTZTZ'],
            ['fspId' => '011', 'fspShortNme' => 'DTB', 'fspFullNme' => 'DIAMOND TRUST BANK', 'fspCode' => 'DTKETZTZ'],
            ['fspId' => '039', 'fspShortNme' => 'MKOMBOZI', 'fspFullNme' => 'MKOMBOZI COMMERCIAL BANK LTD', 'fspCode' => 'MKCBTZTZ'],
            ['fspId' => '511', 'fspShortNme' => 'AZAMPESA', 'fspFullNme' => 'AZAM PESA', 'fspCode' => 'APCASHIN'],
            ['fspId' => '501', 'fspShortNme' => 'TigoPesa', 'fspFullNme' => 'Zantel - Tigo', 'fspCode' => 'ZPCASHIN'],
            ['fspId' => '505', 'fspShortNme' => 'T-PESA', 'fspFullNme' => 'TTCL', 'fspCode' => 'TTCLPPS'],
        ];
    }

    /**
     * Close the disbursement modal and reset all form data
     * 
     * @return void
     */
    public function closeModal(){
        Session::put('currentloanID', null);
        $this->tab_id = null;
        $this->bank_account = '';
        $this->approved_loan_value = 0;
        $this->approved_term = 12;
        $this->client_number = '';
        $this->firstInterestAmount = 0;
        $this->resetPaymentMethodFields();
        $this->validationErrors = []; // Clear validation errors
        
        // Reset charges and insurance data
        $this->charges = [];
        $this->insurance = [];
        $this->totalCharges = 0;
        $this->totalInsurance = 0;
        $this->accountDetails = [];
        
        // Reset control number data
        $this->generatedControlNumbers = [];
        
        // Reset processing state
        $this->isProcessing = false;
    }

    /**
     * Reset payment method specific fields
     * 
     * @return void
     */
    private function resetPaymentMethodFields()
    {
        $this->memberNbcAccount = '';
        $this->memberAccountHolderName = '';
        $this->memberPhoneNumber = '';
        $this->memberMnoProvider = '';
        $this->memberWalletHolderName = '';
        $this->memberBankCode = '';
        $this->memberBankAccountNumber = '';
        $this->memberBankAccountHolderName = '';
        $this->selectedDepositAccount = '';
    }

    public function close(){
        Session::put('currentloanID', null);
        $this->tab_id = null;
    }

    public function updatedSearchTerm()
    {
        // Reset tab_id when searching
        $this->tab_id = null;
        Session::put('currentloanID', null);
    }

    public function disburseLoan($payMethod, $loanType, $productCode)
    {
        // Set processing state to disable buttons
        $this->isProcessing = true;
        
        $startTime = microtime(true);
        $loanID = null;
        $memberName = null;
        $tpsResult = null;

        $payMethod = 'internal_transfer';
        
        try {
            Log::info('Loan disbursement process started', [
                'payment_method' => $payMethod,
                'loan_type' => $loanType,
                'product_code' => $productCode,
                'user_id' => auth()->id(),
                'user_branch' => auth()->user()->branch,
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId()
            ]);

            // STEP 1: VALIDATION AND PREPARATION
            Log::info('Starting validation and preparation phase');
            
            // Validate required data
            if ($payMethod !== 'cash' && !$this->bank_account) {
                Log::error('Disbursement validation failed: No bank account selected', [
                    'user_id' => auth()->id(),
                    'payment_method' => $payMethod
                ]);
                throw new \Exception('Please select a disbursement account.');
            }

            if ($this->approved_loan_value <= 0) {
                Log::error('Disbursement validation failed: Invalid loan amount', [
                    'user_id' => auth()->id(),
                    'approved_loan_value' => $this->approved_loan_value,
                    'payment_method' => $payMethod
                ]);
                throw new \Exception('Invalid loan amount for disbursement.');
            }

            // Get loan and member data for validation
            $loanID = session('currentloanID');
            if (!$loanID) {
                Log::error('Disbursement failed: No loan ID in session', [
                    'user_id' => auth()->id(),
                    'session_data' => session()->all()
                ]);
                throw new \Exception('No loan selected for disbursement.');
            }

            $loan = DB::table('loans')->find($loanID);
            if (!$loan) {
                Log::error('Loan not found in database', [
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Loan not found.');
            }

            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$member) {
                Log::error('Member not found in database', [
                    'client_number' => $loan->client_number,
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Member not found.');
            }

            // Validate critical member data based on payment method
            $this->validateCriticalMemberData($payMethod, $member);

            // Validate payment method specific data
            $this->validatePaymentMethodData($payMethod);

            // Calculate all deductions and additional amounts
            $deductions = $this->calculateAllDeductions($loan);
            
            Log::info('Deductions calculated', [
                'total_deductions' => $deductions['total_deductions'],
                'breakdown' => $deductions['breakdown'],
                'net_disbursement_amount' => $deductions['net_disbursement_amount']
            ]);

            // Validate sufficient funds for net disbursement amount
            if ($payMethod !== 'cash') {
                $this->validateSufficientFunds($deductions['net_disbursement_amount']);
            }

            // Get loan product details
            $loanProduct = DB::table('loan_sub_products')->where('sub_product_id', $productCode)->first();
            if (!$loanProduct) {
                Log::error('Loan product not found', [
                    'product_code' => $productCode,
                    'loan_id' => $loanID
                ]);
                throw new \Exception('Loan product not found.');
            }

            // Load account details for the product
            $this->loadAccountDetails($loanProduct);

            Log::info('Validation and preparation completed successfully');

            // STEP 2: PROCESS EXTERNAL TRANSACTION FIRST
            Log::info('Starting external transaction processing', [
                'payment_method' => $payMethod,
                'net_disbursement_amount' => $deductions['net_disbursement_amount']
            ]);

            // Process the external transaction (NBC, TIPS, etc.) FIRST
            // $tpsResult = $this->processExternalTransactionFirst($payMethod, $deductions['net_disbursement_amount'], $loan, $member);

            // if (!$tpsResult['success']) {
            //     Log::error('External transaction failed', [
            //         'loan_id' => $loanID,
            //         'payment_method' => $payMethod,
            //         'error' => $tpsResult['message'] ?? 'Unknown error'
            //     ]);
            //     throw new \Exception($tpsResult['message'] ?? 'External transaction failed');
            // }

            // Log::info('External transaction completed successfully', [
            //     'loan_id' => $loanID,
            //     'payment_method' => $payMethod,
            //     'external_reference' => $tpsResult['transaction']['external_reference'] ?? null
            // ]);

            // STEP 3: PROCESS POST-TRANSACTION OPERATIONS
            Log::info('Starting post-transaction operations');
            
            $this->processPostTransactionOperations($loanID, $loan, $member, $loanProduct, $deductions, $payMethod, $tpsResult);

            // STEP 4: FINALIZE AND CLEANUP
            Log::info('Finalizing loan disbursement');

            // Reset session and emit event
            session()->forget('currentloanID');
            $this->emit('refreshLoanList');

            // Calculate execution time
            $executionTime = microtime(true) - $startTime;
            
            // Show success message
            $successMessage = 'Loan disbursed successfully to ' . $member->present_surname . ' via ' . ucfirst(str_replace('_', ' ', $payMethod));
            session()->flash('success', $successMessage);

            // Log successful completion
            Log::info('Loan disbursement completed successfully', [
                'loan_id' => $loanID,
                'member_name' => $member->present_surname,
                'payment_method' => $payMethod,
                'net_disbursement_amount' => $deductions['net_disbursement_amount'],
                'total_deductions' => $deductions['total_deductions'],
                'execution_time_seconds' => round($executionTime, 3),
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString(),
                'transaction_reference' => $tpsResult ? ($tpsResult['transaction']['reference'] ?? null) : null,
                'correlation_id' => $tpsResult ? ($tpsResult['transaction']['correlation_id'] ?? null) : null,
                'external_reference' => $tpsResult ? ($tpsResult['transaction']['external_reference'] ?? null) : null
            ]);

            // Send SMS notification to member
            $this->sendLoanDisbursementNotification($member, $loan, $deductions['net_disbursement_amount']);

            // Close modal and reset form
            $this->closeModal();
            
            // Reset processing state
            $this->isProcessing = false;

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            // Log detailed error information
            Log::error('Loan disbursement failed', [
                'loan_id' => $loanID ?? null,
                'member_name' => $memberName ?? null,
                'payment_method' => $payMethod,
                'loan_type' => $loanType,
                'product_code' => $productCode,
                'user_id' => auth()->id(),
                'user_branch' => auth()->user()->branch ?? null,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time_seconds' => round($executionTime, 3),
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => [
                    'bank_account' => $this->bank_account ?? null,
                    'approved_loan_value' => $this->approved_loan_value ?? null,
                    'selected_loan' => $this->selectedLoan ?? null,
                    'first_interest_amount' => $this->firstInterestAmount ?? null
                ]
            ]);
            
            // Log additional context for debugging
            if ($loanID) {
                Log::error('Loan state at failure', [
                    'loan_id' => $loanID,
                    'loan_status' => DB::table('loans')->where('id', $loanID)->value('status'),
                    'loan_account_number' => DB::table('loans')->where('id', $loanID)->value('loan_account_number'),
                    'disbursement_date' => DB::table('loans')->where('id', $loanID)->value('disbursement_date')
                ]);
            }

            // Add system error to UI (sanitized for users)
            $userFriendlyMessage = $this->sanitizeErrorMessageForUser($e->getMessage());
            
            // If we have a response with user-friendly error details, use that instead
            if ($tpsResult && isset($tpsResult['error_details']['user_message'])) {
                $userFriendlyMessage = $tpsResult['error_details']['user_message'];
            }
            
            $this->addSystemError(
                $userFriendlyMessage,
                'disbursement_process'
            );

            // Add warning about retry
            $this->addWarningMessage(
                'You can try the disbursement again. If the problem persists, contact system administrator.',
                'retry_guidance'
            );

            // Add info about what happened
            $this->addInfoMessage(
                'The system has logged this error for investigation. Your loan data is safe and no partial transactions were processed.',
                'system_status'
            );
            
            // Don't re-throw the exception - let the UI handle it gracefully
            // The error will be displayed in the modal
            
            // Reset processing state
            $this->isProcessing = false;
        }
    }

    private function validateCriticalMemberData($payMethod, $member)
    {
        $errors = [];
        
        switch ($payMethod) {
            case 'internal_transfer':
                if (empty($member->account_number)) {
                    $errors[] = 'Member NBC account number is missing. Please update member profile before disbursement.';
                }
                break;
                
            case 'tips_mno':
                if (empty($member->phone_number)) {
                    $errors[] = 'Member phone number is missing. Please update member profile before disbursement.';
                }
                break;
                
            case 'tips_bank':
                if (empty($member->account_number)) {
                    $errors[] = 'Member bank account number is missing. Please update member profile before disbursement.';
                }
                break;
                
            case 'cash':
                // Check if member has deposit accounts
                $depositAccounts = DB::table('accounts')
                    ->where('client_number', $member->client_number)
                    ->where('product_number', '3000')
                    ->where('status', 'ACTIVE')
                    ->count();
                    
                if ($depositAccounts === 0) {
                    $errors[] = 'Member has no active deposit accounts. Please create a deposit account before disbursement.';
                }
                break;
        }
        
        if (!empty($errors)) {
            Log::error('Critical member data validation failed', [
                'payment_method' => $payMethod,
                'client_number' => $member->client_number,
                'errors' => $errors,
                'user_id' => auth()->id()
            ]);
            
            // Set validation errors for UI display
            $this->setValidationErrors($errors);
            
            // Throw exception to stop disbursement process
            throw new \Exception('Critical member data missing: ' . implode(' ', $errors));
        }
    }

    private function validatePaymentMethodData($payMethod)
    {
        switch ($payMethod) {
            case 'cash':
                if (empty($this->selectedDepositAccount)) {
                    throw new \Exception('Please select a deposit account for cash disbursement.');
                }
                break;
            case 'internal_transfer':
                if (empty($this->memberNbcAccount)) {
                    throw new \Exception('Member NBC account number is required for internal transfer.');
                }
                if (empty($this->memberAccountHolderName)) {
                    throw new \Exception('Account holder name is required for internal transfer.');
                }
                break;
            case 'tips_mno':
                if (empty($this->memberPhoneNumber)) {
                    throw new \Exception('Member phone number is required for MNO transfer.');
                }
                if (empty($this->memberMnoProvider)) {
                    throw new \Exception('MNO provider is required for MNO transfer.');
                }
                if (empty($this->memberWalletHolderName)) {
                    throw new \Exception('Wallet holder name is required for MNO transfer.');
                }
                break;
            case 'tips_bank':
                if (empty($this->memberBankCode)) {
                    throw new \Exception('Bank code is required for bank transfer.');
                }
                if (empty($this->memberBankAccountNumber)) {
                    throw new \Exception('Bank account number is required for bank transfer.');
                }
                if (empty($this->memberBankAccountHolderName)) {
                    throw new \Exception('Bank account holder name is required for bank transfer.');
                }
                break;
        }
    }

    // Helper methods from LoanDetails component
    private function handleTopUpLoan($loan)
    {
        // Implementation for top-up loan handling
        return 0; // Simplified for now
    }

    private function processLoanTransactions($loanAccountCode, $interestAccountCode, $chargesAccountCode, $insuranceAccountCode, $topUpAmount)
    {
        // This method is kept for backward compatibility
        // It now calls the comprehensive processAllLoanTransactions method
        
        // Create a simplified deductions array for backward compatibility
        $deductions = [
            'total_deductions' => $this->totalCharges + $this->totalInsurance + ($this->firstInterestAmount ?? 0) + $topUpAmount,
            'net_disbursement_amount' => $this->approved_loan_value - ($this->totalCharges + $this->totalInsurance + ($this->firstInterestAmount ?? 0) + $topUpAmount),
            'breakdown' => [
                'charges' => $this->totalCharges,
                'insurance' => $this->totalInsurance,
                'first_interest' => $this->firstInterestAmount ?? 0,
                'top_up_amount' => $topUpAmount,
                'closed_loan_balance' => 0,
                'outside_settlements' => 0,
                'restructuring_amount' => 0
            ],
            'charges_amount' => $this->totalCharges,
            'insurance_amount' => $this->totalInsurance,
            'first_interest_amount' => $this->firstInterestAmount ?? 0,
            'top_up_amount' => $topUpAmount,
            'closed_loan_balance' => 0,
            'outside_settlements' => 0,
            'restructuring_amount' => 0
        ];

        // Call the comprehensive method
        $this->processAllLoanTransactions($loanAccountCode, $interestAccountCode, $chargesAccountCode, $insuranceAccountCode, $deductions);
    }

    protected function createRepaymentSchedule($loanID, $loan, $loanProduct, $member)
    {
        try {
            Log::info('Creating loan repayment schedule', [
                'loan_id' => $loanID,
                'client_number' => $loan->client_number,
                'approved_loan_value' => $this->approved_loan_value,
                'approved_term' => $this->approved_term
            ]);

            // Validate required parameters
            if (!$this->approved_loan_value || !$this->approved_term || !$loanProduct) {
                Log::error('Missing required parameters for schedule generation', [
                    'approved_loan_value' => $this->approved_loan_value,
                    'approved_term' => $this->approved_term,
                    'loan_product' => $loanProduct
                ]);
                throw new \Exception('Missing required parameters for schedule generation.');
            }

            // Get loan parameters
            $principal = (float)$this->approved_loan_value;
            $annualInterestRate = (float)($loanProduct->interest_value ?? 0);
            $monthlyInterestRate = $annualInterestRate / 12 / 100; // Convert to decimal
            $tenure = (int)$this->approved_term;

            Log::info('Schedule generation parameters', [
                'principal' => $principal,
                'annualInterestRate' => $annualInterestRate,
                'monthlyInterestRate' => $monthlyInterestRate,
                'tenure' => $tenure
            ]);

            // Calculate equal monthly installment using amortization formula
            // PMT = P * (r * (1 + r)^n) / ((1 + r)^n - 1)
            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $tenure)) / (pow(1 + $monthlyInterestRate, $tenure) - 1);
            } else {
                $monthlyInstallment = $principal / $tenure; // If no interest, equal principal payments
            }

            Log::info('Monthly installment calculated', [
                'monthly_installment' => $monthlyInstallment
            ]);

            // Clear any existing schedules for this loan
            DB::table('loans_schedules')->where('loan_id', $loanID)->delete();

            // Generate schedule
            $remainingBalance = $principal;
            $totalPayment = 0;
            $totalInterest = 0;
            $totalPrincipal = 0;

            // Calculate dates
            $disbursementDate = \Carbon\Carbon::now();

            // Calculate first payment date based on member's payroll date (MEMBERS PORTAL PATTERN)
            $firstRegularDate = $disbursementDate->copy()->addMonth(); // Default fallback
            $daysToFirstInstallment = $disbursementDate->diffInDays($firstRegularDate); // Default: ~30 days

            try {
                // Get member's payroll date from their group
                $client = DB::table('clients')->where('client_number', $loan->client_number)->first();
                if ($client && $client->member_group) {
                    $memberGroup = DB::table('member_groups')->where('id', $client->member_group)->first();

                    if ($memberGroup && $memberGroup->payrol_date) {
                        $payrollDay = (int)$memberGroup->payrol_date;

                        // Calculate first payment date based on payroll day
                        if ($disbursementDate->day < $payrollDay) {
                            // Use this month's payroll date
                            $firstRegularDate = $disbursementDate->copy()->day($payrollDay);
                        } else {
                            // Use next month's payroll date
                            $firstRegularDate = $disbursementDate->copy()->addMonth()->day($payrollDay);
                        }

                        // Calculate actual days
                        $daysToFirstInstallment = $disbursementDate->diffInDays($firstRegularDate);

                        Log::info('First payment date calculated from member payroll', [
                            'loan_id' => $loanID,
                            'client_number' => $loan->client_number,
                            'member_group_id' => $client->member_group,
                            'payroll_day' => $payrollDay,
                            'disbursement_date' => $disbursementDate->format('Y-m-d'),
                            'first_payment_date' => $firstRegularDate->format('Y-m-d'),
                            'days_to_first_installment' => $daysToFirstInstallment
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Could not calculate exact days to first payment, using default ~30 days', [
                    'error' => $e->getMessage(),
                    'loan_id' => $loanID,
                    'client_number' => $loan->client_number ?? null
                ]);
            }

            // Add First Interest installment (interest from disbursement to first regular installment)
            $firstInterestAmount = $principal * ($annualInterestRate / 100) * ($daysToFirstInstallment / 365);

            // Store first interest amount for use in other functions
            $this->firstInterestAmount = $firstInterestAmount;
            
            if ($firstInterestAmount > 0) {
                Log::info('Creating first interest installment', [
                    'first_interest_amount' => $firstInterestAmount,
                    'installment_date' => $firstRegularDate->format('Y-m-d')
                ]);

                // Save first interest installment
                DB::table('loans_schedules')->insert([
                    'loan_id' => $loanID,
                    'installment' => $firstInterestAmount,
                    'interest' => $firstInterestAmount,
                    'principle' => 0,
                    'opening_balance' => $principal,
                    'closing_balance' => $principal, // Balance doesn't change for interest-only payment
                    'bank_account_number' => $loan->bank1 ?? null,
                    'completion_status' => 'ACTIVE',
                    'status' => 'ACTIVE',
                    'installment_date' => $firstRegularDate->format('Y-m-d'),
                    'member_number' => $loan->client_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $totalPayment += $firstInterestAmount;
                $totalInterest += $firstInterestAmount;
                // Principal remains unchanged for first interest installment
            }

            // Generate regular installments starting from the second month
            $regularStartDate = $firstRegularDate->copy()->addMonth();
            
            for ($i = 0; $i < $tenure; $i++) {
                $openingBalance = $remainingBalance;
                
                // Calculate interest for this month
                $monthlyInterest = $remainingBalance * $monthlyInterestRate;
                
                // Calculate principal for this month
                $monthlyPrincipal = $monthlyInstallment - $monthlyInterest;
                
                // Ensure we don't overpay in the last installment
                if ($i == $tenure - 1) {
                    $monthlyPrincipal = $remainingBalance;
                    $monthlyInstallment = $monthlyPrincipal + $monthlyInterest;
                }
                
                // Update remaining balance
                $remainingBalance -= $monthlyPrincipal;
                if ($remainingBalance < 0.01) $remainingBalance = 0; // Round to zero if very small
                
                Log::info('Creating regular installment', [
                    'installment_number' => $i + 1,
                    'installment_date' => $regularStartDate->format('Y-m-d'),
                    'opening_balance' => $openingBalance,
                    'payment' => $monthlyInstallment,
                    'principal' => $monthlyPrincipal,
                    'interest' => $monthlyInterest,
                    'closing_balance' => $remainingBalance
                ]);

                // Save regular installment
                DB::table('loans_schedules')->insert([
                    'loan_id' => $loanID,
                    'installment' => $monthlyInstallment,
                    'interest' => $monthlyInterest,
                    'principle' => $monthlyPrincipal,
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $remainingBalance,
                    'bank_account_number' => $loan->bank1 ?? null,
                    'completion_status' => 'ACTIVE',
                    'status' => 'ACTIVE',
                    'installment_date' => $regularStartDate->format('Y-m-d'),
                    'member_number' => $loan->client_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $totalPayment += $monthlyInstallment;
                $totalInterest += $monthlyInterest;
                $totalPrincipal += $monthlyPrincipal;
                
                $regularStartDate->addMonth();
            }

            // Update loan with schedule information
            DB::table('loans')->where('id', $loanID)->update([
                'monthly_installment' => $monthlyInstallment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal,
                'total_payment' => $totalPayment
            ]);

            Log::info('Loan repayment schedule created successfully', [
                'loan_id' => $loanID,
                'total_installments' => $tenure + ($firstInterestAmount > 0 ? 1 : 0),
                'monthly_installment' => $monthlyInstallment,
                'total_payment' => $totalPayment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal,
                'first_interest_amount' => $firstInterestAmount
            ]);

            // Generate control numbers for billing
            Log::info('Generating control numbers for loan billing');
            $this->generateControlNumbers();
            Log::info('Control numbers generated successfully');

            // Register Bill
            // Create bills for each service
            $controlNumbers = $this->generatedControlNumbers ?? [];
            Log::info('Creating service bills', ['control_numbers_count' => count($controlNumbers)]);
            foreach ($controlNumbers as $control) {
                Log::info('Processing service bill', ['service_code' => $control['service_code']]);
                $service = DB::table('services')
                    ->where('code', $control['service_code'])
                    ->first();

                if ($service) {
                    $billingService = new BillingService();
                    $bill = $billingService->createBill(
                        $this->client_number,
                        $service->id,
                        $service->is_recurring,
                        $service->payment_mode,
                        $control['control_number'],
                        $service->lower_limit
                    );

                    Log::info('Service bill created', [
                        'service_id' => $service->id,
                        'control_number' => $control['control_number']
                    ]);
                } else {
                    Log::warning('Service not found', ['service_code' => $control['service_code']]);
                }
            }

            // Get member data for notifications (removed guarantor logic)
            $client = DB::table('clients')->where('client_number', $this->client_number)->first();

            // Generate payment link for loan installments
            $paymentLink = null;
            try {
                // Get loan schedules for payment link generation
                $loanSchedules = DB::table('loans_schedules')
                    ->where('loan_id', $loanID)
                    ->orderBy('installment')
                    ->get();
                
                if ($loanSchedules->isNotEmpty() && $client) {
                    $paymentLinkService = new PaymentLinkService();
                    
                    // Generate payment link with all installments
                    $paymentLinkResponse = $paymentLinkService->generateLoanInstallmentsPaymentLink(
                        $loanID,
                        $client,
                        $loanSchedules,
                        [
                            'description' => 'SACCOS Loan Services - ' . ($loan->loan_id ?? 'Loan ID: ' . $loanID)
                        ]
                    );
                    
                    // Extract payment URL from response
                    if (isset($paymentLinkResponse['data']['payment_url'])) {
                        $paymentLink = $paymentLinkResponse['data']['payment_url'];
                        
                        Log::info('Payment link generated successfully', [
                            'loan_id' => $loanID,
                            'payment_url' => $paymentLink,
                            'link_id' => $paymentLinkResponse['data']['link_id'] ?? null,
                            'total_amount' => $paymentLinkResponse['data']['total_amount'] ?? null
                        ]);
                    } else {
                        Log::warning('Payment URL not found in response', [
                            'loan_id' => $loanID,
                            'response' => $paymentLinkResponse
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate payment link', [
                    'loan_id' => $loanID,
                    'error' => $e->getMessage()
                ]);
                // Continue without payment link - not critical for loan disbursement
            }

            // Dispatch background job for notifications (member only, no guarantor)
            Log::info('Dispatching member notifications', [
                'client' => $client ? [
                    'id' => $client->id,
                    'name' => $client->first_name . ' ' . $client->last_name,
                    'email' => $client->email ?? null,
                    'phone' => $client->phone_number ?? null
                ] : null,
                'control_numbers' => $this->generatedControlNumbers,
                'payment_link' => $paymentLink
            ]);

            // Use payment link if available, otherwise fallback to legacy format
            if (!$paymentLink) {
                $institution_id = DB::table('institutions')->where('id', 1)->value('institution_id');
                $saccos = preg_replace('/[^0-9]/', '', $institution_id); // Remove non-numeric characters
                $paymentLink = env('PAYMENT_LINK').'/'.$saccos.'/'.$this->client_number;
            }
            
            // Dispatch notification job if ProcessMemberNotifications class exists (member only)
            if (class_exists('App\Jobs\ProcessMemberNotifications')) {
                \App\Jobs\ProcessMemberNotifications::dispatch(
                    $client,
                    $controlNumbers,
                    $paymentLink,
                    $loanID  // Pass loan ID for accurate notification data
                )->onQueue('notifications');
                
                Log::info('Notification job dispatched successfully (member only)', [
                    'payment_link' => $paymentLink
                ]);
            } else {
                Log::warning('ProcessMemberNotifications job class not found, skipping notifications');
            }

        } catch (\Exception $e) {
            Log::error('Error creating repayment schedule: ' . $e->getMessage(), [
                'loan_id' => $loanID,
                'trace' => $e->getTraceAsString()
            ]);
                            throw new \Exception('Failed to create loan repayment schedule. Please try again.');
        }
    }

    protected function generateControlNumbers()
    {
        try {
            Log::info('Generating control numbers for loan disbursement', [
                'client_number' => $this->client_number
            ]);

            if (empty($this->client_number)) {
                Log::error('Client number is required for control number generation');
                throw new \Exception('Client number is required for control number generation.');
            }

            $billingService = new BillingService();
            
            // Get all required services in a single query
            $services = DB::table('services')
                ->whereIn('code', ['REP'])
                ->select('id', 'code', 'name', 'is_recurring', 'payment_mode', 'lower_limit')
                ->get()
                ->keyBy('code');

            if ($services->isEmpty()) {
                Log::error('No services found for control number generation', [
                    'service_codes' => ['REP']
                ]);
                throw new \Exception('Required services not found for control number generation.');
            }

            $this->generatedControlNumbers = [];

            // Generate control numbers for each service
            foreach (['REP'] as $serviceCode) {
                if (!$services->has($serviceCode)) {
                    Log::warning('Service not found', ['service_code' => $serviceCode]);
                    continue;
                }

                $service = $services[$serviceCode];
                
                Log::info('Generating control number for service', [
                    'service_code' => $serviceCode,
                    'service_id' => $service->id,
                    'client_number' => $this->client_number
                ]);

                $controlNumber = $billingService->generateControlNumber(
                    $this->client_number,
                    $service->id,
                    $service->is_recurring,
                    $service->payment_mode
                );

                $this->generatedControlNumbers[] = [
                    'service_code' => $service->code,
                    'control_number' => $controlNumber,
                    'amount' => $service->lower_limit
                ];

                Log::info('Control number generated successfully', [
                    'service_code' => $service->code,
                    'control_number' => $controlNumber,
                    'amount' => $service->lower_limit
                ]);
            }

            Log::info('Control number generation completed', [
                'total_control_numbers' => count($this->generatedControlNumbers),
                'client_number' => $this->client_number
            ]);

            return $this->generatedControlNumbers;

        } catch (\Exception $e) {
            Log::error('Error generating control numbers: ' . $e->getMessage(), [
                'client_number' => $this->client_number ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to generate transaction reference numbers. Please try again.');
        }
    }

    /**
     * Process loan disbursement using TransactionProcessingService
     * Note: Cash transactions are handled separately via TransactionPostingService
     * 
     * @param string $payMethod Payment method (internal_transfer, tips_mno, tips_bank)
     * @param object $loanAccount The loan account object
     * @param float $amount Disbursement amount
     * @param string $memberName Member name for narration
     * @return array Transaction result
     * @throws \Exception
     */
    private function processDisbursementByPaymentMethod($payMethod, $loanAccount, $amount, $memberName)
    {
        try {
            Log::info('Processing loan disbursement with TransactionProcessingService', [
                'payment_method' => $payMethod,
                'loan_account' => $loanAccount->account_number,
                'amount' => $amount,
                'member_name' => $memberName
            ]);

            // Get loan and member details
            $loanID = session('currentloanID');
            $loan = DB::table('loans')->where('id', $loanID)->first();
            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();

            if (!$loan || !$member) {
                throw new \Exception('Loan or member data not found for disbursement processing.');
            }

            // Prepare metadata based on payment method
            $meta = $this->prepareTransactionMetadata($payMethod, $member, $loan);

            // Determine source and destination accounts based on payment method
            // Source account is always the internal mirror account from bank_accounts table
            $sourceAccount = DB::table('bank_accounts')
                ->where('internal_mirror_account_number', $this->bank_account)
                ->value('account_number');

            if (!$sourceAccount) {
                throw new \Exception('Internal mirror account not found for bank account: ' . $this->bank_account);
            }

            $destinationAccount = $this->getDestinationAccount($payMethod);

            Log::info('Account mapping for TransactionProcessingService', [
                'payment_method' => $payMethod,
                'source_account' => $sourceAccount,      // Internal mirror account
                'destination_account' => $destinationAccount,
                'bank_account' => $this->bank_account,
                'loan_account' => $loanAccount->account_number
            ]);

            // Initialize TransactionProcessingService according to documented pattern
            $tps = new TransactionProcessingService(
                $payMethod,                    // serviceType
                'loan',                        // saccosService (as per documentation)
                $amount,                       // amount
                $sourceAccount,                // sourceAccount (internal mirror account)
                $destinationAccount,           // destinationAccount
                $member->client_number,        // memberId
                $meta                          // metadata
            );

            // Process the transaction
            $result = $tps->process();

            Log::info('TransactionProcessingService result', [
                'success' => $result['success'] ?? false,
                'referenceNumber' => $result['referenceNumber'] ?? null,
                'externalReferenceNumber' => $result['externalReferenceNumber'] ?? null,
                'correlationId' => $result['correlationId'] ?? null,
                'processingTimeMs' => $result['processingTimeMs'] ?? null,
                'should_post_to_ledger' => $result['should_post_to_ledger'] ?? false,
                'full_result' => $result
            ]);

            // Handle the result according to documented response format
            if (!($result['success'] ?? false)) {
                // Get the full error details
                $errorMessage = $result['message'] ?? 'Unknown error';
                $errorCode = $result['errorCode'] ?? 'UNKNOWN';
                $errorDetails = $result['error_details'] ?? [];
                $externalResult = $result['external_result'] ?? null;
                
                // Build comprehensive error message
                $fullErrorMessage = "Transaction processing failed: {$errorMessage}";
                
                if ($errorCode !== 'UNKNOWN') {
                    $fullErrorMessage .= " (Error Code: {$errorCode})";
                }
                
                if (!empty($errorDetails)) {
                    $fullErrorMessage .= " - Details: " . json_encode($errorDetails);
                }
                
                if ($externalResult) {
                    $fullErrorMessage .= " - External Result: " . json_encode($externalResult);
                }
                
                // Log the complete error information
                Log::error('TransactionProcessingService failed with full details', [
                    'payment_method' => $payMethod,
                    'amount' => $amount,
                    'full_result' => $result,
                    'error_message' => $fullErrorMessage,
                    'error_code' => $errorCode,
                    'error_details' => $errorDetails,
                    'external_result' => $externalResult
                ]);
                
                throw new \Exception($fullErrorMessage);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Error in processDisbursementByPaymentMethod', [
                'payment_method' => $payMethod,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'full_trace' => $e->getTraceAsString(),
                'previous_exception' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
            ]);
            
            // Re-throw with more context
            $contextMessage = "Payment method: {$payMethod}, Amount: {$amount}";
            throw new \Exception("{$contextMessage} - Please try again or contact support if the issue persists.", $e->getCode(), $e);
        }
    }

    /**
     * Prepare transaction metadata based on payment method
     * Following the documented examples exactly (excluding cash)
     * 
     * @param string $payMethod
     * @param object $member
     * @param object $loan
     * @return array
     */
    private function prepareTransactionMetadata($payMethod, $member, $loan)
    {
        $meta = [];

        switch ($payMethod) {
            case 'internal_transfer':
                $meta = [
                    'narration' => 'Internal transfer loan disbursement',
                    'payer_name' => $member->present_surname
                ];
                break;

            case 'tips_mno':
                $meta = [
                    'phone_number' => $this->memberPhoneNumber,
                    'wallet_provider' => 'MPESA',
                    'narration' => 'Loan disbursement via M-Pesa',
                    'payer_name' => $member->present_surname
                ];
                break;

            case 'tips_bank':
                $meta = [
                    'bank_code' => $this->memberBankCode ?? '015',
                    'phone_number' => $this->memberPhoneNumber ?? '255000000000',
                    'narration' => 'Loan disbursement to bank account',
                    'product_code' => 'FTLC'
                ];
                break;

            default:
                throw new \Exception('Unsupported payment method for TransactionProcessingService: ' . $payMethod);
        }

        return $meta;
    }

    /**
     * Get destination account based on payment method
     * 
     * @param string $payMethod
     * @return string
     * @throws \Exception
     */
    private function getDestinationAccount($payMethod)
    {
        switch ($payMethod) {
            case 'internal_transfer':
                if (empty($this->memberNbcAccount)) {
                    throw new \Exception('Member NBC account not provided for internal transfer.');
                }
                return $this->memberNbcAccount;

            case 'tips_mno':
            case 'tips_bank':
                if (empty($this->bank_account)) {
                    throw new \Exception('Bank account not selected for disbursement.');
                }
                return $this->bank_account;

            default:
                throw new \Exception('Invalid payment method for TransactionProcessingService: ' . $payMethod);
        }
    }

    /**
     * Process cash disbursement using TransactionPostingService (not TransactionProcessingService)
     * 
     * @param object $loanAccount
     * @param float $amount
     * @param string $memberName
     * @return array
     */
    private function processCashDisbursement($loanAccount, $amount, $memberName)
    {
        try {
            Log::info('Processing cash disbursement with TransactionPostingService', [
                'loan_account' => $loanAccount->account_number,
                'amount' => $amount,
                'member_name' => $memberName
            ]);

            // Validate deposit account selection
            if (empty($this->selectedDepositAccount)) {
                throw new \Exception('Deposit account not selected for cash disbursement.');
            }

            // Use TransactionPostingService for cash transactions
            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $loanAccount->account_number, // Debit loan account
                'second_account' => $this->selectedDepositAccount, // Credit member's deposit account
                'amount' => $amount,
                'narration' => 'Cash loan disbursement: ' . $amount . ' to ' . $memberName,
                'action' => 'cash_loan_disbursement'
            ];

            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['status'] !== 'success') {
                throw new \Exception('Failed to post cash disbursement transaction: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('Cash loan disbursement processed successfully', [
                'loan_account' => $loanAccount->account_number,
                'deposit_account' => $this->selectedDepositAccount,
                'amount' => $amount,
                'member' => $memberName,
                'reference_number' => $result['reference_number'] ?? null
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error in processCashDisbursement', [
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process internal transfer disbursement using TransactionProcessingService
     * 
     * @param object $loanAccount
     * @param float $amount
     * @param string $memberName
     * @return array
     */
    private function processInternalTransferDisbursement($loanAccount, $amount, $memberName)
    {
        return $this->processDisbursementByPaymentMethod('internal_transfer', $loanAccount, $amount, $memberName);
    }

    /**
     * Process TIPS MNO disbursement using TransactionProcessingService
     * 
     * @param object $loanAccount
     * @param float $amount
     * @param string $memberName
     * @return array
     */
    private function processTipsMnoDisbursement($loanAccount, $amount, $memberName)
    {
        return $this->processDisbursementByPaymentMethod('tips_mno', $loanAccount, $amount, $memberName);
    }

    /**
     * Process TIPS Bank disbursement using TransactionProcessingService
     * 
     * @param object $loanAccount
     * @param float $amount
     * @param string $memberName
     * @return array
     */
    private function processTipsBankDisbursement($loanAccount, $amount, $memberName)
    {
        return $this->processDisbursementByPaymentMethod('tips_bank', $loanAccount, $amount, $memberName);
    }

    private function calculateTotalCharges()
    {
        // Return the total charges calculated from the charges array
        return $this->totalCharges;
    }

    private function calculateInsurance()
    {
        // Return the total insurance calculated from the insurance array
        return $this->totalInsurance;
    }

    public function getFilteredLoansProperty()
    {
        $query = DB::table('loans')
            ->whereIn('status', ['APPROVED']);

        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('client_number', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('loan_type_2', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('amount_to_be_credited', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->get();
    }

    public function getTipsBanksProperty()
    {
        return $this->tipsBanks;
    }

    public function render()
    {
        $loans = $this->getFilteredLoansProperty();
        
        return view('livewire.accounting.loans-disbursement', [
            'loans' => $loans
        ]);
    }

    public function showModal(){
       return true;
    }

    public function setValidationErrors($errors)
    {
        $this->validationErrors = is_array($errors) ? $errors : [$errors];
    }

    /**
     * Add processing error message
     * 
     * @param string $message
     * @param string $category
     * @return void
     */
    public function addProcessingError($message, $category = 'general')
    {
        $this->processingErrors[] = [
            'message' => $message,
            'category' => $category,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Add network error message
     * 
     * @param string $message
     * @param string $service
     * @return void
     */
    public function addNetworkError($message, $service = 'unknown')
    {
        $this->networkErrors[] = [
            'message' => $message,
            'service' => $service,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Add system error message
     * 
     * @param string $message
     * @param string $component
     * @return void
     */
    public function addSystemError($message, $component = 'unknown')
    {
        $this->systemErrors[] = [
            'message' => $message,
            'component' => $component,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Add warning message
     * 
     * @param string $message
     * @param string $category
     * @return void
     */
    public function addWarningMessage($message, $category = 'general')
    {
        $this->warningMessages[] = [
            'message' => $message,
            'category' => $category,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Add info message
     * 
     * @param string $message
     * @param string $category
     * @return void
     */
    public function addInfoMessage($message, $category = 'general')
    {
        $this->infoMessages[] = [
            'message' => $message,
            'category' => $category,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Add success message
     * 
     * @param string $message
     * @param string $category
     * @return void
     */
    public function addSuccessMessage($message, $category = 'general')
    {
        $this->successMessages[] = [
            'message' => $message,
            'category' => $category,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Clear all error messages
     * 
     * @return void
     */
    public function clearAllErrors()
    {
        $this->validationErrors = [];
        $this->processingErrors = [];
        $this->networkErrors = [];
        $this->systemErrors = [];
        $this->warningMessages = [];
        $this->infoMessages = [];
        $this->successMessages = [];
    }
    
    /**
     * Reset processing state (for emergency use)
     */
    public function resetProcessingState()
    {
        $this->isProcessing = false;
    }

    /**
     * Process external transaction FIRST (before any internal operations)
     * This ensures the actual money transfer happens before creating accounts/schedules
     * 
     * @param string $payMethod
     * @param float $amount
     * @param object $loan
     * @param object $member
     * @return array
     * @throws \Exception
     */
    private function processExternalTransactionFirst($payMethod, $amount, $loan, $member)
    {
        Log::info('Processing external transaction first', [
            'payment_method' => $payMethod,
            'amount' => $amount,
            'loan_id' => $loan->id,
            'member' => $member->present_surname ?? 'Unknown'
        ]);

        try {
            // Prepare transaction metadata
            $metadata = $this->prepareTransactionMetadata($payMethod, $member, $loan);
            
            // Get destination account based on payment method
            $destinationAccount = $this->getDestinationAccount($payMethod);
            
            // Create TransactionProcessingService instance
            $transactionService = new TransactionProcessingService(
                $payMethod,
                'loan_disbursement',
                $amount,
                $this->bank_account, // Source account (disbursement account)
                $destinationAccount,
                $member->client_number,
                $metadata
            );

            // Process the transaction
            $result = $transactionService->process();
            
            Log::info('External transaction processed', [
                'success' => $result['success'],
                'status' => $result['status'],
                'external_reference' => $result['transaction']['external_reference'] ?? null
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('External transaction processing failed', [
                'payment_method' => $payMethod,
                'amount' => $amount,
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw the exception to be handled by the main method
            throw $e;
        }
    }

    /**
     * Process all post-transaction operations after successful external transaction
     * This includes creating accounts, schedules, and updating loan status
     * 
     * @param int $loanID
     * @param object $loan
     * @param object $member
     * @param object $loanProduct
     * @param array $deductions
     * @param string $payMethod
     * @param array $tpsResult
     * @throws \Exception
     */
    private function processPostTransactionOperations($loanID, $loan, $member, $loanProduct, $deductions, $payMethod, $tpsResult)
    {
        Log::info('Processing post-transaction operations', [
            'loan_id' => $loanID,
            'payment_method' => $payMethod,
            'external_reference' => $tpsResult['transaction']['external_reference'] ?? null
        ]);

        try {
            // STEP 1: Create all loan accounts
            Log::info('Creating loan accounts');
            $accountService = new AccountCreationService();

            // Validate that loan_account exists in accountDetails
            if (!isset($this->accountDetails['loan_account']) || $this->accountDetails['loan_account'] === 'N/A') {
                throw new \Exception('Loan account configuration not found for this product.');
            }

            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $loanAccountNumber = str_pad($this->accountDetails['loan_account'], 16, '0', STR_PAD_LEFT);
            Log::info('Looking for loan account', [
                'raw_value' => $this->accountDetails['loan_account'],
                'padded_value' => $loanAccountNumber
            ]);
            $loanParentAccount = AccountsModel::where('account_number', $loanAccountNumber)->first();
            if (!$loanParentAccount) {
                // Try without padding if not found
                $loanParentAccount = AccountsModel::where('account_number', $this->accountDetails['loan_account'])->first();
            }
            if (!$loanParentAccount) {
                throw new \Exception('Loan parent account not found in database. Looking for: ' . $loanAccountNumber);
            }

            // Create loan account
            $loanAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $loanParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Loan Account: Loan ID ' . $loanID
            ], $loanParentAccount->account_number);

            // Create interest account
            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $interestAccountNumber = str_pad($this->accountDetails['interest_account'], 16, '0', STR_PAD_LEFT);
            Log::info('Looking for interest account', [
                'raw_value' => $this->accountDetails['interest_account'],
                'padded_value' => $interestAccountNumber
            ]);
            $interestParentAccount = AccountsModel::where('account_number', $interestAccountNumber)->first();
            if (!$interestParentAccount) {
                // Try without padding if not found
                $interestParentAccount = AccountsModel::where('account_number', $this->accountDetails['interest_account'])->first();
            }
            if (!$interestParentAccount) {
                throw new \Exception('Interest parent account not found. Looking for: ' . $interestAccountNumber);
            }

            $interestAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $interestParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Interest Account: Loan ID ' . $loanID
            ], $interestParentAccount->account_number);

            // Create charges account
            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $feesAccountNumber = str_pad($this->accountDetails['fees_account'], 16, '0', STR_PAD_LEFT);
            Log::info('Looking for fees account', [
                'raw_value' => $this->accountDetails['fees_account'],
                'padded_value' => $feesAccountNumber
            ]);
            $chargesParentAccount = AccountsModel::where('account_number', $feesAccountNumber)->first();
            if (!$chargesParentAccount) {
                // Try without padding if not found
                $chargesParentAccount = AccountsModel::where('account_number', $this->accountDetails['fees_account'])->first();
            }
            if (!$chargesParentAccount) {
                throw new \Exception('Charges parent account not found. Looking for: ' . $feesAccountNumber);
            }

            $chargesAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $chargesParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Charges Account: Loan ID ' . $loanID
            ], $chargesParentAccount->account_number);

            // Create insurance account
            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $insuranceAccountNumber = str_pad($this->accountDetails['insurance_account'], 16, '0', STR_PAD_LEFT);
            Log::info('Looking for insurance account', [
                'raw_value' => $this->accountDetails['insurance_account'],
                'padded_value' => $insuranceAccountNumber
            ]);
            $insuranceParentAccount = AccountsModel::where('account_number', $insuranceAccountNumber)->first();
            if (!$insuranceParentAccount) {
                // Try without padding if not found
                $insuranceParentAccount = AccountsModel::where('account_number', $this->accountDetails['insurance_account'])->first();
            }
            if (!$insuranceParentAccount) {
                throw new \Exception('Insurance parent account not found. Looking for: ' . $insuranceAccountNumber);
            }

            $insuranceAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $insuranceParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Insurance Account: Loan ID ' . $loanID
            ], $insuranceParentAccount->account_number);

            Log::info('All loan accounts created successfully', [
                'loan_account' => $loanAccount->account_number,
                'interest_account' => $interestAccount->account_number,
                'charges_account' => $chargesAccount->account_number,
                'insurance_account' => $insuranceAccount->account_number
            ]);

            // Update loan with account numbers immediately after creation
            Log::info('Updating loan with account numbers');
            DB::table('loans')->where('id', $loanID)->update([
                'loan_account_number' => $loanAccount->account_number,
                'interest_account_number' => $interestAccount->account_number,
                'charge_account_number' => $chargesAccount->account_number,
                'insurance_account_number' => $insuranceAccount->account_number
            ]);

            // STEP 2: Process internal transactions (deductions)
            Log::info('Processing internal transactions (deductions)');
            
            $this->processAllLoanTransactions(
                $loanAccount,
                $interestAccount,
                $chargesAccount,
                $insuranceAccount,
                $deductions,
                null, // loanType
                $payMethod
            );

            // STEP 3: Handle special cases
            if ($deductions['restructuring_amount'] > 0) {
                $this->handleLoanRestructuring($loan, $deductions['restructuring_amount']);
            }

            if ($deductions['top_up_amount'] > 0) {
                $this->handleTopUpLoanProcessing($loan, $deductions['top_up_amount']);
            }

            // STEP 4: Create repayment schedule
            Log::info('Creating repayment schedule');
            $this->createRepaymentSchedule($loanID, $loan, $loanProduct, $member);

            // STEP 5: Generate control numbers
            Log::info('Generating control numbers');
            $controlNumbers = $this->generateControlNumbers() ?? [];

            // STEP 6: Update loan status and finalize
            Log::info('Updating loan status and finalizing disbursement');
            
            DB::table('loans')->where('id', $loanID)->update([
                'status' => 'ACTIVE',
                'disbursement_date' => now(),
                'disbursement_method' => $payMethod,
                'disbursement_account' => $this->bank_account,
                'net_disbursement_amount' => $deductions['net_disbursement_amount'],
                'total_deductions' => $deductions['total_deductions'],
                'monthly_installment' => $this->calculateMonthlyInstallment($loan, $loanProduct)
                // Note: external_reference belongs to transactions table, not loans table
            ]);

            // STEP 7: Send notifications
            Log::info('Sending disbursement notifications', [
                'loan_id' => $loanID
            ]);
            $this->sendDisbursementNotifications($member, $controlNumbers, $deductions, $loanID);

            Log::info('Post-transaction operations completed successfully', [
                'loan_id' => $loanID,
                'accounts_created' => [
                    'loan_account' => $loanAccount->account_number,
                    'interest_account' => $interestAccount->account_number,
                    'charges_account' => $chargesAccount->account_number,
                    'insurance_account' => $insuranceAccount->account_number
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Post-transaction operations failed', [
                'loan_id' => $loanID,
                'error' => $e->getMessage(),
                'transaction_reference' => $tpsResult['transaction']['reference'] ?? null,
                'correlation_id' => $tpsResult['transaction']['correlation_id'] ?? null
            ]);
            
            // Re-throw the exception to be handled by the main method
            throw $e;
        }
    }

    /**
     * Sanitize error message for user display
     * Removes technical details, stack traces, and file paths
     * 
     * @param string $errorMessage
     * @return string
     */
    public function sanitizeErrorMessageForUser($errorMessage)
    {
        // Remove stack traces and technical details
        $patterns = [
            '/Details:.*$/s', // Remove "Details: {...}" sections
            '/stack_trace.*$/s', // Remove stack trace sections
            '/exception_class.*$/s', // Remove exception class details
            '/error_code.*$/s', // Remove error code details
            '/error_message.*$/s', // Remove error message details
            '/#\d+.*$/m', // Remove stack trace lines starting with #
            '/D:\\\\.*\.php\(\d+\):/', // Remove file paths with line numbers
            '/App\\\\.*->/', // Remove class method calls
            '/Object\(.*\)/', // Remove object details
            '/Array\(.*\)/', // Remove array details
            '/Illuminate\\\\.*/', // Remove Laravel framework details
            '/vendor\\\\.*/', // Remove vendor path details
            '/app\\\\.*/', // Remove app path details
        ];

        $sanitized = $errorMessage;
        foreach ($patterns as $pattern) {
            $sanitized = preg_replace($pattern, '', $sanitized);
        }

        // Clean up extra whitespace and punctuation
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        $sanitized = trim($sanitized);
        $sanitized = rtrim($sanitized, ' -');

        // If the message is too technical, provide a generic user-friendly message
        if (strlen($sanitized) < 10 || strpos($sanitized, 'Exception') !== false) {
            return 'A system error occurred during loan disbursement. Please try again or contact support if the issue persists.';
        }

        return $sanitized;
    }

    /**
     * Get all errors as a single array for display
     * 
     * @return array
     */
    public function getAllErrors()
    {
        $allErrors = [];
        
        foreach ($this->validationErrors as $error) {
            $allErrors[] = [
                'type' => 'validation',
                'message' => $error,
                'severity' => 'error',
                'icon' => 'exclamation-circle',
                'color' => 'red'
            ];
        }
        
        foreach ($this->processingErrors as $error) {
            $allErrors[] = [
                'type' => 'processing',
                'message' => $error['message'],
                'severity' => 'error',
                'icon' => 'exclamation-triangle',
                'color' => 'red',
                'category' => $error['category'],
                'timestamp' => $error['timestamp']
            ];
        }
        
        foreach ($this->networkErrors as $error) {
            $allErrors[] = [
                'type' => 'network',
                'message' => $error['message'],
                'severity' => 'error',
                'icon' => 'wifi',
                'color' => 'red',
                'service' => $error['service'],
                'timestamp' => $error['timestamp']
            ];
        }
        
        foreach ($this->systemErrors as $error) {
            $allErrors[] = [
                'type' => 'system',
                'message' => $error['message'],
                'severity' => 'error',
                'icon' => 'cog',
                'color' => 'red',
                'component' => $error['component'],
                'timestamp' => $error['timestamp']
            ];
        }
        
        foreach ($this->warningMessages as $warning) {
            $allErrors[] = [
                'type' => 'warning',
                'message' => $warning['message'],
                'severity' => 'warning',
                'icon' => 'exclamation-triangle',
                'color' => 'yellow',
                'category' => $warning['category'],
                'timestamp' => $warning['timestamp']
            ];
        }
        
        foreach ($this->infoMessages as $info) {
            $allErrors[] = [
                'type' => 'info',
                'message' => $info['message'],
                'severity' => 'info',
                'icon' => 'information-circle',
                'color' => 'red',
                'category' => $info['category'],
                'timestamp' => $info['timestamp']
            ];
        }
        
        foreach ($this->successMessages as $success) {
            $allErrors[] = [
                'type' => 'success',
                'message' => $success['message'],
                'severity' => 'success',
                'icon' => 'check-circle',
                'color' => 'green',
                'category' => $success['category'],
                'timestamp' => $success['timestamp']
            ];
        }
        
        return $allErrors;
    }

    public function checkValidationErrors()
    {
        $errors = [];
        
        if (!$this->tab_id) {
            return;
        }
        
        $loan = DB::table('loans')->find($this->tab_id);
        if (!$loan) {
            return;
        }
        
        $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
        if (!$member) {
            return;
        }
        
        // Check critical member data
        switch ($loan->pay_method) {
            case 'internal_transfer':
                if (empty($member->account_number)) {
                    $errors[] = 'Member NBC account number is missing. Please update member profile.';
                }
                if (empty($this->memberAccountHolderName)) {
                    $errors[] = 'Account holder name is required for internal transfer.';
                }
                break;
                
            case 'tips_mno':
                if (empty($member->phone_number)) {
                    $errors[] = 'Member phone number is missing. Please update member profile.';
                }
                if (empty($this->memberMnoProvider)) {
                    $errors[] = 'MNO provider is required for mobile money transfer.';
                }
                if (empty($this->memberWalletHolderName)) {
                    $errors[] = 'Wallet holder name is required for mobile money transfer.';
                }
                break;
                
            case 'tips_bank':
                if (empty($member->account_number)) {
                    $errors[] = 'Member bank account number is missing. Please update member profile.';
                }
                if (empty($this->memberBankCode)) {
                    $errors[] = 'Bank selection is required for bank transfer.';
                }
                if (empty($this->memberBankAccountHolderName)) {
                    $errors[] = 'Bank account holder name is required for bank transfer.';
                }
                break;
                
            case 'cash':
                $memberDepositAccounts = DB::table('accounts')
                    ->where('client_number', $loan->client_number)
                    ->where('product_number', '3000')
                    ->where('status', 'ACTIVE')
                    ->get();
                    
                if ($memberDepositAccounts->count() === 0) {
                    $errors[] = 'Member has no active deposit accounts. Please create a deposit account before disbursement.';
                } elseif (empty($this->selectedDepositAccount)) {
                    $errors[] = 'Please select a deposit account for cash disbursement.';
                }
                break;
        }
        
        $this->validationErrors = $errors;
    }

    private function loadChargesAndInsurance($productId, $loanAmount, $loan = null)
    {
        try {
            // Get product details
            $product = DB::table('loan_sub_products')->where('sub_product_id', $productId)->first();
            if (!$product) {
                return;
            }

            // Load charges
            $this->loadCharges($product, $loanAmount);
            
            // Load insurance
            $this->loadInsurance($product, $loanAmount, $loan);
            
            // Load account details
            $this->loadAccountDetails($product);
            
        } catch (\Exception $e) {
            Log::error('Error loading charges and insurance: ' . $e->getMessage());
        }
    }

    private function loadCharges($product, $loanAmount)
    {
        $charges = [];
        $totalCharges = 0;

        try {
            // Use the same data source as assessment component - loan_product_charges table
            $chargesList = DB::table('loan_product_charges')
                ->where('loan_product_id', $product->sub_product_id)
                ->where('type', 'charge')
                ->get();

            foreach ($chargesList as $charge) {
                $chargeAmount = $this->calculateChargeAmount($charge, $loanAmount);
                $baseAmount = 0.0;
                $capApplied = null;
                
                // Determine if cap was applied for percentage charges
                if (strtolower((string)$charge->value_type) === 'percentage') {
                    $baseAmount = (float)$loanAmount;
                    $calculatedAmount = ($loanAmount > 0) ? ($loanAmount * ((float)$charge->value) / 100.0) : 0.0;
                    
                    if (!empty($charge->min_cap) && $calculatedAmount < (float)$charge->min_cap) {
                        $capApplied = 'Min cap';
                    } elseif (!empty($charge->max_cap) && $calculatedAmount > (float)$charge->max_cap) {
                        $capApplied = 'Max cap';
                    }
                }
                
                $charges[] = [
                    'name' => $charge->name ?? 'N/A',
                    'type' => $charge->value_type ?? 'Fixed',
                    'value' => (float)($charge->value ?? 0),
                    'amount' => $chargeAmount,
                    'computed_amount' => round($chargeAmount, 2),
                    'description' => $charge->description ?? '',
                    'value_type' => $charge->value_type ?? 'fixed',
                    'base_amount' => $baseAmount,
                    'min_cap' => $charge->min_cap ?? null,
                    'max_cap' => $charge->max_cap ?? null,
                    'cap_applied' => $capApplied
                ];
                $totalCharges += $chargeAmount;
            }

            // Add maintenance fees if applicable
            if ($product->maintenance_fees_value && $product->maintenance_fees_value > 0) {
                $maintenanceFeesValue = round((float)$product->maintenance_fees_value, 2);
                // $charges[] = [
                //     'name' => 'Maintenance Fees',
                //     'type' => 'Fixed',
                //     'value' => $maintenanceFeesValue,
                //     'amount' => $maintenanceFeesValue,
                //     'computed_amount' => $maintenanceFeesValue,
                //     'description' => 'Monthly maintenance fees',
                //     'value_type' => 'fixed',
                //     'base_amount' => 0,
                //     'min_cap' => null,
                //     'max_cap' => null,
                //     'cap_applied' => null
                // ];
                $totalCharges += $maintenanceFeesValue;
            }

            // Add ledger fees if applicable
            if ($product->ledger_fees_value && $product->ledger_fees_value > 0) {
                $ledgerFeesValue = round((float)$product->ledger_fees_value, 2);
                $charges[] = [
                    'name' => 'Ledger Fees',
                    'type' => 'Fixed',
                    'value' => $ledgerFeesValue,
                    'amount' => $ledgerFeesValue,
                    'computed_amount' => $ledgerFeesValue,
                    'description' => 'Ledger maintenance fees',
                    'value_type' => 'fixed',
                    'base_amount' => 0,
                    'min_cap' => null,
                    'max_cap' => null,
                    'cap_applied' => null
                ];
                $totalCharges += $ledgerFeesValue;
            }

            $this->charges = $charges;
            $this->totalCharges = $totalCharges;

            // Debug logging
            Log::info('Charges loaded for disbursement', [
                'product_id' => $product->sub_product_id,
                'loan_amount' => $loanAmount,
                'charges_count' => count($charges),
                'total_charges' => $totalCharges,
                'charges_data' => $charges
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading charges: ' . $e->getMessage());
        }
    }

    private function loadInsurance($product, $loanAmount, $loan = null)
    {
        $insurance = [];
        $totalInsurance = 0;

        try {
            // Use the same data source as assessment component - loan_product_charges table
            $insuranceList = DB::table('loan_product_charges')
                ->where('loan_product_id', $product->sub_product_id)
                ->where('type', 'insurance')
                ->get();

            foreach ($insuranceList as $ins) {
                $insuranceAmount = $this->calculateInsuranceAmount($ins, $loanAmount, $loan);
                $baseAmount = 0.0;
                $capApplied = null;
                $monthlyAmount = 0.0;
                
                // Determine if cap was applied for percentage insurance
                if (strtolower((string)$ins->value_type) === 'percentage') {
                    $baseAmount = (float)$loanAmount;
                    // Calculate monthly amount first
                    $monthlyAmount = ($loanAmount > 0) ? ($loanAmount * ((float)$ins->value) / 100.0) : 0.0;
                    
                    // Note: For insurance, we don't apply caps to the monthly amount
                    // The total insurance is monthly amount × tenure
                }
                
                $insurance[] = [
                    'name' => $ins->name ?? 'N/A',
                    'type' => $ins->value_type ?? 'Fixed',
                    'value' => (float)($ins->value ?? 0),
                    'amount' => $insuranceAmount,
                    'computed_amount' => round($insuranceAmount, 2),
                    'monthly_amount' => round($monthlyAmount, 2),
                    'tenure' => $this->approved_term,
                    'description' => $ins->description ?? '',
                    'value_type' => $ins->value_type ?? 'fixed',
                    'base_amount' => $baseAmount,
                    'min_cap' => $ins->min_cap ?? null,
                    'max_cap' => $ins->max_cap ?? null,
                    'cap_applied' => $capApplied
                ];
                $totalInsurance += $insuranceAmount;
            }

            $this->insurance = $insurance;
            $this->totalInsurance = $totalInsurance;

            // Debug logging
            Log::info('Insurance loaded for disbursement', [
                'product_id' => $product->sub_product_id,
                'loan_amount' => $loanAmount,
                'insurance_count' => count($insurance),
                'total_insurance' => $totalInsurance,
                'insurance_data' => $insurance
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading insurance: ' . $e->getMessage());
        }
    }

    private function loadAccountDetails($product)
    {
        $this->accountDetails = [
            'loan_account' => $product->loan_account ?? $product->loan_product_account ?? 'N/A',
            'disbursement_account' => $product->disbursement_account ?? 'N/A',
            'collection_account_loan_interest' => $product->collection_account_loan_interest ?? 'N/A',
            'collection_account_loan_principle' => $product->collection_account_loan_principle ?? 'N/A',
            'collection_account_loan_charges' => $product->collection_account_loan_charges ?? 'N/A',
            'collection_account_loan_penalties' => $product->collection_account_loan_penalties ?? 'N/A',
            'loan_product_account' => $product->loan_product_account ?? 'N/A',
            'interest_account' => $product->interest_account ?? 'N/A',
            'fees_account' => $product->fees_account ?? 'N/A',
            'payable_account' => $product->payable_account ?? 'N/A',
            'insurance_account' => $product->insurance_account ?? 'N/A',
            'loan_interest_account' => $product->loan_interest_account ?? 'N/A',
            'loan_charges_account' => $product->loan_charges_account ?? 'N/A',
            'loan_insurance_account' => $product->loan_insurance_account ?? 'N/A',
            'charge_product_account' => $product->charge_product_account ?? 'N/A',
            'insurance_product_account' => $product->insurance_product_account ?? 'N/A'
        ];

        Log::info('Account details loaded for loan product', [
            'product_id' => $product->id ?? 'N/A',
            'loan_account' => $this->accountDetails['loan_account'],
            'disbursement_account' => $this->accountDetails['disbursement_account'],
            'interest_account' => $this->accountDetails['interest_account']
        ]);
    }

    private function calculateChargeAmount($charge, $loanAmount)
    {
        try {
            $valueType = strtolower($charge->value_type ?? 'fixed');
            $value = (float)($charge->value ?? 0);
            
            if ($valueType === 'fixed') {
                return round($value, 2);
            } else {
                // Percentage calculation
                $amount = $loanAmount > 0 ? ($loanAmount * $value / 100) : 0;
                
                // Log the calculation for debugging
                Log::info('Charge calculation', [
                    'charge_name' => $charge->name ?? 'Unknown',
                    'loan_amount' => $loanAmount,
                    'percentage' => $value,
                    'calculated_amount' => $amount,
                    'min_cap' => $charge->min_cap ?? null,
                    'max_cap' => $charge->max_cap ?? null
                ]);
                
                // Apply min cap if set
                if (!empty($charge->min_cap) && $amount < (float)$charge->min_cap) {
                    Log::info('Min cap applied', [
                        'charge_name' => $charge->name ?? 'Unknown',
                        'original_amount' => $amount,
                        'min_cap' => (float)$charge->min_cap
                    ]);
                    $amount = (float)$charge->min_cap;
                }
                
                // Apply max cap if set
                if (!empty($charge->max_cap) && $amount > (float)$charge->max_cap) {
                    Log::info('Max cap applied', [
                        'charge_name' => $charge->name ?? 'Unknown',
                        'original_amount' => $amount,
                        'max_cap' => (float)$charge->max_cap
                    ]);
                    $amount = (float)$charge->max_cap;
                }
                
                return round($amount, 2);
            }
        } catch (\Exception $e) {
            Log::error('Error calculating charge amount: ' . $e->getMessage());
            return 0;
        }
    }

    private function calculateInsuranceAmount($insurance, $loanAmount, $loan = null)
    {
        try {
            $valueType = strtolower($insurance->value_type ?? 'fixed');
            $value = (float)($insurance->value ?? 0);
            $tenure = (int)$this->approved_term;
            
            // For restructure loans, calculate insurance based on remaining tenure
            if ($loan && in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring'])) {
                $tenure = $this->calculateInsuranceTenureForRestructure($loan);
            }
            
            if ($valueType === 'fixed') {
                // For fixed insurance, multiply by tenure as well
                return round($value * $tenure, 2);
            } else {
                // Percentage calculation - this is monthly rate
                $monthlyAmount = $loanAmount > 0 ? ($loanAmount * $value / 100) : 0;
                
                // Total insurance = monthly amount × tenure (no caps for insurance)
                $totalAmount = $monthlyAmount * $tenure;
                
                // Log the calculation for debugging
                Log::info('Insurance calculation', [
                    'insurance_name' => $insurance->name ?? 'Unknown',
                    'loan_amount' => $loanAmount,
                    'percentage_per_month' => $value,
                    'monthly_amount' => $monthlyAmount,
                    'tenure_months' => $tenure,
                    'total_insurance' => $totalAmount,
                    'formula' => $value . '% × ' . $loanAmount . ' × ' . $tenure . ' months',
                    'loan_type' => $loan->loan_type_2 ?? 'New',
                    'is_restructure' => in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring'])
                ]);
                
                // Note: We don't apply caps to insurance as per requirement
                // Insurance caps should remain null
                
                return round($totalAmount, 2);
            }
        } catch (\Exception $e) {
            Log::error('Error calculating insurance amount: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate all deductions and additional amounts for loan disbursement
     */
    private function calculateAllDeductions($loan)
    {
        try {
            Log::info('Calculating all deductions for loan disbursement', [
                'loan_id' => $loan->id,
                'approved_loan_value' => $this->approved_loan_value
            ]);

            $breakdown = [];
            $totalDeductions = 0;

            // 1. Charges (Administration Fee, etc.)
            $chargesAmount = $this->calculateTotalCharges();
            $breakdown['charges'] = $chargesAmount;
            $totalDeductions += $chargesAmount;

            // 2. Insurance (Loan Assurance, etc.)
            $insuranceAmount = $this->calculateInsurance();
            $breakdown['insurance'] = $insuranceAmount;
            $totalDeductions += $insuranceAmount;

            // 3. First Interest Amount
            $firstInterestAmount = $this->firstInterestAmount ?? 0;
            $breakdown['first_interest'] = $firstInterestAmount;
            $totalDeductions += $firstInterestAmount;

            // 4. Top-up Amount (if this is a top-up loan)
            $topUpAmount = $this->calculateTopUpAmount($loan);
            $breakdown['top_up_amount'] = $topUpAmount;
            $totalDeductions += $topUpAmount;

            // 5. Closed Loan Balance (for top-up scenarios)
            $closedLoanBalance = $this->calculateClosedLoanBalance($loan);
            $breakdown['closed_loan_balance'] = $closedLoanBalance;
            $totalDeductions += $closedLoanBalance;

            // 6. Outside Loan Settlements
            $outsideSettlements = $this->calculateOutsideSettlements($loan);
            $breakdown['outside_settlements'] = $outsideSettlements;
            $totalDeductions += $outsideSettlements;

            // 7. Restructuring Amount (if this is a restructuring)
            $restructuringAmount = $this->calculateRestructuringAmount($loan);
            $breakdown['restructuring_amount'] = $restructuringAmount;
            $totalDeductions += $restructuringAmount;

            // 8. Early Settlement Penalty (for top-up loans)
            $penaltyAmount = $this->calculatePenaltyAmount($loan);
            $breakdown['penalty_amount'] = $penaltyAmount;
            $totalDeductions += $penaltyAmount;

            // Calculate net disbursement amount
            $netDisbursementAmount = $this->approved_loan_value - $totalDeductions;

            Log::info('Deductions calculation completed', [
                'total_deductions' => $totalDeductions,
                'net_disbursement_amount' => $netDisbursementAmount,
                'breakdown' => $breakdown
            ]);

            return [
                'total_deductions' => $totalDeductions,
                'net_disbursement_amount' => $netDisbursementAmount,
                'breakdown' => $breakdown,
                'charges_amount' => $chargesAmount,
                'insurance_amount' => $insuranceAmount,
                'first_interest_amount' => $firstInterestAmount,
                'top_up_amount' => $topUpAmount,
                'closed_loan_balance' => $closedLoanBalance,
                'outside_settlements' => $outsideSettlements,
                'restructuring_amount' => $restructuringAmount,
                'penalty_amount' => $penaltyAmount
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating deductions', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate top-up amount for existing loan
     * Enhanced to handle multiple loan type variations and data sources
     */
    private function calculateTopUpAmount($loan)
    {
        // Check if this is a top-up loan
        if (!in_array($loan->loan_type_2 ?? '', ['Top-up', 'TopUp', 'Top Up'])) {
            return 0;
        }

        try {
            $topUpAmount = 0;
            
            // Priority 1: Get from top_up_amount field directly
            if (isset($loan->top_up_amount) && $loan->top_up_amount > 0) {
                $topUpAmount = abs($loan->top_up_amount);
            }
            // Priority 2: Calculate from top_up_loan_id (for existing loans)
            elseif (isset($loan->top_up_loan_id) && $loan->top_up_loan_id) {
                $topupLoan = DB::table('loans')->where('id', $loan->top_up_loan_id)->first();
                if ($topupLoan && $topupLoan->loan_account_number) {
                    $topupAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
                    if ($topupAccount) {
                        $topUpAmount = abs($topupAccount->balance ?? 0);
                    }
                }
            }
            // Priority 3: Try selectedLoan field (for new loans)
            elseif ($this->selectedLoan) {
                $topupLoan = DB::table('loans')->where('id', $this->selectedLoan)->first();
                if ($topupLoan && $topupLoan->loan_account_number) {
                    $topupAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
                    if ($topupAccount) {
                        $topUpAmount = abs($topupAccount->balance ?? 0);
                    }
                }
            }
            // Priority 4: Try assessment data
            elseif (isset($loan->assessment_data) && $loan->assessment_data) {
                $assessmentData = json_decode($loan->assessment_data, true);
                if (isset($assessmentData['top_up_amount']) && $assessmentData['top_up_amount'] > 0) {
                    $topUpAmount = abs($assessmentData['top_up_amount']);
                }
            }
            
            Log::info('Top-up amount calculated', [
                'loan_id' => $loan->id,
                'loan_type_2' => $loan->loan_type_2,
                'top_up_amount' => $topUpAmount,
                'top_up_loan_id' => $loan->top_up_loan_id ?? 'NULL',
                'selected_loan' => $this->selectedLoan ?? 'NULL'
            ]);

            return $topUpAmount;

        } catch (\Exception $e) {
            Log::error('Error calculating top-up amount', [
                'loan_id' => $loan->id,
                'selected_loan' => $this->selectedLoan,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }



    /**
     * Calculate closed loan balance for top-up scenarios
     */
    private function calculateClosedLoanBalance($loan)
    {
        if (!$loan->loan_account_number) {
            return 0;
        }

        try {
            $balance = DB::table('sub_accounts')
                ->where('account_number', $loan->loan_account_number)
                ->value('balance') ?? 0;

            Log::info('Closed loan balance calculated', [
                'loan_account_number' => $loan->loan_account_number,
                'balance' => $balance
            ]);

            return $balance;

        } catch (\Exception $e) {
            Log::error('Error calculating closed loan balance', [
                'loan_account_number' => $loan->loan_account_number,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calculate outside loan settlements
     */
    private function calculateOutsideSettlements($loan)
    {
        try {
            $settledLoans = DB::table('settled_loans')
                ->where('loan_id', $loan->id)
                ->where('is_selected', true)
                ->get();

            $totalSettlements = 0;
            foreach ($settledLoans as $settledLoan) {
                $totalSettlements += $settledLoan->amount ?? 0;
            }

            Log::info('Outside settlements calculated', [
                'loan_id' => $loan->id,
                'settled_loans_count' => $settledLoans->count(),
                'total_settlements' => $totalSettlements
            ]);

            return $totalSettlements;

        } catch (\Exception $e) {
            Log::error('Error calculating outside settlements', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calculate restructuring amount
     */
    /**
     * Calculate restructuring amount for restructuring loans
     * Enhanced to handle multiple loan type variations and proper calculation logic
     */
    private function calculateRestructuringAmount($loan)
    {
        // Check if this is a restructuring loan
        if (!in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring'])) {
            return 0;
        }

        try {
            $restructureAmount = 0;
            
            // Priority 1: Get from restructure_amount field directly
            if (isset($loan->restructure_amount) && $loan->restructure_amount > 0) {
                $restructureAmount = abs($loan->restructure_amount);
            }
            // Priority 2: Calculate from restructure_loan_id (outstanding + arrears)
            elseif (isset($loan->restructure_loan_id) && $loan->restructure_loan_id) {
                $originalLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
                if ($originalLoan) {
                    // Get outstanding balance from account
                    $outstandingBalance = 0;
                    if ($originalLoan->loan_account_number) {
                        $account = DB::table('accounts')->where('account_number', $originalLoan->loan_account_number)->first();
                        if ($account) {
                            $outstandingBalance = abs($account->balance ?? 0);
                        }
                    }
                    
                    // Get arrears from loan schedules
                    $arrears = DB::table('loans_schedules')
                        ->where('loan_id', $originalLoan->id)
                        ->where('completion_status', '!=', 'ACTIVE')
                        ->sum('amount_in_arrears') ?? 0;
                    
                    $restructureAmount = $outstandingBalance + $arrears;
                }
            }
            // Priority 3: Try assessment data
            elseif (isset($loan->assessment_data) && $loan->assessment_data) {
                $assessmentData = json_decode($loan->assessment_data, true);
                if (isset($assessmentData['restructure_amount']) && $assessmentData['restructure_amount'] > 0) {
                    $restructureAmount = abs($assessmentData['restructure_amount']);
                }
            }
            
            Log::info('Restructuring amount calculated', [
                'loan_id' => $loan->id,
                'loan_type_2' => $loan->loan_type_2,
                'restructure_amount' => $restructureAmount,
                'restructured_loan_id' => $loan->restructured_loan_id ?? 'NULL'
            ]);

            return $restructureAmount;

        } catch (\Exception $e) {
            Log::error('Error calculating restructuring amount', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calculate early settlement penalty for top-up loans
     */
    private function calculatePenaltyAmount($loan)
    {
        // Check if this is a top-up loan
        if (!in_array($loan->loan_type_2 ?? '', ['Top-up', 'TopUp', 'Top Up'])) {
            return 0;
        }

        try {
            $penaltyAmount = 0;
            
            // Priority 1: Get from top_up_penalty_amount field directly
            if (isset($loan->top_up_penalty_amount) && $loan->top_up_penalty_amount > 0) {
                $penaltyAmount = abs($loan->top_up_penalty_amount);
            }
            // Priority 2: Calculate from top-up amount (5% penalty)
            else {
                $topUpAmount = $this->calculateTopUpAmount($loan);
                if ($topUpAmount > 0) {
                    // Get penalty percentage from product or use default 5%
                    $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
                    $penaltyPercentage = (float)($product->penalty_value ?? 5.0) / 100;
                    $penaltyAmount = $topUpAmount * $penaltyPercentage;
                }
            }
            
            Log::info('Penalty amount calculated', [
                'loan_id' => $loan->id,
                'loan_type_2' => $loan->loan_type_2,
                'penalty_amount' => $penaltyAmount
            ]);

            return $penaltyAmount;

        } catch (\Exception $e) {
            Log::error('Error calculating penalty amount', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calculate insurance tenure for restructure loans
     * Formula: New tenure - Remaining time from original loan
     */
    private function calculateInsuranceTenureForRestructure($loan)
    {
        try {
            // Get the original loan being restructured
            if (!isset($loan->restructure_loan_id) || !$loan->restructure_loan_id) {
                Log::warning('No restructure_loan_id found for restructure loan', [
                    'loan_id' => $loan->id
                ]);
                return (int)$this->approved_term; // Fallback to full tenure
            }

            $originalLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
            if (!$originalLoan) {
                Log::warning('Original loan not found for restructure', [
                    'loan_id' => $loan->id,
                    'restructure_loan_id' => $loan->restructure_loan_id
                ]);
                return (int)$this->approved_term; // Fallback to full tenure
            }

            // Calculate remaining time from original loan
            $now = now();
            $originalStartDate = $originalLoan->disbursement_date 
                ? \Carbon\Carbon::parse($originalLoan->disbursement_date)
                : \Carbon\Carbon::parse($originalLoan->created_at);
            
            $originalEndDate = $originalStartDate->copy()->addMonths($originalLoan->tenure ?? 0);
            $remainingDays = max(0, $now->diffInDays($originalEndDate, false));
            $remainingMonths = max(0, ceil($remainingDays / 30));

            // Calculate insurance tenure: New tenure - Remaining time
            $newTenure = (int)$this->approved_term;
            $insuranceTenure = max(0, $newTenure - $remainingMonths);

            Log::info('Insurance tenure calculation for restructure loan', [
                'loan_id' => $loan->id,
                'original_loan_id' => $originalLoan->id,
                'original_tenure' => $originalLoan->tenure,
                'original_start_date' => $originalStartDate->format('Y-m-d'),
                'original_end_date' => $originalEndDate->format('Y-m-d'),
                'current_date' => $now->format('Y-m-d'),
                'remaining_days' => $remainingDays,
                'remaining_months' => $remainingMonths,
                'new_tenure' => $newTenure,
                'insurance_tenure' => $insuranceTenure,
                'formula' => $newTenure . ' - ' . $remainingMonths . ' = ' . $insuranceTenure . ' months'
            ]);

            return $insuranceTenure;

        } catch (\Exception $e) {
            Log::error('Error calculating insurance tenure for restructure', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            return (int)$this->approved_term; // Fallback to full tenure
        }
    }

    /**
     * Validate sufficient funds for disbursement
     */
    private function validateSufficientFunds($netDisbursementAmount)
    {
        if (!$this->bank_account) {
            return; // No validation needed for cash disbursement
        }

        try {
            $selectedAccount = DB::table('bank_accounts')
                ->where('internal_mirror_account_number', $this->bank_account)
                ->first();

            if (!$selectedAccount) {
                throw new \Exception('Selected disbursement account not found.');
            }

            if ($selectedAccount->current_balance < $netDisbursementAmount) {
                throw new \Exception('Insufficient funds in disbursement account. Available: ' . 
                    number_format($selectedAccount->current_balance, 2) . 
                    ', Required: ' . number_format($netDisbursementAmount, 2));
            }

            Log::info('Sufficient funds validated', [
                'account_number' => $this->bank_account,
                'current_balance' => $selectedAccount->current_balance,
                'required_amount' => $netDisbursementAmount
            ]);

        } catch (\Exception $e) {
            Log::error('Funds validation failed', [
                'bank_account' => $this->bank_account,
                'required_amount' => $netDisbursementAmount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process all loan transactions including deductions
     */
    private function processAllLoanTransactions($loanAccount, $interestAccount, $chargesAccount, $insuranceAccount, $deductions, $loanType = null, $payMethod = null)
    {
        try {
            $transactionService = new TransactionPostingService();
            
            // 1. Process charges
            if ($deductions['charges_amount'] > 0) {
                $this->processChargesTransaction($transactionService, $deductions['charges_amount'],$chargesAccount);
            }
            
            // 2. Process insurance
            if ($deductions['insurance_amount'] > 0) {
                $this->processInsuranceTransaction($transactionService, $deductions['insurance_amount'],$insuranceAccount);
            }
            
            // 3. Process first interest
            if ($deductions['first_interest_amount'] > 0) {
                $this->processFirstInterestTransaction($transactionService, $deductions['first_interest_amount'],$interestAccount);
            }
            
            // 4. Process outside settlements
            if ($deductions['outside_settlements'] > 0) {
                $this->processOutsideSettlementsTransaction($transactionService, $deductions['outside_settlements']);
            }
            
            // 5. Process top-up loan closure
            if ($deductions['top_up_amount'] > 0) {
                $this->processTopUpLoanTransaction($transactionService, $deductions['top_up_amount']);
            }
            
            // 6. Process early settlement penalty (for top-up loans)
            if ($deductions['penalty_amount'] > 0) {
                $this->processPenaltyTransaction($transactionService, $deductions['penalty_amount'], $chargesAccount);
            }
            
            // 7. Process restructuring (no separate transaction - handled in main disbursement)
            // Restructuring loans are processed like new loans, but the main disbursement credits the original loan account
            if ($deductions['restructuring_amount'] > 0) {
                $this->processRestructuringLoanClosure($deductions['restructuring_amount']);
            }

            // 8. Process the main disbursement transaction for all loan types
            // (Previously only processed for 'New' or 'Auto' loan types)
            $this->processMainLoanDisbursementTransaction($transactionService, $deductions['net_disbursement_amount'], $payMethod,$loanAccount);

            Log::info('All loan transactions processed successfully', [
                'loan_type' => $loanType,
                'total_deductions' => $deductions['total_deductions'],
                'net_disbursement_amount' => $deductions['net_disbursement_amount'],
                'breakdown' => $deductions['breakdown']
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing all loan transactions', [
                'loan_type' => $loanType,
                'deductions' => $deductions,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update loan record with transaction details using documented response format
     * 
     * @param int $loanID
     * @param array $result
     * @return void
     */
    /*
    private function updateLoanWithTransactionDetails($loanID, $result)
    {
        $updateData = [
            'transaction_reference' => $result['referenceNumber'] ?? null,
            'external_transaction_reference' => $result['externalReferenceNumber'] ?? null,
            'correlation_id' => $result['correlationId'] ?? null,
            'transaction_status' => 'PROCESSED',
            'transaction_processed_at' => now(),
            'processing_time_ms' => $result['processingTimeMs'] ?? null
        ];

        DB::table('loans')->where('id', $loanID)->update($updateData);

        Log::info('Loan updated with transaction details', [
            'loan_id' => $loanID,
            'referenceNumber' => $updateData['transaction_reference'],
            'externalReferenceNumber' => $updateData['external_transaction_reference'],
            'correlationId' => $updateData['correlation_id'],
            'processingTimeMs' => $updateData['processing_time_ms']
        ]);
    }
    */

    /**
     * Process main loan disbursement transaction
     * Cash transactions use TransactionPostingService, others use TransactionProcessingService
     * 
     * @param object $transactionService TransactionPostingService for ledger posting
     * @param float $netDisbursementAmount Net amount after deductions
     * @param string $payMethod Payment method
     * @throws \Exception
     */
    private function processMainLoanDisbursementTransaction($transactionService, $netDisbursementAmount, $payMethod,$loanAccount)
    {

        //dd($payMethod,$netDisbursementAmount,$transactionService);
        try {
            $loanID = session('currentloanID');
            //dd($loanID);
            if (!$loanID) {
                throw new \Exception('No loan ID in session for main disbursement transaction.');
            }

           // dd($payMethod,$netDisbursementAmount,$transactionService,$loanID);
            // Get the loan account number from the loan record
            $loan = DB::table('loans')->where('id', $loanID)->first();
            if (!$loan || !$loan->loan_account_number) {
                throw new \Exception('Loan account number not found for main disbursement transaction.');
            }

            // Get member details
            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$member) {
                throw new \Exception('Member not found for main disbursement transaction.');
            }

            Log::info('Processing main loan disbursement', [
                'loan_id' => $loanID,
                'payment_method' => $payMethod,
                'amount' => $netDisbursementAmount,
                'loan_account' => $loan->loan_account_number
            ]);

            // Since external transaction was already processed in STEP 2, 
            // we only need to post to ledger for accounting purposes
            if ($payMethod === 'cash') {
                // For cash, process the disbursement to member's deposit account
                $result = $this->processCashDisbursement(
                    (object)['account_number' => $loan->loan_account_number],
                    $netDisbursementAmount,
                    $member->present_surname
                );
            } else {
                // For external payment methods, the transaction was already processed
                // We only need to post to ledger for accounting
                $result = [
                    'success' => true,
                    'referenceNumber' => 'POST_TRANSACTION_' . time(),
                    'externalReferenceNumber' => 'EXTERNAL_ALREADY_PROCESSED',
                    'should_post_to_ledger' => true
                ];
                
                // Post to ledger for accounting purposes
                $this->postToLedger($transactionService, $loan, $netDisbursementAmount, $payMethod, $result);
            }

            Log::info('Main loan disbursement transaction completed successfully', [
                'loan_id' => $loanID,
                'payment_method' => $payMethod,
                'amount' => $netDisbursementAmount,
                'transaction_reference' => $result['referenceNumber'] ?? $result['reference_number'] ?? null,
                'external_reference' => $result['externalReferenceNumber'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing main loan disbursement transaction', [
                'loan_id' => session('currentloanID'),
                'payment_method' => $payMethod,
                'amount' => $netDisbursementAmount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Post transaction to ledger for accounting purposes
     * 
     * @param object $transactionService
     * @param object $loan
     * @param float $amount
     * @param string $payMethod
     * @param array $tpsResult
     * @throws \Exception
     */
    private function postToLedger($transactionService, $loan, $amount, $payMethod, $tpsResult)
    {
        try {
            // Check if this is a restructuring loan
            $isRestructuringLoan = in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring']);
            $originalLoanAccount = null;
            
            if ($isRestructuringLoan && $loan->restructure_loan_id) {
                $originalLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
                if ($originalLoan) {
                    $originalLoanAccount = $originalLoan->loan_account_number;
                }
            }
            
            // Determine transaction accounts based on payment method and loan type
            if ($payMethod === 'cash') {
                // For CASH: Debit loan account, credit member's deposit account
                if (empty($this->selectedDepositAccount)) {
                    throw new \Exception('Deposit account not selected for cash disbursement.');
                }
                
                $transactionData = [
                    'first_account' => $loan->loan_account_number, // Debit loan account
                    'second_account' => $this->selectedDepositAccount, // Credit member's deposit account
                    'amount' => $amount,
                    'narration' => 'Cash loan disbursement - Net amount after deductions',
                    'action' => 'cash_loan_disbursement'
                ];
            } else {
                // For restructuring loans: Credit original loan account instead of bank account
                if ($isRestructuringLoan && $originalLoanAccount) {
                    $transactionData = [
                        'first_account' => $loan->loan_account_number, // Debit new loan account
                        'second_account' => $originalLoanAccount, // Credit original loan account
                        'amount' => $amount,
                        'narration' => 'Restructuring loan disbursement - Net amount after deductions',
                        'action' => 'restructuring_loan_disbursement'
                    ];
                } else {
                    // For all other payment methods: Credit selected disbursement account, debit loan account
                    if (empty($this->bank_account)) {
                        throw new \Exception('Disbursement account not selected for disbursement.');
                    }
                    
                    $transactionData = [
                        'first_account' => $loan->loan_account_number, // Debit loan account
                        'second_account' => $this->bank_account, // Credit selected disbursement account
                        'amount' => $amount,
                        'narration' => 'Loan disbursement - Net amount after deductions',
                        'action' => 'loan_disbursement'
                    ];
                }
            }

            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['status'] !== 'success') {
                throw new \Exception('Failed to post to ledger: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('Transaction posted to ledger successfully', [
                'loan_id' => $loan->id,
                'payment_method' => $payMethod,
                'amount' => $amount,
                'ledger_reference' => $result['reference_number'] ?? null,
                'tps_reference' => $tpsResult['transaction']['reference'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Error posting to ledger', [
                'loan_id' => $loan->id,
                'payment_method' => $payMethod,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process external payment services before posting main transaction
     * 
     * @param string $payMethod
     * @param float $amount
     * @param object $loan
     * @throws \Exception
     */
    private function processExternalPaymentService($payMethod, $amount, $loan)
    {
        $maxRetries = 3;
        $retryCount = 0;
        $lastError = null;

        while ($retryCount < $maxRetries) {
            try {
                Log::info('Processing external payment service', [
                    'payment_method' => $payMethod,
                    'amount' => $amount,
                    'loan_id' => $loan->id,
                    'attempt' => $retryCount + 1,
                    'max_retries' => $maxRetries
                ]);

                switch ($payMethod) {
                    case 'internal_transfer':
                        $this->processInternalTransferService($amount, $loan);
                        break;
                        
                    case 'tips_mno':
                        $this->processTipsMnoService($amount, $loan);
                        break;
                        
                    case 'tips_bank':
                        $this->processTipsBankService($amount, $loan);
                        break;
                        
                    default:
                        throw new \Exception('Unsupported payment method for external processing: ' . $payMethod);
                }

                Log::info('External payment service processed successfully', [
                    'payment_method' => $payMethod,
                    'amount' => $amount,
                    'attempt' => $retryCount + 1
                ]);

                // Success - break out of retry loop
                return;

            } catch (\Exception $e) {
                $retryCount++;
                $lastError = $e;

                Log::error('External payment service processing failed', [
                    'payment_method' => $payMethod,
                    'amount' => $amount,
                    'attempt' => $retryCount,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // If this is the last attempt, handle the failure
                if ($retryCount >= $maxRetries) {
                    $this->handleExternalServiceFailure($payMethod, $amount, $loan, $lastError);
                } else {
                    // Wait before retrying (exponential backoff)
                    $waitTime = pow(2, $retryCount) * 1000000; // microseconds
                    Log::info('Retrying external service in ' . ($waitTime / 1000000) . ' seconds', [
                        'payment_method' => $payMethod,
                        'attempt' => $retryCount + 1
                    ]);
                    usleep($waitTime);
                }
            }
        }
    }

    /**
     * Handle external service failure with fallback options
     * 
     * @param string $payMethod
     * @param float $amount
     * @param object $loan
     * @param \Exception $error
     * @throws \Exception
     */
    private function handleExternalServiceFailure($payMethod, $amount, $loan, $error)
    {
        Log::error('External service failed after all retries', [
            'payment_method' => $payMethod,
            'amount' => $amount,
            'loan_id' => $loan->id,
            'error' => $error->getMessage()
        ]);

        // Add network error to UI (user-friendly message)
        $this->addNetworkError(
            "Payment service is temporarily unavailable. Please try again in a few minutes or contact support if the issue persists.",
            $payMethod
        );

        // Get member details for notifications
        $member = DB::table('clients')->where('client_number', $loan->client_number)->first();

        // Determine fallback strategy based on payment method
        switch ($payMethod) {
            case 'internal_transfer':
                $this->handleInternalTransferFailure($amount, $loan, $member, $error);
                break;
                
            case 'tips_mno':
                $this->handleTipsMnoFailure($amount, $loan, $member, $error);
                break;
                
            case 'tips_bank':
                $this->handleTipsBankFailure($amount, $loan, $member, $error);
                break;
                
            default:
                $this->addSystemError('Unsupported payment method for failure handling: ' . $payMethod, 'payment_processing');
                throw new \Exception('Unsupported payment method for failure handling: ' . $payMethod);
        }
    }

    /**
     * Handle internal transfer failure
     * 
     * @param float $amount
     * @param object $loan
     * @param object $member
     * @param \Exception $error
     * @throws \Exception
     */
    private function handleInternalTransferFailure($amount, $loan, $member, $error)
    {
        // Check if member has deposit accounts for fallback to cash
        $depositAccounts = DB::table('accounts')
            ->where('client_number', $loan->client_number)
            ->where('product_number', '3000')
            ->where('status', 'ACTIVE')
            ->get();

        if ($depositAccounts->count() > 0) {
            Log::info('Internal transfer failed, offering cash disbursement fallback', [
                'loan_id' => $loan->id,
                'member' => $member->present_surname ?? 'Unknown',
                'available_deposit_accounts' => $depositAccounts->count()
            ]);

            // Update loan status to indicate external service failure
            DB::table('loans')->where('id', $loan->id)->update([
                'status' => 'EXTERNAL_SERVICE_FAILED',
                'disbursement_failure_reason' => 'Internal transfer failed: ' . $error->getMessage(),
                'disbursement_failure_date' => now(),
                'disbursement_failure_payment_method' => 'internal_transfer'
            ]);

            // Add processing error with fallback option (user-friendly message)
            $this->addProcessingError(
                'Internal transfer is temporarily unavailable. Cash disbursement is available as an alternative option.',
                'payment_fallback'
            );

            // Send notification to member about the failure and fallback option
            $this->sendExternalServiceFailureNotification($member, $loan, 'internal_transfer', $error, 'cash');

            throw new \Exception(
                'Internal transfer failed after multiple attempts. ' .
                'The loan has been marked for manual review. ' .
                'Member has deposit accounts available for cash disbursement. ' .
                'Error: ' . $error->getMessage()
            );

        } else {
            Log::error('Internal transfer failed and no deposit accounts available for fallback', [
                'loan_id' => $loan->id,
                'member' => $member->present_surname ?? 'Unknown'
            ]);

            // Update loan status
            DB::table('loans')->where('id', $loan->id)->update([
                'status' => 'EXTERNAL_SERVICE_FAILED',
                'disbursement_failure_reason' => 'Internal transfer failed and no fallback available: ' . $error->getMessage(),
                'disbursement_failure_date' => now(),
                'disbursement_failure_payment_method' => 'internal_transfer'
            ]);

            // Add processing error with no fallback
            $this->addProcessingError(
                'Internal transfer failed and no fallback options available. Member needs to create a deposit account or provide alternative payment details.',
                'payment_no_fallback'
            );

            // Send notification to member about the failure
            $this->sendExternalServiceFailureNotification($member, $loan, 'internal_transfer', $error, null);

            throw new \Exception(
                'Internal transfer failed after multiple attempts and no fallback options available. ' .
                'The loan has been marked for manual review. ' .
                'Member needs to create a deposit account or provide alternative payment details. ' .
                'Error: ' . $error->getMessage()
            );
        }
    }

    /**
     * Handle TIPS MNO failure
     * 
     * @param float $amount
     * @param object $loan
     * @param object $member
     * @param \Exception $error
     * @throws \Exception
     */
    private function handleTipsMnoFailure($amount, $loan, $member, $error)
    {
        Log::error('TIPS MNO transfer failed after all retries', [
            'loan_id' => $loan->id,
            'member' => $member->present_surname ?? 'Unknown',
            'phone_number' => $this->maskPhoneNumber($this->memberPhoneNumber ?? ''),
            'mno_provider' => $this->memberMnoProvider ?? 'Unknown'
        ]);

        // Update loan status
        DB::table('loans')->where('id', $loan->id)->update([
            'status' => 'EXTERNAL_SERVICE_FAILED',
            'disbursement_failure_reason' => 'TIPS MNO transfer failed: ' . $error->getMessage(),
            'disbursement_failure_date' => now(),
            'disbursement_failure_payment_method' => 'tips_mno'
        ]);

        // Add processing error for TIPS MNO
        $this->addProcessingError(
            'TIPS MNO transfer failed. Please verify the phone number and MNO provider details.',
            'tips_mno_failure'
        );

        // Send notification to member about the failure
        $this->sendExternalServiceFailureNotification($member, $loan, 'tips_mno', $error, null);

        throw new \Exception(
            'TIPS MNO transfer failed after multiple attempts. ' .
            'The loan has been marked for manual review. ' .
            'Please verify the phone number and MNO provider details. ' .
            'Error: ' . $error->getMessage()
        );
    }

    /**
     * Handle TIPS Bank failure
     * 
     * @param float $amount
     * @param object $loan
     * @param object $member
     * @param \Exception $error
     * @throws \Exception
     */
    private function handleTipsBankFailure($amount, $loan, $member, $error)
    {
        Log::error('TIPS Bank transfer failed after all retries', [
            'loan_id' => $loan->id,
            'member' => $member->present_surname ?? 'Unknown',
            'bank_account' => $this->maskAccountNumber($this->memberBankAccountNumber ?? ''),
            'bank_code' => $this->memberBankCode ?? 'Unknown'
        ]);

        // Update loan status
        DB::table('loans')->where('id', $loan->id)->update([
            'status' => 'EXTERNAL_SERVICE_FAILED',
            'disbursement_failure_reason' => 'TIPS Bank transfer failed: ' . $error->getMessage(),
            'disbursement_failure_date' => now(),
            'disbursement_failure_payment_method' => 'tips_bank'
        ]);

        // Add processing error for TIPS Bank
        $this->addProcessingError(
            'TIPS Bank transfer failed. Please verify the bank account number and bank code details.',
            'tips_bank_failure'
        );

        // Send notification to member about the failure
        $this->sendExternalServiceFailureNotification($member, $loan, 'tips_bank', $error, null);

        throw new \Exception(
            'TIPS Bank transfer failed after multiple attempts. ' .
            'The loan has been marked for manual review. ' .
            'Please verify the bank account number and bank code details. ' .
            'Error: ' . $error->getMessage()
        );
    }

    /**
     * Send notification about external service failure
     * 
     * @param object $member
     * @param object $loan
     * @param string $paymentMethod
     * @param \Exception $error
     * @param string|null $fallbackOption
     */
    private function sendExternalServiceFailureNotification($member, $loan, $paymentMethod, $error, $fallbackOption = null)
    {
        try {
            // Log the notification attempt
            Log::info('Sending external service failure notification', [
                'member' => $member->present_surname ?? 'Unknown',
                'loan_id' => $loan->id,
                'payment_method' => $paymentMethod,
                'fallback_option' => $fallbackOption
            ]);

            // Send email notification
            if ($member->email) {
                // You can implement email notification here
                // Mail::to($member->email)->send(new ExternalServiceFailureEmail($member, $loan, $paymentMethod, $error, $fallbackOption));
                Log::info('External service failure email notification sent', [
                    'email' => $member->email,
                    'member' => $member->present_surname
                ]);
            }

            // Send SMS notification
            if ($member->phone_number) {
                // You can implement SMS notification here
                // $smsService = new SmsService();
                // $smsService->sendExternalServiceFailureSms($member, $loan, $paymentMethod, $error, $fallbackOption);
                Log::info('External service failure SMS notification sent', [
                    'phone' => $this->maskPhoneNumber($member->phone_number),
                    'member' => $member->present_surname
                ]);
            }

            // Send internal notification to admin/staff
            $this->sendInternalFailureNotification($member, $loan, $paymentMethod, $error, $fallbackOption);

        } catch (\Exception $e) {
            Log::error('Failed to send external service failure notification', [
                'member' => $member->present_surname ?? 'Unknown',
                'loan_id' => $loan->id,
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send internal notification to admin/staff about external service failure
     * 
     * @param object $member
     * @param object $loan
     * @param string $paymentMethod
     * @param \Exception $error
     * @param string|null $fallbackOption
     */
    private function sendInternalFailureNotification($member, $loan, $paymentMethod, $error, $fallbackOption = null)
    {
        try {
            // Log for admin review
            Log::warning('EXTERNAL SERVICE FAILURE - REQUIRES MANUAL REVIEW', [
                'loan_id' => $loan->id,
                'member_name' => $member->present_surname ?? 'Unknown',
                'member_number' => $member->client_number ?? 'Unknown',
                'payment_method' => $paymentMethod,
                'amount' => $loan->approved_loan_value ?? 'Unknown',
                'error_message' => $error->getMessage(),
                'fallback_option' => $fallbackOption,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            // You can implement additional internal notifications here:
            // - Slack/Discord webhook
            // - Email to admin team
            // - Dashboard alerts
            // - SMS to admin phone numbers

        } catch (\Exception $e) {
            Log::error('Failed to send internal failure notification', [
                'loan_id' => $loan->id,
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process internal transfer via NBC Internal Fund Transfer Service
     * 
     * @param float $amount
     * @param object $loan
     * @throws \Exception
     */
    private function processInternalTransferService($amount, $loan)
    {
        try {
            // Validate required data
            if (empty($this->memberNbcAccount)) {
                throw new \Exception('NBC account number not provided for internal transfer.');
            }

            if (empty($this->bank_account)) {
                throw new \Exception('Bank account not selected for internal transfer.');
            }

            // Get member details
            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$member) {
                throw new \Exception('Member not found for internal transfer.');
            }

            // Initialize Internal Fund Transfer Service
            $internalTransferService = new \App\Services\NbcPayments\InternalFundTransferService();

            // Prepare transfer data
            $transferData = [
                'creditAccount' => $this->memberNbcAccount,
                'creditCurrency' => 'TZS',
                'debitAccount' => $this->bank_account,
                'debitCurrency' => 'TZS',
                'amount' => number_format($amount, 2, '.', ''),
                'narration' => 'Loan disbursement to ' . $member->present_surname . ' - Loan ID: ' . $loan->id,
                'pyrName' => $member->present_surname ?? 'NBC Member'
            ];

            Log::info('Initiating internal transfer', [
                'credit_account' => $this->maskAccountNumber($this->memberNbcAccount),
                'debit_account' => $this->maskAccountNumber($this->bank_account),
                'amount' => $amount,
                'member' => $member->present_surname
            ]);

            // Process the transfer
            $result = $internalTransferService->processInternalTransfer($transferData);

            if (!$result['success']) {
                throw new \Exception('Internal transfer failed: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('Internal transfer completed successfully', [
                'reference' => $result['reference'] ?? 'N/A',
                'status' => $result['status'] ?? 'N/A'
            ]);

        } catch (\Exception $e) {
            Log::error('Internal transfer service failed', [
                'amount' => $amount,
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process TIPS MNO (Mobile Money) transfer
     * 
     * @param float $amount
     * @param object $loan
     * @throws \Exception
     */
    private function processTipsMnoService($amount, $loan)
    {
        try {
            // Validate required data
            if (empty($this->memberPhoneNumber)) {
                throw new \Exception('Phone number not provided for TIPS MNO transfer.');
            }

            if (empty($this->memberMnoProvider)) {
                throw new \Exception('MNO provider not selected for TIPS MNO transfer.');
            }

            if (empty($this->bank_account)) {
                throw new \Exception('Bank account not selected for TIPS MNO transfer.');
            }

            // Get member details
            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$member) {
                throw new \Exception('Member not found for TIPS MNO transfer.');
            }

            // Initialize NBC Payment Service
            $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();

            // Prepare transfer payload for Bank-to-Wallet transfer
            $transferPayload = [
                'payerDetails' => [
                    'identifierType' => 'BANK',
                    'identifier' => $this->bank_account,
                    'phoneNumber' => $this->memberPhoneNumber,
                    'initiatorId' => 'NBC_' . auth()->id(),
                    'branchCode' => substr($this->bank_account, 0, 3),
                    'fspId' => '015', // NBC's FSP ID
                    'fullName' => $member->present_surname ?? 'NBC Member',
                    'accountCategory' => 'PERSON',
                    'accountType' => 'BANK',
                    'identity' => [
                        'type' => '',
                        'value' => ''
                    ]
                ],
                'payeeDetails' => [
                    'identifierType' => 'WALLET',
                    'identifier' => $this->memberPhoneNumber,
                    'fspId' => $this->memberMnoProvider,
                    'destinationFsp' => $this->memberMnoProvider,
                    'fullName' => $member->present_surname ?? 'NBC Member',
                    'accountCategory' => 'PERSON',
                    'accountType' => 'WALLET',
                    'identity' => [
                        'type' => '',
                        'value' => ''
                    ]
                ],
                'transactionDetails' => [
                    'debitAmount' => number_format($amount, 2, '.', ''),
                    'debitCurrency' => 'TZS',
                    'creditAmount' => number_format($amount, 2, '.', ''),
                    'creditCurrency' => 'TZS',
                    'productCode' => 'FTLC',
                    'isServiceChargeApplicable' => true,
                    'serviceChargeBearer' => 'OUR'
                ],
                'remarks' => 'Loan disbursement to ' . $member->present_surname . ' - Loan ID: ' . $loan->id
            ];

            Log::info('Initiating TIPS MNO transfer', [
                'phone_number' => $this->maskPhoneNumber($this->memberPhoneNumber),
                'mno_provider' => $this->memberMnoProvider,
                'amount' => $amount,
                'member' => $member->present_surname
            ]);

            // Process the transfer using sendTransfer method
            $result = $nbcPaymentService->sendTransfer($transferPayload);

            if (!$result['success']) {
                throw new \Exception('TIPS MNO transfer failed: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('TIPS MNO transfer completed successfully', [
                'reference' => $result['reference'] ?? 'N/A',
                'status' => $result['status'] ?? 'N/A'
            ]);

        } catch (\Exception $e) {
            Log::error('TIPS MNO service failed', [
                'amount' => $amount,
                'loan_id' => $loan->id,
                'phone_number' => $this->maskPhoneNumber($this->memberPhoneNumber ?? ''),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process TIPS Bank transfer
     * 
     * @param float $amount
     * @param object $loan
     * @throws \Exception
     */
    private function processTipsBankService($amount, $loan)
    {
        try {
            // Validate required data
            if (empty($this->memberBankAccountNumber)) {
                throw new \Exception('Bank account number not provided for TIPS Bank transfer.');
            }

            if (empty($this->memberBankCode)) {
                throw new \Exception('Bank code not selected for TIPS Bank transfer.');
            }

            if (empty($this->bank_account)) {
                throw new \Exception('Bank account not selected for TIPS Bank transfer.');
            }

            // Get member details
            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$member) {
                throw new \Exception('Member not found for TIPS Bank transfer.');
            }

            // Initialize NBC Payment Service
            $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();

            // First, perform bank lookup
            Log::info('Performing TIPS Bank lookup', [
                'bank_account' => $this->maskAccountNumber($this->memberBankAccountNumber),
                'bank_code' => $this->memberBankCode
            ]);

            $lookupResult = $nbcPaymentService->bankToBankLookup(
                $this->memberBankAccountNumber,
                $this->memberBankCode,
                $this->bank_account,
                number_format($amount, 2, '.', ''),
                'PERSON'
            );

            if (!$lookupResult['success']) {
                throw new \Exception('TIPS Bank lookup failed: ' . ($lookupResult['message'] ?? 'Unknown error'));
            }

            Log::info('TIPS Bank lookup successful', [
                'account_name' => $lookupResult['body']['fullName'] ?? 'N/A',
                'bank_name' => $lookupResult['body']['bankName'] ?? 'N/A'
            ]);

            // Process Bank-to-Bank transfer
            $transferResult = $nbcPaymentService->processBankToBankTransfer(
                $lookupResult,
                $this->bank_account,
                number_format($amount, 2, '.', ''),
                $this->memberPhoneNumber ?? $member->phone_number ?? '',
                'NBC_' . auth()->id(), // Initiator ID
                'Loan disbursement to ' . $member->present_surname . ' - Loan ID: ' . $loan->id,
                'FTLC' // Product code for loan disbursement
            );

            if (!$transferResult['success']) {
                throw new \Exception('TIPS Bank transfer failed: ' . ($transferResult['message'] ?? 'Unknown error'));
            }

            Log::info('TIPS Bank transfer completed successfully', [
                'reference' => $transferResult['reference'] ?? 'N/A',
                'status' => $transferResult['status'] ?? 'N/A'
            ]);

        } catch (\Exception $e) {
            Log::error('TIPS Bank service failed', [
                'amount' => $amount,
                'loan_id' => $loan->id,
                'bank_account' => $this->maskAccountNumber($this->memberBankAccountNumber ?? ''),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mask account number for logging (show only last 4 digits)
     * 
     * @param string $accountNumber
     * @return string
     */
    private function maskAccountNumber($accountNumber)
    {
        if (empty($accountNumber) || strlen($accountNumber) < 4) {
            return '****';
        }
        return str_repeat('*', strlen($accountNumber) - 4) . substr($accountNumber, -4);
    }

    /**
     * Mask phone number for logging (show only last 4 digits)
     * 
     * @param string $phoneNumber
     * @return string
     */
    private function maskPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber) || strlen($phoneNumber) < 4) {
            return '****';
        }
        return str_repeat('*', strlen($phoneNumber) - 4) . substr($phoneNumber, -4);
    }

    /**
     * Process charges transaction
     * Credit bank account, debit charges account from loan sub-product
     */
    private function processChargesTransaction($transactionService, $amount, $chargesAccount = null)
    {
        // Use selected disbursement account from modal
        if (empty($this->bank_account)) {
            throw new \Exception('Disbursement account not selected for charges transaction.');
        }
        
        // Use the passed chargesAccount if available, otherwise use the one from accountDetails
        $chargesAccountNumber = $chargesAccount ? $chargesAccount->account_number : $this->accountDetails['loan_charges_account'];
        
        // Add leading zero if account is 15 digits (common issue with varchar storage)
        if ($chargesAccountNumber && $chargesAccountNumber !== 'N/A' && strlen($chargesAccountNumber) == 15) {
            $chargesAccountNumber = '0' . $chargesAccountNumber;
        }
        
        $transactionData = [
            'first_account' => $chargesAccountNumber, // Debit charges account from loan sub-product
            'second_account' => $this->bank_account, // Credit selected disbursement account
            'amount' => $amount,
            'narration' => 'Loan charges collection for loan disbursement',
            'action' => 'loan_charges_collection'
        ];

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post charges transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Charges transaction processed successfully', [
            'amount' => $amount,
            'debit_account' => $chargesAccountNumber,
            'credit_account' => $this->bank_account
        ]);
    }

    /**
     * Process insurance transaction
     * Credit bank account, debit insurance account from loan sub-product
     */
    private function processInsuranceTransaction($transactionService, $amount, $insuranceAccount = null)
    {
        // Use selected disbursement account from modal
        if (empty($this->bank_account)) {
            throw new \Exception('Disbursement account not selected for insurance transaction.');
        }
        
        // Use the passed insuranceAccount if available, otherwise use the one from accountDetails
        $insuranceAccountNumber = $insuranceAccount ? $insuranceAccount->account_number : $this->accountDetails['loan_insurance_account'];
        
        // Add leading zero if account is 15 digits (common issue with varchar storage)
        if ($insuranceAccountNumber && $insuranceAccountNumber !== 'N/A' && strlen($insuranceAccountNumber) == 15) {
            $insuranceAccountNumber = '0' . $insuranceAccountNumber;
        }
        
        $transactionData = [
            'first_account' => $insuranceAccountNumber, // Debit insurance account from loan sub-product
            'second_account' => $this->bank_account, // Credit selected disbursement account
            'amount' => $amount,
            'narration' => 'Loan insurance collection for loan disbursement',
            'action' => 'loan_insurance_collection'
        ];

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post insurance transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Insurance transaction processed successfully', [
            'amount' => $amount,
            'debit_account' => $insuranceAccountNumber,
            'credit_account' => $this->bank_account
        ]);
    }

    /**
     * Process early settlement penalty transaction
     * Credit bank account, debit charges account (treated like charges/fees)
     */
    private function processPenaltyTransaction($transactionService, $amount, $chargesAccount)
    {
        // Use selected disbursement account from modal
        if (empty($this->bank_account)) {
            throw new \Exception('Disbursement account not selected for penalty transaction.');
        }
        
        $transactionData = [
            'first_account' => $chargesAccount->account_number, // Debit charges account
            'second_account' => $this->bank_account, // Credit selected disbursement account
            'amount' => $amount,
            'narration' => 'Early settlement penalty collection for loan disbursement',
            'action' => 'early_settlement_penalty_collection'
        ];

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post penalty transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Early settlement penalty transaction processed successfully', [
            'amount' => $amount,
            'debit_account' => $chargesAccount->account_number,
            'credit_account' => $this->bank_account
        ]);
    }

    /**
     * Process first interest transaction
     * Credit bank account, debit interest account from loan sub-product
     */
    private function processFirstInterestTransaction($transactionService, $amount, $interestAccount = null)
    {
        // Use selected disbursement account from modal
        if (empty($this->bank_account)) {
            throw new \Exception('Disbursement account not selected for first interest transaction.');
        }
        
        // Use the passed interestAccount if available, otherwise use the one from accountDetails
        $interestAccountNumber = $interestAccount ? $interestAccount->account_number : $this->accountDetails['loan_interest_account'];
        
        // Add leading zero if account is 15 digits (common issue with varchar storage)
        if ($interestAccountNumber && $interestAccountNumber !== 'N/A' && strlen($interestAccountNumber) == 15) {
            $interestAccountNumber = '0' . $interestAccountNumber;
        }
        
        $transactionData = [
            'first_account' => $interestAccountNumber, // Debit interest account from loan sub-product
            'second_account' => $this->bank_account, // Credit selected disbursement account
            'amount' => $amount,
            'narration' => 'First interest collection for loan disbursement',
            'action' => 'loan_first_interest_collection'
        ];

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post first interest transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('First interest transaction processed successfully', [
            'amount' => $amount,
            'debit_account' => $interestAccountNumber,
            'credit_account' => $this->bank_account
        ]);
    }

    /**
     * Process outside settlements transaction
     * Credit bank account, debit liability account from institutions table
     */
    private function processOutsideSettlementsTransaction($transactionService, $amount)
    {
        try {
            // Use selected disbursement account from modal
            if (empty($this->bank_account)) {
                throw new \Exception('Disbursement account not selected for outside settlements transaction.');
            }
            
            // Get the liability account from institutions table
            $liabilityAccount = DB::table('institutions')
                ->where('id', 1)
                ->value('members_external_loans_crealance');

            if (!$liabilityAccount) {
                throw new \Exception('Liability account not found in institutions table for external loan settlements.');
            }

            $transactionData = [
                'first_account' => $liabilityAccount, // Debit liability account from institutions table
                'second_account' => $this->bank_account, // Credit selected disbursement account
                'amount' => $amount,
                'narration' => 'Outside loan settlements for loan disbursement',
                'action' => 'outside_loan_settlements'
            ];

            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['status'] !== 'success') {
                throw new \Exception('Failed to post outside settlements transaction: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('Outside settlements transaction processed successfully', [
                'amount' => $amount,
                'debit_account' => $liabilityAccount,
                'credit_account' => $this->bank_account,
                'institution_id' => 1
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing outside settlements transaction', [
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process top-up loan transaction
     * Credit bank account, debit loan account being topped up
     */
    private function processTopUpLoanTransaction($transactionService, $amount)
    {
        try {
            $loanID = session('currentloanID');
            if (!$loanID) {
                throw new \Exception('No loan ID in session for top-up transaction.');
            }

            // Get the current loan (the new top-up loan)
            $currentLoan = DB::table('loans')->where('id', $loanID)->first();
            if (!$currentLoan) {
                throw new \Exception('Current loan not found.');
            }

            // Get the original loan being topped up
            $originalLoan = null;
            if ($currentLoan->top_up_loan_id) {
                $originalLoan = DB::table('loans')->where('id', $currentLoan->top_up_loan_id)->first();
            } elseif ($this->selectedLoan) {
                $originalLoan = DB::table('loans')->where('id', $this->selectedLoan)->first();
            }

            if (!$originalLoan) {
                throw new \Exception('Original loan for top-up not found.');
            }

            // Use selected disbursement account from modal
            if (empty($this->bank_account)) {
                throw new \Exception('Disbursement account not selected for top-up transaction.');
            }
            $bankAccount = $this->bank_account;

            Log::info('Processing top-up loan transaction', [
                'current_loan_id' => $loanID,
                'original_loan_id' => $originalLoan->id,
                'top_up_amount' => $amount,
                'original_loan_account' => $originalLoan->loan_account_number,
                'bank_account' => $bankAccount
            ]);

            // Process the top-up loan balance transaction
            // Credit bank account, debit original loan account
            $transactionData = [
                'first_account' => $originalLoan->loan_account_number, // Debit original loan account
                'second_account' => $bankAccount, // Credit bank account
                'amount' => $amount,
                'narration' => 'Top-up loan balance settlement for loan ID ' . $currentLoan->loan_id,
                'action' => 'top_up_loan_balance_settlement'
            ];

            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['status'] !== 'success') {
                throw new \Exception('Failed to post top-up loan balance transaction: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('Top-up loan balance transaction processed successfully', [
                'amount' => $amount,
                'debit_account' => $originalLoan->loan_account_number,
                'credit_account' => $bankAccount,
                'transaction_reference' => $result['reference'] ?? null
            ]);

            // Update the original loan status to CLOSED
            DB::table('loans')->where('id', $originalLoan->id)->update([
                'status' => 'CLOSED',
                'closure_date' => now()->toDateString(),
                'updated_at' => now()
            ]);

            Log::info('Top-up loan transaction completed successfully', [
                'original_loan_id' => $originalLoan->id,
                'top_up_amount' => $amount,
                'original_loan_status' => 'CLOSED'
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing top-up loan transaction', [
                'loan_id' => session('currentloanID'),
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate outstanding amount from loans_schedules
     */
    private function calculateOutstandingAmount($loanId)
    {
        try {
            $schedules = DB::table('loans_schedules')
                ->where('loan_id', $loanId)
                ->whereIn('completion_status', ['ACTIVE', 'PENDING', 'PARTIAL'])
                ->get();

            $outstandingAmount = 0;
            foreach ($schedules as $schedule) {
                if ($schedule->installment == 0) {
                    continue;
                }

                $totalPaid = ($schedule->interest_payment ?? 0) + ($schedule->principle_payment ?? 0);
                $outstanding = $schedule->installment - $totalPaid;
                
                if ($outstanding > 0) {
                    $outstandingAmount += $outstanding;
                }
            }

            Log::info('Outstanding amount calculated', [
                'loan_id' => $loanId,
                'outstanding_amount' => $outstandingAmount,
                'schedules_count' => $schedules->count()
            ]);

            return $outstandingAmount;

        } catch (\Exception $e) {
            Log::error('Error calculating outstanding amount', [
                'loan_id' => $loanId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Update repayments of the loan being topped up
     * Based on the provided update_repayment function
     */
    private function updateTopUpLoanRepayments($loanId, $amount)
    {
        try {
            // Get the loan details
            $loan = DB::table('loans')->where('id', $loanId)->first();
            if (!$loan) {
                throw new \Exception('Loan not found for repayment update.');
            }

            // Get bank and account information
            $cashAccount = DB::table('accounts')->where('id', $this->bank)->value('sub_category_code');
            
            // Ensure loan account number has proper padding
            $paddedLoanAccountNumber = str_pad($loan->loan_account_number, 16, '0', STR_PAD_LEFT);
            $loanAccountSubCategoryCode = AccountsModel::where('account_number', $paddedLoanAccountNumber)
                ->orWhere('account_number', $loan->loan_account_number)
                ->value('sub_category_code');
            
            $interestAccountNumber = DB::table('loans')->where('loan_account_number', $loan->loan_account_number)->value('interest_account_number');
            
            // Ensure interest account number has proper padding
            if ($interestAccountNumber) {
                $paddedInterestAccountNumber = str_pad($interestAccountNumber, 16, '0', STR_PAD_LEFT);
                $interestAccountSubCategoryCode = AccountsModel::where('account_number', $paddedInterestAccountNumber)
                    ->orWhere('account_number', $interestAccountNumber)
                    ->value('sub_category_code');
            } else {
                $interestAccountSubCategoryCode = null;
            }

            // Fetch all pending schedules for the given loan ID
            $schedules = DB::table('loans_schedules')
                ->where('loan_id', $loanId)
                ->whereIn('completion_status', ['ACTIVE', 'PENDING', 'PARTIAL'])
                ->orderBy('installment_date', 'asc')
                ->get();

            $remainingAmount = $amount;

            foreach ($schedules as $schedule) {
                // Initialize payment values
                $interestPayment = 0;
                $principalPayment = 0;

                if ($schedule->installment == 0) {
                    continue; // Skip if installment is 0
                }

                // Pay off the interest first
                $interestOutstanding = $schedule->interest - ($schedule->interest_payment ?? 0);
                if ($remainingAmount >= $interestOutstanding) {
                    $interestPayment = $interestOutstanding;
                    $remainingAmount -= $interestPayment;
                } else {
                    $interestPayment = $remainingAmount;
                    $remainingAmount = 0;
                }

                // Pay off the principal next
                if ($remainingAmount > 0) {
                    $principalOutstanding = $schedule->principle - ($schedule->principle_payment ?? 0);
                    if ($remainingAmount >= $principalOutstanding) {
                        $principalPayment = $principalOutstanding;
                        $remainingAmount -= $principalPayment;
                    } else {
                        $principalPayment = $remainingAmount;
                        $remainingAmount = 0;
                    }
                }

                // Calculate total payment made
                $totalPayment = $interestPayment + $principalPayment;

                // Determine the completion status
                $completionStatus = floor($totalPayment * 100) / 100 >= floor($schedule->installment * 100) / 100 ? 'PAID' : 'PARTIAL';

                // Update the schedule record in the database
                DB::table('loans_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'interest_payment' => ($schedule->interest_payment ?? 0) + $interestPayment,
                        'principle_payment' => ($schedule->principle_payment ?? 0) + $principalPayment,
                        'payment' => ($schedule->payment ?? 0) + $totalPayment,
                        'completion_status' => $completionStatus,
                        'updated_at' => now()
                    ]);

                // Process transactions for repayments
                if ($principalPayment > 0) {
                    $this->processTopUpTransaction($loanAccountSubCategoryCode, $cashAccount, $principalPayment, 'Loan Principal Repayment - Loan ID: ' . $loanId);
                }
                
                if ($interestPayment > 0) {
                    $this->processTopUpTransaction($interestAccountSubCategoryCode, $cashAccount, $interestPayment, 'Loan Interest Repayment - Loan ID: ' . $loanId);
                }

                // If the remaining amount is exhausted, break out of the loop
                if ($remainingAmount <= 0) {
                    break;
                }
            }

            // Check if all schedules are marked as "PAID" and set loan to "CLOSED" if true
            $remainingSchedules = DB::table('loans_schedules')
                ->where('loan_id', $loanId)
                ->where('completion_status', '!=', 'PAID')
                ->count();

            if ($remainingSchedules === 0) {
                DB::table('loans')->where('id', $loanId)->update(['status' => 'CLOSED']);
            }

            Log::info('Top-up loan repayments updated successfully', [
                'loan_id' => $loanId,
                'amount_applied' => $amount - $remainingAmount,
                'remaining_amount' => $remainingAmount,
                'schedules_processed' => $schedules->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating top-up loan repayments', [
                'loan_id' => $loanId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process transaction for top-up loan repayments
     */
    private function processTopUpTransaction($debitAccountCode, $creditAccountCode, $amount, $narrationSuffix)
    {
        try {
            // Check if the amount is null, and set it to 0 if true
            if (is_null($amount)) {
                $amount = 0;
            }

            if ($amount <= 0) {
                return; // Skip if no amount to process
            }

            $narration = $narrationSuffix;
            $debitAccount = AccountsModel::where('sub_category_code', $debitAccountCode)->first();
            $creditAccount = AccountsModel::where('sub_category_code', $creditAccountCode)->first();

            if (!$debitAccount || !$creditAccount) {
                throw new \Exception('Account not found for transaction processing.');
            }

            $transactionService = new TransactionPostingService();
            $data = [
                'first_account' => $creditAccount->account_number,
                'second_account' => $debitAccount->account_number,
                'amount' => $amount,
                'narration' => $narration,
                'action' => 'top_up_loan_repayment'
            ];

            $response = $transactionService->postTransaction($data);
            
            if ($response['status'] !== 'success') {
                throw new \Exception('Failed to post top-up repayment transaction: ' . ($response['message'] ?? 'Unknown error'));
            }

            Log::info('Top-up repayment transaction processed successfully', [
                'amount' => $amount,
                'debit_account' => $debitAccount->account_number,
                'credit_account' => $creditAccount->account_number,
                'narration' => $narration
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing top-up transaction', [
                'amount' => $amount,
                'debit_account_code' => $debitAccountCode,
                'credit_account_code' => $creditAccountCode,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle loan restructuring
     */
    private function handleLoanRestructuring($loan, $restructuringAmount)
    {
        try {
            // Update the original loan status to RESTRUCTURED
            if ($loan->restructured_loan_id) {
                DB::table('loans')->where('id', $loan->restructured_loan_id)->update([
                    'status' => 'RESTRUCTURED',
                    'restructured_date' => now(),
                    'restructured_by' => auth()->id()
                ]);

                Log::info('Original loan marked as restructured', [
                    'original_loan_id' => $loan->restructured_loan_id,
                    'new_loan_id' => $loan->id
                ]);
            }

            // Update repayment schedule for the restructured loan
            $this->updateRestructuredLoanSchedule($loan);

        } catch (\Exception $e) {
            Log::error('Error handling loan restructuring', [
                'loan_id' => $loan->id,
                'restructuring_amount' => $restructuringAmount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle top-up loan processing
     */
    private function handleTopUpLoanProcessing($loan, $topUpAmount)
    {
        try {
            if (!$this->selectedLoan) {
                return;
            }

            // Update the original loan status to TOP_UP
            DB::table('loans')->where('id', $this->selectedLoan)->update([
                'status' => 'TOP_UP',
                'top_up_date' => now(),
                'top_up_by' => auth()->id(),
                'top_up_amount' => $topUpAmount
            ]);

            // Update the new loan with top-up reference
            DB::table('loans')->where('id', $loan->id)->update([
                'top_up_loan_id' => $this->selectedLoan,
                'top_up_amount' => $topUpAmount
            ]);

            Log::info('Top-up loan processing completed', [
                'original_loan_id' => $this->selectedLoan,
                'new_loan_id' => $loan->id,
                'top_up_amount' => $topUpAmount
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling top-up loan processing', [
                'loan_id' => $loan->id,
                'top_up_amount' => $topUpAmount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate monthly installment
     */
    private function calculateMonthlyInstallment($loan, $loanProduct)
    {
        try {
            $principal = (float)$this->approved_loan_value;
            $annualInterestRate = (float)($loanProduct->interest_value ?? 0);
            $monthlyInterestRate = $annualInterestRate / 12 / 100;
            $tenure = (int)$this->approved_term;

            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $tenure)) / (pow(1 + $monthlyInterestRate, $tenure) - 1);
            } else {
                $monthlyInstallment = $principal / $tenure;
            }

            return round($monthlyInstallment, 2);

        } catch (\Exception $e) {
            Log::error('Error calculating monthly installment', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Send disbursement notifications
     */
    private function sendDisbursementNotifications($member, $controlNumbers, $deductions, $loanId = null)
    {
        try {
            // Ensure controlNumbers is an array
            $controlNumbers = is_array($controlNumbers) ? $controlNumbers : [];

            // Dispatch notification job (member only - guarantor notifications removed)
            \App\Jobs\ProcessMemberNotifications::dispatch(
                $member,
                $controlNumbers,
                config('app.url') . '/payments/loan/' . $member->client_number,
                $loanId  // Pass loan ID to notification job
            );

            Log::info('Disbursement notifications dispatched (member only)', [
                'member_id' => $member->id,
                'loan_id' => $loanId,
                'control_numbers_count' => is_array($controlNumbers) ? count($controlNumbers) : 0,
                'control_numbers' => $controlNumbers
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending disbursement notifications', [
                'member_id' => $member->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception - notifications are not critical for disbursement
        }
    }

    /**
     * Update restructured loan schedule
     */
    private function updateRestructuredLoanSchedule($loan)
    {
        try {
            // This method would update the repayment schedule for restructured loans
            // Implementation depends on your restructuring logic
            Log::info('Restructured loan schedule updated', [
                'loan_id' => $loan->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating restructured loan schedule', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process restructuring transaction
     * No fund transfer - generates new repayment schedule for restructured loan
     */
    private function processRestructuringLoanClosure($amount)
    {
        try {
            // Get the loan being restructured
            $loanID = session('currentloanID');
            $loan = DB::table('loans')->where('id', $loanID)->first();
            
            if (!$loan) {
                throw new \Exception('Loan not found for restructuring.');
            }

            // Get the original loan being restructured
            $originalLoan = DB::table('loans')
                ->where('id', $loan->restructure_loan_id ?? 0)
                ->first();

            if (!$originalLoan) {
                throw new \Exception('Original loan not found for restructuring.');
            }

            Log::info('Processing restructuring loan closure', [
                'new_loan_id' => $loanID,
                'original_loan_id' => $originalLoan->id,
                'restructuring_amount' => $amount,
                'new_loan_amount' => $this->approved_loan_value,
                'new_loan_term' => $this->approved_term
            ]);

            // 1. Close all loan schedules for the original loan
            DB::table('loans_schedules')
                ->where('loan_id', $originalLoan->id)
                ->update([
                    'completion_status' => 'CLOSED',
                    'status' => 'CLOSED',
                    'updated_at' => now()
                ]);

            // 2. Update the original loan status to CLOSED
            DB::table('loans')->where('id', $originalLoan->id)->update([
                'status' => 'CLOSED',
                'closure_date' => now()->toDateString(),
                'updated_at' => now()
            ]);

            // 3. Update the new loan with restructuring reference
            DB::table('loans')->where('id', $loanID)->update([
                'restructure_loan_id' => $originalLoan->id,
                'restructure_amount' => $amount,
                'updated_at' => now()
            ]);

            Log::info('Restructuring loan closure completed successfully', [
                'new_loan_id' => $loanID,
                'original_loan_id' => $originalLoan->id,
                'original_loan_status' => 'CLOSED',
                'schedules_closed' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing restructuring loan closure', [
                'loan_id' => session('currentloanID'),
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate new repayment schedule for restructured loan
     */
    private function generateRestructuredLoanSchedule($loanID, $originalLoan)
    {
        try {
            // Get loan product details for the new loan
            $loanProduct = DB::table('loan_sub_products')
                ->where('sub_product_id', session('currentloanProductCode') ?? '')
                ->first();

            if (!$loanProduct) {
                throw new \Exception('Loan product not found for restructuring schedule generation.');
            }

            Log::info('Generating restructured loan schedule', [
                'loan_id' => $loanID,
                'original_loan_id' => $originalLoan->id,
                'new_loan_amount' => $this->approved_loan_value,
                'new_loan_term' => $this->approved_term,
                'interest_rate' => $loanProduct->interest_value ?? 0
            ]);

            // Clear any existing schedules for this loan
            DB::table('loans_schedules')->where('loan_id', $loanID)->delete();

            // Get loan parameters for new schedule
            $principal = (float)$this->approved_loan_value;
            $annualInterestRate = (float)($loanProduct->interest_value ?? 0);
            $monthlyInterestRate = $annualInterestRate / 12 / 100; // Convert to decimal
            $tenure = (int)$this->approved_term;

            // Calculate equal monthly installment using amortization formula
            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $tenure)) / (pow(1 + $monthlyInterestRate, $tenure) - 1);
            } else {
                $monthlyInstallment = $principal / $tenure; // If no interest, equal principal payments
            }

            // Generate new schedule starting from current date
            $remainingBalance = $principal;
            $totalPayment = 0;
            $totalInterest = 0;
            $totalPrincipal = 0;

            // Calculate dates
            $disbursementDate = \Carbon\Carbon::now();

            // Calculate first payment date based on member's payroll date (MEMBERS PORTAL PATTERN)
            $firstRegularDate = $disbursementDate->copy()->addMonth(); // Default fallback
            $daysToFirstInstallment = $disbursementDate->diffInDays($firstRegularDate); // Default: ~30 days

            try {
                // Get member's payroll date from their group
                $client = DB::table('clients')->where('client_number', $originalLoan->client_number)->first();
                if ($client && $client->member_group) {
                    $memberGroup = DB::table('member_groups')->where('id', $client->member_group)->first();

                    if ($memberGroup && $memberGroup->payrol_date) {
                        $payrollDay = (int)$memberGroup->payrol_date;

                        // Calculate first payment date based on payroll day
                        if ($disbursementDate->day < $payrollDay) {
                            // Use this month's payroll date
                            $firstRegularDate = $disbursementDate->copy()->day($payrollDay);
                        } else {
                            // Use next month's payroll date
                            $firstRegularDate = $disbursementDate->copy()->addMonth()->day($payrollDay);
                        }

                        // Calculate actual days
                        $daysToFirstInstallment = $disbursementDate->diffInDays($firstRegularDate);

                        Log::info('First payment date calculated for restructured loan', [
                            'loan_id' => $loanID,
                            'client_number' => $originalLoan->client_number,
                            'member_group_id' => $client->member_group,
                            'payroll_day' => $payrollDay,
                            'disbursement_date' => $disbursementDate->format('Y-m-d'),
                            'first_payment_date' => $firstRegularDate->format('Y-m-d'),
                            'days_to_first_installment' => $daysToFirstInstallment
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Could not calculate exact days to first payment for restructured loan, using default ~30 days', [
                    'error' => $e->getMessage(),
                    'loan_id' => $loanID,
                    'client_number' => $originalLoan->client_number ?? null
                ]);
            }

            // Add First Interest installment (interest from disbursement to first regular installment)
            $daysToFirstInstallment = $disbursementDate->diffInDays($firstRegularDate);
            $firstInterestAmount = $principal * ($annualInterestRate / 100) * ($daysToFirstInstallment / 365);
            
            if ($firstInterestAmount > 0) {
                Log::info('Creating first interest installment for restructured loan', [
                    'first_interest_amount' => $firstInterestAmount,
                    'installment_date' => $firstRegularDate->format('Y-m-d')
                ]);

                // Save first interest installment
                DB::table('loans_schedules')->insert([
                    'loan_id' => $loanID,
                    'installment' => $firstInterestAmount,
                    'interest' => $firstInterestAmount,
                    'principle' => 0,
                    'opening_balance' => $principal,
                    'closing_balance' => $principal, // Balance doesn't change for interest-only payment
                    'bank_account_number' => $originalLoan->bank1 ?? null,
                    'completion_status' => 'ACTIVE',
                    'status' => 'ACTIVE',
                    'installment_date' => $firstRegularDate->format('Y-m-d'),
                    'member_number' => $originalLoan->client_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $totalPayment += $firstInterestAmount;
                $totalInterest += $firstInterestAmount;
            }

            // Generate regular installments starting from the second month
            $regularStartDate = $firstRegularDate->copy()->addMonth();
            
            for ($i = 0; $i < $tenure; $i++) {
                $openingBalance = $remainingBalance;
                
                // Calculate interest for this month
                $monthlyInterest = $remainingBalance * $monthlyInterestRate;
                
                // Calculate principal for this month
                $monthlyPrincipal = $monthlyInstallment - $monthlyInterest;
                
                // Ensure we don't overpay in the last installment
                if ($i == $tenure - 1) {
                    $monthlyPrincipal = $remainingBalance;
                    $monthlyInstallment = $monthlyPrincipal + $monthlyInterest;
                }
                
                // Update remaining balance
                $remainingBalance -= $monthlyPrincipal;
                if ($remainingBalance < 0.01) $remainingBalance = 0; // Round to zero if very small
                
                Log::info('Creating regular installment for restructured loan', [
                    'installment_number' => $i + 1,
                    'installment_date' => $regularStartDate->format('Y-m-d'),
                    'opening_balance' => $openingBalance,
                    'payment' => $monthlyInstallment,
                    'principal' => $monthlyPrincipal,
                    'interest' => $monthlyInterest,
                    'closing_balance' => $remainingBalance
                ]);

                // Save regular installment
                DB::table('loans_schedules')->insert([
                    'loan_id' => $loanID,
                    'installment' => $monthlyInstallment,
                    'interest' => $monthlyInterest,
                    'principle' => $monthlyPrincipal,
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $remainingBalance,
                    'bank_account_number' => $originalLoan->bank1 ?? null,
                    'completion_status' => 'ACTIVE',
                    'status' => 'ACTIVE',
                    'installment_date' => $regularStartDate->format('Y-m-d'),
                    'member_number' => $originalLoan->client_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $totalPayment += $monthlyInstallment;
                $totalInterest += $monthlyInterest;
                $totalPrincipal += $monthlyPrincipal;
                
                $regularStartDate->addMonth();
            }

            // Update loan with new schedule information
            DB::table('loans')->where('id', $loanID)->update([
                'monthly_installment' => $monthlyInstallment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal,
                'total_payment' => $totalPayment,
                'restructured_schedule_generated' => true
            ]);

            Log::info('Restructured loan schedule generated successfully', [
                'loan_id' => $loanID,
                'total_installments' => $tenure + ($firstInterestAmount > 0 ? 1 : 0),
                'monthly_installment' => $monthlyInstallment,
                'total_payment' => $totalPayment,
                'total_interest' => $totalInterest,
                'total_principal' => $totalPrincipal,
                'first_interest_amount' => $firstInterestAmount
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating restructured loan schedule', [
                'loan_id' => $loanID,
                'original_loan_id' => $originalLoan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Send loan disbursement notification to member
     */
    private function sendLoanDisbursementNotification($member, $loan, $netDisbursementAmount)
    {
        try {
            // Get loan repayment schedule to find monthly installment
            $schedule = loans_schedules::where('loan_id', $loan->id)
                ->where('status', 'Active')
                ->first();
            
            $monthlyInstallment = $schedule ? $schedule->installment : 0;
            
            // Generate payment link using the same approach as member registration
            $paymentUrl = null;
            $controlNumber = null;
            
            try {
                // Get institution ID for SACCOS code
                $institution_id = DB::table('institutions')->where('id', 1)->value('institution_id');
                $saccos = preg_replace('/[^0-9]/', '', $institution_id);
                
                // Create billing service and generate control number for loan repayment
                $billingService = new BillingService();
                
                // Check if loan repayment service exists
                $loanService = DB::table('services')
                    ->where('code', 'LRP') // Loan Repayment service code
                    ->first();
                
                if ($loanService) {
                    // Generate control number for this loan
                    $controlNumber = $billingService->generateControlNumber(
                        $member->client_number,
                        $loanService->id,
                        true, // is_recurring for monthly payments
                        'partial' // allow partial payments
                    );
                    
                    // Create bill for the loan
                    $bill = $billingService->createBill(
                        $member->client_number,
                        $loanService->id,
                        true,
                        'partial',
                        $controlNumber,
                        $monthlyInstallment
                    );
                    
                    // Generate payment link
                    $paymentService = new PaymentLinkService();
                    
                    $paymentData = [
                        'description' => 'Loan Repayment - ' . $member->first_name . ' ' . $member->last_name,
                        'target' => 'individual',
                        'customer_reference' => $member->client_number,
                        'customer_name' => $member->first_name . ' ' . $member->last_name,
                        'customer_phone' => $member->phone_number,
                        'customer_email' => $member->email,
                        'expires_at' => now()->addDays(30)->toIso8601String(),
                        'items' => [
                            [
                                'type' => 'service',
                                'product_service_reference' => (string) $bill->id,
                                'product_service_name' => 'Loan Repayment - Loan ID: ' . $loan->loan_id,
                                'amount' => $monthlyInstallment,
                                'is_required' => true,
                                'allow_partial' => true
                            ]
                        ]
                    ];
                    
                    $paymentResponse = $paymentService->generateUniversalPaymentLink($paymentData);
                    $paymentUrl = $paymentResponse['data']['payment_url'] ?? null;
                    
                    if ($paymentUrl) {
                        // Store payment link in bills table
                        DB::table('bills')->where('id', $bill->id)->update([
                            'payment_link' => $paymentUrl,
                            'payment_link_id' => $paymentResponse['data']['link_id'] ?? null,
                            'payment_link_generated_at' => now(),
                            'payment_link_items' => json_encode($paymentResponse['data']['items'] ?? [])
                        ]);
                        
                        Log::info('Payment link generated for loan disbursement', [
                            'loan_id' => $loan->id,
                            'payment_url' => $paymentUrl,
                            'control_number' => $controlNumber
                        ]);
                    }
                } else {
                    Log::warning('Loan repayment service not found, skipping control number generation');
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to generate payment link for loan disbursement', [
                    'error' => $e->getMessage(),
                    'loan_id' => $loan->id
                ]);
            }
            
            // Send SMS notification
            if ($member->phone_number) {
                $smsTemplateService = new SmsTemplateService();
                $memberName = $member->first_name . ' ' . $member->last_name;
                
                // Generate SMS message (shortened version without schedule)
                $message = $smsTemplateService->generateLoanDisbursementMemberSMS(
                    $memberName,
                    $netDisbursementAmount,
                    $monthlyInstallment,
                    $controlNumber,
                    $paymentUrl
                );
                
                // Send SMS using your SMS service
                try {
                    // Uncomment and configure your SMS service
                    // $smsService = new SmsService();
                    // $smsService->sendSms($member->phone_number, $message);
                    
                    Log::info('Loan disbursement SMS sent', [
                        'phone' => substr($member->phone_number, 0, 6) . '****',
                        'loan_id' => $loan->id,
                        'message_length' => strlen($message)
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send loan disbursement SMS', [
                        'error' => $e->getMessage(),
                        'loan_id' => $loan->id
                    ]);
                }
            }
            
            // Send notification to guarantors if any
            $guarantors = DB::table('loan_guarantors')
                ->join('clients', 'loan_guarantors.guarantor_client_id', '=', 'clients.id')
                ->where('loan_guarantors.loan_id', $loan->id)
                ->select('clients.*')
                ->get();
            
            if ($guarantors->count() > 0) {
                $smsTemplateService = new SmsTemplateService();
                foreach ($guarantors as $guarantor) {
                    if ($guarantor->phone_number) {
                        try {
                            $guarantorMessage = $smsTemplateService->generateLoanDisbursementGuarantorSMS(
                                $guarantor->first_name . ' ' . $guarantor->last_name,
                                $memberName ?? ($member->first_name . ' ' . $member->last_name),
                                $netDisbursementAmount
                            );
                            
                            // Uncomment and configure your SMS service
                            // $smsService = new SmsService();
                            // $smsService->sendSms($guarantor->phone_number, $guarantorMessage);
                            
                            Log::info('Guarantor notification SMS sent', [
                                'guarantor_phone' => substr($guarantor->phone_number, 0, 6) . '****',
                                'loan_id' => $loan->id
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send guarantor SMS', [
                                'error' => $e->getMessage(),
                                'guarantor_id' => $guarantor->id
                            ]);
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            // Don't throw exception - notifications are not critical for disbursement
            Log::error('Failed to send loan disbursement notification', [
                'member' => $member->client_number ?? 'Unknown',
                'loan_id' => $loan->id ?? 'Unknown',
                'error' => $e->getMessage()
            ]);
        }
    }
}

