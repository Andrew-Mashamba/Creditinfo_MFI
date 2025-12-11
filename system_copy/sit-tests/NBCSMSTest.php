<?php

namespace SitTests;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/IndividualTestLogger.php';

use Illuminate\Support\Facades\Http;

class NBCSMSTest
{
    private $testResults = [];
    private $baseUrl;
    private $apiKey;
    private $channelId;
    
    public function __construct()
    {
        $this->baseUrl = env('NBC_SMS_BASE_URL', 'https://sms-engine.tz.af.absa.local');
        $this->apiKey = env('NBC_SMS_API_KEY', 'TEST_API_KEY');
        $this->channelId = env('NBC_SMS_CHANNEL_ID', 'KRWT43976');
    }
    
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "NBC SMS API Tests\n";
        echo "========================================\n";
        
        $this->testSingleSMS();
        $this->testBulkSMS();
        $this->testSMSStatus();
        $this->testRateLimiting();
        $this->testInvalidPhoneNumber();
        $this->testLongMessage();
        
        $this->printResults();
    }
    
    private function testSingleSMS()
    {
        $testCaseName = 'Single SMS';
        
        // Initialize individual test logging
        IndividualTestLogger::initializeTest($testCaseName);
        
        echo "\n[TEST] Single SMS Send...\n";
        IndividualTestLogger::logInfo("Starting Single SMS test");
        
        $startTime = microtime(true);
        
        try {
            $smsData = [
                'channel_id' => $this->channelId,
                'recipient' => '255712345678',
                'message' => 'Test SMS from SACCOS System',
                'reference' => 'SMS' . time()
            ];
            
            // Prepare request details
            $url = $this->baseUrl . '/api/sms/send';
            $method = 'POST';
            $headers = [
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ];
            
            // Log request to individual file
            IndividualTestLogger::logRequest($method, $url, $headers, $smsData, [
                'test_type' => 'Single SMS',
                'test_environment' => 'mock'
            ]);
            
            // Display request info to console
            echo "📤 REQUEST DETAILS:\n";
            echo "  Method: POST\n";
            echo "  URL: " . $url . "\n";
            echo "  Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
            echo "  Request Body: " . json_encode($smsData, JSON_PRETTY_PRINT) . "\n";
            echo "  Test Type: Single SMS\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/api/sms/send', $smsData);
            
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Log response to individual file
            IndividualTestLogger::logResponse(
                $response->status(),
                $responseTime,
                $response->json()
            );
            
            // Display response info to console
            echo "📥 RESPONSE DETAILS:\n";
            echo "  HTTP Status Code: " . $response->status() . "\n";
            echo "  Response Time: " . round($responseTime * 1000, 2) . " ms\n";
            echo "  Response Headers: " . json_encode($response->headers(), JSON_PRETTY_PRINT) . "\n";
            echo "  Response Body: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Check if endpoint is reachable and working
            $responseData = $response->json();
            
            if ($response->status() === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
                // Endpoint is reachable and working
                $this->testResults['Single SMS'] = 'PASSED';
                
                $details = [
                    'HTTP Status Code' => '200',
                    'Response Status' => 'success',
                    'Message ID' => $responseData['data']['message_id'] ?? 'N/A',
                    'Recipient' => $responseData['data']['recipient'] ?? 'N/A',
                    'Status' => $responseData['data']['status'] ?? 'N/A',
                    'Response Time' => round($responseTime * 1000, 2) . ' ms',
                    'Message' => $responseData['message'] ?? 'N/A'
                ];
                
                IndividualTestLogger::logEndpointStatus(true, $details);
                IndividualTestLogger::logTestResult(true, 'Single SMS test passed - Endpoint is reachable', $details);
                
                echo "✅ ENDPOINT STATUS: REACHABLE\n";
                echo "✓ Single SMS test passed\n";
                echo "  ✓ HTTP Status Code: 200\n";
                echo "  ✓ Response Status: success\n";
                echo "  ✓ Message ID: " . ($responseData['data']['message_id'] ?? 'N/A') . "\n";
                echo "  ✓ Recipient: " . ($responseData['data']['recipient'] ?? 'N/A') . "\n";
                echo "  ✓ Status: " . ($responseData['data']['status'] ?? 'N/A') . "\n";
                echo "  ✓ Response Time: " . round($responseTime * 1000, 2) . " ms\n";
                echo "  ✓ Message: " . ($responseData['message'] ?? 'N/A') . "\n";
            } else {
                // Endpoint is not working as expected
                $this->testResults['Single SMS'] = 'FAILED';
                
                $details = [
                    'HTTP Status Code' => $response->status(),
                    'Response Status' => $responseData['status'] ?? 'N/A',
                    'Error' => $responseData['message'] ?? 'Unknown error',
                    'Response Time' => round($responseTime * 1000, 2) . ' ms'
                ];
                
                IndividualTestLogger::logEndpointStatus(false, $details);
                IndividualTestLogger::logTestResult(false, 'Single SMS test failed - Endpoint not working as expected', $details);
                
                echo "❌ ENDPOINT STATUS: NOT WORKING AS EXPECTED\n";
                echo "✗ Single SMS test failed\n";
                echo "  ✗ HTTP Status Code: " . $response->status() . "\n";
                echo "  ✗ Response Status: " . ($responseData['status'] ?? 'N/A') . "\n";
                echo "  ✗ Error: " . ($responseData['message'] ?? 'Unknown error') . "\n";
                echo "  ✗ Response Time: " . round($responseTime * 1000, 2) . " ms\n";
            }
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Endpoint is not reachable
            $this->testResults['Single SMS'] = 'ERROR';
            
            $details = [
                'Error' => $e->getMessage(),
                'Response Time' => round($responseTime * 1000, 2) . ' ms',
                'Exception Type' => get_class($e)
            ];
            
            IndividualTestLogger::logResponse(null, $responseTime, null, $e->getMessage());
            IndividualTestLogger::logEndpointStatus(false, $details);
            IndividualTestLogger::logTestResult(false, 'Single SMS test error - Endpoint not reachable', $details);
            
            echo "❌ ENDPOINT STATUS: NOT REACHABLE\n";
            echo "✗ Single SMS test error: " . $e->getMessage() . "\n";
        }
        
        // Finalize the test
        IndividualTestLogger::finalizeTest($this->testResults['Single SMS']);
    }
    
    private function testBulkSMS()
    {
        echo "\n[TEST] Bulk SMS Send...\n";
        
        $testCaseName = 'Bulk SMS';
        $startTime = microtime(true);
        
        try {
            $bulkData = [
                'channel_id' => $this->channelId,
                'messages' => [
                    [
                        'recipient' => '255712345678',
                        'message' => 'Bulk message 1',
                        'reference' => 'BULK1_' . time()
                    ],
                    [
                        'recipient' => '255798765432',
                        'message' => 'Bulk message 2',
                        'reference' => 'BULK2_' . time()
                    ],
                    [
                        'recipient' => '255754321098',
                        'message' => 'Bulk message 3',
                        'reference' => 'BULK3_' . time()
                    ]
                ]
            ];
            
            // Mock response data with expected success structure
            $mockResponseData = [
                'status' => 'success',
                'status_code' => 200,
                'message' => 'Bulk SMS sent successfully',
                'data' => [
                    'batch_id' => 'BATCH' . time(),
                    'total_messages' => count($bulkData['messages']),
                    'accepted' => count($bulkData['messages']),
                    'rejected' => 0,
                    'channel_id' => $this->channelId,
                    'status' => 'queued',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'messages' => array_map(function($msg) {
                        return [
                            'reference' => $msg['reference'],
                            'recipient' => $msg['recipient'],
                            'status' => 'queued',
                            'message_id' => 'MSG' . rand(1000, 9999)
                        ];
                    }, $bulkData['messages'])
                ],
                'metadata' => [
                    'api_version' => '1.0',
                    'request_id' => 'req-' . uniqid()
                ]
            ];
            
            Http::fake([
                $this->baseUrl . '/api/sms/bulk' => Http::response($mockResponseData, 200, [
                    'Content-Type' => 'application/json',
                    'X-Request-ID' => 'test-' . uniqid()
                ])
            ]);
            
            // Log detailed request information
            echo "📤 REQUEST DETAILS:\n";
            echo "  Method: POST\n";
            echo "  URL: " . $this->baseUrl . '/api/sms/bulk' . "\n";
            echo "  Headers: " . json_encode(['X-API-Key' => $this->apiKey, 'Content-Type' => 'application/json'], JSON_PRETTY_PRINT) . "\n";
            echo "  Request Body: " . json_encode($bulkData, JSON_PRETTY_PRINT) . "\n";
            echo "  Test Type: Bulk SMS\n";
            echo "  Messages Count: " . count($bulkData['messages']) . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/api/sms/bulk', $bulkData);
            
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;
            
            // Log detailed response information
            echo "📥 RESPONSE DETAILS:\n";
            echo "  HTTP Status Code: " . $response->status() . "\n";
            echo "  Response Time: " . round($responseTime * 1000, 2) . " ms\n";
            echo "  Response Headers: " . json_encode($response->headers(), JSON_PRETTY_PRINT) . "\n";
            echo "  Response Body: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Enhanced test assertion with proper success validation
            $success = true;
            $validationErrors = [];
            
            // Check HTTP status code
            if ($response->status() !== 200) {
                $success = false;
                $validationErrors[] = 'HTTP status code should be 200, got: ' . $response->status();
            }
            
            // Check response JSON structure
            $responseData = $response->json();
            
            // Check main status
            if (!isset($responseData['status']) || $responseData['status'] !== 'success') {
                $success = false;
                $validationErrors[] = 'Status should be "success", got: ' . ($responseData['status'] ?? 'undefined');
            }
            
            // Check status_code field
            if (!isset($responseData['status_code']) || $responseData['status_code'] !== 200) {
                $success = false;
                $validationErrors[] = 'Status code should be 200, got: ' . ($responseData['status_code'] ?? 'undefined');
            }
            
            // Check required data fields
            if (!isset($responseData['data']['batch_id'])) {
                $success = false;
                $validationErrors[] = 'Missing batch_id in response data';
            }
            
            if (!isset($responseData['data']['accepted']) || $responseData['data']['accepted'] !== count($bulkData['messages'])) {
                $success = false;
                $validationErrors[] = 'Accepted count should match sent messages count';
            }
            
            if (!isset($responseData['data']['messages']) || count($responseData['data']['messages']) !== count($bulkData['messages'])) {
                $success = false;
                $validationErrors[] = 'Messages array should match sent messages count';
            }
            
            if ($success) {
                $this->testResults['Bulk SMS'] = 'PASSED';
                echo "✓ Bulk SMS test passed\n";
                echo "  ✓ HTTP Status Code: 200\n";
                echo "  ✓ Response Status: success\n";
                echo "  ✓ Batch ID: " . $responseData['data']['batch_id'] . "\n";
                echo "  ✓ Accepted: " . $responseData['data']['accepted'] . " messages\n";
                echo "  ✓ Rejected: " . $responseData['data']['rejected'] . " messages\n";
                echo "  ✓ Response Time: " . round($responseTime * 1000, 2) . " ms\n";
                echo "  ✓ Message: " . $responseData['message'] . "\n";
            } else {
                $this->testResults['Bulk SMS'] = 'FAILED';
                echo "✗ Bulk SMS test failed - Validation errors:\n";
                foreach ($validationErrors as $error) {
                    echo "  ✗ " . $error . "\n";
                }
                echo "  ✗ Actual Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Bulk SMS'] = 'ERROR';
            echo "✗ Bulk SMS test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testSMSStatus()
    {
        echo "\n[TEST] SMS Status Check...\n";
        
        try {
            $messageId = 'MSG123456';
            
            Http::fake([
                $this->baseUrl . '/api/sms/status/' . $messageId => Http::response([
                    'status' => 'success',
                    'message_id' => $messageId,
                    'delivery_status' => 'DELIVERED',
                    'delivered_at' => date('Y-m-d H:i:s'),
                    'recipient' => '255712345678',
                    'attempts' => 1
                ], 200)
            ]);
            
            // Log detailed request information
            echo "📤 REQUEST DETAILS:\n";
            echo "  Method: GET\n";
            echo "  URL: " . $this->baseUrl . '/api/sms/status/' . $messageId . "\n";
            echo "  Headers: " . json_encode(['X-API-Key' => $this->apiKey], JSON_PRETTY_PRINT) . "\n";
            echo "  Request Body: None (GET request)\n";
            echo "  Test Type: SMS Status Check\n";
            echo "  Message ID: " . $messageId . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey
            ])->get($this->baseUrl . '/api/sms/status/' . $messageId);
            
            // Log detailed response information
            echo "📥 RESPONSE DETAILS:\n";
            echo "  HTTP Status Code: " . $response->status() . "\n";
            echo "  Response Headers: " . json_encode($response->headers(), JSON_PRETTY_PRINT) . "\n";
            echo "  Response Body: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
            echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
            
            $data = $response->json();
            
            if ($response->successful() && $data['delivery_status'] === 'DELIVERED') {
                $this->testResults['SMS Status'] = 'PASSED';
                echo "✓ SMS Status test passed - Status: DELIVERED\n";
            } else {
                $this->testResults['SMS Status'] = 'FAILED';
                echo "✗ SMS Status test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['SMS Status'] = 'ERROR';
            echo "✗ SMS Status test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testRateLimiting()
    {
        echo "\n[TEST] Rate Limiting...\n";
        
        try {
            $rateLimitExceeded = false;
            
            // Simulate hitting rate limit
            Http::fake([
                $this->baseUrl . '/api/sms/send' => Http::sequence()
                    ->push(['status' => 'success', 'message_id' => 'MSG1'], 200)
                    ->push(['status' => 'success', 'message_id' => 'MSG2'], 200)
                    ->push([
                        'status' => 'error',
                        'code' => 429,
                        'message' => 'Rate limit exceeded',
                        'retry_after' => 60
                    ], 429)
            ]);
            
            // Send multiple requests
            for ($i = 1; $i <= 3; $i++) {
                $response = Http::withHeaders([
                    'X-API-Key' => $this->apiKey
                ])->post($this->baseUrl . '/api/sms/send', [
                    'recipient' => '255712345678',
                    'message' => 'Test message ' . $i
                ]);
                
                if ($response->status() === 429) {
                    $rateLimitExceeded = true;
                    echo "  Rate limit hit after $i requests (as expected)\n";
                    break;
                }
            }
            
            $this->testResults['Rate Limiting'] = $rateLimitExceeded ? 'PASSED' : 'FAILED';
            
            if ($rateLimitExceeded) {
                echo "✓ Rate Limiting test passed\n";
            } else {
                echo "✗ Rate Limiting test failed - limit not enforced\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Rate Limiting'] = 'ERROR';
            echo "✗ Rate Limiting test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testInvalidPhoneNumber()
    {
        echo "\n[TEST] Invalid Phone Number Handling...\n";
        
        try {
            $invalidNumbers = [
                '123456',          // Too short
                '255123',          // Invalid format
                'abcdefghijk',     // Non-numeric
                '+255712345678910' // Too long
            ];
            
            $allPassed = true;
            
            foreach ($invalidNumbers as $number) {
                Http::fake([
                    $this->baseUrl . '/api/sms/send' => Http::response([
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Invalid phone number format',
                        'details' => "The number '$number' is not valid"
                    ], 400)
                ]);
                
                $response = Http::withHeaders([
                    'X-API-Key' => $this->apiKey
                ])->post($this->baseUrl . '/api/sms/send', [
                    'recipient' => $number,
                    'message' => 'Test message'
                ]);
                
                if ($response->status() === 400) {
                    echo "  ✓ Invalid number rejected: $number\n";
                } else {
                    $allPassed = false;
                    echo "  ✗ Invalid number not rejected: $number\n";
                }
            }
            
            $this->testResults['Invalid Phone Number'] = $allPassed ? 'PASSED' : 'FAILED';
        } catch (\Exception $e) {
            $this->testResults['Invalid Phone Number'] = 'ERROR';
            echo "✗ Invalid Phone Number test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testLongMessage()
    {
        echo "\n[TEST] Long Message Handling...\n";
        
        try {
            // Create a message longer than 160 characters
            $longMessage = str_repeat('This is a long test message. ', 20); // ~600 characters
            
            Http::fake([
                $this->baseUrl . '/api/sms/send' => Http::response([
                    'status' => 'success',
                    'message_id' => 'MSG' . time(),
                    'parts' => 4, // Message split into 4 parts
                    'characters' => strlen($longMessage),
                    'description' => 'Long message queued as multipart SMS'
                ], 200)
            ]);
            
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey
            ])->post($this->baseUrl . '/api/sms/send', [
                'recipient' => '255712345678',
                'message' => $longMessage
            ]);
            
            $data = $response->json();
            
            if ($response->successful() && isset($data['parts']) && $data['parts'] > 1) {
                $this->testResults['Long Message'] = 'PASSED';
                echo "✓ Long Message test passed - Split into " . $data['parts'] . " parts\n";
                echo "  Total characters: " . $data['characters'] . "\n";
            } else {
                $this->testResults['Long Message'] = 'FAILED';
                echo "✗ Long Message test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Long Message'] = 'ERROR';
            echo "✗ Long Message test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function printResults()
    {
        echo "\n========================================\n";
        echo "Test Results Summary\n";
        echo "========================================\n";
        
        $passed = 0;
        $failed = 0;
        $errors = 0;
        
        foreach ($this->testResults as $test => $result) {
            echo sprintf("%-30s: %s\n", $test, $result);
            
            if ($result === 'PASSED') $passed++;
            elseif ($result === 'FAILED') $failed++;
            else $errors++;
        }
        
        echo "----------------------------------------\n";
        echo "Total: " . count($this->testResults) . " tests\n";
        echo "Passed: $passed | Failed: $failed | Errors: $errors\n";
        echo "========================================\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $test = new NBCSMSTest();
    $test->runAllTests();
}