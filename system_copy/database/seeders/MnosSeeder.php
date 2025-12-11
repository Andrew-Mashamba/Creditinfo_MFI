<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MnosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('mnos')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'mno_name' => 'Airtel',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 2,
                'mno_name' => 'Vodacom',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 3,
                'mno_name' => 'Tigo',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 4,
                'mno_name' => 'Zantel',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 5,
                'mno_name' => 'Halotel',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 6,
                'mno_name' => 'TTCL',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 7,
                'mno_name' => 'Smile',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
            [
                'id' => 8,
                'mno_name' => 'Smart',
                'created_at' => '2025-07-17 16:25:49',
                'updated_at' => '2025-07-17 16:25:49',
            ],
        ];

        foreach ($data as $row) {
            DB::table('mnos')->insert($row);
    }
}
}