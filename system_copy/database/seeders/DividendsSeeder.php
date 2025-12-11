<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DividendsSeeder extends Seeder
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
        DB::table('dividends')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'member_id' => 1,
                'year' => 2024,
                'rate' => 5.5,
                'amount' => 1000,
                'paid_at' => '2025-07-01 10:00:00',
                'payment_mode' => 'Sample payment_mode 1',
                'status' => 'pending',
                'narration' => 'Sample narration 1',
                'created_at' => '2025-07-23 10:38:34',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'year' => 2024,
                'rate' => 11,
                'amount' => 2000,
                'paid_at' => '2025-07-15 14:30:00',
                'payment_mode' => 'Sample payment_mode 2',
                'status' => 'inactive',
                'narration' => 'Sample narration 2',
                'created_at' => '2025-07-23 11:38:34',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('dividends')->updateOrInsert(
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