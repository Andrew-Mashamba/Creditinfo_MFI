<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleLoanCollateralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get first active loan for testing
        $loan = DB::table('loans')
            ->where('status', 'ACTIVE')
            ->first();
            
        if (!$loan) {
            if ($this->command) $this->command->info('No active loans found. Creating a sample loan first...');
            return;
        }
        
        if ($this->command) $this->command->info("Adding sample collateral for loan ID: {$loan->id}");
        
        // Get a sample client to use as guarantor
        $guarantorClient = DB::table('clients')
            ->where('id', '!=', $loan->client_id ?? 1)
            ->first();
            
        if (!$guarantorClient) {
            if ($this->command) $this->command->error('No other clients found to use as guarantor');
            return;
        }
        
        // Create sample guarantor
        $guarantorId = DB::table('loan_guarantors')->insertGetId([
            'loan_id' => $loan->id,
            'guarantor_type' => 'third_party_guarantee',
            'guarantor_member_id' => $guarantorClient->id,
            'relationship' => 'Business Partner',
            'total_guaranteed_amount' => 5000000,
            'available_amount' => 5000000,
            'status' => 'active',
            'notes' => 'Sample guarantor for testing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($this->command) $this->command->info("Created guarantor with ID: {$guarantorId}");
        
        // Create sample collateral
        DB::table('loan_collaterals')->insert([
            'loan_guarantor_id' => $guarantorId,
            'collateral_type' => 'physical',
            'account_id' => null,
            'collateral_amount' => 15000000,
            'physical_collateral_description' => 'Toyota Corolla 2018',
            'physical_collateral_value' => 15000000,
            'physical_collateral_location' => 'Dar es Salaam',
            'physical_collateral_owner_name' => $guarantorClient->first_name . ' ' . $guarantorClient->last_name,
            'physical_collateral_owner_contact' => $guarantorClient->phone_number ?? '0712345678',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($this->command) $this->command->info("Sample collateral data added successfully for loan ID: {$loan->id}");
        if ($this->command) $this->command->info("You can now test Top-up functionality with this loan.");
    }
}