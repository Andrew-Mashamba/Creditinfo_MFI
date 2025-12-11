<?php

namespace App\Services;

use App\Models\BudgetManagement;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BudgetCheckingService
{
    /**
     * Check if an expense would exceed the budget for a given account and month
     */
    public function checkBudgetForExpense($accountId, $amount, $expenseMonth = null)
    {
        $expenseMonth = $expenseMonth ? Carbon::parse($expenseMonth) : Carbon::now();
        $currentMonth = $expenseMonth->format('Y-m');
        
        // Find the budget item for this expense account
        $budgetItem = BudgetManagement::where('expense_account_id', $accountId)
            ->where('approval_status', 'APPROVED')
            ->where('status', 'ACTIVE')
            ->where(function ($query) use ($expenseMonth) {
                $query->where('start_date', '<=', $expenseMonth)
                      ->where('end_date', '>=', $expenseMonth);
            })
            ->first();

        if (!$budgetItem) {
            // Fallback: check account-specific budgets in budget_accounts for this year
            $budgetAccount = DB::table('budget_accounts')
                ->where('account_id', $accountId)
                ->when(DB::getSchemaBuilder()->hasColumn('budget_accounts', 'year'), function ($q) use ($expenseMonth) {
                    $q->where('year', $expenseMonth->year);
                })
                ->first();

            if ($budgetAccount) {
                $annualAmount = (float) ($budgetAccount->amount ?? 0);
                $monthlyBudget = $annualAmount / 12;

                $monthlySpent = Expense::where('account_id', $accountId)
                    ->where('status', 'APPROVED')
                    ->whereYear('created_at', $expenseMonth->year)
                    ->whereMonth('created_at', $expenseMonth->month)
                    ->sum('amount');

                $remainingBudget = $monthlyBudget - $monthlySpent;
                $utilizationPercentage = $monthlyBudget > 0 ? ($monthlySpent / $monthlyBudget) * 100 : 0;

                $wouldExceed = ($monthlySpent + (float) $amount) > $monthlyBudget;
                $overBudgetAmount = $wouldExceed ? ($monthlySpent + (float) $amount) - $monthlyBudget : 0;

                $budgetStatus = 'WITHIN_BUDGET';
                if ($wouldExceed) {
                    $budgetStatus = $overBudgetAmount > ($monthlyBudget * 0.1) ? 'BUDGET_EXCEEDED' : 'OVER_BUDGET';
                }

                return [
                    'has_budget' => true,
                    'budget_item_id' => null, // no BudgetManagement item, but we still have a budget
                    'budget_status' => $budgetStatus,
                    'monthly_budget' => $monthlyBudget,
                    'monthly_spent' => $monthlySpent,
                    'remaining_budget' => $remainingBudget,
                    'utilization_percentage' => $utilizationPercentage,
                    'would_exceed' => $wouldExceed,
                    'over_budget_amount' => $overBudgetAmount,
                    'new_utilization_percentage' => $monthlyBudget > 0 ? (($monthlySpent + (float) $amount) / $monthlyBudget) * 100 : 0,
                    'message' => $this->getBudgetMessage($budgetStatus, $overBudgetAmount, $monthlyBudget)
                ];
            }

            return [
                'has_budget' => false,
                'message' => 'No approved budget found for this expense account.',
                'budget_status' => 'NO_BUDGET',
                'monthly_budget' => 0,
                'monthly_spent' => 0,
                'remaining_budget' => 0,
                'utilization_percentage' => 0,
                'would_exceed' => false,
                'over_budget_amount' => 0,
                'new_utilization_percentage' => 0,
                'budget_item_id' => null
            ];
        }

        // Get monthly budget (annual budget / 12)
        $monthlyBudget = $budgetItem->revenue / 12; // revenue field stores annual budget
        
        // Calculate total spent for this month
        $monthlySpent = Expense::where('account_id', $accountId)
            ->where('status', 'APPROVED')
            ->whereYear('created_at', $expenseMonth->year)
            ->whereMonth('created_at', $expenseMonth->month)
            ->sum('amount');

        // Calculate remaining budget
        $remainingBudget = $monthlyBudget - $monthlySpent;
        $utilizationPercentage = $monthlyBudget > 0 ? ($monthlySpent / $monthlyBudget) * 100 : 0;

        // Check if this expense would exceed the budget
        $wouldExceed = ($monthlySpent + $amount) > $monthlyBudget;
        $overBudgetAmount = $wouldExceed ? ($monthlySpent + $amount) - $monthlyBudget : 0;

        // Determine budget status
        $budgetStatus = 'WITHIN_BUDGET';
        if ($wouldExceed) {
            $budgetStatus = $overBudgetAmount > ($monthlyBudget * 0.1) ? 'BUDGET_EXCEEDED' : 'OVER_BUDGET';
        }

        return [
            'has_budget' => true,
            'budget_item_id' => $budgetItem->id,
            'budget_status' => $budgetStatus,
            'monthly_budget' => $monthlyBudget,
            'monthly_spent' => $monthlySpent,
            'remaining_budget' => $remainingBudget,
            'utilization_percentage' => $utilizationPercentage,
            'would_exceed' => $wouldExceed,
            'over_budget_amount' => $overBudgetAmount,
            'new_utilization_percentage' => $monthlyBudget > 0 ? (($monthlySpent + $amount) / $monthlyBudget) * 100 : 0,
            'message' => $this->getBudgetMessage($budgetStatus, $overBudgetAmount, $monthlyBudget)
        ];
    }

    /**
     * Get previous months' unused budget for an account
     */
    public function getPreviousMonthsUnusedBudget($accountId, $currentMonth = null)
    {
        $currentMonth = $currentMonth ? Carbon::parse($currentMonth) : Carbon::now();
        
        $budgetItem = BudgetManagement::where('expense_account_id', $accountId)
            ->where('approval_status', 'APPROVED')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$budgetItem) {
            return 0;
        }

        $monthlyBudget = $budgetItem->revenue / 12;
        $unusedBudget = 0;

        // Check previous 3 months for unused budget
        for ($i = 1; $i <= 3; $i++) {
            $previousMonth = $currentMonth->copy()->subMonths($i);
            
            // Only check if the budget period covers this month
            if ($previousMonth >= $budgetItem->start_date && $previousMonth <= $budgetItem->end_date) {
                $monthlySpent = Expense::where('account_id', $accountId)
                    ->where('status', 'APPROVED')
                    ->whereYear('created_at', $previousMonth->year)
                    ->whereMonth('created_at', $previousMonth->month)
                    ->sum('amount');

                $monthlyUnused = max(0, $monthlyBudget - $monthlySpent);
                $unusedBudget += $monthlyUnused;
            }
        }

        return $unusedBudget;
    }

    /**
     * Check if using previous months' budget would cover the overrun
     */
    public function canUsePreviousMonthsBudget($accountId, $overBudgetAmount, $currentMonth = null)
    {
        $unusedBudget = $this->getPreviousMonthsUnusedBudget($accountId, $currentMonth);
        return $unusedBudget >= $overBudgetAmount;
    }

    /**
     * Get budget message based on status
     */
    private function getBudgetMessage($status, $overAmount, $monthlyBudget)
    {
        switch ($status) {
            case 'WITHIN_BUDGET':
                return 'Expense is within budget limits.';
            
            case 'OVER_BUDGET':
                return "Expense exceeds monthly budget by " . number_format($overAmount, 2) . " TZS. Consider using previous months' unused budget.";
            
            case 'BUDGET_EXCEEDED':
                return "Expense significantly exceeds monthly budget by " . number_format($overAmount, 2) . " TZS. Additional approval required.";
            
            case 'NO_BUDGET':
                return 'No budget found for this expense account.';
            
            default:
                return 'Budget status unknown.';
        }
    }

    /**
     * Log budget check for audit trail
     */
    public function logBudgetCheck($accountId, $amount, $result, $userId = null)
    {
        Log::channel('budget_management')->info('Budget check performed', [
            'account_id' => $accountId,
            'expense_amount' => $amount,
            'budget_status' => $result['budget_status'],
            'monthly_budget' => $result['monthly_budget'] ?? 0,
            'monthly_spent' => $result['monthly_spent'] ?? 0,
            'would_exceed' => $result['would_exceed'] ?? false,
            'user_id' => $userId ?? auth()->id(),
            'timestamp' => now()
        ]);
    }
} 