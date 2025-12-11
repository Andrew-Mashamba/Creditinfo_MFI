<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankaccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('bank_accounts')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'bank_name' => 'NBC Bank',
                'account_name' => 'Cash In NBC Bank',
                'account_number' => '111099876654',
                'branch_name' => '',
                'swift_code' => 'ostrd',
                'currency' => 'TZS',
                'opening_balance' => 100000000.00,
                'current_balance' => 100000000.00,
                'internal_mirror_account_number' => '0101100010001010',
                'status' => 'ACTIVE',
                'description' => 'SDRGDF',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => '2025-07-18 06:53:32',
                'updated_at' => '2025-07-18 06:53:32',
                'deleted_at' => null,
                'branch_id' => null
            ],
        ];

        foreach ($data as $row) {
            DB::table('bank_accounts')->insert($row);
    }
}
}