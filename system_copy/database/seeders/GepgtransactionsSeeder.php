<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GepgtransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('gepg_transactions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'control_number' => 000001,
                'account_no' => 10,
                'amount' => 1000,
                'currency' => 'TZS',
                'response_code' => 'GEP001',
                'response_description' => 'This is a sample description for gepg_transactions record 1',
                'payload' => json_encode(['value' => 'Sample payload 1']),
                'transaction_type' => 'deposit',
                'quote_reference' => 'Sample quote_reference 1',
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'control_number' => 000002,
                'account_no' => 20,
                'amount' => 2000,
                'currency' => 'TZS',
                'response_code' => 'GEP002',
                'response_description' => 'This is a sample description for gepg_transactions record 2',
                'payload' => json_encode(['value' => 'Sample payload 2']),
                'transaction_type' => 'deposit',
                'quote_reference' => 'Sample quote_reference 2',
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('gepg_transactions')->insert($row);
    }
}
}