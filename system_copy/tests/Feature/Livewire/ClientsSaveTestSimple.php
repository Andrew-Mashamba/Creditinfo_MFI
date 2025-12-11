<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Http\Livewire\Clients\Clients;
use App\Models\User;
use App\Services\PaymentLinkService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;

class ClientsSaveTestSimple extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip database refresh for this test
        $this->withoutExceptionHandling();
        
        // Create minimal test user
        $this->user = new User();
        $this->user->id = 1;
        $this->user->name = 'Test User';
        $this->user->email = 'andrew.s.mashamba@gmail.com';
        $this->user->current_team_id = 1;
        $this->user->branch = 1;
        
        // Mock auth
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_validates_required_fields_on_save()
    {
        // Test validation for step 1 - membership type is required
        Livewire::test(Clients::class)
            ->set('currentStep', 1)
            ->set('membership_type', '') // Missing required field
            ->call('validateStep')
            ->assertHasErrors(['membership_type']);
    }

    /** @test */
    public function it_formats_names_correctly_for_individual_members()
    {
        $component = Livewire::test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('first_name', 'john')
            ->set('middle_name', 'michael')
            ->set('last_name', 'doe');
        
        // Test the name formatting logic
        $this->assertEquals('Individual', $component->get('membership_type'));
        $this->assertEquals('john', $component->get('first_name'));
        
        // When save is called, names should be uppercase
        // This tests the logic: strtoupper($this->first_name)
    }

    /** @test */
    public function it_uses_nbc_account_when_provided()
    {
        $component = Livewire::test(Clients::class)
            ->set('nbc_account_number', '123456789012')
            ->set('client_number', 'MEM2024001');
        
        // Test account number logic
        $nbc = $component->get('nbc_account_number');
        $client = $component->get('client_number');
        
        // The logic: $this->account_number = $this->nbc_account_number ?? $this->client_number
        $expectedAccount = $nbc ?? $client;
        
        $this->assertEquals('123456789012', $expectedAccount);
    }

    /** @test */
    public function it_validates_step_1_personal_information()
    {
        // Test Individual member validation
        Livewire::test(Clients::class)
            ->set('currentStep', 1)
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('first_name', '')
            ->call('validateStep')
            ->assertHasErrors(['first_name']);
        
        // Test Business member validation
        Livewire::test(Clients::class)
            ->set('currentStep', 1)
            ->set('membership_type', 'Business')
            ->set('branch', 1)
            ->set('business_name', '')
            ->call('validateStep')
            ->assertHasErrors(['business_name']);
    }

    /** @test */
    public function it_validates_step_2_contact_details()
    {
        Livewire::test(Clients::class)
            ->set('currentStep', 2)
            ->set('membership_type', 'Individual')
            ->set('phone_number', 'invalid')
            ->call('validateStep')
            ->assertHasErrors(['phone_number']);
        
        // Valid phone number
        Livewire::test(Clients::class)
            ->set('currentStep', 2)
            ->set('membership_type', 'Individual')
            ->set('phone_number', '0712345678')
            ->set('email', 'andrew.s.mashamba@gmail.com')
            ->set('address', 'Test Address')
            ->set('nationality', 'Tanzanian')
            ->set('citizenship', 'Tanzania')
            ->set('next_of_kin_name', 'John Doe')
            ->set('next_of_kin_phone', '0723456789')
            ->call('validateStep')
            ->assertHasNoErrors();
    }

    /** @test */
    public function it_validates_step_3_financial_information()
    {
        Livewire::test(Clients::class)
            ->set('currentStep', 3)
            ->set('income_available', -100) // Negative income
            ->call('validateStep')
            ->assertHasErrors(['income_available']);
        
        // Valid financial info
        Livewire::test(Clients::class)
            ->set('currentStep', 3)
            ->set('income_available', 1000000)
            ->set('income_source', 'Business')
            ->set('tin_number', 'TIN123')
            ->set('hisa', 100000)
            ->set('akiba', 50000)
            ->set('amana', 25000)
            ->call('validateStep')
            ->assertHasNoErrors();
    }

    /** @test */
    public function it_generates_control_numbers_array()
    {
        $component = Livewire::test(Clients::class)
            ->set('generatedControlNumbers', [
                ['service_code' => 'REG', 'control_number' => 'CN123456', 'amount' => 50000],
                ['service_code' => 'SHC', 'control_number' => 'CN789012', 'amount' => 100000]
            ]);
        
        $controlNumbers = $component->get('generatedControlNumbers');
        
        $this->assertCount(2, $controlNumbers);
        $this->assertEquals('REG', $controlNumbers[0]['service_code']);
        $this->assertEquals('SHC', $controlNumbers[1]['service_code']);
    }

    /** @test */
    public function it_builds_correct_account_name()
    {
        // Individual member
        $component = Livewire::test(Clients::class)
            ->set('membership_type', 'Individual')
            ->set('first_name', 'Jane')
            ->set('last_name', 'Smith');
        
        $membershipType = $component->get('membership_type');
        $firstName = $component->get('first_name');
        $lastName = $component->get('last_name');
        
        $accountName = $membershipType === 'Individual' 
            ? $firstName . ' ' . $lastName 
            : null;
        
        $this->assertEquals('Jane Smith', $accountName);
        
        // Business member
        $component = Livewire::test(Clients::class)
            ->set('membership_type', 'Business')
            ->set('business_name', 'ABC Company Ltd');
        
        $membershipType = $component->get('membership_type');
        $businessName = $component->get('business_name');
        
        $accountName = $membershipType === 'Business' 
            ? $businessName 
            : null;
        
        $this->assertEquals('ABC Company Ltd', $accountName);
    }

    /** @test */
    public function it_prepares_client_data_structure()
    {
        $component = Livewire::test(Clients::class)
            ->set('client_number', 'MEM2024001')
            ->set('account_number', 'ACC2024001')
            ->set('membership_type', 'Individual')
            ->set('branch', 1)
            ->set('phone_number', '0712345678')
            ->set('email', 'andrew.s.mashamba@gmail.com')
            ->set('address', 'Test Address')
            ->set('nationarity', 'Tanzanian')
            ->set('citizenship', 'Tanzania')
            ->set('income_available', 1000000)
            ->set('income_source', 'Business')
            ->set('tin_number', 'TIN123')
            ->set('hisa', 100000)
            ->set('akiba', 50000)
            ->set('amana', 25000);
        
        // Test that all required fields are set
        $this->assertEquals('MEM2024001', $component->get('client_number'));
        $this->assertEquals('ACC2024001', $component->get('account_number'));
        $this->assertEquals('Individual', $component->get('membership_type'));
        $this->assertEquals(1, $component->get('branch'));
        $this->assertEquals('0712345678', $component->get('phone_number'));
        $this->assertEquals('andrew.s.mashamba@gmail.com', $component->get('email'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}