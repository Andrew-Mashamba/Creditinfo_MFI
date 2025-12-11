<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconAuditLog extends Model
{
    use HasFactory;

    protected $table = 'recon_audit_log';

    protected $fillable = [
        'session_id',
        'action_type',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'description',
        'performed_by',
        'performed_at',
        'ip_address'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(AnalysisSession::class, 'session_id');
    }

    public function performer()
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }

    // Scopes
    public function scopeByAction($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);

        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('performed_by', $userId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    // Static helper to log actions
    public static function logAction($sessionId, $actionType, $entityType, $entityId = null, $oldValues = null, $newValues = null, $description = null)
    {
        return self::create([
            'session_id' => $sessionId,
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'performed_by' => auth()->id() ?? 1, // Default to system user (1) if no auth
            'performed_at' => now(),
            'ip_address' => request()->ip() ?? '127.0.0.1'
        ]);
    }

    // Helper methods
    public function getChangesAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }

    public function hasFieldChanges()
    {
        return !empty($this->getChangesAttribute());
    }
}
