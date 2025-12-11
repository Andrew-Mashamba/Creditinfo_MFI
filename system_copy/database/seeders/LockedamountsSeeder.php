<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LockedamountsSeeder extends Seeder
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
        DB::table('locked_amounts')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'account_id' => 262,
                'amount' => 4000000.00,
                'service_type' => 'loan_collateral',
                'service_id' => 1,
                'reason' => 'loan_guarantee',
                'status' => 'ONPROGRESS',
                'description' => 'Loan collateral for guarantor ID: 1',
                'locked_at' => '2025-07-18 06:56:42',
                'released_at' => null,
                'expires_at' => null,
                'locked_by' => 1,
                'released_by' => null,
            ],
        ];

        foreach ($data as $row) {
            // Check if the account exists first
            $accountExists = DB::table('accounts')->where('id', $row['account_id'])->exists();
            if (!$accountExists) {
                // Skip this locked amount if account doesn't exist
                if ($this->command) $this->command->warn("Skipping locked amount with account_id {$row['account_id']} - account doesn't exist");
                continue;
            }
            
            DB::table('locked_amounts')->insert($row);
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