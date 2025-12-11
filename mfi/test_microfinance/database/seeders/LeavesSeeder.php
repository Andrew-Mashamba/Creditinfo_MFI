<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeavesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('leaves')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'employee_id' => 1,
                'leave_type' => 'TYPE_B',
                'start_date' => '2025-07-24',
                'end_date' => '2025-07-24',
                'status' => 'pending',
                'reason' => 'Sample reason 1',
                'description' => 'This is a sample description for leaves record 1',
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'employee_id' => 2,
                'leave_type' => 'TYPE_C',
                'start_date' => '2025-07-25',
                'end_date' => '2025-07-25',
                'status' => 'inactive',
                'reason' => 'Sample reason 2',
                'description' => 'This is a sample description for leaves record 2',
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('leaves')->insert($row);
    }
}
}