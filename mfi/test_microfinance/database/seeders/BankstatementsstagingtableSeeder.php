<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankstatementsstagingtableSeeder extends Seeder
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
        DB::table('bank_statements_staging_table')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'Institution_Id' => 1,
                'Reference_Number' => '000001',
                'Details' => 'Sample Details 1',
                'Value_Date' => '2025-07-24',
                'Debit' => 1000.00,
                'Credit' => 0.00,
                'Book_Balance' => 1000,
                'Process_Status' => 'pending',
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'Institution_Id' => 2,
                'Reference_Number' => '000002',
                'Details' => 'Sample Details 2',
                'Value_Date' => '2025-07-25',
                'Debit' => 0.00,
                'Credit' => 2000.00,
                'Book_Balance' => 2000,
                'Process_Status' => 'processed',
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('bank_statements_staging_table')->insert($row);
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