<?php

namespace App\Console\Commands;

use App\Services\LoanArrearsManagementService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessLoanRepayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:process-repayments
                            {--date= : Process for a specific date (Y-m-d format)}
                            {--dry-run : Run without sending notifications or posting entries}
                            {--post-gl : Post consolidated penalty accruals to GL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process due repayments, send notifications, calculate penalties, and update loan classifications';

    protected $service;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $dateOption = $this->option('date');
        $isDryRun = $this->option('dry-run');
        $postGL = $this->option('post-gl');

        $processDate = $dateOption ? Carbon::parse($dateOption) : Carbon::today();

        $this->info('===========================================');
        $this->info('   Loan Repayment Processing - Starting');
        $this->info('===========================================');
        $this->info("Process Date: {$processDate->format('Y-m-d')}");
        $this->info("Dry Run: " . ($isDryRun ? 'Yes' : 'No'));
        $this->info("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->newLine();

        Log::info('===========================================');
        Log::info('Loan Repayment Processing Command Started');
        Log::info("Date: {$processDate->format('Y-m-d')}, Dry Run: " . ($isDryRun ? 'Yes' : 'No'));
        Log::info('===========================================');

        try {
            $this->service = new LoanArrearsManagementService();

            if ($postGL) {
                // Only post GL entries
                $this->info('Posting consolidated penalty accruals to GL...');
                $result = $this->service->postPenaltyAccrualsToGL($processDate);

                if ($result['status'] === 'success') {
                    $this->info("GL posting completed: TZS " . number_format($result['total_penalty'], 2) .
                        " for {$result['loans_count']} loans");
                    $this->info("Reference: " . ($result['reference'] ?? 'N/A'));
                } else {
                    $this->warn($result['message'] ?? 'GL posting skipped or failed');
                }
            } else {
                // Full processing
                $result = $this->service->processAllLoans($processDate);

                $this->displayResults($result);

                // Also post GL entries after processing
                if ($result['status'] === 'success' && !$isDryRun) {
                    $this->newLine();
                    $this->info('Posting penalty accruals to GL...');
                    $glResult = $this->service->postPenaltyAccrualsToGL($processDate);

                    if ($glResult['status'] === 'success') {
                        $this->info("GL posting completed: TZS " . number_format($glResult['total_penalty'], 2));
                    }
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('===========================================');
            $this->info('   Processing Completed Successfully');
            $this->info("   Duration: {$duration} seconds");
            $this->info('===========================================');

            Log::info("Loan Repayment Processing completed in {$duration} seconds");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Processing failed: ' . $e->getMessage());
            Log::error('Loan Repayment Processing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }

    protected function displayResults(array $result): void
    {
        $stats = $result['statistics'] ?? [];

        $this->newLine();
        $this->info('Processing Summary:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Status', $result['status'] ?? 'unknown'],
                ['Date Processed', $result['date'] ?? '-'],
                ['Total Loans Processed', $stats['total_loans_processed'] ?? 0],
                ['Loans in Arrears', $stats['loans_in_arrears'] ?? 0],
                ['Upcoming Reminders Sent', $stats['upcoming_reminders_sent'] ?? 0],
                ['Due Day Reminders Sent', $stats['due_day_reminders_sent'] ?? 0],
                ['Arrears Reminders Sent', $stats['arrears_reminders_sent'] ?? 0],
                ['Penalties Calculated', $stats['penalties_calculated'] ?? 0],
                ['Total Penalty Amount', 'TZS ' . number_format($stats['total_penalty_amount'] ?? 0, 2)],
                ['Classifications Updated', $stats['classifications_updated'] ?? 0],
                ['Legal Documents Generated', $stats['legal_documents_generated'] ?? 0],
                ['Errors', $stats['errors'] ?? 0],
            ]
        );

        if (($stats['errors'] ?? 0) > 0) {
            $this->warn('Some errors occurred during processing. Check the logs for details.');
        }
    }
}
