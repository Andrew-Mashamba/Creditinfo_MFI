<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('banks')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'bank_number' => 000001,
                'bank_name' => 'Sample bank_name',
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'bank_number' => 000002,
                'bank_name' => 'Sample bank_name',
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('banks')->insert($row);
    }
}
}