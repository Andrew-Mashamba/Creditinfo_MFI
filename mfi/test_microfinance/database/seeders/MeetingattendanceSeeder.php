<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetingattendanceSeeder extends Seeder
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
        DB::table('meeting_attendance')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'meeting_id' => 1,
                'leader_id' => 1,
                'status' => 'present',
                'notes' => 'Sample notes 1',
                // 'created_at' => '2025-07-23 10:38:39',
                // 'updated_at' => now(),
                'stipend_paid' => true,
                'stipend_amount' => 1000,
            ],
            [
                'id' => 2,
                'meeting_id' => 2,
                'leader_id' => 2,
                'status' => 'absent',
                'notes' => 'Sample notes 2',
                // 'created_at' => '2025-07-23 11:38:39',
                // 'updated_at' => now(),
                'stipend_paid' => false,
                'stipend_amount' => 2000,
            ],
        ];

        foreach ($data as $row) {
            DB::table('meeting_attendance')->updateOrInsert(
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