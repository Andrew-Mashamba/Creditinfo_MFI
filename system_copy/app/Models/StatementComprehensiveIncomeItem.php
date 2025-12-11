<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatementComprehensiveIncomeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'category',
        'sub_category',
        'account_number',
        'current_period_amount',
        'previous_period_amount',
        'year_to_date_amount',
        'budget_amount',
        'variance_amount',
        'variance_percentage',
        'description',
        'monthly_breakdown',
        'display_order',
        'is_calculated',
        'calculation_formula',
        'status',
        'period_start_date',
        'period_end_date',
        'reporting_period',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'monthly_breakdown' => 'array',
        'current_period_amount' => 'decimal:2',
        'previous_period_amount' => 'decimal:2',
        'year_to_date_amount' => 'decimal:2',
        'budget_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
        'is_calculated' => 'boolean',
        'approved_at' => 'datetime',
        'period_start_date' => 'date',
        'period_end_date' => 'date'
    ];

    /**
     * Get the account associated with this item
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountsModel::class, 'account_number', 'account_number');
    }

    /**
     * Scope for active items
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for revenue items
     */
    public function scopeRevenue($query)
    {
        return $query->where('category', 'revenue');
    }

    /**
     * Scope for expense items
     */
    public function scopeExpenses($query)
    {
        return $query->where('category', 'expenses');
    }

    /**
     * Calculate total revenue
     */
    public static function getTotalRevenue($reportingPeriod)
    {
        return self::active()
            ->revenue()
            ->where('reporting_period', $reportingPeriod)
            ->where('is_calculated', false)
            ->sum('current_period_amount');
    }

    /**
     * Calculate total expenses
     */
    public static function getTotalExpenses($reportingPeriod)
    {
        return self::active()
            ->expenses()
            ->where('reporting_period', $reportingPeriod)
            ->where('is_calculated', false)
            ->sum('current_period_amount');
    }

    /**
     * Calculate net income
     */
    public static function getNetIncome($reportingPeriod)
    {
        $revenue = self::getTotalRevenue($reportingPeriod);
        $expenses = self::getTotalExpenses($reportingPeriod);
        return $revenue - $expenses;
    }

    /**
     * Calculate variance
     */
    public function calculateVariance()
    {
        if ($this->budget_amount > 0) {
            $this->variance_amount = $this->current_period_amount - $this->budget_amount;
            $this->variance_percentage = ($this->variance_amount / $this->budget_amount) * 100;
            $this->save();
        }
    }

    /**
     * Update from GL
     */
    public function updateFromGL($startDate, $endDate)
    {
        if ($this->account_number && !$this->is_calculated) {
            $amount = \DB::table('general_ledger')
                ->where('account_number', $this->account_number)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('trans_status', 'POSTED')
                ->sum($this->category === 'revenue' ? 'credit' : 'debit');
            
            $this->current_period_amount = abs($amount);
            $this->save();
        }
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->item_code) {
                $model->item_code = self::generateItemCode($model->category, $model->sub_category);
            }
            $model->created_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * Generate unique item code
     */
    private static function generateItemCode($category, $subCategory)
    {
        $prefix = match($category) {
            'revenue' => 'R',
            'expenses' => 'E',
            'other_comprehensive_income' => 'OCI',
            default => 'X'
        };

        $lastItem = self::where('item_code', 'like', "$prefix-%")
            ->orderBy('item_code', 'desc')
            ->first();

        if ($lastItem) {
            $lastNumber = intval(substr($lastItem->item_code, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "$prefix-$newNumber";
    }
}