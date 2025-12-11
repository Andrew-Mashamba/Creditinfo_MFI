<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('purchases')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'requisition_description' => 'This is a sample description for purchases record 1',
                'status' => 'pending',
                'invoice' => 'Sample invoice 1',
                'employeeId' => 1,
                'vendorId' => 1,
                'branchId' => 1,
                'quantity' => 10,
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'requisition_description' => 'This is a sample description for purchases record 2',
                'status' => 'inactive',
                'invoice' => 'Sample invoice 2',
                'employeeId' => 2,
                'vendorId' => 2,
                'branchId' => 2,
                'quantity' => 20,
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('purchases')->insert($row);
    }
}
}