<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Auditable Trait - Automatically tracks all changes to models
 *
 * Usage: Add "use Auditable;" to any model you want to audit
 *
 * Example:
 * class Member extends Model {
 *     use Auditable;
 * }
 *
 * All CREATE, UPDATE, DELETE operations will be automatically logged
 */
trait Auditable
{
    /**
     * Boot the trait - Register model event listeners
     */
    protected static function bootAuditable()
    {
        // Log record creation
        static::created(function ($model) {
            $model->auditCreate();
        });

        // Log record updates
        static::updated(function ($model) {
            $model->auditUpdate();
        });

        // Log record deletion
        static::deleted(function ($model) {
            $model->auditDelete();
        });
    }

    /**
     * Log creation event
     */
    protected function auditCreate()
    {
        $this->logAudit('CREATE', [
            'description' => "Created new " . class_basename($this),
            'new_values' => $this->getAuditableAttributes(),
            'old_values' => null
        ]);
    }

    /**
     * Log update event
     */
    protected function auditUpdate()
    {
        $changes = $this->getChanges();
        $original = collect($this->getOriginal())
            ->only(array_keys($changes))
            ->toArray();

        if (empty($changes)) {
            return; // No actual changes, skip logging
        }

        $this->logAudit('UPDATE', [
            'description' => "Updated " . class_basename($this) . " (ID: {$this->id})",
            'old_values' => $original,
            'new_values' => $changes,
            'changes_count' => count($changes)
        ]);
    }

    /**
     * Log deletion event
     */
    protected function auditDelete()
    {
        $this->logAudit('DELETE', [
            'description' => "Deleted " . class_basename($this) . " (ID: {$this->id})",
            'old_values' => $this->getAuditableAttributes(),
            'new_values' => null
        ]);
    }

    /**
     * Log custom audit event
     *
     * @param string $action The action being performed (e.g., 'APPROVED', 'REVERSED')
     * @param string $description Human-readable description
     * @param array $context Additional context data
     */
    public function auditAction(string $action, string $description, array $context = [])
    {
        $this->logAudit($action, [
            'description' => $description,
            'context' => $context,
            'entity_id' => $this->id ?? null
        ]);
    }

    /**
     * Core audit logging method
     *
     * @param string $action The action type
     * @param array $data Additional data to log
     */
    protected function logAudit(string $action, array $data = [])
    {
        try {
            // Generate or retrieve request ID for correlation
            $requestId = request()->header('X-Request-ID') ?? Str::uuid()->toString();

            // Determine severity based on action
            $severity = $this->getAuditSeverity($action);

            // Get user info
            $userId = Auth::id() ?? 1; // Default to system user (1) if no auth
            $branchId = Auth::check() && Auth::user()->branch_id ? Auth::user()->branch_id : null;

            // Create audit log entry
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => get_class($this),
                'entity_id' => $this->id ?? null,
                'old_values' => $data['old_values'] ?? null,
                'new_values' => $data['new_values'] ?? null,
                'description' => $data['description'] ?? '',
                'context' => $data['context'] ?? null,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Unknown',
                'request_id' => $requestId,
                'performed_at' => now(),
                'branch_id' => $branchId,
                'severity' => $severity,
            ]);
        } catch (\Exception $e) {
            // Never let audit logging break the application
            // Log error but continue execution
            \Log::error('Audit logging failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'entity' => get_class($this),
                'entity_id' => $this->id ?? null,
            ]);
        }
    }

    /**
     * Determine audit severity based on action
     *
     * @param string $action
     * @return string
     */
    protected function getAuditSeverity(string $action): string
    {
        $criticalActions = [
            'DELETE',
            'PERMISSION_CHANGED',
            'ROLE_CHANGED',
            'PASSWORD_CHANGED',
            'TRANSACTION_REVERSED',
            'APPROVAL_OVERRIDDEN',
            'DATA_EXPORTED',
            'SYSTEM_CONFIG_CHANGED'
        ];

        $highActions = [
            'APPROVED',
            'REJECTED',
            'STATUS_CHANGED',
            'AMOUNT_CHANGED'
        ];

        if (in_array($action, $criticalActions)) {
            return 'critical';
        } elseif (in_array($action, $highActions)) {
            return 'high';
        } elseif ($action === 'CREATE') {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get attributes to audit (exclude sensitive fields)
     *
     * @return array
     */
    protected function getAuditableAttributes(): array
    {
        // Default exclusions + model-specific exclusions
        $excluded = array_merge(
            ['password', 'remember_token', 'api_token', 'pin'],
            $this->auditExclude ?? []
        );

        return collect($this->getAttributes())
            ->except($excluded)
            ->toArray();
    }

    /**
     * Get audit history for this model instance
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function auditHistory()
    {
        return AuditLog::where('entity_type', get_class($this))
            ->where('entity_id', $this->id)
            ->orderBy('performed_at', 'desc')
            ->get();
    }

    /**
     * Get recent audit entries
     *
     * @param int $days Number of days to look back
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recentAudits(int $days = 7)
    {
        return $this->auditHistory()
            ->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Get audit summary statistics
     *
     * @return array
     */
    public function auditSummary(): array
    {
        $audits = $this->auditHistory();

        return [
            'total_changes' => $audits->count(),
            'created_at' => $audits->where('action', 'CREATE')->first()?->performed_at,
            'last_updated_at' => $audits->where('action', 'UPDATE')->first()?->performed_at,
            'total_updates' => $audits->where('action', 'UPDATE')->count(),
            'unique_editors' => $audits->pluck('user_id')->unique()->count(),
            'critical_actions' => $audits->where('severity', 'critical')->count(),
        ];
    }

    /**
     * Get the user who created this record
     *
     * @return \App\Models\User|null
     */
    public function createdByUser()
    {
        $createAudit = AuditLog::where('entity_type', get_class($this))
            ->where('entity_id', $this->id)
            ->where('action', 'CREATE')
            ->first();

        return $createAudit ? $createAudit->user : null;
    }

    /**
     * Get the user who last modified this record
     *
     * @return \App\Models\User|null
     */
    public function lastModifiedByUser()
    {
        $lastAudit = AuditLog::where('entity_type', get_class($this))
            ->where('entity_id', $this->id)
            ->whereIn('action', ['UPDATE', 'CREATE'])
            ->orderBy('performed_at', 'desc')
            ->first();

        return $lastAudit ? $lastAudit->user : null;
    }
}
