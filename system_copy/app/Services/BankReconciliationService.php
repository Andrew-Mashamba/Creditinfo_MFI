<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BankReconciliationService
{
    /**
     * Reconcile bank transactions against system transactions
     */
    public function reconcileBankTransactions($sessionId)
    {
        $bankTransactions = BankTransaction::where('session_id', $sessionId)
            ->where('reconciliation_status', 'unreconciled')
            ->get();

        $reconciliationResults = [
            'total_processed' => 0,
            'matched' => 0,
            'partial_matches' => 0,
            'unmatched' => 0,
            'errors' => []
        ];

        foreach ($bankTransactions as $bankTransaction) {
            try {
                $result = $this->findMatchingTransaction($bankTransaction);
                
                if ($result['matched']) {
                    $bankTransaction->markAsMatched(
                        $result['transaction_id'],
                        $result['confidence'],
                        $result['notes']
                    );
                    $reconciliationResults['matched']++;
                } elseif ($result['partial']) {
                    $bankTransaction->update([
                        'reconciliation_status' => 'partial',
                        'match_confidence' => $result['confidence'],
                        'reconciliation_notes' => $result['notes']
                    ]);
                    $reconciliationResults['partial_matches']++;
                } else {
                    $reconciliationResults['unmatched']++;
                }
                
                $reconciliationResults['total_processed']++;
                
            } catch (\Exception $e) {
                Log::error('Reconciliation error for bank transaction ' . $bankTransaction->id, [
                    'error' => $e->getMessage(),
                    'bank_transaction' => $bankTransaction->toArray()
                ]);
                $reconciliationResults['errors'][] = $e->getMessage();
            }
        }

        return $reconciliationResults;
    }

    /**
     * Find matching transaction for a bank transaction
     */
    private function findMatchingTransaction(BankTransaction $bankTransaction)
    {
        $amount = $bankTransaction->amount;
        $date = $bankTransaction->transaction_date;
        $narration = $bankTransaction->narration;
        
        // Date range for matching (within 3 days)
        $startDate = $date->subDays(3);
        $endDate = $date->addDays(3);
        
        // First, try exact amount and date match
        $exactMatch = Transaction::where('amount', $amount)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereNull('matched_transaction_id')
            ->first();
            
        if ($exactMatch) {
            return [
                'matched' => true,
                'partial' => false,
                'transaction_id' => $exactMatch->id,
                'confidence' => 100,
                'notes' => 'Exact amount and date match'
            ];
        }
        
        // Try amount match with narration similarity
        $amountMatches = Transaction::where('amount', $amount)
            ->where('status', 'completed')
            ->whereNull('matched_transaction_id')
            ->get();
            
        foreach ($amountMatches as $transaction) {
            $similarity = $this->calculateNarrationSimilarity($narration, $transaction->narration);
            if ($similarity > 70) {
                return [
                    'matched' => true,
                    'partial' => false,
                    'transaction_id' => $transaction->id,
                    'confidence' => $similarity,
                    'notes' => "Amount match with {$similarity}% narration similarity"
                ];
            }
        }
        
        // Try partial amount matches (for fees, charges, etc.)
        $partialMatches = Transaction::where('amount', '<=', $amount)
            ->where('status', 'completed')
            ->whereNull('matched_transaction_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        foreach ($partialMatches as $transaction) {
            $similarity = $this->calculateNarrationSimilarity($narration, $transaction->narration);
            if ($similarity > 60) {
                return [
                    'matched' => false,
                    'partial' => true,
                    'transaction_id' => $transaction->id,
                    'confidence' => $similarity,
                    'notes' => "Partial amount match ({$transaction->amount} of {$amount}) with {$similarity}% narration similarity"
                ];
            }
        }
        
        return [
            'matched' => false,
            'partial' => false,
            'transaction_id' => null,
            'confidence' => 0,
            'notes' => 'No match found'
        ];
    }

    /**
     * Calculate similarity between two narrations
     */
    private function calculateNarrationSimilarity($narration1, $narration2)
    {
        if (empty($narration1) || empty($narration2)) {
            return 0;
        }
        
        // Convert to lowercase and remove special characters
        $narration1 = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($narration1));
        $narration2 = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($narration2));
        
        // Split into words
        $words1 = array_filter(explode(' ', $narration1));
        $words2 = array_filter(explode(' ', $narration2));
        
        if (empty($words1) || empty($words2)) {
            return 0;
        }
        
        // Calculate intersection
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));
        
        if (empty($union)) {
            return 0;
        }
        
        return (count($intersection) / count($union)) * 100;
    }

    /**
     * Get reconciliation summary for a session
     */
    public function getReconciliationSummary($sessionId)
    {
        $bankTransactions = BankTransaction::where('session_id', $sessionId);
        
        return [
            'total' => $bankTransactions->count(),
            'unreconciled' => $bankTransactions->where('reconciliation_status', 'unreconciled')->count(),
            'matched' => $bankTransactions->where('reconciliation_status', 'matched')->count(),
            'partial' => $bankTransactions->where('reconciliation_status', 'partial')->count(),
            'reconciled' => $bankTransactions->where('reconciliation_status', 'reconciled')->count(),
        ];
    }

    /**
     * Manual reconciliation
     */
    public function manualReconcile($bankTransactionId, $transactionId, $notes = null)
    {
        $bankTransaction = BankTransaction::findOrFail($bankTransactionId);
        $transaction = Transaction::findOrFail($transactionId);
        
        $bankTransaction->markAsMatched($transactionId, 100, $notes);
        
        return [
            'success' => true,
            'message' => 'Transaction manually reconciled'
        ];
    }
} 