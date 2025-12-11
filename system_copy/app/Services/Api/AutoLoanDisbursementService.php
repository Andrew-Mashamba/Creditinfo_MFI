<?php

namespace App\Services\Api;

use App\Services\TransactionPostingService;
use App\Services\AccountCreationService;
use App\Services\SmsService;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for automatic loan creation and disbursement
 * Creates and disburses loans with just client_number and amount
 * 
 * @package App\Services\Api
 * @version 1.0
 */
class AutoLoanDisbursementService
{
    private $transactionService;
    private $accountService;
    private $smsService;
    private $billingService;

    public function __construct(
        TransactionPostingService $transactionService,
        AccountCreationService $accountService,
        SmsService $smsService,
        BillingService $billingService
    ) {
        $this->transactionService = $transactionService;
        $this->accountService = $accountService;
        $this->smsService = $smsService;
        $this->billingService = $billingService;
    }

    /**
     * Automatically create and disburse a loan
     *
     * @param string $clientNumber
     * @param float $amount
     * @param int|null $tenure Optional loan tenure in months
     * @return array
     * @throws Exception
     */
    public function createAndDisburseLoan($clientNumber, $amount, $tenure = null)
    {
        DB::beginTransaction();

        try {
            $transactionId = uniqid('AUTO_DISB_');

            Log::info('🚀 Auto Loan Creation and Disbursement Started', [
                'transaction_id' => $transactionId,
                'client_number' => $clientNumber,
                'requested_amount' => $amount,
                'requested_tenure' => $tenure ?? 'default',
                'timestamp' => now()->toISOString()
            ]);

            // Step 1: Get client information
            $client = $this->getClientInfo($clientNumber);
            Log::info('✅ Client Information Retrieved', [
                'transaction_id' => $transactionId,
                'client_number' => $clientNumber,
                'client_name' => $client->first_name . ' ' . $client->last_name,
                'nbc_account' => $client->account_number
            ]);
            
            // Step 2: Get default loan product (id = 1)
            $product = $this->getDefaultProduct();

            // Step 2a: Validate and set tenure
            $validatedTenure = $this->validateAndSetTenure($tenure, $product);
            Log::info('✅ Loan Product Retrieved', [
                'transaction_id' => $transactionId,
                'product_id' => $product->id,
                'product_name' => $product->sub_product_name,
                'interest_rate' => $product->interest_value . '%',
                'tenure' => $validatedTenure . ' months',
                'min_term' => $product->min_term . ' months',
                'max_term' => $product->max_term . ' months'
            ]);

            // Step 2b: Validate loan amount against product limits and client balances
            $this->validateLoanAmount($amount, $product, $clientNumber);
            Log::info('✅ Loan Amount Validated', [
                'transaction_id' => $transactionId,
                'requested_amount' => $amount,
                'validation_status' => 'PASSED'
            ]);

            // Step 2b: Check for client arrears
            $this->checkClientArrears($clientNumber);
            Log::info('✅ Arrears Check Passed', [
                'transaction_id' => $transactionId,
                'client_number' => $clientNumber,
                'arrears_status' => 'NONE'
            ]);

            // Step 2c: Check for existing active loan of same type
            $this->checkExistingLoanOfType($clientNumber, $product->sub_product_id);
            Log::info('✅ Existing Loan Check Passed', [
                'transaction_id' => $transactionId,
                'client_number' => $clientNumber,
                'product_id' => $product->sub_product_id,
                'status' => 'NO_ACTIVE_LOAN_OF_THIS_TYPE'
            ]);

            // Step 3: Generate loan ID
            $loanId = $this->generateLoanId();
            Log::info('✅ Loan ID Generated', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId
            ]);
            
            // Step 4: Create all loan accounts using AccountCreationService
            $loanAccounts = $this->createLoanAccounts($client, $product, $amount, $loanId);
            $loanAccountNumber = $loanAccounts['loan'];

            Log::info('✅ All Loan Accounts Created', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'loan_account' => $loanAccounts['loan'],
                'interest_account' => $loanAccounts['interest'],
                'charges_account' => $loanAccounts['charges'],
                'insurance_account' => $loanAccounts['insurance']
            ]);
            
            // Step 5: Calculate loan details
            $loanDetails = $this->calculateLoanDetails($amount, $product, $validatedTenure);
            Log::info('✅ Loan Details Calculated', [
                'transaction_id' => $transactionId,
                'principal' => $amount,
                'total_interest' => $loanDetails['interest'],
                'monthly_installment' => $loanDetails['monthly_installment'],
                'total_payable' => $loanDetails['total_payable'],
                'tenure_months' => $loanDetails['tenure']
            ]);
            
