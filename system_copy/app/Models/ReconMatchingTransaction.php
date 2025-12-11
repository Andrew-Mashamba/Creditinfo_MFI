<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconMatchingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'bank_transaction_id',
        'cashbook_transaction_id',
        'match_type',
        'match_confidence',
        'match_criteria',
        'bank_amount',
        'cashbook_amount',
        'variance',
        'bank_date',
        'cashbook_date',
        'date_variance_days',
        'status',
        'notes',
        'variance_explanation',
        'matched_by',
        'matched_at',
        'approved_by',
        'approved_at',
        'branch_id'
    ];

    protected $casts = [
        'match_confidence' => 'decimal:2',
        'bank_amount' => 'decimal:2',
        'cashbook_amount' => 'decimal:2',
        'variance' => 'decimal:2',
        'bank_date' => 'date',
        'cashbook_date' => 'date',
        'date_variance_days' => 'integer',
        'match_criteria' => 'array',
        'matched_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(AnalysisSession::class, 'session_id');
    }

    public function bankTransaction()
    {
        return $this->belongsTo(BankTransaction::class, 'bank_transaction_id');
    }

    public function cashbookTransaction()
    {
        return $this->belongsTo(Transaction::class, 'cashbook_transaction_id');
    }

    public function matchedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'matched_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    // Scopes
    public function scopeExactMatch($query)
    {
        return $query->where('match_type', 'exact');
    }

    public function scopePartialMatch($query)
    {
        return $query->where('match_type', 'partial');
    }

    public function scopeManualMatch($query)
    {
        return $query->where('match_type', 'manual');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helper methods
    public function hasVariance()
    {
        return abs($this->variance) > 0.01; // More than 1 cent variance
    }

    public function needsApproval()
    {
        return $this->match_type === 'manual' || $this->hasVariance() || $this->match_confidence < 100;
    }

    public function approve($userId = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now()
        ]);
    }

    public function reject($userId = null, $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
            'notes' => $notes
        ]);
    }
}
