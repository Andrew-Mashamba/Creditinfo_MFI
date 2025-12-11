<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PendingregistrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('pending_registrations')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'reference_number' => '000001',
                'amount' => 1000,
                'account_id' => 1,
                'branch_id' => 1,
                'phone_number' => +255700000001,
                'status' => 'pending',
                'nida_number' => 1,
                'required_amount' => 1000,
                'created_at' => '2025-07-23 10:38:40',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'reference_number' => '000002',
                'amount' => 2000,
                'account_id' => 2,
                'branch_id' => 2,
                'phone_number' => +255700000002,
                'status' => 'inactive',
                'nida_number' => 2,
                'required_amount' => 2000,
                'created_at' => '2025-07-23 11:38:40',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('pending_registrations')->insert($row);
    }
}
}