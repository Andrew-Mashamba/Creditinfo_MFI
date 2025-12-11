<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TilltransactionsSeeder extends Seeder
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
        DB::table('till_transactions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'reference' => 'Sample reference 1',
                'till_id' => 1,
                'teller_id' => 1,
                'client_id' => 1,
                'type' => 'deposit',
                'transaction_type' => 'cash_in',
                'amount' => 1000,
                'balance_before' => 1000,
                'balance_after' => 1000,
                'account_number' => 10,
                'description' => 'This is a sample description for till_transactions record 1',
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 1']),
                'receipt_number' => 000001,
                'status' => 'completed',
                'processed_at' => now(),
                'reversed_by' => 1,
                'reversed_at' => null,
                'reversal_reason' => 'Sample reversal_reason 1',
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'reference' => 'Sample reference 2',
                'till_id' => 2,
                'teller_id' => 2,
                'client_id' => 2,
                'type' => 'deposit',
                'transaction_type' => 'cash_in',
                'amount' => 2000,
                'balance_before' => 2000,
                'balance_after' => 2000,
                'account_number' => 20,
                'description' => 'This is a sample description for till_transactions record 2',
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 2']),
                'receipt_number' => 000002,
                'status' => 'completed',
                'processed_at' => now(),
                'reversed_by' => 1,
                'reversed_at' => null,
                'reversal_reason' => 'Sample reversal_reason 2',
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('till_transactions')->insert($row);
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