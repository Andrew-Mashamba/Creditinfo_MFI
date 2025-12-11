<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionReversal;
use App\Jobs\ProcessReversalRetry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class TransactionReversalService
{
    protected $correlationId;
    protected $maxRetries = 3;
    protected $retryDelay = 5000; // 5 seconds

    public function __construct()
    {
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Initiate transaction reversal
     */
    public function initiateReversal($transactionId, $reason, $reversedBy = null, $isAutomatic = false)
    {
        Log::info('Initiating transaction reversal', [
            'correlationId' => $this->correlationId,
            'transactionId' => $transactionId,
            'reason' => $reason,
            'reversedBy' => $reversedBy,
            'isAutomatic' => $isAutomatic
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($transactionId);

            // Validate if reversal is possible
            $this->validateReversal($transaction);

            // Create reversal record
            $reversal = TransactionReversal::create([
                'transaction_id' => $transaction->id,
                'reversal_reference' => $this->generateReversalReference(),
                'reason' => $reason,
                'reversed_by' => $reversedBy,
                'is_automatic' => $isAutomatic,
                'status' => 'pending',
                'correlation_id' => $this->correlationId,
                'metadata' => [
                    'original_transaction' => [
                        'reference' => $transaction->reference,
                        'amount' => $transaction->amount,
                        'external_reference' => $transaction->external_reference,
                        'service_type' => $transaction->external_system,
                        'status' => $transaction->status
                    ],
                    'reversal_initiated_at' => now()->toIso8601String()
                ]
            ]);

            // Update original transaction
            $transaction->update([
                'status' => 'reversal_pending',
                'reversal_id' => $reversal->id,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'reversal_initiated_at' => now()->toIso8601String(),
                    'reversal_reason' => $reason,
                    'reversed_by' => $reversedBy
                ])
            ]);

            DB::commit();

            // Process reversal asynchronously
            ProcessReversalRetry::dispatch($reversal->id)->onQueue('reversals');

            Log::info('Transaction reversal initiated successfully', [
                'correlationId' => $this->correlationId,
                'reversalId' => $reversal->id,
                'reversalReference' => $reversal->reversal_reference
            ]);

            return [
                'success' => true,
                'reversal_id' => $reversal->id,
                'reversal_reference' => $reversal->reversal_reference,
                'status' => 'pending'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to initiate transaction reversal', [
                'correlationId' => $this->correlationId,
                'transactionId' => $transactionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process reversal with external service
     */
    public function processReversal($reversalId)
    {
        Log::info('Processing transaction reversal', [
            'correlationId' => $this->correlationId,
            'reversalId' => $reversalId
        ]);

        $reversal = TransactionReversal::with('transaction')->findOrFail($reversalId);
        $transaction = $reversal->transaction;

        try {
            // Update reversal status
            $reversal->update([
                'status' => 'processing',
                'processed_at' => now()
            ]);

            // Call external service for reversal
            $externalResult = $this->callExternalReversalService($transaction, $reversal);

            // Update reversal with external response
            $reversal->update([
                'external_reference' => $externalResult['external_reference'] ?? null,
                'external_transaction_id' => $externalResult['external_transaction_id'] ?? null,
                'external_request_payload' => $externalResult['request_payload'] ?? null,
                'external_response_payload' => $externalResult['response_payload'] ?? null,
                'external_status_code' => $externalResult['status_code'] ?? null,
                'external_status_message' => $externalResult['status_message'] ?? null
            ]);

            if ($externalResult['success']) {
                // External reversal successful
                $reversal->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);

                // Update original transaction
                $transaction->update([
                    'status' => 'reversed',
                    'reversed_at' => now(),
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'reversal_completed_at' => now()->toIso8601String(),
                        'reversal_external_reference' => $externalResult['external_reference'] ?? null
                    ])
                ]);

                // Send reversal success notification
                $this->sendReversalNotification($reversal, 'success');

                Log::info('Transaction reversal completed successfully', [
                    'correlationId' => $this->correlationId,
                    'reversalId' => $reversal->id,
                    'externalReference' => $externalResult['external_reference'] ?? null
                ]);

            } else {
                // External reversal failed
                $reversal->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_code' => $externalResult['error_code'] ?? 'EXTERNAL_REVERSAL_FAILED',
                    'error_message' => $externalResult['error_message'] ?? 'External reversal failed'
                ]);

                // Update original transaction
                $transaction->update([
                    'status' => 'reversal_failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'reversal_failed_at' => now()->toIso8601String(),
                        'reversal_error' => $externalResult['error_message'] ?? 'External reversal failed'
                    ])
                ]);

                // Send reversal failure notification
                $this->sendReversalNotification($reversal, 'failure');

                throw new Exception('External reversal failed: ' . ($externalResult['error_message'] ?? 'Unknown error'));
            }

        } catch (Exception $e) {
            // Update reversal status
            $reversal->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_code' => $e->getCode() ?: 'REVERSAL_PROCESSING_ERROR',
                'error_message' => $e->getMessage()
            ]);

            // Update original transaction
            $transaction->update([
                'status' => 'reversal_failed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'reversal_failed_at' => now()->toIso8601String(),
                    'reversal_error' => $e->getMessage()
                ])
            ]);

            // Queue for retry if appropriate
            $this->queueReversalForRetry($reversal, $e);

            // Send reversal failure notification
            $this->sendReversalNotification($reversal, 'failure');

            throw $e;
        }
    }

    /**
     * Call external reversal service
     */
    protected function callExternalReversalService($transaction, $reversal)
    {
        $startTime = microtime(true);

        try {
            switch ($transaction->external_system) {
                case 'tips_mno':
                    return $this->callTipsMnoReversalService($transaction, $reversal);
                
                case 'tips_bank':
                    return $this->callTipsBankReversalService($transaction, $reversal);
                
                case 'internal_transfer':
                    return $this->callInternalTransferReversalService($transaction, $reversal);
                
                default:
                    throw new Exception("Unsupported external system for reversal: {$transaction->external_system}");
            }
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new Exception("Network connectivity failed: " . $e->getMessage(), 'NETWORK_ERROR');
        } catch (\GuzzleHttp\Exception\TimeoutException $e) {
            throw new Exception("Request timeout", 'TIMEOUT_ERROR');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            throw new Exception("Server error {$statusCode}: " . $e->getMessage(), "SERVER_ERROR_{$statusCode}");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            throw new Exception("Client error {$statusCode}: " . $e->getMessage(), "CLIENT_ERROR_{$statusCode}");
        } catch (Exception $e) {
            throw $e;
        } finally {
            $processingTime = round((microtime(true) - $startTime) * 1000);
            Log::info('External reversal service call completed', [
                'correlationId' => $this->correlationId,
                'processingTimeMs' => $processingTime,
                'externalSystem' => $transaction->external_system
            ]);
        }
    }

    /**
     * Call TIPS MNO reversal service
     */
    protected function callTipsMnoReversalService($transaction, $reversal)
    {
        Log::info('Calling TIPS MNO reversal service', [
            'correlationId' => $this->correlationId,
            'originalReference' => $transaction->external_reference,
            'reversalReference' => $reversal->reversal_reference
        ]);

        // Use existing NBC payment service for reversal
        $nbcService = new NbcPaymentService();
        
        $requestPayload = [
            'originalTransactionId' => $transaction->external_transaction_id,
            'reversalReference' => $reversal->reversal_reference,
            'amount' => $transaction->amount,
            'reason' => $reversal->reason,
            'phoneNumber' => $transaction->metadata['phone_number'] ?? null,
            'narration' => "Reversal: {$reversal->reason}"
        ];

        $response = $nbcService->reverseTransaction($requestPayload);

        return [
            'success' => $response['success'] ?? false,
            'external_reference' => $response['reversal_reference'] ?? null,
            'external_transaction_id' => $response['reversal_transaction_id'] ?? null,
            'request_payload' => $requestPayload,
            'response_payload' => $response,
            'status_code' => $response['status_code'] ?? null,
            'status_message' => $response['status_message'] ?? null,
            'error_code' => $response['error_code'] ?? null,
            'error_message' => $response['error_message'] ?? null
        ];
    }

    /**
     * Call TIPS Bank reversal service
     */
    protected function callTipsBankReversalService($transaction, $reversal)
    {
        Log::info('Calling TIPS Bank reversal service', [
            'correlationId' => $this->correlationId,
            'originalReference' => $transaction->external_reference,
            'originalReference' => $transaction->external_reference,
            'reversalReference' => $reversal->reversal_reference
        ]);

        // Use existing NBC payment service for bank reversal
        $nbcService = new NbcPaymentService();
        
        $requestPayload = [
            'originalTransactionId' => $transaction->external_transaction_id,
            'reversalReference' => $reversal->reversal_reference,
            'amount' => $transaction->amount,
            'reason' => $reversal->reason,
            'accountNumber' => $transaction->metadata['account_number'] ?? null,
            'bankCode' => $transaction->metadata['bank_code'] ?? null,
            'narration' => "Reversal: {$reversal->reason}"
        ];

        $response = $nbcService->reverseBankTransaction($requestPayload);

        return [
            'success' => $response['success'] ?? false,
            'external_reference' => $response['reversal_reference'] ?? null,
            'external_transaction_id' => $response['reversal_transaction_id'] ?? null,
            'request_payload' => $requestPayload,
            'response_payload' => $response,
            'status_code' => $response['status_code'] ?? null,
            'status_message' => $response['status_message'] ?? null,
            'error_code' => $response['error_code'] ?? null,
            'error_message' => $response['error_message'] ?? null
        ];
    }

    /**
     * Call Internal Transfer reversal service
     */
    protected function callInternalTransferReversalService($transaction, $reversal)
    {
        Log::info('Calling Internal Transfer reversal service', [
            'correlationId' => $this->correlationId,
            'originalReference' => $transaction->external_reference,
            'reversalReference' => $reversal->reversal_reference
        ]);

        // Use existing internal transfer service for reversal
        $internalService = new InternalFundTransferService();
        
        $requestPayload = [
            'originalTransactionId' => $transaction->external_transaction_id,
            'reversalReference' => $reversal->reversal_reference,
            'amount' => $transaction->amount,
            'reason' => $reversal->reason,
            'sourceAccount' => $transaction->metadata['destination_account'] ?? null, // Reverse the accounts
            'destinationAccount' => $transaction->metadata['source_account'] ?? null,
            'narration' => "Reversal: {$reversal->reason}"
        ];

        $response = $internalService->reverseTransfer($requestPayload);

        return [
            'success' => $response['success'] ?? false,
            'external_reference' => $response['reversal_reference'] ?? null,
            'external_transaction_id' => $response['reversal_transaction_id'] ?? null,
            'request_payload' => $requestPayload,
            'response_payload' => $response,
            'status_code' => $response['status_code'] ?? null,
            'status_message' => $response['status_message'] ?? null,
            'error_code' => $response['error_code'] ?? null,
            'error_message' => $response['error_message'] ?? null
        ];
    }

    /**
     * Validate if reversal is possible
     */
    protected function validateReversal($transaction)
    {
        // Check if transaction exists and is in a reversible state
        if (!$transaction) {
            throw new Exception('Transaction not found');
        }

        // Check if transaction is already reversed
        if ($transaction->status === 'reversed') {
            throw new Exception('Transaction is already reversed');
        }

        // Check if reversal is already pending
        if ($transaction->status === 'reversal_pending') {
            throw new Exception('Transaction reversal is already pending');
        }

        // Check if transaction is in a reversible state
        $reversibleStates = ['completed', 'failed', 'suspect'];
        if (!in_array($transaction->status, $reversibleStates)) {
            throw new Exception("Transaction status '{$transaction->status}' is not reversible");
        }

        // Check if transaction has external reference (for external reversals)
        if ($transaction->external_system !== 'internal_transfer' && !$transaction->external_reference) {
            throw new Exception('Transaction has no external reference for reversal');
        }

        // Check time limit for reversals (24 hours)
        $timeLimit = now()->subHours(24);
        if ($transaction->created_at < $timeLimit) {
            throw new Exception('Transaction is older than 24 hours and cannot be reversed automatically');
        }
    }

    /**
     * Generate reversal reference number
     */
    protected function generateReversalReference()
    {
        return 'REV' . date('Ymd') . strtoupper(Str::random(8));
    }

    /**
     * Queue reversal for retry
     */
    protected function queueReversalForRetry($reversal, $exception)
    {
        $retryCount = $reversal->retry_count ?? 0;
        
        if ($retryCount < $this->maxRetries) {
            $delay = $this->retryDelay * pow(2, $retryCount); // Exponential backoff
            
            $reversal->update([
                'retry_count' => $retryCount + 1,
                'next_retry_at' => now()->addMilliseconds($delay)
            ]);

            ProcessReversalRetry::dispatch($reversal->id)
                ->delay(now()->addMilliseconds($delay))
                ->onQueue('reversals');

            Log::info('Reversal queued for retry', [
                'correlationId' => $this->correlationId,
                'reversalId' => $reversal->id,
                'retryCount' => $retryCount + 1,
                'delayMs' => $delay
            ]);
        } else {
            // Move to dead letter queue
            $reversal->update([
                'status' => 'dead_letter',
                'metadata' => array_merge($reversal->metadata ?? [], [
                    'moved_to_dlq_at' => now()->toIso8601String(),
                    'final_error' => $exception->getMessage()
                ])
            ]);

            Log::error('Reversal moved to dead letter queue', [
                'correlationId' => $this->correlationId,
                'reversalId' => $reversal->id,
                'maxRetries' => $this->maxRetries
            ]);
        }
    }

    /**
     * Send reversal notification
     */
    protected function sendReversalNotification($reversal, $type)
    {
        try {
            \App\Jobs\SendTransactionNotification::dispatch(
                $reversal->transaction_id,
                "reversal_{$type}",
                [
                    'reversal_reference' => $reversal->reversal_reference,
                    'original_reference' => $reversal->transaction->reference,
                    'amount' => $reversal->transaction->amount,
                    'reason' => $reversal->reason,
                    'external_reference' => $reversal->external_reference ?? null
                ]
            )->onQueue('notifications');
        } catch (Exception $e) {
            Log::warning('Failed to queue reversal notification', [
                'reversalId' => $reversal->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get reversal status
     */
    public function getReversalStatus($reversalId)
    {
        $reversal = TransactionReversal::with('transaction')->findOrFail($reversalId);
        
        return [
            'reversal_id' => $reversal->id,
            'reversal_reference' => $reversal->reversal_reference,
            'status' => $reversal->status,
            'reason' => $reversal->reason,
            'is_automatic' => $reversal->is_automatic,
            'reversed_by' => $reversal->reversed_by,
            'created_at' => $reversal->created_at,
            'completed_at' => $reversal->completed_at,
            'failed_at' => $reversal->failed_at,
            'error_message' => $reversal->error_message,
            'external_reference' => $reversal->external_reference,
            'original_transaction' => [
                'id' => $reversal->transaction->id,
                'reference' => $reversal->transaction->reference,
                'amount' => $reversal->transaction->amount,
                'status' => $reversal->transaction->status
            ]
        ];
    }

    /**
     * Get reversals for reconciliation
     */
    public function getReversalsForReconciliation($date = null)
    {
        $query = TransactionReversal::with('transaction')
            ->where('status', 'completed')
            ->whereNotNull('external_reference');

        if ($date) {
            $query->whereDate('completed_at', $date);
        } else {
            $query->whereDate('completed_at', today());
        }

        return $query->get()->map(function ($reversal) {
            return [
                'reversal_id' => $reversal->id,
                'reversal_reference' => $reversal->reversal_reference,
                'external_reference' => $reversal->external_reference,
                'amount' => $reversal->transaction->amount,
                'completed_at' => $reversal->completed_at,
                'original_transaction_reference' => $reversal->transaction->reference,
                'original_external_reference' => $reversal->transaction->external_reference
            ];
        });
    }
}
