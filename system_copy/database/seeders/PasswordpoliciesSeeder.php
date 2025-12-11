<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasswordpoliciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('password_policies')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'requireSpecialCharacter' => true,
                'length' => 8,
                'requireUppercase' => true,
                'requireNumeric' => true,
                'limiter' => 5,
                'passwordExpire' => 90,
                'status' => 'active',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
        ];

        foreach ($data as $row) {
            DB::table('password_policies')->insert($row);
    }
}
}