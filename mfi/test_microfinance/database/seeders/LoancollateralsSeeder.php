<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoancollateralsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('SET session_replication_role = replica;');
        }
        
        try {
        // Clear existing data
        DB::table('loan_collaterals')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'loan_guarantor_id' => 1,
                'collateral_type' => 'savings',
                'account_id' => 262,
                'collateral_amount' => 4000000.00,
                'account_balance' => null,
                'locked_amount' => 0.00,
                'available_amount' => 0.00,
                'physical_collateral_id' => null,
                'physical_collateral_description' => null,
                'physical_collateral_location' => null,
                'physical_collateral_owner_name' => null,
                'physical_collateral_owner_nida' => null,
                'physical_collateral_owner_contact' => null,
                'physical_collateral_owner_address' => null,
                'physical_collateral_value' => null,
                'physical_collateral_valuation_date' => null,
                'physical_collateral_valuation_method' => null,
                'physical_collateral_valuer_name' => null,
                'insurance_policy_number' => null,
                'insurance_company_name' => null,
                'insurance_coverage_details' => null,
                'insurance_expiration_date' => null,
                'status' => 'active',
                'collateral_start_date' => '2025-07-18 09:56:43',
                'collateral_end_date' => null,
                'notes' => null,
                'is_active' => true,
                'created_at' => '2025-07-18 06:56:42',
                'updated_at' => '2025-07-18 06:56:42',
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            // Check if the loan guarantor exists first
            $guarantorExists = DB::table('loan_guarantors')->where('id', $row['loan_guarantor_id'])->exists();
            if (!$guarantorExists) {
                // Skip this collateral if guarantor doesn't exist
                if ($this->command) {
                    $this->command->warn("Skipping loan collateral with loan_guarantor_id {$row['loan_guarantor_id']} - guarantor doesn't exist");
                }
                continue;
            }
            
            // Check if the account exists (if account_id is provided)
            if ($row['account_id'] !== null) {
                $accountExists = DB::table('accounts')->where('id', $row['account_id'])->exists();
                if (!$accountExists) {
                    // Skip this collateral if account doesn't exist
                    if ($this->command) {
                        $this->command->warn("Skipping loan collateral with account_id {$row['account_id']} - account doesn't exist");
                    }
                    continue;
                }
            }
            
            DB::table('loan_collaterals')->insert($row);
        }
    
        
        
        } finally {
            // Re-enable foreign key checks
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif (DB::getDriverName() === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT;');
                }
    }
}
}