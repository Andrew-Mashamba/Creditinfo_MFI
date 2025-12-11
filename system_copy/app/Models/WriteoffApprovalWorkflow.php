<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WriteoffApprovalWorkflow extends Model
{
    use HasFactory;

    protected $table = 'writeoff_approval_workflow';

    protected $fillable = [
        'writeoff_id',
        'approval_level',
        'approver_role',
        'approver_id',
        'status',
        'assigned_date',
        'action_date',
        'comments',
        'conditions',
        'is_final_approval',
        'next_approval_level'
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
        'action_date' => 'datetime',
        'conditions' => 'array',
        'is_final_approval' => 'boolean'
    ];

    // Relationships
    public function writeOff(): BelongsTo
    {
        return $this->belongsTo(LoanWriteOff::class, 'writeoff_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('approval_level', $level);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('approver_id', $userId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending'],
            'approved' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Approved'],
            'rejected' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Rejected'],
            'escalated' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Escalated'],
        ];

        return $badges[$this->status] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->status)];
    }

    public function getApprovalLevelBadgeAttribute()
    {
        $badges = [
            'manager' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Manager'],
            'director' => ['class' => 'bg-indigo-100 text-indigo-800', 'text' => 'Director'],
            'board' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Board'],
            'external_auditor' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'External Auditor'],
            'ceo' => ['class' => 'bg-red-100 text-red-800', 'text' => 'CEO'],
        ];

        return $badges[$this->approval_level] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->approval_level)];
    }

    public function getTimePendingAttribute()
    {
        if ($this->status !== 'pending') {
            return null;
        }

        return $this->assigned_date->diffForHumans();
    }

    public function getProcessingTimeAttribute()
    {
        if (!$this->action_date || !$this->assigned_date) {
            return null;
        }

        $diff = $this->action_date->diff($this->assigned_date);
        
        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
    }

    // Methods
    public function approve(string $comments = null, array $conditions = [], ?User $approver = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $user = $approver ?? auth()->user();

        $this->update([
            'status' => 'approved',
            'action_date' => now(),
            'comments' => $comments,
            'conditions' => $conditions,
            'approver_id' => $user->id
        ]);

        // Update writeoff audit trail
        $this->writeOff->addAuditEntry(
            'approval_level_approved',
            [
                'approval_level' => $this->approval_level,
                'approver' => $user->name,
                'comments' => $comments,
                'conditions' => $conditions,
                'processing_time' => $this->processing_time
            ],
            $user
        );

        // If this is the final approval or no next level, approve the writeoff
        if ($this->is_final_approval || !$this->next_approval_level) {
            $this->writeOff->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_date' => now()
            ]);

            if ($this->approval_level === 'board') {
                $this->writeOff->update([
                    'board_approved_by' => $user->id,
                    'board_approval_date' => now()
                ]);
            }
        } else {
            // Create next approval level
            $this->createNextApprovalLevel();
        }

        return true;
    }

    public function reject(string $comments, ?User $rejector = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $user = $rejector ?? auth()->user();

        $this->update([
            'status' => 'rejected',
            'action_date' => now(),
            'comments' => $comments,
            'approver_id' => $user->id
        ]);

        // Update writeoff status to rejected
        $this->writeOff->update(['status' => 'rejected']);

        // Update writeoff audit trail
        $this->writeOff->addAuditEntry(
            'approval_rejected',
            [
                'approval_level' => $this->approval_level,
                'rejector' => $user->name,
                'comments' => $comments,
                'processing_time' => $this->processing_time
            ],
            $user
        );

        return true;
    }

    public function escalate(string $reason, string $nextLevel = null, ?User $escalator = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $user = $escalator ?? auth()->user();

        $this->update([
            'status' => 'escalated',
            'action_date' => now(),
            'comments' => "Escalated: " . $reason,
            'next_approval_level' => $nextLevel ?? $this->determineEscalationLevel()
        ]);

        // Create escalated approval level
        $this->createNextApprovalLevel($nextLevel);

        // Update writeoff audit trail
        $this->writeOff->addAuditEntry(
            'approval_escalated',
            [
                'from_level' => $this->approval_level,
                'to_level' => $nextLevel ?? $this->next_approval_level,
                'escalator' => $user->name,
                'reason' => $reason
            ],
            $user
        );

        return true;
    }

    private function createNextApprovalLevel($level = null): void
    {
        $nextLevel = $level ?? $this->next_approval_level;
        
        if (!$nextLevel) {
            return;
        }

        // Get appropriate approver for the level
        $approverRole = $this->getApproverRoleForLevel($nextLevel);
        $approverId = $this->getApproverForRole($approverRole);

        self::create([
            'writeoff_id' => $this->writeoff_id,
            'approval_level' => $nextLevel,
            'approver_role' => $approverRole,
            'approver_id' => $approverId,
            'status' => 'pending',
            'assigned_date' => now(),
            'is_final_approval' => $this->isFinalApprovalLevel($nextLevel),
            'next_approval_level' => $this->getNextLevelAfter($nextLevel)
        ]);
    }

    private function determineEscalationLevel(): string
    {
        $escalationMap = [
            'manager' => 'director',
            'director' => 'ceo',
            'ceo' => 'board',
            'board' => 'external_auditor'
        ];

        return $escalationMap[$this->approval_level] ?? 'board';
    }

    private function getApproverRoleForLevel(string $level): string
    {
        $roleMap = [
            'manager' => 'loan_manager',
            'director' => 'director',
            'ceo' => 'ceo',
            'board' => 'board_member',
            'external_auditor' => 'external_auditor'
        ];

        return $roleMap[$level] ?? 'manager';
    }

    private function getApproverForRole(string $role): ?int
    {
        // This would typically query users with the specific role
        // For now, return null to allow manual assignment
        return null;
    }

    private function isFinalApprovalLevel(string $level): bool
    {
        return in_array($level, ['board', 'external_auditor']);
    }

    private function getNextLevelAfter(string $level): ?string
    {
        $levelHierarchy = [
            'manager' => 'director',
            'director' => 'ceo',
            'ceo' => 'board',
            'board' => null,
            'external_auditor' => null
        ];

        return $levelHierarchy[$level] ?? null;
    }

    // Static methods
    public static function initializeWorkflow(LoanWriteOff $writeOff): void
    {
        // Determine initial approval level based on amount
        $initialLevel = 'manager';
        if ($writeOff->requiresBoardApproval()) {
            $initialLevel = $writeOff->total_amount >= 5000000 ? 'board' : 'director'; // 5M TZS goes straight to board
        }

        self::create([
            'writeoff_id' => $writeOff->id,
            'approval_level' => $initialLevel,
            'approver_role' => (new self)->getApproverRoleForLevel($initialLevel),
            'approver_id' => null, // To be assigned
            'status' => 'pending',
            'assigned_date' => now(),
            'is_final_approval' => (new self)->isFinalApprovalLevel($initialLevel),
            'next_approval_level' => (new self)->getNextLevelAfter($initialLevel)
        ]);
    }

    public static function getPendingApprovals($userId = null)
    {
        $query = self::with(['writeOff', 'approver'])
            ->where('status', 'pending');

        if ($userId) {
            $query->where('approver_id', $userId);
        }

        return $query->orderBy('assigned_date', 'asc')->get();
    }

    public static function getApprovalStatistics($from = null, $to = null)
    {
        $query = self::query();
        
        if ($from && $to) {
            $query->whereBetween('assigned_date', [$from, $to]);
        }

        $total = $query->count();
        $approved = $query->clone()->where('status', 'approved')->count();
        $rejected = $query->clone()->where('status', 'rejected')->count();
        $pending = $query->clone()->where('status', 'pending')->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'pending' => $pending,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'rejection_rate' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
        ];
    }
}