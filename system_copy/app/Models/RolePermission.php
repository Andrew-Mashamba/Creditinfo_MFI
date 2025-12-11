<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'permission_id',
        'department_id',
        'conditions'
    ];

    protected $casts = [
        'conditions' => 'array'
    ];

    /**
     * Get the role that owns this permission.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the permission that is assigned to this role.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the department that this permission is assigned to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
} 