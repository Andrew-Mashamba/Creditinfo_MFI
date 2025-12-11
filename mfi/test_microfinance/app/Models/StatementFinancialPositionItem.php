<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatementFinancialPositionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'category',
        'sub_category',
        'account_number',
        'amount',
        'previous_year_amount',
        'description',
        'breakdown',
        'display_order',
        'is_calculated',
        'calculation_formula',
        'status',
        'reporting_date',
        'reporting_period',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'breakdown' => 'array',
        'amount' => 'decimal:2',
        'previous_year_amount' => 'decimal:2',
        'is_calculated' => 'boolean',
        'approved_at' => 'datetime',
        'reporting_date' => 'date'
    ];

    /**
     * Get the account associated with this item
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountsModel::class, 'account_number', 'account_number');
    }

    /**
     * Get the user who created this item
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this item
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who approved this item
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for active items
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for specific reporting period
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('reporting_period', $period);
    }

    /**
     * Calculate total for a category
     */
    public static function getCategoryTotal($category, $reportingPeriod)
    {
        return self::active()
            ->category($category)
            ->forPeriod($reportingPeriod)
            ->where('is_calculated', false)
            ->sum('amount');
    }

    /**
     * Get assets
     */
    public static function getAssets($reportingPeriod)
    {
        return self::active()
            ->category('assets')
            ->forPeriod($reportingPeriod)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get liabilities
     */
    public static function getLiabilities($reportingPeriod)
    {
        return self::active()
            ->category('liabilities')
            ->forPeriod($reportingPeriod)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get equity
     */
    public static function getEquity($reportingPeriod)
    {
        return self::active()
            ->category('equity')
            ->forPeriod($reportingPeriod)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Calculate and update item amount from GL
     */
    public function updateAmountFromGL()
    {
        if ($this->account_number && !$this->is_calculated) {
            $account = AccountsModel::where('account_number', $this->account_number)->first();
            if ($account) {
                $this->amount = $account->balance;
                $this->save();
            }
        }
    }

    /**
     * Boot method for model events
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

        // Log changes to audit trail
        static::created(function ($model) {
            self::logAudit('create', $model);
        });

        static::updated(function ($model) {
            self::logAudit('update', $model);
        });

        static::deleted(function ($model) {
            self::logAudit('delete', $model);
        });
    }

    /**
     * Generate unique item code
     */
    private static function generateItemCode($category, $subCategory)
    {
        $prefix = match($category) {
            'assets' => 'A',
            'liabilities' => 'L',
            'equity' => 'E',
            default => 'X'
        };

        $subPrefix = match($subCategory) {
            'current_assets' => 'CA',
            'non_current_assets' => 'NCA',
            'property_plant_equipment' => 'PPE',
            'current_liabilities' => 'CL',
            'non_current_liabilities' => 'NCL',
            'share_capital' => 'SC',
            'retained_earnings' => 'RE',
            'reserves' => 'RES',
            default => 'XX'
        };

        $lastItem = self::where('item_code', 'like', "$prefix-$subPrefix-%")
            ->orderBy('item_code', 'desc')
            ->first();

        if ($lastItem) {
            $lastNumber = intval(substr($lastItem->item_code, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "$prefix-$subPrefix-$newNumber";
    }

    /**
     * Log audit trail
     */
    private static function logAudit($action, $model)
    {
        \DB::table('financial_statement_audit_logs')->insert([
            'table_name' => $model->getTable(),
            'record_id' => $model->id,
            'action' => $action,
            'old_values' => $action === 'update' ? json_encode($model->getOriginal()) : null,
            'new_values' => $action !== 'delete' ? json_encode($model->getAttributes()) : null,
            'user_id' => auth()->id() ?? 0,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
}