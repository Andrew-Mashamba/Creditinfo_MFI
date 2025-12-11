<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Audit Log Service - Structured logging for audit trail
 *
 * This service provides a simple way to log audit events that can be
 * analyzed later using the logs:analyze command
 */
class AuditLogService
{
    /**
     * Log a user action
     *
     * @param string $action The action performed (e.g., 'CREATED', 'UPDATED', 'DELETED')
     * @param string $entity The entity type (e.g., 'Member', 'Transaction', 'Loan')
     * @param int|null $entityId The entity ID
     * @param array $context Additional context data
     * @param string $level Log level (info, warning, error)
     */
    public static function log(
        string $action,
        string $entity,
        ?int $entityId = null,
        array $context = [],
        string $level = 'info'
    ): void {
        $auditData = [
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'user_id' => Auth::id() ?? 0,
            'user_name' => Auth::user()->name ?? 'System',
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'Unknown',
            'timestamp' => now()->toDateTimeString(),
        ];

        // Merge with additional context
        $auditData = array_merge($auditData, $context);

        // Log with appropriate level
        $message = "{$action} {$entity}" . ($entityId ? " (ID: {$entityId})" : "");

        match($level) {
            'error' => Log::error($message, $auditData),
            'warning' => Log::warning($message, $auditData),
            default => Log::info($message, $auditData)
        };
    }

    /**
     * Log record creation
     */
    public static function created(string $entity, int $entityId, array $data = []): void
    {
        self::log('CREATED', $entity, $entityId, [
            'new_values' => $data
        ]);
    }

    /**
     * Log record update
     */
    public static function updated(string $entity, int $entityId, array $oldValues, array $newValues): void
    {
        self::log('UPDATED', $entity, $entityId, [
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changes' => self::getChanges($oldValues, $newValues)
        ]);
    }

    /**
     * Log record deletion
     */
    public static function deleted(string $entity, int $entityId, array $data = []): void
    {
        self::log('DELETED', $entity, $entityId, [
            'deleted_values' => $data
        ], 'warning');
    }

    /**
     * Log user login
     */
    public static function login(int $userId, bool $successful = true): void
    {
        self::log(
            $successful ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED',
            'User',
            $userId,
            ['successful' => $successful],
            $successful ? 'info' : 'warning'
        );
    }

    /**
     * Log user logout
     */
    public static function logout(int $userId): void
    {
        self::log('LOGOUT', 'User', $userId);
    }

    /**
     * Log permission change
     */
    public static function permissionChanged(int $userId, array $oldPermissions, array $newPermissions): void
    {
        self::log('PERMISSION_CHANGED', 'User', $userId, [
            'old_permissions' => $oldPermissions,
            'new_permissions' => $newPermissions
        ], 'warning');
    }

    /**
     * Log role change
     */
    public static function roleChanged(int $userId, $oldRole, $newRole): void
    {
        self::log('ROLE_CHANGED', 'User', $userId, [
            'old_role' => $oldRole,
            'new_role' => $newRole
        ], 'warning');
    }

    /**
     * Log transaction
     */
    public static function transaction(string $action, int $transactionId, array $context = []): void
    {
        self::log($action, 'Transaction', $transactionId, $context);
    }

    /**
     * Log loan action
     */
    public static function loan(string $action, int $loanId, array $context = []): void
    {
        self::log($action, 'Loan', $loanId, $context);
    }

    /**
     * Log account action
     */
    public static function account(string $action, int $accountId, array $context = []): void
    {
        self::log($action, 'Account', $accountId, $context);
    }

    /**
     * Log member action
     */
    public static function member(string $action, int $memberId, array $context = []): void
    {
        self::log($action, 'Member', $memberId, $context);
    }

    /**
     * Log data export
     */
    public static function dataExport(string $type, array $context = []): void
    {
        self::log('DATA_EXPORTED', $type, null, array_merge($context, [
            'export_type' => $type,
            'record_count' => $context['count'] ?? 0
        ]), 'warning');
    }

    /**
     * Log security event
     */
    public static function securityEvent(string $event, array $context = []): void
    {
        self::log($event, 'Security', null, $context, 'warning');
    }

    /**
     * Log system event
     */
    public static function systemEvent(string $event, array $context = []): void
    {
        self::log($event, 'System', null, $context);
    }

    /**
     * Calculate changes between old and new values
     */
    protected static function getChanges(array $oldValues, array $newValues): array
    {
        $changes = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'from' => $oldValue,
                    'to' => $newValue
                ];
            }
        }

        return $changes;
    }
}
