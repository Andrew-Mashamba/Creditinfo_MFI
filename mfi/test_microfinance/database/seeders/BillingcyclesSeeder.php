<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingcyclesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('billing_cycles')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'month_year' => '2025-07',
                'start_date' => '2025-07-24',
                'end_date' => '2025-07-24',
                'status' => 'Open',
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'month_year' => '2025-08',
                'start_date' => '2025-07-25',
                'end_date' => '2025-07-25',
                'status' => 'Closed',
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('billing_cycles')->insert($row);
    }
}
}