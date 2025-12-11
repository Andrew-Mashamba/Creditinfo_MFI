<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoricalbalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('historical_balances')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'institution_number' => 000001,
                'branch_number' => 000001,
                'major_category_code' => 'HIS001',
                'category_code' => 'HIS001',
                'sub_category_code' => 'HIS001',
                'balance' => 1000,
                'balance_date' => '2025-01-01',
                'account_type' => 10,
                'notes' => 'Sample notes 1',
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'institution_number' => 000002,
                'branch_number' => 000002,
                'major_category_code' => 'HIS002',
                'category_code' => 'HIS002',
                'sub_category_code' => 'HIS002',
                'balance' => 2000,
                'balance_date' => '2025-02-01',
                'account_type' => 20,
                'notes' => 'Sample notes 2',
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('historical_balances')->insert($row);
    }
}
}