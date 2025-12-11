<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('institution_files')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'file_id' => 1,
                'file_name' => 'Sample file_name',
                'file_path' => 'Sample file_path 1',
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'file_id' => 2,
                'file_name' => 'Sample file_name',
                'file_path' => 'Sample file_path 2',
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('institution_files')->insert($row);
    }
}
}