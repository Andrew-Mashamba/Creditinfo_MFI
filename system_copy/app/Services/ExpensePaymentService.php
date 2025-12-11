<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\BudgetAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpensePaymentService
{
    private $transactionPostingService;

    public function __construct()
    {
        $this->transactionPostingService = new TransactionPostingService();
    }
    /**
     * Process payment for an approved expense
     *
     * @param int $expenseId
     * @return array
     */
    public function processPayment(int $expenseId): array
    {
        try {
            DB::beginTransaction();
            
            // Find the expense with its approval
            $expense = Expense::findOrFail($expenseId);
            
            // Load approval manually using process_id
            $expense->approval = \App\Models\Approval::where('process_code', 'EXPENSE_REG')
                ->where('process_id', $expenseId)
                ->first();
            
            // Check if expense is already paid
            if ($expense->status === 'PAID') {
                throw new \Exception('This expense has already been paid.');
            }
            
            // Validate expense can be paid - check approval status instead of expense status
            if (!$expense->approval) {
                throw new \Exception('This expense has no approval record.');
            }
            
            if ($expense->approval->approval_status !== 'APPROVED') {
                throw new \Exception('Only expenses with approved approval status can be paid. Current approval status: ' . $expense->approval->approval_status);
            }
            
            // Log the payment attempt
            Log::channel('budget_management')->info('════════════════════════════════════════════════════════', []);
            Log::channel('budget_management')->info('💳 STARTING EXPENSE PAYMENT PROCESS', [
                'expense_id' => $expenseId,
                'amount' => number_format($expense->amount, 2),
                'account_id' => $expense->account_id,
                'user_id' => Auth::id() ?? 1,
                'processor' => Auth::user() ? Auth::user()->name : 'System'
            ]);
            
            // Get the expense account details
            $expenseAccount = Account::find($expense->account_id);
            if (!$expenseAccount) {
                throw new \Exception('Expense account not found.');
            }
            
            // Get the cash/bank account to pay from (typically a cash or bank account)
            $paymentAccount = $this->getPaymentAccount($expense->payment_type);
            
            // Create the payment transaction
            $transaction = $this->createPaymentTransaction($expense, $expenseAccount, $paymentAccount);
            
            // Update expense status to PAID
            // Remove the dynamically loaded approval property to prevent it from being saved
            unset($expense->approval);
            
            $expense->update([
                'status' => 'PAID',
                'payment_date' => now(),
                'payment_transaction_id' => $transaction->id,
                'paid_by_user_id' => Auth::id() ?? 1,
                'payment_reference' => $this->generatePaymentReference($expense)
            ]);
            
            // Update budget allocation if expense has budget tracking
            if ($expense->budget_item_id || $expense->budget_allocation_id) {
                $this->updateBudgetAfterPayment($expense);
            }
            
            // Log success
            Log::channel('budget_management')->info('✅ EXPENSE PAYMENT SUCCESSFUL', [
                'expense_id' => $expenseId,
                'transaction_id' => $transaction->id,
                'payment_reference' => $expense->payment_reference,
                'amount' => number_format($expense->amount, 2),
                'payment_account' => $paymentAccount->account_name,
                'expense_account' => $expenseAccount->account_name
            ]);
            
            Log::channel('budget_management')->info('════════════════════════════════════════════════════════', []);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Expense payment processed successfully',
                'transaction_id' => $transaction->id,
                'payment_reference' => $expense->payment_reference,
                'amount' => $expense->amount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('budget_management')->error('❌ EXPENSE PAYMENT FAILED', [
                'expense_id' => $expenseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create the payment transaction
     */
    private function createPaymentTransaction($expense, $expenseAccount, $paymentAccount)
    {
        // Create double-entry bookkeeping transaction
        // Debit: Expense Account (increase expense)
        // Credit: Cash/Bank Account (decrease cash)
        
        $transaction = Transaction::create([
            'transaction_uuid' => \Str::uuid(),
            'type' => 'EXPENSE_PAYMENT',
            'amount' => $expense->amount,
            'account_id' => $paymentAccount->id, // Payment account used
            'description' => 'Payment for expense: ' . $expense->description,
            'reference' => 'EXP-PAY-' . $expense->id,
            'narration' => 'Expense payment from ' . $paymentAccount->account_name . ' to ' . $expenseAccount->account_name,
            'status' => 'COMPLETED',
            'processed_at' => now(),
            'completed_at' => now(),
            'initiated_by' => Auth::id() ?? 1,
            'processed_by' => Auth::id() ?? 1,
            'metadata' => json_encode([
                'expense_id' => $expense->id,
                'debit_account_id' => $expenseAccount->id,
                'credit_account_id' => $paymentAccount->id,
                'payment_type' => 'expense_payment'
            ])
        ]);

        // Post to general ledger for double-entry bookkeeping
        // This also handles account balance updates and parent account propagation
        $this->postToGeneralLedger($expense, $expenseAccount, $paymentAccount, $transaction);

        return $transaction;
    }
    
    /**
     * Get the payment account based on payment type
     */
    private function getPaymentAccount($paymentType)
    {
        // First, try to get from institution settings
        $institution = DB::table('institutions')->where('id', 1)->first();

        if ($institution) {
            // Map payment types to institution account fields
            $institutionAccountMapping = [
                'money_transfer' => 'operations_account',
                'bank_transfer' => 'operations_account',
                'bill_payment' => 'operations_account',
                'luku_payment' => 'operations_account',
                'gepg_payment' => 'operations_account',
                'cash' => 'main_petty_cash_account',
                'petty_cash' => 'main_petty_cash_account'
            ];

            $accountField = $institutionAccountMapping[$paymentType] ?? 'operations_account';
            $accountNumber = $institution->$accountField ?? null;

            if ($accountNumber) {
                $account = Account::where('account_number', $accountNumber)
                    ->where('status', 'ACTIVE')
                    ->first();

                if ($account) {
                    return $account;
                }
            }
        }

        // Fallback: Map payment types to account names
        $paymentAccountMapping = [
            'money_transfer' => '%Bank%',
            'bank_transfer' => '%Bank%',
            'bill_payment' => '%Bank%',
            'luku_payment' => '%Bank%',
            'gepg_payment' => '%Bank%',
            'cash' => '%Cash%'
        ];

        $accountPattern = $paymentAccountMapping[$paymentType] ?? '%Cash%';

        // Try to find the account by name pattern
        $account = Account::where('major_category_code', '1000')
            ->where('account_name', 'LIKE', $accountPattern)
            ->where('status', 'ACTIVE')
            ->first();

        // If not found, get any cash or bank account
        if (!$account) {
            $account = Account::whereIn('major_category_code', ['1000', '1100'])
                ->where(function($query) {
                    $query->where('account_name', 'LIKE', '%Cash%')
                        ->orWhere('account_name', 'LIKE', '%Bank%');
                })
                ->where('status', 'ACTIVE')
                ->first();
        }

        if (!$account) {
            throw new \Exception('No payment account found. Please configure cash/bank accounts.');
        }

        return $account;
    }

    /**
     * Generate payment reference
     */
    private function generatePaymentReference($expense)
    {
        return 'PAY-' . date('Ymd') . '-' . str_pad($expense->id, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate transaction number
     */
    private function generateTransactionNumber()
    {
        $lastTransaction = Transaction::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction ? ($lastTransaction->id + 1) : 1;
        
        return 'TRX-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Update budget allocation after payment
     */
    private function updateBudgetAfterPayment($expense)
    {
        if ($expense->budget_allocation_id) {
            $allocation = BudgetAllocation::find($expense->budget_allocation_id);
            if ($allocation) {
                // Update utilized amount and available amount
                $newUtilized = ($allocation->utilized_amount ?? 0) + $expense->amount;
                $newAvailable = $allocation->allocated_amount - $newUtilized;
                
                $allocation->update([
                    'utilized_amount' => $newUtilized,
                    'available_amount' => $newAvailable
                ]);
                
                Log::channel('budget_management')->info('💰 BUDGET UPDATED AFTER PAYMENT', [
                    'allocation_id' => $allocation->id,
                    'expense_amount' => $expense->amount,
                    'new_utilized' => $newUtilized,
                    'new_available' => $newAvailable,
                    'allocated_amount' => $allocation->allocated_amount
                ]);
            }
        }
    }
    
    /**
     * Process payment with additional payment details
     */
    public function processPaymentWithDetails(int $expenseId, array $paymentData): array
    {
        try {
            DB::beginTransaction();
            
            // Find the expense with its approval
            $expense = Expense::findOrFail($expenseId);
            
            // Load approval manually using process_id
            $expense->approval = \App\Models\Approval::where('process_code', 'EXPENSE_REG')
                ->where('process_id', $expenseId)
                ->first();
            
            // Check if expense is already paid
            if ($expense->status === 'PAID') {
                throw new \Exception('This expense has already been paid.');
            }
            
            // Validate expense can be paid
            if (!$expense->approval) {
                throw new \Exception('This expense has no approval record.');
            }
            
            if ($expense->approval->approval_status !== 'APPROVED') {
                throw new \Exception('Only expenses with approved approval status can be paid.');
            }
            
            // Log the payment attempt
            Log::channel('budget_management')->info('════════════════════════════════════════════════════════', []);
            Log::channel('budget_management')->info('💳 STARTING EXPENSE PAYMENT PROCESS (WITH DETAILS)', [
                'expense_id' => $expenseId,
                'amount' => number_format($expense->amount, 2),
                'payment_method' => $paymentData['payment_method'],
                'processor' => Auth::user() ? Auth::user()->name : 'System'
            ]);
            
            // Get the expense account details
            $expenseAccount = Account::find($expense->account_id);
            if (!$expenseAccount) {
                throw new \Exception('Expense account not found.');
            }
            
            // Get the payment account based on payment data
            $paymentAccount = $this->getPaymentAccountFromData($paymentData);
            
            // Create the payment transaction
            $transaction = $this->createPaymentTransaction($expense, $expenseAccount, $paymentAccount);
            
            // Update expense with payment details  
            // Remove the dynamically loaded approval property to prevent it from being saved
            unset($expense->approval);
            
            $expense->update([
                'status' => 'PAID',
                'payment_date' => now(),
                'payment_transaction_id' => $transaction->id,
                'payment_method' => $paymentData['payment_method'],
                'paid_by_user_id' => Auth::id() ?? 1,
                'payment_reference' => $this->generatePaymentReference($expense),
                'payment_notes' => $paymentData['payment_notes'] ?? null
            ]);
            
            // Store payment method specific details if needed
            $this->storePaymentMethodDetails($expense, $paymentData);
            
            // Update budget allocation if expense has budget tracking
            if ($expense->budget_item_id || $expense->budget_allocation_id) {
                $this->updateBudgetAfterPayment($expense);
            }
            
            // Log success
            Log::channel('budget_management')->info('✅ EXPENSE PAYMENT SUCCESSFUL', [
                'expense_id' => $expenseId,
                'transaction_id' => $transaction->id,
                'payment_reference' => $expense->payment_reference,
                'payment_method' => $paymentData['payment_method']
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Expense payment processed successfully',
                'transaction_id' => $transaction->id,
                'payment_reference' => $expense->payment_reference,
                'amount' => $expense->amount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('budget_management')->error('❌ EXPENSE PAYMENT FAILED', [
                'expense_id' => $expenseId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get payment account from payment data
     */
    private function getPaymentAccountFromData($paymentData)
    {
        if (!empty($paymentData['bank_account_id'])) {
            $account = Account::find($paymentData['bank_account_id']);
            if ($account) {
                return $account;
            }
        }
        
        // Fall back to default payment account
        return $this->getPaymentAccount($paymentData['payment_method'] ?? 'cash');
    }
    
    /**
     * Store payment method specific details
     */
    private function storePaymentMethodDetails($expense, $paymentData)
    {
        // Store additional payment details in a separate table or JSON field if needed
        // For now, we can log them
        if ($paymentData['payment_method'] === 'bank_transfer') {
            Log::channel('budget_management')->info('Bank Transfer Details', [
                'expense_id' => $expense->id,
                'account_holder' => $paymentData['account_holder_name'] ?? null
            ]);
        } elseif ($paymentData['payment_method'] === 'mobile_money') {
            Log::channel('budget_management')->info('Mobile Money Details', [
                'expense_id' => $expense->id,
                'phone' => $paymentData['phone_number'] ?? null,
                'provider' => $paymentData['mno_provider'] ?? null
            ]);
        }
    }
    
    /**
     * Process batch payment for multiple expenses
     */
    public function processBatchPayment(array $expenseIds): array
    {
        $results = [
            'successful' => [],
            'failed' => [],
            'total_amount' => 0
        ];
        
        foreach ($expenseIds as $expenseId) {
            $result = $this->processPayment($expenseId);
            
            if ($result['success']) {
                $results['successful'][] = $expenseId;
                $results['total_amount'] += $result['amount'];
            } else {
                $results['failed'][$expenseId] = $result['message'];
            }
        }
        
        return $results;
    }
    
    /**
     * Process enhanced expense payment with funding sources and external transfers
     */
    public function processEnhancedPayment(int $expenseId, array $paymentData): array
    {
        try {
            DB::beginTransaction();
            
            // Find the expense with its approval
            $expense = Expense::findOrFail($expenseId);
            
            // Load approval manually using process_id
            $expense->approval = \App\Models\Approval::where('process_code', 'EXPENSE_REG')
                ->where('process_id', $expenseId)
                ->first();
                
            // Validate expense can be paid
            if ($expense->status === 'PAID') {
                throw new \Exception('This expense has already been paid.');
            }
            
            if (!$expense->approval || $expense->approval->approval_status !== 'APPROVED') {
                throw new \Exception('Only expenses with approved approval status can be paid.');
            }
            
            // Log the enhanced payment attempt
            Log::channel('budget_management')->info('🚀 STARTING ENHANCED EXPENSE PAYMENT', [
                'expense_id' => $expenseId,
                'amount' => number_format($expense->amount, 2),
                'funding_source' => $paymentData['funding_source'],
                'payment_method' => $paymentData['payment_method'],
                'requires_external_transfer' => $paymentData['requires_external_transfer'] ?? false,
                'processor' => Auth::user() ? Auth::user()->name : 'System'
            ]);
            
            // Get accounts
            $expenseAccount = Account::find($expense->account_id);
            if (!$expenseAccount) {
                throw new \Exception('Expense account not found.');
            }
            
            // Get source account (funding source)
            $sourceAccount = $this->getSourceAccount($paymentData);
            
            // Step 1: Create internal transaction (source -> expense account)
            $internalTransaction = $this->createInternalTransaction($expense, $sourceAccount, $expenseAccount, $paymentData);
            
            // Step 2: Handle external transfer if needed
            $transferResult = null;
            if ($paymentData['requires_external_transfer'] ?? false) {
                $transferResult = $this->processExternalTransfer($expense, $paymentData);
            }
            
            // Step 3: Update expense status
            unset($expense->approval);
            $expense->update([
                'status' => 'PAID',
                'payment_date' => now(),
                'payment_transaction_id' => $internalTransaction->id,
                'payment_method' => $paymentData['payment_method'],
                'paid_by_user_id' => Auth::id() ?? 1,
                'payment_reference' => $this->generatePaymentReference($expense),
                'payment_notes' => $paymentData['payment_notes'] ?? null
            ]);
            
            // Step 4: Update budget if linked
            if ($expense->budget_item_id || $expense->budget_allocation_id) {
                $this->updateBudgetAfterPayment($expense);
            }
            
            // Log success
            Log::channel('budget_management')->info('✅ ENHANCED EXPENSE PAYMENT SUCCESSFUL', [
                'expense_id' => $expenseId,
                'internal_transaction_id' => $internalTransaction->id,
                'payment_reference' => $expense->payment_reference,
                'transfer_result' => $transferResult ? 'processed' : 'not_required'
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Enhanced expense payment processed successfully',
                'payment_reference' => $expense->payment_reference,
                'internal_transaction_id' => $internalTransaction->id,
                'transfer_status' => $transferResult['status'] ?? 'internal_only',
                'amount' => $expense->amount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('budget_management')->error('❌ ENHANCED EXPENSE PAYMENT FAILED', [
                'expense_id' => $expenseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Enhanced payment failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get source account based on funding source
     */
    private function getSourceAccount($paymentData)
    {
        if ($paymentData['funding_source'] === 'petty_cash') {
            // Get petty cash account - for now, use a default cash account
            $account = Account::where('major_category_code', 1000)
                ->where('account_name', 'LIKE', '%Cash%')
                ->where('status', 'ACTIVE')
                ->first();
                
            if (!$account) {
                throw new \Exception('Petty cash account not found.');
            }
            
            return $account;
        } elseif ($paymentData['funding_source'] === 'bank_account') {
            // Get from bank_accounts table
            $bankAccountId = $paymentData['source_account_id'];
            $bankAccountData = DB::table('bank_accounts')->where('id', $bankAccountId)->first();
            
            if (!$bankAccountData) {
                throw new \Exception('Selected bank account not found.');
            }
            
            // Map to accounts table or use bank account directly
            // For now, return a mock account object with bank account data
            $account = new \stdClass();
            $account->id = $bankAccountData->id;
            $account->account_name = $bankAccountData->account_name;
            $account->account_number = $bankAccountData->account_number;
            $account->balance = $bankAccountData->current_balance;
            
            return $account;
        }
        
        throw new \Exception('Invalid funding source specified.');
    }
    
    /**
     * Create internal transaction (accounting entry)
     */
    private function createInternalTransaction($expense, $sourceAccount, $expenseAccount, $paymentData)
    {
        $transaction = Transaction::create([
            'transaction_uuid' => \Str::uuid(),
            'type' => 'EXPENSE_PAYMENT_ENHANCED',
            'amount' => $expense->amount,
            'account_id' => $sourceAccount->id,
            'description' => 'Enhanced expense payment: ' . $expense->description,
            'reference' => 'EXP-ENH-' . $expense->id,
            'narration' => sprintf(
                'Expense payment from %s (%s) via %s',
                $sourceAccount->account_name,
                $paymentData['funding_source'],
                $paymentData['payment_method']
            ),
            'status' => 'COMPLETED',
            'processed_at' => now(),
            'completed_at' => now(),
            'initiated_by' => Auth::id() ?? 1,
            'processed_by' => Auth::id() ?? 1,
            'metadata' => json_encode([
                'expense_id' => $expense->id,
                'funding_source' => $paymentData['funding_source'],
                'payment_method' => $paymentData['payment_method'],
                'source_account_id' => $sourceAccount->id,
                'expense_account_id' => $expenseAccount->id,
                'requires_external_transfer' => $paymentData['requires_external_transfer'] ?? false
            ])
        ]);
        
        return $transaction;
    }
    
    /**
     * Process external fund transfer
     */
    private function processExternalTransfer($expense, $paymentData)
    {
        try {
            if ($paymentData['payment_method'] === 'mobile_money') {
                // Use MobileWalletTransferService
                $transferService = new \App\Services\Payments\MobileWalletTransferService();
                
                // Check amount limit for mobile money
                if ($expense->amount > 20000000) { // 20M TZS limit
                    throw new \Exception('Amount exceeds mobile money transfer limit (20M TZS)');
                }
                
                $transferResult = $transferService->transfer([
                    'amount' => $expense->amount,
                    'phone' => $paymentData['phone_number'],
                    'provider' => $paymentData['mno_provider'],
                    'account_holder' => $paymentData['account_holder_name'],
                    'narration' => 'Expense payment: ' . $expense->description
                ]);
                
            } elseif ($paymentData['payment_method'] === 'bank_transfer') {
                // Determine if internal or external transfer
                $isNBCAccount = $this->isNBCAccount($paymentData['recipient_bank_code']);
                
                if ($isNBCAccount) {
                    // Use InternalFundsTransferService
                    $transferService = new \App\Services\Payments\InternalFundsTransferService();
                    $transferResult = $transferService->transfer([
                        'amount' => $expense->amount,
                        'recipient_account' => $paymentData['recipient_account_number'],
                        'account_holder' => $paymentData['account_holder_name'],
                        'narration' => 'Expense payment: ' . $expense->description
                    ]);
                } else {
                    // Use ExternalFundsTransferService
                    $transferService = new \App\Services\Payments\ExternalFundsTransferService();
                    
                    $transferResult = $transferService->transfer([
                        'amount' => $expense->amount,
                        'recipient_account' => $paymentData['recipient_account_number'],
                        'recipient_bank' => $paymentData['recipient_bank_code'],
                        'account_holder' => $paymentData['account_holder_name'],
                        'narration' => 'Expense payment: ' . $expense->description
                    ]);
                }
            }
            
            return $transferResult ?? ['status' => 'completed', 'message' => 'Transfer processed'];
            
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('External transfer failed', [
                'expense_id' => $expense->id,
                'payment_method' => $paymentData['payment_method'],
                'error' => $e->getMessage()
            ]);
            
            // Don't fail the entire payment for transfer issues
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if bank code belongs to NBC
     */
    private function isNBCAccount($bankCode)
    {
        // Get NBC bank configuration
        $nbcBank = config('fsp_providers.banks.NBC', []);
        
        // Check if the bank code matches NBC's code or if it's marked as self
        if (!empty($nbcBank)) {
            $nbcCodes = [
                $nbcBank['code'] ?? '',
                $nbcBank['fsp_id'] ?? '',
                'NBC',
                'NLCBTZTX',
                '015'
            ];
            return in_array(strtoupper($bankCode), array_map('strtoupper', $nbcCodes));
        }
        
        // Fallback to known NBC codes
        $nbcCodes = ['NBC', 'NLCBTZTX', '015', 'NBCBANK'];
        return in_array(strtoupper($bankCode), $nbcCodes);
    }

    /**
     * Post expense payment to general ledger via TransactionPostingService
     * This ensures all GL postings follow the centralized posting logic
     */
    private function postToGeneralLedger($expense, $expenseAccount, $paymentAccount, $transaction)
    {
        $narration = 'Expense payment: ' . $expense->description;

        Log::channel('budget_management')->info('📒 POSTING TO GENERAL LEDGER VIA TRANSACTION POSTING SERVICE', [
            'expense_id' => $expense->id,
            'expense_account' => $expenseAccount->account_number,
            'payment_account' => $paymentAccount->account_number,
            'amount' => number_format($expense->amount, 2)
        ]);

        try {
            // Use TransactionPostingService for all GL postings
            // It handles: debit/credit determination, balance updates, parent account updates,
            // validation (active status, account level, negative balance prevention), and GL entry creation
            $result = $this->transactionPostingService->postTransaction([
                'first_account' => $expenseAccount->account_number,    // Expense account (will be debited)
                'second_account' => $paymentAccount->account_number,   // Payment account (will be credited)
                'amount' => $expense->amount,
                'narration' => $narration,
                'action' => 'expense_payment'
            ]);

            Log::channel('budget_management')->info('✅ GENERAL LEDGER ENTRIES POSTED', [
                'expense_id' => $expense->id,
                'reference_number' => $result['reference_number'],
                'expense_account' => $expenseAccount->account_number . ' (' . $expenseAccount->account_name . ')',
                'payment_account' => $paymentAccount->account_number . ' (' . $paymentAccount->account_name . ')',
                'amount' => number_format($expense->amount, 2),
                'status' => $result['status']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::channel('budget_management')->error('❌ GENERAL LEDGER POSTING FAILED', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
                'expense_account' => $expenseAccount->account_number,
                'payment_account' => $paymentAccount->account_number
            ]);
            throw $e;
        }
    }
}