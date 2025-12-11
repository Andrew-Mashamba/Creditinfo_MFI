<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('payments')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'bill_id' => 1,
                'payment_ref' => 'Sample payment_ref 1',
                'transaction_reference' => 'Sample transaction_reference 1',
                'control_number' => 000001,
                'amount' => 1000,
                'currency' => 'TZS',
                'payment_channel' => 'Sample payment_channel 1',
                'payer_name' => 'Sample payer_name',
                'payer_msisdn' => 'Sample payer_msisdn 1',
                'payer_email' => 'sample1@payments.com',
                'payer_tin' => 'Sample payer_tin 1',
                'payer_nin' => 'Sample payer_nin 1',
                'paid_at' => '2025-07-23 10:35:00',
                'received_at' => '2025-07-23 10:30:00',
                'status' => 'Pending',
                'raw_payload' => json_encode(['value' => 'Sample raw_payload 1']),
                'response_data' => json_encode(['value' => 'Sample response_data 1']),
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'bill_id' => 2,
                'payment_ref' => 'Sample payment_ref 2',
                'transaction_reference' => 'Sample transaction_reference 2',
                'control_number' => 000002,
                'amount' => 2000,
                'currency' => 'TZS',
                'payment_channel' => 'Sample payment_channel 2',
                'payer_name' => 'Sample payer_name',
                'payer_msisdn' => 'Sample payer_msisdn 2',
                'payer_email' => 'sample2@payments.com',
                'payer_tin' => 'Sample payer_tin 2',
                'payer_nin' => 'Sample payer_nin 2',
                'paid_at' => '2025-07-23 11:35:00',
                'received_at' => '2025-07-23 11:30:00',
                'status' => 'Confirmed',
                'raw_payload' => json_encode(['value' => 'Sample raw_payload 2']),
                'response_data' => json_encode(['value' => 'Sample response_data 2']),
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('payments')->insert($row);
    }
}
}