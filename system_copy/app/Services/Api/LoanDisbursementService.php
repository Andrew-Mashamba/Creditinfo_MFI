<?php

namespace App\Services\Api;

use App\Services\TransactionPostingService;
use App\Services\TransactionProcessingService;
use App\Services\AccountCreationService;
use App\Services\BillingService;
use App\Helper\GenerateAccountNumber;
use App\Models\AccountsModel;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service class for handling API-based loan disbursements
 * 
 * @package App\Services\Api
 * @version 1.0
 */
class LoanDisbursementService
{
    private $transactionService;
    private $billingService;
    private $accountService;
    
    public function __construct(
        TransactionPostingService $transactionService,
        BillingService $billingService,
        AccountCreationService $accountService
    ) {
        $this->transactionService = $transactionService;
        $this->billingService = $billingService;
        $this->accountService = $accountService;
    }

    /**
     * Validate loan for disbursement
     * 
     * @param string $loanId
     * @return object
     * @throws Exception
     */
    public function validateLoan($loanId)
    {
        $loan = DB::table('loans')
            ->where('loan_id', $loanId)
            ->orWhere('id', $loanId)
            ->first();

        if (!$loan) {
            throw new Exception("Loan not found: {$loanId}");
        }

        // Check if loan is already disbursed
        if (in_array($loan->status, ['DISBURSED', 'ACTIVE', 'CLOSED'])) {
            throw new Exception("Loan {$loanId} is already disbursed or active. Current status: {$loan->status}");
        }

        // Check if loan is approved
        if (!in_array($loan->status, ['APPROVED', 'PENDING-DISBURSEMENT'])) {
            throw new Exception("Loan {$loanId} is not approved for disbursement. Current status: {$loan->status}");
        }

        // Validate client exists
        $client = DB::table('clients')->where('client_number', $loan->client_number)->first();
        if (!$client) {
            throw new Exception("Client not found for loan {$loanId}");
        }

        // Validate loan product exists
        $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
        if (!$product) {
            throw new Exception("Loan product not found for loan {$loanId}");
        }

        return $loan;
    }

