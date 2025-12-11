<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetAllocation extends Model
{
    use HasFactory;
    
    protected $table = 'budget_allocations';
    
    protected $fillable = [
        'budget_id',
        'allocation_type',
        'period',
        'year',
        'allocated_amount',
        'utilized_amount',
        'available_amount',
        'rollover_amount',
        'advance_amount',
        'transferred_in',
        'transferred_out',
        'supplementary_amount',
        'percentage',
        'notes',
        'rollover_policy',
        'is_locked',
        'locked_at',
        'locked_by'
    ];
    
    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'utilized_amount' => 'decimal:2',
        'available_amount' => 'decimal:2',
        'rollover_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'transferred_in' => 'decimal:2',
        'transferred_out' => 'decimal:2',
        'supplementary_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the budget
     */
    public function budget()
    {
        return $this->belongsTo(BudgetManagement::class, 'budget_id');
    }
    
    /**
     * Get the locker user
     */
    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
    
    /**
     * Calculate total available including rollover and advances
     */
    public function getTotalAvailableAttribute()
    {
        return $this->allocated_amount 
            + $this->rollover_amount 
            + $this->advance_amount 
            + $this->transferred_in
            + $this->supplementary_amount
            - $this->transferred_out
            - $this->utilized_amount;
    }
    
    /**
     * Get utilization percentage
     */
    public function getUtilizationPercentageAttribute()
    {
        if ($this->allocated_amount == 0) {
            return 0;
        }
        return round(($this->utilized_amount / $this->allocated_amount) * 100, 2);
    }
    
    /**
     * Check if can rollover to next period
     */
    public function canRollover()
    {
        return $this->rollover_policy !== 'NO_ROLLOVER' && $this->available_amount > 0;
    }
    
    /**
     * Process rollover to next period
     */
    public function processRollover($nextPeriodAllocation)
    {
        if (!$this->canRollover()) {
            return false;
        }
        
        $rolloverAmount = $this->available_amount;
        
        if ($this->rollover_policy === 'AUTOMATIC') {
            $nextPeriodAllocation->rollover_amount += $rolloverAmount;
            $nextPeriodAllocation->save();
            
            $this->available_amount = 0;
            $this->save();
            
            return true;
        }
        
        // For APPROVAL_REQUIRED, create a pending rollover request
        return $rolloverAmount;
    }
    
    /**
     * Request advance from future period
     */
    public function requestAdvance($amount, $fromPeriod, $fromYear)
    {
        // This will be handled by BudgetAdvance model
        return BudgetAdvance::create([
            'budget_id' => $this->budget_id,
            'to_period' => $this->period,
            'to_year' => $this->year,
            'from_period' => $fromPeriod,
            'from_year' => $fromYear,
            'advance_amount' => $amount,
            'outstanding_amount' => $amount,
            'status' => 'PENDING',
            'requested_by' => auth()->id()
        ]);
    }
    
    /**
     * Apply supplementary allocation
     */
    public function applySupplementary($amount, $notes = null)
    {
        $this->supplementary_amount += $amount;
        $this->available_amount += $amount;
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . 'Supplementary: ' . $notes;
        }
        
        return $this->save();
    }
}