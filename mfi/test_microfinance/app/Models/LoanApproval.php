<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'stage_name',
        'stage_type',
        'approver_id',
        'approver_name',
        'status',
        'comments',
        'approved_at',
        'conditions'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'conditions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoansModel::class, 'loan_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }
} 