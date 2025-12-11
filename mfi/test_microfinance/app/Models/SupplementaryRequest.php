<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplementaryRequest extends Model
{
    use HasFactory;
    
    protected $table = 'supplementary_requests';
    
    protected $fillable = [
        'request_number',
        'budget_id',
        'period',
        'year',
        'current_allocation',
        'requested_amount',
        'approved_amount',
        'urgency_level',
        'justification',
        'supporting_documents',
        'funding_source',
        'status',
        'requested_by',
        'department_head_approval',
        'department_head_approved_at',
        'finance_approval',
        'finance_approved_at',
        'final_approval',
        'final_approved_at',
        'approval_notes',
        'rejection_reason',
        'effective_date'
    ];
    
    protected $casts = [
        'current_allocation' => 'decimal:2',
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'supporting_documents' => 'array',
        'department_head_approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
        'final_approved_at' => 'datetime',
        'effective_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->request_number) {
                $model->request_number = 'SUP-' . date('Ym') . '-' . str_pad(
                    (static::whereYear('created_at', date('Y'))->count() + 1),
                    4,
                    '0',
                    STR_PAD_LEFT
                );
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
     * Get department head approver
     */
    public function departmentHeadApprover()
    {
        return $this->belongsTo(User::class, 'department_head_approval');
    }
    
    /**
     * Get finance approver
     */
    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approval');
    }
    
    /**
     * Get final approver
     */
    public function finalApprover()
    {
        return $this->belongsTo(User::class, 'final_approval');
    }
    
    /**
     * Get the related budget allocation
     */
    public function allocation()
    {
        return BudgetAllocation::where('budget_id', $this->budget_id)
            ->where('period', $this->period)
            ->where('year', $this->year)
            ->first();
    }
    
    /**
     * Submit for approval
     */
    public function submit()
    {
        if ($this->status !== 'DRAFT') {
            throw new \Exception('Only draft requests can be submitted');
        }
        
        $this->status = 'PENDING';
        return $this->save();
    }
    
    /**
     * Department head approval
     */
    public function approveDepartmentHead($notes = null)
    {
        if ($this->status !== 'PENDING') {
            throw new \Exception('Request is not pending approval');
        }
        
        $this->department_head_approval = auth()->id();
        $this->department_head_approved_at = now();
        $this->status = 'UNDER_REVIEW';
        
        if ($notes) {
            $this->approval_notes = $notes;
        }
        
        return $this->save();
    }
    
    /**
     * Finance approval
     */
    public function approveFinance($approvedAmount = null, $fundingSource = null, $notes = null)
    {
        if ($this->status !== 'UNDER_REVIEW') {
            throw new \Exception('Request is not under review');
        }
        
        $this->finance_approval = auth()->id();
        $this->finance_approved_at = now();
        
        if ($approvedAmount !== null) {
            $this->approved_amount = min($approvedAmount, $this->requested_amount);
        } else {
            $this->approved_amount = $this->requested_amount;
        }
        
        if ($fundingSource) {
            $this->funding_source = $fundingSource;
        }
        
        if ($notes) {
            $this->approval_notes = ($this->approval_notes ? $this->approval_notes . ' | ' : '') . $notes;
        }
        
        return $this->save();
    }
    
    /**
     * Final approval and application
     */
    public function approveFinal($effectiveDate = null, $notes = null)
    {
        if (!$this->finance_approval) {
            throw new \Exception('Finance approval is required before final approval');
        }
        
        $this->final_approval = auth()->id();
        $this->final_approved_at = now();
        $this->status = 'APPROVED';
        $this->effective_date = $effectiveDate ?? now();
        
        if ($notes) {
            $this->approval_notes = ($this->approval_notes ? $this->approval_notes . ' | ' : '') . $notes;
        }
        
        // Apply to budget allocation
        $allocation = $this->allocation();
        if ($allocation) {
            $allocation->applySupplementary($this->approved_amount, $this->request_number);
        }
        
        // Create budget alert
        BudgetAlert::create([
            'budget_id' => $this->budget_id,
            'alert_type' => 'SUPPLEMENTARY_APPROVED',
            'severity' => 'INFO',
            'alert_title' => 'Supplementary Budget Approved',
            'alert_message' => "Supplementary request {$this->request_number} has been approved for " . 
                             number_format($this->approved_amount, 2) . " effective {$this->effective_date}",
            'alert_data' => [
                'request_number' => $this->request_number,
                'amount' => $this->approved_amount,
                'period' => $this->period,
                'year' => $this->year
            ]
        ]);
        
        return $this->save();
    }
    
    /**
     * Reject the request
     */
    public function reject($reason, $level = 'final')
    {
        $this->status = 'REJECTED';
        $this->rejection_reason = $reason;
        
        // Set the appropriate rejection level
        if ($level === 'department') {
            $this->department_head_approval = auth()->id();
            $this->department_head_approved_at = now();
        } elseif ($level === 'finance') {
            $this->finance_approval = auth()->id();
            $this->finance_approved_at = now();
        } else {
            $this->final_approval = auth()->id();
            $this->final_approved_at = now();
        }
        
        return $this->save();
    }
    
    /**
     * Cancel the request
     */
    public function cancel($reason = null)
    {
        if (!in_array($this->status, ['DRAFT', 'PENDING'])) {
            throw new \Exception('Only draft or pending requests can be cancelled');
        }
        
        $this->status = 'CANCELLED';
        if ($reason) {
            $this->rejection_reason = $reason;
        }
        
        return $this->save();
    }
    
    /**
     * Check if can be edited
     */
    public function canEdit()
    {
        return in_array($this->status, ['DRAFT', 'PENDING']);
    }
    
    /**
     * Get approval progress
     */
    public function getApprovalProgressAttribute()
    {
        $steps = 0;
        $completed = 0;
        
        if ($this->urgency_level === 'CRITICAL') {
            $steps = 2; // Skip department head for critical
            if ($this->finance_approval) $completed++;
            if ($this->final_approval) $completed++;
        } else {
            $steps = 3;
            if ($this->department_head_approval) $completed++;
            if ($this->finance_approval) $completed++;
            if ($this->final_approval) $completed++;
        }
        
        return [
            'steps' => $steps,
            'completed' => $completed,
            'percentage' => $steps > 0 ? round(($completed / $steps) * 100) : 0
        ];
    }
}