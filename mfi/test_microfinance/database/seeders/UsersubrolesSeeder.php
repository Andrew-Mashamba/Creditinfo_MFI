<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersubrolesSeeder extends Seeder
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
            DB::table('user_sub_roles')->truncate();

            // Insert sample data (table was empty)
            $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'sub_role_id' => 1,
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'sub_role_id' => 2,
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
            ],
        ];

            foreach ($data as $row) {
                DB::table('user_sub_roles')->insert($row);
            }
        } finally {
            // Re-enable foreign key checks
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif (DB::getDriverName() === 'pgsql') {
                DB::statement('SET session_replication_role = origin;');
            }
        }
    }
}