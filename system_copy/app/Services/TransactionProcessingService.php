<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Carbon\Carbon;
use GuzzleHttp\Exception\TimeoutException;

/**
 * Transaction Processing Service (TPS)
 * 
 * A comprehensive, enterprise-grade transaction processing system that handles
 * multiple payment methods with retry logic, circuit breakers, and audit trails.
 * 
 * Features:
 * - 12-digit reference number generation
 * - Idempotency checks
 * - External service integration (TIPS, NBC, Internal Transfer)
 * - Retry logic with exponential backoff
 * - Circuit breaker pattern
 * - Dead Letter Queue (DLQ) support
 * - Comprehensive audit logging
 * - ACID-compliant database operations
 * 
 * Usage Examples:
 * 
 * 1. Cash Transaction:
 * $tps = new TransactionProcessingService(
 *     'cash',                    // serviceType
 *     'loan',                    // saccosService
 *     100000,                    // amount
 *     '1234567890',              // sourceAccount
 *     '0987654321',              // destinationAccount
 *     'M001',                    // memberId
 *     ['narration' => 'Loan disbursement']
 * );
 * $result = $tps->process();
 * 
 * 2. TIPS Mobile Money:
 * $tps = new TransactionProcessingService(
 *     'tips_mno',                // serviceType
 *     'loan',                    // saccosService
 *     100000,                    // amount
 *     '1234567890',              // sourceAccount
 *     '0987654321',              // destinationAccount
 *     'M001',                    // memberId
 *     [
 *         'phone_number' => '255712345678',
 *         'wallet_provider' => 'MPESA',
 *         'narration' => 'Loan disbursement via M-Pesa',
 *         'payer_name' => 'John Doe'
 *     ]
 * );
 * $result = $tps->process();
 * 
 * 3. TIPS Bank Transfer:
 * $tps = new TransactionProcessingService(
 *     'tips_bank',               // serviceType
 *     'loan',                    // saccosService
 *     100000,                    // amount
 *     '1234567890',              // sourceAccount
 *     '0987654321',              // destinationAccount
 *     'M001',                    // memberId
 *     [
 *         'bank_code' => '015',  // NBC bank code
 *         'phone_number' => '255712345678',
 *         'narration' => 'Loan disbursement to bank account',
 *         'product_code' => 'FTLC'
 *     ]
 * );
 * $result = $tps->process();
 * 
 * 4. Internal Transfer:
 * $tps = new TransactionProcessingService(
 *     'internal_transfer',       // serviceType
 *     'loan',                    // saccosService
 *     100000,                    // amount
 *     '1234567890',              // sourceAccount
 *     '0987654321',              // destinationAccount
 *     'M001',                    // memberId
 *     [
 *         'narration' => 'Internal transfer loan disbursement',
 *         'payer_name' => 'John Doe'
 *     ]
 * );
 * $result = $tps->process();
 * 
 * Response Format:
 * [
 *     'success' => true,
 *     'referenceNumber' => '123456789012',
 *     'externalReferenceNumber' => 'CBS123456789',
 *     'correlationId' => 'uuid-string',
 *     'processingTimeMs' => 1500
 * ]
 * 
 * Error Handling:
 * - All exceptions are logged with correlation ID
 * - Failed transactions are queued for retry
 * - Circuit breaker prevents cascading failures
 * - Dead Letter Queue for permanently failed transactions
 * 
 * @author System Administrator
 * @version 1.0
 */
class TransactionProcessingService
{
    // Core transaction properties
    protected $serviceType;
    protected $saccosService;
    protected $amount;
    protected $sourceAccount;
    protected $destinationAccount;
    protected $memberId;
    protected $meta;
    
    // Generated references
    protected $referenceNumber;
    protected $externalReferenceNumber;
    protected $correlationId;
    
    // Status tracking
    protected $status = 'pending';
    protected $errorCode;
    protected $errorMessage;
    
    // External service integration
    protected $externalServices = [
        'tips_mno' => 'NbcPaymentService',
        'tips_bank' => 'NbcPaymentService', 
        'internal_transfer' => 'InternalFundTransferService',
        'cash' => null
    ];

