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
    
    // Mobile Money Provider Codes
    const MOBILE_MONEY_PROVIDERS = [
        '503' => 'VODACOM', // Vodacom M-Pesa
        '504' => 'AIRTEL',  // Airtel Money
        '501' => 'MIC',     // MIC Tanzania (Zantel)
        '505' => 'TTCL',    // TTCL Pesa
        '506' => 'VIETTEL', // Halotel
        '502' => 'ZANTEL',  // Zanzibar Telecom
        '511' => 'AZAMPESA' // Azam Pesa
    ];

    public function __construct()
    {
        // Use Letshego TIPS API as default
        $this->baseUrl = config('services.letshego_payments.base_url', 'https://api-staging-cb.letshego.com/letshego-api-gw/tz-eftpay-tips/v2');
        $this->apiKey = config('services.letshego_payments.api_key', 'eyJ4NXQiOiJPREUzWTJaaE1UQmpNRE00WlRCbU1qQXlZemxpWVRJMllqUmhZVFpsT0dJeVptVXhOV0UzWVE9PSIsImtpZCI6ImdhdGV3YXlfY2VydGlmaWNhdGVfYWxpYXMiLCJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJhZG1pbkBjYXJib24uc3VwZXIiLCJhcHBsaWNhdGlvbiI6eyJvd25lciI6ImFkbWluIiwidGllclF1b3RhVHlwZSI6bnVsbCwidGllciI6IlVubGltaXRlZCIsIm5hbWUiOiJUWi1USVBTLVJBSUxTLVYyIiwiaWQiOjcsInV1aWQiOiJhZWU4ZmE5NC0xNzQ1LTQyM2UtOGZjYi1kMzMwMDkyZGZkYzMifSwiaXNzIjoiaHR0cHM6XC9cL3dzbzItYXBpbS1zdGFnaW5nLmxldHNoZWdvLmNvbTo5NDQ0XC9vYXV0aDJcL3Rva2VuIiwidGllckluZm8iOnsiVW5saW1pdGVkIjp7InRpZXJRdW90YVR5cGUiOiJyZXF1ZXN0Q291bnQiLCJncmFwaFFMTWF4Q29tcGxleGl0eSI6MCwiZ3JhcGhRTE1heERlcHRoIjowLCJzdG9wT25RdW90YVJlYWNoIjp0cnVlLCJzcGlrZUFycmVzdExpbWl0IjowLCJzcGlrZUFycmVzdFVuaXQiOm51bGx9fSwia2V5dHlwZSI6IlBST0RVQ1RJT04iLCJwZXJtaXR0ZWRSZWZlcmVyIjoiIiwic3Vic2NyaWJlZEFQSXMiOlt7InN1YnNjcmliZXJUZW5hbnREb21haW4iOiJjYXJib24uc3VwZXIiLCJuYW1lIjoidHotZWZ0cGF5LXRpcHMtcmFpbHMiLCJjb250ZXh0IjoiXC9sZXRzaGVnby1hcGktZ3dcL3R6LWVmdHBheS10aXBzXC92MiIsInB1Ymxpc2hlciI6ImFkbWluIiwidmVyc2lvbiI6InYyIiwic3Vic2NyaXB0aW9uVGllciI6IlVubGltaXRlZCJ9XSwidG9rZW5fdHlwZSI6ImFwaUtleSIsInBlcm1pdHRlZElQIjoiIiwiaWF0IjoxNzU3MzI5ODYwLCJqdGkiOiJjMDQ3NDY3Yy1kZmE1LTRhMTItOTU4ZC00ZGEyOGExMDFhYzcifQ==.ME2XheKIiUrdG5cOZ0INhmoNemXUL_hLS4Qx54TdszlKEHhcZf375GJIHQvEFTr-Lc7bXQNcWfyBzGcD7GfVo_eg8eECCfjyaTF_8JiGYpa2ImSPMKnhwU4O4b1YnuWpQC5bjpDDx5c9K2RnGurmPbxVf4nRSs4zUTTGk3xz2ktCD9hgAUOAdic2ubandIv9XnzZ7tjxufI5TcVNUlvu5Gxwg_BnIF9KFfVXkM-TSI0JnPBzBrgqbgPc9NXLCgDPQu2OaZWmxTnY1N4lkI29pNPetZ47zN0NHzHGQfeGJ9qOIKXeI4t_7mjajM-TCVfNOxhdDSouQUL2Hv5ba0HVCA==');
        $this->clientId = config('services.letshego_payments.consumer_id', '12123');
        $this->privateKeyPath = storage_path('app/keys/private_key.pem');
        
        $this->logInfo('EFT Service initialized with Letshego endpoints', [
            'base_url' => $this->baseUrl,
            'consumer_id' => $this->clientId
        ]);
    }

    /**
     * Perform account lookup before transfer using Letshego API
     * 
     * @param string $accountNumber
     * @param string $bankCode
     * @param float $amount
     * @return array
     */
    public function lookupAccount(string $accountNumber, string $bankCode, float $amount = 1): array
    {
        $startTime = microtime(true);
        $this->logInfo("Starting Letshego account lookup", [
            'account' => $accountNumber,
            'bank_code' => $bankCode,
            'amount' => $amount
        ]);

        try {
            // Handle mobile money vs bank account lookup
            $isMobileMoney = $this->isMobileMoneyProvider($bankCode);
            
            if ($isMobileMoney) {
                // Format phone number for mobile money
                $formattedNumber = $this->formatPhoneNumber($accountNumber, $bankCode);
                $endpoint = "/{$this->clientId}/outwards/accountenquiry/{$formattedNumber}";
                
                $this->logInfo("Mobile money lookup detected", [
                    'provider' => $this->getMobileMoneyProvider($bankCode),
                    'original_number' => $accountNumber,
                    'formatted_number' => $formattedNumber
                ]);
            } else {
                // Standard bank account lookup
                $endpoint = "/{$this->clientId}/outwards/accountenquiry/{$accountNumber}";
            }
            
            // Headers as per Letshego API
            $headers = [
                'Accept' => 'application/json',
                'fsp-destination' => $bankCode,
                'x-user-platform' => 'web',
                'apikey' => $this->apiKey
            ];
            
            // Add identifier type for mobile money
            if ($isMobileMoney) {
                $headers['identifier-type'] = 'MSISDN';
            }
            
            $response = $this->sendRequest($endpoint, [], $headers, 'GET');
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response['success']) {
                $data = $response['data'];
                
                // Extract account name from Letshego response
                $accountName = $data['fullName'] ?? $data['accountName'] ?? '';
                
                $this->logInfo("Letshego account lookup successful", [
                    'account' => $accountNumber,
                    'bank_code' => $bankCode,
                    'name' => $accountName,
                    'duration_ms' => $duration
                ]);

                return [
                    'success' => true,
                    'account_number' => $accountNumber,
                    'account_name' => $accountName,
                    'actual_identifier' => $accountNumber,
                    'bank_code' => $bankCode,
                    'fsp_id' => $bankCode,
                    'can_receive' => true,
                    'engine_ref' => null,
                    'message' => 'Account verified successfully',
                    'status_code' => 200,
                    'response_time' => $duration
                ];
            }

            throw new Exception($response['message'] ?? 'Account lookup failed');

        } catch (Exception $e) {
            $this->logError("Letshego account lookup failed", [
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
                    'letshego_reference' => $response['data']['payerRef'] ?? $response['data']['transactionId'] ?? '',
                    'duration_ms' => $duration,
                    'sender_name' => $transferData['sender_name'] ?? null,
                    'sender_phone' => $transferData['sender_phone'] ?? null,
                    'sender_email' => $transferData['sender_email'] ?? null,
                    'narration' => $transferData['narration'] ?? null,
                    'initiated_by' => $transferData['initiated_by'] ?? 'SYSTEM',
                    'branch_code' => $transferData['branch_code'] ?? null,
                ]);

                $this->logInfo("Letshego EFT transfer successful", [
                    'reference' => $reference,
                    'routing_system' => $routingSystem,
                    'letshego_reference' => $response['data']['payerRef'] ?? $response['data']['transactionId'] ?? '',
                    'duration_ms' => $duration
                ]);

                return [
                    'success' => true,
                    'reference' => $reference,
                    'routing_system' => $routingSystem,
                    'letshego_reference' => $response['data']['payerRef'] ?? $response['data']['transactionId'] ?? '',
                    'message' => "Transfer completed successfully via Letshego {$routingSystem}",
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
     * Execute TIPS transfer via Letshego (amounts < 20M)
     */
    protected function executeTIPSTransfer(string $reference, array $transferData, array $sourceAccount, array $destAccount): array
    {
        // Generate unique reference as per Letshego format
        $timestamp = time();
        $payerRef = '044-WK' . $timestamp;
        
        // Determine if destination is mobile money
        $isMobileMoney = $this->isMobileMoneyProvider($transferData['bank_code']);
        $identifierType = $this->getIdentifierType($transferData['bank_code']);
        $accountType = $this->getAccountType($transferData['bank_code']);
        
        // Format identifier for mobile money
        $identifier = $isMobileMoney 
            ? $this->formatPhoneNumber($transferData['to_account'], $transferData['bank_code'])
            : $transferData['to_account'];
        
        $payload = [
            'payee' => [
                'identifierType' => $identifierType,
                'identifier' => $identifier,
                'fspId' => $transferData['bank_code'],
                'fullName' => $destAccount['account_name'] ?? 'Beneficiary',
                'accountCategory' => 'PERSON',
                'accountType' => $accountType,
                'identity' => [
                    'type' => $isMobileMoney ? '' : 'NIN',
                    'value' => ''
                ],
                'additionalInfo' => null
            ],
            'payer' => [
                'identifierType' => 'BANK',
                'identifier' => $transferData['from_account'],
                'fspId' => '044', // Letshego's FSP ID
                'fullName' => $sourceAccount['account_name'] ?? 'Letshego Account',
                'accountCategory' => 'BUSINESS',
                'accountType' => 'BANK',
                'identity' => [
                    'type' => 'NIN',
                    'value' => ''
                ]
            ],
            'amount' => [
                'amount' => (string)$transferData['amount'],
                'currency' => 'TZS'
            ],
            'endUserFee' => [
                'amount' => '130',
                'currency' => 'TZS'
            ],
            'transactionType' => [
                'scenario' => 'TRANSFER',
                'initiator' => 'PAYER',
                'initiatorType' => 'CONSUMER',
                'subScenario' => null
            ],
            'description' => $transferData['narration'] ?? 'Transfer via Letshego',
            'payerRef' => $payerRef,
            'callbackUrl' => config('services.letshego_payments.callback_url', 'http://abc.com/xxxxxx')
        ];
        
        // Log payload for debugging
        $this->logDebug("Letshego TIPS Transfer Payload", [
            'payload' => $payload
        ]);

        // Use Letshego transfer endpoint
        // POST /{{consumer_id}}/outwards/transfers
        $endpoint = "/{$this->clientId}/outwards/transfers";
        
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-user-platform' => 'web',
            'apikey' => $this->apiKey
        ];
        
        return $this->sendRequest($endpoint, $payload, $headers);
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
     * Lookup internal Letshego account
     */
    protected function lookupInternalAccount(string $accountNumber): array
    {
        try {
            // For Letshego internal accounts, we skip API verification
            // In production, this would validate against the core banking system
            
            // Basic validation
            if (empty($accountNumber)) {
                return [
                    'success' => false,
                    'error' => 'Account number is required'
                ];
            }
            
            // Check if it's a valid Letshego account format
            if (!preg_match('/^\d{11,12}$/', $accountNumber)) {
                return [
                    'success' => false,
                    'error' => 'Invalid Letshego account format'
                ];
            }
            
            // For known test accounts, return success
            $testAccounts = [
                '21710176786' => 'Letshego Main Account',
                '21710176787' => 'Letshego Test Account',
                '21710176788' => 'Letshego Business Account',
                '21710176789' => 'Letshego Default Account'
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
                'account_name' => 'Letshego Account',
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
     * Send HTTP request to Letshego API with retry logic
     */
    protected function sendRequest(string $endpoint, array $payload = [], array $additionalHeaders = [], string $method = 'POST'): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            // Use Letshego headers
            $headers = array_merge([
                'Accept' => 'application/json'
            ], $additionalHeaders);
            
            // Add Content-Type for POST requests
            if ($method === 'POST') {
                $headers['Content-Type'] = 'application/json';
            }
            
            // Prepare options
            $options = [
                'headers' => $headers
            ];
            
            // Add payload for POST requests
            if ($method === 'POST' && !empty($payload)) {
                $options['json'] = $payload;
            }

            $this->logDebug("Sending Letshego EFT request", [
                'url' => $url,
                'method' => $method,
                'headers' => array_keys($headers),
                'has_payload' => !empty($payload)
            ]);

            // Use retry logic from BasePaymentService
            $result = $this->sendRequestWithRetry($method, $url, $options);
            
            if ($result['success']) {
                $responseData = $result['data'];
                
                $this->logDebug("Letshego EFT response received", [
                    'has_data' => !empty($responseData),
                    'attempts' => $result['attempts'] ?? 1
                ]);
                
                // Letshego API success handling
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }
            
            // Request failed - log error details
            if (isset($result['data'])) {
                $this->logError("Letshego transfer failed", [
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
                'external_system' => 'LETSHEGO_GATEWAY',
                'external_system_version' => 'v2',
                'external_transaction_id' => $data['letshego_reference'] ?? $data['reference'],
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
     * Check if bank code is a mobile money provider
     */
    protected function isMobileMoneyProvider(string $bankCode): bool
    {
        return array_key_exists($bankCode, self::MOBILE_MONEY_PROVIDERS);
    }
    
    /**
     * Get mobile money provider name
     */
    protected function getMobileMoneyProvider(string $bankCode): ?string
    {
        return self::MOBILE_MONEY_PROVIDERS[$bankCode] ?? null;
    }
    
    /**
     * Format phone number for mobile money
     * Converts various formats to standard MSISDN format
     */
    protected function formatPhoneNumber(string $phoneNumber, string $bankCode): string
    {
        // Remove any non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Handle different input formats
        if (str_starts_with($clean, '255')) {
            // Already has country code: 255749908161
            return $clean;
        } elseif (str_starts_with($clean, '0')) {
            // Starts with 0: 0749908161 -> 255749908161
            return '255' . substr($clean, 1);
        } elseif (strlen($clean) === 9) {
            // 9 digits without prefix: 749908161 -> 255749908161
            return '255' . $clean;
        }
        
        // Return as-is if we can't determine format
        return $clean;
    }
    
    /**
     * Get identifier type based on bank code
     */
    protected function getIdentifierType(string $bankCode): string
    {
        return $this->isMobileMoneyProvider($bankCode) ? 'MSISDN' : 'BANK';
    }
    
    /**
     * Get account type based on bank code  
     */
    protected function getAccountType(string $bankCode): string
    {
        return $this->isMobileMoneyProvider($bankCode) ? 'WALLET' : 'BANK';
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

    /**
     * Get list of supported institutions from Letshego
     * 
     * @return array
     */
    public function getInstitutionList(): array
    {
        try {
            $endpoint = "/{$this->clientId}/outwards/institution";
            
            $headers = [
                'Accept' => 'application/json',
                'apikey' => $this->apiKey
            ];
            
            $response = $this->sendRequest($endpoint, [], $headers, 'GET');
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'institutions' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => $response['message'] ?? 'Failed to get institution list'
            ];
            
        } catch (Exception $e) {
            $this->logError("Failed to get institution list", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get transaction status from Letshego
     * 
     * @param string $payerRef
     * @return array
     */
    public function getTransactionStatus(string $payerRef): array
    {
        try {
            $endpoint = "/{$this->clientId}/outwards/status/{$payerRef}";
            
            $headers = [
                'Accept' => 'application/json',
                'apikey' => $this->apiKey
            ];
            
            $response = $this->sendRequest($endpoint, [], $headers, 'GET');
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'status' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => $response['message'] ?? 'Failed to get transaction status'
            ];
            
        } catch (Exception $e) {
            $this->logError("Failed to get transaction status", [
                'payer_ref' => $payerRef,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Reverse a transaction via Letshego
     * 
     * @param string $payerRef
     * @param string $reversalReason
     * @return array
     */
    public function reverseTransaction(string $payerRef, string $reversalReason): array
    {
        try {
            $endpoint = "/{$this->clientId}/outwards/transfers/reversal";
            
            $payload = [
                'payerRef' => $payerRef,
                'reversalReason' => $reversalReason
            ];
            
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'apikey' => $this->apiKey
            ];
            
            $response = $this->sendRequest($endpoint, $payload, $headers);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'reversal' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => $response['message'] ?? 'Failed to reverse transaction'
            ];
            
        } catch (Exception $e) {
            $this->logError("Failed to reverse transaction", [
                'payer_ref' => $payerRef,
                'reversal_reason' => $reversalReason,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}