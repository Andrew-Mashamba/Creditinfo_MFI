<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadershipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('leaderships')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'full_name' => 'John Doe',
                'type' => 'standard',
                'image' => 'Sample image 1',
                'position' => 'Sample position 1',
                'leaderDescriptions' => 'This is a sample description for leaderships record 1',
                'approval_option' => 'APPROVE',
                'startDate' => '2025-07-24',
                'endDate' => '2025-07-24',
                'member_number' => 000001,
                'is_signatory' => false,
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'full_name' => 'Jane Smith',
                'type' => 'standard',
                'image' => 'Sample image 2',
                'position' => 'Sample position 2',
                'leaderDescriptions' => 'This is a sample description for leaderships record 2',
                'approval_option' => 'REVIEW',
                'startDate' => '2025-07-25',
                'endDate' => '2025-07-25',
                'member_number' => 000002,
                'is_signatory' => true,
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('leaderships')->insert($row);
    }
}
}