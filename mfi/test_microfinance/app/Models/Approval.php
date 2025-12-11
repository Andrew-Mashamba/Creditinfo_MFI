<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'approvals';

    protected $fillable = [
        'institution',
        'process_name',
        'process_description',
        'approval_process_description',
        'process_code',
        'process_id',
        'approver_id',
        'approval_status',
        'process_status',
        'user_id',
        'team_id',
        'edit_package',
        'checker_level',
        'first_checker_id',
        'second_checker_id',
        'first_checker_status',
        'second_checker_status',
        'first_checked_at',
        'second_checked_at',
        'first_checker_rejection_reason',
        'second_checker_rejection_reason',
        'approver_rejection_reason',
        'additional_data',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_at',
        'approval_notes',
        'approval_category',
        'last_action_by',
        // SLA Tracking
        'sla_breach_level',
        'sla_breached_at',
        'sla_warning_sent_at',
        'sla_breach_notified_at',
        'sla_escalated',
        'sla_escalated_at',
        'sla_notification_count'
    ];

    protected $casts = [
        'edit_package' => 'array',
        'additional_data' => 'array',
        'checker_level' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'first_checked_at' => 'datetime',
        'second_checked_at' => 'datetime',
        // SLA Tracking
        'sla_breach_level' => 'integer',
        'sla_breached_at' => 'datetime',
        'sla_warning_sent_at' => 'datetime',
        'sla_breach_notified_at' => 'datetime',
        'sla_escalated' => 'boolean',
        'sla_escalated_at' => 'datetime',
        'sla_notification_count' => 'integer'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function firstChecker()
    {
        return $this->belongsTo(User::class, 'first_checker_id');
    }

    public function secondChecker()
    {
        return $this->belongsTo(User::class, 'second_checker_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function processConfig()
    {
        return $this->belongsTo(ProcessCodeConfig::class, 'process_code', 'process_code');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('process_status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('process_status', 'APPROVED');
    }

    public function scopeRejected($query)
    {
        return $query->where('process_status', 'REJECTED');
    }

    // Helper Methods
    public function isPending()
    {
        return $this->process_status === 'PENDING';
    }

    public function isApproved()
    {
        return $this->process_status === 'APPROVED';
    }

    public function isRejected()
    {
        return $this->process_status === 'REJECTED';
    }

    public function getCurrentLevel()
    {
        return $this->checker_level;
    }

    public function getNextLevel()
    {
        $config = $this->processConfig;
        if (!$config) return null;

        if ($this->checker_level === 1 && $config->requires_second_checker) {
            return 2;
        } elseif ($this->checker_level === 2 && $config->requires_approver) {
            return 3;
        }

        return null;
    }

    // ==================== SLA Methods ====================

    /**
     * Get the timestamp when the current level started waiting
     */
    public function getCurrentLevelStartTime()
    {
        $level = $this->checker_level ?? 1;

        return match($level) {
            1 => $this->submitted_at ?? $this->created_at,
            2 => $this->first_checked_at,
            3 => $this->second_checked_at ?? $this->first_checked_at,
            default => $this->created_at
        };
    }

    /**
     * Get hours elapsed at current level
     */
    public function getHoursAtCurrentLevel(): float
    {
        $startTime = $this->getCurrentLevelStartTime();
        if (!$startTime) {
            return 0;
        }

        return now()->diffInMinutes($startTime) / 60;
    }

    /**
     * Get the SLA hours limit for the current level
     */
    public function getSlaHoursLimit(): ?int
    {
        $config = $this->processConfig;
        if (!$config) {
            return null;
        }

        return $config->getSlaHoursForLevel($this->checker_level ?? 1);
    }

    /**
     * Check if SLA is breached at current level
     */
    public function isSlaBreached(): bool
    {
        $limit = $this->getSlaHoursLimit();
        if ($limit === null) {
            return false;
        }

        return $this->getHoursAtCurrentLevel() > $limit;
    }

    /**
     * Check if SLA warning threshold is reached
     */
    public function isSlaWarningReached(): bool
    {
        $config = $this->processConfig;
        if (!$config) {
            return false;
        }

        $limit = $this->getSlaHoursLimit();
        if ($limit === null) {
            return false;
        }

        $threshold = $config->getSlaWarningThresholdDecimal();
        $warningHours = $limit * $threshold;

        return $this->getHoursAtCurrentLevel() >= $warningHours;
    }

    /**
     * Get SLA status: 'ok', 'warning', 'breached'
     */
    public function getSlaStatus(): string
    {
        if ($this->isSlaBreached()) {
            return 'breached';
        }

        if ($this->isSlaWarningReached()) {
            return 'warning';
        }

        return 'ok';
    }

    /**
     * Get percentage of SLA time used
     */
    public function getSlaPercentageUsed(): float
    {
        $limit = $this->getSlaHoursLimit();
        if ($limit === null || $limit === 0) {
            return 0;
        }

        $used = $this->getHoursAtCurrentLevel();
        return min(($used / $limit) * 100, 100);
    }

    /**
     * Get the current level approver/checker
     */
    public function getCurrentLevelUser()
    {
        $level = $this->checker_level ?? 1;

        return match($level) {
            1 => $this->firstChecker,
            2 => $this->secondChecker,
            3 => $this->approver,
            default => null
        };
    }

    /**
     * Get level name for display
     */
    public function getCurrentLevelName(): string
    {
        $level = $this->checker_level ?? 1;

        return match($level) {
            1 => 'First Checker',
            2 => 'Second Checker',
            3 => 'Final Approver',
            default => 'Unknown'
        };
    }

    /**
     * Mark SLA as breached
     */
    public function markSlaBreached(int $level): void
    {
        if (!$this->sla_breached_at) {
            $this->update([
                'sla_breach_level' => $level,
                'sla_breached_at' => now()
            ]);
        }
    }

    /**
     * Mark warning notification sent
     */
    public function markSlaWarningSent(): void
    {
        $this->update([
            'sla_warning_sent_at' => now(),
            'sla_notification_count' => ($this->sla_notification_count ?? 0) + 1
        ]);
    }

    /**
     * Mark breach notification sent
     */
    public function markSlaBreachNotified(): void
    {
        $this->update([
            'sla_breach_notified_at' => now(),
            'sla_notification_count' => ($this->sla_notification_count ?? 0) + 1
        ]);
    }

    /**
     * Mark as escalated
     */
    public function markSlaEscalated(): void
    {
        $this->update([
            'sla_escalated' => true,
            'sla_escalated_at' => now()
        ]);
    }

    /**
     * Scope for pending approvals that need SLA check
     */
    public function scopePendingSlaCheck($query)
    {
        return $query->where('process_status', 'PENDING')
                    ->whereHas('processConfig', function ($q) {
                        $q->where('sla_enabled', true);
                    });
    }

    /**
     * Scope for SLA breached approvals
     */
    public function scopeSlaBreached($query)
    {
        return $query->whereNotNull('sla_breached_at');
    }

    /**
     * Scope for approvals needing warning (not yet warned)
     */
    public function scopeNeedsSlaWarning($query)
    {
        return $query->where('process_status', 'PENDING')
                    ->whereNull('sla_warning_sent_at');
    }

    /**
     * Scope for breached approvals not yet notified
     */
    public function scopeNeedsSlaBreachNotification($query)
    {
        return $query->where('process_status', 'PENDING')
                    ->whereNotNull('sla_breached_at')
                    ->whereNull('sla_breach_notified_at');
    }
} 