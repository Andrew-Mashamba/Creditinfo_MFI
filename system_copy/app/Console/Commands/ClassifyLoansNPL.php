<?php

namespace App\Console\Commands;

use App\Services\LoanClassificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClassifyLoansNPL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:classify-npl
                            {--date= : Process date (YYYY-MM-DD format, defaults to today)}
                            {--report : Show classification report only without processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classify loans based on NPL categories (CURRENT, WATCH, SUBSTANDARD, DOUBTFUL, LOSS) and calculate provisions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new LoanClassificationService();

        // If report mode, just show the current classification status
        if ($this->option('report')) {
            $this->showClassificationReport($service);
            return 0;
        }

        // Parse date
        $dateOption = $this->option('date');
        $processDate = $dateOption ? Carbon::parse($dateOption) : Carbon::today();

        $this->info("========================================");
        $this->info("   LOAN NPL CLASSIFICATION");
        $this->info("   Processing Date: {$processDate->format('Y-m-d')}");
        $this->info("========================================");
        $this->newLine();

        // Show classification criteria
        $this->info("Classification Criteria:");
        $this->table(
            ['Classification', 'Days in Arrears', 'Provision Rate'],
            [
                ['CURRENT', '0 - 30', '0%'],
                ['WATCH', '31 - 90', '10%'],
                ['SUBSTANDARD', '91 - 180', '30%'],
                ['DOUBTFUL', '181 - 365', '50%'],
                ['LOSS', '> 365', '100%'],
            ]
        );
        $this->newLine();

        // Run classification
        $this->info("Running classification...");
        $result = $service->runDailyClassification($processDate);

        if ($result['status'] === 'error') {
            $this->error("Classification failed: " . $result['message']);
            return 1;
        }

        // Display results
        $stats = $result['statistics'];

        $this->newLine();
        $this->info("========================================");
        $this->info("   CLASSIFICATION RESULTS");
        $this->info("========================================");

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Active Loans', $stats['total_loans']],
                ['Loans Classified', $stats['classified']],
                ['Upgraded (Improved)', $stats['upgraded']],
                ['Downgraded (Deteriorated)', $stats['downgraded']],
                ['Unchanged', $stats['unchanged']],
            ]
        );

        $this->newLine();
        $this->info("Classification Distribution:");
        $this->table(
            ['Classification', 'Count', 'Provision Rate'],
            [
                ['CURRENT', $stats['by_classification']['CURRENT'], '0%'],
                ['WATCH', $stats['by_classification']['WATCH'], '10%'],
                ['SUBSTANDARD', $stats['by_classification']['SUBSTANDARD'], '30%'],
                ['DOUBTFUL', $stats['by_classification']['DOUBTFUL'], '50%'],
                ['LOSS', $stats['by_classification']['LOSS'], '100%'],
            ]
        );

        $this->newLine();
        $this->info("Provision Summary:");
        $this->table(
            ['Metric', 'Amount (TZS)'],
            [
                ['Total Provision Required', number_format($stats['total_provision'], 2)],
                ['Net Provision Change', number_format($stats['provision_change'], 2)],
            ]
        );

        $this->newLine();
        $this->info("Classification completed successfully!");

        return 0;
    }

    /**
     * Show classification report
     */
    protected function showClassificationReport(LoanClassificationService $service): void
    {
        $this->info("========================================");
        $this->info("   LOAN CLASSIFICATION REPORT");
        $this->info("   As of: " . Carbon::now()->format('Y-m-d H:i:s'));
        $this->info("========================================");
        $this->newLine();

        $report = $service->getClassificationReport();

        $rows = [];
        $totalLoans = 0;
        $totalPrincipal = 0;
        $totalProvision = 0;

        foreach ($report as $classification => $data) {
            $rows[] = [
                $classification,
                $data['loan_count'],
                number_format($data['total_principal'], 2),
                $data['provision_rate'] . '%',
                number_format($data['total_provision'], 2),
                $data['avg_days_in_arrears'] . ' days'
            ];

            $totalLoans += $data['loan_count'];
            $totalPrincipal += $data['total_principal'];
            $totalProvision += $data['total_provision'];
        }

        // Add totals row
        $rows[] = ['---', '---', '---', '---', '---', '---'];
        $rows[] = ['TOTAL', $totalLoans, number_format($totalPrincipal, 2), '-', number_format($totalProvision, 2), '-'];

        $this->table(
            ['Classification', 'Loans', 'Principal (TZS)', 'Rate', 'Provision (TZS)', 'Avg Arrears'],
            $rows
        );

        // Calculate NPL ratio
        $nplLoans = ($report['SUBSTANDARD']['loan_count'] ?? 0) +
                    ($report['DOUBTFUL']['loan_count'] ?? 0) +
                    ($report['LOSS']['loan_count'] ?? 0);

        $nplRatio = $totalLoans > 0 ? round(($nplLoans / $totalLoans) * 100, 2) : 0;

        $this->newLine();
        $this->info("Key Metrics:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Active Loans', $totalLoans],
                ['NPL Count (Substandard+Doubtful+Loss)', $nplLoans],
                ['NPL Ratio', $nplRatio . '%'],
                ['Total Provision Required', 'TZS ' . number_format($totalProvision, 2)],
                ['Coverage Ratio', $totalPrincipal > 0 ? round(($totalProvision / $totalPrincipal) * 100, 2) . '%' : '0%'],
            ]
        );
    }
}
