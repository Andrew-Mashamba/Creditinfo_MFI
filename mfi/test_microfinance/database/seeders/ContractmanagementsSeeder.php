<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractmanagementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('contract_managements')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'contract_name' => 'Sample contract_name',
                'contract_description' => 'This is a sample description for contract_managements record 1',
                'contract_file_path' => 'Sample contract_file_path 1',
                'endDate' => '2025-07-24',
                'startDate' => '2025-07-24',
                'vendorId' => 1,
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'contract_name' => 'Sample contract_name',
                'contract_description' => 'This is a sample description for contract_managements record 2',
                'contract_file_path' => 'Sample contract_file_path 2',
                'endDate' => '2025-07-25',
                'startDate' => '2025-07-25',
                'vendorId' => 2,
                'status' => 'inactive',
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('contract_managements')->insert($row);
    }
}
}