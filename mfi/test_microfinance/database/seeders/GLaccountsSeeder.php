<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GLaccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('GL_accounts')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'account_code' => 4000,
                'account_name' => 'Revenue Account',
                'created_at' => '2025-07-18 02:59:44',
                'updated_at' => '2025-07-18 02:59:44',
            ],
            [
                'id' => 2,
                'account_code' => 5000,
                'account_name' => 'Expense Accounts',
                'created_at' => '2025-07-18 02:59:44',
                'updated_at' => '2025-07-18 02:59:44',
            ],
            [
                'id' => 3,
                'account_code' => 1000,
                'account_name' => 'Asset Account',
                'created_at' => '2025-07-18 02:59:44',
                'updated_at' => '2025-07-18 02:59:44',
            ],
            [
                'id' => 4,
                'account_code' => 2000,
                'account_name' => 'Liability Accounts',
                'created_at' => '2025-07-18 02:59:44',
                'updated_at' => '2025-07-18 02:59:44',
            ],
            [
                'id' => 5,
                'account_code' => 3000,
                'account_name' => 'Equity Accounts',
                'created_at' => '2025-07-18 02:59:44',
                'updated_at' => '2025-07-18 02:59:44',
            ],
        ];

        foreach ($data as $row) {
            DB::table('GL_accounts')->insert($row);
    }
}
}