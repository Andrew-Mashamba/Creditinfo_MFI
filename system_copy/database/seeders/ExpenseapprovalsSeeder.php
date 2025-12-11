<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseapprovalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('expense_approvals')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'user_name' => 'Sample user_name',
                'status' => 'pending',
                'expense_id' => 1,
                'approval_level' => 'Sample approval_level 1',
                'created_at' => '2025-07-23 10:38:35',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'user_name' => 'Sample user_name',
                'status' => 'inactive',
                'expense_id' => 2,
                'approval_level' => 'Sample approval_level 2',
                'created_at' => '2025-07-23 11:38:35',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('expense_approvals')->insert($row);
    }
}
}