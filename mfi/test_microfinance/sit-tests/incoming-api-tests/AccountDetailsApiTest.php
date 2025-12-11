<?php

namespace SitTests\IncomingApi;

require_once __DIR__ . '/IncomingApiTestBase.php';

/**
 * Account Details API Test Suite
 * 
 * Tests for /api/v1/account-details endpoints
 */
class AccountDetailsApiTest extends IncomingApiTestBase
{
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "Account Details API Tests\n";
        echo "========================================\n";
        
        // Test connectivity
        $this->testConnectivity();
        
        // Test main endpoints
        $this->testGetAccountDetails();
        $this->testGetStatistics();
        
        // Test validation
        $this->testAccountValidation();
        
        // Test security
        $this->testSecurityFeatures();
        
        // Test error handling
        $this->testErrorScenarios();
        
        $this->printResults();
        $this->generateReport('Account Details API');
    }
    
    /**
     * Test connectivity endpoint
     */
    private function testConnectivity()
    {
        echo "\n[TEST] Connectivity Test Endpoint...\n";
        
        $response = $this->makeRequest('GET', '/v1/account-details/test', [], [], 'Connectivity Test');
        
        if ($response['status'] === 200) {
            echo "  ✓ Connectivity test passed\n";
            $this->testResults['Connectivity'] = 'PASSED';
            
            if (isset($response['body']['status'])) {
                echo "  ✓ Status: " . $response['body']['status'] . "\n";
            }
            if (isset($response['body']['timestamp'])) {
                echo "  ✓ Timestamp: " . $response['body']['timestamp'] . "\n";
            }
            if (isset($response['body']['version'])) {
                echo "  ✓ API Version: " . $response['body']['version'] . "\n";
            }
        } else {
            echo "  ✗ Connectivity test failed\n";
            $this->testResults['Connectivity'] = 'FAILED';
        }
    }
    
    /**
     * Test get account details endpoint
     */
    private function testGetAccountDetails()
    {
        echo "\n[TEST] Get Account Details Endpoint...\n";
        
        // Test with different account types
        $accountTests = [
            'savings' => [
                'account_number' => '01J01234567890',
                'account_type' => 'savings'
            ],
            'current' => [
                'account_number' => '02C09876543210',
                'account_type' => 'current'
            ],
            'loan' => [
                'account_number' => '03L11223344556',
                'account_type' => 'loan'
            ]
        ];
        
        foreach ($accountTests as $type => $data) {
            echo "\n  Testing $type account details...\n";
            
            $response = $this->makeRequest('POST', '/v1/account-details', $data, [], ucfirst($type) . ' Account');
            
            $validation = $this->validateResponse($response, ['status', 'data'], 200);
            
            if ($validation['passed']) {
                echo "  ✓ $type account details test passed\n";
                $this->testResults[ucfirst($type) . ' Account Details'] = 'PASSED';
                
                // Check account information
                if (isset($response['body']['data']['account_number'])) {
                    echo "  ✓ Account number: " . $response['body']['data']['account_number'] . "\n";
                }
                if (isset($response['body']['data']['account_name'])) {
                    echo "  ✓ Account name: " . $response['body']['data']['account_name'] . "\n";
                }
                if (isset($response['body']['data']['balance'])) {
                    echo "  ✓ Balance: " . $response['body']['data']['balance'] . "\n";
                }
                if (isset($response['body']['data']['currency'])) {
                    echo "  ✓ Currency: " . $response['body']['data']['currency'] . "\n";
                }
                if (isset($response['body']['data']['status'])) {
                    echo "  ✓ Status: " . $response['body']['data']['status'] . "\n";
                }
            } else {
                echo "  ✗ $type account details test failed\n";
                foreach ($validation['errors'] as $error) {
                    echo "    - $error\n";
                }
                $this->testResults[ucfirst($type) . ' Account Details'] = 'FAILED';
            }
        }
        
        // Test with multiple accounts
        echo "\n  Testing multiple accounts request...\n";
        $multipleAccounts = [
            'account_numbers' => [
                '01J01234567890',
                '02C09876543210',
                '03L11223344556'
            ]
        ];
        
        $response = $this->makeRequest('POST', '/v1/account-details', $multipleAccounts, [], 'Multiple Accounts');
        
        if ($response['status'] === 200 && isset($response['body']['data']) && is_array($response['body']['data'])) {
            echo "  ✓ Multiple accounts test passed\n";
            echo "  ✓ Returned details for " . count($response['body']['data']) . " accounts\n";
            $this->testResults['Multiple Accounts'] = 'PASSED';
        } else {
            echo "  ✗ Multiple accounts test failed\n";
            $this->testResults['Multiple Accounts'] = 'FAILED';
        }
    }
    
    /**
     * Test get statistics endpoint
     */
    private function testGetStatistics()
    {
        echo "\n[TEST] Get Statistics Endpoint...\n";
        
        $response = $this->makeRequest('GET', '/v1/account-details/stats', [], [], 'Statistics');
        
        $validation = $this->validateResponse($response, ['status', 'data'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Statistics test passed\n";
            $this->testResults['Statistics'] = 'PASSED';
            
            // Check statistics data
            if (isset($response['body']['data']['total_accounts'])) {
                echo "  ✓ Total accounts: " . $response['body']['data']['total_accounts'] . "\n";
            }
            if (isset($response['body']['data']['active_accounts'])) {
                echo "  ✓ Active accounts: " . $response['body']['data']['active_accounts'] . "\n";
            }
            if (isset($response['body']['data']['total_balance'])) {
                echo "  ✓ Total balance: " . $response['body']['data']['total_balance'] . "\n";
            }
            if (isset($response['body']['data']['accounts_by_type'])) {
                echo "  ✓ Accounts by type breakdown available\n";
            }
        } else {
            echo "  ✗ Statistics test failed\n";
            foreach ($validation['errors'] as $error) {
                echo "    - $error\n";
            }
            $this->testResults['Statistics'] = 'FAILED';
        }
    }
    
    /**
     * Test account validation
     */
    private function testAccountValidation()
    {
        echo "\n[TEST] Account Validation...\n";
        
        // Test with invalid account number format
        echo "  Testing invalid account number format...\n";
        $invalidFormat = [
            'account_number' => 'INVALID123'
        ];
        
        $response = $this->makeRequest('POST', '/v1/account-details', $invalidFormat, [], 'Invalid Format');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid account format rejected\n";
            $this->testResults['Account Format Validation'] = 'PASSED';
        } else {
            echo "  ✗ Invalid account format not rejected\n";
            $this->testResults['Account Format Validation'] = 'FAILED';
        }
        
        // Test with missing account number
        echo "\n  Testing missing account number...\n";
        $missingAccount = [
            'account_type' => 'savings'
        ];
        
        $response = $this->makeRequest('POST', '/v1/account-details', $missingAccount, [], 'Missing Account');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Missing account number rejected\n";
            $this->testResults['Missing Account Validation'] = 'PASSED';
            
            if (isset($response['body']['errors'])) {
                echo "  ✓ Validation errors provided\n";
            }
        } else {
            echo "  ✗ Missing account number not rejected\n";
            $this->testResults['Missing Account Validation'] = 'FAILED';
        }
        
        // Test with non-existent account
        echo "\n  Testing non-existent account...\n";
        $nonExistent = [
            'account_number' => '99999999999999'
        ];
        
        $response = $this->makeRequest('POST', '/v1/account-details', $nonExistent, [], 'Non-existent Account');
        
        if ($response['status'] === 404) {
            echo "  ✓ Non-existent account handled correctly\n";
            $this->testResults['Non-existent Account'] = 'PASSED';
        } else if ($response['status'] === 200 && isset($response['body']['data']) && empty($response['body']['data'])) {
            echo "  ✓ Non-existent account returns empty data\n";
            $this->testResults['Non-existent Account'] = 'PASSED';
        } else {
            echo "  ⚠ Non-existent account handling unclear\n";
            $this->testResults['Non-existent Account'] = 'WARNING';
        }
    }
    
    /**
     * Test security features
     */
    private function testSecurityFeatures()
    {
        echo "\n[TEST] Security Features...\n";
        
        // Test authentication requirement
        $this->testAuthentication('/v1/account-details', 'POST', [
            'account_number' => '01J01234567890'
        ]);
        
        // Test data masking
        echo "\n  Testing sensitive data masking...\n";
        $response = $this->makeRequest('POST', '/v1/account-details', [
            'account_number' => '01J01234567890',
            'include_sensitive' => false
        ], [], 'Data Masking');
        
        if ($response['status'] === 200 && isset($response['body']['data'])) {
            $data = $response['body']['data'];
            
            // Check if sensitive fields are masked
            $masked = false;
            if (isset($data['account_number']) && strpos($data['account_number'], '***') !== false) {
                echo "  ✓ Account number masked\n";
                $masked = true;
            }
            if (isset($data['balance']) && is_string($data['balance']) && strpos($data['balance'], '***') !== false) {
                echo "  ✓ Balance masked\n";
                $masked = true;
            }
            
            $this->testResults['Data Masking'] = $masked ? 'PASSED' : 'INFO';
        }
        
        // Test rate limiting
        echo "\n  Testing rate limiting...\n";
        $this->testRateLimiting('/v1/account-details', 'POST', [
            'account_number' => '01J01234567890'
        ], 100);
    }
    
    /**
     * Test error scenarios
     */
    private function testErrorScenarios()
    {
        echo "\n[TEST] Error Scenarios...\n";
        
        // Test closed account
        echo "  Testing closed account scenario...\n";
        $closedAccount = [
            'account_number' => '00X99999999999'  // Assuming this is a closed account
        ];
        
        $response = $this->makeRequest('POST', '/v1/account-details', $closedAccount, [], 'Closed Account');
        
        if ($response['status'] === 200 && isset($response['body']['data']['status'])) {
            if ($response['body']['data']['status'] === 'closed' || $response['body']['data']['status'] === 'inactive') {
                echo "  ✓ Closed account status shown correctly\n";
                $this->testResults['Closed Account Handling'] = 'PASSED';
            } else {
                echo "  ⚠ Account status unclear\n";
                $this->testResults['Closed Account Handling'] = 'WARNING';
            }
        } else if ($response['status'] === 403) {
            echo "  ✓ Access to closed account denied\n";
            $this->testResults['Closed Account Handling'] = 'PASSED';
        } else {
            echo "  ⚠ Closed account handling unclear\n";
            $this->testResults['Closed Account Handling'] = 'WARNING';
        }
        
        // Test dormant account
        echo "\n  Testing dormant account scenario...\n";
        $dormantAccount = [
            'account_number' => '01D88888888888'  // Assuming this is a dormant account
        ];
        
        $response = $this->makeRequest('POST', '/v1/account-details', $dormantAccount, [], 'Dormant Account');
        
        if ($response['status'] === 200 && isset($response['body']['data']['status'])) {
            if ($response['body']['data']['status'] === 'dormant') {
                echo "  ✓ Dormant account status shown correctly\n";
                $this->testResults['Dormant Account Handling'] = 'PASSED';
            } else {
                echo "  ⚠ Dormant account status unclear\n";
                $this->testResults['Dormant Account Handling'] = 'WARNING';
            }
        } else {
            echo "  ⚠ Dormant account handling unclear\n";
            $this->testResults['Dormant Account Handling'] = 'WARNING';
        }
        
        // Test frozen account
        echo "\n  Testing frozen account scenario...\n";
        $frozenAccount = [
            'account_number' => '01F77777777777'  // Assuming this is a frozen account
        ];
        
        $response = $this->makeRequest('POST', '/v1/account-details', $frozenAccount, [], 'Frozen Account');
        
        if ($response['status'] === 200 && isset($response['body']['data'])) {
            if (isset($response['body']['data']['status']) && $response['body']['data']['status'] === 'frozen') {
                echo "  ✓ Frozen account status shown correctly\n";
                $this->testResults['Frozen Account Handling'] = 'PASSED';
            } else if (isset($response['body']['data']['restrictions'])) {
                echo "  ✓ Account restrictions shown\n";
                $this->testResults['Frozen Account Handling'] = 'PASSED';
            } else {
                echo "  ⚠ Frozen account status unclear\n";
                $this->testResults['Frozen Account Handling'] = 'WARNING';
            }
        } else if ($response['status'] === 403) {
            echo "  ✓ Access to frozen account restricted\n";
            $this->testResults['Frozen Account Handling'] = 'PASSED';
        } else {
            echo "  ⚠ Frozen account handling unclear\n";
            $this->testResults['Frozen Account Handling'] = 'WARNING';
        }
        
        // Test error handling
        $this->testErrorHandling('/v1/account-details');
    }
}