<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChequebooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('cheque_books')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'institution_id' => 1,
                'bank' => 1,
                'chequeBook_id' => 1,
                'remaining_leaves' => 50,
                'leave_number' => '000001',
                'branch_id' => 1,
                'status' => 'PENDING',
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'institution_id' => 1,
                'bank' => 2,
                'chequeBook_id' => 2,
                'remaining_leaves' => 100,
                'leave_number' => '000002',
                'branch_id' => 1,
                'status' => 'ACTIVE',
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('cheque_books')->insert($row);
    }
}
}