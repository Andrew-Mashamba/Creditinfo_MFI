<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobpostingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('job_postings')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'job_title' => 'Sample job_title 1',
                'department' => 'Sample department 1',
                'location' => 'Sample location 1',
                'job_type' => 'Full-time',
                'description' => 'This is a sample description for job_postings record 1',
                'requirements' => 'Sample requirements 1',
                'salary' => 50000.00,
                'status' => 'open',
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'job_title' => 'Sample job_title 2',
                'department' => 'Sample department 2',
                'location' => 'Sample location 2',
                'job_type' => 'Part-time',
                'description' => 'This is a sample description for job_postings record 2',
                'requirements' => 'Sample requirements 2',
                'salary' => 75000.00,
                'status' => 'closed',
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('job_postings')->insert($row);
    }
}
}