<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use App\Models\departmentsList;

class Role extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'name',
    //     'department_id',
    //     'description',
    //     'level',
    //     'is_system_role',
    //     'institution_id',
    //     'parent_role_id',
    //     'permission_inheritance_enabled',
    //     'department_specific',
    //     'conditions'
    // ];

    protected $guarded = [];

    protected $table = 'roles';

    protected $casts = [
        'is_system_role' => 'boolean',
        'level' => 'integer',
        'permission_inheritance_enabled' => 'boolean',
        'department_specific' => 'boolean',
        'conditions' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if ($role->parent_role_id) {
                $parent = Role::find($role->parent_role_id);
                $role->level = $parent->level + 1;
            } else {
                $role->level = 1;
            }
        });

        static::updating(function ($role) {
            if ($role->isDirty('parent_role_id')) {
                $parent = Role::find($role->parent_role_id);
                $role->level = $parent ? $parent->level + 1 : 1;
            }
        });
    }

    /**
     * Get the department this role belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(departmentsList::class, 'department_id', 'id');
    }

    /**
     * Get the users with this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withTimestamps();
    }

    /**
     * Get the permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Get the menu actions for this role.
     */
    public function menuActions(): HasMany
    {
        return $this->hasMany(RoleMenuAction::class);
    }


    /**
     * Get the sub-roles for this role.
     */
    public function subRoles(): HasMany
    {
        return $this->hasMany(SubRole::class);
    }

    /**
     * Get the institution this role belongs to.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if this role is a system role.
     */
    public function isSystemRole(): bool
    {
        return $this->is_system_role;
    }

    /**
     * Scope a query to only include system roles.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system_role', true);
    }

    /**
     * Scope a query to only include department-specific roles.
     */
    public function scopeDepartment($query)
    {
        return $query->where('is_system_role', false);
    }

    /**
     * Scope a query to only include roles for a specific department.
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include roles for a specific institution.
     */
    public function scopeForInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    /**
     * Get the parent role.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    /**
     * Get the child roles.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    /**
     * Get all ancestor roles.
     */
    public function ancestors()
    {
        return Role::whereRaw("? LIKE CONCAT(path, '%')", [$this->path])
            ->where('id', '!=', $this->id)
            ->orderBy('level');
    }

    /**
     * Get all descendant roles.
     */
    public function descendants()
    {
        return Role::whereRaw("path LIKE ?", [$this->path . '%'])
            ->where('id', '!=', $this->id)
            ->orderBy('level');
    }

    /**
     * Get all permissions for this role, including inherited ones.
     */
    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;

        if ($this->permission_inheritance_enabled && $this->parent_role_id) {
            $parentRole = Role::find($this->parent_role_id);
            if ($parentRole) {
                $permissions = $permissions->merge($parentRole->getAllPermissions());
            }
        }

        return $permissions->unique('id');
    }

    /**
     * Check if this role inherits from another role.
     */
    public function inheritsFrom(Role $role): bool
    {
        return strpos($this->path, $role->path) === 0;
    }

    /**
     * Get all roles that inherit from this role.
     */
    public function getInheritingRoles()
    {
        return Role::whereRaw("path LIKE ?", [$this->path . '%'])
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Get all roles that this role inherits from.
     */
    public function getInheritedRoles()
    {
        return Role::whereRaw("? LIKE CONCAT(path, '%')", [$this->path])
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Check if this role has a specific permission, including inherited permissions.
     */
    public function hasPermissionIncludingInherited(string $permission): bool
    {
        return $this->getAllPermissions()->contains('name', $permission);
    }

    /**
     * Get all users with this role or any of its descendant roles.
     */
    public function getAllUsers()
    {
        return User::whereIn('id', function($query) {
            $query->select('user_id')
                ->from('user_roles')
                ->whereIn('role_id', function($subQuery) {
                    $subQuery->select('id')
                        ->from('roles')
                        ->whereRaw("path LIKE ?", [$this->path . '%']);
                });
        })->get();
    }

    /**
     * Get department-specific permissions for this role.
     */
    public function getDepartmentPermissions(Department $department): Collection
    {
        return $this->permissions()
            ->wherePivot('department_id', $department->id)
            ->get();
    }

    /**
     * Check if this role has a specific permission in a department.
     */
    public function hasPermissionInDepartment(string $permission, Department $department): bool
    {
        return $this->permissions()
            ->where('name', $permission)
            ->wherePivot('department_id', $department->id)
            ->exists();
    }

    /**
     * Grant a permission to this role for a specific department.
     */
    public function grantPermission(Permission $permission, Department $department = null, array $constraints = []): void
    {
        $this->permissions()->attach($permission->id, [
            'department_id' => $department ? $department->id : null,
            'constraints' => json_encode($constraints)
        ]);
    }

    /**
     * Revoke a permission from this role for a specific department.
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
     * Get all child roles that inherit from this role.
     */
    public function childRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    /**
     * Get all descendant roles (children, grandchildren, etc.).
     */
    public function getAllDescendants(): Collection
    {
        $descendants = collect();
        $children = $this->childRoles;

        foreach ($children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    /**
     * Check if this role is a descendant of another role.
     */
    public function isDescendantOf(Role $role): bool
    {
        $current = $this;
        while ($current->parent_role_id) {
            if ($current->parent_role_id === $role->id) {
                return true;
            }
            $current = Role::find($current->parent_role_id);
        }
        return false;
    }

    /**
     * Scope a query to only include roles that are department-specific.
     */
    public function scopeDepartmentSpecific($query)
    {
        return $query->where('department_specific', true);
    }

    /**
     * Scope a query to only include roles that have permission inheritance enabled.
     */
    public function scopeWithInheritance($query)
    {
        return $query->where('permission_inheritance_enabled', true);
    }
}
