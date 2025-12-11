<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Role;

class SubRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'name',
        'description'
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function roleMenuActions(): HasMany
    {
        return $this->hasMany(RoleMenuAction::class);
    }

    public function menuActions(): BelongsToMany
    {
        return $this->belongsToMany(MenuAction::class, 'role_menu_actions');
    }

    /**
     * Get the permissions for this sub-role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'sub_role_permissions')
            ->withPivot('department_id', 'conditions', 'is_inherited')
            ->withTimestamps();
    }

    /**
     * Get the sub-role permissions for this sub-role.
     */
    public function subRolePermissions(): HasMany
    {
        return $this->hasMany(SubRolePermission::class);
    }

    /**
     * Check if this sub-role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if this sub-role has a specific permission in a department.
     */
    public function hasPermissionInDepartment(string $permission, Department $department): bool
    {
        return $this->permissions()
            ->where('name', $permission)
            ->wherePivot('department_id', $department->id)
            ->exists();
    }

    /**
     * Grant a permission to this sub-role for a specific department.
     */
    public function grantPermission(Permission $permission, Department $department = null, array $conditions = []): void
    {
        $this->permissions()->attach($permission->id, [
            'department_id' => $department ? $department->id : null,
            'conditions' => json_encode($conditions)
        ]);
    }

    /**
     * Revoke a permission from this sub-role for a specific department.
     */
    public function revokePermission(Permission $permission, Department $department = null): void
    {
        $query = $this->permissions()->where('permission_id', $permission->id);
        if ($department) {
            $query->wherePivot('department_id', $department->id);
        }
        $query->detach();
    }

    /**
     * Get all permissions for this sub-role, including inherited ones from the parent role.
     */
    public function getAllPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        $permissions = $this->permissions;

        // Include permissions from the parent role
        if ($this->role) {
            $parentPermissions = $this->role->getAllPermissions();
            $permissions = $permissions->merge($parentPermissions);
        }

        return $permissions->unique('id');
    }

    /**
     * Check if this sub-role has a specific permission, including inherited permissions.
     */
    public function hasPermissionIncludingInherited(string $permission): bool
    {
        return $this->getAllPermissions()->contains('name', $permission);
    }
}
