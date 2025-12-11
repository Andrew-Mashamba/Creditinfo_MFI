<?php

namespace App\Jobs;

use App\Models\TransactionReversal;
use App\Services\TransactionReversalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class ProcessReversalRetry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // This job handles its own retries
    public $timeout = 120;
    public $maxExceptions = 3;

    protected $reversalId;
    protected $correlationId;

    /**
     * Create a new job instance.
     */
    public function __construct($reversalId)
    {
        $this->reversalId = $reversalId;
        $this->correlationId = \Illuminate\Support\Str::uuid()->toString();
        
        // Set queue name
        $this->onQueue('reversals');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Processing reversal retry', [
            'correlationId' => $this->correlationId,
            'reversalId' => $this->reversalId,
            'attempt' => $this->attempts()
        ]);

        try {
            $reversal = TransactionReversal::findOrFail($this->reversalId);

            // Check if reversal can be processed
            if (!$this->canProcessReversal($reversal)) {
                Log::info('Reversal cannot be processed, skipping', [
                    'correlationId' => $this->correlationId,
                    'reversalId' => $reversal->id,
                    'status' => $reversal->status,
                    'retryCount' => $reversal->retry_count
                ]);
                return;
            }

            // Check circuit breaker
            if ($this->isCircuitBreakerOpen($reversal)) {
                Log::warning('Circuit breaker is open, delaying reversal', [
                    'correlationId' => $this->correlationId,
                    'reversalId' => $reversal->id,
                    'externalSystem' => $reversal->transaction->external_system ?? 'unknown'
                ]);

                // Re-queue with delay
                $this->release(300); // 5 minutes delay
                return;
            }

            // Process the reversal
            $reversalService = new TransactionReversalService();
            $reversalService->processReversal($reversal->id);

            // Record success in circuit breaker
            $this->recordCircuitBreakerSuccess($reversal);

            Log::info('Reversal processed successfully', [
                'correlationId' => $this->correlationId,
                'reversalId' => $reversal->id
            ]);

        } catch (Exception $e) {
            Log::error('Reversal processing failed', [
                'correlationId' => $this->correlationId,
                'reversalId' => $this->reversalId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Record failure in circuit breaker
            if (isset($reversal)) {
                $this->recordCircuitBreakerFailure($reversal);
            }

            // Handle the failure
            $this->handleReversalFailure($e);
        }
    }

    /**
     * Check if reversal can be processed
     */
    protected function canProcessReversal($reversal)
    {
        // Check if reversal is in a processable state
        $processableStates = ['pending', 'failed'];
        if (!in_array($reversal->status, $processableStates)) {
            return false;
        }

        // Check if reversal has exceeded retry limit
        if ($reversal->retry_count >= 3) {
            return false;
        }

        // Check if it's time for retry
        if ($reversal->next_retry_at && $reversal->next_retry_at > now()) {
            return false;
        }

        return true;
    }

    /**
     * Check if circuit breaker is open
     */
    protected function isCircuitBreakerOpen($reversal)
    {
        $externalSystem = $reversal->transaction->external_system ?? 'unknown';
        $circuitBreakerKey = "circuit_breaker:reversal:{$externalSystem}";
        
        $circuitBreaker = Cache::get($circuitBreakerKey, [
            'state' => 'closed',
            'failure_count' => 0,
            'last_failure_time' => null,
            'open_until' => null
        ]);

        // If circuit breaker is open, check if it's time to try again
        if ($circuitBreaker['state'] === 'open') {
            if ($circuitBreaker['open_until'] && $circuitBreaker['open_until'] > now()) {
                return true; // Still open
            } else {
                // Try to close the circuit breaker
                $circuitBreaker['state'] = 'half_open';
                Cache::put($circuitBreakerKey, $circuitBreaker, 3600);
                return false;
            }
        }

        return false;
    }

    /**
     * Record circuit breaker success
     */
    protected function recordCircuitBreakerSuccess($reversal)
    {
        $externalSystem = $reversal->transaction->external_system ?? 'unknown';
        $circuitBreakerKey = "circuit_breaker:reversal:{$externalSystem}";
        
        $circuitBreaker = Cache::get($circuitBreakerKey, [
            'state' => 'closed',
            'failure_count' => 0,
            'last_failure_time' => null,
            'open_until' => null
        ]);

        if ($circuitBreaker['state'] === 'half_open') {
            // Reset circuit breaker on success
            $circuitBreaker['state'] = 'closed';
            $circuitBreaker['failure_count'] = 0;
            $circuitBreaker['last_failure_time'] = null;
            $circuitBreaker['open_until'] = null;
            
            Cache::put($circuitBreakerKey, $circuitBreaker, 3600);
        }
    }

    /**
     * Record circuit breaker failure
     */
    protected function recordCircuitBreakerFailure($reversal)
    {
        $externalSystem = $reversal->transaction->external_system ?? 'unknown';
        $circuitBreakerKey = "circuit_breaker:reversal:{$externalSystem}";
        
        $circuitBreaker = Cache::get($circuitBreakerKey, [
            'state' => 'closed',
            'failure_count' => 0,
            'last_failure_time' => null,
            'open_until' => null
        ]);

        $circuitBreaker['failure_count']++;
        $circuitBreaker['last_failure_time'] = now()->toIso8601String();

        // Open circuit breaker if failure threshold is reached
        if ($circuitBreaker['failure_count'] >= 5) {
            $circuitBreaker['state'] = 'open';
            $circuitBreaker['open_until'] = now()->addMinutes(30); // 30 minutes timeout
        }

        Cache::put($circuitBreakerKey, $circuitBreaker, 3600);
    }

    /**
     * Handle reversal failure
     */
    protected function handleReversalFailure($exception)
    {
        $reversal = TransactionReversal::find($this->reversalId);
        
        if (!$reversal) {
            Log::error('Reversal not found for failure handling', [
                'correlationId' => $this->correlationId,
                'reversalId' => $this->reversalId
            ]);
            return;
        }

        // Update reversal with failure details
        $reversal->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_code' => $exception->getCode() ?: 'REVERSAL_RETRY_FAILED',
            'error_message' => $exception->getMessage(),
            'retry_count' => ($reversal->retry_count ?? 0) + 1
        ]);

        // Log the retry attempt
        $reversal->logRetry(
            $reversal->retry_count,
            'failed',
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getMessage()
        );

        // Check if we should retry again
        if ($reversal->retry_count < 3) {
            $this->scheduleNextRetry($reversal);
        } else {
            // Move to dead letter queue
            $this->moveToDeadLetterQueue($reversal, $exception);
        }
    }

    /**
     * Schedule next retry
     */
    protected function scheduleNextRetry($reversal)
    {
        $delay = 5000 * pow(2, $reversal->retry_count - 1); // Exponential backoff
        $jitter = rand(0, 1000); // Add jitter
        $totalDelay = $delay + $jitter;

        $reversal->update([
            'next_retry_at' => now()->addMilliseconds($totalDelay)
        ]);

        // Re-queue the job
        self::dispatch($reversal->id)
            ->delay(now()->addMilliseconds($totalDelay))
            ->onQueue('reversals');

        Log::info('Reversal scheduled for retry', [
            'correlationId' => $this->correlationId,
            'reversalId' => $reversal->id,
            'retryCount' => $reversal->retry_count,
            'delayMs' => $totalDelay
        ]);
    }

    /**
     * Move reversal to dead letter queue
     */
    protected function moveToDeadLetterQueue($reversal, $exception)
    {
        $reversal->update([
            'status' => 'dead_letter',
            'metadata' => array_merge($reversal->metadata ?? [], [
                'moved_to_dlq_at' => now()->toIso8601String(),
                'final_error' => $exception->getMessage(),
                'max_retries_exceeded' => true
            ])
        ]);

        Log::error('Reversal moved to dead letter queue', [
            'correlationId' => $this->correlationId,
            'reversalId' => $reversal->id,
            'maxRetries' => 3,
            'finalError' => $exception->getMessage()
        ]);

        // Send admin alert for dead letter queue
        $this->sendDeadLetterAlert($reversal, $exception);
    }

    /**
     * Send dead letter queue alert
     */
    protected function sendDeadLetterAlert($reversal, $exception)
    {
        try {
            \App\Jobs\SendTransactionNotification::dispatch(
                $reversal->transaction_id,
                'reversal_dead_letter',
                [
                    'reversal_reference' => $reversal->reversal_reference,
                    'original_reference' => $reversal->transaction->reference ?? null,
                    'amount' => $reversal->transaction->amount ?? null,
                    'reason' => $reversal->reason,
                    'final_error' => $exception->getMessage(),
                    'retry_count' => $reversal->retry_count
                ]
            )->onQueue('notifications');
        } catch (Exception $e) {
            Log::warning('Failed to send dead letter alert', [
                'reversalId' => $reversal->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception)
    {
        Log::error('Reversal retry job failed permanently', [
            'correlationId' => $this->correlationId,
            'reversalId' => $this->reversalId,
            'error' => $exception->getMessage()
        ]);

        // Move reversal to dead letter queue
        $reversal = TransactionReversal::find($this->reversalId);
        if ($reversal) {
            $this->moveToDeadLetterQueue($reversal, $exception);
        }
    }
}
