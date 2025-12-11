<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetaccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('budget_accounts')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'account_id' => 1,
                'amount' => 1000,
                'year' => 2024,
                'branch' => 'Sample branch 1',
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'account_id' => 2,
                'amount' => 2000,
                'year' => 2024,
                'branch' => 'Sample branch 2',
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('budget_accounts')->insert($row);
    }
}
}