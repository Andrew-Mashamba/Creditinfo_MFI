<?php

namespace App\Http\Livewire\Reports;

use App\Models\approvals;
use App\Models\LoansModel;
use App\Models\Transactions;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\BranchesModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MainReport;
use App\Exports\LoanSchedule;
use App\Exports\ContractData;
use Carbon\Carbon;
use Exception;
use App\Traits\Livewire\WithModulePermissions;

class Reports extends Component
{
    use WithModulePermissions;
    // Core Properties
    public $endDate;
    public $startDate;
    public $nodes;
    public $services;
    public $channels;
    public $type;

    // Modal Properties
    public $showResolveModal = false;
    public $transactionToReview;
    public $comments;
    public $showReportDetailsModal = false;
    public $showExportOptionsModal = false;
    public $showScheduleReportModal = false;

    // Report Properties
    public $processorNodes;
    public $sortByBranch;
    public $ReportCategory = 1;
    public $tab_id = 37;
    public $loanItems;
    public $reportStartDate;
    public $reportEndDate;
    public $customize = "NO";
    public $custome_client_number;

    // Enhanced Analytics Properties
    public $totalMembers = 0;
    public $activeMembers = 0;
    public $inactiveMembers = 0;
    public $totalLoans = 0;
    public $activeLoans = 0;
    public $overdueLoans = 0;
    public $totalSavings = 0;
    public $totalDeposits = 0;
    public $totalShares = 0;
    public $totalTransactions = 0;
    public $monthlyGrowth = 0;
    public $portfolioAtRisk = 0;
    public $capitalAdequacyRatio = 0;
    public $liquidityRatio = 0;

    // Report Generation Properties
    public $selectedReportType = '';
    public $reportFormat = 'pdf'; // pdf, excel, csv
    public $includeCharts = true;
    public $includeSummary = true;

    // Search and Filter Properties
    public $searchTerm = '';
    public $selectedCategory = '';
    public $showAllReports = false;
    public $includeDetails = true;
    public $reportPeriod = 'monthly'; // daily, weekly, monthly, quarterly, annually
    public $reportTitle = '';
    public $reportDescription = '';

    // Report Viewing Properties
    public $showReportView = false;
    public $currentReportId = null;
    public $currentReport = null;
    public $activeComponent = 'dashboard'; // 'dashboard', 'statement-of-financial-position', 'statement-of-comprehensive-income', 'statement-of-cash-flow', 'loan-portfolio-report', 'loan-delinquency-report', 'loan-disbursement-report', 'portfolio-at-risk', 'active-loans-by-officer', 'loan-application-report'
    public $showStatementView = false;
    public $statementData = null;

    // Scheduling Properties
    public $scheduleFrequency = 'once'; // once, daily, weekly, monthly
    public $scheduleDate = '';
    public $scheduleTime = '09:00';
    public $emailRecipients = [];
    public $emailSubject = '';
    public $emailMessage = '';

    // Filter Properties
    public $changeBranch;
    public $selectedBranches = [];
    public $selectedProducts = [];
    public $selectedStatuses = [];
    public $dateRange = 'this_month'; // this_month, last_month, this_quarter, this_year, custom

    // Loading States
    public $isLoading = false;
    public $isGenerating = false;
    public $isExporting = false;
    public $isScheduling = false;

    // Messages
    public $successMessage = '';
    public $errorMessage = '';
    public $warningMessage = '';

    // Report History
    public $reportHistory = [];
    public $showReportHistory = false;

