<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuarantorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('guarantors')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'client_id' => 1,
                'guarantor_member_id' => 2,
                'relationship' => 'JHIKJ',
                'notes' => null,
                'is_active' => true,
                'guarantee_start_date' => '2025-07-18 04:00:30',
                'guarantee_end_date' => null,
                'created_at' => '2025-07-18 04:00:30',
                'updated_at' => '2025-07-18 04:00:30',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'client_id' => 2,
                'guarantor_member_id' => 1,
                'relationship' => 'GHFGHFGH',
                'notes' => null,
                'is_active' => true,
                'guarantee_start_date' => '2025-07-18 06:47:37',
                'guarantee_end_date' => null,
                'created_at' => '2025-07-18 06:47:37',
                'updated_at' => '2025-07-18 06:47:37',
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('guarantors')->insert($row);
    }
}
}