<?php

namespace App\Listeners;

use App\Models\Expense;
use App\Models\BudgetAllocation;
use App\Services\BudgetMonitoringService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateBudgetAllocationOnExpenseApproval
{
    /**
     * Handle expense status changes
     */
    public function handle($event)
    {
        $expense = $event->expense ?? $event->model ?? null;
        
        if (!$expense instanceof Expense) {
            return;
        }
        
        // Only process approved or paid expenses
        if (!in_array($expense->status, ['APPROVED', 'PAID'])) {
            return;
        }
        
        // Update allocation if linked
        if ($expense->budget_allocation_id) {
            $this->updateAllocation($expense);
        } 
        // Otherwise, try to find and update based on budget item and month
        elseif ($expense->budget_item_id && $expense->expense_month) {
            $this->findAndUpdateAllocation($expense);
        }
    }
    
    /**
     * Update the linked allocation
     */
    private function updateAllocation($expense)
    {
        $allocation = BudgetAllocation::find($expense->budget_allocation_id);
        
        if (!$allocation) {
            Log::warning('Budget allocation not found for expense', [
                'expense_id' => $expense->id,
                'allocation_id' => $expense->budget_allocation_id
            ]);
            return;
        }
        
        // Update utilized amount
        $allocation->utilized_amount += $expense->amount;
        $allocation->available_amount = max(0, $allocation->available_amount - $expense->amount);
        
        // Calculate new utilization percentage
        $totalAvailable = $allocation->allocated_amount + $allocation->rollover_amount + 
                         $allocation->advance_amount + $allocation->supplementary_amount + 
                         $allocation->transferred_in - $allocation->transferred_out;
        
        if ($totalAvailable > 0) {
            $allocation->utilization_percentage = ($allocation->utilized_amount / $totalAvailable) * 100;
        }
        
        $allocation->save();
        
        Log::info('Budget allocation updated for expense', [
            'expense_id' => $expense->id,
            'allocation_id' => $allocation->id,
            'new_utilized' => $allocation->utilized_amount,
            'new_available' => $allocation->available_amount
        ]);
        
        // Check for alerts
        $this->checkBudgetAlerts($allocation);
    }
    
    /**
     * Find and update allocation based on budget item and month
     */
    private function findAndUpdateAllocation($expense)
    {
        $expenseMonth = Carbon::parse($expense->expense_month);
        
        $allocation = BudgetAllocation::where('budget_id', $expense->budget_item_id)
            ->where('year', $expenseMonth->year)
            ->where('period', $expenseMonth->month)
            ->first();
        
        if ($allocation) {
            // Link the allocation to the expense
            $expense->update(['budget_allocation_id' => $allocation->id]);
            
            // Update the allocation
            $this->updateAllocation($expense);
        } else {
            Log::warning('No budget allocation found for expense', [
                'expense_id' => $expense->id,
                'budget_item_id' => $expense->budget_item_id,
                'expense_month' => $expense->expense_month
            ]);
        }
    }
    
    /**
     * Check for budget alerts
     */
    private function checkBudgetAlerts($allocation)
    {
        try {
            $monitoringService = new BudgetMonitoringService();
            
            // Check utilization threshold
            if ($allocation->utilization_percentage >= 80 && $allocation->utilization_percentage < 100) {
                $monitoringService->createBudgetAlert(
                    $allocation->budget_id,
                    'WARNING',
                    'High Budget Utilization',
                    "Budget utilization has reached {$allocation->utilization_percentage}% for " . 
                    Carbon::create($allocation->year, $allocation->period)->format('F Y'),
                    [
                        'allocation_id' => $allocation->id,
                        'utilization' => $allocation->utilization_percentage,
                        'available' => $allocation->available_amount
                    ]
                );
            } elseif ($allocation->utilization_percentage >= 100) {
                $monitoringService->createBudgetAlert(
                    $allocation->budget_id,
                    'CRITICAL',
                    'Budget Exceeded',
                    "Budget has been exceeded for " . 
                    Carbon::create($allocation->year, $allocation->period)->format('F Y'),
                    [
                        'allocation_id' => $allocation->id,
                        'utilization' => $allocation->utilization_percentage,
                        'overrun' => $allocation->utilized_amount - ($allocation->allocated_amount + 
                                     $allocation->rollover_amount + $allocation->advance_amount + 
                                     $allocation->supplementary_amount)
                    ]
                );
            }
            
            // Check if month is ending and budget is underutilized
            if (now()->day >= 25 && $allocation->utilization_percentage < 50) {
                $monitoringService->createBudgetAlert(
                    $allocation->budget_id,
                    'INFO',
                    'Low Budget Utilization',
                    "Budget utilization is only {$allocation->utilization_percentage}% with month ending soon",
                    [
                        'allocation_id' => $allocation->id,
                        'available_for_rollover' => $allocation->available_amount
                    ]
                );
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to check budget alerts', [
                'allocation_id' => $allocation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}