<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('project')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'tender_no' => 'Sample tender_no 1',
                'procuring_entity' => 'Sample procuring_entity 1',
                'supplier_name' => 'Sample supplier_name',
                'award_date' => '2025-07-24',
                'award_amount' => 1000,
                'lot_name' => 'Sample lot_name',
                'expected_end_date' => '2025-07-24',
                'project_summary' => 'Sample project_summary 1',
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tender_no' => 'Sample tender_no 2',
                'procuring_entity' => 'Sample procuring_entity 2',
                'supplier_name' => 'Sample supplier_name',
                'award_date' => '2025-07-25',
                'award_amount' => 2000,
                'lot_name' => 'Sample lot_name',
                'expected_end_date' => '2025-07-25',
                'project_summary' => 'Sample project_summary 2',
                'status' => 'inactive',
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('project')->insert($row);
    }
}
}