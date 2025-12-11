<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'employee_id',
        'type',
        'department',
        'subject',
        'details',
        'status',
        'approver_id',
        'approved_at',
        'rejection_reason',
        'attachments',
        'branch_id'
    ];
    
    protected $casts = [
        'approved_at' => 'datetime',
        'attachments' => 'array'
    ];
    
    // Request types
    const TYPE_MATERIALS = 'materials';
    const TYPE_RESIGNATION = 'resignation';
    const TYPE_TRAVEL = 'travel';
    const TYPE_ADVANCE = 'advance';
    const TYPE_TRAINING = 'training';
    const TYPE_WORKSHOP = 'workshop';
    const TYPE_OVERTIME = 'overtime';
    const TYPE_PAYSLIP = 'payslip';
    const TYPE_HR_DOCS = 'hr_docs';
    const TYPE_GENERAL = 'general';
    
    // Status types
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    // Get type label
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_MATERIALS => 'Working Materials',
            self::TYPE_RESIGNATION => 'Resignation',
            self::TYPE_TRAVEL => 'Travel Request',
            self::TYPE_ADVANCE => 'Advance Request',
            self::TYPE_TRAINING => 'Training Request',
            self::TYPE_WORKSHOP => 'Workshop Request',
            self::TYPE_OVERTIME => 'Overtime Request',
            self::TYPE_PAYSLIP => 'Payslip Request',
            self::TYPE_HR_DOCS => 'HR Documents',
            self::TYPE_GENERAL => 'General Request'
        ];
        
        return $labels[$this->type] ?? ucfirst($this->type);
    }
    
    // Get status badge color
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_PROCESSING => 'blue',
            self::STATUS_COMPLETED => 'purple'
        ];
        
        return $colors[$this->status] ?? 'gray';
    }
}