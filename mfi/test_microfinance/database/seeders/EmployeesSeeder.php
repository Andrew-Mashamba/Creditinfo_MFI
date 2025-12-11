<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('employees')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'institution_user_id' => 1,
                'first_name' => 'Sample first_name',
                'middle_name' => 1,
                'last_name' => 'Sample last_name',
                'date_of_birth' => '2025-07-24',
                'gender' => 'MALE',
                'marital_status' => 'single',
                'nationality' => 'Sample nationality 1',
                'address' => 'Sample Address 1, employees Street',
                'street' => 'Sample street 1',
                'city' => 'Sample City 1',
                'region' => 'Sample region 1',
                'district' => 'Sample district 1',
                'ward' => 'Sample ward 1',
                'postal_code' => 'EMP001',
                'phone' => +255700000001,
                'email' => 'sample1@employees.com',
                'job_title' => 'Sample job_title 1',
                'branch_id' => 1,
                'hire_date' => '2025-07-24',
                'basic_salary' => 50000.00,
                'gross_salary' => 55000.00,
                'payment_frequency' => 'monthly',
                'employee_status' => 'active',
                'registering_officer' => 1,
                'employment_type' => 'full_time',
                'emergency_contact_name' => 'Sample emergency_contact_name',
                'emergency_contact_relationship' => 'Sample emergency_contact_relationship 1',
                'emergency_contact_phone' => +255700000001,
                'emergency_contact_email' => 'sample1@employees.com',
                'department_id' => 1,
                'role_id' => 1,
                'reporting_manager_id' => 1,
                'employee_number' => 'EMP001',
                'notes' => 'Sample notes 1',
                'profile_photo_path' => 'Sample profile_photo_path 1',
                'next_of_kin_name' => 'Sample next_of_kin_name',
                'place_of_birth' => 'Sample place_of_birth 1',
                'next_of_kin_phone' => +255700000001,
                'tin_number' => 'TIN001',
                'nida_number' => '19900101000001',
                'nssf_number' => 'NSSF001',
                'nssf_rate' => 5.5,
                'nhif_number' => 'NHIF001',
                'nhif_rate' => 5.5,
                'workers_compensation' => 100.00,
                'life_insurance' => 500.00,
                'tax_category' => 'Sample tax_category 1',
                'paye_rate' => 5.5,
                'tax_paid' => 5000.00,
                'pension' => 2500.00,
                'nhif' => 500.00,
                'education_level' => 'Sample education_level 1',
                'approval_stage' => 'Sample approval_stage 1',
                'user_id' => 1,
                'client_id' => 1,
                'created_at' => '2025-07-23 10:38:35',
                'updated_at' => now(),
                'physical_address' => 'Sample Address 1, employees Street',
            ],
            [
                'id' => 2,
                'institution_user_id' => 2,
                'first_name' => 'Sample first_name',
                'middle_name' => 2,
                'last_name' => 'Sample last_name',
                'date_of_birth' => '2025-07-25',
                'gender' => 'MALE',
                'marital_status' => 'married',
                'nationality' => 'Sample nationality 2',
                'address' => 'Sample Address 2, employees Street',
                'street' => 'Sample street 2',
                'city' => 'Sample City 2',
                'region' => 'Sample region 2',
                'district' => 'Sample district 2',
                'ward' => 'Sample ward 2',
                'postal_code' => 'EMP002',
                'phone' => +255700000002,
                'email' => 'sample2@employees.com',
                'job_title' => 'Sample job_title 2',
                'branch_id' => 2,
                'hire_date' => '2025-07-25',
                'basic_salary' => 50000.00,
                'gross_salary' => 55000.00,
                'payment_frequency' => 'monthly',
                'employee_status' => 'inactive',
                'registering_officer' => 2,
                'employment_type' => 'full_time',
                'emergency_contact_name' => 'Sample emergency_contact_name',
                'emergency_contact_relationship' => 'Sample emergency_contact_relationship 2',
                'emergency_contact_phone' => +255700000002,
                'emergency_contact_email' => 'sample2@employees.com',
                'department_id' => 2,
                'role_id' => 2,
                'reporting_manager_id' => 2,
                'employee_number' => 'EMP002',
                'notes' => 'Sample notes 2',
                'profile_photo_path' => 'Sample profile_photo_path 2',
                'next_of_kin_name' => 'Sample next_of_kin_name',
                'place_of_birth' => 'Sample place_of_birth 2',
                'next_of_kin_phone' => +255700000002,
                'tin_number' => 'TIN002',
                'nida_number' => '19900202000002',
                'nssf_number' => 'NSSF002',
                'nssf_rate' => 11,
                'nhif_number' => 'NHIF002',
                'nhif_rate' => 11,
                'workers_compensation' => 150.00,
                'life_insurance' => 750.00,
                'tax_category' => 'Sample tax_category 2',
                'paye_rate' => 11,
                'tax_paid' => 7500.00,
                'pension' => 2500.00,
                'nhif' => 500.00,
                'education_level' => 'Sample education_level 2',
                'approval_stage' => 'Sample approval_stage 2',
                'user_id' => 2,
                'client_id' => 2,
                'created_at' => '2025-07-23 11:38:35',
                'updated_at' => now(),
                'physical_address' => 'Sample Address 2, employees Street',
            ],
        ];

        foreach ($data as $row) {
            DB::table('employees')->insert($row);
    }
}
}