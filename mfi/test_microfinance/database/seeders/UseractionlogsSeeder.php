<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UseractionlogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('user_action_logs')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'action_type' => 'TYPE_B',
                'action_details' => 'Sample action_details 1',
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'action_type' => 'TYPE_C',
                'action_details' => 'Sample action_details 2',
                'created_at' => '2025-07-23 11:38:44',
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
                        if ($this->command) $this->command->warn("Skipping user_action_log - no users found");
                        continue;
                    }
                    $row['user_id'] = $firstUser->id;
                }
            }
            
            DB::table('user_action_logs')->insert($row);
        }
}
}