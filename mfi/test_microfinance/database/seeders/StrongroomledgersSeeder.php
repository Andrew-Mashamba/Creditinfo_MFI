<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StrongroomledgersSeeder extends Seeder
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
        DB::table('strongroom_ledgers')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'vault_id' => 1,
                'balance' => 1000,
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 1']),
                'branch_id' => 1,
                'vault_code' => 'STR001',
                'status' => 'active',
                'notes' => 'Sample notes 1',
                'last_transaction_at' => '2025-07-23 10:00:00',
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'vault_id' => 2,
                'balance' => 2000,
                'denomination_breakdown' => json_encode(['value' => 'Sample denomination_breakdown 2']),
                'branch_id' => 2,
                'vault_code' => 'STR002',
                'status' => 'maintenance',
                'notes' => 'Sample notes 2',
                'last_transaction_at' => '2025-07-23 11:00:00',
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('strongroom_ledgers')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
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