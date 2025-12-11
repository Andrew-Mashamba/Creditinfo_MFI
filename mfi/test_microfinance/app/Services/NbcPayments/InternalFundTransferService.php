<?php

namespace App\Services\NbcPayments;
//namespace App\Services\NbcPayments;

use Exception;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\TransactionLogger;
use Illuminate\Support\Facades\Config;

/**
 * Internal Fund Transfer Service for NBC Bank API Integration
 * 
 * This service handles internal fund transfers between NBC accounts
 * following the NBC Internal Fund Transfer API specification.
 * 
 * @author System Administrator
 * @version 1.0
 */
class InternalFundTransferService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $username;
    protected string $password;
    protected string $privateKey;
    protected string $serviceName;
    protected string $channelId;
    protected TransactionLogger $logger;

    /**
     * Response status codes as per NBC API documentation
     */
    private const STATUS_CODES = [
        'SUCCESS' => 600,
        'TRANSACTION_FAILED' => 626,
        'NO_RESPONSE' => 625,
        'CURRENCY_MISMATCH' => 630,
        'BILLER_NOT_DEFINED' => 631,
        'FAILED' => 700
    ];

    /**
     * Constructor - Initialize service with configuration
     * 
     * @throws \RuntimeException
     */
    public function __construct()
    {
        try {
            Log::info('Initializing InternalFundTransferService');

            $this->baseUrl = config('services.nbc_internal_fund_transfer.base_url');
            $this->apiKey = config('services.nbc_internal_fund_transfer.api_key');
            $this->username = config('services.nbc_internal_fund_transfer.username');
            $this->password = config('services.nbc_internal_fund_transfer.password');
            $this->privateKey = config('services.nbc_internal_fund_transfer.private_key');
            $this->serviceName = config('services.nbc_internal_fund_transfer.service_name');
            $this->channelId = config('services.nbc_internal_fund_transfer.channel_id');

            // Validate required configuration
            $this->validateConfiguration();

            $this->logger = new TransactionLogger();

            Log::info('InternalFundTransferService initialized successfully', [
                'baseUrl' => $this->baseUrl,
                'serviceName' => $this->serviceName,
                'channelId' => $this->channelId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to initialize InternalFundTransferService: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('Payment service configuration error. Please contact support.', 0, $e);
        }
    }

    /**
     * Process internal fund transfer
     * 
     * @param array $transferData
     * @return array
     * @throws \Exception
     */
    public function processInternalTransfer(array $transferData): array
    {
        $requestId = Str::uuid()->toString();
        $logContext = [
            'requestId' => $requestId,
            'serviceName' => $this->serviceName,
            'channelId' => $this->channelId
        ];

        Log::info('Starting internal fund transfer processing', $logContext);
        $this->logger->logTransactionStart($transferData);

        try {
            // Step 1: Validate transfer data
            Log::debug('Validating transfer data', $logContext);
            $this->validateTransferData($transferData);
            Log::debug('Transfer data validation successful', $logContext);

            // Step 2: Build request payload
            Log::debug('Building request payload', $logContext);
            $requestPayload = $this->buildRequestPayload($transferData);
            Log::debug('Request payload built successfully', [
                'requestId' => $requestId,
                'payload' => $this->sanitizePayloadForLogging($requestPayload)
            ]);

            // Step 3: Generate authentication headers
            Log::debug('Generating authentication headers', $logContext);
            $headers = $this->generateHeaders($requestPayload);
            Log::debug('Authentication headers generated successfully', $logContext);

            // Step 4: Make API request with retry mechanism
            $endpoint = rtrim($this->baseUrl, '/') . '/' . ltrim($this->serviceName, '/');
            
            Log::info('Making API request to NBC Internal Fund Transfer', [
                'requestId' => $requestId,
                'endpoint' => $endpoint,
                'timestamp' => Carbon::now()->toIso8601String()
            ]);

            // Implement retry mechanism with exponential backoff
            $maxRetries = config('services.nbc_internal_fund_transfer.max_retries', 3);
            $retryDelay = config('services.nbc_internal_fund_transfer.retry_delay', 2);
            $attempt = 0;
            $lastException = null;

            while ($attempt < $maxRetries) {
                $attempt++;
                
                try {
                    Log::debug("API request attempt {$attempt} of {$maxRetries}", [
                        'requestId' => $requestId,
                        'endpoint' => $endpoint,
                        'headers' => $headers,
                        'payload' => $requestPayload
                    ]);

                    $response = Http::withHeaders($headers)
                        ->withOptions(['verify' => config('services.nbc_internal_fund_transfer.verify_ssl', true)])
                        ->timeout(config('services.nbc_internal_fund_transfer.timeout', 30))
                        ->retry($maxRetries, $retryDelay * 1000, function ($exception, $request) {
                            // Only retry on network errors, not on 4xx/5xx responses
                            return $exception instanceof \Illuminate\Http\Client\ConnectionException ||
                                   $exception instanceof \Illuminate\Http\Client\RequestException;
                        })
                        ->post($endpoint, $requestPayload);

                    // Log the exact response for debugging
                    Log::debug('API request completed - Full Response Details', [
                        'requestId' => $requestId,
                        'attempt' => $attempt,
                        'statusCode' => $response->status(),
                        'responseBody' => $response->body(),
                        'responseHeaders' => $response->headers(),
                        'responseJson' => $response->json()
                    ]);

                    // Step 5: Process response
                    Log::debug('Processing API response', $logContext);
                    $result = $this->processResponse($response, $requestId);
                    
                    // Step 6: Log transaction completion
                    $this->logger->logTransactionCompletion($requestId, $result['success'] ? 'SUCCESS' : 'FAILED');
                    
                    Log::info('Internal fund transfer processing completed', [
                        'requestId' => $requestId,
                        'success' => $result['success'],
                        'statusCode' => $result['statusCode'] ?? null,
                        'attempts' => $attempt
                    ]);

                    return $result;

                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $lastException = $e;
                    Log::warning("API request attempt {$attempt} failed - Connection error", [
                        'requestId' => $requestId,
                        'attempt' => $attempt,
                        'maxRetries' => $maxRetries,
                        'error' => $e->getMessage(),
                        'endpoint' => $endpoint
                    ]);

                    if ($attempt < $maxRetries) {
                        $delay = $retryDelay * pow(2, $attempt - 1); // Exponential backoff
                        Log::info("Retrying in {$delay} seconds...", ['requestId' => $requestId]);
                        sleep($delay);
                    }
                } catch (\Illuminate\Http\Client\RequestException $e) {
                    $lastException = $e;
                    
                    // Log the exact response body for debugging
                    $responseBody = null;
                    $responseHeaders = null;
                    if ($e->response) {
                        $responseBody = $e->response->body();
                        $responseHeaders = $e->response->headers();
                    }
                    
                    Log::warning("API request attempt {$attempt} failed - Request error", [
                        'requestId' => $requestId,
                        'attempt' => $attempt,
                        'maxRetries' => $maxRetries,
                        'error' => $e->getMessage(),
                        'statusCode' => $e->response?->status(),
                        'responseBody' => $responseBody,
                        'responseHeaders' => $responseHeaders,
                        'endpoint' => $endpoint
                    ]);

                    if ($attempt < $maxRetries) {
                        $delay = $retryDelay * pow(2, $attempt - 1);
                        Log::info("Retrying in {$delay} seconds...", ['requestId' => $requestId]);
                        sleep($delay);
                    }
                }
            }

            // All retries exhausted - handle gracefully
            $this->handleApiFailure($lastException, $requestId, $endpoint, $logContext);
            
            // Return a user-friendly error response instead of throwing
            return [
                'success' => false,
                'statusCode' => 'NETWORK_ERROR',
                'message' => 'Temporary network issue - please try again in a few minutes',
                'error' => 'Unable to connect to NBC service after multiple attempts',
                'data' => [
                    'requestId' => $requestId,
                    'attempts' => $attempt,
                    'endpoint' => $endpoint
                ]
            ];

        } catch (InvalidArgumentException $e) {
            Log::error('Invalid transfer data: ' . $e->getMessage(), $logContext);
            $this->logger->logError($e, $logContext);
            throw new Exception('Invalid transfer request data. Please check the account numbers and amount.', 400, $e);
        } catch (Exception $e) {
            Log::error('Internal fund transfer processing failed: ' . $e->getMessage(), [
                'requestId' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->logger->logError($e, $logContext);
            throw new Exception('External service call failed: Temporary network issue - please try again in a few minutes', 500, $e);
        }
    }

    /**
     * Validate the transfer data against required fields
     * 
     * @param array $transferData
     * @throws \InvalidArgumentException
     */
    protected function validateTransferData(array $transferData): void
    {
        Log::debug('Starting transfer data validation');

        $requiredFields = [
            'creditAccount',
            'creditCurrency', 
            'debitAccount',
            'debitCurrency',
            'amount',
            'narration',
            'pyrName'
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($transferData[$field]) || empty(trim($transferData[$field]))) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new InvalidArgumentException('Missing required fields: ' . implode(', ', $missingFields));
        }

        // Validate amount format
        if (!is_numeric($transferData['amount']) || $transferData['amount'] <= 0) {
            throw new InvalidArgumentException('Invalid amount: Amount must be a positive number');
        }

        // Validate currency codes
        $validCurrencies = ['TZS', 'USD', 'EUR', 'GBP'];
        if (!in_array(strtoupper($transferData['creditCurrency']), $validCurrencies)) {
            throw new InvalidArgumentException('Invalid credit currency: ' . $transferData['creditCurrency']);
        }
        if (!in_array(strtoupper($transferData['debitCurrency']), $validCurrencies)) {
            throw new InvalidArgumentException('Invalid debit currency: ' . $transferData['debitCurrency']);
        }

        // Validate account numbers (basic format check)
        if (!preg_match('/^\d{10,16}$/', $transferData['creditAccount'])) {
            throw new InvalidArgumentException('Invalid credit account number format');
        }
        if (!preg_match('/^\d{10,16}$/', $transferData['debitAccount'])) {
            throw new InvalidArgumentException('Invalid debit account number format');
        }

        Log::debug('Transfer data validation completed successfully');
    }

    /**
     * Build the request payload according to NBC API specification
     * 
     * @param array $transferData
     * @return array
     */
    protected function buildRequestPayload(array $transferData): array
    {
        Log::debug('Building request payload');

        $channelRef = $this->generateChannelReference();
        
        $payload = [
            'header' => [
                'service' => $this->serviceName,
                'extra' => [
                    'pyrName' => $transferData['pyrName']
                ]
            ],
            'channelId' => $this->channelId,
            'channelRef' => $channelRef,
            'creditAccount' => $transferData['creditAccount'],
            'creditCurrency' => strtoupper($transferData['creditCurrency']),
            'debitAccount' => $transferData['debitAccount'],
            'debitCurrency' => strtoupper($transferData['debitCurrency']),
            'amount' => (string) $transferData['amount'],
            'narration' => $transferData['narration']
        ];

        Log::debug('Request payload built', [
            'channelRef' => $channelRef,
            'amount' => $transferData['amount']
        ]);

        return $payload;
    }

    /**
     * Generate authentication headers
     * 
     * @param array $payload
     * @return array
     */
    protected function generateHeaders(array $payload): array
    {
        Log::debug('Generating authentication headers');

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey,  // lowercase as per NBC documentation
        ];

        // Add Basic Authentication
        $basicAuth = base64_encode($this->username . ':' . $this->password);
        $headers['NBC-Authorization'] = 'Basic ' . $basicAuth;

        // Add Digital Signature
        $signature = $this->generateDigitalSignature($payload);
        $headers['Signature'] = $signature;

        Log::debug('Authentication headers generated successfully');

        return $headers;
    }

    /**
     * Generate digital signature using SHA256withRSA
     * 
     * @param array $payload
     * @return string
     */
    protected function generateDigitalSignature(array $payload): string
    {

        Log::debug('Generating digital signature xxxxxxxxxxxx', [
            'payload_keys' => array_keys($payload),
            'payload_size' => count($payload),
            'private_key_path' => $this->privateKey,
            'private_key_exists' => !empty($this->privateKey),
            'private_key_length' => strlen($this->privateKey ?? '')
        ]);


        try {
            Log::debug('Generating digital signature', [
                'payload_keys' => array_keys($payload),
                'payload_size' => count($payload),
                'private_key_path' => $this->privateKey,
                'private_key_exists' => !empty($this->privateKey),
                'private_key_length' => strlen($this->privateKey ?? '')
            ]);

            // Convert payload to JSON string
            $payloadString = json_encode($payload, JSON_UNESCAPED_SLASHES);
            
            if ($payloadString === false) {
                $jsonError = json_last_error_msg();
                Log::error('Failed to encode payload to JSON', [
                    'json_error' => $jsonError,
                    'json_error_code' => json_last_error(),
                    'payload_sample' => array_slice($payload, 0, 3)
                ]);
                throw new Exception('Failed to encode payload to JSON: ' . $jsonError);
            }
            
            Log::debug('Payload JSON created successfully', [
                'json_length' => strlen($payloadString),
                'json_preview' => substr($payloadString, 0, 100) . '...'
            ]);
            
            // Log private key details before loading
            Log::debug('Attempting to load private key', [
                'private_key_source' => $this->privateKey,
                'private_key_starts_with_file' => str_starts_with($this->privateKey ?? '', 'file://'),
                'private_key_starts_with_-----' => str_starts_with($this->privateKey ?? '', '-----BEGIN'),
                'private_key_length' => strlen($this->privateKey ?? ''),
                'openssl_errors_before' => openssl_error_string()
            ]);
            
            // Create signature using private key
            $privateKey = openssl_pkey_get_private($this->privateKey);
            
            if (!$privateKey) {
                $opensslError = openssl_error_string();
                Log::error('Failed to load private key for digital signature', [
                    'private_key_source' => $this->privateKey,
                    'private_key_exists' => !empty($this->privateKey),
                    'private_key_length' => strlen($this->privateKey ?? ''),
                    'openssl_error' => $opensslError,
                    'all_openssl_errors' => $this->getAllOpenSSLErrors(),
                    'file_exists_check' => str_starts_with($this->privateKey ?? '', 'file://') ? 
                        file_exists(substr($this->privateKey, 7)) : 'N/A',
                    'file_path_if_file' => str_starts_with($this->privateKey ?? '', 'file://') ? 
                        substr($this->privateKey, 7) : 'N/A'
                ]);
                throw new Exception('Failed to load private key for digital signature. OpenSSL Error: ' . $opensslError);
            }

            Log::debug('Private key loaded successfully', [
                'private_key_type' => openssl_pkey_get_details($privateKey)['type'] ?? 'unknown',
                'private_key_bits' => openssl_pkey_get_details($privateKey)['bits'] ?? 'unknown'
            ]);

            $signature = '';
            $signResult = openssl_sign($payloadString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            
            if (!$signResult) {
                $opensslError = openssl_error_string();
                Log::error('Failed to generate digital signature', [
                    'payload_length' => strlen($payloadString),
                    'private_key_loaded' => !empty($privateKey),
                    'openssl_error' => $opensslError,
                    'all_openssl_errors' => $this->getAllOpenSSLErrors(),
                    'algorithm_used' => 'OPENSSL_ALGO_SHA256'
                ]);
                throw new Exception('Failed to generate digital signature: ' . $opensslError);
            }

            $base64Signature = base64_encode($signature);
            
            Log::debug('Digital signature generated successfully', [
                'signature_length' => strlen($signature),
                'base64_signature_length' => strlen($base64Signature),
                'base64_signature_preview' => substr($base64Signature, 0, 50) . '...'
            ]);
            
            return $base64Signature;

        } catch (Exception $e) {
            Log::error('Failed to generate digital signature', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'private_key_configured' => !empty($this->privateKey),
                'private_key_length' => strlen($this->privateKey ?? ''),
                'payload_keys' => array_keys($payload),
                'openssl_errors' => $this->getAllOpenSSLErrors()
            ]);
            throw new Exception('Payment service authentication error. Please contact support.', 0, $e);
        }
    }

    /**
     * Process the API response
     * 
     * @param \Illuminate\Http\Client\Response $response
     * @param string $requestId
     * @return array
     */
    protected function processResponse($response, string $requestId): array
    {
        Log::debug('Processing API response', ['requestId' => $requestId]);

        $responseData = $response->json();
        $statusCode = $response->status();

        // Log the raw response for debugging
        Log::debug('Raw API response', [
            'requestId' => $requestId,
            'httpStatusCode' => $statusCode,
            'responseData' => $this->sanitizeResponseForLogging($responseData)
        ]);

        // Check HTTP status code
        if ($statusCode !== 200) {
            Log::error('API request failed with HTTP status: ' . $statusCode, [
                'requestId' => $requestId,
                'response' => $responseData
            ]);
            
            return [
                'success' => false,
                'statusCode' => $statusCode,
                'message' => 'HTTP request failed',
                'error' => $responseData['message'] ?? 'Unknown error',
                'requestId' => $requestId
            ];
        }

        // Check NBC API status code
        $nbcStatusCode = $responseData['statusCode'] ?? null;
        
        if ($nbcStatusCode === self::STATUS_CODES['SUCCESS']) {
            Log::info('Internal fund transfer successful', [
                'requestId' => $requestId,
                'hostReferenceCbs' => $responseData['body']['hostReferenceCbs'] ?? null,
                'hostReferenceGw' => $responseData['body']['hostReferenceGw'] ?? null
            ]);

            return [
                'success' => true,
                'statusCode' => $nbcStatusCode,
                'message' => $responseData['message'] ?? 'SUCCESS',
                'data' => [
                    'hostReferenceCbs' => $responseData['body']['hostReferenceCbs'] ?? '',
                    'hostStatusCodeCbs' => $responseData['body']['hostStatusCodeCbs'] ?? '',
                    'hostReferenceGw' => $responseData['body']['hostReferenceGw'] ?? '',
                    'cbsRespTime' => $responseData['body']['cbsRespTime'] ?? '',
                    'requestId' => $requestId
                ]
            ];
        } else {
            $errorMessage = $this->getErrorMessageByCode($nbcStatusCode);
            
            Log::error('Internal fund transfer failed', [
                'requestId' => $requestId,
                'nbcStatusCode' => $nbcStatusCode,
                'errorMessage' => $errorMessage,
                'response' => $responseData
            ]);

            return [
                'success' => false,
                'statusCode' => $nbcStatusCode,
                'message' => $responseData['message'] ?? 'FAILED',
                'error' => $errorMessage,
                'data' => [
                    'hostReferenceCbs' => $responseData['body']['hostReferenceCbs'] ?? '',
                    'hostStatusCodeCbs' => $responseData['body']['hostStatusCodeCbs'] ?? '',
                    'hostReferenceGw' => $responseData['body']['hostReferenceGw'] ?? '',
                    'cbsRespTime' => $responseData['body']['cbsRespTime'] ?? '',
                    'requestId' => $requestId
                ]
            ];
        }
    }

    /**
     * Get error message by status code
     * 
     * @param int $statusCode
     * @return string
     */
    protected function getErrorMessageByCode(int $statusCode): string
    {
        $errorMessages = [
            self::STATUS_CODES['TRANSACTION_FAILED'] => 'Transaction Failed',
            self::STATUS_CODES['NO_RESPONSE'] => 'No Response from CBS',
            self::STATUS_CODES['CURRENCY_MISMATCH'] => 'Currency account combination does not match',
            self::STATUS_CODES['BILLER_NOT_DEFINED'] => 'Biller not defined',
            self::STATUS_CODES['FAILED'] => 'Transaction Failed'
        ];

        return $errorMessages[$statusCode] ?? 'Unknown error occurred';
    }

    /**
     * Generate unique channel reference
     * 
     * @return string
     */
    protected function generateChannelReference(): string
    {
        return 'CH' . date('YmdHis') . Str::random(6);
    }

    /**
     * Validate service configuration
     * 
     * @throws \RuntimeException
     */
    protected function validateConfiguration(): void
    {
        $requiredConfig = [
            'base_url' => $this->baseUrl,
            'api_key' => $this->apiKey,
            'username' => $this->username,
            'password' => $this->password,
            'private_key' => $this->privateKey,
            'service_name' => $this->serviceName,
            'channel_id' => $this->channelId
        ];

        $missingConfig = [];
        foreach ($requiredConfig as $key => $value) {
            if (empty($value)) {
                $missingConfig[] = $key;
            }
        }

        if (!empty($missingConfig)) {
            throw new \RuntimeException('Missing required configuration: ' . implode(', ', $missingConfig));
        }
    }

    /**
     * Sanitize payload for logging (remove sensitive data)
     * 
     * @param array $payload
     * @return array
     */
    protected function sanitizePayloadForLogging(array $payload): array
    {
        $sanitized = $payload;
        
        // Mask account numbers
        if (isset($sanitized['creditAccount'])) {
            $sanitized['creditAccount'] = $this->maskAccountNumber($sanitized['creditAccount']);
        }
        if (isset($sanitized['debitAccount'])) {
            $sanitized['debitAccount'] = $this->maskAccountNumber($sanitized['debitAccount']);
        }

        // Remove sensitive headers if present
        if (isset($sanitized['header']['extra']['pyrName'])) {
            $sanitized['header']['extra']['pyrName'] = '***MASKED***';
        }

        return $sanitized;
    }

    /**
     * Sanitize response for logging
     * 
     * @param array|null $response
     * @return array|null
     */
    protected function sanitizeResponseForLogging(?array $response): ?array
    {
        if (!$response) {
            return null;
        }

        $sanitized = $response;

        // Mask sensitive data in response
        if (isset($sanitized['body']['hostReferenceCbs'])) {
            $sanitized['body']['hostReferenceCbs'] = $this->maskReferenceNumber($sanitized['body']['hostReferenceCbs']);
        }
        if (isset($sanitized['body']['hostReferenceGw'])) {
            $sanitized['body']['hostReferenceGw'] = $this->maskReferenceNumber($sanitized['body']['hostReferenceGw']);
        }

        return $sanitized;
    }

    /**
     * Mask account number for logging
     * 
     * @param string $accountNumber
     * @return string
     */
    protected function maskAccountNumber(string $accountNumber): string
    {
        if (strlen($accountNumber) <= 4) {
            return str_repeat('*', strlen($accountNumber));
        }
        
        return substr($accountNumber, 0, 2) . str_repeat('*', strlen($accountNumber) - 4) . substr($accountNumber, -2);
    }

    /**
     * Mask reference number for logging
     * 
     * @param string $referenceNumber
     * @return string
     */
    protected function maskReferenceNumber(string $referenceNumber): string
    {
        if (strlen($referenceNumber) <= 4) {
            return str_repeat('*', strlen($referenceNumber));
        }
        
        return substr($referenceNumber, 0, 3) . str_repeat('*', strlen($referenceNumber) - 6) . substr($referenceNumber, -3);
    }

    /**
     * Get service status and health check
     * 
     * @return array
     */
    public function getServiceStatus(): array
    {
        try {
            Log::info('Performing service health check');

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Api-Key' => $this->apiKey,
            ];

            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => config('services.nbc_internal_fund_transfer.verify_ssl', true)])
                ->timeout(10)
                ->get($this->baseUrl . '/health');

            return [
                'status' => 'healthy',
                'response_time' => $response->handlerStats()['total_time'] ?? null,
                'http_status' => $response->status()
            ];

        } catch (Exception $e) {
            Log::error('Service health check failed: ' . $e->getMessage());
            
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all OpenSSL errors as an array
     * 
     * @return array
     */
    protected function getAllOpenSSLErrors(): array
    {
        $errors = [];
        while ($error = openssl_error_string()) {
            $errors[] = $error;
        }
        return $errors;
    }

    /**
     * Handle API failure with admin notification and detailed logging
     * 
     * @param \Exception $exception
     * @param string $requestId
     * @param string $endpoint
     * @param array $logContext
     */
    protected function handleApiFailure(\Exception $exception, string $requestId, string $endpoint, array $logContext): void
    {
        // Log detailed error information
        Log::error('NBC Internal Fund Transfer API failure after all retries', [
            'requestId' => $requestId,
            'endpoint' => $endpoint,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'context' => $logContext
        ]);

        // Send admin notification email
        $this->sendAdminNotification($exception, $requestId, $endpoint, $logContext);

        // Log to transaction logger
        $this->logger->logError($exception, array_merge($logContext, [
            'requestId' => $requestId,
            'endpoint' => $endpoint,
            'failure_type' => 'api_connection'
        ]));
    }

    /**
     * Send admin notification about API failure
     * 
     * @param \Exception $exception
     * @param string $requestId
     * @param string $endpoint
     * @param array $logContext
     */
    protected function sendAdminNotification(\Exception $exception, string $requestId, string $endpoint, array $logContext): void
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@example.com');
            
            $subject = 'NBC Internal Fund Transfer API Failure Alert';
            $message = "NBC Internal Fund Transfer API has failed after multiple retry attempts.\n\n";
            $message .= "Request ID: {$requestId}\n";
            $message .= "Endpoint: {$endpoint}\n";
            $message .= "Error: {$exception->getMessage()}\n";
            $message .= "Time: " . now()->toDateTimeString() . "\n";
            $message .= "Please check the system logs and NBC service status.\n";
            
            // Use Laravel's mail system to send notification
            Mail::raw($message, function($mail) use ($adminEmail, $subject) {
                $mail->to($adminEmail)
                     ->subject($subject);
            });

            Log::info('Admin notification sent for API failure', [
                'requestId' => $requestId,
                'admin_email' => $adminEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'requestId' => $requestId,
                'notification_error' => $e->getMessage()
            ]);
        }
    }
}
