<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TillsSeeder extends Seeder
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
        DB::table('tills')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Sample name',
                'till_number' => 000001,
                'branch_id' => 1,
                'current_balance' => 1000,
                'opening_balance' => 1000,
                'maximum_limit' => 100000.00,
                'minimum_limit' => 100000.00,
                'status' => 'open',
                'opened_at' => now(),
                'closed_at' => null,
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 1']),
                'requires_supervisor_approval' => true,
                'description' => 'This is a sample description for tills record 1',
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
                'till_account_number' => 10,
                'assigned_user_id' => 1,
                'assigned_at' => now(),
                'assignment_notes' => 'Sample assignment_notes 1',
                'code' => 'TIL001',
                'variance' => 0,
                'variance_explanation' => 'Sample variance_explanation 1',
                'closing_balance' => 1000,
            ],
            [
                'id' => 2,
                'name' => 'Sample name',
                'till_number' => 000002,
                'branch_id' => 2,
                'current_balance' => 2000,
                'opening_balance' => 2000,
                'maximum_limit' => 100000.00,
                'minimum_limit' => 100000.00,
                'status' => 'closed',
                'opened_at' => now(),
                'closed_at' => null,
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 2']),
                'requires_supervisor_approval' => true,
                'description' => 'This is a sample description for tills record 2',
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
                'till_account_number' => 20,
                'assigned_user_id' => 2,
                'assigned_at' => now(),
                'assignment_notes' => 'Sample assignment_notes 2',
                'code' => 'TIL002',
                'variance' => 0,
                'variance_explanation' => 'Sample variance_explanation 2',
                'closing_balance' => 2000,
            ],
        ];

        foreach ($data as $row) {
            DB::table('tills')->insert($row);
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