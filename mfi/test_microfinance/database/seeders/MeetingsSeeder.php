<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('meetings')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'committee_id' => 1,
                'title' => 'Sample title 1',
                'agenda' => 'Sample agenda 1',
                'meeting_date' => '2025-07-24',
                'location' => 'Sample location 1',
                'notes' => 'Sample notes 1',
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'committee_id' => 2,
                'title' => 'Sample title 2',
                'agenda' => 'Sample agenda 2',
                'meeting_date' => '2025-07-25',
                'location' => 'Sample location 2',
                'notes' => 'Sample notes 2',
                'created_at' => '2025-07-23 11:38:39',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('meetings')->insert($row);
    }
}
}