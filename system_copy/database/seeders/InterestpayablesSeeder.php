<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestpayablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('interest_payables')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'member_id' => 1,
                'account_type' => 10,
                'amount' => 1000,
                'interest_rate' => 5.5,
                'deposit_date' => '2025-07-24',
                'maturity_date' => '2025-07-24',
                'payment_frequency' => 'monthly',
                'accrued_interest' => 0.00,
                'interest_payable' => 0.00,
                'loan_provider' => 1,
                'loan_interest_rate' => 5.5,
                'loan_term' => 'Sample loan_term 1',
                'loan_start_date' => '2025-07-24',
                'interest_payment_schedule' => 'Sample interest_payment_schedule 1',
                'accrued_interest_loan' => 0.00,
                'interest_payable_loan' => 0.00,
                'created_by' => 1,
                'created_at' => '2025-07-23 10:38:36',
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'account_type' => 20,
                'amount' => 2000,
                'interest_rate' => 11,
                'deposit_date' => '2025-07-25',
                'maturity_date' => '2025-07-25',
                'payment_frequency' => 'monthly',
                'accrued_interest' => 0.00,
                'interest_payable' => 0.00,
                'loan_provider' => 2,
                'loan_interest_rate' => 11,
                'loan_term' => 'Sample loan_term 2',
                'loan_start_date' => '2025-07-25',
                'interest_payment_schedule' => 'Sample interest_payment_schedule 2',
                'accrued_interest_loan' => 0.00,
                'interest_payable_loan' => 0.00,
                'created_by' => 1,
                'created_at' => '2025-07-23 11:38:36',
            ],
        ];

        foreach ($data as $row) {
            DB::table('interest_payables')->insert($row);
    }
}
}