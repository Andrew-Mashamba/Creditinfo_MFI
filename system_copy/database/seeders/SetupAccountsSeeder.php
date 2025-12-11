<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetupAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('setup_accounts')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'sub_category_code' => 'SET001',
                'account_number' => '1001',
                'account_name' => 'Cash in Hand',
                'table_name' => 'cash_accounts',
                'item' => 'CASH',
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => '2025-07-23 10:38:42',
            ],
            [
                'id' => 2,
                'sub_category_code' => 'SET002',
                'account_number' => '1002',
                'account_name' => 'Petty Cash',
                'table_name' => 'cash_accounts',
                'item' => 'PETTY_CASH',
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => '2025-07-23 10:38:42',
            ],
        ];

        foreach ($data as $row) {
            DB::table('setup_accounts')->insert($row);
    }
}
}