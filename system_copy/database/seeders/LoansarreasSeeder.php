<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoansarreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loans_arreas')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'balance' => 1000,
                'bank_account_number' => 10,
                'completion_status' => 'PENDING',
                'status' => 10,
                'installment_date' => '2025-07-24',
                'last_check_date' => '2025-07-24',
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'balance' => 2000,
                'bank_account_number' => 20,
                'completion_status' => 'INACTIVE',
                'status' => 20,
                'installment_date' => '2025-07-25',
                'last_check_date' => '2025-07-25',
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('loans_arreas')->insert($row);
    }
}
}