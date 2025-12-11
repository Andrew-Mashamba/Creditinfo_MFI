<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanstagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loan_stages')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_product_id' => 1,
                'stage_id' => 1,
                'stage_type' => 'TYPE_B',
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'loan_product_id' => 2,
                'stage_id' => 2,
                'stage_type' => 'TYPE_C',
                'status' => 'inactive',
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_stages')->insert($row);
    }
}
}