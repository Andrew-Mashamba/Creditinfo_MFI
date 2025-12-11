<?php

namespace SitTests\IncomingApi;

require_once __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

/**
 * Base class for Incoming API Tests
 * 
 * Provides common functionality for testing APIs exposed by the system
 */
abstract class IncomingApiTestBase
{
    protected $baseUrl;
    protected $apiKey;
    protected $testResults = [];
    protected $logDir;
    
    public function __construct()
    {
        $this->baseUrl = env('APP_URL', 'http://127.0.0.1:8000') . '/api';
        $this->apiKey = env('TEST_API_KEY', 'test_api_key_123');
        $this->logDir = __DIR__ . '/logs';
        
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Log API request
     */
    protected function logRequest($endpoint, $method, $headers, $body, $testName)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'test_name' => $testName,
            'endpoint' => $endpoint,
            'method' => $method,
            'url' => $this->baseUrl . $endpoint,
            'headers' => $this->sanitizeHeaders($headers),
            'body' => $this->sanitizeBody($body)
        ];
        
        $this->appendToLog('requests', $logEntry);
        
        return $logEntry;
    }
    
    /**
     * Log API response
     */
    protected function logResponse($testName, $statusCode, $headers, $body, $responseTime, $responseReceived = true, $error = null)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'test_name' => $testName,
            'response_received' => $responseReceived,
            'status_code' => $statusCode,
            'headers' => $headers,
            'body' => $body,
            'response_time_ms' => round($responseTime * 1000, 2),
            'error' => $error
        ];
        
        $this->appendToLog('responses', $logEntry);
        
        // Print response status
        if ($responseReceived) {
            echo "  ✓ Response received: YES\n";
            echo "  ✓ Status Code: $statusCode\n";
            echo "  ✓ Response Time: " . round($responseTime * 1000, 2) . " ms\n";
        } else {
            echo "  ✗ Response received: NO\n";
            if ($error) {
                echo "  ✗ Error: $error\n";
            }
        }
        
        return $logEntry;
    }
    
    /**
     * Make API request with logging
     */
    protected function makeRequest($method, $endpoint, $data = [], $headers = [], $testName = '')
    {
        $startTime = microtime(true);
        
        // Add default headers
        $headers = array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $headers);
        
        // Add API key if configured
        if ($this->apiKey) {
            $headers['X-API-Key'] = $this->apiKey;
        }
        
        // Log request
        $this->logRequest($endpoint, $method, $headers, $data, $testName);
        
        try {
            $url = $this->baseUrl . $endpoint;
            
            // Make request based on method
            $response = null;
            switch (strtoupper($method)) {
                case 'GET':
                    $response = Http::withHeaders($headers)->get($url, $data);
                    break;
                case 'POST':
                    $response = Http::withHeaders($headers)->post($url, $data);
                    break;
                case 'PUT':
                    $response = Http::withHeaders($headers)->put($url, $data);
                    break;
                case 'PATCH':
                    $response = Http::withHeaders($headers)->patch($url, $data);
                    break;
                case 'DELETE':
                    $response = Http::withHeaders($headers)->delete($url, $data);
                    break;
            }
            
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Log response
            $this->logResponse(
                $testName,
                $response->status(),
                $response->headers(),
                $response->json() ?? $response->body(),
                $responseTime,
                true
            );
            
            return [
                'success' => true,
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
                'response_time' => $responseTime,
                'response' => $response
            ];
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Log error
            $this->logResponse(
                $testName,
                null,
                [],
                null,
                $responseTime,
                false,
                $e->getMessage()
            );
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response_time' => $responseTime
            ];
        }
    }
    
    /**
     * Validate response structure
     */
    protected function validateResponse($response, $expectedFields = [], $expectedStatus = 200)
    {
        $validations = [
            'status_code_match' => false,
            'has_required_fields' => false,
            'response_format_valid' => false,
            'errors' => []
        ];
        
        // Check status code
        if ($response['status'] === $expectedStatus) {
            $validations['status_code_match'] = true;
        } else {
            $validations['errors'][] = "Expected status $expectedStatus, got {$response['status']}";
        }
        
        // Check response format
        if (is_array($response['body']) || is_object($response['body'])) {
            $validations['response_format_valid'] = true;
        } else {
            $validations['errors'][] = "Response is not valid JSON";
        }
        
        // Check required fields
        $missingFields = [];
        foreach ($expectedFields as $field) {
            if (!isset($response['body'][$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            $validations['has_required_fields'] = true;
        } else {
            $validations['errors'][] = "Missing required fields: " . implode(', ', $missingFields);
        }
        
        $validations['passed'] = $validations['status_code_match'] && 
                                 $validations['has_required_fields'] && 
                                 $validations['response_format_valid'];
        
        return $validations;
    }
    
    /**
     * Test authentication
     */
    protected function testAuthentication($endpoint, $method = 'POST', $data = [])
    {
        echo "\n[TEST] Authentication for $endpoint...\n";
        
        // Test without API key
        $response = $this->makeRequest($method, $endpoint, $data, [], 'No Auth');
        
        if ($response['status'] === 401 || $response['status'] === 403) {
            echo "  ✓ Endpoint requires authentication\n";
            $this->testResults['Authentication'] = 'PASSED';
            return true;
        } else if ($response['status'] === 200) {
            echo "  ⚠ Endpoint does not require authentication\n";
            $this->testResults['Authentication'] = 'WARNING';
            return false;
        } else {
            echo "  ✗ Unexpected response: " . $response['status'] . "\n";
            $this->testResults['Authentication'] = 'FAILED';
            return false;
        }
    }
    
    /**
     * Test rate limiting
     */
    protected function testRateLimiting($endpoint, $method = 'POST', $data = [], $limit = 60)
    {
        echo "\n[TEST] Rate Limiting for $endpoint...\n";
        
        $hitLimit = false;
        
        // Make rapid requests
        for ($i = 1; $i <= $limit + 5; $i++) {
            $response = $this->makeRequest($method, $endpoint, $data, [], "Rate Limit Test $i");
            
            if ($response['status'] === 429) {
                echo "  ✓ Rate limit hit after $i requests\n";
                $hitLimit = true;
                
                // Check for Retry-After header
                if (isset($response['headers']['Retry-After'])) {
                    echo "  ✓ Retry-After header present: " . $response['headers']['Retry-After'][0] . " seconds\n";
                }
                
                $this->testResults['Rate Limiting'] = 'PASSED';
                break;
            }
        }
        
        if (!$hitLimit) {
            echo "  ⚠ Rate limit not enforced (made $limit+ requests)\n";
            $this->testResults['Rate Limiting'] = 'WARNING';
        }
        
        return $hitLimit;
    }
    
    /**
     * Test input validation
     */
    protected function testValidation($endpoint, $invalidData = [], $validData = [])
    {
        echo "\n[TEST] Input Validation for $endpoint...\n";
        
        // Test with invalid data
        echo "  Testing with invalid data...\n";
        $response = $this->makeRequest('POST', $endpoint, $invalidData, [], 'Invalid Data');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid data rejected (Status: {$response['status']})\n";
            
            // Check for validation errors in response
            if (isset($response['body']['errors']) || isset($response['body']['message'])) {
                echo "  ✓ Validation errors returned\n";
            }
            
            $this->testResults['Input Validation - Invalid'] = 'PASSED';
        } else {
            echo "  ✗ Invalid data not rejected (Status: {$response['status']})\n";
            $this->testResults['Input Validation - Invalid'] = 'FAILED';
        }
        
        // Test with valid data
        if (!empty($validData)) {
            echo "  Testing with valid data...\n";
            $response = $this->makeRequest('POST', $endpoint, $validData, [], 'Valid Data');
            
            if ($response['status'] === 200 || $response['status'] === 201) {
                echo "  ✓ Valid data accepted\n";
                $this->testResults['Input Validation - Valid'] = 'PASSED';
            } else {
                echo "  ✗ Valid data rejected (Status: {$response['status']})\n";
                $this->testResults['Input Validation - Valid'] = 'FAILED';
            }
        }
    }
    
    /**
     * Test error handling
     */
    protected function testErrorHandling($endpoint)
    {
        echo "\n[TEST] Error Handling for $endpoint...\n";
        
        // Test with malformed JSON
        $headers = ['Content-Type' => 'application/json'];
        $malformedJson = '{invalid json}';
        
        // Make raw request
        $response = Http::withHeaders($headers)
            ->withBody($malformedJson, 'application/json')
            ->post($this->baseUrl . $endpoint);
        
        if ($response->status() === 400 || $response->status() === 422) {
            echo "  ✓ Malformed JSON handled properly\n";
            $this->testResults['Error Handling'] = 'PASSED';
        } else {
            echo "  ✗ Malformed JSON not handled properly\n";
            $this->testResults['Error Handling'] = 'FAILED';
        }
    }
    
    /**
     * Sanitize headers for logging
     */
    protected function sanitizeHeaders($headers)
    {
        $sensitive = ['x-api-key', 'authorization', 'token'];
        $sanitized = [];
        
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitive)) {
                $sanitized[$key] = '***REDACTED***';
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize body for logging
     */
    protected function sanitizeBody($body)
    {
        if (!is_array($body)) {
            return $body;
        }
        
        $sensitive = ['password', 'pin', 'api_key', 'secret', 'token'];
        $sanitized = $body;
        
        foreach ($sensitive as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '***REDACTED***';
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Append to log file
     */
    protected function appendToLog($type, $data)
    {
        $date = date('Y-m-d');
        $file = $this->logDir . "/{$date}_{$type}.json";
        
        $logs = [];
        if (file_exists($file)) {
            $logs = json_decode(file_get_contents($file), true) ?? [];
        }
        
        $logs[] = $data;
        
        file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT));
    }
    
    /**
     * Print test results
     */
    protected function printResults()
    {
        echo "\n========================================\n";
        echo "Test Results Summary\n";
        echo "========================================\n";
        
        $passed = 0;
        $failed = 0;
        $warnings = 0;
        
        foreach ($this->testResults as $test => $result) {
            echo sprintf("%-40s: %s\n", $test, $result);
            
            if ($result === 'PASSED') $passed++;
            elseif ($result === 'FAILED') $failed++;
            elseif ($result === 'WARNING') $warnings++;
        }
        
        echo "----------------------------------------\n";
        echo "Total: " . count($this->testResults) . " tests\n";
        echo "Passed: $passed | Failed: $failed | Warnings: $warnings\n";
        echo "========================================\n";
    }
    
    /**
     * Generate test report
     */
    protected function generateReport($serviceName)
    {
        $report = [
            'service' => $serviceName,
            'timestamp' => date('Y-m-d H:i:s'),
            'base_url' => $this->baseUrl,
            'tests' => $this->testResults,
            'summary' => [
                'total' => count($this->testResults),
                'passed' => count(array_filter($this->testResults, fn($r) => $r === 'PASSED')),
                'failed' => count(array_filter($this->testResults, fn($r) => $r === 'FAILED')),
                'warnings' => count(array_filter($this->testResults, fn($r) => $r === 'WARNING'))
            ]
        ];
        
        $file = $this->logDir . '/' . date('Y-m-d') . '_' . str_replace(' ', '_', $serviceName) . '_report.json';
        file_put_contents($file, json_encode($report, JSON_PRETTY_PRINT));
        
        return $report;
    }
}