<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Http\Livewire\Clients\Clients;
use App\Models\ClientsModel;
use App\Models\User;
use App\Models\approvals;
use App\Services\PaymentLinkService;
use App\Jobs\ProcessMemberNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Mockery;

class ClientsSaveTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $branch;
    protected $institution;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with proper authentication
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'andrew.s.mashamba@gmail.com',
            'current_team_id' => 1,
            'branch' => 1
        ]);
        
        // Create test branch
        $this->branch = DB::table('branches')->insertGetId([
            'name' => 'Test Branch',
            'code' => 'TB001',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Create test institution
        $this->institution = DB::table('institutions')->insertGetId([
            'institution_id' => 'SACCOS001',
            'mandatory_shares_account' => '1000',
            'mandatory_savings_account' => '2000', 
            'mandatory_deposits_account' => '3000',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Create services
        DB::table('services')->insert([
            [
                'code' => 'REG',
                'name' => 'Registration Fee',
                'is_recurring' => false,
                'payment_mode' => 'full',
                'lower_limit' => 50000,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SHC',
                'name' => 'Share Capital',
                'is_recurring' => false,
                'payment_mode' => 'full',
                'lower_limit' => 100000,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        // Mock storage for file uploads
        Storage::fake('public');
        
        // Fake queue for job dispatching
        Queue::fake();
        Bus::fake();
    }

    /** @test */
    public function it_can_save_individual_member_with_complete_data()
    {
        $this->actingAs($this->user);
        
        // Mock PaymentLinkService
        $mockPaymentService = Mockery::mock(PaymentLinkService::class);
        $mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->andReturn([
                'data' => [
                    'payment_url' => 'http://172.240.241.188/pay/ABC123',
                    'link_id' => 'LINK_ABC123',
                    'total_amount' => 150000,
                    'items' => [
                        ['name' => 'Registration Fee', 'amount' => 50000],
                        ['name' => 'Share Capital', 'amount' => 100000]
                    ]
                ]
            ]);
        
        $this->app->instance(PaymentLinkService::class, $mockPaymentService);
        
        // Create test guarantor
        $guarantor = ClientsModel::create([
            'client_number' => '1001',
            'account_number' => '1001',
            'first_name' => 'JOHN',
            'last_name' => 'DOE',
            'phone_number' => '0712345678',
            'status' => 'ACTIVE',
            'branch' => $this->branch,
            'branch_id' => $this->branch,
            'membership_type' => 'Individual'
        ]);
        
        Livewire::test(Clients::class)
            ->set('currentStep', 4) // Final step
            ->set('membership_type', 'Individual')
            ->set('branch', $this->branch)
            ->set('first_name', 'Jane')
            ->set('middle_name', 'Mary')
            ->set('last_name', 'Smith')
            ->set('gender', 'female')
            ->set('date_of_birth', '1990-01-01')
            ->set('marital_status', 'single')
            ->set('phone_number', '0723456789')
            ->set('email', 'andrew.s.mashamba@gmail.com')
            ->set('address', '123 Main Street')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzania')
            ->set('next_of_kin_name', 'John Smith')
            ->set('next_of_kin_phone', '0754321098')
            ->set('income_available', 1000000)
            ->set('income_source', 'Business')
            ->set('tin_number', 'TIN123456')
            ->set('hisa', 100000)
            ->set('akiba', 50000)
            ->set('amana', 25000)
            ->set('guarantor_member_number', '1001')
            ->set('guarantor_relationship', 'Friend')
            ->set('photo', UploadedFile::fake()->image('profile.jpg'))
            ->set('additionalDocuments', [
                ['file' => UploadedFile::fake()->create('application.pdf'), 'description' => 'Application Letter']
            ])
            ->set('generatedControlNumbers', [
                ['service_code' => 'REG', 'control_number' => 'CN123456', 'amount' => 50000],
                ['service_code' => 'SHC', 'control_number' => 'CN789012', 'amount' => 100000]
            ])
            ->call('save')
            ->assertSessionHas('success')
            ->assertEmitted('refreshClientsList');
        
        // Assert client was created
        $this->assertDatabaseHas('clients', [
            'first_name' => 'JANE',
            'middle_name' => 'MARY',
            'last_name' => 'SMITH',
            'phone_number' => '0723456789',
            'email' => 'andrew.s.mashamba@gmail.com',
            'membership_type' => 'Individual',
            'status' => 'PENDING'
        ]);
        
        // Assert approval was created
        $this->assertDatabaseHas('approvals', [
            'process_name' => 'new_member_registration',
            'process_status' => 'PENDING',
            'approval_status' => 'PENDING',
            'user_id' => $this->user->id
        ]);
        
        // Assert accounts were created
        $this->assertDatabaseHas('accounts', [
            'type' => 'capital_accounts',
            'member_number' => DB::table('clients')->latest()->first()->client_number
        ]);
        
        // Assert bills were created
        $client = ClientsModel::latest()->first();
        $this->assertDatabaseHas('bills', [
            'client_number' => $client->client_number,
            'payment_status' => 'PENDING'
        ]);
        
        // Assert guarantor was created
        $this->assertDatabaseHas('guarantors', [
            'client_id' => $client->id,
            'guarantor_member_id' => $guarantor->id,
            'relationship' => 'Friend'
        ]);
        
        // Assert documents were uploaded
        Storage::disk('public')->assertExists('profile-photos');
        Storage::disk('public')->assertExists('client-documents');
        
        // Assert job was dispatched
        Bus::assertDispatched(ProcessMemberNotifications::class);
    }

    /** @test */
    public function it_can_save_business_member()
    {
        $this->actingAs($this->user);
        
        // Mock PaymentLinkService
        $mockPaymentService = Mockery::mock(PaymentLinkService::class);
        $mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->andReturn([
                'data' => [
                    'payment_url' => 'http://172.240.241.188/pay/BUS123',
                    'link_id' => 'LINK_BUS123',
                    'total_amount' => 150000,
                    'items' => []
                ]
            ]);
        
        $this->app->instance(PaymentLinkService::class, $mockPaymentService);
        
        Livewire::test(Clients::class)
            ->set('currentStep', 4)
            ->set('membership_type', 'Business')
            ->set('branch', $this->branch)
            ->set('business_name', 'ABC Company Ltd')
            ->set('incorporation_number', 'INC123456')
            ->set('phone_number', '0723456789')
            ->set('email', 'andrew.s.mashamba@gmail.com')
            ->set('address', '456 Business Avenue')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzania')
            ->set('income_available', 5000000)
            ->set('income_source', 'Trading')
            ->set('tin_number', 'TIN789012')
            ->set('hisa', 200000)
            ->set('akiba', 100000)
            ->set('amana', 50000)
            ->set('additionalDocuments', [
                ['file' => UploadedFile::fake()->create('incorporation.pdf'), 'description' => 'Incorporation Certificate']
            ])
            ->set('generatedControlNumbers', [
                ['service_code' => 'REG', 'control_number' => 'CN345678', 'amount' => 50000]
            ])
            ->call('save')
            ->assertSessionHas('success');
        
        // Assert business client was created
        $this->assertDatabaseHas('clients', [
            'business_name' => 'ABC Company Ltd',
            'incorporation_number' => 'INC123456',
            'membership_type' => 'Business',
            'status' => 'PENDING'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_before_saving()
    {
        $this->actingAs($this->user);
        
        Livewire::test(Clients::class)
            ->set('currentStep', 4)
            ->set('membership_type', 'Individual')
            ->set('branch', '') // Missing required field
            ->call('save')
            ->assertHasErrors();
    }

    /** @test */
    public function it_handles_payment_link_generation_failure()
    {
        $this->actingAs($this->user);
        
        // Mock PaymentLinkService to throw exception
        $mockPaymentService = Mockery::mock(PaymentLinkService::class);
        $mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->andThrow(new \Exception('Payment gateway error'));
        
        $this->app->instance(PaymentLinkService::class, $mockPaymentService);
        
        Livewire::test(Clients::class)
            ->set('currentStep', 4)
            ->set('membership_type', 'Individual')
            ->set('branch', $this->branch)
            ->set('first_name', 'Test')
            ->set('last_name', 'User')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('marital_status', 'single')
            ->set('phone_number', '0723456789')
            ->set('address', 'Test Address')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzania')
            ->set('next_of_kin_name', 'Next Kin')
            ->set('next_of_kin_phone', '0754321098')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('hisa', 100000)
            ->set('akiba', 50000)
            ->set('amana', 25000)
            ->set('additionalDocuments', [
                ['file' => UploadedFile::fake()->create('doc.pdf'), 'description' => 'Document']
            ])
            ->set('generatedControlNumbers', [])
            ->call('save')
            ->assertSessionHas('success'); // Should still succeed with fallback URL
        
        // Assert client was still created despite payment link failure
        $this->assertDatabaseHas('clients', [
            'first_name' => 'TEST',
            'last_name' => 'USER',
            'status' => 'PENDING'
        ]);
    }

    /** @test */
    public function it_updates_bills_with_payment_link_information()
    {
        $this->actingAs($this->user);
        
        // Create a client first
        $client = ClientsModel::create([
            'client_number' => '2001',
            'account_number' => '2001',
            'first_name' => 'EXISTING',
            'last_name' => 'CLIENT',
            'phone_number' => '0712345678',
            'status' => 'PENDING',
            'branch' => $this->branch,
            'branch_id' => $this->branch,
            'membership_type' => 'Individual'
        ]);
        
        // Create bills for the client
        $bill1 = DB::table('bills')->insertGetId([
            'client_number' => '2001',
            'service_id' => 1,
            'control_number' => 'CN111111',
            'bill_amount' => 50000,
            'payment_status' => 'PENDING',
            'payment_mode' => 'full',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $bill2 = DB::table('bills')->insertGetId([
            'client_number' => '2001',
            'service_id' => 2,
            'control_number' => 'CN222222',
            'bill_amount' => 100000,
            'payment_status' => 'PENDING',
            'payment_mode' => 'full',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Mock PaymentLinkService
        $mockPaymentService = Mockery::mock(PaymentLinkService::class);
        $mockPaymentService->shouldReceive('generateUniversalPaymentLink')
            ->once()
            ->andReturn([
                'data' => [
                    'payment_url' => 'http://172.240.241.188/pay/TEST123',
                    'link_id' => 'LINK_TEST123',
                    'total_amount' => 150000,
                    'items' => [
                        ['name' => 'Registration Fee', 'amount' => 50000],
                        ['name' => 'Share Capital', 'amount' => 100000]
                    ]
                ]
            ]);
        
        $this->app->instance(PaymentLinkService::class, $mockPaymentService);
        
        // Test the save function with existing client
        Livewire::test(Clients::class)
            ->set('client_number', '2001')
            ->set('currentStep', 4)
            ->set('membership_type', 'Individual')
            ->set('branch', $this->branch)
            ->set('first_name', 'EXISTING')
            ->set('last_name', 'CLIENT')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('marital_status', 'single')
            ->set('phone_number', '0712345678')
            ->set('address', 'Test Address')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzania')
            ->set('next_of_kin_name', 'Next Kin')
            ->set('next_of_kin_phone', '0754321098')
            ->set('income_available', 1000000)
            ->set('income_source', 'Business')
            ->set('hisa', 100000)
            ->set('akiba', 50000)
            ->set('amana', 25000)
            ->set('additionalDocuments', [
                ['file' => UploadedFile::fake()->create('doc.pdf'), 'description' => 'Document']
            ])
            ->set('generatedControlNumbers', [])
            ->call('save');
        
        // Assert bills were updated with payment link
        $this->assertDatabaseHas('bills', [
            'id' => $bill1,
            'payment_link' => 'http://172.240.241.188/pay/TEST123',
            'payment_link_id' => 'LINK_TEST123'
        ]);
        
        $this->assertDatabaseHas('bills', [
            'id' => $bill2,
            'payment_link' => 'http://172.240.241.188/pay/TEST123',
            'payment_link_id' => 'LINK_TEST123'
        ]);
    }

    /** @test */
    public function it_validates_guarantor_exists_and_is_active()
    {
        $this->actingAs($this->user);
        
        // Create inactive guarantor
        $inactiveGuarantor = ClientsModel::create([
            'client_number' => '3001',
            'account_number' => '3001',
            'first_name' => 'INACTIVE',
            'last_name' => 'MEMBER',
            'phone_number' => '0712345678',
            'status' => 'INACTIVE', // Not active
            'branch' => $this->branch,
            'branch_id' => $this->branch,
            'membership_type' => 'Individual'
        ]);
        
        Livewire::test(Clients::class)
            ->set('currentStep', 4)
            ->set('membership_type', 'Individual')
            ->set('branch', $this->branch)
            ->set('first_name', 'Test')
            ->set('last_name', 'User')
            ->set('gender', 'male')
            ->set('date_of_birth', '1990-01-01')
            ->set('marital_status', 'single')
            ->set('phone_number', '0723456789')
            ->set('address', 'Test Address')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzania')
            ->set('next_of_kin_name', 'Next Kin')
            ->set('next_of_kin_phone', '0754321098')
            ->set('income_available', 1000000)
            ->set('income_source', 'Employment')
            ->set('hisa', 100000)
            ->set('akiba', 50000)
            ->set('amana', 25000)
            ->set('guarantor_member_number', '3001') // Inactive member
            ->set('guarantor_relationship', 'Friend')
            ->set('additionalDocuments', [
                ['file' => UploadedFile::fake()->create('doc.pdf'), 'description' => 'Document']
            ])
            ->set('generatedControlNumbers', [])
            ->call('save')
            ->assertSessionHas('error', 'Invalid guarantor membership number. Please provide a valid active member number.');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}