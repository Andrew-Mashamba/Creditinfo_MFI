<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsSeeder extends Seeder
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
        DB::table('notifications')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'member_id' => 1,
                'type' => 'standard',
                'title' => 'Sample title 1',
                'message' => 'Sample message 1',
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => now(),
                'action_url' => 'Sample action_url 1',
                'action_text' => 'This is a sample text field for notifications record 1. It contains some sample content to demonstrate the seeder functionality.',
                'read_at' => null,
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'type' => 'standard',
                'title' => 'Sample title 2',
                'message' => 'Sample message 2',
                'status' => 'read',
                'created_at' => '2025-07-23 11:38:39',
                'updated_at' => now(),
                'action_url' => 'Sample action_url 2',
                'action_text' => 'This is a sample text field for notifications record 2. It contains some sample content to demonstrate the seeder functionality.',
                'read_at' => '2025-07-23 11:30:00',
            ],
        ];

        foreach ($data as $row) {
            DB::table('notifications')->updateOrInsert(
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