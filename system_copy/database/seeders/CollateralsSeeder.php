<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollateralsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('collaterals')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'loan_id' => 1,
                'main_collateral_type' => 'TYPE_B',
                'collateral_value' => 1000.00,
                'member_number' => 000001,
                'account_id' => 1,
                'collateral_category' => 'Sample collateral_category 1',
                'collateral_type' => 'TYPE_B',
                'description' => 'This is a sample description for collaterals record 1',
                'collateral_id' => 1,
                'client_id' => 1,
                'type_of_owner' => 'TYPE_B',
                'relationship' => 'Sample relationship 1',
                'collateral_owner_full_name' => 'Sample collateral_owner_full_name',
                'collateral_owner_nida' => 1,
                'collateral_owner_contact_number' => 000001,
                'collateral_owner_residential_address' => 1,
                'collateral_owner_spouse_full_name' => 'Sample collateral_owner_spouse_full_name',
                'collateral_owner_spouse_nida' => 1,
                'collateral_owner_spouse_contact_number' => 000001,
                'collateral_owner_spouse_residential_address' => 1,
                'company_registered_name' => 'Sample company_registered_name',
                'business_licence_number' => 000001,
                'tin' => 'Sample tin 1',
                'director_nida' => 1,
                'director_contact' => 'Sample director_contact 1',
                'director_address' => 'Sample Address 1, collaterals Street',
                'business_address' => 'Sample Address 1, collaterals Street',
                'date_of_valuation' => '2025-07-24',
                'valuation_method_used' => 'Sample valuation_method_used 1',
                'name_of_valuer' => 'Sample name_of_valuer',
                'policy_number' => 000001,
                'company_name' => 'Sample company_name',
                'coverage_details' => 'Sample coverage_details 1',
                'expiration_date' => '2025-07-24',
                'disbursement_date' => '2025-07-24',
                'loan_amount' => 1000,
                'physical_condition' => 'Sample physical_condition 1',
                'current_status' => 'PENDING',
                'region' => 'Sample region 1',
                'district' => 'Sample district 1',
                'ward' => 'Sample ward 1',
                'postal_code' => 'COL001',
                'address' => 'Sample Address 1, collaterals Street',
                'building_number' => 000001,
                'release_status' => 'PENDING',
                'collateral_file' => 'Sample collateral_file 1',
                'collateral_file_name' => 'Sample collateral_file_name',
                'collateral_file_rejected' => true,
                'expiration_period' => true,
                'expiration_period_rejected' => true,
                'approval_status' => 'pending',
                'guarantor_id' => 1,
                'created_at' => '2025-07-23 10:38:33',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'loan_id' => 2,
                'main_collateral_type' => 'TYPE_C',
                'collateral_value' => 1000.00,
                'member_number' => 000002,
                'account_id' => 2,
                'collateral_category' => 'Sample collateral_category 2',
                'collateral_type' => 'TYPE_C',
                'description' => 'This is a sample description for collaterals record 2',
                'collateral_id' => 2,
                'client_id' => 2,
                'type_of_owner' => 'TYPE_C',
                'relationship' => 'Sample relationship 2',
                'collateral_owner_full_name' => 'Sample collateral_owner_full_name',
                'collateral_owner_nida' => 2,
                'collateral_owner_contact_number' => 000002,
                'collateral_owner_residential_address' => 2,
                'collateral_owner_spouse_full_name' => 'Sample collateral_owner_spouse_full_name',
                'collateral_owner_spouse_nida' => 2,
                'collateral_owner_spouse_contact_number' => 000002,
                'collateral_owner_spouse_residential_address' => 2,
                'company_registered_name' => 'Sample company_registered_name',
                'business_licence_number' => 000002,
                'tin' => 'Sample tin 2',
                'director_nida' => 2,
                'director_contact' => 'Sample director_contact 2',
                'director_address' => 'Sample Address 2, collaterals Street',
                'business_address' => 'Sample Address 2, collaterals Street',
                'date_of_valuation' => '2025-07-25',
                'valuation_method_used' => 'Sample valuation_method_used 2',
                'name_of_valuer' => 'Sample name_of_valuer',
                'policy_number' => 000002,
                'company_name' => 'Sample company_name',
                'coverage_details' => 'Sample coverage_details 2',
                'expiration_date' => '2025-07-25',
                'disbursement_date' => '2025-07-25',
                'loan_amount' => 2000,
                'physical_condition' => 'Sample physical_condition 2',
                'current_status' => 'INACTIVE',
                'region' => 'Sample region 2',
                'district' => 'Sample district 2',
                'ward' => 'Sample ward 2',
                'postal_code' => 'COL002',
                'address' => 'Sample Address 2, collaterals Street',
                'building_number' => 000002,
                'release_status' => 'INACTIVE',
                'collateral_file' => 'Sample collateral_file 2',
                'collateral_file_name' => 'Sample collateral_file_name',
                'collateral_file_rejected' => true,
                'expiration_period' => true,
                'expiration_period_rejected' => true,
                'approval_status' => 'INACTIVE',
                'guarantor_id' => 2,
                'created_at' => '2025-07-23 11:38:33',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('collaterals')->insert($row);
    }
}
}