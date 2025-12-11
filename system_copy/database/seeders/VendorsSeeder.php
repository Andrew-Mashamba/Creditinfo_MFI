<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('vendors')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'organization_name' => 'Sample organization_name',
                'organization_tin_number' => 000001,
                'status' => 'pending',
                'email' => 'sample1@vendors.com',
                'organization_license_number' => 000001,
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
                'organization_description' => 'This is a sample description for vendors record 1',
            ],
            [
                'id' => 2,
                'organization_name' => 'Sample organization_name',
                'organization_tin_number' => 000002,
                'status' => 'inactive',
                'email' => 'sample2@vendors.com',
                'organization_license_number' => 000002,
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
                'organization_description' => 'This is a sample description for vendors record 2',
            ],
        ];

        foreach ($data as $row) {
            DB::table('vendors')->insert($row);
    }
}
}