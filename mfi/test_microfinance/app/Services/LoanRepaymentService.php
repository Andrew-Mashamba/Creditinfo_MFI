<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\LoansModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Professional Loan Repayment Service
 * 
 * Handles all loan repayment operations including:
 * - Payment allocation (FIFO: Penalties -> Interest -> Principal)
 * - Partial and full payments
 * - Early settlement
 * - Overpayments and advance payments
 * - Payment reversal
 * - Receipt generation
 * 
 * @package App\Services
 * @version 2.0
 */
class LoanRepaymentService
{
    private $transactionService;
    private $penaltyReceivableAccount;
    private $interestReceivableCurrent;
    private $interestReceivableOverdue;

    /**
     * Payment allocation priorities
     */
    const ALLOCATION_PRIORITY = [
        'PENALTY' => 1,
        'INTEREST' => 2,
        'PRINCIPAL' => 3
    ];

    /**
     * Payment methods
     */
    const PAYMENT_METHODS = [
        'CASH' => 'Cash Payment',
        'BANK' => 'Bank Transfer',
        'MOBILE' => 'Mobile Money',
        'INTERNAL' => 'Internal Transfer',
        'SALARY' => 'Salary Deduction'
    ];

    public function __construct(TransactionPostingService $transactionService = null)
    {
        $this->transactionService = $transactionService ?: new TransactionPostingService();
        $this->loadInstitutionAccounts();
    }

    /**
     * Load GL accounts from institutions table
     */
    protected function loadInstitutionAccounts(): void
    {
        $institution = DB::table('institutions')->where('id', 1)->first();

        if ($institution) {
            $this->penaltyReceivableAccount = $institution->penalty_receivable_account ?? '010000014020';
            $this->interestReceivableCurrent = $institution->interest_receivable_current_account ?? '0101100014001410';
            $this->interestReceivableOverdue = $institution->interest_receivable_overdue_account ?? '0101100014001420';
        } else {
            // Fallback to defaults if institution not found
            $this->penaltyReceivableAccount = '010000014020';
            $this->interestReceivableCurrent = '0101100014001410';
            $this->interestReceivableOverdue = '0101100014001420';
        }
    }

    /**
     * Get penalty receivable account number
     */
    public function getPenaltyReceivableAccount(): string
    {
        return $this->penaltyReceivableAccount;
    }
    
