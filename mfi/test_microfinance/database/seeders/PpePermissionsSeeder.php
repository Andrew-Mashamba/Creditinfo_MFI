<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class PpePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create PPE management permissions
        $permissions = [
            [
                'name' => 'create-ppe',
                'slug' => 'create-ppe',
                'action' => 'create',
                'description' => 'Permission to create new PPE assets',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'edit-ppe',
                'slug' => 'edit-ppe',
                'action' => 'edit',
                'description' => 'Permission to edit existing PPE assets',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'delete-ppe',
                'slug' => 'delete-ppe',
                'action' => 'delete',
                'description' => 'Permission to delete PPE assets',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'view-ppe',
                'slug' => 'view-ppe',
                'action' => 'view',
                'description' => 'Permission to view PPE assets',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'manage-ppe-categories',
                'slug' => 'manage-ppe-categories',
                'action' => 'manage',
                'description' => 'Permission to manage PPE categories',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'view-ppe-reports',
                'slug' => 'view-ppe-reports',
                'action' => 'view',
                'description' => 'Permission to view PPE reports',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert permissions if they don't exist
        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')
                ->where('name', $permission['name'])
                ->where('guard_name', $permission['guard_name'])
                ->exists();
            
            if (!$exists) {
                DB::table('permissions')->insert($permission);
            }
        }

        // Create PPE Manager role if it doesn't exist
        $existingRole = DB::table('roles')
            ->where('name', 'ppe-manager')
            ->where('guard_name', 'web')
            ->first();
        
        if ($existingRole) {
            $roleId = $existingRole->id;
        } else {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'ppe-manager',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Get permission IDs
        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id');

        // Assign permissions to role
        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $roleId
            ]);
        }

        // Create default roles and assign permissions
        if (DB::getSchemaBuilder()->hasTable('roles')) {
            $roles = [
                [
                    'name' => 'ppe-operator',
                    'display_name' => 'PPE Operator',
                    'description' => 'Limited PPE management access',
                    'guard_name' => 'web',
                    'permissions' => ['create-ppe', 'edit-ppe', 'view-ppe', 'export-ppe-reports']
                ],
                [
                    'name' => 'ppe-viewer',
                    'display_name' => 'PPE Viewer',
                    'description' => 'Read-only access to PPE data',
                    'guard_name' => 'web',
                    'permissions' => ['view-ppe', 'export-ppe-reports']
                ]
            ];

            foreach ($roles as $roleData) {
                // Check if role already exists
                $roleExists = DB::table('roles')
                    ->where('name', $roleData['name'])
                    ->exists();

                if (!$roleExists) {
                    // Create role
                    $roleId = DB::table('roles')->insertGetId([
                        'name' => $roleData['name'],
                        'display_name' => $roleData['display_name'] ?? $roleData['name'],
                        'description' => $roleData['description'] ?? '',
                        'guard_name' => $roleData['guard_name'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Assign permissions to role
                    if (DB::getSchemaBuilder()->hasTable('role_has_permissions')) {
                        foreach ($roleData['permissions'] as $permissionName) {
                            $permission = DB::table('permissions')
                                ->where('name', $permissionName)
                                ->first();

                            if ($permission) {
                                DB::table('role_has_permissions')->insert([
                                    'role_id' => $roleId,
                                    'permission_id' => $permission->id
                                ]);
                            }
                        }
                    }
                }
            }

            $this->command->info('PPE roles created and permissions assigned successfully!');
    }
}
}