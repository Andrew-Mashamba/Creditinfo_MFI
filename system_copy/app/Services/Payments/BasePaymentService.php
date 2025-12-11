<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Exception;

abstract class BasePaymentService
{
    protected $maxRetries = 3;
    protected $retryDelay = 1000; // milliseconds
    protected $timeout = 30; // seconds
    protected $connectTimeout = 10; // seconds
    
    /**
     * Send HTTP request with retry logic
     */
    protected function sendRequestWithRetry(string $method, string $url, array $options = [], int $maxRetries = null): array
    {
        $maxRetries = $maxRetries ?? $this->maxRetries;
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info("Payment API Request Attempt {$attempt}/{$maxRetries}", [
                    'url' => $url,
                    'method' => $method
                ]);
                
                $client = new Client([
                    'verify' => false,
                    'timeout' => $this->timeout,
                    'connect_timeout' => $this->connectTimeout
                ]);
                
                $startTime = microtime(true);
                $response = $client->request($method, $url, $options);
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                $statusCode = $response->getStatusCode();
                $responseBody = json_decode($response->getBody()->getContents(), true);
                
                Log::info("Payment API Response", [
                    'attempt' => $attempt,
                    'duration_ms' => $duration,
                    'status_code' => $statusCode,
                    'response_status' => $responseBody['statusCode'] ?? null,
                    'response_body' => $responseBody // Log full response for debugging
                ]);
                
                // Check for timeout errors in response
                if (isset($responseBody['body']) && is_array($responseBody['body'])) {
                    foreach ($responseBody['body'] as $item) {
                        if (isset($item['errorInformation']['errorCode']) && $item['errorInformation']['errorCode'] === '3200') {
                            // Processing timeout - retry
                            if ($attempt < $maxRetries) {
                                Log::warning("Processing timeout detected, retrying...", [
                                    'attempt' => $attempt,
                                    'error' => $item['errorInformation']['errorDescription']
                                ]);
                                usleep($this->retryDelay * 1000 * $attempt); // Exponential backoff
                                continue 2; // Continue outer loop
                            }
                        }
                    }
                }
                
                return [
                    'success' => true,
                    'data' => $responseBody,
                    'duration_ms' => $duration,
                    'attempts' => $attempt
                ];
                
            } catch (ConnectException $e) {
                $lastException = $e;
                Log::warning("Connection failed, attempt {$attempt}/{$maxRetries}", [
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt < $maxRetries) {
                    usleep($this->retryDelay * 1000 * $attempt); // Exponential backoff
                    continue;
                }
                
            } catch (RequestException $e) {
                $lastException = $e;
                
                // Check if it's a timeout
                if (strpos($e->getMessage(), 'cURL error 28') !== false || 
                    strpos($e->getMessage(), 'Operation timed out') !== false) {
                    
                    Log::warning("Request timeout, attempt {$attempt}/{$maxRetries}", [
                        'error' => $e->getMessage()
                    ]);
                    
                    if ($attempt < $maxRetries) {
                        usleep($this->retryDelay * 1000 * $attempt); // Exponential backoff
                        continue;
                    }
                } else {
                    // Non-timeout error, don't retry
                    Log::error("Request failed with non-timeout error", [
                        'error' => $e->getMessage()
                    ]);
                    
                    if ($e->hasResponse()) {
                        $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                        return [
                            'success' => false,
                            'error' => $errorResponse['message'] ?? $e->getMessage(),
                            'data' => $errorResponse,
                            'attempts' => $attempt
                        ];
                    }
                    
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'attempts' => $attempt
                    ];
                }
                
            } catch (Exception $e) {
                $lastException = $e;
                Log::error("Unexpected error in payment request", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt
                ]);
                
                if ($attempt < $maxRetries) {
                    usleep($this->retryDelay * 1000 * $attempt);
                    continue;
                }
            }
        }
        
        // All retries exhausted
        Log::error("All retry attempts exhausted", [
            'max_retries' => $maxRetries,
            'last_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ]);
        
        return [
            'success' => false,
            'error' => 'Request failed after ' . $maxRetries . ' attempts: ' . 
                      ($lastException ? $lastException->getMessage() : 'Unknown error'),
            'attempts' => $maxRetries
        ];
    }
    
    /**
     * Optimize NBC internal lookups
     */
    protected function optimizeForNBC(array $options): array
    {
        // For NBC internal lookups, reduce timeout and skip certain validations
        if (isset($options['json']['destinationFsp']) && $options['json']['destinationFsp'] === 'NLCBTZTX') {
            // Use shorter timeout for NBC
            $this->timeout = 10;
            $this->connectTimeout = 5;
            
            // Add optimization flag
            $options['json']['optimized'] = true;
        }
        
        return $options;
    }
    
    /**
     * Check if FSP is production ready
     */
    protected function isProductionReady(string $fspCode): bool
    {
        $workingFsps = config('working_fsps');
        
        // Check banks
        foreach ($workingFsps['banks'] ?? [] as $bank) {
            if ($bank['code'] === $fspCode && $bank['active']) {
                return true;
            }
        }
        
        // Check wallets
        foreach ($workingFsps['mobile_wallets'] ?? [] as $wallet) {
            if ($wallet['code'] === $fspCode && $wallet['active']) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get average response time for FSP
     */
    protected function getExpectedResponseTime(string $fspCode): int
    {
        $workingFsps = config('working_fsps');
        
        // Check banks
        foreach ($workingFsps['banks'] ?? [] as $bank) {
            if ($bank['code'] === $fspCode) {
                return $bank['average_response_time'] ?? 5000;
            }
        }
        
        // Check wallets  
        foreach ($workingFsps['mobile_wallets'] ?? [] as $wallet) {
            if ($wallet['code'] === $fspCode) {
                return $wallet['average_response_time'] ?? 5000;
            }
        }
        
        return 5000; // Default 5 seconds
    }
}