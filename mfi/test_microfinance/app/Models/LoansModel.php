<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class LoansModel extends Model
{
    use HasFactory;
    
    protected $table = 'loans';
    
    protected $guarded = [];

    protected $casts = [
        'principle' => 'decimal:2',
        'interest' => 'decimal:2',
        'business_inventory' => 'decimal:2',
        'cash_at_hand' => 'decimal:2',
        'daily_sales' => 'decimal:2',
        'cost_of_goods_sold' => 'decimal:2',
        'available_funds' => 'decimal:2',
        'operating_expenses' => 'decimal:2',
        'monthly_taxes' => 'decimal:2',
        'other_expenses' => 'decimal:2',
        'collateral_value' => 'decimal:2',
        'tenure' => 'integer',
        'principle_amount' => 'decimal:2',
        'days_in_arrears' => 'integer',
        'total_days_in_arrears' => 'integer',
        'arrears_in_amount' => 'decimal:2',
        'future_interest' => 'decimal:2',
        'total_principle' => 'decimal:2',
        'approved_loan_value' => 'decimal:2',
        'approved_term' => 'integer',
        'amount_to_be_credited' => 'decimal:2',
        'disbursement_date' => 'datetime',
        'transaction_processed_at' => 'datetime',
        'transaction_metadata' => 'array',
        'has_exceptions' => 'boolean',
        'exceptions_cleared_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // SLA Tracking
        'stage_entered_at' => 'datetime',
        'sla_breach_level' => 'integer',
        'sla_breached_at' => 'datetime',
        'sla_warning_sent_at' => 'datetime',
        'sla_breach_notified_at' => 'datetime',
        'sla_escalated' => 'boolean',
        'sla_escalated_at' => 'datetime',
        'sla_notification_count' => 'integer'
    ];

    // Relationships
    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanSubProduct::class, 'loan_sub_product', 'sub_product_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    public function clientName(): BelongsTo
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    public function loanBranch(): BelongsTo
    {
        return $this->belongsTo(BranchesModel::class, 'branch_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(loans_schedules::class, 'loan_id', 'loan_id');
    }

    public function loanAccount()
    {
        return $this->belongsTo(AccountsModel::class, 'loan_account_number', 'account_number');
    }

    /**
     * Get the maximum days in arrears for this loan
     */
    public function getMaxDaysInArrearsAttribute()
    {
        return $this->schedules()->max('days_in_arrears') ?? 0;
    }

    /**
     * Get the total amount in arrears for this loan
     */
    public function getTotalAmountInArrearsAttribute()
    {
        return $this->schedules()->sum('amount_in_arrears') ?? 0;
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LoanApproval::class, 'loan_id', 'id');
    }

    public function collateral(): HasMany
    {
        return $this->hasMany(LoanCollateral::class, 'loan_id', 'id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(LoanAuditLog::class, 'loan_id', 'id');
    }

    public function settledLoans(): HasMany
    {
        return $this->hasMany(SettledLoan::class, 'loan_id', 'id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByClient($query, $clientNumber)
    {
        return $query->where('client_number', $clientNumber);
    }

    public function scopeByLoanType($query, $loanType)
    {
        return $query->where('loan_type_2', $loanType);
    }

    // Business Logic Methods
    public function getMonthlyPaymentAttribute()
    {
        if ($this->principle <= 0 || $this->interest <= 0 || $this->tenure <= 0) {
            return 0;
        }

        $monthlyRate = $this->interest / 12 / 100;
        $numerator = $this->principle * $monthlyRate * pow(1 + $monthlyRate, $this->tenure);
        $denominator = pow(1 + $monthlyRate, $this->tenure) - 1;

        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    public function getTotalInterestAttribute()
    {
        return ($this->monthly_payment * $this->tenure) - $this->principle;
    }

    public function getTotalAmountAttribute()
    {
        return $this->principle + $this->total_interest;
    }

    public function getDaysInArrearsAttribute()
    {
        if ($this->disbursement_date && $this->status === 'ACTIVE') {
            // First, check if there are any overdue schedules
            $overdueSchedule = $this->schedules()
                ->where('installment_date', '<=', now())
                ->where('completion_status', '!=', 'COMPLETED')
                ->orderBy('installment_date', 'asc')
                ->first();
            
            if ($overdueSchedule) {
                // Return days since the first overdue payment
                return Carbon::now()->diffInDays($overdueSchedule->installment_date);
            }
            
            // If no overdue schedules, check last completed payment
            $lastPaymentDate = $this->schedules()
                ->where('completion_status', 'COMPLETED')
                ->max('installment_date');
            
            if ($lastPaymentDate) {
                return Carbon::now()->diffInDays($lastPaymentDate);
            }
        }
        return 0;
    }

    public function getAffordabilityRatioAttribute()
    {
        if ($this->available_funds <= 0) {
            return 0;
        }
        return ($this->monthly_payment / $this->available_funds) * 100;
    }

    public function getCollateralCoverageRatioAttribute()
    {
        if ($this->collateral_value <= 0 || $this->principle <= 0) {
            return 0;
        }
        return ($this->collateral_value / $this->principle) * 100;
    }

    public function isOverdue(): bool
    {
        return $this->days_in_arrears > 0;
    }

    public function isAffordable(): bool
    {
        return $this->affordability_ratio <= 70; // 70% threshold
    }

    public function hasAdequateCollateral(): bool
    {
        return $this->collateral_coverage_ratio >= 120; // 120% coverage
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'PENDING' && 
               $this->isAffordable() && 
               $this->hasAdequateCollateral();
    }

    public function getRiskLevelAttribute(): string
    {
        $riskScore = 0;
        
        // Income risk
        if ($this->affordability_ratio > 50) $riskScore += 2;
        if ($this->affordability_ratio > 70) $riskScore += 3;
        
        // Collateral risk
        if ($this->collateral_coverage_ratio < 100) $riskScore += 3;
        if ($this->collateral_coverage_ratio < 120) $riskScore += 1;
        
        // Business risk
        if ($this->business_age < 1) $riskScore += 2;
        if ($this->business_age < 2) $riskScore += 1;
        
        // Credit history risk
        if ($this->days_in_arrears > 30) $riskScore += 3;
        if ($this->days_in_arrears > 90) $riskScore += 2;

        if ($riskScore >= 8) return 'HIGH';
        if ($riskScore >= 5) return 'MEDIUM';
        return 'LOW';
    }

    // Exception handling methods
    public function markAsHavingExceptions(string $trackingId = null): void
    {
        $this->update([
            'has_exceptions' => true,
            'exception_tracking_id' => $trackingId ?? 'EXC_' . $this->id . '_' . time(),
            'status' => 'PENDING-WITH-EXCEPTIONS'
        ]);
    }

    public function clearExceptions(int $clearedBy = null): void
    {
        $this->update([
            // Keep has_exceptions as true for historical tracking - it indicates loan originally had exceptions
            // 'has_exceptions' => false, // DON'T CHANGE THIS - it's for tracking original state
            'exceptions_cleared_at' => now(),
            'exceptions_cleared_by' => $clearedBy ?? auth()->id(),
            'status' => 'PENDING'
        ]);
    }

    public function hasInitialExceptions(): bool
    {
        return $this->has_exceptions || !empty($this->exception_tracking_id);
    }
    
    public function areExceptionsCleared(): bool
    {
        return $this->has_exceptions && !empty($this->exceptions_cleared_at);
    }
    
    public function hasActiveExceptions(): bool
    {
        // Has exceptions but not yet cleared
        return $this->has_exceptions && empty($this->exceptions_cleared_at);
    }

    public function getExceptionTrackingId(): ?string
    {
        return $this->exception_tracking_id;
    }

    public function scopeWithExceptions($query)
    {
        return $query->where('has_exceptions', true);
    }

    public function scopeWithoutExceptions($query)
    {
        return $query->where('has_exceptions', false);
    }

    // ==================== SLA Tracking Methods ====================

    /**
     * Get the process code config for loan approvals
     */
    public function getProcessConfig()
    {
        return ProcessCodeConfig::where('process_code', 'LOAN_APP')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the current approval level based on approval_stage
     * Returns: 1 = First Checker, 2 = Second Checker, 3 = Approver
     */
    public function getCurrentApprovalLevel(): int
    {
        return match($this->approval_stage) {
            'Exception' => 0,
            'Inputter' => 0, // Not in approval workflow yet
            'First Checker' => 1,
            'Second Checker' => 2,
            'Approver' => 3,
            'FINANCE' => 4, // Post-approval
            default => 0
        };
    }

    /**
     * Get the timestamp when the current stage started
     */
    public function getCurrentStageStartTime()
    {
        // If we have explicit stage_entered_at, use that
        if ($this->stage_entered_at) {
            return $this->stage_entered_at;
        }

        // Otherwise, estimate from approval logs or created_at
        $lastLog = \DB::table('loan_approval_logs')
            ->where('loan_id', $this->id)
            ->where('stage', $this->approval_stage)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastLog) {
            return Carbon::parse($lastLog->created_at);
        }

        // Fallback to created_at
        return $this->created_at;
    }

    /**
     * Get hours elapsed at current stage
     */
    public function getHoursAtCurrentStage(): float
    {
        $startTime = $this->getCurrentStageStartTime();
        if (!$startTime) {
            return 0;
        }

        if (!($startTime instanceof Carbon)) {
            $startTime = Carbon::parse($startTime);
        }

        return now()->diffInMinutes($startTime) / 60;
    }

    /**
     * Get the SLA hours limit for the current approval stage
     */
    public function getSlaHoursLimit(): ?int
    {
        $config = $this->getProcessConfig();
        if (!$config) {
            return null;
        }

        $level = $this->getCurrentApprovalLevel();
        return $config->getSlaHoursForLevel($level);
    }

    /**
     * Check if SLA is breached at current stage
     */
    public function isSlaBreached(): bool
    {
        $limit = $this->getSlaHoursLimit();
        if ($limit === null) {
            return false;
        }

        return $this->getHoursAtCurrentStage() > $limit;
    }

    /**
     * Check if SLA warning threshold is reached
     */
    public function isSlaWarningReached(): bool
    {
        $config = $this->getProcessConfig();
        if (!$config) {
            return false;
        }

        $limit = $this->getSlaHoursLimit();
        if ($limit === null) {
            return false;
        }

        $threshold = $config->getSlaWarningThresholdDecimal();
        $warningHours = $limit * $threshold;

        return $this->getHoursAtCurrentStage() >= $warningHours;
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

        $used = $this->getHoursAtCurrentStage();
        return min(($used / $limit) * 100, 100);
    }

    /**
     * Get the role IDs for the current approval stage
     */
    public function getCurrentStageRoleIds(): array
    {
        $config = $this->getProcessConfig();
        if (!$config) {
            return [];
        }

        $level = $this->getCurrentApprovalLevel();

        $roles = match($level) {
            1 => $config->first_checker_roles,
            2 => $config->second_checker_roles,
            3 => $config->approver_roles,
            default => null
        };

        if (empty($roles)) {
            return [];
        }

        // Handle both array and JSON string formats
        if (is_string($roles)) {
            $roles = json_decode($roles, true) ?? [];
        }

        return is_array($roles) ? $roles : [];
    }

    /**
     * Get level name for display
     */
    public function getCurrentStageName(): string
    {
        return $this->approval_stage ?? 'Unknown';
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
     * Reset SLA tracking when moving to a new stage
     */
    public function resetSlaTracking(): void
    {
        $this->update([
            'stage_entered_at' => now(),
            'sla_breach_level' => null,
            'sla_breached_at' => null,
            'sla_warning_sent_at' => null,
            'sla_breach_notified_at' => null,
            'sla_escalated' => false,
            'sla_escalated_at' => null
        ]);
    }

    /**
     * Scope for loans pending SLA check
     * Includes loans in approval workflow (not draft, not completed)
     */
    public function scopePendingSlaCheck($query)
    {
        return $query->whereIn('status', ['PENDING', 'PENDING_APPROVAL', 'PENDING-EXCEPTIONS', 'RETURNED'])
                    ->whereIn('approval_stage', ['First Checker', 'Second Checker', 'Approver'])
                    ->whereExists(function ($q) {
                        $q->select(\DB::raw(1))
                          ->from('process_code_configs')
                          ->where('process_code', 'LOAN_APP')
                          ->where('is_active', true)
                          ->where('sla_enabled', true);
                    });
    }

    /**
     * Scope for SLA breached loans
     */
    public function scopeSlaBreached($query)
    {
        return $query->whereNotNull('sla_breached_at');
    }

    /**
     * Scope for loans needing SLA warning
     */
    public function scopeNeedsSlaWarning($query)
    {
        return $query->whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
                    ->whereIn('approval_stage', ['First Checker', 'Second Checker', 'Approver'])
                    ->whereNull('sla_warning_sent_at');
    }

    /**
     * Scope for breached loans not yet notified
     */
    public function scopeNeedsSlaBreachNotification($query)
    {
        return $query->whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
                    ->whereIn('approval_stage', ['First Checker', 'Second Checker', 'Approver'])
                    ->whereNotNull('sla_breached_at')
                    ->whereNull('sla_breach_notified_at');
    }
}
