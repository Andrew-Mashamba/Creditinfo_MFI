<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'transaction_date',
        'value_date',
        'reference_number',
        'narration',
        'withdrawal_amount',
        'deposit_amount',
        'balance',
        'matched_transaction_id',
        'reconciliation_status',
        'match_confidence',
        'reconciliation_notes',
        'reconciled_at',
        'reconciled_by',
        'recon_match_type',
        'recon_match_score',
        'branch',
        'branch_id',
        'transaction_type',
        'raw_data'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'value_date' => 'date',
        'withdrawal_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'match_confidence' => 'decimal:2',
        'recon_match_score' => 'decimal:2',
        'reconciled_at' => 'datetime',
        'raw_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function analysisSession()
    {
        return $this->belongsTo(AnalysisSession::class, 'session_id');
    }

    public function matchedTransaction()
    {
        return $this->belongsTo(Transaction::class, 'matched_transaction_id');
    }

    public function reconMatch()
    {
        return $this->hasOne(ReconMatchingTransaction::class, 'bank_transaction_id');
    }

    public function reconNonMatch()
    {
        return $this->hasOne(ReconNonMatchingTransaction::class, 'transaction_id')
            ->where('source', 'bank');
    }

    public function branchRelation()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    // Accessors
    public function getAmountAttribute()
    {
        return $this->deposit_amount > 0 ? $this->deposit_amount : $this->withdrawal_amount;
    }

    public function getTransactionTypeAttribute()
    {
        return $this->deposit_amount > 0 ? 'credit' : 'debit';
    }

    // Scopes
    public function scopeUnreconciled($query)
    {
        return $query->where('reconciliation_status', 'unreconciled');
    }

    public function scopeMatched($query)
    {
        return $query->whereIn('reconciliation_status', ['matched', 'reconciled']);
    }

    public function scopeReconciled($query)
    {
        return $query->where('reconciliation_status', 'reconciled');
    }

    public function scopeAutoMatched($query)
    {
        return $query->where('recon_match_type', 'auto');
    }

    public function scopeManuallyMatched($query)
    {
        return $query->where('recon_match_type', 'manual');
    }

    public function scopePartialMatched($query)
    {
        return $query->where('recon_match_type', 'partial');
    }

    // Helper methods
    public function markAsMatched($transactionId, $confidence = 100, $notes = null, $matchType = 'auto')
    {
        $this->update([
            'matched_transaction_id' => $transactionId,
            'reconciliation_status' => 'matched',
            'match_confidence' => $confidence,
            'recon_match_type' => $matchType,
            'recon_match_score' => $confidence,
            'reconciliation_notes' => $notes,
            'reconciled_at' => now(),
            'reconciled_by' => auth()->id()
        ]);
    }

    public function markAsReconciled($notes = null)
    {
        $this->update([
            'reconciliation_status' => 'reconciled',
            'reconciliation_notes' => $notes,
            'reconciled_at' => now(),
            'reconciled_by' => auth()->id()
        ]);
    }

    public function unmatch()
    {
        $this->update([
            'matched_transaction_id' => null,
            'reconciliation_status' => 'unreconciled',
            'match_confidence' => null,
            'recon_match_type' => 'none',
            'recon_match_score' => null,
            'reconciliation_notes' => null,
            'reconciled_at' => null,
            'reconciled_by' => null
        ]);
    }

    public function isMatched()
    {
        return in_array($this->reconciliation_status, ['matched', 'reconciled']);
    }

    public function isUnreconciled()
    {
        return $this->reconciliation_status === 'unreconciled';
    }
} 