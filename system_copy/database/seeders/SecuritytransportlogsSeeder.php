<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecuritytransportlogsSeeder extends Seeder
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
        DB::table('security_transport_logs')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'transfer_reference' => 'Sample transfer_reference 1',
                'transfer_type' => 'BANK_TO_VAULT',
                'amount' => 1000,
                'currency' => 'TZS',
                'source_vault_id' => 1,
                'destination_vault_id' => 1,
                'bank_account_id' => 1,
                'pickup_location' => 'Sample pickup_location 1',
                'delivery_location' => 'Sample delivery_location 1',
                'transport_company_name' => 'Sample transport_company_name',
                'transport_company_license' => 'Sample transport_company_license 1',
                'transport_company_contact' => 'Sample transport_company_contact 1',
                'insurance_policy_number' => 000001,
                'insurance_coverage_amount' => 1000,
                'vehicle_registration' => 'Sample vehicle_registration 1',
                'vehicle_type' => 'armored van',
                'vehicle_gps_tracker' => 'Sample vehicle_gps_tracker 1',
                'security_personnel' => json_encode(['value' => 'Sample security_personnel 1']),
                'team_leader_name' => 'Sample team_leader_name',
                'team_leader_badge' => 'Sample team_leader_badge 1',
                'team_leader_contact' => 'Sample team_leader_contact 1',
                'cash_bag_seal_number' => 000001,
                'container_seal_number' => 000001,
                'verification_codes' => json_encode(['value' => 'SEC001']),
                'scheduled_pickup_time' => '2025-07-23 10:38:42',
                'actual_pickup_time' => '2025-07-23 10:38:42',
                'scheduled_delivery_time' => '2025-07-23 10:38:42',
                'actual_delivery_time' => '2025-07-23 10:38:42',
                'planned_route' => 'Sample planned_route 1',
                'actual_route' => 'Sample actual_route 1',
                'status' => 'SCHEDULED',
                'status_notes' => 'PENDING',
                'pickup_verified_by' => 'Sample pickup_verified_by 1',
                'delivery_verified_by' => 'Sample delivery_verified_by 1',
                'pickup_verification_time' => '2025-07-23 10:38:42',
                'delivery_verification_time' => '2025-07-23 10:38:42',
                'pickup_notes' => 'Sample pickup_notes 1',
                'delivery_notes' => 'Sample delivery_notes 1',
                'has_incident' => false,
                'incident_description' => null,
                'incident_reported_at' => null,
                'incident_report_number' => null,
                'additional_metadata' => json_encode(['value' => 'Sample additional_metadata 1']),
                'initiated_by' => 1,
                'authorized_by' => 1,
                'created_at' => '2025-07-23 10:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'transfer_reference' => 'Sample transfer_reference 2',
                'transfer_type' => 'VAULT_TO_BANK',
                'amount' => 2000,
                'currency' => 'TZS',
                'source_vault_id' => 2,
                'destination_vault_id' => 2,
                'bank_account_id' => 2,
                'pickup_location' => 'Sample pickup_location 2',
                'delivery_location' => 'Sample delivery_location 2',
                'transport_company_name' => 'Sample transport_company_name',
                'transport_company_license' => 'Sample transport_company_license 2',
                'transport_company_contact' => 'Sample transport_company_contact 2',
                'insurance_policy_number' => 000002,
                'insurance_coverage_amount' => 2000,
                'vehicle_registration' => 'Sample vehicle_registration 2',
                'vehicle_type' => 'security vehicle',
                'vehicle_gps_tracker' => 'Sample vehicle_gps_tracker 2',
                'security_personnel' => json_encode(['value' => 'Sample security_personnel 2']),
                'team_leader_name' => 'Sample team_leader_name',
                'team_leader_badge' => 'Sample team_leader_badge 2',
                'team_leader_contact' => 'Sample team_leader_contact 2',
                'cash_bag_seal_number' => 000002,
                'container_seal_number' => 000002,
                'verification_codes' => json_encode(['value' => 'SEC002']),
                'scheduled_pickup_time' => '2025-07-23 11:38:42',
                'actual_pickup_time' => '2025-07-23 11:38:42',
                'scheduled_delivery_time' => '2025-07-23 11:38:42',
                'actual_delivery_time' => '2025-07-23 11:38:42',
                'planned_route' => 'Sample planned_route 2',
                'actual_route' => 'Sample actual_route 2',
                'status' => 'DELIVERED',
                'status_notes' => 'INACTIVE',
                'pickup_verified_by' => 'Sample pickup_verified_by 2',
                'delivery_verified_by' => 'Sample delivery_verified_by 2',
                'pickup_verification_time' => '2025-07-23 11:38:42',
                'delivery_verification_time' => '2025-07-23 11:38:42',
                'pickup_notes' => 'Sample pickup_notes 2',
                'delivery_notes' => 'Sample delivery_notes 2',
                'has_incident' => true,
                'incident_description' => 'Minor delay due to traffic',
                'incident_reported_at' => '2025-07-23 12:00:00',
                'incident_report_number' => 'INC-2025-002',
                'additional_metadata' => json_encode(['value' => 'Sample additional_metadata 2']),
                'initiated_by' => 2,
                'authorized_by' => 1,
                'created_at' => '2025-07-23 11:38:42',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('security_transport_logs')->insert($row);
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