<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScheduledReportDataService;
use App\Services\MonthlyReportDataService;
use App\Services\QuarterlyAnnualReportDataService;
use Exception;

class TestScheduledReports extends Command
{
    protected $signature = 'reports:test
                            {--category=all : Category to test (daily|weekly|monthly|quarterly|annual|all)}
                            {--report= : Specific report to test}
                            {--show-data : Show full report data}';

    protected $description = 'Test all scheduled report queries and validate data returned';

    protected $dailyWeeklyService;
    protected $monthlyService;
    protected $quarterlyAnnualService;

    protected $results = [
        'passed' => 0,
        'failed' => 0,
        'warnings' => 0,
        'errors' => [],
    ];

    public function handle()
    {
        $this->info('===========================================');
        $this->info('  Scheduled Reports Query Tester');
        $this->info('===========================================');
        $this->info('Started at: ' . now()->format('Y-m-d H:i:s'));
        $this->newLine();

        // Initialize services
        $this->dailyWeeklyService = new ScheduledReportDataService();
        $this->monthlyService = new MonthlyReportDataService();
        $this->quarterlyAnnualService = new QuarterlyAnnualReportDataService();

        $category = $this->option('category');
        $specificReport = $this->option('report');

        if ($specificReport) {
            $this->testSpecificReport($specificReport);
        } else {
            if (in_array($category, ['all', 'daily'])) {
                $this->testDailyReports();
            }
            if (in_array($category, ['all', 'weekly'])) {
                $this->testWeeklyReports();
            }
            if (in_array($category, ['all', 'monthly'])) {
                $this->testMonthlyReports();
            }
            if (in_array($category, ['all', 'quarterly'])) {
                $this->testQuarterlyReports();
            }
            if (in_array($category, ['all', 'annual'])) {
                $this->testAnnualReports();
            }
        }

        $this->displaySummary();

        return $this->results['failed'] > 0 ? 1 : 0;
    }

    protected function testDailyReports(): void
    {
        $this->info('--- DAILY REPORTS ---');
        $this->newLine();

        $reports = [
            'Daily Operations Report' => 'generateDailyOperationsReport',
            'Daily Arrears Report' => 'generateDailyArrearsReport',
            'Daily Cash Position Report' => 'generateDailyCashPositionReport',
            'Daily Disbursements Report' => 'generateDailyDisbursementsReport',
            'Daily Collections Report' => 'generateDailyCollectionsReport',
            'Daily Member Activity Report' => 'generateDailyMemberActivityReport',
            'Daily Loan Officer Portfolio' => 'generateDailyLoanOfficerPortfolioReport',
            'Daily Recovery Action Report' => 'generateDailyRecoveryActionReport',
            'Daily GL Summary Report' => 'generateDailyGLSummaryReport',
            'Daily Large Transactions Report' => 'generateDailyLargeTransactionsReport',
        ];

        foreach ($reports as $name => $method) {
            $this->testReport($name, $this->dailyWeeklyService, $method);
        }
    }

    protected function testWeeklyReports(): void
    {
        $this->info('--- WEEKLY REPORTS ---');
        $this->newLine();

        $reports = [
            'Weekly Executive Summary' => 'generateWeeklyExecutiveSummaryReport',
            'Weekly Arrears Analysis' => 'generateWeeklyArrearsReport',
            'Weekly Credit Committee Report' => 'generateWeeklyCreditCommitteeReport',
            'Weekly Collections Performance' => 'generateWeeklyCollectionsPerformanceReport',
            'Weekly Risk Alerts Report' => 'generateWeeklyRiskAlertsReport',
            'Weekly Savings Mobilization' => 'generateWeeklySavingsMobilizationReport',
            'Weekly Loan Officer Targets' => 'generateWeeklyLoanOfficerTargetsReport',
            'Weekly Suspense Account Report' => 'generateWeeklySuspenseAccountReport',
        ];

        foreach ($reports as $name => $method) {
            $this->testReport($name, $this->dailyWeeklyService, $method);
        }
    }

