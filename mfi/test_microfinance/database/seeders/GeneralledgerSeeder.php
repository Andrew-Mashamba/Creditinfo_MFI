<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralledgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('general_ledger')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'record_on_account_number' => '0101100010001010',
                'record_on_account_number_balance' => 6000000,
                'sender_branch_id' => 1,
                'beneficiary_branch_id' => 1,
                'sender_product_id' => null,
                'sender_sub_product_id' => null,
                'beneficiary_product_id' => 2000,
                'beneficiary_sub_product_id' => null,
                'sender_id' => 0,
                'beneficiary_id' => 10003,
                'sender_name' => 'OPERATING ACCOUNT - BANK A',
                'beneficiary_name' => 'MANDATORY SAVINGS: JOHN  SAFARI PENGO',
                'sender_account_number' => '0101100010001010',
                'beneficiary_account_number' => '011000321018',
                'transaction_type' => 'debit',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' => 'Deposits deposit : 6000000 : JUMANNE MASELE : NBC Bank : HGJFGHJJ',
                'branch_id' => 1,
                'credit' => 0,
                'debit' => 6000000,
                'reference_number' => '1752821658',
                'trans_status' => 'System initiated',
                'trans_status_description' => 'System initiated',
                'swift_code' => null,
                'destination_bank_name' => null,
                'destination_bank_number' => null,
                'partner_bank' => null,
                'partner_bank_name' => null,
                'partner_bank_account_number' => null,
                'partner_bank_transaction_reference_number' => null,
                'payment_status' => 'Done',
                'recon_status' => 'Pending',
                'loan_id' => null,
                'bank_reference_number' => null,
                'product_number' => '',
                'major_category_code' => '1000',
                'category_code' => '1000',
                'sub_category_code' => '1010',
                'gl_balance' => 6000000,
                'account_level' => 3,
                'created_at' => '2025-07-18 06:54:18',
                'updated_at' => '2025-07-18 06:54:18',
            ],
            [
                'id' => 2,
                'record_on_account_number' => '011000321018',
                'record_on_account_number_balance' => 6000000,
                'sender_branch_id' => 1,
                'beneficiary_branch_id' => 1,
                'sender_product_id' => 2000,
                'sender_sub_product_id' => null,
                'beneficiary_product_id' => null,
                'beneficiary_sub_product_id' => null,
                'sender_id' => 10003,
                'beneficiary_id' => 0,
                'sender_name' => 'OPERATING ACCOUNT - BANK A',
                'beneficiary_name' => 'MANDATORY SAVINGS: JOHN  SAFARI PENGO',
                'sender_account_number' => '0101100010001010',
                'beneficiary_account_number' => '011000321018',
                'transaction_type' => 'credit',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' => 'Deposits deposit : 6000000 : JUMANNE MASELE : NBC Bank : HGJFGHJJ',
                'branch_id' => 1,
                'credit' => 6000000,
                'debit' => 0,
                'reference_number' => '1752821658',
                'trans_status' => 'System initiated',
                'trans_status_description' => 'System initiated',
                'swift_code' => null,
                'destination_bank_name' => null,
                'destination_bank_number' => null,
                'partner_bank' => null,
                'partner_bank_name' => null,
                'partner_bank_account_number' => null,
                'partner_bank_transaction_reference_number' => null,
                'payment_status' => 'Done',
                'recon_status' => 'Pending',
                'loan_id' => null,
                'bank_reference_number' => null,
                'product_number' => 2000,
                'major_category_code' => '2000',
                'category_code' => '2100',
                'sub_category_code' => '2101',
                'gl_balance' => 6000000,
                'account_level' => 4,
                'created_at' => '2025-07-18 06:54:18',
                'updated_at' => '2025-07-18 06:54:18',
            ],
        ];

        foreach ($data as $row) {
            DB::table('general_ledger')->insert($row);
    }
}
}