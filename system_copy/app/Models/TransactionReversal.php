<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class TransactionReversal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'reversal_reference',
        'reason',
        'reversed_by',
        'is_automatic',
        'status',
        'correlation_id',
        'external_reference',
        'external_transaction_id',
        'external_request_payload',
        'external_response_payload',
        'external_status_code',
        'external_status_message',
        'error_code',
        'error_message',
        'retry_count',
        'next_retry_at',
        'processed_at',
        'completed_at',
        'failed_at',
        'metadata'
    ];

    protected $casts = [
        'is_automatic' => 'boolean',
        'external_request_payload' => 'array',
        'external_response_payload' => 'array',
        'metadata' => 'array',
        'next_retry_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime'
    ];

    /**
     * Get the transaction that this reversal belongs to
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who initiated the reversal
     */
    public function reversedByUser()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Scope for pending reversals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processing reversals
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for completed reversals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed reversals
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for automatic reversals
     */
    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    /**
     * Scope for manual reversals
     */
    public function scopeManual($query)
    {
        return $query->where('is_automatic', false);
    }

    /**
     * Scope for reversals ready for retry
     */
    public function scopeReadyForRetry($query)
    {
        return $query->where('status', 'failed')
                    ->where('retry_count', '<', 3)
                    ->where(function ($q) {
                        $q->whereNull('next_retry_at')
                          ->orWhere('next_retry_at', '<=', now());
                    });
    }

    /**
     * Scope for dead letter queue reversals
     */
    public function scopeDeadLetter($query)
    {
        return $query->where('status', 'dead_letter');
    }

    /**
     * Scope for reversals by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for reversals by external system
     */
    public function scopeByExternalSystem($query, $externalSystem)
    {
        return $query->whereHas('transaction', function ($q) use ($externalSystem) {
            $q->where('external_system', $externalSystem);
        });
    }

    /**
     * Get reversals for reconciliation
     */
    public static function getReconciliationData($date = null)
    {
        $query = self::with('transaction')
            ->where('status', 'completed')
            ->whereNotNull('external_reference');

        if ($date) {
            $query->whereDate('completed_at', $date);
        } else {
            $query->whereDate('completed_at', today());
        }

        return $query->get();
    }

    /**
     * Get reversal statistics
     */
    public static function getStatistics($dateRange = null)
    {
        $query = self::query();

        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_reversals,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_reversals,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_reversals,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_reversals,
            SUM(CASE WHEN is_automatic = 1 THEN 1 ELSE 0 END) as automatic_reversals,
            SUM(CASE WHEN is_automatic = 0 THEN 1 ELSE 0 END) as manual_reversals
        ')->first();

        return [
            'total_reversals' => $stats->total_reversals ?? 0,
            'successful_reversals' => $stats->successful_reversals ?? 0,
            'failed_reversals' => $stats->failed_reversals ?? 0,
            'pending_reversals' => $stats->pending_reversals ?? 0,
            'automatic_reversals' => $stats->automatic_reversals ?? 0,
            'manual_reversals' => $stats->manual_reversals ?? 0,
            'success_rate' => $stats->total_reversals > 0 ? 
                round(($stats->successful_reversals / $stats->total_reversals) * 100, 2) : 0
        ];
    }

    /**
     * Log retry attempt
     */
    public function logRetry($attempt, $status, $message, $errorCode = null, $errorMessage = null)
    {
        $retryLog = [
            'attempt' => $attempt,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toIso8601String()
        ];

        if ($errorCode) {
            $retryLog['error_code'] = $errorCode;
        }

        if ($errorMessage) {
            $retryLog['error_message'] = $errorMessage;
        }

        $metadata = $this->metadata ?? [];
        $metadata['retry_logs'] = $metadata['retry_logs'] ?? [];
        $metadata['retry_logs'][] = $retryLog;

        $this->update([
            'metadata' => $metadata,
            'retry_count' => $attempt
        ]);
    }

    /**
     * Check if reversal can be retried
     */
    public function canBeRetried()
    {
        return $this->status === 'failed' && 
               $this->retry_count < 3 && 
               (!$this->next_retry_at || $this->next_retry_at <= now());
    }

    /**
     * Get reversal summary for reporting
     */
    public function getSummary()
    {
        return [
            'reversal_id' => $this->id,
            'reversal_reference' => $this->reversal_reference,
            'status' => $this->status,
            'reason' => $this->reason,
            'is_automatic' => $this->is_automatic,
            'reversed_by' => $this->reversed_by,
            'external_reference' => $this->external_reference,
            'retry_count' => $this->retry_count,
            'created_at' => $this->created_at,
            'completed_at' => $this->completed_at,
            'failed_at' => $this->failed_at,
            'original_transaction' => [
                'id' => $this->transaction->id ?? null,
                'reference' => $this->transaction->reference ?? null,
                'amount' => $this->transaction->amount ?? null,
                'external_system' => $this->transaction->external_system ?? null
            ]
        ];
    }
} 