<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MandatorysavingstrackingSeeder extends Seeder
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
        DB::table('mandatory_savings_tracking')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'client_number' => '000001',
                'account_number' => 10,
                'year' => 2024,
                'month' => 1,
                'required_amount' => 1000,
                'paid_amount' => 1,
                'balance' => 1000,
                'status' => 'UNPAID',
                'due_date' => '2025-07-24',
                'paid_date' => null,
                'notes' => 'Sample notes 1',
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'client_number' => '000002',
                'account_number' => 20,
                'year' => 2024,
                'month' => 1,
                'required_amount' => 2000,
                'paid_amount' => 2,
                'balance' => 2000,
                'status' => 'PAID',
                'due_date' => '2025-07-25',
                'paid_date' => '2025-07-25',
                'notes' => 'Sample notes 2',
                'created_at' => '2025-07-23 11:38:39',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('mandatory_savings_tracking')->insert($row);
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