<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'departments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'department_name',
    //     'department_code',
    //     'parent_department_id',
    //     'description',
    //     'status',
    //     'level',
    //     'path',
    //     'institution_id'
    // ];

    protected $guarded = [];

    protected $casts = [
        'level' => 'integer',
        'status' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            if ($department->parent_department_id) {
                $parent = Department::find($department->parent_department_id);
                $department->level = $parent->level + 1;
                $department->path = $parent->path . '.' . $parent->id;
            } else {
                $department->level = 1;
                $department->path = '0';
            }
        });

        static::updating(function ($department) {
            if ($department->isDirty('parent_department_id')) {
                $oldParent = Department::find($department->getOriginal('parent_department_id'));
                $newParent = Department::find($department->parent_department_id);
                
                if ($newParent) {
                    $department->level = $newParent->level + 1;
                    $department->path = $newParent->path . '.' . $newParent->id;
                } else {
                    $department->level = 1;
                    $department->path = '0';
                }

                // Update all descendants' paths (SQL injection safe with parameter binding)
                $oldPath = $oldParent ? $oldParent->path . '.' . $oldParent->id : '0';
                $newPath = $newParent ? $newParent->path . '.' . $newParent->id : '0';

                // Use parameterized query to prevent SQL injection
                DB::statement(
                    "UPDATE departments SET path = REPLACE(path, ?, ?) WHERE path LIKE ?",
                    [$oldPath, $newPath, $oldPath . '.%']
                );
            }
        });
    }

    /**
     * Get the parent department.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    /**
     * Get the child departments.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    /**
     * Get all ancestor departments.
     */
    public function ancestors()
    {
        return Department::whereRaw("? LIKE CONCAT(path, '%')", [$this->path])
            ->where('id', '!=', $this->id)
            ->orderBy('level');
    }

    /**
     * Get all descendant departments.
     */
    public function descendants()
    {
        return Department::whereRaw("path LIKE ?", [$this->path . '%'])
            ->where('id', '!=', $this->id)
            ->orderBy('level');
    }

    /**
     * Get the roles associated with this department.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the committees associated with this department.
     */
    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    /**
     * Get the users in this department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_code', 'department_code');
    }

    /**
     * Get the institution this department belongs to.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }



    /**
     * Scope a query to only include active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include root departments.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_department_id');
    }

    /**
     * Get the full department path as a string.
     */
    public function getFullPathAttribute(): string
    {
        return $this->ancestors()
            ->pluck('department_name')
            ->push($this->department_name)
            ->implode(' > ');
    }

    /**
     * Check if this department is a child of another department.
     */
    public function isChildOf(Department $department): bool
    {
        return $this->parent_department_id === $department->id;
    }

    /**
     * Check if this department is a parent of another department.
     */
    public function isParentOf(Department $department): bool
    {
        return $department->parent_department_id === $this->id;
    }

    /**
     * Check if this department is an ancestor of another department.
     */
    public function isAncestorOf(Department $department): bool
    {
        return strpos($department->path, $this->path) === 0;
    }

    /**
     * Check if this department is a descendant of another department.
     */
    public function isDescendantOf(Department $department): bool
    {
        return strpos($this->path, $department->path) === 0;
    }

    /**
     * Get the role menu actions for the department.
     */
    public function roleMenuActions()
    {
        return $this->hasMany(RoleMenuAction::class, 'sub_role', 'sub_role');
    }

    /**
     * Get all ancestor departments with their roles and committees.
     */
    public function ancestorsWithRelations()
    {
        return $this->ancestors()
            ->with(['roles', 'committees'])
            ->get();
    }

    /**
     * Get all descendant departments with their roles and committees.
     */
    public function descendantsWithRelations()
    {
        return $this->descendants()
            ->with(['roles', 'committees'])
            ->get();
    }

    /**
     * Get all roles available in this department and its ancestors.
     */
    public function getAllAvailableRoles()
    {
        return Role::whereIn('department_id', function($query) {
            $query->select('id')
                ->from('departments')
                ->whereRaw("path LIKE ?", [$this->path . '%']);
        })->get();
    }

    /**
     * Get all committees available in this department and its ancestors.
     */
    public function getAllAvailableCommittees()
    {
        return Committee::whereIn('department_id', function($query) {
            $query->select('id')
                ->from('departments')
                ->whereRaw("path LIKE ?", [$this->path . '%']);
        })->get();
    }

    /**
     * Check if this department has any active roles.
     */
    public function hasActiveRoles(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Check if this department has any active committees.
     */
    public function hasActiveCommittees(): bool
    {
        return $this->committees()->active()->exists();
    }

    /**
     * Get the root department of this department.
     */
    public function getRootDepartment(): ?Department
    {
        return Department::whereRaw("? LIKE CONCAT(path, '%')", [$this->path])
            ->whereNull('parent_department_id')
            ->first();
    }

    public function getAllChildren()
    {
        return $this->children()->with('children');
    }

    public function getFullPath()
    {
        $path = [$this->department_name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->department_name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }
}
