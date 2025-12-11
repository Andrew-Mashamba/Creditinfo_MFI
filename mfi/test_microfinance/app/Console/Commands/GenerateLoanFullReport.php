<?php

namespace App\Console\Commands;

use App\Services\LoanFullReportService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateLoanFullReport extends Command
{
    protected $signature = 'loans:generate-full-report
                            {--format=xlsx : Output format (xlsx or csv)}
                            {--branch= : Filter by branch ID}
                            {--status= : Filter by loan status}
                            {--product= : Filter by product type}
                            {--classification= : Filter by classification}
                            {--date-from= : Filter loans from date (YYYY-MM-DD)}
                            {--date-to= : Filter loans to date (YYYY-MM-DD)}
                            {--all : Include all loans regardless of status}
                            {--stats : Show report statistics only}';

    protected $description = 'Generate comprehensive loan full report with all loan details';

    protected $reportService;

    public function __construct(LoanFullReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle()
    {
        $this->info('🚀 Starting Loan Full Report Generation...');
        $this->newLine();

        try {
            // Prepare filters
            $filters = $this->prepareFilters();

            // Display active filters
            $this->displayFilters($filters);

            // If stats only, show statistics and exit
            if ($this->option('stats')) {
                $this->showStatistics($filters);
                return 0;
            }

            // Generate report
            $format = $this->option('format');
            $this->info("Generating report in {$format} format...");

            if ($format === 'csv') {
                $result = $this->reportService->exportAsCSV($filters);
            } else {
                $result = $this->reportService->generateFullLoanReport($filters);
            }

            if ($result['success']) {
                $this->newLine();
                $this->info('✅ Report generated successfully!');
                $this->newLine();
                $this->line('📄 File Name: ' . $result['file_name']);
                $this->line('📂 File Path: ' . $result['file_path']);
                $this->line('📊 Records: ' . number_format($result['record_count']));
                $this->newLine();

                // Show statistics
                if ($this->confirm('Would you like to see report statistics?', true)) {
                    $this->showStatistics($filters);
                }

                return 0;
            } else {
                $this->error('❌ Failed to generate report');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function prepareFilters()
    {
        $filters = [
            'report_date' => Carbon::now()
        ];

        if ($this->option('branch')) {
            $filters['branch_id'] = $this->option('branch');
        }

        if ($this->option('status')) {
            $filters['loan_status'] = $this->option('status');
        }

        if ($this->option('product')) {
            $filters['product_type'] = $this->option('product');
        }

        if ($this->option('classification')) {
            $filters['classification'] = $this->option('classification');
        }

        if ($this->option('date-from')) {
            $filters['date_from'] = $this->option('date-from');
        }

        if ($this->option('date-to')) {
            $filters['date_to'] = $this->option('date-to');
        }

        if ($this->option('all')) {
            $filters['include_all'] = true;
        }

        return $filters;
    }

    private function displayFilters($filters)
    {
        $this->info('📋 Active Filters:');
        $this->line('─────────────────────────────────────');

        $this->line('Report Date: ' . $filters['report_date']->format('Y-m-d'));

        if (isset($filters['branch_id'])) {
            $this->line('Branch ID: ' . $filters['branch_id']);
        }

        if (isset($filters['loan_status'])) {
            $this->line('Loan Status: ' . $filters['loan_status']);
        }

        if (isset($filters['product_type'])) {
            $this->line('Product Type: ' . $filters['product_type']);
        }

        if (isset($filters['classification'])) {
            $this->line('Classification: ' . $filters['classification']);
        }

        if (isset($filters['date_from'])) {
            $this->line('Date From: ' . $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->line('Date To: ' . $filters['date_to']);
        }

        if (isset($filters['include_all']) && $filters['include_all']) {
            $this->line('Include All: Yes');
        } else {
            $this->line('Include All: No (Active loans only)');
        }

        $this->newLine();
    }

    private function showStatistics($filters)
    {
        $this->info('📊 Generating Report Statistics...');
        $this->newLine();

        $stats = $this->reportService->getReportStatistics($filters);

        // Overall statistics
        $this->info('Overall Statistics:');
        $this->line('─────────────────────────────────────');
        $this->line('Total Loans: ' . number_format($stats['total_loans']));
        $this->line('Total Disbursed: TZS ' . number_format($stats['total_disbursed'], 2));
        $this->line('Total Outstanding: TZS ' . number_format($stats['total_outstanding'], 2));
        $this->line('Total Principal Outstanding: TZS ' . number_format($stats['total_principal_outstanding'], 2));
        $this->line('Total Arrears: TZS ' . number_format($stats['total_arrears'], 2));
        $this->newLine();

        // By product type
        if (!empty($stats['by_product']) && count($stats['by_product']) > 0) {
            $this->info('Breakdown by Product Type:');
            $this->line('─────────────────────────────────────');

            foreach ($stats['by_product'] as $product => $data) {
                $this->line('');
                $this->line("Product: {$product}");
                $this->line('  Count: ' . number_format($data['count']));
                $this->line('  Disbursed: TZS ' . number_format($data['total_disbursed'], 2));
                $this->line('  Outstanding: TZS ' . number_format($data['total_outstanding'], 2));
            }
            $this->newLine();
        }

        // By classification
        if (!empty($stats['by_classification']) && count($stats['by_classification']) > 0) {
            $this->info('Breakdown by Classification:');
            $this->line('─────────────────────────────────────');

            foreach ($stats['by_classification'] as $classification => $data) {
                $this->line('');
                $this->line("Classification: {$classification}");
                $this->line('  Count: ' . number_format($data['count']));
                $this->line('  Outstanding: TZS ' . number_format($data['total_outstanding'], 2));
            }
            $this->newLine();
        }
    }
}