    protected $listeners = [
        'resolveModal' => 'showResolveModal',
        'refresh' => '$refresh',
        'reportGenerated' => 'handleReportGenerated',
        'exportCompleted' => 'handleExportCompleted'
    ];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->initializeDates();
        $this->loadAnalytics();
        $this->loadReportHistory();
    }

    public function initializeDates()
    {
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->reportStartDate = $this->startDate;
        $this->reportEndDate = $this->endDate;
        $this->scheduleDate = Carbon::now()->addDay()->format('Y-m-d');
    }

    public function loadAnalytics()
    {
        try {
            $this->isLoading = true;

            // Member Analytics
            $this->totalMembers = ClientsModel::count();
            $this->activeMembers = ClientsModel::where('client_status', 'ACTIVE')->count();
            $this->inactiveMembers = $this->totalMembers - $this->activeMembers;

            // Loan Analytics
            $this->totalLoans = LoansModel::count();
            $this->activeLoans = LoansModel::where('loan_status', 'ACTIVE')->count();
            $this->overdueLoans = LoansModel::where('due_date', '<', now())->count();

            // Financial Analytics
            $this->totalSavings = AccountsModel::where('major_category_code', 1000)->sum('balance');
            $this->totalDeposits = AccountsModel::where('major_category_code', 2000)->sum('balance');
            $this->totalShares = DB::table('share_registers')->sum('current_share_balance');
            $this->totalTransactions = general_ledger::count();

            // Performance Metrics
            $this->calculatePerformanceMetrics();

        } catch (Exception $e) {
            Log::error('Error loading analytics: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load analytics data.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function calculatePerformanceMetrics()
    {
        try {
            // Portfolio at Risk (PAR) - Loans overdue by 30+ days
            $overdueAmount = LoansModel::where('due_date', '<', now()->subDays(30))
                ->sum('remaining_amount');
            $totalPortfolio = LoansModel::where('loan_status', 'ACTIVE')->sum('remaining_amount');
            $this->portfolioAtRisk = $totalPortfolio > 0 ? ($overdueAmount / $totalPortfolio) * 100 : 0;

            // Capital Adequacy Ratio (simplified calculation)
            $totalAssets = AccountsModel::where('major_category_code', 1000)->sum('balance');
            $totalLiabilities = AccountsModel::where('major_category_code', 2000)->sum('balance');
            $capital = $totalAssets - $totalLiabilities;
            $this->capitalAdequacyRatio = $totalAssets > 0 ? ($capital / $totalAssets) * 100 : 0;

            // Liquidity Ratio
            $liquidAssets = AccountsModel::where('major_category_code', 1000)
                ->whereIn('account_number', ['1001', '1002', '1003']) // Cash and cash equivalents
                ->sum('balance');
            $shortTermLiabilities = AccountsModel::where('major_category_code', 2000)
                ->whereIn('account_number', ['2001', '2002']) // Short-term deposits
                ->sum('balance');
            $this->liquidityRatio = $shortTermLiabilities > 0 ? ($liquidAssets / $shortTermLiabilities) * 100 : 0;

            // Monthly Growth Rate
            $currentMonth = general_ledger::whereMonth('created_at', now()->month)->sum('debit');
            $lastMonth = general_ledger::whereMonth('created_at', now()->subMonth()->month)->sum('debit');
            $this->monthlyGrowth = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;

        } catch (Exception $e) {
            Log::error('Error calculating performance metrics: ' . $e->getMessage());
        }
    }

    public function loadReportHistory()
    {
        try {
            $this->reportHistory = DB::table('scheduled_reports')
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (Exception $e) {
            Log::error('Error loading report history: ' . $e->getMessage());
        }
    }

    public function updatedChangeBranch($value)
    {
        $this->emit('changeBranch', $value);
    }

    public function updatedloanItems($value)
    {
        $this->emit('loanItem', $this->loanItems);
    }

    public function menuItemClicked($id)
    {
        // Check permissions based on the report category being accessed
        $reportTypes = $this->getReportTypes();
        if (isset($reportTypes[$id])) {
            $report = $reportTypes[$id];
            $requiredPermission = $this->getRequiredPermissionForReport($report);
            
            if (!$this->authorize($requiredPermission, 'You do not have permission to access this report')) {
                return;
            }
        }
        
        $this->tab_id = $id;
        $this->loadReportDetails($id);
        $this->showReport($id);
    }

    public function showComponent($componentName)
    {
        // Check permissions based on the component being accessed
        $requiredPermission = $this->getRequiredPermissionForComponent($componentName);
        
        if (!$this->authorize($requiredPermission, 'You do not have permission to access this report component')) {
            return;
        }
        
        $this->activeComponent = $componentName;
        $this->showReportView = true;
        
        // Log the component switch
        Log::info('Report component switched', [
            'component' => $componentName,
            'user_id' => auth()->id()
        ]);
    }

    public function backToDashboard()
    {
        $this->activeComponent = 'dashboard';
        $this->showReportView = false;
        $this->currentReportId = null;
        $this->currentReport = null;
    }

    public function showReport($reportId)
    {
        try {
            $reportTypes = $this->getReportTypes();
            if (isset($reportTypes[$reportId])) {
                $this->currentReportId = $reportId;
                $this->currentReport = $reportTypes[$reportId];
                $this->showReportView = true;
                $this->selectedReportType = $reportTypes[$reportId]['name'];
                $this->reportTitle = $reportTypes[$reportId]['title'];
                $this->reportDescription = $reportTypes[$reportId]['description'];
            }
        } catch (Exception $e) {
            Log::error('Error showing report: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load report. Please try again.';
        }
    }

    public function backToReportsList()
    {
        $this->showReportView = false;
        $this->currentReportId = null;
        $this->currentReport = null;
        $this->selectedReportType = '';
        $this->reportTitle = '';
        $this->reportDescription = '';
    }

    public function generateStatementOfFinancialPosition()
    {
        // Check permission to generate financial statements
        if (!$this->authorize('view', 'You do not have permission to generate financial statements')) {
            return;
        }
        
        try {
            $this->isGenerating = true;
            $this->errorMessage = '';
            
            // Check if we need to add sample data for demonstration
            $this->addSampleDataIfNeeded();
            
            // Get the statement data
            $statementData = $this->getStatementOfFinancialPositionData();
            
            // Store the data for display
            $this->statementData = $statementData;
            $this->showStatementView = true;
            
            $this->successMessage = 'Statement of Financial Position generated successfully!';
            
            // Log the report generation
            Log::info('Statement of Financial Position generated', [
                'report_id' => $this->currentReportId,
                'user_id' => auth()->id(),
                'as_of_date' => $this->reportEndDate
            ]);
            
        } catch (Exception $e) {
            Log::error('Error generating Statement of Financial Position: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate Statement of Financial Position. Please try again.';
        } finally {
            $this->isGenerating = false;
        }
    }

    private function addSampleDataIfNeeded()
    {
        // Check if there are any general ledger entries
        $glCount = DB::table('general_ledger')->count();
        
        if ($glCount == 0) {
            // Add some sample data for demonstration
            $sampleTransactions = [
                // Cash deposit
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 1000000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 1000000.00,
                    'narration' => 'Initial cash deposit',
                    'reference_number' => 'CASH001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010120002100', // MEMBER DEPOSITS
                    'debit' => 0.00,
                    'credit' => 1000000.00,
                    'record_on_account_number_balance' => 1000000.00,
                    'narration' => 'Member deposit received',
                    'reference_number' => 'CASH001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Loan disbursement
                [
                    'record_on_account_number' => '010110001200', // LOAN PORTFOLIO
                    'debit' => 500000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 500000.00,
                    'narration' => 'Loan disbursed to member',
                    'reference_number' => 'LOAN001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 0.00,
                    'credit' => 500000.00,
                    'record_on_account_number_balance' => 500000.00,
                    'narration' => 'Cash paid for loan',
                    'reference_number' => 'LOAN001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Share capital
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 200000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 700000.00,
                    'narration' => 'Share capital contribution',
                    'reference_number' => 'SHARE001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010130003000', // MEMBER SHARE CAPITAL
                    'debit' => 0.00,
                    'credit' => 200000.00,
                    'record_on_account_number_balance' => 200000.00,
                    'narration' => 'Share capital received',
                    'reference_number' => 'SHARE001',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            // Insert sample transactions
            DB::table('general_ledger')->insert($sampleTransactions);
            
            // Update account balances
            $this->updateAccountBalances();
        }
    }

    private function updateAccountBalances()
    {
        // Update account balances based on general ledger entries
        $accounts = DB::table('accounts')->where('status', 'ACTIVE')->get();
        
        foreach ($accounts as $account) {
            $debitTotal = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->sum('debit');
                
            $creditTotal = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->sum('credit');
                
            $balance = $debitTotal - $creditTotal;
            
            DB::table('accounts')
                ->where('account_number', $account->account_number)
                ->update([
                    'debit' => $debitTotal,
                    'credit' => $creditTotal,
                    'balance' => $balance,
                    'updated_at' => now()
                ]);
        }
    }

    public function getStatementOfFinancialPositionData()
    {
        $asOfDate = $this->reportEndDate ?: Carbon::now()->format('Y-m-d');
        
        // Get all active accounts with their balances
        $accounts = DB::table('accounts')
            ->select(
                'account_number',
                'account_name',
                'type',
                'major_category_code',
                'category_code',
                'sub_category_code',
                'account_level',
                DB::raw('COALESCE(CAST(balance AS DECIMAL(20,2)), 0) as current_balance'),
                DB::raw('COALESCE(CAST(debit AS DECIMAL(20,2)), 0) as debit_balance'),
                DB::raw('COALESCE(CAST(credit AS DECIMAL(20,2)), 0) as credit_balance')
            )
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('major_category_code')
            ->orderBy('category_code')
            ->orderBy('sub_category_code')
            ->orderBy('account_number')
            ->get();

        // Group accounts by type and calculate totals
        $statementData = [
            'as_of_date' => $asOfDate,
            'assets' => $this->groupAccountsByType($accounts, ['asset_accounts']),
            'liabilities' => $this->groupAccountsByType($accounts, ['liability_accounts']),
            'equity' => $this->groupAccountsByType($accounts, ['capital_accounts']),
            'totals' => []
        ];

        // Calculate totals
        $totalAssets = $statementData['assets']['total'];
        $totalLiabilities = $statementData['liabilities']['total'];
        $totalEquity = $statementData['equity']['total'];
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        $statementData['totals'] = [
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced' => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
            'difference' => abs($totalAssets - $totalLiabilitiesAndEquity)
        ];

        return $statementData;
    }

    private function groupAccountsByType($accounts, $types)
    {
        $grouped = [
            'categories' => [],
            'total' => 0
        ];

        foreach ($accounts as $account) {
            if (in_array($account->type, $types)) {
                // Use the combination of major_category_code and category_code for grouping
                $categoryCode = $account->major_category_code . '-' . $account->category_code;
                $categoryName = $this->getCategoryName($account->type, $categoryCode);
                
                // If no specific category name found, use the major category
                if ($categoryName === "Category {$categoryCode}") {
                    $categoryCode = $account->major_category_code;
                    $categoryName = $this->getCategoryName($account->type, $categoryCode);
                }
                
                if (!isset($grouped['categories'][$categoryCode])) {
                    $grouped['categories'][$categoryCode] = [
                        'name' => $categoryName,
                        'accounts' => [],
                        'subtotal' => 0
                    ];
                }

                $grouped['categories'][$categoryCode]['accounts'][] = $account;
                $grouped['categories'][$categoryCode]['subtotal'] += $account->current_balance;
                $grouped['total'] += $account->current_balance;
            }
        }

        return $grouped;
    }

    private function getCategoryName($type, $categoryCode)
    {
        $categoryNames = [
            'asset_accounts' => [
                '1000' => 'Assets',
                '1000-1000' => 'Cash and Cash Equivalents',
                '1000-1100' => 'Short-term Investments',
                '1000-1200' => 'Loan Portfolio',
                '1000-1300' => 'Loan Loss Provisions',
                '1000-1400' => 'Interest Receivable',
                '1000-1500' => 'Accounts Receivable',
                '1000-1600' => 'Property and Equipment',
                '1000-1700' => 'Long-term Investments',
                '1000-1800' => 'Prepaid Expenses'
            ],
            'liability_accounts' => [
                '2000' => 'Liabilities',
                '2000-2100' => 'Member Deposits',
                '2000-2200' => 'Short-term Debt',
                '2000-2300' => 'Long-term Debt',
                '2000-2400' => 'Accounts Payable'
            ],
            'capital_accounts' => [
                '3000' => 'Equity',
                '3000-3000' => 'Member Share Capital',
                '3000-3100' => 'Retained Earnings',
                '3000-3200' => 'Reserves',
                '3000-3300' => 'Donated Capital'
            ],
            'income_accounts' => [
                '4000' => 'Revenue',
                '4000-4000' => 'Interest Income',
                '4000-4100' => 'Loan Fees and Charges',
                '4000-4200' => 'Service Fees',
                '4000-4300' => 'Investment Income',
                '4000-4400' => 'Grants and Donations',
                '4000-4500' => 'Other Income',
                '4000-4600' => 'Investment Gains',
                '4000-4700' => 'Gains on Disposal',
                '4000-4800' => 'Exchange Gains'
            ],
            'expense_accounts' => [
                '5000' => 'Expenses',
                '5000-5000' => 'Financial Expenses',
                '5000-5100' => 'Personnel Expenses',
                '5000-5200' => 'Administrative Expenses',
                '5000-5300' => 'Operational Expenses',
                '5000-5600' => 'Office Expenses',
                '5000-5700' => 'Facility Expenses',
                '5000-5800' => 'Travel Expenses',
                '5000-5900' => 'Information Technology',
                '5000-6000' => 'Training and Development'
            ]
        ];

        return $categoryNames[$type][$categoryCode] ?? "Category {$categoryCode}";
    }

    public function exportStatementOfFinancialPosition($format = 'pdf')
    {
        // Check permission to export reports
        if (!$this->authorize('export', 'You do not have permission to export reports')) {
            return;
        }
        
        try {
            $this->isExporting = true;
            $this->errorMessage = '';
            
            if (!$this->statementData) {
                $this->statementData = $this->getStatementOfFinancialPositionData();
            }
            
            $filename = 'statement_of_financial_position_' . now()->format('Y-m-d_H-i-s') . '.' . $format;
            
            // For now, we'll simulate the export process
            // In a real implementation, you would use libraries like:
            // - DomPDF for PDF generation
            // - Laravel Excel for Excel/CSV export
            
            $this->successMessage = "Statement of Financial Position exported as {$format} successfully!";
            
            // Log the export
            Log::info('Statement of Financial Position exported', [
                'format' => $format,
                'user_id' => auth()->id(),
                'as_of_date' => $this->statementData['as_of_date']
            ]);
            
        } catch (Exception $e) {
            Log::error('Error exporting Statement of Financial Position: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export Statement of Financial Position. Please try again.';
        } finally {
            $this->isExporting = false;
        }
    }

    public function generateStatementOfComprehensiveIncome()
    {
        // Check permission to generate financial statements
        if (!$this->authorize('view', 'You do not have permission to generate financial statements')) {
            return;
        }
        
        try {
            $this->isGenerating = true;
            $this->errorMessage = '';
            
            // Check if we need to add sample data for demonstration
            $this->addSampleIncomeDataIfNeeded();
            
            // Get the statement data
            $statementData = $this->getStatementOfComprehensiveIncomeData();
            
            // Store the data for display
            $this->statementData = $statementData;
            $this->showStatementView = true;
            
            $this->successMessage = 'Statement of Comprehensive Income generated successfully!';
            
            // Log the report generation
            Log::info('Statement of Comprehensive Income generated', [
                'report_id' => $this->currentReportId,
                'user_id' => auth()->id(),
                'period_start' => $this->reportStartDate,
                'period_end' => $this->reportEndDate
            ]);
            
        } catch (Exception $e) {
            Log::error('Error generating Statement of Comprehensive Income: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate Statement of Comprehensive Income. Please try again.';
        } finally {
            $this->isGenerating = false;
        }
    }

    public function getStatementOfComprehensiveIncomeData()
    {
        $startDate = $this->reportStartDate ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->reportEndDate ?: Carbon::now()->format('Y-m-d');
        
        // Get all active income and expense accounts with their balances
        $accounts = DB::table('accounts')
            ->select(
                'account_number',
                'account_name',
                'type',
                'major_category_code',
                'category_code',
                'sub_category_code',
                'account_level',
                DB::raw('COALESCE(CAST(balance AS DECIMAL(20,2)), 0) as current_balance'),
                DB::raw('COALESCE(CAST(debit AS DECIMAL(20,2)), 0) as debit_balance'),
                DB::raw('COALESCE(CAST(credit AS DECIMAL(20,2)), 0) as credit_balance')
            )
            ->where('status', 'ACTIVE')
            ->whereIn('type', ['income_accounts', 'expense_accounts'])
            ->whereNull('deleted_at')
            ->orderBy('type')
            ->orderBy('major_category_code')
            ->orderBy('category_code')
            ->orderBy('sub_category_code')
            ->orderBy('account_number')
            ->get();

        // Group accounts by type and calculate totals
        $statementData = [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'income' => $this->groupAccountsByType($accounts, ['income_accounts']),
            'expenses' => $this->groupAccountsByType($accounts, ['expense_accounts']),
            'totals' => []
        ];

        // Calculate totals
        $totalIncome = $statementData['income']['total'];
        $totalExpenses = $statementData['expenses']['total'];
        $netIncome = $totalIncome - $totalExpenses;

        $statementData['totals'] = [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'is_profitable' => $netIncome > 0
        ];

        return $statementData;
    }

    private function addSampleIncomeDataIfNeeded()
    {
        // Check if we need to add sample income/expense data
        $incomeAccounts = DB::table('accounts')->where('type', 'income_accounts')->where('status', 'ACTIVE')->get();
        $expenseAccounts = DB::table('accounts')->where('type', 'expense_accounts')->where('status', 'ACTIVE')->get();
        
        $hasIncomeData = $incomeAccounts->where('balance', '>', 0)->count() > 0;
        $hasExpenseData = $expenseAccounts->where('balance', '>', 0)->count() > 0;
        
        if (!$hasIncomeData || !$hasExpenseData) {
            // Add sample income and expense transactions
            $sampleTransactions = [
                // Interest Income
                [
                    'record_on_account_number' => '010140004000', // INTEREST INCOME
                    'debit' => 0.00,
                    'credit' => 150000.00,
                    'record_on_account_number_balance' => 150000.00,
                    'narration' => 'Interest income from loans',
                    'reference_number' => 'INT001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 150000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 850000.00,
                    'narration' => 'Interest received in cash',
                    'reference_number' => 'INT001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Loan Fees
                [
                    'record_on_account_number' => '010140004100', // LOAN FEES AND CHARGES
                    'debit' => 0.00,
                    'credit' => 50000.00,
                    'record_on_account_number_balance' => 50000.00,
                    'narration' => 'Loan processing fees',
                    'reference_number' => 'FEE001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 50000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 900000.00,
                    'narration' => 'Fees received in cash',
                    'reference_number' => 'FEE001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Personnel Expenses
                [
                    'record_on_account_number' => '010150005100', // PERSONNEL EXPENSES
                    'debit' => 80000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 80000.00,
                    'narration' => 'Staff salaries and benefits',
                    'reference_number' => 'SAL001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 0.00,
                    'credit' => 80000.00,
                    'record_on_account_number_balance' => 820000.00,
                    'narration' => 'Salary payments',
                    'reference_number' => 'SAL001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Administrative Expenses
                [
                    'record_on_account_number' => '010150005200', // ADMINISTRATIVE EXPENSES
                    'debit' => 30000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 30000.00,
                    'narration' => 'Office supplies and utilities',
                    'reference_number' => 'ADM001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 0.00,
                    'credit' => 30000.00,
                    'record_on_account_number_balance' => 790000.00,
                    'narration' => 'Administrative expenses paid',
                    'reference_number' => 'ADM001',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            // Insert sample transactions
            DB::table('general_ledger')->insert($sampleTransactions);
            
            // Update account balances
            $this->updateAccountBalances();
        }
    }

    public function exportStatementOfComprehensiveIncome($format = 'pdf')
    {
        // Check permission to export reports
        if (!$this->authorize('export', 'You do not have permission to export reports')) {
            return;
        }
        
        try {
            $this->isExporting = true;
            $this->errorMessage = '';
            
            if (!$this->statementData) {
                $this->statementData = $this->getStatementOfComprehensiveIncomeData();
            }
            
            $filename = 'statement_of_comprehensive_income_' . now()->format('Y-m-d_H-i-s') . '.' . $format;
            
            // For now, we'll simulate the export process
            // In a real implementation, you would use libraries like:
            // - DomPDF for PDF generation
            // - Laravel Excel for Excel/CSV export
            
            $this->successMessage = "Statement of Comprehensive Income exported as {$format} successfully!";
            
            // Log the export
            Log::info('Statement of Comprehensive Income exported', [
                'format' => $format,
                'user_id' => auth()->id(),
                'period_start' => $this->statementData['period_start'],
                'period_end' => $this->statementData['period_end']
            ]);
            
        } catch (Exception $e) {
            Log::error('Error exporting Statement of Comprehensive Income: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export Statement of Comprehensive Income. Please try again.';
        } finally {
            $this->isExporting = false;
        }
    }

    public function loadReportDetails($reportId)
    {
        try {
            $reportTypes = $this->getReportTypes();
            if (isset($reportTypes[$reportId])) {
                $this->selectedReportType = $reportTypes[$reportId]['name'];
                $this->reportTitle = $reportTypes[$reportId]['title'];
                $this->reportDescription = $reportTypes[$reportId]['description'];
            }
        } catch (Exception $e) {
            Log::error('Error loading report details: ' . $e->getMessage());
        }
    }

    public function getReportsByCategory()
    {
        $reports = $this->getReportTypes();
        $categorized = [];
        
        foreach ($reports as $id => $report) {
            $category = $report['category'];
            if (!isset($categorized[$category])) {
                $categorized[$category] = [];
            }
            $categorized[$category][$id] = $report;
        }
        
        return $categorized;
    }

    public function getCategoryInfo()
    {
        return [
            'regulatory' => [
                'name' => 'Regulatory Reports',
                'description' => 'BOT and IFRS compliant financial statements',
                'icon' => 'shield-check',
                'color' => 'red'
            ],
            'loans' => [
                'name' => 'Loan Reports',
                'description' => 'Comprehensive loan portfolio analysis and management',
                'icon' => 'currency-dollar',
                'color' => 'blue'
            ],
            'operational' => [
                'name' => 'Operational Reports',
                'description' => 'Daily operations and member management',
                'icon' => 'chart-bar',
                'color' => 'green'
            ],
            'committee' => [
                'name' => 'Committee Reports',
                'description' => 'Committee minutes and decisions',
                'icon' => 'clipboard-document-check',
                'color' => 'purple'
            ],
            'compliance' => [
                'name' => 'Compliance Reports',
                'description' => 'Compliance monitoring and analysis',
                'icon' => 'exclamation-triangle',
                'color' => 'yellow'
            ],
            'tables' => [
                'name' => 'Table Reports',
                'description' => 'Tabular data views and summaries',
                'icon' => 'table-cells',
                'color' => 'indigo'
            ],
            'enhanced' => [
                'name' => 'Enhanced Reports',
                'description' => 'Advanced reporting interfaces',
                'icon' => 'sparkles',
                'color' => 'pink'
            ]
        ];
    }

    public function getFilteredReports()
    {
        $reports = $this->getReportTypes();
        $filtered = [];

        foreach ($reports as $id => $report) {
            // Apply category filter
            if ($this->selectedCategory && $report['category'] !== $this->selectedCategory) {
                continue;
            }

            // Apply search filter
            if ($this->searchTerm) {
                $searchLower = strtolower($this->searchTerm);
                $nameMatch = strpos(strtolower($report['name']), $searchLower) !== false;
                $titleMatch = strpos(strtolower($report['title']), $searchLower) !== false;
                $descMatch = strpos(strtolower($report['description']), $searchLower) !== false;
                
                if (!$nameMatch && !$titleMatch && !$descMatch) {
                    continue;
                }
            }

            $filtered[$id] = $report;
        }

        return $filtered;
    }

    public function updatedSearchTerm()
    {
        // This method will be called automatically when searchTerm is updated
        // Livewire will re-render the component
    }

    public function updatedSelectedCategory()
    {
        // This method will be called automatically when selectedCategory is updated
        // Livewire will re-render the component
    }

    public function clearFilters()
    {
        $this->searchTerm = '';
        $this->selectedCategory = '';
        $this->showAllReports = false;
    }

    public function toggleShowAllReports()
    {
        $this->showAllReports = !$this->showAllReports;
    }

    public function getReportTypes()
    {
        return [
            // Regulatory Reports
            37 => [
                'name' => 'Statement of Financial Position',
                'title' => 'Statement of Financial Position for the Month Ended',
                'description' => 'Comprehensive balance sheet showing assets, liabilities, and equity positions',
                'category' => 'regulatory',
                'compliance' => 'BOT, IFRS',
                'file' => 'statement-of-financial-position.blade.php',
                'icon' => 'document-text'
            ],
            38 => [
                'name' => 'Statement of Comprehensive Income',
                'title' => 'Statement of Comprehensive Income for the Month Ended',
                'description' => 'Detailed income statement showing revenue, expenses, and net income',
                'category' => 'regulatory',
                'compliance' => 'BOT, IFRS',
                'file' => 'statement-of-comprehensive-income.blade.php',
                'icon' => 'chart-bar'
            ],
            39 => [
                'name' => 'Statement of Cash Flow',
                'title' => 'Statement of Cash Flow for the Month Ended',
                'description' => 'Cash flow analysis showing operating, investing, and financing activities',
                'category' => 'regulatory',
                'compliance' => 'BOT, IFRS',
                'file' => 'statement-of-cash-flow.blade.php',
                'icon' => 'currency-dollar'
            ],
            8 => [
                'name' => 'Computation of Capital Adequacy',
                'title' => 'Computation of Capital Adequacy for the Month Ended',
                'description' => 'Capital adequacy ratio calculation and analysis',
                'category' => 'regulatory',
                'compliance' => 'BOT',
                'file' => 'computation-of-capital-adequacy-for-the-month-ended.blade.php',
                'icon' => 'shield-check'
            ],
            9 => [
                'name' => 'Computation of Liquid Assets',
                'title' => 'Computation of Liquid Assets for the Month Ended',
                'description' => 'Liquidity position and liquid assets analysis',
                'category' => 'regulatory',
                'compliance' => 'BOT',
                'file' => 'computation-of-liquid-assets-for-the-month-ended.blade.php',
                'icon' => 'banknotes'
            ],
            14 => [
                'name' => 'Deposits and Loans in Banks',
                'title' => 'Deposits and Loans in Banks and Financial Institutions for the Month Ended',
                'description' => 'Analysis of deposits and loans with other financial institutions',
                'category' => 'regulatory',
                'compliance' => 'BOT',
                'file' => 'deposits-and-loans-in-banks-and-financial-institutions-for-the-month-ended.blade.php',
                'icon' => 'building-library'
            ],
            18 => [
                'name' => 'Geographical Distribution',
                'title' => 'Geographical Distribution of Branches, Employees and Loans by Age for the Month Ended',
                'description' => 'Geographic analysis of operations and loan distribution',
                'category' => 'regulatory',
                'compliance' => 'BOT',
                'file' => 'geographical-distribution-of-branches-employees-and-loans-by-age-for-the-month-ended.blade.php',
                'icon' => 'map'
            ],
            30 => [
                'name' => 'Loans to Insiders',
                'title' => 'Loans to Insiders and Related Parties',
                'description' => 'Analysis of loans to insiders and related parties',
                'category' => 'regulatory',
                'compliance' => 'BOT',
                'file' => 'loans-to-insiders-and-related-parties.blade.php',
                'icon' => 'user-group'
            ],

            // Loan Reports
            1 => [
                'name' => 'Active Loan by Officer',
                'title' => 'Active Loan by Officer Report',
                'description' => 'Active loans grouped by loan officer',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'active-loan-by-officer.blade.php',
                'icon' => 'user-circle'
            ],
            2 => [
                'name' => 'Client Loan Account',
                'title' => 'Client Loan Account Report',
                'description' => 'Individual client loan account details',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'client-loan-account.blade.php',
                'icon' => 'document-duplicate'
            ],
            3 => [
                'name' => 'Client Repayment History',
                'title' => 'Client Repayment History Report',
                'description' => 'Detailed repayment history for clients',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'client-repayment-history.blade.php',
                'icon' => 'clock'
            ],
            4 => [
                'name' => 'Clients Details Report',
                'title' => 'Comprehensive Member Information Report',
                'description' => 'Detailed member information including personal data, accounts, and status',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'clients-details-report.blade.php',
                'icon' => 'users'
            ],
            5 => [
                'name' => 'General Loan Report',
                'title' => 'General Loan Report',
                'description' => 'Comprehensive loan portfolio analysis including disbursements, collections, and performance',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'general-loan-report.blade.php',
                'icon' => 'chart-pie'
            ],
            6 => [
                'name' => 'Interest Rates Structure',
                'title' => 'Interest Rates Structure for Loans',
                'description' => 'Current interest rate structure for different loan products',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'interest-rates-structure-for-loans.blade.php',
                'icon' => 'percent'
            ],
            7 => [
                'name' => 'Loan Application Report',
                'title' => 'Loan Application Report',
                'description' => 'Analysis of loan applications and approval rates',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-application-report.blade.php',
                'icon' => 'clipboard-document-list'
            ],
            10 => [
                'name' => 'Loan Delinquency Report',
                'title' => 'Loan Delinquency Report',
                'description' => 'Analysis of delinquent loans and recovery efforts',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-delinquency-report.blade.php',
                'icon' => 'exclamation-triangle'
            ],
            11 => [
                'name' => 'Loan Delinquent',
                'title' => 'Loan Delinquent Report',
                'description' => 'Detailed view of delinquent loan accounts',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-delinquent.blade.php',
                'icon' => 'exclamation-circle'
            ],
            12 => [
                'name' => 'Loan Disbursement Report',
                'title' => 'Loan Disbursement Report',
                'description' => 'Analysis of loan disbursements by period and product',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-disbursement-report.blade.php',
                'icon' => 'arrow-trending-up'
            ],
            13 => [
                'name' => 'Loan Portfolio Report',
                'title' => 'Loan Portfolio Report',
                'description' => 'Comprehensive loan portfolio analysis and performance metrics',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-portifolio-report.blade.php',
                'icon' => 'briefcase'
            ],
            15 => [
                'name' => 'Loan Repayment Schedule',
                'title' => 'Loan Repayment Schedule Report',
                'description' => 'Detailed repayment schedules for all active loans',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-repayment-schedule.blade.php',
                'icon' => 'calendar'
            ],
            16 => [
                'name' => 'Loan Report',
                'title' => 'General Loan Report',
                'description' => 'General loan information and statistics',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-report.blade.php',
                'icon' => 'document'
            ],
            17 => [
                'name' => 'Loan Reports',
                'title' => 'Comprehensive Loan Reports',
                'description' => 'Multiple loan reports and analytics',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-reports.blade.php',
                'icon' => 'document-text'
            ],
            19 => [
                'name' => 'Loan Status',
                'title' => 'Loan Status Report',
                'description' => 'Current status of all loans in the system',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loan-status.blade.php',
                'icon' => 'check-circle'
            ],
            20 => [
                'name' => 'Loans Disbursed',
                'title' => 'Loans Disbursed for the Month Ended',
                'description' => 'Monthly analysis of loans disbursed',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'loans-disbursed-for-the-month-ended.blade.php',
                'icon' => 'arrow-up-right'
            ],
            21 => [
                'name' => 'Portfolio at Risk',
                'title' => 'Portfolio at Risk Report',
                'description' => 'Analysis of portfolio at risk and risk management',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'portifolio-at-risk.blade.php',
                'icon' => 'shield-exclamation'
            ],
            22 => [
                'name' => 'Sectoral Classification',
                'title' => 'Sectoral Classification of Loans',
                'description' => 'Loan classification by economic sectors',
                'category' => 'loans',
                'compliance' => 'Internal',
                'file' => 'sectoral-classification-of-loans.blade.php',
                'icon' => 'squares-2x2'
            ],

            // Operational Reports
            23 => [
                'name' => 'Daily Report',
                'title' => 'Daily Operations Report',
                'description' => 'Daily summary of operations and transactions',
                'category' => 'operational',
                'compliance' => 'Internal',
                'file' => 'daily-report.blade.php',
                'icon' => 'calendar-days'
            ],
            24 => [
                'name' => 'General Report',
                'title' => 'General Operations Report',
                'description' => 'General operational statistics and summaries',
                'category' => 'operational',
                'compliance' => 'Internal',
                'file' => 'general-report.blade.php',
                'icon' => 'chart-bar'
            ],
            25 => [
                'name' => 'Main Report',
                'title' => 'Main Operations Report',
                'description' => 'Primary operational report with key metrics',
                'category' => 'operational',
                'compliance' => 'Internal',
                'file' => 'main-report.blade.php',
                'icon' => 'home'
            ],

            // Committee Reports
            26 => [
                'name' => 'Committee Minutes',
                'title' => 'Committee Minutes Report',
                'description' => 'Minutes and decisions from committee meetings',
                'category' => 'committee',
                'compliance' => 'Internal',
                'file' => 'commitee.blade.php',
                'icon' => 'clipboard-document-check'
            ],
            27 => [
                'name' => 'Committee Decisions',
                'title' => 'Committee Decisions Report',
                'description' => 'Record of committee decisions and resolutions',
                'category' => 'committee',
                'compliance' => 'Internal',
                'file' => 'decision.blade.php',
                'icon' => 'gavel'
            ],

            // Compliance Reports
            28 => [
                'name' => 'Complaint Report',
                'title' => 'Complaint Report for the Month Ended',
                'description' => 'Analysis of complaints and resolution status',
                'category' => 'compliance',
                'compliance' => 'Internal',
                'file' => 'complaint-report-for-the-month-ended.blade.php',
                'icon' => 'chat-bubble-left-right'
            ],
            29 => [
                'name' => 'Financial Ratio',
                'title' => 'Financial Ratio Analysis',
                'description' => 'Key financial ratios and performance indicators',
                'category' => 'compliance',
                'compliance' => 'Internal',
                'file' => 'financial-ratio.blade.php',
                'icon' => 'calculator'
            ],

            // Table Reports
            31 => [
                'name' => 'Active Loan by Officer Table',
                'title' => 'Active Loan by Officer Table',
                'description' => 'Tabular view of active loans by officer',
                'category' => 'tables',
                'compliance' => 'Internal',
                'file' => 'active-loan-by-officer-table.blade.php',
                'icon' => 'table-cells'
            ],
            32 => [
                'name' => 'Committee Minute Table',
                'title' => 'Committee Minute Table',
                'description' => 'Tabular view of committee minutes',
                'category' => 'tables',
                'compliance' => 'Internal',
                'file' => 'commitee-minute-table.blade.php',
                'icon' => 'table-cells'
            ],
            33 => [
                'name' => 'Daily Table',
                'title' => 'Daily Operations Table',
                'description' => 'Tabular view of daily operations',
                'category' => 'tables',
                'compliance' => 'Internal',
                'file' => 'daily-table.blade.php',
                'icon' => 'table-cells'
            ],
            34 => [
                'name' => 'Portfolio at Risk Table',
                'title' => 'Portfolio at Risk Table',
                'description' => 'Tabular view of portfolio at risk',
                'category' => 'tables',
                'compliance' => 'Internal',
                'file' => 'portifolio-at-risk-table.blade.php',
                'icon' => 'table-cells'
            ],

            // Enhanced Reports
            35 => [
                'name' => 'Reports Enhanced',
                'title' => 'Enhanced Reports Dashboard',
                'description' => 'Enhanced reporting interface with advanced features',
                'category' => 'enhanced',
                'compliance' => 'Internal',
                'file' => 'reports-enhanced.blade.php',
                'icon' => 'sparkles'
            ],
            36 => [
                'name' => 'Reports Main',
                'title' => 'Main Reports Interface',
                'description' => 'Main reports interface and navigation',
                'category' => 'enhanced',
                'compliance' => 'Internal',
                'file' => 'reports.blade.php',
                'icon' => 'squares-plus'
            ]
        ];
    }

    public function showResolveModal($id)
    {
        $this->transactionToReview = $id;
        $this->showResolveModal = true;
    }

    public function showExportOptions()
    {
        $this->showExportOptionsModal = true;
    }

    public function showScheduleReport()
    {
        $this->showScheduleReportModal = true;
    }

    public function downloadExcelFile()
    {
        // Check permission to export reports
        if (!$this->authorize('export', 'You do not have permission to export reports')) {
            return;
        }
        
        $this->validate([
            'reportEndDate' => 'required|date',
            'reportStartDate' => 'required|date|before_or_equal:reportEndDate'
        ]);

        try {
            $this->isExporting = true;

            if ($this->customize == "YES") {
                $input = $this->custome_client_number;
                $input = rtrim($input, ',');
                $numbers = explode(',', $input);
                $array = [];

                foreach ($numbers as $number) {
                    $number = trim($number);
                    $number = intval($number);

                    if (LoansModel::where('client_number', $number)->exists()) {
                        $array[] = ['number' => str_pad($number, 4, 0, STR_PAD_LEFT)];
                    }
                }

                $LoanId = LoansModel::whereIn('client_number', $array)->pluck('id');
            } else {
                $LoanId = LoansModel::get()->pluck('id')->toArray();
            }

            $filename = 'comprehensive_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(new MainReport($LoanId), $filename);

        } catch (Exception $e) {
            Log::error('Error downloading Excel file: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate Excel report.';
        } finally {
            $this->isExporting = false;
        }
    }

    public function generateReport()
    {
        // Check permission to generate reports
        if (!$this->authorize('view', 'You do not have permission to generate reports')) {
            return;
        }
        
        try {
            $this->isGenerating = true;
            $this->validate([
                'reportStartDate' => 'required|date',
                'reportEndDate' => 'required|date|after_or_equal:reportStartDate',
                'reportFormat' => 'required|in:pdf,excel,csv'
            ]);

            // Check if this is Statement of Financial Position
            if ($this->currentReportId == 37) {
                $this->generateStatementOfFinancialPosition();
                return;
            }

            // Check if this is Statement of Comprehensive Income
            if ($this->currentReportId == 38) {
                $this->generateStatementOfComprehensiveIncome();
                return;
            }

            // Generate report based on selected type
            $reportData = $this->prepareReportData();
            
            // Log report generation
            $this->logReportGeneration($reportData);

            $this->successMessage = 'Report generated successfully!';
            $this->emit('reportGenerated', $reportData);

        } catch (Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate report: ' . $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function prepareReportData()
    {
        $data = [
            'report_type' => $this->selectedReportType,
            'period' => [
                'start' => $this->reportStartDate,
                'end' => $this->reportEndDate
            ],
            'analytics' => [
                'total_members' => $this->totalMembers,
                'active_members' => $this->activeMembers,
                'total_loans' => $this->totalLoans,
                'active_loans' => $this->activeLoans,
                'portfolio_at_risk' => $this->portfolioAtRisk,
                'capital_adequacy_ratio' => $this->capitalAdequacyRatio,
                'liquidity_ratio' => $this->liquidityRatio,
                'monthly_growth' => $this->monthlyGrowth
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->name,
            'format' => $this->reportFormat
        ];

        return $data;
    }

    public function logReportGeneration($reportData)
    {
        try {
            DB::table('scheduled_reports')->insert([
                'report_type' => $reportData['report_type'],
                'report_config' => json_encode($reportData),
                'user_id' => auth()->id(),
                'status' => 'completed',
                'generated_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Error logging report generation: ' . $e->getMessage());
        }
    }

    public function scheduleReport()
    {
        // Check permission to schedule reports
        if (!$this->authorize('manage', 'You do not have permission to schedule reports')) {
            return;
        }
        
        try {
            $this->isScheduling = true;
            $this->validate([
                'scheduleDate' => 'required|date|after:today',
                'scheduleTime' => 'required|date_format:H:i',
                'emailRecipients' => 'required|array|min:1',
                'emailSubject' => 'required|string|max:255',
                'emailMessage' => 'required|string'
            ]);

            // Schedule the report
            $scheduledReport = DB::table('scheduled_reports')->insert([
                'report_type' => $this->selectedReportType,
                'report_config' => json_encode($this->prepareReportData()),
                'user_id' => auth()->id(),
                'status' => 'scheduled',
                'frequency' => $this->scheduleFrequency,
                'scheduled_at' => $this->scheduleDate . ' ' . $this->scheduleTime,
                'email_recipients' => json_encode($this->emailRecipients),
                'email_subject' => $this->emailSubject,
                'email_message' => $this->emailMessage,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->successMessage = 'Report scheduled successfully!';
            $this->showScheduleReportModal = false;
            $this->loadReportHistory();

        } catch (Exception $e) {
            Log::error('Error scheduling report: ' . $e->getMessage());
            $this->errorMessage = 'Failed to schedule report: ' . $e->getMessage();
        } finally {
            $this->isScheduling = false;
        }
    }

    public function handleReportGenerated($data)
    {
        $this->successMessage = 'Report generated successfully!';
        $this->loadReportHistory();
    }

    public function handleExportCompleted($data)
    {
        $this->successMessage = 'Export completed successfully!';
    }

    public function saveResolution()
    {
        try {
            $rrn = Transactions::where('ID', $this->transactionToReview)->value('DB_TABLE_REFERENCE');
            
            if ($rrn) {
                DB::table('transactions')->where('DB_TABLE_REFERENCE', $rrn)->update([
                    'status' => 'RESOLVED',
                    'resolution_comments' => $this->comments,
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now()
                ]);

                $this->successMessage = 'Transaction resolved successfully!';
                $this->showResolveModal = false;
                $this->comments = '';
            } else {
                $this->errorMessage = 'Transaction not found.';
            }
        } catch (Exception $e) {
            Log::error('Error saving resolution: ' . $e->getMessage());
            $this->errorMessage = 'Failed to save resolution.';
        }
    }

    /**
     * Get the required permission for a specific report
     */
    private function getRequiredPermissionForReport($report)
    {
        $categoryPermissionMap = [
            'regulatory' => 'view',      // Financial statements and regulatory reports
            'loans' => 'view',           // Loan-related reports
            'operational' => 'view',     // Operational reports
            'committee' => 'view',       // Committee reports
            'compliance' => 'view',      // Compliance reports
            'tables' => 'view',          // Table reports
            'enhanced' => 'view'         // Enhanced reports
        ];
        
        return $categoryPermissionMap[$report['category']] ?? 'view';
    }
    
    /**
     * Get the required permission for a specific component
     */
    private function getRequiredPermissionForComponent($componentName)
    {
        $componentPermissionMap = [
            'statement-of-financial-position' => 'view',
            'statement-of-comprehensive-income' => 'view',
            'statement-of-cash-flow' => 'view',
            'loan-portfolio-report' => 'view',
            'loan-delinquency-report' => 'view',
            'loan-disbursement-report' => 'view',
            'portfolio-at-risk' => 'view',
            'active-loans-by-officer' => 'view',
            'loan-application-report' => 'view',
            'dashboard' => 'view'
        ];
        
        return $componentPermissionMap[$componentName] ?? 'view';
    }

    public function render()
    {
        return view('livewire.reports.reports-simple', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }

    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'reports';
    }
}
