<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterviewsSeeder extends Seeder
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
        // DB::table('interviews')->truncate(); // Commented out to avoid foreign key issues

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'applicant_id' => 1,
                'interviewer_id' => 1,
                'interview_date' => '2025-07-24',
                'interview_time' => '2025-07-23 10:38:36',
                'interview_type' => 'TYPE_B',
                'notes' => 'Sample notes 1',
                'feedback' => 'Sample feedback 1',
                'status' => 'scheduled',
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'applicant_id' => 2,
                'interviewer_id' => 2,
                'interview_date' => '2025-07-25',
                'interview_time' => '2025-07-23 11:38:36',
                'interview_type' => 'TYPE_C',
                'notes' => 'Sample notes 2',
                'feedback' => 'Sample feedback 2',
                'status' => 'completed',
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Use updateOrInsert to avoid foreign key conflicts
            DB::table('interviews')->updateOrInsert(
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