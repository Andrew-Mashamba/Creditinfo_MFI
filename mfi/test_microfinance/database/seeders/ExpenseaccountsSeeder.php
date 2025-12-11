<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseaccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('expense_accounts')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'major_category_code' => 'EXP001',
                'category_code' => 'EXP001',
                'category_name' => 'Sample category_name',
                'created_at' => '2025-07-23 10:38:35',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'major_category_code' => 'EXP002',
                'category_code' => 'EXP002',
                'category_name' => 'Sample category_name',
                'created_at' => '2025-07-23 11:38:35',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('expense_accounts')->insert($row);
    }
}
}