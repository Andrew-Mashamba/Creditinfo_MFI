<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconNonMatchingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'source',
        'transaction_id',
        'transaction_date',
        'reference_number',
        'narration',
        'debit_amount',
        'credit_amount',
        'amount',
        'non_match_reason',
        'reason_details',
        'resolution_status',
        'resolution_notes',
        'expected_resolution_date',
        'requires_action',
        'action_type',
        'action_notes',
        'assigned_to',
        'assigned_at',
        'resolved_by',
        'resolved_at',
        'branch_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'expected_resolution_date' => 'date',
        'requires_action' => 'boolean',
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(AnalysisSession::class, 'session_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    // Get the source transaction polymorphically
    public function sourceTransaction()
    {
        if ($this->source === 'bank') {
            return BankTransaction::find($this->transaction_id);
        } else {
            return Transaction::find($this->transaction_id);
        }
    }

    // Scopes
    public function scopeFromBank($query)
    {
        return $query->where('source', 'bank');
    }

    public function scopeFromCashbook($query)
    {
        return $query->where('source', 'cashbook');
    }

    public function scopePending($query)
    {
        return $query->where('resolution_status', 'pending');
    }

    public function scopeInvestigating($query)
    {
        return $query->where('resolution_status', 'investigating');
    }

    public function scopeResolved($query)
    {
        return $query->where('resolution_status', 'resolved');
    }

    public function scopeRequiresAction($query)
    {
        return $query->where('requires_action', true);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_resolution_date', '<', now())
            ->where('resolution_status', '!=', 'resolved');
    }

    // Helper methods
    public function assign($userId, $notes = null)
    {
        $this->update([
            'assigned_to' => $userId,
            'assigned_at' => now(),
            'action_notes' => $notes,
            'resolution_status' => 'investigating'
        ]);
    }

    public function resolve($userId = null, $notes = null)
    {
        $this->update([
            'resolution_status' => 'resolved',
            'resolved_by' => $userId ?? auth()->id(),
            'resolved_at' => now(),
            'resolution_notes' => $notes,
            'requires_action' => false
        ]);
    }

    public function accept($notes = null)
    {
        $this->update([
            'resolution_status' => 'accepted',
            'resolution_notes' => $notes,
            'requires_action' => false
        ]);
    }

    public function isOverdue()
    {
        return $this->expected_resolution_date &&
               $this->expected_resolution_date->isPast() &&
               $this->resolution_status !== 'resolved';
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->expected_resolution_date);
    }

    public function getTypeAttribute()
    {
        if ($this->debit_amount > 0) {
            return 'debit';
        } elseif ($this->credit_amount > 0) {
            return 'credit';
        }
        return 'unknown';
    }
}
