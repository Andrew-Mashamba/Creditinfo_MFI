<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanprovisionsettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loan_provision_settings')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'per' => 'Sample per 1',
                'description' => 'This is a sample description for loan_provision_settings record 1',
                'status' => true,
            ],
            [
                'id' => 2,
                'per' => 'Sample per 2',
                'description' => 'This is a sample description for loan_provision_settings record 2',
                'status' => true,
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_provision_settings')->insert($row);
    }
}
}