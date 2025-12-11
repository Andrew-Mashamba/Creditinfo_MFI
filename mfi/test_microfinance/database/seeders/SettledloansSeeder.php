<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettledloansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('settled_loans')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'loan_array_id' => 1,
                'amount' => 1000,
                'institution' => 'Sample institution 1',
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
                'is_selected' => false,
                'account' => 10,
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'loan_array_id' => 2,
                'amount' => 2000,
                'institution' => 'Sample institution 2',
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
                'is_selected' => true,
                'account' => 20,
            ],
        ];

        foreach ($data as $row) {
            DB::table('settled_loans')->insert($row);
    }
}
}