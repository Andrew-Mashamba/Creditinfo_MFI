<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeposittypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('deposit_types')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'type' => 'Fixed Deposit',
                'summary' => 'Time-locked deposits with higher interest rates',
                'status' => true,
                'created_at' => '2025-07-18 04:31:18',
                'updated_at' => '2025-07-18 04:31:18',
            ],
            [
                'id' => 2,
                'type' => 'Recurring Deposit',
                'summary' => 'Regular deposits with fixed intervals',
                'status' => true,
                'created_at' => '2025-07-18 04:31:18',
                'updated_at' => '2025-07-18 04:31:18',
            ],
            [
                'id' => 3,
                'type' => 'Term Deposit',
                'summary' => 'Deposits with specific maturity periods',
                'status' => true,
                'created_at' => '2025-07-18 04:31:18',
                'updated_at' => '2025-07-18 04:31:18',
            ],
            [
                'id' => 4,
                'type' => 'Call Deposit',
                'summary' => 'Flexible deposits with immediate access',
                'status' => true,
                'created_at' => '2025-07-18 04:31:18',
                'updated_at' => '2025-07-18 04:31:18',
            ],
            [
                'id' => 5,
                'type' => 'Special Deposit',
                'summary' => 'Custom deposit products for specific needs',
                'status' => true,
                'created_at' => '2025-07-18 04:31:18',
                'updated_at' => '2025-07-18 04:31:18',
            ],
        ];

        foreach ($data as $row) {
            DB::table('deposit_types')->insert($row);
    }
}
}