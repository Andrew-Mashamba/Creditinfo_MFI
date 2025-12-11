<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanauditlogsSeeder extends Seeder
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
        DB::table('loan_audit_logs')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'loan_id' => 2,
                'action' => 'STATUS_CHANGE',
                'old_values' => json_encode(['value' => '{"status":"PENDING"}']),
                'new_values' => json_encode(['value' => '{"status":"AWAITING_DISBURSEMENT"}']),
                'user_id' => 1,
                'ip_address' => null,
                'user_agent' => null,
                'description' => 'Approved by Andrew S. Mashamba',
                'created_at' => '2025-07-18 07:08:48',
                'updated_at' => '2025-07-18 07:08:48',
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_audit_logs')->insert($row);
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