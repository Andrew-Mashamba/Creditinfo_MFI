<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialpositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('financial_position')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'end_of_business_year' => date('Y-12-31'),
                'created_at' => '2025-07-23 10:38:36',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'end_of_business_year' => date('Y-12-31'),
                'created_at' => '2025-07-23 11:38:36',
                'updated_at' => now(),
            ],
        ];

                foreach ($data as $row) {
            // Validate user references
            $userFields = ['user_id', 'created_by', 'updated_by', 'deleted_by', 'approved_by', 'rejected_by'];
            foreach ($userFields as $field) {
                if (isset($row[$field]) && $row[$field]) {
                    $userExists = DB::table('users')->where('id', $row[$field])->exists();
                    if (!$userExists) {
                        $firstUser = DB::table('users')->first();
                        $row[$field] = $firstUser ? $firstUser->id : null;
                    }
                }
            }
            
            // Validate client references
            if (isset($row['client_id']) && $row['client_id']) {
                $clientExists = DB::table('clients')->where('id', $row['client_id'])->exists();
                if (!$clientExists) {
                    $firstClient = DB::table('clients')->first();
                    $row['client_id'] = $firstClient ? $firstClient->id : null;
                }
            }
            
            // Validate account references
            if (isset($row['account_id']) && $row['account_id']) {
                $accountExists = DB::table('accounts')->where('id', $row['account_id'])->exists();
                if (!$accountExists) {
                    $firstAccount = DB::table('accounts')->first();
                    $row['account_id'] = $firstAccount ? $firstAccount->id : null;
                }
            }
            
            DB::table('financial_position')->insert($row);
        }
}
}