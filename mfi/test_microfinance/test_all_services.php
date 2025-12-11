#!/usr/bin/env php
<?php

/**
 * Comprehensive Service Test Script
 * Tests all API endpoints with detailed logging and responses
 * 
 * Usage: php test_all_services.php [environment]
 * Example: php test_all_services.php local
 *          php test_all_services.php staging
 */

class ServiceTestRunner
{
    private $baseUrl;
    private $apiKey;
    private $results = [];
    private $startTime;
    private $logFile;
    private $verbose = true;
    private $testData;
    
    public function __construct($environment = 'local')
    {
        $this->startTime = microtime(true);
        $this->setupEnvironment($environment);
        $this->setupLogging();
        $this->loadTestData();
    }
    
    private function setupEnvironment($environment)
    {
        switch ($environment) {
            case 'staging':
                $this->baseUrl = 'https://staging.example.com';
                $this->apiKey = 'staging_api_key';
                break;
            case 'production':
                $this->baseUrl = 'https://api.example.com';
                $this->apiKey = 'production_api_key';
                break;
            case 'local':
            default:
                $this->baseUrl = 'http://127.0.0.1:8000';
                $this->apiKey = 'local_test_api_key';
                break;
        }
        
        $this->output("Environment: $environment", 'INFO');
        $this->output("Base URL: {$this->baseUrl}", 'INFO');
    }
    
    private function setupLogging()
    {
        $timestamp = date('Y-m-d_H-i-s');
        $this->logFile = __DIR__ . "/test_results/service_test_{$timestamp}.log";
        
        if (!is_dir(__DIR__ . '/test_results')) {
            mkdir(__DIR__ . '/test_results', 0755, true);
        }
        
        file_put_contents($this->logFile, "Service Test Log - Started at " . date('Y-m-d H:i:s') . "\n");
        file_put_contents($this->logFile, str_repeat('=', 80) . "\n", FILE_APPEND);
    }
    
    private function loadTestData()
    {
        // Load test data for each service
        $this->testData = [
            'internal_funds_transfer' => [
                'source_account' => '06012040022',
                'destination_account' => '06012040023',
                'amount' => 10000.00,
                'currency' => 'TZS',
                'description' => 'Test internal transfer',
                'reference' => 'TEST_INT_' . time(),
                'beneficiary_name' => 'John Doe Test'
            ],
            'external_funds_transfer' => [
                'source_account' => '06012040022',
                'destination_account' => '1234567890',
                'destination_bank' => 'CRDB',
                'swift_code' => 'CORUTZTZ',
                'amount' => 50000.00,
                'currency' => 'TZS',
                'beneficiary_name' => 'Jane Smith External',
                'description' => 'Test external transfer',
                'reference' => 'TEST_EXT_' . time()
            ],
            'mini_statement' => [
                'account_number' => '06012040022',
                'limit' => 5,
                'channel_code' => 'MFINBC'
            ],
            'full_statement' => [
                'account_number' => '06012040022',
                'from_date' => date('Y-m-d', strtotime('-30 days')),
                'to_date' => date('Y-m-d'),
                'format' => 'json',
                'channel_code' => 'MFINBC'
            ],
            'account_lookup' => [
                'account_number' => '06012040022',
                'channel_code' => 'MFINBC'
            ],
            'utilities' => [
                'utility_type' => 'electricity',
                'account_number' => '06012040022',
                'meter_number' => 'LUKU123456',
                'amount' => 20000.00,
                'customer_name' => 'Test Customer',
                'phone_number' => '255700000000',
                'provider_code' => 'TANESCO'
            ],
            'sms' => [
                'phone_numbers' => ['255700000001', '255700000002'],
                'message' => 'Test SMS from API test script at ' . date('H:i:s'),
                'sender_id' => 'MFI',
                'sms_type' => 'TRANSACTIONAL'
            ],
            'direct_debit' => [
                'account_number' => '06012040022',
                'amount' => 100000.00,
                'mandate_reference' => 'DD_TEST_' . time(),
                'beneficiary_account' => '06012040023',
                'beneficiary_name' => 'Test Beneficiary',
                'frequency' => 'monthly',
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+12 months')),
                'bank_code' => 'NBC'
            ],
            'control_number_payments' => [
                'control_number' => '991234567890',
                'payer_name' => 'Test Payer',
                'payer_phone' => '255700000000',
                'amount' => 75000.00,
                'payment_description' => 'Test control number payment',
                'currency' => 'TZS',
                'account_number' => '06012040022'
            ],
            'luku' => [
                'meter_number' => '04123456789',
                'amount' => 10000.00,
                'customer_phone' => '255700000000',
                'customer_name' => 'Test LUKU Customer',
                'account_number' => '28012040011'
            ],
            'gepg' => [
                'bill_id' => 'BILL_' . time(),
                'payer_name' => 'Test GEPG Payer',
                'payer_phone' => '255700000000',
                'payer_email' => 'test@example.com',
                'amount' => 150000.00,
                'payment_option' => 'exact',
                'currency' => 'TZS',
                'account_number' => '06012040022'
            ],
            'pay_by_link' => [
                'amount' => 25000.00,
                'description' => 'Test payment link for debugging',
                'customer_email' => 'customer@test.com',
                'customer_phone' => '255700000000',
                'customer_name' => 'Test Link Customer',
                'expiry_hours' => 24,
                'callback_url' => 'https://webhook.site/test-callback',
                'currency' => 'TZS'
            ]
        ];
    }
    
