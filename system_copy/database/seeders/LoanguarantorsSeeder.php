<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanguarantorsSeeder extends Seeder
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
        DB::table('loan_guarantors')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'loan_id' => 2,
                'guarantor_member_id' => 5,
                'guarantor_type' => 'self_guarantee',
                'relationship' => '',
                'total_guaranteed_amount' => 0.00,
                'available_amount' => 0.00,
                'status' => 'active',
                'guarantee_start_date' => '2025-07-18 09:56:43',
                'guarantee_end_date' => null,
                'notes' => null,
                'is_active' => true,
                'created_at' => '2025-07-18 06:56:42',
                'updated_at' => '2025-07-18 06:56:42',
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_guarantors')->insert($row);
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