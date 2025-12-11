<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BudgetManagement;
use App\Services\BudgetMonitoringService;

class TestBudgetMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:test-monitoring {budget_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test budget monitoring functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new BudgetMonitoringService();
        
        // Get or select a budget
        $budgetId = $this->argument('budget_id');
        
        if (!$budgetId) {
            $budgets = BudgetManagement::active()->get();
            
            if ($budgets->isEmpty()) {
                $this->error('No active budgets found.');
                return 1;
            }
            
            $this->info('Active Budgets:');
            foreach ($budgets as $budget) {
                $this->line("ID: {$budget->id} - {$budget->budget_name}");
            }
            
            $budgetId = $this->ask('Enter the budget ID to test');
        }
        
        $budget = BudgetManagement::find($budgetId);
        
        if (!$budget) {
            $this->error("Budget with ID {$budgetId} not found.");
            return 1;
        }
        
        $this->info("Testing Budget: {$budget->budget_name}");
        $this->line(str_repeat('-', 50));
        
        // Calculate initial metrics
        $this->info("Calculating budget metrics...");
        $budget->calculateBudgetMetrics();
        
        // Display current status
        $this->table(
            ['Metric', 'Value'],
            [
                ['Allocated Amount', number_format($budget->allocated_amount, 2)],
                ['Spent Amount', number_format($budget->spent_amount, 2)],
                ['Committed Amount', number_format($budget->committed_amount, 2)],
                ['Available Amount', number_format($budget->available_amount, 2)],
                ['Utilization %', $budget->utilization_percentage . '%'],
                ['Health Status', $budget->health_status],
                ['Status Color', $budget->status_color]
            ]
        );
        
        // Simulate adding expenses
        if ($this->confirm('Would you like to simulate adding expenses?')) {
            $amount = $this->ask('Enter expense amount', 1000);
            $description = $this->ask('Enter description', 'Test expense');
            
            $this->info("Recording expense of {$amount}...");
            $transaction = $service->recordExpense($budgetId, $amount, $description, 'TEST-' . time());
            
            $this->info("Transaction created: ID {$transaction->id}");
            
            // Refresh budget and show new metrics
            $budget->refresh();
            $this->info("\nUpdated Budget Metrics:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Spent Amount', number_format($budget->spent_amount, 2)],
                    ['Available Amount', number_format($budget->available_amount, 2)],
                    ['Utilization %', $budget->utilization_percentage . '%'],
                    ['Health Status', $budget->health_status]
                ]
            );
        }
        
        // Check for alerts
        $alertStatus = $budget->checkAlertStatus();
        if ($alertStatus) {
            $this->warn("Alert Status: {$alertStatus}");
            
            if ($this->confirm('Would you like to create an alert?')) {
                $alert = $service->checkAndCreateAlerts($budget);
                if ($alert) {
                    $this->info("Alert created: {$alert->message}");
                }
            }
        } else {
            $this->info("No alerts needed at current utilization level.");
        }
        
        // Generate variance report
        if ($this->confirm('Would you like to see variance analysis?')) {
            $variance = $service->calculateVariance($budget, 'all');
            
            $this->info("\nVariance Analysis:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Period', $variance['period']],
                    ['Expected Spending', number_format($variance['expected_spending'], 2)],
                    ['Actual Spending', number_format($variance['actual_spending'], 2)],
                    ['Variance', number_format($variance['variance'], 2)],
                    ['Variance %', $variance['variance_percentage'] . '%']
                ]
            );
        }
        
        $this->info("\nBudget monitoring test completed successfully!");
        
        return 0;
    }
}