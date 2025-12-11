<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovablepropertytypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('movable_property_types')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'main_type_id' => 1,
                'type_name' => 'Sample type_name',
                'is_landed' => false,
                'requires_insurance' => true,
                'requires_valuation' => true,
                'is_matrimonial' => false,
            ],
            [
                'id' => 2,
                'main_type_id' => 2,
                'type_name' => 'Sample type_name',
                'is_landed' => true,
                'requires_insurance' => true,
                'requires_valuation' => true,
                'is_matrimonial' => true,
            ],
        ];

        foreach ($data as $row) {
            DB::table('movable_property_types')->insert($row);
    }
}
}