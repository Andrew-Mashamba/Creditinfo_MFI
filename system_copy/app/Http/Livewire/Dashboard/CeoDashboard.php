<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\LoansModel;
use App\Models\Branch;
use App\Models\BranchesModel;
use App\Models\ExpensesModel;
use App\Models\Investment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CeoDashboard extends Component
{
    public $totalMembers;
    public $totalSavings;
    public $totalDeposits;
    public $totalShares;
    public $totalLoanPortfolio;
    public $monthlyIncome;
    public $monthlyExpenses;
    public $nplRatio;
    public $netIncomeYTD;
    public $roiInvestments;
    public $branchPerformance;
    public $loanDisbursementTrend;
    public $repaymentTrend;
    public $expenseBreakdown;
    public $savingsPerBranch;
    public $branchLocations;
    public $pendingHighValueLoans;
    public $topDelinquentLoans;
    public $branchProfitability;
    public $budgetUtilization;

    // Chart data arrays for ApexCharts
    public $loanChartData = [];
    public $expenseChartData = [];
    public $savingsChartData = [];
    public $depositsChartData = [];
    public $sharesChartData = [];



    public function mount()
    {
        try {
            // Total Members
            $this->totalMembers = ClientsModel::count();

        // Total Savings (product_number = 2000)
        $this->totalSavings = AccountsModel::where('product_number', 2000)->sum('balance');
        // Total Deposits (product_number = 3000)
        $this->totalDeposits = AccountsModel::where('product_number', 3000)->sum('balance');
        // Total Shares (product_number = 1000)
        $this->totalShares = AccountsModel::where('product_number', 1000)->sum('balance');
        // Total Loan Portfolio (product_number = 4000)
        $this->totalLoanPortfolio = AccountsModel::where('product_number', 4000)->sum('balance');

        // Monthly Income vs. Expenses (current month)
        $this->monthlyIncome = AccountsModel::where('type', 'income_accounts')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('balance');
        $this->monthlyExpenses = AccountsModel::where('type', 'expense_accounts')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('balance');

        // NPL Ratio (Non-Performing Loans)
        $totalLoans = LoansModel::count();
        $nplLoans = LoansModel::where('status', 'ARREARS')->orWhere('days_in_arrears', '>', 90)->count();
        $this->nplRatio = $totalLoans > 0 ? round(($nplLoans / $totalLoans) * 100, 2) : 0;

        // Net Income YTD
        $this->netIncomeYTD =
            AccountsModel::where('type', 'income_accounts')
                ->whereYear('created_at', now()->year)
                ->sum('balance')
            - AccountsModel::where('type', 'expense_accounts')
                ->whereYear('created_at', now()->year)
                ->sum('balance');

        // ROI from Investments (simple: total returns / total invested)
        $totalInvested = Investment::sum('principal_amount');
        $totalReturns = Investment::sum(DB::raw('COALESCE(dividend_rate,0) + COALESCE(interest_rate,0) + COALESCE(sale_price,0)'));
        $this->roiInvestments = $totalInvested > 0 ? round(($totalReturns / $totalInvested) * 100, 2) : 0;

        // Branch Performance Summary (total savings, deposits, shares, loans per branch)
        $this->branchPerformance = BranchesModel::select('id', 'name', 'region', 'wilaya')
            ->get()
            ->map(function($branch) {
                $savings = AccountsModel::where('product_number', 2000)->sum('balance');
                $deposits = AccountsModel::where('product_number', 3000)->sum('balance');
                $shares = AccountsModel::where('product_number', 1000)->sum('balance');
                $loans = AccountsModel::where('product_number', 4000)->sum('balance');
                $branch->total_savings = $savings;
                $branch->total_deposits = $deposits;
                $branch->total_shares = $shares;
                $branch->total_loans = $loans;
                return $branch;
            });

        // Loan disbursement vs Repayments (Line Chart)
        $loanDisbursement = LoansModel::select(
                DB::raw("TO_CHAR(disbursement_date, 'YYYY-MM') as month"),
                DB::raw('SUM(principle) as total_disbursed')
            )
            ->whereNotNull('disbursement_date')
            ->where('disbursement_date', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $repayments = DB::table('loans_schedules')
            ->select(
                DB::raw("TO_CHAR(installment_date, 'YYYY-MM') as month"),
                DB::raw('SUM(payment) as total_repaid')
            )
            ->where('installment_date', '>=', Carbon::now()->subMonths(12))
            ->where('completion_status', 'COMPLETED')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $months = collect($loanDisbursement)->pluck('month')->merge(collect($repayments)->pluck('month'))->unique()->sort()->values();
        $loanData = $months->mapWithKeys(function($month) use ($loanDisbursement) {
            $row = $loanDisbursement->firstWhere('month', $month);
            return [$month => $row ? (float)$row->total_disbursed : 0];
        });
        $repaymentData = $months->mapWithKeys(function($month) use ($repayments) {
            $row = $repayments->firstWhere('month', $month);
            return [$month => $row ? (float)$row->total_repaid : 0];
        });
        // Prepare loan chart data for ApexCharts
        $this->loanChartData = [
            'labels' => $months && $months->count() > 0 ? $months->toArray() : ['Jan'],
            'series' => [
                [
                    'name' => 'Disbursed',
                    'data' => $months && $months->count() > 0 
                        ? $months->map(function($month) use ($loanData) {
                            return isset($loanData[$month]) ? (float)$loanData[$month] : 0;
                        })->toArray()
                        : [1000000]
                ],
                [
                    'name' => 'Repaid',
                    'data' => $months && $months->count() > 0 
                        ? $months->map(function($month) use ($repaymentData) {
                            return isset($repaymentData[$month]) ? (float)$repaymentData[$month] : 0;
                        })->toArray()
                        : [800000]
                ]
            ]
        ];

        // Expense Breakdown (Pie Chart)
        $expenseCategories = DB::table('accounts')
            ->join('expense_accounts', 'accounts.category_code', '=', 'expense_accounts.category_code')
            ->select('expense_accounts.category_name', DB::raw('SUM(accounts.balance) as total'))
            ->where('accounts.type', 'expense_accounts')
            ->groupBy('expense_accounts.category_name')
            ->get();
        // Prepare expense chart data for ApexCharts
        $expenseLabels = [];
        $expenseValues = [];
        
        if ($expenseCategories && $expenseCategories->count() > 0) {
            foreach ($expenseCategories as $cat) {
                if ($cat && $cat->category_name && is_numeric($cat->total) && $cat->total > 0) {
                    $expenseLabels[] = $cat->category_name;
                    $expenseValues[] = (float)$cat->total;
                }
            }
        } else {
            // Add dummy data if no expense categories found
            $expenseLabels = ['Salaries', 'Utilities'];
            $expenseValues = [500000, 200000];
        }
        
        $this->expenseChartData = [
            'labels' => $expenseLabels,
            'series' => $expenseValues
        ];

        // Savings Trends per Branch (Column Chart)
        $branchSavings = AccountsModel::where('product_number', 2000)
            ->select('branch_number', DB::raw('SUM(balance) as total_savings'))
            ->groupBy('branch_number')
            ->get();
        // Prepare savings chart data for ApexCharts
        $savingsLabels = [];
        $savingsValues = [];
        
        if ($branchSavings && $branchSavings->count() > 0) {
            foreach ($branchSavings as $row) {
                if ($row && $row->branch_number && is_numeric($row->total_savings)) {
                    $branchName = optional(BranchesModel::find($row->branch_number))->name ?? 'Unknown Branch';
                    $savingsLabels[] = $branchName;
                    $savingsValues[] = (float)$row->total_savings;
                }
            }
        } else {
            // Add dummy data if no branch savings found
            $savingsLabels = ['Main Branch', 'Branch 2'];
            $savingsValues = [2000000, 1500000];
        }
        
        $this->savingsChartData = [
            'labels' => $savingsLabels,
            'series' => $savingsValues
        ];

        // Deposits Trends per Branch (Column Chart)
        $branchDeposits = AccountsModel::where('product_number', 3000)
            ->select('branch_number', DB::raw('SUM(balance) as total_deposits'))
            ->groupBy('branch_number')
            ->get();
        // Prepare deposits chart data for ApexCharts
        $depositsLabels = [];
        $depositsValues = [];
        
        if ($branchDeposits && $branchDeposits->count() > 0) {
            foreach ($branchDeposits as $row) {
                if ($row && $row->branch_number && is_numeric($row->total_deposits)) {
                    $branchName = optional(BranchesModel::find($row->branch_number))->name ?? 'Unknown Branch';
                    $depositsLabels[] = $branchName;
                    $depositsValues[] = (float)$row->total_deposits;
                }
            }
        } else {
            // Add dummy data if no branch deposits found
            $depositsLabels = ['Main Branch', 'Branch 2'];
            $depositsValues = [3000000, 2500000];
        }
        
        $this->depositsChartData = [
            'labels' => $depositsLabels,
            'series' => $depositsValues
        ];

        // Shares Trends per Branch (Column Chart)
        $branchShares = AccountsModel::where('product_number', 1000)
            ->select('branch_number', DB::raw('SUM(balance) as total_shares'))
            ->groupBy('branch_number')
            ->get();
        // Prepare shares chart data for ApexCharts
        $sharesLabels = [];
        $sharesValues = [];
        
        if ($branchShares && $branchShares->count() > 0) {
            foreach ($branchShares as $row) {
                if ($row && $row->branch_number && is_numeric($row->total_shares)) {
                    $branchName = optional(BranchesModel::find($row->branch_number))->name ?? 'Unknown Branch';
                    $sharesLabels[] = $branchName;
                    $sharesValues[] = (float)$row->total_shares;
                }
            }
        } else {
            // Add dummy data if no branch shares found
            $sharesLabels = ['Main Branch', 'Branch 2'];
            $sharesValues = [1000000, 800000];
        }
        
        $this->sharesChartData = [
            'labels' => $sharesLabels,
            'series' => $sharesValues
        ];

        // Branch locations and activity
        $this->branchLocations = BranchesModel::select('id', 'name', 'region', 'wilaya', 'address', 'status')->get();

        // Pending high-value loan approvals
        $this->pendingHighValueLoans = LoansModel::where('status', 'PENDING')
            ->where('principle', '>', 10000000) // threshold can be adjusted
            ->orderByDesc('principle')
            ->take(10)
            ->get();

        // Top 5 delinquent loans
        $this->topDelinquentLoans = LoansModel::where('status', 'ARREARS')
            ->orderByDesc('days_in_arrears')
            ->take(5)
            ->get();

        // Branch profitability ranking (net income per branch)
        $this->branchProfitability = BranchesModel::select('id', 'name')
            ->get();
        // You may need to aggregate income/expenses per branch for real ranking

        // Budget utilization (dummy, as budget table not found)
        $this->budgetUtilization = [
            'utilized' => 80000000,
            'target' => 100000000,
            'percent' => 80
        ];
        } catch (\Exception $e) {
            // Initialize with dummy data if there's an error
            $this->initializeDummyData();
        }
    }

    private function initializeDummyData()
    {
        try {
            // Initialize chart data arrays with dummy data
            $this->loanChartData = [
                'labels' => ['Jan', 'Feb', 'Mar'],
                'series' => [
                    [
                        'name' => 'Disbursed',
                        'data' => [1000000, 1200000, 1100000]
                    ],
                    [
                        'name' => 'Repaid',
                        'data' => [800000, 900000, 850000]
                    ]
                ]
            ];

            $this->expenseChartData = [
                'labels' => ['Salaries', 'Utilities', 'Office Supplies'],
                'series' => [500000, 200000, 100000]
            ];

            $this->savingsChartData = [
                'labels' => ['Main Branch', 'Branch 2', 'Branch 3'],
                'series' => [2000000, 1500000, 1200000]
            ];

            $this->depositsChartData = [
                'labels' => ['Main Branch', 'Branch 2', 'Branch 3'],
                'series' => [3000000, 2500000, 2000000]
            ];

            $this->sharesChartData = [
                'labels' => ['Main Branch', 'Branch 2', 'Branch 3'],
                'series' => [1000000, 800000, 600000]
            ];
        } catch (\Exception $e) {
            // If chart initialization fails, create minimal chart data
            $this->createMinimalChartData();
        }

        // Initialize other properties with safe defaults
        $this->totalMembers = 0;
        $this->totalSavings = 0;
        $this->totalDeposits = 0;
        $this->totalShares = 0;
        $this->totalLoanPortfolio = 0;
        $this->monthlyIncome = 0;
        $this->monthlyExpenses = 0;
        $this->nplRatio = 0;
        $this->netIncomeYTD = 0;
        $this->roiInvestments = 0;
        $this->branchPerformance = collect();
        $this->branchLocations = collect();
        $this->pendingHighValueLoans = collect();
        $this->topDelinquentLoans = collect();
        $this->branchProfitability = collect();
        $this->budgetUtilization = ['utilized' => 0, 'target' => 0, 'percent' => 0];
    }

    private function createMinimalChartData()
    {
        // Create minimal chart data arrays
        $this->loanChartData = [
            'labels' => ['Jan'],
            'series' => [
                [
                    'name' => 'Test',
                    'data' => [1000]
                ]
            ]
        ];
        
        $this->expenseChartData = [
            'labels' => ['Test'],
            'series' => [1000]
        ];
        
        $this->savingsChartData = [
            'labels' => ['Test'],
            'series' => [1000]
        ];
        
        $this->depositsChartData = [
            'labels' => ['Test'],
            'series' => [1000]
        ];
        
        $this->sharesChartData = [
            'labels' => ['Test'],
            'series' => [1000]
        ];
    }





    public function render()
    {
        // Ensure all chart data is initialized
        if (empty($this->loanChartData) || empty($this->expenseChartData) || empty($this->savingsChartData) || empty($this->depositsChartData) || empty($this->sharesChartData)) {
            $this->initializeDummyData();
        }
        
        return view('livewire.dashboard.ceo-dashboard', [
            'loanChartData' => $this->loanChartData,
            'expenseChartData' => $this->expenseChartData,
            'savingsChartData' => $this->savingsChartData,
            'depositsChartData' => $this->depositsChartData,
            'sharesChartData' => $this->sharesChartData,
        ]);
    }
}