    protected function testMonthlyReports(): void
    {
        $this->info('--- MONTHLY CREDIT REPORTS ---');
        $this->newLine();

        $creditReports = [
            'Monthly Loan Portfolio' => 'generateMonthlyLoanPortfolioReport',
            'Monthly Delinquency Report' => 'generateMonthlyDelinquencyReport',
            'Monthly NPL Classification' => 'generateMonthlyNPLClassificationReport',
            'Monthly Provision Report' => 'generateMonthlyProvisionReport',
            'Monthly Loan Officer Performance' => 'generateMonthlyLoanOfficerPerformanceReport',
            'Monthly Product Analysis' => 'generateMonthlyProductAnalysisReport',
        ];

        foreach ($creditReports as $name => $method) {
            $this->testReport($name, $this->monthlyService, $method);
        }

        $this->info('--- MONTHLY FINANCE REPORTS ---');
        $this->newLine();

        $financeReports = [
            'Monthly Trial Balance' => 'generateMonthlyTrialBalanceReport',
            'Monthly Income Statement' => 'generateMonthlyIncomeStatementReport',
            'Monthly Balance Sheet' => 'generateMonthlyBalanceSheetReport',
            'Monthly Cash Flow Report' => 'generateMonthlyCashFlowReport',
            'Monthly Budget Variance' => 'generateMonthlyBudgetVarianceReport',
            'Monthly Bank Reconciliation' => 'generateMonthlyBankReconciliationReport',
            'Monthly Interest Accrual' => 'generateMonthlyInterestAccrualReport',
            'Monthly Liquidity Report' => 'generateMonthlyLiquidityReport',
        ];

        foreach ($financeReports as $name => $method) {
            $this->testReport($name, $this->monthlyService, $method);
        }

        $this->info('--- MONTHLY MEMBERSHIP REPORTS ---');
        $this->newLine();

        $membershipReports = [
            'Monthly Membership Report' => 'generateMonthlyMembershipReport',
            'Monthly Savings & Deposits' => 'generateMonthlySavingsDepositsReport',
            'Monthly Share Capital' => 'generateMonthlyShareCapitalReport',
            'Monthly Mandatory Savings' => 'generateMonthlyMandatorySavingsReport',
        ];

        foreach ($membershipReports as $name => $method) {
            $this->testReport($name, $this->quarterlyAnnualService, $method);
        }

        $this->info('--- MONTHLY RISK REPORTS ---');
        $this->newLine();

        $riskReports = [
            'Monthly Risk Assessment' => 'generateMonthlyRiskAssessmentReport',
            'Monthly Concentration Risk' => 'generateMonthlyConcentrationRiskReport',
            'Monthly AML Compliance' => 'generateMonthlyAMLComplianceReport',
            'Monthly Write-off Report' => 'generateMonthlyWriteOffReport',
        ];

        foreach ($riskReports as $name => $method) {
            $this->testReport($name, $this->quarterlyAnnualService, $method);
        }
    }

    protected function testQuarterlyReports(): void
    {
        $this->info('--- QUARTERLY REGULATORY REPORTS ---');
        $this->newLine();

        $regulatoryReports = [
            'Quarterly BOT Regulatory' => 'generateQuarterlyBOTRegulatoryReport',
            'Quarterly Capital Adequacy' => 'generateQuarterlyCapitalAdequacyReport',
            'Quarterly Liquid Assets' => 'generateQuarterlyLiquidAssetsReport',
            'Quarterly Large Exposures' => 'generateQuarterlyLargeExposuresReport',
            'Quarterly NPL Return' => 'generateQuarterlyNPLReturnReport',
            'Quarterly TCDC Supervision' => 'generateQuarterlyTCDCSupervisionReport',
            'Quarterly TCDC Membership' => 'generateQuarterlyTCDCMembershipReport',
        ];

        foreach ($regulatoryReports as $name => $method) {
            $this->testReport($name, $this->quarterlyAnnualService, $method);
        }

        $this->info('--- QUARTERLY GOVERNANCE REPORTS ---');
        $this->newLine();

        $governanceReports = [
            'Quarterly Board Pack' => 'generateQuarterlyBoardPackReport',
            'Quarterly Supervisory Report' => 'generateQuarterlySupervisoryReport',
            'Quarterly Audit Report' => 'generateQuarterlyAuditReport',
            'Quarterly Risk Report' => 'generateQuarterlyRiskReport',
            'Quarterly Financial Statements' => 'generateQuarterlyFinancialStatementsReport',
        ];

        foreach ($governanceReports as $name => $method) {
            $this->testReport($name, $this->quarterlyAnnualService, $method);
        }
    }

    protected function testAnnualReports(): void
    {
        $this->info('--- ANNUAL REPORTS ---');
        $this->newLine();

        $reports = [
            'Annual Financial Statements' => 'generateAnnualFinancialStatementsReport',
            'Annual AGM Pack' => 'generateAnnualAGMPackReport',
            'Annual BOT Return' => 'generateAnnualBOTReturnReport',
            'Annual TCDC Return' => 'generateAnnualTCDCReturnReport',
        ];

        foreach ($reports as $name => $method) {
            $this->testReport($name, $this->quarterlyAnnualService, $method);
        }
    }

