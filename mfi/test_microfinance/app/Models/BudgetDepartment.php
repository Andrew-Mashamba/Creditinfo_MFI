<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetDepartment extends Model
{
    use HasFactory;
    
    protected $table = 'budget_departments';
    
    protected $fillable = [
        'department_code',
        'department_name',
        'parent_department_id',
        'hierarchy_level',
        'cost_center',
        'manager_id',
        'total_budget_allocation',
        'total_spent',
        'is_active'
    ];
    
    protected $casts = [
        'total_budget_allocation' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'is_active' => 'boolean',
        'hierarchy_level' => 'integer'
    ];
    
    /**
     * Get parent department
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_department_id');
    }
    
    /**
     * Get child departments
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_department_id');
    }
    
    /**
     * Get department manager
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    /**
     * Get budgets for this department
     */
    public function budgets()
    {
        return $this->hasMany(BudgetManagement::class, 'budget_department_id');
    }
    
    /**
     * Get all descendant departments
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }
    
    /**
     * Get total allocation including children
     */
    public function getTotalAllocationWithChildrenAttribute()
    {
        $total = $this->total_budget_allocation;
        
        foreach ($this->children as $child) {
            $total += $child->total_allocation_with_children;
        }
        
        return $total;
    }
    
    /**
     * Get total spent including children
     */
    public function getTotalSpentWithChildrenAttribute()
    {
        $total = $this->total_spent;
        
        foreach ($this->children as $child) {
            $total += $child->total_spent_with_children;
        }
        
        return $total;
    }
    
    /**
     * Calculate department utilization
     */
    public function getUtilizationPercentageAttribute()
    {
        if ($this->total_budget_allocation <= 0) {
            return 0;
        }
        
        return round(($this->total_spent / $this->total_budget_allocation) * 100, 2);
    }
    
    /**
     * Update department totals
     */
    public function updateTotals()
    {
        $this->total_budget_allocation = $this->budgets()->sum('allocated_amount');
        $this->total_spent = $this->budgets()->sum('spent_amount');
        $this->save();
        
        // Update parent if exists
        if ($this->parent) {
            $this->parent->updateTotals();
        }
    }
}