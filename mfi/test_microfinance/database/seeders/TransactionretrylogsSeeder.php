<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionretrylogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('SET session_replication_role = replica;');
        }
        
        try {
        // Clear existing data
        DB::table('transaction_retry_logs')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'transaction_id' => 1,
                'retry_attempt' => 1,
                'retry_at' => now(),
                'retry_reason' => 'Sample retry_reason 1',
                'retry_result' => 'Sample retry_result 1',
                'error_code' => 'TRA001',
                'error_message' => 'Sample error_message 1',
                'retry_payload' => json_encode(['value' => 'Sample retry_payload 1']),
                'retry_response' => json_encode(['value' => 'Sample retry_response 1']),
                'processing_time_ms' => 100,
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'transaction_id' => 2,
                'retry_attempt' => 1,
                'retry_at' => now(),
                'retry_reason' => 'Sample retry_reason 2',
                'retry_result' => 'Sample retry_result 2',
                'error_code' => 'TRA002',
                'error_message' => 'Sample error_message 2',
                'retry_payload' => json_encode(['value' => 'Sample retry_payload 2']),
                'retry_response' => json_encode(['value' => 'Sample retry_response 2']),
                'processing_time_ms' => 200,
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('transaction_retry_logs')->insert($row);
        }
        
        } finally {
            // Re-enable foreign key checks
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif (DB::getDriverName() === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT;');
            }
        }
    }
}