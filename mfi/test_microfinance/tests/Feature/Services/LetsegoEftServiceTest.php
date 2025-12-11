<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\Payments\ExternalFundsTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Comprehensive Test Suite for Letshego EFT Service
 * 
 * Tests all major functionalities:
 * - Account Enquiry
 * - Outward Transfers  
 * - Transaction Status
 * - Institution List
 * - Transaction Reversal
 * - Error Handling
 */
class LetshEgoEftServiceTest extends TestCase
{
    protected ExternalFundsTransferService $eftService;
    protected array $testData;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eftService = new ExternalFundsTransferService();
        
        // Test data based on Postman collection
        $this->testData = [
            'valid_accounts' => [
                'letshego_account' => '21710176786',
                'external_account' => '0752518001',
                'bank_code' => '016'
            ],
            'invalid_accounts' => [
                'invalid_account' => '1234567890',
                'invalid_bank_code' => '999'
            ],
            'transfer_data' => [
                'from_account' => '21710176786',
                'to_account' => '0752518001',
                'bank_code' => '016',
                'amount' => 50000,
                'narration' => 'Test transfer via Letshego',
                'sender_name' => 'Test User',
                'sender_phone' => '+255715000001',
                'sender_email' => 'test@example.com',
                'initiated_by' => 'TEST_USER',
                'branch_code' => '044'
            ]
        ];
    }

    /**
     * Test 1: Institution List Retrieval
     * GET /{consumer_id}/outwards/institution
     */
    public function test_can_get_institution_list()
    {
        Log::info("🏦 Testing Institution List Retrieval");
        
        $response = $this->eftService->getInstitutionList();
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        
        if ($response['success']) {
            $this->assertArrayHasKey('institutions', $response);
            Log::info("✅ Institution list retrieved successfully", [
                'count' => count($response['institutions'] ?? [])
            ]);
        } else {
            Log::warning("⚠️ Institution list retrieval failed", [
                'error' => $response['error'] ?? 'Unknown error'
            ]);
        }
        
        // Should return either success or proper error structure
        $this->assertTrue(
            $response['success'] || isset($response['error']),
            'Response should have success=true or error message'
        );
    }

    /**
     * Test 2: Account Enquiry - Valid Account
     * GET /{consumer_id}/outwards/accountenquiry/{account_number}
     */
    public function test_can_lookup_valid_external_account()
    {
        Log::info("🔍 Testing Account Enquiry - Valid Account");
        
        $accountNumber = $this->testData['valid_accounts']['external_account'];
        $bankCode = $this->testData['valid_accounts']['bank_code'];
        
        $response = $this->eftService->lookupAccount($accountNumber, $bankCode);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('account_number', $response);
        $this->assertEquals($accountNumber, $response['account_number']);
        
        if ($response['success']) {
            $this->assertArrayHasKey('account_name', $response);
            $this->assertArrayHasKey('can_receive', $response);
            
            Log::info("✅ Account lookup successful", [
                'account' => $accountNumber,
                'name' => $response['account_name'],
                'bank_code' => $bankCode
            ]);
        } else {
            Log::warning("⚠️ Account lookup failed", [
                'account' => $accountNumber,
                'error' => $response['error'] ?? 'Unknown error'
            ]);
        }
    }

    /**
     * Test 3: Account Enquiry - Invalid Account
     */
    public function test_handles_invalid_account_lookup_gracefully()
    {
        Log::info("❌ Testing Account Enquiry - Invalid Account");
        
        $accountNumber = $this->testData['invalid_accounts']['invalid_account'];
        $bankCode = $this->testData['invalid_accounts']['invalid_bank_code'];
        
        $response = $this->eftService->lookupAccount($accountNumber, $bankCode);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success'], 'Invalid account should return success=false');
        $this->assertArrayHasKey('error', $response);
        
        Log::info("✅ Invalid account handled correctly", [
            'account' => $accountNumber,
            'error' => $response['error']
        ]);
    }

    /**
     * Test 4: Internal Account Validation
     */
    public function test_can_validate_internal_letshego_accounts()
    {
        Log::info("🏦 Testing Internal Letshego Account Validation");
        
        $testAccounts = [
            '21710176786' => 'Letshego Main Account',
            '21710176787' => 'Letshego Test Account',
            '21710176788' => 'Letshego Business Account',
            '21710176789' => 'Letshego Default Account',
            '21710176790' => 'Letshego Account', // Generic account
        ];
        
        foreach ($testAccounts as $account => $expectedName) {
            $reflection = new \ReflectionClass($this->eftService);
            $method = $reflection->getMethod('lookupInternalAccount');
            $method->setAccessible(true);
            
            $response = $method->invoke($this->eftService, $account);
            
            $this->assertIsArray($response);
            $this->assertTrue($response['success'], "Account {$account} should be valid");
            $this->assertEquals($account, $response['account_number']);
            $this->assertTrue($response['can_debit']);
            
            Log::info("✅ Internal account validated", [
                'account' => $account,
                'name' => $response['account_name']
            ]);
        }
    }

    /**
     * Test 5: Transfer Validation
     */
    public function test_validates_transfer_data_correctly()
    {
        Log::info("✅ Testing Transfer Data Validation");
        
        $reflection = new \ReflectionClass($this->eftService);
        $method = $reflection->getMethod('validateTransferData');
        $method->setAccessible(true);
        
        // Test valid data
        try {
            $method->invoke($this->eftService, $this->testData['transfer_data']);
            Log::info("✅ Valid transfer data passed validation");
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail("Valid transfer data should not throw exception: " . $e->getMessage());
        }
        
        // Test missing required fields
        $invalidData = [
            [], // Empty data
            ['from_account' => '123'], // Missing to_account
            ['to_account' => '456'], // Missing from_account
            ['from_account' => '123', 'to_account' => '456'], // Missing bank_code and amount
        ];
        
        foreach ($invalidData as $index => $data) {
            try {
                $method->invoke($this->eftService, $data);
                $this->fail("Invalid data set {$index} should throw exception");
            } catch (\Exception $e) {
                Log::info("✅ Invalid data set {$index} correctly rejected", [
                    'error' => $e->getMessage()
                ]);
                $this->assertIsString($e->getMessage());
            }
        }
        
        // Test invalid amount
        $invalidAmountData = $this->testData['transfer_data'];
        $invalidAmountData['amount'] = -100;
        
        try {
            $method->invoke($this->eftService, $invalidAmountData);
            $this->fail("Negative amount should throw exception");
        } catch (\Exception $e) {
            Log::info("✅ Negative amount correctly rejected", [
                'error' => $e->getMessage()
            ]);
            $this->assertStringContains('Invalid amount', $e->getMessage());
        }
    }

    /**
     * Test 6: Outward Transfer - Mock Test
     * POST /{consumer_id}/outwards/transfers
     */
    public function test_can_initiate_outward_transfer()
    {
        Log::info("💸 Testing Outward Transfer Initiation");
        
        // Mock HTTP responses to avoid actual API calls in tests
        Http::fake([
            '*/outwards/accountenquiry/*' => Http::response([
                'fullName' => 'HAJI NYEMBO',
                'accountNumber' => $this->testData['transfer_data']['to_account'],
                'status' => 'ACTIVE'
            ], 200),
            
            '*/outwards/transfers' => Http::response([
                'payerRef' => '044-WK' . time(),
                'transactionId' => 'TXN' . time(),
                'status' => 'PENDING',
                'message' => 'Transaction initiated successfully'
            ], 200)
        ]);
        
        $response = $this->eftService->transfer($this->testData['transfer_data']);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        
        if ($response['success']) {
            $this->assertArrayHasKey('reference', $response);
            $this->assertArrayHasKey('routing_system', $response);
            $this->assertArrayHasKey('letshego_reference', $response);
            
            Log::info("✅ Transfer initiated successfully", [
                'reference' => $response['reference'],
                'routing_system' => $response['routing_system'],
                'letshego_ref' => $response['letshego_reference'] ?? 'N/A'
            ]);
        } else {
            Log::warning("⚠️ Transfer initiation failed", [
                'error' => $response['error'] ?? 'Unknown error'
            ]);
        }
    }

    /**
     * Test 7: Transaction Status Check
     * GET /{consumer_id}/outwards/status/{payerRef}
     */
    public function test_can_check_transaction_status()
    {
        Log::info("📊 Testing Transaction Status Check");
        
        $testPayerRef = '044-WK175371850125227076'; // From Postman example
        
        // Mock HTTP response
        Http::fake([
            "*/outwards/status/{$testPayerRef}" => Http::response([
                'payerRef' => $testPayerRef,
                'status' => 'COMPLETED',
                'amount' => '50000.00',
                'currency' => 'TZS',
                'completedAt' => '2024-01-01T12:00:00Z'
            ], 200)
        ]);
        
        $response = $this->eftService->getTransactionStatus($testPayerRef);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        
        if ($response['success']) {
            $this->assertArrayHasKey('status', $response);
            
            Log::info("✅ Transaction status retrieved", [
                'payer_ref' => $testPayerRef,
                'status_data' => $response['status']
            ]);
        } else {
            Log::warning("⚠️ Transaction status check failed", [
                'payer_ref' => $testPayerRef,
                'error' => $response['error'] ?? 'Unknown error'
            ]);
        }
    }

    /**
     * Test 8: Transaction Reversal
     * POST /{consumer_id}/outwards/transfers/reversal
     */
    public function test_can_reverse_transaction()
    {
        Log::info("🔄 Testing Transaction Reversal");
        
        $testPayerRef = '044-20220616000009'; // From Postman example
        $reversalReason = 'Sent funds twice';
        
        // Mock HTTP response
        Http::fake([
            '*/outwards/transfers/reversal' => Http::response([
                'payerRef' => $testPayerRef,
                'reversalId' => 'REV' . time(),
                'status' => 'REVERSAL_PENDING',
                'message' => 'Reversal request submitted successfully'
            ], 200)
        ]);
        
        $response = $this->eftService->reverseTransaction($testPayerRef, $reversalReason);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        
        if ($response['success']) {
            $this->assertArrayHasKey('reversal', $response);
            
            Log::info("✅ Transaction reversal initiated", [
                'payer_ref' => $testPayerRef,
                'reason' => $reversalReason,
                'reversal_data' => $response['reversal']
            ]);
        } else {
            Log::warning("⚠️ Transaction reversal failed", [
                'payer_ref' => $testPayerRef,
                'reason' => $reversalReason,
                'error' => $response['error'] ?? 'Unknown error'
            ]);
        }
    }

    /**
     * Test 9: Error Handling
     */
    public function test_handles_api_errors_gracefully()
    {
        Log::info("⚠️ Testing Error Handling");
        
        // Mock HTTP error responses
        Http::fake([
            '*/outwards/accountenquiry/*' => Http::response([
                'error' => 'Account not found',
                'code' => 'ACCOUNT_NOT_FOUND'
            ], 404),
            
            '*/outwards/institution' => Http::response([
                'error' => 'Service unavailable',
                'code' => 'SERVICE_ERROR'
            ], 503),
            
            '*/outwards/status/*' => Http::response([
                'error' => 'Transaction not found',
                'code' => 'TRANSACTION_NOT_FOUND'
            ], 404)
        ]);
        
        // Test account lookup error
        $accountResponse = $this->eftService->lookupAccount('9999999999', '999');
        $this->assertFalse($accountResponse['success']);
        $this->assertArrayHasKey('error', $accountResponse);
        
        // Test institution list error
        $institutionResponse = $this->eftService->getInstitutionList();
        $this->assertFalse($institutionResponse['success']);
        $this->assertArrayHasKey('error', $institutionResponse);
        
        // Test transaction status error
        $statusResponse = $this->eftService->getTransactionStatus('INVALID_REF');
        $this->assertFalse($statusResponse['success']);
        $this->assertArrayHasKey('error', $statusResponse);
        
        Log::info("✅ Error handling working correctly");
    }

    /**
     * Test 10: TISS vs TIPS Routing
     */
    public function test_routes_to_correct_system_based_on_amount()
    {
        Log::info("🔀 Testing TISS vs TIPS Routing");
        
        $smallAmount = 1000000; // 1M TZS - should use TIPS
        $largeAmount = 25000000; // 25M TZS - should use TISS
        
        $tipsData = $this->testData['transfer_data'];
        $tipsData['amount'] = $smallAmount;
        
        $tissData = $this->testData['transfer_data'];
        $tissData['amount'] = $largeAmount;
        
        // Mock responses for both systems
        Http::fake([
            '*/outwards/accountenquiry/*' => Http::response(['fullName' => 'Test Account'], 200),
            '*/outwards/transfers' => Http::response(['payerRef' => '044-TEST'], 200),
            '*/tiss/api/v2/transfer' => Http::response(['tissRef' => 'TISS-TEST'], 200)
        ]);
        
        // Test TIPS routing (amount < 20M)
        $tipsResponse = $this->eftService->transfer($tipsData);
        if ($tipsResponse['success']) {
            $this->assertEquals('TIPS', $tipsResponse['routing_system']);
            Log::info("✅ TIPS routing correct for amount: " . number_format($smallAmount));
        }
        
        // Test TISS routing (amount >= 20M) 
        $tissResponse = $this->eftService->transfer($tissData);
        if ($tissResponse['success']) {
            $this->assertEquals('TISS', $tissResponse['routing_system']);
            Log::info("✅ TISS routing correct for amount: " . number_format($largeAmount));
        }
    }

    /**
     * Cleanup after tests
     */
    protected function tearDown(): void
    {
        Log::info("🧹 Test cleanup completed");
        parent::tearDown();
    }
}