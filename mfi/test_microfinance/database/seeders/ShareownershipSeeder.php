<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShareownershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('share_ownership')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'client_number' => '000001',
                'total_value' => 1000.00,
                'number_of_members' => 000001,
                'end_business_year_date' => '2025-07-24',
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'client_number' => '000002',
                'total_value' => 1000.00,
                'number_of_members' => 000002,
                'end_business_year_date' => '2025-07-25',
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('share_ownership')->insert($row);
    }
}
}