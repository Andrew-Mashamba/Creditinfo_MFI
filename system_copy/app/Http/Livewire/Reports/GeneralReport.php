<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use App\Models\BranchesModel;
use App\Models\LoanSubProduct;
use App\Models\loans_schedules;
use App\Models\AccountsModel;
use App\Models\Loan;
use App\Models\general_ledger;
use App\Exports\GeneralReportExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Exception;

class GeneralReport extends Component
{
    public $reportPeriod = 'monthly';
    public $selectedMonth;
    public $selectedYear;
    public $selectedBranch = 'all';
    public $branches = [];
    
    // General Overview Data
    public $overviewData = [];
    public $clientSummary = [];
    public $loanSummary = [];
    public $depositSummary = [];
    public $branchPerformance = [];
    public $productPerformance = [];
    public $staffPerformance = [];
    public $financialSummary = [];
    public $operationalMetrics = [];
    
    // Statistics
    public $totalClients = 0;
    public $totalLoans = 0;
    public $totalLoanAmount = 0;
    public $totalDeposits = 0;
    public $totalAssets = 0;
    public $totalLiabilities = 0;
    public $netWorth = 0;
    public $activeBranches = 0;
    public $activeStaff = 0;
    
    protected $rules = [
        'reportPeriod' => 'required|string',
        'selectedMonth' => 'required|string',
        'selectedYear' => 'required|string',
        'selectedBranch' => 'required|string'
    ];

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->format('m');
        $this->selectedYear = Carbon::now()->format('Y');
        $this->loadBranches();
        $this->loadGeneralReportData();
    }

    public function loadBranches()
    {
        try {
            $this->branches = BranchesModel::select('id', 'branch_name')
                ->orderBy('branch_name')
                ->get()
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error loading branches: ' . $e->getMessage());
            $this->branches = [];
        }
    }

    public function loadGeneralReportData()
    {
        try {
            $this->loadOverviewData();
            $this->loadClientSummary();
            $this->loadLoanSummary();
            $this->loadDepositSummary();
            $this->loadBranchPerformance();
            $this->loadProductPerformance();
            $this->loadStaffPerformance();
            $this->loadFinancialSummary();
            $this->loadOperationalMetrics();
            $this->calculateStatistics();
        } catch (Exception $e) {
            Log::error('Error loading General Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadOverviewData()
    {
        $dateFilter = $this->getDateFilter();
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];

        // Get total members
        $totalMembers = ClientsModel::when($branchFilter, function($query) use ($branchFilter) {
            return $query->where($branchFilter);
        })->count();

        // Get active members
        $activeMembers = ClientsModel::where('client_status', 'ACTIVE')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        // Get new members this period
        $newMembersThisPeriod = ClientsModel::when($dateFilter, function($query) use ($dateFilter) {
            return $query->whereBetween('created_at', $dateFilter);
        })->when($branchFilter, function($query) use ($branchFilter) {
            return $query->where($branchFilter);
        })->count();

        // Get total loans outstanding
        $totalLoansOutstanding = LoansModel::whereIn('status', ['ACTIVE', 'APPROVED'])
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        // Get total loan portfolio
        $totalLoanPortfolio = LoansModel::whereIn('status', ['ACTIVE', 'APPROVED'])
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->sum('principle');

        // Get total deposits (from accounts)
        $totalDeposits = AccountsModel::whereIn('account_type', ['SAVINGS', 'CURRENT', 'FIXED_DEPOSIT'])
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->whereHas('client', function($q) use ($branchFilter) {
                    $q->where($branchFilter);
                });
            })->sum('account_balance');

        // Get number of branches
        $numberOfBranches = BranchesModel::where('status', 'ACTIVE')->count();

        // Get number of staff
        $numberOfStaff = Employee::count();

        // Calculate loan approval rate
        $totalLoanApplications = LoansModel::when($dateFilter, function($query) use ($dateFilter) {
            return $query->whereBetween('created_at', $dateFilter);
        })->when($branchFilter, function($query) use ($branchFilter) {
            return $query->where($branchFilter);
        })->count();

        $approvedLoans = LoansModel::where('status', 'APPROVED')
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereBetween('created_at', $dateFilter);
            })->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $loanApprovalRate = $totalLoanApplications > 0 ? ($approvedLoans / $totalLoanApplications) * 100 : 0;

        // Get financial data from general ledger
        $totalAssets = general_ledger::where('account_type', 'ASSET')->sum('amount');
        $totalLiabilities = general_ledger::where('account_type', 'LIABILITY')->sum('amount');
        $netWorth = $totalAssets - $totalLiabilities;

        $this->overviewData = [
            'report_period' => $this->getReportPeriodLabel(),
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'new_members_this_period' => $newMembersThisPeriod,
            'total_loans_outstanding' => $totalLoansOutstanding,
            'total_loan_portfolio' => $totalLoanPortfolio,
            'total_deposits' => $totalDeposits,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'net_worth' => $netWorth,
            'number_of_branches' => $numberOfBranches,
            'number_of_staff' => $numberOfStaff,
            'loan_approval_rate' => round($loanApprovalRate, 1),
            'member_satisfaction_score' => 4.2, // This would need a separate feedback/satisfaction system
            'operational_efficiency' => 85.3 // This would need operational metrics calculation
        ];
    }

    public function loadClientSummary()
    {
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];
        $totalClients = ClientsModel::when($branchFilter, function($query) use ($branchFilter) {
            return $query->where($branchFilter);
        })->count();

        if ($totalClients == 0) {
            $this->clientSummary = [
                'by_type' => [
                    'individual' => ['count' => 0, 'percentage' => 0],
                    'group' => ['count' => 0, 'percentage' => 0],
                    'corporate' => ['count' => 0, 'percentage' => 0]
                ],
                'by_age_group' => [
                    '18-25' => ['count' => 0, 'percentage' => 0],
                    '26-35' => ['count' => 0, 'percentage' => 0],
                    '36-45' => ['count' => 0, 'percentage' => 0]
                ],
                'by_gender' => [
                    'male' => ['count' => 0, 'percentage' => 0],
                    'female' => ['count' => 0, 'percentage' => 0]
                ],
                'geographical_distribution' => [
                    'urban' => ['count' => 0, 'percentage' => 0],
                    'rural' => ['count' => 0, 'percentage' => 0]
                ]
            ];
            return;
        }

        // Get clients by type
        $individualCount = ClientsModel::where('membership_type', 'Individual')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $groupCount = ClientsModel::where('membership_type', 'Group')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $corporateCount = ClientsModel::where('membership_type', 'Corporate')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        // Get clients by age group
        $age18_25 = ClientsModel::whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 25')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $age26_35 = ClientsModel::whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 26 AND 35')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $age36_45 = ClientsModel::whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 45')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        // Get clients by gender
        $maleCount = ClientsModel::where('gender', 'Male')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $femaleCount = ClientsModel::where('gender', 'Female')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        // Get geographical distribution (assuming we have a field for this)
        $urbanCount = ClientsModel::where('location_type', 'Urban')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $ruralCount = ClientsModel::where('location_type', 'Rural')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->count();

        $this->clientSummary = [
            'by_type' => [
                'individual' => [
                    'count' => $individualCount, 
                    'percentage' => $totalClients > 0 ? round(($individualCount / $totalClients) * 100, 1) : 0
                ],
                'group' => [
                    'count' => $groupCount, 
                    'percentage' => $totalClients > 0 ? round(($groupCount / $totalClients) * 100, 1) : 0
                ],
                'corporate' => [
                    'count' => $corporateCount, 
                    'percentage' => $totalClients > 0 ? round(($corporateCount / $totalClients) * 100, 1) : 0
                ]
            ],
            'by_age_group' => [
                '18-25' => [
                    'count' => $age18_25, 
                    'percentage' => $totalClients > 0 ? round(($age18_25 / $totalClients) * 100, 1) : 0
                ],
                '26-35' => [
                    'count' => $age26_35, 
                    'percentage' => $totalClients > 0 ? round(($age26_35 / $totalClients) * 100, 1) : 0
                ],
                '36-45' => [
                    'count' => $age36_45, 
                    'percentage' => $totalClients > 0 ? round(($age36_45 / $totalClients) * 100, 1) : 0
                ]
            ],
            'by_gender' => [
                'male' => [
                    'count' => $maleCount, 
                    'percentage' => $totalClients > 0 ? round(($maleCount / $totalClients) * 100, 1) : 0
                ],
                'female' => [
                    'count' => $femaleCount, 
                    'percentage' => $totalClients > 0 ? round(($femaleCount / $totalClients) * 100, 1) : 0
                ]
            ],
            'geographical_distribution' => [
                'urban' => [
                    'count' => $urbanCount, 
                    'percentage' => $totalClients > 0 ? round(($urbanCount / $totalClients) * 100, 1) : 0
                ],
                'rural' => [
                    'count' => $ruralCount, 
                    'percentage' => $totalClients > 0 ? round(($ruralCount / $totalClients) * 100, 1) : 0
                ]
            ]
        ];
    }

    public function loadLoanSummary()
    {
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];
        $totalLoans = LoansModel::when($branchFilter, function($query) use ($branchFilter) {
            return $query->where($branchFilter);
        })->count();

        if ($totalLoans == 0) {
            $this->loanSummary = [
                'by_status' => [
                    'active' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
                    'overdue' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
                    'written_off' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
                    'completed' => ['count' => 0, 'amount' => 0, 'percentage' => 0]
                ],
                'by_product' => [],
                'by_risk_category' => []
            ];
            return;
        }

        // Get loans by status
        $activeLoans = LoansModel::where('status', 'ACTIVE')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            });
        $activeCount = $activeLoans->count();
        $activeAmount = $activeLoans->sum('principle');

        $overdueLoans = LoansModel::where('status', 'OVERDUE')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            });
        $overdueCount = $overdueLoans->count();
        $overdueAmount = $overdueLoans->sum('principle');

        $completedLoans = LoansModel::where('status', 'COMPLETED')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            });
        $completedCount = $completedLoans->count();
        $completedAmount = $completedLoans->sum('principle');

        // Get loans by product type
        $productLoans = LoansModel::with('loanProduct')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })
            ->get()
            ->groupBy('loan_type_2');

        $byProduct = [];
        foreach ($productLoans as $productType => $loans) {
            $count = $loans->count();
            $amount = $loans->sum('principle');
            $byProduct[strtolower(str_replace(' ', '_', $productType))] = [
                'count' => $count,
                'amount' => $amount,
                'percentage' => $totalLoans > 0 ? round(($count / $totalLoans) * 100, 1) : 0
            ];
        }

        // Get loans by risk category (based on days in arrears)
        $lowRiskLoans = LoansModel::where('days_in_arrears', '<=', 30)
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            });
        $lowRiskCount = $lowRiskLoans->count();
        $lowRiskAmount = $lowRiskLoans->sum('principle');

        $mediumRiskLoans = LoansModel::whereBetween('days_in_arrears', [31, 90])
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            });
        $mediumRiskCount = $mediumRiskLoans->count();
        $mediumRiskAmount = $mediumRiskLoans->sum('principle');

        $highRiskLoans = LoansModel::where('days_in_arrears', '>', 90)
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            });
        $highRiskCount = $highRiskLoans->count();
        $highRiskAmount = $highRiskLoans->sum('principle');

        $this->loanSummary = [
            'by_status' => [
                'active' => [
                    'count' => $activeCount,
                    'amount' => $activeAmount,
                    'percentage' => $totalLoans > 0 ? round(($activeCount / $totalLoans) * 100, 1) : 0
                ],
                'overdue' => [
                    'count' => $overdueCount,
                    'amount' => $overdueAmount,
                    'percentage' => $totalLoans > 0 ? round(($overdueCount / $totalLoans) * 100, 1) : 0
                ],
                'written_off' => [
                    'count' => 0, // This would need a specific status or field
                    'amount' => 0,
                    'percentage' => 0
                ],
                'completed' => [
                    'count' => $completedCount,
                    'amount' => $completedAmount,
                    'percentage' => $totalLoans > 0 ? round(($completedCount / $totalLoans) * 100, 1) : 0
                ]
            ],
            'by_product' => $byProduct,
            'by_risk_category' => [
                'low_risk' => [
                    'count' => $lowRiskCount,
                    'amount' => $lowRiskAmount,
                    'percentage' => $totalLoans > 0 ? round(($lowRiskCount / $totalLoans) * 100, 1) : 0
                ],
                'medium_risk' => [
                    'count' => $mediumRiskCount,
                    'amount' => $mediumRiskAmount,
                    'percentage' => $totalLoans > 0 ? round(($mediumRiskCount / $totalLoans) * 100, 1) : 0
                ],
                'high_risk' => [
                    'count' => $highRiskCount,
                    'amount' => $highRiskAmount,
                    'percentage' => $totalLoans > 0 ? round(($highRiskCount / $totalLoans) * 100, 1) : 0
                ]
            ]
        ];
    }

    public function loadDepositSummary()
    {
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];
        
        // Get accounts with branch filter
        $accountsQuery = AccountsModel::when($branchFilter, function($query) use ($branchFilter) {
            return $query->whereHas('client', function($q) use ($branchFilter) {
                $q->where($branchFilter);
            });
        });

        $totalAccounts = $accountsQuery->count();

        if ($totalAccounts == 0) {
            $this->depositSummary = [
                'by_type' => [
                    'savings' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
                    'current' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
                    'fixed_deposit' => ['count' => 0, 'amount' => 0, 'percentage' => 0]
                ],
                'by_balance_range' => [
                    '0-10000' => ['count' => 0, 'percentage' => 0],
                    '10001-50000' => ['count' => 0, 'percentage' => 0],
                    '50001-100000' => ['count' => 0, 'percentage' => 0],
                    '100000+' => ['count' => 0, 'percentage' => 0]
                ],
                'average_balance' => 0,
                'total_interest_paid' => 0
            ];
            return;
        }

        // Get accounts by type
        $savingsAccounts = $accountsQuery->where('account_type', 'SAVINGS');
        $savingsCount = $savingsAccounts->count();
        $savingsAmount = $savingsAccounts->sum('account_balance');

        $currentAccounts = $accountsQuery->where('account_type', 'CURRENT');
        $currentCount = $currentAccounts->count();
        $currentAmount = $currentAccounts->sum('account_balance');

        $fixedDepositAccounts = $accountsQuery->where('account_type', 'FIXED_DEPOSIT');
        $fixedDepositCount = $fixedDepositAccounts->count();
        $fixedDepositAmount = $fixedDepositAccounts->sum('account_balance');

        // Get accounts by balance range
        $balance0_10k = $accountsQuery->whereBetween('account_balance', [0, 10000])->count();
        $balance10k_50k = $accountsQuery->whereBetween('account_balance', [10001, 50000])->count();
        $balance50k_100k = $accountsQuery->whereBetween('account_balance', [50001, 100000])->count();
        $balance100kPlus = $accountsQuery->where('account_balance', '>', 100000)->count();

        // Calculate average balance
        $totalBalance = $accountsQuery->sum('account_balance');
        $averageBalance = $totalAccounts > 0 ? $totalBalance / $totalAccounts : 0;

        // Get total interest paid (this would need to be calculated from transaction records)
        $totalInterestPaid = 0; // This would need a separate calculation from transaction history

        $this->depositSummary = [
            'by_type' => [
                'savings' => [
                    'count' => $savingsCount,
                    'amount' => $savingsAmount,
                    'percentage' => $totalAccounts > 0 ? round(($savingsCount / $totalAccounts) * 100, 1) : 0
                ],
                'current' => [
                    'count' => $currentCount,
                    'amount' => $currentAmount,
                    'percentage' => $totalAccounts > 0 ? round(($currentCount / $totalAccounts) * 100, 1) : 0
                ],
                'fixed_deposit' => [
                    'count' => $fixedDepositCount,
                    'amount' => $fixedDepositAmount,
                    'percentage' => $totalAccounts > 0 ? round(($fixedDepositCount / $totalAccounts) * 100, 1) : 0
                ]
            ],
            'by_balance_range' => [
                '0-10000' => [
                    'count' => $balance0_10k,
                    'percentage' => $totalAccounts > 0 ? round(($balance0_10k / $totalAccounts) * 100, 1) : 0
                ],
                '10001-50000' => [
                    'count' => $balance10k_50k,
                    'percentage' => $totalAccounts > 0 ? round(($balance10k_50k / $totalAccounts) * 100, 1) : 0
                ],
                '50001-100000' => [
                    'count' => $balance50k_100k,
                    'percentage' => $totalAccounts > 0 ? round(($balance50k_100k / $totalAccounts) * 100, 1) : 0
                ],
                '100000+' => [
                    'count' => $balance100kPlus,
                    'percentage' => $totalAccounts > 0 ? round(($balance100kPlus / $totalAccounts) * 100, 1) : 0
                ]
            ],
            'average_balance' => round($averageBalance, 2),
            'total_interest_paid' => $totalInterestPaid
        ];
    }

    public function loadBranchPerformance()
    {
        $branches = BranchesModel::where('status', 'ACTIVE')->get();
        $branchPerformance = [];

        foreach ($branches as $branch) {
            // Get clients for this branch
            $totalClients = ClientsModel::where('branch_id', $branch->id)->count();
            
            // Get loans for this branch
            $totalLoans = LoansModel::where('branch_id', $branch->id)->count();
            $loanAmount = LoansModel::where('branch_id', $branch->id)->sum('principle');
            
            // Get deposits for this branch
            $totalDeposits = AccountsModel::whereHas('client', function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })->sum('account_balance');
            
            // Get staff count for this branch
            $staffCount = Employee::where('branch_id', $branch->id)->count();
            
            // Calculate performance score (simplified calculation)
            $performanceScore = 0;
            if ($totalClients > 0) {
                $clientScore = min(($totalClients / 100) * 30, 30); // Max 30 points for clients
                $loanScore = min(($totalLoans / 50) * 25, 25); // Max 25 points for loans
                $depositScore = min(($totalDeposits / 10000000) * 25, 25); // Max 25 points for deposits
                $staffScore = min(($staffCount / 10) * 20, 20); // Max 20 points for staff
                $performanceScore = round($clientScore + $loanScore + $depositScore + $staffScore, 1);
            }

            $branchPerformance[] = [
                'branch_name' => $branch->name,
                'total_clients' => $totalClients,
                'total_loans' => $totalLoans,
                'loan_amount' => $loanAmount,
                'total_deposits' => $totalDeposits,
                'staff_count' => $staffCount,
                'performance_score' => $performanceScore
            ];
        }

        // Sort by performance score descending
        usort($branchPerformance, function($a, $b) {
            return $b['performance_score'] <=> $a['performance_score'];
        });

        $this->branchPerformance = $branchPerformance;
    }

    public function loadProductPerformance()
    {
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];
        
        // Get loan products with their performance data
        $loanProducts = LoansModel::with('loanProduct')
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })
            ->get()
            ->groupBy('loan_type_2');

        $productPerformance = [];

        foreach ($loanProducts as $productType => $loans) {
            $totalLoans = $loans->count();
            $totalAmount = $loans->sum('principle');
            $averageAmount = $totalLoans > 0 ? $totalAmount / $totalLoans : 0;
            
            // Calculate average interest rate
            $averageInterestRate = $loans->avg('interest') ?? 0;
            
            // Calculate default rate (loans with days_in_arrears > 90)
            $defaultedLoans = $loans->where('days_in_arrears', '>', 90)->count();
            $defaultRate = $totalLoans > 0 ? ($defaultedLoans / $totalLoans) * 100 : 0;
            
            // Calculate profitability (simplified - based on interest rate and default rate)
            $profitability = max(0, $averageInterestRate - ($defaultRate * 2));

            $productPerformance[] = [
                'product_name' => $productType,
                'total_loans' => $totalLoans,
                'total_amount' => $totalAmount,
                'average_amount' => round($averageAmount, 2),
                'interest_rate' => round($averageInterestRate, 1),
                'default_rate' => round($defaultRate, 1),
                'profitability' => round($profitability, 1)
            ];
        }

        // Sort by total amount descending
        usort($productPerformance, function($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        $this->productPerformance = $productPerformance;
    }

    public function loadStaffPerformance()
    {
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];
        
        $employees = Employee::with(['department', 'branch'])
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })
            ->get();

        $staffPerformance = [];

        foreach ($employees as $employee) {
            // Get clients served (this would need a relationship or tracking system)
            $clientsServed = 0; // This would need to be tracked in a separate table
            
            // Get loans processed by this employee
            $loansProcessed = LoansModel::where('supervisor_id', $employee->id)->count();
            
            // Get deposits handled (this would need transaction tracking)
            $depositsHandled = 0; // This would need to be tracked in transaction records
            
            // Calculate performance rating (simplified)
            $performanceRating = 0;
            if ($loansProcessed > 0) {
                $performanceRating = min(($loansProcessed / 10) * 100, 100);
            }
            
            // Customer satisfaction (this would need a feedback system)
            $customerSatisfaction = 4.0; // Default value, would need actual feedback data

            $staffPerformance[] = [
                'staff_name' => $employee->first_name . ' ' . $employee->last_name,
                'position' => $employee->position ?? 'Staff',
                'department' => $employee->department->name ?? 'General',
                'clients_served' => $clientsServed,
                'loans_processed' => $loansProcessed,
                'deposits_handled' => $depositsHandled,
                'performance_rating' => round($performanceRating, 1),
                'customer_satisfaction' => $customerSatisfaction
            ];
        }

        // Sort by performance rating descending
        usort($staffPerformance, function($a, $b) {
            return $b['performance_rating'] <=> $a['performance_rating'];
        });

        $this->staffPerformance = $staffPerformance;
    }

    public function loadFinancialSummary()
    {
        $dateFilter = $this->getDateFilter();
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];

        // Get income data from general ledger
        $interestIncome = general_ledger::where('account_type', 'INCOME')
            ->where('description', 'like', '%interest%')
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereBetween('created_at', $dateFilter);
            })
            ->sum('amount');

        $feeIncome = general_ledger::where('account_type', 'INCOME')
            ->where('description', 'like', '%fee%')
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereBetween('created_at', $dateFilter);
            })
            ->sum('amount');

        $otherIncome = general_ledger::where('account_type', 'INCOME')
            ->where('description', 'not like', '%interest%')
            ->where('description', 'not like', '%fee%')
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereBetween('created_at', $dateFilter);
            })
            ->sum('amount');

        $totalIncome = $interestIncome + $feeIncome + $otherIncome;

        // Get expense data from general ledger
        $operatingExpenses = general_ledger::where('account_type', 'EXPENSE')
            ->where('description', 'like', '%operating%')
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereBetween('created_at', $dateFilter);
            })
            ->sum('amount');

        $staffCosts = general_ledger::where('account_type', 'EXPENSE')
            ->where('description', 'like', '%staff%')
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereBetween('created_at', $dateFilter);
            })
            ->sum('amount');

        $administrativeCosts = general_ledger::where('account_type', 'EXPENSE')
            ->where('description', 'like', '%administrative%')
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereBetween('created_at', $dateFilter);
            })
            ->sum('amount');

        $totalExpenses = $operatingExpenses + $staffCosts + $administrativeCosts;

        // Calculate profitability metrics
        $netProfit = $totalIncome - $totalExpenses;
        $profitMargin = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;
        
        // Get total assets for ROA calculation
        $totalAssets = general_ledger::where('account_type', 'ASSET')->sum('amount');
        $roa = $totalAssets > 0 ? ($netProfit / $totalAssets) * 100 : 0;
        
        // Get total equity for ROE calculation
        $totalEquity = general_ledger::where('account_type', 'EQUITY')->sum('amount');
        $roe = $totalEquity > 0 ? ($netProfit / $totalEquity) * 100 : 0;

        $this->financialSummary = [
            'income' => [
                'interest_income' => $interestIncome,
                'fee_income' => $feeIncome,
                'other_income' => $otherIncome,
                'total_income' => $totalIncome
            ],
            'expenses' => [
                'operating_expenses' => $operatingExpenses,
                'staff_costs' => $staffCosts,
                'administrative_costs' => $administrativeCosts,
                'total_expenses' => $totalExpenses
            ],
            'profitability' => [
                'gross_profit' => $totalIncome,
                'net_profit' => $netProfit,
                'profit_margin' => round($profitMargin, 1),
                'roa' => round($roa, 2),
                'roe' => round($roe, 2)
            ]
        ];
    }

    public function loadOperationalMetrics()
    {
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];
        
        // Calculate efficiency ratios
        $totalIncome = $this->financialSummary['income']['total_income'] ?? 0;
        $totalExpenses = $this->financialSummary['expenses']['total_expenses'] ?? 0;
        $costToIncomeRatio = $totalIncome > 0 ? ($totalExpenses / $totalIncome) * 100 : 0;
        
        $operatingEfficiency = $totalIncome > 0 ? (($totalIncome - $totalExpenses) / $totalIncome) * 100 : 0;
        
        // Staff productivity (loans per staff member)
        $totalStaff = Employee::when($branchFilter, function($query) use ($branchFilter) {
            return $query->where($branchFilter);
        })->count();
        
        $totalLoans = LoansModel::when($branchFilter, function($query) use ($branchFilter) {
            return $query->where($branchFilter);
        })->count();
        
        $staffProductivity = $totalStaff > 0 ? ($totalLoans / $totalStaff) : 0;

        // Calculate risk metrics
        $totalLoanPortfolio = LoansModel::whereIn('status', ['ACTIVE', 'APPROVED'])
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->sum('principle');
        
        $portfolioAtRisk = LoansModel::where('days_in_arrears', '>', 30)
            ->when($branchFilter, function($query) use ($branchFilter) {
                return $query->where($branchFilter);
            })->sum('principle');
        
        $portfolioAtRiskPercentage = $totalLoanPortfolio > 0 ? ($portfolioAtRisk / $totalLoanPortfolio) * 100 : 0;
        
        // Provision coverage (simplified calculation)
        $provisionCoverage = 85.0; // This would need actual provision calculations
        
        // Capital adequacy (simplified)
        $totalAssets = general_ledger::where('account_type', 'ASSET')->sum('amount');
        $totalCapital = general_ledger::where('account_type', 'EQUITY')->sum('amount');
        $capitalAdequacy = $totalAssets > 0 ? ($totalCapital / $totalAssets) * 100 : 0;

        $this->operationalMetrics = [
            'efficiency_ratios' => [
                'cost_to_income_ratio' => round($costToIncomeRatio, 1),
                'operating_efficiency' => round($operatingEfficiency, 1),
                'staff_productivity' => round($staffProductivity, 1)
            ],
            'service_metrics' => [
                'average_processing_time' => '2.5 days', // This would need actual processing time tracking
                'customer_satisfaction' => 4.2, // This would need a feedback system
                'complaint_resolution_time' => '24 hours' // This would need complaint tracking
            ],
            'risk_metrics' => [
                'portfolio_at_risk' => round($portfolioAtRiskPercentage, 1),
                'provision_coverage' => $provisionCoverage,
                'capital_adequacy' => round($capitalAdequacy, 1)
            ]
        ];
    }

    public function calculateStatistics()
    {
        $this->totalClients = $this->overviewData['total_members'] ?? 0;
        $this->totalLoans = $this->overviewData['total_loans_outstanding'] ?? 0;
        $this->totalLoanAmount = $this->overviewData['total_loan_portfolio'] ?? 0;
        $this->totalDeposits = $this->overviewData['total_deposits'] ?? 0;
        $this->totalAssets = $this->overviewData['total_assets'] ?? 0;
        $this->totalLiabilities = $this->overviewData['total_liabilities'] ?? 0;
        $this->netWorth = $this->overviewData['net_worth'] ?? 0;
        $this->activeBranches = $this->overviewData['number_of_branches'] ?? 0;
        $this->activeStaff = $this->overviewData['number_of_staff'] ?? 0;
    }

    public function getDateFilter()
    {
        $year = $this->selectedYear;
        $month = $this->selectedMonth;

        switch ($this->reportPeriod) {
            case 'daily':
                return [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
            case 'weekly':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'monthly':
                return [
                    Carbon::createFromFormat('Y-m', $year . '-' . $month)->startOfMonth(),
                    Carbon::createFromFormat('Y-m', $year . '-' . $month)->endOfMonth()
                ];
            case 'yearly':
                return [
                    Carbon::createFromFormat('Y', $year)->startOfYear(),
                    Carbon::createFromFormat('Y', $year)->endOfYear()
                ];
            default:
                return null;
        }
    }

    public function getReportPeriodLabel()
    {
        switch ($this->reportPeriod) {
            case 'daily':
                return 'Daily Report';
            case 'weekly':
                return 'Weekly Report';
            case 'monthly':
                return 'Monthly Report - ' . Carbon::createFromFormat('Y-m', $this->selectedYear . '-' . $this->selectedMonth)->format('F Y');
            case 'yearly':
                return 'Yearly Report - ' . $this->selectedYear;
            default:
                return 'General Report';
        }
    }

    public function updatedReportPeriod()
    {
        $this->loadGeneralReportData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadGeneralReportData();
    }

    public function updatedSelectedYear()
    {
        $this->loadGeneralReportData();
    }

    public function updatedSelectedBranch()
    {
        $this->loadGeneralReportData();
    }

    public function exportToExcel()
    {
        try {
            // Prepare report data for export
            $reportData = [
                'overview' => $this->overviewData,
                'client_summary' => $this->clientSummary,
                'loan_summary' => $this->loanSummary,
                'deposit_summary' => $this->depositSummary,
                'branch_performance' => $this->branchPerformance,
                'product_performance' => $this->productPerformance,
                'staff_performance' => $this->staffPerformance,
                'financial_summary' => $this->financialSummary,
                'operational_metrics' => $this->operationalMetrics,
            ];

            // Prepare filters for export
            $filters = [
                'report_period' => $this->getReportPeriodLabel(),
                'selected_month' => $this->selectedMonth,
                'selected_year' => $this->selectedYear,
                'selected_branch' => $this->selectedBranch,
                'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];

            // Generate filename
            $filename = 'general_report_' . 
                       strtolower(str_replace(' ', '_', $this->reportPeriod)) . '_' . 
                       $this->selectedYear . '_' . 
                       str_pad($this->selectedMonth, 2, '0', STR_PAD_LEFT) . '_' . 
                       Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Create and download the Excel file
            return Excel::download(new GeneralReportExport($reportData, $filters), $filename);

        } catch (Exception $e) {
            Log::error('Error exporting General Report to Excel: ' . $e->getMessage());
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.reports.general-report');
    }
}
