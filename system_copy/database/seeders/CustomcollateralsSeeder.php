<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomcollateralsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('custom_collaterals')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'inputs' => 'Sample inputs 1',
                'loan_id' => 1,
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'inputs' => 'Sample inputs 2',
                'loan_id' => 2,
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('custom_collaterals')->insert($row);
    }
}
}