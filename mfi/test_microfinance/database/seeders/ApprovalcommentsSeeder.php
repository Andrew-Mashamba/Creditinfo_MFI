<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalcommentsSeeder extends Seeder
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
        // DB::table('approval_comments')->truncate(); // Commented out to avoid foreign key issues

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'approval_id' => 1,
                'comment' => 'Sample comment 1',
            ],
            [
                'id' => 2,
                'approval_id' => 2,
                'comment' => 'Sample comment 2',
            ],
        ];

        foreach ($data as $row) {
            // Use updateOrInsert to avoid foreign key conflicts
            DB::table('approval_comments')->updateOrInsert(
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