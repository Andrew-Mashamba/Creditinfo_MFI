<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsurancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('insurances')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Life Insurance Plan',
                'category' => 'LIFE',
                'coverage_amount' => 1000,
                'monthly_rate' => 5.5,
                'account_number' => 10,
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Health Insurance Plan',
                'category' => 'HEALTH',
                'coverage_amount' => 2000,
                'monthly_rate' => 11,
                'account_number' => 20,
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('insurances')->insert($row);
    }
}
}