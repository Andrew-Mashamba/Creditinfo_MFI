<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoancollateralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loan_collateral')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'collateral_type' => 'TYPE_B',
                'description' => 'This is a sample description for loan_collateral record 1',
                'value' => 1000.00,
                'location' => 'Sample location 1',
                'document_number' => 000001,
                'document_type' => 'TYPE_B',
                'insurance_policy' => 'Sample insurance_policy 1',
                'insurance_expiry' => '2026-07-23',
                'verification_status' => 'PENDING',
                'verified_by' => 1,
                'verified_at' => now(),
                'notes' => 'Sample notes 1',
                'created_at' => '2025-07-23 10:38:38',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'collateral_type' => 'TYPE_C',
                'description' => 'This is a sample description for loan_collateral record 2',
                'value' => 1000.00,
                'location' => 'Sample location 2',
                'document_number' => 000002,
                'document_type' => 'TYPE_C',
                'insurance_policy' => 'Sample insurance_policy 2',
                'insurance_expiry' => '2026-08-23',
                'verification_status' => 'INACTIVE',
                'verified_by' => 1,
                'verified_at' => now(),
                'notes' => 'Sample notes 2',
                'created_at' => '2025-07-23 11:38:38',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_collateral')->insert($row);
    }
}
}