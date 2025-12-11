<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientdocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('client_documents')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'client_id' => 1,
                'document_type' => 'application letter',
                'file_path' => 'client-documents/51J2nN0FwgXAPEqeRxLRMnyY3z6DIrkuiq6CEOUg.docx',
                'original_filename' => null,
                'mime_type' => null,
                'file_size' => null,
                'description' => 'Application Letter',
                'is_verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'created_at' => '2025-07-18 04:00:30',
                'updated_at' => '2025-07-18 04:00:30',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'client_id' => 2,
                'document_type' => 'application letter',
                'file_path' => 'client-documents/30nGl4JIqSzt8PA3Nvxd8Ghk8ccf6r2IW8O7zJZ5.docx',
                'original_filename' => null,
                'mime_type' => null,
                'file_size' => null,
                'description' => 'Application Letter',
                'is_verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'created_at' => '2025-07-18 06:47:37',
                'updated_at' => '2025-07-18 06:47:37',
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('client_documents')->insert($row);
    }
}
}