<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PpeInsurance extends Model
{
    use HasFactory;
    
    protected $table = 'ppe_insurance';
    
    protected $fillable = [
        'ppe_id', 'policy_number', 'insurance_company', 'coverage_type', 'insured_value',
        'premium_amount', 'start_date', 'end_date', 'deductible', 'coverage_details',
        'agent_name', 'agent_contact', 'status', 'notes'
    ];
    
    protected $casts = [
        'insured_value' => 'decimal:2',
        'premium_amount' => 'decimal:2',
        'deductible' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    public function ppe()
    {
        return $this->belongsTo(PPE::class, 'ppe_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('end_date', '>=', now());
    }
    
    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('status', 'active')
                    ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }
    
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now())
                    ->where('status', '!=', 'cancelled');
    }
    
    public function isActive()
    {
        return $this->status === 'active' && $this->end_date >= now();
    }
    
    public function isExpiring($days = 30)
    {
        return $this->isActive() && 
               $this->end_date <= now()->addDays($days);
    }
    
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->isActive()) {
            return null;
        }
        
        return now()->diffInDays($this->end_date, false);
    }
    
    public function getCoverageTypeDisplayAttribute()
    {
        $types = [
            'comprehensive' => 'Comprehensive Coverage',
            'fire' => 'Fire Insurance',
            'theft' => 'Theft Coverage',
            'damage' => 'Damage Coverage',
            'liability' => 'Liability Insurance',
            'other' => 'Other Coverage'
        ];
        
        return $types[$this->coverage_type] ?? $this->coverage_type;
    }
    
    public function renew($newEndDate, $newPremium = null)
    {
        $this->update([
            'end_date' => $newEndDate,
            'premium_amount' => $newPremium ?? $this->premium_amount,
            'status' => 'active'
        ]);
    }
}