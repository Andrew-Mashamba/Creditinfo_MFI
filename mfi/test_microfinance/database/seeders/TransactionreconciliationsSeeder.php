<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionreconciliationsSeeder extends Seeder
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
        DB::table('transaction_reconciliations')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'transaction_id' => 1,
                'reconciliation_type' => 'TYPE_B',
                'external_system' => 'Sample external_system 1',
                'external_reference' => 'Sample external_reference 1',
                'external_amount' => 1000,
                'external_currency' => 'TZS',
                'external_transaction_date' => '2025-07-24',
                'reconciliation_status' => 'unreconciled',
                'reconciliation_notes' => 'Sample reconciliation_notes 1',
                'reconciliation_data' => json_encode(['value' => 'Sample reconciliation_data 1']),
                'reconciled_at' => now(),
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'transaction_id' => 2,
                'reconciliation_type' => 'TYPE_C',
                'external_system' => 'Sample external_system 2',
                'external_reference' => 'Sample external_reference 2',
                'external_amount' => 2000,
                'external_currency' => 'TZS',
                'external_transaction_date' => '2025-07-25',
                'reconciliation_status' => 'unreconciled',
                'reconciliation_notes' => 'Sample reconciliation_notes 2',
                'reconciliation_data' => json_encode(['value' => 'Sample reconciliation_data 2']),
                'reconciled_at' => now(),
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('transaction_reconciliations')->insert($row);
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