    /**
     * Process loan disbursement
     * 
     * @param object $loan
     * @param string $paymentMethod
     * @param array $paymentDetails
     * @param string|null $narration
     * @param string $transactionId
     * @return array
     * @throws Exception
     */
    public function processDisbursement($loan, $paymentMethod, $paymentDetails, $narration, $transactionId)
    {
        try {
            Log::info('API Disbursement Processing Started', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'payment_method' => $paymentMethod
            ]);

            // Get member and product details
            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
            $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();

            // Validate payment method data
            $this->validatePaymentMethodData($paymentMethod, $paymentDetails, $member);

            // Calculate deductions
            $deductions = $this->calculateDeductions($loan, $product);
            
            // Calculate net disbursement amount
            $netDisbursementAmount = $loan->approved_loan_value - $deductions['total'];

            if ($netDisbursementAmount <= 0) {
                throw new Exception("Net disbursement amount is zero or negative after deductions");
            }

            // Create loan account if not exists
            $loanAccount = $this->createLoanAccount($loan, $member, $product);

            // Process disbursement based on payment method
            $disbursementResult = $this->processDisbursementByMethod(
                $paymentMethod,
                $paymentDetails,
                $netDisbursementAmount,
                $loan,
                $member,
                $product,
                $loanAccount,
                $transactionId
            );

            // Create repayment schedule
            $this->createRepaymentSchedule($loan, $product, $member);

            // Update loan status to ACTIVE after successful disbursement
            $this->updateLoanStatus($loan->id, 'ACTIVE', $disbursementResult);

            // Generate control numbers
            $controlNumbers = $this->generateControlNumbers($loan, $product);

            // Send notifications
            $this->sendNotifications($member, $loan, $controlNumbers, $deductions, $netDisbursementAmount);

            $result = [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'status' => 'ACTIVE',
                'disbursed_amount' => $netDisbursementAmount,
                'total_loan_amount' => $loan->approved_loan_value,
                'deductions' => $deductions,
                'payment_method' => $paymentMethod,
                'payment_reference' => $disbursementResult['reference'] ?? null,
                'control_numbers' => $controlNumbers,
                'disbursement_date' => now()->toISOString(),
                'loan_account' => $loanAccount
            ];

            Log::info('API Disbursement Completed Successfully', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'net_amount' => $netDisbursementAmount
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('API Disbursement Failed', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate all deductions
     */
    private function calculateDeductions($loan, $product)
    {
        $deductions = [
            'charges' => 0,
            'insurance' => 0,
            'first_interest' => 0,
            'top_up_amount' => 0,
            'top_up_penalty' => 0,
            'total' => 0,
            'breakdown' => []
        ];

        // Calculate charges
        $charges = DB::table('loan_product_charges')
            ->where('loan_product_id', $product->sub_product_id)
            ->where('type', 'charge')
            ->get();

        foreach ($charges as $charge) {
            $amount = $this->calculateChargeAmount($charge, $loan->approved_loan_value);
            $deductions['charges'] += $amount;
            $deductions['breakdown'][] = [
                'type' => 'charge',
                'name' => $charge->name,
                'amount' => $amount
            ];
        }

        // Calculate insurance
        $insurances = DB::table('loan_product_charges')
            ->where('loan_product_id', $product->sub_product_id)
            ->where('type', 'insurance')
            ->get();

        foreach ($insurances as $insurance) {
            $amount = $this->calculateChargeAmount($insurance, $loan->approved_loan_value);
            $deductions['insurance'] += $amount;
            $deductions['breakdown'][] = [
                'type' => 'insurance',
                'name' => $insurance->name,
                'amount' => $amount
            ];
        }

        // Calculate first interest if applicable
        if ($product->interest_method === 'flat') {
            $firstInterest = ($loan->approved_loan_value * $product->interest_value / 100) / 12;
            $deductions['first_interest'] = $firstInterest;
            $deductions['breakdown'][] = [
                'type' => 'first_interest',
                'name' => 'First Month Interest',
                'amount' => $firstInterest
            ];
        }

        // Handle top-up loan deductions
        if ($loan->loan_type === 'TOPUP' && $loan->top_up_loan_id) {
            // Get outstanding balance of the loan being topped up
            $outstandingBalance = $this->calculateOutstandingBalance($loan->top_up_loan_id);
            $deductions['top_up_amount'] = $outstandingBalance;
            $deductions['breakdown'][] = [
                'type' => 'top_up_settlement',
                'name' => 'Previous Loan Settlement',
                'amount' => $outstandingBalance
            ];

            // Include top-up penalty if applicable
            if ($loan->top_up_penalty_amount > 0) {
                $deductions['top_up_penalty'] = $loan->top_up_penalty_amount;
                $deductions['breakdown'][] = [
                    'type' => 'top_up_penalty',
                    'name' => 'Early Settlement Penalty',
                    'amount' => $loan->top_up_penalty_amount
                ];
            }
        }

        $deductions['total'] = $deductions['charges'] + 
                               $deductions['insurance'] + 
                               $deductions['first_interest'] + 
                               $deductions['top_up_amount'] +
                               $deductions['top_up_penalty'];

        return $deductions;
    }

    /**
     * Calculate charge amount based on type (fixed or percentage)
     */
    private function calculateChargeAmount($charge, $loanAmount)
    {
        if ($charge->value_type === 'percentage') {
            $amount = ($loanAmount * $charge->value) / 100;
            
            // Apply min/max caps if set
            if ($charge->min_cap && $amount < $charge->min_cap) {
                $amount = $charge->min_cap;
            }
            if ($charge->max_cap && $amount > $charge->max_cap) {
                $amount = $charge->max_cap;
            }
            
            return $amount;
        }
        
        return $charge->value; // Fixed amount
    }

    /**
     * Calculate outstanding balance for top-up loan
     */
    private function calculateOutstandingBalance($loanId)
    {
        $loan = DB::table('loans')->where('id', $loanId)->first();
        if (!$loan) {
            return 0;
        }

        // Get the loan account balance
        $account = DB::table('accounts')
            ->where('account_number', $loan->loan_account_number)
            ->first();

        return $account ? abs($account->balance) : 0;
    }

    /**
     * Validate payment method data
     */
    private function validatePaymentMethodData($paymentMethod, $paymentDetails, $member)
    {
        switch ($paymentMethod) {
            case 'NBC_ACCOUNT':
                if (empty($paymentDetails['account_number'])) {
                    throw new Exception("NBC account number is required");
                }
                break;
                
            case 'TIPS_MNO':
                if (empty($paymentDetails['phone_number'])) {
                    throw new Exception("Phone number is required for mobile money transfer");
                }
                if (empty($paymentDetails['mno_provider'])) {
                    throw new Exception("Mobile network operator is required");
                }
                if (!in_array($paymentDetails['mno_provider'], ['MPESA', 'TIGOPESA', 'AIRTELMONEY', 'HALOPESA'])) {
                    throw new Exception("Invalid mobile network operator");
                }
                break;
                
            case 'TIPS_BANK':
                if (empty($paymentDetails['bank_code'])) {
                    throw new Exception("Bank code is required");
                }
                if (empty($paymentDetails['bank_account'])) {
                    throw new Exception("Bank account number is required");
                }
                break;
                
            case 'CASH':
                // No additional validation needed for cash
                break;
                
            default:
                throw new Exception("Invalid payment method: {$paymentMethod}");
        }
    }

    /**
     * Process disbursement by payment method
     */
    private function processDisbursementByMethod($paymentMethod, $paymentDetails, $amount, $loan, $member, $product, $loanAccount, $transactionId)
    {
        $result = ['reference' => null, 'status' => 'pending'];

        switch ($paymentMethod) {
            case 'CASH':
                $result['reference'] = $this->processCashDisbursement($amount, $loan, $member, $loanAccount);
                $result['status'] = 'completed';
                break;
                
            case 'NBC_ACCOUNT':
                $result['reference'] = $this->processInternalTransfer($amount, $loan, $member, $paymentDetails, $loanAccount);
                $result['status'] = 'completed';
                break;
                
            case 'TIPS_MNO':
                $result = $this->processMobileMoneyTransfer($amount, $loan, $member, $paymentDetails, $loanAccount);
                break;
                
            case 'TIPS_BANK':
                $result = $this->processBankTransfer($amount, $loan, $member, $paymentDetails, $loanAccount);
                break;
        }

        // Post to general ledger
        $this->postToGeneralLedger($loan, $product, $amount, $paymentMethod, $loanAccount);

        return $result;
    }

    /**
     * Process cash disbursement
     */
    private function processCashDisbursement($amount, $loan, $member, $loanAccount)
    {
        $reference = 'CASH_' . uniqid();
        
        // Record cash disbursement transaction
        DB::table('general_ledger')->insert([
            'transaction_type' => 'LOAN_DISBURSEMENT',
            'transaction_reference' => $reference,
            'debit_account' => 'CASH_ACCOUNT',
            'credit_account' => $loanAccount,
            'amount' => $amount,
            'narration' => "Cash disbursement for loan {$loan->loan_id}",
            'created_by' => 'API',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $reference;
    }

    /**
     * Process internal transfer
     */
    private function processInternalTransfer($amount, $loan, $member, $paymentDetails, $loanAccount)
    {
        $reference = 'NBC_' . uniqid();
        $memberAccount = $paymentDetails['account_number'];

        // Verify member account exists
        $account = DB::table('accounts')->where('account_number', $memberAccount)->first();
        if (!$account) {
            throw new Exception("Member account not found: {$memberAccount}");
        }

        // Transfer funds
        DB::table('accounts')->where('account_number', $memberAccount)->increment('balance', $amount);
        DB::table('accounts')->where('account_number', $loanAccount)->decrement('balance', $amount);

        // Record transaction
        DB::table('general_ledger')->insert([
            'transaction_type' => 'LOAN_DISBURSEMENT',
            'transaction_reference' => $reference,
            'debit_account' => $loanAccount,
            'credit_account' => $memberAccount,
            'amount' => $amount,
            'narration' => "Loan disbursement transfer for loan {$loan->loan_id}",
            'created_by' => 'API',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $reference;
    }

    /**
     * Process mobile money transfer (stub - implement actual integration)
     */
    private function processMobileMoneyTransfer($amount, $loan, $member, $paymentDetails, $loanAccount)
    {
        // This would integrate with actual mobile money APIs
        // For now, return mock response
        return [
            'reference' => 'MNO_' . uniqid(),
            'status' => 'pending',
            'provider' => $paymentDetails['mno_provider'],
            'phone' => $paymentDetails['phone_number']
        ];
    }

    /**
     * Process bank transfer (stub - implement actual integration)
     */
    private function processBankTransfer($amount, $loan, $member, $paymentDetails, $loanAccount)
    {
        // This would integrate with actual bank transfer APIs
        // For now, return mock response
        return [
            'reference' => 'BANK_' . uniqid(),
            'status' => 'pending',
            'bank_code' => $paymentDetails['bank_code'],
            'account' => $paymentDetails['bank_account']
        ];
    }

    /**
     * Create loan account
     */
    private function createLoanAccount($loan, $member, $product)
    {
        // Check if loan account already exists
        if ($loan->loan_account_number) {
            return $loan->loan_account_number;
        }

        // Generate new loan account number
        $accountNumber = GenerateAccountNumber::generate('LOAN', $member->client_number);

        // Create account
        DB::table('accounts')->insert([
            'account_number' => $accountNumber,
            'account_name' => "Loan Account - {$member->first_name} {$member->last_name}",
            'account_type' => 'LOAN',
            'client_number' => $member->client_number,
            'product_id' => $product->sub_product_id,
            'balance' => -$loan->approved_loan_value,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update loan with account number
        DB::table('loans')->where('id', $loan->id)->update([
            'loan_account_number' => $accountNumber
        ]);

        return $accountNumber;
    }

    /**
     * Create repayment schedule
     */
    private function createRepaymentSchedule($loan, $product, $member)
    {
        $principal = $loan->approved_loan_value;
        $tenure = $loan->tenure;
        $interestRate = $product->interest_value / 100;
        $monthlyRate = $interestRate / 12;

        // Calculate monthly installment
        if ($product->amortization_method === 'equal_installments') {
            $monthlyInstallment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $tenure)) / 
                                 (pow(1 + $monthlyRate, $tenure) - 1);
        } else {
            $monthlyInstallment = ($principal / $tenure) + ($principal * $monthlyRate);
        }

        $balance = $principal;
        $scheduleDate = Carbon::now()->addMonth();

        for ($i = 1; $i <= $tenure; $i++) {
            $interestAmount = $balance * $monthlyRate;
            $principalAmount = $monthlyInstallment - $interestAmount;
            $balance -= $principalAmount;

            DB::table('loans_schedules')->insert([
                'loan_id' => $loan->loan_id,
                'installment_number' => $i,
                'due_date' => $scheduleDate->format('Y-m-d'),
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'total_amount' => $monthlyInstallment,
                'outstanding_balance' => max(0, $balance),
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $scheduleDate->addMonth();
        }
    }

    /**
     * Update loan status
     */
    private function updateLoanStatus($loanId, $status, $disbursementResult)
    {
        DB::table('loans')->where('id', $loanId)->update([
            'status' => $status,
            'loan_status' => $status,
            'disbursement_date' => now(),
            'disbursement_reference' => $disbursementResult['reference'] ?? null,
            'disbursement_method' => $disbursementResult['payment_method'] ?? null,
            'updated_at' => now()
        ]);
    }

    /**
     * Generate control numbers for payment collection
     */
    private function generateControlNumbers($loan, $product)
    {
        $controlNumbers = [];
        
        // Generate control number for loan repayment
        $controlNumber = $this->generateControlNumber($loan->loan_id, 'REPAYMENT');
        $controlNumbers[] = [
            'type' => 'REPAYMENT',
            'control_number' => $controlNumber,
            'description' => 'Monthly Loan Repayment'
        ];

        return $controlNumbers;
    }

    /**
     * Generate single control number
     */
    private function generateControlNumber($loanId, $type)
    {
        $prefix = substr($type, 0, 3);
        $timestamp = time();
        $random = rand(1000, 9999);
        
        return strtoupper($prefix . $timestamp . $random);
    }

    /**
     * Post to general ledger
     */
    private function postToGeneralLedger($loan, $product, $amount, $paymentMethod, $loanAccount)
    {
        // This would integrate with the actual general ledger system
        // Implementation depends on specific accounting requirements
    }

    /**
     * Send notifications
     */
    private function sendNotifications($member, $loan, $controlNumbers, $deductions, $netAmount)
    {
        // Send SMS notification
        if ($member->phone_number) {
            $message = "Dear {$member->first_name}, your loan {$loan->loan_id} of TZS " . 
                      number_format($loan->approved_loan_value, 2) . 
                      " has been disbursed. Net amount: TZS " . 
                      number_format($netAmount, 2) . 
                      ". Control Number: " . ($controlNumbers[0]['control_number'] ?? 'N/A');
            
            // Queue SMS sending
            // This would integrate with actual SMS service
            Log::info('SMS notification queued', [
                'phone' => $member->phone_number,
                'message' => $message
            ]);
        }

        // Send email notification if available
        if ($member->email) {
            // Queue email sending
            // This would integrate with actual email service
            Log::info('Email notification queued', [
                'email' => $member->email,
                'loan_id' => $loan->loan_id
            ]);
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus($transactionId)
    {
        $transaction = DB::table('api_disbursement_logs')
            ->where('transaction_id', $transactionId)
            ->first();

        if (!$transaction) {
            throw new Exception("Transaction not found: {$transactionId}");
        }

        return [
            'transaction_id' => $transaction->transaction_id,
            'loan_id' => $transaction->loan_id,
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'payment_method' => $transaction->payment_method,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at
        ];
    }
}