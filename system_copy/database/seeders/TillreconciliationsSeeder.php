<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TillreconciliationsSeeder extends Seeder
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
        DB::table('till_reconciliations')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'till_id' => 1,
                'teller_id' => 1,
                'supervisor_id' => 1,
                'reconciliation_date' => '2025-07-24',
                'opening_balance' => 1000,
                'closing_balance_system' => 1000,
                'closing_balance_actual' => 1000,
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 1']),
                'transaction_count' => 10,
                'status' => 'pending_approval',
                'variance_explanation' => 'Sample variance_explanation 1',
                'supervisor_notes' => 'Sample supervisor_notes 1',
                'submitted_at' => now(),
                'approved_at' => now(),
                'started_at' => now(),
                'completed_at' => now(),
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'till_id' => 2,
                'teller_id' => 2,
                'supervisor_id' => 2,
                'reconciliation_date' => '2025-07-25',
                'opening_balance' => 2000,
                'closing_balance_system' => 2000,
                'closing_balance_actual' => 2000,
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 2']),
                'transaction_count' => 20,
                'status' => 'approved',
                'variance_explanation' => 'Sample variance_explanation 2',
                'supervisor_notes' => 'Sample supervisor_notes 2',
                'submitted_at' => now(),
                'approved_at' => now(),
                'started_at' => now(),
                'completed_at' => now(),
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('till_reconciliations')->insert($row);
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