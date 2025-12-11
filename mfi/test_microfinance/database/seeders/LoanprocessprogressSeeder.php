<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanprocessprogressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loan_process_progress')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'completed_tabs' => json_encode(['value' => '["client"]']),
                'tab_data' => null,
                'created_at' => null,
                'updated_at' => '2025-07-18 10:17:12',
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'completed_tabs' => json_encode(['value' => '["client","guarantor","addDocument","assessment"]']),
                'tab_data' => null,
                'created_at' => null,
                'updated_at' => '2025-07-23 07:26:51',
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_process_progress')->insert($row);
    }
}
}