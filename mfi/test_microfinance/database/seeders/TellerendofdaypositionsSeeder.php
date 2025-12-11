<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TellerendofdaypositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('teller_end_of_day_positions')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'institution_id' => 1,
                'employee_id' => 1,
                'branch_id' => 1,
                'til_number' => 000001,
                'til_account' => 10,
                'til_balance' => 1000,
                'tiller_cash_at_hand' => 'Sample tiller_cash_at_hand 1',
                'business_date' => '2025-07-24',
                'message' => 'Sample message 1',
                'status' => 'pending',
                'created_at' => '2025-07-23 10:38:43',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'institution_id' => 1,
                'employee_id' => 2,
                'branch_id' => 1,
                'til_number' => 000002,
                'til_account' => 20,
                'til_balance' => 2000,
                'tiller_cash_at_hand' => 'Sample tiller_cash_at_hand 2',
                'business_date' => '2025-07-25',
                'message' => 'Sample message 2',
                'status' => 'inactive',
                'created_at' => '2025-07-23 11:38:43',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('teller_end_of_day_positions')->insert($row);
    }
}
}