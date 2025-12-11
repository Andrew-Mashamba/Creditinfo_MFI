<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChequesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('cheques')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'customer_account' => 10,
                'amount' => 1000,
                'cheque_number' => '000001',
                'branch' => 1,
                'finance_approver' => 'John Doe',
                'manager_approver' => 'Jane Smith',
                'expiry_date' => '2025-07-24',
                'is_cleared' => false,
                'status' => 'pending',
                'bank_account' => 10,
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'customer_account' => 20,
                'amount' => 2000,
                'cheque_number' => '000002',
                'branch' => 2,
                'finance_approver' => 'Bob Johnson',
                'manager_approver' => 'Alice Brown',
                'expiry_date' => '2025-07-25',
                'is_cleared' => true,
                'status' => 'inactive',
                'bank_account' => 20,
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('cheques')->insert($row);
    }
}
}