<?php

namespace App\Services;

use App\Models\BudgetManagement;
use App\Models\BudgetTransaction;
use App\Models\BudgetAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BudgetMonitoringService
{
    /**
     * Process a new expense against budget
     */
    public function recordExpense($budgetId, $amount, $description = null, $reference = null, $transactionId = null)
    {
        try {
            DB::beginTransaction();

            $budget = BudgetManagement::findOrFail($budgetId);
            
            // Create budget transaction
            $transaction = BudgetTransaction::create([
                'budget_id' => $budgetId,
                'transaction_id' => $transactionId,
                'transaction_type' => 'EXPENSE',
                'reference_number' => $reference,
                'amount' => $amount,
                'description' => $description,
                'transaction_date' => now(),
                'status' => 'POSTED',
                'created_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now()
            ]);

            // Update budget spent amount
            $budget->spent_amount += $amount;
            $budget->last_transaction_date = now();
            
            // Recalculate metrics
            $budget->calculateBudgetMetrics();
            
            // Check for alerts
            $this->checkAndCreateAlerts($budget);
            
            DB::commit();
            
            Log::info('Budget expense recorded', [
                'budget_id' => $budgetId,
                'amount' => $amount,
                'new_spent_amount' => $budget->spent_amount,
                'utilization' => $budget->utilization_percentage
            ]);
            
            return $transaction;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record budget expense', [
                'budget_id' => $budgetId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Record a commitment (purchase order, etc.)
     */
    public function recordCommitment($budgetId, $amount, $description = null, $reference = null)
    {
        try {
            DB::beginTransaction();

            $budget = BudgetManagement::findOrFail($budgetId);
            
            // Check if commitment would exceed budget
            if (($budget->spent_amount + $budget->committed_amount + $amount) > $budget->allocated_amount) {
                throw new \Exception('Commitment would exceed budget allocation');
            }
            
            // Create budget transaction
            $transaction = BudgetTransaction::create([
                'budget_id' => $budgetId,
                'transaction_type' => 'COMMITMENT',
                'reference_number' => $reference,
                'amount' => $amount,
                'description' => $description,
                'transaction_date' => now(),
                'status' => 'POSTED',
                'created_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now()
            ]);

            // Update budget committed amount
            $budget->committed_amount += $amount;
            
            // Recalculate metrics
            $budget->calculateBudgetMetrics();
            
            // Check for alerts
            $this->checkAndCreateAlerts($budget);
            
            DB::commit();
            
            return $transaction;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record budget commitment', [
                'budget_id' => $budgetId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Convert commitment to expense
     */
    public function convertCommitmentToExpense($commitmentId, $actualAmount = null)
    {
        try {
            DB::beginTransaction();

            $commitment = BudgetTransaction::where('transaction_type', 'COMMITMENT')
                ->where('status', 'POSTED')
                ->findOrFail($commitmentId);
            
            $budget = $commitment->budget;
            $amount = $actualAmount ?? $commitment->amount;
            
            // Reverse the commitment
            $budget->committed_amount -= $commitment->amount;
            $commitment->status = 'REVERSED';
            $commitment->save();
            
            // Create expense transaction
            $expense = $this->recordExpense(
                $budget->id,
                $amount,
                "Converted from commitment: " . $commitment->description,
                $commitment->reference_number
            );
            
            DB::commit();
            
            return $expense;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to convert commitment to expense', [
                'commitment_id' => $commitmentId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check and create alerts for budget
     */
    public function checkAndCreateAlerts(BudgetManagement $budget)
    {
        $alertStatus = $budget->checkAlertStatus();
        
        if (!$alertStatus) {
            return null;
        }
        
        // Check if alert was recently sent (within last 24 hours)
        if ($budget->last_alert_sent && $budget->last_alert_sent->diffInHours(now()) < 24) {
            return null;
        }
        
        // Check if similar alert already exists and is unacknowledged
        $existingAlert = BudgetAlert::where('budget_id', $budget->id)
            ->where('alert_type', $alertStatus)
            ->where('is_acknowledged', false)
            ->where('created_at', '>=', now()->subDay())
            ->first();
        
        if ($existingAlert) {
            return null;
        }
        
        // Create and send alert
        $alert = BudgetAlert::createForBudget($budget, $alertStatus);
        $alert->send();
        
        return $alert;
    }

    /**
     * Calculate budget variance analysis
     */
    public function calculateVariance(BudgetManagement $budget, $period = 'current_month')
    {
        $startDate = null;
        $endDate = null;
        
        switch ($period) {
            case 'current_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'current_quarter':
                $startDate = Carbon::now()->firstOfQuarter();
                $endDate = Carbon::now()->lastOfQuarter();
                break;
            case 'current_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'all':
                $startDate = $budget->start_date;
                $endDate = $budget->end_date;
                break;
        }
        
        // Get transactions for period
        $transactions = $budget->transactions()
            ->posted()
            ->expenses()
            ->forPeriod($startDate, $endDate)
            ->sum('amount');
        
        // Calculate expected spending for period
        $totalDays = $budget->start_date->diffInDays($budget->end_date);
        $periodDays = $startDate->diffInDays($endDate);
        $expectedSpending = ($budget->allocated_amount / $totalDays) * $periodDays;
        
        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'expected_spending' => $expectedSpending,
            'actual_spending' => $transactions,
            'variance' => $expectedSpending - $transactions,
            'variance_percentage' => $expectedSpending > 0 ? 
                round((($expectedSpending - $transactions) / $expectedSpending) * 100, 2) : 0
        ];
    }

    /**
     * Get budget summary statistics
     */
    public function getBudgetSummary()
    {
        $summary = [
            'total_budgets' => BudgetManagement::count(),
            'active_budgets' => BudgetManagement::active()->count(),
            'total_allocated' => BudgetManagement::active()
                ->selectRaw('COALESCE(SUM(CAST(allocated_amount as DECIMAL)), 0) as total')
                ->value('total') ?? 0,
            'total_spent' => BudgetManagement::active()
                ->selectRaw('COALESCE(SUM(CAST(spent_amount as DECIMAL)), 0) as total')
                ->value('total') ?? 0,
            'total_committed' => BudgetManagement::active()
                ->selectRaw('COALESCE(SUM(CAST(committed_amount as DECIMAL)), 0) as total')
                ->value('total') ?? 0,
            'total_available' => BudgetManagement::active()
                ->selectRaw('COALESCE(SUM(CAST(available_amount as DECIMAL)), 0) as total')
                ->value('total') ?? 0,
            'average_utilization' => BudgetManagement::active()
                ->selectRaw('COALESCE(AVG(CAST(utilization_percentage as DECIMAL)), 0) as average')
                ->value('average') ?? 0,
            'over_budget_count' => BudgetManagement::overBudget()->count(),
            'at_risk_count' => BudgetManagement::atRisk()->count(),
            'healthy_count' => BudgetManagement::active()
                ->whereRaw('CAST(utilization_percentage as DECIMAL) < ?', [50])
                ->count()
        ];
        
        return $summary;
    }

    /**
     * Get budgets needing attention
     */
    public function getBudgetsNeedingAttention()
    {
        return BudgetManagement::with(['expenseAccount', 'alerts' => function($query) {
                $query->unacknowledged()->latest();
            }])
            ->active()
            ->where(function ($query) {
                $query->overBudget()
                    ->orWhere(function($q) {
                        $q->atRisk();
                    });
            })
            ->orderByRaw('CAST(utilization_percentage as DECIMAL) DESC NULLS LAST')
            ->get();
    }

    /**
     * Recalculate all budget metrics
     */
    public function recalculateAllBudgets()
    {
        $budgets = BudgetManagement::active()->get();
        
        foreach ($budgets as $budget) {
            $budget->calculateBudgetMetrics();
        }
        
        Log::info('Budget metrics recalculated', [
            'budget_count' => $budgets->count()
        ]);
        
        return $budgets->count();
    }

    /**
     * Generate budget performance report
     */
    public function generatePerformanceReport($budgetId = null, $startDate = null, $endDate = null)
    {
        $query = BudgetManagement::with(['transactions', 'expenseAccount']);
        
        if ($budgetId) {
            $query->where('id', $budgetId);
        }
        
        $budgets = $query->get();
        
        $report = [];
        
        foreach ($budgets as $budget) {
            $variance = $this->calculateVariance($budget, 'all');
            
            $report[] = [
                'budget_id' => $budget->id,
                'budget_name' => $budget->budget_name,
                'allocated_amount' => $budget->allocated_amount,
                'spent_amount' => $budget->spent_amount,
                'committed_amount' => $budget->committed_amount,
                'available_amount' => $budget->available_amount,
                'utilization_percentage' => $budget->utilization_percentage,
                'variance' => $variance['variance'],
                'variance_percentage' => $variance['variance_percentage'],
                'health_status' => $budget->health_status,
                'transaction_count' => $budget->transactions()->count(),
                'last_transaction_date' => $budget->last_transaction_date
            ];
        }
        
        return $report;
    }

    /**
     * Process budget transfer
     */
    public function transferBudget($fromBudgetId, $toBudgetId, $amount, $reason)
    {
        try {
            DB::beginTransaction();
            
            $fromBudget = BudgetManagement::findOrFail($fromBudgetId);
            $toBudget = BudgetManagement::findOrFail($toBudgetId);
            
            // Check if source budget has sufficient funds
            if ($fromBudget->available_amount < $amount) {
                throw new \Exception('Insufficient available funds in source budget');
            }
            
            // Create transfer transactions
            BudgetTransaction::create([
                'budget_id' => $fromBudgetId,
                'transaction_type' => 'TRANSFER',
                'reference_number' => 'TRF-' . time(),
                'amount' => -$amount,
                'description' => "Transfer to {$toBudget->budget_name}: {$reason}",
                'transaction_date' => now(),
                'status' => 'POSTED',
                'created_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now()
            ]);
            
            BudgetTransaction::create([
                'budget_id' => $toBudgetId,
                'transaction_type' => 'TRANSFER',
                'reference_number' => 'TRF-' . time(),
                'amount' => $amount,
                'description' => "Transfer from {$fromBudget->budget_name}: {$reason}",
                'transaction_date' => now(),
                'status' => 'POSTED',
                'created_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now()
            ]);
            
            // Update budget amounts
            $fromBudget->allocated_amount -= $amount;
            $fromBudget->calculateBudgetMetrics();
            
            $toBudget->allocated_amount += $amount;
            $toBudget->calculateBudgetMetrics();
            
            DB::commit();
            
            Log::info('Budget transfer completed', [
                'from_budget' => $fromBudgetId,
                'to_budget' => $toBudgetId,
                'amount' => $amount
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget transfer failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}