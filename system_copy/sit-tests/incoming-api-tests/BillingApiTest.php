<?php

namespace SitTests\IncomingApi;

require_once __DIR__ . '/IncomingApiTestBase.php';

/**
 * Billing API Test Suite
 * 
 * Tests for /api/billing endpoints
 */
class BillingApiTest extends IncomingApiTestBase
{
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "Billing API Tests\n";
        echo "========================================\n";
        
        $this->testInquiryEndpoint();
        $this->testPaymentNotificationEndpoint();
        $this->testStatusCheckEndpoint();
        $this->testValidationRules();
        $this->testErrorScenarios();
        
        $this->printResults();
        $this->generateReport('Billing API');
    }
    
    /**
     * Test /api/billing/inquiry endpoint
     */
    private function testInquiryEndpoint()
    {
        echo "\n[TEST] Billing Inquiry Endpoint...\n";
        
        $testData = [
            'control_number' => '991700123456',
            'payer_name' => 'John Doe',
            'payer_phone' => '255712345678',
            'amount' => 50000,
            'currency' => 'TZS'
        ];
        
        $response = $this->makeRequest('POST', '/billing/inquiry', $testData, [], 'Billing Inquiry');
        
        // Validate response
        $validation = $this->validateResponse($response, ['status', 'message'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Billing inquiry test passed\n";
            $this->testResults['Billing Inquiry'] = 'PASSED';
            
            // Check response content
            if (isset($response['body']['bill_details'])) {
                echo "  ✓ Bill details returned\n";
            }
            if (isset($response['body']['control_number'])) {
                echo "  ✓ Control number: " . $response['body']['control_number'] . "\n";
            }
        } else {
            echo "  ✗ Billing inquiry test failed\n";
            foreach ($validation['errors'] as $error) {
                echo "    - $error\n";
            }
            $this->testResults['Billing Inquiry'] = 'FAILED';
        }
    }
    
    /**
     * Test /api/billing/payment-notify endpoint
     */
    private function testPaymentNotificationEndpoint()
    {
        echo "\n[TEST] Payment Notification Endpoint...\n";
        
        $testData = [
            'control_number' => '991700123456',
            'transaction_id' => 'TXN' . time(),
            'amount_paid' => 50000,
            'currency' => 'TZS',
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_method' => 'MOBILE',
            'payer_details' => [
                'name' => 'John Doe',
                'phone' => '255712345678',
                'email' => 'john@example.com'
            ]
        ];
        
        $response = $this->makeRequest('POST', '/billing/payment-notify', $testData, [], 'Payment Notification');
        
        // Validate response
        $validation = $this->validateResponse($response, ['status', 'message'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Payment notification test passed\n";
            $this->testResults['Payment Notification'] = 'PASSED';
            
            // Check for receipt
            if (isset($response['body']['receipt_number'])) {
                echo "  ✓ Receipt number: " . $response['body']['receipt_number'] . "\n";
            }
            if (isset($response['body']['transaction_status'])) {
                echo "  ✓ Transaction status: " . $response['body']['transaction_status'] . "\n";
            }
        } else {
            echo "  ✗ Payment notification test failed\n";
            foreach ($validation['errors'] as $error) {
                echo "    - $error\n";
            }
            $this->testResults['Payment Notification'] = 'FAILED';
        }
    }
    
    /**
     * Test /api/billing/status-check endpoint
     */
    private function testStatusCheckEndpoint()
    {
        echo "\n[TEST] Status Check Endpoint...\n";
        
        $testData = [
            'control_number' => '991700123456',
            'transaction_id' => 'TXN123456'
        ];
        
        $response = $this->makeRequest('POST', '/billing/status-check', $testData, [], 'Status Check');
        
        // Validate response
        $validation = $this->validateResponse($response, ['status'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Status check test passed\n";
            $this->testResults['Status Check'] = 'PASSED';
            
            // Check status details
            if (isset($response['body']['payment_status'])) {
                echo "  ✓ Payment status: " . $response['body']['payment_status'] . "\n";
            }
            if (isset($response['body']['paid_amount'])) {
                echo "  ✓ Paid amount: " . $response['body']['paid_amount'] . "\n";
            }
        } else {
            echo "  ✗ Status check test failed\n";
            foreach ($validation['errors'] as $error) {
                echo "    - $error\n";
            }
            $this->testResults['Status Check'] = 'FAILED';
        }
    }
    
    /**
     * Test validation rules
     */
    private function testValidationRules()
    {
        echo "\n[TEST] Validation Rules...\n";
        
        // Test with missing control number
        $invalidData = [
            'amount' => 50000,
            'currency' => 'TZS'
        ];
        
        $validData = [
            'control_number' => '991700123456',
            'amount' => 50000,
            'currency' => 'TZS',
            'payer_name' => 'Test User'
        ];
        
        $this->testValidation('/billing/inquiry', $invalidData, $validData);
        
        // Test with invalid amount
        echo "\n  Testing amount validation...\n";
        $invalidAmount = [
            'control_number' => '991700123456',
            'amount' => -1000,  // Negative amount
            'currency' => 'TZS'
        ];
        
        $response = $this->makeRequest('POST', '/billing/inquiry', $invalidAmount, [], 'Invalid Amount');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid amount rejected\n";
            $this->testResults['Amount Validation'] = 'PASSED';
        } else {
            echo "  ✗ Invalid amount not rejected\n";
            $this->testResults['Amount Validation'] = 'FAILED';
        }
        
        // Test with invalid control number format
        echo "\n  Testing control number format...\n";
        $invalidControlNumber = [
            'control_number' => 'INVALID',
            'amount' => 50000,
            'currency' => 'TZS'
        ];
        
        $response = $this->makeRequest('POST', '/billing/inquiry', $invalidControlNumber, [], 'Invalid Control Number');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid control number format rejected\n";
            $this->testResults['Control Number Format'] = 'PASSED';
        } else {
            echo "  ✗ Invalid control number format not rejected\n";
            $this->testResults['Control Number Format'] = 'FAILED';
        }
    }
    
    /**
     * Test error scenarios
     */
    private function testErrorScenarios()
    {
        echo "\n[TEST] Error Scenarios...\n";
        
        // Test duplicate payment notification
        echo "  Testing duplicate payment notification...\n";
        $paymentData = [
            'control_number' => '991700123456',
            'transaction_id' => 'DUP123456',
            'amount_paid' => 50000,
            'currency' => 'TZS',
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        // First payment
        $response1 = $this->makeRequest('POST', '/billing/payment-notify', $paymentData, [], 'First Payment');
        
        // Duplicate payment with same transaction ID
        $response2 = $this->makeRequest('POST', '/billing/payment-notify', $paymentData, [], 'Duplicate Payment');
        
        if ($response2['status'] === 409 || 
            (isset($response2['body']['status']) && $response2['body']['status'] === 'duplicate')) {
            echo "  ✓ Duplicate payment handled correctly\n";
            $this->testResults['Duplicate Payment Handling'] = 'PASSED';
        } else {
            echo "  ⚠ Duplicate payment may not be handled\n";
            $this->testResults['Duplicate Payment Handling'] = 'WARNING';
        }
        
        // Test non-existent control number
        echo "\n  Testing non-existent control number...\n";
        $nonExistentData = [
            'control_number' => '999999999999',
            'transaction_id' => 'TXN' . time()
        ];
        
        $response = $this->makeRequest('POST', '/billing/status-check', $nonExistentData, [], 'Non-existent Control Number');
        
        if ($response['status'] === 404 || 
            (isset($response['body']['status']) && $response['body']['status'] === 'not_found')) {
            echo "  ✓ Non-existent control number handled correctly\n";
            $this->testResults['Non-existent Control Number'] = 'PASSED';
        } else {
            echo "  ⚠ Non-existent control number handling unclear\n";
            $this->testResults['Non-existent Control Number'] = 'WARNING';
        }
        
        // Test error handling
        $this->testErrorHandling('/billing/inquiry');
    }
}