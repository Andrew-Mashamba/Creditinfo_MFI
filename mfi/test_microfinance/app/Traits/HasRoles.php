<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Menu;
use App\Models\MenuAction;
use App\Models\RoleMenuAction;
use App\Models\UserRole;
use App\Models\SubRole;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait HasRoles
{
    /**
     * Get all roles for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): Collection
    {
        $roleIds = DB::table('user_roles')
            ->where('user_id', $this->id)
            ->pluck('role_id');

        return DB::table('roles')
            ->whereIn('id', $roleIds)
            ->get();
    }

    /**
     * Get all permissions for the user, including role-based and direct permissions.
     *
     * @param Department|null $department
     * @return Collection
     */
    public function getAllPermissions(?Department $department = null): Collection
    {
        $permissions = collect();

        // Get direct user permissions
        $userPermissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $this->id)
            ->when($department, function ($query) use ($department) {
                return $query->where('user_permissions.department_id', $department->id);
            })
            ->where('user_permissions.is_granted', true)
            ->select('permissions.*', 'user_permissions.conditions as user_conditions')
            ->get();

        $permissions = $permissions->merge($userPermissions);

        // Get role-based permissions
        foreach ($this->roles as $role) {
            $rolePermissions = $role->getAllPermissions();
            
            // Filter by department if specified
            if ($department) {
                $rolePermissions = $rolePermissions->filter(function ($permission) use ($department) {
                    return $permission->pivot->department_id === $department->id;
                });
            }

            $permissions = $permissions->merge($rolePermissions);
        }

        return $permissions->unique('id');
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @param Department|null $department
     * @param array $conditions
     * @return bool
     */
    public function hasPermission(string $permission, ?Department $department = null, array $conditions = []): bool
    {
        // Check direct user permissions
        $userPermission = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $this->id)
            ->where('permissions.name', $permission)
            ->when($department, function ($query) use ($department) {
                return $query->where('user_permissions.department_id', $department->id);
            })
            ->where('user_permissions.is_granted', true)
            ->first();

        if ($userPermission) {
            // Check conditions if specified
            if (!empty($conditions)) {
                $userConditions = json_decode($userPermission->conditions, true);
                return $this->checkConditions($userConditions, $conditions);
            }
            return true;
        }

        // Check role-based permissions
        foreach ($this->roles as $role) {
            if ($role->hasPermissionInDepartment($permission, $department)) {
                // Check conditions if specified
                if (!empty($conditions)) {
                    $rolePermission = $role->permissions()
                        ->where('name', $permission)
                        ->wherePivot('department_id', $department?->id)
                        ->first();
                    
                    if ($rolePermission) {
                        $roleConditions = $rolePermission->pivot->conditions;
                        return $this->checkConditions($roleConditions, $conditions);
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param array $permissions
     * @param Department|null $department
     * @return bool
     */
    public function hasAnyPermission(array $permissions, ?Department $department = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission, $department)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the user has all of the given permissions.
     *
     * @param array $permissions
     * @param Department|null $department
     * @return bool
     */
    public function hasAllPermissions(array $permissions, ?Department $department = null): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission, $department)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Grant a permission to the user.
     *
     * @param Permission $permission
     * @param Department|null $department
     * @param array $conditions
     * @return void
     */
    public function grantPermission(Permission $permission, ?Department $department = null, array $conditions = []): void
    {
        DB::table('user_permissions')->updateOrInsert(
            [
                'user_id' => $this->id,
                'permission_id' => $permission->id,
                'department_id' => $department?->id
            ],
            [
                'conditions' => json_encode($conditions),
                'is_granted' => true,
                'granted_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Revoke a permission from the user.
     *
     * @param Permission $permission
     * @param Department|null $department
     * @return void
     */
    public function revokePermission(Permission $permission, ?Department $department = null): void
    {
        DB::table('user_permissions')
            ->where('user_id', $this->id)
            ->where('permission_id', $permission->id)
            ->when($department, function ($query) use ($department) {
                return $query->where('department_id', $department->id);
            })
            ->delete();
    }

    /**
     * Check if conditions match.
     *
     * @param array|null $storedConditions
     * @param array $requiredConditions
     * @return bool
     */
    private function checkConditions(?array $storedConditions, array $requiredConditions): bool
    {
        if (empty($storedConditions)) {
            return true;
        }

        foreach ($requiredConditions as $key => $value) {
            if (!isset($storedConditions[$key]) || $storedConditions[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign a role to the user.
     *
     * @param int|Role $role
     * @return void
     */
    public function assignRole($role): void
    {
        Log::info('Assigning role', [
            'user_id' => $this->id,
            'role' => is_numeric($role) ? $role : $role->id
        ]);

        if (is_numeric($role)) {
            $role = Role::findOrFail($role);
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $this->id, 'role_id' => $role->id],
            ['user_id' => $this->id, 'role_id' => $role->id]
        );

        Log::info('Role assigned successfully', [
            'user_id' => $this->id,
            'role_id' => $role->id
        ]);
    }

    /**
     * Remove a role from the user.
     *
     * @param int|Role $role
     * @return void
     */
    public function removeRole($role): void
    {
        Log::info('Removing role', [
            'user_id' => $this->id,
            'role' => is_numeric($role) ? $role : $role->id
        ]);

        if (is_numeric($role)) {
            $role = Role::findOrFail($role);
        }

        DB::table('user_roles')
            ->where('user_id', $this->id)
            ->where('role_id', $role->id)
            ->delete();

        Log::info('Role removed successfully', [
            'user_id' => $this->id,
            'role_id' => $role->id
        ]);
    }

    /**
     * Sync multiple roles for the user.
     *
     * @param array $roleIds
     * @return void
     */
    public function syncRoles(array $roleIds): void
    {
        Log::info('Syncing roles', [
            'user_id' => $this->id,
            'role_ids' => $roleIds
        ]);

        DB::table('user_roles')
            ->where('user_id', $this->id)
            ->delete();

        foreach ($roleIds as $roleId) {
            DB::table('user_roles')->insert([
                'user_id' => $this->id,
                'role_id' => $roleId
            ]);
        }

        Log::info('Roles synced successfully', [
            'user_id' => $this->id,
            'role_ids' => $roleIds
        ]);
    }
}