<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('orders')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'team_id' => 1,
                'user_id' => 1,
                'order_number' => 000001,
                'order_status' => 'PENDING',
                'order_failed_transaction' => 'Sample order_failed_transaction 1',
                'completed' => true,
                'source_account' => 10,
                'amountOfTransactions' => 1000,
                'typeOfTransfer' => 'TYPE_B',
                'first_authorizer_id' => 1,
                'first_authorizer_action' => 'Sample first_authorizer_action 1',
                'first_authorizer_comments' => 'Sample first_authorizer_comments 1',
                'second_authorizer_id' => 1,
                'second_authorizer_action' => 'Sample second_authorizer_action 1',
                'second_authorizer_comments' => 'Sample second_authorizer_comments 1',
                'third_authorizer_id' => 1,
                'third_authorizer_action' => 'Sample third_authorizer_action 1',
                'third_authorizer_comments' => 'Sample third_authorizer_comments 1',
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'team_id' => 2,
                'user_id' => 2,
                'order_number' => 000002,
                'order_status' => 'INACTIVE',
                'order_failed_transaction' => 'Sample order_failed_transaction 2',
                'completed' => true,
                'source_account' => 20,
                'amountOfTransactions' => 2000,
                'typeOfTransfer' => 'TYPE_C',
                'first_authorizer_id' => 2,
                'first_authorizer_action' => 'Sample first_authorizer_action 2',
                'first_authorizer_comments' => 'Sample first_authorizer_comments 2',
                'second_authorizer_id' => 2,
                'second_authorizer_action' => 'Sample second_authorizer_action 2',
                'second_authorizer_comments' => 'Sample second_authorizer_comments 2',
                'third_authorizer_id' => 2,
                'third_authorizer_action' => 'Sample third_authorizer_action 2',
                'third_authorizer_comments' => 'Sample third_authorizer_comments 2',
                'created_at' => '2025-07-23 11:38:39',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('orders')->insert($row);
    }
}
}