<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ResellerApiService
{
    private $baseUrl;
    private $apiKey;
    private $timeout;
    private $logger;

    public function __construct()
    {
        $this->baseUrl = config('services.reseller.api_url', 'https://reseller.co.tz/api');
        $this->apiKey = config('services.reseller.api_key');
        $this->timeout = config('services.reseller.timeout', 30);
        $this->logger = new ResellerApiLogger();
    }

    /**
     * Check domain availability
     */
    public function checkDomainAvailability($domainName)
    {
        try {
            $this->logger->logDomainOperation('check_availability', $domainName);
            
            $response = $this->makeRequest([
                'action' => 'checkDomain',
                'domainName' => $domainName
            ]);

            $result = $this->handleResponse($response);
            
            // Log the result based on the actual response structure
            if ($result['status'] === 'success') {
                $this->logger->logDomainOperation('check_availability', $domainName, [], [
                    'success' => true,
                    'available' => $result['data']['available'] ?? false
                ]);
            } else {
                $this->logger->logDomainOperation('check_availability', $domainName, [], [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }
            
            return $result;
        } catch (Exception $e) {
            $this->logger->logDomainOperation('check_availability', $domainName, [], [
                'success' => false,
                'error' => $e->getMessage()
            ]);
            
            Log::error('Domain availability check failed', [
                'domain' => $domainName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Register a new domain
     */
    public function registerDomain($domainData)
    {
        $domainName = $domainData['domainName'] ?? 'unknown';
        
        try {
            $this->logger->logDomainOperation('register', $domainName, [
                'period' => $domainData['period'] ?? 1,
                'registrant_name' => $domainData['registrantInfo']['name'] ?? 'unknown',
                'admin_name' => $domainData['adminInfo']['name'] ?? $domainData['registrantInfo']['name'] ?? 'unknown'
            ]);
            
            $payload = [
                'action' => 'registerDomain',
                'domainName' => $domainData['domainName'],
                'period' => $domainData['period'] ?? 1,
                'registrantInfo' => $domainData['registrantInfo'],
                'adminInfo' => $domainData['adminInfo'] ?? $domainData['registrantInfo'], // Use registrant as admin if not provided
                'nameservers' => $domainData['nameservers']
            ];

            $response = $this->makeRequest($payload);
            $result = $this->handleResponse($response);
            
            // Log the result based on the actual response structure
            if ($result['status'] === 'success') {
                $this->logger->logDomainOperation('register', $domainName, [], [
                    'success' => true,
                    'transaction_id' => $result['data']['transactionId'] ?? null,
                    'expiry_date' => $result['data']['expiryDate'] ?? null
                ]);
            } else {
                $this->logger->logDomainOperation('register', $domainName, [], [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->logDomainOperation('register', $domainName, [], [
                'success' => false,
                'error' => $e->getMessage()
            ]);
            
            Log::error('Domain registration failed', [
                'domain' => $domainName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Renew a domain
     */
    public function renewDomain($domainName, $period = 1)
    {
        try {
            $this->logger->logDomainOperation('renew', $domainName, [
                'period' => $period
            ]);
            
            $response = $this->makeRequest([
                'action' => 'renewDomain',
                'domainName' => $domainName,
                'period' => $period
            ]);

            $result = $this->handleResponse($response);
            
            // Log the result based on the actual response structure
            if ($result['status'] === 'success') {
                $this->logger->logDomainOperation('renew', $domainName, [], [
                    'success' => true,
                    'transaction_id' => $result['data']['transactionId'] ?? null,
                    'new_expiry_date' => $result['data']['expiryDate'] ?? null
                ]);
            } else {
                $this->logger->logDomainOperation('renew', $domainName, [], [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->logDomainOperation('renew', $domainName, [], [
                'success' => false,
                'error' => $e->getMessage()
            ]);
            
            Log::error('Domain renewal failed', [
                'domain' => $domainName,
                'period' => $period,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get domain information
     */
    public function getDomainInfo($domainName)
    {
        try {
            $response = $this->makeRequest([
                'action' => 'getDomainInfo',
                'domainName' => $domainName
            ]);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error('Domain info retrieval failed', [
                'domain' => $domainName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update domain nameservers
     */
    public function updateNameservers($domainName, $nameservers)
    {
        try {
            $response = $this->makeRequest([
                'action' => 'updateNameservers',
                'domainName' => $domainName,
                'nameservers' => $nameservers
            ]);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error('Nameserver update failed', [
                'domain' => $domainName,
                'nameservers' => $nameservers,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Make HTTP request to the API
     */
    private function makeRequest($payload)
    {
        if (!$this->apiKey) {
            $this->logger->logError(null, 'API key not configured', ['base_url' => $this->baseUrl]);
            throw new Exception('Reseller API key is not configured');
        }

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        // Log the request
        $requestId = $this->logger->logRequest('POST', $this->baseUrl, $payload, $headers);
        
        $startTime = microtime(true);
        
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($this->baseUrl, $payload);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log rate limit information if available
            if ($response->hasHeader('X-RateLimit-Limit')) {
                $this->logger->logRateLimit(
                    $requestId,
                    $response->header('X-RateLimit-Limit'),
                    $response->header('X-RateLimit-Remaining'),
                    $response->header('X-RateLimit-Reset')
                );
            }

            // Log the response
            $this->logger->logResponse(
                $requestId,
                $response->status(),
                $response->json(),
                $responseTime
            );

            // Always return the JSON response, even for failed HTTP status codes
            // The API returns proper error responses in JSON format
            $responseData = $response->json();
            
            if ($response->failed()) {
                $this->logger->logError($requestId, "HTTP request failed", [
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'response_data' => $responseData
                ]);
            }

            return $responseData;
            
        } catch (Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->logError($requestId, $e->getMessage(), [
                'response_time_ms' => $responseTime,
                'exception_type' => get_class($e)
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle API response
     */
    private function handleResponse($response)
    {
        if (!isset($response['status'])) {
            throw new Exception('Invalid API response format');
        }

        // Return error responses as-is, don't throw exceptions
        if ($response['status'] === 'error') {
            return $response;
        }

        // Handle success response with new format
        if ($response['status'] === 'success' && isset($response['data'])) {
            $data = $response['data'];
            
            // For domain availability check, handle the new response format
            if (isset($data['code']) && isset($data['message'])) {
                // This is the new format with code and message
                return [
                    'status' => 'success',
                    'data' => [
                        'available' => $data['available'] ?? false,
                        'domain' => $data['domain'] ?? null,
                        'code' => $data['code'],
                        'message' => $data['message']
                    ]
                ];
            }
        }

        return $response;
    }

    /**
     * Get user-friendly error message
     */
    private function getErrorMessage($statusCode)
    {
        $errorMessages = [
            400 => 'Bad Request - Invalid parameters',
            401 => 'Unauthorized - Invalid API key',
            402 => 'Payment Required - Insufficient balance',
            403 => 'Forbidden - Permission denied',
            404 => 'Not Found - Domain not found',
            409 => 'Conflict - Domain already registered',
            422 => 'Unprocessable Entity - Validation failed',
            429 => 'Too Many Requests - Rate limit exceeded',
            500 => 'Internal Server Error'
        ];

        return $errorMessages[$statusCode] ?? "HTTP Error {$statusCode}";
    }

}
