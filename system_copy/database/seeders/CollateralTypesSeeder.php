<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollateralTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('collateral_types')->truncate();

        // Insert existing data (without specifying IDs to avoid conflicts)
        $data = [
            [
                'loan_product_id' => 1,
                'type' => 'Business Premises',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'loan_product_id' => 1,
                'type' => 'Business Equipment',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'loan_product_id' => 1,
                'type' => 'Business Inventory',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                'loan_product_id' => 1,
                'type' => 'Business Vehicle',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 5,
                'loan_product_id' => 2,
                'type' => 'Personal Vehicle',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 6,
                'loan_product_id' => 2,
                'type' => 'Household Items',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 7,
                'loan_product_id' => 2,
                'type' => 'Electronics',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 8,
                'loan_product_id' => 3,
                'type' => 'Residential Property',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 9,
                'loan_product_id' => 3,
                'type' => 'Commercial Property',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 10,
                'loan_product_id' => 3,
                'type' => 'Land',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 11,
                'loan_product_id' => 4,
                'type' => 'Agricultural Land',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 12,
                'loan_product_id' => 4,
                'type' => 'Farm Equipment',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 13,
                'loan_product_id' => 4,
                'type' => 'Livestock',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
            [
                // Removed hardcoded ID - let database auto-increment handle it
                // 'id' => 14,
                'loan_product_id' => 4,
                'type' => 'Crops',
                'created_at' => '2025-07-17 16:25:51',
                'updated_at' => '2025-07-17 16:25:51',
            ],
        ];

        foreach ($data as $row) {
            DB::table('collateral_types')->insert($row);
    }
}
}