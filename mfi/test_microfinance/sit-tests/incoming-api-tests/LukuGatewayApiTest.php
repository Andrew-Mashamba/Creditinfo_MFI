<?php

namespace SitTests\IncomingApi;

require_once __DIR__ . '/IncomingApiTestBase.php';

/**
 * Luku Gateway API Test Suite
 * 
 * Tests for /api/luku-gateway endpoints
 */
class LukuGatewayApiTest extends IncomingApiTestBase
{
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "Luku Gateway API Tests\n";
        echo "========================================\n";
        
        // Test authentication
        $this->testSecurityFeatures();
        
        // Test main endpoints
        $this->testMeterLookup();
        $this->testProcessPayment();
        $this->testCheckTokenStatus();
        $this->testPaymentCallback();
        
        // Test validation
        $this->testInputValidation();
        
        // Test error scenarios
        $this->testErrorScenarios();
        
        $this->printResults();
        $this->generateReport('Luku Gateway API');
    }
    
    /**
     * Test security features
     */
    private function testSecurityFeatures()
    {
        echo "\n[TEST] Security Features...\n";
        
        // Test authentication requirement
        $this->testAuthentication('/luku-gateway/meter/lookup', 'POST', [
            'meter_number' => '12345678'
        ]);
        
        // Test rate limiting for payment processing
        echo "\n  Testing payment processing rate limiting...\n";
        $this->testRateLimiting('/luku-gateway/payment', 'POST', [
            'meter_number' => '12345678',
            'amount' => 10000,
            'currency' => 'TZS'
        ], 30); // Lower limit for payment processing
    }
    
    /**
     * Test meter lookup endpoint
     */
    private function testMeterLookup()
    {
        echo "\n[TEST] Meter Lookup Endpoint...\n";
        
        // Test valid meter lookup
        $validMeter = [
            'meter_number' => '12345678901234',
            'provider' => 'TANESCO'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/meter/lookup', $validMeter, [], 'Meter Lookup');
        
        $validation = $this->validateResponse($response, ['status'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Meter lookup test passed\n";
            $this->testResults['Meter Lookup'] = 'PASSED';
            
            // Check response details
            if (isset($response['body']['meter_details'])) {
                echo "  ✓ Meter details returned\n";
                
                if (isset($response['body']['meter_details']['customer_name'])) {
                    echo "  ✓ Customer name: " . $response['body']['meter_details']['customer_name'] . "\n";
                }
                if (isset($response['body']['meter_details']['meter_status'])) {
                    echo "  ✓ Meter status: " . $response['body']['meter_details']['meter_status'] . "\n";
                }
                if (isset($response['body']['meter_details']['tariff'])) {
                    echo "  ✓ Tariff: " . $response['body']['meter_details']['tariff'] . "\n";
                }
            }
        } else {
            echo "  ✗ Meter lookup test failed\n";
            foreach ($validation['errors'] as $error) {
                echo "    - $error\n";
            }
            $this->testResults['Meter Lookup'] = 'FAILED';
        }
        
        // Test with LUKU meter
        echo "\n  Testing LUKU meter lookup...\n";
        $lukuMeter = [
            'meter_number' => '98765432109876',
            'provider' => 'LUKU'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/meter/lookup', $lukuMeter, [], 'LUKU Meter Lookup');
        
        if ($response['status'] === 200) {
            echo "  ✓ LUKU meter lookup successful\n";
            $this->testResults['LUKU Meter Lookup'] = 'PASSED';
        } else {
            echo "  ✗ LUKU meter lookup failed\n";
            $this->testResults['LUKU Meter Lookup'] = 'FAILED';
        }
    }
    
    /**
     * Test process payment endpoint
     */
    private function testProcessPayment()
    {
        echo "\n[TEST] Process Payment Endpoint...\n";
        
        // Test different payment scenarios
        $paymentScenarios = [
            'small_amount' => [
                'meter_number' => '12345678901234',
                'amount' => 5000,
                'currency' => 'TZS',
                'customer_phone' => '255712345678',
                'payment_reference' => 'LUKU_' . time()
            ],
            'large_amount' => [
                'meter_number' => '12345678901234',
                'amount' => 100000,
                'currency' => 'TZS',
                'customer_phone' => '255712345678',
                'payment_reference' => 'LUKU_LARGE_' . time()
            ],
            'with_callback' => [
                'meter_number' => '12345678901234',
                'amount' => 20000,
                'currency' => 'TZS',
                'customer_phone' => '255712345678',
                'payment_reference' => 'LUKU_CB_' . time(),
                'callback_url' => 'https://example.com/luku/callback'
            ]
        ];
        
        foreach ($paymentScenarios as $scenario => $data) {
            echo "\n  Testing $scenario payment...\n";
            
            $response = $this->makeRequest('POST', '/luku-gateway/payment', $data, [], ucfirst(str_replace('_', ' ', $scenario)));
            
            $validation = $this->validateResponse($response, ['status'], 200);
            
            if ($validation['passed']) {
                echo "  ✓ $scenario payment test passed\n";
                $this->testResults[ucfirst(str_replace('_', ' ', $scenario)) . ' Payment'] = 'PASSED';
                
                // Check payment response
                if (isset($response['body']['transaction_id'])) {
                    echo "  ✓ Transaction ID: " . $response['body']['transaction_id'] . "\n";
                }
                if (isset($response['body']['token'])) {
                    echo "  ✓ Token generated: " . substr($response['body']['token'], 0, 4) . "****\n";
                }
                if (isset($response['body']['units'])) {
                    echo "  ✓ Units: " . $response['body']['units'] . " kWh\n";
                }
                if (isset($response['body']['receipt_number'])) {
                    echo "  ✓ Receipt: " . $response['body']['receipt_number'] . "\n";
                }
            } else {
                echo "  ✗ $scenario payment test failed\n";
                $this->testResults[ucfirst(str_replace('_', ' ', $scenario)) . ' Payment'] = 'FAILED';
            }
        }
    }
    
    /**
     * Test check token status endpoint
     */
    private function testCheckTokenStatus()
    {
        echo "\n[TEST] Check Token Status Endpoint...\n";
        
        // First, process a payment to get a transaction ID
        $paymentData = [
            'meter_number' => '12345678901234',
            'amount' => 10000,
            'currency' => 'TZS',
            'customer_phone' => '255712345678',
            'payment_reference' => 'STATUS_TEST_' . time()
        ];
        
        $paymentResponse = $this->makeRequest('POST', '/luku-gateway/payment', $paymentData, [], 'Payment for Status Check');
        
        if (isset($paymentResponse['body']['transaction_id'])) {
            $transactionId = $paymentResponse['body']['transaction_id'];
            
            // Now check the token status
            echo "\n  Checking status for transaction: $transactionId\n";
            
            $statusData = [
                'transaction_id' => $transactionId,
                'meter_number' => '12345678901234'
            ];
            
            $response = $this->makeRequest('POST', '/luku-gateway/token/status', $statusData, [], 'Token Status');
            
            $validation = $this->validateResponse($response, ['status'], 200);
            
            if ($validation['passed']) {
                echo "  ✓ Token status test passed\n";
                $this->testResults['Token Status'] = 'PASSED';
                
                // Check status details
                if (isset($response['body']['token_status'])) {
                    echo "  ✓ Token status: " . $response['body']['token_status'] . "\n";
                }
                if (isset($response['body']['delivery_status'])) {
                    echo "  ✓ Delivery status: " . $response['body']['delivery_status'] . "\n";
                }
                if (isset($response['body']['timestamp'])) {
                    echo "  ✓ Timestamp: " . $response['body']['timestamp'] . "\n";
                }
            } else {
                echo "  ✗ Token status test failed\n";
                $this->testResults['Token Status'] = 'FAILED';
            }
        } else {
            echo "  ✗ Could not create payment for status test\n";
            $this->testResults['Token Status'] = 'FAILED';
        }
        
        // Test with non-existent transaction
        echo "\n  Testing non-existent transaction status...\n";
        $nonExistentData = [
            'transaction_id' => 'NONEXISTENT123',
            'meter_number' => '12345678901234'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/token/status', $nonExistentData, [], 'Non-existent Transaction');
        
        if ($response['status'] === 404) {
            echo "  ✓ Non-existent transaction handled correctly\n";
            $this->testResults['Non-existent Transaction Status'] = 'PASSED';
        } else {
            echo "  ✗ Non-existent transaction not handled properly\n";
            $this->testResults['Non-existent Transaction Status'] = 'FAILED';
        }
    }
    
    /**
     * Test payment callback endpoint
     */
    private function testPaymentCallback()
    {
        echo "\n[TEST] Payment Callback Endpoint...\n";
        
        $callbackData = [
            'transaction_id' => 'TXN_' . time(),
            'payment_status' => 'SUCCESS',
            'token' => '1234-5678-9012-3456-7890',
            'units' => '50.5',
            'meter_number' => '12345678901234',
            'amount' => 10000,
            'receipt_number' => 'RCP_' . time(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/callback', $callbackData, [], 'Payment Callback');
        
        $validation = $this->validateResponse($response, ['status'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Payment callback test passed\n";
            $this->testResults['Payment Callback'] = 'PASSED';
            
            // Check acknowledgment
            if (isset($response['body']['received']) && $response['body']['received'] === true) {
                echo "  ✓ Callback acknowledged\n";
            }
        } else {
            echo "  ✗ Payment callback test failed\n";
            $this->testResults['Payment Callback'] = 'FAILED';
        }
        
        // Test failed payment callback
        echo "\n  Testing failed payment callback...\n";
        $failedCallbackData = [
            'transaction_id' => 'TXN_FAILED_' . time(),
            'payment_status' => 'FAILED',
            'error_code' => 'INSUFFICIENT_FUNDS',
            'error_message' => 'Not enough balance',
            'meter_number' => '12345678901234',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/callback', $failedCallbackData, [], 'Failed Payment Callback');
        
        if ($response['status'] === 200) {
            echo "  ✓ Failed payment callback handled\n";
            $this->testResults['Failed Payment Callback'] = 'PASSED';
        } else {
            echo "  ✗ Failed payment callback not handled\n";
            $this->testResults['Failed Payment Callback'] = 'FAILED';
        }
    }
    
    /**
     * Test input validation
     */
    private function testInputValidation()
    {
        echo "\n[TEST] Input Validation...\n";
        
        // Test invalid meter number format
        echo "  Testing invalid meter number format...\n";
        $invalidMeter = [
            'meter_number' => 'INVALID',
            'provider' => 'TANESCO'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/meter/lookup', $invalidMeter, [], 'Invalid Meter Format');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid meter format rejected\n";
            $this->testResults['Meter Format Validation'] = 'PASSED';
        } else {
            echo "  ✗ Invalid meter format not rejected\n";
            $this->testResults['Meter Format Validation'] = 'FAILED';
        }
        
        // Test invalid amount
        echo "\n  Testing invalid payment amount...\n";
        $invalidAmount = [
            'meter_number' => '12345678901234',
            'amount' => -1000,
            'currency' => 'TZS'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/payment', $invalidAmount, [], 'Invalid Amount');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid amount rejected\n";
            $this->testResults['Amount Validation'] = 'PASSED';
        } else {
            echo "  ✗ Invalid amount not rejected\n";
            $this->testResults['Amount Validation'] = 'FAILED';
        }
        
        // Test missing required fields
        echo "\n  Testing missing required fields...\n";
        $missingFields = [
            'amount' => 10000
            // Missing meter_number
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/payment', $missingFields, [], 'Missing Fields');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Missing fields rejected\n";
            $this->testResults['Required Fields Validation'] = 'PASSED';
            
            if (isset($response['body']['errors'])) {
                echo "  ✓ Validation errors provided\n";
            }
        } else {
            echo "  ✗ Missing fields not rejected\n";
            $this->testResults['Required Fields Validation'] = 'FAILED';
        }
    }
    
    /**
     * Test error scenarios
     */
    private function testErrorScenarios()
    {
        echo "\n[TEST] Error Scenarios...\n";
        
        // Test invalid provider
        echo "  Testing invalid provider...\n";
        $invalidProvider = [
            'meter_number' => '12345678901234',
            'provider' => 'INVALID_PROVIDER'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/meter/lookup', $invalidProvider, [], 'Invalid Provider');
        
        if ($response['status'] === 400 || $response['status'] === 422) {
            echo "  ✓ Invalid provider rejected\n";
            $this->testResults['Invalid Provider'] = 'PASSED';
        } else {
            echo "  ⚠ Invalid provider handling unclear\n";
            $this->testResults['Invalid Provider'] = 'WARNING';
        }
        
        // Test blacklisted meter
        echo "\n  Testing blacklisted meter...\n";
        $blacklistedMeter = [
            'meter_number' => '00000000000000',  // Assuming this is blacklisted
            'amount' => 10000,
            'currency' => 'TZS'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/payment', $blacklistedMeter, [], 'Blacklisted Meter');
        
        if ($response['status'] === 403 || 
            (isset($response['body']['error_code']) && $response['body']['error_code'] === 'METER_BLACKLISTED')) {
            echo "  ✓ Blacklisted meter rejected\n";
            $this->testResults['Blacklisted Meter'] = 'PASSED';
        } else {
            echo "  ⚠ Blacklisted meter handling unclear\n";
            $this->testResults['Blacklisted Meter'] = 'WARNING';
        }
        
        // Test maximum amount exceeded
        echo "\n  Testing maximum amount exceeded...\n";
        $maxAmount = [
            'meter_number' => '12345678901234',
            'amount' => 10000000,  // 10 million
            'currency' => 'TZS'
        ];
        
        $response = $this->makeRequest('POST', '/luku-gateway/payment', $maxAmount, [], 'Max Amount Exceeded');
        
        if ($response['status'] === 400 || $response['status'] === 422) {
            echo "  ✓ Maximum amount limit enforced\n";
            $this->testResults['Max Amount Limit'] = 'PASSED';
            
            if (isset($response['body']['message'])) {
                echo "  ✓ Error message: " . $response['body']['message'] . "\n";
            }
        } else {
            echo "  ⚠ Maximum amount limit may not be enforced\n";
            $this->testResults['Max Amount Limit'] = 'WARNING';
        }
        
        // Test error handling
        $this->testErrorHandling('/luku-gateway/payment');
    }
}