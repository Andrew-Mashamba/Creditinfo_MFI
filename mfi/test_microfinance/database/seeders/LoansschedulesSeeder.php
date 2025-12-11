<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoansschedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loans_schedules')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'opening_balance' => 1000,
                'closing_balance' => 1000,
                'bank_account_number' => 10,
                'completion_status' => 'PENDING',
                'status' => 10,
                'installment_date' => '2025-07-24',
                'next_check_date' => '2025-07-24',
                'amount_in_arrears' => 1000,
                'promise_date' => '2025-07-24',
                'comment' => 'Sample comment 1',
                'member_number' => 000001,
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'opening_balance' => 2000,
                'closing_balance' => 2000,
                'bank_account_number' => 20,
                'completion_status' => 'INACTIVE',
                'status' => 20,
                'installment_date' => '2025-07-25',
                'next_check_date' => '2025-07-25',
                'amount_in_arrears' => 2000,
                'promise_date' => '2025-07-25',
                'comment' => 'Sample comment 2',
                'member_number' => 000002,
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('loans_schedules')->insert($row);
    }
}
}