<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'action',
        'is_system',
        'resource_type',
        'resource_id',
        'conditions'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'conditions' => 'array'
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withPivot('department_id', 'conditions', 'is_inherited')
            ->withTimestamps();
    }

    /**
     * Get the users that have this permission.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot('department_id', 'conditions', 'is_granted', 'granted_by')
            ->withTimestamps();
    }

    /**
     * Get the sub-roles that have this permission.
     */
    public function subRoles(): BelongsToMany
    {
        return $this->belongsToMany(SubRole::class, 'sub_role_permissions')
            ->withPivot('department_id', 'conditions', 'is_inherited')
            ->withTimestamps();
    }

    /**
     * Get the role permissions for this permission.
     */
    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /**
     * Get the user permissions for this permission.
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Get the sub-role permissions for this permission.
     */
    public function subRolePermissions(): HasMany
    {
        return $this->hasMany(SubRolePermission::class);
    }

    /**
     * Scope a query to only include permissions for a specific module.
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope a query to only include permissions for a specific action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include system permissions.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to only include non-system permissions.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope a query to only include permissions for a specific resource type.
     */
    public function scopeForResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope a query to only include permissions for a specific resource.
     */
    public function scopeForResource($query, string $resourceType, $resourceId)
    {
        return $query->where('resource_type', $resourceType)
                    ->where('resource_id', $resourceId);
    }

    /**
     * Check if a user has this permission.
     */
    public function isGrantedTo(User $user, ?Department $department = null): bool
    {
        // Check direct user permissions
        $userPermission = $this->userPermissions()
            ->where('user_id', $user->id)
            ->when($department, function ($query) use ($department) {
                return $query->where('department_id', $department->id);
            })
            ->first();

        if ($userPermission) {
            return $userPermission->is_granted;
        }

        // Check role-based permissions
        foreach ($user->roles as $role) {
            $rolePermission = $this->rolePermissions()
                ->where('role_id', $role->id)
                ->when($department, function ($query) use ($department) {
                    return $query->where('department_id', $department->id);
                })
                ->first();

            if ($rolePermission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grant this permission to a user.
     */
    public function grantTo(User $user, ?Department $department = null, array $conditions = []): void
    {
        $this->users()->syncWithoutDetaching([
            $user->id => [
                'department_id' => $department?->id,
                'conditions' => $conditions,
                'is_granted' => true
            ]
        ]);
    }

    /**
     * Revoke this permission from a user.
     */
    public function revokeFrom(User $user, ?Department $department = null): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'is_granted' => false
        ]);
    }

    /**
     * Check if a permission matches a specific action on a resource.
     */
    public function matchesAction(string $action, string $resourceType, $resourceId): bool
    {
        return $this->action === $action &&
               $this->resource_type === $resourceType &&
               $this->resource_id == $resourceId;
    }

    /**
     * Get all permissions for a specific module.
     */
    public static function getModulePermissions(string $module): Collection
    {
        return static::where('module', $module)->get();
    }

    /**
     * Get all permissions for a specific action.
     */
    public static function getActionPermissions(string $action): Collection
    {
        return static::where('action', $action)->get();
    }
} 