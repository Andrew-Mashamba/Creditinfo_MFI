<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class LoanWriteOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'client_number',
        'write_off_date',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'total_amount',
        'provision_utilized',
        'direct_writeoff_amount',
        'reason',
        'writeoff_type',
        'status',
        'initiated_by',
        'approved_by',
        'board_approved_by',
        'approved_date',
        'board_approval_date',
        'board_approval_threshold',
        'requires_board_approval',
        'approval_workflow',
        'collection_efforts',
        'member_notification_sent',
        'audit_trail',
        'recovery_status',
        'recovered_amount',
        'notes'
    ];

    protected $casts = [
        'write_off_date' => 'date',
        'approved_date' => 'datetime',
        'board_approval_date' => 'datetime',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'provision_utilized' => 'decimal:2',
        'direct_writeoff_amount' => 'decimal:2',
        'board_approval_threshold' => 'decimal:2',
        'recovered_amount' => 'decimal:2',
        'requires_board_approval' => 'boolean',
        'approval_workflow' => 'array',
        'collection_efforts' => 'array',
        'audit_trail' => 'array'
    ];

    // Relationships
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function boardApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'board_approved_by');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'loan_id');
    }

    public function recoveries(): HasMany
    {
        return $this->hasMany(LoanWriteoffRecovery::class, 'writeoff_id');
    }

    public function approvalWorkflow(): HasMany
    {
        return $this->hasMany(WriteoffApprovalWorkflow::class, 'writeoff_id');
    }

    public function memberCommunications(): HasMany
    {
        return $this->hasMany(WriteoffMemberCommunication::class, 'writeoff_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRequiringBoardApproval($query)
    {
        return $query->where('requires_board_approval', true);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('write_off_date', [$from, $to]);
    }

    // Accessors
    public function getFormattedTotalAmountAttribute()
    {
        return 'TZS ' . number_format($this->total_amount, 2);
    }

    public function getFormattedRecoveredAmountAttribute()
    {
        return 'TZS ' . number_format($this->recovered_amount, 2);
    }

    public function getRecoveryPercentageAttribute()
    {
        if ($this->total_amount == 0) return 0;
        return round(($this->recovered_amount / $this->total_amount) * 100, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending_approval' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending Approval'],
            'approved' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Approved'],
            'board_pending' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Board Pending'],
            'rejected' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Rejected'],
            'cancelled' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Cancelled'],
        ];

        return $badges[$this->status] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->status)];
    }

    public function getRecoveryStatusBadgeAttribute()
    {
        $badges = [
            'not_recovered' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Not Recovered'],
            'partial' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Partial Recovery'],
            'full' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Fully Recovered'],
        ];

        return $badges[$this->recovery_status] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->recovery_status)];
    }

    // Methods
    public function requiresBoardApproval(): bool
    {
        $threshold = $this->board_approval_threshold ?? 1000000; // Default 1M TZS
        return $this->total_amount >= $threshold;
    }

    public function addAuditEntry(string $action, array $data = [], ?User $user = null): void
    {
        $auditTrail = $this->audit_trail ?? [];
        
        $auditTrail[] = [
            'timestamp' => now()->toISOString(),
            'action' => $action,
            'user_id' => $user?->id ?? auth()?->id(),
            'user_name' => $user?->name ?? auth()?->user()?->name ?? 'System',
            'data' => $data,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent()
        ];

        $this->update(['audit_trail' => $auditTrail]);
    }

    public function addCollectionEffort(array $effortData): void
    {
        $efforts = $this->collection_efforts ?? [];
        $efforts[] = array_merge($effortData, ['timestamp' => now()->toISOString()]);
        $this->update(['collection_efforts' => $efforts]);
    }

    public function canBeApproved(): bool
    {
        if ($this->status !== 'pending_approval') {
            return false;
        }

        // Check if requires board approval and if board approval is complete
        if ($this->requires_board_approval && !$this->board_approval_date) {
            return false;
        }

        return true;
    }

    public function updateRecoveryStatus(): void
    {
        $totalRecovered = $this->recoveries()->where('status', 'approved')->sum('recovery_amount');
        $this->recovered_amount = $totalRecovered;

        if ($totalRecovered == 0) {
            $this->recovery_status = 'not_recovered';
        } elseif ($totalRecovered >= $this->total_amount) {
            $this->recovery_status = 'full';
        } else {
            $this->recovery_status = 'partial';
        }

        $this->save();
    }

    // Static methods
    public static function getTotalWrittenOff($from = null, $to = null)
    {
        $query = self::where('status', 'approved');
        
        if ($from && $to) {
            $query->whereBetween('write_off_date', [$from, $to]);
        }

        return $query->sum('total_amount');
    }

    public static function getTotalRecovered($from = null, $to = null)
    {
        $query = self::where('status', 'approved');
        
        if ($from && $to) {
            $query->whereBetween('write_off_date', [$from, $to]);
        }

        return $query->sum('recovered_amount');
    }

    public static function getRecoveryRate($from = null, $to = null): float
    {
        $totalWrittenOff = self::getTotalWrittenOff($from, $to);
        $totalRecovered = self::getTotalRecovered($from, $to);

        if ($totalWrittenOff == 0) return 0;

        return round(($totalRecovered / $totalWrittenOff) * 100, 2);
    }
}