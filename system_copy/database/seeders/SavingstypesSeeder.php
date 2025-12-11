<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavingstypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('savings_types')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'type' => 'Regular Savings',
                'summary' => 'Basic savings account with standard interest rates',
                'status' => true,
                'created_at' => '2025-07-18 04:31:16',
                'updated_at' => '2025-07-18 04:31:16',
            ],
            [
                'id' => 2,
                'type' => 'Fixed Deposit',
                'summary' => 'Time-locked savings with higher interest rates',
                'status' => true,
                'created_at' => '2025-07-18 04:31:16',
                'updated_at' => '2025-07-18 04:31:16',
            ],
            [
                'id' => 3,
                'type' => 'Goal Savings',
                'summary' => 'Savings account for specific financial goals',
                'status' => true,
                'created_at' => '2025-07-18 04:31:16',
                'updated_at' => '2025-07-18 04:31:16',
            ],
            [
                'id' => 4,
                'type' => 'Youth Savings',
                'summary' => 'Savings account for young members',
                'status' => true,
                'created_at' => '2025-07-18 04:31:16',
                'updated_at' => '2025-07-18 04:31:16',
            ],
            [
                'id' => 5,
                'type' => 'Senior Savings',
                'summary' => 'Savings account with benefits for senior members',
                'status' => true,
                'created_at' => '2025-07-18 04:31:16',
                'updated_at' => '2025-07-18 04:31:16',
            ],
        ];

        foreach ($data as $row) {
            DB::table('savings_types')->insert($row);
    }
}
}