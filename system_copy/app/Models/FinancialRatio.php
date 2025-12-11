<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialRatio extends Model
{
    protected $fillable = [
        'financial_period_id',
        'ratio_category',
        'ratio_name',
        'value',
        'formula',
        'benchmark_value',
        'trend',
        'interpretation'
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'benchmark_value' => 'decimal:4'
    ];

    /**
     * Get the financial period this ratio belongs to
     */
    public function financialPeriod(): BelongsTo
    {
        return $this->belongsTo(FinancialPeriod::class);
    }

    /**
     * Get ratios by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('ratio_category', $category);
    }

    /**
     * Calculate trend based on previous period
     */
    public function calculateTrend()
    {
        $previousPeriod = $this->financialPeriod->getPreviousPeriod();
        
        if (!$previousPeriod) {
            $this->trend = 'stable';
            return;
        }
        
        $previousRatio = self::where('financial_period_id', $previousPeriod->id)
            ->where('ratio_name', $this->ratio_name)
            ->first();
        
        if (!$previousRatio) {
            $this->trend = 'stable';
            return;
        }
        
        $change = (($this->value - $previousRatio->value) / abs($previousRatio->value)) * 100;
        
        if ($change > 5) {
            $this->trend = 'improving';
        } elseif ($change < -5) {
            $this->trend = 'declining';
        } else {
            $this->trend = 'stable';
        }
        
        $this->save();
    }

    /**
     * Get performance indicator
     */
    public function getPerformanceIndicatorAttribute()
    {
        if (!$this->benchmark_value) {
            return 'neutral';
        }
        
        $variance = (($this->value - $this->benchmark_value) / abs($this->benchmark_value)) * 100;
        
        // Different ratios have different interpretations
        $higherIsBetter = in_array($this->ratio_name, [
            'Current Ratio',
            'Quick Ratio',
            'Return on Assets (ROA)',
            'Return on Equity (ROE)',
            'Net Profit Margin',
            'Gross Profit Margin',
            'Operating Cash Flow Ratio'
        ]);
        
        $lowerIsBetter = in_array($this->ratio_name, [
            'Debt to Equity Ratio',
            'Debt Ratio',
            'Operating Expense Ratio'
        ]);
        
        if ($higherIsBetter) {
            if ($variance >= 10) return 'excellent';
            if ($variance >= 0) return 'good';
            if ($variance >= -10) return 'fair';
            return 'poor';
        }
        
        if ($lowerIsBetter) {
            if ($variance <= -10) return 'excellent';
            if ($variance <= 0) return 'good';
            if ($variance <= 10) return 'fair';
            return 'poor';
        }
        
        return 'neutral';
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValueAttribute()
    {
        // Percentage ratios
        if (str_contains($this->ratio_name, 'Margin') || 
            str_contains($this->ratio_name, 'Return') ||
            str_contains($this->ratio_name, 'ROA') ||
            str_contains($this->ratio_name, 'ROE')) {
            return number_format($this->value, 2) . '%';
        }
        
        // Times ratios
        if (str_contains($this->ratio_name, 'Turnover')) {
            return number_format($this->value, 2) . ' times';
        }
        
        // Days ratios
        if (str_contains($this->ratio_name, 'Days')) {
            return number_format($this->value, 0) . ' days';
        }
        
        // Regular ratios
        return number_format($this->value, 2) . ':1';
    }

    /**
     * Standard financial ratios to calculate
     */
    public static function getStandardRatios()
    {
        return [
            'liquidity' => [
                'Current Ratio' => 'Current Assets / Current Liabilities',
                'Quick Ratio' => '(Current Assets - Inventory) / Current Liabilities',
                'Cash Ratio' => 'Cash and Cash Equivalents / Current Liabilities',
                'Operating Cash Flow Ratio' => 'Operating Cash Flow / Current Liabilities'
            ],
            'solvency' => [
                'Debt to Equity Ratio' => 'Total Debt / Total Equity',
                'Debt Ratio' => 'Total Debt / Total Assets',
                'Interest Coverage Ratio' => 'EBIT / Interest Expense',
                'Equity Ratio' => 'Total Equity / Total Assets'
            ],
            'profitability' => [
                'Net Profit Margin' => '(Net Income / Revenue) × 100',
                'Gross Profit Margin' => '(Gross Profit / Revenue) × 100',
                'Return on Assets (ROA)' => '(Net Income / Total Assets) × 100',
                'Return on Equity (ROE)' => '(Net Income / Total Equity) × 100',
                'Operating Margin' => '(Operating Income / Revenue) × 100'
            ],
            'efficiency' => [
                'Asset Turnover' => 'Revenue / Average Total Assets',
                'Receivables Turnover' => 'Revenue / Average Accounts Receivable',
                'Inventory Turnover' => 'Cost of Goods Sold / Average Inventory',
                'Days Sales Outstanding' => '(Accounts Receivable / Revenue) × 365',
                'Days Inventory Outstanding' => '(Inventory / COGS) × 365'
            ]
        ];
    }
}