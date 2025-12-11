<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'transactions';

    protected $casts = [
        'amount' => 'decimal:6',
        'balance_before' => 'decimal:6',
        'balance_after' => 'decimal:6',
        'running_balance' => 'decimal:6',
        'external_request_payload' => 'array',
        'external_response_payload' => 'array',
        'error_context' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
        'extra_fields' => 'array', // Added for JSON field
        'received_at' => 'datetime', // Added for timestamp field
        'initiated_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'failure_reported_at' => 'datetime',
        'reversed_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_manual' => 'boolean',
        'is_system_generated' => 'boolean',
        'requires_approval' => 'boolean',
        'is_approved' => 'boolean',
        'is_suspicious' => 'boolean',
        'lookup_request_payload' => 'array',
        'lookup_response_payload' => 'array',
        'lookup_performed_at' => 'datetime',
        'request_timestamp' => 'datetime',
        'lookup_validated' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_uuid)) {
                $transaction->transaction_uuid = Str::uuid();
            }
        });
    }

    // Relationships
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_id');
    }

    public function originalTransaction()
    {
        return $this->belongsTo(Transaction::class, 'original_transaction_id');
    }

    public function reversalTransaction()
    {
        return $this->belongsTo(Transaction::class, 'reversal_transaction_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(TransactionAuditLog::class);
    }

    public function retryLogs()
    {
        return $this->hasMany(TransactionRetryLog::class);
    }

    public function reconciliations()
    {
        return $this->hasMany(TransactionReconciliation::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeFailedUnreported($query)
    {
        return $query->where('status', 'failed')
                    ->whereNull('failure_reported_at');
    }

    /**
     * Get the user who initiated the transaction
     */
    public function initiatedByUser()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('reconciliation_status', 'unreconciled');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('transaction_category', $category);
    }

    public function scopeByExternalSystem($query, $system)
    {
        return $query->where('external_system', $system);
    }

    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopeByProcess($query, $processId)
    {
        return $query->where('process_id', $processId);
    }

    public function scopeNeedsRetry($query)
    {
        return $query->where('status', 'failed')
                    ->where('retry_count', '<', 'max_retries')
                    ->where(function ($q) {
                        $q->whereNull('next_retry_at')
                          ->orWhere('next_retry_at', '<=', now());
                    });
    }

    // Methods
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'processed_at' => now()
        ]);
        $this->logAudit('status_changed', 'pending', 'processing', 'Transaction marked as processing');
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        $this->logAudit('status_changed', 'processing', 'completed', 'Transaction completed successfully');
    }

    public function markAsFailed($errorCode = null, $errorMessage = null, $failureReason = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'failure_reason' => $failureReason
        ]);
        $this->logAudit('status_changed', 'processing', 'failed', "Transaction failed: {$errorMessage}");
    }

    public function markForRetry($reason = null)
    {
        $this->update([
            'status' => 'retry_pending',
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
            'next_retry_at' => now()->addMinutes(pow(2, $this->retry_count)) // Exponential backoff
        ]);
        $this->logAudit('retry_scheduled', null, null, "Retry scheduled: {$reason}");
    }

    public function reverse($reason = null, $reversedBy = null)
    {
        $this->update([
            'status' => 'reversed',
            'reversed_at' => now(),
            'reversed_by' => $reversedBy,
            'reversal_reason' => $reason
        ]);
        $this->logAudit('reversed', 'completed', 'reversed', "Transaction reversed: {$reason}");
    }

    public function reconcile($reconciliationData = [])
    {
        $this->update([
            'reconciliation_status' => 'reconciled',
            'reconciled_at' => now()
        ]);
        $this->logAudit('reconciled', 'unreconciled', 'reconciled', 'Transaction reconciled');
    }

    public function logAudit($action, $previousStatus = null, $newStatus = null, $description = null, $context = null)
    {
        return $this->auditLogs()->create([
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'description' => $description,
            'context' => $context,
            'performed_by' => auth()->id() ?? 'system',
            'client_ip' => request()->ip()
        ]);
    }

    public function logRetry($attempt, $reason, $result, $errorCode = null, $errorMessage = null)
    {
        return $this->retryLogs()->create([
            'retry_attempt' => $attempt,
            'retry_at' => now(),
            'retry_reason' => $reason,
            'retry_result' => $result,
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ]);
    }

    public function canBeRetried()
    {
        return $this->status === 'failed' && 
               $this->retry_count < $this->max_retries &&
               !in_array($this->failure_reason, ['insufficient_funds', 'invalid_account', 'duplicate_transaction']);
    }

    public function isReversible()
    {
        return $this->status === 'completed' && 
               !$this->reversal_transaction_id &&
               $this->created_at->diffInHours(now()) <= 24; // Within 24 hours
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'reversed' => 'dark',
            'timeout' => 'warning',
            'retry_pending' => 'info',
            'suspended' => 'warning',
            'disputed' => 'danger'
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}
