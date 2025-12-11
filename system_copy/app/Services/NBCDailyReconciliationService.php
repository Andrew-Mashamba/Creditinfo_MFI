<?php

namespace App\Services;

use App\Models\AnalysisSession;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Transaction;
use App\Models\ReconMatchingTransaction;
use App\Models\ReconNonMatchingTransaction;
use App\Models\ReconAuditLog;
use App\Services\NBCStatementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * NBC Daily Reconciliation Service
 *
 * Handles automated daily bank statement fetching and reconciliation
 * for NBC SACCOS' NBC Bank account.
 */
class NBCDailyReconciliationService
{
    protected NBCStatementService $nbcStatementService;
    protected ?BankAccount $bankAccount = null;

    public function __construct(NBCStatementService $nbcStatementService)
    {
        $this->nbcStatementService = $nbcStatementService;
    }

    /**
     * Get the NBC bank account from database
     *
     * @return BankAccount
     * @throws Exception
     */
    protected function getNBCBankAccount(): BankAccount
    {
        if ($this->bankAccount !== null) {
            return $this->bankAccount;
        }

        // Get bank account with id = 1 (NBC main account)
        $this->bankAccount = BankAccount::find(1);

        if (!$this->bankAccount) {
            throw new Exception('NBC Bank Account (id=1) not found in bank_accounts table');
        }

        if ($this->bankAccount->status !== 'ACTIVE') {
            throw new Exception('NBC Bank Account (id=1) is not active. Status: ' . $this->bankAccount->status);
        }

        return $this->bankAccount;
    }

