<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditlogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('audit_logs')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'action' => 'Sample action 1',
                'details' => json_encode([]),
                'created_at' => '2025-07-23 10:38:32',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'action' => 'Sample action 2',
                'details' => json_encode([]),
                'created_at' => '2025-07-23 11:38:32',
                'updated_at' => now(),
            ],
        ];

                foreach ($data as $row) {
            // Validate user_id (required field)
            if (isset($row['user_id'])) {
                $userExists = DB::table('users')->where('id', $row['user_id'])->exists();
                if (!$userExists) {
                    $firstUser = DB::table('users')->first();
                    if (!$firstUser) {
                        // Skip this record if no users exist
                        if ($this->command) $this->command->warn("Skipping audit_log - no users found");
                        continue;
                    }
                    $row['user_id'] = $firstUser->id;
                }
            }
            
            DB::table('audit_logs')->insert($row);
        }
}
}