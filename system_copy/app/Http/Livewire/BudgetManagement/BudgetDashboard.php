<?php

namespace App\Http\Livewire\BudgetManagement;

use Livewire\Component;
use App\Models\BudgetManagement;
use App\Models\BudgetAlert;
use App\Services\BudgetMonitoringService;
use Illuminate\Support\Facades\DB;

class BudgetDashboard extends Component
{
    public $summary = [];
    public $budgetsNeedingAttention = [];
    public $recentAlerts = [];
    public $topSpenders = [];
    public $budgetUtilizationData = [];
    public $selectedPeriod = 'current_month';
    public $showAlertDetails = false;
    public $selectedAlert = null;

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $service = new BudgetMonitoringService();
        
        // Get summary statistics
        $this->summary = $service->getBudgetSummary();
        
        // Get budgets needing attention
        $this->budgetsNeedingAttention = $service->getBudgetsNeedingAttention();
        
        // Get recent alerts
        $this->recentAlerts = BudgetAlert::with('budget')
            ->unacknowledged()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get top spenders (highest utilization)
        $this->topSpenders = BudgetManagement::with('expenseAccount')
            ->active()
            ->orderByRaw('CAST(utilization_percentage as DECIMAL) DESC NULLS LAST')
            ->limit(5)
            ->get();
        
        // Prepare chart data for budget utilization
        $this->prepareBudgetUtilizationData();
    }

    private function prepareBudgetUtilizationData()
    {
        $budgets = BudgetManagement::active()
            ->selectRaw('budget_name, 
                        CAST(allocated_amount as DECIMAL) as allocated_amount, 
                        CAST(spent_amount as DECIMAL) as spent_amount, 
                        CAST(utilization_percentage as DECIMAL) as utilization_percentage')
            ->orderByRaw('CAST(utilization_percentage as DECIMAL) DESC')
            ->limit(10)
            ->get();
        
        $this->budgetUtilizationData = [
            'labels' => $budgets->pluck('budget_name')->toArray(),
            'allocated' => $budgets->pluck('allocated_amount')->toArray(),
            'spent' => $budgets->pluck('spent_amount')->toArray(),
            'utilization' => $budgets->pluck('utilization_percentage')->toArray()
        ];
    }

    public function acknowledgeAlert($alertId)
    {
        $alert = BudgetAlert::find($alertId);
        if ($alert) {
            $alert->acknowledge();
            $this->loadDashboardData();
            session()->flash('message', 'Alert acknowledged successfully.');
        }
    }

    public function viewAlertDetails($alertId)
    {
        $this->selectedAlert = BudgetAlert::with('budget')->find($alertId);
        $this->showAlertDetails = true;
    }

    public function closeAlertDetails()
    {
        $this->showAlertDetails = false;
        $this->selectedAlert = null;
    }

    public function recalculateAllBudgets()
    {
        $service = new BudgetMonitoringService();
        $count = $service->recalculateAllBudgets();
        
        $this->loadDashboardData();
        session()->flash('message', "Successfully recalculated {$count} budgets.");
    }

    public function getUtilizationColor($percentage)
    {
        if ($percentage > 100) return 'red';
        if ($percentage >= 90) return 'orange';
        if ($percentage >= 80) return 'yellow';
        if ($percentage >= 50) return 'blue';
        return 'green';
    }

    public function getHealthIcon($status)
    {
        $icons = [
            'OVERSPENT' => '❌',
            'CRITICAL' => '⚠️',
            'WARNING' => '⚠️',
            'NORMAL' => '✓',
            'HEALTHY' => '✅'
        ];
        
        return $icons[$status] ?? '•';
    }

    public function render()
    {
        return view('livewire.budget-management.budget-dashboard');
    }
}