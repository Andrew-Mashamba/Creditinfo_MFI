<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumenttypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('document_types')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'document_id' => 1,
                'document_name' => 'Sample document_name Document_types 1',
                'collateral_type' => 'TYPE_B',
            ],
            [
                'document_id' => 2,
                'document_name' => 'Sample document_name Document_types 2',
                'collateral_type' => 'TYPE_C',
            ],
        ];

        foreach ($data as $row) {
            DB::table('document_types')->insert($row);
    }
}
}