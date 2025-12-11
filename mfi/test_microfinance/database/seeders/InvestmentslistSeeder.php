<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvestmentslistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('investments_list')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'investment_type' => 'TYPE_B',
                'principal_amount' => 1000,
                'investment_date' => '2025-07-24',
                'number_of_shares' => 000001,
                'share_price' => 1000,
                'dividend_rate' => 1,
                'sale_price' => 1000,
                'interest_rate' => 5.5,
                'maturity_date' => '2025-07-24',
                'bond_type' => 'TYPE_B',
                'coupon_rate' => 5.5,
                'fund_name' => 'Sample fund_name',
                'fund_manager' => 'Sample fund_manager 1',
                'property_value' => 1000.00,
                'location' => 'Sample location 1',
                'purchase_date' => '2025-07-24',
                'description' => 'This is a sample description for investments_list record 1',
                'interest_dividend_rate' => 1,
                'status' => 'pending',
                'cash_account' => 10,
                'investment_account' => 10,
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'investment_type' => 'TYPE_C',
                'principal_amount' => 2000,
                'investment_date' => '2025-07-25',
                'number_of_shares' => 000002,
                'share_price' => 2000,
                'dividend_rate' => 2,
                'sale_price' => 2000,
                'interest_rate' => 11,
                'maturity_date' => '2025-07-25',
                'bond_type' => 'TYPE_C',
                'coupon_rate' => 11,
                'fund_name' => 'Sample fund_name',
                'fund_manager' => 'Sample fund_manager 2',
                'property_value' => 1000.00,
                'location' => 'Sample location 2',
                'purchase_date' => '2025-07-25',
                'description' => 'This is a sample description for investments_list record 2',
                'interest_dividend_rate' => 2,
                'status' => 'inactive',
                'cash_account' => 20,
                'investment_account' => 20,
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('investments_list')->insert($row);
    }
}
}