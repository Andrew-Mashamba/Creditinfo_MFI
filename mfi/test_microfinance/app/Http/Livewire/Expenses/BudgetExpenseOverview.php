<?php

namespace App\Http\Livewire\Expenses;

use Livewire\Component;
use App\Models\BudgetManagement;
use App\Models\Expense;
use App\Services\BudgetCheckingService;
use Illuminate\Support\Facades\DB;

class BudgetExpenseOverview extends Component
{
    public $selectedMonth;
    public $budgetOverview = [];
    public $expenseOverview = [];

    public function mount()
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function updatedSelectedMonth()
    {
        $this->loadOverview();
    }

    public function loadOverview()
    {
        $month = \Carbon\Carbon::parse($this->selectedMonth . '-01');
        
        // Get all approved budgets
        $budgets = BudgetManagement::where('approval_status', 'APPROVED')
            ->where('status', 'ACTIVE')
            ->where('start_date', '<=', $month)
            ->where('end_date', '>=', $month)
            ->with('expenseAccount')
            ->get();

        $this->budgetOverview = [];
        $this->expenseOverview = [];

        foreach ($budgets as $budget) {
            $monthlyBudget = $budget->revenue / 12; // Annual budget divided by 12
            
            // Get expenses for this month
            $monthlyExpenses = Expense::where('account_id', $budget->expense_account_id)
                ->where('status', 'APPROVED')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');

            $utilizationPercentage = $monthlyBudget > 0 ? ($monthlyExpenses / $monthlyBudget) * 100 : 0;
            $remainingBudget = $monthlyBudget - $monthlyExpenses;

            $this->budgetOverview[] = [
                'budget_id' => $budget->id,
                'account_name' => $budget->expenseAccount->account_name ?? 'Unknown',
                'account_number' => $budget->expenseAccount->account_number ?? 'N/A',
                'monthly_budget' => $monthlyBudget,
                'monthly_expenses' => $monthlyExpenses,
                'remaining_budget' => $remainingBudget,
                'utilization_percentage' => $utilizationPercentage,
                'status' => $this->getBudgetStatus($utilizationPercentage, $remainingBudget)
            ];
        }

        // Get overall statistics
        $totalBudget = collect($this->budgetOverview)->sum('monthly_budget');
        $totalExpenses = collect($this->budgetOverview)->sum('monthly_expenses');
        $totalRemaining = collect($this->budgetOverview)->sum('remaining_budget');
        $overallUtilization = $totalBudget > 0 ? ($totalExpenses / $totalBudget) * 100 : 0;

        $this->expenseOverview = [
            'total_budget' => $totalBudget,
            'total_expenses' => $totalExpenses,
            'total_remaining' => $totalRemaining,
            'utilization_percentage' => $overallUtilization,
            'budget_count' => count($this->budgetOverview),
            'over_budget_count' => collect($this->budgetOverview)->where('status', 'OVER_BUDGET')->count(),
            'within_budget_count' => collect($this->budgetOverview)->where('status', 'WITHIN_BUDGET')->count()
        ];
    }

    private function getBudgetStatus($utilizationPercentage, $remainingBudget)
    {
        if ($remainingBudget < 0) {
            return 'OVER_BUDGET';
        } elseif ($utilizationPercentage >= 90) {
            return 'NEAR_LIMIT';
        } else {
            return 'WITHIN_BUDGET';
        }
    }

    public function render()
    {
        $this->loadOverview();
        
        return view('livewire.expenses.budget-expense-overview', [
            'budgetOverview' => $this->budgetOverview,
            'expenseOverview' => $this->expenseOverview
        ]);
    }
} 