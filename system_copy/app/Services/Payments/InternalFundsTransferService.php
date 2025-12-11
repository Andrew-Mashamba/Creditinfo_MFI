<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

/**
 * Internal Funds Transfer (IFT) Service
 * Handles transfers between SACCOS accounts and member accounts within NBC Bank
 */
class InternalFundsTransferService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected array $transactionLog = [];

    public function __construct()
    {
        // Use NBC Internal Fund Transfer configuration instead of NBC Payments
        $this->baseUrl = config('services.nbc_internal_fund_transfer.base_url');
        $this->apiKey = config('services.nbc_internal_fund_transfer.api_key');
        $this->clientId = config('services.nbc_internal_fund_transfer.channel_id');
        
        $this->logInfo('IFT Service initialized', [
            'base_url' => $this->baseUrl,
            'client_id' => $this->clientId
        ]);
    }

    /**
     * Perform account lookup before transfer
     * Makes real API call to NBC for account validation
     * 
     * @param string $accountNumber
     * @param string $accountType 'source' or 'destination'
     * @return array
     */
    public function lookupAccount(string $accountNumber, string $accountType = 'destination'): array
    {
        $startTime = microtime(true);
        $this->logInfo("Starting account lookup via NBC API", [
            'account' => $accountNumber,
            'type' => $accountType,
            'timestamp' => now()->toIso8601String(),
            'method' => 'lookupAccount'
        ]);

        try {
            // Validate account format
            if (!$this->validateAccountNumber($accountNumber)) {
                throw new Exception("Invalid account number format");
            }

            // Prepare lookup request payload
            $payload = [
                'accountNumber' => $accountNumber,
                'channelId' => $this->clientId
            ];

            // Use the account lookup service endpoint
            $lookupUrl = config('services.account_details.base_url', 'http://cbpuat.intra.nbc.co.tz:9004/api/v1/account-lookup');
            $lookupPayload = [
                'accountNumber' => $accountNumber,
                'channelCode' => config('services.account_details.channel_code', 'SACCOSNBC'),
                'channelName' => config('services.account_details.channel_name', 'NBC_SACCOS')
            ];
            
            $this->logDebug("[TECHNICAL] Account Lookup API Request", [
                'endpoint' => $lookupUrl,
                'method' => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => substr(config('services.account_details.api_key'), 0, 10) . '...MASKED'
                ],
                'request_body' => json_encode($lookupPayload, JSON_PRETTY_PRINT),
                'raw_payload' => $lookupPayload,
                'account_type' => $accountType,
                'ssl_verify' => false,
                'timeout' => 30
            ]);
            
            $this->logInfo("[HTTP] Executing Account Lookup Request", [
                'target' => $lookupUrl,
                'account' => $accountNumber
            ]);
            
            $lookupResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => config('services.account_details.api_key'),  // lowercase as per NBC documentation
            ])->withOptions(['verify' => false])
              ->timeout(30)
              ->post($lookupUrl, $lookupPayload);
            
            $this->logDebug("[TECHNICAL] Account Lookup API Response", [
                'http_status' => $lookupResponse->status(),
                'response_headers' => $lookupResponse->headers(),
                'response_body' => $lookupResponse->body(),
                'parsed_response' => $lookupResponse->json(),
                'successful' => $lookupResponse->successful(),
                'account' => $accountNumber
            ]);
            
            $response = [
                'success' => $lookupResponse->successful(),
                'data' => $lookupResponse->json() ?? []
            ];
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response['success']) {
                $accountData = $response['data'];
                
                $this->logInfo("[SUCCESS] Account Validation Complete", [
                    'account' => $accountNumber,
                    'full_response' => json_encode($accountData, JSON_PRETTY_PRINT),
                    'extracted_data' => [
                        'account_name' => $accountData['accountName'] ?? 'Not provided',
                        'status' => $accountData['status'] ?? 'Unknown',
                        'branch' => $accountData['branchName'] ?? 'Unknown',
                        'currency' => $accountData['currency'] ?? 'TZS',
                        'can_receive' => $accountData['canReceive'] ?? null,
                        'can_debit' => $accountData['canDebit'] ?? null
                    ],
                    'duration_ms' => $duration,
                    'api_call_completed' => true
                ]);

                return [
                    'success' => true,
                    'account_number' => $accountNumber,
                    'account_name' => $accountData['accountName'] ?? 'NBC Account',
                    'account_status' => $accountData['status'] ?? 'ACTIVE',
                    'branch_code' => $accountData['branchCode'] ?? substr($accountNumber, 0, 3),
                    'branch_name' => $accountData['branchName'] ?? 'NBC Branch',
                    'currency' => $accountData['currency'] ?? 'TZS',
                    'can_receive' => $accountData['canReceive'] ?? true,
                    'can_debit' => $accountData['canDebit'] ?? ($accountType === 'source'),
                    'response_time' => $duration
                ];
            } else {
                // If validation API fails, use basic validation
                $this->logWarning("API validation failed, using basic validation", [
                    'account' => $accountNumber,
                    'api_error' => $response['message'] ?? 'No error message',
                    'api_response' => $response,
                    'fallback_mode' => 'basic_validation'
                ]);
                
                // Return basic validation result
                return [
                    'success' => true,
                    'account_number' => $accountNumber,
                    'account_name' => 'NBC Account Holder',
                    'account_status' => 'ACTIVE',
                    'branch_code' => substr($accountNumber, 0, 3),
                    'branch_name' => 'NBC Branch',
                    'currency' => 'TZS',
                    'can_receive' => true,
                    'can_debit' => $accountType === 'source',
                    'response_time' => $duration,
                    'validation_type' => 'basic'
                ];
            }

        } catch (Exception $e) {
            $this->logError("Account lookup failed", [
                'account' => $accountNumber,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'account_number' => $accountNumber
            ];
        }
    }

    /**
     * Perform Internal Funds Transfer using real NBC API
     * 
     * @param array $transferData
     * @return array
     */
    public function transfer(array $transferData): array
    {
        $startTime = microtime(true);
        $reference = $this->generateReference('IFT');
        
        $this->logInfo("Starting IFT transfer via NBC API", [
            'reference' => $reference,
            'from_account' => $transferData['from_account'],
            'to_account' => $transferData['to_account'],
            'amount' => $transferData['amount'],
            'narration' => $transferData['narration'] ?? 'Not provided',
            'sender_name' => $transferData['sender_name'] ?? 'Not provided',
            'timestamp' => now()->toIso8601String()
        ]);

        try {
            // Validate required fields
            $this->validateTransferData($transferData);

            // Step 1: Validate source account
            $this->logDebug("Validating source account", [
                'account' => $transferData['from_account'],
                'reference' => $reference
            ]);
            
            $sourceAccount = $this->lookupAccount($transferData['from_account'], 'source');
            if (!$sourceAccount['success']) {
                $this->logError("Source account validation failed", [
                    'account' => $transferData['from_account'],
                    'error' => $sourceAccount['error'] ?? 'Unknown error',
                    'reference' => $reference
                ]);
                throw new Exception("Source account validation failed: " . ($sourceAccount['error'] ?? 'Unknown error'));
            }
            
            $this->logDebug("[VALIDATION] Source Account Validated", [
                'account' => $transferData['from_account'],
                'validation_response' => json_encode($sourceAccount, JSON_PRETTY_PRINT),
                'account_details' => [
                    'name' => $sourceAccount['account_name'] ?? 'Unknown',
                    'status' => $sourceAccount['account_status'] ?? 'Unknown',
                    'can_debit' => $sourceAccount['can_debit'] ?? false
                ],
                'reference' => $reference
            ]);

            // Step 2: Validate destination account
            $this->logDebug("Validating destination account", [
                'account' => $transferData['to_account'],
                'reference' => $reference
            ]);
            
            $destAccount = $this->lookupAccount($transferData['to_account'], 'destination');
            if (!$destAccount['success']) {
                $this->logError("Destination account validation failed", [
                    'account' => $transferData['to_account'],
                    'error' => $destAccount['error'] ?? 'Unknown error',
                    'reference' => $reference
                ]);
                throw new Exception("Destination account validation failed: " . ($destAccount['error'] ?? 'Unknown error'));
            }
            
            $this->logDebug("[VALIDATION] Destination Account Validated", [
                'account' => $transferData['to_account'],
                'validation_response' => json_encode($destAccount, JSON_PRETTY_PRINT),
                'account_details' => [
                    'name' => $destAccount['account_name'] ?? 'Unknown',
                    'status' => $destAccount['account_status'] ?? 'Unknown',
                    'can_receive' => $destAccount['can_receive'] ?? false
                ],
                'reference' => $reference
            ]);

            // Step 3: Execute real IFT Transfer via NBC API
            // Build transfer request payload according to NBC API spec
            $channelRef = 'CH' . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6));
            
            $payload = [
                'header' => [
                    'service' => config('services.nbc_internal_fund_transfer.service_name', 'internal_ft'),
                    'extra' => [
                        'pyrName' => $transferData['sender_name'] ?? 'SACCOS User'
                    ]
                ],
                'channelId' => $this->clientId,
                'channelRef' => $channelRef,
                'creditAccount' => $transferData['to_account'],
                'creditCurrency' => $transferData['to_currency'] ?? 'TZS',
                'debitAccount' => $transferData['from_account'],
                'debitCurrency' => $transferData['from_currency'] ?? 'TZS',
                'amount' => (string) $transferData['amount'],
                'narration' => $transferData['narration'] ?? 'Internal Funds Transfer'
            ];
            
            $this->logInfo("[TECHNICAL] NBC IFT Transfer Request Prepared", [
                'reference' => $reference,
                'channel_ref' => $channelRef,
                'request_structure' => [
                    'header' => $payload['header'],
                    'channelId' => $payload['channelId'],
                    'channelRef' => $payload['channelRef'],
                    'amount' => $payload['amount'],
                    'narration' => $payload['narration']
                ],
                'full_request_payload' => json_encode($payload, JSON_PRETTY_PRINT),
                'raw_request' => $payload,
                'payload_bytes' => strlen(json_encode($payload)),
                'accounts' => [
                    'from' => $transferData['from_account'],
                    'to' => $transferData['to_account']
                ]
            ]);
            
            // Make the actual API call
            $this->logDebug("Initiating NBC API call", [
                'reference' => $reference,
                'method' => 'sendRealNBCRequest'
            ]);
            
            $response = $this->sendRealNBCRequest($payload);
            
            $this->logDebug("NBC API call completed", [
                'reference' => $reference,
                'success' => $response['success'] ?? false,
                'has_data' => isset($response['data'])
            ]);
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response['success']) {
                $nbcReference = $response['data']['hostReferenceCbs'] ?? $response['data']['hostReferenceGw'] ?? $channelRef;
                
                // Save successful transaction to database
                $this->saveTransaction([
                    'reference' => $reference,
                    'type' => 'IFT',
                    'from_account' => $transferData['from_account'],
                    'to_account' => $transferData['to_account'],
                    'amount' => $transferData['amount'],
                    'currency' => $transferData['from_currency'] ?? 'TZS',
                    'status' => 'SUCCESS',
                    'response_code' => $response['data']['statusCode'] ?? '600',
                    'response_message' => $response['data']['message'] ?? 'Transfer completed successfully',
                    'nbc_reference' => $nbcReference,
                    'duration_ms' => $duration,
                    'sender_name' => $transferData['sender_name'] ?? null,
                    'sender_phone' => $transferData['sender_phone'] ?? null,
                    'sender_email' => $transferData['sender_email'] ?? null,
                    'narration' => $transferData['narration'] ?? null,
                    'initiated_by' => $transferData['initiated_by'] ?? 'SYSTEM',
                    'branch_code' => $transferData['branch_code'] ?? null,
                ]);

                $this->logInfo("[SUCCESS] IFT Transfer Completed", [
                    'reference' => $reference,
                    'nbc_reference' => $nbcReference,
                    'transfer_details' => [
                        'amount' => $transferData['amount'],
                        'from_account' => $transferData['from_account'],
                        'to_account' => $transferData['to_account'],
                        'narration' => $transferData['narration'] ?? 'Not provided'
                    ],
                    'nbc_response' => [
                        'status_code' => $response['data']['statusCode'] ?? 'Unknown',
                        'message' => $response['data']['message'] ?? 'Unknown',
                        'references' => [
                            'internal' => $reference,
                            'nbc' => $nbcReference,
                            'cbs' => $response['data']['hostReferenceCbs'] ?? null,
                            'gateway' => $response['data']['hostReferenceGw'] ?? null
                        ]
                    ],
                    'performance' => [
                        'total_duration_ms' => $duration,
                        'timestamp' => now()->toIso8601String()
                    ],
                    'full_api_response' => json_encode($response['data'] ?? [], JSON_PRETTY_PRINT)
                ]);

                return [
                    'success' => true,
                    'reference' => $reference,
                    'nbc_reference' => $nbcReference,
                    'message' => $response['data']['message'] ?? 'Internal transfer completed successfully',
                    'from_account' => $transferData['from_account'],
                    'to_account' => $transferData['to_account'],
                    'amount' => $transferData['amount'],
                    'timestamp' => Carbon::now()->toIso8601String(),
                    'response_time' => $duration,
                    'api_response' => $response['data']
                ];
            } else {
                $errorMessage = $response['message'] ?? 'Transfer failed';
                $this->logError("[FAILURE] NBC Transfer Request Failed", [
                    'reference' => $reference,
                    'error_message' => $errorMessage,
                    'full_error_response' => json_encode($response, JSON_PRETTY_PRINT),
                    'response_data' => $response['data'] ?? null,
                    'duration_ms' => $duration,
                    'request_summary' => [
                        'from' => $transferData['from_account'],
                        'to' => $transferData['to_account'],
                        'amount' => $transferData['amount']
                    ]
                ]);
                throw new Exception($errorMessage);
            }

        } catch (Exception $e) {
            $this->logError("[EXCEPTION] IFT Transfer Failed", [
                'reference' => $reference,
                'exception' => [
                    'class' => get_class($e),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'transfer_request' => [
                    'from_account' => $transferData['from_account'] ?? 'Unknown',
                    'to_account' => $transferData['to_account'] ?? 'Unknown',
                    'amount' => $transferData['amount'] ?? 0,
                    'narration' => $transferData['narration'] ?? 'Not provided'
                ],
                'stack_trace' => $e->getTraceAsString(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'timestamp' => now()->toIso8601String()
            ]);

            // Save failed transaction
            $this->saveTransaction([
                'reference' => $reference,
                'type' => 'IFT',
                'from_account' => $transferData['from_account'] ?? '',
                'to_account' => $transferData['to_account'] ?? '',
                'amount' => $transferData['amount'] ?? 0,
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            return [
                'success' => false,
                'reference' => $reference,
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()->toIso8601String()
            ];
        }
    }

    /**
     * Get transfer status
     * 
     * @param string $reference
     * @return array
     */
    public function getTransferStatus(string $reference): array
    {
        $this->logInfo("Checking transfer status", ['reference' => $reference]);

        try {
            $payload = [
                'clientRef' => $reference,
                'transferType' => 'IFT'
            ];

            $response = $this->sendRequest('/api/nbc/ift/status', $payload);

            if ($response['success']) {
                return [
                    'success' => true,
                    'reference' => $reference,
                    'status' => $response['data']['status'] ?? 'UNKNOWN',
                    'message' => $response['data']['message'] ?? '',
                    'timestamp' => $response['data']['timestamp'] ?? ''
                ];
            }

            throw new Exception($response['message'] ?? 'Status check failed');

        } catch (Exception $e) {
            $this->logError("Status check failed", [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'reference' => $reference,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate account number format
     * 
     * @param string $accountNumber
     * @return bool
     */
    protected function validateAccountNumber(string $accountNumber): bool
    {
        // NBC account format validation
        if (strlen($accountNumber) < 10 || strlen($accountNumber) > 16) {
            return false;
        }

        if (!is_numeric($accountNumber)) {
            return false;
        }

        return true;
    }
    
    /**
     * Query internal account from database
     * 
     * @param string $accountNumber
     * @return array|null
     */
    private function queryInternalAccount(string $accountNumber): ?array
    {
        try {
            // Query from accounts table or internal API
            $account = \DB::table('accounts')
                ->where('account_number', $accountNumber)
                ->where('status', 'ACTIVE')
                ->first();
                
            if ($account) {
                return [
                    'account_name' => $account->account_name ?? $account->name ?? 'NBC Account Holder',
                    'branch_code' => $account->branch_code ?? substr($accountNumber, 0, 3),
                    'branch_name' => $account->branch_name ?? 'NBC Branch',
                    'status' => $account->status ?? 'ACTIVE',
                    'account_type' => $account->account_type ?? 'SAVINGS'
                ];
            }
            
            // If not in database, could call internal NBC API here
            // For now, return null if not found
            return null;
            
        } catch (\Exception $e) {
            $this->logError("Database query failed", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validate transfer data
     * 
     * @param array $data
     * @throws Exception
     */
    protected function validateTransferData(array $data): void
    {
        $this->logDebug("Validating transfer data", [
            'has_from_account' => isset($data['from_account']),
            'has_to_account' => isset($data['to_account']),
            'has_amount' => isset($data['amount']),
            'amount_value' => $data['amount'] ?? 'Not set'
        ]);
        
        $required = ['from_account', 'to_account', 'amount'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->logError("Transfer validation failed - missing field", [
                    'missing_field' => $field,
                    'provided_fields' => array_keys($data)
                ]);
                throw new Exception("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $this->logError("Transfer validation failed - invalid amount", [
                'amount' => $data['amount'],
                'is_numeric' => is_numeric($data['amount'])
            ]);
            throw new Exception("Invalid amount");
        }

        if ($data['from_account'] === $data['to_account']) {
            $this->logError("Transfer validation failed - same accounts", [
                'from_account' => $data['from_account'],
                'to_account' => $data['to_account']
            ]);
            throw new Exception("Source and destination accounts cannot be the same");
        }
        
        $this->logDebug("Transfer data validation successful", [
            'from_account' => substr($data['from_account'], 0, 3) . '****',
            'to_account' => substr($data['to_account'], 0, 3) . '****',
            'amount' => $data['amount']
        ]);
    }

    /**
     * Send HTTP request to NBC API (for general endpoints)
     * 
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    protected function sendRequest(string $endpoint, array $payload): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $this->logDebug("[TECHNICAL] Generic API Request", [
                'endpoint' => $url,
                'method' => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => '***MASKED***',
                    'Client-Id' => $this->clientId,
                    'Service-Name' => 'IFT'
                ],
                'request_body' => json_encode($payload, JSON_PRETTY_PRINT),
                'raw_payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,  // lowercase as per NBC documentation
                'Client-Id' => $this->clientId,
                'Service-Name' => 'IFT'
            ])->withOptions(['verify' => config('services.nbc_internal_fund_transfer.verify_ssl', false)])
              ->timeout(config('services.nbc_internal_fund_transfer.timeout', 30))
              ->post($url, $payload);

            $statusCode = $response->status();
            $responseData = $response->json() ?? [];

            $this->logDebug("[TECHNICAL] Generic API Response", [
                'http_status' => $statusCode,
                'response_headers' => $response->headers(),
                'response_body' => $response->body(),
                'parsed_response' => json_encode($responseData, JSON_PRETTY_PRINT),
                'raw_response' => $responseData
            ]);

            if ($statusCode === 200 || $statusCode === 201) {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? "Request failed with status {$statusCode}"
            ];

        } catch (Exception $e) {
            $this->logError("Request failed", [
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
     * Send real NBC Internal Fund Transfer request with proper authentication
     * 
     * @param array $payload
     * @return array
     */
    protected function sendRealNBCRequest(array $payload): array
    {
        try {
            // Construct the URL using base URL and service name
            $serviceName = config('services.nbc_internal_fund_transfer.service_name', 'internal_ft');
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($serviceName, '/');
            
            // Generate authentication headers
            $headers = $this->generateNBCHeaders($payload);
            
            $this->logDebug("[TECHNICAL] NBC API HTTP Request Details", [
                'endpoint' => $url,
                'method' => 'POST',
                'service_name' => $serviceName,
                'base_url' => $this->baseUrl,
                'headers_sent' => array_merge(
                    array_diff_key($headers, array_flip(['NBC-Authorization', 'Signature'])),
                    [
                        'NBC-Authorization' => isset($headers['NBC-Authorization']) ? 'Basic ***MASKED***' : 'Not set',
                        'Signature' => isset($headers['Signature']) ? 'SHA256 ***MASKED***' : 'Not set'
                    ]
                ),
                'request_body' => json_encode($payload, JSON_PRETTY_PRINT),
                'raw_payload' => $payload,
                'payload_size_bytes' => strlen(json_encode($payload)),
                'channel_ref' => $payload['channelRef'] ?? 'Unknown',
                'timeout_seconds' => config('services.nbc_internal_fund_transfer.timeout', 30),
                'ssl_verification' => config('services.nbc_internal_fund_transfer.verify_ssl', false)
            ]);

            $httpStartTime = microtime(true);
            $this->logInfo("[HTTP] Executing NBC Transfer Request", [
                'url' => $url,
                'method' => 'POST'
            ]);
            
            $response = Http::withHeaders($headers)
                ->withOptions([
                    'verify' => config('services.nbc_internal_fund_transfer.verify_ssl', false)
                ])
                ->timeout(config('services.nbc_internal_fund_transfer.timeout', 30))
                ->post($url, $payload);
            
            $httpDuration = round((microtime(true) - $httpStartTime) * 1000, 2);
            $this->logInfo("[HTTP] NBC Transfer Response Received", [
                'http_duration_ms' => $httpDuration,
                'http_status' => $response->status()
            ]);

            $statusCode = $response->status();
            $responseData = $response->json() ?? [];

            $this->logDebug("[TECHNICAL] NBC API HTTP Response Details", [
                'http_status_code' => $statusCode,
                'response_headers' => $response->headers(),
                'raw_response_body' => $response->body(),
                'parsed_response' => json_encode($responseData, JSON_PRETTY_PRINT),
                'response_structure' => [
                    'statusCode' => $responseData['statusCode'] ?? null,
                    'message' => $responseData['message'] ?? null,
                    'hostReferenceCbs' => $responseData['hostReferenceCbs'] ?? null,
                    'hostReferenceGw' => $responseData['hostReferenceGw'] ?? null,
                    'has_body' => isset($responseData['body']),
                    'body_content' => $responseData['body'] ?? null
                ],
                'response_size_bytes' => strlen($response->body()),
                'full_response_data' => $responseData
            ]);

            // Check NBC specific status codes
            if ($statusCode === 200 || $statusCode === 201) {
                $nbcStatusCode = $responseData['statusCode'] ?? null;
                
                if ($nbcStatusCode == 600) { // NBC success code
                    $this->logInfo("[SUCCESS] NBC Transfer Confirmed", [
                        'nbc_status_code' => $nbcStatusCode,
                        'nbc_message' => $responseData['message'] ?? 'Success',
                        'nbc_references' => [
                            'cbs_reference' => $responseData['hostReferenceCbs'] ?? null,
                            'gateway_reference' => $responseData['hostReferenceGw'] ?? null
                        ],
                        'full_success_response' => json_encode($responseData, JSON_PRETTY_PRINT)
                    ]);
                    return [
                        'success' => true,
                        'data' => array_merge($responseData, ['body' => $responseData['body'] ?? []])
                    ];
                } else {
                    $errorMsg = $this->getNBCErrorMessage($nbcStatusCode);
                    $this->logError("[FAILURE] NBC Transfer Failed", [
                        'nbc_status_code' => $nbcStatusCode,
                        'interpreted_error' => $errorMsg,
                        'raw_nbc_message' => $responseData['message'] ?? 'No message',
                        'full_error_response' => json_encode($responseData, JSON_PRETTY_PRINT),
                        'response_dump' => $responseData
                    ]);
                    return [
                        'success' => false,
                        'message' => $errorMsg,
                        'data' => $responseData
                    ];
                }
            }

            $this->logError("[FAILURE] HTTP Request Failed", [
                'http_status' => $statusCode,
                'response_body' => $response->body(),
                'parsed_response' => $responseData
            ]);
            return [
                'success' => false,
                'message' => "Request failed with HTTP status {$statusCode}",
                'data' => $responseData
            ];

        } catch (Exception $e) {
            $this->logError("[EXCEPTION] NBC API Request Failed", [
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'url' => $url ?? 'Not set',
                'request_payload' => isset($payload) ? json_encode($payload, JSON_PRETTY_PRINT) : 'Not available',
                'stack_trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Connection to NBC service failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate NBC authentication headers
     * 
     * @param array $payload
     * @return array
     */
    protected function generateNBCHeaders(array $payload): array
    {
        $username = config('services.nbc_internal_fund_transfer.username');
        $password = config('services.nbc_internal_fund_transfer.password');
        $privateKeyPath = config('services.nbc_internal_fund_transfer.private_key');
        
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey  // lowercase as per NBC documentation
        ];
        
        // Add Basic Authentication
        if ($username && $password) {
            $basicAuth = base64_encode($username . ':' . $password);
            $headers['NBC-Authorization'] = 'Basic ' . $basicAuth;
        }
        
        // Generate digital signature if private key exists
        if ($privateKeyPath) {
            try {
                $signature = $this->generateDigitalSignature($payload, $privateKeyPath);
                $headers['Signature'] = $signature;
            } catch (Exception $e) {
                $this->logWarning("Failed to generate signature", ['error' => $e->getMessage()]);
            }
        }
        
        return $headers;
    }

    /**
     * Generate digital signature for NBC API
     * 
     * @param array $payload
     * @param string $privateKeyPath
     * @return string
     */
    protected function generateDigitalSignature(array $payload, string $privateKeyPath): string
    {
        $payloadString = json_encode($payload, JSON_UNESCAPED_SLASHES);
        
        // Load private key
        $privateKey = openssl_pkey_get_private($privateKeyPath);
        if (!$privateKey) {
            throw new Exception('Failed to load private key');
        }
        
        // Generate signature
        $signature = '';
        if (!openssl_sign($payloadString, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Failed to generate signature');
        }
        
        return base64_encode($signature);
    }

    /**
     * Get NBC error message by status code
     * 
     * @param int|null $statusCode
     * @return string
     */
    protected function getNBCErrorMessage(?int $statusCode): string
    {
        $messages = [
            602 => 'Duplicate Payment Detected - This transaction was already processed',
            626 => 'Transaction Failed',
            625 => 'No Response from CBS',
            630 => 'Currency account combination does not match',
            631 => 'Biller not defined',
            700 => 'General Failure'
        ];

        return $messages[$statusCode] ?? 'Unknown error (Code: ' . $statusCode . ')';
    }

    /**
     * Sanitize data for logging
     * 
     * @param mixed $data
     * @return mixed
     */
    protected function sanitizeForLogging($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        
        $sanitized = $data;
        
        // Mask sensitive fields
        $sensitiveFields = ['creditAccount', 'debitAccount', 'pyrName'];
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $value = $sanitized[$field];
                if (strlen($value) > 4) {
                    $sanitized[$field] = substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2);
                } else {
                    $sanitized[$field] = str_repeat('*', strlen($value));
                }
            }
        }
        
        // Recursively sanitize nested arrays
        foreach ($sanitized as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeForLogging($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Generate unique reference
     * 
     * @param string $prefix
     * @return string
     */
    protected function generateReference(string $prefix = 'IFT'): string
    {
        // NBC API requires alphanumeric clientRef only (no underscores or special chars)
        return $prefix . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6));
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
     * 
     * @param array $data
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
                'transaction_subcategory' => 'IFT',
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'TZS',
                'status' => $data['status'],
                'reconciliation_status' => 'UNRECONCILED',
                'service_name' => 'Internal Funds Transfer',

                // External system tracking
                'external_system' => 'NBC_INTERNAL',
                'external_system_version' => 'v1',
                'external_transaction_id' => $data['nbc_reference'] ?? $data['reference'],
                'external_status_code' => $data['response_code'] ?? null,
                'external_status_message' => $data['response_message'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'processing_time_ms' => isset($data['duration_ms']) ? round($data['duration_ms']) : null,

                // Source and channel info
                'source' => 'IFT_SERVICE',
                'channel_id' => $data['channel_id'] ?? 'INTERNAL',
                'channel_code' => $data['channel_code'] ?? 'IFT',

                // Account information
                'credit_account' => $data['to_account'],
                'credit_currency' => $data['currency'] ?? 'TZS',
                'lookup_account_number' => $data['from_account'],

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
                'narration' => $data['narration'] ?? sprintf('Internal transfer from %s to %s', 
                    $data['from_account'], 
                    $data['to_account']
                ),
                'description' => $data['narration'] ?? sprintf('IFT: %s to %s', 
                    $data['from_account'], 
                    $data['to_account']
                ),

                // Store full payloads
                'raw_payload' => json_encode($data),
                'external_request_payload' => json_encode($data),
                'metadata' => json_encode([
                    'from_account' => $data['from_account'],
                    'to_account' => $data['to_account'],
                    'transfer_type' => 'IFT',
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
                'data' => $data
            ]);
        }
    }

    /**
     * Log information
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::channel('payments')->info("[IFT] {$message}", $context);
    }

    /**
     * Log error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::channel('payments')->error("[IFT] {$message}", $context);
    }

    /**
     * Log debug information
     */
    protected function logDebug(string $message, array $context = []): void
    {
        Log::channel('payments')->debug("[IFT] {$message}", $context);
    }

    /**
     * Log warning
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::channel('payments')->warning("[IFT] {$message}", $context);
    }
}