<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanproductchargesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loan_product_charges')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'loan_product_id' => 731205,
                'type' => 'charge',
                'name' => 'APPLICATION FEE',
                'value_type' => 'fixed',
                'value' => 12000.00,
                'account_id' => '010140004100',
                'created_at' => '2025-07-18 06:07:38',
                'updated_at' => '2025-07-18 06:07:38',
            ],
            [
                'id' => 2,
                'loan_product_id' => 731205,
                'type' => 'insurance',
                'name' => 'LOAN INSURANCE',
                'value_type' => 'percentage',
                'value' => 2.50,
                'account_id' => '010140004500',
                'created_at' => '2025-07-18 06:07:38',
                'updated_at' => '2025-07-18 06:07:38',
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_product_charges')->insert($row);
    }
}
}