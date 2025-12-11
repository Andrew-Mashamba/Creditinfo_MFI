<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'permission_id',
        'department_id',
        'conditions',
        'is_granted'
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_granted' => 'boolean'
    ];

    /**
     * Get the user that owns this permission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the permission that is assigned to this user.
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