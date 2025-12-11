<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SharesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('shares')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 5,
                'type' => 'Ordinary Shares',
                'name' => 'Ordinary Shares',
                'description' => null,
                'summary' => 'Basic membership shares that give voting rights and dividend eligibility',
                'par_value' => 1000.00,
                'price_per_share' => 1000.00,
                'authorized_shares' => 1000000,
                'issued_shares' => 0,
                'paid_up_shares' => 0,
                'is_active' => true,
                'created_at' => '2025-07-18 04:25:04',
                'updated_at' => '2025-07-18 04:25:04',
                'deleted_at' => null,
                'status' => 'active',
            ],
            [
                'id' => 6,
                'type' => 'Preference Shares',
                'name' => 'Preference Shares',
                'description' => null,
                'summary' => 'Shares with priority in dividend payments and capital repayment',
                'par_value' => 1000.00,
                'price_per_share' => 1000.00,
                'authorized_shares' => 1000000,
                'issued_shares' => 0,
                'paid_up_shares' => 0,
                'is_active' => true,
                'created_at' => '2025-07-18 04:25:04',
                'updated_at' => '2025-07-18 04:25:04',
                'deleted_at' => null,
                'status' => 'active',
            ],
            [
                'id' => 7,
                'type' => 'Development Shares',
                'name' => 'Development Shares',
                'description' => null,
                'summary' => 'Shares specifically for SACCO development and infrastructure',
                'par_value' => 1000.00,
                'price_per_share' => 1000.00,
                'authorized_shares' => 1000000,
                'issued_shares' => 0,
                'paid_up_shares' => 0,
                'is_active' => true,
                'created_at' => '2025-07-18 04:25:04',
                'updated_at' => '2025-07-18 04:25:04',
                'deleted_at' => null,
                'status' => 'active',
            ],
            [
                'id' => 8,
                'type' => 'Bonus Shares',
                'name' => 'Bonus Shares',
                'description' => null,
                'summary' => 'Additional shares issued as a bonus to existing shareholders',
                'par_value' => 1000.00,
                'price_per_share' => 1000.00,
                'authorized_shares' => 1000000,
                'issued_shares' => 0,
                'paid_up_shares' => 0,
                'is_active' => true,
                'created_at' => '2025-07-18 04:25:04',
                'updated_at' => '2025-07-18 04:25:04',
                'deleted_at' => null,
                'status' => 'active',
            ],
            [
                'id' => 9,
                'type' => 'Rights Shares',
                'name' => 'Rights Shares',
                'description' => null,
                'summary' => 'Shares offered to existing members at a preferential rate',
                'par_value' => 1000.00,
                'price_per_share' => 1000.00,
                'authorized_shares' => 1000000,
                'issued_shares' => 0,
                'paid_up_shares' => 0,
                'is_active' => true,
                'created_at' => '2025-07-18 04:25:04',
                'updated_at' => '2025-07-18 04:25:04',
                'deleted_at' => null,
                'status' => 'active',
            ],
            [
                'id' => 10,
                'type' => 'Employee Shares',
                'name' => 'Employee Shares',
                'description' => null,
                'summary' => 'Shares allocated to SACCO employees as part of their benefits',
                'par_value' => 1000.00,
                'price_per_share' => 1000.00,
                'authorized_shares' => 1000000,
                'issued_shares' => 0,
                'paid_up_shares' => 0,
                'is_active' => true,
                'created_at' => '2025-07-18 04:25:04',
                'updated_at' => '2025-07-18 04:25:04',
                'deleted_at' => null,
                'status' => 'active',
            ],
            [
                'id' => 11,
                'type' => 'Special Purpose Shares',
                'name' => 'Special Purpose Shares',
                'description' => null,
                'summary' => 'Shares created for specific SACCO projects or initiatives',
                'par_value' => 1000.00,
                'price_per_share' => 1000.00,
                'authorized_shares' => 1000000,
                'issued_shares' => 0,
                'paid_up_shares' => 0,
                'is_active' => true,
                'created_at' => '2025-07-18 04:25:04',
                'updated_at' => '2025-07-18 04:25:04',
                'deleted_at' => null,
                'status' => 'active',
            ],
        ];

        foreach ($data as $row) {
            DB::table('shares')->insert($row);
    }
}
}