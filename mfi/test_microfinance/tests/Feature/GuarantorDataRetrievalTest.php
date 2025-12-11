<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class GuarantorDataRetrievalTest extends TestCase
{
    use RefreshDatabase;

    protected $client;

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
    }

    /** @test */
    public function it_can_identify_missing_guarantor_data_for_specific_loan()
    {
        // Create loan with ID 39 (matching the debug output)
        $loan = LoansModel::create([
            'id' => 39,
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_amount' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Create guarantor data for other loans (like in the debug output)
        $otherLoans = [34, 35, 38, 36, 37, 33];
        
        foreach ($otherLoans as $loanId) {
            // Create loan if it doesn't exist
            LoansModel::create([
                'id' => $loanId,
                'loan_id' => "LN20250817{$loanId}",
                'client_number' => '10003',
                'principle' => 50000000,
                'loan_type_2' => 'New',
                'status' => 'active',
            ]);

            // Create guarantor for this loan
            DB::table('loan_guarantors')->insert([
                'loan_id' => $loanId,
                'guarantor_member_id' => $this->client->id,
                'guarantor_type' => 'self_guarantee',
                'total_guaranteed_amount' => 50000000,
                'available_amount' => 50000000,
                'status' => 'active',
                'guarantee_start_date' => now(),
                'notes' => "Guarantor for loan {$loanId}",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Verify that loan 39 has no guarantor data
        $guarantorsForLoan39 = DB::table('loan_guarantors')->where('loan_id', 39)->get();
        $this->assertEquals(0, $guarantorsForLoan39->count());

        // Verify that other loans have guarantor data
        foreach ($otherLoans as $loanId) {
            $guarantors = DB::table('loan_guarantors')->where('loan_id', $loanId)->get();
            $this->assertEquals(1, $guarantors->count(), "Loan {$loanId} should have 1 guarantor");
        }

        // Get all guarantor loan IDs (should match debug output)
        $allGuarantorLoanIds = DB::table('loan_guarantors')
            ->select('loan_id')
            ->distinct()
            ->get()
            ->pluck('loan_id')
            ->toArray();

        $this->assertEquals(6, count($allGuarantorLoanIds));
        $this->assertNotContains(39, $allGuarantorLoanIds);
        $this->assertContains(34, $allGuarantorLoanIds);
        $this->assertContains(35, $allGuarantorLoanIds);
        $this->assertContains(38, $allGuarantorLoanIds);
        $this->assertContains(36, $allGuarantorLoanIds);
        $this->assertContains(37, $allGuarantorLoanIds);
        $this->assertContains(33, $allGuarantorLoanIds);
    }

    /** @test */
    public function it_can_verify_loan_id_data_type_consistency()
    {
        // Create loan
        $loan = LoansModel::create([
            'id' => 39,
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Test that loan_id in guarantors table expects bigint
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // This should fail because we're trying to insert a string into a bigint column
        DB::table('loan_guarantors')->insert([
            'loan_id' => 'LN202508171897', // String - should fail
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
    }

    /** @test */
    public function it_can_verify_correct_loan_id_usage()
    {
        // Create loan
        $loan = LoansModel::create([
            'id' => 39,
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // This should work because we're using the numeric ID
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $loan->id, // Numeric ID - should work
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

        // Verify the guarantor was created
        $this->assertDatabaseHas('loan_guarantors', [
            'loan_id' => $loan->id,
            'guarantor_member_id' => $this->client->id,
        ]);

        // Verify we can query it correctly
        $guarantor = DB::table('loan_guarantors')->where('loan_id', $loan->id)->first();
        $this->assertNotNull($guarantor);
        $this->assertEquals($loan->id, $guarantor->loan_id);
    }

    /** @test */
    public function it_can_simulate_the_exact_debug_scenario()
    {
        // Simulate the exact scenario from the debug output
        // Loan ID 39 exists but has no guarantor data
        $loan = LoansModel::create([
            'id' => 39,
            'loan_id' => 'LN202508171897',
            'client_number' => '10003',
            'principle' => 70000000,
            'loan_type_2' => 'Top-up',
            'status' => 'active',
        ]);

        // Create guarantor data for other loans (like in the debug output)
        $otherLoans = [34, 35, 38, 36, 37, 33];
        
        foreach ($otherLoans as $loanId) {
            LoansModel::create([
                'id' => $loanId,
                'loan_id' => "LN20250817{$loanId}",
                'client_number' => '10003',
                'principle' => 50000000,
                'loan_type_2' => 'New',
                'status' => 'active',
            ]);

            DB::table('loan_guarantors')->insert([
                'loan_id' => $loanId,
                'guarantor_member_id' => $this->client->id,
                'guarantor_type' => 'self_guarantee',
                'total_guaranteed_amount' => 50000000,
                'available_amount' => 50000000,
                'status' => 'active',
                'guarantee_start_date' => now(),
                'notes' => "Guarantor for loan {$loanId}",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Simulate the exact queries from the debug output
        $loanIdToQuery = 39;
        
        // Query guarantor data (should return 0 records)
        $guarantors = DB::table('loan_guarantors')->where('loan_id', $loanIdToQuery)->get();
        $this->assertEquals(0, $guarantors->count());

        // Get guarantor IDs (should be empty array)
        $guarantorIds = DB::table('loan_guarantors')
            ->where('loan_id', $loanIdToQuery)
            ->pluck('id');
        
        $this->assertEquals(0, $guarantorIds->count());

        // Query collateral data (should return 0 records)
        $collaterals = DB::table('loan_collaterals')
            ->whereIn('loan_guarantor_id', $guarantorIds)
            ->get();
        
        $this->assertEquals(0, $collaterals->count());

        // Verify total counts match debug output
        $totalGuarantors = DB::table('loan_guarantors')->count();
        $totalCollaterals = DB::table('loan_collaterals')->count();
        
        $this->assertEquals(6, $totalGuarantors); // 6 other loans have guarantors
        $this->assertEquals(0, $totalCollaterals); // No collaterals created in this test

        // Get all guarantor loan IDs
        $allGuarantorLoanIds = DB::table('loan_guarantors')
            ->select('loan_id')
            ->distinct()
            ->get()
            ->pluck('loan_id')
            ->toArray();

        $this->assertEquals(6, count($allGuarantorLoanIds));
        $this->assertNotContains(39, $allGuarantorLoanIds);
    }

    /** @test */
    public function it_can_verify_table_structure_matches_debug_output()
    {
        // Verify that the table structure matches what we see in the debug output
        $guarantorColumns = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'loan_guarantors' ORDER BY ordinal_position");
        $collateralColumns = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'loan_collaterals' ORDER BY ordinal_position");

        // Check key columns exist
        $guarantorColumnNames = collect($guarantorColumns)->pluck('column_name')->toArray();
        $collateralColumnNames = collect($collateralColumns)->pluck('column_name')->toArray();

        $this->assertContains('loan_id', $guarantorColumnNames);
        $this->assertContains('guarantor_member_id', $guarantorColumnNames);
        $this->assertContains('guarantor_type', $guarantorColumnNames);
        $this->assertContains('total_guaranteed_amount', $guarantorColumnNames);

        $this->assertContains('loan_guarantor_id', $collateralColumnNames);
        $this->assertContains('collateral_type', $collateralColumnNames);
        $this->assertContains('account_id', $collateralColumnNames);
        $this->assertContains('collateral_amount', $collateralColumnNames);

        // Check data types
        $loanIdColumn = collect($guarantorColumns)->firstWhere('column_name', 'loan_id');
        $this->assertEquals('bigint', $loanIdColumn->data_type);
        $this->assertEquals('NO', $loanIdColumn->is_nullable);
    }
}
