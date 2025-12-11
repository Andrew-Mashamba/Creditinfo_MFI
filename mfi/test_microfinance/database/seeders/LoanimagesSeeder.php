<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanimagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('loan_images')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'loan_id' => '1752821326',
                'category' => 'add-document',
                'filename' => '1752821849_IAalfNuj_PARTNER-Karisani_NDA__1_.docx',
                'url' => 'LoanDocument/1752821849_IAalfNuj_PARTNER-Karisani_NDA__1_.docx',
                'document_descriptions' => 'SFGWER',
                'created_at' => '2025-07-18 06:57:29',
                'updated_at' => '2025-07-18 06:57:29',
                'document_category' => null,
                'file_size' => null,
                'mime_type' => null,
                'original_name' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('loan_images')->insert($row);
    }
}
}