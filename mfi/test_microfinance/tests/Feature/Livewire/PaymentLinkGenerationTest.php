<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Http\Livewire\Clients\Clients;
use App\Services\PaymentLinkService;
use App\Models\ClientsModel;
use App\Models\User;
use App\Models\approvals;
use App\Services\AccountCreationService;
use App\Services\BillingService;
use App\Services\MemberNumberGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Mockery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaymentLinkGenerationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;
    protected $mockPaymentService;
    protected $mockBillingService;
    protected $mockAccountService;
    protected $mockMemberNumberGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'current_team_id' => 1,
            'branch' => '1'
        ]);

        // Mock services
        $this->mockPaymentService = Mockery::mock(PaymentLinkService::class);
        $this->mockBillingService = Mockery::mock(BillingService::class);
        $this->mockAccountService = Mockery::mock(AccountCreationService::class);
        $this->mockMemberNumberGenerator = Mockery::mock(MemberNumberGeneratorService::class);

        // Bind mocked services
        $this->app->instance(PaymentLinkService::class, $this->mockPaymentService);
        $this->app->instance(BillingService::class, $this->mockBillingService);
        $this->app->instance(AccountCreationService::class, $this->mockAccountService);
        $this->app->instance(MemberNumberGeneratorService::class, $this->mockMemberNumberGenerator);

        // Mock queue and mail
        Bus::fake();
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_payment_link_successfully_for_new_member()
    {
        // Arrange
        $this->setupDatabase();
        
        $expectedPaymentUrl = 'https://payment.example.com/pay/123456';
        $expectedLinkId = 'link_123456';
        $expectedTotalAmount = 50000;

        $this->mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->andReturn([
                'data' => [
                    'payment_url' => $expectedPaymentUrl,
                    'link_id' => $expectedLinkId,
                    'total_amount' => $expectedTotalAmount,
                    'items' => [
                        [
                            'type' => 'service',
                            'product_service_reference' => 1,
                            'product_service_name' => 'Registration Fee',
                            'amount' => 25000,
                            'is_required' => true,
                            'allow_partial' => false
                        ],
                        [
                            'type' => 'service',
                            'product_service_reference' => 2,
                            'product_service_name' => 'Share Capital',
                            'amount' => 25000,
                            'is_required' => true,
                            'allow_partial' => false
                        ]
                    ]
                ]
            ]);

        $this->mockMemberNumberGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(1001);

        $this->mockAccountService->shouldReceive('createAccount')
            ->times(3) // For shares, savings, and deposits accounts
            ->andReturn((object)[
                'account_number' => 'ACC001',
                'account_name' => 'Test Account',
                'type' => 'capital_accounts'
            ]);

        // Act
        Livewire::actingAs($this->user)
            ->test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('phone_number', '0712345678')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('id_type', 'nida')
            ->set('nida_number', '19900101-12345-12345-12')
            ->set('marital_status', 'single')
            ->set('email', 'john.doe@example.com')
            ->set('address', '123 Test Street')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzanian')
            ->set('next_of_kin_name', 'Jane Doe')
            ->set('next_of_kin_phone', '0712345679')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('generatedControlNumbers', [
                [
                    'service_code' => 'REG',
                    'control_number' => 'CN123456789',
                    'amount' => 25000
                ],
                [
                    'service_code' => 'SHC',
                    'control_number' => 'CN987654321',
                    'amount' => 25000
                ]
            ])
            ->call('save');

        // Assert
        $this->assertDatabaseHas('bills', [
            'client_number' => 1001,
            'payment_link' => $expectedPaymentUrl,
            'payment_link_id' => $expectedLinkId,
            'payment_link_generated_at' => now()->toDateString()
        ]);

        // Verify that bills were created with payment link information
        $bills = DB::table('bills')->where('client_number', 1001)->get();
        $this->assertCount(2, $bills);
        
        foreach ($bills as $bill) {
            $this->assertEquals($expectedPaymentUrl, $bill->payment_link);
            $this->assertEquals($expectedLinkId, $bill->payment_link_id);
            $this->assertNotNull($bill->payment_link_generated_at);
        }
    }

    /** @test */
    public function it_falls_back_to_legacy_url_when_payment_service_fails()
    {
        // Arrange
        $this->setupDatabase();
        
        $this->mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->andThrow(new \Exception('Payment service unavailable'));

        $this->mockMemberNumberGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(1001);

        $this->mockAccountService->shouldReceive('createAccount')
            ->times(3)
            ->andReturn((object)[
                'account_number' => 'ACC001',
                'account_name' => 'Test Account',
                'type' => 'capital_accounts'
            ]);

        // Act
        Livewire::actingAs($this->user)
            ->test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('phone_number', '0712345678')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('id_type', 'nida')
            ->set('nida_number', '19900101-12345-12345-12')
            ->set('marital_status', 'single')
            ->set('email', 'john.doe@example.com')
            ->set('address', '123 Test Street')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzanian')
            ->set('next_of_kin_name', 'Jane Doe')
            ->set('next_of_kin_phone', '0712345679')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('generatedControlNumbers', [
                [
                    'service_code' => 'REG',
                    'control_number' => 'CN123456789',
                    'amount' => 25000
                ]
            ])
            ->call('save');

        // Assert
        $expectedLegacyUrl = env('PAYMENT_LINK') . '/123456/1001';
        
        $this->assertDatabaseHas('bills', [
            'client_number' => 1001,
            'payment_link' => $expectedLegacyUrl
        ]);
    }

    /** @test */
    public function it_handles_empty_payment_items_gracefully()
    {
        // Arrange
        $this->setupDatabase();
        
        $this->mockMemberNumberGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(1001);

        $this->mockAccountService->shouldReceive('createAccount')
            ->times(3)
            ->andReturn((object)[
                'account_number' => 'ACC001',
                'account_name' => 'Test Account',
                'type' => 'capital_accounts'
            ]);

        // Act - No control numbers generated
        Livewire::actingAs($this->user)
            ->test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('phone_number', '0712345678')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('id_type', 'nida')
            ->set('nida_number', '19900101-12345-12345-12')
            ->set('marital_status', 'single')
            ->set('email', 'john.doe@example.com')
            ->set('address', '123 Test Street')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzanian')
            ->set('next_of_kin_name', 'Jane Doe')
            ->set('next_of_kin_phone', '0712345679')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('generatedControlNumbers', []) // Empty control numbers
            ->call('save');

        // Assert
        $expectedLegacyUrl = env('PAYMENT_LINK') . '/123456/1001';
        
        // Should still create client but with legacy URL
        $this->assertDatabaseHas('clients', [
            'client_number' => 1001,
            'first_name' => 'JOHN',
            'last_name' => 'DOE'
        ]);
    }

    /** @test */
    public function it_validates_payment_data_structure()
    {
        // Arrange
        $this->setupDatabase();
        
        $this->mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->with(Mockery::on(function ($paymentData) {
                // Validate payment data structure
                $this->assertArrayHasKey('description', $paymentData);
                $this->assertArrayHasKey('target', $paymentData);
                $this->assertArrayHasKey('customer_reference', $paymentData);
                $this->assertArrayHasKey('customer_name', $paymentData);
                $this->assertArrayHasKey('customer_phone', $paymentData);
                $this->assertArrayHasKey('customer_email', $paymentData);
                $this->assertArrayHasKey('expires_at', $paymentData);
                $this->assertArrayHasKey('items', $paymentData);
                
                // Validate specific values
                $this->assertEquals('individual', $paymentData['target']);
                $this->assertEquals(1001, $paymentData['customer_reference']);
                $this->assertEquals('JOHN DOE', $paymentData['customer_name']);
                $this->assertEquals('0712345678', $paymentData['customer_phone']);
                $this->assertEquals('john.doe@example.com', $paymentData['customer_email']);
                
                // Validate items structure
                $this->assertIsArray($paymentData['items']);
                $this->assertCount(2, $paymentData['items']);
                
                foreach ($paymentData['items'] as $item) {
                    $this->assertArrayHasKey('type', $item);
                    $this->assertArrayHasKey('product_service_reference', $item);
                    $this->assertArrayHasKey('product_service_name', $item);
                    $this->assertArrayHasKey('amount', $item);
                    $this->assertArrayHasKey('is_required', $item);
                    $this->assertArrayHasKey('allow_partial', $item);
                }
                
                return true;
            }))
            ->andReturn([
                'data' => [
                    'payment_url' => 'https://payment.example.com/pay/123456',
                    'link_id' => 'link_123456',
                    'total_amount' => 50000
                ]
            ]);

        $this->mockMemberNumberGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(1001);

        $this->mockAccountService->shouldReceive('createAccount')
            ->times(3)
            ->andReturn((object)[
                'account_number' => 'ACC001',
                'account_name' => 'Test Account',
                'type' => 'capital_accounts'
            ]);

        // Act
        Livewire::actingAs($this->user)
            ->test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('phone_number', '0712345678')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('id_type', 'nida')
            ->set('nida_number', '19900101-12345-12345-12')
            ->set('marital_status', 'single')
            ->set('email', 'john.doe@example.com')
            ->set('address', '123 Test Street')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzanian')
            ->set('next_of_kin_name', 'Jane Doe')
            ->set('next_of_kin_phone', '0712345679')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('generatedControlNumbers', [
                [
                    'service_code' => 'REG',
                    'control_number' => 'CN123456789',
                    'amount' => 25000
                ],
                [
                    'service_code' => 'SHC',
                    'control_number' => 'CN987654321',
                    'amount' => 25000
                ]
            ])
            ->call('save');

        // Assert - If we reach here, the payment data structure validation passed
        $this->assertTrue(true);
    }

    /** @test */
    public function it_logs_payment_link_generation_events()
    {
        // Arrange
        $this->setupDatabase();
        
        Log::shouldReceive('info')
            ->with('Generating payment link for member registration', Mockery::any())
            ->once();
            
        Log::shouldReceive('info')
            ->with('Bills found for payment link', Mockery::any())
            ->once();
            
        Log::shouldReceive('info')
            ->with('Payment link generated successfully', Mockery::any())
            ->once();

        $this->mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->andReturn([
                'data' => [
                    'payment_url' => 'https://payment.example.com/pay/123456',
                    'link_id' => 'link_123456',
                    'total_amount' => 50000
                ]
            ]);

        $this->mockMemberNumberGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(1001);

        $this->mockAccountService->shouldReceive('createAccount')
            ->times(3)
            ->andReturn((object)[
                'account_number' => 'ACC001',
                'account_name' => 'Test Account',
                'type' => 'capital_accounts'
            ]);

        // Act
        Livewire::actingAs($this->user)
            ->test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('phone_number', '0712345678')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('id_type', 'nida')
            ->set('nida_number', '19900101-12345-12345-12')
            ->set('marital_status', 'single')
            ->set('email', 'john.doe@example.com')
            ->set('address', '123 Test Street')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzanian')
            ->set('next_of_kin_name', 'Jane Doe')
            ->set('next_of_kin_phone', '0712345679')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('generatedControlNumbers', [
                [
                    'service_code' => 'REG',
                    'control_number' => 'CN123456789',
                    'amount' => 25000
                ]
            ])
            ->call('save');

        // Assert - Logging assertions are handled by Mockery expectations above
        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_partial_payment_modes_correctly()
    {
        // Arrange
        $this->setupDatabase();
        
        // Create a service with partial payment mode
        DB::table('services')->insert([
            'id' => 3,
            'code' => 'PARTIAL',
            'name' => 'Partial Payment Service',
            'is_recurring' => false,
            'payment_mode' => 'partial',
            'lower_limit' => 10000
        ]);

        $this->mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->with(Mockery::on(function ($paymentData) {
                // Check that partial payment items have allow_partial = true
                foreach ($paymentData['items'] as $item) {
                    if ($item['product_service_name'] === 'Partial Payment Service') {
                        $this->assertTrue($item['allow_partial']);
                    }
                }
                return true;
            }))
            ->andReturn([
                'data' => [
                    'payment_url' => 'https://payment.example.com/pay/123456',
                    'link_id' => 'link_123456',
                    'total_amount' => 10000
                ]
            ]);

        $this->mockMemberNumberGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(1001);

        $this->mockAccountService->shouldReceive('createAccount')
            ->times(3)
            ->andReturn((object)[
                'account_number' => 'ACC001',
                'account_name' => 'Test Account',
                'type' => 'capital_accounts'
            ]);

        // Act
        Livewire::actingAs($this->user)
            ->test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('phone_number', '0712345678')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('id_type', 'nida')
            ->set('nida_number', '19900101-12345-12345-12')
            ->set('marital_status', 'single')
            ->set('email', 'john.doe@example.com')
            ->set('address', '123 Test Street')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzanian')
            ->set('next_of_kin_name', 'Jane Doe')
            ->set('next_of_kin_phone', '0712345679')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('generatedControlNumbers', [
                [
                    'service_code' => 'PARTIAL',
                    'control_number' => 'CN123456789',
                    'amount' => 10000
                ]
            ])
            ->call('save');

        // Assert
        $this->assertDatabaseHas('bills', [
            'client_number' => 1001,
            'payment_link' => 'https://payment.example.com/pay/123456'
        ]);
    }

    private function setupDatabase()
    {
        // Create required database records
        DB::table('branches')->insert([
            'id' => 1,
            'name' => 'Test Branch',
            'branch_number' => 'TB001',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('institutions')->insert([
            'id' => 1,
            'name' => 'Default SACCOS',
            'code' => 'SACCOS001',
            'institution_id' => '123456',
            'mandatory_shares_account' => '1000',
            'mandatory_savings_account' => '2000',
            'mandatory_deposits_account' => '3000',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('services')->insert([
            [
                'id' => 1,
                'code' => 'REG',
                'name' => 'Registration Fee',
                'is_recurring' => false,
                'payment_mode' => 'full',
                'lower_limit' => 25000
            ],
            [
                'id' => 2,
                'code' => 'SHC',
                'name' => 'Share Capital',
                'is_recurring' => false,
                'payment_mode' => 'full',
                'lower_limit' => 25000
            ]
        ]);

        DB::table('member_groups')->insert([
            'id' => 1,
            'name' => 'Regular Member',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
