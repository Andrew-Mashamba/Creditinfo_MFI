<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubrolepermissionsSeeder extends Seeder
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
            DB::table('sub_role_permissions')->truncate();

            // Insert sample data (table was empty)
            $data = [
            [
                'id' => 1,
                'sub_role_id' => 1,
                'permission_id' => 1,
                'department_id' => 1,
                'conditions' => json_encode(['value' => 'Sample conditions 1']),
                'is_inherited' => false,
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sub_role_id' => 2,
                'permission_id' => 2,
                'department_id' => 2,
                'conditions' => json_encode(['value' => 'Sample conditions 2']),
                'is_inherited' => true,
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

            foreach ($data as $row) {
                DB::table('sub_role_permissions')->insert($row);
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