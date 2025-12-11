<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainbudgetpendingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('main_budget_pending')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'sub_category_code' => 'MAI001',
                'sub_category_name' => 'Sample sub_category_name',
                'year' => 2024,
                'budget_id' => 1,
                'stage' => 'Sample stage 1',
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sub_category_code' => 'MAI002',
                'sub_category_name' => 'Sample sub_category_name',
                'year' => 2024,
                'budget_id' => 2,
                'stage' => 'Sample stage 2',
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('main_budget_pending')->insert($row);
    }
}
}