<?php

namespace App\Console\Commands;

use App\Services\SavingsInterestAccrualService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AccrueSavingsInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savings:accrue-interest
                            {--date= : Process date (YYYY-MM-DD format, defaults to yesterday)}
                            {--report : Show accrual summary report only without processing}
                            {--year= : Year for report (requires --report)}
                            {--month= : Month for report (requires --report)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and accrue daily interest on savings and deposit accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new SavingsInterestAccrualService();

        // If report mode, show summary
        if ($this->option('report')) {
            $this->showAccrualReport($service);
            return 0;
        }

        // Parse date - default to yesterday (interest accrued on previous day's closing balance)
        $dateOption = $this->option('date');
        $processDate = $dateOption ? Carbon::parse($dateOption) : Carbon::yesterday();

        $this->info("========================================");
        $this->info("   SAVINGS & DEPOSITS INTEREST ACCRUAL");
        $this->info("   Processing Date: {$processDate->format('Y-m-d')}");
        $this->info("========================================");
        $this->newLine();

        // Show interest calculation method
        $this->info("Interest Calculation Method: Actual/365 (or 366 for leap years)");
        $this->info("Formula: Daily Interest = Closing Balance × (Annual Rate / Days in Year)");
        $this->info("Accounts: Savings (2000) + Fixed Deposits (3000)");
        $this->newLine();

        // Run accrual
        $this->info("Processing daily interest accrual...");
        $this->newLine();

        try {
            $result = $service->processDailyAccrual($processDate);

            if ($result['status'] === 'skipped') {
                $this->warn("⚠️  Accrual skipped: {$result['message']}");
                $this->info("Date: {$result['date']}");
                $this->info("Existing records: {$result['existing_records']}");
                return 0;
            }

            // Display results
            $this->newLine();
            $this->info("========================================");
            $this->info("   ACCRUAL RESULTS");
            $this->info("========================================");

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Processing Date', $result['date']],
                    ['Total Accounts', $result['total_accounts']],
                    ['Accounts Processed', $result['processed_accounts']],
                    ['---', '---'],
                    ['Savings Accounts', $result['savings_accounts'] ?? 0],
                    ['Savings Interest', 'TZS ' . number_format($result['savings_interest'] ?? 0, 2)],
                    ['Deposit Accounts', $result['deposit_accounts'] ?? 0],
                    ['Deposit Interest', 'TZS ' . number_format($result['deposit_interest'] ?? 0, 2)],
                    ['---', '---'],
                    ['Total Interest Accrued', 'TZS ' . number_format($result['total_interest_accrued'], 2)],
                    ['Errors', $result['errors_count']],
                ]
            );

            // Show errors if any
            if ($result['errors_count'] > 0) {
                $this->newLine();
                $this->warn("Errors encountered:");
                foreach ($result['errors'] as $error) {
                    $this->error("  Account {$error['account_number']}: {$error['error']}");
                }
            }

            $this->newLine();
            $this->info("✅ Daily interest accrual completed successfully!");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Accrual failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show accrual summary report
     */
    protected function showAccrualReport(SavingsInterestAccrualService $service): void
    {
        $year = $this->option('year') ?? Carbon::now()->year;
        $month = $this->option('month');

        $this->info("========================================");
        $this->info("   SAVINGS INTEREST ACCRUAL REPORT");
        $periodLabel = $month ? "Year: {$year}, Month: {$month}" : "Year: {$year}";
        $this->info("   {$periodLabel}");
        $this->info("========================================");
        $this->newLine();

        $summary = $service->getAccrualSummary($year, $month);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Year', $summary['year']],
                ['Month', $summary['month'] ?? 'All'],
                ['Total Accounts with Accruals', $summary['total_accounts']],
                ['Total Interest Accrued', 'TZS ' . number_format($summary['total_interest_accrued'], 2)],
                ['Average Daily Interest', 'TZS ' . number_format($summary['average_daily_interest'], 2)],
                ['Period Start', $summary['period_start'] ?? 'N/A'],
                ['Period End', $summary['period_end'] ?? 'N/A'],
            ]
        );

        // Show breakdown by month if viewing full year
        if (!$month) {
            $this->newLine();
            $this->info("Monthly Breakdown:");

            $monthlyData = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthSummary = $service->getAccrualSummary($year, $m);
                if ($monthSummary['total_interest_accrued'] > 0) {
                    $monthlyData[] = [
                        Carbon::create($year, $m, 1)->format('F'),
                        $monthSummary['total_accounts'],
                        'TZS ' . number_format($monthSummary['total_interest_accrued'], 2),
                    ];
                }
            }

            if (!empty($monthlyData)) {
                $this->table(
                    ['Month', 'Accounts', 'Interest Accrued'],
                    $monthlyData
                );
            } else {
                $this->info("No accrual data found for {$year}");
            }
        }
    }
}
