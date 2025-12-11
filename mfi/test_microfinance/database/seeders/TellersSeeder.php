<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TellersSeeder extends Seeder
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
        DB::table('tellers')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'employee_id' => 1,
                'status' => 'ACTIVE',
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
                'branch_id' => 1,
                'max_amount' => 1000,
                'account_id' => 1,
                'registered_by_id' => 1,
                'progress_status' => 'PENDING',
                'teller_name' => 'Sample teller_name',
                'user_id' => 1,
                'till_id' => 1,
                'transaction_limit' => 100000.00,
                'permissions' => json_encode(['read', 'write', 'delete']),
                'last_login_at' => now(),
                'assigned_at' => now(),
                'assigned_by' => 1,
            ],
            [
                'id' => 2,
                'employee_id' => 2,
                'status' => 'INACTIVE',
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
                'branch_id' => 2,
                'max_amount' => 2000,
                'account_id' => 2,
                'registered_by_id' => 2,
                'progress_status' => 'INACTIVE',
                'teller_name' => 'Sample teller_name',
                'user_id' => 2,
                'till_id' => 2,
                'transaction_limit' => 100000.00,
                'permissions' => json_encode(['read', 'write', 'delete']),
                'last_login_at' => now(),
                'assigned_at' => now(),
                'assigned_by' => 1,
            ],
        ];

        foreach ($data as $row) {
            DB::table('tellers')->insert($row);
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