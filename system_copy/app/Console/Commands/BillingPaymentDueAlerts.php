<?php

namespace App\Console\Commands;

use App\Services\BillingPaymentDueAlertsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Billing Payment Due Alerts Command
 *
 * Sends payment reminders for:
 * - Bills approaching due date
 * - Overdue bills
 * - Trade receivables approaching due date
 * - Overdue trade receivables
 *
 * Scheduled to run daily at 08:00 AM
 */
class BillingPaymentDueAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:payment-due-alerts
                            {--dry-run : Show what would be sent without actually sending}
                            {--summary : Show summary of pending payments only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders for upcoming and overdue bills';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("========================================");
        $this->info("   BILLING PAYMENT DUE ALERTS");
        $this->info("   " . now()->format('Y-m-d H:i:s'));
        $this->info("========================================");
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $showSummaryOnly = $this->option('summary');

        if ($isDryRun) {
            $this->warn("MODE: DRY RUN (no notifications will be sent)");
            $this->newLine();
        }

        try {
            $service = new BillingPaymentDueAlertsService();

            // Show summary if requested
            if ($showSummaryOnly) {
                $this->showSummary($service);
                return 0;
            }

            // Process alerts
            $this->info("Processing payment due alerts...");
            $this->newLine();

            $results = $service->processAllAlerts($isDryRun);

            // Display results
            $this->displayResults($results);

            // Log completion
            Log::info('[BILLING_PAYMENT_DUE_ALERTS] Command completed', [
                'dry_run' => $isDryRun,
                'totals' => $results['totals']
            ]);

            $this->newLine();
            if ($isDryRun) {
                $this->warn("DRY RUN complete. No notifications were sent.");
                $this->info("Run without --dry-run to send actual notifications.");
            } else {
                $this->info("Payment due alerts processing completed successfully!");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed: " . $e->getMessage());
            Log::error('[BILLING_PAYMENT_DUE_ALERTS] Command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Display processing results
     */
    protected function displayResults(array $results): void
    {
        $this->info("========================================");
        $this->info("   RESULTS");
        $this->info("========================================");
        $this->newLine();

        // Bills section
        $this->info("MEMBER BILLS:");
        $this->table(
            ['Category', 'Found', 'Sent', 'Skipped', 'Errors'],
            [
                [
                    'Upcoming (due soon)',
                    $results['bills']['upcoming']['count'],
                    $results['bills']['upcoming']['sent'],
                    $results['bills']['upcoming']['skipped'],
                    $results['bills']['upcoming']['errors']
                ],
                [
                    'Overdue',
                    $results['bills']['overdue']['count'],
                    $results['bills']['overdue']['sent'],
                    $results['bills']['overdue']['skipped'],
                    $results['bills']['overdue']['errors']
                ]
            ]
        );

        $this->newLine();

        // Trade receivables section
        $this->info("TRADE RECEIVABLES:");
        $this->table(
            ['Category', 'Found', 'Sent', 'Skipped', 'Errors'],
            [
                [
                    'Upcoming (due soon)',
                    $results['trade_receivables']['upcoming']['count'],
                    $results['trade_receivables']['upcoming']['sent'],
                    $results['trade_receivables']['upcoming']['skipped'],
                    $results['trade_receivables']['upcoming']['errors']
                ],
                [
                    'Overdue',
                    $results['trade_receivables']['overdue']['count'],
                    $results['trade_receivables']['overdue']['sent'],
                    $results['trade_receivables']['overdue']['skipped'],
                    $results['trade_receivables']['overdue']['errors']
                ]
            ]
        );

        $this->newLine();

        // Totals
        $this->info("TOTALS:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Found', $results['totals']['total_found']],
                ['Total Sent', $results['totals']['total_sent']],
                ['Total Skipped', $results['totals']['total_skipped']],
                ['Total Errors', $results['totals']['total_errors']]
            ]
        );

        // Show errors if any
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error("Errors encountered:");
            foreach ($results['errors'] as $error) {
                $this->error("  [{$error['type']}] {$error['error']}");
            }
        }
    }

    /**
     * Show summary of pending payments
     */
    protected function showSummary(BillingPaymentDueAlertsService $service): void
    {
        $this->info("PENDING PAYMENTS SUMMARY:");
        $this->newLine();

        $summary = $service->getPendingPaymentsSummary();

        $this->info("MEMBER BILLS:");
        $this->table(
            ['Status', 'Count', 'Amount (TZS)'],
            [
                ['Pending (Current)', $summary['bills']['pending_count'], number_format($summary['bills']['pending_amount'], 2)],
                ['Overdue', $summary['bills']['overdue_count'], number_format($summary['bills']['overdue_amount'], 2)]
            ]
        );

        $this->newLine();

        $this->info("TRADE RECEIVABLES:");
        $this->table(
            ['Status', 'Count', 'Amount (TZS)'],
            [
                ['Pending (Current)', $summary['trade_receivables']['pending_count'], number_format($summary['trade_receivables']['pending_amount'], 2)],
                ['Overdue', $summary['trade_receivables']['overdue_count'], number_format($summary['trade_receivables']['overdue_amount'], 2)]
            ]
        );

        $totalPending = $summary['bills']['pending_amount'] + $summary['trade_receivables']['pending_amount'];
        $totalOverdue = $summary['bills']['overdue_amount'] + $summary['trade_receivables']['overdue_amount'];

        $this->newLine();
        $this->info("GRAND TOTALS:");
        $this->info("  Total Pending: TZS " . number_format($totalPending, 2));
        $this->warn("  Total Overdue: TZS " . number_format($totalOverdue, 2));
    }
}
