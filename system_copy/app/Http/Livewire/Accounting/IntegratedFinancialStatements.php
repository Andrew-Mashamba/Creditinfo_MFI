<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Services\FinancialStatementIntegrationService;
use App\Services\AccountsBasedFinancialStatementService;
use App\Models\FinancialPeriod;
use App\Models\FinancialStatementItem;
use App\Models\StatementRelationship;
use App\Models\FinancialRatio;
use App\Models\FinancialStatementNote;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class IntegratedFinancialStatements extends Component
{
    // Common properties for all statements
    public $selectedYear;
    public $selectedPeriodType = 'annual';
    public $comparisonYears = [];
    public $companyName = 'NBC SACCOS LTD';
    public $currentStatement = 'dashboard';
    
    // Financial period
    public $financialPeriod;
    
    // Statement data
    public $balanceSheetData = [];
    public $incomeStatementData = [];
    public $cashFlowData = [];
    public $equityStatementData = [];
    public $trialBalanceData = [];
    public $financialRatios = [];
    public $financialNotes = [];
    
    // Display options
    public $expandedCategories = [];
    public $showDetailed = false;
    public $showComparison = true;
    public $showNotes = true;
    public $showRatios = true;
    
    // Cash Flow specific options
    public $cashFlowMethod = 'indirect';
    public $cashFlowRatios = [];
    
    // Note modal properties
    public $showNoteModal = false;
    
    // Notes-related properties
    public $expandedNotes = [];
    public $corporateInfo = [];
    public $accountingPolicies = [];
    public $significantEstimates = [];
    public $financialData = [];
    public $reportingDate;
    public $showAccountDetail = false;
    public $selectedAccountForDetail = ['name' => '', 'number' => ''];
    public $accountTransactions = [];
    public $noteNumber = '';
    public $noteTitle = '';
    public $noteContent = [];
    
    // Income Statement Note Modal properties
    public $showIncomeNoteModal = false;
    public $incomeNoteNumber = '';
    public $incomeNoteTitle = '';
    public $incomeNoteContent = [];
    
    // Off-balance sheet items
    public $offBalanceSheetItems = [];
    
    // Consolidation settings
    public $isConsolidated = false;
    
    // Integration services
    protected $integrationService;
    protected $accountsService;
    
    public function boot(FinancialStatementIntegrationService $integrationService, AccountsBasedFinancialStatementService $accountsService)
    {
        $this->integrationService = $integrationService;
        $this->accountsService = $accountsService;
    }
    
    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1,
            $this->selectedYear - 2
        ];
        
        $this->initializeNotesData();
        $this->loadFinancialData();
    }
    
    private function initializeNotesData()
    {
        $this->reportingDate = date('d F Y');
        
        // Initialize with some sections expanded by default
        $this->expandedNotes = ['assets', 'liabilities', 'equity', 'income', 'expenses'];
        
        // Ensure comparison years are integers
        $this->comparisonYears = [
            intval($this->selectedYear),
            intval($this->selectedYear) - 1,
            intval($this->selectedYear) - 2
        ];
        
        $this->corporateInfo = [
            'name' => $this->companyName,
            'nature' => 'Savings and Credit Cooperative Society',
            'registration' => 'REG/SACCOS/2020',
            'address' => 'P.O. Box 1234, Dar es Salaam, Tanzania',
            'reporting_period' => '1 January to 31 December',
            'functional_currency' => 'Tanzania Shillings (TZS)',
            'principal_activities' => [
                'Mobilization of savings from members',
                'Provision of credit facilities to members',
                'Financial advisory services',
                'Investment management on behalf of members'
            ]
        ];
        
        $this->accountingPolicies = [
            [
                'title' => 'Basis of Preparation',
                'content' => 'The financial statements have been prepared in accordance with International Financial Reporting Standards (IFRS).',
                'subcategories' => []
            ],
            [
                'title' => 'Revenue Recognition',
                'content' => 'Revenue is recognized when it is probable that economic benefits will flow to the institution.',
                'subcategories' => [
                    'Interest Income' => 'Recognized using the effective interest method',
                    'Fee Income' => 'Recognized when services are provided'
                ]
            ]
        ];
        
        $this->significantEstimates = [
            [
                'title' => 'Impairment of Financial Assets',
                'description' => 'The institution reviews its loan portfolio for impairment on a regular basis.'
            ],
            [
                'title' => 'Useful Lives of Property and Equipment',
                'description' => 'Management estimates the useful lives based on expected usage patterns.'
            ]
        ];
        
        // Initialize financialData with empty arrays for each category
        $this->financialData = [
            'assets' => [],
            'liabilities' => [],
            'equity' => [],
            'income' => [],
            'expenses' => []
        ];
        
        // Populate with sample data if needed
        $this->populateNotesFinancialData();
    }
    
    private function populateNotesFinancialData()
    {
        // Load actual data from the database
        $currentYear = intval($this->selectedYear);
        $prevYear = $currentYear - 1;
        $prevYear2 = $currentYear - 2;
        
        // Initialize the arrays
        $this->financialData = [
            'assets' => [],
            'liabilities' => [],
            'equity' => [],
            'income' => [],
            'expenses' => []
        ];
        
        // Load data for each category
        $this->loadNotesDataByCategory('1000', 'assets'); // Assets
        $this->loadNotesDataByCategory('2000', 'liabilities'); // Liabilities 
        $this->loadNotesDataByCategory('3000', 'equity'); // Equity
        $this->loadNotesDataByCategory('4000', 'income'); // Revenue/Income
        $this->loadNotesDataByCategory('5000', 'expenses'); // Expenses
    }
    
    private function loadNotesDataByCategory($majorCategoryCode, $categoryKey)
    {
        // Get L2 accounts for this category (similar to StatementOfFinancialPosition)
        $parentAccounts = \DB::table('accounts')
            ->where('major_category_code', $majorCategoryCode)
            ->where('account_level', '2') // L2 accounts only
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('account_number')
            ->get();
            
        // Initialize category data if not exists
        if (!isset($this->financialData[$categoryKey])) {
            $this->financialData[$categoryKey] = [];
        }
        
        foreach ($parentAccounts as $account) {
            // Get current year balance
            $currentBalance = $this->getAccountBalance($account->account_number, $this->selectedYear);
            $prevBalance = $this->getAccountBalance($account->account_number, $this->selectedYear - 1);
            $prevBalance2 = $this->getAccountBalance($account->account_number, $this->selectedYear - 2);
            
            // Skip accounts with zero balances across all years
            if ($currentBalance == 0 && $prevBalance == 0 && $prevBalance2 == 0) {
                continue;
            }
            
            // Calculate percentage change
            $changePercentage = 0;
            if ($prevBalance != 0) {
                $changePercentage = (($currentBalance - $prevBalance) / abs($prevBalance)) * 100;
            }
            
            // Get composition (child accounts)
            $composition = $this->getAccountComposition($account->account_number);
            
            // Show absolute values for display
            $accountData = [
                'account_name' => $account->account_name,
                'account_number' => $account->account_number,
                'years' => [
                    intval($this->selectedYear) => abs($currentBalance),
                    intval($this->selectedYear - 1) => abs($prevBalance),
                    intval($this->selectedYear - 2) => abs($prevBalance2)
                ],
                'movements' => [
                    'change_percentage' => round($changePercentage, 2),
                    'change_amount' => abs($currentBalance) - abs($prevBalance)
                ],
                'composition' => $composition
            ];
            
            $this->financialData[$categoryKey][] = $accountData;
        }
    }
    
    
    private function getAccountComposition($parentAccountNumber)
    {
        $composition = [];
        
        // Get L3 child accounts (one level deeper)
        $childAccounts = \DB::table('accounts')
            ->where('parent_account_number', $parentAccountNumber)
            ->where('account_level', '3')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('account_number')
            ->get();
            
        foreach ($childAccounts as $child) {
            $currentBalance = $this->getAccountBalance($child->account_number, $this->selectedYear);
            $prevBalance = $this->getAccountBalance($child->account_number, $this->selectedYear - 1);
            $prevBalance2 = $this->getAccountBalance($child->account_number, $this->selectedYear - 2);
            
            // Only include accounts with non-zero balances
            if ($currentBalance != 0 || $prevBalance != 0 || $prevBalance2 != 0) {
                $composition[] = [
                    'account_name' => $child->account_name,
                    'account_number' => $child->account_number,
                    'years' => [
                        intval($this->selectedYear) => abs($currentBalance),
                        intval($this->selectedYear - 1) => abs($prevBalance),
                        intval($this->selectedYear - 2) => abs($prevBalance2)
                    ]
                ];
            }
        }
        
        return $composition;
    }
    
    private function populateNotesFinancialDataOLD()
    {
        // Old mock data - keeping for reference
        $currentYear = intval($this->selectedYear);
        $prevYear = $currentYear - 1;
        $prevYear2 = $currentYear - 2;
        
        $this->financialData['assets'] = [
            [
                'account_name' => 'Cash and Cash Equivalents',
                'account_number' => '1000',
                'years' => [
                    $currentYear => 1000000,
                    $prevYear => 950000,
                    $prevYear2 => 900000
                ],
                'movements' => [
                    'change_percentage' => 5.26
                ],
                'composition' => [
                    [
                        'account_name' => 'Cash on Hand',
                        'years' => [
                            $currentYear => 50000,
                            $prevYear => 45000,
                            $prevYear2 => 40000
                        ]
                    ],
                    [
                        'account_name' => 'Bank Balances',
                        'years' => [
                            $currentYear => 950000,
                            $prevYear => 905000,
                            $prevYear2 => 860000
                        ]
                    ]
                ]
            ],
            [
                'account_name' => 'Loans and Advances to Members',
                'account_number' => '1100',
                'years' => [
                    $currentYear => 5000000,
                    $prevYear => 4500000,
                    $prevYear2 => 4000000
                ],
                'movements' => [
                    'change_percentage' => 11.11
                ],
                'composition' => []
            ]
        ];
        
        $this->financialData['liabilities'] = [
            [
                'account_name' => 'Member Deposits',
                'account_number' => '2000',
                'years' => [
                    $currentYear => 3500000,
                    $prevYear => 3200000,
                    $prevYear2 => 2900000
                ],
                'movements' => [
                    'change_percentage' => 9.38
                ],
                'composition' => [
                    [
                        'account_name' => 'Savings Deposits',
                        'years' => [
                            $currentYear => 2000000,
                            $prevYear => 1850000,
                            $prevYear2 => 1700000
                        ]
                    ],
                    [
                        'account_name' => 'Fixed Deposits',
                        'years' => [
                            $currentYear => 1500000,
                            $prevYear => 1350000,
                            $prevYear2 => 1200000
                        ]
                    ]
                ]
            ]
        ];
        
        $this->financialData['equity'] = [
            [
                'account_name' => 'Share Capital',
                'account_number' => '3000',
                'years' => [
                    $currentYear => 1500000,
                    $prevYear => 1350000,
                    $prevYear2 => 1200000
                ],
                'movements' => [
                    'change_percentage' => 11.11
                ],
                'composition' => []
            ],
            [
                'account_name' => 'Retained Earnings',
                'account_number' => '3100',
                'years' => [
                    $currentYear => 800000,
                    $prevYear => 700000,
                    $prevYear2 => 600000
                ],
                'movements' => [
                    'change_percentage' => 14.29
                ],
                'composition' => []
            ]
        ];
        
        $this->financialData['income'] = [
            [
                'account_name' => 'Interest Income',
                'account_number' => '4000',
                'years' => [
                    $currentYear => 850000,
                    $prevYear => 780000,
                    $prevYear2 => 720000
                ],
                'movements' => [
                    'change_percentage' => 8.97
                ],
                'composition' => [
                    [
                        'account_name' => 'Interest on Loans',
                        'years' => [
                            $currentYear => 750000,
                            $prevYear => 690000,
                            $prevYear2 => 640000
                        ]
                    ],
                    [
                        'account_name' => 'Interest on Investments',
                        'years' => [
                            $currentYear => 100000,
                            $prevYear => 90000,
                            $prevYear2 => 80000
                        ]
                    ]
                ]
            ],
            [
                'account_name' => 'Fee and Commission Income',
                'account_number' => '4100',
                'years' => [
                    $currentYear => 250000,
                    $prevYear => 230000,
                    $prevYear2 => 210000
                ],
                'movements' => [
                    'change_percentage' => 8.70
                ],
                'composition' => []
            ]
        ];
        
        $this->financialData['expenses'] = [
            [
                'account_name' => 'Operating Expenses',
                'account_number' => '5000',
                'years' => [
                    $currentYear => 450000,
                    $prevYear => 420000,
                    $prevYear2 => 390000
                ],
                'movements' => [
                    'change_percentage' => 7.14
                ],
                'composition' => [
                    [
                        'account_name' => 'Staff Costs',
                        'years' => [
                            $currentYear => 300000,
                            $prevYear => 280000,
                            $prevYear2 => 260000
                        ]
                    ],
                    [
                        'account_name' => 'Administrative Expenses',
                        'years' => [
                            $currentYear => 150000,
                            $prevYear => 140000,
                            $prevYear2 => 130000
                        ]
                    ]
                ]
            ],
            [
                'account_name' => 'Interest Expense',
                'account_number' => '5100',
                'years' => [
                    $currentYear => 180000,
                    $prevYear => 165000,
                    $prevYear2 => 150000
                ],
                'movements' => [
                    'change_percentage' => 9.09
                ],
                'composition' => []
            ]
        ];
    }
    
    public function updatedSelectedYear()
    {
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1,
            $this->selectedYear - 2
        ];
        
        $this->initializeNotesData();
        $this->loadFinancialData();
    }
    
    public function loadFinancialData()
    {
        // Get or create financial period
        $this->financialPeriod = FinancialPeriod::getOrCreateForDate(
            "{$this->selectedYear}-12-31",
            $this->selectedPeriodType
        );
        
        // Check if we need to generate new statements
        if (!$this->hasCompleteStatements()) {
            $this->generateFinancialStatements();
        }
        
        // Load all statement data
        $this->loadBalanceSheet();
        $this->loadIncomeStatement();
        $this->loadCashFlowStatement();
        $this->loadEquityStatement();
        $this->loadTrialBalance();
        $this->loadFinancialRatios();
        $this->loadFinancialNotes();
    }
    
    private function hasCompleteStatements()
    {
        $requiredStatements = ['balance_sheet', 'income_statement', 'cash_flow', 'equity_changes', 'trial_balance'];
        
        foreach ($requiredStatements as $statementType) {
            $snapshot = \App\Models\FinancialStatementSnapshot::getLatest($this->financialPeriod->id, $statementType);
            if (!$snapshot) {
                return false;
            }
        }
        
        return true;
    }
    
    private function generateFinancialStatements()
    {
        try {
            // Use the new accounts-based service for accurate data
            $balanceSheet = $this->accountsService->getStatementOfFinancialPosition("{$this->selectedYear}-12-31");
            $incomeStatement = $this->accountsService->getIncomeStatement(
                "{$this->selectedYear}-01-01",
                "{$this->selectedYear}-12-31"
            );
            $trialBalance = $this->accountsService->getTrialBalance("{$this->selectedYear}-12-31");
            
            // Store the data
            $this->balanceSheetData = $balanceSheet;
            $this->incomeStatementData = $incomeStatement;
            $this->trialBalanceData = $trialBalance;
            
            // Also try the integration service for other statements
            try {
                $result = $this->integrationService->generateIntegratedStatements(
                    $this->selectedYear,
                    $this->selectedPeriodType
                );
                
                if (isset($result['cash_flow'])) {
                    $this->cashFlowData = $result['cash_flow'];
                }
            } catch (\Exception $e) {
                // Log but don't fail if integration service has issues
                \Log::warning('Integration service error: ' . $e->getMessage());
            }
            
            session()->flash('success', 'Financial statements generated successfully from accounts data.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating financial statements: ' . $e->getMessage());
        }
    }
    
    private function loadBalanceSheet()
    {
        // Use the same logic as StatementOfFinancialPosition
        // Load Assets (1000 series)
        $assetsData = $this->getAccountCategoryData('1000', 'ASSETS');
        
        // Load Liabilities (2000 series)
        $liabilitiesData = $this->getAccountCategoryData('2000', 'LIABILITIES');
        
        // Load Equity (3000 series)
        $equityData = $this->getAccountCategoryData('3000', 'EQUITY');
        
        // Calculate Net Income and add to Equity
        $currentYear = $this->selectedYear;
        $netIncome = $this->calculateCurrentYearNetIncome($currentYear);
        $previousYearNetIncome = $this->calculateCurrentYearNetIncome($currentYear - 1);
        
        // Add Net Income as a line item in equity
        $equityData['current'][] = [
            'account_number' => '3999',
            'account_name' => 'Current Year Net Income (Loss)',
            'account_level' => '2',
            'amount' => $netIncome,
            'years' => [
                $currentYear => $netIncome,
                $currentYear - 1 => $previousYearNetIncome
            ],
            'is_calculated' => true
        ];
        
        // Update retained earnings if it exists
        foreach ($equityData['current'] as &$account) {
            if (str_contains(strtolower($account['account_name'] ?? ''), 'retained earnings')) {
                $account['amount'] = ($account['amount'] ?? 0) + $netIncome;
                $account['includes_net_income'] = true;
                if (isset($account['years'][$currentYear])) {
                    $account['years'][$currentYear] += $netIncome;
                }
            }
        }
        
        // Apply year-end adjustments
        $this->applyYearEndAdjustments($assetsData, $liabilitiesData, $equityData);
        
        // Apply inter-company eliminations if consolidated view
        if ($this->isConsolidated) {
            $this->applyIntercompanyEliminations($assetsData, $liabilitiesData);
        }
        
        // Load off-balance sheet items
        $this->loadOffBalanceSheetItems();
        
        // Calculate totals after all adjustments
        $totalAssets = $assetsData['total'][$currentYear] ?? 0;
        $totalLiabilities = $liabilitiesData['total'][$currentYear] ?? 0;
        $totalEquity = ($equityData['total'][$currentYear] ?? 0) + $netIncome;
        
        $this->balanceSheetData = [
            'assets' => [
                'current' => $assetsData['current'],
                'non_current' => $assetsData['non_current'],
                'current_total' => $this->sumAccountBalances($assetsData['current'], $currentYear),
                'non_current_total' => $this->sumAccountBalances($assetsData['non_current'], $currentYear),
                'total' => $totalAssets
            ],
            'liabilities' => [
                'current' => $liabilitiesData['current'],
                'non_current' => $liabilitiesData['non_current'],
                'current_total' => $this->sumAccountBalances($liabilitiesData['current'], $currentYear),
                'non_current_total' => $this->sumAccountBalances($liabilitiesData['non_current'], $currentYear),
                'total' => $totalLiabilities
            ],
            'equity' => [
                'items' => array_merge($equityData['current'], $equityData['non_current']),
                'total' => $totalEquity
            ],
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
            'balance_difference' => $totalAssets - ($totalLiabilities + $totalEquity)
        ];
    }
    
    private function getAccountCategoryData($majorCode, $categoryName)
    {
        $data = [
            'current' => [],
            'non_current' => [],
            'total' => []
        ];
        
        // Get L2 accounts for this major category
        $l2Accounts = \DB::table('accounts')
            ->where('major_category_code', $majorCode)
            ->where('account_level', '2')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('account_number')
            ->get();
        
        $totalByYear = [];
        foreach ($this->comparisonYears as $year) {
            $totalByYear[$year] = 0;
        }
        
        foreach ($l2Accounts as $l2Account) {
            // Determine if current or non-current based on account name or code
            $isCurrent = $this->isCurrentAccount($l2Account);
            $section = $isCurrent ? 'current' : 'non_current';
            
            $accountData = [
                'account_number' => $l2Account->account_number,
                'account_name' => $l2Account->account_name,
                'account_level' => $l2Account->account_level,
                'amount' => $this->getAccountBalance($l2Account->account_number, $this->selectedYear),
                'years' => [],
                'children' => []
            ];
            
            // Get balances for comparison years
            foreach ($this->comparisonYears as $year) {
                $balance = $this->getAccountBalance($l2Account->account_number, $year);
                $accountData['years'][$year] = $balance;
                $totalByYear[$year] += $balance;
            }
            
            $data[$section][] = $accountData;
        }
        
        $data['total'] = $totalByYear;
        
        return $data;
    }
    
    private function isCurrentAccount($account)
    {
        $currentKeywords = ['current', 'cash', 'bank', 'receivable', 'inventory', 'prepaid', 'short-term'];
        $accountNameLower = strtolower($account->account_name);
        
        foreach ($currentKeywords as $keyword) {
            if (str_contains($accountNameLower, $keyword)) {
                return true;
            }
        }
        
        // Check specific account codes if you have a coding convention
        $accountNumber = intval($account->account_number);
        if ($account->major_category_code == '1000') {
            return $accountNumber >= 1100 && $accountNumber < 1500;
        } elseif ($account->major_category_code == '2000') {
            return $accountNumber >= 2100 && $accountNumber < 2500;
        }
        
        return false;
    }
    
    private function getAccountBalance($accountNumber, $year)
    {
        // Get account info first
        $accountInfo = \DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        if (!$accountInfo) {
            return 0;
        }
        
        // For current year, use the balance from accounts table if available
        if ($year == date('Y') && isset($accountInfo->balance)) {
            // Get sum of this account and all its children
            $totalBalance = floatval($accountInfo->balance);
            
            // Add child account balances
            $childAccounts = \DB::table('accounts')
                ->where('parent_account_number', '=', $accountNumber)
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->get();
            
            foreach ($childAccounts as $child) {
                $totalBalance += floatval($child->balance ?? 0);
            }
            
            // Check if it's a contra account and reverse sign if needed
            $isContraAccount = $this->isContraAccount($accountInfo);
            if ($isContraAccount) {
                $totalBalance = -$totalBalance;
            }
            
            return $totalBalance;
        }
        
        // For historical years or if balance not in accounts table, calculate from general_ledger
        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Get opening balance
        $openingBalance = $this->getOpeningBalance($accountNumber, $year);
        
        // Get all child accounts - use exact match instead of LIKE
        $allAccounts = [$accountNumber];
        $childAccounts = \DB::table('accounts')
            ->where('parent_account_number', '=', $accountNumber)
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->pluck('account_number');
        
        $allAccounts = array_merge($allAccounts, $childAccounts->toArray());
        
        // Get total from general ledger
        $result = \DB::table('general_ledger')
            ->whereIn('record_on_account_number', $allAccounts)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                \DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit'),
                \DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit')
            )
            ->first();
        
        // Check if it's a contra account
        $isContraAccount = $this->isContraAccount($accountInfo);
        
        // Determine balance based on account type
        $majorCode = $accountInfo->major_category_code;
        $transactionBalance = 0;
        
        // Assets: Debit balance (debit - credit)
        // Liabilities and Equity: Credit balance (credit - debit)
        if ($majorCode == '1000') {
            $transactionBalance = ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
        } else {
            $transactionBalance = ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
        }
        
        // Reverse sign for contra accounts
        if ($isContraAccount) {
            $transactionBalance = -$transactionBalance;
        }
        
        return $openingBalance + $transactionBalance;
    }
    
    private function sumAccountBalances($accounts, $year)
    {
        $total = 0;
        foreach ($accounts as $account) {
            $total += $account['years'][$year] ?? $account['amount'] ?? 0;
        }
        return $total;
    }
    
    private function loadIncomeStatement()
    {
        // Load revenue accounts (4000 series) with categorization
        $revenueCategories = \DB::table('accounts')
            ->where('major_category_code', '4000')
            ->where('account_level', 2) // Category level
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('account_number')
            ->get();
        
        $revenue = [];
        $revenueByCategory = [];
        $totalRevenue = 0;
        
        foreach ($revenueCategories as $category) {
            // Get detail accounts under this category
            $detailAccounts = \DB::table('accounts')
                ->where('parent_account_number', $category->account_number)
                ->where('account_level', 3)
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->get();
            
            $categoryTotal = 0;
            $categoryItems = [];
            
            foreach ($detailAccounts as $account) {
                $balance = $this->getAccountBalance($account->account_number, $this->selectedYear);
                if ($balance != 0) {
                    $amount = abs($balance); // Revenue is credit balance, show as positive
                    $categoryItems[] = [
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'amount' => $amount,
                        'classification' => 'revenue'
                    ];
                    $categoryTotal += $amount;
                }
            }
            
            if ($categoryTotal > 0) {
                $revenueByCategory[$category->account_name] = [
                    'items' => $categoryItems,
                    'total' => $categoryTotal
                ];
                $revenue = array_merge($revenue, $categoryItems);
                $totalRevenue += $categoryTotal;
            }
        }
        
        // Load expense accounts (5000 series) with categorization
        $expenseCategories = \DB::table('accounts')
            ->where('major_category_code', '5000')
            ->where('account_level', 2) // Category level
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('account_number')
            ->get();
        
        $expenses = [];
        $expensesByCategory = [];
        $totalExpenses = 0;
        
        foreach ($expenseCategories as $category) {
            // Get detail accounts under this category
            $detailAccounts = \DB::table('accounts')
                ->where('parent_account_number', $category->account_number)
                ->where('account_level', 3)
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->get();
            
            $categoryTotal = 0;
            $categoryItems = [];
            
            foreach ($detailAccounts as $account) {
                $balance = $this->getAccountBalance($account->account_number, $this->selectedYear);
                if ($balance != 0) {
                    $amount = abs($balance); // Expenses are debit balance, show as positive
                    $categoryItems[] = [
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'amount' => $amount,
                        'classification' => 'expense'
                    ];
                    $categoryTotal += $amount;
                }
            }
            
            if ($categoryTotal > 0) {
                $expensesByCategory[$category->account_name] = [
                    'items' => $categoryItems,
                    'total' => $categoryTotal
                ];
                $expenses = array_merge($expenses, $categoryItems);
                $totalExpenses += $categoryTotal;
            }
        }
        
        // Calculate net income
        $netIncome = $totalRevenue - $totalExpenses;
        
        // Load Other Comprehensive Income items if any
        $ociItems = $this->loadOtherComprehensiveIncome();
        
        $this->incomeStatementData = [
            'revenue' => $revenue,
            'revenue_by_category' => $revenueByCategory,
            'expenses' => $expenses,
            'expenses_by_category' => $expensesByCategory,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'other_comprehensive_income' => $ociItems,
            'total_comprehensive_income' => $netIncome + ($ociItems['total'] ?? 0)
        ];
    }
    
    private function loadOtherComprehensiveIncome()
    {
        $ociItems = [];
        $total = 0;
        
        // Check for revaluation reserves (typically in equity accounts)
        $revaluationReserve = \DB::table('accounts')
            ->where('account_name', 'LIKE', '%REVALUATION%')
            ->where('major_category_code', '3000')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();
            
        if ($revaluationReserve) {
            $currentYearChange = $this->getYearOverYearChange($revaluationReserve->account_number);
            if ($currentYearChange != 0) {
                $ociItems[] = [
                    'description' => 'Revaluation of Property, Plant and Equipment',
                    'amount' => $currentYearChange
                ];
                $total += $currentYearChange;
            }
        }
        
        // Check for foreign currency translation adjustments
        $fcTranslation = \DB::table('accounts')
            ->where('account_name', 'LIKE', '%FOREIGN CURRENCY%')
            ->orWhere('account_name', 'LIKE', '%TRANSLATION%')
            ->where('major_category_code', '3000')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();
            
        if ($fcTranslation) {
            $currentYearChange = $this->getYearOverYearChange($fcTranslation->account_number);
            if ($currentYearChange != 0) {
                $ociItems[] = [
                    'description' => 'Foreign Currency Translation Adjustments',
                    'amount' => $currentYearChange
                ];
                $total += $currentYearChange;
            }
        }
        
        // Check for actuarial gains/losses on defined benefit plans
        $actuarial = \DB::table('accounts')
            ->where('account_name', 'LIKE', '%ACTUARIAL%')
            ->where('major_category_code', '3000')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();
            
        if ($actuarial) {
            $currentYearChange = $this->getYearOverYearChange($actuarial->account_number);
            if ($currentYearChange != 0) {
                $ociItems[] = [
                    'description' => 'Actuarial Gains/(Losses) on Defined Benefit Plans',
                    'amount' => $currentYearChange
                ];
                $total += $currentYearChange;
            }
        }
        
        return [
            'items' => $ociItems,
            'total' => $total
        ];
    }
    
    private function getYearOverYearChange($accountNumber)
    {
        $currentBalance = $this->getAccountBalance($accountNumber, $this->selectedYear);
        $priorBalance = $this->getAccountBalance($accountNumber, $this->selectedYear - 1);
        return $currentBalance - $priorBalance;
    }
    
    private function loadCashFlowStatement()
    {
        $items = FinancialStatementItem::where('financial_period_id', $this->financialPeriod->id)
            ->where('statement_type', 'cash_flow')
            ->orderBy('display_order')
            ->get();
        
        // Get net income from income statement
        $netIncome = $this->incomeStatementData['net_income'] ?? 0;
        
        $this->cashFlowData = [
            'net_income' => $netIncome,
            'operating' => $items->where('classification', 'operating_activity')->toArray(),
            'investing' => $items->where('classification', 'investing_activity')->toArray(),
            'financing' => $items->where('classification', 'financing_activity')->toArray(),
            'beginning_cash' => 0, // Will be loaded from snapshot
            'ending_cash' => 0, // Will be loaded from snapshot
            'net_change' => 0,
            'non_cash_activities' => [],
            'supplemental_cash' => [
                'interest_paid' => 0,
                'income_taxes_paid' => 0
            ]
        ];
        
        // Load additional data from snapshot
        $snapshot = \App\Models\FinancialStatementSnapshot::getLatest($this->financialPeriod->id, 'cash_flow');
        if ($snapshot) {
            $data = json_decode($snapshot->data, true);
            $this->cashFlowData['beginning_cash'] = $data['beginning_cash'] ?? 0;
            $this->cashFlowData['ending_cash'] = $data['ending_cash'] ?? 0;
            $this->cashFlowData['net_change'] = $data['net_cash_flow'] ?? 0;
            $this->cashFlowData['non_cash_activities'] = $data['non_cash_activities'] ?? [];
            if (isset($data['supplemental_cash'])) {
                $this->cashFlowData['supplemental_cash'] = $data['supplemental_cash'];
            }
        }
        
        // Calculate net change if not loaded from snapshot
        if ($this->cashFlowData['net_change'] == 0) {
            $operatingCash = array_sum(array_column($this->cashFlowData['operating'], 'amount')) + $netIncome;
            $investingCash = array_sum(array_column($this->cashFlowData['investing'], 'amount'));
            $financingCash = array_sum(array_column($this->cashFlowData['financing'], 'amount'));
            $this->cashFlowData['net_change'] = $operatingCash + $investingCash + $financingCash;
        }
        
        // Calculate ending cash if not set
        if ($this->cashFlowData['ending_cash'] == 0) {
            $this->cashFlowData['ending_cash'] = $this->cashFlowData['beginning_cash'] + $this->cashFlowData['net_change'];
        }
    }
    
    private function loadEquityStatement()
    {
        // Get current year
        $currentYear = $this->selectedYear;
        $previousYear = $currentYear - 1;
        
        // Get ALL equity accounts (Level 2 and Level 3)
        $allEquityAccounts = \DB::table('accounts')
            ->where('major_category_code', '3000')
            ->whereIn('account_level', ['2', '3'])
            ->where('status', 'ACTIVE')
            ->orderBy('account_number')
            ->get();
        
        // Organize accounts by category
        $equityComponents = [];
        
        foreach ($allEquityAccounts as $account) {
            // For Level 2 accounts, use them as main components
            if ($account->account_level == '2') {
                $equityComponents[$account->account_number] = [
                    'name' => $account->account_name,
                    'current_balance' => floatval($account->balance ?? 0),
                    'beginning_balance' => $this->getAccountBalancePriorYear($account->account_number, $previousYear),
                    'changes' => 0,
                    'sub_accounts' => []
                ];
            }
        }
        
        // Add Level 3 accounts as sub-accounts
        foreach ($allEquityAccounts as $account) {
            if ($account->account_level == '3' && isset($equityComponents[$account->parent_account_number])) {
                $equityComponents[$account->parent_account_number]['sub_accounts'][] = [
                    'name' => $account->account_name,
                    'number' => $account->account_number,
                    'balance' => floatval($account->balance ?? 0)
                ];
            }
        }
        
        // Debug logging
        \Log::info('Equity Components', ['components' => $equityComponents]);
        
        // Calculate totals for beginning and ending
        $beginningTotals = [];
        $endingTotals = [];
        
        foreach ($equityComponents as $key => $component) {
            $beginningTotals[$key] = $component['beginning_balance'];
            $endingTotals[$key] = $component['current_balance'];
        }
        
        $totalBeginning = array_sum($beginningTotals);
        $totalEnding = array_sum($endingTotals);
        
        // Get net income from income statement data (if loaded)
        $netIncome = $this->incomeStatementData['net_income'] ?? 0;
        
        // If income statement not loaded, calculate from accounts
        if ($netIncome == 0) {
            // Revenue accounts typically have credit balances (negative in accounting system)
            $totalRevenue = \DB::table('accounts')
                ->where('major_category_code', '4000')
                ->where('account_level', '2')
                ->sum('balance');
                
            // Expense accounts typically have debit balances (positive in accounting system)
            $totalExpenses = \DB::table('accounts')
                ->where('major_category_code', '5000')
                ->where('account_level', '2')
                ->sum('balance');
                
            // Net income = Revenue - Expenses (convert to absolute values)
            // If revenue is stored as negative (credit), take absolute value
            // If expenses are stored as positive (debit), they're already positive
            $netIncome = abs($totalRevenue) - abs($totalExpenses);
            
            // If the result is still showing the wrong sign, fix it
            if ($totalRevenue < 0 && $totalExpenses > 0) {
                // Revenue is negative (credit), expenses are positive (debit)
                $netIncome = abs($totalRevenue) - $totalExpenses;
            }
        }
        
        // Get dividends from dividend accounts (should be negative as they reduce equity)
        $dividends = \DB::table('accounts')
            ->where('parent_account_number', '010130003600')
            ->sum('balance');
        
        // Ensure dividends are shown as negative (reduction in equity)
        if ($dividends > 0) {
            $dividends = -$dividends;
        }
        
        // Get share buybacks from treasury shares
        $shareBuybacks = \DB::table('accounts')
            ->where('account_number', '0101300037003710')
            ->value('balance') ?? 0;
        
        // Get Other Comprehensive Income changes
        $otherComprehensiveIncome = \DB::table('accounts')
            ->where('parent_account_number', '010130003800')
            ->sum('balance');
        
        // Calculate changes for each component (simple difference for display)
        $changesByComponent = [];
        foreach ($equityComponents as $key => $component) {
            // Basic change is current minus beginning
            $change = $component['current_balance'] - $component['beginning_balance'];
            
            $changesByComponent[$key] = $change;
            $equityComponents[$key]['changes'] = $change;
        }
        
        // Calculate share issues from actual changes
        $shareIssuesCapital = 0;
        $shareIssuesPremium = 0;
        
        // Check for share capital changes from actual data
        foreach ($equityComponents as $component) {
            if (strpos($component['name'], 'SHARE CAPITAL') !== false) {
                $shareIssuesCapital = max(0, $component['current_balance'] - $component['beginning_balance']);
            }
            if (strpos($component['name'], 'SHARE PREMIUM') !== false) {
                $shareIssuesPremium = max(0, $component['current_balance'] - $component['beginning_balance']);
            }
        }
        
        // Get actual transfer to reserves from journal entries or account movements
        $transferToReserves = $this->getTransferToReserves($currentYear);
        
        // Get detailed transfer information by reserve type
        $transferDetails = $this->getDetailedReserveTransfers($currentYear);
        
        // Structure data for the view with dynamic components
        $this->equityStatementData = [
            'components' => $equityComponents,
            'beginning_totals' => $beginningTotals,
            'ending_totals' => $endingTotals,
            'total_beginning' => $totalBeginning,
            'total_ending' => $totalEnding,
            'net_income' => $netIncome,
            'dividends' => $dividends,
            'share_issues' => [
                'capital' => $shareIssuesCapital,
                'premium' => $shareIssuesPremium
            ],
            'share_buybacks' => $shareBuybacks,
            'other_comprehensive_income' => $otherComprehensiveIncome,
            'transfer_to_reserves' => $transferToReserves,
            'transfer_details' => $transferDetails,
            'changes_by_component' => $changesByComponent,
            'total_changes' => array_sum($changesByComponent),
            'disclosures' => $this->generateEquityDisclosures($currentYear, $transferDetails)
        ];
    }
    
    private function loadTrialBalance()
    {
        // First try to get from snapshot
        $snapshot = \App\Models\FinancialStatementSnapshot::getLatest($this->financialPeriod->id, 'trial_balance');
        if ($snapshot) {
            $data = json_decode($snapshot->data, true);
            $this->trialBalanceData = $data;
            return;
        }
        
        // Generate trial balance from general ledger
        $this->trialBalanceData = $this->generateTrialBalanceFromGL();
    }
    
    private function generateTrialBalanceFromGL()
    {
        $accounts = [
            'assets' => [],
            'liabilities' => [],
            'equity' => [],
            'revenue' => [],
            'expenses' => []
        ];
        
        $totalDebit = 0;
        $totalCredit = 0;
        
        // Get all active accounts with balances
        $allAccounts = \DB::table('accounts')
            ->where('status', 'ACTIVE')
            ->whereNotNull('balance')
            ->where('balance', '!=', 0)
            ->orderBy('account_number')
            ->get();
        
        foreach ($allAccounts as $account) {
            // Use the account balance directly
            $accountBalance = floatval($account->balance ?? 0);
            
            // Skip zero balance accounts
            if ($accountBalance == 0) {
                continue;
            }
            
            // Determine account category and normal balance
            $category = '';
            $normalBalance = '';
            
            switch ($account->major_category_code) {
                case '1000': // Assets
                    $category = 'assets';
                    $normalBalance = 'debit';
                    break;
                case '2000': // Liabilities
                    $category = 'liabilities';
                    $normalBalance = 'credit';
                    break;
                case '3000': // Equity
                    $category = 'equity';
                    $normalBalance = 'credit';
                    break;
                case '4000': // Revenue
                    $category = 'revenue';
                    $normalBalance = 'credit';
                    break;
                case '5000': // Expenses
                    $category = 'expenses';
                    $normalBalance = 'debit';
                    break;
            }
            
            // Add to appropriate category
            if ($category) {
                $accountData = [
                    'code' => $account->account_number,
                    'name' => $account->account_name,
                    'type' => ucfirst($category),
                    'balance' => $accountBalance
                ];
                
                $accounts[$category][] = $accountData;
                
                // Add to totals based on normal balance and actual balance
                // Assets and Expenses (normal debit balance)
                if ($normalBalance === 'debit') {
                    if ($accountBalance > 0) {
                        $totalDebit += abs($accountBalance);
                    } else {
                        $totalCredit += abs($accountBalance);
                    }
                }
                // Liabilities, Equity, Revenue (normal credit balance)
                else if ($normalBalance === 'credit') {
                    if ($accountBalance > 0) {
                        $totalCredit += abs($accountBalance);
                    } else {
                        $totalDebit += abs($accountBalance);
                    }
                }
            }
        }
        
        // Log for debugging
        \Log::info('Trial Balance Generated', [
            'total_accounts' => array_sum(array_map('count', $accounts)),
            'debit_total' => $totalDebit,
            'credit_total' => $totalCredit,
            'assets_count' => count($accounts['assets']),
            'liabilities_count' => count($accounts['liabilities']),
            'equity_count' => count($accounts['equity']),
            'revenue_count' => count($accounts['revenue']),
            'expenses_count' => count($accounts['expenses'])
        ]);
        
        return [
            'accounts' => $accounts,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit
            ],
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            'difference' => abs($totalDebit - $totalCredit),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'year' => $this->selectedYear
        ];
    }
    
    private function loadFinancialRatios()
    {
        // Check if we have the new financial_ratios table structure
        if (\Schema::hasColumn('financial_ratios', 'financial_period_id')) {
            $this->financialRatios = FinancialRatio::where('financial_period_id', $this->financialPeriod->id)
                ->orderBy('ratio_category')
                ->orderBy('ratio_name')
                ->get()
                ->groupBy('ratio_category')
                ->toArray();
        } else {
            // Use the existing financial_ratios table structure
            // Calculate ratios from the existing table
            $this->calculateFinancialRatiosFromExisting();
        }
    }
    
    private function loadFinancialNotes()
    {
        if (\Schema::hasTable('financial_statement_notes')) {
            $this->financialNotes = FinancialStatementNote::where('financial_period_id', $this->financialPeriod->id)
                ->orderBy('display_order')
                ->get()
                ->toArray();
        } else {
            $this->financialNotes = [];
        }
    }
    
    private function calculateFinancialRatiosFromExisting()
    {
        // Get the latest financial ratios from the existing table
        $latestRatio = \DB::table('financial_ratios')
            ->whereYear('end_of_financial_year_date', $this->selectedYear)
            ->orderBy('end_of_financial_year_date', 'desc')
            ->first();
            
        if (!$latestRatio) {
            $this->financialRatios = [];
            return;
        }
        
        // Calculate common financial ratios
        $ratios = [];
        
        // Liquidity Ratios
        if ($latestRatio->short_term_liabilities > 0) {
            $ratios['liquidity'][] = [
                'ratio_name' => 'Current Ratio',
                'value' => round($latestRatio->short_term_assets / $latestRatio->short_term_liabilities, 2),
                'formula' => 'Current Assets / Current Liabilities'
            ];
        }
        
        // Solvency Ratios
        if ($latestRatio->total_assets > 0) {
            $ratios['solvency'][] = [
                'ratio_name' => 'Core Capital Ratio',
                'value' => round(($latestRatio->core_capital / $latestRatio->total_assets) * 100, 2),
                'formula' => '(Core Capital / Total Assets) × 100'
            ];
        }
        
        // Profitability Ratios
        if ($latestRatio->income > 0) {
            $ratios['profitability'][] = [
                'ratio_name' => 'Expense Ratio',
                'value' => round(($latestRatio->expenses / $latestRatio->income) * 100, 2),
                'formula' => '(Expenses / Income) × 100'
            ];
            
            $ratios['profitability'][] = [
                'ratio_name' => 'Net Margin',
                'value' => round((($latestRatio->income - $latestRatio->expenses) / $latestRatio->income) * 100, 2),
                'formula' => '((Income - Expenses) / Income) × 100'
            ];
        }
        
        $this->financialRatios = $ratios;
    }
    
    public function switchStatement($statement)
    {
        $this->currentStatement = $statement;
        
        // Load year-end activities component data if needed
        if ($statement === 'year_end') {
            $this->emit('loadYearEndActivities', $this->selectedYear);
        }
    }
    
    public function toggleCategory($category)
    {
        if (in_array($category, $this->expandedCategories)) {
            $this->expandedCategories = array_diff($this->expandedCategories, [$category]);
        } else {
            $this->expandedCategories[] = $category;
        }
    }
    
    public function regenerateStatements()
    {
        $this->generateFinancialStatements();
        $this->loadFinancialData();
    }
    
    public function exportToExcel()
    {
        $data = [
            'period' => $this->financialPeriod,
            'balance_sheet' => $this->balanceSheetData,
            'income_statement' => $this->incomeStatementData,
            'cash_flow' => $this->cashFlowData,
            'equity_statement' => $this->equityStatementData,
            'ratios' => $this->financialRatios,
            'notes' => $this->financialNotes
        ];
        
        return Excel::download(
            new \App\Exports\IntegratedFinancialStatementsExport($data),
            "financial_statements_{$this->selectedYear}.xlsx"
        );
    }
    
    public function exportToPDF()
    {
        $data = [
            'company_name' => $this->companyName,
            'year' => $this->selectedYear,
            'period' => $this->financialPeriod,
            'balance_sheet' => $this->balanceSheetData,
            'income_statement' => $this->incomeStatementData,
            'cash_flow' => $this->cashFlowData,
            'equity_statement' => $this->equityStatementData,
            'ratios' => $this->financialRatios,
            'notes' => $this->financialNotes
        ];
        
        $pdf = PDF::loadView('exports.financial-statements-pdf', $data)
            ->setPaper('a4', 'portrait');
        
        return $pdf->download("financial_statements_{$this->selectedYear}.pdf");
    }
    
    public function verifyRelationships()
    {
        $relationships = StatementRelationship::where('financial_period_id', $this->financialPeriod->id)->get();
        $errors = [];
        
        foreach ($relationships as $relationship) {
            if (!$relationship->verifyIntegrity()) {
                $errors[] = "Relationship error: {$relationship->source_statement}.{$relationship->source_item} -> {$relationship->target_statement}.{$relationship->target_item}";
            }
        }
        
        if (empty($errors)) {
            session()->flash('success', 'All financial statement relationships are valid.');
        } else {
            session()->flash('error', 'Relationship errors found: ' . implode(', ', $errors));
        }
    }
    
    public function formatNumber($number)
    {
        // Show 0.00 instead of dash for zero values to maintain consistency in financial statements
        if ($number == 0 || $number === null) {
            return '0.00';
        }
        
        $isNegative = $number < 0;
        $absNumber = abs($number);
        
        // Always show full number with 2 decimal places
        $formatted = number_format($absNumber, 2);
        
        return $isNegative ? '(' . $formatted . ')' : $formatted;
    }
    
    public function showNote($noteNumber, $accountNumber, $accountName)
    {
        $this->noteNumber = $noteNumber;
        $this->noteTitle = $accountName;
        
        // Load note content based on account number (stable identifier)
        $this->noteContent = $this->getNoteContent($accountNumber, $accountName);
        
        $this->showNoteModal = true;
    }
    
    public function closeNote()
    {
        $this->showNoteModal = false;
        $this->noteNumber = '';
        $this->noteTitle = '';
        $this->noteContent = [];
    }
    
    public function showIncomeNote($noteNumber, $accountNumber, $accountName)
    {
        $this->incomeNoteNumber = $noteNumber;
        $this->incomeNoteTitle = $accountName;
        
        // Load income note content based on account number or special keys
        $this->incomeNoteContent = $this->getIncomeNoteContent($accountNumber, $accountName);
        
        $this->showIncomeNoteModal = true;
    }
    
    public function closeIncomeNote()
    {
        $this->showIncomeNoteModal = false;
        $this->incomeNoteNumber = '';
        $this->incomeNoteTitle = '';
        $this->incomeNoteContent = [];
    }
    
    private function getIncomeNoteContent($accountNumber, $accountName)
    {
        // Handle special analysis notes
        if ($accountNumber == 'NET_INCOME' || $accountNumber == 'TOTAL_COMPREHENSIVE') {
            return $this->getNetIncomeAnalysis();
        }
        
        // Handle OCI items
        if (strpos($accountNumber, 'OCI_') === 0) {
            return $this->getOCIAnalysis($accountName);
        }
        
        // Handle regular account details - find child accounts
        $childAccounts = \DB::table('accounts')
            ->where('parent_account_number', '=', $accountNumber)
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->get();
        
        if ($childAccounts->count() > 0) {
            // This is a parent account, show child accounts
            $noteContent = [];
            foreach ($childAccounts as $child) {
                $balance = $this->getAccountBalance($child->account_number, $this->selectedYear);
                if ($balance != 0) {
                    $noteContent[] = [
                        'account_number' => $child->account_number,
                        'account_name' => $child->account_name,
                        'balance' => $balance
                    ];
                }
            }
            return $noteContent;
        } else {
            // This is a detail account, show just this account
            $account = \DB::table('accounts')
                ->where('account_number', $accountNumber)
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->first();
                
            if ($account) {
                $balance = $this->getAccountBalance($account->account_number, $this->selectedYear);
                return [
                    [
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'balance' => $balance
                    ]
                ];
            }
        }
        
        return [];
    }
    
    private function getNetIncomeAnalysis()
    {
        // Return analysis data for net income
        return [
            'revenue_breakdown' => $this->incomeStatementData['revenue'] ?? [],
            'expense_breakdown' => $this->incomeStatementData['expenses'] ?? [],
            'totals' => [
                'total_revenue' => $this->incomeStatementData['total_revenue'] ?? 0,
                'total_expenses' => $this->incomeStatementData['total_expenses'] ?? 0,
                'net_income' => $this->incomeStatementData['net_income'] ?? 0
            ]
        ];
    }
    
    private function getOCIAnalysis($description)
    {
        // Find the specific OCI item and return analysis
        $ociItems = $this->incomeStatementData['other_comprehensive_income']['items'] ?? [];
        
        foreach ($ociItems as $item) {
            if ($item['description'] === $description) {
                return [
                    [
                        'account_number' => 'OCI',
                        'account_name' => $description,
                        'balance' => $item['amount']
                    ]
                ];
            }
        }
        
        return [];
    }
    
    private function getAccountBalancePriorYear($accountNumber, $year)
    {
        // Check general_ledger for prior year balance - ACTUAL DATA ONLY
        if (\Schema::hasTable('general_ledger')) {
            $priorYearBalance = \DB::table('general_ledger')
                ->where('record_on_account_number', $accountNumber)
                ->whereYear('created_at', $year)
                ->selectRaw('SUM(COALESCE(credit, 0) - COALESCE(debit, 0)) as balance')
                ->value('balance');
            
            return floatval($priorYearBalance ?? 0);
        }
        
        // No data means ZERO - no assumptions with financial data
        return 0;
    }
    
    private function getTransferToReserves($year)
    {
        // Only check the dedicated transfer_to_reserves table - NO FALLBACKS
        if (\Schema::hasTable('transfer_to_reserves')) {
            $totalTransfers = \DB::table('transfer_to_reserves')
                ->where('financial_year', $year)
                ->where('status', 'POSTED')
                ->sum('amount');
            
            return floatval($totalTransfers ?? 0);
        }
        
        // No data means ZERO - this is financial data, no assumptions
        return 0;
    }
    
    private function getDetailedReserveTransfers($year)
    {
        $details = [];
        
        if (\Schema::hasTable('transfer_to_reserves')) {
            $transfers = \DB::table('transfer_to_reserves')
                ->where('financial_year', $year)
                ->where('status', 'POSTED')
                ->select('transfer_type', 'destination_reserve_account_name', \DB::raw('SUM(amount) as total_amount'))
                ->groupBy('transfer_type', 'destination_reserve_account_name')
                ->get();
            
            foreach ($transfers as $transfer) {
                $details[] = [
                    'type' => $transfer->transfer_type,
                    'account_name' => $transfer->destination_reserve_account_name,
                    'amount' => floatval($transfer->total_amount)
                ];
            }
        }
        
        return $details;
    }
    
    private function generateEquityDisclosures($year, $transferDetails)
    {
        $disclosures = [
            'Share capital represents members\' contributions to the cooperative'
        ];
        
        // Add transfer to reserves disclosure if applicable
        if (!empty($transferDetails)) {
            $totalTransfers = array_sum(array_column($transferDetails, 'amount'));
            $disclosures[] = 'Transfers to reserves of ' . $this->formatNumber($totalTransfers) . ' were made during the year';
            
            // Check for statutory transfers
            $statutoryTransfers = array_filter($transferDetails, function($transfer) {
                return strpos($transfer['type'], 'STATUTORY') !== false;
            });
            
            if (!empty($statutoryTransfers)) {
                $disclosures[] = 'Statutory reserve transfers were made in compliance with regulatory requirements';
            }
        }
        
        // Add dividend disclosure if applicable
        if (($this->equityStatementData['dividends'] ?? 0) != 0) {
            $disclosures[] = 'Dividends of ' . $this->formatNumber(abs($this->equityStatementData['dividends'])) . ' were declared and paid during the year';
        }
        
        // Add OCI disclosure if applicable
        if (($this->equityStatementData['other_comprehensive_income'] ?? 0) != 0) {
            $disclosures[] = 'Other comprehensive income includes revaluation gains and actuarial adjustments';
        }
        
        return $disclosures;
    }
    
    private function getNoteContent($accountNumber, $accountName = '')
    {
        // Get the breakdown of accounts for this line item from the actual database
        $content = [];
        
        // If account number is empty, try to find it by name (fallback)
        if (empty($accountNumber) && !empty($accountName)) {
            $parentAccount = \DB::table('accounts')
                ->where('account_name', $accountName)
                ->where('account_use', 'internal')
                ->first();
            
            $accountNumber = $parentAccount ? $parentAccount->account_number : null;
        }
        
        if ($accountNumber) {
            // Get all accounts where parent_account_number is this account's number
            $childAccounts = \DB::table('accounts')
                ->where('parent_account_number', $accountNumber)
                ->where('account_use', 'internal')
                ->orderBy('account_number')
                ->get();
            
            foreach ($childAccounts as $child) {
                $content[] = [
                    'account_number' => $child->account_number,
                    'account_name' => $child->account_name,
                    'balance' => $child->balance ?? 0
                ];
            }
            
            // If no children found, get the account itself
            if (empty($content)) {
                $parentAccount = \DB::table('accounts')
                    ->where('account_number', $accountNumber)
                    ->where('account_use', 'internal')
                    ->first();
                    
                if ($parentAccount) {
                    $content[] = [
                        'account_number' => $parentAccount->account_number,
                        'account_name' => $parentAccount->account_name,
                        'balance' => $parentAccount->balance ?? 0
                    ];
                }
            }
        }
        
        // If still no content, show a message
        if (empty($content)) {
            $content[] = [
                'account_number' => 'N/A',
                'account_name' => 'No detailed breakdown available for: ' . ($accountName ?: $accountNumber),
                'balance' => 0
            ];
        }
        
        return $content;
    }
    
    // New methods for handling missing features
    
    private function calculateCurrentYearNetIncome($year)
    {
        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Get all revenue accounts (4000 series)
        $revenue = \DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.major_category_code', '4000')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(\DB::raw('SUM(CAST(gl.credit AS DECIMAL(20,2))) - SUM(CAST(gl.debit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
        
        // Get all expense accounts (5000 series)
        $expenses = \DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.major_category_code', '5000')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(\DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
        
        return $revenue - $expenses;
    }
    
    private function isContraAccount($account)
    {
        // Define contra account patterns
        $contraPatterns = [
            'accumulated depreciation',
            'allowance for doubtful',
            'allowance for bad debt',
            'provision for',
            'discount on bonds',
            'treasury stock',
            'treasury shares',
            'drawings',
            'contra'
        ];
        
        $accountNameLower = strtolower($account->account_name ?? '');
        
        foreach ($contraPatterns as $pattern) {
            if (str_contains($accountNameLower, $pattern)) {
                return true;
            }
        }
        
        // Check if account has a specific contra flag in account_type
        if (isset($account->account_type) && str_contains(strtolower($account->account_type), 'contra')) {
            return true;
        }
        
        return false;
    }
    
    private function getOpeningBalance($accountNumber, $year)
    {
        // Method 1: Check if there's an opening balance entry
        $openingEntry = \DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->whereIn('transaction_type', ['OPENING_BALANCE', 'OPENING'])
            ->whereYear('created_at', $year)
            ->select(
                \DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit'),
                \DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit')
            )
            ->first();
        
        if ($openingEntry && ($openingEntry->total_debit > 0 || $openingEntry->total_credit > 0)) {
            // Get account type to determine which is positive
            $accountInfo = \DB::table('accounts')
                ->where('account_number', $accountNumber)
                ->first();
                
            if ($accountInfo && $accountInfo->major_category_code == '1000') {
                return ($openingEntry->total_debit ?? 0) - ($openingEntry->total_credit ?? 0);
            } else {
                return ($openingEntry->total_credit ?? 0) - ($openingEntry->total_debit ?? 0);
            }
        }
        
        // Method 2: Calculate from previous year's closing if not the first year
        if ($year > 2020) { // Assuming system started in 2020
            return $this->getClosingBalance($accountNumber, $year - 1);
        }
        
        // Method 3: Check accounts table for initial balance field if it exists
        if (\Schema::hasColumn('accounts', 'opening_balance')) {
            $account = \DB::table('accounts')
                ->where('account_number', $accountNumber)
                ->first();
            
            return $account->opening_balance ?? 0;
        }
        
        return 0;
    }
    
    private function getClosingBalance($accountNumber, $year)
    {
        // Get balance as of Dec 31 of the year
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        $result = \DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->where('created_at', '<=', $endDate)
            ->select(
                \DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit'),
                \DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit')
            )
            ->first();
        
        // Get account info to determine type
        $accountInfo = \DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
            
        if (!$accountInfo) {
            return 0;
        }
            
        if ($accountInfo->major_category_code == '1000') {
            return ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
        } else {
            return ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
        }
    }
    
    private function applyYearEndAdjustments(&$assetsData, &$liabilitiesData, &$equityData)
    {
        $year = $this->selectedYear;
        
        // Check for year-end adjustment entries
        $adjustments = \DB::table('general_ledger')
            ->whereIn('transaction_type', ['YEAR_END_ADJUSTMENT', 'CLOSING_ENTRY', 'ACCRUAL', 'PREPAYMENT', 'DEPRECIATION'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', 12)
            ->get();
        
        // Apply depreciation if not already posted
        $this->applyDepreciationAdjustment($assetsData, $year);
        
        // Apply accruals and prepayments
        $this->applyAccrualsAndPrepayments($assetsData, $liabilitiesData, $year);
    }
    
    private function applyDepreciationAdjustment(&$assetsData, $year)
    {
        // Check if depreciation for the year has been recorded
        $depreciationRecorded = \DB::table('general_ledger')
            ->where('transaction_type', 'DEPRECIATION')
            ->whereYear('created_at', $year)
            ->exists();
        
        if (!$depreciationRecorded) {
            // Find or create accumulated depreciation account in non-current assets
            $accumDepFound = false;
            foreach ($assetsData['non_current'] as &$asset) {
                if (str_contains(strtolower($asset['account_name'] ?? ''), 'accumulated depreciation')) {
                    $accumDepFound = true;
                    // This would be updated with calculated depreciation
                    break;
                }
            }
            
            if (!$accumDepFound) {
                // Add accumulated depreciation as a contra asset
                $assetsData['non_current'][] = [
                    'account_number' => '1999',
                    'account_name' => 'Accumulated Depreciation (Calculated)',
                    'account_level' => '2',
                    'amount' => 0, // Would be calculated based on depreciation policy
                    'years' => [
                        $year => 0,
                        $year - 1 => 0
                    ],
                    'is_contra' => true,
                    'is_calculated' => true
                ];
            }
        }
    }
    
    private function applyAccrualsAndPrepayments(&$assetsData, &$liabilitiesData, $year)
    {
        // Check for accruals (expenses incurred but not yet paid)
        $accruals = \DB::table('general_ledger')
            ->where('transaction_type', 'ACCRUAL')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', 12)
            ->select(
                \DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit'),
                \DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit')
            )
            ->first();
        
        if ($accruals && ($accruals->total_credit > 0 || $accruals->total_debit > 0)) {
            // Add to current liabilities if not already there
            $accrualFound = false;
            foreach ($liabilitiesData['current'] as &$liability) {
                if (str_contains(strtolower($liability['account_name'] ?? ''), 'accrued')) {
                    $accrualFound = true;
                    break;
                }
            }
            
            if (!$accrualFound) {
                $liabilitiesData['current'][] = [
                    'account_number' => '2999',
                    'account_name' => 'Accrued Expenses',
                    'account_level' => '2',
                    'amount' => $accruals->total_credit - $accruals->total_debit,
                    'years' => [
                        $year => $accruals->total_credit - $accruals->total_debit,
                        $year - 1 => 0
                    ],
                    'is_calculated' => true
                ];
            }
        }
    }
    
    private function applyIntercompanyEliminations(&$assetsData, &$liabilitiesData)
    {
        // Get all inter-company accounts
        $intercompanyAccounts = \DB::table('accounts')
            ->where(function ($query) {
                $query->where('account_name', 'LIKE', '%Intercompany%')
                      ->orWhere('account_name', 'LIKE', '%Inter-branch%')
                      ->orWhere('account_name', 'LIKE', '%Inter-company%');
            })
            ->where('status', 'ACTIVE')
            ->get();
        
        foreach ($intercompanyAccounts as $account) {
            // Remove from assets
            $assetsData['current'] = array_filter($assetsData['current'], function($item) use ($account) {
                return $item['account_number'] !== $account->account_number;
            });
            
            $assetsData['non_current'] = array_filter($assetsData['non_current'], function($item) use ($account) {
                return $item['account_number'] !== $account->account_number;
            });
            
            // Remove from liabilities
            $liabilitiesData['current'] = array_filter($liabilitiesData['current'], function($item) use ($account) {
                return $item['account_number'] !== $account->account_number;
            });
            
            $liabilitiesData['non_current'] = array_filter($liabilitiesData['non_current'], function($item) use ($account) {
                return $item['account_number'] !== $account->account_number;
            });
        }
        
        // Re-index arrays
        $assetsData['current'] = array_values($assetsData['current']);
        $assetsData['non_current'] = array_values($assetsData['non_current']);
        $liabilitiesData['current'] = array_values($liabilitiesData['current']);
        $liabilitiesData['non_current'] = array_values($liabilitiesData['non_current']);
    }
    
    private function loadOffBalanceSheetItems()
    {
        $year = $this->selectedYear;
        $this->offBalanceSheetItems = [];
        
        // Check if contingent_liabilities table exists
        if (\Schema::hasTable('contingent_liabilities')) {
            $contingentLiabilities = \DB::table('contingent_liabilities')
                ->where('status', 'ACTIVE')
                ->whereYear('as_of_date', $year)
                ->get();
                
            $this->offBalanceSheetItems['contingent_liabilities'] = $contingentLiabilities;
        }
        
        // Check if guarantees table exists
        if (\Schema::hasTable('guarantees')) {
            $guarantees = \DB::table('guarantees')
                ->where('status', 'ACTIVE')
                ->whereDate('expiry_date', '>=', "$year-12-31")
                ->get();
                
            $this->offBalanceSheetItems['guarantees'] = $guarantees;
        }
        
        // Check if lease_commitments table exists
        if (\Schema::hasTable('lease_commitments')) {
            $operatingLeases = \DB::table('lease_commitments')
                ->where('lease_type', 'OPERATING')
                ->where('status', 'ACTIVE')
                ->get();
                
            $this->offBalanceSheetItems['operating_leases'] = $operatingLeases;
        }
        
        // Calculate total exposure
        $totalExposure = 0;
        
        if (isset($this->offBalanceSheetItems['contingent_liabilities'])) {
            $totalExposure += $this->offBalanceSheetItems['contingent_liabilities']->sum('amount');
        }
        
        if (isset($this->offBalanceSheetItems['guarantees'])) {
            $totalExposure += $this->offBalanceSheetItems['guarantees']->sum('guaranteed_amount');
        }
        
        if (isset($this->offBalanceSheetItems['operating_leases'])) {
            $totalExposure += $this->offBalanceSheetItems['operating_leases']->sum('total_commitment');
        }
        
        $this->offBalanceSheetItems['total_exposure'] = $totalExposure;
    }
    
    // Notes-related methods
    public function toggleNote($noteKey)
    {
        if (in_array($noteKey, $this->expandedNotes)) {
            $this->expandedNotes = array_diff($this->expandedNotes, [$noteKey]);
        } else {
            $this->expandedNotes[] = $noteKey;
        }
    }
    
    public function showAccountDetails($accountNumber, $accountName)
    {
        $this->selectedAccountForDetail = [
            'number' => $accountNumber,
            'name' => $accountName
        ];
        
        // Load account transactions (placeholder - implement based on your data structure)
        $this->accountTransactions = $this->loadAccountTransactions($accountNumber);
        $this->showAccountDetail = true;
    }
    
    public function closeAccountDetail()
    {
        $this->showAccountDetail = false;
        $this->selectedAccountForDetail = ['name' => '', 'number' => ''];
        $this->accountTransactions = [];
    }
    
    private function loadAccountTransactions($accountNumber)
    {
        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-01-01")->startOfYear();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-12-31")->endOfYear();
        
        // Load actual transactions from general_ledger using same pattern as StatementOfFinancialPosition
        $transactions = \DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(100) // Limit to recent 100 transactions
            ->get();
            
        $accountTransactions = [];
        
        foreach ($transactions as $transaction) {
            $accountTransactions[] = [
                'date' => \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d'),
                'reference' => $transaction->reference_number ?? $transaction->transaction_number ?? '',
                'description' => $transaction->description ?? $transaction->narration ?? 'Transaction',
                'debit' => floatval($transaction->debit ?? 0),
                'credit' => floatval($transaction->credit ?? 0)
            ];
        }
        
        return $accountTransactions;
    }
    
    public function exportNotesPDF()
    {
        $data = [
            'companyName' => $this->companyName,
            'selectedYear' => $this->selectedYear,
            'corporateInfo' => $this->corporateInfo,
            'accountingPolicies' => $this->accountingPolicies,
            'significantEstimates' => $this->significantEstimates,
            'financialData' => $this->financialData,
            'comparisonYears' => $this->comparisonYears,
            'reportingDate' => $this->reportingDate,
            'expandedNotes' => $this->expandedNotes,
        ];
        
        $pdf = Pdf::loadView('pdf.notes-to-financial-statements', $data);
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->stream();
        }, 'notes-to-financial-statements-' . $this->selectedYear . '.pdf');
    }
    
    public function printNotes()
    {
        $this->dispatchBrowserEvent('print-notes');
    }

    public function render()
    {
        return view('livewire.accounting.integrated-financial-statements');
    }
}