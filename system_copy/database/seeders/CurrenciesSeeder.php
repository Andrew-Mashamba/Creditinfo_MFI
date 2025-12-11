<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('currencies')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'currency_id' => 1,
                'currency_name' => 'Sample currency_name',
                'currency_short_name' => 'Sample currency_short_name',
                'exchange_rate' => 5.5,
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'currency_id' => 2,
                'currency_name' => 'Sample currency_name',
                'currency_short_name' => 'Sample currency_short_name',
                'exchange_rate' => 11,
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('currencies')->insert($row);
    }
}
}