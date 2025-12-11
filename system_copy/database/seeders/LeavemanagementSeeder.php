<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeavemanagementSeeder extends Seeder
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
        DB::table('leave_management')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'balance' => 1000,
                'total_days' => 21,
                'days_acquire' => 15,
                'leave_days_taken' => 6,
                'employee_number' => 000001,
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'balance' => 2000,
                'total_days' => 30,
                'days_acquire' => 20,
                'leave_days_taken' => 10,
                'employee_number' => 000002,
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('leave_management')->updateOrInsert(
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