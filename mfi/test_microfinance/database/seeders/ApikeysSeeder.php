<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApikeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('api_keys')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'key' => 'Sample key 1',
                'client_name' => 'Sample client_name',
                'description' => 'This is a sample description for api_keys record 1',
                'is_active' => false,
                'rate_limit' => 100,
                'allowed_ips' => json_encode(['127.0.0.1', '::1']),
                'permissions' => json_encode(['read', 'write', 'delete']),
                'expires_at' => now(),
                'last_used_at' => now(),
                'created_by' => 1,
                'created_at' => '2025-07-23 10:38:32',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'key' => 'Sample key 2',
                'client_name' => 'Sample client_name',
                'description' => 'This is a sample description for api_keys record 2',
                'is_active' => true,
                'rate_limit' => 11,
                'allowed_ips' => json_encode(['127.0.0.1', '::1']),
                'permissions' => json_encode(['read', 'write', 'delete']),
                'expires_at' => now(),
                'last_used_at' => now(),
                'created_by' => 1,
                'created_at' => '2025-07-23 11:38:32',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Validate created_by user reference
            if (isset($row['created_by'])) {
                $userExists = DB::table('users')->where('id', $row['created_by'])->exists();
                if (!$userExists) {
                    $firstUser = DB::table('users')->first();
                    if (!$firstUser) {
                        // Skip this record if no users exist
                        if ($this->command) $this->command->warn("Skipping api_key - no users found");
                        continue;
                    }
                    $row['created_by'] = $firstUser->id;
                }
            }
            
            DB::table('api_keys')->insert($row);
    }
}
}