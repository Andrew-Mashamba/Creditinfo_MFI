<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContributionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('Contributions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Sample name',
                'amount' => 1000,
                'updated_at' => now(),
                'created_at' => '2025-07-23 10:38:31',
                'status' => 'pending',
            ],
            [
                'id' => 2,
                'name' => 'Sample name',
                'amount' => 2000,
                'updated_at' => now(),
                'created_at' => '2025-07-23 11:38:31',
                'status' => 'inactive',
            ],
        ];

        foreach ($data as $row) {
            DB::table('Contributions')->insert($row);
    }
}
}