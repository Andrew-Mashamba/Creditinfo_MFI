<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'account_number',
        'bank',
        'bank_account_id',
        'mirror_account_number',
        'statement_period',
        'status',
        'total_transactions',
        'matched_count',
        'unmatched_count',
        'reconciliation_summary',
        'file_path',
        'file_hash',
        'uploaded_by',
        'reconciled_by',
        'reconciled_at',
        'currency',
        'opening_balance',
        'closing_balance',
        'notes',
        'metadata',
        'branch_id'
    ];

    protected $casts = [
        'total_transactions' => 'integer',
        'matched_count' => 'integer',
        'unmatched_count' => 'integer',
        'reconciliation_summary' => 'array',
        'reconciled_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class, 'session_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(\App\Models\BankAccount::class, 'bank_account_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function reconciledBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'reconciled_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    public function matchingTransactions()
    {
        return $this->hasMany(ReconMatchingTransaction::class, 'session_id');
    }

    public function nonMatchingTransactions()
    {
        return $this->hasMany(ReconNonMatchingTransaction::class, 'session_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(ReconAuditLog::class, 'session_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForBank($query, $bank)
    {
        return $query->where('bank', $bank);
    }

    // Accessor - Backward compatible with existing code
    public function getReconciliationSummaryAttribute($value)
    {
        // If summary is stored in database, return it
        if ($value && is_string($value)) {
            return json_decode($value, true);
        }

        // Otherwise, calculate dynamically for backward compatibility
        $total = $this->bankTransactions()->count();
        $reconciled = $this->bankTransactions()->where('reconciliation_status', 'reconciled')->count();
        $matched = $this->bankTransactions()->where('reconciliation_status', 'matched')->count();
        $unreconciled = $this->bankTransactions()->where('reconciliation_status', 'unreconciled')->count();

        return [
            'total' => $total,
            'reconciled' => $reconciled,
            'matched' => $matched,
            'unreconciled' => $unreconciled,
            'reconciliation_rate' => $total > 0 ? round((($reconciled + $matched) / $total) * 100, 2) : 0
        ];
    }

    // Helper methods
    public function updateReconciliationCounts()
    {
        $matched = $this->matchingTransactions()->count();
        $unmatched = $this->nonMatchingTransactions()->count();

        $this->update([
            'matched_count' => $matched,
            'unmatched_count' => $unmatched
        ]);
    }

    public function markAsCompleted($userId = null)
    {
        $this->update([
            'status' => 'completed',
            'reconciled_by' => $userId ?? auth()->id(),
            'reconciled_at' => now()
        ]);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }
} 