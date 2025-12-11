<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubaccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('sub_accounts')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'institution_number' => 000001,
                'branch_number' => 000001,
                'client_number' => '000001',
                'account_use' => 10,
                'product_number' => 000001,
                'sub_product_number' => 000001,
                'major_category_code' => 'SUB001',
                'category_code' => 'SUB001',
                'sub_category_code' => 'SUB001',
                'account_name' => 'Sample account_name',
                'account_number' => 10,
                'balance' => 1000,
                'notes' => 'Sample notes 1',
                'status' => 10,
                'mirror_account' => 10,
                'created_at' => '2025-07-23 10:38:42',
                'employee_id' => 1,
                'updated_at' => now(),
                'phone_number' => +255700000001,
                'locked_amount' => 1000,
                'suspense_account' => 10,
                'bank_id' => 1,
                'account_level' => 10,
                'parent_account_number' => 10,
                'type' => 'standard',
                'debit' => 0,
                'credit' => 0,
            ],
            [
                'id' => 2,
                'institution_number' => 000002,
                'branch_number' => 000002,
                'client_number' => '000002',
                'account_use' => 20,
                'product_number' => 000002,
                'sub_product_number' => 000002,
                'major_category_code' => 'SUB002',
                'category_code' => 'SUB002',
                'sub_category_code' => 'SUB002',
                'account_name' => 'Sample account_name',
                'account_number' => 20,
                'balance' => 2000,
                'notes' => 'Sample notes 2',
                'status' => 20,
                'mirror_account' => 20,
                'created_at' => '2025-07-23 11:38:42',
                'employee_id' => 2,
                'updated_at' => now(),
                'phone_number' => +255700000002,
                'locked_amount' => 2000,
                'suspense_account' => 20,
                'bank_id' => 2,
                'account_level' => 20,
                'parent_account_number' => 20,
                'type' => 'standard',
                'debit' => 0,
                'credit' => 0,
            ],
        ];

        foreach ($data as $row) {
            DB::table('sub_accounts')->insert($row);
    }
}
}