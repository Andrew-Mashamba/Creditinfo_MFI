<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashInTransitProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('cash_in_transit_providers')->truncate();

        // Insert existing data
        $data = [
            [
                'id' => 1,
                'name' => 'SecureTransit Solutions Ltd',
                'company_code' => 'STS001',
                'contact_person' => 'John Mwangi',
                'phone_number' => '+255 712 345 678',
                'email' => 'info@securetransit.co.tz',
                'address' => 'Plot 123, Uhuru Street, Dar es Salaam',
                'license_number' => 'CIT-LIC-2024-001',
                'license_expiry_date' => '2026-12-31',
                'status' => 'ACTIVE',
                'service_fee_percentage' => 1.50,
                'minimum_fee' => 50000.00,
                'service_areas' => '["Dar es Salaam","Dodoma","Arusha","Mwanza"]',
                'notes' => 'Primary CIT provider with nationwide coverage',
                'created_at' => '2025-07-17 16:25:46',
                'updated_at' => '2025-07-17 16:25:46',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'SafeCash Transport Services',
                'company_code' => 'SCT002',
                'contact_person' => 'Mary Kimani',
                'phone_number' => '+255 754 567 890',
                'email' => 'operations@safecash.co.tz',
                'address' => 'Mikocheni Light Industrial Area, Dar es Salaam',
                'license_number' => 'CIT-LIC-2024-002',
                'license_expiry_date' => '2025-06-30',
                'status' => 'ACTIVE',
                'service_fee_percentage' => 1.75,
                'minimum_fee' => 75000.00,
                'service_areas' => '["Dar es Salaam","Pwani","Morogoro"]',
                'notes' => 'Regional provider specializing in coastal areas',
                'created_at' => '2025-07-17 16:25:46',
                'updated_at' => '2025-07-17 16:25:46',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Armored Cash Logistics',
                'company_code' => 'ACL003',
                'contact_person' => 'David Msemo',
                'phone_number' => '+255 713 789 012',
                'email' => 'contact@armoredcash.co.tz',
                'address' => 'Ubungo Industrial Area, Dar es Salaam',
                'license_number' => 'CIT-LIC-2024-003',
                'license_expiry_date' => '2027-03-15',
                'status' => 'ACTIVE',
                'service_fee_percentage' => 1.25,
                'minimum_fee' => 40000.00,
                'service_areas' => '["Northern Zone","Arusha","Kilimanjaro","Tanga"]',
                'notes' => 'Specialized in northern Tanzania operations',
                'created_at' => '2025-07-17 16:25:46',
                'updated_at' => '2025-07-17 16:25:46',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Rapid Security Transport',
                'company_code' => 'RST004',
                'contact_person' => 'Grace Mollel',
                'phone_number' => '+255 765 432 109',
                'email' => 'info@rapidsecurity.co.tz',
                'address' => 'Mbeya Business District, Mbeya',
                'license_number' => 'CIT-LIC-2024-004',
                'license_expiry_date' => '2025-11-20',
                'status' => 'INACTIVE',
                'service_fee_percentage' => 2.00,
                'minimum_fee' => 60000.00,
                'service_areas' => '["Southern Highlands","Mbeya","Iringa","Njombe"]',
                'notes' => 'Currently suspended pending license renewal',
                'created_at' => '2025-07-17 16:25:46',
                'updated_at' => '2025-07-17 16:25:46',
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'name' => 'Elite Vault Services',
                'company_code' => 'EVS005',
                'contact_person' => 'James Mahenge',
                'phone_number' => '+255 784 123 456',
                'email' => 'service@elitevault.co.tz',
                'address' => 'Mwanza Commercial District, Mwanza',
                'license_number' => 'CIT-LIC-2024-005',
                'license_expiry_date' => '2026-08-10',
                'status' => 'ACTIVE',
                'service_fee_percentage' => 1.60,
                'minimum_fee' => 55000.00,
                'service_areas' => '["Lake Zone","Mwanza","Shinyanga","Simiyu","Geita"]',
                'notes' => 'Lake zone specialist with modern armored vehicles',
                'created_at' => '2025-07-17 16:25:46',
                'updated_at' => '2025-07-17 16:25:46',
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('cash_in_transit_providers')->insert($row);
        }
    }
}