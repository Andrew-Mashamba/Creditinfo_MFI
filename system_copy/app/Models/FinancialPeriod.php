<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class FinancialPeriod extends Model
{
    protected $fillable = [
        'year',
        'month',
        'quarter',
        'start_date',
        'end_date',
        'period_type',
        'status',
        'is_audited',
        'closed_at',
        'closed_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
        'is_audited' => 'boolean'
    ];

    /**
     * Get all statement snapshots for this period
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(FinancialStatementSnapshot::class);
    }

    /**
     * Get all statement items for this period
     */
    public function items(): HasMany
    {
        return $this->hasMany(FinancialStatementItem::class);
    }

    /**
     * Get all financial ratios for this period
     */
    public function ratios(): HasMany
    {
        return $this->hasMany(FinancialRatio::class);
    }

    /**
     * Get all notes for this period
     */
    public function notes(): HasMany
    {
        return $this->hasMany(FinancialStatementNote::class);
    }

    /**
     * Get all statement relationships for this period
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(StatementRelationship::class);
    }

    /**
     * Get all consolidation entries for this period
     */
    public function consolidationEntries(): HasMany
    {
        return $this->hasMany(ConsolidationEntry::class);
    }

    /**
     * Get audit trail for this period
     */
    public function auditTrail(): HasMany
    {
        return $this->hasMany(FinancialStatementAuditTrail::class);
    }

    /**
     * Get or create a financial period for a given date
     */
    public static function getOrCreateForDate($date, $periodType = 'annual')
    {
        $carbonDate = Carbon::parse($date);
        
        $query = self::where('period_type', $periodType);
        
        switch ($periodType) {
            case 'annual':
                $startDate = $carbonDate->copy()->startOfYear();
                $endDate = $carbonDate->copy()->endOfYear();
                $query->where('year', $carbonDate->year);
                break;
                
            case 'quarterly':
                $quarter = $carbonDate->quarter;
                $startDate = $carbonDate->copy()->firstOfQuarter();
                $endDate = $carbonDate->copy()->lastOfQuarter();
                $query->where('year', $carbonDate->year)
                      ->where('quarter', $quarter);
                break;
                
            case 'monthly':
                $startDate = $carbonDate->copy()->startOfMonth();
                $endDate = $carbonDate->copy()->endOfMonth();
                $query->where('year', $carbonDate->year)
                      ->where('month', $carbonDate->month);
                break;
        }
        
        $period = $query->first();
        
        if (!$period) {
            $period = self::create([
                'year' => $carbonDate->year,
                'month' => $periodType === 'monthly' ? $carbonDate->month : null,
                'quarter' => $periodType === 'quarterly' ? $carbonDate->quarter : null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_type' => $periodType,
                'status' => 'draft'
            ]);
        }
        
        return $period;
    }

    /**
     * Get the previous period
     */
    public function getPreviousPeriod()
    {
        $query = self::where('period_type', $this->period_type)
                    ->where('end_date', '<', $this->start_date)
                    ->orderBy('end_date', 'desc');
        
        return $query->first();
    }

    /**
     * Check if period is closed
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'published']);
    }

    /**
     * Close the period
     */
    public function close($userId = null)
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $userId
        ]);
    }

    /**
     * Get display name for the period
     */
    public function getDisplayNameAttribute(): string
    {
        switch ($this->period_type) {
            case 'annual':
                return "Year {$this->year}";
            case 'quarterly':
                return "Q{$this->quarter} {$this->year}";
            case 'monthly':
                return Carbon::createFromDate($this->year, $this->month)->format('F Y');
            default:
                return "{$this->start_date->format('M d, Y')} - {$this->end_date->format('M d, Y')}";
        }
    }
}