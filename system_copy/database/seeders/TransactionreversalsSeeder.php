<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionreversalsSeeder extends Seeder
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
        DB::table('transaction_reversals')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'transaction_id' => 1,
                'reversal_reference' => 'Sample reversal_reference 1',
                'reason' => 'Sample reason 1',
                'reversed_by' => 1,
                'is_automatic' => false,
                'status' => 'pending',
                'correlation_id' => Str::uuid(),
                'retry_count' => 10,
                'next_retry_at' => null,
                'external_reference' => 'Sample external_reference 1',
                'external_transaction_id' => 1,
                'external_request_payload' => json_encode([]),
                'external_response_payload' => json_encode([]),
                'external_status_code' => 'PENDING',
                'external_status_message' => 'PENDING',
                'error_code' => 'TRA001',
                'error_message' => 'Sample error_message 1',
                'processed_at' => now(),
                'completed_at' => now(),
                'failed_at' => null,
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
                'deleted_at' => null,
                'metadata' => json_encode(['source' => 'seeder', 'version' => '1.0']),
            ],
            [
                'id' => 2,
                'transaction_id' => 2,
                'reversal_reference' => 'Sample reversal_reference 2',
                'reason' => 'Sample reason 2',
                'reversed_by' => 1,
                'is_automatic' => true,
                'status' => 'completed',
                'correlation_id' => Str::uuid(),
                'retry_count' => 20,
                'next_retry_at' => null,
                'external_reference' => 'Sample external_reference 2',
                'external_transaction_id' => 2,
                'external_request_payload' => json_encode([]),
                'external_response_payload' => json_encode([]),
                'external_status_code' => 'INACTIVE',
                'external_status_message' => 'INACTIVE',
                'error_code' => 'TRA002',
                'error_message' => 'Sample error_message 2',
                'processed_at' => now(),
                'completed_at' => now(),
                'failed_at' => null,
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
                'deleted_at' => null,
                'metadata' => json_encode(['source' => 'seeder', 'version' => '1.0']),
            ],
        ];

        foreach ($data as $row) {
            DB::table('transaction_reversals')->insert($row);
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