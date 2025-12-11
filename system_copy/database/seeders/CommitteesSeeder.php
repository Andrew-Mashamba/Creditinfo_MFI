<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommitteesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('committees')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Credit Committee',
                'description' => 'This is a sample description for committees record 1',
                'status' => true,
                'department_id' => 1,
                'loan_category' => 'Sample loan_category 1',
                'type' => 'standard',
                'level' => 1,
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
                'deleted_at' => null,
                'parent_committee_id' => 1,
                'path' => 'Sample path 1',
            ],
            [
                'id' => 2,
                'name' => 'Risk Management Committee',
                'description' => 'This is a sample description for committees record 2',
                'status' => true,
                'department_id' => 2,
                'loan_category' => 'Sample loan_category 2',
                'type' => 'standard',
                'level' => 1,
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
                'deleted_at' => null,
                'parent_committee_id' => 2,
                'path' => 'Sample path 2',
            ],
        ];

        foreach ($data as $row) {
            DB::table('committees')->insert($row);
    }
}
}