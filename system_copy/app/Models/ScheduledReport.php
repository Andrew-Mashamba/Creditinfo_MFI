<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'report_config',
        'user_id',
        'status',
        'frequency',
        'scheduled_at',
        'last_run_at',
        'next_run_at',
        'error_message',
        'output_path',
        'email_recipients',
        'email_sent',
        'retry_count',
        'max_retries',
    ];

    protected $casts = [
        'report_config' => 'array',
        'scheduled_at' => 'datetime',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'email_sent' => 'boolean',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
    ];

    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Frequency constants
    const FREQUENCY_ONCE = 'once';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_QUARTERLY = 'quarterly';
    const FREQUENCY_ANNUALLY = 'annually';

    /**
     * Get the user that owns the scheduled report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope for failed reports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if report should be retried
     */
    public function shouldRetry(): bool
    {
        return $this->status === self::STATUS_FAILED && 
               $this->retry_count < $this->max_retries;
    }

    /**
     * Mark report as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Mark report as completed
     */
    public function markAsCompleted(string $outputPath = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'last_run_at' => now(),
            'output_path' => $outputPath,
            'error_message' => null,
        ]);
    }

    /**
     * Mark report as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Get formatted frequency text
     */
    public function getFrequencyTextAttribute(): string
    {
        return match($this->frequency) {
            self::FREQUENCY_ONCE => 'One Time',
            self::FREQUENCY_DAILY => 'Daily',
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
            self::FREQUENCY_QUARTERLY => 'Quarterly',
            self::FREQUENCY_ANNUALLY => 'Annually',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'blue',
            self::STATUS_PROCESSING => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }
}
