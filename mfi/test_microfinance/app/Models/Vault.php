<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Vault extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'branch_id',
        'current_balance',
        'limit',
        'warning_threshold',
        'bank_name',
        'bank_account_number',
        'internal_account_number',
        'auto_bank_transfer',
        'requires_dual_approval',
        'send_alerts',
        'status',
        'description',
        'parent_account',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'limit' => 'decimal:2',
        'warning_threshold' => 'integer',
        'auto_bank_transfer' => 'boolean',
        'requires_dual_approval' => 'boolean',
        'send_alerts' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'active',
        'warning_threshold' => 80,
        'auto_bank_transfer' => false,
        'requires_dual_approval' => true,
        'send_alerts' => true,
    ];

    /**
     * Get the branch this vault belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchesModel::class, 'branch_id');
    }

    /**
     * Get the institution through branch
     */
    public function institution(): HasOneThrough
    {
        return $this->hasOneThrough(Institution::class, BranchesModel::class, 'id', 'id', 'branch_id', 'institution_id');
    }

    /**
     * Get cash movements related to this vault
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'vault_id');
    }

    /**
     * Get strongroom ledger entries
     */
    public function strongroomLedgers(): HasMany
    {
        return $this->hasMany(StrongroomLedger::class, 'vault_id');
    }

    /**
     * Check if vault is over the limit
     */
    public function isOverLimit(): bool
    {
        return $this->current_balance > $this->limit;
    }

    /**
     * Check if vault is at warning threshold
     */
    public function isAtWarningThreshold(): bool
    {
        $percentage = $this->limit > 0 ? ($this->current_balance / $this->limit) * 100 : 0;
        return $percentage >= $this->warning_threshold;
    }

    /**
     * Get vault utilization percentage
     */
    public function getUtilizationPercentageAttribute(): float
    {
        return $this->limit > 0 ? ($this->current_balance / $this->limit) * 100 : 0;
    }

    /**
     * Get available space in vault
     */
    public function getAvailableSpaceAttribute(): float
    {
        return max(0, $this->limit - $this->current_balance);
    }

    /**
     * Get excess amount if over limit
     */
    public function getExcessAmountAttribute(): float
    {
        return max(0, $this->current_balance - $this->limit);
    }

    /**
     * Get formatted current balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2);
    }

    /**
     * Get formatted limit
     */
    public function getFormattedLimitAttribute(): string
    {
        return number_format($this->limit, 2);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => $this->isOverLimit() ? 'red' : ($this->isAtWarningThreshold() ? 'yellow' : 'green'),
            'inactive' => 'gray',
            'maintenance' => 'yellow',
            'over_limit' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status_color) {
            'red' => 'bg-red-100 text-red-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'green' => 'bg-green-100 text-green-800',
            'gray' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Scope for active vaults
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for vaults by branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for over limit vaults
     */
    public function scopeOverLimit($query)
    {
        return $query->whereRaw('current_balance > `limit`');
    }
}
