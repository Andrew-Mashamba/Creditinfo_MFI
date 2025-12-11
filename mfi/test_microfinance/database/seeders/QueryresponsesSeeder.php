<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QueryresponsesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('query_responses')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'message_id' => 1,
                'connector_id' => 1,
                'type' => 'standard',
                'message' => 'Sample message 1',
                'response_data' => json_encode(['status' => 'success', 'data' => 'Sample response 1']),
                'timestamp' => '2025-07-23 10:38:40',
                'CheckNumber' => 000001,
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'message_id' => 2,
                'connector_id' => 2,
                'type' => 'standard',
                'message' => 'Sample message 2',
                'response_data' => json_encode(['status' => 'success', 'data' => 'Sample response 2']),
                'timestamp' => '2025-07-23 11:38:40',
                'CheckNumber' => 000002,
                'created_at' => '2025-07-23 11:38:40',
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
            
            DB::table('query_responses')->insert($row);
        }
}
}