<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccounthistoricalbalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('account_historical_balances')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'year' => 2024,
                'account_number' => '010130000000',
                'account_name' => 'Share Capital',
                'major_category_code' => '3000',
                'account_level' => 1,
                'type' => 'CREDIT',
                'balance' => 10000000.00,
                'credit' => 10000000.00,
                'debit' => 0.00,
                'snapshot_date' => '2024-12-31',
                'captured_by' => 'system',
                'notes' => 'Year-end balance for 2024',
                'created_at' => '2025-07-23 10:38:32',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'year' => 2024,
                'account_number' => '010110000000',
                'account_name' => 'Members Savings',
                'major_category_code' => '2000',
                'account_level' => 1,
                'type' => 'CREDIT',
                'balance' => 50000000.00,
                'credit' => 50000000.00,
                'debit' => 0.00,
                'snapshot_date' => '2024-12-31',
                'captured_by' => 'system',
                'notes' => 'Year-end balance for 2024',
                'created_at' => '2025-07-23 10:38:32',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('account_historical_balances')->insert($row);
    }
}
}