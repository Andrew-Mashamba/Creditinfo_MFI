<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeerequestsSeeder extends Seeder
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
        // DB::table('employee_requests')->truncate(); // Commented out to avoid foreign key issues

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'employee_id' => 1,
                'type' => 'standard',
                'department' => 'Sample department 1',
                'subject' => 'Sample subject 1',
                'details' => json_encode([]),
                'status' => 'pending',
                'approver_id' => 1,
                'approved_at' => now(),
                'rejection_reason' => 'Sample rejection_reason 1',
                'attachments' => json_encode(['value' => 'Sample attachments 1']),
                'created_at' => '2025-07-23 10:38:34',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'employee_id' => 2,
                'type' => 'standard',
                'department' => 'Sample department 2',
                'subject' => 'Sample subject 2',
                'details' => json_encode([]),
                'status' => 'inactive',
                'approver_id' => 2,
                'approved_at' => now(),
                'rejection_reason' => 'Sample rejection_reason 2',
                'attachments' => json_encode(['value' => 'Sample attachments 2']),
                'created_at' => '2025-07-23 11:38:34',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Use updateOrInsert to avoid foreign key conflicts
            DB::table('employee_requests')->updateOrInsert(
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