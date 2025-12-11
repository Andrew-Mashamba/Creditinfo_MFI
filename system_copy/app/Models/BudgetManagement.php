<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetManagement extends Model
{
    use HasFactory;

    protected $table = 'budget_managements';

    protected $fillable = [
        'revenue',
        'expenditure',
        'capital_expenditure',
        'budget_name',
        'budget_type',
        'allocation_pattern',
        'monthly_allocations',
        'quarterly_allocations',
        'start_date',
        'end_date',
        'budget_year',
        'budget_quarter',
        'budget_month',
        'allocated_amount',
        'spent_amount',
        'committed_amount',
        'available_amount',
        'variance_amount',
        'utilization_percentage',
        'status',
        'approval_status',
        'notes',
        'expense_account_id',
        'department',
        'currency',
        'warning_threshold',
        'critical_threshold',
        'alerts_enabled',
        'last_alert_sent',
        'last_transaction_date',
        'last_calculated_at',
        'approval_request_id',
        'edit_approval_status',
        'pending_changes',
        'is_locked',
        'locked_reason',
        'locked_at',
        'locked_by'
    ];

    protected $casts = [
        'revenue' => 'double',
        'expenditure' => 'double',
        'capital_expenditure' => 'double',
        'allocated_amount' => 'double',
        'spent_amount' => 'double',
        'committed_amount' => 'double',
        'available_amount' => 'double',
        'variance_amount' => 'double',
        'utilization_percentage' => 'double',
        'warning_threshold' => 'double',
        'critical_threshold' => 'double',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_alert_sent' => 'datetime',
        'last_transaction_date' => 'datetime',
        'last_calculated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'department' => 'integer',
        'budget_year' => 'integer',
        'budget_quarter' => 'integer',
        'budget_month' => 'integer',
        'monthly_allocations' => 'array',
        'quarterly_allocations' => 'array',
        'pending_changes' => 'array',
        'is_locked' => 'boolean',
        'alerts_enabled' => 'boolean',
        'locked_at' => 'datetime',
        'locked_by' => 'integer'
    ];

    /**
     * Get the department that owns the budget
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department');
    }
    
    /**
     * Get the budget department
     */
    public function budgetDepartment()
    {
        return $this->belongsTo(BudgetDepartment::class, 'budget_department_id');
    }
    
    /**
     * Get budget versions
     */
    public function versions()
    {
        return $this->hasMany(BudgetVersion::class, 'budget_id');
    }
    
    /**
     * Get budget scenarios
     */
    public function scenarios()
    {
        return $this->hasMany(BudgetScenario::class, 'budget_id');
    }
    
    /**
     * Get active scenario
     */
    public function activeScenario()
    {
        return $this->belongsTo(BudgetScenario::class, 'active_scenario_id');
    }
    
    /**
     * Get commitments
     */
    public function commitments()
    {
        return $this->hasMany(BudgetCommitment::class, 'budget_id');
    }
    
    /**
     * Get custom allocations
     */
    public function customAllocations()
    {
        return $this->hasMany(BudgetCustomAllocation::class, 'budget_id');
    }
    
    /**
     * Get transfers from this budget
     */
    public function transfersFrom()
    {
        return $this->hasMany(BudgetTransfer::class, 'from_budget_id');
    }
    
    /**
     * Get transfers to this budget
     */
    public function transfersTo()
    {
        return $this->hasMany(BudgetTransfer::class, 'to_budget_id');
    }

    /**
     * Get the expense account associated with this budget
     */
    public function expenseAccount()
    {
        return $this->belongsTo(AccountsModel::class, 'expense_account_id');
    }

    /**
     * Get the expenses associated with this budget
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'budget_item_id');
    }

    /**
     * Get the budget transactions
     */
    public function transactions()
    {
        return $this->hasMany(BudgetTransaction::class, 'budget_id');
    }

    /**
     * Get the budget alerts
     */
    public function alerts()
    {
        return $this->hasMany(BudgetAlert::class, 'budget_id');
    }

    /**
     * Scope a query to only include pending budgets
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'PENDING');
    }

    /**
     * Scope a query to only include approved budgets
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'APPROVED');
    }

    /**
     * Scope a query to only include active budgets
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Get the total budget amount
     */
    public function getTotalAmountAttribute()
    {
        return ($this->revenue ?? 0) + ($this->capital_expenditure ?? 0);
    }

    /**
     * Get the remaining budget amount
     */
    public function getRemainingAmountAttribute()
    {
        return $this->getTotalAmountAttribute() - ($this->spent_amount ?? 0);
    }

    /**
     * Get the budget utilization percentage
     */
    public function getUtilizationPercentageAttribute()
    {
        $total = $this->getTotalAmountAttribute();
        if ($total <= 0) {
            return 0;
        }
        
        return round((($this->spent_amount ?? 0) / $total) * 100, 2);
    }

    /**
     * Calculate and update budget metrics
     */
    public function calculateBudgetMetrics()
    {
        // Calculate allocated amount based on budget type
        $this->allocated_amount = $this->revenue + $this->capital_expenditure;
        
        // Get actual spent amount from the linked expense account
        if ($this->expense_account_id && $this->expenseAccount) {
            // Get account balance (which represents total utilized amount)
            $accountBalance = floatval($this->expenseAccount->balance ?? 0);
            $this->spent_amount = abs($accountBalance); // Use absolute value as expense accounts may be negative
            
            // Also calculate from General Ledger transactions for detailed tracking
            $glTransactions = \App\Models\GeneralLedger::where('record_on_account_number', $this->expenseAccount->account_number)
                ->whereNotNull('debit')
                ->sum('debit');
            
            // Use the greater of the two values for accuracy
            if ($glTransactions > $this->spent_amount) {
                $this->spent_amount = $glTransactions;
            }
        }
        
        // Calculate available amount (allocated - spent - committed)
        $this->available_amount = $this->allocated_amount - $this->spent_amount - $this->committed_amount;
        
        // Calculate variance (positive means under budget, negative means over budget)
        $this->variance_amount = $this->allocated_amount - $this->spent_amount;
        
        // Calculate utilization percentage
        if ($this->allocated_amount > 0) {
            $this->utilization_percentage = round(($this->spent_amount / $this->allocated_amount) * 100, 2);
        } else {
            $this->utilization_percentage = 0;
        }
        
        // Update calculation timestamp
        $this->last_calculated_at = now();
        
        // Save without triggering events to avoid loops
        $this->saveQuietly();
        
        // Check for spending milestones and create versions if needed
        $this->checkAndCreateMilestoneVersion();
        
        return $this;
    }

    /**
     * Check if budget needs alert
     */
    public function checkAlertStatus()
    {
        if (!$this->alerts_enabled) {
            return null;
        }
        
        if ($this->utilization_percentage >= $this->critical_threshold) {
            return 'CRITICAL';
        }
        
        if ($this->utilization_percentage >= $this->warning_threshold) {
            return 'WARNING';
        }
        
        if ($this->utilization_percentage > 100) {
            return 'OVERSPENT';
        }
        
        return null;
    }

    /**
     * Get budget status color
     */
    public function getStatusColorAttribute()
    {
        if ($this->utilization_percentage > 100) {
            return 'red';
        }
        
        if ($this->utilization_percentage >= $this->critical_threshold) {
            return 'orange';
        }
        
        if ($this->utilization_percentage >= $this->warning_threshold) {
            return 'yellow';
        }
        
        return 'green';
    }

    /**
     * Get budget health status
     */
    public function getHealthStatusAttribute()
    {
        if ($this->utilization_percentage > 100) {
            return 'OVERSPENT';
        }
        
        if ($this->utilization_percentage >= $this->critical_threshold) {
            return 'CRITICAL';
        }
        
        if ($this->utilization_percentage >= $this->warning_threshold) {
            return 'WARNING';
        }
        
        if ($this->utilization_percentage >= 50) {
            return 'NORMAL';
        }
        
        return 'HEALTHY';
    }

    /**
     * Add a transaction to the budget
     */
    public function addTransaction($amount, $type = 'EXPENSE', $description = null, $reference = null)
    {
        $transaction = $this->transactions()->create([
            'transaction_type' => $type,
            'amount' => $amount,
            'description' => $description,
            'reference_number' => $reference,
            'transaction_date' => now(),
            'status' => 'POSTED',
            'created_by' => auth()->id() ?? 1,
            'posted_by' => auth()->id() ?? 1,
            'posted_at' => now()
        ]);
        
        // Update spent amount if expense
        if ($type === 'EXPENSE') {
            $this->spent_amount += $amount;
            $this->last_transaction_date = now();
        } elseif ($type === 'COMMITMENT') {
            $this->committed_amount += $amount;
        }
        
        // Recalculate metrics
        $this->calculateBudgetMetrics();
        
        return $transaction;
    }

    /**
     * Get GL entries for this budget
     */
    public function generalLedgerEntries()
    {
        return $this->hasMany(GeneralLedger::class, 'budget_id');
    }
    
    /**
     * Get GL transactions based on linked expense account
     */
    public function glTransactions()
    {
        if ($this->expense_account_id && $this->expenseAccount) {
            return GeneralLedger::where('record_on_account_number', $this->expenseAccount->account_number);
        }
        return GeneralLedger::whereNull('id'); // Return empty query if no account linked
    }
    
    /**
     * Get individual utilization details
     */
    public function getUtilizationDetails()
    {
        if (!$this->expense_account_id || !$this->expenseAccount) {
            return collect([]);
        }
        
        return GeneralLedger::where('record_on_account_number', $this->expenseAccount->account_number)
            ->select('id', 'reference_number', 'description', 'debit_amount', 'credit_amount', 'transaction_date', 'beneficiary_name')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }
    
    /**
     * Get carry forwards from this budget
     */
    public function carryForwardsFrom()
    {
        return $this->hasMany(BudgetCarryForward::class, 'from_budget_id');
    }
    
    /**
     * Get carry forwards to this budget
     */
    public function carryForwardsTo()
    {
        return $this->hasMany(BudgetCarryForward::class, 'to_budget_id');
    }
    
    /**
     * Get monthly allocation for a specific month
     */
    public function getMonthlyAllocation($month = null)
    {
        $month = $month ?? now()->month;
        
        if ($this->monthly_allocations && isset($this->monthly_allocations[$month - 1])) {
            return $this->monthly_allocations[$month - 1];
        }
        
        // Default to equal distribution
        return round($this->allocated_amount / 12, 2);
    }

    /**
     * Scope for budgets needing alerts
     */
    public function scopeNeedingAlerts($query)
    {
        return $query->where('alerts_enabled', true)
            ->where(function ($q) {
                $q->whereRaw('utilization_percentage >= warning_threshold')
                    ->orWhere('utilization_percentage', '>', 100);
            });
    }

    /**
     * Scope for over-budget items
     */
    public function scopeOverBudget($query)
    {
        return $query->whereRaw('CAST(utilization_percentage as DECIMAL) > ?', [100]);
    }

    /**
     * Check and create version for spending milestones
     */
    public function checkAndCreateMilestoneVersion()
    {
        $utilization = floatval($this->utilization_percentage);
        $milestoneThresholds = [50, 75, 100];
        
        foreach ($milestoneThresholds as $threshold) {
            // Check if we've crossed this milestone
            $milestoneKey = "milestone_{$threshold}_recorded";
            
            // Check if this milestone hasn't been recorded yet
            if ($utilization >= $threshold && !$this->$milestoneKey) {
                try {
                    $versionNumber = BudgetVersion::where('budget_id', $this->id)->count() + 1;
                    
                    BudgetVersion::create([
                        'budget_id' => $this->id,
                        'version_number' => $versionNumber,
                        'version_name' => "Version {$versionNumber} - {$threshold}% Utilization Milestone",
                        'version_type' => 'MILESTONE',
                        'allocated_amount' => $this->allocated_amount,
                        'spent_amount' => $this->spent_amount,
                        'committed_amount' => $this->committed_amount,
                        'effective_from' => now(),
                        'created_by' => auth()->id() ?? 1, // System generated
                        'revision_reason' => "Automatic version created at {$threshold}% budget utilization",
                        'change_summary' => json_encode([
                            'milestone' => "{$threshold}%",
                            'actual_utilization' => $utilization,
                            'spent_amount' => $this->spent_amount,
                            'allocated_amount' => $this->allocated_amount,
                            'available_amount' => $this->available_amount,
                            'recorded_at' => now()->toDateTimeString()
                        ]),
                        'is_active' => false // Milestone versions are informational only
                    ]);
                    
                    // Mark this milestone as recorded
                    $this->$milestoneKey = true;
                    $this->save();
                    
                    \Log::info('Milestone version created for budget', [
                        'budget_id' => $this->id,
                        'budget_name' => $this->budget_name,
                        'milestone' => "{$threshold}%",
                        'version_number' => $versionNumber
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create milestone version', [
                        'budget_id' => $this->id,
                        'milestone' => "{$threshold}%",
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Scope for at-risk budgets
     */
    public function scopeAtRisk($query)
    {
        return $query->whereRaw('CAST(utilization_percentage as DECIMAL) >= CAST(warning_threshold as DECIMAL)');
    }
} 