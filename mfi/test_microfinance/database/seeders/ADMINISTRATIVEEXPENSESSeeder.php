<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ADMINISTRATIVEEXPENSESSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('ADMINISTRATIVE_EXPENSES')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'category_code' => 'ADM001',
                'sub_category_code' => 'ADM001',
                'sub_category_name' => 'Sample sub_category_name',
                'updated_at' => now(),
                'created_at' => '2025-07-23 10:38:31',
            ],
            [
                'id' => 2,
                'category_code' => 'ADM002',
                'sub_category_code' => 'ADM002',
                'sub_category_name' => 'Sample sub_category_name',
                'updated_at' => now(),
                'created_at' => '2025-07-23 11:38:31',
            ],
        ];

        foreach ($data as $row) {
            DB::table('ADMINISTRATIVE_EXPENSES')->insert($row);
    }
}
}