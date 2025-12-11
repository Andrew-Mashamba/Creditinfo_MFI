<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChargesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('charges')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'institution_number' => 000001,
                'branch_number' => 000001,
                'charge_number' => 000001,
                'charge_name' => 'Sample charge_name',
                'charge_type' => 'TYPE_B',
                'flat_charge_amount' => 1000,
                'percentage_charge_amount' => 1000,
                'status' => 10,
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
                'product_id' => 1,
            ],
            [
                'id' => 2,
                'institution_number' => 000002,
                'branch_number' => 000002,
                'charge_number' => 000002,
                'charge_name' => 'Sample charge_name',
                'charge_type' => 'TYPE_C',
                'flat_charge_amount' => 2000,
                'percentage_charge_amount' => 2000,
                'status' => 20,
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
                'product_id' => 2,
            ],
        ];

        foreach ($data as $row) {
            DB::table('charges')->insert($row);
    }
}
}