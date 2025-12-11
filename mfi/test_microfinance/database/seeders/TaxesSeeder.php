<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('taxes')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Sample name',
                'amount' => 1000,
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Sample name',
                'amount' => 2000,
                'status' => 'inactive',
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('taxes')->insert($row);
    }
}
}