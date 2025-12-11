<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NBCDailyReconciliationService;
use Illuminate\Support\Facades\Log;

class NBCDailyReconciliationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nbc:daily-reconciliation
                            {--force : Force execution even if not scheduled time}
                            {--date= : Specific date to reconcile (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute daily NBC bank statement reconciliation - fetches transactions and reconciles with cashbook';

    /**
     * NBC Daily Reconciliation Service
     *
     * @var NBCDailyReconciliationService
     */
    protected $reconciliationService;

    /**
     * Create a new command instance.
     *
     * @param NBCDailyReconciliationService $reconciliationService
     */
    public function __construct(NBCDailyReconciliationService $reconciliationService)
    {
        parent::__construct();
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║   NBC SACCOS - Daily Bank Reconciliation Job              ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $startTime = now();
        $this->info("Started at: {$startTime->toDateTimeString()}");
        $this->newLine();

        try {
            // Log command start
            Log::channel('daily')->info('NBC Daily Reconciliation Command Started', [
                'command' => $this->signature,
                'started_at' => $startTime->toDateTimeString(),
                'forced' => $this->option('force'),
                'specific_date' => $this->option('date')
            ]);

            // Show progress
            $this->info('📊 Step 1: Creating reconciliation session...');
            $this->info('💰 Step 2: Fetching NBC bank statement...');
            $this->info('📝 Step 3: Inserting bank transactions...');
            $this->info('🔄 Step 4: Running reconciliation...');
            $this->info('✅ Step 5: Updating results...');
            $this->newLine();

            // Execute reconciliation
            $result = $this->reconciliationService->executeDailyReconciliation();

            // Display results
            if ($result['success']) {
                $this->displaySuccessResults($result);

                Log::channel('daily')->info('NBC Daily Reconciliation Completed Successfully', $result);

                return Command::SUCCESS;
            } else {
                $this->displayFailureResults($result);

                Log::channel('daily')->error('NBC Daily Reconciliation Failed', $result);

                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('╔════════════════════════════════════════════════════════════╗');
            $this->error('║   RECONCILIATION FAILED                                    ║');
            $this->error('╚════════════════════════════════════════════════════════════╝');
            $this->newLine();
            $this->error("Error: {$e->getMessage()}");
            $this->newLine();
            $this->error("Stack trace:");
            $this->error($e->getTraceAsString());

            Log::channel('daily')->error('NBC Daily Reconciliation Command Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Display success results
     *
     * @param array $result
     * @return void
     */
    protected function displaySuccessResults(array $result): void
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║   RECONCILIATION COMPLETED SUCCESSFULLY                    ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Create results table
        $this->table(
            ['Metric', 'Value'],
            [
                ['Session ID', $result['session_id']],
                ['Total Transactions', $result['total_transactions']],
                ['Matched Transactions', "<fg=green>{$result['matched_count']}</>"],
                ['Unmatched Transactions', $result['unmatched_count'] > 0
                    ? "<fg=yellow>{$result['unmatched_count']}</>"
                    : "<fg=green>{$result['unmatched_count']}</>"],
                ['Execution Time', "{$result['execution_time_ms']} ms"],
                ['Reconciliation Rate', $this->calculateReconciliationRate($result) . '%']
            ]
        );

        $this->newLine();

        // Display warnings if any unmatched
        if ($result['unmatched_count'] > 0) {
            $this->warn("⚠️  {$result['unmatched_count']} unmatched transaction(s) require attention");
            $this->warn("   View details in Bank Reconciliation Manager");
        } else {
            $this->info("✅  All transactions matched successfully!");
        }

        $this->newLine();
        $this->info("Completed at: " . now()->toDateTimeString());
    }

    /**
     * Display failure results
     *
     * @param array $result
     * @return void
     */
    protected function displayFailureResults(array $result): void
    {
        $this->error('╔════════════════════════════════════════════════════════════╗');
        $this->error('║   RECONCILIATION FAILED                                    ║');
        $this->error('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->error("Error: {$result['error']}");
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Status', '<fg=red>FAILED</>'],
                ['Execution Time', "{$result['execution_time_ms']} ms"],
                ['Failed At', now()->toDateTimeString()]
            ]
        );

        $this->newLine();
        $this->error("Please check the logs for more details:");
        $this->error("  tail -f storage/logs/daily-" . now()->format('Y-m-d') . ".log");
    }

    /**
     * Calculate reconciliation rate
     *
     * @param array $result
     * @return string
     */
    protected function calculateReconciliationRate(array $result): string
    {
        if ($result['total_transactions'] == 0) {
            return '0.00';
        }

        $rate = ($result['matched_count'] / $result['total_transactions']) * 100;
        return number_format($rate, 2);
    }
}
