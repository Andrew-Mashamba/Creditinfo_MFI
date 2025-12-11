<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('wards')->truncate();

        // Get some district IDs
        $districts = DB::table('districts')->limit(2)->get();
        if ($districts->isEmpty()) {
            $this->command->warn('No districts found. Skipping WardsSeeder.');
            return;
        }
        
        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'ward_name' => 'Kivukoni',
                'district_id' => $districts[0]->id,
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => '2025-07-23 10:38:44',
            ],
            [
                'id' => 2,
                'ward_name' => 'Kariakoo',
                'district_id' => isset($districts[1]) ? $districts[1]->id : $districts[0]->id,
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => '2025-07-23 10:38:44',
            ],
        ];

        foreach ($data as $row) {
            DB::table('wards')->insert($row);
    }
}
}