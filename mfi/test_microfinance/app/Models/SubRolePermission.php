<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubRolePermission extends Model
{
    protected $fillable = [
        'sub_role_id',
        'permission_id',
        'department_id',
        'conditions',
        'is_inherited'
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_inherited' => 'boolean'
    ];

    /**
     * Get the sub-role that owns this permission.
     */
    public function subRole(): BelongsTo
    {
        return $this->belongsTo(SubRole::class);
    }

    /**
     * Get the permission that is assigned to this sub-role.
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