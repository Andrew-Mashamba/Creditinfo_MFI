<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersecurityprofilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('user_security_profiles')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'two_factor_enabled' => true,
                'two_factor_secret' => 'Sample two_factor_secret 1',
                'last_password_change' => now(),
                'account_locked_until' => null,
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'two_factor_enabled' => true,
                'two_factor_secret' => 'Sample two_factor_secret 2',
                'last_password_change' => now(),
                'account_locked_until' => null,
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('user_security_profiles')->insert($row);
        }
    }
}