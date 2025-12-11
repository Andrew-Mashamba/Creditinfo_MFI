<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Till extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'till_number',
        'till_account_number',
        'branch_id',
        'current_balance',
        'opening_balance',
        'closing_balance',
        'variance',
        'variance_explanation',
        'maximum_limit',
        'minimum_limit',
        'status',
        'opened_at',
        'closed_at',
        'opened_by',
        'closed_by',
        'assigned_to',
        'assigned_user_id',
        'assigned_at',
        'assignment_notes',
        'denomination_breakdown',
        'requires_supervisor_approval',
        'description',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'variance' => 'decimal:2',
        'maximum_limit' => 'decimal:2',
        'minimum_limit' => 'decimal:2',
        'denomination_breakdown' => 'array',
        'requires_supervisor_approval' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the branch this till belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the institution this till belongs to
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the teller assigned to this till
     */
    public function teller(): BelongsTo
    {
        return $this->belongsTo(Teller::class);
    }

    /**
     * Get the user who opened this till
     */
    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Get the user who closed this till
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get the user assigned to this till
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user assigned to this till (new field)
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get all transactions for this till
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(TillTransaction::class);
    }

    /**
     * Get all reconciliations for this till
     */
    public function reconciliations(): HasMany
    {
        return $this->hasMany(TillReconciliation::class);
    }

    /**
     * Check if till is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if till is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Get formatted current balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2);
    }

    /**
     * Get the status color for UI display
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'green',
            'closed' => 'gray',
            'suspended' => 'red',
            default => 'gray'
        };
    }
}
