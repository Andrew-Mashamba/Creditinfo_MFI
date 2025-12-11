<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('roles')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'name' => 'System Administrator',
                'department_id' => null,
                'description' => 'Full system access',
                'level' => 1,
                'is_system_role' => true,
                'created_at' => '2025-07-17 16:25:44',
                'updated_at' => '2025-07-17 16:25:44',
                'deleted_at' => null,
                'permission_inheritance_enabled' => true,
                'department_specific' => false,
                'conditions' => null,
                'parent_role_id' => null,
                'path' => null,
                'guard_name' => 'web',
            ],
            [
                'id' => 2,
                'name' => 'Institution Administrator',
                'department_id' => null,
                'description' => 'Institution level administration',
                'level' => 1,
                'is_system_role' => true,
                'created_at' => '2025-07-17 16:25:44',
                'updated_at' => '2025-07-17 16:25:44',
                'deleted_at' => null,
                'permission_inheritance_enabled' => true,
                'department_specific' => false,
                'conditions' => null,
                'parent_role_id' => null,
                'path' => null,
                'guard_name' => 'web',
            ],
        ];

        foreach ($data as $row) {
            DB::table('roles')->insert($row);
    }
}
}