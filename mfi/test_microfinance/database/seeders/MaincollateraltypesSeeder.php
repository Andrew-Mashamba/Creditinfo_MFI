<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaincollateraltypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('main_collateral_types')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'main_type_name' => 'Sample main_type_name',
            ],
            [
                'id' => 2,
                'main_type_name' => 'Sample main_type_name',
            ],
        ];

        foreach ($data as $row) {
            DB::table('main_collateral_types')->insert($row);
    }
}
}