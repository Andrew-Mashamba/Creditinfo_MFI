<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternaltransfersSeeder extends Seeder
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
        DB::table('internal_transfers')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'transfer_date' => '2025-07-24',
                'transfer_type' => 'asset_to_asset',
                'from_account_id' => 1,
                'to_account_id' => 1,
                'amount' => 1000,
                'narration' => 'Sample narration 1',
                'attachment_path' => 'Sample attachment_path 1',
                'status' => 'draft',
                'created_by' => 1,
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'transfer_date' => '2025-07-25',
                'transfer_type' => 'asset_to_liability',
                'from_account_id' => 2,
                'to_account_id' => 2,
                'amount' => 2000,
                'narration' => 'Sample narration 2',
                'attachment_path' => 'Sample attachment_path 2',
                'status' => 'posted',
                'created_by' => 1,
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('internal_transfers')->insert($row);
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