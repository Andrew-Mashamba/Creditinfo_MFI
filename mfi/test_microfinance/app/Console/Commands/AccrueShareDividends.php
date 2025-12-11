<?php

namespace App\Console\Commands;

use App\Services\ShareDividendAccrualService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AccrueShareDividends extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shares:accrue-dividends
                            {--date= : Process date (YYYY-MM-DD format, defaults to yesterday)}
                            {--report : Show accrual summary report only without processing}
                            {--year= : Year for report (requires --report)}
                            {--month= : Month for report (requires --report)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and accrue daily dividends on share capital accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new ShareDividendAccrualService();

        // If report mode, show summary
        if ($this->option('report')) {
            $this->showAccrualReport($service);
            return 0;
        }

        // Parse date - default to yesterday (dividend accrued on previous day's closing balance)
        $dateOption = $this->option('date');
        $processDate = $dateOption ? Carbon::parse($dateOption) : Carbon::yesterday();

        $this->info("========================================");
        $this->info("   SHARE DIVIDEND ACCRUAL");
        $this->info("   Processing Date: {$processDate->format('Y-m-d')}");
        $this->info("========================================");
        $this->newLine();

        // Show dividend calculation method
        $this->info("Dividend Calculation Method: Actual/365 (or 366 for leap years)");
        $this->info("Formula: Daily Dividend = Share Value x (Annual Rate / Days in Year)");
        $this->info("Annual Dividend Rate: " . number_format($service->getDividendRate(), 2) . "%");
        $this->newLine();

        // Run accrual
        $this->info("Processing daily dividend accrual...");
        $this->newLine();

        try {
            $result = $service->processDailyAccrual($processDate);

            if ($result['status'] === 'skipped') {
                $this->warn("Accrual skipped: {$result['message']}");
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
                    ['Annual Dividend Rate', number_format($result['annual_rate'], 2) . '%'],
                    ['---', '---'],
                    ['Total Share Registers', $result['total_registers']],
                    ['Registers Processed', $result['processed_count']],
                    ['Registers Skipped', $result['skipped_count']],
                    ['---', '---'],
                    ['Total Dividend Accrued', 'TZS ' . number_format($result['total_dividend_accrued'], 2)],
                    ['Errors', $result['errors_count']],
                ]
            );

            // Show errors if any
            if ($result['errors_count'] > 0) {
                $this->newLine();
                $this->warn("Errors encountered:");
                foreach ($result['errors'] as $error) {
                    $this->error("  Member {$error['member_number']}: {$error['error']}");
                }
            }

            $this->newLine();
            $this->info("Daily share dividend accrual completed successfully!");

            return 0;

        } catch (\Exception $e) {
            $this->error("Accrual failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show accrual summary report
     */
    protected function showAccrualReport(ShareDividendAccrualService $service): void
    {
        $year = $this->option('year') ?? Carbon::now()->year;
        $month = $this->option('month');

        $this->info("========================================");
        $this->info("   SHARE DIVIDEND ACCRUAL REPORT");
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
                ['Total Members with Accruals', $summary['total_members']],
                ['Total Accrual Records', $summary['total_accruals']],
                ['Total Dividend Accrued', 'TZS ' . number_format($summary['total_dividend_accrued'], 2)],
                ['Average Daily Dividend', 'TZS ' . number_format($summary['average_daily_dividend'], 2)],
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
                if ($monthSummary['total_dividend_accrued'] > 0) {
                    $monthlyData[] = [
                        Carbon::create($year, $m, 1)->format('F'),
                        $monthSummary['total_members'],
                        $monthSummary['total_accruals'],
                        'TZS ' . number_format($monthSummary['total_dividend_accrued'], 2),
                    ];
                }
            }

            if (!empty($monthlyData)) {
                $this->table(
                    ['Month', 'Members', 'Accruals', 'Dividend Accrued'],
                    $monthlyData
                );
            } else {
                $this->info("No accrual data found for {$year}");
            }
        }

        // Show current share register summary
        $this->newLine();
        $this->info("Current Share Register Summary:");

        $shareStats = \DB::table('share_registers')
            ->where('status', 'ACTIVE')
            ->where('current_share_balance', '>', 0)
            ->selectRaw('
                COUNT(*) as total_members,
                SUM(current_share_balance) as total_shares,
                SUM(current_share_balance * current_price) as total_share_value,
                SUM(accumulated_dividends) as total_accumulated_dividends,
                SUM(total_pending_dividends) as total_pending_dividends,
                SUM(total_paid_dividends) as total_paid_dividends
            ')
            ->first();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Active Members with Shares', $shareStats->total_members ?? 0],
                ['Total Shares Held', number_format($shareStats->total_shares ?? 0)],
                ['Total Share Value', 'TZS ' . number_format($shareStats->total_share_value ?? 0, 2)],
                ['---', '---'],
                ['Accumulated Dividends', 'TZS ' . number_format($shareStats->total_accumulated_dividends ?? 0, 2)],
                ['Pending Dividends', 'TZS ' . number_format($shareStats->total_pending_dividends ?? 0, 2)],
                ['Paid Dividends', 'TZS ' . number_format($shareStats->total_paid_dividends ?? 0, 2)],
            ]
        );

        // Calculate projected annual dividends
        $projectedAnnual = ($shareStats->total_share_value ?? 0) * ($service->getDividendRate() / 100);
        $this->newLine();
        $this->info("Projected Annual Dividend at {$service->getDividendRate()}%: TZS " . number_format($projectedAnnual, 2));
    }
}
