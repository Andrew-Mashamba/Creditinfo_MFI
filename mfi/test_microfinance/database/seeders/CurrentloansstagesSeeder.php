<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrentloansstagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('current_loans_stages')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'product_id' => 1,
                'stage_id' => 1,
                'stage_type' => 'TYPE_B',
                'stage_name' => 'Sample stage_name',
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'product_id' => 2,
                'stage_id' => 2,
                'stage_type' => 'TYPE_C',
                'stage_name' => 'Sample stage_name',
                'status' => 'inactive',
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('current_loans_stages')->insert($row);
    }
}
}