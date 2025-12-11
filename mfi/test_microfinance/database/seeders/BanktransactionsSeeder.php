<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanktransactionsSeeder extends Seeder
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
        DB::table('bank_transactions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'session_id' => 1,
                'transaction_date' => '2025-07-24',
                'value_date' => '2025-07-24',
                'reference_number' => '000001',
                'narration' => 'Sample narration 1',
                'withdrawal_amount' => 1000,
                'deposit_amount' => 1000,
                'balance' => 1000,
                'matched_transaction_id' => 1,
                'reconciliation_status' => 'unreconciled',
                'match_confidence' => 1,
                'reconciliation_notes' => 'Sample reconciliation_notes 1',
                'reconciled_at' => now(),
                'reconciled_by' => null,
                'branch' => 'Sample branch 1',
                'transaction_type' => 'deposit',
                'raw_data' => json_encode(['value' => 'Sample raw_data 1']),
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'session_id' => 2,
                'transaction_date' => '2025-07-25',
                'value_date' => '2025-07-25',
                'reference_number' => '000002',
                'narration' => 'Sample narration 2',
                'withdrawal_amount' => 2000,
                'deposit_amount' => 2000,
                'balance' => 2000,
                'matched_transaction_id' => 2,
                'reconciliation_status' => 'unreconciled',
                'match_confidence' => 2,
                'reconciliation_notes' => 'Sample reconciliation_notes 2',
                'reconciled_at' => now(),
                'reconciled_by' => null,
                'branch' => 'Sample branch 2',
                'transaction_type' => 'deposit',
                'raw_data' => json_encode(['value' => 'Sample raw_data 2']),
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('bank_transactions')->insert($row);
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