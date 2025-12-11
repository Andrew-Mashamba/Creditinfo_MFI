<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if we already have users to avoid duplicates
        if (DB::table('users')->count() > 0) {
            echo "Users already exist, skipping UsersSeeder\n";
            return;
        }

        // Insert user data
        $data = [
            [
                // 'id' => 2,
                'institution_user_id' => null,
                'name' => 'Jane Doe',
                'email' => 'jane.doe@example.com',
                'email_verified_at' => '2025-07-17 16:25:47',
                'password' => '$2y$10$TyRcJsQ01zrX4AQmwMTF6.r/lTQbgSTdh5bWXi3kDlGLYU61apKTq', // Password: 1234567890
                'remember_token' => null,
                'current_team_id' => 1,
                'profile_photo_path' => null,
                'role' => null,
                'status' => 'ACTIVE',
                'otp_time' => null,
                'otp' => null,
                'verification_status' => 1,
                'phone_number' => null,
                'employeeId' => null,
                'department_code' => 'GOV',
                'sub_role' => null,
                'branch_id' => 1,
                'created_at' => '2025-07-17 16:25:47',
                'updated_at' => '2025-07-17 16:25:47',
                'last_update_password' => '2025-07-17 19:25:42',
                'token' => null,
                'token_expires_at' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'password_changed_at' => null,
                'otp_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'otp_last_sent_at' => null,
                'otp_locked_until' => null,
            ],
            [
                //  'id' => 1,
                'institution_user_id' => null,
                'name' => 'Andrew S. Mashamba',
                'email' => 'andrew.s.mashamba@gmail.com',
                'email_verified_at' => '2025-07-17 16:25:47',
                'password' => '$2y$10$TyRcJsQ01zrX4AQmwMTF6.r/lTQbgSTdh5bWXi3kDlGLYU61apKTq', // Password: 1234567890
                'remember_token' => null,
                'current_team_id' => 1,
                'profile_photo_path' => null,
                'role' => null,
                'status' => 'ACTIVE',
                'otp_time' => null,
                'otp' => null,
                'verification_status' => 1,
                'phone_number' => null,
                'employeeId' => null,
                'department_code' => 'ICT',
                'sub_role' => null,
                'branch_id' => 1,
                'created_at' => '2025-07-17 16:25:47',
                'updated_at' => '2025-07-18 03:33:03',
                'last_update_password' => '2025-07-17 19:25:42',
                'token' => null,
                'token_expires_at' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'password_changed_at' => null,
                'otp_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'otp_last_sent_at' => null,
                'otp_locked_until' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('users')->insert($row);
        }
    }
}