<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialStatementItem extends Model
{
    protected $fillable = [
        'financial_period_id',
        'account_number',
        'account_name',
        'statement_type',
        'classification',
        'amount',
        'previous_period_amount',
        'variance_amount',
        'variance_percentage',
        'display_order',
        'indent_level',
        'is_subtotal',
        'is_total',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_period_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
        'is_subtotal' => 'boolean',
        'is_total' => 'boolean',
        'metadata' => 'json'
    ];

    /**
     * Get the financial period this item belongs to
     */
    public function financialPeriod(): BelongsTo
    {
        return $this->belongsTo(FinancialPeriod::class);
    }

    /**
     * Get the account associated with this item
     */
    public function account()
    {
        return \App\Models\accounts::where('account_number', $this->account_number)->first();
    }

    /**
     * Calculate variance from previous period
     */
    public function calculateVariance()
    {
        if ($this->previous_period_amount == 0) {
            $this->variance_amount = $this->amount;
            $this->variance_percentage = $this->amount > 0 ? 100 : 0;
        } else {
            $this->variance_amount = $this->amount - $this->previous_period_amount;
            $this->variance_percentage = ($this->variance_amount / abs($this->previous_period_amount)) * 100;
        }
        
        return $this;
    }

    /**
     * Scope for specific statement type
     */
    public function scopeForStatement($query, $statementType)
    {
        return $query->where('statement_type', $statementType);
    }

    /**
     * Scope for specific classification
     */
    public function scopeForClassification($query, $classification)
    {
        return $query->where('classification', $classification);
    }

    /**
     * Get formatted amount with proper sign
     */
    public function getFormattedAmountAttribute(): string
    {
        $amount = abs($this->amount);
        $formatted = number_format($amount, 2);
        
        // Apply parentheses for negative values in certain contexts
        if ($this->amount < 0) {
            return "(" . $formatted . ")";
        }
        
        return $formatted;
    }

    /**
     * Get CSS class for variance display
     */
    public function getVarianceClassAttribute(): string
    {
        if ($this->variance_percentage > 10) {
            return 'text-green-600';
        } elseif ($this->variance_percentage < -10) {
            return 'text-red-600';
        }
        
        return 'text-gray-600';
    }

    /**
     * Check if this item represents an asset
     */
    public function isAsset(): bool
    {
        return in_array($this->classification, ['current_asset', 'non_current_asset']);
    }

    /**
     * Check if this item represents a liability
     */
    public function isLiability(): bool
    {
        return in_array($this->classification, ['current_liability', 'non_current_liability']);
    }

    /**
     * Check if this item represents equity
     */
    public function isEquity(): bool
    {
        return $this->classification === 'equity';
    }

    /**
     * Check if this item represents revenue
     */
    public function isRevenue(): bool
    {
        return $this->classification === 'revenue';
    }

    /**
     * Check if this item represents an expense
     */
    public function isExpense(): bool
    {
        return $this->classification === 'expense';
    }
}