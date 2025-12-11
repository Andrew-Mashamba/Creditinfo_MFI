<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('regions')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'region_name' => 'Arusha',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 2,
                'region_name' => 'Dar es Salaam',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 3,
                'region_name' => 'Dodoma',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 4,
                'region_name' => 'Geita',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 5,
                'region_name' => 'Iringa',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 6,
                'region_name' => 'Kagera',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 7,
                'region_name' => 'Katavi',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 8,
                'region_name' => 'Kigoma',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 9,
                'region_name' => 'Kilimanjaro',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 10,
                'region_name' => 'Lindi',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 11,
                'region_name' => 'Manyara',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 12,
                'region_name' => 'Mara',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 13,
                'region_name' => 'Mbeya',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 14,
                'region_name' => 'Morogoro',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 15,
                'region_name' => 'Mtwara',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 16,
                'region_name' => 'Mwanza',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 17,
                'region_name' => 'Njombe',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 18,
                'region_name' => 'Pemba Kaskazini',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 19,
                'region_name' => 'Pemba Kusini',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 20,
                'region_name' => 'Pwani',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 21,
                'region_name' => 'Rukwa',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 22,
                'region_name' => 'Ruvuma',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 23,
                'region_name' => 'Shinyanga',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 24,
                'region_name' => 'Simiyu',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 25,
                'region_name' => 'Singida',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 26,
                'region_name' => 'Songwe',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 27,
                'region_name' => 'Tabora',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 28,
                'region_name' => 'Tanga',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 29,
                'region_name' => 'Unguja Kaskazini',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 30,
                'region_name' => 'Unguja Mjini Magharibi',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 31,
                'region_name' => 'Unguja Kusini',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
        ];

        foreach ($data as $row) {
            DB::table('regions')->insert($row);
    }
}
}