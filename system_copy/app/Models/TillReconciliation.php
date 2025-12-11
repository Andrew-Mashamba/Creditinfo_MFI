<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TillReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'till_id',
        'teller_id',
        'supervisor_id',
        'reconciliation_date',
        'opening_balance',
        'closing_balance_system',
        'closing_balance_actual',
        'total_deposits',
        'total_withdrawals',
        'total_transfers_in',
        'total_transfers_out',
        'variance',
        'denomination_breakdown',
        'transaction_count',
        'status',
        'variance_explanation',
        'supervisor_notes',
        'submitted_at',
        'approved_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance_system' => 'decimal:2',
        'closing_balance_actual' => 'decimal:2',
        'total_deposits' => 'decimal:2',
        'total_withdrawals' => 'decimal:2',
        'total_transfers_in' => 'decimal:2',
        'total_transfers_out' => 'decimal:2',
        'variance' => 'decimal:2',
        'denomination_breakdown' => 'array',
        'reconciliation_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the till for this reconciliation
     */
    public function till(): BelongsTo
    {
        return $this->belongsTo(Till::class);
    }

    /**
     * Get the teller who performed the reconciliation
     */
    public function teller(): BelongsTo
    {
        return $this->belongsTo(Teller::class);
    }

    /**
     * Get the supervisor who reviewed the reconciliation
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Check if reconciliation has a variance
     */
    public function hasVariance(): bool
    {
        return $this->variance != 0;
    }

    /**
     * Check if reconciliation is over (more cash counted than expected)
     */
    public function isOver(): bool
    {
        return $this->variance > 0;
    }

    /**
     * Check if reconciliation is short (less cash counted than expected)
     */
    public function isShort(): bool
    {
        return $this->variance < 0;
    }

    /**
     * Check if reconciliation is balanced
     */
    public function isBalanced(): bool
    {
        return $this->variance == 0;
    }

    /**
     * Get formatted variance amount
     */
    public function getFormattedVarianceAttribute(): string
    {
        return number_format(abs($this->variance), 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'balanced' => 'green',
            'over' => 'blue',
            'short' => 'red',
            'pending_approval' => 'yellow',
            'approved' => 'green',
            default => 'gray'
        };
    }
} 