<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class GuarantorDatabaseTest extends TestCase
{
    use RefreshDatabase;

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
    }

    /** @test */
    public function it_can_save_guarantor_data_to_database()
    {
        // Create loan
        $loan = LoansModel::create([
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_amount' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Save guarantor data
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $loan->id,
            'guarantor_member_id' => $this->client->id,
            'guarantor_type' => 'self_guarantee',
            'relationship' => null,
            'total_guaranteed_amount' => 70000000,
            'available_amount' => 70000000,
            'status' => 'active',
            'guarantee_start_date' => now(),
            'notes' => 'Test guarantor',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify guarantor was saved
        $this->assertDatabaseHas('loan_guarantors', [
            'loan_id' => $loan->id,
            'guarantor_member_id' => $this->client->id,
            'guarantor_type' => 'self_guarantee',
            'total_guaranteed_amount' => 70000000,
        ]);

        // Verify we can retrieve the guarantor
        $guarantor = DB::table('loan_guarantors')->where('loan_id', $loan->id)->first();
        $this->assertNotNull($guarantor);
        $this->assertEquals($loan->id, $guarantor->loan_id);
    }

    /** @test */
    public function it_can_save_collateral_data_to_database()
    {
        // Create loan
        $loan = LoansModel::create([
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_amount' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Create guarantor first
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $loan->id,
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

        // Save account collateral
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
            'notes' => 'Account collateral',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Save physical collateral
        DB::table('loan_collaterals')->insert([
            'loan_guarantor_id' => $guarantorId,
            'collateral_type' => 'physical',
            'collateral_amount' => 2000000,
            'physical_collateral_description' => 'Test Vehicle',
            'physical_collateral_location' => 'Test Location',
            'physical_collateral_owner_name' => 'John Doe',
            'physical_collateral_owner_contact' => '1234567890',
            'physical_collateral_value' => 2000000,
            'physical_collateral_valuation_date' => now(),
            'status' => 'active',
            'collateral_start_date' => now(),
            'notes' => 'Physical collateral',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify collaterals were saved
        $this->assertDatabaseHas('loan_collaterals', [
            'loan_guarantor_id' => $guarantorId,
            'collateral_type' => 'savings',
            'account_id' => $this->account->id,
            'collateral_amount' => 500000,
        ]);

        $this->assertDatabaseHas('loan_collaterals', [
            'loan_guarantor_id' => $guarantorId,
            'collateral_type' => 'physical',
            'collateral_amount' => 2000000,
            'physical_collateral_description' => 'Test Vehicle',
        ]);

        // Verify we can retrieve collaterals through relationship
        $collaterals = DB::table('loan_collaterals')
            ->join('loan_guarantors', 'loan_collaterals.loan_guarantor_id', '=', 'loan_guarantors.id')
            ->where('loan_guarantors.loan_id', $loan->id)
            ->select('loan_collaterals.*')
            ->get();

        $this->assertEquals(2, $collaterals->count());
    }

    /** @test */
    public function it_can_query_guarantor_data_by_loan_id()
    {
        // Create multiple loans
        $loan1 = LoansModel::create([
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        $loan2 = LoansModel::create([
            'loan_id' => 'LN202508171898',
            'client_number' => '10003',
            'principle' => 50000000,
            'loan_type_2' => 'New',
            'status' => 'active',
        ]);

        // Create guarantor for loan1 only
        DB::table('loan_guarantors')->insert([
            'loan_id' => $loan1->id,
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

        // Query guarantor data for loan1
        $guarantors1 = DB::table('loan_guarantors')->where('loan_id', $loan1->id)->get();
        $this->assertEquals(1, $guarantors1->count());

        // Query guarantor data for loan2 (should be empty)
        $guarantors2 = DB::table('loan_guarantors')->where('loan_id', $loan2->id)->get();
        $this->assertEquals(0, $guarantors2->count());
    }

    /** @test */
    public function it_handles_restructure_loan_relationships()
    {
        // Create original loan
        $originalLoan = LoansModel::create([
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_type_2' => 'New',
            'status' => 'active',
        ]);

        // Create restructure loan
        $restructureLoan = LoansModel::create([
            'loan_id' => 'LN202508171898',
            'client_number' => '10003',
            'principle' => 50000000,
            'loan_type_2' => 'Restructure',
            'restructured_loan' => $originalLoan->id,
            'status' => 'active',
        ]);

        // Create guarantor for original loan
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $originalLoan->id,
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

        // Query guarantor data using restructured loan ID
        $guarantors = DB::table('loan_guarantors')->where('loan_id', $originalLoan->id)->get();
        $this->assertEquals(1, $guarantors->count());

        // Query guarantor data using restructure loan ID (should be empty)
        $restructureGuarantors = DB::table('loan_guarantors')->where('loan_id', $restructureLoan->id)->get();
        $this->assertEquals(0, $restructureGuarantors->count());
    }

    /** @test */
    public function it_validates_loan_id_data_types()
    {
        // Create loan
        $loan = LoansModel::create([
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Test that loan_id is stored as bigint in guarantors table
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $loan->id, // This should be the numeric ID, not the string loan_id
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

        // Verify the data type
        $guarantor = DB::table('loan_guarantors')->where('id', $guarantorId)->first();
        $this->assertIsInt($guarantor->loan_id);
        $this->assertEquals($loan->id, $guarantor->loan_id);
    }

    /** @test */
    public function it_can_retrieve_all_guarantor_loan_ids()
    {
        // Create multiple loans with guarantors
        $loans = [];
        for ($i = 1; $i <= 3; $i++) {
            $loan = LoansModel::create([
                'loan_id' => "LN20250817189{$i}",
                'client_number' => '10003',
                'principle' => 70000000,
                'loan_type_2' => 'Top-up',
                'status' => 'active',
            ]);

            DB::table('loan_guarantors')->insert([
                'loan_id' => $loan->id,
                'guarantor_member_id' => $this->client->id,
                'guarantor_type' => 'self_guarantee',
                'total_guaranteed_amount' => 70000000,
                'available_amount' => 70000000,
                'status' => 'active',
                'guarantee_start_date' => now(),
                'notes' => "Test guarantor {$i}",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $loans[] = $loan;
        }

        // Get all unique loan IDs from guarantors table
        $allGuarantorLoanIds = DB::table('loan_guarantors')
            ->select('loan_id')
            ->distinct()
            ->get()
            ->pluck('loan_id')
            ->toArray();

        $this->assertEquals(3, count($allGuarantorLoanIds));
        $this->assertContains($loans[0]->id, $allGuarantorLoanIds);
        $this->assertContains($loans[1]->id, $allGuarantorLoanIds);
        $this->assertContains($loans[2]->id, $allGuarantorLoanIds);
    }

    /** @test */
    public function it_handles_empty_guarantor_tables()
    {
        // Create loan without guarantor
        $loan = LoansModel::create([
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Query guarantor data (should be empty)
        $guarantors = DB::table('loan_guarantors')->where('loan_id', $loan->id)->get();
        $this->assertEquals(0, $guarantors->count());

        // Query collateral data (should be empty)
        $guarantorIds = DB::table('loan_guarantors')
            ->where('loan_id', $loan->id)
            ->pluck('id');

        $collaterals = DB::table('loan_collaterals')
            ->whereIn('loan_guarantor_id', $guarantorIds)
            ->get();

        $this->assertEquals(0, $collaterals->count());
    }

    /** @test */
    public function it_can_map_account_types_to_collateral_types()
    {
        // Create different account types
        $savingsAccount = AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'SAV001',
            'account_type' => 'savings',
            'balance' => 1000000,
            'status' => 'active',
        ]);

        $depositsAccount = AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'DEP001',
            'account_type' => 'deposits',
            'balance' => 2000000,
            'status' => 'active',
        ]);

        $sharesAccount = AccountsModel::create([
            'client_number' => '10003',
            'account_number' => 'SHR001',
            'account_type' => 'shares',
            'balance' => 3000000,
            'status' => 'active',
        ]);

        // Test mapping function
        $mapAccountTypeToCollateralType = function($accountType) {
            switch (strtolower($accountType ?? '')) {
                case 'savings':
                case 'savings_account':
                    return 'savings';
                case 'deposits':
                case 'fixed_deposit':
                case 'term_deposit':
                    return 'deposits';
                case 'shares':
                case 'share_account':
                    return 'shares';
                default:
                    return 'savings';
            }
        };

        $this->assertEquals('savings', $mapAccountTypeToCollateralType('savings'));
        $this->assertEquals('savings', $mapAccountTypeToCollateralType('savings_account'));
        $this->assertEquals('deposits', $mapAccountTypeToCollateralType('deposits'));
        $this->assertEquals('deposits', $mapAccountTypeToCollateralType('fixed_deposit'));
        $this->assertEquals('shares', $mapAccountTypeToCollateralType('shares'));
        $this->assertEquals('shares', $mapAccountTypeToCollateralType('share_account'));
        $this->assertEquals('savings', $mapAccountTypeToCollateralType('unknown'));
    }
}
