<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('districts')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'district_name' => 'Meru District',
                'region_id' => 1,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 2,
                'district_name' => 'Arusha City',
                'region_id' => 1,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 3,
                'district_name' => 'Arusha District',
                'region_id' => 1,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 4,
                'district_name' => 'Karatu District',
                'region_id' => 1,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 5,
                'district_name' => 'Longido District',
                'region_id' => 1,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 6,
                'district_name' => 'Monduli District',
                'region_id' => 1,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 7,
                'district_name' => 'Ngorongoro District',
                'region_id' => 1,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 8,
                'district_name' => 'Ilala Municipal',
                'region_id' => 2,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 9,
                'district_name' => 'Kinondoni Municipal',
                'region_id' => 2,
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 10,
                'district_name' => 'Temeke Municipal',
                'region_id' => 2,
                'created_at' => '2025-07-17 16:25:50',
                'updated_at' => '2025-07-17 16:25:50',
            ],
            [
                'id' => 11,
                'district_name' => 'Kigamboni Municipal',
                'region_id' => 2,
                'created_at' => '2025-07-17 16:25:50',
                'updated_at' => '2025-07-17 16:25:50',
            ],
            [
                'id' => 12,
                'district_name' => 'Ubungo Municipal',
                'region_id' => 2,
                'created_at' => '2025-07-17 16:25:50',
                'updated_at' => '2025-07-17 16:25:50',
            ],
        ];

        foreach ($data as $row) {
            DB::table('districts')->insert($row);
    }
}
}