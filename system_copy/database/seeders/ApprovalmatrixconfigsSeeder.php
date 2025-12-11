<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalmatrixconfigsSeeder extends Seeder
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
        // DB::table('approval_matrix_configs')->truncate(); // Commented out to avoid foreign key issues

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'process_type' => 'loan',
                'process_name' => 'Sample process_name',
                'process_code' => 'APP001',
                'level' => 1,
                'approver_role' => 'Sample approver_role 1',
                'approver_sub_role' => 'Sample approver_sub_role 1',
                'min_amount' => 1000,
                'max_amount' => 1000,
                'is_active' => false,
                'additional_conditions' => json_encode(['condition' => 'Sample additional_conditions 1']),
                'created_at' => '2025-07-23 10:38:32',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'process_type' => 'loan',
                'process_name' => 'Sample process_name',
                'process_code' => 'APP002',
                'level' => 1,
                'approver_role' => 'Sample approver_role 2',
                'approver_sub_role' => 'Sample approver_sub_role 2',
                'min_amount' => 2000,
                'max_amount' => 2000,
                'is_active' => true,
                'additional_conditions' => json_encode(['condition' => 'Sample additional_conditions 2']),
                'created_at' => '2025-07-23 11:38:32',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Use updateOrInsert to avoid foreign key conflicts
            DB::table('approval_matrix_configs')->updateOrInsert(
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