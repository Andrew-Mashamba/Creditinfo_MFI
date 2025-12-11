<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembercategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('member_categories')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'name' => 'Sample name',
                'repayment_date' => '2025-07-24',
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Sample name',
                'repayment_date' => '2025-07-25',
                'created_at' => '2025-07-23 11:38:39',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('member_categories')->insert($row);
    }
}
}