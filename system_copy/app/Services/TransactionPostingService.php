<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Jobs\SendTransactionNotification;
use App\Jobs\SendControlTransactionNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class TransactionPostingService
{
    private $validator;
    private $balanceManager;
    private $transactionTypes;
    private $logger;
    public $credit_account_level, $debit_account_level;
    public $action;

    public function __construct()
    {
        $this->validator = new TransactionValidator();
        $this->balanceManager = new BalanceManager();
        $this->transactionTypes = new TransactionTypes();
        $this->logger = new TransactionLogger();
    }

    /**
     * Post a transaction with automatic debit/credit determination.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function postTransaction(array $data)
    {
        $this->logger->logTransactionStart($data);

        try {
            // Validate transaction data
            $validations = $this->validator->validateTransaction($data);
            $this->logger->logValidationResults($validations);

            $referenceNumber = time();
            DB::beginTransaction();

            $firstAccountDetails = $data['first_account'];
            $secondAccountDetails = $data['second_account'];
            $amount = $data['amount'];
            $narration = $data['narration'];
            $this->action = $data['action'] ?? 'none';

            // Extract optional source/destination hints for same-type transactions
            $sourceAccountNumber = $data['source_account'] ?? null;
            $destinationAccountNumber = $data['destination_account'] ?? null;

            // Fetch account objects
            $firstAccount = AccountsModel::where('account_number', $firstAccountDetails)->first();
            $secondAccount = AccountsModel::where('account_number', $secondAccountDetails)->first();

            if (!$firstAccount || !$secondAccount) {
                throw new Exception('One or both accounts not found');
            }

            // Determine which account to debit and credit
            $whichToDebit = $this->determineDebitAccount($firstAccount, $secondAccount, $sourceAccountNumber, $destinationAccountNumber);
            $debitAccountDetails = $whichToDebit === 'first' ? $firstAccount : $secondAccount;
            $creditAccountDetails = $debitAccountDetails === $firstAccount ? $secondAccount : $firstAccount;

            Log::info('===== TRANSACTION POSTING: DEBIT/CREDIT DETERMINATION =====', [
                'decision' => $whichToDebit,
                'DEBIT_ACCOUNT' => [
                    'account_number' => $debitAccountDetails->account_number,
                    'account_name' => $debitAccountDetails->account_name,
                    'major_category' => substr($debitAccountDetails->major_category_code ?? '0000', 0, 1),
                    'account_type' => $debitAccountDetails->type,
                    'current_balance' => $debitAccountDetails->balance,
                ],
                'CREDIT_ACCOUNT' => [
                    'account_number' => $creditAccountDetails->account_number,
                    'account_name' => $creditAccountDetails->account_name,
                    'major_category' => substr($creditAccountDetails->major_category_code ?? '0000', 0, 1),
                    'account_type' => $creditAccountDetails->type,
                    'current_balance' => $creditAccountDetails->balance,
                ],
                'amount' => $amount,
                'narration' => $narration
            ]);

            // Validate account balances and business rules before processing
            $this->validateAccountBalances($debitAccountDetails, $creditAccountDetails, $amount);
            $this->validateBusinessRules($debitAccountDetails, $creditAccountDetails, $amount);

            // Process the transaction
            $this->processTransaction($referenceNumber, $debitAccountDetails, $creditAccountDetails, $amount, $narration);

            // Get post-transaction balances for notifications
            $debitNewBalance = AccountsModel::where('account_number', $debitAccountDetails->account_number)->value('balance');
            $creditNewBalance = AccountsModel::where('account_number', $creditAccountDetails->account_number)->value('balance');

            DB::commit();
            $this->logger->logTransactionCompletion($referenceNumber, 'success');

            // Send notifications for member accounts (non-blocking via queue)
            $this->dispatchTransactionNotifications(
                $debitAccountDetails, 
                $creditAccountDetails, 
                $amount, 
                $debitNewBalance, 
                $creditNewBalance, 
                $referenceNumber, 
                $narration,
                'success'
            );

            return ['status' => 'success', 'reference_number' => $referenceNumber];
        } catch (Exception $e) {
            DB::rollBack();
            $this->logger->logError($e, [
                'transaction_data' => $data,
                'reference_number' => $referenceNumber ?? null
            ]);

            // Send failure notifications for member accounts
            if (isset($debitAccountDetails) && isset($creditAccountDetails)) {
                $this->dispatchTransactionNotifications(
                    $debitAccountDetails, 
                    $creditAccountDetails, 
                    $amount, 
                    $debitAccountDetails->balance ?? 0, 
                    $creditAccountDetails->balance ?? 0, 
                    $referenceNumber ?? 'N/A', 
                    $narration,
                    'failed',
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    /**
     * Determine which account to debit based on Chart of Accounts standards
     * Uses major_category_code to understand account nature:
     * - 1000 = Assets (Debit increases, Credit decreases)
     * - 2000 = Liabilities (Credit increases, Debit decreases)
     * - 3000 = Equity (Credit increases, Debit decreases)
     * - 4000 = Income (Credit increases, Debit decreases)
     * - 5000 = Expenses (Debit increases, Credit decreases)
     *
     * @param object $firstAccount First account object
     * @param object $secondAccount Second account object
     * @param string|null $sourceAccountNumber Optional source account number for same-type transactions
     * @param string|null $destinationAccountNumber Optional destination account number for same-type transactions
     * @return string 'first' or 'second' indicating which account to debit
     */
    private function determineDebitAccount($firstAccount, $secondAccount, $sourceAccountNumber = null, $destinationAccountNumber = null)
    {
        // Extract first digit of major category code to identify account class
        $firstMajorCategory = substr($firstAccount->major_category_code ?? '0000', 0, 1);
        $secondMajorCategory = substr($secondAccount->major_category_code ?? '0000', 0, 1);

        Log::info('Determining debit account using Chart of Accounts standards', [
            'first_account' => $firstAccount->account_number,
            'first_account_name' => $firstAccount->account_name,
            'first_major_category' => $firstMajorCategory,
            'first_type' => $firstAccount->type,
            'second_account' => $secondAccount->account_number,
            'second_account_name' => $secondAccount->account_name,
            'second_major_category' => $secondMajorCategory,
            'second_type' => $secondAccount->type,
            'source_account' => $sourceAccountNumber,
            'destination_account' => $destinationAccountNumber,
        ]);

        // ASSET (1) - LIABILITY (2) Transaction
        // Can be either: Deposits (money IN to liability) or Withdrawals (money OUT from liability)
        // Use source/destination to determine direction if provided
        if (($firstMajorCategory == '1' && $secondMajorCategory == '2') ||
            ($firstMajorCategory == '2' && $secondMajorCategory == '1')) {

            // If source/destination provided, use them to determine direction
            if ($sourceAccountNumber && $destinationAccountNumber) {
                Log::info('Asset-Liability transaction with source/destination specified', [
                    'source_account' => $sourceAccountNumber,
                    'destination_account' => $destinationAccountNumber
                ]);

                // Determine which account is the asset
                $firstIsAsset = ($firstMajorCategory == '1');
                $assetAccount = $firstIsAsset ? $firstAccount : $secondAccount;

                // If money flows FROM asset (deposit to liability): Debit asset, Credit liability
                // If money flows TO asset (withdrawal from liability): Debit liability, Credit asset
                if ($assetAccount->account_number == $sourceAccountNumber) {
                    // Asset is source → Money going TO liability (deposit)
                    // Debit asset (decreases), Credit liability (increases)
                    Log::info('Asset-Liability DEPOSIT: Money flowing TO liability FROM asset');
                    return $firstIsAsset ? 'first' : 'second'; // Debit the asset
                } else {
                    // Asset is destination → Money coming FROM liability (withdrawal)
                    // Debit liability (decreases), Credit asset (increases)
                    Log::info('Asset-Liability WITHDRAWAL: Money flowing FROM liability TO asset');
                    return $firstIsAsset ? 'second' : 'first'; // Debit the liability
                }
            }

            // No source/destination provided, use default logic (withdrawal scenario)
            if ($firstMajorCategory == '1' && $secondMajorCategory == '2') {
                Log::info('Asset-Liability transaction (default): Debiting liability (second), Crediting asset (first)');
                return 'second'; // Debit second (liability)
            }
            if ($firstMajorCategory == '2' && $secondMajorCategory == '1') {
                Log::info('Liability-Asset transaction (default): Debiting liability (first), Crediting asset (second)');
                return 'first'; // Debit first (liability)
            }
        }

        // ASSET (1) - EQUITY (3) Transaction
        // Can be either: Deposits/Payments (money IN) or Withdrawals/Dividends (money OUT)
        // Use source/destination to determine direction if provided
        if (($firstMajorCategory == '1' && $secondMajorCategory == '3') ||
            ($firstMajorCategory == '3' && $secondMajorCategory == '1')) {

            // If source/destination provided, use them to determine direction
            if ($sourceAccountNumber && $destinationAccountNumber) {
                Log::info('Asset-Equity transaction with source/destination specified', [
                    'source_account' => $sourceAccountNumber,
                    'destination_account' => $destinationAccountNumber
                ]);

                // Determine which account is the asset
                $firstIsAsset = ($firstMajorCategory == '1');
                $assetAccount = $firstIsAsset ? $firstAccount : $secondAccount;

                // If money flows FROM asset (withdrawal): Debit equity, Credit asset
                // If money flows TO asset (deposit): Debit asset, Credit equity
                if ($assetAccount->account_number == $sourceAccountNumber) {
                    // Asset is source → Money coming IN (deposit/payment)
                    // Debit asset (increases), Credit equity (increases)
                    Log::info('Asset-Equity DEPOSIT: Money flowing TO equity FROM asset');
                    return $firstIsAsset ? 'first' : 'second'; // Debit the asset
                } else {
                    // Asset is destination → Money going OUT (withdrawal/dividend)
                    // Debit equity (decreases), Credit asset (decreases)
                    Log::info('Asset-Equity WITHDRAWAL: Money flowing FROM equity TO asset');
                    return $firstIsAsset ? 'second' : 'first'; // Debit the equity
                }
            }

            // No source/destination provided, use default logic (withdrawal scenario)
            if ($firstMajorCategory == '1' && $secondMajorCategory == '3') {
                Log::info('Asset-Equity transaction (default): Debiting equity (second), Crediting asset (first)');
                return 'second'; // Debit second (equity)
            }
            if ($firstMajorCategory == '3' && $secondMajorCategory == '1') {
                Log::info('Equity-Asset transaction (default): Debiting equity (first), Crediting asset (second)');
                return 'first'; // Debit first (equity)
            }
        }

        // ASSET (1) - INCOME (4) Transaction
        // Typical: Revenue receipt, fee collection
        // Debit asset (increases cash/receivable), Credit income (records revenue)
        if ($firstMajorCategory == '1' && $secondMajorCategory == '4') {
            Log::info('Asset-Income transaction: Debiting asset (first), Crediting income (second)');
            return 'first'; // Debit first (asset)
        }
        if ($firstMajorCategory == '4' && $secondMajorCategory == '1') {
            Log::info('Income-Asset transaction: Debiting asset (second), Crediting income (first)');
            return 'second'; // Debit second (asset)
        }

        // EXPENSE (5) - ASSET (1) Transaction
        // Typical: Expense payment, bill payment
        // Debit expense (records cost), Credit asset (reduces cash)
        if ($firstMajorCategory == '5' && $secondMajorCategory == '1') {
            Log::info('Expense-Asset transaction: Debiting expense (first), Crediting asset (second)');
            return 'first'; // Debit first (expense)
        }
        if ($firstMajorCategory == '1' && $secondMajorCategory == '5') {
            Log::info('Asset-Expense transaction: Debiting expense (second), Crediting asset (first)');
            return 'second'; // Debit second (expense)
        }

        // EXPENSE (5) - LIABILITY (2) Transaction
        // Typical: Expense accrual
        // Debit expense (records cost), Credit liability (records payable)
        if ($firstMajorCategory == '5' && $secondMajorCategory == '2') {
            Log::info('Expense-Liability transaction: Debiting expense (first), Crediting liability (second)');
            return 'first'; // Debit first (expense)
        }
        if ($firstMajorCategory == '2' && $secondMajorCategory == '5') {
            Log::info('Liability-Expense transaction: Debiting expense (second), Crediting liability (first)');
            return 'second'; // Debit second (expense)
        }

        // LIABILITY (2) - EQUITY (3) Transaction
        // Typical: Capital contributions, retained earnings transfer
        // Debit liability, Credit equity (or vice versa depending on direction)
        if ($firstMajorCategory == '2' && $secondMajorCategory == '3') {
            Log::info('Liability-Equity transaction: Debiting liability (first), Crediting equity (second)');
            return 'first'; // Debit first (liability)
        }
        if ($firstMajorCategory == '3' && $secondMajorCategory == '2') {
            Log::info('Equity-Liability transaction: Debiting equity (first), Crediting liability (second)');
            return 'first'; // Debit first (equity)
        }

        // INCOME (4) - LIABILITY (2) Transaction
        // Typical: Unearned revenue, deferred income
        // Debit income (reverses revenue), Credit liability (records unearned revenue)
        if ($firstMajorCategory == '4' && $secondMajorCategory == '2') {
            Log::info('Income-Liability transaction: Debiting income (first), Crediting liability (second)');
            return 'first'; // Debit first (income)
        }
        if ($firstMajorCategory == '2' && $secondMajorCategory == '4') {
            Log::info('Liability-Income transaction: Debiting income (second), Crediting liability (first)');
            return 'second'; // Debit second (income)
        }

        // INCOME (4) - EQUITY (3) Transaction
        // Typical: Closing entries, retained earnings transfer
        // Debit income (closes revenue), Credit equity (increases retained earnings)
        if ($firstMajorCategory == '4' && $secondMajorCategory == '3') {
            Log::info('Income-Equity transaction: Debiting income (first), Crediting equity (second)');
            return 'first'; // Debit first (income)
        }
        if ($firstMajorCategory == '3' && $secondMajorCategory == '4') {
            Log::info('Equity-Income transaction: Debiting income (second), Crediting equity (first)');
            return 'second'; // Debit second (income)
        }

        // INCOME (4) - EXPENSE (5) Transaction
        // Typical: Revenue-expense offset, adjustments
        // Debit income (reduces revenue), Credit expense (reduces expense)
        // OR Debit expense, Credit income (rare: revenue used to cover expense)
        if ($firstMajorCategory == '4' && $secondMajorCategory == '5') {
            Log::info('Income-Expense transaction: Debiting income (first), Crediting expense (second)');
            return 'first'; // Debit first (income)
        }
        if ($firstMajorCategory == '5' && $secondMajorCategory == '4') {
            Log::info('Expense-Income transaction: Debiting income (second), Crediting expense (first)');
            return 'second'; // Debit second (income)
        }

        // EQUITY (3) - EXPENSE (5) Transaction
        // Typical: Capital used to pay expenses, dividends
        // Debit expense (records cost) OR Debit equity (reduces capital), Credit expense
        if ($firstMajorCategory == '3' && $secondMajorCategory == '5') {
            Log::info('Equity-Expense transaction: Debiting expense (second), Crediting equity (first)');
            return 'second'; // Debit second (expense)
        }
        if ($firstMajorCategory == '5' && $secondMajorCategory == '3') {
            Log::info('Expense-Equity transaction: Debiting expense (first), Crediting equity (second)');
            return 'first'; // Debit first (expense)
        }

        // SAME-TYPE TRANSACTIONS - Use source/destination logic
        if ($firstMajorCategory == $secondMajorCategory) {
            Log::info('Same-type transaction detected', [
                'first_account' => $firstAccount->account_number,
                'second_account' => $secondAccount->account_number,
                'major_category' => $firstMajorCategory,
                'source_account' => $sourceAccountNumber,
                'destination_account' => $destinationAccountNumber
            ]);

            // If source/destination are provided, use them to determine debit/credit
            if ($sourceAccountNumber && $destinationAccountNumber) {
                // LEFT SIDE of Balance Sheet (Assets=1, Expenses=5):
                // - Debit increases balance, Credit decreases balance
                // - Transfer: CREDIT source (decrease), DEBIT destination (increase)
                if (in_array($firstMajorCategory, ['1', '5'])) {
                    Log::info('Left-side balance sheet account (Asset/Expense): CREDIT source, DEBIT destination');

                    // Determine which account is source
                    if ($firstAccount->account_number == $sourceAccountNumber) {
                        return 'second'; // Debit second (destination), Credit first (source)
                    } else {
                        return 'first'; // Debit first (destination), Credit second (source)
                    }
                }

                // RIGHT SIDE of Balance Sheet (Liabilities=2, Equity=3, Income=4):
                // - Credit increases balance, Debit decreases balance
                // - Transfer: DEBIT source (decrease), CREDIT destination (increase)
                if (in_array($firstMajorCategory, ['2', '3', '4'])) {
                    Log::info('Right-side balance sheet account (Liability/Equity/Income): DEBIT source, CREDIT destination');

                    // Determine which account is source
                    if ($firstAccount->account_number == $sourceAccountNumber) {
                        return 'first'; // Debit first (source), Credit second (destination)
                    } else {
                        return 'second'; // Debit second (source), Credit first (destination)
                    }
                }
            }

            // No source/destination provided - cannot determine
            Log::error('Cannot determine debit/credit for same-type transaction without source/destination', [
                'first_account' => $firstAccount->account_number,
                'second_account' => $secondAccount->account_number,
                'major_category' => $firstMajorCategory,
                'suggestion' => 'Pass source_account and destination_account parameters'
            ]);
            throw new \Exception(
                "Cannot automatically determine debit/credit for transaction between accounts of same type. " .
                "First account: {$firstAccount->account_name} ({$firstAccount->type}), " .
                "Second account: {$secondAccount->account_name} ({$secondAccount->type}). " .
                "Please provide source_account and destination_account parameters."
            );
        }

        // FALLBACK: Use original logic if major_category_code is not set
        Log::warning('Falling back to original type-based logic', [
            'first_type' => $firstAccount->type,
            'second_type' => $secondAccount->type
        ]);

        if (in_array($firstAccount->type, ['asset_accounts', 'expense_accounts'])) {
            return 'first';
        }
        return 'second';
    }

    private function processTransaction($referenceNumber, $debitAccountDetails, $creditAccountDetails, $amount, $narration)
    {
        $this->credit_account_level = 2;
        $this->debit_account_level = 2;

        Log::info('===== JOURNAL ENTRY CREATION START =====', [
            'reference_number' => $referenceNumber,
            'narration' => $narration,
            'amount' => $amount
        ]);

        // Update balances using BalanceManager
        $transaction = [
            'debit_account' => $debitAccountDetails,
            'credit_account' => $creditAccountDetails,
            'amount' => $amount,
            'type' => $this->determineTransactionType($debitAccountDetails, $creditAccountDetails)
        ];

        $balanceUpdate = $this->balanceManager->updateBalances($transaction);
        $postBalances = $balanceUpdate['post_transaction_balances'];
        $this->logger->logBalanceChanges(
            $balanceUpdate['pre_transaction_balances'],
            $balanceUpdate['balance_changes'],
            $postBalances
        );

        Log::info('===== JOURNAL ENTRY: DEBIT SIDE =====', [
            'reference_number' => $referenceNumber,
            'account_number' => $debitAccountDetails->account_number,
            'account_name' => $debitAccountDetails->account_name,
            'entry_type' => 'DEBIT',
            'amount' => $amount,
            'balance_before' => $debitAccountDetails->balance,
            'balance_after' => $postBalances['debit_account']['new_balance'],
            'change' => $postBalances['debit_account']['new_balance'] - $debitAccountDetails->balance
        ]);

        // Record transaction entries
        $this->recordTransaction(
            $referenceNumber,
            $debitAccountDetails,
            $creditAccountDetails,
            $postBalances['debit_account']['new_balance'],
            'debit',
            $amount,
            $narration
        );

        Log::info('===== JOURNAL ENTRY: CREDIT SIDE =====', [
            'reference_number' => $referenceNumber,
            'account_number' => $creditAccountDetails->account_number,
            'account_name' => $creditAccountDetails->account_name,
            'entry_type' => 'CREDIT',
            'amount' => $amount,
            'balance_before' => $creditAccountDetails->balance,
            'balance_after' => $postBalances['credit_account']['new_balance'],
            'change' => $postBalances['credit_account']['new_balance'] - $creditAccountDetails->balance
        ]);

        $this->recordTransaction(
            $referenceNumber,
            $creditAccountDetails,
            $debitAccountDetails,
            $postBalances['credit_account']['new_balance'],
            'credit',
            $amount,
            $narration
        );

        Log::info('===== JOURNAL ENTRY SUMMARY =====', [
            'reference_number' => $referenceNumber,
            'journal_entry' => [
                'Dr. ' . $debitAccountDetails->account_name => number_format($amount, 2),
                '    Cr. ' . $creditAccountDetails->account_name => number_format($amount, 2),
            ],
            'narration' => $narration,
            'total_debit' => $amount,
            'total_credit' => $amount,
            'balanced' => true
        ]);

        // Update account balances using only values from BalanceManager
        $this->updateAccountBalance(
            $debitAccountDetails, 
            $postBalances['debit_account']['new_balance'],
            $amount, 
            'debit',
            $postBalances['debit_account']['sub_category_account']['new_balance'] ?? null,
            $postBalances['debit_account']['category_account']['new_balance'] ?? null,
            $postBalances['debit_account']['major_account']['new_balance'] ?? null
        );
        
        $this->updateAccountBalance(
            $creditAccountDetails, 
            $postBalances['credit_account']['new_balance'],
            $amount, 
            'credit',
            $postBalances['credit_account']['sub_category_account']['new_balance'] ?? null,
            $postBalances['credit_account']['category_account']['new_balance'] ?? null,
            $postBalances['credit_account']['major_account']['new_balance'] ?? null
        );
    }

    private function determineTransactionType($debitAccount, $creditAccount)
    {
        $debitType = $debitAccount->type;
        $creditType = $creditAccount->type;

        $typeMap = [
            'asset_accounts' => [
                'asset_accounts' => TransactionTypes::ASSET_PURCHASE,
                'liability_accounts' => TransactionTypes::LIABILITY_SETTLEMENT,
                'capital_accounts' => TransactionTypes::CAPITAL_CONTRIBUTION,
                'income_accounts' => TransactionTypes::REVENUE_RECOGNITION,
                'expense_accounts' => TransactionTypes::EXPENSE_PAYMENT
            ],
            'liability_accounts' => [
                'asset_accounts' => TransactionTypes::LIABILITY_RECOGNITION,
                'liability_accounts' => TransactionTypes::LIABILITY_ADJUSTMENT,
                'capital_accounts' => TransactionTypes::CAPITAL_ADJUSTMENT,
                'expense_accounts' => TransactionTypes::EXPENSE_ACCRUAL
            ],
            'capital_accounts' => [
                'asset_accounts' => TransactionTypes::CAPITAL_WITHDRAWAL,
                'liability_accounts' => TransactionTypes::LIABILITY_ADJUSTMENT,
                'capital_accounts' => TransactionTypes::CAPITAL_ADJUSTMENT,
                'expense_accounts' => TransactionTypes::EXPENSE_PAYMENT
            ],
            'income_accounts' => [
                'asset_accounts' => TransactionTypes::REVENUE_RECOGNITION,
                'liability_accounts' => TransactionTypes::LIABILITY_SETTLEMENT,
                'capital_accounts' => TransactionTypes::CAPITAL_CONTRIBUTION,
                'expense_accounts' => TransactionTypes::EXPENSE_PAYMENT,
                'income_accounts' => TransactionTypes::REVENUE_ADJUSTMENT
            ],
            'expense_accounts' => [
                'asset_accounts' => TransactionTypes::EXPENSE_PAYMENT,
                'liability_accounts' => TransactionTypes::EXPENSE_ACCRUAL,
                'capital_accounts' => TransactionTypes::EXPENSE_PAYMENT,
                'income_accounts' => TransactionTypes::EXPENSE_PAYMENT
            ]
        ];

        return $typeMap[$debitType][$creditType] ?? 'unknown';
    }

    private function updateAccountBalance(
        $accountDetails,      
        $memberAccountNewBalance,  
        $amount,             
        $action,             
        $subCategoryNewBalance = null,    
        $categoryNewBalance = null,       
        $majorNewBalance = null          
    ) {
        // Validate balance is not null
        if ($memberAccountNewBalance === null) {
            Log::error('Attempting to update account with null balance', [
                'account_number' => $accountDetails->account_number,
                'new_balance' => $memberAccountNewBalance,
                'action' => $action,
                'amount' => $amount
            ]);
            throw new \InvalidArgumentException('Cannot update account with null balance');
        }

        $accountData = [
            'account_number' => $accountDetails->account_number,
            'previous_balance' => $accountDetails->balance,
            'new_balance' => $memberAccountNewBalance,
            'amount' => $amount,
            'account_level' => $accountDetails->account_level
        ];

        $this->logger->logAccountUpdate($accountData, $action);

        // Update the current account
        AccountsModel::where('account_number', $accountDetails->account_number)
            ->update([
                'balance' => (float)$memberAccountNewBalance,
                $action => ($accountDetails->{$action} ?? 0) + (float)$amount
            ]);

        // Function to update parent accounts recursively
        $updateParentAccounts = function($account, $amount, $action) use (&$updateParentAccounts) {
            if (!$account->parent_account_number) {
                return;
            }

            $parentAccount = AccountsModel::where('account_number', $account->parent_account_number)->first();
            if (!$parentAccount) {
                return;
            }

            // Calculate new balance for parent
            $newParentBalance = ($parentAccount->balance ?? 0) + (float)$amount;
            
            // Update parent account
            AccountsModel::where('account_number', $parentAccount->account_number)
                ->update([
                    'balance' => (float)$newParentBalance,
                    $action => ($parentAccount->{$action} ?? 0) + (float)$amount
                ]);

            // Log parent account update
            Log::info('Parent account updated', [
                'parent_account_number' => $parentAccount->account_number,
                'parent_account_level' => $parentAccount->account_level,
                'previous_balance' => $parentAccount->balance,
                'new_balance' => $newParentBalance,
                'amount' => $amount,
                'action' => $action
            ]);

            // Recursively update higher level parents
            $updateParentAccounts($parentAccount, $amount, $action);
        };

        // Update all parent accounts
        $updateParentAccounts($accountDetails, $amount, $action);
    }

    private function parentAccountUpdate($account_number, $amount, $action)
    {
        if (!$account_number) {
            return; // Skip if no parent account number provided
        }

        DB::beginTransaction();
        try {
            $parentAccount = AccountsModel::where('account_number', $account_number)->first();
            
            if (!$parentAccount) {
                Log::warning('Parent account not found', ['account_number' => $account_number]);
                DB::rollBack();
                return;
            }

            AccountsModel::where('account_number', $account_number)
                ->update([
                    'balance' => $parentAccount->balance + ($action === 'credit' ? $amount : -$amount),
                    'credit' => $action === 'credit' ? $parentAccount->credit + $amount : $parentAccount->credit,
                    'debit' => $action === 'debit' ? $parentAccount->debit + $amount : $parentAccount->debit,
                ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Parent account update failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function recordTransaction($referenceNumber, $account, $counterparty, $newBalance, $transactionType, $amount, $narration)
    {
        //Log the transaction data
        Log::info('Transaction data xxxxxxxxxxxxxx', ['reference_number' => $referenceNumber, 'account' => $account, 'counterparty' => $counterparty, 'new_balance' => $newBalance, 'transaction_type' => $transactionType, 'amount' => $amount, 'narration' => $narration]);

        try {
            DB::beginTransaction();

            // Validate inputs
            $missingParams = [];
            if (!$account) $missingParams[] = 'account';
            if (!$counterparty) $missingParams[] = 'counterparty';
            if ($newBalance === null || $newBalance === '') $missingParams[] = 'new_balance';
            if (!$amount) $missingParams[] = 'amount';

            if (!empty($missingParams)) {
                Log::error('Missing transaction parameters', [
                    'missing_parameters' => $missingParams,
                    'reference_number' => $referenceNumber,
                    'account' => $account ? 'present' : 'missing',
                    'counterparty' => $counterparty ? 'present' : 'missing',
                    'new_balance' => $newBalance,
                    'amount' => $amount
                ]);
                throw new \InvalidArgumentException('Missing required transaction parameters: ' . implode(', ', $missingParams));
            }

            // Validate balance consistency
            $this->validateBalanceConsistency($account, $newBalance, $amount, $transactionType);

            // Prepare detailed ledger entry
            $ledgerData = [
                //'institution_id' => isset($account->institution_number) && $account->institution_number !== '' ? (int)$account->institution_number : null,
                'record_on_account_number' => $account->account_number,
                'record_on_account_number_balance' => $newBalance,
                'sender_branch_id' => isset($account->branch_number) && $account->branch_number !== '' ? (int)$account->branch_number : null,
                'beneficiary_branch_id' => isset($counterparty->branch_number) && $counterparty->branch_number !== '' ? (int)$counterparty->branch_number : null,
                'sender_product_id' => isset($account->product_number) && is_numeric($account->product_number) ? (int)$account->product_number : null,
                'sender_sub_product_id' => isset($account->sub_product_number) && is_numeric($account->sub_product_number) ? (int)$account->sub_product_number : null,
                'beneficiary_product_id' => isset($counterparty->product_number) && is_numeric($counterparty->product_number) ? (int)$counterparty->product_number : null,
                'beneficiary_sub_product_id' => isset($counterparty->sub_product_number) && is_numeric($counterparty->sub_product_number) ? (int)$counterparty->sub_product_number : null,
                'sender_id' => isset($account->client_number) && is_numeric($account->client_number) ? (int)$account->client_number : null,
                'beneficiary_id' => isset($counterparty->client_number) && is_numeric($counterparty->client_number) ? (int)$counterparty->client_number : null,
                'sender_name' => $transactionType === 'debit' ? $account->account_name : $counterparty->account_name,
                'beneficiary_name' => $transactionType === 'debit' ? $counterparty->account_name : $account->account_name,
                'sender_account_number' => $transactionType === 'debit' ? $account->account_number : $counterparty->account_number,
                'beneficiary_account_number' => $transactionType === 'debit' ? $counterparty->account_number : $account->account_number,
                'transaction_type' => $transactionType,
                'sender_account_currency_type' => $account->currency_type ?? 'TZS',
                'beneficiary_account_currency_type' => $counterparty->currency_type ?? 'TZS',
                'narration' => $narration,
                'branch_id' => $account->branch_number ?? null,
                'credit' => $transactionType === 'credit' ? $amount : 0,
                'debit' => $transactionType === 'debit' ? $amount : 0,
                'reference_number' => $referenceNumber,
                'trans_status' => 'System initiated',
                'trans_status_description' => 'System initiated',
                'swift_code' => $account->swift_code ?? null,
                'destination_bank_name' => $counterparty->bank_name ?? null,
                'destination_bank_number' => $counterparty->bank_number ?? null,
                'partner_bank' => $counterparty->partner_bank ?? null,
                'partner_bank_name' => $counterparty->partner_bank_name ?? null,
                'partner_bank_account_number' => $counterparty->partner_bank_account_number ?? null,
                'partner_bank_transaction_reference_number' => $counterparty->partner_bank_transaction_reference_number ?? null,
                'payment_status' => 'Done',
                'recon_status' => 'Pending',
                'loan_id' => $account->loan_id ?? null,
                'bank_reference_number' => $account->bank_reference_number ?? null,
                'product_number' => $account->product_number ?? null,
                'major_category_code' => $account->major_category_code ?? null,
                'category_code' => $account->category_code ?? null,
                'sub_category_code' => $account->sub_category_code ?? null,
                'gl_balance' => $newBalance,
                'account_level' => $account->account_level ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Log the start of the ledger entry creation
            Log::info('Attempting to create general ledger entry', $ledgerData);

            // Create the ledger entry (both debit and credit entries should have same reference_number)
            $ledgerEntry = general_ledger::create($ledgerData);

            if (!$ledgerEntry) {
                Log::error('Failed to create general ledger entry', $ledgerData);
                throw new \Exception('Failed to create general ledger entry');
            }

            // Log successful creation with the new ledger entry ID
            Log::info('General ledger entry created successfully', array_merge($ledgerData, ['ledger_entry_id' => $ledgerEntry->id]));

            DB::commit();
            
            // Log transaction record with correct field mapping for TransactionLogger
            $this->logger->logTransactionRecord([
                'reference_number' => $referenceNumber,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'debit_account' => $transactionType === 'debit' ? $account->account_number : $counterparty->account_number,
                'credit_account' => $transactionType === 'credit' ? $account->account_number : $counterparty->account_number,
                'status' => 'success',
                'ledger_entry_id' => $ledgerEntry->id,
                'narration' => $narration,
                'new_balance' => $newBalance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during general ledger entry creation', [
                'error_message' => $e->getMessage(),
                'ledger_data' => $ledgerData ?? null
            ]);
            
            // Log error with correct field mapping
            $this->logger->logTransactionRecord([
                'reference_number' => $referenceNumber ?? 'N/A',
                'transaction_type' => $transactionType ?? 'N/A',
                'amount' => $amount ?? 0,
                'debit_account' => $transactionType === 'debit' ? ($account->account_number ?? 'N/A') : ($counterparty->account_number ?? 'N/A'),
                'credit_account' => $transactionType === 'credit' ? ($account->account_number ?? 'N/A') : ($counterparty->account_number ?? 'N/A'),
                'status' => 'error',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate account balances before transaction
     * @param $debitAccount
     * @param $creditAccount
     * @param $amount
     * @throws Exception
     */
    private function validateAccountBalances($debitAccount, $creditAccount, $amount)
    {
        $debitBalance = floatval($debitAccount->balance ?? 0);
        $creditBalance = floatval($creditAccount->balance ?? 0);
        $debitType = $debitAccount->type ?? '';
        $creditType = $creditAccount->type ?? '';

        Log::info('Validating account balances', [
            'debit_account' => $debitAccount->account_number,
            'debit_balance' => $debitBalance,
            'debit_type' => $debitType,
            'credit_account' => $creditAccount->account_number,
            'credit_balance' => $creditBalance,
            'credit_type' => $creditType,
            'amount' => $amount
        ]);

        // IMPORTANT: In double-entry bookkeeping:
        // - Debiting an asset/expense INCREASES it
        // - Crediting an asset/expense DECREASES it
        // - Debiting a liability/equity/income DECREASES it
        // - Crediting a liability/equity/income INCREASES it

        // GENERAL RULE: NO NEGATIVE BALANCES ALLOWED REGARDLESS OF ACCOUNT TYPE

        // Check DEBIT side (account being debited)
        // Debiting reduces balance for liability/capital/income/equity accounts
        if (in_array($debitType, ['liability_accounts', 'capital_accounts', 'income_accounts', 'equity_accounts'])) {
            $resultingBalance = $debitBalance - $amount;
            if ($resultingBalance < 0) {
                throw new Exception("Transaction would result in negative balance for {$debitAccount->account_name}. Current balance: " . number_format($debitBalance, 2) . ", Amount to debit: " . number_format($amount, 2) . ", Would result in: " . number_format($resultingBalance, 2));
            }
        }

        // Check CREDIT side (account being credited)
        // Crediting reduces balance for asset and expense accounts
        if (in_array($creditType, ['asset_accounts', 'expense_accounts'])) {
            $resultingBalance = $creditBalance - $amount;
            if ($resultingBalance < 0) {
                throw new Exception("Transaction would result in negative balance for {$creditAccount->account_name}. Current balance: " . number_format($creditBalance, 2) . ", Amount to credit: " . number_format($amount, 2) . ", Would result in: " . number_format($resultingBalance, 2));
            }
        }

        // Additional check: Ensure no account currently has or would have negative balance
        if ($debitBalance < 0) {
            throw new Exception("Account {$debitAccount->account_name} already has a negative balance: " . number_format($debitBalance, 2) . ". Please correct this before posting new transactions.");
        }

        if ($creditBalance < 0) {
            throw new Exception("Account {$creditAccount->account_name} already has a negative balance: " . number_format($creditBalance, 2) . ". Please correct this before posting new transactions.");
        }
    }

    /**
     * Validate business rules for the transaction
     * @param $debitAccount
     * @param $creditAccount
     * @param $amount
     * @throws Exception
     */
    private function validateBusinessRules($debitAccount, $creditAccount, $amount)
    {
        // Check if accounts are active
        if (($debitAccount->status ?? 'ACTIVE') !== 'ACTIVE') {
            throw new Exception("Debit account {$debitAccount->account_name} is not active. Status: {$debitAccount->status}");
        }

        if (($creditAccount->status ?? 'ACTIVE') !== 'ACTIVE') {
            throw new Exception("Credit account {$creditAccount->account_name} is not active. Status: {$creditAccount->status}");
        }

        // Prevent same account transaction
        if ($debitAccount->account_number === $creditAccount->account_number) {
            throw new Exception("Cannot post transaction to the same account: {$debitAccount->account_number}");
        }

        // Check for locked amounts
        if (isset($debitAccount->locked_amount) && $debitAccount->locked_amount > 0) {
            $availableBalance = floatval($debitAccount->balance) - floatval($debitAccount->locked_amount);
            if ($debitAccount->type === 'asset_accounts' && $availableBalance < 0) {
                throw new Exception("Insufficient available balance in {$debitAccount->account_name} after considering locked amount. Available: " . number_format($availableBalance, 2));
            }
        }

        if (isset($creditAccount->locked_amount) && $creditAccount->locked_amount > 0) {
            $availableBalance = floatval($creditAccount->balance) - floatval($creditAccount->locked_amount);
            if ($creditAccount->type === 'asset_accounts' && $availableBalance < $amount) {
                throw new Exception("Insufficient available balance in {$creditAccount->account_name} to credit after considering locked amount. Available: " . number_format($availableBalance, 2));
            }
        }

        // Validate minimum transaction amount
        $minTransactionAmount = config('accounting.min_transaction_amount', 0.01);
        if ($amount < $minTransactionAmount) {
            throw new Exception("Transaction amount must be at least " . number_format($minTransactionAmount, 2));
        }

        // Validate maximum transaction amount
        $maxTransactionAmount = config('accounting.max_transaction_amount', 999999999.99);
        if ($amount > $maxTransactionAmount) {
            throw new Exception("Transaction amount exceeds maximum allowed: " . number_format($maxTransactionAmount, 2));
        }

        // Check for suspense accounts
        if ($debitAccount->suspense_account === 'YES' || $creditAccount->suspense_account === 'YES') {
            Log::warning('Transaction involves suspense account', [
                'debit_account' => $debitAccount->account_number,
                'credit_account' => $creditAccount->account_number
            ]);
        }

        // Validate account levels for posting
        $minPostingLevel = config('accounting.min_posting_level', 3);
        if ($debitAccount->account_level < $minPostingLevel) {
            throw new Exception("Cannot post to parent account {$debitAccount->account_name}. Only detail accounts (level {$minPostingLevel}+) can be posted to.");
        }

        if ($creditAccount->account_level < $minPostingLevel) {
            throw new Exception("Cannot post to parent account {$creditAccount->account_name}. Only detail accounts (level {$minPostingLevel}+) can be posted to.");
        }
    }

    /**
     * Dispatch transaction notifications for member accounts
     * 
     * @param $debitAccount
     * @param $creditAccount
     * @param $amount
     * @param $debitNewBalance
     * @param $creditNewBalance
     * @param $referenceNumber
     * @param $narration
     * @param string $status
     * @param string|null $errorMessage
     */
    private function dispatchTransactionNotifications(
        $debitAccount, 
        $creditAccount, 
        $amount, 
        $debitNewBalance, 
        $creditNewBalance, 
        $referenceNumber, 
        $narration,
        $status = 'success',
        $errorMessage = null
    ) {
        try {
            // Check if notifications are enabled
            if (!config('accounting.notifications.enabled', true)) {
                Log::info('Transaction notifications are disabled', [
                    'reference_number' => $referenceNumber
                ]);
                return;
            }

            // Check if we should notify based on status
            if ($status === 'success' && !config('accounting.notifications.notify_on_success', true)) {
                return;
            }

            if ($status === 'failed' && !config('accounting.notifications.notify_on_failure', true)) {
                return;
            }

            // Check minimum amount threshold
            $minAmount = config('accounting.notifications.min_amount_for_notification', 0);
            if ($amount < $minAmount) {
                Log::info('Transaction amount below notification threshold', [
                    'amount' => $amount,
                    'threshold' => $minAmount,
                    'reference_number' => $referenceNumber
                ]);
                return;
            }

            $queueName = config('accounting.notifications.queue_name', 'notifications');

            // Check and send notification for debit account
            if ($this->isMemberAccount($debitAccount)) {
                Log::info('Dispatching debit notification for member account', [
                    'account_number' => $debitAccount->account_number,
                    'client_number' => $debitAccount->client_number,
                    'reference_number' => $referenceNumber
                ]);

                $job = SendTransactionNotification::dispatch(
                    $debitAccount->account_number,
                    'debit',
                    $amount,
                    $debitNewBalance,
                    $referenceNumber,
                    $narration,
                    $status,
                    $creditAccount->account_name,
                    $errorMessage
                )->onQueue($queueName);

                // Set retry attempts if configured
                if (config('accounting.notifications.retry_failed', true)) {
                    // Removed deprecated property assignment
                    // Retry configuration should be handled in the job class itself
                }
            }

            // Check and send notification for credit account
            if ($this->isMemberAccount($creditAccount)) {
                Log::info('Dispatching credit notification for member account', [
                    'account_number' => $creditAccount->account_number,
                    'client_number' => $creditAccount->client_number,
                    'reference_number' => $referenceNumber
                ]);

                $job = SendTransactionNotification::dispatch(
                    $creditAccount->account_number,
                    'credit',
                    $amount,
                    $creditNewBalance,
                    $referenceNumber,
                    $narration,
                    $status,
                    $debitAccount->account_name,
                    $errorMessage
                )->onQueue($queueName);

                // Set retry attempts if configured
                if (config('accounting.notifications.retry_failed', true)) {
                    // Removed deprecated property assignment
                    // Retry configuration should be handled in the job class itself
                }
            }

            // Determine if we need to notify control emails
            $isDebitMember = $this->isMemberAccount($debitAccount);
            $isCreditMember = $this->isMemberAccount($creditAccount);
            
            // Notify control emails for:
            // 1. Pure internal transactions (both accounts are non-member)
            // 2. Mixed transactions (one member, one internal)
            if (!$isDebitMember || !$isCreditMember) {
                // Determine transaction type for logging
                $transactionType = 'unknown';
                if (!$isDebitMember && !$isCreditMember) {
                    $transactionType = 'internal';
                    Log::info('Pure internal transaction detected, notifying control emails', [
                        'debit_account' => $debitAccount->account_number,
                        'credit_account' => $creditAccount->account_number,
                        'reference_number' => $referenceNumber
                    ]);
                } else {
                    $transactionType = 'mixed';
                    Log::info('Mixed transaction detected (member + internal), notifying control emails', [
                        'debit_account' => $debitAccount->account_number,
                        'debit_is_member' => $isDebitMember,
                        'credit_account' => $creditAccount->account_number,
                        'credit_is_member' => $isCreditMember,
                        'reference_number' => $referenceNumber
                    ]);
                }

                // Send notification to control emails for internal/mixed transactions
                $this->notifyControlEmails(
                    $debitAccount,
                    $creditAccount,
                    $amount,
                    $debitNewBalance,
                    $creditNewBalance,
                    $referenceNumber,
                    $narration,
                    $status,
                    $errorMessage,
                    $transactionType
                );
            }

        } catch (\Exception $e) {
            // Don't let notification errors affect the transaction
            Log::error('Failed to dispatch transaction notifications', [
                'error' => $e->getMessage(),
                'reference_number' => $referenceNumber
            ]);
        }
    }

    /**
     * Check if an account belongs to a member
     * 
     * @param $account
     * @return bool
     */
    private function isMemberAccount($account)
    {
        return !empty($account->client_number) && 
               $account->client_number !== null && 
               $account->client_number !== '0000' &&
               $account->client_number !== '0';
    }

    /**
     * Notify control emails about internal/system account transactions
     * 
     * @param $debitAccount
     * @param $creditAccount
     * @param $amount
     * @param $debitNewBalance
     * @param $creditNewBalance
     * @param $referenceNumber
     * @param $narration
     * @param string $status
     * @param string|null $errorMessage
     * @param string $transactionType
     */
    private function notifyControlEmails(
        $debitAccount,
        $creditAccount,
        $amount,
        $debitNewBalance,
        $creditNewBalance,
        $referenceNumber,
        $narration,
        $status = 'success',
        $errorMessage = null,
        $transactionType = 'internal'
    ) {
        try {
            // Get control emails from environment
            $controlEmails = env('CONTROL_EMAILS', '');
            
            if (empty($controlEmails)) {
                Log::info('No control emails configured for internal transaction notifications');
                return;
            }

            // Convert comma-separated emails to array
            $emailList = array_map('trim', explode(',', $controlEmails));
            $emailList = array_filter($emailList, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            if (empty($emailList)) {
                Log::warning('Control emails configured but none are valid', ['configured_emails' => $controlEmails]);
                return;
            }

            // Dispatch notification job for each control email
            foreach ($emailList as $email) {
                Log::info('Dispatching control email notification', [
                    'email' => $this->maskEmailForLog($email),
                    'reference_number' => $referenceNumber,
                    'transaction_type' => $transactionType
                ]);

                SendControlTransactionNotification::dispatch(
                    $email,
                    $debitAccount,
                    $creditAccount,
                    $amount,
                    $debitNewBalance,
                    $creditNewBalance,
                    $referenceNumber,
                    $narration,
                    $status,
                    $errorMessage,
                    $transactionType
                )->onQueue(config('accounting.notifications.queue_name', 'notifications'));
            }

            Log::info('Control email notifications dispatched', [
                'count' => count($emailList),
                'reference_number' => $referenceNumber
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to notify control emails', [
                'error' => $e->getMessage(),
                'reference_number' => $referenceNumber
            ]);
        }
    }

    /**
     * Mask email for logging
     * 
     * @param string $email
     * @return string
     */
    private function maskEmailForLog($email)
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return 'invalid_email';
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        if (strlen($username) <= 3) {
            return $username . '@' . $domain;
        }
        
        return substr($username, 0, 3) . '***@' . $domain;
    }

    private function validateBalanceConsistency($account, $newBalance, $amount, $transactionType)
    {
        $currentBalance = $account->balance;
        $type = $account->type;

        if ($transactionType === 'debit') {
            if (in_array($type, ['asset_accounts', 'expense_accounts'])) {
                $expectedBalance = $currentBalance + $amount;
            } else { // liability, capital, income
                $expectedBalance = $currentBalance - $amount;
            }
        } else { // credit
            if (in_array($type, ['asset_accounts', 'expense_accounts'])) {
                $expectedBalance = $currentBalance - $amount;
            } else { // liability, capital, income
                $expectedBalance = $currentBalance + $amount;
            }
        }

        // Log the check
        Log::info('Validating balance consistency', [
            'account_number' => $account->account_number,
            'account_type' => $type,
            'transaction_type' => $transactionType,
            'current_balance' => $currentBalance,
            'amount' => $amount,
            'expected_balance' => $expectedBalance,
            'new_balance' => $newBalance
        ]);

        if (abs($newBalance - $expectedBalance) > 0.01) {
            Log::error('Balance inconsistency detected', [
                'account_number' => $account->account_number,
                'account_type' => $type,
                'transaction_type' => $transactionType,
                'current_balance' => $currentBalance,
                'amount' => $amount,
                'expected_balance' => $expectedBalance,
                'new_balance' => $newBalance
            ]);
            throw new \Exception(sprintf(
                'Balance inconsistency detected. Expected: %f, Got: %f',
                $expectedBalance,
                $newBalance
            ));
        }
    }
}