    /**
     * Execute daily NBC bank reconciliation
     *
     * @return array Results of the reconciliation process
     */
    public function executeDailyReconciliation(): array
    {
        $startTime = microtime(true);

        try {
            // Get NBC bank account from database
            $bankAccount = $this->getNBCBankAccount();

            Log::info('NBC Daily Reconciliation Started', [
                'bank_account_id' => $bankAccount->id,
                'account_number' => $bankAccount->account_number,
                'account_name' => $bankAccount->account_name,
                'date' => now()->toDateString()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get NBC bank account', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }

        DB::beginTransaction();

        try {
            // Step 1: Create new reconciliation session
            $session = $this->createReconciliationSession();

            Log::info('Reconciliation session created', [
                'session_id' => $session->id,
                'bank_account_id' => $session->bank_account_id,
                'account_number' => $session->account_number,
                'account_name' => $session->account_name
            ]);

            // Step 2: Fetch NBC bank statement transactions
            $statementTransactions = $this->fetchNBCBankStatement();

            Log::info('Bank statement fetched', [
                'session_id' => $session->id,
                'transaction_count' => count($statementTransactions)
            ]);

            // Step 3: Insert transactions into bank_transactions table with duplicate checking
            $insertedCount = $this->insertBankTransactions($session->id, $statementTransactions);

            Log::info('Bank transactions inserted', [
                'session_id' => $session->id,
                'inserted_count' => $insertedCount,
                'duplicates_skipped' => count($statementTransactions) - $insertedCount
            ]);

            // Step 4: Run reconciliation process
            $reconciliationResults = $this->runReconciliation($session->id);

            Log::info('Reconciliation completed', [
                'session_id' => $session->id,
                'matched_count' => $reconciliationResults['matched_count'],
                'unmatched_count' => $reconciliationResults['unmatched_count']
            ]);

            // Step 5: Update session with results
            $this->updateSessionResults($session, $reconciliationResults);

            // Step 6: Log completion
            ReconAuditLog::logAction(
                $session->id,
                'daily_reconciliation_completed',
                'analysis_session',
                $session->id,
                null,
                [
                    'matched_count' => $reconciliationResults['matched_count'],
                    'unmatched_count' => $reconciliationResults['unmatched_count'],
                    'total_transactions' => $insertedCount
                ],
                'Daily NBC bank reconciliation completed successfully'
            );

            DB::commit();

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('NBC Daily Reconciliation Completed Successfully', [
                'session_id' => $session->id,
                'execution_time_ms' => $executionTime,
                'results' => $reconciliationResults
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'matched_count' => $reconciliationResults['matched_count'],
                'unmatched_count' => $reconciliationResults['unmatched_count'],
                'total_transactions' => $insertedCount,
                'execution_time_ms' => $executionTime
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('NBC Daily Reconciliation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
    }

    /**
     * Create a new reconciliation session
     *
     * @return AnalysisSession
     * @throws Exception
     */
    protected function createReconciliationSession(): AnalysisSession
    {
        // Get NBC bank account from database
        $bankAccount = $this->getNBCBankAccount();

        // Create reconciliation session
        $session = AnalysisSession::create([
            'bank_account_id' => $bankAccount->id,
            'account_name' => $bankAccount->account_name,
            'account_number' => $bankAccount->account_number,
            'mirror_account_number' => $bankAccount->internal_mirror_account_number,
            'bank' => $bankAccount->bank_name,
            'statement_period' => now()->format('F Y'),
            'status' => 'processing',
            'total_transactions' => 0,
            'matched_count' => 0,
            'unmatched_count' => 0,
            'file_path' => 'automated_daily_pull',
            'uploaded_by' => 1 // System user
        ]);

        // Log session creation
        ReconAuditLog::logAction(
            $session->id,
            'session_created',
            'analysis_session',
            $session->id,
            null,
            [
                'bank_account_id' => $bankAccount->id,
                'account_number' => $bankAccount->account_number,
                'account_name' => $bankAccount->account_name,
                'mirror_account' => $bankAccount->internal_mirror_account_number,
                'bank' => $bankAccount->bank_name,
                'status' => 'processing'
            ],
            'Automated daily reconciliation session created for ' . $bankAccount->account_name
        );

        return $session;
    }

    /**
     * Fetch NBC bank statement transactions using NBC PVAS API
     *
     * @return array Array of transaction records mapped to bank_transactions format
     * @throws Exception
     */
    protected function fetchNBCBankStatement(): array
    {
        // Get NBC bank account from database
        $bankAccount = $this->getNBCBankAccount();

        // Date range: Get yesterday's transactions
        $statementDate = now()->subDay();

        Log::info('Fetching NBC bank statement via PVAS API', [
            'bank_account_id' => $bankAccount->id,
            'account_number' => $bankAccount->account_number,
            'account_name' => $bankAccount->account_name,
            'statement_date' => $statementDate->toDateString()
        ]);

        try {
            // Fetch transactions from NBC PVAS API
            $nbcTransactions = $this->nbcStatementService->fetchAccountStatement(
                $bankAccount->account_number,
                $statementDate
            );

            Log::info('NBC statement fetched successfully', [
                'bank_account_id' => $bankAccount->id,
                'account_number' => $bankAccount->account_number,
                'transaction_count' => count($nbcTransactions)
            ]);

            // Map NBC transaction format to our bank_transactions format
            $mappedTransactions = array_map(
                [$this->nbcStatementService, 'mapNBCTransaction'],
                $nbcTransactions
            );

            return $mappedTransactions;

        } catch (Exception $e) {
            Log::error('Error fetching NBC bank statement', [
                'error' => $e->getMessage(),
                'bank_account_id' => $bankAccount->id,
                'account_number' => $bankAccount->account_number
            ]);

            throw new Exception('Failed to fetch NBC bank statement: ' . $e->getMessage());
        }
    }

    /**
     * Insert bank transactions with duplicate checking
     *
     * @param int $sessionId
     * @param array $statementTransactions
     * @return int Number of inserted transactions
     */
    protected function insertBankTransactions(int $sessionId, array $statementTransactions): int
    {
        $insertedCount = 0;

        foreach ($statementTransactions as $txn) {
            // Check for duplicate based on: session_id, date, reference, amount
            $exists = BankTransaction::where('session_id', $sessionId)
                ->where('transaction_date', $txn['transaction_date'])
                ->where('reference_number', $txn['reference_number'])
                ->where(function($query) use ($txn) {
                    $query->where('deposit_amount', $txn['deposit_amount'] ?? 0)
                          ->orWhere('withdrawal_amount', $txn['withdrawal_amount'] ?? 0);
                })
                ->exists();

            if ($exists) {
                Log::info('Duplicate transaction detected, skipping', [
                    'session_id' => $sessionId,
                    'reference' => $txn['reference_number'],
                    'date' => $txn['transaction_date']
                ]);
                continue;
            }

            // Insert new transaction
            BankTransaction::create([
                'session_id' => $sessionId,
                'transaction_date' => $txn['transaction_date'],
                'value_date' => $txn['value_date'] ?? $txn['transaction_date'],
                'reference_number' => $txn['reference_number'],
                'narration' => $txn['narration'] ?? '',
                'withdrawal_amount' => $txn['withdrawal_amount'] ?? 0,
                'deposit_amount' => $txn['deposit_amount'] ?? 0,
                'balance' => $txn['balance'] ?? 0,
                'reconciliation_status' => 'unreconciled',
                'recon_match_type' => 'none',
                'branch' => $txn['branch'] ?? null,
                'transaction_type' => ($txn['deposit_amount'] ?? 0) > 0 ? 'credit' : 'debit'
            ]);

            $insertedCount++;
        }

        return $insertedCount;
    }

    /**
     * Run reconciliation between bank_transactions and transactions tables
     *
     * @param int $sessionId
     * @return array Reconciliation results
     */
    protected function runReconciliation(int $sessionId): array
    {
        $matchedCount = 0;
        $unmatchedBankCount = 0;
        $unmatchedCashbookCount = 0;

        // Get all unreconciled bank transactions for this session
        $bankTransactions = BankTransaction::where('session_id', $sessionId)
            ->where('reconciliation_status', 'unreconciled')
            ->get();

        Log::info('Starting reconciliation matching', [
            'session_id' => $sessionId,
            'bank_transaction_count' => $bankTransactions->count()
        ]);

        foreach ($bankTransactions as $bankTxn) {
            $match = $this->findMatchingCashbookTransaction($bankTxn);

            if ($match) {
                // Create matching record
                $this->createMatchRecord($sessionId, $bankTxn, $match['transaction'], $match['confidence'], $match['match_type']);

                // Update bank transaction status
                $bankTxn->markAsMatched(
                    $match['transaction']->id,
                    $match['confidence'],
                    $match['notes'],
                    $match['match_type']
                );

                $matchedCount++;

                Log::info('Match found', [
                    'bank_txn_id' => $bankTxn->id,
                    'cashbook_txn_id' => $match['transaction']->id,
                    'confidence' => $match['confidence'],
                    'match_type' => $match['match_type']
                ]);
            } else {
                // Create non-matching record for bank transaction (only if not already exists)
                $exists = ReconNonMatchingTransaction::where('transaction_id', $bankTxn->id)
                    ->where('source', 'bank')
                    ->exists();

                if (!$exists) {
                    $this->createNonMatchRecord($sessionId, $bankTxn, 'bank');
                }
                $unmatchedBankCount++;

                Log::info('No match found for bank transaction', [
                    'bank_txn_id' => $bankTxn->id,
                    'reference' => $bankTxn->reference_number,
                    'amount' => $bankTxn->deposit_amount ?: $bankTxn->withdrawal_amount
                ]);
            }
        }

        // Find unmatched cashbook transactions
        $unmatchedCashbookCount = $this->findUnmatchedCashbookTransactions($sessionId);

        return [
            'matched_count' => $matchedCount,
            'unmatched_count' => $unmatchedBankCount + $unmatchedCashbookCount,
            'unmatched_bank_count' => $unmatchedBankCount,
            'unmatched_cashbook_count' => $unmatchedCashbookCount
        ];
    }

    /**
     * Find matching cashbook transaction for a bank transaction
     * Only searches within 35 days before the bank transaction date
     *
     * @param BankTransaction $bankTxn
     * @return array|null Match details or null if no match
     */
    protected function findMatchingCashbookTransaction(BankTransaction $bankTxn): ?array
    {
        $amount = $bankTxn->deposit_amount ?: $bankTxn->withdrawal_amount;
        $transactionDate = $bankTxn->transaction_date;

        // Define 35-day lookback period (for monthly statements)
        $lookbackStartDate = Carbon::parse($transactionDate)->subDays(35);
        $lookbackEndDate = Carbon::parse($transactionDate)->addDay(); // Allow 1 day forward

        // Tier 1: Exact match on reference, amount, and date (within 35-day window)
        $exactMatch = Transaction::where('status', 'COMPLETED')
            ->where('external_reference', $bankTxn->reference_number)
            ->where('amount', $amount)
            ->whereDate('initiated_at', $transactionDate)
            ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
            ->first();

        if ($exactMatch) {
            return [
                'transaction' => $exactMatch,
                'confidence' => 100.00,
                'match_type' => 'exact',
                'notes' => 'Exact match: reference, amount, and date'
            ];
        }

        // Tier 2: Match on amount and date (within +/- 1 day, but still within 35-day window)
        $dateMatch = Transaction::where('status', 'COMPLETED')
            ->where('amount', $amount)
            ->whereBetween('initiated_at', [
                Carbon::parse($transactionDate)->subDay(),
                Carbon::parse($transactionDate)->addDay()
            ])
            ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
            ->first();

        if ($dateMatch) {
            return [
                'transaction' => $dateMatch,
                'confidence' => 85.00,
                'match_type' => 'partial',
                'notes' => 'Partial match: amount and date (±1 day)'
            ];
        }

        // Tier 3: Match on reference only (within 35-day window)
        if ($bankTxn->reference_number) {
            $referenceMatch = Transaction::where('status', 'COMPLETED')
                ->where('external_reference', $bankTxn->reference_number)
                ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
                ->first();

            if ($referenceMatch) {
                return [
                    'transaction' => $referenceMatch,
                    'confidence' => 70.00,
                    'match_type' => 'partial',
                    'notes' => 'Partial match: reference only'
                ];
            }
        }

        return null;
    }

    /**
     * Create a matching transaction record
     * Also cleans up any existing non-matching records for these transactions
     *
     * @param int $sessionId
     * @param BankTransaction $bankTxn
     * @param Transaction $cashbookTxn
     * @param float $confidence
     * @param string $matchType
     * @return void
     */
    protected function createMatchRecord(int $sessionId, BankTransaction $bankTxn, Transaction $cashbookTxn, float $confidence, string $matchType): void
    {
        $bankAmount = $bankTxn->deposit_amount ?: $bankTxn->withdrawal_amount;
        $cashbookAmount = $cashbookTxn->amount;
        $variance = abs($bankAmount - $cashbookAmount);

        // Use initiated_at for cashbook date
        $cashbookDate = $cashbookTxn->initiated_at ?? $cashbookTxn->completed_at ?? $cashbookTxn->created_at;
        $dateVariance = Carbon::parse($bankTxn->transaction_date)->diffInDays(
            Carbon::parse($cashbookDate)
        );

        // Remove any existing non-matching records for these transactions
        // They were previously marked as non-matching but now we found matches
        $bankNonMatchDeleted = ReconNonMatchingTransaction::where('transaction_id', $bankTxn->id)
            ->where('source', 'bank')
            ->delete();

        $cashbookNonMatchDeleted = ReconNonMatchingTransaction::where('transaction_id', $cashbookTxn->id)
            ->where('source', 'cashbook')
            ->delete();

        if ($bankNonMatchDeleted > 0 || $cashbookNonMatchDeleted > 0) {
            Log::info('Non-matching records cleaned up for matched transactions', [
                'bank_transaction_id' => $bankTxn->id,
                'cashbook_transaction_id' => $cashbookTxn->id,
                'bank_non_match_deleted' => $bankNonMatchDeleted,
                'cashbook_non_match_deleted' => $cashbookNonMatchDeleted,
                'session_id' => $sessionId
            ]);
        }

        ReconMatchingTransaction::create([
            'session_id' => $sessionId,
            'bank_transaction_id' => $bankTxn->id,
            'cashbook_transaction_id' => $cashbookTxn->id,
            'match_type' => $matchType,
            'match_confidence' => $confidence,
            'match_criteria' => [
                'reference_match' => $bankTxn->reference_number === $cashbookTxn->external_reference,
                'amount_match' => $bankAmount == $cashbookAmount,
                'date_match' => $dateVariance == 0
            ],
            'bank_amount' => $bankAmount,
            'cashbook_amount' => $cashbookAmount,
            'variance' => $variance,
            'bank_date' => $bankTxn->transaction_date,
            'cashbook_date' => Carbon::parse($cashbookDate)->format('Y-m-d'),
            'date_variance_days' => $dateVariance,
            'status' => $variance > 0 || $confidence < 100 ? 'pending_approval' : 'approved',
            'matched_by' => 1, // System user
            'matched_at' => now(),
            'notes' => "Auto-matched by daily reconciliation job with {$confidence}% confidence"
        ]);

        // Log the match
        ReconAuditLog::logAction(
            $sessionId,
            'transaction_matched',
            'bank_transaction',
            $bankTxn->id,
            ['reconciliation_status' => 'unreconciled'],
            ['reconciliation_status' => 'matched'],
            "Matched with cashbook transaction #{$cashbookTxn->id} ({$matchType} match, {$confidence}% confidence)"
        );
    }

    /**
     * Create a non-matching transaction record
     *
     * @param int $sessionId
     * @param BankTransaction|Transaction $transaction
     * @param string $source 'bank' or 'cashbook'
     * @return void
     */
    protected function createNonMatchRecord(int $sessionId, $transaction, string $source): void
    {
        if ($source === 'bank') {
            $amount = $transaction->deposit_amount ?: $transaction->withdrawal_amount;
            $debitAmount = $transaction->withdrawal_amount;
            $creditAmount = $transaction->deposit_amount;
            $transactionDate = $transaction->transaction_date;
            $reference = $transaction->reference_number;
            $narration = $transaction->narration;
        } else {
            $amount = $transaction->amount;
            $debitAmount = $transaction->debit ?? 0;
            $creditAmount = $transaction->credit ?? 0;
            $transactionDate = $transaction->initiated_at ?? $transaction->completed_at ?? $transaction->created_at;
            $reference = $transaction->external_reference ?? $transaction->transaction_uuid;
            $narration = $transaction->narration ?? $transaction->description;
        }

        ReconNonMatchingTransaction::create([
            'session_id' => $sessionId,
            'source' => $source,
            'transaction_id' => $transaction->id,
            'transaction_date' => $transactionDate,
            'reference_number' => $reference,
            'narration' => $narration,
            'debit_amount' => $debitAmount,
            'credit_amount' => $creditAmount,
            'amount' => $amount,
            'non_match_reason' => $source === 'bank' ? 'not_in_cashbook' : 'not_in_bank',
            'reason_details' => "Transaction found in {$source} but no matching transaction found in " . ($source === 'bank' ? 'cashbook' : 'bank statement'),
            'resolution_status' => 'pending',
            'requires_action' => true,
            'action_type' => 'investigate'
        ]);

        // Log the non-match
        ReconAuditLog::logAction(
            $sessionId,
            'transaction_not_matched',
            $source === 'bank' ? 'bank_transaction' : 'cashbook_transaction',
            $transaction->id,
            null,
            null,
            "No matching transaction found for {$source} transaction"
        );
    }

    /**
     * Find unmatched cashbook transactions
     * Only looks back 35 days from the oldest bank transaction date
     *
     * @param int $sessionId
     * @return int Count of unmatched cashbook transactions
     */
    protected function findUnmatchedCashbookTransactions(int $sessionId): int
    {
        // Get date range from session's bank transactions
        $session = AnalysisSession::find($sessionId);
        $bankTransactions = BankTransaction::where('session_id', $sessionId)->get();

        if ($bankTransactions->isEmpty()) {
            return 0;
        }

        $minDate = $bankTransactions->min('transaction_date');
        $maxDate = $bankTransactions->max('transaction_date');

        // Apply 35-day lookback from the earliest bank transaction (for monthly statements)
        $lookbackStartDate = Carbon::parse($minDate)->subDays(35);
        $lookbackEndDate = Carbon::parse($maxDate)->addDay(); // Allow 1 day forward

        // Find cashbook transactions in the 35-day window that aren't matched
        $unmatchedCashbook = Transaction::where('status', 'COMPLETED')
            ->whereBetween('initiated_at', [$lookbackStartDate, $lookbackEndDate])
            ->whereNotIn('id', function($query) use ($sessionId) {
                $query->select('cashbook_transaction_id')
                      ->from('recon_matching_transactions')
                      ->where('session_id', $sessionId);
            })
            ->get();

        foreach ($unmatchedCashbook as $txn) {
            // Only create non-match record if it doesn't already exist
            // This prevents duplicates when running reconciliation multiple times
            $exists = ReconNonMatchingTransaction::where('transaction_id', $txn->id)
                ->where('source', 'cashbook')
                ->exists();

            if (!$exists) {
                $this->createNonMatchRecord($sessionId, $txn, 'cashbook');
            }
        }

        return $unmatchedCashbook->count();
    }

    /**
     * Update session with reconciliation results
     *
     * @param AnalysisSession $session
     * @param array $results
     * @return void
     */
    protected function updateSessionResults(AnalysisSession $session, array $results): void
    {
        $totalTransactions = BankTransaction::where('session_id', $session->id)->count();

        $session->update([
            'status' => 'completed',
            'total_transactions' => $totalTransactions,
            'matched_count' => $results['matched_count'],
            'unmatched_count' => $results['unmatched_count'],
            'reconciliation_summary' => [
                'total' => $totalTransactions,
                'matched' => $results['matched_count'],
                'unmatched_bank' => $results['unmatched_bank_count'],
                'unmatched_cashbook' => $results['unmatched_cashbook_count'],
                'reconciliation_rate' => $totalTransactions > 0
                    ? round(($results['matched_count'] / $totalTransactions) * 100, 2)
                    : 0
            ],
            'reconciled_by' => 1, // System user
            'reconciled_at' => now()
        ]);

        Log::info('Session updated with results', [
            'session_id' => $session->id,
            'total_transactions' => $totalTransactions,
            'matched_count' => $results['matched_count'],
            'unmatched_count' => $results['unmatched_count']
        ]);
    }
}
