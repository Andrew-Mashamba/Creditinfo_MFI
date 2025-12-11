<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('payables')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'customer_name' => 'Sample customer_name',
                'due_date' => '2025-07-24',
                'invoice_number' => 000001,
                'amount' => 1000,
                'liability_account' => 10,
                'cash_account' => 10,
                'expense_account' => 10,
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'customer_name' => 'Sample customer_name',
                'due_date' => '2025-07-25',
                'invoice_number' => 000002,
                'amount' => 2000,
                'liability_account' => 20,
                'cash_account' => 20,
                'expense_account' => 20,
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('payables')->insert($row);
    }
}
}