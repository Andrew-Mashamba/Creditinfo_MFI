<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'type',
        'from_till_id',
        'to_till_id',
        'strongroom_ledger_id',
        'user_id',
        'initiated_by',
        'approved_by',
        'amount',
        'denomination_breakdown',
        'reason',
        'status',
        'approved_at',
        'completed_at',
        'notes',
        'approval_id',
        'to_vault_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'denomination_breakdown' => 'array',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the source till if movement is from a till
     */
    public function fromTill(): BelongsTo
    {
        return $this->belongsTo(Till::class, 'from_till_id');
    }

    /**
     * Get the destination till if movement is to a till
     */
    public function toTill(): BelongsTo
    {
        return $this->belongsTo(Till::class, 'to_till_id');
    }

    /**
     * Get the strongroom ledger for vault transactions
     */
    public function strongroomLedger(): BelongsTo
    {
        return $this->belongsTo(StrongroomLedger::class);
    }

    /**
     * Get the user who initiated this movement
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the user who initiated this movement (alias for compatibility)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the user who approved this movement
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get the movement type description
     */
    public function getMovementTypeAttribute(): string
    {
        return str_replace('_', ' ', ucwords($this->type, '_'));
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'blue',
            'completed' => 'green',
            'rejected' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }
} 