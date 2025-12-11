<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpeDepreciationSchedule extends Model
{
    use HasFactory;
    
    protected $table = 'ppe_depreciation_schedule';
    
    protected $fillable = [
        'ppe_id', 'period_year', 'period_month', 'opening_value',
        'depreciation_amount', 'closing_value', 'accumulated_depreciation',
        'is_posted', 'journal_reference', 'posting_date'
    ];
    
    protected $casts = [
        'opening_value' => 'decimal:2',
        'depreciation_amount' => 'decimal:2',
        'closing_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'is_posted' => 'boolean',
        'posting_date' => 'date'
    ];
    
    public function ppe()
    {
        return $this->belongsTo(PPE::class, 'ppe_id');
    }
    
    public function scopeUnposted($query)
    {
        return $query->where('is_posted', false);
    }
    
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('period_year', $year)
                    ->where('period_month', $month);
    }
    
    public function getPeriodDisplayAttribute()
    {
        return sprintf('%04d-%02d', $this->period_year, $this->period_month);
    }
}