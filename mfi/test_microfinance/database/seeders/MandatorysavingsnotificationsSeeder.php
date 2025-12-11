<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MandatorysavingsnotificationsSeeder extends Seeder
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
        DB::table('mandatory_savings_notifications')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'client_number' => '000001',
                'account_number' => 10,
                'year' => 2024,
                'month' => 1,
                'notification_type' => 'FIRST_REMINDER',
                'notification_method' => 'SMS',
                'message' => 'Sample message 1',
                'status' => 'PENDING',
                'sent_at' => null,
                'scheduled_at' => now(),
                'metadata' => json_encode(['source' => 'seeder', 'version' => '1.0']),
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'client_number' => '000002',
                'account_number' => 20,
                'year' => 2024,
                'month' => 1,
                'notification_type' => 'SECOND_REMINDER',
                'notification_method' => 'EMAIL',
                'message' => 'Sample message 2',
                'status' => 'SENT',
                'sent_at' => null,
                'scheduled_at' => now(),
                'metadata' => json_encode(['source' => 'seeder', 'version' => '1.0']),
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('mandatory_savings_notifications')->insert($row);
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