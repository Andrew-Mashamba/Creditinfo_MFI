<?php

namespace SitTests\IncomingApi;

require_once __DIR__ . '/IncomingApiTestBase.php';

/**
 * NBC Payment Callback API Test Suite
 * 
 * Tests for /api/v1/nbc-payments/callback endpoint
 */
class NbcPaymentCallbackApiTest extends IncomingApiTestBase
{
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "NBC Payment Callback API Tests\n";
        echo "========================================\n";
        
        // Test authentication and security
        $this->testSecurityFeatures();
        
        // Test callback scenarios
        $this->testSuccessfulPaymentCallback();
        $this->testFailedPaymentCallback();
        $this->testPendingPaymentCallback();
        
        // Test validation
        $this->testCallbackValidation();
        
        // Test idempotency
        $this->testCallbackIdempotency();
        
        // Test error scenarios
        $this->testErrorScenarios();
        
        $this->printResults();
        $this->generateReport('NBC Payment Callback API');
    }
    
    /**
     * Test security features
     */
    private function testSecurityFeatures()
    {
        echo "\n[TEST] Security Features...\n";
        
        // Test signature verification
        echo "  Testing signature verification...\n";
        
        $callbackData = [
            'transaction_id' => 'NBC_TXN_' . time(),
            'reference_number' => 'REF_' . time(),
            'status' => 'SUCCESS',
            'amount' => 50000,
            'currency' => 'TZS'
        ];
        
        // Test without signature
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $callbackData, [], 'No Signature');
        
        if ($response['status'] === 401 || $response['status'] === 403) {
            echo "  ✓ Callback without signature rejected\n";
            $this->testResults['Signature Verification'] = 'PASSED';
        } else {
            echo "  ✗ Callback without signature not rejected\n";
            $this->testResults['Signature Verification'] = 'FAILED';
        }
        
        // Test with invalid signature
        echo "\n  Testing invalid signature...\n";
        $headers = [
            'X-NBC-Signature' => 'invalid_signature_123'
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $callbackData, $headers, 'Invalid Signature');
        
        if ($response['status'] === 401 || $response['status'] === 403) {
            echo "  ✓ Invalid signature rejected\n";
            $this->testResults['Invalid Signature'] = 'PASSED';
        } else {
            echo "  ✗ Invalid signature not rejected\n";
            $this->testResults['Invalid Signature'] = 'FAILED';
        }
        
        // Test with valid signature (mock)
        echo "\n  Testing valid signature...\n";
        $validSignature = $this->generateMockSignature($callbackData);
        $headers = [
            'X-NBC-Signature' => $validSignature
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $callbackData, $headers, 'Valid Signature');
        
        if ($response['status'] === 200) {
            echo "  ✓ Valid signature accepted\n";
            $this->testResults['Valid Signature'] = 'PASSED';
        } else {
            echo "  ⚠ Valid signature handling unclear\n";
            $this->testResults['Valid Signature'] = 'WARNING';
        }
    }
    
    /**
     * Test successful payment callback
     */
    private function testSuccessfulPaymentCallback()
    {
        echo "\n[TEST] Successful Payment Callback...\n";
        
        $successData = [
            'transaction_id' => 'NBC_SUCCESS_' . time(),
            'reference_number' => 'REF_SUCCESS_' . time(),
            'status' => 'SUCCESS',
            'amount' => 100000,
            'currency' => 'TZS',
            'payer_details' => [
                'name' => 'John Doe',
                'account' => '1234567890',
                'phone' => '255712345678'
            ],
            'payment_method' => 'BANK_TRANSFER',
            'timestamp' => date('Y-m-d H:i:s'),
            'receipt_number' => 'RCP_' . time()
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($successData)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $successData, $headers, 'Successful Payment');
        
        $validation = $this->validateResponse($response, ['status', 'message'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Successful payment callback test passed\n";
            $this->testResults['Successful Payment Callback'] = 'PASSED';
            
            // Check acknowledgment
            if (isset($response['body']['received']) && $response['body']['received'] === true) {
                echo "  ✓ Callback acknowledged\n";
            }
            if (isset($response['body']['transaction_id'])) {
                echo "  ✓ Transaction ID confirmed: " . $response['body']['transaction_id'] . "\n";
            }
        } else {
            echo "  ✗ Successful payment callback test failed\n";
            foreach ($validation['errors'] as $error) {
                echo "    - $error\n";
            }
            $this->testResults['Successful Payment Callback'] = 'FAILED';
        }
    }
    
    /**
     * Test failed payment callback
     */
    private function testFailedPaymentCallback()
    {
        echo "\n[TEST] Failed Payment Callback...\n";
        
        $failedData = [
            'transaction_id' => 'NBC_FAILED_' . time(),
            'reference_number' => 'REF_FAILED_' . time(),
            'status' => 'FAILED',
            'amount' => 50000,
            'currency' => 'TZS',
            'error_code' => 'INSUFFICIENT_FUNDS',
            'error_message' => 'The account has insufficient funds',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($failedData)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $failedData, $headers, 'Failed Payment');
        
        if ($response['status'] === 200) {
            echo "  ✓ Failed payment callback handled\n";
            $this->testResults['Failed Payment Callback'] = 'PASSED';
            
            // Check if failure is acknowledged
            if (isset($response['body']['status']) && $response['body']['status'] === 'received') {
                echo "  ✓ Failure acknowledged\n";
            }
        } else {
            echo "  ✗ Failed payment callback not handled properly\n";
            $this->testResults['Failed Payment Callback'] = 'FAILED';
        }
    }
    
    /**
     * Test pending payment callback
     */
    private function testPendingPaymentCallback()
    {
        echo "\n[TEST] Pending Payment Callback...\n";
        
        $pendingData = [
            'transaction_id' => 'NBC_PENDING_' . time(),
            'reference_number' => 'REF_PENDING_' . time(),
            'status' => 'PENDING',
            'amount' => 75000,
            'currency' => 'TZS',
            'message' => 'Payment is being processed',
            'expected_completion' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($pendingData)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $pendingData, $headers, 'Pending Payment');
        
        if ($response['status'] === 200) {
            echo "  ✓ Pending payment callback handled\n";
            $this->testResults['Pending Payment Callback'] = 'PASSED';
            
            // Check if pending status is acknowledged
            if (isset($response['body']['status'])) {
                echo "  ✓ Pending status acknowledged\n";
            }
        } else {
            echo "  ✗ Pending payment callback not handled properly\n";
            $this->testResults['Pending Payment Callback'] = 'FAILED';
        }
    }
    
    /**
     * Test callback validation
     */
    private function testCallbackValidation()
    {
        echo "\n[TEST] Callback Validation...\n";
        
        // Test missing transaction ID
        echo "  Testing missing transaction ID...\n";
        $missingTransactionId = [
            'reference_number' => 'REF_' . time(),
            'status' => 'SUCCESS',
            'amount' => 50000,
            'currency' => 'TZS'
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($missingTransactionId)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $missingTransactionId, $headers, 'Missing Transaction ID');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Missing transaction ID rejected\n";
            $this->testResults['Missing Transaction ID'] = 'PASSED';
        } else {
            echo "  ✗ Missing transaction ID not rejected\n";
            $this->testResults['Missing Transaction ID'] = 'FAILED';
        }
        
        // Test invalid status
        echo "\n  Testing invalid status...\n";
        $invalidStatus = [
            'transaction_id' => 'NBC_INVALID_' . time(),
            'reference_number' => 'REF_' . time(),
            'status' => 'INVALID_STATUS',
            'amount' => 50000,
            'currency' => 'TZS'
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($invalidStatus)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $invalidStatus, $headers, 'Invalid Status');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid status rejected\n";
            $this->testResults['Invalid Status'] = 'PASSED';
        } else {
            echo "  ✗ Invalid status not rejected\n";
            $this->testResults['Invalid Status'] = 'FAILED';
        }
        
        // Test negative amount
        echo "\n  Testing negative amount...\n";
        $negativeAmount = [
            'transaction_id' => 'NBC_NEG_' . time(),
            'reference_number' => 'REF_' . time(),
            'status' => 'SUCCESS',
            'amount' => -1000,
            'currency' => 'TZS'
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($negativeAmount)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $negativeAmount, $headers, 'Negative Amount');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Negative amount rejected\n";
            $this->testResults['Negative Amount'] = 'PASSED';
        } else {
            echo "  ✗ Negative amount not rejected\n";
            $this->testResults['Negative Amount'] = 'FAILED';
        }
    }
    
    /**
     * Test callback idempotency
     */
    private function testCallbackIdempotency()
    {
        echo "\n[TEST] Callback Idempotency...\n";
        
        $callbackData = [
            'transaction_id' => 'NBC_IDEM_' . time(),
            'reference_number' => 'REF_IDEM_' . time(),
            'status' => 'SUCCESS',
            'amount' => 25000,
            'currency' => 'TZS',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($callbackData)
        ];
        
        // First callback
        echo "  Sending first callback...\n";
        $response1 = $this->makeRequest('POST', '/v1/nbc-payments/callback', $callbackData, $headers, 'Idempotency Test 1');
        
        if ($response1['status'] === 200) {
            // Second callback with same data
            echo "  Sending duplicate callback...\n";
            $response2 = $this->makeRequest('POST', '/v1/nbc-payments/callback', $callbackData, $headers, 'Idempotency Test 2');
            
            if ($response2['status'] === 200) {
                // Check if it's handled as duplicate
                if (isset($response2['body']['duplicate']) && $response2['body']['duplicate'] === true) {
                    echo "  ✓ Duplicate callback identified\n";
                    $this->testResults['Callback Idempotency'] = 'PASSED';
                } else if ($response2['status'] === 409) {
                    echo "  ✓ Duplicate callback rejected with 409\n";
                    $this->testResults['Callback Idempotency'] = 'PASSED';
                } else {
                    echo "  ⚠ Duplicate callback handling unclear\n";
                    $this->testResults['Callback Idempotency'] = 'WARNING';
                }
            } else {
                echo "  ✗ Second callback failed\n";
                $this->testResults['Callback Idempotency'] = 'FAILED';
            }
        } else {
            echo "  ✗ First callback failed\n";
            $this->testResults['Callback Idempotency'] = 'FAILED';
        }
    }
    
    /**
     * Test error scenarios
     */
    private function testErrorScenarios()
    {
        echo "\n[TEST] Error Scenarios...\n";
        
        // Test timeout simulation
        echo "  Testing timeout scenario...\n";
        $timeoutData = [
            'transaction_id' => 'NBC_TIMEOUT_' . time(),
            'reference_number' => 'REF_TIMEOUT_' . time(),
            'status' => 'TIMEOUT',
            'amount' => 30000,
            'currency' => 'TZS',
            'error_message' => 'Transaction timed out',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($timeoutData)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $timeoutData, $headers, 'Timeout Callback');
        
        if ($response['status'] === 200) {
            echo "  ✓ Timeout callback handled\n";
            $this->testResults['Timeout Handling'] = 'PASSED';
        } else {
            echo "  ✗ Timeout callback not handled\n";
            $this->testResults['Timeout Handling'] = 'FAILED';
        }
        
        // Test reversal callback
        echo "\n  Testing reversal callback...\n";
        $reversalData = [
            'transaction_id' => 'NBC_REVERSAL_' . time(),
            'original_transaction_id' => 'NBC_ORIGINAL_123456',
            'reference_number' => 'REF_REVERSAL_' . time(),
            'status' => 'REVERSED',
            'amount' => 45000,
            'currency' => 'TZS',
            'reversal_reason' => 'Customer request',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $headers = [
            'X-NBC-Signature' => $this->generateMockSignature($reversalData)
        ];
        
        $response = $this->makeRequest('POST', '/v1/nbc-payments/callback', $reversalData, $headers, 'Reversal Callback');
        
        if ($response['status'] === 200) {
            echo "  ✓ Reversal callback handled\n";
            $this->testResults['Reversal Handling'] = 'PASSED';
        } else {
            echo "  ✗ Reversal callback not handled\n";
            $this->testResults['Reversal Handling'] = 'FAILED';
        }
        
        // Test error handling
        $this->testErrorHandling('/v1/nbc-payments/callback');
    }
    
    /**
     * Generate mock signature for testing
     */
    private function generateMockSignature($data)
    {
        // Mock signature generation for testing
        // In production, this would use the actual NBC signature algorithm
        $secret = env('NBC_WEBHOOK_SECRET', 'test_secret');
        return hash_hmac('sha256', json_encode($data), $secret);
    }
}