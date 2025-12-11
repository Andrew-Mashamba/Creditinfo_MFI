<?php

namespace App\Services;

use App\Models\BudgetManagement;
use App\Models\BudgetAllocation;
use App\Models\BudgetAdvance;
use App\Models\SupplementaryRequest;
use App\Models\BudgetAlert;
use App\Models\BudgetTransfer;
use App\Models\BudgetCarryForward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BudgetFlexibilityService
{
    /**
     * Create monthly allocations for a budget
     */
    public function createMonthlyAllocations($budgetId, $year, $customAllocations = null)
    {
        $budget = BudgetManagement::findOrFail($budgetId);
        $annualAmount = $budget->revenue ?: $budget->allocated_amount;
        
        if (!$annualAmount || $annualAmount <= 0) {
            throw new \Exception('Budget must have a positive annual amount');
        }
        
        // Check if allocations already exist for this budget and year
        $existingAllocations = BudgetAllocation::where('budget_id', $budgetId)
            ->where('year', $year)
            ->count();
            
        if ($existingAllocations > 0) {
            throw new \Exception('Allocations already exist for this budget and year. Delete existing allocations first.');
        }
        
        $allocations = [];
        
        DB::beginTransaction();
        try {
            for ($month = 1; $month <= 12; $month++) {
                $percentage = 8.33; // Default equal distribution
                $amount = round($annualAmount / 12, 2);
                
                // Check for custom allocation
                if ($customAllocations && isset($customAllocations[$month])) {
                    $percentage = $customAllocations[$month]['percentage'] ?? 8.33;
                    $amount = round(($annualAmount * $percentage) / 100, 2);
                }
                
                // Adjust last month for rounding differences
                if ($month == 12) {
                    $totalAllocated = array_sum(array_column($allocations, 'allocated_amount'));
                    $amount = $annualAmount - $totalAllocated;
                }
                
                $allocation = BudgetAllocation::create([
                    'budget_id' => $budgetId,
                    'allocation_type' => 'MONTHLY',
                    'period' => $month,
                    'year' => $year,
                    'allocated_amount' => $amount,
                    'available_amount' => $amount,
                    'percentage' => $percentage,
                    'rollover_policy' => $budget->rollover_policy ?? 'APPROVAL_REQUIRED'
                ]);
                
                $allocations[] = $allocation;
            }
            
            DB::commit();
            return $allocations;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process budget advance request
     */
    public function requestBudgetAdvance($budgetId, $currentPeriod, $currentYear, $advanceAmount, $fromPeriod, $fromYear, $reason)
    {
        DB::beginTransaction();
        try {
            // Validate source allocation exists and has funds
            $sourceAllocation = BudgetAllocation::where('budget_id', $budgetId)
                ->where('period', $fromPeriod)
                ->where('year', $fromYear)
                ->first();
                
            if (!$sourceAllocation) {
                throw new \Exception('Source period allocation not found');
            }
            
            if ($sourceAllocation->available_amount < $advanceAmount) {
                throw new \Exception('Insufficient funds in source period. Available: ' . 
                                   number_format($sourceAllocation->available_amount, 2));
            }
            
            // Create advance request
            $advance = BudgetAdvance::create([
                'budget_id' => $budgetId,
                'from_period' => $fromPeriod,
                'from_year' => $fromYear,
                'to_period' => $currentPeriod,
                'to_year' => $currentYear,
                'advance_amount' => $advanceAmount,
                'outstanding_amount' => $advanceAmount,
                'reason' => $reason,
                'status' => 'PENDING',
                'requested_by' => auth()->id() ?? 1,
                'due_date' => Carbon::create($fromYear, $fromPeriod, 1)->endOfMonth(),
                'repayment_method' => 'AUTOMATIC'
            ]);
            
            // Create alert
            $this->createBudgetAlert($budgetId, 'WARNING', 
                'Budget Advance Requested',
                "Advance request of " . number_format($advanceAmount, 2) . " from period {$fromPeriod}/{$fromYear}",
                'INFO', ['advance_id' => $advance->id]);
            
            DB::commit();
            return $advance;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process supplementary budget request
     */
    public function requestSupplementaryBudget($budgetId, $period, $year, $requestedAmount, $justification, $urgencyLevel = 'MEDIUM')
    {
        DB::beginTransaction();
        try {
            $allocation = BudgetAllocation::where('budget_id', $budgetId)
                ->where('period', $period)
                ->where('year', $year)
                ->first();
                
            if (!$allocation) {
                // Create allocation if it doesn't exist
                $budget = BudgetManagement::findOrFail($budgetId);
                $allocation = BudgetAllocation::create([
                    'budget_id' => $budgetId,
                    'allocation_type' => 'MONTHLY',
                    'period' => $period,
                    'year' => $year,
                    'allocated_amount' => 0,
                    'available_amount' => 0
                ]);
            }
            
            $request = SupplementaryRequest::create([
                'budget_id' => $budgetId,
                'period' => $period,
                'year' => $year,
                'current_allocation' => $allocation->allocated_amount,
                'requested_amount' => $requestedAmount,
                'urgency_level' => $urgencyLevel,
                'justification' => $justification,
                'status' => 'DRAFT',
                'requested_by' => auth()->id() ?? 1
            ]);
            
            // Auto-submit if urgent
            if ($urgencyLevel === 'CRITICAL') {
                $request->submit();
            }
            
            DB::commit();
            return $request;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process rollover from previous period
     */
    public function processRollover($budgetId, $fromPeriod, $fromYear, $toPeriod, $toYear)
    {
        DB::beginTransaction();
        try {
            $sourceAllocation = BudgetAllocation::where('budget_id', $budgetId)
                ->where('period', $fromPeriod)
                ->where('year', $fromYear)
                ->first();
                
            if (!$sourceAllocation || $sourceAllocation->available_amount <= 0) {
                throw new \Exception('No funds available for rollover');
            }
            
            if (!$sourceAllocation->canRollover()) {
                throw new \Exception('Rollover not allowed for this allocation');
            }
            
            $targetAllocation = BudgetAllocation::where('budget_id', $budgetId)
                ->where('period', $toPeriod)
                ->where('year', $toYear)
                ->first();
                
            if (!$targetAllocation) {
                throw new \Exception('Target period allocation not found');
            }
            
            $rolloverAmount = $sourceAllocation->available_amount;
            
            // Process based on policy
            if ($sourceAllocation->rollover_policy === 'AUTOMATIC') {
                $targetAllocation->rollover_amount += $rolloverAmount;
                $targetAllocation->available_amount += $rolloverAmount;
                $targetAllocation->save();
                
                $sourceAllocation->available_amount = 0;
                $sourceAllocation->save();
                
                $this->createBudgetAlert($budgetId, 'MILESTONE',
                    'Automatic Rollover Completed',
                    "Amount of " . number_format($rolloverAmount, 2) . " rolled over from {$fromPeriod}/{$fromYear} to {$toPeriod}/{$toYear}",
                    'INFO');
                    
            } else {
                // Create pending rollover request
                $this->createBudgetAlert($budgetId, 'MILESTONE',
                    'Rollover Available',
                    "Amount of " . number_format($rolloverAmount, 2) . " available for rollover from {$fromPeriod}/{$fromYear}",
                    'INFO');
            }
            
            DB::commit();
            return $rolloverAmount;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process budget transfer between items
     */
    public function processBudgetTransfer($fromBudgetId, $toBudgetId, $amount, $period, $year, $reason)
    {
        DB::beginTransaction();
        try {
            // Get source allocation
            $sourceAllocation = BudgetAllocation::where('budget_id', $fromBudgetId)
                ->where('period', $period)
                ->where('year', $year)
                ->first();
                
            if (!$sourceAllocation || $sourceAllocation->available_amount < $amount) {
                throw new \Exception('Insufficient funds in source budget');
            }
            
            // Get or create target allocation
            $targetAllocation = BudgetAllocation::firstOrCreate(
                [
                    'budget_id' => $toBudgetId,
                    'period' => $period,
                    'year' => $year
                ],
                [
                    'allocation_type' => 'MONTHLY',
                    'allocated_amount' => 0,
                    'available_amount' => 0
                ]
            );
            
            // Process transfer
            $sourceAllocation->transferred_out += $amount;
            $sourceAllocation->available_amount -= $amount;
            $sourceAllocation->save();
            
            $targetAllocation->transferred_in += $amount;
            $targetAllocation->available_amount += $amount;
            $targetAllocation->save();
            
            // Create transfer record
            $transfer = BudgetTransfer::create([
                'from_budget_id' => $fromBudgetId,
                'to_budget_id' => $toBudgetId,
                'amount' => $amount,
                'reason' => $reason,
                'status' => 'APPROVED',
                'transfer_number' => 'TRF-' . date('Ymd') . '-' . rand(1000, 9999),
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);
            
            DB::commit();
            return $transfer;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Check and create budget alerts
     */
    public function checkBudgetAlerts($budgetId, $period = null, $year = null)
    {
        $budget = BudgetManagement::find($budgetId);
        if (!$budget) return;
        
        $period = $period ?: now()->month;
        $year = $year ?: now()->year;
        
        $allocation = BudgetAllocation::where('budget_id', $budgetId)
            ->where('period', $period)
            ->where('year', $year)
            ->first();
            
        if (!$allocation) return;
        
        $utilizationPercentage = $allocation->utilization_percentage;
        
        // Check for over-utilization
        if ($utilizationPercentage >= 100) {
            $this->createBudgetAlert($budgetId, 'OVERSPENT',
                'Budget Over-Utilized',
                "Budget utilization at {$utilizationPercentage}% for period {$period}/{$year}",
                'CRITICAL', ['percentage' => $utilizationPercentage]);
        }
        // Check for approaching limit
        elseif ($utilizationPercentage >= 80) {
            $this->createBudgetAlert($budgetId, 'WARNING',
                'Approaching Budget Limit',
                "Budget utilization at {$utilizationPercentage}% for period {$period}/{$year}",
                'WARNING', ['percentage' => $utilizationPercentage]);
        }
        
        // Check for low balance
        if ($allocation->available_amount < ($allocation->allocated_amount * 0.1)) {
            $this->createBudgetAlert($budgetId, 'WARNING',
                'Low Budget Balance',
                "Only " . number_format($allocation->available_amount, 2) . " remaining for period {$period}/{$year}",
                'WARNING');
        }
        
        // Check for period closing (5 days before month end)
        if (now()->endOfMonth()->diffInDays(now()) <= 5) {
            $this->createBudgetAlert($budgetId, 'PERIOD_END',
                'Budget Period Closing Soon',
                "Budget period {$period}/{$year} closing in " . now()->endOfMonth()->diffInDays(now()) . " days",
                'INFO');
        }
        
        // Check for overdue advances
        $overdueAdvances = BudgetAdvance::where('budget_id', $budgetId)
            ->where('status', 'APPROVED')
            ->where('due_date', '<', now())
            ->where('outstanding_amount', '>', 0)
            ->get();
            
        foreach ($overdueAdvances as $advance) {
            $this->createBudgetAlert($budgetId, 'WARNING',
                'Budget Advance Overdue',
                "Advance {$advance->advance_number} of " . number_format($advance->outstanding_amount, 2) . " is overdue",
                'WARNING', ['advance_id' => $advance->id]);
        }
    }
    
    /**
     * Create budget alert
     */
    private function createBudgetAlert($budgetId, $type, $title, $message, $severity = 'INFO', $data = null)
    {
        // Check if similar alert exists in last 24 hours
        $existingAlert = BudgetAlert::where('budget_id', $budgetId)
            ->where('alert_type', $type)
            ->where('created_at', '>=', now()->subDay())
            ->where('is_acknowledged', false)
            ->first();
            
        if ($existingAlert) {
            return $existingAlert;
        }
        
        return BudgetAlert::create([
            'budget_id' => $budgetId,
            'alert_type' => $type,
            'message' => $title . ': ' . $message,
            'threshold_value' => 0,
            'actual_value' => 0,
            'is_sent' => false,
            'is_acknowledged' => false
        ]);
    }
    
    /**
     * Get budget utilization summary
     */
    public function getBudgetUtilizationSummary($budgetId, $year = null)
    {
        $year = $year ?: now()->year;
        
        $allocations = BudgetAllocation::where('budget_id', $budgetId)
            ->where('year', $year)
            ->orderBy('period')
            ->get();
            
        $summary = [
            'annual_allocated' => $allocations->sum('allocated_amount'),
            'annual_utilized' => $allocations->sum('utilized_amount'),
            'annual_available' => $allocations->sum('available_amount'),
            'total_rollover' => $allocations->sum('rollover_amount'),
            'total_advances' => $allocations->sum('advance_amount'),
            'total_supplementary' => $allocations->sum('supplementary_amount'),
            'total_transfers_in' => $allocations->sum('transferred_in'),
            'total_transfers_out' => $allocations->sum('transferred_out'),
            'monthly_breakdown' => []
        ];
        
        foreach ($allocations as $allocation) {
            $summary['monthly_breakdown'][$allocation->period] = [
                'allocated' => $allocation->allocated_amount,
                'utilized' => $allocation->utilized_amount,
                'available' => $allocation->total_available,
                'utilization_percentage' => $allocation->utilization_percentage,
                'rollover' => $allocation->rollover_amount,
                'advances' => $allocation->advance_amount,
                'supplementary' => $allocation->supplementary_amount
            ];
        }
        
        $summary['annual_utilization_percentage'] = $summary['annual_allocated'] > 0 
            ? round(($summary['annual_utilized'] / $summary['annual_allocated']) * 100, 2)
            : 0;
            
        return $summary;
    }
    
    /**
     * Process end of period activities
     */
    public function processEndOfPeriod($period, $year)
    {
        $allocations = BudgetAllocation::where('period', $period)
            ->where('year', $year)
            ->get();
            
        foreach ($allocations as $allocation) {
            // Process automatic rollovers
            if ($allocation->rollover_policy === 'AUTOMATIC' && $allocation->available_amount > 0) {
                $nextPeriod = $period == 12 ? 1 : $period + 1;
                $nextYear = $period == 12 ? $year + 1 : $year;
                
                $this->processRollover(
                    $allocation->budget_id,
                    $period,
                    $year,
                    $nextPeriod,
                    $nextYear
                );
            }
            
            // Process automatic advance repayments
            $advances = BudgetAdvance::where('budget_id', $allocation->budget_id)
                ->where('to_period', $period)
                ->where('to_year', $year)
                ->where('status', 'APPROVED')
                ->where('outstanding_amount', '>', 0)
                ->where('repayment_method', 'AUTOMATIC')
                ->get();
                
            foreach ($advances as $advance) {
                $advance->processAutomaticRepayment();
            }
            
            // Lock the period
            $allocation->is_locked = true;
            $allocation->locked_at = now();
            $allocation->locked_by = 1; // System user
            $allocation->save();
        }
    }
    
    /**
     * Create year-end carry forward
     */
    public function createYearEndCarryForward($budgetId, $fromYear, $toYear)
    {
        $totalAvailable = BudgetAllocation::where('budget_id', $budgetId)
            ->where('year', $fromYear)
            ->sum('available_amount');
            
        if ($totalAvailable <= 0) {
            throw new \Exception('No funds available for carry forward');
        }
        
        return BudgetCarryForward::create([
            'from_budget_id' => $budgetId,
            'to_budget_id' => $budgetId,
            'from_year' => $fromYear,
            'to_year' => $toYear,
            'carry_forward_amount' => $totalAvailable,
            'status' => 'PENDING',
            'justification' => 'Year-end carry forward'
        ]);
    }
}