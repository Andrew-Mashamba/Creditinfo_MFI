<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BudgetAdvance extends Model
{
    use HasFactory;
    
    protected $table = 'budget_advances';
    
    protected $fillable = [
        'advance_number',
        'budget_id',
        'from_period',
        'from_year',
        'to_period',
        'to_year',
        'advance_amount',
        'repaid_amount',
        'outstanding_amount',
        'reason',
        'status',
        'due_date',
        'repayment_method',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason'
    ];
    
    protected $casts = [
        'advance_amount' => 'decimal:2',
        'repaid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->advance_number) {
                $model->advance_number = 'ADV-' . date('Ym') . '-' . str_pad(
                    (static::whereYear('created_at', date('Y'))->count() + 1),
                    4,
                    '0',
                    STR_PAD_LEFT
                );
            }
            
            if (!$model->outstanding_amount) {
                $model->outstanding_amount = $model->advance_amount;
            }
        });
    }
    
    /**
     * Get the budget
     */
    public function budget()
    {
        return $this->belongsTo(BudgetManagement::class, 'budget_id');
    }
    
    /**
     * Get the requester
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    /**
     * Get the approver
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get source allocation
     */
    public function sourceAllocation()
    {
        return BudgetAllocation::where('budget_id', $this->budget_id)
            ->where('period', $this->from_period)
            ->where('year', $this->from_year)
            ->first();
    }
    
    /**
     * Get destination allocation
     */
    public function destinationAllocation()
    {
        return BudgetAllocation::where('budget_id', $this->budget_id)
            ->where('period', $this->to_period)
            ->where('year', $this->to_year)
            ->first();
    }
    
    /**
     * Approve the advance
     */
    public function approve($notes = null)
    {
        if ($this->status !== 'PENDING') {
            throw new \Exception('Only pending advances can be approved');
        }
        
        // Check if source allocation has enough funds
        $sourceAllocation = $this->sourceAllocation();
        if (!$sourceAllocation || $sourceAllocation->available_amount < $this->advance_amount) {
            throw new \Exception('Insufficient funds in source period');
        }
        
        // Deduct from source
        $sourceAllocation->available_amount -= $this->advance_amount;
        $sourceAllocation->save();
        
        // Add to destination
        $destAllocation = $this->destinationAllocation();
        if ($destAllocation) {
            $destAllocation->advance_amount += $this->advance_amount;
            $destAllocation->available_amount += $this->advance_amount;
            $destAllocation->save();
        }
        
        // Update advance status
        $this->status = 'APPROVED';
        $this->approved_by = auth()->id();
        $this->approved_at = now();
        $this->approval_notes = $notes;
        
        return $this->save();
    }
    
    /**
     * Reject the advance
     */
    public function reject($reason)
    {
        if ($this->status !== 'PENDING') {
            throw new \Exception('Only pending advances can be rejected');
        }
        
        $this->status = 'REJECTED';
        $this->rejection_reason = $reason;
        
        return $this->save();
    }
    
    /**
     * Record repayment
     */
    public function recordRepayment($amount)
    {
        if ($amount > $this->outstanding_amount) {
            throw new \Exception('Repayment amount exceeds outstanding amount');
        }
        
        $this->repaid_amount += $amount;
        $this->outstanding_amount -= $amount;
        
        if ($this->outstanding_amount == 0) {
            $this->status = 'REPAID';
        } else {
            $this->status = 'PARTIAL_REPAID';
        }
        
        return $this->save();
    }
    
    /**
     * Process automatic repayment
     */
    public function processAutomaticRepayment()
    {
        if ($this->repayment_method !== 'AUTOMATIC' || $this->outstanding_amount == 0) {
            return false;
        }
        
        $sourceAllocation = $this->sourceAllocation();
        if ($sourceAllocation && $sourceAllocation->available_amount > 0) {
            $repayAmount = min($this->outstanding_amount, $sourceAllocation->available_amount);
            
            // Return funds to source
            $sourceAllocation->available_amount += $repayAmount;
            $sourceAllocation->save();
            
            // Deduct from destination
            $destAllocation = $this->destinationAllocation();
            if ($destAllocation) {
                $destAllocation->advance_amount -= $repayAmount;
                $destAllocation->save();
            }
            
            // Record repayment
            return $this->recordRepayment($repayAmount);
        }
        
        return false;
    }
    
    /**
     * Check if advance is overdue
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && $this->outstanding_amount > 0;
    }
}