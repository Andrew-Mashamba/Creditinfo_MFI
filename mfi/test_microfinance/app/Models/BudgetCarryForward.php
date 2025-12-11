<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCarryForward extends Model
{
    use HasFactory;
    
    protected $table = 'budget_carry_forwards';
    
    protected $fillable = [
        'from_budget_id',
        'to_budget_id',
        'from_year',
        'to_year',
        'carry_forward_amount',
        'utilized_amount',
        'remaining_amount',
        'carry_type',
        'justification',
        'status',
        'approved_by',
        'approved_at',
        'expiry_date'
    ];
    
    protected $casts = [
        'carry_forward_amount' => 'decimal:2',
        'utilized_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the source budget
     */
    public function fromBudget()
    {
        return $this->belongsTo(BudgetManagement::class, 'from_budget_id');
    }
    
    /**
     * Get the destination budget
     */
    public function toBudget()
    {
        return $this->belongsTo(BudgetManagement::class, 'to_budget_id');
    }
    
    /**
     * Get the approver
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}