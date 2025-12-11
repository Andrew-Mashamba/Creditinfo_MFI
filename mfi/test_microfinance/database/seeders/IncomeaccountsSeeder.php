<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncomeaccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('income_accounts')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'major_category_code' => 'INC001',
                'category_code' => 'INC001',
                'category_name' => 'Sample category_name',
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'major_category_code' => 'INC002',
                'category_code' => 'INC002',
                'category_name' => 'Sample category_name',
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('income_accounts')->insert($row);
    }
}
}