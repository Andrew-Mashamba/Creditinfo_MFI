<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixUserRoles extends Command
{
    protected $signature = 'fix:user-roles';
    protected $description = 'Fix user roles and permissions';

    public function handle()
    {
        $this->info('Starting role fix process...');

        // Check if admin role exists
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        if (!$adminRole) {
            $this->error('Admin role not found! Creating it...');
            $adminRoleId = DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $adminRole = DB::table('roles')->find($adminRoleId);
        }

        // Get admin user
        $adminUser = DB::table('users')->where('id', 1)->first();
        if (!$adminUser) {
            $this->error('Admin user not found!');
            return;
        }

        // Assign admin role to user
        $this->info('Assigning admin role to user...');
        DB::table('user_roles')->updateOrInsert(
            ['user_id' => 1],
            [
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Check sub-roles
        $subRole = DB::table('sub_roles')
            ->where('role_id', $adminRole->id)
            ->where('name', 'Super Admin')
            ->first();

        if (!$subRole) {
            $this->info('Creating Super Admin sub-role...');
            $subRoleId = DB::table('sub_roles')->insertGetId([
                'role_id' => $adminRole->id,
                'name' => 'Super Admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $subRole = DB::table('sub_roles')->find($subRoleId);
        }

        // Assign sub-role to user
        $this->info('Assigning sub-role to user...');
        DB::table('user_sub_roles')->updateOrInsert(
            ['user_id' => 1],
            [
                'sub_role_id' => $subRole->id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Verify assignments
        $userRole = DB::table('user_roles')->where('user_id', 1)->first();
        $userSubRole = DB::table('user_sub_roles')->where('user_id', 1)->first();

        $this->info('Verification:');
        $this->info('User Role: ' . ($userRole ? 'Assigned' : 'Not Assigned'));
        $this->info('User Sub-Role: ' . ($userSubRole ? 'Assigned' : 'Not Assigned'));

        $this->info('Role fix process completed!');
    }
} 