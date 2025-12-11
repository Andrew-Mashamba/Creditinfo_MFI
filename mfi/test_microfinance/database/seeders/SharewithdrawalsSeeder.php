<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SharewithdrawalsSeeder extends Seeder
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
        DB::table('share_withdrawals')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'member_id' => 1,
                'client_number' => '000001',
                'product_id' => 1,
                'product_name' => 'Sample product_name',
                'withdrawal_amount' => 1000,
                'nominal_price' => 1000,
                'total_value' => 1000.00,
                'receiving_account_id' => 1,
                'receiving_account_number' => 10,
                'source_account_id' => 1,
                'source_account_number' => 10,
                'reason' => 'Sample reason 1',
                'status' => 'pending',
                'approved_by' => 1,
                'approved_at' => now(),
                'created_by' => 1,
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'client_number' => '000002',
                'product_id' => 2,
                'product_name' => 'Sample product_name',
                'withdrawal_amount' => 2000,
                'nominal_price' => 2000,
                'total_value' => 1000.00,
                'receiving_account_id' => 2,
                'receiving_account_number' => 20,
                'source_account_id' => 2,
                'source_account_number' => 20,
                'reason' => 'Sample reason 2',
                'status' => 'inactive',
                'approved_by' => 1,
                'approved_at' => now(),
                'created_by' => 1,
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('share_withdrawals')->insert($row);
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