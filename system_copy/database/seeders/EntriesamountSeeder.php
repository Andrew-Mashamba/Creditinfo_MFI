<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntriesamountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('entries_amount')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'entry_id' => 1,
                'account_id' => 1,
                'amount' => 1000,
                'created_at' => '2025-07-23 10:38:35',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'entry_id' => 2,
                'account_id' => 2,
                'amount' => 2000,
                'created_at' => '2025-07-23 11:38:35',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Validate optional user references
            $userFields = ['user_id', 'created_by', 'updated_by', 'approved_by'];
            foreach ($userFields as $field) {
                if (isset($row[$field]) && $row[$field]) {
                    $userExists = DB::table('users')->where('id', $row[$field])->exists();
                    if (!$userExists) {
                        $firstUser = DB::table('users')->first();
                        $row[$field] = $firstUser ? $firstUser->id : null;
                    }
                }
            }
            
            DB::table('entries_amount')->insert($row);
        }
}
}