<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionauditlogsSeeder extends Seeder
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
        DB::table('transaction_audit_logs')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'transaction_id' => 1,
                'action' => 'Sample action 1',
                'previous_status' => 'PENDING',
                'new_status' => 'PENDING',
                'description' => 'This is a sample description for transaction_audit_logs record 1',
                'performed_by' => 1,
                'client_ip' => '192.168.1.1',
                'context' => json_encode(['type' => 'sample', 'category' => 'test']),
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'transaction_id' => 2,
                'action' => 'Sample action 2',
                'previous_status' => 'INACTIVE',
                'new_status' => 'INACTIVE',
                'description' => 'This is a sample description for transaction_audit_logs record 2',
                'performed_by' => 1,
                'client_ip' => '192.168.1.2',
                'context' => json_encode(['type' => 'sample', 'category' => 'test']),
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('transaction_audit_logs')->insert($row);
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