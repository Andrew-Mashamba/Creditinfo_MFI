<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolepermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('role_permissions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'role_id' => 1,
                'permission_id' => 1,
                'department_id' => 1,
                'conditions' => json_encode(['value' => 'Sample conditions 1']),
                'is_inherited' => false,
                'created_at' => '2025-07-23 10:38:41',
                'updated_at' => now(),
                'constraints' => json_encode(['rule' => 'Sample constraint rule 1']),
            ],
            [
                'id' => 2,
                'role_id' => 2,
                'permission_id' => 2,
                'department_id' => 2,
                'conditions' => json_encode(['value' => 'Sample conditions 2']),
                'is_inherited' => true,
                'created_at' => '2025-07-23 11:38:41',
                'updated_at' => now(),
                'constraints' => json_encode(['rule' => 'Sample constraint rule 2']),
            ],
        ];

        foreach ($data as $row) {
            DB::table('role_permissions')->insert($row);
        }
    }
}