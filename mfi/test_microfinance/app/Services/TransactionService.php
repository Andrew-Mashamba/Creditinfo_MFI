<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function createTransaction($data)
    {
        try {
            DB::beginTransaction();

            $account = Account::findOrFail($data['account_id']);
            
            // Calculate balance after transaction
            $balanceAfter = $this->calculateBalanceAfter($account, $data['amount'], $data['type']);
            
            // Create transaction record
            $transaction = new Transaction([
                'account_id' => $data['account_id'],
                'amount' => $data['amount'],
                'type' => $data['type'],
                'narration' => $data['narration'],
                'reference' => $data['reference'],
                'status' => $data['status'] ?? 'pending',
                'balance_before' => $account->balance,
                'balance_after' => $balanceAfter
            ]);

            $transaction->save();

            // Update account balance
            $account->balance = $balanceAfter;
            $account->save();

            DB::commit();
            Log::info("Transaction created successfully: {$transaction->id}");
            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Transaction creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function calculateBalanceAfter($account, $amount, $type)
    {
        if ($type === 'credit') {
            return $account->balance + $amount;
        } else {
            return $account->balance - $amount;
        }
    }

    public function updateTransactionStatus($transactionId, $status)
    {
        try {
            $transaction = Transaction::findOrFail($transactionId);
            $transaction->status = $status;
            $transaction->save();

            Log::info("Transaction {$transactionId} status updated to {$status}");
            return $transaction;

        } catch (\Exception $e) {
            Log::error("Failed to update transaction status: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAccountTransactions($accountId, $startDate = null, $endDate = null)
    {
        $query = Transaction::where('account_id', $accountId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getDailyTransactions($date)
    {
        return Transaction::whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingTransactions()
    {
        return Transaction::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function reverseTransaction($transactionId, $reason)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($transactionId);
            
            // Create reversal transaction
            $reversal = new Transaction([
                'account_id' => $transaction->account_id,
                'amount' => $transaction->amount,
                'type' => $transaction->type === 'credit' ? 'debit' : 'credit',
                'narration' => "Reversal: {$transaction->narration} - {$reason}",
                'reference' => "REV-{$transaction->reference}",
                'status' => 'completed',
                'balance_before' => $transaction->balance_after,
                'balance_after' => $transaction->balance_before
            ]);

            $reversal->save();

            // Update original transaction
            $transaction->status = 'reversed';
            $transaction->save();

            // Update account balance
            $account = Account::find($transaction->account_id);
            $account->balance = $reversal->balance_after;
            $account->save();

            DB::commit();
            Log::info("Transaction {$transactionId} reversed successfully");
            return $reversal;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Transaction reversal failed: " . $e->getMessage());
            throw $e;
        }
    }
} 