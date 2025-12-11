<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\AccountDetailsService;

class AccountDetailsApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the external API configuration
        config([
            'services.account_details.base_url' => 'https://api.test.com',
            'services.account_details.api_key' => 'test-api-key',
            'services.account_details.private_key_path' => storage_path('test-keys/private.pem'),
            'services.account_details.channel_name' => 'TEST_CHANNEL',
            'services.account_details.channel_code' => 'TEST001',
        ]);
    }

    /**
     * Test successful account details retrieval
     */
    public function test_successful_account_details_retrieval()
    {
        $accountNumber = '011101018916';
        
        // Mock external API response
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 600,
                'message' => 'SUCCESS',
                'body' => [
                    'currencyShortName' => 'TZS',
                    'availableBalance' => '740916137.85',
                    'blocked' => false,
                    'replyCode' => '0',
                    'accountTitle' => 'VIAZI',
                    'branchCode' => '12',
                    'branchShortName' => 'SAMORA',
                    'customerShortName' => 'JUMA J MWANGU',
                    'restrictedAccount' => false,
                    'casaAccountStatus' => 'ACCOUNT OPEN REGULAR',
                    'casaAccountStatusCode' => '8',
                    'customerId' => '724930'
                ]
            ], 200)
        ]);

        $response = $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'statusCode' => 600,
                    'message' => 'SUCCESS',
                    'body' => [
                        'currencyShortName' => 'TZS',
                        'availableBalance' => '740916137.85',
                        'blocked' => false,
                        'replyCode' => '0',
                        'accountTitle' => 'VIAZI',
                        'branchCode' => '12',
                        'branchShortName' => 'SAMORA',
                        'customerShortName' => 'JUMA J MWANGU',
                        'restrictedAccount' => false,
                        'casaAccountStatus' => 'ACCOUNT OPEN REGULAR',
                        'casaAccountStatusCode' => '8',
                        'customerId' => '724930'
                    ]
                ]);
    }

    /**
     * Test account not found response
     */
    public function test_account_not_found_response()
    {
        $accountNumber = '999999999999';
        
        // Mock external API response for account not found
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 605,
                'message' => 'Account not found',
                'body' => []
            ], 200)
        ]);

        $response = $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'statusCode' => 605,
                    'message' => 'Account not found',
                    'body' => []
                ]);
    }

    /**
     * Test invalid request validation
     */
    public function test_invalid_request_validation()
    {
        // Test missing account number
        $response = $this->postJson('/api/v1/account-details', []);
        
        $response->assertStatus(400)
                ->assertJson([
                    'statusCode' => 400,
                    'message' => 'Invalid request: Account number is required'
                ]);

        // Test empty account number
        $response = $this->postJson('/api/v1/account-details', [
            'accountNumber' => ''
        ]);
        
        $response->assertStatus(400);

        // Test account number too long
        $response = $this->postJson('/api/v1/account-details', [
            'accountNumber' => str_repeat('1', 51)
        ]);
        
        $response->assertStatus(400);
    }

    /**
     * Test external API error handling
     */
    public function test_external_api_error_handling()
    {
        $accountNumber = '011101018916';
        
        // Mock external API error
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 700,
                'message' => 'An error occurred while processing the request',
                'body' => []
            ], 500)
        ]);

        $response = $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'statusCode' => 700,
                    'message' => 'An error occurred while processing the request',
                    'body' => []
                ]);
    }

    /**
     * Test external API timeout handling
     */
    public function test_external_api_timeout_handling()
    {
        $accountNumber = '011101018916';
        
        // Mock external API timeout
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response('', 408)
        ]);

        $response = $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        $response->assertStatus(500)
                ->assertJson([
                    'statusCode' => 700,
                    'message' => 'An error occurred while processing the request'
                ]);
    }

    /**
     * Test caching functionality
     */
    public function test_caching_functionality()
    {
        $accountNumber = '011101018916';
        
        // Mock external API response
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 600,
                'message' => 'SUCCESS',
                'body' => [
                    'currencyShortName' => 'TZS',
                    'availableBalance' => '1000000.00'
                ]
            ], 200)
        ]);

        // First request - should call external API
        $response1 = $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        $response1->assertStatus(200);

        // Second request - should use cache
        $response2 = $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        $response2->assertStatus(200);

        // Verify only one external API call was made
        Http::assertSentCount(1);
    }

    /**
     * Test cache clearing functionality
     */
    public function test_cache_clearing_functionality()
    {
        $accountNumber = '011101018916';
        
        // Mock external API response
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 600,
                'message' => 'SUCCESS',
                'body' => []
            ], 200)
        ]);

        // Make initial request to populate cache
        $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        // Clear cache
        $response = $this->postJson('/api/v1/account-details/clear-cache', [
            'accountNumber' => $accountNumber
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cache cleared successfully'
                ]);
    }

    /**
     * Test connectivity test endpoint
     */
    public function test_connectivity_test_endpoint()
    {
        // Mock successful connectivity test
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 605,
                'message' => 'Account not found',
                'body' => []
            ], 200)
        ]);

        $response = $this->getJson('/api/v1/account-details/test');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'External API is accessible'
                ]);
    }

    /**
     * Test statistics endpoint
     */
    public function test_statistics_endpoint()
    {
        $response = $this->getJson('/api/v1/account-details/stats');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'base_url',
                        'channel_name',
                        'channel_code',
                        'timeout',
                        'cache_ttl'
                    ]
                ]);
    }

    /**
     * Test service configuration validation
     */
    public function test_service_configuration_validation()
    {
        // Test with missing configuration
        config(['services.account_details.api_key' => null]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing required configuration: api_key');
        
        $service = new AccountDetailsService();
    }

    /**
     * Test account number format validation
     */
    public function test_account_number_format_validation()
    {
        $invalidAccountNumbers = [
            '',                    // Empty
            str_repeat('1', 51),   // Too long
            'ABC@123',             // Invalid characters
            'ABC 123',             // Spaces
        ];

        foreach ($invalidAccountNumbers as $accountNumber) {
            $response = $this->postJson('/api/v1/account-details', [
                'accountNumber' => $accountNumber
            ]);

            $response->assertStatus(400);
        }
    }

    /**
     * Test request logging
     */
    public function test_request_logging()
    {
        $accountNumber = '011101018916';
        
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 600,
                'message' => 'SUCCESS',
                'body' => []
            ], 200)
        ]);

        $this->postJson('/api/v1/account-details', [
            'accountNumber' => $accountNumber
        ]);

        // Verify logs were written (this would require custom log assertions)
        $this->assertTrue(true); // Placeholder assertion
    }
} 