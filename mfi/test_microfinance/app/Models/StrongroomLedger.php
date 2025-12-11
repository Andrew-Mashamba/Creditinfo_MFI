<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrongroomLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'vault_id',
        'balance',
        'total_deposits',
        'total_withdrawals',
        'denomination_breakdown',
        'branch_id',
        'vault_code',
        'status',
        'notes',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_deposits' => 'decimal:2',
        'total_withdrawals' => 'decimal:2',
        'denomination_breakdown' => 'array',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Get the vault this ledger belongs to
     */
    public function vault()
    {
        return $this->belongsTo(Vault::class, 'vault_id');
    }

    /**
     * Get the branch this ledger belongs to
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    /**
     * Check if transaction is recent (within last 24 hours)
     */
    public function isRecentlyActive(): bool
    {
        if (!$this->last_transaction_at) {
            return false;
        }
        
        return $this->last_transaction_at->diffInHours(now()) < 24;
    }

    /**
     * Get hours since last transaction
     */
    public function getHoursSinceLastTransactionAttribute(): int
    {
        if (!$this->last_transaction_at) {
            return 999; // Indicates never had a transaction
        }
        
        return $this->last_transaction_at->diffInHours(now());
    }

    /**
     * Get activity status color for UI
     */
    public function getActivityStatusColorAttribute(): string
    {
        $hours = $this->hours_since_last_transaction;
        
        if ($hours > 48) {
            return 'red'; // Inactive
        } elseif ($hours > 24) {
            return 'yellow'; // Warning
        } else {
            return 'green'; // Active
        }
    }

    /**
     * Get total cash movement (deposits - withdrawals)
     */
    public function getTotalCashMovementAttribute(): float
    {
        return $this->total_deposits - $this->total_withdrawals;
    }

    /**
     * Get formatted denomination breakdown
     */
    public function getFormattedDenominationBreakdownAttribute(): string
    {
        if (!$this->denomination_breakdown) {
            return 'No breakdown available';
        }
        
        $breakdown = $this->denomination_breakdown;
        $formatted = [];
        
        foreach ($breakdown as $denomination => $count) {
            $formatted[] = "{$count} x {$denomination}";
        }
        
        return implode(', ', $formatted);
    }
}
