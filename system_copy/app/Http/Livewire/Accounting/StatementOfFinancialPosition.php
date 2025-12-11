<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use App\Exports\StatementOfFinancialPositionExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StatementOfFinancialPosition extends Component
{
    // Period selection
    public $selectedYear;
    public $comparisonYears = [];
    public $reportingDate;
    public $companyName = 'NBC SACCOS LTD';
    
    // Display options
    public $expandedCategories = [];
    public $expandedSubcategories = [];
    public $showAccountDetail = false;
    public $selectedAccountForDetail = null;
    public $accountTransactions = [];
    public $viewLevel = 2; // Default to L2 view
    
    // Note modal properties
    public $showNoteModal = false;
    public $noteNumber = '';
    public $noteTitle = '';
    public $noteContent = '';
    
    // Financial data
    public $assetsData = [];
    public $liabilitiesData = [];
    public $equityData = [];
    public $summaryData = [];
    
    public function mount()
    {
        // Set current year as default
        $this->selectedYear = date('Y');
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->reportingDate = Carbon::create($this->selectedYear, 12, 31)->format('d F Y');
        
        $this->loadFinancialData();
    }
    
    public function updatedSelectedYear()
    {
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->reportingDate = Carbon::create($this->selectedYear, 12, 31)->format('d F Y');
        $this->loadFinancialData();
    }
    
    public function toggleCategory($categoryCode)
    {
        if (in_array($categoryCode, $this->expandedCategories)) {
            $key = array_search($categoryCode, $this->expandedCategories);
            unset($this->expandedCategories[$key]);
            $this->expandedCategories = array_values($this->expandedCategories);
        } else {
            $this->expandedCategories[] = $categoryCode;
        }
        
        $this->loadFinancialData();
    }
    
    public function toggleSubcategory($subcategoryCode)
    {
        if (in_array($subcategoryCode, $this->expandedSubcategories)) {
            $key = array_search($subcategoryCode, $this->expandedSubcategories);
            unset($this->expandedSubcategories[$key]);
            $this->expandedSubcategories = array_values($this->expandedSubcategories);
        } else {
            $this->expandedSubcategories[] = $subcategoryCode;
        }
        
        $this->loadFinancialData();
    }
    
    public function showAccountDetails($accountNumber, $accountName)
    {
        $this->selectedAccountForDetail = [
            'number' => $accountNumber,
            'name' => $accountName
        ];
        
        // Load recent transactions for this account
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-12-31")->endOfYear();
        
        $this->accountTransactions = DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function($entry) {
                return [
                    'date' => Carbon::parse($entry->created_at)->format('d/m/Y'),
                    'reference' => $entry->reference_number ?? $entry->transaction_number,
                    'description' => $entry->description ?? $entry->narration ?? 'Transaction',
                    'debit' => $entry->debit,
                    'credit' => $entry->credit,
                ];
            })
            ->toArray();
        
        $this->showAccountDetail = true;
    }
    
    public function closeAccountDetail()
    {
        $this->showAccountDetail = false;
        $this->selectedAccountForDetail = null;
        $this->accountTransactions = [];
    }
    
    public function loadFinancialData()
    {
        // Load Assets (1000 series)
        $this->assetsData = $this->getAccountCategoryData('1000', 'ASSETS');
        
        // Load Liabilities (2000 series)
        $this->liabilitiesData = $this->getAccountCategoryData('2000', 'LIABILITIES');
        
        // Load Equity (3000 series)
        $this->equityData = $this->getAccountCategoryData('3000', 'EQUITY');
        
        // Calculate summary totals
        $this->calculateSummary();
    }
    
    private function getAccountCategoryData($majorCode, $categoryName)
    {
        $data = [
            'current' => [],
            'non_current' => [],
            'total' => []
        ];
        
        // Get L2 accounts for this major category
        $l2Accounts = DB::table('accounts')
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
                'years' => [],
                'children' => []
            ];
            
            // Get balances for comparison years
            foreach ($this->comparisonYears as $year) {
                $balance = $this->getAccountBalance($l2Account->account_number, $year);
                $accountData['years'][$year] = $balance;
                $totalByYear[$year] += $balance;
            }
            
            // Get L3 children if category is expanded
            if (in_array($l2Account->account_number, $this->expandedCategories)) {
                $l3Accounts = DB::table('accounts')
                    ->where('parent_account_number', $l2Account->account_number)
                    ->where('account_level', '3')
                    ->where('status', 'ACTIVE')
                    ->whereNull('deleted_at')
                    ->orderBy('account_number')
                    ->get();
                
                foreach ($l3Accounts as $l3Account) {
                    $l3Data = [
                        'account_number' => $l3Account->account_number,
                        'account_name' => $l3Account->account_name,
                        'account_level' => $l3Account->account_level,
                        'years' => [],
                        'children' => []
                    ];
                    
                    foreach ($this->comparisonYears as $year) {
                        $l3Data['years'][$year] = $this->getAccountBalance($l3Account->account_number, $year);
                    }
                    
                    // Get L4 children if subcategory is expanded
                    if (in_array($l3Account->account_number, $this->expandedSubcategories)) {
                        $l4Accounts = DB::table('accounts')
                            ->where('parent_account_number', $l3Account->account_number)
                            ->where('account_level', '4')
                            ->where('status', 'ACTIVE')
                            ->whereNull('deleted_at')
                            ->orderBy('account_number')
                            ->get();
                        
                        foreach ($l4Accounts as $l4Account) {
                            $l4Data = [
                                'account_number' => $l4Account->account_number,
                                'account_name' => $l4Account->account_name,
                                'account_level' => $l4Account->account_level,
                                'years' => []
                            ];
                            
                            foreach ($this->comparisonYears as $year) {
                                $l4Data['years'][$year] = $this->getAccountBalance($l4Account->account_number, $year);
                            }
                            
                            $l3Data['children'][] = $l4Data;
                        }
                    }
                    
                    $accountData['children'][] = $l3Data;
                }
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
        // For example, 1100-1499 might be current assets
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
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Get all child accounts
        $allAccounts = [$accountNumber];
        $childAccounts = DB::table('accounts')
            ->where('parent_account_number', 'LIKE', $accountNumber . '%')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->pluck('account_number');
        
        $allAccounts = array_merge($allAccounts, $childAccounts->toArray());
        
        // Get total from general ledger
        $result = DB::table('general_ledger')
            ->whereIn('record_on_account_number', $allAccounts)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit'),
                DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit')
            )
            ->first();
        
        // Get account info to determine type
        $accountInfo = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        if (!$accountInfo) {
            return 0;
        }
        
        // Determine balance based on account type
        $majorCode = $accountInfo->major_category_code;
        
        // Assets: Debit balance (debit - credit)
        // Liabilities and Equity: Credit balance (credit - debit)
        if ($majorCode == '1000') {
            return ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
        } else {
            return ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
        }
    }
    
    private function calculateSummary()
    {
        $this->summaryData = [];
        
        foreach ($this->comparisonYears as $year) {
            $totalAssets = $this->assetsData['total'][$year] ?? 0;
            $totalLiabilities = $this->liabilitiesData['total'][$year] ?? 0;
            $totalEquity = $this->equityData['total'][$year] ?? 0;
            
            $this->summaryData[$year] = [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_equity' => $totalLiabilities + $totalEquity,
                'balance_check' => $totalAssets - ($totalLiabilities + $totalEquity)
            ];
        }
    }
    
    public function formatNumber($number)
    {
        if ($number < 0) {
            return '(' . number_format(abs($number), 2) . ')';
        }
        return number_format($number, 2);
    }
    
    public function calculateVariance($currentValue, $previousValue)
    {
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }
        return (($currentValue - $previousValue) / abs($previousValue)) * 100;
    }
    
    public function exportToExcel()
    {
        try {
            // Prepare the data for export
            $exportData = $this->prepareExportData();
            
            $fileName = 'statement_of_financial_position_' . $this->selectedYear . '_' . date('Y_m_d_His') . '.xlsx';
            
            return Excel::download(
                new StatementOfFinancialPositionExport($exportData, "{$this->selectedYear}-12-31"),
                $fileName
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to export to Excel: ' . $e->getMessage());
        }
    }
    
    public function exportToPDF()
    {
        try {
            // Prepare the data for export
            $data = [
                'companyName' => $this->companyName,
                'reportingDate' => $this->reportingDate,
                'selectedYear' => $this->selectedYear,
                'comparisonYears' => $this->comparisonYears,
                'assetsData' => $this->assetsData,
                'liabilitiesData' => $this->liabilitiesData,
                'equityData' => $this->equityData,
                'summaryData' => $this->summaryData,
            ];
            
            $pdf = PDF::loadView('exports.statement-of-financial-position-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $fileName = 'statement_of_financial_position_' . $this->selectedYear . '_' . date('Y_m_d_His') . '.pdf';
            
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to export to PDF: ' . $e->getMessage());
        }
    }
    
    private function prepareExportData()
    {
        $data = [
            'assets' => [
                'categories' => [],
                'total' => $this->assetsData['total'][$this->selectedYear] ?? 0
            ],
            'liabilities' => [
                'categories' => [],
                'total' => $this->liabilitiesData['total'][$this->selectedYear] ?? 0
            ],
            'equity' => [
                'categories' => [],
                'total' => $this->equityData['total'][$this->selectedYear] ?? 0
            ],
            'totals' => [
                'total_assets' => $this->assetsData['total'][$this->selectedYear] ?? 0,
                'total_liabilities' => $this->liabilitiesData['total'][$this->selectedYear] ?? 0,
                'total_equity' => $this->equityData['total'][$this->selectedYear] ?? 0,
                'total_liabilities_and_equity' => ($this->liabilitiesData['total'][$this->selectedYear] ?? 0) + ($this->equityData['total'][$this->selectedYear] ?? 0),
                'difference' => 0,
                'is_balanced' => true
            ]
        ];
        
        // Process assets
        foreach (['current', 'non_current'] as $section) {
            foreach ($this->assetsData[$section] ?? [] as $asset) {
                $categoryCode = $asset['account_number'];
                if (!isset($data['assets']['categories'][$categoryCode])) {
                    $data['assets']['categories'][$categoryCode] = [
                        'name' => $asset['account_name'],
                        'accounts' => [],
                        'subtotal' => 0
                    ];
                }
                
                // Add main account
                $data['assets']['categories'][$categoryCode]['accounts'][] = [
                    'account_name' => $asset['account_name'],
                    'current_balance' => $asset['years'][$this->selectedYear] ?? 0
                ];
                $data['assets']['categories'][$categoryCode]['subtotal'] += $asset['years'][$this->selectedYear] ?? 0;
                
                // Add child accounts if any
                foreach ($asset['children'] ?? [] as $child) {
                    $data['assets']['categories'][$categoryCode]['accounts'][] = [
                        'account_name' => '  ' . $child['account_name'],
                        'current_balance' => $child['years'][$this->selectedYear] ?? 0
                    ];
                }
            }
        }
        
        // Process liabilities
        foreach (['current', 'non_current'] as $section) {
            foreach ($this->liabilitiesData[$section] ?? [] as $liability) {
                $categoryCode = $liability['account_number'];
                if (!isset($data['liabilities']['categories'][$categoryCode])) {
                    $data['liabilities']['categories'][$categoryCode] = [
                        'name' => $liability['account_name'],
                        'accounts' => [],
                        'subtotal' => 0
                    ];
                }
                
                // Add main account
                $data['liabilities']['categories'][$categoryCode]['accounts'][] = [
                    'account_name' => $liability['account_name'],
                    'current_balance' => $liability['years'][$this->selectedYear] ?? 0
                ];
                $data['liabilities']['categories'][$categoryCode]['subtotal'] += $liability['years'][$this->selectedYear] ?? 0;
                
                // Add child accounts if any
                foreach ($liability['children'] ?? [] as $child) {
                    $data['liabilities']['categories'][$categoryCode]['accounts'][] = [
                        'account_name' => '  ' . $child['account_name'],
                        'current_balance' => $child['years'][$this->selectedYear] ?? 0
                    ];
                }
            }
        }
        
        // Process equity
        foreach (['current', 'non_current'] as $section) {
            foreach ($this->equityData[$section] ?? [] as $equity) {
                $categoryCode = $equity['account_number'];
                if (!isset($data['equity']['categories'][$categoryCode])) {
                    $data['equity']['categories'][$categoryCode] = [
                        'name' => $equity['account_name'],
                        'accounts' => [],
                        'subtotal' => 0
                    ];
                }
                
                // Add main account
                $data['equity']['categories'][$categoryCode]['accounts'][] = [
                    'account_name' => $equity['account_name'],
                    'current_balance' => $equity['years'][$this->selectedYear] ?? 0
                ];
                $data['equity']['categories'][$categoryCode]['subtotal'] += $equity['years'][$this->selectedYear] ?? 0;
                
                // Add child accounts if any
                foreach ($equity['children'] ?? [] as $child) {
                    $data['equity']['categories'][$categoryCode]['accounts'][] = [
                        'account_name' => '  ' . $child['account_name'],
                        'current_balance' => $child['years'][$this->selectedYear] ?? 0
                    ];
                }
            }
        }
        
        // Calculate balance check
        $data['totals']['difference'] = $data['totals']['total_assets'] - $data['totals']['total_liabilities_and_equity'];
        $data['totals']['is_balanced'] = abs($data['totals']['difference']) < 0.01;
        
        return $data;
    }
    
    public function showNote($noteNumber, $noteTitle)
    {
        $this->noteNumber = $noteNumber;
        $this->noteTitle = $noteTitle;
        
        // Load note content based on the title
        $this->noteContent = $this->getNoteContent($noteTitle);
        $this->showNoteModal = true;
    }
    
    public function closeNote()
    {
        $this->showNoteModal = false;
        $this->noteNumber = '';
        $this->noteTitle = '';
        $this->noteContent = '';
    }
    
    private function getNoteContent($noteTitle)
    {
        // Get the relevant accounts and transactions for the note
        $startDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "{$this->selectedYear}-12-31")->endOfYear();
        
        // First, find the L2 account that matches this note title
        $l2Account = DB::table('accounts')
            ->where('account_name', $noteTitle)
            ->where('account_level', '2')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->first();
        
        $content = [];
        
        if ($l2Account) {
            // Get all child accounts (L3 and L4) under this L2 account
            $childAccounts = DB::table('accounts')
                ->where(function($query) use ($l2Account) {
                    // Get direct children (L3)
                    $query->where('parent_account_number', $l2Account->account_number)
                          ->whereIn('account_level', ['3', '4']);
                })
                ->orWhere(function($query) use ($l2Account) {
                    // Get L4 accounts whose parent L3 belongs to this L2
                    $query->whereIn('parent_account_number', function($subquery) use ($l2Account) {
                        $subquery->select('account_number')
                                 ->from('accounts')
                                 ->where('parent_account_number', $l2Account->account_number)
                                 ->where('account_level', '3');
                    })
                    ->where('account_level', '4');
                })
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->orderBy('account_number')
                ->get();
            
            // Include the L2 account itself if it has direct transactions
            $l2Balance = $this->getDirectAccountBalance($l2Account->account_number, $this->selectedYear);
            if ($l2Balance != 0) {
                $content[] = [
                    'account_number' => $l2Account->account_number,
                    'account_name' => $l2Account->account_name . ' (Direct)',
                    'balance' => $l2Balance,
                    'level' => 'L2'
                ];
            }
            
            // Add all child accounts with balances
            foreach ($childAccounts as $account) {
                $balance = $this->getDirectAccountBalance($account->account_number, $this->selectedYear);
                if ($balance != 0) {
                    $indent = $account->account_level == '3' ? '  ' : '    ';
                    $content[] = [
                        'account_number' => $account->account_number,
                        'account_name' => $indent . $account->account_name,
                        'balance' => $balance,
                        'level' => 'L' . $account->account_level
                    ];
                }
            }
        }
        
        return $content;
    }
    
    private function getDirectAccountBalance($accountNumber, $year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Get balance only for this specific account (no children)
        $result = DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit'),
                DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit')
            )
            ->first();
        
        // Get account info to determine type
        $accountInfo = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        if (!$accountInfo) {
            return 0;
        }
        
        // Determine balance based on account type
        $majorCode = $accountInfo->major_category_code;
        
        // Assets: Debit balance (debit - credit)
        // Liabilities and Equity: Credit balance (credit - debit)
        if ($majorCode == '1000' || $majorCode == '4000') {
            // Assets and Expenses
            return ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
        } else {
            // Liabilities, Equity, and Income
            return ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
        }
    }
    
    public function render()
    {
        return view('livewire.accounting.statement-of-financial-position', [
            'assetsData' => $this->assetsData,
            'liabilitiesData' => $this->liabilitiesData,
            'equityData' => $this->equityData,
            'summaryData' => $this->summaryData,
            'comparisonYears' => $this->comparisonYears
        ]);
    }
}