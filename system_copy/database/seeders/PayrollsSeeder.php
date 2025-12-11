<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('pay_rolls')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'employee_id' => 1,
                'pay_period_start' => '2025-07-01',
                'pay_period_end' => '2025-07-31',
                'gross_salary' => 50000.00,
                'overtime_hours' => 8,
                'payment_method' => 'Sample payment_method 1',
                'payment_date' => '2025-07-24',
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'employee_id' => 2,
                'pay_period_start' => '2025-08-01',
                'pay_period_end' => '2025-08-31',
                'gross_salary' => 50000.00,
                'overtime_hours' => 12,
                'payment_method' => 'Sample payment_method 2',
                'payment_date' => '2025-07-25',
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('pay_rolls')->insert($row);
    }
}
}