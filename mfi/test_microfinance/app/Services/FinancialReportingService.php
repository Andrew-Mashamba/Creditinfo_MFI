<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\ClientsModel;
use App\Models\LoansModel;
use App\Models\general_ledger;
use App\Models\BranchesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class FinancialReportingService
{
    protected $reportTypes = [
        'statement_of_financial_position' => [
            'name' => 'Statement of Financial Position',
            'category' => 'regulatory',
            'compliance' => ['BOT', 'IFRS'],
            'frequency' => 'monthly',
            'description' => 'Comprehensive balance sheet showing assets, liabilities, and equity positions'
        ],
        'statement_of_comprehensive_income' => [
            'name' => 'Statement of Comprehensive Income',
            'category' => 'regulatory',
            'compliance' => ['BOT', 'IFRS'],
            'frequency' => 'monthly',
            'description' => 'Detailed income statement showing revenue, expenses, and net income'
        ],
        'statement_of_cash_flow' => [
            'name' => 'Statement of Cash Flow',
            'category' => 'regulatory',
            'compliance' => ['BOT', 'IFRS'],
            'frequency' => 'monthly',
            'description' => 'Cash flow analysis showing operating, investing, and financing activities'
        ],
        'sectoral_classification_of_loans' => [
            'name' => 'Sectoral Classification of Loans',
            'category' => 'regulatory',
            'compliance' => ['BOT'],
            'frequency' => 'monthly',
            'description' => 'Classification of loans by economic sector'
        ],
        'trial_balance' => [
            'name' => 'Trial Balance',
            'category' => 'operational',
            'compliance' => ['Internal'],
            'frequency' => 'daily',
            'description' => 'Complete trial balance for accounting verification'
        ],
        'general_ledger' => [
            'name' => 'General Ledger',
            'category' => 'operational',
            'compliance' => ['Internal'],
            'frequency' => 'daily',
            'description' => 'Detailed general ledger entries'
        ],
        'loan_portfolio_analysis' => [
            'name' => 'Loan Portfolio Analysis',
            'category' => 'analytical',
            'compliance' => ['Internal'],
            'frequency' => 'weekly',
            'description' => 'Comprehensive loan portfolio performance analysis'
        ],
        'member_analysis' => [
            'name' => 'Member Analysis Report',
            'category' => 'operational',
            'compliance' => ['Internal'],
            'frequency' => 'monthly',
            'description' => 'Detailed member demographics and behavior analysis'
        ],
        'financial_ratios' => [
            'name' => 'Financial Ratios and Metrics',
            'category' => 'analytical',
            'compliance' => ['Internal'],
            'frequency' => 'weekly',
            'description' => 'Key financial ratios and performance indicators'
        ],
        'compliance_status' => [
            'name' => 'Compliance Status Report',
            'category' => 'regulatory',
            'compliance' => ['BOT', 'TCDC'],
            'frequency' => 'monthly',
            'description' => 'Comprehensive compliance status across all regulatory requirements'
        ]
    ];

    public function getRegulatoryReports(): array
    {
        return array_filter($this->reportTypes, function($report) {
            return in_array('BOT', $report['compliance']) || in_array('IFRS', $report['compliance']);
        });
    }

    public function getGeneralReports(): array
    {
        return array_filter($this->reportTypes, function($report) {
            return $report['category'] === 'operational' || $report['category'] === 'analytical';
        });
    }

    public function generateFinancialData($reportType, $startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? Carbon::now()->startOfMonth();
            $endDate = $endDate ?? Carbon::now()->endOfMonth();

            switch ($reportType) {
                case 'statement_of_financial_position':
                    return $this->generateStatementOfFinancialPosition($startDate, $endDate);
                case 'statement_of_comprehensive_income':
                    return $this->generateStatementOfComprehensiveIncome($startDate, $endDate);
                case 'statement_of_cash_flow':
                    return $this->generateStatementOfCashFlow($startDate, $endDate);
                case 'loan_portfolio_analysis':
                    return $this->generateLoanPortfolioAnalysis($startDate, $endDate);
                case 'member_analysis':
                    return $this->generateMemberAnalysis($startDate, $endDate);
                case 'financial_ratios':
                    return $this->generateFinancialRatios($startDate, $endDate);
                case 'compliance_status':
                    return $this->generateComplianceStatus($startDate, $endDate);
                default:
                    throw new Exception("Unknown report type: {$reportType}");
            }
        } catch (Exception $e) {
            Log::error("Error generating financial data for {$reportType}: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateStatementOfFinancialPosition($startDate, $endDate)
    {
        $data = [
            'report_info' => [
                'title' => 'Statement of Financial Position',
                'as_at' => $endDate->format('F d, Y'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'compliance' => ['BOT', 'IFRS']
            ],
            'assets' => [
                'current_assets' => [
                    'cash_and_cash_equivalents' => $this->getAccountBalance('1001'),
                    'short_term_investments' => $this->getAccountBalance('1002'),
                    'accounts_receivable' => $this->getAccountBalance('1003'),
                    'inventory' => $this->getAccountBalance('1004'),
                    'prepaid_expenses' => $this->getAccountBalance('1005'),
                    'other_current_assets' => $this->getAccountBalance('1006')
                ],
                'non_current_assets' => [
                    'property_plant_equipment' => $this->getAccountBalance('1101'),
                    'intangible_assets' => $this->getAccountBalance('1102'),
                    'long_term_investments' => $this->getAccountBalance('1103'),
                    'deferred_tax_assets' => $this->getAccountBalance('1104'),
                    'other_non_current_assets' => $this->getAccountBalance('1105')
                ]
            ],
            'liabilities' => [
                'current_liabilities' => [
                    'accounts_payable' => $this->getAccountBalance('2001'),
                    'short_term_borrowings' => $this->getAccountBalance('2002'),
                    'accrued_expenses' => $this->getAccountBalance('2003'),
                    'income_tax_payable' => $this->getAccountBalance('2004'),
                    'other_current_liabilities' => $this->getAccountBalance('2005')
                ],
                'non_current_liabilities' => [
                    'long_term_borrowings' => $this->getAccountBalance('2101'),
                    'deferred_tax_liabilities' => $this->getAccountBalance('2102'),
                    'other_non_current_liabilities' => $this->getAccountBalance('2103')
                ]
            ],
            'equity' => [
                'share_capital' => $this->getAccountBalance('3001'),
                'retained_earnings' => $this->getAccountBalance('3002'),
                'other_comprehensive_income' => $this->getAccountBalance('3003'),
                'treasury_shares' => $this->getAccountBalance('3004')
            ]
        ];

        // Calculate totals
        $data['totals'] = $this->calculateBalanceSheetTotals($data);

        return $data;
    }

    public function generateStatementOfComprehensiveIncome($startDate, $endDate)
    {
        $data = [
            'report_info' => [
                'title' => 'Statement of Comprehensive Income',
                'period' => $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'compliance' => ['BOT', 'IFRS']
            ],
            'revenue' => [
                'interest_income' => $this->getAccountBalance('4001', $startDate, $endDate),
                'fee_income' => $this->getAccountBalance('4002', $startDate, $endDate),
                'investment_income' => $this->getAccountBalance('4003', $startDate, $endDate),
                'other_income' => $this->getAccountBalance('4004', $startDate, $endDate)
            ],
            'expenses' => [
                'interest_expense' => $this->getAccountBalance('5001', $startDate, $endDate),
                'personnel_expenses' => $this->getAccountBalance('5002', $startDate, $endDate),
                'administrative_expenses' => $this->getAccountBalance('5003', $startDate, $endDate),
                'depreciation_amortization' => $this->getAccountBalance('5004', $startDate, $endDate),
                'provision_for_loan_losses' => $this->getAccountBalance('5005', $startDate, $endDate),
                'other_expenses' => $this->getAccountBalance('5006', $startDate, $endDate)
            ],
            'other_comprehensive_income' => [
                'unrealized_gains_losses' => $this->getAccountBalance('6001', $startDate, $endDate),
                'foreign_currency_translation' => $this->getAccountBalance('6002', $startDate, $endDate)
            ]
        ];

        // Calculate totals
        $data['totals'] = $this->calculateIncomeStatementTotals($data);

        return $data;
    }

    public function generateStatementOfCashFlow($startDate, $endDate)
    {
        $data = [
            'report_info' => [
                'title' => 'Statement of Cash Flow',
                'period' => $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'compliance' => ['BOT', 'IFRS']
            ],
            'operating_activities' => [
                'net_income' => $this->calculateNetIncome($startDate, $endDate),
                'adjustments_for_non_cash_items' => [
                    'depreciation_amortization' => $this->getAccountBalance('5004', $startDate, $endDate),
                    'provision_for_loan_losses' => $this->getAccountBalance('5005', $startDate, $endDate),
                    'changes_in_working_capital' => $this->calculateWorkingCapitalChanges($startDate, $endDate)
                ]
            ],
            'investing_activities' => [
                'purchase_of_property_equipment' => $this->getAccountBalance('1101', $startDate, $endDate, 'debit'),
                'purchase_of_investments' => $this->getAccountBalance('1103', $startDate, $endDate, 'debit'),
                'proceeds_from_sale_of_investments' => $this->getAccountBalance('1103', $startDate, $endDate, 'credit')
            ],
            'financing_activities' => [
                'proceeds_from_borrowings' => $this->getAccountBalance('2002', $startDate, $endDate, 'credit'),
                'repayment_of_borrowings' => $this->getAccountBalance('2002', $startDate, $endDate, 'debit'),
                'dividends_paid' => $this->getAccountBalance('3002', $startDate, $endDate, 'debit')
            ]
        ];

        // Calculate totals
        $data['totals'] = $this->calculateCashFlowTotals($data);

        return $data;
    }

    public function generateLoanPortfolioAnalysis($startDate, $endDate)
    {
        $data = [
            'report_info' => [
                'title' => 'Loan Portfolio Analysis',
                'period' => $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'compliance' => ['Internal']
            ],
            'portfolio_summary' => [
                'total_loans' => LoansModel::count(),
                'active_loans' => LoansModel::where('loan_status', 'ACTIVE')->count(),
                'total_portfolio_value' => LoansModel::where('loan_status', 'ACTIVE')->sum('remaining_amount'),
                'average_loan_size' => LoansModel::where('loan_status', 'ACTIVE')->avg('remaining_amount')
            ],
            'portfolio_at_risk' => [
                'par_30_days' => $this->calculatePortfolioAtRisk(30),
                'par_60_days' => $this->calculatePortfolioAtRisk(60),
                'par_90_days' => $this->calculatePortfolioAtRisk(90),
                'par_180_days' => $this->calculatePortfolioAtRisk(180)
            ],
            'loan_distribution' => [
                'by_product' => $this->getLoanDistributionByProduct(),
                'by_sector' => $this->getLoanDistributionBySector(),
                'by_size' => $this->getLoanDistributionBySize(),
                'by_region' => $this->getLoanDistributionByRegion()
            ],
            'performance_metrics' => [
                'disbursement_rate' => $this->calculateDisbursementRate($startDate, $endDate),
                'collection_rate' => $this->calculateCollectionRate($startDate, $endDate),
                'average_interest_rate' => $this->calculateAverageInterestRate(),
                'loan_to_value_ratio' => $this->calculateLoanToValueRatio()
            ]
        ];

        return $data;
    }

    public function generateMemberAnalysis($startDate, $endDate)
    {
        $data = [
            'report_info' => [
                'title' => 'Member Analysis Report',
                'period' => $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'compliance' => ['Internal']
            ],
            'member_summary' => [
                'total_members' => ClientsModel::count(),
                'active_members' => ClientsModel::where('client_status', 'ACTIVE')->count(),
                'new_members_this_period' => ClientsModel::whereBetween('created_at', [$startDate, $endDate])->count(),
                'inactive_members' => ClientsModel::where('client_status', 'INACTIVE')->count()
            ],
            'demographics' => [
                'by_age_group' => $this->getMemberDistributionByAge(),
                'by_gender' => $this->getMemberDistributionByGender(),
                'by_occupation' => $this->getMemberDistributionByOccupation(),
                'by_region' => $this->getMemberDistributionByRegion()
            ],
            'product_usage' => [
                'savings_accounts' => $this->getProductUsageStats('savings'),
                'loan_accounts' => $this->getProductUsageStats('loans'),
                'share_accounts' => $this->getProductUsageStats('shares'),
                'deposit_accounts' => $this->getProductUsageStats('deposits')
            ],
            'behavioral_analysis' => [
                'average_transaction_frequency' => $this->calculateAverageTransactionFrequency($startDate, $endDate),
                'average_account_balance' => $this->calculateAverageAccountBalance(),
                'member_retention_rate' => $this->calculateMemberRetentionRate($startDate, $endDate),
                'cross_selling_opportunities' => $this->identifyCrossSellingOpportunities()
            ]
        ];

        return $data;
    }

    public function generateFinancialRatios($startDate, $endDate)
    {
        $data = [
            'report_info' => [
                'title' => 'Financial Ratios and Metrics',
                'period' => $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'compliance' => ['Internal']
            ],
            'liquidity_ratios' => [
                'current_ratio' => $this->calculateCurrentRatio(),
                'quick_ratio' => $this->calculateQuickRatio(),
                'cash_ratio' => $this->calculateCashRatio(),
                'working_capital' => $this->calculateWorkingCapital()
            ],
            'profitability_ratios' => [
                'return_on_assets' => $this->calculateReturnOnAssets($startDate, $endDate),
                'return_on_equity' => $this->calculateReturnOnEquity($startDate, $endDate),
                'net_interest_margin' => $this->calculateNetInterestMargin($startDate, $endDate),
                'cost_to_income_ratio' => $this->calculateCostToIncomeRatio($startDate, $endDate)
            ],
            'efficiency_ratios' => [
                'asset_utilization' => $this->calculateAssetUtilization($startDate, $endDate),
                'operating_efficiency' => $this->calculateOperatingEfficiency($startDate, $endDate),
                'employee_productivity' => $this->calculateEmployeeProductivity($startDate, $endDate)
            ],
            'risk_ratios' => [
                'capital_adequacy_ratio' => $this->calculateCapitalAdequacyRatio(),
                'loan_to_deposit_ratio' => $this->calculateLoanToDepositRatio(),
                'provision_coverage_ratio' => $this->calculateProvisionCoverageRatio(),
                'concentration_risk' => $this->calculateConcentrationRisk()
            ]
        ];

        return $data;
    }

    public function generateComplianceStatus($startDate, $endDate)
    {
        $data = [
            'report_info' => [
                'title' => 'Compliance Status Report',
                'period' => $startDate->format('F d, Y') . ' to ' . $endDate->format('F d, Y'),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'compliance' => ['BOT', 'TCDC']
            ],
            'regulatory_compliance' => [
                'bot_requirements' => $this->checkBOTCompliance(),
                'ifrs_compliance' => $this->checkIFRSCompliance(),
                'tcdc_requirements' => $this->checkTCDCCompliance(),
                'tax_compliance' => $this->checkTaxCompliance()
            ],
            'risk_management' => [
                'credit_risk' => $this->assessCreditRisk(),
                'liquidity_risk' => $this->assessLiquidityRisk(),
                'operational_risk' => $this->assessOperationalRisk(),
                'market_risk' => $this->assessMarketRisk()
            ],
            'internal_controls' => [
                'segregation_of_duties' => $this->checkSegregationOfDuties(),
                'access_controls' => $this->checkAccessControls(),
                'audit_trail' => $this->checkAuditTrail(),
                'backup_recovery' => $this->checkBackupRecovery()
            ],
            'reporting_requirements' => [
                'monthly_reports' => $this->checkMonthlyReporting(),
                'quarterly_reports' => $this->checkQuarterlyReporting(),
                'annual_reports' => $this->checkAnnualReporting(),
                'ad_hoc_reports' => $this->checkAdHocReporting()
            ]
        ];

        return $data;
    }

    // Helper methods for calculations
    private function getAccountBalance($accountNumber, $startDate = null, $endDate = null, $type = 'balance')
    {
        $query = AccountsModel::where('account_number', $accountNumber);
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        if ($type === 'balance') {
            return $query->sum('balance');
        } elseif ($type === 'debit') {
            return $query->sum('debit');
        } elseif ($type === 'credit') {
            return $query->sum('credit');
        }
        
        return 0;
    }

    private function calculateBalanceSheetTotals($data)
    {
        $totalCurrentAssets = array_sum($data['assets']['current_assets']);
        $totalNonCurrentAssets = array_sum($data['assets']['non_current_assets']);
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        $totalCurrentLiabilities = array_sum($data['liabilities']['current_liabilities']);
        $totalNonCurrentLiabilities = array_sum($data['liabilities']['non_current_liabilities']);
        $totalLiabilities = $totalCurrentLiabilities + $totalNonCurrentLiabilities;

        $totalEquity = array_sum($data['equity']);

        return [
            'total_current_assets' => $totalCurrentAssets,
            'total_non_current_assets' => $totalNonCurrentAssets,
            'total_assets' => $totalAssets,
            'total_current_liabilities' => $totalCurrentLiabilities,
            'total_non_current_liabilities' => $totalNonCurrentLiabilities,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity
        ];
    }

    private function calculateIncomeStatementTotals($data)
    {
        $totalRevenue = array_sum($data['revenue']);
        $totalExpenses = array_sum($data['expenses']);
        $netIncome = $totalRevenue - $totalExpenses;
        $totalOtherComprehensiveIncome = array_sum($data['other_comprehensive_income']);
        $totalComprehensiveIncome = $netIncome + $totalOtherComprehensiveIncome;

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'total_other_comprehensive_income' => $totalOtherComprehensiveIncome,
            'total_comprehensive_income' => $totalComprehensiveIncome
        ];
    }

    private function calculateCashFlowTotals($data)
    {
        $netCashFromOperating = $data['operating_activities']['net_income'] + 
                               array_sum($data['operating_activities']['adjustments_for_non_cash_items']);
        
        $netCashFromInvesting = array_sum($data['investing_activities']);
        $netCashFromFinancing = array_sum($data['financing_activities']);
        
        $netChangeInCash = $netCashFromOperating + $netCashFromInvesting + $netCashFromFinancing;

        return [
            'net_cash_from_operating' => $netCashFromOperating,
            'net_cash_from_investing' => $netCashFromInvesting,
            'net_cash_from_financing' => $netCashFromFinancing,
            'net_change_in_cash' => $netChangeInCash
        ];
    }

    private function calculatePortfolioAtRisk($days)
    {
        $overdueAmount = LoansModel::where('due_date', '<', now()->subDays($days))
            ->where('loan_status', 'ACTIVE')
            ->sum('remaining_amount');
        
        $totalPortfolio = LoansModel::where('loan_status', 'ACTIVE')->sum('remaining_amount');
        
        return $totalPortfolio > 0 ? ($overdueAmount / $totalPortfolio) * 100 : 0;
    }

    private function calculateNetIncome($startDate, $endDate)
    {
        $revenue = $this->getAccountBalance('4001', $startDate, $endDate) + 
                  $this->getAccountBalance('4002', $startDate, $endDate) + 
                  $this->getAccountBalance('4003', $startDate, $endDate) + 
                  $this->getAccountBalance('4004', $startDate, $endDate);
        
        $expenses = $this->getAccountBalance('5001', $startDate, $endDate) + 
                   $this->getAccountBalance('5002', $startDate, $endDate) + 
                   $this->getAccountBalance('5003', $startDate, $endDate) + 
                   $this->getAccountBalance('5004', $startDate, $endDate) + 
                   $this->getAccountBalance('5005', $startDate, $endDate) + 
                   $this->getAccountBalance('5006', $startDate, $endDate);
        
        return $revenue - $expenses;
    }

    // Additional helper methods would be implemented here for all the other calculations
    // These are placeholder methods that would contain the actual business logic
    
    private function calculateWorkingCapitalChanges($startDate, $endDate) { return 0; }
    private function getLoanDistributionByProduct() { return []; }
    private function getLoanDistributionBySector() { return []; }
    private function getLoanDistributionBySize() { return []; }
    private function getLoanDistributionByRegion() { return []; }
    private function calculateDisbursementRate($startDate, $endDate) { return 0; }
    private function calculateCollectionRate($startDate, $endDate) { return 0; }
    private function calculateAverageInterestRate() { return 0; }
    private function calculateLoanToValueRatio() { return 0; }
    private function getMemberDistributionByAge() { return []; }
    private function getMemberDistributionByGender() { return []; }
    private function getMemberDistributionByOccupation() { return []; }
    private function getMemberDistributionByRegion() { return []; }
    private function getProductUsageStats($product) { return []; }
    private function calculateAverageTransactionFrequency($startDate, $endDate) { return 0; }
    private function calculateAverageAccountBalance() { return 0; }
    private function calculateMemberRetentionRate($startDate, $endDate) { return 0; }
    private function identifyCrossSellingOpportunities() { return []; }
    private function calculateCurrentRatio() { return 0; }
    private function calculateQuickRatio() { return 0; }
    private function calculateCashRatio() { return 0; }
    private function calculateWorkingCapital() { return 0; }
    private function calculateReturnOnAssets($startDate, $endDate) { return 0; }
    private function calculateReturnOnEquity($startDate, $endDate) { return 0; }
    private function calculateNetInterestMargin($startDate, $endDate) { return 0; }
    private function calculateCostToIncomeRatio($startDate, $endDate) { return 0; }
    private function calculateAssetUtilization($startDate, $endDate) { return 0; }
    private function calculateOperatingEfficiency($startDate, $endDate) { return 0; }
    private function calculateEmployeeProductivity($startDate, $endDate) { return 0; }
    private function calculateCapitalAdequacyRatio() { return 0; }
    private function calculateLoanToDepositRatio() { return 0; }
    private function calculateProvisionCoverageRatio() { return 0; }
    private function calculateConcentrationRisk() { return 0; }
    private function checkBOTCompliance() { return []; }
    private function checkIFRSCompliance() { return []; }
    private function checkTCDCCompliance() { return []; }
    private function checkTaxCompliance() { return []; }
    private function assessCreditRisk() { return []; }
    private function assessLiquidityRisk() { return []; }
    private function assessOperationalRisk() { return []; }
    private function assessMarketRisk() { return []; }
    private function checkSegregationOfDuties() { return []; }
    private function checkAccessControls() { return []; }
    private function checkAuditTrail() { return []; }
    private function checkBackupRecovery() { return []; }
    private function checkMonthlyReporting() { return []; }
    private function checkQuarterlyReporting() { return []; }
    private function checkAnnualReporting() { return []; }
    private function checkAdHocReporting() { return []; }
} 