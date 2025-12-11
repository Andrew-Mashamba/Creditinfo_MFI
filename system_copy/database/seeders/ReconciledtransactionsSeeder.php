<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReconciledtransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('reconciled_transactions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'Account_Code' => '10',
                'Reference_Number' => '000001',
                'Value_Date' => '2025-07-24',
                'Gl_Details' => 'Sample Gl_Details 1',
                'Gl_Debit' => 1000.00,
                'Gl_Credit' => 0.00,
                'Bank_Details' => 'Sample Bank_Details 1',
                'Bank_Debit' => 1000.00,
                'Bank_Credit' => 0.00,
                'created_at' => '2025-07-23 10:38:41',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'Account_Code' => '20',
                'Reference_Number' => '000002',
                'Value_Date' => '2025-07-25',
                'Gl_Details' => 'Sample Gl_Details 2',
                'Gl_Debit' => 0.00,
                'Gl_Credit' => 2000.00,
                'Bank_Details' => 'Sample Bank_Details 2',
                'Bank_Debit' => 0.00,
                'Bank_Credit' => 2000.00,
                'created_at' => '2025-07-23 11:38:41',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('reconciled_transactions')->insert($row);
    }
}
}