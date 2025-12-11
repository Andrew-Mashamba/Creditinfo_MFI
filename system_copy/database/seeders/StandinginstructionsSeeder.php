<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StandinginstructionsSeeder extends Seeder
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
        DB::table('standing_instructions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'member_id' => 1,
                'source_account_id' => 1,
                'source_account_number' => '100001',
                'destination_type' => 'member',
                'destination_account_id' => 2,
                'destination_account_number' => '100002',
                'destination_account_name' => 'Sample destination account',
                'amount' => 1000.00,
                'description' => 'Monthly savings transfer',
                'reference_number' => 'SI000001',
                'frequency' => 'monthly',
                'start_date' => '2025-07-24',
                'end_date' => '2025-12-24',
                'day_of_month' => 1,
                'next_execution_date' => '2025-07-24',
                'status' => 'ACTIVE',
                'created_by' => 1,
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'source_account_id' => 2,
                'source_account_number' => '100002',
                'destination_type' => 'internal',
                'destination_account_id' => 3,
                'destination_account_number' => '100003',
                'destination_account_name' => 'Internal account',
                'amount' => 2000.00,
                'description' => 'Weekly loan payment',
                'reference_number' => 'SI000002',
                'frequency' => 'weekly',
                'start_date' => '2025-07-25',
                'end_date' => '2025-12-25',
                'day_of_week' => 1,
                'next_execution_date' => '2025-07-25',
                'status' => 'PENDING',
                'created_by' => 1,
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('standing_instructions')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
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