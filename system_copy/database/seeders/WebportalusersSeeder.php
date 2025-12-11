<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebportalusersSeeder extends Seeder
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
        DB::table('web_portal_users')->truncate();

        // Insert sample data (table was empty)
        $data = [
            [
                'id' => 1,
                'client_id' => 1,
                'client_number' => '000001',
                'username' => 'user1',
                'email' => 'sample1@web_portal_users.com',
                'phone' => '255700000001',
                'password_hash' => 'Sample password_hash 1',
                'email_verified_at' => now(),
                'remember_token' => 'Sample remember_token 1',
                'is_active' => false,
                'is_locked' => false,
                'locked_at' => now(),
                'locked_reason' => 'Sample locked_reason 1',
                'last_failed_attempt' => null,
                'last_login_at' => now(),
                'last_login_ip' => '192.168.1.1',
                'last_user_agent' => 'Sample last_user_agent 1',
                'password_reset_token' => 'Sample password_reset_token 1',
                'password_reset_expires_at' => null,
                'password_changed_at' => null,
                'force_password_change' => true,
                'current_session_id' => 'session1',
                'session_expires_at' => null,
                'active_sessions' => json_encode(['value' => 'Sample active_sessions 1']),
                'permissions' => json_encode(['read', 'write', 'delete']),
                'preferences' => json_encode(['value' => 'Sample preferences 1']),
                'preferred_language' => 'en',
                'timezone' => 'Africa/Dar_es_Salaam',
                'two_factor_enabled' => true,
                'two_factor_secret' => 'Sample two_factor_secret 1',
                'two_factor_recovery_codes' => json_encode(['value' => 'WEB001']),
                'two_factor_confirmed_at' => null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'login_notifications' => true,
                'transaction_notifications' => true,
                'portal_registered_at' => now(),
                'registered_by' => 1,
                'last_activity_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-07-23 10:38:44',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'client_id' => 2,
                'client_number' => '000002',
                'username' => 'user2',
                'email' => 'sample2@web_portal_users.com',
                'phone' => '255700000002',
                'password_hash' => 'Sample password_hash 2',
                'email_verified_at' => now(),
                'remember_token' => 'Sample remember_token 2',
                'is_active' => true,
                'is_locked' => true,
                'locked_at' => now(),
                'locked_reason' => 'Sample locked_reason 2',
                'last_failed_attempt' => null,
                'last_login_at' => now(),
                'last_login_ip' => '192.168.1.1',
                'last_user_agent' => 'Sample last_user_agent 2',
                'password_reset_token' => 'Sample password_reset_token 2',
                'password_reset_expires_at' => null,
                'password_changed_at' => null,
                'force_password_change' => true,
                'current_session_id' => 'session2',
                'session_expires_at' => null,
                'active_sessions' => json_encode(['value' => 'Sample active_sessions 2']),
                'permissions' => json_encode(['read', 'write', 'delete']),
                'preferences' => json_encode(['value' => 'Sample preferences 2']),
                'preferred_language' => 'sw',
                'timezone' => 'Africa/Dar_es_Salaam',
                'two_factor_enabled' => true,
                'two_factor_secret' => 'Sample two_factor_secret 2',
                'two_factor_recovery_codes' => json_encode(['value' => 'WEB002']),
                'two_factor_confirmed_at' => null,
                'email_notifications' => true,
                'sms_notifications' => true,
                'login_notifications' => true,
                'transaction_notifications' => true,
                'portal_registered_at' => now(),
                'registered_by' => 1,
                'last_activity_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-07-23 11:38:44',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ];

        foreach ($data as $row) {
            DB::table('web_portal_users')->insert($row);
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