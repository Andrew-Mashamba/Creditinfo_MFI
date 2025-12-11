<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsurancelistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('insurancelist')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Sample name',
                'type' => 'standard',
                'value' => 1000.00,
                'calculating_type' => 'TYPE_B',
                'source' => 'Sample source 1',
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Sample name',
                'type' => 'standard',
                'value' => 1000.00,
                'calculating_type' => 'TYPE_C',
                'source' => 'Sample source 2',
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('insurancelist')->insert($row);
    }
}
}