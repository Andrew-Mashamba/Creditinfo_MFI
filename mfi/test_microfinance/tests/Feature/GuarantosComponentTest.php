<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Livewire\Loans\Guarantos;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Illuminate\Support\Facades\Session;

class GuarantosComponentTest extends TestCase
{
    use RefreshDatabase;

    protected $loan;
    protected $client;
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test client
        $this->client = ClientsModel::create([
            'client_number' => '10003',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '1234567890',
            'client_status' => 'ACTIVE',
        ]);

        // Create test account
        $this->account = AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'ACC001',
            'account_type' => 'savings',
            'balance' => 1000000,
            'available_balance' => 1000000,
            'status' => 'active',
        ]);

        // Create test loan
        $this->loan = LoansModel::create([
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_amount' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Set session data
        Session::put('currentloanID', $this->loan->id);
        Session::put('currentloanClient', '10003');
    }

    /** @test */
    public function it_can_load_existing_guarantor_data()
    {
        // Create guarantor record
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $this->loan->id,
            'guarantor_member_id' => $this->client->id,
            'guarantor_type' => 'self_guarantee',
            'total_guaranteed_amount' => 70000000,
            'available_amount' => 70000000,
            'status' => 'active',
            'guarantee_start_date' => now(),
            'notes' => 'Test guarantor',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create collateral record
        DB::table('loan_collaterals')->insert([
            'loan_guarantor_id' => $guarantorId,
            'collateral_type' => 'savings',
            'account_id' => $this->account->id,
            'collateral_amount' => 500000,
            'account_balance' => 500000,
            'locked_amount' => 500000,
            'available_amount' => 0,
            'status' => 'active',
            'collateral_start_date' => now(),
            'notes' => 'Test collateral',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::test(Guarantos::class)
            ->assertSet('loan_id', $this->loan->id)
            ->assertSet('member_number', '10003')
            ->call('loadExistingGuarantorData')
            ->assertSet('existingGuarantorData', function ($data) {
                return count($data) === 1;
            })
            ->assertSet('existingCollateralData', function ($data) {
                return count($data) === 1;
            });
    }

    /** @test */
    public function it_can_save_guarantor_and_collateral_data()
    {
        Livewire::test(Guarantos::class)
            ->set('guarantorType', 'self_guarantee')
            ->set('selectedAccountId', $this->account->id)
            ->set('collateralAmount', 500000)
            ->set('physicalCollateralDescription', 'Test Vehicle')
            ->set('physicalCollateralValue', 2000000)
            ->set('physicalCollateralLocation', 'Test Location')
            ->set('physicalCollateralOwnerName', 'John Doe')
            ->set('physicalCollateralOwnerContact', '1234567890')
            ->call('saveGuarantorAndCollateral')
            ->assertSet('collateralCommitted', true)
            ->assertHasNoErrors();

        // Verify data was saved to database
        $this->assertDatabaseHas('loan_guarantors', [
            'loan_id' => $this->loan->id,
            'guarantor_member_id' => $this->client->id,
            'guarantor_type' => 'self_guarantee',
            'total_guaranteed_amount' => 70000000,
        ]);

        $this->assertDatabaseHas('loan_collaterals', [
            'collateral_type' => 'savings',
            'account_id' => $this->account->id,
            'collateral_amount' => 500000,
        ]);

        $this->assertDatabaseHas('loan_collaterals', [
            'collateral_type' => 'physical',
            'collateral_amount' => 2000000,
            'physical_collateral_description' => 'Test Vehicle',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Livewire::test(Guarantos::class)
            ->call('saveGuarantorAndCollateral')
            ->assertHasErrors([
                'guarantorType' => 'required',
                'selectedAccountId' => 'required',
                'collateralAmount' => 'required',
            ]);
    }

    /** @test */
    public function it_validates_third_party_guarantor_fields()
    {
        Livewire::test(Guarantos::class)
            ->set('guarantorType', 'third_party_guarantee')
            ->set('selectedAccountId', $this->account->id)
            ->set('collateralAmount', 500000)
            ->call('saveGuarantorAndCollateral')
            ->assertHasErrors([
                'selectedGuarantorId' => 'required_if',
                'guarantorRelationship' => 'required_if',
            ]);
    }

    /** @test */
    public function it_validates_physical_collateral_fields_when_enabled()
    {
        Livewire::test(Guarantos::class)
            ->set('guarantorType', 'self_guarantee')
            ->set('selectedAccountId', $this->account->id)
            ->set('collateralAmount', 500000)
            ->set('showPhysicalCollateral', true)
            ->call('saveGuarantorAndCollateral')
            ->assertHasErrors([
                'physicalCollateralDescription' => 'required_if',
                'physicalCollateralValue' => 'required_if',
                'physicalCollateralLocation' => 'required_if',
                'physicalCollateralOwnerName' => 'required_if',
                'physicalCollateralOwnerContact' => 'required_if',
            ]);
    }

    /** @test */
    public function it_can_get_all_member_accounts()
    {
        // Create additional account
        AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'ACC002',
            'account_type' => 'deposits',
            'balance' => 2000000,
            'available_balance' => 2000000,
            'status' => 'active',
        ]);

        Livewire::test(Guarantos::class)
            ->call('getAllMemberAccounts')
            ->assertSet('member_number', '10003');
    }

    /** @test */
    public function it_can_commit_collateral()
    {
        Livewire::test(Guarantos::class)
            ->set('guarantorType', 'self_guarantee')
            ->set('selectedAccountId', $this->account->id)
            ->set('collateralAmount', 500000)
            ->call('commitCollateral')
            ->assertSet('collateralCommitted', true)
            ->assertHasNoErrors();
    }

    /** @test */
    public function it_can_refresh_collateral_data()
    {
        Livewire::test(Guarantos::class)
            ->call('refreshCollateralData')
            ->assertHasNoErrors();
    }

    /** @test */
    public function it_can_toggle_debug_info()
    {
        Livewire::test(Guarantos::class)
            ->assertSet('showDebugInfo', true)
            ->call('toggleDebugInfo')
            ->assertSet('showDebugInfo', false)
            ->call('toggleDebugInfo')
            ->assertSet('showDebugInfo', true);
    }

    /** @test */
    public function it_can_refresh_debug_info()
    {
        Livewire::test(Guarantos::class)
            ->call('refreshDebugInfo')
            ->assertHasNoErrors();
    }

    /** @test */
    public function it_handles_restructure_loans_correctly()
    {
        // Create a restructure loan
        $restructureLoan = LoansModel::create([
            'loan_id' => 'LN202508171898',
            'client_number' => '10003',
            'principle' => 50000000,
            'loan_amount' => 50000000,
            'loan_type_2' => 'Restructure',
            'restructured_loan' => $this->loan->id,
            'status' => 'active',
        ]);

        Session::put('currentloanID', $restructureLoan->id);

        // Create guarantor for original loan
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $this->loan->id,
            'guarantor_member_id' => $this->client->id,
            'guarantor_type' => 'self_guarantee',
            'total_guaranteed_amount' => 70000000,
            'available_amount' => 70000000,
            'status' => 'active',
            'guarantee_start_date' => now(),
            'notes' => 'Original loan guarantor',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::test(Guarantos::class)
            ->assertSet('loan_id', $restructureLoan->id)
            ->assertSet('loanType', 'Restructure')
            ->call('loadExistingGuarantorData')
            ->assertSet('existingGuarantorData', function ($data) {
                return count($data) === 1;
            });
    }

    /** @test */
    public function it_handles_missing_loan_data()
    {
        Session::forget('currentloanID');

        Livewire::test(Guarantos::class)
            ->assertSet('loan_id', null)
            ->call('loadExistingGuarantorData')
            ->assertSet('existingGuarantorData', [])
            ->assertSet('existingCollateralData', []);
    }

    /** @test */
    public function it_handles_missing_client_data()
    {
        // Delete the client
        $this->client->delete();

        Livewire::test(Guarantos::class)
            ->set('guarantorType', 'self_guarantee')
            ->set('selectedAccountId', $this->account->id)
            ->set('collateralAmount', 500000)
            ->call('saveGuarantorAndCollateral')
            ->assertHasErrors();
    }

    /** @test */
    public function it_handles_missing_account_data()
    {
        Livewire::test(Guarantos::class)
            ->set('guarantorType', 'self_guarantee')
            ->set('selectedAccountId', 99999) // Non-existent account
            ->set('collateralAmount', 500000)
            ->call('saveGuarantorAndCollateral')
            ->assertHasNoErrors(); // Should still save but with default collateral type
    }

    /** @test */
    public function it_correctly_maps_account_types_to_collateral_types()
    {
        // Test savings account
        $savingsAccount = AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'SAV001',
            'account_type' => 'savings',
            'balance' => 1000000,
            'status' => 'active',
        ]);

        // Test deposits account
        $depositsAccount = AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'DEP001',
            'account_type' => 'deposits',
            'balance' => 2000000,
            'status' => 'active',
        ]);

        // Test shares account
        $sharesAccount = AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'SHR001',
            'account_type' => 'shares',
            'balance' => 3000000,
            'status' => 'active',
        ]);

        Livewire::test(Guarantos::class)
            ->set('guarantorType', 'self_guarantee')
            ->set('selectedAccountId', $savingsAccount->id)
            ->set('collateralAmount', 500000)
            ->call('saveGuarantorAndCollateral')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('loan_collaterals', [
            'collateral_type' => 'savings',
            'account_id' => $savingsAccount->id,
        ]);
    }

    /** @test */
    public function it_handles_debug_info_properties()
    {
        Livewire::test(Guarantos::class)
            ->assertSet('debugInfo', [])
            ->assertSet('showDebugInfo', true)
            ->call('loadExistingGuarantorData')
            ->assertSet('debugInfo', function ($debugInfo) {
                return is_array($debugInfo) && isset($debugInfo['method_called']);
            });
    }
}
