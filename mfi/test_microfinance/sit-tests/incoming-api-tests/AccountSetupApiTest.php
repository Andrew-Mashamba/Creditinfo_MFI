<?php

namespace SitTests\IncomingApi;

require_once __DIR__ . '/IncomingApiTestBase.php';

/**
 * Account Setup API Test Suite
 * 
 * Tests for /api/accounts/setup endpoint
 */
class AccountSetupApiTest extends IncomingApiTestBase
{
    public function runAllTests()
    {
        echo "\n========================================\n";
        echo "Account Setup API Tests\n";
        echo "========================================\n";
        
        // Test authentication
        $this->testSecurityFeatures();
        
        // Test account setup scenarios
        $this->testSingleAccountSetup();
        $this->testBulkAccountSetup();
        $this->testAccountWithInitialDeposit();
        
        // Test validation
        $this->testAccountValidation();
        
        // Test duplicate handling
        $this->testDuplicateAccountHandling();
        
        // Test error scenarios
        $this->testErrorScenarios();
        
        $this->printResults();
        $this->generateReport('Account Setup API');
    }
    
    /**
     * Test security features
     */
    private function testSecurityFeatures()
    {
        echo "\n[TEST] Security Features...\n";
        
        // Test authentication requirement
        $this->testAuthentication('/accounts/setup', 'POST', [
            'customer_id' => 'CUST123',
            'account_type' => 'savings'
        ]);
        
        // Test rate limiting for account creation
        echo "\n  Testing account creation rate limiting...\n";
        $this->testRateLimiting('/accounts/setup', 'POST', [
            'customer_id' => 'CUST' . time(),
            'account_type' => 'savings',
            'branch_code' => 'BR001'
        ], 10); // Lower limit for account creation
    }
    
    /**
     * Test single account setup
     */
    private function testSingleAccountSetup()
    {
        echo "\n[TEST] Single Account Setup...\n";
        
        // Test different account types
        $accountTypes = [
            'savings' => [
                'customer_id' => 'CUST_SAV_' . time(),
                'account_type' => 'savings',
                'customer_details' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '255712345678',
                    'national_id' => 'ID123456789'
                ],
                'branch_code' => 'BR001',
                'currency' => 'TZS'
            ],
            'current' => [
                'customer_id' => 'CUST_CUR_' . time(),
                'account_type' => 'current',
                'customer_details' => [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '255722345678',
                    'national_id' => 'ID987654321'
                ],
                'branch_code' => 'BR002',
                'currency' => 'TZS',
                'overdraft_limit' => 500000
            ],
            'loan' => [
                'customer_id' => 'CUST_LOAN_' . time(),
                'account_type' => 'loan',
                'customer_details' => [
                    'first_name' => 'Bob',
                    'last_name' => 'Johnson',
                    'email' => 'bob.johnson@example.com',
                    'phone' => '255732345678',
                    'national_id' => 'ID456789123'
                ],
                'branch_code' => 'BR001',
                'currency' => 'TZS',
                'loan_amount' => 1000000,
                'interest_rate' => 12.5,
                'loan_term_months' => 12
            ],
            'fixed_deposit' => [
                'customer_id' => 'CUST_FD_' . time(),
                'account_type' => 'fixed_deposit',
                'customer_details' => [
                    'first_name' => 'Alice',
                    'last_name' => 'Williams',
                    'email' => 'alice.williams@example.com',
                    'phone' => '255742345678',
                    'national_id' => 'ID789123456'
                ],
                'branch_code' => 'BR003',
                'currency' => 'TZS',
                'deposit_amount' => 5000000,
                'interest_rate' => 8.5,
                'term_months' => 6
            ]
        ];
        
