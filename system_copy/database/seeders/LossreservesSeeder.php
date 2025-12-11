<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LossreservesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loss_reserves')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'year' => 2024,
                'percentage' => 5.5,
                'reserve_amount' => 1000,
                'status' => 'pending',
                'initial_allocation' => 'Sample initial_allocation 1',
                'profitsx' => 'Sample profitsx 1',
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'year' => 2024,
                'percentage' => 11,
                'reserve_amount' => 2000,
                'status' => 'inactive',
                'initial_allocation' => 'Sample initial_allocation 2',
                'profitsx' => 'Sample profitsx 2',
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('loss_reserves')->insert($row);
    }
}
}