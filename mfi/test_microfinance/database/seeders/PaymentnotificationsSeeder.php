<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentnotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('payment_notifications')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'control_number' => 000001,
                'received_at' => '2025-07-23 10:30:00',
                'raw_payload' => json_encode(['value' => 'Sample raw_payload 1']),
                'status' => 'Pending',
                'processed_at' => now(),
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'control_number' => 000002,
                'received_at' => '2025-07-23 11:30:00',
                'raw_payload' => json_encode(['value' => 'Sample raw_payload 2']),
                'status' => 'Processed',
                'processed_at' => now(),
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('payment_notifications')->insert($row);
    }
}
}