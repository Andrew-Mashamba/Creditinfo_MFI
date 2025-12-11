<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JournalEntryService
{
    private $validator;
    private $balanceManager;
    private $logger;
    private $postingService;

    public function __construct()
    {
        $this->validator = new TransactionValidator();
        $this->balanceManager = new BalanceManager();
        $this->logger = new TransactionLogger();
        $this->postingService = new TransactionPostingService();
    }

    /**
     * Create a new journal entry
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createJournalEntry(array $data)
    {
        Log::info('Starting journal entry creation', [
            'transaction_date' => $data['transaction_date'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'lines_count' => count($data['lines'] ?? [])
        ]);

        try {
            // Validate journal entry data
            $this->validateJournalEntry($data);

            DB::beginTransaction();

            // Calculate total amount (should equal both debit and credit totals)
            $totalAmount = $this->calculateTotalAmount($data['lines']);

            // Create journal entry record
            $journalEntryId = DB::table('journal_entries')->insertGetId([
                'transaction_date' => $data['transaction_date'],
                'reference_no' => $data['reference_no'],
                'description' => $data['description'],
                'total_amount' => $totalAmount,
                'is_reversal' => $data['is_reversal'] ?? false,
                'reversed_journal_id' => $data['reversed_journal_id'] ?? null,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create journal entry lines
            foreach ($data['lines'] as $line) {
                $account = AccountsModel::where('account_number', $line['account_code'])->first();

                if (!$account) {
                    throw new Exception("Account not found: {$line['account_code']}");
                }

                DB::table('journal_entry_lines')->insert([
                    'journal_entry_id' => $journalEntryId,
                    'account_code' => $line['account_code'],
                    'account_name' => $account->account_name,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Journal entry created successfully', [
                'journal_entry_id' => $journalEntryId,
                'reference_no' => $data['reference_no']
            ]);

            return [
                'status' => 'success',
                'journal_entry_id' => $journalEntryId,
                'reference_no' => $data['reference_no'],
                'message' => 'Journal entry created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Journal entry creation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Post journal entry to general ledger
     * This updates account balances and creates GL entries
     *
     * @param int $journalEntryId
     * @return array
     * @throws Exception
     */
    public function postJournalEntry(int $journalEntryId)
    {
        Log::info('Starting journal entry posting', ['journal_entry_id' => $journalEntryId]);

        try {
            DB::beginTransaction();

            // Get journal entry
            $journalEntry = DB::table('journal_entries')->where('id', $journalEntryId)->first();

            if (!$journalEntry) {
                throw new Exception("Journal entry not found: {$journalEntryId}");
            }

            // Check if already posted
            if ($journalEntry->is_posted ?? false) {
                throw new Exception("Journal entry {$journalEntry->reference_no} has already been posted");
            }

            // Get journal entry lines
            $lines = DB::table('journal_entry_lines')
                ->where('journal_entry_id', $journalEntryId)
                ->get();

            if ($lines->isEmpty()) {
                throw new Exception("No lines found for journal entry: {$journalEntryId}");
            }

            // Validate accounts are still active and valid
            $this->validateAccountsForPosting($lines);

            // Process each line and post to GL
            foreach ($lines as $line) {
                $account = AccountsModel::where('account_number', $line->account_code)->first();

                if (!$account) {
                    throw new Exception("Account not found: {$line->account_code}");
                }

                $amount = max($line->debit, $line->credit);
                $transactionType = $line->debit > 0 ? 'debit' : 'credit';

                // Calculate new balance based on account type and transaction type
                $newBalance = $this->calculateNewBalance(
                    $account,
                    $amount,
                    $transactionType
                );

                // Validate balance won't go negative
                if ($newBalance < 0) {
                    throw new Exception(
                        "Posting would result in negative balance for account {$account->account_name}. " .
                        "Current balance: " . number_format($account->balance, 2) .
                        ", New balance: " . number_format($newBalance, 2)
                    );
                }

                // Record to general ledger
                $this->recordToGeneralLedger(
                    $journalEntry,
                    $account,
                    $line,
                    $newBalance,
                    $transactionType
                );

                // Update account balance
                $this->updateAccountBalanceForPosting(
                    $account,
                    $newBalance,
                    $amount,
                    $transactionType
                );
            }

            // Mark journal entry as posted
            DB::table('journal_entries')
                ->where('id', $journalEntryId)
                ->update([
                    'is_posted' => true,
                    'posted_at' => now(),
                    'posted_by' => Auth::id(),
                    'updated_at' => now()
                ]);

            DB::commit();

            Log::info('Journal entry posted successfully', [
                'journal_entry_id' => $journalEntryId,
                'reference_no' => $journalEntry->reference_no
            ]);

            return [
                'status' => 'success',
                'journal_entry_id' => $journalEntryId,
                'reference_no' => $journalEntry->reference_no,
                'message' => 'Journal entry posted successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Journal entry posting failed', [
                'journal_entry_id' => $journalEntryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reverse a posted journal entry
     *
     * @param int $journalEntryId
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function reverseJournalEntry(int $journalEntryId, string $reason = '')
    {
        Log::info('Starting journal entry reversal', [
            'journal_entry_id' => $journalEntryId,
            'reason' => $reason
        ]);

        try {
            DB::beginTransaction();

            // Get original journal entry
            $originalEntry = DB::table('journal_entries')->where('id', $journalEntryId)->first();

            if (!$originalEntry) {
                throw new Exception("Journal entry not found: {$journalEntryId}");
            }

            if (!($originalEntry->is_posted ?? false)) {
                throw new Exception("Only posted journal entries can be reversed");
            }

            if ($originalEntry->is_reversal) {
                throw new Exception("Cannot reverse a reversal entry");
            }

            // Check if already reversed
            $existingReversal = DB::table('journal_entries')
                ->where('reversed_journal_id', $journalEntryId)
                ->first();

            if ($existingReversal) {
                throw new Exception("This journal entry has already been reversed. Reversal reference: {$existingReversal->reference_no}");
            }

            // Get original lines
            $originalLines = DB::table('journal_entry_lines')
                ->where('journal_entry_id', $journalEntryId)
                ->get();

            // Create reversal entry
            $reversalReferenceNo = 'REV-' . $originalEntry->reference_no;

            $reversalData = [
                'transaction_date' => now()->format('Y-m-d'),
                'reference_no' => $reversalReferenceNo,
                'description' => 'REVERSAL: ' . $originalEntry->description . ($reason ? ' - Reason: ' . $reason : ''),
                'lines' => [],
                'is_reversal' => true,
                'reversed_journal_id' => $journalEntryId
            ];

            // Swap debits and credits for reversal
            foreach ($originalLines as $line) {
                $reversalData['lines'][] = [
                    'account_code' => $line->account_code,
                    'debit' => $line->credit, // Swap
                    'credit' => $line->debit,  // Swap
                    'description' => 'Reversal of: ' . $line->description
                ];
            }

            // Create and post the reversal entry
            $reversalResult = $this->createJournalEntry($reversalData);
            $this->postJournalEntry($reversalResult['journal_entry_id']);

            DB::commit();

            Log::info('Journal entry reversed successfully', [
                'original_journal_entry_id' => $journalEntryId,
                'reversal_journal_entry_id' => $reversalResult['journal_entry_id'],
                'reversal_reference_no' => $reversalReferenceNo
            ]);

            return [
                'status' => 'success',
                'original_journal_entry_id' => $journalEntryId,
                'reversal_journal_entry_id' => $reversalResult['journal_entry_id'],
                'reversal_reference_no' => $reversalReferenceNo,
                'message' => 'Journal entry reversed successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Journal entry reversal failed', [
                'journal_entry_id' => $journalEntryId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get journal entry with lines
     *
     * @param int $journalEntryId
     * @return array
     */
    public function getJournalEntry(int $journalEntryId)
    {
        $entry = DB::table('journal_entries')->where('id', $journalEntryId)->first();

        if (!$entry) {
            throw new Exception("Journal entry not found: {$journalEntryId}");
        }

        $lines = DB::table('journal_entry_lines')
            ->where('journal_entry_id', $journalEntryId)
            ->get();

        return [
            'entry' => $entry,
            'lines' => $lines
        ];
    }

    /**
     * Validate journal entry data
     *
     * @param array $data
     * @throws Exception
     */
    private function validateJournalEntry(array $data)
    {
        // Validate required fields
        if (empty($data['transaction_date'])) {
            throw new Exception('Transaction date is required');
        }

        if (empty($data['reference_no'])) {
            throw new Exception('Reference number is required');
        }

        if (empty($data['description'])) {
            throw new Exception('Description is required');
        }

        if (empty($data['lines']) || count($data['lines']) < 2) {
            throw new Exception('Journal entry must have at least 2 lines');
        }

        // Check for duplicate reference number
        $existing = DB::table('journal_entries')
            ->where('reference_no', $data['reference_no'])
            ->first();

        if ($existing) {
            throw new Exception("Reference number already exists: {$data['reference_no']}");
        }

        // Validate that entry is balanced
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($data['lines'] as $line) {
            if (empty($line['account_code'])) {
                throw new Exception('Account code is required for all lines');
            }

            $debit = floatval($line['debit'] ?? 0);
            $credit = floatval($line['credit'] ?? 0);

            // Validate each line has either debit or credit (but not both or neither)
            if ($debit > 0 && $credit > 0) {
                throw new Exception('A line cannot have both debit and credit amounts');
            }

            if ($debit == 0 && $credit == 0) {
                throw new Exception('Each line must have either a debit or credit amount');
            }

            $totalDebit += $debit;
            $totalCredit += $credit;

            // Verify account exists and is active
            $account = AccountsModel::where('account_number', $line['account_code'])->first();

            if (!$account) {
                throw new Exception("Account not found: {$line['account_code']}");
            }

            if ($account->status !== 'ACTIVE') {
                throw new Exception("Account {$account->account_name} is not active. Status: {$account->status}");
            }

            // Validate account can be posted to (must be detail account - level 3 or above)
            $minPostingLevel = 3;
            if (!empty($account->account_level) && $account->account_level < $minPostingLevel) {
                throw new Exception("Cannot post to parent account {$account->account_name} (Level {$account->account_level}). Only detail accounts (level {$minPostingLevel} and above) can be posted to.");
            }
        }

        // Check if balanced (within 0.01 tolerance for floating point)
        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new Exception(
                'Journal entry is not balanced. ' .
                'Total Debits: ' . number_format($totalDebit, 2) . ', ' .
                'Total Credits: ' . number_format($totalCredit, 2) . ', ' .
                'Difference: ' . number_format(abs($totalDebit - $totalCredit), 2)
            );
        }

        // Validate transaction date is not in the future
        $transactionDate = Carbon::parse($data['transaction_date']);
        if ($transactionDate->isFuture()) {
            throw new Exception('Transaction date cannot be in the future');
        }

        Log::info('Journal entry validation passed', [
            'reference_no' => $data['reference_no'],
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'lines_count' => count($data['lines'])
        ]);
    }

    /**
     * Calculate total amount for journal entry
     *
     * @param array $lines
     * @return float
     */
    private function calculateTotalAmount(array $lines)
    {
        $totalDebit = 0;

        foreach ($lines as $line) {
            $totalDebit += floatval($line['debit'] ?? 0);
        }

        return $totalDebit;
    }

    /**
     * Calculate new balance after posting
     *
     * @param object $account
     * @param float $amount
     * @param string $transactionType
     * @return float
     */
    private function calculateNewBalance($account, $amount, $transactionType)
    {
        $currentBalance = floatval($account->balance ?? 0);
        $accountType = $account->type;

        // Apply double-entry bookkeeping rules
        // Assets & Expenses: Debit increases, Credit decreases
        // Liabilities, Equity & Income: Credit increases, Debit decreases

        if ($transactionType === 'debit') {
            if (in_array($accountType, ['asset_accounts', 'expense_accounts'])) {
                return $currentBalance + $amount;
            } else {
                return $currentBalance - $amount;
            }
        } else { // credit
            if (in_array($accountType, ['asset_accounts', 'expense_accounts'])) {
                return $currentBalance - $amount;
            } else {
                return $currentBalance + $amount;
            }
        }
    }

    /**
     * Update account balance after posting
     *
     * @param object $account
     * @param float $newBalance
     * @param float $amount
     * @param string $action
     * @throws Exception
     */
    private function updateAccountBalanceForPosting($account, $newBalance, $amount, $action)
    {
        Log::info('Updating account balance', [
            'account_number' => $account->account_number,
            'account_name' => $account->account_name,
            'previous_balance' => $account->balance,
            'new_balance' => $newBalance,
            'amount' => $amount,
            'action' => $action
        ]);

        // Update the account balance
        AccountsModel::where('account_number', $account->account_number)
            ->update([
                'balance' => (float)$newBalance,
                $action => ($account->{$action} ?? 0) + (float)$amount,
                'updated_at' => now()
            ]);

        // Update parent accounts recursively
        $this->updateParentAccounts($account, $amount, $action);
    }

    /**
     * Update parent accounts recursively
     *
     * @param object $account
     * @param float $amount
     * @param string $action
     */
    private function updateParentAccounts($account, $amount, $action)
    {
        if (!$account->parent_account_number) {
            return;
        }

        $parentAccount = AccountsModel::where('account_number', $account->parent_account_number)->first();

        if (!$parentAccount) {
            return;
        }

        // Calculate balance change based on account type
        $accountType = $parentAccount->type;

        if ($action === 'debit') {
            if (in_array($accountType, ['asset_accounts', 'expense_accounts'])) {
                $balanceChange = $amount;
            } else {
                $balanceChange = -$amount;
            }
        } else { // credit
            if (in_array($accountType, ['asset_accounts', 'expense_accounts'])) {
                $balanceChange = -$amount;
            } else {
                $balanceChange = $amount;
            }
        }

        $newParentBalance = ($parentAccount->balance ?? 0) + $balanceChange;

        // Update parent account
        AccountsModel::where('account_number', $parentAccount->account_number)
            ->update([
                'balance' => (float)$newParentBalance,
                $action => ($parentAccount->{$action} ?? 0) + (float)$amount,
                'updated_at' => now()
            ]);

        Log::info('Parent account updated', [
            'parent_account_number' => $parentAccount->account_number,
            'parent_account_name' => $parentAccount->account_name,
            'previous_balance' => $parentAccount->balance,
            'new_balance' => $newParentBalance,
            'amount' => $amount,
            'action' => $action
        ]);

        // Recursively update higher level parents
        $this->updateParentAccounts($parentAccount, $amount, $action);
    }

    /**
     * Record journal entry to general ledger
     *
     * @param object $journalEntry
     * @param object $account
     * @param object $line
     * @param float $newBalance
     * @param string $transactionType
     */
    private function recordToGeneralLedger($journalEntry, $account, $line, $newBalance, $transactionType)
    {
        $amount = $transactionType === 'debit' ? $line->debit : $line->credit;

        // Follow TransactionPostingService pattern: convert empty strings to NULL for integer fields
        $ledgerData = [
            'record_on_account_number' => $account->account_number,
            'record_on_account_number_balance' => $newBalance,
            'sender_branch_id' => isset($account->branch_number) && $account->branch_number !== '' ? (int)$account->branch_number : null,
            'beneficiary_branch_id' => null,
            'sender_product_id' => isset($account->product_number) && is_numeric($account->product_number) ? (int)$account->product_number : null,
            'sender_sub_product_id' => isset($account->sub_product_number) && is_numeric($account->sub_product_number) ? (int)$account->sub_product_number : null,
            'beneficiary_product_id' => null,
            'beneficiary_sub_product_id' => null,
            'sender_id' => isset($account->client_number) && is_numeric($account->client_number) ? (int)$account->client_number : null,
            'beneficiary_id' => null,
            'sender_name' => $account->account_name,
            'beneficiary_name' => 'Journal Entry',
            'sender_account_number' => $account->account_number,
            'beneficiary_account_number' => $account->account_number,
            'transaction_type' => $transactionType,
            'sender_account_currency_type' => $account->currency_type ?? 'TZS',
            'beneficiary_account_currency_type' => $account->currency_type ?? 'TZS',
            'narration' => $journalEntry->description . ' | ' . $line->description,
            'branch_id' => isset($account->branch_number) && $account->branch_number !== '' ? (int)$account->branch_number : null,
            'credit' => $transactionType === 'credit' ? $amount : 0,
            'debit' => $transactionType === 'debit' ? $amount : 0,
            'reference_number' => $journalEntry->reference_no,
            'trans_status' => 'Journal Entry',
            'trans_status_description' => 'Posted from Journal Entry: ' . $journalEntry->reference_no,
            'payment_status' => 'Done',
            'recon_status' => 'Pending',
            'product_number' => isset($account->product_number) && $account->product_number !== '' ? $account->product_number : null,
            'major_category_code' => isset($account->major_category_code) && $account->major_category_code !== '' ? $account->major_category_code : null,
            'category_code' => isset($account->category_code) && $account->category_code !== '' ? $account->category_code : null,
            'sub_category_code' => isset($account->sub_category_code) && $account->sub_category_code !== '' ? $account->sub_category_code : null,
            'gl_balance' => $newBalance,
            'account_level' => isset($account->account_level) && $account->account_level !== '' ? $account->account_level : null,
            'created_at' => now(),
            'updated_at' => now()
        ];

        try {
            $ledgerEntry = general_ledger::create($ledgerData);

            Log::info('General ledger entry created', [
                'ledger_entry_id' => $ledgerEntry->id,
                'account_number' => $account->account_number,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'reference_no' => $journalEntry->reference_no
            ]);

        } catch (Exception $e) {
            Log::error('Failed to create general ledger entry', [
                'error' => $e->getMessage(),
                'ledger_data' => $ledgerData
            ]);
            throw $e;
        }
    }

    /**
     * Validate accounts before posting
     *
     * @param $lines
     * @throws Exception
     */
    private function validateAccountsForPosting($lines)
    {
        foreach ($lines as $line) {
            $account = AccountsModel::where('account_number', $line->account_code)->first();

            if (!$account) {
                throw new Exception("Account not found: {$line->account_code}");
            }

            if ($account->status !== 'ACTIVE') {
                throw new Exception("Account {$account->account_name} is not active. Cannot post journal entry.");
            }

            // Validate account can be posted to (must be detail account - level 3 or above)
            $minPostingLevel = 3;
            if (!empty($account->account_level) && $account->account_level < $minPostingLevel) {
                throw new Exception("Cannot post to parent account {$account->account_name} (Level {$account->account_level}). Only detail accounts (level {$minPostingLevel} and above) can be posted to.");
            }
        }
    }
}