    protected function testReport(string $name, $service, string $method): void
    {
        $this->line("Testing: {$name}");

        try {
            $startTime = microtime(true);
            $data = $service->$method();
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            // Validate the response
            $validation = $this->validateReportData($name, $data);

            if ($validation['status'] === 'passed') {
                $this->info("  ✓ PASSED ({$executionTime}ms)");
                $this->results['passed']++;
            } elseif ($validation['status'] === 'warning') {
                $this->warn("  ⚠ WARNING ({$executionTime}ms): {$validation['message']}");
                $this->results['warnings']++;
            } else {
                $this->error("  ✗ FAILED: {$validation['message']}");
                $this->results['failed']++;
                $this->results['errors'][] = "{$name}: {$validation['message']}";
            }

            // Show key metrics
            $this->displayKeyMetrics($name, $data);

            // Show full data if verbose
            if ($this->option('show-data')) {
                $this->line('  Full Data:');
                $this->line('  ' . json_encode($data, JSON_PRETTY_PRINT));
            }

            $this->newLine();

        } catch (Exception $e) {
            $this->error("  ✗ ERROR: " . $e->getMessage());
            $this->results['failed']++;
            $this->results['errors'][] = "{$name}: " . $e->getMessage();
            $this->newLine();
        }
    }

    protected function validateReportData(string $name, array $data): array
    {
        // Check if report has error
        if (isset($data['error'])) {
            return ['status' => 'failed', 'message' => $data['error']];
        }

        // Check if report_type is set
        if (!isset($data['report_type'])) {
            return ['status' => 'warning', 'message' => 'Missing report_type field'];
        }

        // Check if generated_at is set
        if (!isset($data['generated_at'])) {
            return ['status' => 'warning', 'message' => 'Missing generated_at field'];
        }

        return ['status' => 'passed', 'message' => 'OK'];
    }

    protected function displayKeyMetrics(string $name, array $data): void
    {
        $metrics = [];

        // Extract key metrics based on report type
        if (isset($data['summary'])) {
            foreach ($data['summary'] as $key => $value) {
                if (is_scalar($value)) {
                    $metrics[$key] = $value;
                }
            }
        }

        if (isset($data['portfolio_summary'])) {
            foreach ($data['portfolio_summary'] as $key => $value) {
                if (is_scalar($value)) {
                    $metrics[$key] = $value;
                }
            }
        }

        if (isset($data['metrics'])) {
            foreach ($data['metrics'] as $key => $value) {
                if (is_scalar($value)) {
                    $metrics[$key] = $value;
                }
            }
        }

        if (isset($data['totals'])) {
            foreach ($data['totals'] as $key => $value) {
                if (is_scalar($value)) {
                    $metrics["total_{$key}"] = $value;
                }
            }
        }

        // Show up to 5 key metrics
        $count = 0;
        foreach ($metrics as $key => $value) {
            if ($count >= 5) break;
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $this->line("    • {$formattedKey}: {$value}");
            $count++;
        }
    }

