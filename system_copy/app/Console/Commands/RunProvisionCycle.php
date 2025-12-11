<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Livewire\Accounting\LoanLossReserveManager;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RunProvisionCycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provision:cycle 
                            {frequency=MONTHLY : Frequency of the cycle (MONTHLY, QUARTERLY, SEMI_ANNUAL, ANNUAL)}
                            {--auto-approve : Automatically approve and execute adjustments}
                            {--dry-run : Run calculation without making actual adjustments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the complete provision lifecycle: CALCULATE → COMPARE → ADJUST → MONITOR → REPORT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $frequency = strtoupper($this->argument('frequency'));
        $autoApprove = $this->option('auto-approve');
        $dryRun = $this->option('dry-run');
        
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║           LOAN LOSS PROVISION CYCLE EXECUTION               ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        $this->info("Frequency: {$frequency}");
        $this->info("Auto-Approve: " . ($autoApprove ? 'Yes' : 'No'));
        $this->info("Dry Run: " . ($dryRun ? 'Yes' : 'No'));
        $this->newLine();
        
        // Check for active cycle
        $activeCycle = DB::table('provision_cycles')
            ->where('status', '!=', 'COMPLETED')
            ->where('status', '!=', 'FAILED')
            ->first();
        
        if ($activeCycle) {
            $this->warn("Active cycle found: {$activeCycle->cycle_id} (Status: {$activeCycle->status})");
            
            if (!$this->confirm('Do you want to continue with the existing cycle?')) {
                return 0;
            }
            
            $this->continueExistingCycle($activeCycle, $autoApprove, $dryRun);
        } else {
            $this->startNewCycle($frequency, $autoApprove, $dryRun);
        }
        
        return 0;
    }
    
    private function startNewCycle($frequency, $autoApprove, $dryRun)
    {
        $this->info('Starting new provision cycle...');
        $this->newLine();
        
        $manager = new LoanLossReserveManager();
        
        // Step 1: CALCULATE
        $this->info('Step 1: CALCULATE - Computing ECL based on loan portfolio');
        $this->output->write('Calculating... ');
        
        if ($dryRun) {
            $this->info('[DRY RUN - No cycle created]');
            $manager->loadDashboardData();
            $this->displayCalculationResults($manager);
            return;
        }
        
        $cycleId = $manager->startProvisionCycle($frequency);
        $this->info('✓');
        
        $cycle = DB::table('provision_cycles')->where('id', $cycleId)->first();
        
        $this->info("Cycle ID: {$cycle->cycle_id}");
        $this->info("Portfolio Value: " . number_format($cycle->portfolio_value, 2) . " TZS");
        $this->info("Calculated ECL: " . number_format($cycle->calculated_ecl, 2) . " TZS");
        $this->newLine();
        
        // Step 2: COMPARE (automatic)
        $this->info('Step 2: COMPARE - Comparing required vs current reserve');
        $cycle = DB::table('provision_cycles')->where('id', $cycleId)->first();
        
        $this->info("Current Reserve: " . number_format($cycle->current_reserve, 2) . " TZS");
        $this->info("Required Reserve: " . number_format($cycle->required_reserve, 2) . " TZS");
        
        if ($cycle->provision_gap > 0) {
            $this->warn("Provision Gap: " . number_format($cycle->provision_gap, 2) . " TZS (Under-provisioned)");
        } elseif ($cycle->provision_gap < 0) {
            $this->info("Provision Surplus: " . number_format(abs($cycle->provision_gap), 2) . " TZS (Over-provisioned)");
        } else {
            $this->info("Provision Status: Adequate (No adjustment needed)");
        }
        $this->newLine();
        
        // Step 3: ADJUST
        if (abs($cycle->provision_gap) > 0.01) {
            $this->info('Step 3: ADJUST - Booking provision adjustment');
            
            if (!$autoApprove) {
                if (!$this->confirm('Do you want to execute the adjustment?')) {
                    $this->warn('Adjustment skipped. Cycle paused at COMPARED status.');
                    return;
                }
            }
            
            $result = $manager->executeProvisionAdjustment($cycleId);
            
            if ($result) {
                $this->info('✓ Adjustment executed successfully');
            } else {
                $this->error('✗ Adjustment failed');
                return;
            }
        } else {
            $this->info('Step 3: ADJUST - No adjustment needed');
        }
        $this->newLine();
        
        // Steps 4-6 are automatic after adjustment
        $this->info('Step 4: MONITOR - Updating coverage ratios and metrics');
        $this->info('Step 5: REPORT - Generating board and regulatory reports');
        $this->info('Step 6: REPEAT - Scheduling next cycle');
        $this->newLine();
        
        // Final status
        $cycle = DB::table('provision_cycles')->where('id', $cycleId)->first();
        
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    CYCLE COMPLETED                          ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info("Final Status: {$cycle->status}");
        $this->info("Coverage Ratio: " . number_format($cycle->coverage_ratio, 2) . "%");
        $this->info("NPL Ratio: " . number_format($cycle->npl_ratio, 2) . "%");
        
        // Display aging breakdown
        $this->displayAgingBreakdown($cycle);
    }
    
    private function continueExistingCycle($cycle, $autoApprove, $dryRun)
    {
        $this->info("Continuing cycle: {$cycle->cycle_id}");
        $this->newLine();
        
        $manager = new LoanLossReserveManager();
        
        switch ($cycle->status) {
            case 'COMPARED':
                if (abs($cycle->provision_gap) > 0.01) {
                    $this->info('Cycle is at COMPARED status. Ready for adjustment.');
                    
                    if (!$autoApprove && !$dryRun) {
                        if (!$this->confirm('Execute provision adjustment?')) {
                            return;
                        }
                    }
                    
                    if (!$dryRun) {
                        $manager->executeProvisionAdjustment($cycle->id);
                        $this->info('✓ Adjustment executed');
                    }
                }
                break;
                
            case 'ADJUSTED':
                $this->info('Cycle is at ADJUSTED status. Proceeding to monitoring.');
                // Monitoring and reporting will be automatic
                break;
                
            default:
                $this->info("Cycle is at {$cycle->status} status.");
                break;
        }
    }
    
    private function displayCalculationResults($manager)
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Portfolio Value', number_format($manager->loanPortfolioValue, 2) . ' TZS'],
                ['Required Reserve', number_format($manager->requiredReserve, 2) . ' TZS'],
                ['Current Reserve', number_format($manager->currentReserveBalance, 2) . ' TZS'],
                ['Provision Gap', number_format($manager->provisionGap, 2) . ' TZS'],
            ]
        );
    }
    
    private function displayAgingBreakdown($cycle)
    {
        if (!$cycle->aging_analysis) {
            return;
        }
        
        $aging = json_decode($cycle->aging_analysis, true);
        
        $this->newLine();
        $this->info('Aging Analysis Breakdown:');
        
        $data = [];
        foreach ($aging as $category => $details) {
            if (!empty($details)) {
                $data[] = [
                    ucfirst($category),
                    $details['count'] ?? 0,
                    number_format($details['amount'] ?? 0, 2),
                    ($details['provision_rate'] ?? 0) . '%',
                    number_format($details['required_provision'] ?? 0, 2)
                ];
            }
        }
        
        $this->table(
            ['Category', 'Count', 'Amount (TZS)', 'Rate', 'Provision (TZS)'],
            $data
        );
    }
}