    /**
     * Process loan repayment with professional allocation logic
     * 
     * @param string $loanId The loan ID or account number
     * @param float $amount The payment amount
     * @param string $paymentMethod The payment method
     * @param array $paymentDetails Additional payment details
     * @return array Payment result with breakdown
     */
    public function processRepayment($loanId, $amount, $paymentMethod = 'CASH', $paymentDetails = [])
    {
        // Log repayment initiation
        Log::info('🔵 LOAN REPAYMENT INITIATED', [
            'loan_id' => $loanId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'timestamp' => now()->toDateTimeString(),
            'user' => auth()->user()->name ?? 'System'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Validate and fetch loan
            $loan = $this->validateAndFetchLoan($loanId);
            
            Log::info('📋 Loan details retrieved', [
                'loan_id' => $loan->loan_id,
                'client_number' => $loan->client_number,
                'principal' => $loan->principle,
                'status' => $loan->status,
                'tenure' => $loan->tenure
            ]);
            
            // Check loan status - only ACTIVE and RESTRUCTURED loans can be repaid
            if (!in_array($loan->status, ['ACTIVE', 'RESTRUCTURED'])) {
                Log::warning('❌ Invalid loan status for repayment', [
                    'loan_id' => $loan->loan_id,
                    'current_status' => $loan->status,
                    'allowed_statuses' => ['ACTIVE', 'RESTRUCTURED']
                ]);
                throw new Exception("Loan is not active for repayment. Current status: {$loan->status}. Only ACTIVE or RESTRUCTURED loans can be repaid.");
            }
            
            // Get outstanding balances
            $outstandingBalances = $this->calculateOutstandingBalances($loan);
            
            Log::info('💰 Outstanding balances calculated', [
                'loan_id' => $loan->loan_id,
                'principal' => $outstandingBalances['principal'],
                'interest' => $outstandingBalances['interest'],
                'penalties' => $outstandingBalances['penalties'],
                'total' => $outstandingBalances['total'],
                'schedules_count' => $outstandingBalances['schedules_count']
            ]);
            
            // Check for overpayment
            if ($amount > $outstandingBalances['total']) {
                $overpayment = $amount - $outstandingBalances['total'];
                Log::info("💵 Overpayment detected", [
                    'loan_id' => $loan->loan_id,
                    'payment' => $amount,
                    'outstanding' => $outstandingBalances['total'],
                    'overpayment' => $overpayment
                ]);
            }
            
            // Allocate payment
            $allocation = $this->allocatePayment($amount, $outstandingBalances);
            
            Log::info('📊 Payment allocation completed', [
                'loan_id' => $loan->loan_id,
                'total_payment' => $amount,
                'allocation' => [
                    'penalties' => $allocation['penalties'],
                    'interest' => $allocation['interest'],
                    'principal' => $allocation['principal'],
                    'overpayment' => $allocation['overpayment'] ?? 0
                ]
            ]);
            
            // Update loan schedules
            $this->updateLoanSchedules($loan, $allocation);
            
            Log::info('📅 Loan schedules updated', [
                'loan_id' => $loan->loan_id,
                'schedules_affected' => DB::table('loans_schedules')
                    ->where('loan_id', $loan->loan_id)
                    ->whereIn('completion_status', ['PARTIAL', 'PAID'])
                    ->count()
            ]);
            
            // Process accounting transactions
            $this->processAccountingTransactions($loan, $allocation, $paymentMethod, $paymentDetails);
            
            Log::info('📑 Accounting transactions processed', [
                'loan_id' => $loan->loan_id,
                'payment_method' => $paymentMethod,
                'transactions' => [
                    'penalties' => $allocation['penalties'] > 0,
                    'interest' => $allocation['interest'] > 0,
                    'principal' => $allocation['principal'] > 0
                ]
            ]);
            
            // Record payment history
            $paymentRecord = $this->recordPaymentHistory($loan, $amount, $allocation, $paymentMethod, $paymentDetails);
            
            Log::info('📝 Payment history recorded', [
                'loan_id' => $loan->loan_id,
                'payment_id' => $paymentRecord->id,
                'receipt_number' => $paymentRecord->receipt_number,
                'amount' => $amount
            ]);
            
            // Update loan status if fully paid
            $this->updateLoanStatus($loan, $amount, $paymentRecord->receipt_number);
            
            // Generate receipt
            $receipt = $this->generateReceipt($loan, $paymentRecord, $allocation);
            
            Log::info('🧾 Receipt generated', [
                'loan_id' => $loan->loan_id,
                'receipt_number' => $receipt['receipt_number'],
                'outstanding_after_payment' => $receipt['outstanding_balance']
            ]);
            
            DB::commit();
            
            Log::info('✅ LOAN REPAYMENT COMPLETED SUCCESSFULLY', [
                'loan_id' => $loan->loan_id,
                'receipt_number' => $receipt['receipt_number'],
                'amount_paid' => $amount,
                'new_outstanding' => $receipt['outstanding_balance'],
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Send notifications
            $this->sendPaymentNotifications($loan, $paymentRecord, $receipt);
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment_id' => $paymentRecord->id,
                'receipt_number' => $receipt['receipt_number'],
                'loan_id' => $loan->loan_id,
                'amount_paid' => $amount,
                'allocation' => $allocation,
                'outstanding_balance' => $this->calculateOutstandingBalances($loan),
                'receipt' => $receipt
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('❌ LOAN REPAYMENT FAILED', [
                'loan_id' => $loanId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'error' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => basename($e->getFile()),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Validate and fetch loan details
     */
    private function validateAndFetchLoan($loanId)
    {
        // Try to find by loan_id first, then by loan_account_number
        $loan = DB::table('loans')
            ->where('loan_id', $loanId)
            ->orWhere('loan_account_number', $loanId)
            ->first();
        
        if (!$loan) {
            throw new Exception("Loan not found: {$loanId}");
        }
        
        return $loan;
    }
    
    /**
     * Calculate all outstanding balances
     */
    public function calculateOutstandingBalances($loan)
    {
        // First try with numeric ID (most common), then string ID
        $schedules = DB::table('loans_schedules')
            ->where('loan_id', (string)$loan->id)
            ->whereIn('completion_status', ['PENDING', 'PARTIAL', 'ACTIVE', 'NOT PAID'])
            ->get();
            
        // If no schedules found, try with string loan_id
        if ($schedules->isEmpty()) {
            $schedules = DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id)
                ->whereIn('completion_status', ['PENDING', 'PARTIAL', 'ACTIVE', 'NOT PAID'])
                ->get();
        }
        
        $penalties = 0;
        $interest = 0;
        $principal = 0;
        
        foreach ($schedules as $schedule) {
            // Calculate penalties (if any)
            if ($this->isOverdue($schedule)) {
                $penalties += $this->calculatePenalty($schedule, $loan);
            }
            
            // Outstanding interest
            $interest += ($schedule->interest - ($schedule->interest_payment ?? 0));
            
            // Outstanding principal
            $principal += ($schedule->principle - ($schedule->principle_payment ?? 0));
        }
        
        return [
            'penalties' => round($penalties, 2),
            'interest' => round($interest, 2),
            'principal' => round($principal, 2),
            'total' => round($penalties + $interest + $principal, 2),
            'schedules_count' => $schedules->count()
        ];
    }
    
    /**
     * Check if schedule is overdue
     */
    private function isOverdue($schedule)
    {
        return Carbon::parse($schedule->installment_date)->isPast() && 
               $schedule->completion_status !== 'PAID';
    }
    
    /**
     * Calculate penalty for overdue payment
     */
    private function calculatePenalty($schedule, $loan)
    {
        $daysOverdue = Carbon::parse($schedule->installment_date)->diffInDays(now());
        $outstandingAmount = $schedule->installment - ($schedule->payment ?? 0);
        
        // Get penalty rate from loan product
        $product = DB::table('loan_sub_products')
            ->where('sub_product_id', $loan->loan_sub_product)
            ->first();
        
        if (!$product || !$product->penalty_value) {
            return 0;
        }
        
        // Calculate penalty (daily rate)
        $dailyPenaltyRate = $product->penalty_value / 30 / 100; // Monthly rate to daily
        $penalty = $outstandingAmount * $dailyPenaltyRate * $daysOverdue;
        
        // Cap penalty at maximum if configured
        if ($product->penalty_max_cap) {
            $penalty = min($penalty, $product->penalty_max_cap);
        }
        
        return $penalty;
    }
    
    /**
     * Allocate payment according to priority (Penalties -> Interest -> Principal)
     */
    private function allocatePayment($amount, $outstandingBalances)
    {
        $remainingAmount = $amount;
        $allocation = [
            'penalties' => 0,
            'interest' => 0,
            'principal' => 0,
            'overpayment' => 0
        ];
        
        // 1. Pay penalties first
        if ($remainingAmount > 0 && $outstandingBalances['penalties'] > 0) {
            $penaltyPayment = min($remainingAmount, $outstandingBalances['penalties']);
            $allocation['penalties'] = $penaltyPayment;
            $remainingAmount -= $penaltyPayment;
        }
        
        // 2. Pay interest next
        if ($remainingAmount > 0 && $outstandingBalances['interest'] > 0) {
            $interestPayment = min($remainingAmount, $outstandingBalances['interest']);
            $allocation['interest'] = $interestPayment;
            $remainingAmount -= $interestPayment;
        }
        
        // 3. Pay principal
        if ($remainingAmount > 0 && $outstandingBalances['principal'] > 0) {
            $principalPayment = min($remainingAmount, $outstandingBalances['principal']);
            $allocation['principal'] = $principalPayment;
            $remainingAmount -= $principalPayment;
        }
        
        // 4. Handle overpayment
        if ($remainingAmount > 0) {
            $allocation['overpayment'] = $remainingAmount;
        }
        
        return $allocation;
    }
    
    /**
     * Update loan schedules with payment allocation
     * Following the pattern from FrontDesk repayment
     */
    private function updateLoanSchedules($loan, $allocation)
    {
        // Get total amount to allocate (penalties + interest + principal)
        $amount = $allocation['penalties'] + $allocation['interest'] + $allocation['principal'];
        
        // First try with numeric ID (most common), then string ID
        $schedules = DB::table('loans_schedules')
            ->where('loan_id', (string)$loan->id)
            ->whereIn('completion_status', ['ACTIVE', 'PENDING', 'PARTIAL'])
            ->orderBy('installment_date', 'asc')
            ->get();
        
        // If no schedules found, try with string loan_id
        if ($schedules->isEmpty()) {
            $schedules = DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id)
                ->whereIn('completion_status', ['ACTIVE', 'PENDING', 'PARTIAL'])
                ->orderBy('installment_date', 'asc')
                ->get();
        }
        
        Log::info('Updating loan schedules', [
            'loan_id' => $loan->loan_id,
            'schedules_found' => $schedules->count(),
            'amount_to_allocate' => $amount
        ]);
        
        foreach ($schedules as $schedule) {
            // Skip if installment is 0
            if ($schedule->installment == 0) {
                continue;
            }
            
            // Initialize payment values
            $interest_payment = 0;
            $principal_payment = 0;
            
            // Get current payment values (handle NULL)
            $current_interest_payment = is_numeric($schedule->interest_payment) ? (float)$schedule->interest_payment : 0;
            $current_principal_payment = is_numeric($schedule->principle_payment) ? (float)$schedule->principle_payment : 0;
            
            // Pay off the interest first
            $outstanding_interest = (float)$schedule->interest - $current_interest_payment;
            if ($amount > 0 && $outstanding_interest > 0) {
                if ($amount >= $outstanding_interest) {
                    $interest_payment = $outstanding_interest;
                    $amount -= $interest_payment;
                } else {
                    $interest_payment = $amount;
                    $amount = 0;
                }
            }
            
            // Pay off the principal next
            $outstanding_principal = (float)$schedule->principle - $current_principal_payment;
            if ($amount > 0 && $outstanding_principal > 0) {
                if ($amount >= $outstanding_principal) {
                    $principal_payment = $outstanding_principal;
                    $amount -= $principal_payment;
                } else {
                    $principal_payment = $amount;
                    $amount = 0;
                }
            }
            
            // Only update if there's a payment to record
            if ($interest_payment > 0 || $principal_payment > 0) {
                // Calculate new totals
                $new_interest_payment = $current_interest_payment + $interest_payment;
                $new_principal_payment = $current_principal_payment + $principal_payment;
                $total_payment = $new_interest_payment + $new_principal_payment;
                
                // Determine the completion status (using floor to handle floating point)
                $completion_status = floor($total_payment * 100) / 100 >= floor($schedule->installment * 100) / 100 ? 'PAID' : 'PARTIAL';
                
                // Update the schedule record
                $updateResult = DB::table('loans_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'interest_payment' => $new_interest_payment,
                        'principle_payment' => $new_principal_payment,
                        'payment' => $total_payment,
                        'completion_status' => $completion_status,
                        'updated_at' => now()
                    ]);
                
                Log::info('Schedule updated', [
                    'schedule_id' => $schedule->id,
                    'interest_paid' => $interest_payment,
                    'principal_paid' => $principal_payment,
                    'total_paid' => $total_payment,
                    'status' => $completion_status,
                    'update_result' => $updateResult
                ]);
            }
            
            // If the remaining amount is exhausted, break out of the loop
            if ($amount <= 0) {
                break;
            }
        }
    }
    
    /**
     * Process accounting transactions
     *
     * First Account: NBC bank account mirror (from bank_accounts where id=1)
     * Second Accounts: From loans table - loan_account_number, interest_account_number, charge_account_number
     */
    private function processAccountingTransactions($loan, $allocation, $paymentMethod, $paymentDetails)
    {
        // Get NBC bank account mirror (source of funds - money deposited into real NBC account)
        $nbcBankAccount = DB::table('bank_accounts')->where('id', 1)->first();

        if (!$nbcBankAccount || !$nbcBankAccount->internal_mirror_account_number) {
            Log::warning('NBC bank account mirror not found - skipping GL posting', [
                'loan_id' => $loan->loan_id
            ]);
            return;
        }

        $sourceMirrorAccount = $nbcBankAccount->internal_mirror_account_number;

        Log::info('Processing loan repayment GL transactions', [
            'loan_id' => $loan->loan_id,
            'nbc_mirror_account' => $sourceMirrorAccount,
            'loan_account' => $loan->loan_account_number,
            'interest_account' => $loan->interest_account_number,
            'charge_account' => $loan->charge_account_number,
            'allocation' => $allocation
        ]);

        // Process principal payment: DEBIT NBC mirror, CREDIT loan account
        if ($allocation['principal'] > 0 && $loan->loan_account_number) {
            $this->postTransaction(
                $sourceMirrorAccount,  // First account: NBC bank mirror
                $loan->loan_account_number,  // Second account: Loan account (reduces loan asset)
                $allocation['principal'],
                "Principal payment for loan {$loan->loan_id}"
            );
        }

        // Process interest payment: Clear the interest receivable (accrued income)
        // Income was already recognized when interest was accrued (DR Interest Receivable / CR Interest Income)
        // Now payment received: DR Cash/Bank (via mirror) / CR Interest Receivable
        if ($allocation['interest'] > 0) {
            // Determine which interest receivable to clear based on loan arrears status
            $daysInArrears = $loan->days_in_arrears ?? 0;
            $interestReceivableAccount = $daysInArrears > 0
                ? $this->interestReceivableOverdue
                : $this->interestReceivableCurrent;

            $this->postTransaction(
                $sourceMirrorAccount,  // First account: NBC bank mirror (DEBIT - increases cash)
                $interestReceivableAccount,  // Second account: Interest Receivable (CREDIT - clears receivable)
                $allocation['interest'],
                "Interest payment received for loan {$loan->loan_id} - clearing accrued receivable"
            );

            Log::info('[INTEREST_PAYMENT] Cleared interest receivable', [
                'loan_id' => $loan->loan_id,
                'amount' => $allocation['interest'],
                'debit_account' => $sourceMirrorAccount,
                'credit_account' => $interestReceivableAccount,
                'days_in_arrears' => $daysInArrears
            ]);
        }

        // Process penalty/charges payment: Clear the penalty receivable (accrued income)
        // Income was already recognized when penalty was accrued (DR Penalty Receivable / CR Late Fee Income)
        // Now payment received: DR Cash/Bank (via mirror) / CR Penalty Receivable
        if ($allocation['penalties'] > 0) {
            $this->postTransaction(
                $sourceMirrorAccount,  // First account: NBC bank mirror (DEBIT - increases cash)
                $this->penaltyReceivableAccount,  // Second account: Penalty Receivable (CREDIT - clears receivable)
                $allocation['penalties'],
                "Penalty payment received for loan {$loan->loan_id} - clearing accrued receivable"
            );

            Log::info('[PENALTY_PAYMENT] Cleared penalty receivable', [
                'loan_id' => $loan->loan_id,
                'amount' => $allocation['penalties'],
                'debit_account' => $sourceMirrorAccount,
                'credit_account' => $this->penaltyReceivableAccount
            ]);
        }

        // Handle overpayment
        if ($allocation['overpayment'] > 0) {
            $this->handleOverpayment($loan, $allocation['overpayment'], $sourceMirrorAccount);
        }
    }
    
    /**
     * Get cash account based on payment method
     */
    private function getCashAccount($paymentMethod, $paymentDetails)
    {
        switch ($paymentMethod) {
            case 'BANK':
                return $paymentDetails['bank_account'] ?? 'BANK_ACCOUNT';
            case 'MOBILE':
                return $paymentDetails['mobile_account'] ?? 'MOBILE_MONEY_ACCOUNT';
            case 'INTERNAL':
                return $paymentDetails['source_account'] ?? 'INTERNAL_ACCOUNT';
            case 'SALARY':
                return 'SALARY_DEDUCTION_ACCOUNT';
            default:
                return 'CASH_ACCOUNT';
        }
    }
    
    /**
     * Get loan account
     */
    private function getLoanAccount($loan)
    {
        $account = DB::table('accounts')
            ->where('account_number', $loan->loan_account_number)
            ->first();
        
        return $account ? $account->sub_category_code : $loan->loan_account_number;
    }
    
    /**
     * Get interest account
     */
    private function getInterestAccount($loan)
    {
        // Get from loan record or use default
        if ($loan->interest_account_number) {
            $account = DB::table('accounts')
                ->where('account_number', $loan->interest_account_number)
                ->first();
            return $account ? $account->sub_category_code : $loan->interest_account_number;
        }
        
        // Get from product configuration
        $product = DB::table('loan_sub_products')
            ->where('sub_product_id', $loan->loan_sub_product)
            ->first();
        
        return $product ? $product->collection_account_loan_interest : 'INTEREST_INCOME_ACCOUNT';
    }
    
    /**
     * Get penalty account
     */
    private function getPenaltyAccount($loan)
    {
        $product = DB::table('loan_sub_products')
            ->where('sub_product_id', $loan->loan_sub_product)
            ->first();
        
        return $product ? $product->collection_account_loan_penalties : 'PENALTY_INCOME_ACCOUNT';
    }
    
    /**
     * Post transaction to general ledger
     */
    private function postTransaction($sourceAccount, $destinationAccount, $amount, $narration)
    {
        if ($amount <= 0) {
            return;
        }

        try {
            Log::info('Posting loan repayment transaction', [
                'source_account' => $sourceAccount,
                'destination_account' => $destinationAccount,
                'amount' => $amount,
                'narration' => $narration
            ]);

            // Transaction data format expected by TransactionPostingService
            $transactionData = [
                'first_account' => $destinationAccount,  // Account to be credited
                'second_account' => $sourceAccount,      // Account to be debited
                'source_account' => $sourceAccount,
                'destination_account' => $destinationAccount,
                'amount' => $amount,
                'narration' => $narration,
            ];

            $response = $this->transactionService->postTransaction($transactionData);

            Log::info('GL transaction posted successfully', [
                'reference_number' => $response['reference_number'] ?? null,
                'narration' => $narration
            ]);

            return $response;

        } catch (Exception $e) {
            Log::error('Transaction posting failed', [
                'error' => $e->getMessage(),
                'source_account' => $sourceAccount,
                'destination_account' => $destinationAccount,
                'amount' => $amount,
                'narration' => $narration,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - let repayment complete even if GL posting fails
            // GL can be corrected later
        }
    }
    
    /**
     * Handle overpayment - credit to member's deposit account (product_number = 3000)
     */
    private function handleOverpayment($loan, $amount, $sourceAccount)
    {
        if ($amount <= 0) {
            return;
        }

        Log::info('💰 Processing overpayment', [
            'loan_id' => $loan->loan_id,
            'client_number' => $loan->client_number,
            'overpayment_amount' => $amount
        ]);

        try {
            // Get member's deposit account (product_number = 3000) with the lowest id
            $memberDepositAccount = DB::table('accounts')
                ->where('client_number', $loan->client_number)
                ->where('product_number', 3000)
                ->orderBy('id', 'asc')
                ->first();

            if (!$memberDepositAccount) {
                Log::warning('⚠️ Member deposit account not found for overpayment', [
                    'client_number' => $loan->client_number,
                    'loan_id' => $loan->loan_id,
                    'overpayment_amount' => $amount
                ]);
                return;
            }

            Log::info('📍 Member deposit account found', [
                'account_number' => $memberDepositAccount->account_number,
                'account_name' => $memberDepositAccount->account_name,
                'account_id' => $memberDepositAccount->id
            ]);

            // Post GL transaction: DEBIT NBC mirror, CREDIT member deposit account
            $this->postTransaction(
                $sourceAccount,  // First account: NBC bank mirror (already debited for the payment)
                $memberDepositAccount->account_number,  // Second account: Member deposit account
                $amount,
                "Overpayment refund to deposit account for loan {$loan->loan_id}"
            );

            Log::info('✅ Overpayment credited to member deposit account', [
                'loan_id' => $loan->loan_id,
                'client_number' => $loan->client_number,
                'deposit_account' => $memberDepositAccount->account_number,
                'amount' => $amount
            ]);

        } catch (Exception $e) {
            Log::error('❌ Failed to process overpayment', [
                'loan_id' => $loan->loan_id,
                'client_number' => $loan->client_number,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            // Don't throw - overpayment can be handled manually later
        }
    }
    
    /**
     * Record payment history
     */
    private function recordPaymentHistory($loan, $amount, $allocation, $paymentMethod, $paymentDetails)
    {
        $paymentData = [
            'loan_id' => $loan->loan_id,
            'payment_date' => now(),
            'amount' => $amount,
            'principal_paid' => $allocation['principal'],
            'interest_paid' => $allocation['interest'],
            'penalties_paid' => $allocation['penalties'],
            'overpayment' => $allocation['overpayment'] ?? 0,
            'payment_method' => $paymentMethod,
            'reference_number' => $paymentDetails['reference'] ?? null,
            'receipt_number' => $this->generateReceiptNumber(),
            'processed_by' => auth()->user()->name ?? 'SYSTEM',
            'created_at' => now(),
            'updated_at' => now()
        ];

        $paymentId = DB::table('loan_payments')->insertGetId($paymentData);
        
        // Return payment record
        return (object) array_merge($paymentData, ['id' => $paymentId]);
    }
    
    /**
     * Update loan status based on payment
     * Close loan when outstanding balance is zero
     */
    private function updateLoanStatus($loan, $paymentAmount = null, $receiptNumber = null)
    {
        // Calculate current outstanding balance
        $outstandingBalances = $this->calculateOutstandingBalances($loan);

        // Check if all schedules are marked as "PAID" - try numeric ID first
        $remainingSchedules = DB::table('loans_schedules')
            ->where('loan_id', (string)$loan->id)
            ->where('completion_status', '!=', 'PAID')
            ->count();

        // If no schedules found with numeric ID, try string loan_id
        if (DB::table('loans_schedules')->where('loan_id', (string)$loan->id)->count() == 0) {
            $remainingSchedules = DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id)
                ->where('completion_status', '!=', 'PAID')
                ->count();
        }

        // Log for debugging
        Log::info('Loan status check', [
            'loan_id' => $loan->loan_id,
            'remaining_schedules' => $remainingSchedules,
            'outstanding_balance' => $outstandingBalances['total']
        ]);

        // Close loan if all schedules are paid AND outstanding balance is zero
        if ($remainingSchedules === 0 && $outstandingBalances['total'] <= 0) {
            // All schedules paid and no outstanding balance - close the loan
            DB::table('loans')
                ->where('id', $loan->id)
                ->update([
                    'status' => 'CLOSED',
                    'loan_status' => 'CLOSED',
                    'closure_date' => now()->toDateString(),
                    'updated_at' => now()
                ]);

            Log::info('✅ LOAN CLOSED - Fully paid with zero outstanding balance', [
                'loan_id' => $loan->loan_id,
                'final_payment' => $paymentAmount,
                'receipt' => $receiptNumber,
                'closure_date' => now()->toDateString()
            ]);
        } else {
            // Update days in arrears
            $this->updateArrearsStatus($loan);
        }
    }
    
    /**
     * Update arrears status
     */
    private function updateArrearsStatus($loan)
    {
        $overdueSchedule = DB::table('loans_schedules')
            ->where('loan_id', $loan->loan_id)
            ->where('completion_status', '!=', 'PAID')
            ->where('installment_date', '<', now())
            ->orderBy('installment_date', 'asc')
            ->first();
        
        if ($overdueSchedule) {
            $daysInArrears = Carbon::parse($overdueSchedule->installment_date)->diffInDays(now());
            $arrearsAmount = DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id)
                ->where('completion_status', '!=', 'PAID')
                ->where('installment_date', '<', now())
                ->sum(DB::raw('installment - payment'));
            
            DB::table('loans')
                ->where('id', $loan->id)
                ->update([
                    'days_in_arrears' => $daysInArrears,
                    'arrears_in_amount' => $arrearsAmount,
                    'updated_at' => now()
                ]);
        } else {
            // No arrears
            DB::table('loans')
                ->where('id', $loan->id)
                ->update([
                    'days_in_arrears' => 0,
                    'arrears_in_amount' => 0,
                    'updated_at' => now()
                ]);
        }
    }
    
    /**
     * Generate receipt for payment
     */
    private function generateReceipt($loan, $payment, $allocation)
    {
        $member = DB::table('clients')
            ->where('client_number', $loan->client_number)
            ->first();
        
        $outstandingBalance = $this->calculateOutstandingBalances($loan);
        
        return [
            'receipt_number' => $payment->receipt_number,
            'payment_date' => $payment->payment_date,
            'loan_id' => $loan->loan_id,
            'member_name' => $member ? "{$member->first_name} {$member->last_name}" : 'N/A',
            'member_number' => $loan->client_number,
            'amount_paid' => $payment->amount,
            'payment_breakdown' => [
                'penalties' => $allocation['penalties'],
                'interest' => $allocation['interest'],
                'principal' => $allocation['principal'],
                'overpayment' => $allocation['overpayment'] ?? 0
            ],
            'payment_method' => self::PAYMENT_METHODS[$payment->payment_method] ?? $payment->payment_method,
            'reference_number' => $payment->reference_number,
            'outstanding_balance' => $outstandingBalance['total'],
            'processed_by' => auth()->user()->name ?? 'System',
            'branch' => auth()->user()->branch_id ?? 1,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber()
    {
        $prefix = 'RCP';
        $date = now()->format('Ymd');
        $sequence = DB::table('loan_payments')
            ->whereDate('created_at', now())
            ->count() + 1;
        
        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }
    
    /**
     * Send payment notifications
     */
    private function sendPaymentNotifications($loan, $payment, $receipt)
    {
        $member = DB::table('clients')
            ->where('client_number', $loan->client_number)
            ->first();

        if (!$member) {
            return;
        }

        // SMS notification
        if ($member->phone_number) {
            try {
                $message = sprintf(
                    "Dear %s, payment of TZS %s received for loan %s. Outstanding balance: TZS %s. Receipt: %s. Thank you.",
                    $member->first_name,
                    number_format($payment->amount, 0),
                    $loan->loan_id,
                    number_format($receipt['outstanding_balance'], 0),
                    $receipt['receipt_number']
                );

                // Send SMS using SmsService
                $smsService = new SmsService();
                $smsResult = $smsService->send($member->phone_number, $message, $member, [
                    'smsType' => 'TRANSACTIONAL',
                    'serviceName' => 'MFI',
                    'language' => 'English'
                ]);

                Log::info('✅ SMS notification sent successfully', [
                    'phone' => $member->phone_number,
                    'loan_id' => $loan->loan_id,
                    'receipt' => $receipt['receipt_number'],
                    'notification_ref' => $smsResult['notification_ref'] ?? null
                ]);

            } catch (Exception $e) {
                Log::error('❌ Failed to send SMS notification', [
                    'phone' => $member->phone_number,
                    'loan_id' => $loan->loan_id,
                    'receipt' => $receipt['receipt_number'],
                    'error' => $e->getMessage()
                ]);
                // Don't throw - notification failure shouldn't stop the payment process
            }
        }
        
        // Email notification
        if ($member->email) {
            try {
                // Create email content
                $emailData = [
                    'member_name' => $member->first_name . ' ' . $member->last_name,
                    'loan_id' => $loan->loan_id,
                    'payment_amount' => number_format($payment->amount, 2),
                    'receipt_number' => $receipt['receipt_number'],
                    'payment_date' => $receipt['payment_date'],
                    'payment_method' => $receipt['payment_method'],
                    'outstanding_balance' => number_format($receipt['outstanding_balance'], 2),
                    'payment_breakdown' => $receipt['payment_breakdown']
                ];
                
                // Send email using Laravel Mail
                \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($member, $emailData, $receipt) {
                    $htmlContent = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #333;'>Payment Receipt</h2>
                            <p>Dear {$emailData['member_name']},</p>
                            <p>We have received your payment for loan <strong>{$emailData['loan_id']}</strong>.</p>
                            
                            <div style='background: #f5f5f5; padding: 15px; margin: 20px 0;'>
                                <h3 style='margin-top: 0;'>Payment Details</h3>
                                <table style='width: 100%;'>
                                    <tr><td><strong>Receipt Number:</strong></td><td>{$emailData['receipt_number']}</td></tr>
                                    <tr><td><strong>Payment Date:</strong></td><td>{$emailData['payment_date']}</td></tr>
                                    <tr><td><strong>Amount Paid:</strong></td><td>TZS {$emailData['payment_amount']}</td></tr>
                                    <tr><td><strong>Payment Method:</strong></td><td>{$emailData['payment_method']}</td></tr>
                                </table>
                            </div>
                            
                            <div style='background: #e8f4f8; padding: 15px; margin: 20px 0;'>
                                <h3 style='margin-top: 0;'>Payment Allocation</h3>
                                <table style='width: 100%;'>
                                    <tr><td><strong>Principal:</strong></td><td>TZS " . number_format($emailData['payment_breakdown']['principal'], 2) . "</td></tr>
                                    <tr><td><strong>Interest:</strong></td><td>TZS " . number_format($emailData['payment_breakdown']['interest'], 2) . "</td></tr>
                                    <tr><td><strong>Penalties:</strong></td><td>TZS " . number_format($emailData['payment_breakdown']['penalties'], 2) . "</td></tr>
                                </table>
                            </div>
                            
                            <p><strong>Outstanding Balance:</strong> TZS {$emailData['outstanding_balance']}</p>
                            
                            <p>Thank you for your payment.</p>
                            
                            <hr style='margin-top: 30px;'>
                            <p style='font-size: 12px; color: #666;'>
                                This is an automated email from MFI Core System.<br>
                                If you have any questions, please contact our support team.
                            </p>
                        </div>
                    ";
                    
                    $message->to($member->email)
                            ->subject('Payment Receipt - ' . $receipt['receipt_number'])
                            ->html($htmlContent);
                });
                
                Log::info('✅ Email notification sent successfully', [
                    'email' => $member->email,
                    'receipt' => $receipt['receipt_number'],
                    'loan_id' => $loan->loan_id
                ]);
                
            } catch (Exception $e) {
                Log::error('❌ Failed to send email notification', [
                    'email' => $member->email,
                    'receipt' => $receipt['receipt_number'],
                    'error' => $e->getMessage()
                ]);
                
                // Don't throw - notification failure shouldn't stop the payment process
            }
        }
    }
    
    /**
     * Get loan payment history
     */
    public function getPaymentHistory($loanId, $limit = 10)
    {
        return DB::table('loan_payments')
            ->where('loan_id', $loanId)
            ->orderBy('payment_date', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Calculate early settlement amount
     */
    public function calculateEarlySettlement($loanId)
    {
        Log::info('🔍 Calculating early settlement', [
            'loan_id' => $loanId,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        $loan = $this->validateAndFetchLoan($loanId);
        $outstandingBalance = $this->calculateOutstandingBalances($loan);
        
        // Calculate early settlement discount or penalty
        $product = DB::table('loan_sub_products')
            ->where('sub_product_id', $loan->loan_sub_product)
            ->first();
        
        $settlementAmount = $outstandingBalance['principal'];
        $waiver = 0;
        
        if ($product && isset($product->early_settlement_waiver) && $product->early_settlement_waiver > 0) {
            // Apply interest waiver for early settlement
            $waiver = $outstandingBalance['interest'] * ($product->early_settlement_waiver / 100);
            $settlementAmount += ($outstandingBalance['interest'] - $waiver);
            
            Log::info('💸 Early settlement waiver applied', [
                'loan_id' => $loanId,
                'waiver_percentage' => $product->early_settlement_waiver,
                'waiver_amount' => $waiver,
                'interest_before' => $outstandingBalance['interest'],
                'interest_after' => $outstandingBalance['interest'] - $waiver
            ]);
        } else {
            $settlementAmount += $outstandingBalance['interest'];
            
            Log::info('ℹ️ No early settlement waiver available', [
                'loan_id' => $loanId,
                'product_id' => $loan->loan_sub_product
            ]);
        }
        
        $result = [
            'principal' => $outstandingBalance['principal'],
            'interest' => $outstandingBalance['interest'],
            'penalties' => $outstandingBalance['penalties'],
            'waiver' => $waiver,
            'total_settlement' => $settlementAmount + $outstandingBalance['penalties'],
            'savings' => $waiver
        ];
        
        Log::info('📋 Early settlement calculated', [
            'loan_id' => $loanId,
            'result' => $result
        ]);
        
        return $result;
    }
    
    /**
     * Process bulk repayments (e.g., salary deductions)
     */
    public function processBulkRepayments($repayments)
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($repayments as $repayment) {
            try {
                $result = $this->processRepayment(
                    $repayment['loan_id'],
                    $repayment['amount'],
                    'SALARY',
                    ['batch_id' => $repayment['batch_id'] ?? null]
                );
                
                $results[] = [
                    'loan_id' => $repayment['loan_id'],
                    'success' => true,
                    'receipt' => $result['receipt_number']
                ];
                $successCount++;
                
            } catch (Exception $e) {
                $results[] = [
                    'loan_id' => $repayment['loan_id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                $failureCount++;
            }
        }
        
        return [
            'total' => count($repayments),
            'successful' => $successCount,
            'failed' => $failureCount,
            'results' => $results
        ];
    }
}