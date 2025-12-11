<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubRoleSeeder extends Seeder
{
    public function run()
    {
        Log::info('Starting SubRoleSeeder');

        $roles = [
            [
                'name' => 'System Administrator',
                'role_id' => 1, // System Administrator role
                'description' => 'Full system access with all permissions'
            ],
            [
                'name' => 'Institution Administrator',
                'role_id' => 2, // Institution Administrator role
                'description' => 'Institution level administration with full access'
            ],
            [
                'name' => 'IT Head',
                'role_id' => 3, // IT Department Head role
                'description' => 'Head of IT Department with full department access'
            ],
            [
                'name' => 'IT Manager',
                'role_id' => 4, // IT Department Manager role
                'description' => 'Manager of IT Department with department management access'
            ],
            [
                'name' => 'IT Staff',
                'role_id' => 5, // IT Department Staff role
                'description' => 'IT Department staff member with basic department access'
            ],
            [
                'name' => 'HR Head',
                'role_id' => 6, // HR Department Head role
                'description' => 'Head of HR Department with full department access'
            ],
            [
                'name' => 'HR Manager',
                'role_id' => 7, // HR Department Manager role
                'description' => 'Manager of HR Department with department management access'
            ],
            [
                'name' => 'HR Staff',
                'role_id' => 8, // HR Department Staff role
                'description' => 'HR Department staff member with basic department access'
            ]
        ];

        foreach ($roles as $role) {
            $roleRecord = DB::table('roles')->where('name', $role['name'])->first();
            if ($roleRecord) {
                DB::table('sub_roles')->updateOrInsert(
                    ['name' => $role['name']],
                    [
                        'role_id' => $roleRecord->id,
                        'description' => $role['description'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                Log::info('Created/Updated sub role', ['role' => $role['name']]);
            } else {
                Log::warning('Role not found', ['role' => $role['name']]);
            }
        }

        Log::info('SubRoleSeeder completed');
    }
}