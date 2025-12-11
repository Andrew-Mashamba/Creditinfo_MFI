<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProducthaschargesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('product_has_charges')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'product_id' => 1,
                'charge_id' => 1,
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'product_id' => 2,
                'charge_id' => 2,
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('product_has_charges')->insert($row);
    }
}
}