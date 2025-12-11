<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('inventories')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'item_name' => 'Sample item_name',
                'item_amount' => 1000,
                'item_expiration_date' => '2025-07-24',
                'status' => 'pending',
                'item_description' => 'This is a sample description for inventories record 1',
                'created_at' => '2025-07-23 10:38:37',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'item_name' => 'Sample item_name',
                'item_amount' => 2000,
                'item_expiration_date' => '2025-07-25',
                'status' => 'inactive',
                'item_description' => 'This is a sample description for inventories record 2',
                'created_at' => '2025-07-23 11:38:37',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('inventories')->insert($row);
    }
}
}