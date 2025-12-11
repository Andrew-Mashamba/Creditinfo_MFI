<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TendersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('tenders')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'tender_description' => 'This is a sample description for tenders record 1',
                'tender_name' => 'Sample tender_name',
                'status' => 'pending',
                'tender_number' => 000001,
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tender_description' => 'This is a sample description for tenders record 2',
                'tender_name' => 'Sample tender_name',
                'status' => 'inactive',
                'tender_number' => 000002,
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('tenders')->insert($row);
    }
}
}