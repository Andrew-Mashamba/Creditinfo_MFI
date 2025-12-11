<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaultsSeeder extends Seeder
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
        DB::table('vaults')->truncate();
        
        // Get the first branch ID
        $branch = DB::table('branches')->first();
        if (!$branch) {
            if ($this->command) $this->command->warn('No branches found. Skipping VaultsSeeder.');
            return;
        }

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Main Branch Vault',
                'code' => 'VAU001',
                'branch_id' => $branch->id,
                'current_balance' => 5000000.00,
                'limit' => 10000000.00,
                'warning_threshold' => 1000000.00,
                'bank_name' => 'National Bank',
                'bank_account_number' => '1234567890',
                'internal_account_number' => '010101001',
                'auto_bank_transfer' => false,
                'requires_dual_approval' => true,
                'send_alerts' => true,
                'status' => 'active',
                'description' => 'Main branch vault for cash management',
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => '2025-07-23 10:38:44',
                'parent_account' => '010101001',
            ],
            [
                'id' => 2,
                'name' => 'Secondary Branch Vault',
                'code' => 'VAU002',
                'branch_id' => $branch->id,
                'current_balance' => 2000000.00,
                'limit' => 5000000.00,
                'warning_threshold' => 500000.00,
                'bank_name' => 'Commercial Bank',
                'bank_account_number' => '0987654321',
                'internal_account_number' => '010101002',
                'auto_bank_transfer' => false,
                'requires_dual_approval' => true,
                'send_alerts' => true,
                'status' => 'active',
                'description' => 'Secondary vault for overflow cash',
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => '2025-07-23 10:38:44',
                'parent_account' => '010101002',
            ],
        ];

        foreach ($data as $row) {
            DB::table('vaults')->insert($row);
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