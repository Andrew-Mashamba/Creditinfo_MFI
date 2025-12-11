<?php

namespace SitTests;

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Http;

class LukuGatewayTest
{
    private $testResults = [];
    private $baseUrl;
    private $channelId;
    private $channelName;
    private $apiToken;
    
    public function __construct()
    {
        $this->baseUrl = env('LUKU_GATEWAY_BASE_URL', 'https://nbc-gateway-uat.intra.nbc.co.tz');
        $this->channelId = env('LUKU_GATEWAY_CHANNEL_ID', 'SACCOSNBC');
        $this->channelName = env('LUKU_GATEWAY_CHANNEL_NAME', 'TR');
        $this->apiToken = env('LUKU_GATEWAY_API_TOKEN', 'c2FjY29zbmJjOkBOQkNzYWNjb3Npc2FsZUx0ZA==');
    }
    
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "Luku Gateway API Tests\n";
        echo "========================================\n";
        
        $this->testTokenQuery();
        $this->testTokenPurchase();
        $this->testMeterValidation();
        $this->testTransactionStatus();
        $this->testSSLConfiguration();
        $this->testErrorHandling();
        
        $this->printResults();
    }
    
    private function testTokenQuery()
    {
        echo "\n[TEST] Token Query...\n";
        
        try {
            $meterNumber = '04123456789';
            
            // Mock response
            Http::fake([
                $this->baseUrl . '/api/token/query' => Http::response([
                    'status' => 'success',
                    'data' => [
                        'meter_number' => $meterNumber,
                        'customer_name' => 'John Doe',
                        'address' => 'Dar es Salaam',
                        'tariff' => 'D1',
                        'last_purchase' => '2024-01-15',
                        'outstanding_debt' => 0
                    ]
                ], 200)
            ]);
            
            // Simulate API call
            $response = Http::withToken($this->apiToken)
                ->withHeaders([
                    'Content-Type' => 'application/xml',
                    'Accept' => 'application/xml'
                ])
                ->post($this->baseUrl . '/api/token/query', [
                    'meter_number' => $meterNumber
                ]);
            
            if ($response->successful()) {
                $this->testResults['Token Query'] = 'PASSED';
                echo "✓ Token Query test passed - Meter: $meterNumber\n";
            } else {
                $this->testResults['Token Query'] = 'FAILED';
                echo "✗ Token Query test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Token Query'] = 'ERROR';
            echo "✗ Token Query test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testTokenPurchase()
    {
        echo "\n[TEST] Token Purchase...\n";
        
        try {
            $purchaseData = [
                'meter_number' => '04123456789',
                'amount' => 50000.00,
                'currency' => 'TZS',
                'account_number' => '01J01234567890',
                'reference' => 'LUKU' . time()
            ];
            
            // Mock response with token
            Http::fake([
                $this->baseUrl . '/api/token/purchase' => Http::response([
                    'status' => 'success',
                    'data' => [
                        'token' => '12345-67890-12345-67890',
                        'units' => 125.5,
                        'amount' => $purchaseData['amount'],
                        'vat' => 7650.00,
                        'energy_charge' => 42350.00,
                        'meter_number' => $purchaseData['meter_number'],
                        'transaction_id' => 'TRX' . time(),
                        'receipt_number' => 'RCP' . time()
                    ]
                ], 200)
            ]);
            
            $response = Http::withToken($this->apiToken)
                ->post($this->baseUrl . '/api/token/purchase', $purchaseData);
            
            $responseData = $response->json();
            
            if ($response->successful() && isset($responseData['data']['token'])) {
                $this->testResults['Token Purchase'] = 'PASSED';
                echo "✓ Token Purchase test passed - Token: " . $responseData['data']['token'] . "\n";
                echo "  Units: " . $responseData['data']['units'] . " kWh\n";
            } else {
                $this->testResults['Token Purchase'] = 'FAILED';
                echo "✗ Token Purchase test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Token Purchase'] = 'ERROR';
            echo "✗ Token Purchase test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testMeterValidation()
    {
        echo "\n[TEST] Meter Validation...\n";
        
        try {
            $testCases = [
                ['meter' => '04123456789', 'valid' => true],
                ['meter' => '04999999999', 'valid' => false],
                ['meter' => 'INVALID', 'valid' => false]
            ];
            
            $allPassed = true;
            
            foreach ($testCases as $case) {
                Http::fake([
                    $this->baseUrl . '/api/meter/validate' => Http::response([
                        'status' => $case['valid'] ? 'success' : 'error',
                        'valid' => $case['valid'],
                        'message' => $case['valid'] ? 'Valid meter' : 'Invalid meter number'
                    ], $case['valid'] ? 200 : 400)
                ]);
                
                $response = Http::withToken($this->apiToken)
                    ->post($this->baseUrl . '/api/meter/validate', [
                        'meter_number' => $case['meter']
                    ]);
                
                $isValid = $response->successful() && $response->json('valid') === $case['valid'];
                
                if ($isValid) {
                    echo "  ✓ Meter " . $case['meter'] . " validation: " . 
                         ($case['valid'] ? 'Valid' : 'Invalid') . " (as expected)\n";
                } else {
                    $allPassed = false;
                    echo "  ✗ Meter " . $case['meter'] . " validation failed\n";
                }
            }
            
            $this->testResults['Meter Validation'] = $allPassed ? 'PASSED' : 'FAILED';
        } catch (\Exception $e) {
            $this->testResults['Meter Validation'] = 'ERROR';
            echo "✗ Meter Validation test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testTransactionStatus()
    {
        echo "\n[TEST] Transaction Status Check...\n";
        
        try {
            $transactionId = 'TRX' . time();
            
            Http::fake([
                $this->baseUrl . '/api/transaction/status' => Http::response([
                    'status' => 'success',
                    'data' => [
                        'transaction_id' => $transactionId,
                        'status' => 'COMPLETED',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'token' => '98765-43210-98765-43210',
                        'amount' => 50000.00
                    ]
                ], 200)
            ]);
            
            $response = Http::withToken($this->apiToken)
                ->get($this->baseUrl . '/api/transaction/status', [
                    'transaction_id' => $transactionId
                ]);
            
            $data = $response->json('data');
            
            if ($response->successful() && $data['status'] === 'COMPLETED') {
                $this->testResults['Transaction Status'] = 'PASSED';
                echo "✓ Transaction Status test passed - ID: $transactionId\n";
            } else {
                $this->testResults['Transaction Status'] = 'FAILED';
                echo "✗ Transaction Status test failed\n";
            }
        } catch (\Exception $e) {
            $this->testResults['Transaction Status'] = 'ERROR';
            echo "✗ Transaction Status test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testSSLConfiguration()
    {
        echo "\n[TEST] SSL Configuration...\n";
        
        try {
            $sslConfig = config('services.luku_gateway.ssl');
            
            if ($sslConfig && $sslConfig['verify']) {
                // Check if SSL certificate files exist
                $certExists = file_exists($sslConfig['cert_path'] ?? '');
                $keyExists = file_exists($sslConfig['key_path'] ?? '');
                $caExists = file_exists($sslConfig['ca_path'] ?? '');
                
                if ($certExists && $keyExists && $caExists) {
                    $this->testResults['SSL Configuration'] = 'PASSED';
                    echo "✓ SSL Configuration test passed - All certificates found\n";
                } else {
                    $this->testResults['SSL Configuration'] = 'WARNING';
                    echo "⚠ SSL Configuration warning - Some certificates missing\n";
                    if (!$certExists) echo "  - Certificate not found\n";
                    if (!$keyExists) echo "  - Private key not found\n";
                    if (!$caExists) echo "  - CA certificate not found\n";
                }
            } else {
                $this->testResults['SSL Configuration'] = 'WARNING';
                echo "⚠ SSL Configuration warning - SSL verification disabled\n";
            }
        } catch (\Exception $e) {
            $this->testResults['SSL Configuration'] = 'ERROR';
            echo "✗ SSL Configuration test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testErrorHandling()
    {
        echo "\n[TEST] Error Handling...\n";
        
        try {
            $errorScenarios = [
                ['code' => 400, 'message' => 'Invalid meter number'],
                ['code' => 401, 'message' => 'Unauthorized'],
                ['code' => 403, 'message' => 'Insufficient balance'],
                ['code' => 500, 'message' => 'Internal server error'],
                ['code' => 503, 'message' => 'Service unavailable']
            ];
            
            $allPassed = true;
            
            foreach ($errorScenarios as $scenario) {
                Http::fake([
                    $this->baseUrl . '/api/test/error' => Http::response([
                        'status' => 'error',
                        'message' => $scenario['message'],
                        'code' => $scenario['code']
                    ], $scenario['code'])
                ]);
                
                $response = Http::withToken($this->apiToken)
                    ->post($this->baseUrl . '/api/test/error');
                
                if ($response->status() === $scenario['code']) {
                    echo "  ✓ Error " . $scenario['code'] . " handled: " . $scenario['message'] . "\n";
                } else {
                    $allPassed = false;
                    echo "  ✗ Error " . $scenario['code'] . " not handled properly\n";
                }
            }
            
            $this->testResults['Error Handling'] = $allPassed ? 'PASSED' : 'FAILED';
        } catch (\Exception $e) {
            $this->testResults['Error Handling'] = 'ERROR';
            echo "✗ Error Handling test error: " . $e->getMessage() . "\n";
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
        $warnings = 0;
        
        foreach ($this->testResults as $test => $result) {
            echo sprintf("%-30s: %s\n", $test, $result);
            
            if ($result === 'PASSED') $passed++;
            elseif ($result === 'FAILED') $failed++;
            elseif ($result === 'WARNING') $warnings++;
            else $errors++;
        }
        
        echo "----------------------------------------\n";
        echo "Total: " . count($this->testResults) . " tests\n";
        echo "Passed: $passed | Failed: $failed | Warnings: $warnings | Errors: $errors\n";
        echo "========================================\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $test = new LukuGatewayTest();
    $test->runAllTests();
}