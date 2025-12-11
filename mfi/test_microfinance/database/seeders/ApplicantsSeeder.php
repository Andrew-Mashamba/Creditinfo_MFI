<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('applicants')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'job_posting_id' => 1,
                'name' => 'Sample name',
                'email' => 'sample1@applicants.com',
                'phone' => +255700000001,
                'resume_path' => 'Sample resume_path 1',
                'status' => 'new',
                'notes' => 'Sample notes 1',
                'created_at' => '2025-07-23 10:38:32',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'job_posting_id' => 2,
                'name' => 'Sample name',
                'email' => 'sample2@applicants.com',
                'phone' => +255700000002,
                'resume_path' => 'Sample resume_path 2',
                'status' => 'reviewing',
                'notes' => 'Sample notes 2',
                'created_at' => '2025-07-23 11:38:32',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('applicants')->insert($row);
    }
}
}