            // Step 6: Create loan record
            $loan = $this->createLoanRecord(
                $loanId,
                $loanAccounts,
                $client,
                $product,
                $amount,
                $loanDetails
            );
            Log::info('✅ Loan Record Created', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'loan_db_id' => $loan->id,
                'status' => 'APPROVED',
                'loan_type' => 'NEW'
            ]);
            
            // Step 7: Calculate deductions
            $deductions = $this->calculateDeductions($amount, $product);
            Log::info('💰 Deductions Calculated', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'total_deductions' => $deductions['total'],
                'breakdown' => [
                    'charges' => $deductions['charges'],
                    'insurance' => $deductions['insurance'],
                    'first_interest' => $deductions['first_interest']
                ],
                'detailed_breakdown' => $deductions['breakdown']
            ]);
            
            // Step 8: Calculate net disbursement
            $netDisbursementAmount = $amount - $deductions['total'];
            
            Log::info('💳 Net Disbursement Calculated', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'gross_amount' => $amount,
                'total_deductions' => $deductions['total'],
                'net_disbursement' => $netDisbursementAmount,
                'calculation' => "{$amount} - {$deductions['total']} = {$netDisbursementAmount}"
            ]);
            
            if ($netDisbursementAmount <= 0) {
                Log::error('❌ Net Disbursement Amount Invalid', [
                    'transaction_id' => $transactionId,
                    'loan_id' => $loanId,
                    'gross_amount' => $amount,
                    'total_deductions' => $deductions['total'],
                    'net_disbursement' => $netDisbursementAmount,
                    'error' => 'Net disbursement amount is zero or negative after deductions'
                ]);
                throw new Exception("Net disbursement amount is zero or negative after deductions");
            }
            
            // Step 9: Get SACCOS bank account for transfer
            $bankAccount = $this->getSaccosBankAccount();
            Log::info('✅ SACCOS Bank Account Retrieved', [
                'transaction_id' => $transactionId,
                'bank_name' => $bankAccount->bank_name ?? 'N/A',
                'account_number' => $bankAccount->account_number,
                'current_balance' => $bankAccount->current_balance ?? 0
            ]);

            // Step 10: Process NBC Internal Funds Transfer (REAL MONEY TRANSFER)
            $nbcTransferResult = $this->processNBCTransfer(
                $client,
                $loan,
                $bankAccount,
                $netDisbursementAmount,
                $transactionId
            );

            // Step 11: Post GL Transactions (Double Entry Bookkeeping)
            $glPostingResult = $this->postGLTransactions(
                $loan,
                $product,
                $amount,
                $netDisbursementAmount,
                $deductions,
                $loanAccounts,
                $bankAccount,
                $transactionId
            );

            // Step 12: Create repayment schedule
            $this->createRepaymentSchedule($loan, $product);
            Log::info('✅ Repayment Schedule Created', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'tenure' => $loan->tenure . ' months',
                'first_payment_date' => Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d')
            ]);

            // Step 13: Generate control numbers and create bill
            $controlNumbers = $this->generateControlNumbers($client, $loan);
            Log::info('✅ Control Numbers Generated and Bill Created', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'control_numbers' => $controlNumbers
            ]);
            
            // Step 14: Send notifications
            $this->sendNotifications($client, $loan, $controlNumbers, $netDisbursementAmount);

            // Step 15: Update loan status to ACTIVE (after successful disbursement)
            $this->updateLoanStatus($loan->id, 'ACTIVE', $nbcTransferResult, $netDisbursementAmount, $deductions['total']);
            
            DB::commit();
            
            // Comprehensive financial summary log
            Log::info('🎉 Auto Loan Creation and Disbursement Completed Successfully', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'client_number' => $clientNumber,
                'client_name' => $client->first_name . ' ' . $client->last_name,
                'nbc_account' => $client->account_number,
                'loan_account' => $loanAccountNumber,
                'nbc_reference' => $nbcTransferResult['nbc_reference'] ?? null,
                'gl_transactions_count' => $glPostingResult['transactions_count'] ?? 0,
                'completion_time' => now()->toISOString()
            ]);
            
            // Detailed financial breakdown log
            Log::info('💰 COMPREHENSIVE FINANCIAL BREAKDOWN', [
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'financial_summary' => [
                    'gross_loan_amount' => $amount,
                    'total_deductions' => $deductions['total'],
                    'net_disbursed_amount' => $netDisbursementAmount,
                    'calculation' => "{$amount} - {$deductions['total']} = {$netDisbursementAmount}"
                ],
                'deductions_breakdown' => [
                    'charges' => [
                        'amount' => $deductions['charges'],
                        'percentage_of_gross' => round(($deductions['charges'] / $amount) * 100, 2) . '%'
                    ],
                    'insurance' => [
                        'amount' => $deductions['insurance'],
                        'percentage_of_gross' => round(($deductions['insurance'] / $amount) * 100, 2) . '%'
                    ],
                    'first_interest' => [
                        'amount' => $deductions['first_interest'],
                        'percentage_of_gross' => round(($deductions['first_interest'] / $amount) * 100, 2) . '%'
                    ]
                ],
                'loan_terms' => [
                    'tenure_months' => $loanDetails['tenure'],
                    'interest_rate' => $product->interest_value . '%',
                    'monthly_installment' => $loanDetails['monthly_installment'],
                    'total_payable' => $loanDetails['total_payable'],
                    'total_interest' => $loanDetails['interest']
                ],
                'account_balances' => [
                    'nbc_account' => $client->account_number,
                    'loan_account' => $loanAccountNumber,
                    'net_amount_credited_to_nbc' => $netDisbursementAmount,
                    'loan_account_debited' => $netDisbursementAmount
                ],
                'detailed_charges' => $deductions['breakdown']
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'loan_id' => $loanId,
                'loan_account' => $loanAccountNumber,
                'client_number' => $clientNumber,
                'client_name' => $client->first_name . ' ' . $client->last_name,
                'nbc_account' => $client->account_number,
                'loan_amount' => $amount,
                'deductions' => $deductions,
                'net_disbursed' => $netDisbursementAmount,
                'tenure_months' => $loanDetails['tenure'],
                'interest_rate' => $product->interest_value,
                'monthly_installment' => $loanDetails['monthly_installment'],
                'total_payable' => $loanDetails['total_payable'],
                'control_numbers' => $controlNumbers,
                'disbursement_date' => now()->toISOString(),
                'first_payment_date' => Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d'),
                'nbc_transfer_reference' => $nbcTransferResult['nbc_reference'] ?? null,
                'payment_reference' => $nbcTransferResult['nbc_reference'] ?? 'NBC_AUTO_' . uniqid(),
                'gl_posting' => [
                    'transactions_count' => $glPostingResult['transactions_count'] ?? 0,
                    'reference_numbers' => $glPostingResult['reference_numbers'] ?? []
                ]
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('💥 Auto Loan Creation and Disbursement Failed', [
                'transaction_id' => $transactionId ?? 'UNKNOWN',
                'client_number' => $clientNumber,
                'amount' => $amount,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'failure_time' => now()->toISOString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Get client information
     */
    private function getClientInfo($clientNumber)
    {
        $client = DB::table('clients')->where('client_number', $clientNumber)->first();

        if (!$client) {
            throw new Exception("Client not found: {$clientNumber}");
        }

        // Validate client status is ACTIVE
        if (strtoupper($client->status ?? '') !== 'ACTIVE') {
            throw new Exception("Client {$clientNumber} is not active. Current status: " . ($client->status ?? 'NULL'));
        }

        if (empty($client->account_number)) {
            throw new Exception("Client {$clientNumber} does not have an NBC account number");
        }

        // Verify NBC account exists
        $account = DB::table('accounts')
            ->where('account_number', $client->account_number)
            ->where('client_number', $clientNumber)
            ->first();

        if (!$account) {
            throw new Exception("NBC account {$client->account_number} not found for client {$clientNumber}");
        }

        return $client;
    }
    
    /**
     * Get default loan product (id = 1)
     */
    private function getDefaultProduct()
    {
        $product = DB::table('loan_sub_products')->where('id', 1)->first();

        if (!$product) {
            throw new Exception("Default loan product (id=1) not found");
        }

        if ($product->sub_product_status != '1') {
            throw new Exception("Default loan product is not active");
        }

        return $product;
    }

    /**
     * Validate loan amount against product limits and client's savings
     */
    private function validateLoanAmount($amount, $product, $clientNumber)
    {
        Log::info('🔍 Validating Loan Amount', [
            'requested_amount' => $amount,
            'client_number' => $clientNumber,
            'product_max_amount' => $product->principle_max_value ?? 'N/A'
        ]);

        // Check 1: Validate against product maximum amount
        if (isset($product->principle_max_value) && $amount > $product->principle_max_value) {
            throw new Exception(
                "Requested amount TZS " . number_format($amount, 2) .
                " exceeds maximum allowed TZS " . number_format($product->principle_max_value, 2) .
                " for this loan product"
            );
        }

        // Check 2: Calculate total savings, deposits, and shares
        $totalBalance = DB::table('accounts')
            ->where('client_number', $clientNumber)
            ->whereIn('product_number', ['1000', '2000', '3000']) // Shares, Savings, Deposits
            ->where('status', 'ACTIVE')
            ->sum('balance');

        Log::info('💰 Client Account Balances', [
            'client_number' => $clientNumber,
            'total_savings_deposits_shares' => $totalBalance,
            'requested_loan_amount' => $amount,
            'validation_passed' => $totalBalance >= $amount
        ]);

        // Check 3: Total balance must be >= requested loan amount
        if ($totalBalance < $amount) {
            throw new Exception(
                "Insufficient savings, deposits and shares. " .
                "Total balance: TZS " . number_format($totalBalance, 2) .
                " is less than requested loan amount: TZS " . number_format($amount, 2)
            );
        }

        Log::info('✅ Loan amount validation passed');
    }

    /**
     * Validate and set loan tenure
     *
     * @param int|null $requestedTenure Tenure requested in API call
     * @param object $product Loan product object
     * @return int Validated tenure in months
     * @throws Exception
     */
    private function validateAndSetTenure($requestedTenure, $product)
    {
        // Use product max_term as default if no tenure specified
        $tenure = $requestedTenure ?? $product->max_term;

        Log::info('🔍 Validating Loan Tenure', [
            'requested_tenure' => $requestedTenure ?? 'default',
            'product_min_term' => $product->min_term,
            'product_max_term' => $product->max_term,
            'final_tenure' => $tenure
        ]);

        // Validate tenure is within product limits
        if ($tenure < $product->min_term) {
            throw new Exception(
                "Requested tenure of {$tenure} months is below minimum allowed tenure of {$product->min_term} months for this loan product"
            );
        }

        if ($tenure > $product->max_term) {
            throw new Exception(
                "Requested tenure of {$tenure} months exceeds maximum allowed tenure of {$product->max_term} months for this loan product"
            );
        }

        Log::info('✅ Tenure validation passed', ['tenure' => $tenure . ' months']);

        return $tenure;
    }

    /**
     * Check if client has any arrears in existing loans
     */
    private function checkClientArrears($clientNumber)
    {
        Log::info('🔍 Checking Client Arrears', [
            'client_number' => $clientNumber
        ]);

        // Get all active loans for the client
        $activeLoans = DB::table('loans')
            ->where('client_number', $clientNumber)
            ->whereIn('status', ['ACTIVE', 'DISBURSED'])
            ->pluck('loan_id');

        if ($activeLoans->isEmpty()) {
            Log::info('✅ No active loans found for client');
            return;
        }

        // Check for overdue schedules (installment_date < today AND status != PAID)
        $overdueSchedules = DB::table('loans_schedules')
            ->whereIn('loan_id', $activeLoans)
            ->where('installment_date', '<', now()->format('Y-m-d'))
            ->where('status', '!=', 'PAID')
            ->get();

        if ($overdueSchedules->count() > 0) {
            $totalArrears = $overdueSchedules->sum('installment');

            Log::error('❌ Client has arrears', [
                'client_number' => $clientNumber,
                'overdue_schedules_count' => $overdueSchedules->count(),
                'total_arrears_amount' => $totalArrears,
                'affected_loans' => $overdueSchedules->pluck('loan_id')->unique()->toArray()
            ]);

            throw new Exception(
                "Client has arrears in existing loans. " .
                "Total overdue amount: TZS " . number_format($totalArrears, 2) .
                ". Please clear existing arrears before applying for a new loan."
            );
        }

        Log::info('✅ No arrears found for client');
    }

    /**
     * Check if client has any existing active loan of the same type
     */
    private function checkExistingLoanOfType($clientNumber, $productId)
    {
        Log::info('🔍 Checking Existing Loans of Same Type', [
            'client_number' => $clientNumber,
            'product_id' => $productId
        ]);

        // Check for active loans of the same product type
        $existingLoan = DB::table('loans')
            ->where('client_number', $clientNumber)
            ->where('loan_sub_product', $productId)
            ->whereIn('status', ['ACTIVE', 'APPROVED', 'DISBURSED', 'PENDING'])
            ->first();

        if ($existingLoan) {
            Log::error('❌ Client has existing active loan of same type', [
                'client_number' => $clientNumber,
                'product_id' => $productId,
                'existing_loan_id' => $existingLoan->loan_id,
                'existing_loan_status' => $existingLoan->status,
                'existing_loan_amount' => $existingLoan->principle ?? 'N/A'
            ]);

            throw new Exception(
                "Client already has an active loan of this type (Loan ID: {$existingLoan->loan_id}). " .
                "The existing loan must be closed before applying for a new loan of the same type."
            );
        }

        Log::info('✅ No active loans of same type found for client');
    }

    /**
     * Get SACCOS bank account for disbursement
     */
    private function getSaccosBankAccount()
    {
        Log::info('🏦 Fetching SACCOS bank account');

        $bankAccount = DB::table('bank_accounts')
            ->where('id', 1)
            ->first();

        if (!$bankAccount || !$bankAccount->account_number) {
            throw new Exception('SACCOS bank account not found (id=1). Please configure bank settings.');
        }

        Log::info('✅ SACCOS bank account retrieved', [
            'bank_id' => $bankAccount->id,
            'bank_name' => $bankAccount->bank_name ?? 'N/A',
            'account_number' => $bankAccount->account_number,
            'current_balance' => $bankAccount->current_balance ?? 0
        ]);

        return $bankAccount;
    }

    /**
     * Process NBC Internal Funds Transfer (REAL MONEY TRANSFER)
     */
    private function processNBCTransfer($client, $loan, $bankAccount, $netAmount, $transactionId)
    {
        Log::info('💸 Starting NBC Internal Funds Transfer', [
            'transaction_id' => $transactionId,
            'loan_id' => $loan->loan_id,
            'from_account' => $bankAccount->account_number,
            'to_account' => $client->account_number,
            'amount' => $netAmount
        ]);

        try {
            $iftService = new \App\Services\Payments\InternalFundsTransferService();

            $transferData = [
                'from_account' => $bankAccount->account_number,  // SACCOS bank account at NBC
                'to_account' => $client->account_number,         // Member's NBC account
                'amount' => $netAmount,
                'narration' => 'Loan Disbursement - ' . $client->first_name . ' ' . $client->last_name . ' - Loan ID: ' . $loan->loan_id,
                'sender_name' => 'SACCOS',
                'from_currency' => 'TZS',
                'to_currency' => 'TZS'
            ];

            Log::info('📤 Calling NBC Transfer API', [
                'transaction_id' => $transactionId,
                'transfer_data' => $transferData
            ]);

            $transferResult = $iftService->transfer($transferData);

            if (!$transferResult['success']) {
                $errorMessage = $transferResult['message'] ?? 'Unknown NBC transfer error';
                Log::error('❌ NBC Transfer Failed', [
                    'transaction_id' => $transactionId,
                    'loan_id' => $loan->loan_id,
                    'error' => $errorMessage,
                    'full_response' => $transferResult
                ]);
                throw new Exception('NBC Bank Transfer Failed: ' . $errorMessage);
            }

            $nbcReference = $transferResult['nbc_reference'] ?? $transferResult['reference'];

            Log::info('✅ NBC Transfer Successful', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'nbc_reference' => $nbcReference,
                'amount_transferred' => $netAmount,
                'from_account' => $bankAccount->account_number,
                'to_account' => $client->account_number
            ]);

            // Store NBC reference in loan record
            DB::table('loans')->where('id', $loan->id)->update([
                'nbc_transfer_reference' => $nbcReference
            ]);

            return [
                'success' => true,
                'nbc_reference' => $nbcReference,
                'amount' => $netAmount,
                'transfer_result' => $transferResult
            ];

        } catch (\Exception $e) {
            Log::error('💥 NBC Transfer Exception', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Post GL Transactions (Double Entry Bookkeeping)
     */
    private function postGLTransactions($loan, $product, $grossAmount, $netAmount, $deductions, $loanAccounts, $bankAccount, $transactionId)
    {
        Log::info('📊 Starting GL Transaction Posting', [
            'transaction_id' => $transactionId,
            'loan_id' => $loan->loan_id,
            'method' => 'NET',
            'gross_amount' => $grossAmount,
            'net_amount' => $netAmount,
            'total_deductions' => $deductions['total']
        ]);

        try {
            $transactionService = new \App\Services\TransactionPostingService();
            $referenceNumbers = [];
            $transactionsCount = 0;

            $bankMirrorAccount = $bankAccount->internal_mirror_account_number ?? null;

            if (!$bankMirrorAccount) {
                Log::warning('⚠️ Bank mirror account not found, using bank account number');
                $bankMirrorAccount = $bankAccount->account_number;
            }

            // Get GL account numbers for income recognition (from created accounts)
            $loanAccountNumber = $loanAccounts['loan'];
            $chargeAccount = $loanAccounts['charges'];
            $insuranceAccount = $loanAccounts['insurance'];
            $interestAccount = $loanAccounts['interest'];

            // NET METHOD - Most common for disbursements

            // Transaction 1: Net Disbursement
            // Source: Bank (money going out)
            // Destination: Loan Account (money received)
            $result = $transactionService->postTransaction([
                'first_account' => $loanAccountNumber,
                'second_account' => $bankMirrorAccount,
                'source_account' => $bankMirrorAccount,
                'destination_account' => $loanAccountNumber,
                'amount' => $netAmount,
                'narration' => 'Loan Disbursement - Net Amount - Loan ID: ' . $loan->loan_id,
                'action' => 'loan_disbursement_net'
            ]);

            $referenceNumbers[] = $result['reference_number'];
            $transactionsCount++;

            Log::info('✅ Posted net disbursement GL transaction', [
                'reference' => $result['reference_number'],
                'amount' => $netAmount
            ]);

            // Transaction 2: Processing Fees Income
            if ($deductions['charges'] > 0) {
                // DR: Loan Account (Asset)
                // CR: Processing Fees Income
                $result = $transactionService->postTransaction([
                    'first_account' => $loanAccountNumber,
                    'second_account' => $chargeAccount,
                    'amount' => $deductions['charges'],
                    'narration' => 'Loan Processing Charges - Loan ID: ' . $loan->loan_id,
                    'action' => 'charges_income_net'
                ]);

                $referenceNumbers[] = $result['reference_number'];
                $transactionsCount++;

                Log::info('✅ Posted charges income GL transaction', [
                    'reference' => $result['reference_number'],
                    'amount' => $deductions['charges']
                ]);
            }

            // Transaction 3: Insurance Income
            if ($deductions['insurance'] > 0) {
                // DR: Loan Account (Asset)
                // CR: Insurance Income
                $result = $transactionService->postTransaction([
                    'first_account' => $loanAccountNumber,
                    'second_account' => $insuranceAccount,
                    'amount' => $deductions['insurance'],
                    'narration' => 'Loan Insurance Premium - Loan ID: ' . $loan->loan_id,
                    'action' => 'insurance_income_net'
                ]);

                $referenceNumbers[] = $result['reference_number'];
                $transactionsCount++;

                Log::info('✅ Posted insurance income GL transaction', [
                    'reference' => $result['reference_number'],
                    'amount' => $deductions['insurance']
                ]);
            }

            // Transaction 4: First Interest Income
            if ($deductions['first_interest'] > 0) {
                // DR: Loan Account (Asset)
                // CR: Interest Income
                $result = $transactionService->postTransaction([
                    'first_account' => $loanAccountNumber,
                    'second_account' => $interestAccount,
                    'amount' => $deductions['first_interest'],
                    'narration' => 'First Interest - Loan ID: ' . $loan->loan_id,
                    'action' => 'interest_income_net'
                ]);

                $referenceNumbers[] = $result['reference_number'];
                $transactionsCount++;

                Log::info('✅ Posted interest income GL transaction', [
                    'reference' => $result['reference_number'],
                    'amount' => $deductions['first_interest']
                ]);
            }

            Log::info('✅ GL Posting Completed Successfully', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'transactions_count' => $transactionsCount,
                'reference_numbers' => $referenceNumbers
            ]);

            return [
                'success' => true,
                'transactions_count' => $transactionsCount,
                'reference_numbers' => $referenceNumbers
            ];

        } catch (\Exception $e) {
            Log::error('💥 GL Posting Failed', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to post GL transactions: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique loan ID with format AUTO{YEAR}{SEQUENCE}
     */
    private function generateLoanId()
    {
        $year = date('Y');
        $prefix = "AUTO{$year}";
        
        // Get the last loan ID for this year
        $lastLoan = DB::table('loans')
            ->where('loan_id', 'like', $prefix . '%')
            ->orderBy('loan_id', 'desc')
            ->first();
        
        if ($lastLoan) {
            // Extract sequence number and increment
            $lastSequence = intval(substr($lastLoan->loan_id, strlen($prefix)));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Format with leading zeros (6 digits)
        return $prefix . str_pad($newSequence, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Create all loan-related accounts using AccountCreationService
     * Returns array with loan, interest, charges, and insurance account numbers
     */
    private function createLoanAccounts($client, $product, $amount, $loanID)
    {
        Log::info('Creating loan accounts using AccountCreationService', [
            'loan_id' => $loanID,
            'client_number' => $client->client_number,
            'product_id' => $product->sub_product_id
        ]);

        $accounts = [];
        $branchNumber = '001';

        // 1. Create Loan Account
        $loanParentAccountNumber = $this->findParentAccount($product->loan_product_account);
        $loanParentAccount = DB::table('accounts')->where('account_number', $loanParentAccountNumber)->first();

        if (!$loanParentAccount) {
            throw new Exception('Loan parent account not found: ' . $loanParentAccountNumber);
        }

        $loanAccountResult = $this->accountService->createAccount([
            'account_use' => 'internal',
            'account_name' => $loanParentAccount->account_name . ': Loan ID ' . $loanID,
            'type' => 'capital_accounts',
            'product_number' => '0000',
            'branch_number' => $branchNumber,
            'sub_product_number' => $loanID,
            'notes' => 'Auto-Disbursed Loan Account: Loan ID ' . $loanID,
            'client_number' => $client->client_number
        ], $loanParentAccount->account_number);

        $accounts['loan'] = $this->extractAccountNumber($loanAccountResult);

        // 2. Create Interest Income Account
        $interestParentAccountNumber = $this->findParentAccount($product->loan_interest_account);
        $interestParentAccount = DB::table('accounts')->where('account_number', $interestParentAccountNumber)->first();

        if (!$interestParentAccount) {
            throw new Exception('Interest parent account not found: ' . $interestParentAccountNumber);
        }

        $interestAccountResult = $this->accountService->createAccount([
            'account_use' => 'internal',
            'account_name' => $interestParentAccount->account_name . ': Loan ID ' . $loanID,
            'type' => 'capital_accounts',
            'product_number' => '0000',
            'branch_number' => $branchNumber,
            'sub_product_number' => $loanID,
            'notes' => 'Interest Income Account: Loan ID ' . $loanID
        ], $interestParentAccount->account_number);

        $accounts['interest'] = $this->extractAccountNumber($interestAccountResult);

        // 3. Create Charges Income Account
        $chargesParentAccountNumber = $this->findParentAccount($product->loan_charges_account);
        $chargesParentAccount = DB::table('accounts')->where('account_number', $chargesParentAccountNumber)->first();

        if (!$chargesParentAccount) {
            throw new Exception('Charges parent account not found: ' . $chargesParentAccountNumber);
        }

        $chargesAccountResult = $this->accountService->createAccount([
            'account_use' => 'internal',
            'account_name' => $chargesParentAccount->account_name . ': Loan ID ' . $loanID,
            'type' => 'capital_accounts',
            'product_number' => '0000',
            'branch_number' => $branchNumber,
            'sub_product_number' => $loanID,
            'notes' => 'Processing Fees Income Account: Loan ID ' . $loanID
        ], $chargesParentAccount->account_number);

        $accounts['charges'] = $this->extractAccountNumber($chargesAccountResult);

        // 4. Create Insurance Income Account
        $insuranceParentAccountNumber = $this->findParentAccount($product->insurance_account ?: $product->loan_insurance_account);
        $insuranceParentAccount = DB::table('accounts')->where('account_number', $insuranceParentAccountNumber)->first();

        if (!$insuranceParentAccount) {
            throw new Exception('Insurance parent account not found: ' . $insuranceParentAccountNumber);
        }

        $insuranceAccountResult = $this->accountService->createAccount([
            'account_use' => 'internal',
            'account_name' => $insuranceParentAccount->account_name . ': Loan ID ' . $loanID,
            'type' => 'capital_accounts',
            'product_number' => '0000',
            'branch_number' => $branchNumber,
            'sub_product_number' => $loanID,
            'notes' => 'Insurance Income Account: Loan ID ' . $loanID
        ], $insuranceParentAccount->account_number);

        $accounts['insurance'] = $this->extractAccountNumber($insuranceAccountResult);

        Log::info('✅ All loan accounts created successfully', [
            'loan_id' => $loanID,
            'accounts' => $accounts
        ]);

        return $accounts;
    }

    /**
     * Find parent account number with padding if needed
     */
    private function findParentAccount($accountNumber)
    {
        if (!$accountNumber || $accountNumber === 'N/A') {
            throw new Exception('Parent account not configured');
        }

        // Try with padding
        $paddedNumber = str_pad($accountNumber, 16, '0', STR_PAD_LEFT);
        $account = DB::table('accounts')->where('account_number', $paddedNumber)->first();

        if ($account) {
            return $paddedNumber;
        }

        // Try without padding
        $account = DB::table('accounts')->where('account_number', $accountNumber)->first();

        if ($account) {
            return $accountNumber;
        }

        throw new Exception('Parent account not found: ' . $accountNumber);
    }

    /**
     * Extract account number from AccountCreationService result
     */
    private function extractAccountNumber($result)
    {
        $accountNumber = is_array($result)
            ? ($result['account_number'] ?? null)
            : (is_object($result) ? $result->account_number : $result);

        if (!$accountNumber) {
            throw new Exception('Failed to extract account number from result');
        }

        return $accountNumber;
    }
    
    /**
     * Calculate loan details (interest, installments, etc.)
     */
    private function calculateLoanDetails($amount, $product, $tenure)
    {
        $principal = $amount;
        $annualInterestRate = $product->interest_value;
        $monthlyInterestRate = $annualInterestRate / 12 / 100;
        
        // Calculate total interest
        $totalInterest = $principal * $annualInterestRate * $tenure / 12 / 100;
        
        // Calculate monthly installment based on amortization method
        if ($product->amortization_method === 'equal_installments') {
            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * 
                    ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $tenure)) / 
                    (pow(1 + $monthlyInterestRate, $tenure) - 1);
            } else {
                $monthlyInstallment = $principal / $tenure;
            }
        } else {
            // Equal principal method
            $monthlyInstallment = ($principal / $tenure) + ($principal * $monthlyInterestRate);
        }
        
        $totalPayable = $monthlyInstallment * $tenure;
        
        return [
            'interest' => $totalInterest,
            'monthly_installment' => round($monthlyInstallment, 2),
            'total_payable' => round($totalPayable, 2),
            'tenure' => $tenure
        ];
    }
    
    /**
     * Create loan record in database
     */
    private function createLoanRecord($loanId, $loanAccounts, $client, $product, $amount, $loanDetails)
    {
        $loanData = [
            'loan_id' => $loanId,
            'loan_account_number' => $loanAccounts['loan'],
            'interest_account_number' => $loanAccounts['interest'],
            'charge_account_number' => $loanAccounts['charges'],
            'insurance_account_number' => $loanAccounts['insurance'],
            'client_number' => $client->client_number,
            'loan_sub_product' => $product->sub_product_id,
            'principle' => $amount,
            'interest' => $loanDetails['interest'],
            'tenure' => $loanDetails['tenure'],
            'status' => 'APPROVED', // Auto-approved as specified
            'loan_status' => 'APPROVED',
            'loan_type' => 'NEW', // Always NEW as specified
            'loan_type_2' => 'New',
            'approved_loan_value' => $amount,
            'approved_term' => $loanDetails['tenure'],
            'days_in_arrears' => 0,
            'arrears_in_amount' => 0,
            'source' => 'API',
            'approval_stage' => 'Auto-Approved',
            'approval_stage_role_name' => 'System',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $id = DB::table('loans')->insertGetId($loanData);
        
        // Return as object with ID
        $loanData['id'] = $id;
        return (object) $loanData;
    }
    
    /**
     * Calculate all deductions
     */
    private function calculateDeductions($amount, $product)
    {
        $deductions = [
            'charges' => 0,
            'insurance' => 0,
            'first_interest' => 0,
            'total' => 0,
            'breakdown' => []
        ];
        
        Log::info('🔍 Starting Deductions Calculation', [
            'loan_amount' => $amount,
            'product_id' => $product->sub_product_id,
            'product_name' => $product->sub_product_name
        ]);
        
        // Get charges from loan_product_charges table
        $charges = DB::table('loan_product_charges')
            ->where('loan_product_id', $product->sub_product_id)
            ->where('type', 'charge')
            ->get();
        
        Log::info('📋 Charges Found', [
            'product_id' => $product->sub_product_id,
            'charges_count' => $charges->count(),
            'charges_details' => $charges->map(function($charge) {
                return [
                    'id' => $charge->id,
                    'name' => $charge->name,
                    'type' => $charge->type,
                    'value_type' => $charge->value_type,
                    'value' => $charge->value,
                    'min_cap' => $charge->min_cap,
                    'max_cap' => $charge->max_cap
                ];
            })->toArray()
        ]);
        
        foreach ($charges as $charge) {
            $chargeAmount = $this->calculateChargeAmount($charge, $amount);
            $deductions['charges'] += $chargeAmount;
            $deductions['breakdown'][] = [
                'type' => 'charge',
                'name' => $charge->name,
                'amount' => $chargeAmount
            ];
            
            Log::info('💸 Charge Calculated', [
                'charge_name' => $charge->name,
                'charge_id' => $charge->id,
                'value_type' => $charge->value_type,
                'value' => $charge->value,
                'calculated_amount' => $chargeAmount,
                'min_cap' => $charge->min_cap,
                'max_cap' => $charge->max_cap,
                'applied_cap' => $chargeAmount
            ]);
        }
        
        // Get insurance
        $insurances = DB::table('loan_product_charges')
            ->where('loan_product_id', $product->sub_product_id)
            ->where('type', 'insurance')
            ->get();
        
        Log::info('🛡️ Insurance Found', [
            'product_id' => $product->sub_product_id,
            'insurance_count' => $insurances->count(),
            'insurance_details' => $insurances->map(function($insurance) {
                return [
                    'id' => $insurance->id,
                    'name' => $insurance->name,
                    'type' => $insurance->type,
                    'value_type' => $insurance->value_type,
                    'value' => $insurance->value,
                    'min_cap' => $insurance->min_cap,
                    'max_cap' => $insurance->max_cap
                ];
            })->toArray()
        ]);
        
        foreach ($insurances as $insurance) {
            $insuranceAmount = $this->calculateChargeAmount($insurance, $amount);
            $deductions['insurance'] += $insuranceAmount;
            $deductions['breakdown'][] = [
                'type' => 'insurance',
                'name' => $insurance->name,
                'amount' => $insuranceAmount
            ];
            
            Log::info('🛡️ Insurance Calculated', [
                'insurance_name' => $insurance->name,
                'insurance_id' => $insurance->id,
                'value_type' => $insurance->value_type,
                'value' => $insurance->value,
                'calculated_amount' => $insuranceAmount,
                'min_cap' => $insurance->min_cap,
                'max_cap' => $insurance->max_cap,
                'applied_cap' => $insuranceAmount
            ]);
        }
        
        // Calculate first interest if applicable
        if ($product->interest_method === 'flat') {
            $firstInterest = ($amount * $product->interest_value / 100) / 12;
            $deductions['first_interest'] = round($firstInterest, 2);
            $deductions['breakdown'][] = [
                'type' => 'first_interest',
                'name' => 'First Month Interest',
                'amount' => $deductions['first_interest']
            ];
            
            Log::info('📊 First Interest Calculated', [
                'interest_method' => $product->interest_method,
                'interest_rate' => $product->interest_value . '%',
                'loan_amount' => $amount,
                'calculation' => "({$amount} × {$product->interest_value}%) ÷ 12",
                'first_interest_amount' => $deductions['first_interest']
            ]);
        } else {
            Log::info('📊 First Interest Skipped', [
                'interest_method' => $product->interest_method,
                'reason' => 'First interest only calculated for flat interest method'
            ]);
        }
        
        $deductions['total'] = $deductions['charges'] + $deductions['insurance'] + $deductions['first_interest'];
        
        Log::info('💰 Deductions Summary', [
            'total_charges' => $deductions['charges'],
            'total_insurance' => $deductions['insurance'],
            'first_interest' => $deductions['first_interest'],
            'total_deductions' => $deductions['total'],
            'calculation' => "{$deductions['charges']} + {$deductions['insurance']} + {$deductions['first_interest']} = {$deductions['total']}",
            'breakdown_count' => count($deductions['breakdown'])
        ]);
        
        return $deductions;
    }
    
    /**
     * Calculate charge amount based on type
     */
    private function calculateChargeAmount($charge, $loanAmount)
    {
        $originalAmount = 0;
        $finalAmount = 0;
        $capApplied = null;
        
        if ($charge->value_type === 'percentage') {
            $originalAmount = ($loanAmount * $charge->value) / 100;
            
            Log::info('📊 Percentage Charge Calculation', [
                'charge_name' => $charge->name,
                'charge_id' => $charge->id,
                'value_type' => $charge->value_type,
                'percentage' => $charge->value . '%',
                'loan_amount' => $loanAmount,
                'calculation' => "({$loanAmount} × {$charge->value}%) ÷ 100",
                'calculated_amount' => $originalAmount
            ]);
            
            // Apply caps if set
            if ($charge->min_cap && $originalAmount < $charge->min_cap) {
                $finalAmount = $charge->min_cap;
                $capApplied = 'min_cap';
                
                Log::info('📏 Min Cap Applied', [
                    'charge_name' => $charge->name,
                    'calculated_amount' => $originalAmount,
                    'min_cap' => $charge->min_cap,
                    'final_amount' => $finalAmount,
                    'cap_reason' => 'Calculated amount below minimum cap'
                ]);
            } elseif ($charge->max_cap && $originalAmount > $charge->max_cap) {
                $finalAmount = $charge->max_cap;
                $capApplied = 'max_cap';
                
                Log::info('📏 Max Cap Applied', [
                    'charge_name' => $charge->name,
                    'calculated_amount' => $originalAmount,
                    'max_cap' => $charge->max_cap,
                    'final_amount' => $finalAmount,
                    'cap_reason' => 'Calculated amount above maximum cap'
                ]);
            } else {
                $finalAmount = $originalAmount;
                $capApplied = 'none';
                
                Log::info('✅ No Cap Applied', [
                    'charge_name' => $charge->name,
                    'calculated_amount' => $originalAmount,
                    'min_cap' => $charge->min_cap,
                    'max_cap' => $charge->max_cap,
                    'final_amount' => $finalAmount
                ]);
            }
            
        } else {
            // Fixed amount
            $originalAmount = $charge->value;
            $finalAmount = $charge->value;
            $capApplied = 'fixed';
            
            Log::info('💰 Fixed Amount Charge', [
                'charge_name' => $charge->name,
                'charge_id' => $charge->id,
                'value_type' => $charge->value_type,
                'fixed_amount' => $charge->value,
                'final_amount' => $finalAmount
            ]);
        }
        
        $roundedAmount = round($finalAmount, 2);
        
        Log::info('🎯 Final Charge Amount', [
            'charge_name' => $charge->name,
            'charge_id' => $charge->id,
            'original_calculation' => $originalAmount,
            'cap_applied' => $capApplied,
            'final_amount' => $finalAmount,
            'rounded_amount' => $roundedAmount,
            'calculation_summary' => [
                'value_type' => $charge->value_type,
                'value' => $charge->value,
                'loan_amount' => $loanAmount,
                'min_cap' => $charge->min_cap,
                'max_cap' => $charge->max_cap
            ]
        ]);
        
        return $roundedAmount;
    }
    
    /**
     * Get appropriate account for deduction type
     */
    private function getDeductionAccount($type, $product)
    {
        switch ($type) {
            case 'charge':
                return $product->loan_charges_account;
            case 'insurance':
                return $product->loan_insurance_account; // Using penalties account for insurance
            case 'first_interest':
                return $product->loan_interest_account;
            default:
                return null;
        }
    }
    
    /**
     * Create repayment schedule
     */
    private function createRepaymentSchedule($loan, $product)
    {
        $principal = $loan->principle;
        $tenure = $loan->tenure;
        $interestRate = $product->interest_value / 100;
        $monthlyRate = $interestRate / 12;
        
        // Calculate monthly installment
        if ($product->amortization_method === 'equal_installments' && $monthlyRate > 0) {
            $monthlyInstallment = $principal * 
                ($monthlyRate * pow(1 + $monthlyRate, $tenure)) / 
                (pow(1 + $monthlyRate, $tenure) - 1);
        } else {
            $monthlyInstallment = ($principal / $tenure) + ($principal * $monthlyRate);
        }
        
        $balance = $principal;
        $scheduleDate = Carbon::now()->addMonth()->startOfMonth();
        
        for ($i = 1; $i <= $tenure; $i++) {
            $interestAmount = $balance * $monthlyRate;
            $principalAmount = $monthlyInstallment - $interestAmount;
            
            if ($i == $tenure) {
                // Adjust last payment to clear remaining balance
                $principalAmount = $balance;
                $monthlyInstallment = $principalAmount + $interestAmount;
            }
            
            $balance -= $principalAmount;
            
            DB::table('loans_schedules')->insert([
                'loan_id' => $loan->loan_id,
                'installment_date' => $scheduleDate->format('Y-m-d'),
                'principle' => round($principalAmount, 2),
                'interest' => round($interestAmount, 2),
                'installment' => round($monthlyInstallment, 2),
                'opening_balance' => round($balance + $principalAmount, 2),
                'closing_balance' => round(max(0, $balance), 2),
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $scheduleDate->addMonth();
        }
    }
    
    /**
     * Generate control numbers for payment using BillingService
     * Creates a bill record in the bills table for NBC payment gateway integration
     *
     * @param object $client Client object
     * @param object $loan Loan object
     * @return array Array of control numbers
     */
    private function generateControlNumbers($client, $loan)
    {
        try {
            Log::info('🎫 Starting control number generation using BillingService', [
                'loan_id' => $loan->loan_id,
                'client_number' => $client->client_number,
                'loan_amount' => $loan->principle
            ]);

            // Check if there's a loan repayment service defined
            $repaymentService = DB::table('services')
                ->where('code', 'REP')
                ->orWhere('code', 'LOAN_REP')
                ->first();

            if (!$repaymentService) {
                Log::warning('No loan repayment service found - using fallback control number', [
                    'loan_id' => $loan->loan_id
                ]);

                // Fallback to simple control number if service not configured
                return [
                    [
                        'type' => 'REPAYMENT',
                        'number' => 'AUTO' . time() . rand(1000, 9999),
                        'description' => 'Monthly Loan Repayment',
                        'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d')
                    ]
                ];
            }

            // Use service configuration for control number generation
            $isRecurring = $repaymentService->is_recurring ? 1 : 0;
            $paymentMode = $repaymentService->payment_mode;

            Log::info('📋 Generating control number with service configuration', [
                'service_code' => $repaymentService->code,
                'service_id' => $repaymentService->id,
                'service_name' => $repaymentService->name,
                'is_recurring' => $isRecurring,
                'payment_mode' => $paymentMode,
                'client_number' => $client->client_number
            ]);

            // Generate control number using BillingService
            $controlNumber = $this->billingService->generateControlNumber(
                $client->client_number,
                $repaymentService->id,
                $isRecurring,
                $paymentMode
            );

            Log::info('✅ Control number generated', [
                'control_number' => $controlNumber,
                'loan_id' => $loan->loan_id
            ]);

            // Create bill using BillingService to ensure all required fields are set
            try {
                $billId = $this->billingService->createBill(
                    $client->client_number,
                    $repaymentService->id,
                    $isRecurring,
                    $paymentMode,
                    $controlNumber,
                    $loan->principle  // Total loan amount (not monthly installment)
                );

                Log::info('✅ Bill created successfully for loan repayment', [
                    'bill_id' => $billId,
                    'loan_id' => $loan->loan_id,
                    'control_number' => $controlNumber,
                    'amount_due' => $loan->principle,
                    'payment_mode' => $paymentMode,
                    'payment_mode_name' => $this->getPaymentModeName($paymentMode)
                ]);

                // Return control number in expected format
                return [
                    [
                        'type' => 'REPAYMENT',
                        'number' => $controlNumber,
                        'description' => $repaymentService->name ?? 'Loan Repayment',
                        'amount' => $loan->principle,
                        'service_code' => $repaymentService->code,
                        'service_name' => $repaymentService->name,
                        'bill_id' => $billId,
                        'payment_mode' => $paymentMode,
                        'valid_until' => Carbon::now()->addDays(14)->format('Y-m-d')
                    ]
                ];

            } catch (\Exception $billException) {
                Log::error('❌ Error creating bill for loan repayment', [
                    'loan_id' => $loan->loan_id,
                    'control_number' => $controlNumber,
                    'error' => $billException->getMessage(),
                    'trace' => $billException->getTraceAsString()
                ]);

                // Return control number even if bill creation failed
                // This ensures disbursement continues but with limited payment integration
                return [
                    [
                        'type' => 'REPAYMENT',
                        'number' => $controlNumber,
                        'description' => $repaymentService->name ?? 'Loan Repayment',
                        'amount' => $loan->principle,
                        'error' => 'Bill creation failed',
                        'valid_until' => Carbon::now()->addDays(14)->format('Y-m-d')
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('❌ Error generating control number', [
                'loan_id' => $loan->loan_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback to simple control number to not block disbursement
            return [
                [
                    'type' => 'REPAYMENT',
                    'number' => 'AUTO' . time() . rand(1000, 9999),
                    'description' => 'Monthly Loan Repayment',
                    'error' => 'Service lookup failed',
                    'valid_until' => Carbon::now()->addDays(30)->format('Y-m-d')
                ]
            ];
        }
    }

    /**
     * Get payment mode name for logging
     *
     * @param string $mode Payment mode number
     * @return string Payment mode name
     */
    private function getPaymentModeName($mode)
    {
        $modes = [
            '1' => 'Partial',
            '2' => 'Full',
            '3' => 'Exact',
            '4' => 'Limited',
            '5' => 'Infinity (Unlimited Partial Payments)'
        ];

        return $modes[$mode] ?? 'Unknown';
    }
    
    /**
     * Send notifications to client
     */
    private function sendNotifications($client, $loan, $controlNumbers, $netAmount)
    {
        // SMS notification
        if ($client->phone_number) {
            try {
                $message = "Dear {$client->first_name}, your loan {$loan->loan_id} of TZS " .
                          number_format($loan->principle, 2) .
                          " has been approved and disbursed. Net amount: TZS " .
                          number_format($netAmount, 2) .
                          " has been credited to your NBC account {$client->account_number}. " .
                          "Control Number: " . $controlNumbers[0]['number'];

                // Send SMS via NBC SMS Service
                $smsResult = $this->smsService->send(
                    $client->phone_number,
                    $message,
                    $client,
                    [
                        'smsType' => 'TRANSACTIONAL',
                        'serviceName' => 'SACCOSS',
                        'language' => 'English'
                    ]
                );

                Log::info('📱 Loan disbursement SMS sent successfully', [
                    'loan_id' => $loan->loan_id,
                    'client_number' => $client->client_number,
                    'phone' => $client->phone_number,
                    'notification_ref' => $smsResult['notification_ref'] ?? null,
                    'sms_engine_uuid' => $smsResult['sms_engine_uuid'] ?? null
                ]);

            } catch (\Exception $e) {
                Log::error('❌ Failed to send loan disbursement SMS', [
                    'loan_id' => $loan->loan_id,
                    'client_number' => $client->client_number,
                    'phone' => $client->phone_number,
                    'error' => $e->getMessage()
                ]);
                // Don't throw - continue with email notification
            }
        }
        
        // Email notification
        if ($client->email) {
            try {
                // Prepare email data
                $emailData = [
                    'to' => $client->email,
                    'to_name' => $client->first_name . ' ' . $client->last_name,
                    'subject' => 'Loan Disbursement Notification - ' . $loan->loan_id,
                    'client_name' => $client->first_name,
                    'loan_id' => $loan->loan_id,
                    'loan_amount' => number_format($loan->principle, 2),
                    'net_amount' => number_format($netAmount, 2),
                    'nbc_account' => $client->account_number,
                    'control_number' => $controlNumbers[0]['number'],
                    'repayment_amount' => number_format($loan->principle + $loan->interest, 2),
                    'repayment_date' => $controlNumbers[0]['valid_until'] ?? Carbon::now()->addMonth()->format('Y-m-d'),
                    'company_name' => config('app.name', 'SACCOS Core System')
                ];
                
                // Send email using Mail facade
                \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($emailData) {
                    $message->to($emailData['to'], $emailData['to_name'])
                            ->subject($emailData['subject'])
                            ->html($this->generateEmailHtml($emailData));
                });
                
                // Log successful email
                Log::info('Loan disbursement email sent', [
                    'email' => $client->email,
                    'loan_id' => $loan->loan_id,
                    'status' => 'sent'
                ]);
                
                // Store in emails table for tracking
                DB::table('emails')->insert([
                    'recipient_email' => $emailData['to'],
                    'subject' => $emailData['subject'],
                    'body' => $this->generateEmailHtml($emailData),
                    'is_sent' => true,
                    'sent_at' => now(),
                    'folder' => 'sent',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to send loan disbursement email', [
                    'email' => $client->email,
                    'loan_id' => $loan->loan_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Generate HTML email content
     */
    private function generateEmailHtml($data)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Loan Disbursement Notification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; margin-top: 20px; }
                .details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; }
                .amount { font-size: 24px; color: #4CAF50; font-weight: bold; }
                .footer { margin-top: 20px; padding: 20px; background-color: #333; color: white; text-align: center; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 8px; }
                .label { font-weight: bold; width: 40%; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . $data['company_name'] . '</h1>
                    <h2>Loan Disbursement Notification</h2>
                </div>
                
                <div class="content">
                    <p>Dear <strong>' . $data['client_name'] . '</strong>,</p>
                    
                    <p>We are pleased to inform you that your loan application has been <strong>APPROVED</strong> and the funds have been successfully disbursed to your account.</p>
                    
                    <div class="details">
                        <h3>Loan Details:</h3>
                        <table>
                            <tr>
                                <td class="label">Loan ID:</td>
                                <td><strong>' . $data['loan_id'] . '</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Loan Amount:</td>
                                <td>TZS ' . $data['loan_amount'] . '</td>
                            </tr>
                            <tr>
                                <td class="label">Net Amount Disbursed:</td>
                                <td class="amount">TZS ' . $data['net_amount'] . '</td>
                            </tr>
                            <tr>
                                <td class="label">Credited to Account:</td>
                                <td>' . $data['nbc_account'] . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="details">
                        <h3>Repayment Information:</h3>
                        <table>
                            <tr>
                                <td class="label">Control Number:</td>
                                <td><strong style="color: #FF5722;">' . $data['control_number'] . '</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Total Repayment Amount:</td>
                                <td><strong>TZS ' . $data['repayment_amount'] . '</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Payment Due Date:</td>
                                <td>' . $data['repayment_date'] . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p><strong>Important Notes:</strong></p>
                    <ul>
                        <li>Please use the control number above for all loan repayments</li>
                        <li>Ensure timely repayment to maintain a good credit history</li>
                        <li>The net amount has been credited to your NBC account after deducting applicable fees</li>
                        <li>For any queries, please contact our customer service</li>
                    </ul>
                    
                    <p>Thank you for choosing ' . $data['company_name'] . '.</p>
                </div>
                
                <div class="footer">
                    <p>This is an automated notification. Please do not reply to this email.</p>
                    <p>&copy; ' . date('Y') . ' ' . $data['company_name'] . '. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Update loan status after disbursement
     * Sets status to ACTIVE to indicate the loan is now active and accepting repayments
     */
    private function updateLoanStatus($loanId, $status, $nbcTransferResult, $netAmount, $totalDeductions)
    {
        DB::table('loans')->where('id', $loanId)->update([
            'status' => $status,
            'loan_status' => $status,
            'disbursement_date' => now(),
            'net_disbursement_amount' => $netAmount,
            'total_deductions' => $totalDeductions,
            'nbc_transfer_reference' => $nbcTransferResult['nbc_reference'] ?? null,
            'updated_at' => now()
        ]);

        Log::info('✅ Loan status updated', [
            'loan_id' => $loanId,
            'status' => $status,
            'net_disbursement' => $netAmount,
            'total_deductions' => $totalDeductions,
            'nbc_reference' => $nbcTransferResult['nbc_reference'] ?? null
        ]);
    }
}