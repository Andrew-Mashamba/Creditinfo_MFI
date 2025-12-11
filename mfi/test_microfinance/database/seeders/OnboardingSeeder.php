<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OnboardingSeeder extends Seeder
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
        // DB::table('onboarding')->truncate(); // Commented out to avoid foreign key issues

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'applicant_id' => 1,
                'job_posting_id' => 1,
                'employee_id' => 1,
                'start_date' => '2025-07-24',
                'status' => 'pending',
                'notes' => 'Sample notes 1',
                'cv_path' => 'Sample cv_path 1',
                'national_id_path' => 'documents/national_id_1.pdf',
                'passport_photo_path' => 'Sample passport_photo_path 1',
                'employment_contract_path' => 'Sample employment_contract_path 1',
                'bank_account_details_path' => 'documents/bank_details_1.pdf',
                'full_name' => 'Sample full_name',
                'date_of_birth' => '2025-07-24',
                'nationality' => 'Sample nationality 1',
                'nida_number' => '19900101000001',
                'tin_number' => 'TIN001',
                'physical_address' => 'Sample Address 1, onboarding Street',
                'emergency_contact_name' => 'Sample emergency_contact_name',
                'emergency_contact_phone' => +255700000001,
                'workstation_id' => 'WS001',
                'email_created' => true,
                'system_access' => true,
                'id_badge' => true,
                'created_by' => 1,
                'created_at' => '2025-07-23 10:38:39',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'applicant_id' => 2,
                'job_posting_id' => 2,
                'employee_id' => 2,
                'start_date' => '2025-07-25',
                'status' => 'in_progress',
                'notes' => 'Sample notes 2',
                'cv_path' => 'Sample cv_path 2',
                'national_id_path' => 'documents/national_id_2.pdf',
                'passport_photo_path' => 'Sample passport_photo_path 2',
                'employment_contract_path' => 'Sample employment_contract_path 2',
                'bank_account_details_path' => 'documents/bank_details_2.pdf',
                'full_name' => 'Sample full_name',
                'date_of_birth' => '2025-07-25',
                'nationality' => 'Sample nationality 2',
                'nida_number' => '19900202000002',
                'tin_number' => 'TIN002',
                'physical_address' => 'Sample Address 2, onboarding Street',
                'emergency_contact_name' => 'Sample emergency_contact_name',
                'emergency_contact_phone' => +255700000002,
                'workstation_id' => 'WS002',
                'email_created' => false,
                'system_access' => false,
                'id_badge' => false,
                'created_by' => 1,
                'created_at' => '2025-07-23 11:38:39',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            // Validate user references
            $userFields = ['user_id', 'created_by', 'approved_by'];
            foreach ($userFields as $field) {
                if (isset($row[$field]) && $row[$field]) {
                    $userExists = DB::table('users')->where('id', $row[$field])->exists();
                    if (!$userExists) {
                        $firstUser = DB::table('users')->first();
                        if (!$firstUser) {
                            // Skip this record if no users exist
                            if ($this->command) $this->command->warn("Skipping onboarding record - no users found");
                            continue 2; // Continue outer loop
                        }
                        $row[$field] = $firstUser->id;
                    }
                }
            }
            
            // Use updateOrInsert to avoid foreign key conflicts
            DB::table('onboarding')->updateOrInsert(
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