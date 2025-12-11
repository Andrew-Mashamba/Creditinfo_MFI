<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SharetransfersSeeder extends Seeder
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
        DB::table('share_transfers')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'transaction_reference' => 'Sample transaction_reference 1',
                'sender_member_id' => 1,
                'sender_client_number' => 000001,
                'sender_member_name' => 'Sample sender_member_name',
                'sender_share_register_id' => 1,
                'sender_share_account_number' => 10,
                'receiver_member_id' => 1,
                'receiver_client_number' => 000001,
                'receiver_member_name' => 'Sample receiver_member_name',
                'receiver_share_register_id' => 1,
                'receiver_share_account_number' => 10,
                'share_product_id' => 1,
                'share_product_name' => 'Sample share_product_name',
                'number_of_shares' => 000001,
                'nominal_price' => 1000,
                'total_value' => 1000.00,
                'transfer_reason' => 'Sample transfer_reason 1',
                'status' => 'PENDING',
                'rejection_reason' => 'Sample rejection_reason 1',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'transaction_reference' => 'Sample transaction_reference 2',
                'sender_member_id' => 2,
                'sender_client_number' => 000002,
                'sender_member_name' => 'Sample sender_member_name',
                'sender_share_register_id' => 2,
                'sender_share_account_number' => 20,
                'receiver_member_id' => 2,
                'receiver_client_number' => 000002,
                'receiver_member_name' => 'Sample receiver_member_name',
                'receiver_share_register_id' => 2,
                'receiver_share_account_number' => 20,
                'share_product_id' => 2,
                'share_product_name' => 'Sample share_product_name',
                'number_of_shares' => 000002,
                'nominal_price' => 2000,
                'total_value' => 1000.00,
                'transfer_reason' => 'Sample transfer_reason 2',
                'status' => 'COMPLETED',
                'rejection_reason' => 'Sample rejection_reason 2',
                'created_by' => 1,
                'updated_by' => 2,
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('share_transfers')->insert($row);
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