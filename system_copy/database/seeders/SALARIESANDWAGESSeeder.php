<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SALARIESANDWAGESSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('SALARIES_AND_WAGES')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'category_code' => 'SAL001',
                'sub_category_code' => 'SAL001',
                'sub_category_name' => 'Sample sub_category_name',
                'updated_at' => now(),
                'created_at' => '2025-07-23 10:38:32',
            ],
            [
                'id' => 2,
                'category_code' => 'SAL002',
                'sub_category_code' => 'SAL002',
                'sub_category_name' => 'Sample sub_category_name',
                'updated_at' => now(),
                'created_at' => '2025-07-23 11:38:32',
            ],
        ];

        foreach ($data as $row) {
            DB::table('SALARIES_AND_WAGES')->insert($row);
    }
}
}