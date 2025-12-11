<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanktransfersSeeder extends Seeder
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
            DB::table('bank_transfers')->truncate();

            // Insert sample data (table was empty)
            $data = [
                [
                    'id' => 1,
                    'vault_id' => 1,
                    'amount' => 1000,
                    'reason' => 'manual',
                    'status' => 'pending',
                    'reference' => 'Sample reference 1',
                    'initiated_by' => 1,
                    'approved_by' => 1,
                    'bank_response' => 'Sample bank_response 1',
                    'processed_at' => now(),
                    'created_at' => '2025-07-23 10:38:33',
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'vault_id' => 2,
                    'amount' => 2000,
                    'reason' => 'over_limit',
                    'status' => 'pending',
                    'reference' => 'Sample reference 2',
                    'initiated_by' => 1,
                    'approved_by' => 1,
                    'bank_response' => 'Sample bank_response 2',
                    'processed_at' => now(),
                    'created_at' => '2025-07-23 11:38:33',
                    'updated_at' => now(),
                ],
            ];

            foreach ($data as $row) {
                DB::table('bank_transfers')->insert($row);
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