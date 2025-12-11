<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetScenario extends Model
{
    use HasFactory;
    
    protected $table = 'budget_scenarios';
    
    protected $fillable = [
        'budget_id',
        'scenario_name',
        'scenario_type',
        'base_amount',
        'adjustment_percentage',
        'projected_amount',
        'projected_utilization',
        'assumptions',
        'is_active',
        'created_by',
        'approved_by',
        'approved_at'
    ];
    
    protected $casts = [
        'base_amount' => 'decimal:2',
        'adjustment_percentage' => 'decimal:2',
        'projected_amount' => 'decimal:2',
        'projected_utilization' => 'decimal:2',
        'assumptions' => 'array',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
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
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the approver
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Calculate projected amount based on adjustment
     */
    public function calculateProjection()
    {
        $this->projected_amount = $this->base_amount * (1 + ($this->adjustment_percentage / 100));
        $this->save();
        return $this->projected_amount;
    }
    
    /**
     * Activate this scenario
     */
    public function activate()
    {
        // Deactivate other scenarios for this budget
        self::where('budget_id', $this->budget_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
        
        $this->is_active = true;
        $this->save();
        
        return $this;
    }
}