<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetaccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('SET session_replication_role = replica;');
        }
        
        try {
        // Clear existing data
        // DB::table('asset_accounts')->truncate(); // Commented out to avoid foreign key issues

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'major_category_code' => 'ASS001',
                'category_code' => 'ASS001',
                'category_name' => 'Sample category_name',
                'created_at' => '2025-07-23 10:38:32',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'major_category_code' => 'ASS002',
                'category_code' => 'ASS002',
                'category_name' => 'Sample category_name',
                'created_at' => '2025-07-23 11:38:32',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Use updateOrInsert to avoid foreign key conflicts
            DB::table('asset_accounts')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    
        
        
        } finally {
            // Re-enable foreign key checks
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif (DB::getDriverName() === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT;');
                }
    }
}
}