    protected function testSpecificReport(string $reportName): void
    {
        $reportMap = [
            // Daily
            'daily_operations' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyOperationsReport'],
            'daily_arrears' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyArrearsReport'],
            'daily_cash_position' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyCashPositionReport'],
            'daily_disbursements' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyDisbursementsReport'],
            'daily_collections' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyCollectionsReport'],
            'daily_member_activity' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyMemberActivityReport'],
            'daily_loan_officer_portfolio' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyLoanOfficerPortfolioReport'],
            'daily_recovery_action' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyRecoveryActionReport'],
            'daily_gl_summary' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyGLSummaryReport'],
            'daily_large_transactions' => ['service' => 'dailyWeeklyService', 'method' => 'generateDailyLargeTransactionsReport'],

            // Weekly
            'weekly_executive_summary' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklyExecutiveSummaryReport'],
            'weekly_arrears' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklyArrearsReport'],
            'weekly_credit_committee' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklyCreditCommitteeReport'],
            'weekly_collections_performance' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklyCollectionsPerformanceReport'],
            'weekly_risk_alerts' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklyRiskAlertsReport'],
            'weekly_savings_mobilization' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklySavingsMobilizationReport'],
            'weekly_loan_officer_targets' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklyLoanOfficerTargetsReport'],
            'weekly_suspense_account' => ['service' => 'dailyWeeklyService', 'method' => 'generateWeeklySuspenseAccountReport'],

            // Monthly Credit
            'monthly_loan_portfolio' => ['service' => 'monthlyService', 'method' => 'generateMonthlyLoanPortfolioReport'],
            'monthly_delinquency' => ['service' => 'monthlyService', 'method' => 'generateMonthlyDelinquencyReport'],
            'monthly_npl_classification' => ['service' => 'monthlyService', 'method' => 'generateMonthlyNPLClassificationReport'],
            'monthly_provision' => ['service' => 'monthlyService', 'method' => 'generateMonthlyProvisionReport'],
            'monthly_loan_officer_performance' => ['service' => 'monthlyService', 'method' => 'generateMonthlyLoanOfficerPerformanceReport'],
            'monthly_product_analysis' => ['service' => 'monthlyService', 'method' => 'generateMonthlyProductAnalysisReport'],

            // Monthly Finance
            'monthly_trial_balance' => ['service' => 'monthlyService', 'method' => 'generateMonthlyTrialBalanceReport'],
            'monthly_income_statement' => ['service' => 'monthlyService', 'method' => 'generateMonthlyIncomeStatementReport'],
            'monthly_balance_sheet' => ['service' => 'monthlyService', 'method' => 'generateMonthlyBalanceSheetReport'],
            'monthly_cash_flow' => ['service' => 'monthlyService', 'method' => 'generateMonthlyCashFlowReport'],
            'monthly_budget_variance' => ['service' => 'monthlyService', 'method' => 'generateMonthlyBudgetVarianceReport'],
            'monthly_bank_reconciliation' => ['service' => 'monthlyService', 'method' => 'generateMonthlyBankReconciliationReport'],
            'monthly_interest_accrual' => ['service' => 'monthlyService', 'method' => 'generateMonthlyInterestAccrualReport'],
            'monthly_liquidity' => ['service' => 'monthlyService', 'method' => 'generateMonthlyLiquidityReport'],

            // Monthly Membership
            'monthly_membership' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlyMembershipReport'],
            'monthly_savings_deposits' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlySavingsDepositsReport'],
            'monthly_share_capital' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlyShareCapitalReport'],
            'monthly_mandatory_savings' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlyMandatorySavingsReport'],

            // Monthly Risk
            'monthly_risk_assessment' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlyRiskAssessmentReport'],
            'monthly_concentration_risk' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlyConcentrationRiskReport'],
            'monthly_aml_compliance' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlyAMLComplianceReport'],
            'monthly_write_off' => ['service' => 'quarterlyAnnualService', 'method' => 'generateMonthlyWriteOffReport'],

            // Quarterly Regulatory
            'quarterly_bot_regulatory' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyBOTRegulatoryReport'],
            'quarterly_capital_adequacy' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyCapitalAdequacyReport'],
            'quarterly_liquid_assets' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyLiquidAssetsReport'],
            'quarterly_large_exposures' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyLargeExposuresReport'],
            'quarterly_npl_return' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyNPLReturnReport'],
            'quarterly_tcdc_supervision' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyTCDCSupervisionReport'],
            'quarterly_tcdc_membership' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyTCDCMembershipReport'],

            // Quarterly Governance
            'quarterly_board_pack' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyBoardPackReport'],
            'quarterly_supervisory' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlySupervisoryReport'],
            'quarterly_audit' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyAuditReport'],
            'quarterly_risk' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyRiskReport'],
            'quarterly_financial_statements' => ['service' => 'quarterlyAnnualService', 'method' => 'generateQuarterlyFinancialStatementsReport'],

            // Annual
            'annual_financial_statements' => ['service' => 'quarterlyAnnualService', 'method' => 'generateAnnualFinancialStatementsReport'],
            'annual_agm_pack' => ['service' => 'quarterlyAnnualService', 'method' => 'generateAnnualAGMPackReport'],
            'annual_bot_return' => ['service' => 'quarterlyAnnualService', 'method' => 'generateAnnualBOTReturnReport'],
            'annual_tcdc_return' => ['service' => 'quarterlyAnnualService', 'method' => 'generateAnnualTCDCReturnReport'],
        ];

        if (!isset($reportMap[$reportName])) {
            $this->error("Unknown report: {$reportName}");
            $this->info("Available reports: " . implode(', ', array_keys($reportMap)));
            return;
        }

        $config = $reportMap[$reportName];
        $service = $this->{$config['service']};
        $this->testReport($reportName, $service, $config['method']);
    }

    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('===========================================');
        $this->info('  Test Summary');
        $this->info('===========================================');
        $this->info("Passed:   {$this->results['passed']}");
        $this->warn("Warnings: {$this->results['warnings']}");
        $this->error("Failed:   {$this->results['failed']}");
        $this->newLine();

        if (!empty($this->results['errors'])) {
            $this->error('Errors:');
            foreach ($this->results['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        $this->info('Completed at: ' . now()->format('Y-m-d H:i:s'));
    }
}
