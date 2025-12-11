<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShortlongtermloansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('short_long_term_loans')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'source_account_id' => 1,
                'user_id' => 1,
                'status' => 'pending',
                'is_approved' => false,
                'amount' => 1000,
                'organization_name' => 'Sample organization_name',
                'address' => 'Sample Address 1, short_long_term_loans Street',
                'phone' => +255700000001,
                'email' => 'sample1@short_long_term_loans.com',
                'description' => 'This is a sample description for short_long_term_loans record 1',
                'application_form' => 'Sample application_form 1',
                'contract_form' => 'Sample contract_form 1',
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
                'loan_type' => 'TYPE_B',
            ],
            [
                'id' => 2,
                'source_account_id' => 2,
                'user_id' => 2,
                'status' => 'inactive',
                'is_approved' => true,
                'amount' => 2000,
                'organization_name' => 'Sample organization_name',
                'address' => 'Sample Address 2, short_long_term_loans Street',
                'phone' => +255700000002,
                'email' => 'sample2@short_long_term_loans.com',
                'description' => 'This is a sample description for short_long_term_loans record 2',
                'application_form' => 'Sample application_form 2',
                'contract_form' => 'Sample contract_form 2',
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
                'loan_type' => 'TYPE_C',
            ],
        ];

        foreach ($data as $row) {
            DB::table('short_long_term_loans')->insert($row);
    }
}
}