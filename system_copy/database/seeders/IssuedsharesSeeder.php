<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IssuedsharesSeeder extends Seeder
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
        DB::table('issued_shares')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'reference_number' => '000001',
                'share_id' => 1,
                'member' => 'Sample member 1',
                'product' => 'Sample product 1',
                'account_number' => 10,
                'price' => 1000,
                'branch' => 'Sample branch 1',
                'client_number' => '000001',
                'number_of_shares' => 000001,
                'nominal_price' => 1000,
                'total_value' => 1000.00,
                'linked_savings_account' => 10,
                'linked_share_account' => 10,
                'status' => 'pending',
                'created_by' => 1,
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'reference_number' => '000002',
                'share_id' => 2,
                'member' => 'Sample member 2',
                'product' => 'Sample product 2',
                'account_number' => 20,
                'price' => 2000,
                'branch' => 'Sample branch 2',
                'client_number' => '000002',
                'number_of_shares' => 000002,
                'nominal_price' => 2000,
                'total_value' => 1000.00,
                'linked_savings_account' => 20,
                'linked_share_account' => 20,
                'status' => 'inactive',
                'created_by' => 1,
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('issued_shares')->updateOrInsert(
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