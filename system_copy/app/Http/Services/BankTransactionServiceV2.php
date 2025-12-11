<?php

namespace App\Http\Services;

use App\Services\ApiLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Bank Transaction Service with comprehensive logging
 * and response validation
 */
class BankTransactionServiceV2
{
    private $apiLogger;
    private $baseUrl;
    private $timeout;
    private $retryAttempts;
    
    public function __construct()
    {
        $this->apiLogger = ApiLogger::getInstance();
        $this->baseUrl = config('services.bank_api.base_url', 'https://api.bank.com');
        $this->timeout = config('services.bank_api.timeout', 30);
        $this->retryAttempts = config('services.bank_api.retry_attempts', 3);
    }
    
    /**
     * Send transaction data with enhanced logging and validation
     * 
     * @param string $transactionType Transaction type (IFT, EFT, MOBILE)
     * @param array $data Transaction data
     * @return array Response with status and details
     */
    public function sendTransactionData(string $transactionType, array $data): array
    {
        $startTime = microtime(true);
        $requestId = null;
        
        try {
            // Validate transaction type
            $endpoint = $this->getEndpoint($transactionType);
            if (!$endpoint) {
                return $this->formatErrorResponse('Invalid transaction type: ' . $transactionType);
            }
            
            // Prepare request
            $url = $this->baseUrl . $endpoint;
            $headers = $this->getHeaders();
            $body = $this->prepareRequestBody($transactionType, $data);
            
            // Validate request data
            $validation = $this->validateRequestData($transactionType, $body);
            if (!$validation['valid']) {
                return $this->formatErrorResponse('Validation failed: ' . $validation['message']);
            }
            
            // Log request
            $requestId = $this->apiLogger->logRequest(
                'BankTransactionService',
                $transactionType . ' Transaction',
                'POST',
                $url,
                $headers,
                $body,
                [
                    'transaction_type' => $transactionType,
                    'amount' => $data['amount'] ?? null,
                    'currency' => $data['currency'] ?? null,
                ]
            );
            
            // Make HTTP request with retry logic
            $response = $this->makeRequestWithRetry($url, $body, $headers);
            
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Process successful response
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Log successful response
                $this->apiLogger->logResponse(
                    $requestId,
                    $response->status(),
                    $response->headers(),
                    $responseData,
                    $responseTime,
                    null,
                    ['transaction_type' => $transactionType]
                );
                
                // Validate response
                $responseValidation = $this->validateResponse($transactionType, $responseData);
                
                // Log validation result
                $this->apiLogger->logValidation(
                    $requestId,
                    'BankTransactionService',
                    $transactionType,
                    $responseValidation['valid'],
                    $responseValidation
                );
                
                if (!$responseValidation['valid']) {
                    return $this->formatErrorResponse(
                        'Invalid response structure: ' . $responseValidation['message'],
                        $responseData
                    );
                }
                
                return $this->formatSuccessResponse($responseData, [
                    'request_id' => $requestId,
                    'response_time_ms' => round($responseTime * 1000, 2),
                    'transaction_type' => $transactionType
                ]);
            }
            
            // Handle error responses
            $errorBody = $response->body();
            
            // Try to parse error as JSON
            $errorData = json_decode($errorBody, true) ?? ['message' => $errorBody];
            
            // Log error response
            $this->apiLogger->logResponse(
                $requestId,
                $response->status(),
                $response->headers(),
                $errorData,
                $responseTime,
                'HTTP Error: ' . $response->status(),
                ['transaction_type' => $transactionType]
            );
            
            // Format error response based on status code
            $errorMessage = $this->getErrorMessage($response->status(), $errorData);
            
            return $this->formatErrorResponse($errorMessage, $errorData, [
                'request_id' => $requestId,
                'status_code' => $response->status(),
                'response_time_ms' => round($responseTime * 1000, 2)
            ]);
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Log connection error
            if ($requestId) {
                $this->apiLogger->logResponse(
                    $requestId,
                    null,
                    [],
                    null,
                    $responseTime,
                    'Connection failed: ' . $e->getMessage()
                );
                
                $this->apiLogger->logError($requestId, $e, [
                    'transaction_type' => $transactionType,
                    'url' => $url ?? null
                ]);
            }
            
            return $this->formatErrorResponse(
                'Connection failed: Unable to reach the payment server',
                null,
                [
                    'request_id' => $requestId,
                    'response_time_ms' => round($responseTime * 1000, 2),
                    'error_type' => 'connection_error'
                ]
            );
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Log unexpected error
            if ($requestId) {
                $this->apiLogger->logError($requestId, $e, [
                    'transaction_type' => $transactionType
                ]);
            }
            
            Log::error('Bank Transaction Service Error', [
                'request_id' => $requestId,
                'transaction_type' => $transactionType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->formatErrorResponse(
                'An unexpected error occurred',
                null,
                [
                    'request_id' => $requestId,
                    'response_time_ms' => round($responseTime * 1000, 2),
                    'error_type' => 'system_error'
                ]
            );
        }
    }
    
    /**
     * Get endpoint for transaction type
     */
    private function getEndpoint(string $transactionType): ?string
    {
        $endpoints = [
            'IFT' => '/ift-transaction',
            'EFT' => '/eft-transaction',
            'MOBILE' => '/mobile-transaction',
            'RTGS' => '/rtgs-transaction',
            'ACH' => '/ach-transaction'
        ];
        
        return $endpoints[$transactionType] ?? null;
    }
    
    /**
     * Get request headers
     */
    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-Key' => config('services.bank_api.api_key'),
            'X-Request-ID' => uniqid('req_', true),
            'X-Client-ID' => config('services.bank_api.client_id'),
            'X-Timestamp' => now()->toIso8601String()
        ];
    }
    
    /**
     * Prepare request body based on transaction type
     */
    private function prepareRequestBody(string $transactionType, array $data): array
    {
        $body = [
            'transaction_type' => $transactionType,
            'timestamp' => now()->toIso8601String(),
            'reference' => $data['reference'] ?? uniqid($transactionType . '_', true),
        ];
        
        switch ($transactionType) {
            case 'IFT':
            case 'EFT':
                $body = array_merge($body, [
                    'source_account' => $data['account_from'] ?? $data['source_account'] ?? null,
                    'destination_account' => $data['account_to'] ?? $data['destination_account'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'TZS',
                    'description' => $data['description'] ?? '',
                    'beneficiary_name' => $data['beneficiary_name'] ?? null,
                    'bank_code' => $data['bank_code'] ?? null,
                ]);
                break;
                
            case 'MOBILE':
                $body = array_merge($body, [
                    'phone_number' => $data['phone_number'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'TZS',
                    'description' => $data['description'] ?? '',
                    'mobile_network' => $data['mobile_network'] ?? $this->detectMobileNetwork($data['phone_number'] ?? ''),
                    'source_account' => $data['source_account'] ?? null,
                ]);
                break;
        }
        
        return $body;
    }
    
    /**
     * Validate request data
     */
    private function validateRequestData(string $transactionType, array $data): array
    {
        $errors = [];
        
        // Common validations
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Invalid amount';
        }
        
        if ($data['amount'] > 1000000000) { // 1 billion limit
            $errors[] = 'Amount exceeds maximum limit';
        }
        
        // Type-specific validations
        switch ($transactionType) {
            case 'IFT':
            case 'EFT':
                if (empty($data['source_account'])) {
                    $errors[] = 'Source account is required';
                }
                if (empty($data['destination_account'])) {
                    $errors[] = 'Destination account is required';
                }
                if ($data['source_account'] === $data['destination_account']) {
                    $errors[] = 'Source and destination accounts cannot be the same';
                }
                break;
                
            case 'MOBILE':
                if (empty($data['phone_number'])) {
                    $errors[] = 'Phone number is required';
                }
                if (!$this->isValidPhoneNumber($data['phone_number'])) {
                    $errors[] = 'Invalid phone number format';
                }
                break;
        }
        
        return [
            'valid' => empty($errors),
            'message' => implode(', ', $errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate response data
     */
    private function validateResponse(string $transactionType, $responseData): array
    {
        if (!is_array($responseData)) {
            return [
                'valid' => false,
                'message' => 'Response is not in expected format'
            ];
        }
        
        $requiredFields = ['status', 'transaction_id'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($responseData[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            return [
                'valid' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missingFields),
                'missing_fields' => $missingFields
            ];
        }
        
        // Validate transaction ID format
        if (empty($responseData['transaction_id'])) {
            return [
                'valid' => false,
                'message' => 'Transaction ID is empty'
            ];
        }
        
        // Type-specific validations
        if ($transactionType === 'MOBILE' && !isset($responseData['mobile_network'])) {
            return [
                'valid' => false,
                'message' => 'Mobile network information missing'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Response validation passed'
        ];
    }
    
    /**
     * Make HTTP request with retry logic
     */
    private function makeRequestWithRetry(string $url, array $body, array $headers)
    {
        $attempts = 0;
        $lastException = null;
        
        while ($attempts < $this->retryAttempts) {
            $attempts++;
            
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($headers)
                    ->post($url, $body);
                
                // If we get a response (even error), return it
                return $response;
                
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastException = $e;
                
                // If not the last attempt, wait before retry
                if ($attempts < $this->retryAttempts) {
                    $waitTime = pow(2, $attempts); // Exponential backoff
                    Log::warning("Retry attempt $attempts after {$waitTime}s", [
                        'url' => $url,
                        'error' => $e->getMessage()
                    ]);
                    sleep($waitTime);
                }
            }
        }
        
        // If all retries failed, throw the last exception
        throw $lastException;
    }
    
    /**
     * Detect mobile network from phone number
     */
    private function detectMobileNetwork(string $phoneNumber): ?string
    {
        // Tanzania mobile network prefixes
        $networks = [
            'VODACOM' => ['74', '75', '76'],
            'TIGO' => ['71', '65', '67'],
            'AIRTEL' => ['68', '69', '78'],
            'HALOTEL' => ['62'],
            'TTCL' => ['73'],
        ];
        
        // Remove country code if present
        $phone = preg_replace('/^(\+?255|0)/', '', $phoneNumber);
        $prefix = substr($phone, 0, 2);
        
        foreach ($networks as $network => $prefixes) {
            if (in_array($prefix, $prefixes)) {
                return $network;
            }
        }
        
        return null;
    }
    
    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber(string $phoneNumber): bool
    {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s\-]/', '', $phoneNumber);
        
        // Tanzania phone number patterns
        $patterns = [
            '/^255[67]\d{8}$/',     // With country code
            '/^0[67]\d{8}$/',        // With leading zero
            '/^[67]\d{8}$/',         // Without prefix
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get error message based on status code
     */
    private function getErrorMessage(int $statusCode, array $errorData): string
    {
        $message = $errorData['message'] ?? $errorData['error'] ?? 'Unknown error';
        
        $statusMessages = [
            400 => 'Bad Request: ' . $message,
            401 => 'Authentication Failed: Please check API credentials',
            403 => 'Access Forbidden: Insufficient permissions',
            404 => 'Service Not Found: Invalid endpoint',
            422 => 'Validation Error: ' . $message,
            429 => 'Rate Limit Exceeded: Too many requests',
            500 => 'Server Error: The payment service is experiencing issues',
            502 => 'Gateway Error: Unable to reach payment service',
            503 => 'Service Unavailable: Payment service is temporarily down',
            504 => 'Gateway Timeout: Request took too long to process',
        ];
        
        return $statusMessages[$statusCode] ?? "HTTP $statusCode: $message";
    }
    
    /**
     * Format success response
     */
    private function formatSuccessResponse(array $data, array $metadata = []): array
    {
        return [
            'status' => 'success',
            'message' => $data['message'] ?? 'Transaction processed successfully',
            'data' => $data,
            'metadata' => array_merge([
                'timestamp' => now()->toIso8601String(),
                'response_received' => true,
            ], $metadata)
        ];
    }
    
    /**
     * Format error response
     */
    private function formatErrorResponse(string $message, $data = null, array $metadata = []): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'data' => $data,
            'metadata' => array_merge([
                'timestamp' => now()->toIso8601String(),
                'response_received' => isset($metadata['status_code']),
            ], $metadata)
        ];
    }
    
    /**
     * Get transaction status
     * 
     * @param string $transactionId
     * @return array
     */
    public function getTransactionStatus(string $transactionId): array
    {
        $startTime = microtime(true);
        
        try {
            $url = $this->baseUrl . '/transaction/status/' . $transactionId;
            $headers = $this->getHeaders();
            
            // Log request
            $requestId = $this->apiLogger->logRequest(
                'BankTransactionService',
                'Transaction Status Check',
                'GET',
                $url,
                $headers,
                null,
                ['transaction_id' => $transactionId]
            );
            
            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->get($url);
            
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Log response
            $this->apiLogger->logResponse(
                $requestId,
                $response->status(),
                $response->headers(),
                $response->json(),
                $responseTime
            );
            
            if ($response->successful()) {
                return $this->formatSuccessResponse($response->json(), [
                    'request_id' => $requestId,
                    'response_time_ms' => round($responseTime * 1000, 2)
                ]);
            }
            
            return $this->formatErrorResponse(
                'Failed to get transaction status',
                $response->json(),
                ['status_code' => $response->status()]
            );
            
        } catch (\Exception $e) {
            Log::error('Transaction status check failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            return $this->formatErrorResponse('Failed to check transaction status');
        }
    }
}