    private function output($message, $level = 'INFO', $data = null)
    {
        $timestamp = date('Y-m-d H:i:s');
        $output = "[$timestamp] [$level] $message";
        
        if ($this->verbose) {
            switch ($level) {
                case 'ERROR':
                    echo "\033[31m$output\033[0m\n"; // Red
                    break;
                case 'SUCCESS':
                    echo "\033[32m$output\033[0m\n"; // Green
                    break;
                case 'WARNING':
                    echo "\033[33m$output\033[0m\n"; // Yellow
                    break;
                case 'DEBUG':
                    echo "\033[36m$output\033[0m\n"; // Cyan
                    break;
                default:
                    echo "$output\n";
            }
        }
        
        file_put_contents($this->logFile, "$output\n", FILE_APPEND);
        
        if ($data !== null) {
            $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($this->verbose) {
                echo $jsonData . "\n";
            }
            file_put_contents($this->logFile, $jsonData . "\n", FILE_APPEND);
        }
    }
    
    private function makeRequest($endpoint, $method = 'POST', $data = null, $headers = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-Key: ' . $this->apiKey,
            'X-Request-ID: ' . uniqid('TEST_'),
            'User-Agent: ServiceTestScript/1.0'
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        
        // Capture detailed info
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'duration_ms' => $duration,
            'response' => $response,
            'error' => $curlError,
            'info' => $curlInfo,
            'request_data' => $data
        ];
    }
    
    public function testService($serviceName, $endpoint, $testData)
    {
        $this->output(str_repeat('-', 80), 'INFO');
        $this->output("Testing: $serviceName", 'INFO');
        $this->output("Endpoint: $endpoint", 'INFO');
        $this->output(str_repeat('-', 80), 'INFO');
        
        // Log request data
        $this->output("Request Data:", 'DEBUG', $testData);
        
        // Make the request
        $result = $this->makeRequest($endpoint, 'POST', $testData);
        
        // Parse response
        $responseData = null;
        if ($result['response']) {
            $responseData = json_decode($result['response'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->output("Failed to parse JSON response: " . json_last_error_msg(), 'ERROR');
                $responseData = ['raw_response' => $result['response']];
            }
        }
        
        // Log detailed result
        $testResult = [
            'service' => $serviceName,
            'endpoint' => $endpoint,
            'timestamp' => date('Y-m-d H:i:s'),
            'http_code' => $result['http_code'],
            'duration_ms' => $result['duration_ms'],
            'success' => $result['success'],
            'request_id' => $responseData['request_id'] ?? null,
            'request_data' => $testData,
            'response_data' => $responseData,
            'curl_error' => $result['error'],
            'request_info' => [
                'url' => $result['info']['url'] ?? null,
                'content_type' => $result['info']['content_type'] ?? null,
                'total_time' => $result['info']['total_time'] ?? null,
                'namelookup_time' => $result['info']['namelookup_time'] ?? null,
                'connect_time' => $result['info']['connect_time'] ?? null,
                'size_upload' => $result['info']['size_upload'] ?? null,
                'size_download' => $result['info']['size_download'] ?? null,
                'request_header' => $result['info']['request_header'] ?? null
            ]
        ];
        
        // Store result
        $this->results[$serviceName] = $testResult;
        
        // Output result
        if ($result['success']) {
            $this->output("✓ SUCCESS - HTTP {$result['http_code']} - {$result['duration_ms']}ms", 'SUCCESS');
            if (isset($responseData['request_id'])) {
                $this->output("Request ID: {$responseData['request_id']}", 'SUCCESS');
            }
            if (isset($responseData['message'])) {
                $this->output("Message: {$responseData['message']}", 'SUCCESS');
            }
        } else {
            $this->output("✗ FAILED - HTTP {$result['http_code']} - {$result['duration_ms']}ms", 'ERROR');
            if ($result['error']) {
                $this->output("CURL Error: {$result['error']}", 'ERROR');
            }
            if (isset($responseData['message'])) {
                $this->output("Error Message: {$responseData['message']}", 'ERROR');
            }
        }
        
        // Log full response
        $this->output("Full Response:", 'DEBUG', $responseData);
        
        // Add delay between tests to avoid rate limiting
        sleep(1);
        
        return $testResult;
    }
    
    public function runAllTests()
    {
        $this->output("Starting comprehensive service tests...", 'INFO');
        $this->output(str_repeat('=', 80), 'INFO');
        
        $services = [
            'Internal Funds Transfer' => '/api/test-services/internal-funds-transfer',
            'External Funds Transfer' => '/api/test-services/external-funds-transfer',
            'Mini Statement' => '/api/test-services/mini-statement',
            'Full Statement' => '/api/test-services/full-statement',
            'Account Lookup' => '/api/test-services/account-lookup',
            'Utilities' => '/api/test-services/utilities',
            'SMS' => '/api/test-services/sms',
            'Direct Debit' => '/api/test-services/direct-debit',
            'Control Number Payments' => '/api/test-services/control-number-payments',
            'LUKU' => '/api/test-services/luku',
            'GEPG' => '/api/test-services/gepg',
            'Pay By Link' => '/api/test-services/pay-by-link'
        ];
        
        $testDataMap = [
            'Internal Funds Transfer' => 'internal_funds_transfer',
            'External Funds Transfer' => 'external_funds_transfer',
            'Mini Statement' => 'mini_statement',
            'Full Statement' => 'full_statement',
            'Account Lookup' => 'account_lookup',
            'Utilities' => 'utilities',
            'SMS' => 'sms',
            'Direct Debit' => 'direct_debit',
            'Control Number Payments' => 'control_number_payments',
            'LUKU' => 'luku',
            'GEPG' => 'gepg',
            'Pay By Link' => 'pay_by_link'
        ];
        
        foreach ($services as $serviceName => $endpoint) {
            $testDataKey = $testDataMap[$serviceName];
            $testData = $this->testData[$testDataKey];
            
            try {
                $this->testService($serviceName, $endpoint, $testData);
            } catch (Exception $e) {
                $this->output("Exception testing $serviceName: " . $e->getMessage(), 'ERROR');
                $this->output("Stack trace: " . $e->getTraceAsString(), 'ERROR');
            }
        }
        
        $this->generateReport();
    }
    
    public function testSingleService($serviceName)
    {
        $services = [
            'internal-funds-transfer' => [
                'name' => 'Internal Funds Transfer',
                'endpoint' => '/api/test-services/internal-funds-transfer',
                'data' => 'internal_funds_transfer'
            ],
            'external-funds-transfer' => [
                'name' => 'External Funds Transfer',
                'endpoint' => '/api/test-services/external-funds-transfer',
                'data' => 'external_funds_transfer'
            ],
            'mini-statement' => [
                'name' => 'Mini Statement',
                'endpoint' => '/api/test-services/mini-statement',
                'data' => 'mini_statement'
            ],
            'full-statement' => [
                'name' => 'Full Statement',
                'endpoint' => '/api/test-services/full-statement',
                'data' => 'full_statement'
            ],
            'account-lookup' => [
                'name' => 'Account Lookup',
                'endpoint' => '/api/test-services/account-lookup',
                'data' => 'account_lookup'
            ],
            'utilities' => [
                'name' => 'Utilities',
                'endpoint' => '/api/test-services/utilities',
                'data' => 'utilities'
            ],
            'sms' => [
                'name' => 'SMS',
                'endpoint' => '/api/test-services/sms',
                'data' => 'sms'
            ],
            'direct-debit' => [
                'name' => 'Direct Debit',
                'endpoint' => '/api/test-services/direct-debit',
                'data' => 'direct_debit'
            ],
            'control-number' => [
                'name' => 'Control Number Payments',
                'endpoint' => '/api/test-services/control-number-payments',
                'data' => 'control_number_payments'
            ],
            'luku' => [
                'name' => 'LUKU',
                'endpoint' => '/api/test-services/luku',
                'data' => 'luku'
            ],
            'gepg' => [
                'name' => 'GEPG',
                'endpoint' => '/api/test-services/gepg',
                'data' => 'gepg'
            ],
            'pay-by-link' => [
                'name' => 'Pay By Link',
                'endpoint' => '/api/test-services/pay-by-link',
                'data' => 'pay_by_link'
            ]
        ];
        
        if (!isset($services[$serviceName])) {
            $this->output("Unknown service: $serviceName", 'ERROR');
            $this->output("Available services: " . implode(', ', array_keys($services)), 'INFO');
            return;
        }
        
        $service = $services[$serviceName];
        $testData = $this->testData[$service['data']];
        
        $this->output("Testing single service: {$service['name']}", 'INFO');
        $this->testService($service['name'], $service['endpoint'], $testData);
        $this->generateReport();
    }
    
    private function generateReport()
    {
        $totalDuration = round(microtime(true) - $this->startTime, 2);
        
        $this->output(str_repeat('=', 80), 'INFO');
        $this->output("TEST SUMMARY REPORT", 'INFO');
        $this->output(str_repeat('=', 80), 'INFO');
        
        $successful = 0;
        $failed = 0;
        $totalResponseTime = 0;
        
        foreach ($this->results as $serviceName => $result) {
            if ($result['success']) {
                $successful++;
                $status = '✓ PASSED';
                $statusLevel = 'SUCCESS';
            } else {
                $failed++;
                $status = '✗ FAILED';
                $statusLevel = 'ERROR';
            }
            
            $totalResponseTime += $result['duration_ms'];
            
            $this->output(
                sprintf(
                    "%-30s %s (HTTP %d - %dms)",
                    $serviceName,
                    $status,
                    $result['http_code'],
                    $result['duration_ms']
                ),
                $statusLevel
            );
            
            if (!$result['success'] && isset($result['response_data']['message'])) {
                $this->output("  └─ Error: {$result['response_data']['message']}", 'ERROR');
            }
        }
        
        $this->output(str_repeat('-', 80), 'INFO');
        $this->output("Total Tests: " . count($this->results), 'INFO');
        $this->output("Successful: $successful", 'SUCCESS');
        $this->output("Failed: $failed", $failed > 0 ? 'ERROR' : 'INFO');
        $this->output("Average Response Time: " . round($totalResponseTime / max(count($this->results), 1), 2) . "ms", 'INFO');
        $this->output("Total Execution Time: {$totalDuration}s", 'INFO');
        $this->output(str_repeat('=', 80), 'INFO');
        
        // Save detailed results to JSON file
        $jsonFile = str_replace('.log', '.json', $this->logFile);
        file_put_contents($jsonFile, json_encode($this->results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->output("Detailed results saved to: $jsonFile", 'INFO');
        $this->output("Log file saved to: {$this->logFile}", 'INFO');
    }
    
    public function testListServices()
    {
        $this->output("Fetching available services...", 'INFO');
        
        $result = $this->makeRequest('/api/test-services/', 'GET');
        
        if ($result['response']) {
            $services = json_decode($result['response'], true);
            
            if (isset($services['services'])) {
                $this->output("Available Services:", 'INFO');
                $this->output(str_repeat('-', 80), 'INFO');
                
                foreach ($services['services'] as $service) {
                    $this->output("Service: {$service['name']}", 'INFO');
                    $this->output("  Endpoint: {$service['endpoint']}", 'DEBUG');
                    $this->output("  Method: {$service['method']}", 'DEBUG');
                    $this->output("  Description: {$service['description']}", 'DEBUG');
                    
                    if (isset($service['required_params'])) {
                        $this->output("  Required: " . json_encode($service['required_params']), 'DEBUG');
                    }
                    
                    if (isset($service['optional_params'])) {
                        $this->output("  Optional: " . json_encode($service['optional_params']), 'DEBUG');
                    }
                    
                    $this->output("", 'INFO');
                }
            }
        }
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line\n");
}

$environment = $argv[1] ?? 'local';
$action = $argv[2] ?? 'all';

$tester = new ServiceTestRunner($environment);

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           SERVICE API COMPREHENSIVE TEST SUITE               ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($action === 'list') {
    $tester->testListServices();
} elseif ($action === 'all') {
    $tester->runAllTests();
} else {
    $tester->testSingleService($action);
}

echo "\n";
echo "Test execution completed. Check the test_results directory for detailed logs.\n";
echo "\n";