<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnearneddeferredrevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('unearned_deferred_revenue')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'source_account_id' => 1,
                'destination_account_id' => 1,
                'status' => 'pending',
                'is_recognized' => false,
                'is_delivery' => false,
                'description' => 'This is a sample description for unearned_deferred_revenue record 1',
                'name' => 'Sample name',
                'address' => 'Sample Address 1, unearned_deferred_revenue Street',
                'phone' => +255700000001,
                'email' => 'sample1@unearned_deferred_revenue.com',
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
                'amount' => 1000,
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'source_account_id' => 2,
                'destination_account_id' => 2,
                'status' => 'inactive',
                'is_recognized' => true,
                'is_delivery' => true,
                'description' => 'This is a sample description for unearned_deferred_revenue record 2',
                'name' => 'Sample name',
                'address' => 'Sample Address 2, unearned_deferred_revenue Street',
                'phone' => +255700000002,
                'email' => 'sample2@unearned_deferred_revenue.com',
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
                'amount' => 2000,
            ],
        ];

        foreach ($data as $row) {
            DB::table('unearned_deferred_revenue')->insert($row);
    }
}
}