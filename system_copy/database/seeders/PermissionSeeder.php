<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $institutionId = \App\Models\Institution::first()->id ?? 1; // Use the first institution or default to 1

        // Create base permissions
        $permissions = [
            [
                'name' => 'manage_users',
                'slug' => 'manage-users',
                'description' => 'Can manage users in the system',
                'module' => 'users',
                'action' => 'manage',
                'resource_type' => 'model',
                'resource_id' => 'User',
                'is_system' => true
            ],
            [
                'name' => 'view_users',
                'slug' => 'view-users',
                'description' => 'Can view users in the system',
                'module' => 'users',
                'action' => 'view',
                'resource_type' => 'model',
                'resource_id' => 'User',
                'is_system' => true
            ],
            [
                'name' => 'create_users',
                'slug' => 'create-users',
                'description' => 'Can create new users',
                'module' => 'users',
                'action' => 'create',
                'resource_type' => 'model',
                'resource_id' => 'User',
                'is_system' => true
            ],
            [
                'name' => 'edit_users',
                'slug' => 'edit-users',
                'description' => 'Can edit existing users',
                'module' => 'users',
                'action' => 'edit',
                'resource_type' => 'model',
                'resource_id' => 'User',
                'is_system' => true
            ],
            [
                'name' => 'delete_users',
                'slug' => 'delete-users',
                'description' => 'Can delete users',
                'module' => 'users',
                'action' => 'delete',
                'resource_type' => 'model',
                'resource_id' => 'User',
                'is_system' => true
            ]
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                [
                    'name' => $permissionData['name']
                ],
                $permissionData
            );
        }

        // Create roles
        $adminRole = Role::updateOrCreate(
            [
                'name' => 'Administrator'
            ],
            [
                'description' => 'System Administrator with full access',
                'department_specific' => false,
                'permission_inheritance_enabled' => true,
                'is_system_role' => true,
                'level' => 1
            ]
        );

        $managerRole = Role::updateOrCreate(
            [
                'name' => 'Department Manager'
            ],
            [
                'description' => 'Department Manager with limited access',
                'parent_role_id' => $adminRole->id,
                'department_specific' => true,
                'permission_inheritance_enabled' => true,
                'is_system_role' => true
            ]
        );

        // Grant permissions to roles
        $manageUsersPermission = Permission::where('name', 'manage_users')->first();
        $viewUsersPermission = Permission::where('name', 'view_users')->first();
        $createUsersPermission = Permission::where('name', 'create_users')->first();
        $editUsersPermission = Permission::where('name', 'edit_users')->first();
        $deleteUsersPermission = Permission::where('name', 'delete_users')->first();

        // Grant all permissions to admin role
        $adminRole->grantPermission($manageUsersPermission);
        $adminRole->grantPermission($viewUsersPermission);
        $adminRole->grantPermission($createUsersPermission);
        $adminRole->grantPermission($editUsersPermission);
        $adminRole->grantPermission($deleteUsersPermission);

        // Grant limited permissions to manager role with conditions
        $managerRole->grantPermission($viewUsersPermission, null, [
            'max_amount' => 10000,
            'allowed_departments' => ['HR', 'Finance']
        ]);

        $managerRole->grantPermission($createUsersPermission, null, [
            'max_amount' => 5000,
            'allowed_departments' => ['HR', 'Finance']
        ]);

        $managerRole->grantPermission($editUsersPermission, null, [
            'max_amount' => 5000,
            'allowed_departments' => ['HR', 'Finance']
        ]);
    }
}