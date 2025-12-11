<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('reports')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'type' => 'standard',
                'date' => '2025-07-24',
                'data' => json_encode(['value' => 'Sample data 1']),
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:41',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'type' => 'standard',
                'date' => '2025-07-25',
                'data' => json_encode(['value' => 'Sample data 2']),
                'status' => 'inactive',
                'created_at' => '2025-07-23 11:38:41',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('reports')->insert($row);
    }
}
}