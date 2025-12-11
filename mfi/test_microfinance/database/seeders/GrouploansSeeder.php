<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrouploansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('Group_loans')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'loan_id' => 1,
                'group_number' => 000001,
                'amount' => 1000,
                'member_number' => 000001,
                'status' => 'pending',
                'id' => 1,
            ],
            [
                'loan_id' => 2,
                'group_number' => 000002,
                'amount' => 2000,
                'member_number' => 000002,
                'status' => 'inactive',
                'id' => 2,
            ],
        ];

        foreach ($data as $row) {
            DB::table('Group_loans')->insert($row);
    }
}
}