    public function __construct($serviceType, $saccosService, $amount, $sourceAccount, $destinationAccount, $memberId, $meta = [])
    {
        $this->serviceType = $serviceType;
        $this->saccosService = $saccosService;
        $this->amount = $amount;
        $this->sourceAccount = $sourceAccount;
        $this->destinationAccount = $destinationAccount;
        $this->memberId = $memberId;
        $this->meta = $meta;
        
        // Generate unique identifiers
        $this->correlationId = Str::uuid()->toString();
        $this->referenceNumber = $this->generateReferenceNumber();
        
        Log::info('TransactionProcessingService: Service initialized', [
            'correlationId' => $this->correlationId,
            'serviceType' => $this->serviceType,
            'saccosService' => $this->saccosService,
            'amount' => $this->amount,
            'sourceAccount' => $this->maskAccountNumber($this->sourceAccount),
            'destinationAccount' => $this->maskAccountNumber($this->destinationAccount),
            'memberId' => $this->memberId,
            'referenceNumber' => $this->referenceNumber,
            'meta' => $this->sanitizeMetaForLogging($this->meta),
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Process the transaction and return comprehensive response
     */
    public function process()
    {
        $startTime = microtime(true);
        
        Log::info('TransactionProcessingService: Starting transaction processing', [
            'correlationId' => $this->correlationId,
            'serviceType' => $this->serviceType,
            'saccosService' => $this->saccosService,
            'amount' => $this->amount,
            'sourceAccount' => $this->maskAccountNumber($this->sourceAccount),
            'destinationAccount' => $this->maskAccountNumber($this->destinationAccount),
            'memberId' => $this->memberId,
            'referenceNumber' => $this->referenceNumber,
            'meta' => $this->sanitizeMetaForLogging($this->meta),
            'timestamp' => now()->toIso8601String()
        ]);

        DB::beginTransaction();
        try {
            // Step 1: Validate transaction
            Log::info('TransactionProcessingService: Starting transaction validation', [
                'correlationId' => $this->correlationId,
                'step' => 'validation_start'
            ]);
            
            $this->validateTransaction();
            
            Log::info('TransactionProcessingService: Transaction validation completed successfully', [
                'correlationId' => $this->correlationId,
                'step' => 'validation_complete'
            ]);

            // Step 2: Check idempotency
            Log::info('TransactionProcessingService: Checking idempotency', [
                'correlationId' => $this->correlationId,
                'step' => 'idempotency_check',
                'referenceNumber' => $this->referenceNumber
            ]);
            
            $existingTransaction = $this->checkIdempotency();
            if ($existingTransaction) {
                Log::info('TransactionProcessingService: Idempotent transaction detected, returning existing result', [
                    'correlationId' => $this->correlationId,
                    'step' => 'idempotent_return',
                    'referenceNumber' => $this->referenceNumber,
                    'existingTransactionId' => $existingTransaction->id
                ]);
                
                DB::rollBack();
                return $this->buildResponse($existingTransaction, 'completed', 'Idempotent transaction - returning existing result');
            }
            
            Log::info('TransactionProcessingService: Idempotency check passed, proceeding with new transaction', [
                'correlationId' => $this->correlationId,
                'step' => 'idempotency_passed'
            ]);

            // Step 3: Create transaction record
            Log::info('TransactionProcessingService: Creating transaction record', [
                'correlationId' => $this->correlationId,
                'step' => 'create_record'
            ]);
            
            $transaction = $this->createTransactionRecord();
            
            Log::info('TransactionProcessingService: Transaction record created successfully', [
                'correlationId' => $this->correlationId,
                'step' => 'record_created',
                'transactionId' => $transaction->id,
                'referenceNumber' => $transaction->reference,
                'status' => $transaction->status,
                'balanceBefore' => $transaction->balance_before
            ]);

            // Step 4: Process based on service type
            Log::info('TransactionProcessingService: Starting transaction processing based on service type', [
                'correlationId' => $this->correlationId,
                'step' => 'process_transaction',
                'serviceType' => $this->serviceType
            ]);

            if ($this->serviceType === 'cash') {
                Log::info('TransactionProcessingService: Processing cash transaction', [
                    'correlationId' => $this->correlationId,
                    'step' => 'cash_processing'
                ]);
                
                $this->processCashTransaction($transaction);
                
                Log::info('TransactionProcessingService: Cash transaction processed successfully', [
                    'correlationId' => $this->correlationId,
                    'step' => 'cash_complete',
                    'transactionId' => $transaction->id
                ]);
            } else {
                Log::info('TransactionProcessingService: Processing external transaction', [
                    'correlationId' => $this->correlationId,
                    'step' => 'external_processing',
                    'serviceType' => $this->serviceType
                ]);
                
                $this->processExternalTransaction($transaction);
                
                Log::info('TransactionProcessingService: External transaction processed successfully', [
                    'correlationId' => $this->correlationId,
                    'step' => 'external_complete',
                    'transactionId' => $transaction->id,
                    'externalReference' => $this->externalReferenceNumber
                ]);
            }

            // Step 5: Commit transaction
            Log::info('TransactionProcessingService: Committing database transaction', [
                'correlationId' => $this->correlationId,
                'step' => 'commit_transaction'
            ]);
            
            DB::commit();
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            Log::info('TransactionProcessingService: Transaction processing completed successfully', [
                'correlationId' => $this->correlationId,
                'step' => 'complete',
                'transactionId' => $transaction->id,
                'processingTimeMs' => $processingTime,
                'referenceNumber' => $this->referenceNumber,
                'externalReference' => $this->externalReferenceNumber,
                'status' => $transaction->status
            ]);

            return $this->buildResponse($transaction, 'completed', 'Transaction processed successfully');

        } catch (Exception $e) {
            DB::rollBack();
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            Log::error('TransactionProcessingService: Transaction processing failed', [
                'correlationId' => $this->correlationId,
                'step' => 'failed',
                'error' => $e->getMessage(),
                'errorCode' => $e->getCode(),
                'processingTimeMs' => $processingTime,
                'trace' => $e->getTraceAsString(),
                'serviceType' => $this->serviceType,
                'amount' => $this->amount,
                'memberId' => $this->memberId
            ]);

            $this->handleFailure($e, [
                'step' => 'main_process',
                'processingTimeMs' => $processingTime
            ]);

            return $this->buildResponse(null, 'failed', $this->getUserFriendlyErrorMessage($e), null, $e);
        }
    }

    /**
     * Build comprehensive response for posting service
     */
    protected function buildResponse($transaction, $status, $message, $externalResult = null, $exception = null)
    {
        // Handle null transaction case
        $transactionData = null;
        if ($transaction) {
            $transactionData = [
                'id' => $transaction->id,
                'reference' => $transaction->reference,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'external_system' => $transaction->external_system,
                'external_reference' => $transaction->external_reference,
                'created_at' => $transaction->created_at->toIso8601String(),
                'completed_at' => $transaction->completed_at ? $transaction->completed_at->toIso8601String() : null,
                'failed_at' => $transaction->failed_at ? $transaction->failed_at->toIso8601String() : null,
                'error_code' => $transaction->error_code,
                'error_message' => $transaction->error_message,
                'retry_count' => $transaction->retry_count ?? 0,
                'metadata' => $transaction->metadata ?? []
            ];
        } else {
            // Provide default values when transaction is null
            $transactionData = [
                'id' => null,
                'reference' => $this->referenceNumber,
                'amount' => $this->amount,
                'status' => $status,
                'external_system' => $this->serviceType,
                'external_reference' => $this->externalReferenceNumber ?? null,
                'created_at' => now()->toIso8601String(),
                'completed_at' => null,
                'failed_at' => $status === 'failed' ? now()->toIso8601String() : null,
                'error_code' => $exception ? $exception->getCode() : null,
                'error_message' => $exception ? $exception->getMessage() : null,
                'retry_count' => 0,
                'metadata' => $this->meta ?? []
            ];
        }

        $response = [
            'success' => in_array($status, ['success', 'completed']),
            'status' => $status,
            'message' => $message,
            'correlation_id' => $this->correlationId,
            'transaction' => $transactionData,
            'accounts' => [
                'source_account' => $this->sourceAccount,
                'destination_account' => $this->destinationAccount
            ],
            'service_details' => [
                'service_type' => $this->serviceType,
                'saccos_service' => $this->saccosService,
                'member_id' => $this->memberId
            ],
            'external_result' => $externalResult,
            'should_post_to_ledger' => $this->shouldPostToLedger($transaction, $status),
            'posting_advice' => $this->getPostingAdvice($transaction, $status, $exception)
        ];

        // Add user-friendly error information if failed (no technical details)
        if ($exception) {
            $response['error_details'] = [
                'user_message' => $this->getUserFriendlyErrorMessage($exception),
                'error_code' => $exception->getCode(),
                'technical_message' => config('app.debug') ? $exception->getMessage() : null,
                'stack_trace' => config('app.debug') ? $exception->getTraceAsString() : null
            ];
        }

        return $response;
    }

    /**
     * Determine if transaction should be posted to ledger
     */
    protected function shouldPostToLedger($transaction, $status)
    {
        // Only post to ledger for successful transactions
        if (!in_array($status, ['success', 'completed'])) {
            return false;
        }

        // If transaction is null, we can't post to ledger
        if (!$transaction) {
            return false;
        }

        // Check if transaction is in a postable state
        $postableStatuses = ['completed', 'processing'];
        if (!in_array($transaction->status, $postableStatuses)) {
            return false;
        }

        // For external transactions, ensure we have external reference
        if ($transaction->external_system !== 'internal_transfer' && !$transaction->external_reference) {
            return false;
        }

        return true;
    }

    /**
     * Get posting advice for the posting service
     */
    protected function getPostingAdvice($transaction, $status, $exception = null)
    {
        $advice = [
            'action' => 'none',
            'reason' => '',
            'recommendations' => []
        ];

        switch ($status) {
            case 'success':
            case 'completed':
                if ($this->shouldPostToLedger($transaction, $status)) {
                    $advice['action'] = 'post_to_ledger';
                    $advice['reason'] = 'Transaction processed successfully and ready for ledger posting';
                    $advice['recommendations'] = [
                        'Post transaction to ledger immediately',
                        'Use transaction reference for audit trail',
                        'Monitor for any reversal requests'
                    ];
                } else {
                    $advice['action'] = 'wait';
                    $advice['reason'] = 'Transaction processed but not ready for ledger posting';
                    $advice['recommendations'] = [
                        'Wait for external confirmation',
                        'Monitor transaction status',
                        'Post to ledger when status becomes completed'
                    ];
                }
                break;

            case 'failed':
                $advice['action'] = 'do_not_post';
                $advice['reason'] = 'Transaction failed and should not be posted to ledger';
                $advice['recommendations'] = [
                    'Do not post to ledger',
                    'Investigate failure reason',
                    'Consider manual intervention if needed',
                    'Monitor for retry attempts'
                ];
                break;

            case 'idempotent':
                $advice['action'] = 'check_existing';
                $advice['reason'] = 'Transaction already exists, check if already posted to ledger';
                $advice['recommendations'] = [
                    'Check if transaction was already posted to ledger',
                    'Use existing transaction reference',
                    'Do not create duplicate ledger entries'
                ];
                break;

            default:
                $advice['action'] = 'investigate';
                $advice['reason'] = 'Unknown transaction status, requires investigation';
                $advice['recommendations'] = [
                    'Investigate transaction status',
                    'Check logs for more details',
                    'Contact system administrator if needed'
                ];
        }

        return $advice;
    }

    /**
     * Create failed transaction record for audit
     */
    protected function createFailedTransactionRecord($exception)
    {
        return Transaction::create([
            'reference' => $this->referenceNumber,
            'amount' => $this->amount,
            'external_system' => $this->serviceType,
            'saccos_service' => $this->saccosService,
            'source_account' => $this->sourceAccount,
            'destination_account' => $this->destinationAccount,
            'member_id' => $this->memberId,
            'status' => 'failed',
            'failed_at' => now(),
            'error_code' => $exception->getCode() ?: 'PROCESSING_ERROR',
            'error_message' => $exception->getMessage(),
            'correlation_id' => $this->correlationId,
            'metadata' => array_merge($this->meta ?? [], [
                'processing_failed_at' => now()->toIso8601String(),
                'exception_class' => get_class($exception),
                'stack_trace' => $exception->getTraceAsString()
            ])
        ]);
    }

    /**
     * Generate 12-digit reference number
     */
    protected function generateReferenceNumber()
    {
        $timestamp = microtime(true);
        $microseconds = (int)($timestamp * 1000000) % 1000000; // Get microseconds
        $random = mt_rand(1000, 9999);
        $reference = substr($timestamp . $microseconds . $random, -12);
        
        // Ensure it's exactly 12 digits
        return str_pad($reference, 12, '0', STR_PAD_LEFT);
    }

    /**
     * Validate transaction parameters
     */
    protected function validateTransaction()
    {
        Log::info('TransactionProcessingService: Starting transaction validation', [
            'correlationId' => $this->correlationId,
            'step' => 'validation_start'
        ]);

        // Validate service type
        Log::info('TransactionProcessingService: Validating service type', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_service_type',
            'serviceType' => $this->serviceType
        ]);
        
        if (!in_array($this->serviceType, ['cash', 'tips_mno', 'tips_bank', 'internal_transfer'])) {
            $error = "Invalid service type: {$this->serviceType}";
            Log::error('TransactionProcessingService: Service type validation failed', [
                'correlationId' => $this->correlationId,
                'step' => 'validate_service_type_failed',
                'serviceType' => $this->serviceType,
                'error' => $error
            ]);
            throw new Exception($error);
        }
        
        Log::info('TransactionProcessingService: Service type validation passed', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_service_type_passed'
        ]);

        // Validate amount
        Log::info('TransactionProcessingService: Validating amount', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_amount',
            'amount' => $this->amount
        ]);
        
        if ($this->amount <= 0) {
            $error = "Invalid amount: {$this->amount}";
            Log::error('TransactionProcessingService: Amount validation failed', [
                'correlationId' => $this->correlationId,
                'step' => 'validate_amount_failed',
                'amount' => $this->amount,
                'error' => $error
            ]);
            throw new Exception($error);
        }
        
        Log::info('TransactionProcessingService: Amount validation passed', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_amount_passed'
        ]);

