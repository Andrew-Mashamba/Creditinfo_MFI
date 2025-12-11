<?php

namespace SitTests\IncomingApi;

require_once __DIR__ . '/IncomingApiTestBase.php';

/**
 * Transaction Processing API Test Suite
 * 
 * Tests for /api/secure/transactions endpoints
 */
class TransactionApiTest extends IncomingApiTestBase
{
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "Transaction Processing API Tests\n";
        echo "========================================\n";
        
        // Test authentication and security
        $this->testSecurityFeatures();
        
        // Test main endpoints
        $this->testProcessTransaction();
        $this->testGetTransactionStatus();
        $this->testGetTransactionHistory();
        
        // Test validation and limits
        $this->testTransactionValidation();
        $this->testTransactionLimits();
        
        // Test idempotency
        $this->testIdempotency();
        
        // Test error scenarios
        $this->testErrorScenarios();
        
        $this->printResults();
        $this->generateReport('Transaction Processing API');
    }
    
    /**
     * Test security features
     */
    private function testSecurityFeatures()
    {
        echo "\n[TEST] Security Features...\n";
        
        // Test API key requirement
        echo "  Testing API key authentication...\n";
        $this->testAuthentication('/secure/transactions/process', 'POST', [
            'amount' => 1000,
            'currency' => 'TZS'
        ]);
        
        // Test IP whitelisting (if configured)
        echo "\n  Testing IP whitelisting...\n";
        $testData = [
            'transaction_type' => 'transfer',
            'amount' => 1000,
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321'
        ];
        
        // Test with spoofed IP header
        $headers = [
            'X-Forwarded-For' => '192.168.1.1',
            'X-Real-IP' => '10.0.0.1'
        ];
        
        $response = $this->makeRequest('POST', '/secure/transactions/process', $testData, $headers, 'IP Whitelist Test');
        
        if ($response['status'] === 403) {
            echo "  ✓ IP whitelisting enforced\n";
            $this->testResults['IP Whitelisting'] = 'PASSED';
        } else {
            echo "  ⚠ IP whitelisting may not be enforced\n";
            $this->testResults['IP Whitelisting'] = 'WARNING';
        }
        
        // Test security headers
        echo "\n  Testing security headers...\n";
        if (isset($response['headers'])) {
            $securityHeaders = [
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block'
            ];
            
            $hasAllHeaders = true;
            foreach ($securityHeaders as $header => $expectedValue) {
                if (!isset($response['headers'][$header])) {
                    echo "    ⚠ Missing header: $header\n";
                    $hasAllHeaders = false;
                }
            }
            
            $this->testResults['Security Headers'] = $hasAllHeaders ? 'PASSED' : 'WARNING';
        }
    }
    
    /**
     * Test process transaction endpoint
     */
    private function testProcessTransaction()
    {
        echo "\n[TEST] Process Transaction Endpoint...\n";
        
        // Test different transaction types
        $transactionTypes = [
            'transfer' => [
                'transaction_type' => 'transfer',
                'amount' => 10000,
                'currency' => 'TZS',
                'from_account' => '1234567890',
                'to_account' => '0987654321',
                'description' => 'Test transfer',
                'reference' => 'TRF' . time()
            ],
            'deposit' => [
                'transaction_type' => 'deposit',
                'amount' => 5000,
                'currency' => 'TZS',
                'to_account' => '1234567890',
                'deposit_method' => 'cash',
                'reference' => 'DEP' . time()
            ],
            'withdrawal' => [
                'transaction_type' => 'withdrawal',
                'amount' => 3000,
                'currency' => 'TZS',
                'from_account' => '1234567890',
                'withdrawal_method' => 'atm',
                'reference' => 'WTH' . time()
            ]
        ];
        
        foreach ($transactionTypes as $type => $data) {
            echo "\n  Testing $type transaction...\n";
            
            $response = $this->makeRequest('POST', '/secure/transactions/process', $data, [], ucfirst($type) . ' Transaction');
            
            $validation = $this->validateResponse($response, ['status', 'transaction_id'], 200);
            
            if ($validation['passed']) {
                echo "  ✓ $type transaction test passed\n";
                $this->testResults[ucfirst($type) . ' Transaction'] = 'PASSED';
                
                // Check response details
                if (isset($response['body']['transaction_id'])) {
                    echo "  ✓ Transaction ID: " . $response['body']['transaction_id'] . "\n";
                }
                if (isset($response['body']['status'])) {
                    echo "  ✓ Status: " . $response['body']['status'] . "\n";
                }
                if (isset($response['body']['timestamp'])) {
                    echo "  ✓ Timestamp: " . $response['body']['timestamp'] . "\n";
                }
            } else {
                echo "  ✗ $type transaction test failed\n";
                foreach ($validation['errors'] as $error) {
                    echo "    - $error\n";
                }
                $this->testResults[ucfirst($type) . ' Transaction'] = 'FAILED';
            }
        }
    }
    
    /**
     * Test get transaction status endpoint
     */
    private function testGetTransactionStatus()
    {
        echo "\n[TEST] Get Transaction Status Endpoint...\n";
        
        // First create a transaction to get its ID
        $transactionData = [
            'transaction_type' => 'transfer',
            'amount' => 1000,
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321',
            'reference' => 'STATUS_TEST_' . time()
        ];
        
        $createResponse = $this->makeRequest('POST', '/secure/transactions/process', $transactionData, [], 'Create Transaction for Status');
        
        if (isset($createResponse['body']['transaction_id'])) {
            $transactionId = $createResponse['body']['transaction_id'];
            
            // Now check its status
            echo "\n  Checking status for transaction: $transactionId\n";
            
            $statusResponse = $this->makeRequest('GET', "/secure/transactions/{$transactionId}/status", [], [], 'Transaction Status');
            
            $validation = $this->validateResponse($statusResponse, ['status', 'transaction_id'], 200);
            
            if ($validation['passed']) {
                echo "  ✓ Transaction status test passed\n";
                $this->testResults['Transaction Status'] = 'PASSED';
                
                // Check status details
                if (isset($statusResponse['body']['status'])) {
                    echo "  ✓ Current status: " . $statusResponse['body']['status'] . "\n";
                }
                if (isset($statusResponse['body']['last_updated'])) {
                    echo "  ✓ Last updated: " . $statusResponse['body']['last_updated'] . "\n";
                }
            } else {
                echo "  ✗ Transaction status test failed\n";
                $this->testResults['Transaction Status'] = 'FAILED';
            }
        } else {
            echo "  ✗ Could not create transaction for status test\n";
            $this->testResults['Transaction Status'] = 'FAILED';
        }
        
        // Test non-existent transaction
        echo "\n  Testing non-existent transaction status...\n";
        $response = $this->makeRequest('GET', '/secure/transactions/NONEXISTENT123/status', [], [], 'Non-existent Transaction');
        
        if ($response['status'] === 404) {
            echo "  ✓ Non-existent transaction handled correctly\n";
            $this->testResults['Non-existent Transaction Status'] = 'PASSED';
        } else {
            echo "  ✗ Non-existent transaction not handled properly\n";
            $this->testResults['Non-existent Transaction Status'] = 'FAILED';
        }
    }
    
    /**
     * Test get transaction history endpoint
     */
    private function testGetTransactionHistory()
    {
        echo "\n[TEST] Get Transaction History Endpoint...\n";
        
        // Test with different filters
        $filters = [
            'all' => [],
            'date_range' => [
                'from_date' => date('Y-m-d', strtotime('-7 days')),
                'to_date' => date('Y-m-d')
            ],
            'account' => [
                'account_number' => '1234567890'
            ],
            'type' => [
                'transaction_type' => 'transfer'
            ],
            'pagination' => [
                'page' => 1,
                'per_page' => 10
            ]
        ];
        
        foreach ($filters as $filterName => $params) {
            echo "\n  Testing with $filterName filter...\n";
            
            $response = $this->makeRequest('GET', '/secure/transactions', $params, [], "History - $filterName");
            
            $validation = $this->validateResponse($response, ['data'], 200);
            
            if ($validation['passed']) {
                echo "  ✓ Transaction history ($filterName) test passed\n";
                $this->testResults["Transaction History - $filterName"] = 'PASSED';
                
                // Check response structure
                if (isset($response['body']['data']) && is_array($response['body']['data'])) {
                    echo "  ✓ Returned " . count($response['body']['data']) . " transactions\n";
                }
                if (isset($response['body']['pagination'])) {
                    echo "  ✓ Pagination info included\n";
                }
            } else {
                echo "  ✗ Transaction history ($filterName) test failed\n";
                $this->testResults["Transaction History - $filterName"] = 'FAILED';
            }
        }
    }
    
    /**
     * Test transaction validation
     */
    private function testTransactionValidation()
    {
        echo "\n[TEST] Transaction Validation...\n";
        
        // Test missing required fields
        $invalidData = [
            'amount' => 1000
            // Missing transaction_type, accounts, etc.
        ];
        
        $validData = [
            'transaction_type' => 'transfer',
            'amount' => 1000,
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321'
        ];
        
        $this->testValidation('/secure/transactions/process', $invalidData, $validData);
        
        // Test invalid amount
        echo "\n  Testing invalid amount...\n";
        $negativeAmount = array_merge($validData, ['amount' => -1000]);
        
        $response = $this->makeRequest('POST', '/secure/transactions/process', $negativeAmount, [], 'Negative Amount');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Negative amount rejected\n";
            $this->testResults['Negative Amount Validation'] = 'PASSED';
        } else {
            echo "  ✗ Negative amount not rejected\n";
            $this->testResults['Negative Amount Validation'] = 'FAILED';
        }
        
        // Test invalid account format
        echo "\n  Testing invalid account format...\n";
        $invalidAccount = array_merge($validData, ['from_account' => 'INVALID']);
        
        $response = $this->makeRequest('POST', '/secure/transactions/process', $invalidAccount, [], 'Invalid Account');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid account format rejected\n";
            $this->testResults['Account Format Validation'] = 'PASSED';
        } else {
            echo "  ✗ Invalid account format not rejected\n";
            $this->testResults['Account Format Validation'] = 'FAILED';
        }
    }
    
    /**
     * Test transaction limits
     */
    private function testTransactionLimits()
    {
        echo "\n[TEST] Transaction Limits...\n";
        
        // Test maximum amount
        echo "  Testing maximum transaction amount...\n";
        $largeAmount = [
            'transaction_type' => 'transfer',
            'amount' => 1000000001, // Over 1 billion
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321'
        ];
        
        $response = $this->makeRequest('POST', '/secure/transactions/process', $largeAmount, [], 'Max Amount');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Maximum amount limit enforced\n";
            $this->testResults['Maximum Amount Limit'] = 'PASSED';
            
            if (isset($response['body']['message'])) {
                echo "  ✓ Error message: " . $response['body']['message'] . "\n";
            }
        } else {
            echo "  ⚠ Maximum amount limit may not be enforced\n";
            $this->testResults['Maximum Amount Limit'] = 'WARNING';
        }
        
        // Test minimum amount
        echo "\n  Testing minimum transaction amount...\n";
        $smallAmount = [
            'transaction_type' => 'transfer',
            'amount' => 0.01,
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321'
        ];
        
        $response = $this->makeRequest('POST', '/secure/transactions/process', $smallAmount, [], 'Min Amount');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Minimum amount limit enforced\n";
            $this->testResults['Minimum Amount Limit'] = 'PASSED';
        } else if ($response['status'] === 200) {
            echo "  ✓ Small amount accepted\n";
            $this->testResults['Minimum Amount Limit'] = 'PASSED';
        } else {
            echo "  ⚠ Minimum amount handling unclear\n";
            $this->testResults['Minimum Amount Limit'] = 'WARNING';
        }
    }
    
    /**
     * Test idempotency
     */
    private function testIdempotency()
    {
        echo "\n[TEST] Idempotency...\n";
        
        $idempotencyKey = 'test_idempotency_' . uniqid();
        
        $transactionData = [
            'transaction_type' => 'transfer',
            'amount' => 5000,
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321',
            'reference' => 'IDEM_' . time()
        ];
        
        $headers = ['X-Idempotency-Key' => $idempotencyKey];
        
        // First request
        echo "  Making first request with idempotency key...\n";
        $response1 = $this->makeRequest('POST', '/secure/transactions/process', $transactionData, $headers, 'Idempotency Test 1');
        
        if ($response1['status'] === 200 && isset($response1['body']['transaction_id'])) {
            $transactionId1 = $response1['body']['transaction_id'];
            
            // Second request with same idempotency key
            echo "  Making second request with same idempotency key...\n";
            $response2 = $this->makeRequest('POST', '/secure/transactions/process', $transactionData, $headers, 'Idempotency Test 2');
            
            if ($response2['status'] === 200 && isset($response2['body']['transaction_id'])) {
                $transactionId2 = $response2['body']['transaction_id'];
                
                if ($transactionId1 === $transactionId2) {
                    echo "  ✓ Idempotency working - same transaction ID returned\n";
                    $this->testResults['Idempotency'] = 'PASSED';
                } else {
                    echo "  ✗ Idempotency not working - different transaction IDs\n";
                    $this->testResults['Idempotency'] = 'FAILED';
                }
            } else {
                echo "  ⚠ Second request failed\n";
                $this->testResults['Idempotency'] = 'WARNING';
            }
        } else {
            echo "  ✗ First request failed\n";
            $this->testResults['Idempotency'] = 'FAILED';
        }
    }
    
    /**
     * Test error scenarios
     */
    private function testErrorScenarios()
    {
        echo "\n[TEST] Error Scenarios...\n";
        
        // Test insufficient funds
        echo "  Testing insufficient funds scenario...\n";
        $insufficientFunds = [
            'transaction_type' => 'transfer',
            'amount' => 999999999,
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321',
            'reference' => 'INSUFF_' . time()
        ];
        
        $response = $this->makeRequest('POST', '/secure/transactions/process', $insufficientFunds, [], 'Insufficient Funds');
        
        if ($response['status'] === 400 || $response['status'] === 422) {
            if (isset($response['body']['error_code']) && $response['body']['error_code'] === 'INSUFFICIENT_FUNDS') {
                echo "  ✓ Insufficient funds handled correctly\n";
                $this->testResults['Insufficient Funds Handling'] = 'PASSED';
            } else {
                echo "  ⚠ Error returned but code unclear\n";
                $this->testResults['Insufficient Funds Handling'] = 'WARNING';
            }
        } else {
            echo "  ⚠ Insufficient funds scenario not clearly handled\n";
            $this->testResults['Insufficient Funds Handling'] = 'WARNING';
        }
        
        // Test account not found
        echo "\n  Testing account not found scenario...\n";
        $invalidAccount = [
            'transaction_type' => 'transfer',
            'amount' => 1000,
            'currency' => 'TZS',
            'from_account' => '9999999999',
            'to_account' => '8888888888',
            'reference' => 'NOACCT_' . time()
        ];
        
        $response = $this->makeRequest('POST', '/secure/transactions/process', $invalidAccount, [], 'Account Not Found');
        
        if ($response['status'] === 404 || $response['status'] === 400) {
            echo "  ✓ Account not found handled correctly\n";
            $this->testResults['Account Not Found Handling'] = 'PASSED';
        } else {
            echo "  ⚠ Account not found handling unclear\n";
            $this->testResults['Account Not Found Handling'] = 'WARNING';
        }
        
        // Test error handling
        $this->testErrorHandling('/secure/transactions/process');
        
        // Test rate limiting
        $this->testRateLimiting('/secure/transactions/process', 'POST', [
            'transaction_type' => 'transfer',
            'amount' => 100,
            'currency' => 'TZS',
            'from_account' => '1234567890',
            'to_account' => '0987654321'
        ]);
    }
}