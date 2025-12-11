<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class AssignSuperAdminPermissionsSeeder extends Seeder
{
    /**
     * Assign all permissions to the super admin user (User ID 1)
     * This ensures the IT Manager has full system access after migrations
     */
    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Find user ID 1
            $user = User::find(1);
            
            if (!$user) {
                if ($this->command) {
                    if ($this->command) $this->command->warn('User with ID 1 not found. Skipping super admin permission assignment.');
                }
                DB::commit();
                return;
            }
            
            if ($this->command) {
                if ($this->command) $this->command->info("Found user: {$user->name} ({$user->email})");
            }
            
            // Get or create IT Manager role
            $itDepartment = DB::table('departments')
                ->where('department_code', 'ICT')
                ->orWhere('department_name', 'LIKE', '%Information%')
                ->first();
                
            if (!$itDepartment) {
                if ($this->command) {
                    if ($this->command) $this->command->warn('IT Department not found. Creating a system admin role instead.');
                }
                
                // Create a system admin role if IT department doesn't exist
                $role = Role::firstOrCreate(
                    ['name' => 'System Administrator'],
                    [
                        'description' => 'Full system administrator with all permissions',
                        'status' => 'ACTIVE',
                        'level' => 1,
                        'institution_id' => $user->institution_id ?? 11,
                        'is_system_role' => true,
                        'permission_inheritance_enabled' => true,
                        'department_specific' => false,
                    ]
                );
            } else {
                // Get or create IT Manager role
                $role = Role::firstOrCreate(
                    [
                        'name' => 'IT Manager',
                        'department_id' => $itDepartment->id
                    ],
                    [
                        'description' => 'Head of Information Technology with full system access',
                        'status' => 'ACTIVE',
                        'level' => 1,
                        'institution_id' => $user->institution_id ?? 11,
                        'is_system_role' => false,
                        'permission_inheritance_enabled' => true,
                        'department_specific' => true,
                    ]
                );
            }
            
            if ($this->command) {
                if ($this->command) $this->command->info("Using role: {$role->name} (ID: {$role->id})");
            }
            
            // Assign role to user if not already assigned
            if (!$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
                if ($this->command) $this->command->info("Assigned role '{$role->name}' to user");
            } else {
                if ($this->command) $this->command->info("User already has role '{$role->name}'");
            }
            
            // Get all permissions
            $allPermissions = Permission::all();
            $permissionCount = $allPermissions->count();
            
            if ($permissionCount === 0) {
                if ($this->command) $this->command->warn('No permissions found in the system. Please run SystemPermissionsSeeder first.');
                DB::commit();
                return;
            }
            
            // Sync all permissions to the role
            $permissionIds = $allPermissions->pluck('id')->toArray();
            $role->permissions()->sync($permissionIds);
            
            if ($this->command) $this->command->info("✅ Successfully assigned {$permissionCount} permissions to role '{$role->name}'");
            
            // Verify the assignment
            $assignedCount = $role->permissions()->count();
            if ($this->command) $this->command->info("Verification: Role now has {$assignedCount} permissions");
            
            // Show some sample permissions
            if ($this->command) $this->command->info("\nSample permissions assigned:");
            $role->permissions()
                ->whereIn('module', ['System', 'User Management'])
                ->limit(5)
                ->pluck('name')
                ->each(function($permission) {
                    if ($this->command) $this->command->info("  ✓ {$permission}");
                });
            
            DB::commit();
            
            if ($this->command) $this->command->info("\n🎯 Super Admin permissions seeding completed successfully!");
            if ($this->command) $this->command->info("User '{$user->name}' now has full system access through role '{$role->name}'");
            
        } catch (\Exception $e) {
            DB::rollBack();
            if ($this->command) $this->command->error('Error assigning super admin permissions: ' . $e->getMessage());
            throw $e;
        }
    }
}