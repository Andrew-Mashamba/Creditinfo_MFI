<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('permissions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'view_dashboard',
                'slug' => 'view-dashboard',
                'description' => 'This is a sample description for permissions record 1',
                'module' => 'Sample module 1',
                'action' => 'Sample action 1',
                'resource_type' => 'TYPE_B',
                'resource_id' => 1,
                'conditions' => json_encode(['value' => 'Sample conditions 1']),
                'is_system' => false,
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
                'guard_name' => 'Sample guard_name',
            ],
            [
                'id' => 2,
                'name' => 'edit_users',
                'slug' => 'edit-users',
                'description' => 'This is a sample description for permissions record 2',
                'module' => 'Sample module 2',
                'action' => 'Sample action 2',
                'resource_type' => 'TYPE_C',
                'resource_id' => 2,
                'conditions' => json_encode(['value' => 'Sample conditions 2']),
                'is_system' => true,
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
                'guard_name' => 'Sample guard_name',
            ],
        ];

        foreach ($data as $row) {
            DB::table('permissions')->insert($row);
    }
}
}