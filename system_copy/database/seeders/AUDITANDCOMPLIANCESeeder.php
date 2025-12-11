<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AUDITANDCOMPLIANCESeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('AUDIT_AND_COMPLIANCE')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'category_code' => 'AUD001',
                'sub_category_code' => 'AUD001',
                'sub_category_name' => 'Sample sub_category_name',
                'updated_at' => now(),
                'created_at' => '2025-07-23 10:38:31',
            ],
            [
                'id' => 2,
                'category_code' => 'AUD002',
                'sub_category_code' => 'AUD002',
                'sub_category_name' => 'Sample sub_category_name',
                'updated_at' => now(),
                'created_at' => '2025-07-23 11:38:31',
            ],
        ];

        foreach ($data as $row) {
            DB::table('AUDIT_AND_COMPLIANCE')->insert($row);
    }
}
}