<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserpermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('user_permissions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'permission_id' => 1,
                'conditions' => json_encode(['value' => 'Sample conditions 1']),
                'is_granted' => false,
                'granted_at' => '2025-07-23 10:38:44',
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'permission_id' => 2,
                'conditions' => json_encode(['value' => 'Sample conditions 2']),
                'is_granted' => true,
                'granted_at' => '2025-07-23 11:38:44',
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('user_permissions')->insert($row);
        }
    }
}