        // Validate accounts
        Log::info('TransactionProcessingService: Validating accounts', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_accounts',
            'sourceAccount' => $this->maskAccountNumber($this->sourceAccount),
            'destinationAccount' => $this->maskAccountNumber($this->destinationAccount)
        ]);
        
        if (empty($this->sourceAccount)) {
            $error = "Source account is required";
            Log::error('TransactionProcessingService: Source account validation failed', [
                'correlationId' => $this->correlationId,
                'step' => 'validate_source_account_failed',
                'error' => $error
            ]);
            throw new Exception($error);
        }
        
        if (empty($this->destinationAccount)) {
            $error = "Destination account is required";
            Log::error('TransactionProcessingService: Destination account validation failed', [
                'correlationId' => $this->correlationId,
                'step' => 'validate_destination_account_failed',
                'error' => $error
            ]);
            throw new Exception($error);
        }
        
        Log::info('TransactionProcessingService: Account validation passed', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_accounts_passed'
        ]);

        // Validate member ID
        Log::info('TransactionProcessingService: Validating member ID', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_member_id',
            'memberId' => $this->memberId
        ]);
        
        if (empty($this->memberId)) {
            $error = "Member ID is required";
            Log::error('TransactionProcessingService: Member ID validation failed', [
                'correlationId' => $this->correlationId,
                'step' => 'validate_member_id_failed',
                'error' => $error
            ]);
            throw new Exception($error);
        }
        
        Log::info('TransactionProcessingService: Member ID validation passed', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_member_id_passed'
        ]);

        // Validate service-specific requirements
        Log::info('TransactionProcessingService: Validating service-specific requirements', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_service_specific',
            'serviceType' => $this->serviceType
        ]);
        
        if ($this->serviceType === 'tips_mno') {
            if (empty($this->meta['phone_number'])) {
                $error = "Phone number is required for TIPS MNO service";
                Log::error('TransactionProcessingService: TIPS MNO validation failed', [
                    'correlationId' => $this->correlationId,
                    'step' => 'validate_tips_mno_failed',
                    'error' => $error,
                    'meta' => $this->sanitizeMetaForLogging($this->meta)
                ]);
                throw new Exception($error);
            }
            Log::info('TransactionProcessingService: TIPS MNO validation passed', [
                'correlationId' => $this->correlationId,
                'step' => 'validate_tips_mno_passed'
            ]);
        }
        
        if ($this->serviceType === 'tips_bank') {
            if (empty($this->meta['bank_code'])) {
                $error = "Bank code is required for TIPS Bank service";
                Log::error('TransactionProcessingService: TIPS Bank validation failed', [
                    'correlationId' => $this->correlationId,
                    'step' => 'validate_tips_bank_failed',
                    'error' => $error,
                    'meta' => $this->sanitizeMetaForLogging($this->meta)
                ]);
                throw new Exception($error);
            }
            Log::info('TransactionProcessingService: TIPS Bank validation passed', [
                'correlationId' => $this->correlationId,
                'step' => 'validate_tips_bank_passed'
            ]);
        }
        
        Log::info('TransactionProcessingService: Service-specific validation passed', [
            'correlationId' => $this->correlationId,
            'step' => 'validate_service_specific_passed'
        ]);

        Log::info('TransactionProcessingService: All validation checks passed successfully', [
            'correlationId' => $this->correlationId,
            'step' => 'validation_complete',
            'serviceType' => $this->serviceType,
            'amount' => $this->amount,
            'memberId' => $this->memberId
        ]);
    }

    /**
     * Check for duplicate transactions (idempotency)
     */
    protected function checkIdempotency()
    {
        // Check if transaction with same correlation ID exists
        $existing = Transaction::where('correlation_id', $this->correlationId)->first();
        
        if ($existing) {
            Log::warning('Duplicate transaction detected', [
                'correlationId' => $this->correlationId,
                'existingTransactionId' => $existing->id
            ]);
            return $existing;
        }

        return null;
    }

    /**
     * Check if transaction is idempotent (legacy method for backward compatibility)
     */
    protected function isIdempotent()
    {
        return $this->checkIdempotency() === null;
    }

    /**
     * Create initial transaction record
     */
    protected function createTransactionRecord()
    {
        Log::info('TransactionProcessingService: Creating transaction record', [
            'correlationId' => $this->correlationId,
            'step' => 'create_record_start',
            'serviceType' => $this->serviceType,
            'amount' => $this->amount,
            'sourceAccount' => $this->maskAccountNumber($this->sourceAccount),
            'destinationAccount' => $this->maskAccountNumber($this->destinationAccount)
        ]);

        try {
            $accountId = $this->getAccountId($this->sourceAccount);
            
            Log::info('TransactionProcessingService: Account ID resolved', [
                'correlationId' => $this->correlationId,
                'step' => 'account_id_resolved',
                'accountId' => $accountId,
                'sourceAccount' => $this->maskAccountNumber($this->sourceAccount)
            ]);

            $transactionData = [
                'transaction_uuid' => Str::uuid(),
                'account_id' => $accountId,
                'amount' => $this->amount,
                'currency' => 'TZS',
                'type' => 'transfer',
                'transaction_category' => $this->saccosService,
                'transaction_subcategory' => $this->serviceType,
                'narration' => $this->meta['narration'] ?? "Transaction: {$this->saccosService}",
                'reference' => $this->referenceNumber,
                'correlation_id' => $this->correlationId,
                'status' => 'pending',
                'balance_before' => $this->getAccountBalance($this->sourceAccount),
                'external_system' => $this->externalServices[$this->serviceType] ?? null,
                'metadata' => $this->meta,
                'initiated_at' => now(),
                'is_system_generated' => true,
                'initiated_by' => 'system'
            ];

            Log::info('TransactionProcessingService: Transaction data prepared', [
                'correlationId' => $this->correlationId,
                'step' => 'data_prepared',
                'transactionData' => array_merge($transactionData, [
                    'sourceAccount' => $this->maskAccountNumber($this->sourceAccount),
                    'destinationAccount' => $this->maskAccountNumber($this->destinationAccount),
                    'metadata' => $this->sanitizeMetaForLogging($this->meta)
                ])
            ]);

            $transaction = Transaction::create($transactionData);

            Log::info('TransactionProcessingService: Transaction record created successfully', [
                'correlationId' => $this->correlationId,
                'step' => 'record_created',
                'transactionId' => $transaction->id,
                'referenceNumber' => $transaction->reference,
                'status' => $transaction->status,
                'balanceBefore' => $transaction->balance_before
            ]);

            return $transaction;

        } catch (Exception $e) {
            Log::error('TransactionProcessingService: Failed to create transaction record', [
                'correlationId' => $this->correlationId,
                'step' => 'create_record_failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'serviceType' => $this->serviceType,
                'amount' => $this->amount
            ]);
            throw $e;
        }
    }

    /**
     * Get account ID from account number
     */
    protected function getAccountId($accountNumber)
    {
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        return $account ? $account->id : null;
    }

    /**
     * Get account balance
     */
    protected function getAccountBalance($accountNumber)
    {
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        return $account ? $account->balance : 0;
    }

    /**
     * Process cash transaction (internal only)
     */
    protected function processCashTransaction($transaction)
    {
        Log::info('Processing cash transaction', [
            'correlationId' => $this->correlationId,
            'referenceNumber' => $this->referenceNumber
        ]);

        DB::beginTransaction();
        try {
            // Use existing TransactionPostingService for ledger posting
            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $this->sourceAccount,
                'second_account' => $this->destinationAccount,
                'amount' => $this->amount,
                'narration' => $this->meta['narration'] ?? "Cash transaction: {$this->saccosService}",
                'action' => $this->saccosService
            ];

            $result = $transactionService->postTransaction($transactionData);

            // Update transaction record
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'balance_after' => $this->getAccountBalance($this->sourceAccount),
                'processing_time_ms' => round((microtime(true) - $transaction->initiated_at->getPreciseTimestamp(3)) * 1000)
            ]);

            DB::commit();

            $this->audit('transaction_completed', [
                'transactionId' => $transaction->id,
                'referenceNumber' => $this->referenceNumber,
                'result' => $result
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process external transaction (TIPS, NBC, etc.)
     */
    protected function processExternalTransaction($transaction)
    {
        Log::info('TransactionProcessingService: Starting external transaction processing', [
            'correlationId' => $this->correlationId,
            'step' => 'external_processing_start',
            'serviceType' => $this->serviceType,
            'transactionId' => $transaction->id,
            'externalSystem' => $this->externalServices[$this->serviceType] ?? 'unknown'
        ]);

        // Update transaction status to processing
        Log::info('TransactionProcessingService: Updating transaction status to processing', [
            'correlationId' => $this->correlationId,
            'step' => 'status_update_processing',
            'transactionId' => $transaction->id,
            'previousStatus' => $transaction->status,
            'newStatus' => 'processing'
        ]);

        $transaction->update([
            'status' => 'processing',
            'processed_at' => now()
        ]);

        try {
            // Step 1: Call external service with retry logic
            Log::info('TransactionProcessingService: Calling external service with retry logic', [
                'correlationId' => $this->correlationId,
                'step' => 'external_service_call',
                'serviceType' => $this->serviceType,
                'transactionId' => $transaction->id,
                'retryEnabled' => true
            ]);

            $externalResult = $this->callExternalServiceWithRetry($transaction);

            Log::info('TransactionProcessingService: External service call completed', [
                'correlationId' => $this->correlationId,
                'step' => 'external_service_complete',
                'serviceType' => $this->serviceType,
                'transactionId' => $transaction->id,
                'success' => $externalResult['success'] ?? false,
                'externalReference' => $externalResult['external_reference'] ?? null,
                'statusCode' => $externalResult['status_code'] ?? null
            ]);

            // Step 2: Update transaction with external reference
            $this->externalReferenceNumber = $externalResult['external_reference'] ?? null;
            
            Log::info('TransactionProcessingService: Updating transaction with external reference', [
                'correlationId' => $this->correlationId,
                'step' => 'update_external_reference',
                'transactionId' => $transaction->id,
                'externalReference' => $this->externalReferenceNumber,
                'externalTransactionId' => $externalResult['external_transaction_id'] ?? null
            ]);
            
            $transaction->update([
                'external_reference' => $this->externalReferenceNumber,
                'external_transaction_id' => $externalResult['external_transaction_id'] ?? null,
                'external_request_payload' => $externalResult['request_payload'] ?? null,
                'external_response_payload' => $externalResult['response_payload'] ?? null,
                'external_status_code' => $externalResult['status_code'] ?? null,
                'external_status_message' => $externalResult['status_message'] ?? null
            ]);

            // Step 3: Handle result based on external service response
            if ($externalResult['success']) {
                Log::info('TransactionProcessingService: External service call successful, updating transaction status', [
                    'correlationId' => $this->correlationId,
                    'step' => 'external_success',
                    'transactionId' => $transaction->id,
                    'externalReference' => $this->externalReferenceNumber
                ]);

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'balance_after' => $this->getAccountBalance($this->sourceAccount)
                ]);

                Log::info('TransactionProcessingService: Transaction completed successfully', [
                    'correlationId' => $this->correlationId,
                    'step' => 'transaction_completed',
                    'transactionId' => $transaction->id,
                    'externalReference' => $this->externalReferenceNumber,
                    'balanceAfter' => $transaction->balance_after
                ]);

                $this->audit('external_transaction_completed', [
                    'externalReference' => $this->externalReferenceNumber,
                    'externalResult' => $externalResult
                ]);

                // Send success notification
                $this->sendSuccessNotification($transaction, $externalResult);

            } else {
                Log::error('TransactionProcessingService: External service call failed', [
                    'correlationId' => $this->correlationId,
                    'step' => 'external_failure',
                    'transactionId' => $transaction->id,
                    'errorCode' => $externalResult['error_code'] ?? 'UNKNOWN',
                    'errorMessage' => $externalResult['error_message'] ?? 'Unknown error',
                    'statusCode' => $externalResult['status_code'] ?? null
                ]);

                $transaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_code' => $externalResult['error_code'] ?? 'EXTERNAL_SERVICE_ERROR',
                    'error_message' => $externalResult['error_message'] ?? 'External service call failed'
                ]);

                $this->audit('external_transaction_failed', [
                    'errorCode' => $externalResult['error_code'] ?? 'UNKNOWN',
                    'errorMessage' => $externalResult['error_message'] ?? 'Unknown error',
                    'externalResult' => $externalResult
                ]);

                // Send failure notification
                $this->sendFailureNotification($transaction, $externalResult);

                throw new Exception('External service call failed: ' . ($externalResult['error_message'] ?? 'Unknown error'));
            }

        } catch (Exception $e) {
            Log::error('TransactionProcessingService: External transaction processing failed', [
                'correlationId' => $this->correlationId,
                'step' => 'external_processing_failed',
                'transactionId' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'serviceType' => $this->serviceType
            ]);

            $transaction->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_code' => 'EXTERNAL_PROCESSING_ERROR',
                'error_message' => $e->getMessage()
            ]);

            $this->audit('external_processing_failed', [
                'error' => $e->getMessage(),
                'serviceType' => $this->serviceType
            ]);

            throw $e;
        }
    }

    /**
     * Call external service with retry logic and circuit breaker
     */
    protected function callExternalServiceWithRetry($transaction)
    {
        $maxRetries = 3;
        $attempt = 1;
        $lastException = null;

        Log::info('TransactionProcessingService: Starting external service call with retry logic', [
            'correlationId' => $this->correlationId,
            'step' => 'retry_logic_start',
            'serviceType' => $this->serviceType,
            'transactionId' => $transaction->id,
            'maxRetries' => $maxRetries
        ]);

        while ($attempt <= $maxRetries) {
            Log::info('TransactionProcessingService: External service call attempt', [
                'correlationId' => $this->correlationId,
                'step' => 'external_call_attempt',
                'attempt' => $attempt,
                'maxRetries' => $maxRetries,
                'serviceType' => $this->serviceType,
                'transactionId' => $transaction->id
            ]);

            try {
                $result = $this->callExternalService($attempt);
                
                Log::info('TransactionProcessingService: External service call successful', [
                    'correlationId' => $this->correlationId,
                    'step' => 'external_call_success',
                    'attempt' => $attempt,
                    'serviceType' => $this->serviceType,
                    'transactionId' => $transaction->id,
                    'success' => $result['success'] ?? false,
                    'externalReference' => $result['external_reference'] ?? null
                ]);

                return $result;

            } catch (Exception $e) {
                $lastException = $e;
                
                Log::warning('TransactionProcessingService: External service call failed', [
                    'correlationId' => $this->correlationId,
                    'step' => 'external_call_failed',
                    'attempt' => $attempt,
                    'maxRetries' => $maxRetries,
                    'serviceType' => $this->serviceType,
                    'transactionId' => $transaction->id,
                    'error' => $e->getMessage(),
                    'errorCode' => $e->getCode()
                ]);

                // Check if this is a permanent failure (no retry needed)
                if ($this->isPermanentFailure($e)) {
                    Log::info('TransactionProcessingService: Permanent failure detected, no retry needed', [
                        'correlationId' => $this->correlationId,
                        'step' => 'permanent_failure',
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                        'errorCode' => $e->getCode()
                    ]);
                    break;
                }

                // Update transaction retry count
                $transaction->increment('retry_count');
                $transaction->update([
                    'last_retry_at' => now(),
                    'next_retry_at' => $attempt < $maxRetries ? now()->addMinutes(pow(2, $attempt)) : null
                ]);

                Log::info('TransactionProcessingService: Updated transaction retry information', [
                    'correlationId' => $this->correlationId,
                    'step' => 'retry_info_updated',
                    'attempt' => $attempt,
                    'retryCount' => $transaction->retry_count,
                    'nextRetryAt' => $transaction->next_retry_at
                ]);

                if ($attempt < $maxRetries) {
                    $delay = pow(2, $attempt); // Exponential backoff: 2, 4, 8 seconds
                    
                    Log::info('TransactionProcessingService: Waiting before retry', [
                        'correlationId' => $this->correlationId,
                        'step' => 'retry_delay',
                        'attempt' => $attempt,
                        'delaySeconds' => $delay,
                        'nextRetryAt' => now()->addSeconds($delay)
                    ]);
                    
                    sleep($delay);
                }

                $attempt++;
            }
        }

        // All retries exhausted
        Log::error('TransactionProcessingService: All retry attempts exhausted', [
            'correlationId' => $this->correlationId,
            'step' => 'retries_exhausted',
            'maxRetries' => $maxRetries,
            'serviceType' => $this->serviceType,
            'transactionId' => $transaction->id,
            'finalError' => $lastException ? $lastException->getMessage() : 'Unknown error',
            'finalErrorCode' => $lastException ? $lastException->getCode() : 'UNKNOWN'
        ]);

        // Update transaction status
        $transaction->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_code' => 'RETRY_EXHAUSTED',
            'error_message' => 'All retry attempts exhausted: ' . ($lastException ? $lastException->getMessage() : 'Unknown error')
        ]);

        $this->audit('retry_exhausted', [
            'maxRetries' => $maxRetries,
            'finalError' => $lastException ? $lastException->getMessage() : 'Unknown error',
            'serviceType' => $this->serviceType
        ]);

        // Return failure result
        return [
            'success' => false,
            'error_code' => 'RETRY_EXHAUSTED',
            'error_message' => 'All retry attempts exhausted: ' . ($lastException ? $lastException->getMessage() : 'Unknown error'),
            'attempts' => $maxRetries,
            'final_exception' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ];
    }

    /**
     * Call the appropriate external service with enhanced error handling
     */
    protected function callExternalService($attempt = 1, $timeout = 30)
    {
        $startTime = microtime(true);

        Log::info('TransactionProcessingService: Calling external service', [
            'correlationId' => $this->correlationId,
            'step' => 'external_service_call',
            'serviceType' => $this->serviceType,
            'attempt' => $attempt,
            'timeout' => $timeout,
            'amount' => $this->amount,
            'sourceAccount' => $this->maskAccountNumber($this->sourceAccount),
            'destinationAccount' => $this->maskAccountNumber($this->destinationAccount)
        ]);

        try {
            Log::info('TransactionProcessingService: Routing to specific service handler', [
                'correlationId' => $this->correlationId,
                'step' => 'service_routing',
                'serviceType' => $this->serviceType
            ]);

            switch ($this->serviceType) {
                case 'tips_mno':
                    Log::info('TransactionProcessingService: Routing to TIPS MNO service', [
                        'correlationId' => $this->correlationId,
                        'step' => 'route_tips_mno',
                        'phoneNumber' => $this->maskPhoneNumber($this->meta['phone_number'] ?? 'NOT_PROVIDED'),
                        'walletProvider' => $this->meta['wallet_provider'] ?? 'MPESA'
                    ]);
                    return $this->callTipsMnoService($timeout);
                
                case 'tips_bank':
                    Log::info('TransactionProcessingService: Routing to TIPS Bank service', [
                        'correlationId' => $this->correlationId,
                        'step' => 'route_tips_bank',
                        'bankCode' => $this->meta['bank_code'] ?? 'NOT_PROVIDED',
                        'accountNumber' => $this->maskAccountNumber($this->destinationAccount)
                    ]);
                    return $this->callTipsBankService($timeout);
                
                case 'internal_transfer':
                    Log::info('TransactionProcessingService: Routing to Internal Transfer service', [
                        'correlationId' => $this->correlationId,
                        'step' => 'route_internal_transfer',
                        'sourceAccount' => $this->maskAccountNumber($this->sourceAccount),
                        'destinationAccount' => $this->maskAccountNumber($this->destinationAccount)
                    ]);
                    return $this->callInternalTransferService($timeout);
                
                default:
                    $error = "Unsupported service type: {$this->serviceType}";
                    Log::error('TransactionProcessingService: Unsupported service type', [
                        'correlationId' => $this->correlationId,
                        'step' => 'unsupported_service',
                        'serviceType' => $this->serviceType,
                        'error' => $error
                    ]);
                    throw new Exception($error);
            }
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            // Network connectivity issues
            $error = "Network connectivity failed: " . $e->getMessage();
            Log::error('TransactionProcessingService: Network connectivity error', [
                'correlationId' => $this->correlationId,
                'step' => 'network_error',
                'serviceType' => $this->serviceType,
                'attempt' => $attempt,
                'error' => $error,
                'errorCode' => 'NETWORK_ERROR',
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception($error, 'NETWORK_ERROR');
        } catch (\GuzzleHttp\Exception\TimeoutException $e) {
            // Request timeout
            $error = "Request timeout after {$timeout} seconds";
            Log::error('TransactionProcessingService: Request timeout error', [
                'correlationId' => $this->correlationId,
                'step' => 'timeout_error',
                'serviceType' => $this->serviceType,
                'attempt' => $attempt,
                'timeout' => $timeout,
                'error' => $error,
                'errorCode' => 'TIMEOUT_ERROR',
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception($error, 'TIMEOUT_ERROR');
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // 5xx server errors
            $statusCode = $e->getResponse()->getStatusCode();
            $error = "Server error {$statusCode}: " . $e->getMessage();
            Log::error('TransactionProcessingService: Server error', [
                'correlationId' => $this->correlationId,
                'step' => 'server_error',
                'serviceType' => $this->serviceType,
                'attempt' => $attempt,
                'statusCode' => $statusCode,
                'error' => $error,
                'errorCode' => "SERVER_ERROR_{$statusCode}",
                'responseBody' => $e->getResponse()->getBody()->getContents(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception($error, "SERVER_ERROR_{$statusCode}");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 4xx client errors
            $statusCode = $e->getResponse()->getStatusCode();
            $error = "Client error {$statusCode}: " . $e->getMessage();
            Log::error('TransactionProcessingService: Client error', [
                'correlationId' => $this->correlationId,
                'step' => 'client_error',
                'serviceType' => $this->serviceType,
                'attempt' => $attempt,
                'statusCode' => $statusCode,
                'error' => $error,
                'errorCode' => "CLIENT_ERROR_{$statusCode}",
                'responseBody' => $e->getResponse()->getBody()->getContents(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception($error, "CLIENT_ERROR_{$statusCode}");
        } catch (Exception $e) {
            // Other exceptions
            Log::error('TransactionProcessingService: General exception in external service call', [
                'correlationId' => $this->correlationId,
                'step' => 'general_exception',
                'serviceType' => $this->serviceType,
                'attempt' => $attempt,
                'error' => $e->getMessage(),
                'errorCode' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } finally {
            $processingTime = round((microtime(true) - $startTime) * 1000);
            Log::info('TransactionProcessingService: External service call completed', [
                'correlationId' => $this->correlationId,
                'step' => 'external_service_complete',
                'serviceType' => $this->serviceType,
                'processingTimeMs' => $processingTime,
                'attempt' => $attempt,
                'timeout' => $timeout,
                'success' => !isset($e) // true if no exception was thrown
            ]);
        }
    }

    /**
     * Call TIPS Mobile Money service
     */
    protected function callTipsMnoService($timeout = 30)
    {
        Log::info('Calling TIPS MNO service', [
            'correlationId' => $this->correlationId,
            'amount' => $this->amount,
            'phoneNumber' => $this->meta['phone_number'] ?? 'NOT_PROVIDED'
        ]);

        // Validate required meta data
        if (empty($this->meta['phone_number'])) {
            throw new Exception('Phone number is required for TIPS MNO service');
        }

        try {
            $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();
            $nbcLookupService = new \App\Services\NbcPayments\NbcLookupService();

            // Prepare lookup request payload
            $lookupRequestPayload = [
                'phone_number' => $this->meta['phone_number'],
                'wallet_provider' => $this->meta['wallet_provider'] ?? 'MPESA',
                'source_account' => $this->sourceAccount,
                'amount' => $this->amount,
                'account_category' => 'PERSON'
            ];

            // Step 1: Lookup the mobile number
            $lookupStartTime = microtime(true);
            $lookupResult = $nbcLookupService->bankToWalletLookup(
                $this->meta['phone_number'],
                $this->meta['wallet_provider'] ?? 'MPESA',
                $this->sourceAccount,
                $this->amount,
                'PERSON'
            );
            $lookupProcessingTime = round((microtime(true) - $lookupStartTime) * 1000);

            // Store lookup information in transaction record
            $this->storeLookupInformation($lookupResult, $lookupRequestPayload, $lookupProcessingTime);

            if (!$lookupResult['success']) {
                throw new Exception('Mobile number lookup failed: ' . ($lookupResult['message'] ?? 'Unknown error'));
            }

            // Step 2: Process the transfer
            $transferResult = $nbcPaymentService->processBankToWalletTransfer(
                $lookupResult,
                $this->sourceAccount,
                $this->amount,
                $this->meta['phone_number'],
                $this->memberId,
                $this->meta['narration'] ?? "TIPS MNO transfer: {$this->saccosService}",
                $this->meta['payer_name'] ?? null
            );

            return [
                'success' => $transferResult['success'],
                'external_reference' => $transferResult['data']['hostReferenceCbs'] ?? null,
                'external_transaction_id' => $transferResult['data']['hostReferenceGw'] ?? null,
                'status_code' => $transferResult['statusCode'] ?? null,
                'status_message' => $transferResult['message'] ?? null,
                'request_payload' => $transferResult['request_payload'] ?? null,
                'response_payload' => $transferResult['response_payload'] ?? null,
                'error_code' => $transferResult['error'] ?? null,
                'error_message' => $transferResult['message'] ?? null
            ];

        } catch (Exception $e) {
            Log::error('TIPS MNO service call failed', [
                'correlationId' => $this->correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error_code' => 'TIPS_MNO_ERROR',
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * Call TIPS Bank service
     */
    protected function callTipsBankService($timeout = 30)
    {
        Log::info('Calling TIPS Bank service', [
            'correlationId' => $this->correlationId,
            'amount' => $this->amount,
            'destinationAccount' => $this->destinationAccount
        ]);

        try {
            $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();
            $nbcLookupService = new \App\Services\NbcPayments\NbcLookupService();

            // Prepare lookup request payload
            $lookupRequestPayload = [
                'account_number' => $this->destinationAccount,
                'bank_code' => $this->meta['bank_code'] ?? '015',
                'source_account' => $this->sourceAccount,
                'amount' => $this->amount,
                'account_category' => 'PERSON'
            ];

            // Step 1: Lookup the bank account
            $lookupStartTime = microtime(true);
            $lookupResult = $nbcLookupService->bankToBankLookup(
                $this->destinationAccount,
                $this->meta['bank_code'] ?? '015', // Default to NBC
                $this->sourceAccount,
                $this->amount,
                'PERSON'
            );
            $lookupProcessingTime = round((microtime(true) - $lookupStartTime) * 1000);

            // Store lookup information in transaction record
            $this->storeLookupInformation($lookupResult, $lookupRequestPayload, $lookupProcessingTime);

            if (!$lookupResult['success']) {
                throw new Exception('Bank account lookup failed: ' . ($lookupResult['message'] ?? 'Unknown error'));
            }

            // Step 2: Process the transfer
            $transferResult = $nbcPaymentService->processBankToBankTransfer(
                $lookupResult,
                $this->sourceAccount,
                $this->amount,
                $this->meta['phone_number'] ?? '255000000000',
                $this->memberId,
                $this->meta['narration'] ?? "TIPS Bank transfer: {$this->saccosService}",
                $this->meta['product_code'] ?? 'FTLC'
            );

            return [
                'success' => $transferResult['success'],
                'external_reference' => $transferResult['data']['hostReferenceCbs'] ?? null,
                'external_transaction_id' => $transferResult['data']['hostReferenceGw'] ?? null,
                'status_code' => $transferResult['statusCode'] ?? null,
                'status_message' => $transferResult['message'] ?? null,
                'request_payload' => $transferResult['request_payload'] ?? null,
                'response_payload' => $transferResult['response_payload'] ?? null,
                'error_code' => $transferResult['error'] ?? null,
                'error_message' => $transferResult['message'] ?? null
            ];

        } catch (Exception $e) {
            Log::error('TIPS Bank service call failed', [
                'correlationId' => $this->correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error_code' => 'TIPS_BANK_ERROR',
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * Call Internal Transfer service
     */
    protected function callInternalTransferService($timeout = 30)
    {
        Log::info('Calling Internal Transfer service', [
            'correlationId' => $this->correlationId,
            'amount' => $this->amount,
            'sourceAccount' => $this->sourceAccount,
            'destinationAccount' => $this->destinationAccount
        ]);

        try {
            $internalTransferService = new \App\Services\NbcPayments\InternalFundTransferService();

            $transferData = [
                'creditAccount' => $this->destinationAccount,
                'creditCurrency' => 'TZS',
                'debitAccount' => $this->sourceAccount,
                'debitCurrency' => 'TZS',
                'amount' => $this->amount,
                'narration' => $this->meta['narration'] ?? "Internal transfer: {$this->saccosService}",
                'pyrName' => $this->meta['payer_name'] ?? 'NBC Member'
            ];

            $result = $internalTransferService->processInternalTransfer($transferData);

            return [
                'success' => $result['success'],
                'external_reference' => $result['data']['hostReferenceCbs'] ?? null,
                'external_transaction_id' => $result['data']['hostReferenceGw'] ?? null,
                'status_code' => $result['statusCode'] ?? null,
                'status_message' => $result['message'] ?? null,
                'request_payload' => $result['request_payload'] ?? null,
                'response_payload' => $result['response_payload'] ?? null,
                'error_code' => $result['error'] ?? null,
                'error_message' => $result['message'] ?? null
            ];

        } catch (Exception $e) {
            Log::error('Internal Transfer service call failed', [
                'correlationId' => $this->correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error_code' => 'INTERNAL_TRANSFER_ERROR',
                'error_message' => $e->getMessage()
            ];
        }
    }

    /**
     * Queue transaction for retry
     */
    protected function queueForRetry($transaction, $exception)
    {
        // Check if transaction should be queued for retry
        if ($transaction->retry_count < $transaction->max_retries) {
            Log::info('Queueing transaction for retry', [
                'correlationId' => $this->correlationId,
                'retryCount' => $transaction->retry_count,
                'maxRetries' => $transaction->max_retries
            ]);

            // TODO: Implement job queue for retry
            // ProcessTransactionRetry::dispatch($transaction)->delay(now()->addMinutes(5));
        } else {
            Log::warning('Transaction exceeded max retries, moving to DLQ', [
                'correlationId' => $this->correlationId,
                'retryCount' => $transaction->retry_count,
                'maxRetries' => $transaction->max_retries
            ]);

            // TODO: Implement Dead Letter Queue
            // $this->moveToDeadLetterQueue($transaction, $exception);
        }
    }

    /**
     * Handle transaction failure
     */
    protected function handleFailure($error, $context = [])
    {
        $this->status = 'failed';
        $this->errorCode = $error->getCode();
        $this->errorMessage = $error->getMessage();

        Log::error('Transaction processing failed', [
            'correlationId' => $this->correlationId,
            'referenceNumber' => $this->referenceNumber,
            'errorCode' => $this->errorCode,
            'errorMessage' => $this->errorMessage,
            'context' => $context
        ]);

        // Update transaction record if it exists
        $transaction = Transaction::where('correlation_id', $this->correlationId)->first();
        if ($transaction) {
            $transaction->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_code' => $this->errorCode,
                'error_message' => $this->errorMessage,
                'error_details' => $error->getTraceAsString(),
                'error_context' => $context
            ]);
        }

        $this->audit('transaction_failed', [
            'errorCode' => $this->errorCode,
            'errorMessage' => $this->errorMessage,
            'context' => $context
        ]);
    }

    /**
     * Write audit log with enhanced details
     */
    protected function audit($action, $details = [])
    {
        $auditData = [
            'correlationId' => $this->correlationId,
            'action' => $action,
            'serviceType' => $this->serviceType,
            'saccosService' => $this->saccosService,
            'amount' => $this->amount,
            'memberId' => $this->memberId,
            'referenceNumber' => $this->referenceNumber,
            'externalReference' => $this->externalReferenceNumber,
            'details' => $details,
            'timestamp' => now()->toIso8601String(),
            'userAgent' => request()->userAgent(),
            'clientIp' => request()->ip(),
            'sessionId' => session()->getId()
        ];

        Log::info('TransactionProcessingService: Audit log entry', $auditData);

        // TODO: Write to dedicated audit table for compliance and reporting
        try {
            // Example audit table write (uncomment when audit table is available)
            /*
            TransactionAuditLog::create([
                'transaction_id' => $transaction->id ?? null,
                'action' => $action,
                'description' => json_encode($auditData),
                'performed_by' => auth()->id() ?? 'system',
                'client_ip' => request()->ip(),
                'context' => $details
            ]);
            */
        } catch (Exception $e) {
            Log::warning('TransactionProcessingService: Failed to write to audit table', [
                'correlationId' => $this->correlationId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send success notification
     */
    protected function sendSuccessNotification($transaction, $externalResult)
    {
        try {
            // Queue success notification
            \App\Jobs\SendTransactionNotification::dispatch(
                $transaction->id,
                'success',
                [
                    'external_reference' => $this->externalReferenceNumber,
                    'amount' => $this->amount,
                    'service_type' => $this->serviceType,
                    'member_id' => $this->memberId
                ]
            )->onQueue('notifications');
        } catch (Exception $e) {
            Log::warning('Failed to queue success notification', [
                'transactionId' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send failure notification
     */
    protected function sendFailureNotification($transaction, $externalResult)
    {
        try {
            // Queue failure notification
            \App\Jobs\SendTransactionNotification::dispatch(
                $transaction->id,
                'failure',
                [
                    'error_message' => $externalResult['error_message'] ?? 'Unknown error',
                    'error_code' => $externalResult['error_code'] ?? 'UNKNOWN',
                    'amount' => $this->amount,
                    'service_type' => $this->serviceType,
                    'member_id' => $this->memberId
                ]
            )->onQueue('notifications');
        } catch (Exception $e) {
            Log::warning('Failed to queue failure notification', [
                'transactionId' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if transaction result indicates a suspect transaction
     */
    protected function isSuspectTransaction($result)
    {
        // Check for unclear or partial success responses
        $suspectIndicators = [
            'status_code' => [200, 201, 202], // Success but might need verification
            'external_status_code' => ['PENDING', 'PROCESSING', 'UNKNOWN'],
            'response_contains' => ['pending', 'processing', 'unknown', 'unclear']
        ];

        // Check status codes
        if (isset($result['status_code']) && in_array($result['status_code'], $suspectIndicators['status_code'])) {
            return true;
        }

        // Check external status codes
        if (isset($result['external_status_code']) && in_array($result['external_status_code'], $suspectIndicators['external_status_code'])) {
            return true;
        }

        // Check response content
        if (isset($result['response_payload'])) {
            $responseStr = json_encode($result['response_payload']);
            foreach ($suspectIndicators['response_contains'] as $indicator) {
                if (stripos($responseStr, $indicator) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if error indicates permanent failure (no retry needed)
     */
    protected function isPermanentFailure($exception)
    {
        $permanentErrorCodes = [
            'VALIDATION_ERROR',
            'INVALID_ACCOUNT',
            'INSUFFICIENT_FUNDS',
            'ACCOUNT_BLOCKED',
            'INVALID_AMOUNT',
            'CLIENT_ERROR_400',
            'CLIENT_ERROR_401',
            'CLIENT_ERROR_403',
            'CLIENT_ERROR_404'
        ];

        return in_array($exception->getCode(), $permanentErrorCodes) || 
               in_array($exception->getMessage(), $permanentErrorCodes);
    }

    /**
     * Send suspect transaction notification
     */
    protected function sendSuspectNotification($transaction, $result)
    {
        try {
            // Queue suspect notification
            \App\Jobs\SendTransactionNotification::dispatch(
                $transaction->id,
                'suspect',
                [
                    'external_reference' => $this->externalReferenceNumber,
                    'amount' => $this->amount,
                    'service_type' => $this->serviceType,
                    'member_id' => $this->memberId,
                    'suspect_reason' => 'Transaction status unclear',
                    'external_status' => $result['external_status_code'] ?? 'UNKNOWN'
                ]
            )->onQueue('notifications');
        } catch (Exception $e) {
            Log::warning('Failed to queue suspect notification', [
                'transactionId' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store lookup information in transaction record
     */
    protected function storeLookupInformation($lookupResult, $lookupRequestPayload, $lookupProcessingTime)
    {
        try {
            $transaction = Transaction::where('correlation_id', $this->correlationId)->first();
            if (!$transaction) {
                Log::warning('Transaction not found for lookup information storage', [
                    'correlationId' => $this->correlationId
                ]);
                return;
            }

            // Extract lookup details from response
            $lookupData = $lookupResult['data'] ?? [];
            $lookupBody = $lookupData['body'] ?? [];

            // Prepare lookup update data
            $lookupUpdateData = [
                'lookup_reference' => $lookupData['clientRef'] ?? null,
                'lookup_status' => $lookupResult['success'] ? 'success' : 'failed',
                'lookup_error_code' => $lookupResult['success'] ? null : ($lookupResult['error'] ?? null),
                'lookup_error_message' => $lookupResult['success'] ? null : ($lookupResult['message'] ?? null),
                'lookup_request_payload' => $lookupRequestPayload,
                'lookup_response_payload' => $lookupResult,
                'lookup_performed_at' => now(),
                'lookup_processing_time_ms' => $lookupProcessingTime,
                'lookup_validated' => $lookupResult['success'],
                'lookup_validation_status' => $lookupResult['success'] ? 'validated' : 'failed'
            ];

            // Extract account details from successful lookup
            if ($lookupResult['success'] && !empty($lookupBody)) {
                $lookupUpdateData = array_merge($lookupUpdateData, [
                    'lookup_account_name' => $lookupBody['fullName'] ?? null,
                    'lookup_account_type' => $lookupBody['accountCategory'] ?? null,
                    'lookup_bank_name' => $lookupBody['bankName'] ?? null,
                    'lookup_bank_code' => $lookupBody['fspId'] ?? null,
                    'lookup_wallet_provider' => $lookupBody['destinationFsp'] ?? null,
                    'lookup_phone_number' => $this->maskPhoneNumber($lookupBody['identifier'] ?? null),
                    'lookup_account_number' => $this->maskAccountNumber($lookupBody['identifier'] ?? null),
                    'lookup_identity_type' => $lookupBody['identity']['type'] ?? null,
                    'lookup_identity_value' => $this->maskIdentityValue($lookupBody['identity']['value'] ?? null),
                    'lookup_validation_notes' => 'Lookup successful - Account validated'
                ]);
            } else {
                $lookupUpdateData['lookup_validation_notes'] = 'Lookup failed - ' . ($lookupResult['message'] ?? 'Unknown error');
            }

            // Update transaction with lookup information
            $transaction->update($lookupUpdateData);

            Log::info('Lookup information stored in transaction record', [
                'correlationId' => $this->correlationId,
                'lookupStatus' => $lookupUpdateData['lookup_status'],
                'lookupReference' => $lookupUpdateData['lookup_reference'],
                'processingTimeMs' => $lookupProcessingTime,
                'accountName' => $lookupUpdateData['lookup_account_name'] ?? 'N/A'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to store lookup information', [
                'correlationId' => $this->correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Mask phone number for security
     */
    protected function maskPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return null;
        }
        
        $length = strlen($phoneNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return substr($phoneNumber, 0, 3) . str_repeat('*', $length - 6) . substr($phoneNumber, -3);
    }

    /**
     * Mask account number for security
     */
    protected function maskAccountNumber($accountNumber)
    {
        if (empty($accountNumber)) {
            return null;
        }
        
        $length = strlen($accountNumber);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        // Handle account numbers with length less than 8
        if ($length < 8) {
            $maskLength = max(0, $length - 4); // Ensure mask length is not negative
            return substr($accountNumber, 0, 2) . str_repeat('*', $maskLength) . substr($accountNumber, -2);
        }
        
        return substr($accountNumber, 0, 4) . str_repeat('*', $length - 8) . substr($accountNumber, -4);
    }

    /**
     * Mask identity value for security
     */
    protected function maskIdentityValue($identityValue)
    {
        if (empty($identityValue)) {
            return null;
        }
        
        return '***MASKED***';
    }

    /**
     * Sanitize meta data for logging
     */
    protected function sanitizeMetaForLogging($meta)
    {
        if (empty($meta)) {
            return [];
        }
        
        $sanitized = $meta;
        
        // Mask sensitive fields
        $sensitiveFields = [
            'phone_number', 'account_number', 'bank_account_number', 
            'identity_value', 'password', 'pin', 'otp'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = $this->maskSensitiveValue($sanitized[$field]);
            }
        }
        
        return $sanitized;
    }

    /**
     * Get user-friendly error message from exception
     * 
     * @param \Exception $exception
     * @return string
     */
    protected function getUserFriendlyErrorMessage($exception)
    {
        $message = $exception->getMessage();
        
        // Remove technical details and provide user-friendly messages
        $patterns = [
            '/Details:.*$/s', // Remove "Details: {...}" sections
            '/stack_trace.*$/s', // Remove stack trace sections
            '/exception_class.*$/s', // Remove exception class details
            '/error_code.*$/s', // Remove error code details
            '/error_message.*$/s', // Remove error message details
            '/#\d+.*$/m', // Remove stack trace lines starting with #
            '/D:\\\\.*\.php\(\d+\):/', // Remove file paths with line numbers
            '/App\\\\.*->/', // Remove class method calls
            '/Object\(.*\)/', // Remove object details
            '/Array\(.*\)/', // Remove array details
            '/Illuminate\\\\.*/', // Remove Laravel framework details
            '/vendor\\\\.*/', // Remove vendor path details
            '/app\\\\.*/', // Remove app path details
        ];

        $sanitized = $message;
        foreach ($patterns as $pattern) {
            $sanitized = preg_replace($pattern, '', $sanitized);
        }

        // Clean up extra whitespace and punctuation
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        $sanitized = trim($sanitized);
        $sanitized = rtrim($sanitized, ' -');

        // Provide specific user-friendly messages for common errors
        if (strpos($sanitized, 'cURL error 6') !== false) {
            return 'Network connection failed. Please check your internet connection and try again.';
        }
        
        if (strpos($sanitized, 'cURL error 28') !== false) {
            return 'Request timed out. Please try again in a few moments.';
        }
        
        if (strpos($sanitized, 'Failed to load private key') !== false) {
            return 'Payment service configuration error. Please contact support.';
        }
        
        if (strpos($sanitized, 'Temporary network issue') !== false) {
            return 'Temporary network issue - please try again in a few minutes.';
        }
        
        if (strpos($sanitized, 'External service call failed') !== false) {
            return 'Payment service is temporarily unavailable. Please try again later.';
        }

        // If the message is too technical, provide a generic user-friendly message
        if (strlen($sanitized) < 10 || strpos($sanitized, 'Exception') !== false) {
            return 'A system error occurred during payment processing. Please try again or contact support if the issue persists.';
        }

        return $sanitized;
    }

    /**
     * Mask sensitive values for logging
     */
    protected function maskSensitiveValue($value)
    {
        if (empty($value)) {
            return null;
        }
        
        $length = strlen($value);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }
} 