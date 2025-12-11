<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserprofilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('user_profiles')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'user_id' => 1,
                'employee_id' => 1,
                'job_title' => 'Sample job_title 1',
                'phone_number' => '255700000001',
                'emergency_contact' => json_encode(['value' => 'Sample emergency_contact 1']),
                'date_of_birth' => '2025-07-24',
                'hire_date' => '2025-07-24',
                'employment_status' => 'PENDING',
                'employment_type' => 'full_time',
                'salary_grade' => 'Sample salary_grade 1',
                'reporting_manager_id' => 1,
                'skills' => json_encode(['value' => 'Sample skills 1']),
                'certifications' => json_encode(['value' => 'Sample certifications 1']),
                'education' => json_encode(['value' => 'Sample education 1']),
                'work_experience' => json_encode(['value' => 'Sample work_experience 1']),
                'preferences' => json_encode(['value' => 'Sample preferences 1']),
                'language_preference' => 'Sample language_preference 1',
                'timezone' => 'Africa/Dar_es_Salaam',
                'notification_preferences' => json_encode(['value' => 'Sample notification_preferences 1']),
                'profile_completion_percentage' => 50,
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'employee_id' => 2,
                'job_title' => 'Sample job_title 2',
                'phone_number' => '255700000002',
                'emergency_contact' => json_encode(['value' => 'Sample emergency_contact 2']),
                'date_of_birth' => '2025-07-25',
                'hire_date' => '2025-07-25',
                'employment_status' => 'INACTIVE',
                'employment_type' => 'full_time',
                'salary_grade' => 'Sample salary_grade 2',
                'reporting_manager_id' => 2,
                'skills' => json_encode(['value' => 'Sample skills 2']),
                'certifications' => json_encode(['value' => 'Sample certifications 2']),
                'education' => json_encode(['value' => 'Sample education 2']),
                'work_experience' => json_encode(['value' => 'Sample work_experience 2']),
                'preferences' => json_encode(['value' => 'Sample preferences 2']),
                'language_preference' => 'Sample language_preference 2',
                'timezone' => 'Africa/Dar_es_Salaam',
                'notification_preferences' => json_encode(['value' => 'Sample notification_preferences 2']),
                'profile_completion_percentage' => 75,
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            DB::table('user_profiles')->insert($row);
        }
    }
}