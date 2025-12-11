<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashmovementsSeeder extends Seeder
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
        DB::table('cash_movements')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'vault_id' => 1,
                'strongroom_ledger_id' => 1,
                'type' => 'till_to_vault',
                'amount' => 1000.00,
                'status' => 'pending',
                'description' => 'End of day till to vault transfer',
                'user_id' => 1,
                'branch_id' => 1,
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'vault_id' => 2,
                'strongroom_ledger_id' => 2,
                'type' => 'vault_to_till',
                'amount' => 2000.00,
                'status' => 'completed',
                'description' => 'Morning till replenishment',
                'user_id' => 2,
                'branch_id' => 2,
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('cash_movements')->insert($row);
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