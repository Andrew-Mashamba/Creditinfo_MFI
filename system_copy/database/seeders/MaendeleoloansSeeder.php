<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaendeleoloansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('maendeleo_loans')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'category_code' => 'MAE001',
                'sub_category_code' => 'MAE001',
                'sub_category_name' => 'Sample sub_category_name',
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'category_code' => 'MAE002',
                'sub_category_code' => 'MAE002',
                'sub_category_name' => 'Sample sub_category_name',
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('maendeleo_loans')->insert($row);
    }
}
}