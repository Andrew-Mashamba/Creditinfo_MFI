<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvestmenttypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('investment_types')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'investment_type' => 'TYPE_B',
                'investment_name' => 'Sample investment_name',
                'description' => 'This is a sample description for investment_types record 1',
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'investment_type' => 'TYPE_C',
                'investment_name' => 'Sample investment_name',
                'description' => 'This is a sample description for investment_types record 2',
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('investment_types')->insert($row);
    }
}
}