        foreach ($accountTypes as $type => $data) {
            echo "\n  Testing $type account setup...\n";
            
            $response = $this->makeRequest('POST', '/accounts/setup', $data, [], ucfirst($type) . ' Account Setup');
            
            $validation = $this->validateResponse($response, ['status'], 200);
            
            if ($validation['passed']) {
                echo "  ✓ $type account setup test passed\n";
                $this->testResults[ucfirst($type) . ' Account Setup'] = 'PASSED';
                
                // Check response details
                if (isset($response['body']['account_number'])) {
                    echo "  ✓ Account number: " . $response['body']['account_number'] . "\n";
                }
                if (isset($response['body']['account_status'])) {
                    echo "  ✓ Account status: " . $response['body']['account_status'] . "\n";
                }
                if (isset($response['body']['created_at'])) {
                    echo "  ✓ Created at: " . $response['body']['created_at'] . "\n";
                }
            } else {
                echo "  ✗ $type account setup test failed\n";
                foreach ($validation['errors'] as $error) {
                    echo "    - $error\n";
                }
                $this->testResults[ucfirst($type) . ' Account Setup'] = 'FAILED';
            }
        }
    }
    
    /**
     * Test bulk account setup
     */
    private function testBulkAccountSetup()
    {
        echo "\n[TEST] Bulk Account Setup...\n";
        
        $bulkData = [
            'accounts' => [
                [
                    'customer_id' => 'BULK_CUST_1_' . time(),
                    'account_type' => 'savings',
                    'customer_details' => [
                        'first_name' => 'Customer',
                        'last_name' => 'One',
                        'email' => 'customer1@example.com',
                        'phone' => '255712111111',
                        'national_id' => 'ID111111111'
                    ],
                    'branch_code' => 'BR001'
                ],
                [
                    'customer_id' => 'BULK_CUST_2_' . time(),
                    'account_type' => 'current',
                    'customer_details' => [
                        'first_name' => 'Customer',
                        'last_name' => 'Two',
                        'email' => 'customer2@example.com',
                        'phone' => '255712222222',
                        'national_id' => 'ID222222222'
                    ],
                    'branch_code' => 'BR002'
                ],
                [
                    'customer_id' => 'BULK_CUST_3_' . time(),
                    'account_type' => 'savings',
                    'customer_details' => [
                        'first_name' => 'Customer',
                        'last_name' => 'Three',
                        'email' => 'customer3@example.com',
                        'phone' => '255712333333',
                        'national_id' => 'ID333333333'
                    ],
                    'branch_code' => 'BR001'
                ]
            ]
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $bulkData, [], 'Bulk Account Setup');
        
        if ($response['status'] === 200 || $response['status'] === 207) { // 207 for partial success
            echo "  ✓ Bulk account setup test passed\n";
            $this->testResults['Bulk Account Setup'] = 'PASSED';
            
            // Check bulk response
            if (isset($response['body']['created'])) {
                echo "  ✓ Accounts created: " . count($response['body']['created']) . "\n";
            }
            if (isset($response['body']['failed'])) {
                echo "  ⚠ Accounts failed: " . count($response['body']['failed']) . "\n";
            }
            if (isset($response['body']['total'])) {
                echo "  ✓ Total processed: " . $response['body']['total'] . "\n";
            }
        } else {
            echo "  ✗ Bulk account setup test failed\n";
            $this->testResults['Bulk Account Setup'] = 'FAILED';
        }
    }
    
    /**
     * Test account with initial deposit
     */
    private function testAccountWithInitialDeposit()
    {
        echo "\n[TEST] Account Setup with Initial Deposit...\n";
        
        $accountWithDeposit = [
            'customer_id' => 'CUST_DEPOSIT_' . time(),
            'account_type' => 'savings',
            'customer_details' => [
                'first_name' => 'Initial',
                'last_name' => 'Deposit',
                'email' => 'deposit@example.com',
                'phone' => '255712999999',
                'national_id' => 'ID999999999'
            ],
            'branch_code' => 'BR001',
            'currency' => 'TZS',
            'initial_deposit' => [
                'amount' => 100000,
                'payment_method' => 'cash',
                'reference' => 'INIT_DEP_' . time()
            ]
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $accountWithDeposit, [], 'Account with Initial Deposit');
        
        $validation = $this->validateResponse($response, ['status'], 200);
        
        if ($validation['passed']) {
            echo "  ✓ Account with initial deposit test passed\n";
            $this->testResults['Account with Initial Deposit'] = 'PASSED';
            
            // Check deposit confirmation
            if (isset($response['body']['initial_balance'])) {
                echo "  ✓ Initial balance: " . $response['body']['initial_balance'] . "\n";
            }
            if (isset($response['body']['deposit_receipt'])) {
                echo "  ✓ Deposit receipt: " . $response['body']['deposit_receipt'] . "\n";
            }
        } else {
            echo "  ✗ Account with initial deposit test failed\n";
            $this->testResults['Account with Initial Deposit'] = 'FAILED';
        }
    }
    
    /**
     * Test account validation
     */
    private function testAccountValidation()
    {
        echo "\n[TEST] Account Validation...\n";
        
        // Test missing customer ID
        echo "  Testing missing customer ID...\n";
        $missingCustomerId = [
            'account_type' => 'savings',
            'branch_code' => 'BR001'
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $missingCustomerId, [], 'Missing Customer ID');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Missing customer ID rejected\n";
            $this->testResults['Missing Customer ID'] = 'PASSED';
        } else {
            echo "  ✗ Missing customer ID not rejected\n";
            $this->testResults['Missing Customer ID'] = 'FAILED';
        }
        
        // Test invalid account type
        echo "\n  Testing invalid account type...\n";
        $invalidAccountType = [
            'customer_id' => 'CUST_INVALID_' . time(),
            'account_type' => 'invalid_type',
            'branch_code' => 'BR001'
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $invalidAccountType, [], 'Invalid Account Type');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid account type rejected\n";
            $this->testResults['Invalid Account Type'] = 'PASSED';
        } else {
            echo "  ✗ Invalid account type not rejected\n";
            $this->testResults['Invalid Account Type'] = 'FAILED';
        }
        
        // Test invalid email format
        echo "\n  Testing invalid email format...\n";
        $invalidEmail = [
            'customer_id' => 'CUST_EMAIL_' . time(),
            'account_type' => 'savings',
            'customer_details' => [
                'first_name' => 'Invalid',
                'last_name' => 'Email',
                'email' => 'invalid-email',
                'phone' => '255712888888',
                'national_id' => 'ID888888888'
            ],
            'branch_code' => 'BR001'
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $invalidEmail, [], 'Invalid Email');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid email format rejected\n";
            $this->testResults['Invalid Email Format'] = 'PASSED';
        } else {
            echo "  ✗ Invalid email format not rejected\n";
            $this->testResults['Invalid Email Format'] = 'FAILED';
        }
        
        // Test invalid phone format
        echo "\n  Testing invalid phone format...\n";
        $invalidPhone = [
            'customer_id' => 'CUST_PHONE_' . time(),
            'account_type' => 'savings',
            'customer_details' => [
                'first_name' => 'Invalid',
                'last_name' => 'Phone',
                'email' => 'phone@example.com',
                'phone' => '12345',  // Too short
                'national_id' => 'ID777777777'
            ],
            'branch_code' => 'BR001'
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $invalidPhone, [], 'Invalid Phone');
        
        if ($response['status'] === 422 || $response['status'] === 400) {
            echo "  ✓ Invalid phone format rejected\n";
            $this->testResults['Invalid Phone Format'] = 'PASSED';
        } else {
            echo "  ✗ Invalid phone format not rejected\n";
            $this->testResults['Invalid Phone Format'] = 'FAILED';
        }
    }
    
    /**
     * Test duplicate account handling
     */
    private function testDuplicateAccountHandling()
    {
        echo "\n[TEST] Duplicate Account Handling...\n";
        
        $accountData = [
            'customer_id' => 'CUST_DUP_' . time(),
            'account_type' => 'savings',
            'customer_details' => [
                'first_name' => 'Duplicate',
                'last_name' => 'Test',
                'email' => 'duplicate@example.com',
                'phone' => '255712666666',
                'national_id' => 'ID666666666'
            ],
            'branch_code' => 'BR001'
        ];
        
        // First account creation
        echo "  Creating first account...\n";
        $response1 = $this->makeRequest('POST', '/accounts/setup', $accountData, [], 'First Account');
        
        if ($response1['status'] === 200 || $response1['status'] === 201) {
            // Try to create duplicate account
            echo "  Attempting to create duplicate account...\n";
            $response2 = $this->makeRequest('POST', '/accounts/setup', $accountData, [], 'Duplicate Account');
            
            if ($response2['status'] === 409) {
                echo "  ✓ Duplicate account rejected with 409\n";
                $this->testResults['Duplicate Account Prevention'] = 'PASSED';
            } else if ($response2['status'] === 400 && isset($response2['body']['error']) && 
                      strpos(strtolower($response2['body']['error']), 'duplicate') !== false) {
                echo "  ✓ Duplicate account rejected\n";
                $this->testResults['Duplicate Account Prevention'] = 'PASSED';
            } else {
                echo "  ✗ Duplicate account not properly handled\n";
                $this->testResults['Duplicate Account Prevention'] = 'FAILED';
            }
        } else {
            echo "  ✗ First account creation failed\n";
            $this->testResults['Duplicate Account Prevention'] = 'FAILED';
        }
    }
    
    /**
     * Test error scenarios
     */
    private function testErrorScenarios()
    {
        echo "\n[TEST] Error Scenarios...\n";
        
        // Test invalid branch code
        echo "  Testing invalid branch code...\n";
        $invalidBranch = [
            'customer_id' => 'CUST_BRANCH_' . time(),
            'account_type' => 'savings',
            'customer_details' => [
                'first_name' => 'Invalid',
                'last_name' => 'Branch',
                'email' => 'branch@example.com',
                'phone' => '255712555555',
                'national_id' => 'ID555555555'
            ],
            'branch_code' => 'INVALID_BRANCH'
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $invalidBranch, [], 'Invalid Branch');
        
        if ($response['status'] === 400 || $response['status'] === 404) {
            echo "  ✓ Invalid branch code rejected\n";
            $this->testResults['Invalid Branch Code'] = 'PASSED';
        } else {
            echo "  ⚠ Invalid branch code handling unclear\n";
            $this->testResults['Invalid Branch Code'] = 'WARNING';
        }
        
        // Test blacklisted customer
        echo "\n  Testing blacklisted customer...\n";
        $blacklistedCustomer = [
            'customer_id' => 'BLACKLISTED_001',  // Assuming this is blacklisted
            'account_type' => 'savings',
            'customer_details' => [
                'first_name' => 'Blacklisted',
                'last_name' => 'Customer',
                'email' => 'blacklist@example.com',
                'phone' => '255712444444',
                'national_id' => 'BLACKLISTED_ID'
            ],
            'branch_code' => 'BR001'
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $blacklistedCustomer, [], 'Blacklisted Customer');
        
        if ($response['status'] === 403) {
            echo "  ✓ Blacklisted customer rejected\n";
            $this->testResults['Blacklisted Customer'] = 'PASSED';
        } else {
            echo "  ⚠ Blacklisted customer handling unclear\n";
            $this->testResults['Blacklisted Customer'] = 'WARNING';
        }
        
        // Test age restriction
        echo "\n  Testing age restriction...\n";
        $underageCustomer = [
            'customer_id' => 'CUST_UNDERAGE_' . time(),
            'account_type' => 'current',  // Current accounts may have age restrictions
            'customer_details' => [
                'first_name' => 'Minor',
                'last_name' => 'Customer',
                'email' => 'minor@example.com',
                'phone' => '255712333333',
                'national_id' => 'ID333333333',
                'date_of_birth' => date('Y-m-d', strtotime('-10 years'))  // 10 years old
            ],
            'branch_code' => 'BR001'
        ];
        
        $response = $this->makeRequest('POST', '/accounts/setup', $underageCustomer, [], 'Underage Customer');
        
        if ($response['status'] === 400 || $response['status'] === 422) {
            echo "  ✓ Age restriction enforced\n";
            $this->testResults['Age Restriction'] = 'PASSED';
        } else {
            echo "  ⚠ Age restriction may not be enforced\n";
            $this->testResults['Age Restriction'] = 'WARNING';
        }
        
        // Test error handling
        $this->testErrorHandling('/accounts/setup');
    }
}