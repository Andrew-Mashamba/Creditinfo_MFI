<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanapprovalsSeeder extends Seeder
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
        DB::table('loan_approvals')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'stage_name' => 'Sample stage_name',
                'stage_type' => 'TYPE_B',
                'approver_id' => 1,
                'approver_name' => 'Sample approver_name',
                'status' => 'pending',
                'comments' => 'Sample comments 1',
                'approved_at' => now(),
                'conditions' => json_encode(['value' => 'Sample conditions 1']),
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'stage_name' => 'Sample stage_name',
                'stage_type' => 'TYPE_C',
                'approver_id' => 2,
                'approver_name' => 'Sample approver_name',
                'status' => 'inactive',
                'comments' => 'Sample comments 2',
                'approved_at' => now(),
                'conditions' => json_encode(['value' => 'Sample conditions 2']),
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_approvals')->insert($row);
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