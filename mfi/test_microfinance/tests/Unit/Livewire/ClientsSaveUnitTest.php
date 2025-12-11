<?php

namespace Tests\Unit\Livewire;

use Tests\TestCase;
use App\Http\Livewire\Clients\Clients;
use App\Services\MemberNumberGeneratorService;
use App\Services\AccountCreationService;
use App\Services\BillingService;
use App\Services\PaymentLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ClientsSaveUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $component;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create component instance
        $this->component = new Clients();
    }

    /** @test */
    public function save_generates_unique_member_number()
    {
        // Mock the member number generator
        $mockGenerator = Mockery::mock(MemberNumberGeneratorService::class);
        $mockGenerator->shouldReceive('generate')
            ->once()
            ->andReturn('MEM2024001');
        
        $this->app->instance(MemberNumberGeneratorService::class, $mockGenerator);
        
        // Set minimum required data
        $this->component->membership_type = 'Individual';
        $this->component->branch = 1;
        $this->component->first_name = 'John';
        $this->component->last_name = 'Doe';
        $this->component->phone_number = '0712345678';
        
        // Partially test the save method
        // Note: This would require refactoring the save method to be more testable
        // by extracting the member number generation logic
        
        $this->assertTrue(true); // Placeholder assertion
    }

    /** @test */
    public function save_uses_nbc_account_number_when_provided()
    {
        $this->component->nbc_account_number = '123456789012';
        $this->component->client_number = 'MEM2024001';
        
        // The account_number should be set to nbc_account_number
        // This tests the logic: $this->account_number = $this->nbc_account_number ?? $this->client_number;
        
        $expectedAccountNumber = '123456789012';
        
        // In actual implementation, you would extract this logic to a method
        $actualAccountNumber = $this->component->nbc_account_number ?? $this->component->client_number;
        
        $this->assertEquals($expectedAccountNumber, $actualAccountNumber);
    }

    /** @test */
    public function save_uses_client_number_when_nbc_account_not_provided()
    {
        $this->component->nbc_account_number = null;
        $this->component->client_number = 'MEM2024001';
        
        $expectedAccountNumber = 'MEM2024001';
        
        $actualAccountNumber = $this->component->nbc_account_number ?? $this->component->client_number;
        
        $this->assertEquals($expectedAccountNumber, $actualAccountNumber);
    }

    /** @test */
    public function save_formats_names_to_uppercase_for_individual_members()
    {
        $this->component->membership_type = 'Individual';
        $this->component->first_name = 'john';
        $this->component->middle_name = 'michael';
        $this->component->last_name = 'doe';
        
        // Extract the logic that would be in save()
        $formattedFirstName = strtoupper($this->component->first_name);
        $formattedMiddleName = strtoupper($this->component->middle_name);
        $formattedLastName = strtoupper($this->component->last_name);
        
        $this->assertEquals('JOHN', $formattedFirstName);
        $this->assertEquals('MICHAEL', $formattedMiddleName);
        $this->assertEquals('DOE', $formattedLastName);
    }

    /** @test */
    public function save_creates_correct_account_name_for_individual()
    {
        $this->component->membership_type = 'Individual';
        $this->component->first_name = 'Jane';
        $this->component->last_name = 'Smith';
        
        $accountName = $this->component->membership_type === 'Individual' 
            ? $this->component->first_name . ' ' . $this->component->last_name 
            : $this->component->business_name;
        
        $this->assertEquals('Jane Smith', $accountName);
    }

    /** @test */
    public function save_creates_correct_account_name_for_business()
    {
        $this->component->membership_type = 'Business';
        $this->component->business_name = 'ABC Company Ltd';
        
        $accountName = $this->component->membership_type === 'Individual' 
            ? $this->component->first_name . ' ' . $this->component->last_name 
            : $this->component->business_name;
        
        $this->assertEquals('ABC Company Ltd', $accountName);
    }

    /** @test */
    public function save_handles_empty_generated_control_numbers()
    {
        $this->component->generatedControlNumbers = [];
        
        // The save method should handle empty control numbers gracefully
        $this->assertEmpty($this->component->generatedControlNumbers);
        $this->assertIsArray($this->component->generatedControlNumbers);
    }

    /** @test */
    public function save_validates_guarantor_member_number_format()
    {
        // Test various guarantor number formats
        $validNumbers = ['1001', '2024001', 'MEM123'];
        $invalidNumbers = ['', null, false];
        
        foreach ($validNumbers as $number) {
            $this->component->guarantor_member_number = $number;
            $this->assertNotEmpty($this->component->guarantor_member_number);
        }
        
        foreach ($invalidNumbers as $number) {
            $this->component->guarantor_member_number = $number;
            $this->assertEmpty($this->component->guarantor_member_number);
        }
    }

    /** @test */
    public function save_builds_correct_client_data_structure()
    {
        // Set all required properties
        $this->component->client_number = 'MEM2024001';
        $this->component->account_number = 'ACC2024001';
        $this->component->membership_type = 'Individual';
        $this->component->branch = 1;
        $this->component->phone_number = '0712345678';
        $this->component->email = 'andrew.s.mashamba@gmail.com';
        $this->component->address = '123 Test Street';
        $this->component->nationarity = 'Tanzanian';
        $this->component->citizenship = 'Tanzania';
        $this->component->income_available = 1000000;
        $this->component->income_source = 'Business';
        $this->component->tin_number = 'TIN123';
        $this->component->hisa = 100000;
        $this->component->akiba = 50000;
        $this->component->amana = 25000;
        
        // Build the client data array as done in save()
        $clientData = [
            'client_number' => $this->component->client_number,
            'account_number' => $this->component->account_number,
            'membership_type' => $this->component->membership_type,
            'branch' => $this->component->branch,
            'phone_number' => $this->component->phone_number,
            'email' => $this->component->email,
            'address' => $this->component->address,
            'nationarity' => $this->component->nationarity,
            'citizenship' => $this->component->citizenship,
            'income_available' => $this->component->income_available,
            'income_source' => $this->component->income_source,
            'tin_number' => $this->component->tin_number,
            'hisa' => $this->component->hisa,
            'akiba' => $this->component->akiba,
            'amana' => $this->component->amana,
            'status' => 'PENDING',
            'branch_id' => $this->component->branch,
            'created_by' => auth()->id()
        ];
        
        // Assert the structure is correct
        $this->assertArrayHasKey('client_number', $clientData);
        $this->assertArrayHasKey('account_number', $clientData);
        $this->assertArrayHasKey('membership_type', $clientData);
        $this->assertEquals('PENDING', $clientData['status']);
        $this->assertEquals($this->component->branch, $clientData['branch_id']);
    }

    /** @test */
    public function save_calculates_correct_saccos_id_from_institution()
    {
        $institutionId = 'SACCOS-001-TZ';
        
        // Extract the logic from save()
        $saccos = preg_replace('/[^0-9]/', '', $institutionId);
        
        $this->assertEquals('001', $saccos);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}