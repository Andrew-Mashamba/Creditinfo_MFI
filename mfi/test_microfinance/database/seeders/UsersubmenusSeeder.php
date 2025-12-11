<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersubmenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('user_sub_menus')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'menu_id' => 1,
                'sub_menu_id' => 1,
                'permission' => 'Sample permission 1',
                'updated' => 1,
                'status' => 'ACTIVE',
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'menu_id' => 2,
                'sub_menu_id' => 2,
                'permission' => 'Sample permission 2',
                'updated' => 1,
                'status' => 'INACTIVE',
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('user_sub_menus')->insert($row);
        }
    }
}