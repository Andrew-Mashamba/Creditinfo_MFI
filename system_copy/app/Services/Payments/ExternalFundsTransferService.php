<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

/**
 * External Funds Transfer (EFT) Service
 * Handles transfers to accounts outside NBC Bank via TISS/TIPS
 * TISS: For amounts >= 20,000,000 TZS
 * TIPS: For amounts < 20,000,000 TZS
 */
class ExternalFundsTransferService extends BasePaymentService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected string $privateKeyPath;
    
    const TISS_THRESHOLD = 20000000; // 20 million TZS
    const TIPS_SYSTEM = 'TIPS';
    const TISS_SYSTEM = 'TISS';

    public function __construct()
    {
        $this->baseUrl = config('services.nbc_payments.base_url');
        $this->apiKey = config('services.nbc_payments.api_key');
        $this->clientId = config('services.nbc_payments.client_id');
        $this->privateKeyPath = storage_path('app/keys/private_key.pem');
        
        $this->logInfo('EFT Service initialized', [
            'base_url' => $this->baseUrl,
            'client_id' => $this->clientId
        ]);
    }

    /**
     * Perform account lookup before transfer
     * 
     * @param string $accountNumber
     * @param string $bankCode
     * @param float $amount
     * @return array
     */
    public function lookupAccount(string $accountNumber, string $bankCode, float $amount = 1): array
    {
        $startTime = microtime(true);
        $this->logInfo("Starting external account lookup", [
            'account' => $accountNumber,
            'bank_code' => $bankCode,
            'amount' => $amount
        ]);

        try {
            $lookupRef = $this->generateReference('LOOKUP');
            $debitAccount = config('services.nbc_payments.saccos_account', '06012040022');
            
            $payload = [
                'serviceName' => 'TIPS_LOOKUP',
                'clientId' => $this->clientId,
                'clientRef' => $lookupRef,
                'identifierType' => 'BANK',
                'identifier' => $accountNumber,
                'destinationFsp' => $bankCode,
                'debitAccount' => $debitAccount,
                'debitAccountCurrency' => 'TZS',
                'debitAccountBranchCode' => substr($debitAccount, 0, 3),
                'amount' => (string)$amount,
                'debitAccountCategory' => 'BUSINESS'
            ];

            // Generate UUID for tracing
            $uuid = $this->generateUUID();
            
            // Use exact headers from working curl command
            $response = $this->sendRequest('/domestix/api/v2/lookup', $payload, [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Trace-Uuid' => 'domestix-' . $uuid,
                'x-api-key' => $this->apiKey,  // lowercase as in working curl
                'Client-Id' => $this->clientId,
                'Service-Name' => 'TIPS_LOOKUP'
                // Note: No Signature or Timestamp headers - they're not needed
            ]);
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response['success']) {
                // Extract data from the response
                $data = $response['data'];
                $body = $data['body'] ?? [];
                
                // Account name is in body.fullName
                $accountName = $body['fullName'] ?? '';
                
                $this->logInfo("External account lookup successful", [
                    'account' => $accountNumber,
                    'bank_code' => $bankCode,
                    'name' => $accountName,
                    'identifier_returned' => $body['identifier'] ?? 'N/A',
                    'engine_ref' => $data['engineRef'] ?? 'N/A',
                    'duration_ms' => $duration
                ]);

                return [
                    'success' => true,
                    'account_number' => $accountNumber,
                    'account_name' => $accountName,
                    'actual_identifier' => $body['identifier'] ?? $accountNumber,
                    'bank_code' => $bankCode,
                    'fsp_id' => $body['fspId'] ?? $bankCode,
                    'can_receive' => true,
                    'engine_ref' => $data['engineRef'] ?? null,
                    'message' => $data['message'] ?? '',
                    'status_code' => $data['statusCode'] ?? null,
                    'response_time' => $duration
                ];
            }

            throw new Exception($response['message'] ?? 'Account lookup failed');

        } catch (Exception $e) {
            $this->logError("External account lookup failed", [
                'account' => $accountNumber,
                'bank_code' => $bankCode,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'account_number' => $accountNumber,
                'bank_code' => $bankCode
            ];
        }
    }

    /**
     * Perform External Funds Transfer
     * Automatically routes to TISS or TIPS based on amount
     * 
     * @param array $transferData
     * @return array
     */
    public function transfer(array $transferData): array
    {
        $startTime = microtime(true);
        $reference = $this->generateReference('EFT');
        
        // Determine routing system based on amount
        $amount = floatval($transferData['amount']);
        $routingSystem = $amount >= self::TISS_THRESHOLD ? self::TISS_SYSTEM : self::TIPS_SYSTEM;
        
        $this->logInfo("Starting EFT transfer", [
            'reference' => $reference,
            'from_account' => $transferData['from_account'],
            'to_account' => $transferData['to_account'],
            'bank_code' => $transferData['bank_code'],
            'amount' => $amount,
            'routing_system' => $routingSystem
        ]);

        try {
            // Validate required fields
            $this->validateTransferData($transferData);

            // Step 1: Lookup source account (internal NBC account)
            $sourceAccount = $this->lookupInternalAccount($transferData['from_account']);
            if (!$sourceAccount['success']) {
                throw new Exception("Source account verification failed: " . $sourceAccount['error']);
            }

            // Step 2: Lookup destination account (external bank)
            $destAccount = $this->lookupAccount(
                $transferData['to_account'], 
                $transferData['bank_code'],
                floatval($transferData['amount'])
            );
            
            if (!$destAccount['success']) {
                throw new Exception("Destination account verification failed: " . $destAccount['error']);
            }
            
            // Store lookup reference if available
            if (isset($destAccount['engine_ref'])) {
                $transferData['lookup_ref'] = $destAccount['engine_ref'];
            }

            // Step 3: Route to appropriate system
            if ($routingSystem === self::TISS_SYSTEM) {
                $response = $this->executeTISSTransfer($reference, $transferData, $sourceAccount, $destAccount);
            } else {
                $response = $this->executeTIPSTransfer($reference, $transferData, $sourceAccount, $destAccount);
            }
            
            // Log full response for debugging
            $this->logDebug("Transfer Response", [
                'reference' => $reference,
                'routing' => $routingSystem,
                'success' => $response['success'] ?? false,
                'data' => $response['data'] ?? null,
                'message' => $response['message'] ?? null
            ]);
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($response['success']) {
                // Save transaction to database
                $this->saveTransaction([
                    'reference' => $reference,
                    'type' => 'EFT',
                    'routing_system' => $routingSystem,
                    'from_account' => $transferData['from_account'],
                    'to_account' => $transferData['to_account'],
                    'bank_code' => $transferData['bank_code'],
                    'amount' => $amount,
                    'currency' => 'TZS',
                    'status' => 'SUCCESS',
                    'response_code' => $response['data']['responseCode'] ?? '',
                    'response_message' => $response['data']['message'] ?? '',
                    'nbc_reference' => $response['data']['nbcReference'] ?? '',
                    'duration_ms' => $duration,
                    'sender_name' => $transferData['sender_name'] ?? null,
                    'sender_phone' => $transferData['sender_phone'] ?? null,
                    'sender_email' => $transferData['sender_email'] ?? null,
                    'narration' => $transferData['narration'] ?? null,
                    'initiated_by' => $transferData['initiated_by'] ?? 'SYSTEM',
                    'branch_code' => $transferData['branch_code'] ?? null,
                ]);

                $this->logInfo("EFT transfer successful", [
                    'reference' => $reference,
                    'routing_system' => $routingSystem,
                    'nbc_reference' => $response['data']['nbcReference'] ?? '',
                    'duration_ms' => $duration
                ]);

                return [
                    'success' => true,
                    'reference' => $reference,
                    'routing_system' => $routingSystem,
                    'nbc_reference' => $response['data']['nbcReference'] ?? '',
                    'message' => "Transfer completed successfully via {$routingSystem}",
                    'from_account' => $transferData['from_account'],
                    'to_account' => $transferData['to_account'],
                    'bank_code' => $transferData['bank_code'],
                    'amount' => $amount,
                    'timestamp' => Carbon::now()->toIso8601String(),
                    'response_time' => $duration
                ];
            }

            throw new Exception($response['message'] ?? 'Transfer failed');

        } catch (Exception $e) {
            $this->logError("EFT transfer failed", [
                'reference' => $reference,
                'routing_system' => $routingSystem,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            // Save failed transaction
            $this->saveTransaction([
                'reference' => $reference,
                'type' => 'EFT',
                'routing_system' => $routingSystem,
                'from_account' => $transferData['from_account'] ?? '',
                'to_account' => $transferData['to_account'] ?? '',
                'bank_code' => $transferData['bank_code'] ?? '',
                'amount' => $amount ?? 0,
                'currency' => 'TZS',
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'sender_name' => $transferData['sender_name'] ?? null,
                'sender_phone' => $transferData['sender_phone'] ?? null,
                'sender_email' => $transferData['sender_email'] ?? null,
                'narration' => $transferData['narration'] ?? null,
                'initiated_by' => $transferData['initiated_by'] ?? 'SYSTEM',
                'branch_code' => $transferData['branch_code'] ?? null,
            ]);

            return [
                'success' => false,
                'reference' => $reference,
                'routing_system' => $routingSystem,
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()->toIso8601String()
            ];
        }
    }

    /**
     * Execute TIPS transfer (amounts < 20M)
     */
    protected function executeTIPSTransfer(string $reference, array $transferData, array $sourceAccount, array $destAccount): array
    {
        // Generate lookup reference with timestamp like in the working example
        $timestamp = time();
        $lookupRef = 'LOOKUPREF' . $timestamp;
        $customerRef = 'CUSTOMERREF' . $timestamp;
        $initiatorId = (string)$timestamp;
        // Generate short clientRef (max 16 chars) - use last part of timestamp
        $shortRef = 'EFT' . substr((string)$timestamp, -10);
        
        $payload = [
            'serviceName' => 'TIPS_B2B_OUTWARD_TRANSFER',  // Use the correct service name from lookup response
            'clientId' => $this->clientId,
            'clientRef' => $shortRef,  // Use shortened reference (max 16 chars)
            'customerRef' => $customerRef,
            'lookupRef' => $lookupRef,
            'timestamp' => Carbon::now()->toIso8601String(),
            'callbackUrl' => config('services.nbc_payments.callback_url', 'http://localhost:90/post'),
            
            'payerDetails' => [
                'identifierType' => 'BANK',
                'identifier' => $transferData['from_account'],
                'phoneNumber' => $transferData['payer_phone'] ?? '255715000001',
                'initiatorId' => $initiatorId,
                'branchCode' => substr($transferData['from_account'], 0, 3),
                'fspId' => substr($transferData['from_account'], 0, 3),
                'fullName' => $sourceAccount['account_name'] ?? 'SACCOS Account',
                'accountCategory' => 'BUSINESS',
                'accountType' => 'BANK',
                'identity' => [
                    'type' => '',
                    'value' => ''
                ]
            ],
            
            'payeeDetails' => [
                'identifierType' => 'BANK',
                'identifier' => $destAccount['actual_identifier'] ?? $transferData['to_account'], // Use identifier from lookup
                'fspId' => $destAccount['fsp_id'] ?? substr($transferData['bank_code'], 0, 3), // Use FSP ID from lookup
                'destinationFsp' => $transferData['bank_code'],
                'fullName' => $destAccount['account_name'] ?? 'Beneficiary',
                'accountCategory' => 'PERSON',
                'accountType' => 'BANK',
                'identity' => [
                    'type' => '',
                    'value' => ''
                ]
            ],
            
            'transactionDetails' => [
                'debitAmount' => (string)$transferData['amount'],
                'debitCurrency' => 'TZS',
                'creditAmount' => (string)$transferData['amount'],
                'creditCurrency' => 'TZS',
                'productCode' => '',
                'isServiceChargeApplicable' => true,
                'serviceChargeBearer' => $transferData['charge_bearer'] ?? 'OUR'
            ],
            
            'remarks' => $transferData['narration'] ?? 'External Transfer via TIPS'
        ];
        
        // Log payload for debugging
        $this->logDebug("TIPS Transfer Payload", [
            'payload' => $payload
        ]);

        // Use exact headers from working curl command (including Signature)
        return $this->sendRequest('/domestix/api/v2/outgoing-transfers', $payload, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Trace-Uuid' => 'domestix-' . $this->generateUUID(),
            'Signature' => 'asdasdasdasd', // Dummy signature as per working example
            'x-api-key' => $this->apiKey  // lowercase as in working curl
        ]);
    }

    /**
     * Execute TISS transfer (amounts >= 20M)
     */
    protected function executeTISSTransfer(string $reference, array $transferData, array $sourceAccount, array $destAccount): array
    {
        $payload = [
            'serviceName' => 'TISS_TRANSFER',
            'clientId' => $this->clientId,
            'clientRef' => $reference,
            'debitAccount' => $transferData['from_account'],
            'debitAccountName' => $sourceAccount['account_name'] ?? '',
            'debitAccountCurrency' => 'TZS',
            'creditAccount' => $transferData['to_account'],
            'creditAccountName' => $destAccount['account_name'] ?? '',
            'creditBankCode' => $transferData['bank_code'],
            'creditBankName' => $destAccount['bank_name'] ?? '',
            'amount' => $transferData['amount'],
            'currency' => 'TZS',
            'narration' => $transferData['narration'] ?? 'External Transfer via TISS',
            'chargeBearer' => $transferData['charge_bearer'] ?? 'OUR',
            'purposeCode' => $transferData['purpose_code'] ?? 'CASH',
            'timestamp' => Carbon::now()->toIso8601String()
        ];

        $signature = $this->generateSignature($payload);
        
        return $this->sendRequest('/tiss/api/v2/transfer', $payload, [
            'Signature' => $signature,
            'Service-Name' => 'TISS_TRANSFER'
        ]);
    }

    /**
     * Lookup internal NBC account
     */
    protected function lookupInternalAccount(string $accountNumber): array
    {
        try {
            // For NBC internal accounts, we skip API verification
            // In production, this would validate against the core banking system
            
            // Basic validation
            if (empty($accountNumber)) {
                return [
                    'success' => false,
                    'error' => 'Account number is required'
                ];
            }
            
            // Check if it's a valid NBC account format (12 digits)
            if (!preg_match('/^\d{12}$/', $accountNumber)) {
                return [
                    'success' => false,
                    'error' => 'Invalid NBC account format'
                ];
            }
            
            // For known test accounts, return success
            $testAccounts = [
                '015103001490' => 'SACCOS Main Account',
                '011103033734' => 'NBC Test Account',
                '011201318462' => 'NBC Account Holder',
                '06012040022' => 'NBC Default Account'
            ];
            
            if (isset($testAccounts[$accountNumber])) {
                return [
                    'success' => true,
                    'account_number' => $accountNumber,
                    'account_name' => $testAccounts[$accountNumber],
                    'can_debit' => true
                ];
            }
            
            // For other accounts, assume valid if format is correct
            return [
                'success' => true,
                'account_number' => $accountNumber,
                'account_name' => 'NBC Account',
                'can_debit' => true
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate digital signature for payload
     */
    protected function generateSignature(array $payload): string
    {
        try {
            if (!file_exists($this->privateKeyPath)) {
                throw new Exception("Private key file not found");
            }

            $privateKeyContent = file_get_contents($this->privateKeyPath);
            $privateKey = openssl_pkey_get_private($privateKeyContent);

            if (!$privateKey) {
                throw new Exception("Failed to load private key");
            }

            $jsonPayload = json_encode($payload);
            openssl_sign($jsonPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            return base64_encode($signature);

        } catch (Exception $e) {
            $this->logError("Signature generation failed", ['error' => $e->getMessage()]);
            throw new Exception("Failed to generate digital signature: " . $e->getMessage());
        }
    }

    /**
     * Validate transfer data
     */
    protected function validateTransferData(array $data): void
    {
        $required = ['from_account', 'to_account', 'bank_code', 'amount'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception("Invalid amount");
        }

        if ($data['from_account'] === $data['to_account']) {
            throw new Exception("Source and destination accounts cannot be the same");
        }
    }

    /**
     * Send HTTP request to NBC API with retry logic
     */
    protected function sendRequest(string $endpoint, array $payload, array $additionalHeaders = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            // Generate UUID if not provided in additional headers
            if (!isset($additionalHeaders['X-Trace-Uuid'])) {
                $additionalHeaders['X-Trace-Uuid'] = 'domestix-' . $this->generateUUID();
            }
            
            // Use only the headers that are proven to work
            $headers = array_merge([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ], $additionalHeaders);
            
            // Check if this is for a production-ready FSP
            $destinationFsp = $payload['destinationFsp'] ?? '';
            if (!$this->isProductionReady($destinationFsp)) {
                Log::warning("Using non-production FSP: {$destinationFsp}");
            }
            
            // Prepare options
            $options = [
                'headers' => $headers,
                'json' => $payload
            ];
            
            // Optimize for NBC if needed
            $options = $this->optimizeForNBC($options);

            $this->logDebug("Sending EFT request with retry", [
                'url' => $url,
                'headers' => array_keys($headers),
                'payload_size' => strlen(json_encode($payload))
            ]);

            // Use retry logic from BasePaymentService
            $result = $this->sendRequestWithRetry('POST', $url, $options);
            
            if ($result['success']) {
                $responseData = $result['data'];
                
                $this->logDebug("EFT response received", [
                    'has_data' => !empty($responseData),
                    'attempts' => $result['attempts'] ?? 1
                ]);
                
                // Check the internal statusCode
                if (isset($responseData['statusCode']) && $responseData['statusCode'] == 600) {
                    return [
                        'success' => true,
                        'data' => $responseData
                    ];
                } elseif (isset($responseData['statusCode'])) {
                    // Other status codes are errors
                    return [
                        'success' => false,
                        'message' => $responseData['message'] ?? "NBC API returned status {$responseData['statusCode']}"
                    ];
                }
                // If no statusCode field, treat as success
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }
            
            // Retry failed - log full error details
            if (isset($result['data'])) {
                $this->logError("Transfer validation failed", [
                    'error_data' => $result['data']
                ]);
            }
            return [
                'success' => false,
                'message' => $result['error'] ?? 'Request failed after retries',
                'error_details' => $result['data'] ?? null
            ];

        } catch (Exception $e) {
            $this->logError("EFT request failed", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique reference
     */
    protected function generateReference(string $prefix = 'EFT'): string
    {
        // For lookups, use numeric timestamp only (NBC requirement for TIPS_LOOKUP)
        if ($prefix === 'LOOKUP' || $prefix === 'LOOKUPB') {
            return (string)time();
        }
        // For transfers, use alphanumeric reference
        return $prefix . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6));
    }
    
    /**
     * Convert string to alphanumeric only
     */
    protected function toAlphanumeric(string $str): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $str);
    }
    
    /**
     * Generate UUID
     */
    protected function generateUUID(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Save transaction to database
     */
    protected function saveTransaction(array $data): void
    {
        try {
            DB::table('transactions')->insert([
                // Core identifiers
                'transaction_uuid' => $this->generateUUID(),
                'reference' => $data['reference'],
                'external_reference' => $data['to_account'],
                'correlation_id' => $data['reference'],

                // Transaction details
                'type' => $data['type'],
                'transaction_category' => 'TRANSFER',
                'transaction_subcategory' => $data['routing_system'] ?? 'EFT',
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'TZS',
                'status' => $data['status'],
                'reconciliation_status' => 'UNRECONCILED',
                'service_name' => 'External Funds Transfer',

                // External system tracking
                'external_system' => 'NBC_GATEWAY',
                'external_system_version' => 'v2',
                'external_transaction_id' => $data['nbc_reference'] ?? $data['reference'],
                'external_status_code' => $data['response_code'] ?? null,
                'external_status_message' => $data['response_message'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'processing_time_ms' => isset($data['duration_ms']) ? round($data['duration_ms']) : null,

                // Source and channel info
                'source' => 'EFT_SERVICE',
                'channel_id' => $data['channel_id'] ?? 'EXTERNAL',
                'channel_code' => $data['routing_system'] ?? 'TIPS',

                // Account information
                'credit_account' => $data['to_account'],
                'credit_currency' => $data['currency'] ?? 'TZS',
                'lookup_account_number' => $data['from_account'],
                'lookup_bank_code' => $data['bank_code'] ?? null,

                // Payer information
                'payer_name' => $data['sender_name'] ?? null,
                'payer_phone' => $data['sender_phone'] ?? null,
                'payer_email' => $data['sender_email'] ?? null,

                // User and branch info
                'initiated_by' => $data['initiated_by'] ?? 'SYSTEM',
                'branch_code' => $data['branch_code'] ?? null,

                // Request tracking
                'client_ip' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,

                // Narration
                'narration' => $data['narration'] ?? sprintf('EFT from %s to %s via %s', 
                    $data['from_account'], 
                    $data['to_account'],
                    $data['routing_system'] ?? 'TIPS'
                ),
                'description' => $data['narration'] ?? sprintf('EFT: %s to %s (%s)', 
                    $data['from_account'], 
                    $data['to_account'],
                    $data['routing_system'] ?? 'TIPS'
                ),

                // Store full payloads
                'raw_payload' => json_encode($data),
                'external_request_payload' => json_encode($data),
                'metadata' => json_encode([
                    'from_account' => $data['from_account'],
                    'to_account' => $data['to_account'],
                    'bank_code' => $data['bank_code'] ?? null,
                    'routing_system' => $data['routing_system'] ?? null,
                    'nbc_reference' => $data['nbc_reference'] ?? null
                ]),

                // Timestamps
                'received_at' => now(),
                'initiated_at' => now(),
                'processed_at' => $data['status'] !== 'PENDING' ? now() : null,
                'completed_at' => $data['status'] === 'SUCCESS' ? now() : null,
                'failed_at' => $data['status'] === 'FAILED' ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),

                // Flags
                'is_manual' => false,
                'is_system_generated' => false,
                'requires_approval' => false,
            ]);
        } catch (Exception $e) {
            $this->logError("Failed to save transaction", [
                'error' => $e->getMessage(),
                'reference' => $data['reference']
            ]);
        }
    }

    /**
     * Log information
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::channel('payments')->info("[EFT] {$message}", $context);
    }

    /**
     * Log error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::channel('payments')->error("[EFT] {$message}", $context);
    }

    /**
     * Log debug information
     */
    protected function logDebug(string $message, array $context = []): void
    {
        Log::channel('payments')->debug("[EFT] {$message}", $context);
    }
}