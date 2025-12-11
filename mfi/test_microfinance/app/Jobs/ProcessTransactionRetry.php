<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\TransactionProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessTransactionRetry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $maxExceptions = 3;

    protected $transactionId;
    protected $correlationId;

    /**
     * Create a new job instance.
     */
    public function __construct($transactionId, $correlationId = null)
    {
        $this->transactionId = $transactionId;
        $this->correlationId = $correlationId;
        
        // Set queue name
        $this->onQueue('transaction-retries');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Processing transaction retry job', [
            'transactionId' => $this->transactionId,
            'correlationId' => $this->correlationId,
            'attempt' => $this->attempts()
        ]);

        try {
            // Get the transaction
            $transaction = Transaction::findOrFail($this->transactionId);

            // Check if transaction is still in retry state
            if ($transaction->status !== 'retry_pending') {
                Log::info('Transaction no longer in retry state, skipping', [
                    'transactionId' => $this->transactionId,
                    'status' => $transaction->status
                ]);
                return;
            }

            // Check if we should retry based on circuit breaker
            if ($this->isCircuitBreakerOpen($transaction)) {
                Log::warning('Circuit breaker is open, moving to DLQ', [
                    'transactionId' => $this->transactionId,
                    'serviceType' => $transaction->external_system
                ]);
                
                $this->moveToDeadLetterQueue($transaction, new Exception('Circuit breaker open'));
                return;
            }

            // Process the retry
            $this->processRetry($transaction);

        } catch (Exception $e) {
            Log::error('Transaction retry job failed', [
                'transactionId' => $this->transactionId,
                'correlationId' => $this->correlationId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // If this is the last attempt, move to DLQ
            if ($this->attempts() >= $this->tries) {
                $transaction = Transaction::find($this->transactionId);
                if ($transaction) {
                    $this->moveToDeadLetterQueue($transaction, $e);
                }
            }

            throw $e;
        }
    }

    /**
     * Process the retry attempt
     */
    protected function processRetry($transaction)
    {
        Log::info('Processing retry attempt', [
            'transactionId' => $transaction->id,
            'correlationId' => $transaction->correlation_id,
            'retryCount' => $transaction->retry_count,
            'maxRetries' => $transaction->max_retries
        ]);

        // Update transaction status
        $transaction->update([
            'status' => 'processing',
            'processed_at' => now()
        ]);

        try {
            // Reconstruct the transaction processing service
            $service = $this->reconstructTransactionService($transaction);

            // Call the external service again
            $externalResult = $service->callExternalService($transaction->retry_count + 1);

            if ($externalResult['success']) {
                // Success - update transaction
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'external_reference' => $externalResult['external_reference'] ?? null,
                    'external_transaction_id' => $externalResult['external_transaction_id'] ?? null,
                    'external_status_code' => $externalResult['status_code'] ?? null,
                    'external_status_message' => $externalResult['status_message'] ?? null
                ]);

                // Post to ledger
                $service->postToLedger($transaction);

                Log::info('Transaction retry successful', [
                    'transactionId' => $transaction->id,
                    'externalReference' => $externalResult['external_reference'] ?? null
                ]);

            } else {
                // Still failed - schedule next retry or move to DLQ
                if ($transaction->retry_count < $transaction->max_retries) {
                    $this->scheduleNextRetry($transaction);
                } else {
                    $this->moveToDeadLetterQueue($transaction, new Exception($externalResult['error_message'] ?? 'External service failed'));
                }
            }

        } catch (Exception $e) {
            // Update transaction with error
            $transaction->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_code' => $e->getCode() ?: 'RETRY_FAILED',
                'error_message' => $e->getMessage()
            ]);

            // Schedule next retry or move to DLQ
            if ($transaction->retry_count < $transaction->max_retries) {
                $this->scheduleNextRetry($transaction);
            } else {
                $this->moveToDeadLetterQueue($transaction, $e);
            }

            throw $e;
        }
    }

    /**
     * Reconstruct the transaction processing service
     */
    protected function reconstructTransactionService($transaction)
    {
        // Extract data from transaction metadata
        $metadata = $transaction->metadata ?? [];
        
        return new TransactionProcessingService(
            $transaction->external_system,
            $transaction->transaction_category,
            $transaction->amount,
            $metadata['source_account'] ?? '',
            $metadata['destination_account'] ?? '',
            $metadata['member_id'] ?? '',
            $metadata
        );
    }

    /**
     * Check if circuit breaker is open
     */
    protected function isCircuitBreakerOpen($transaction)
    {
        // Simple circuit breaker implementation
        // Count recent failures for this service type
        $recentFailures = Transaction::where('external_system', $transaction->external_system)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        // If more than 5 failures in 5 minutes, open circuit breaker
        return $recentFailures >= 5;
    }

    /**
     * Schedule next retry with exponential backoff
     */
    protected function scheduleNextRetry($transaction)
    {
        $retryCount = $transaction->retry_count + 1;
        $delay = pow(2, $retryCount) * 60; // Exponential backoff in seconds
        
        // Add jitter
        $jitter = rand(0, 30);
        $totalDelay = $delay + $jitter;

        Log::info('Scheduling next retry', [
            'transactionId' => $transaction->id,
            'retryCount' => $retryCount,
            'delaySeconds' => $totalDelay
        ]);

        // Update transaction
        $transaction->update([
            'status' => 'retry_pending',
            'retry_count' => $retryCount,
            'next_retry_at' => now()->addSeconds($totalDelay)
        ]);

        // Dispatch next retry job
        self::dispatch($transaction->id, $transaction->correlation_id)
            ->delay(now()->addSeconds($totalDelay));
    }

    /**
     * Move transaction to Dead Letter Queue
     */
    protected function moveToDeadLetterQueue($transaction, $exception)
    {
        Log::warning('Moving transaction to Dead Letter Queue', [
            'transactionId' => $transaction->id,
            'correlationId' => $transaction->correlation_id,
            'error' => $exception->getMessage()
        ]);

        // Update transaction status
        $transaction->update([
            'status' => 'suspended',
            'error_code' => 'DLQ',
            'error_message' => 'Moved to Dead Letter Queue: ' . $exception->getMessage(),
            'error_context' => [
                'moved_to_dlq_at' => now()->toIso8601String(),
                'final_error' => $exception->getMessage(),
                'retry_count' => $transaction->retry_count
            ]
        ]);

        // TODO: Send notification to administrators
        // TODO: Create DLQ record in dedicated table
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception)
    {
        Log::error('Transaction retry job failed permanently', [
            'transactionId' => $this->transactionId,
            'correlationId' => $this->correlationId,
            'error' => $exception->getMessage()
        ]);

        // Move to DLQ
        $transaction = Transaction::find($this->transactionId);
        if ($transaction) {
            $this->moveToDeadLetterQueue($transaction, $exception);
        }
    }
} 