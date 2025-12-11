<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\NbcBillsPaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Livewire\Livewire;
use App\Http\Livewire\Payments\Payments;

class NbcBillsPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $nbcService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '255715000000',
            'name' => 'Test User'
        ]);
        
        // Mock Storage for keys
        Storage::fake('local');
        Storage::put('keys/private_key.pem', $this->generateTestPrivateKey());
        Storage::put('keys/public_key.pem', $this->generateTestPublicKey());
        
        $this->nbcService = new NbcBillsPaymentService();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    /** @test */
    public function it_can_fetch_billers_from_nbc_api()
    {
        // Mock HTTP response
        Http::fake([
            '*/bills-payments-engine/api/v1/billers-retrieval' => Http::response([
                'statusCode' => '600',
                'message' => 'Success',
                'channelId' => 'SACCOSAPP',
                'requestType' => 'getServiceProviders',
                'channelRef' => 'REF123456',
                'timestamp' => now()->toIso8601String(),
                'serviceProviders' => [
                    [
                        'spCode' => 'PE0001001BC',
                        'shortName' => 'Bugando',
                        'fullName' => 'Bugando Hospital',
                        'active' => true,
                        'category' => 'hospital',
                        'spIcon' => base64_encode('icon'),
                        'categoryIcon' => base64_encode('category_icon')
                    ],
                    [
                        'spCode' => 'PE0002001BC',
                        'shortName' => 'TANESCO',
                        'fullName' => 'Tanzania Electric Supply Company',
                        'active' => true,
                        'category' => 'utility',
                        'spIcon' => base64_encode('icon'),
                        'categoryIcon' => base64_encode('category_icon')
                    ],
                    [
                        'spCode' => 'PE0003001BC',
                        'shortName' => 'DAWASA',
                        'fullName' => 'Dar es Salaam Water and Sewerage Authority',
                        'active' => false,
                        'category' => 'utility',
                        'spIcon' => base64_encode('icon'),
                        'categoryIcon' => base64_encode('category_icon')
                    ]
                ]
            ], 200)
        ]);

        $billers = $this->nbcService->getBillers();

        // Assert structure
        $this->assertIsArray($billers);
        $this->assertArrayHasKey('flat', $billers);
        $this->assertArrayHasKey('grouped', $billers);
        
        // Assert only active billers are returned
        $this->assertCount(2, $billers['flat']);
        
        // Assert grouped by category
        $this->assertArrayHasKey('hospital', $billers['grouped']);
        $this->assertArrayHasKey('utility', $billers['grouped']);
        $this->assertCount(1, $billers['grouped']['hospital']);
        $this->assertCount(1, $billers['grouped']['utility']); // Only active TANESCO
        
        // Assert caching works
        Cache::shouldReceive('remember')->once();
    }

    /** @test */
    public function it_can_inquire_bill_details()
    {
        // Mock HTTP response for inquiry
        Http::fake([
            '*/bills-payments-engine/api/v1/inquiry' => Http::response([
                'statusCode' => '600',
                'message' => 'Success',
                'channelId' => 'SACCOSAPP',
                'spCode' => 'PE0001001BC',
                'requestType' => 'inquiry',
                'channelRef' => 'INQ123456',
                'timestamp' => now()->toIso8601String(),
                'billDetails' => [
                    'billRef' => 'BILL001',
                    'serviceName' => 'Hospital Services',
                    'description' => 'Medical bill payment',
                    'billCreatedAt' => '2023-03-10T10:45:20',
                    'totalAmount' => '50000',
                    'balance' => '50000',
                    'phoneNumber' => '255715000000',
                    'email' => 'patient@example.com',
                    'billedName' => 'John Doe',
                    'currency' => 'TZS',
                    'paymentMode' => 'exact',
                    'expiryDate' => '2024-12-31T23:59:59',
                    'creditAccount' => '0122****1486',
                    'creditCurrency' => 'TZS',
                    'extraFields' => []
                ]
            ], 200)
        ]);

        $result = $this->nbcService->inquireDetailedBill([
            'spCode' => 'PE0001001BC',
            'billRef' => 'BILL001',
            'userId' => 'USER001',
            'branchCode' => '015',
            'extraFields' => []
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('rawResponse', $result);
        
        $billDetails = $result['data'];
        $this->assertEquals('BILL001', $billDetails['billRef']);
        $this->assertEquals('50000', $billDetails['balance']);
        $this->assertEquals('exact', $billDetails['paymentMode']);
        $this->assertEquals('John Doe', $billDetails['billedName']);
    }

    /** @test */
    public function it_handles_failed_bill_inquiry()
    {
        // Mock HTTP response for failed inquiry
        Http::fake([
            '*/bills-payments-engine/api/v1/inquiry' => Http::response([
                'statusCode' => '601',
                'message' => 'Bill not found',
                'channelId' => 'SACCOSAPP',
                'spCode' => 'PE0001001BC',
                'requestType' => 'inquiry',
                'channelRef' => 'INQ123456',
                'timestamp' => now()->toIso8601String()
            ], 200)
        ]);

        $result = $this->nbcService->inquireDetailedBill([
            'spCode' => 'PE0001001BC',
            'billRef' => 'INVALID',
            'userId' => 'USER001',
            'branchCode' => '015',
            'extraFields' => []
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Bill not found', $result['message']);
        $this->assertEquals('601', $result['statusCode']);
    }

    /** @test */
    public function it_can_process_async_payment()
    {
        // Mock HTTP response for payment
        Http::fake([
            '*/bills-payments-engine/api/v1/payment' => Http::response([
                'statusCode' => '600',
                'message' => 'Received and validated, engine is now processing your request',
                'channelId' => 'SACCOSAPP',
                'spCode' => 'PE0001001BC',
                'requestType' => 'payment',
                'channelRef' => 'PAY123456',
                'gatewayRef' => 'PE12371273189238721',
                'timestamp' => now()->toIso8601String(),
                'paymentDetails' => null
            ], 200)
        ]);

        $result = $this->nbcService->processPaymentAsync([
            'spCode' => 'PE0001001BC',
            'billRef' => 'BILL001',
            'amount' => '50000',
            'callbackUrl' => 'https://example.com/callback',
            'userId' => 'USER001',
            'branchCode' => '015',
            'creditAccount' => '0122****1486',
            'debitAccount' => '28012040011',
            'payerName' => 'John Doe',
            'payerPhone' => '255715000000',
            'payerEmail' => 'john@example.com',
            'narration' => 'Test payment',
            'inquiryRawResponse' => json_encode(['test' => 'data'])
        ]);

        $this->assertEquals('processing', $result['status']);
        $this->assertArrayHasKey('gatewayRef', $result);
        $this->assertEquals('PE12371273189238721', $result['gatewayRef']);
        $this->assertArrayHasKey('message', $result);
    }

    /** @test */
    public function it_can_check_payment_status()
    {
        // Mock HTTP response for status check
        Http::fake([
            '*/bills-payments-engine/api/v1/status-check' => Http::response([
                'statusCode' => '600',
                'message' => 'Success',
                'channelId' => 'SACCOSAPP',
                'spCode' => 'PE0001001BC',
                'requestType' => 'statusCheck',
                'channelRef' => 'PAY123456',
                'timestamp' => now()->toIso8601String(),
                'paymentDetails' => [
                    'billRef' => 'BILL001',
                    'gatewayRef' => 'PE12371273189238721',
                    'amount' => '50000',
                    'currency' => 'TZS',
                    'transactionTime' => '20230310T104520',
                    'billerReceipt' => 'RCPT283432988',
                    'remarks' => 'Successfully received',
                    'accountingStatus' => 'success',
                    'billerNotified' => 'processed',
                    'extraFields' => []
                ]
            ], 200)
        ]);

        $result = $this->nbcService->checkPaymentStatus([
            'spCode' => 'PE0001001BC',
            'billRef' => 'BILL001',
            'channelRef' => 'PAY123456'
        ]);

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('data', $result);
        
        $paymentDetails = $result['data']['paymentDetails'];
        $this->assertEquals('success', $paymentDetails['accountingStatus']);
        $this->assertEquals('processed', $paymentDetails['billerNotified']);
        $this->assertEquals('RCPT283432988', $paymentDetails['billerReceipt']);
    }

    /** @test */
    public function livewire_component_can_fetch_billers()
    {
        $this->actingAs($this->user);
        
        // Mock HTTP response
        Http::fake([
            '*/bills-payments-engine/api/v1/billers-retrieval' => Http::response([
                'statusCode' => '600',
                'message' => 'Success',
                'serviceProviders' => [
                    [
                        'spCode' => 'TEST001',
                        'shortName' => 'Test Biller',
                        'fullName' => 'Test Biller Full Name',
                        'active' => true,
                        'category' => 'test'
                    ]
                ]
            ], 200)
        ]);

        Livewire::test(Payments::class)
            ->assertSet('billers', [])
            ->call('fetchBillers')
            ->assertCount('billers', 1)
            ->assertSee('Test Biller');
    }

    /** @test */
    public function livewire_component_can_select_biller()
    {
        $this->actingAs($this->user);
        
        Livewire::test(Payments::class)
            ->set('billers', [
                ['spCode' => 'TEST001', 'shortName' => 'Test Biller']
            ])
            ->call('selectBiller', 'TEST001')
            ->assertSet('selectedSpCode', 'TEST001')
            ->assertSet('billDetails', null)
            ->assertSet('paymentResponse', null);
    }

    /** @test */
    public function livewire_component_validates_bill_inquiry()
    {
        $this->actingAs($this->user);
        
        Livewire::test(Payments::class)
            ->set('selectedSpCode', 'TEST001')
            ->set('billRef', '')
            ->call('inquireBill')
            ->assertHasErrors(['billRef' => 'required']);
    }

    /** @test */
    public function livewire_component_can_reset_payment_flow()
    {
        $this->actingAs($this->user);
        
        Livewire::test(Payments::class)
            ->set('selectedSpCode', 'TEST001')
            ->set('billRef', 'BILL001')
            ->set('billDetails', ['test' => 'data'])
            ->set('amount', '5000')
            ->call('resetBillPayment')
            ->assertSet('selectedSpCode', null)
            ->assertSet('billRef', '')
            ->assertSet('billDetails', null)
            ->assertSet('amount', '');
    }

    /** @test */
    public function payment_callback_controller_handles_success_callback()
    {
        $response = $this->postJson('/api/nbc/payment/callback', [
            'statusCode' => '600',
            'message' => 'Success',
            'channelId' => 'SACCOSAPP',
            'spCode' => 'PE0001001BC',
            'requestType' => 'payment',
            'channelRef' => 'PAY123456',
            'timestamp' => now()->toIso8601String(),
            'paymentDetails' => [
                'billRef' => 'BILL001',
                'gatewayRef' => 'PE12371273189238721',
                'amount' => '50000',
                'currency' => 'TZS',
                'transactionTime' => '20230310T104520',
                'billerReceipt' => 'RCPT283432988',
                'remarks' => 'Successfully received',
                'extraFields' => []
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'statusCode' => '600',
            'message' => 'Success'
        ]);
    }

    /** @test */
    public function payment_callback_controller_handles_failed_callback()
    {
        $response = $this->postJson('/api/nbc/payment/callback', [
            'statusCode' => '601',
            'message' => 'Payment failed',
            'channelId' => 'SACCOSAPP',
            'spCode' => 'PE0001001BC',
            'requestType' => 'payment',
            'channelRef' => 'PAY123456',
            'timestamp' => now()->toIso8601String()
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'statusCode' => '600',
            'message' => 'Acknowledged'
        ]);
    }

    /** @test */
    public function payment_callback_controller_validates_required_fields()
    {
        $response = $this->postJson('/api/nbc/payment/callback', [
            'statusCode' => '600',
            // Missing required fields
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'statusCode',
            'message'
        ]);
    }

    /** @test */
    public function payment_modes_are_validated_correctly()
    {
        $this->actingAs($this->user);
        
        // Test exact payment mode
        Livewire::test(Payments::class)
            ->set('billDetails', [
                'balance' => '5000',
                'totalAmount' => '5000',
                'paymentMode' => 'exact'
            ])
            ->set('paymentMode', 'exact')
            ->set('amount', '5000')
            ->call('validatePaymentAmount')
            ->assertHasNoErrors();
            
        // Test with wrong amount for exact mode
        Livewire::test(Payments::class)
            ->set('billDetails', [
                'balance' => '5000',
                'totalAmount' => '5000',
                'paymentMode' => 'exact'
            ])
            ->set('paymentMode', 'exact')
            ->set('amount', '4000')
            ->call('validatePaymentAmount')
            ->assertHasErrors('amount');
    }

    /**
     * Generate test private key
     */
    private function generateTestPrivateKey()
    {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        
        return $privateKey;
    }

    /**
     * Generate test public key
     */
    private function generateTestPublicKey()
    {
        $privateKey = $this->generateTestPrivateKey();
        $res = openssl_pkey_get_private($privateKey);
        $pubKey = openssl_pkey_get_details($res);
        
        return $pubKey["key"];
    }
}