<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if branches already exist to avoid duplicates
        if (DB::table('branches')->count() > 0) {
            echo "Branches already exist, skipping BranchesSeeder\n";
            return;
        }

        // Insert branch data
        $data = [
            [
                // 'id' => 1,
                'name' => 'Headquarters',
                'region' => 'Dar es Salaam',
                'wilaya' => 'Ilala',
                'branch_number' => 01,
                'status' => 'active',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
                'email' => 'hq@nbc.co.tz',
                'phone_number' => '255000000000',
                'address' => 'Main Office',
                'branch_type' => 'MAIN',
                'opening_date' => null,
                'branch_manager' => null,
                'operating_hours' => null,
                'services_offered' => null,
                'cit_provider_id' => null,
                'vault_account' => null,
                'till_account' => null,
                'petty_cash_account' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('branches')->insert($row);
        }
    }
}