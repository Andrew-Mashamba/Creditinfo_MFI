<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserrolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data carefully
        DB::table('user_roles')->delete();
        
        // Reset auto-increment (PostgreSQL syntax)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER SEQUENCE user_roles_id_seq RESTART WITH 1");
        } elseif (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE user_roles AUTO_INCREMENT = 1');
        }

        // Get all users and roles
        $users = DB::table('users')->orderBy('id')->get();
        $systemAdminRole = DB::table('roles')->where('name', 'System Administrator')->first();
        $institutionAdminRole = DB::table('roles')->where('name', 'Institution Administrator')->first();
        
        if (!$systemAdminRole || !$institutionAdminRole) {
            echo "UserRolesSeeder: Required roles not found. Please run RolesSeeder first.\n";
            return;
        }
        
        $id = 1;
        $insertedCount = 0;
        
        // Assign roles to users
        foreach ($users as $index => $user) {
            if ($index < 2) {
                // First two users get System Administrator role
                DB::table('user_roles')->insert([
                    'id' => $id++,
                    'user_id' => $user->id,
                    'role_id' => $systemAdminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertedCount++;
            } else {
                // Third user gets Institution Administrator role
                DB::table('user_roles')->insert([
                    'id' => $id++,
                    'user_id' => $user->id,
                    'role_id' => $institutionAdminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertedCount++;
            }
        }
        
        echo "UserRolesSeeder: Assigned roles to $insertedCount users\n";
}
}