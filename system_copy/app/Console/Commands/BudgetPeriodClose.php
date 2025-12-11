<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BudgetManagement;
use App\Models\BudgetVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetPeriodClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:period-close 
                            {--type=monthly : The type of close (monthly or quarterly)} 
                            {--period= : Specific period to close (e.g., "2025-01" for monthly, "2025-Q1" for quarterly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform period close for budgets and create snapshot versions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $period = $this->option('period') ?? $this->getCurrentPeriod($type);
        
        $this->info("Starting {$type} budget close for period: {$period}");
        
        // Get all active budgets
        $budgets = BudgetManagement::where('status', 'ACTIVE')->get();
        
        if ($budgets->isEmpty()) {
            $this->warn('No active budgets found to close.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$budgets->count()} active budgets to process...");
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($budgets as $budget) {
            try {
                DB::beginTransaction();
                
                // Recalculate metrics before creating version
                $budget->calculateBudgetMetrics();
                
                // Create period close version
                $versionNumber = BudgetVersion::where('budget_id', $budget->id)->count() + 1;
                $versionType = $type === 'monthly' ? 'MONTHLY_CLOSE' : 'QUARTERLY_CLOSE';
                
                BudgetVersion::create([
                    'budget_id' => $budget->id,
                    'version_number' => $versionNumber,
                    'version_name' => "Version {$versionNumber} - {$period} Close",
                    'version_type' => $versionType,
                    'allocated_amount' => $budget->allocated_amount,
                    'spent_amount' => $budget->spent_amount,
                    'committed_amount' => $budget->committed_amount,
                    'effective_from' => now(),
                    'created_by' => 1, // System generated
                    'revision_reason' => ucfirst($type) . " period close for {$period}",
                    'change_summary' => json_encode([
                        'period' => $period,
                        'period_type' => $type,
                        'utilization_percentage' => $budget->utilization_percentage,
                        'variance_amount' => $budget->variance_amount,
                        'available_amount' => $budget->available_amount,
                        'closed_at' => now()->toDateTimeString(),
                        'snapshot_data' => [
                            'spent_amount' => $budget->spent_amount,
                            'committed_amount' => $budget->committed_amount,
                            'allocated_amount' => $budget->allocated_amount,
                            'utilization' => $budget->utilization_percentage . '%'
                        ]
                    ]),
                    'is_active' => false // Period close versions are snapshots, not active versions
                ]);
                
                // Update last period close timestamp
                $budget->last_period_close = now();
                $budget->last_closed_period = $period;
                $budget->saveQuietly();
                
                DB::commit();
                
                $successCount++;
                $this->info("✓ Closed budget: {$budget->budget_name}");
                
                Log::info('Budget period close completed', [
                    'budget_id' => $budget->id,
                    'budget_name' => $budget->budget_name,
                    'period' => $period,
                    'type' => $type,
                    'version_number' => $versionNumber
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                $failCount++;
                
                $this->error("✗ Failed to close budget: {$budget->budget_name} - {$e->getMessage()}");
                
                Log::error('Budget period close failed', [
                    'budget_id' => $budget->id,
                    'budget_name' => $budget->budget_name,
                    'period' => $period,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->newLine();
        $this->info("Period close completed!");
        $this->info("Success: {$successCount} budgets");
        
        if ($failCount > 0) {
            $this->warn("Failed: {$failCount} budgets");
        }
        
        // Generate summary report
        $this->generateCloseSummary($period, $type, $successCount, $failCount);
        
        return Command::SUCCESS;
    }
    
    /**
     * Get current period based on type
     */
    private function getCurrentPeriod($type)
    {
        if ($type === 'monthly') {
            return now()->format('Y-m');
        } else {
            $quarter = ceil(now()->month / 3);
            return now()->format('Y') . '-Q' . $quarter;
        }
    }
    
    /**
     * Generate close summary report
     */
    private function generateCloseSummary($period, $type, $successCount, $failCount)
    {
        $summary = [
            'period' => $period,
            'type' => $type,
            'closed_at' => now()->toDateTimeString(),
            'total_budgets' => $successCount + $failCount,
            'successful_closes' => $successCount,
            'failed_closes' => $failCount
        ];
        
        // Store summary in database or log
        Log::info('Budget period close summary', $summary);
        
        // You could also create a BudgetPeriodCloseSummary model to store this
        // BudgetPeriodCloseSummary::create($summary);
    }
}