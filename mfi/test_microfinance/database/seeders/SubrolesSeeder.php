<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubrolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('sub_roles')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'name' => 'System Administrator',
                'role_id' => 1,
                'description' => 'Full system access with all permissions',
                'created_at' => '2025-07-17 16:25:44',
                'updated_at' => '2025-07-17 16:25:44',
            ],
            [
                'id' => 2,
                'name' => 'Institution Administrator',
                'role_id' => 2,
                'description' => 'Institution level administration with full access',
                'created_at' => '2025-07-17 16:25:44',
                'updated_at' => '2025-07-17 16:25:44',
            ],
        ];

        foreach ($data as $row) {
            DB::table('sub_roles')->insert($row);
    }
}
}