<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReconciliationstagingtableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('reconciliation_staging_table')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'Reference_Number' => '000001',
                'Account_code' => '10',
                'Details' => 'Sample Details 1',
                'Value_Date' => '2025-07-24',
                'Debit' => 1000.00,
                'Credit' => 0.00,
                'Book_Balance' => 1000.00,
                'Institution_Id' => '1',
                'Process_Status' => 'pending',
                'created_at' => '2025-07-23 10:38:41',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'Reference_Number' => '000002',
                'Account_code' => '20',
                'Details' => 'Sample Details 2',
                'Value_Date' => '2025-07-25',
                'Debit' => 0.00,
                'Credit' => 2000.00,
                'Book_Balance' => 2000.00,
                'Institution_Id' => '2',
                'Process_Status' => 'processed',
                'created_at' => '2025-07-23 11:38:41',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('reconciliation_staging_table')->insert($row);
    }
}
}