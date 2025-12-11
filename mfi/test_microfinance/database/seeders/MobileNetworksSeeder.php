<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileNetworksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert mobile networks
        $networks = [
            [
                'id' => 1,
                'mno_name' => 'Airtel',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'mno_name' => 'Vodacom',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'mno_name' => 'Tigo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'mno_name' => 'Zantel',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'mno_name' => 'Halotel',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'mno_name' => 'TTCL',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'mno_name' => 'Smile',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'mno_name' => 'Smart',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($networks as $network) {
            DB::table('mnos')->updateOrInsert(
                ['id' => $network['id']],
                $network
            );
    }
}
}