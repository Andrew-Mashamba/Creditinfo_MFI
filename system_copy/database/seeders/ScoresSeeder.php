<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('scores')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'client_id' => 1,
                'date' => '2025-07-24',
                'grade' => 'Sample grade 1',
                'score' => 80,
                'trend' => 'Sample trend 1',
                'reasons' => json_encode(['reason1' => 'High payment history', 'reason2' => 'Good credit utilization']),
                'probability_of_default' => 0.05,
                'created_at' => '2025-07-23 10:38:41',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'client_id' => 2,
                'date' => '2025-07-25',
                'grade' => 'Sample grade 2',
                'score' => 80,
                'trend' => 'Sample trend 2',
                'reasons' => json_encode(['reason1' => 'Recent late payments', 'reason2' => 'High debt ratio']),
                'probability_of_default' => 0.15,
                'created_at' => '2025-07-23 11:38:41',
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
            
            DB::table('scores')->insert($row);
        }
}
}