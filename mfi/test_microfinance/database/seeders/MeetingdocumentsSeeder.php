<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetingdocumentsSeeder extends Seeder
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
        DB::table('meeting_documents')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'meeting_id' => 1,
                'file_name' => 'meeting_minutes_2025_01.pdf',
                'file_path' => '/documents/meetings/meeting_minutes_2025_01.pdf',
                'uploaded_by' => 1,
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'meeting_id' => 2,
                'file_name' => 'meeting_agenda_2025_02.pdf',
                'file_path' => '/documents/meetings/meeting_agenda_2025_02.pdf',
                'uploaded_by' => 2,
                'created_at' => '2025-07-23 11:38:39',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('meeting_documents')->updateOrInsert(
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