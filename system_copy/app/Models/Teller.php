<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'till_id',
        'status',
        'hired_date',
        'supervisor_id',
        'branch_id',
        'institution_id',
        'employee_id',
        'transaction_limit',
        'permissions',
        'last_login_at',
        'assigned_at',
        'assigned_by',
        'max_amount',
        'account_id',
        'registered_by_id',
        'progress_status',
        'teller_name',
    ];

    protected $casts = [
        'hired_date' => 'date',
        'permissions' => 'array',
        'last_login_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
        'transaction_limit' => 100000.00,
        'max_amount' => 100000.00,
    ];

    /**
     * Get the user associated with this teller
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the till assigned to this teller
     */
    public function till(): BelongsTo
    {
        return $this->belongsTo(Till::class);
    }

    /**
     * Get the supervisor for this teller
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the branch for this teller
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the institution for this teller
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the user who assigned this teller
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
