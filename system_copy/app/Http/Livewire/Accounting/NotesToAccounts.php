<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class NotesToAccounts extends Component
{
    // Period selection
    public $selectedYear;
    public $comparisonYears = [];
    public $reportingDate;
    public $companyName = 'NBC SACCOS LTD';
    
    // Display options
    public $expandedNotes = [];
    public $showAccountDetail = false;
    public $selectedAccountForDetail = null;
    public $accountTransactions = [];
    
    // Notes sections
    public $corporateInfo = [];
    public $accountingPolicies = [];
    public $significantEstimates = [];
    public $financialData = [];
    
    public function mount()
    {
        // Set current year as default
        $this->selectedYear = date('Y');
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->reportingDate = Carbon::now()->format('d F Y');
        
        $this->loadNotesData();
    }
    
    public function updatedSelectedYear()
    {
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->loadNotesData();
    }
    
    public function toggleNote($noteId)
    {
        if (in_array($noteId, $this->expandedNotes)) {
            $key = array_search($noteId, $this->expandedNotes);
            unset($this->expandedNotes[$key]);
            $this->expandedNotes = array_values($this->expandedNotes);
        } else {
            $this->expandedNotes[] = $noteId;
        }
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
            ->limit(50)
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
    
    private function loadNotesData()
    {
        // Load corporate information
        $this->loadCorporateInfo();
        
        // Load accounting policies
        $this->loadAccountingPolicies();
        
        // Load financial data for each account category
        $this->loadFinancialData();
        
        // Load significant estimates and judgments
        $this->loadSignificantEstimates();
    }
    
    private function loadCorporateInfo()
    {
        $this->corporateInfo = [
            'name' => $this->companyName,
            'nature' => 'Savings and Credit Cooperative Organization',
            'registration' => 'Registered under the Cooperative Societies Act',
            'address' => 'Tanzania',
            'reporting_period' => "Year ended 31 December {$this->selectedYear}",
            'functional_currency' => 'Tanzania Shillings (TZS)',
            'principal_activities' => [
                'Mobilization of member savings',
                'Provision of credit facilities to members',
                'Financial advisory services',
                'Investment management'
            ]
        ];
    }
    
    private function loadAccountingPolicies()
    {
        $this->accountingPolicies = [
            [
                'id' => 'basis',
                'title' => 'Basis of Preparation',
                'content' => 'These financial statements have been prepared in accordance with International Financial Reporting Standards (IFRS) and the requirements of the Cooperative Societies Act.',
                'subcategories' => [
                    'Going Concern' => 'The financial statements have been prepared on a going concern basis.',
                    'Accrual Basis' => 'The financial statements have been prepared under the historical cost convention on an accrual basis.',
                    'Functional Currency' => 'The financial statements are presented in Tanzania Shillings (TZS), which is the functional and presentation currency.'
                ]
            ],
            [
                'id' => 'revenue',
                'title' => 'Revenue Recognition',
                'content' => 'Revenue is recognized when it is probable that economic benefits will flow to the organization.',
                'subcategories' => [
                    'Interest Income' => 'Interest income is recognized using the effective interest rate method.',
                    'Fee Income' => 'Fees and commissions are recognized when the related services are performed.',
                    'Other Income' => 'Other income is recognized on an accrual basis when the right to receive payment is established.'
                ]
            ],
            [
                'id' => 'financial_instruments',
                'title' => 'Financial Instruments',
                'content' => 'Financial instruments are recognized when the organization becomes party to the contractual provisions.',
                'subcategories' => [
                    'Classification' => 'Financial assets are classified as loans and receivables, held-to-maturity, or available-for-sale.',
                    'Measurement' => 'Initially measured at fair value plus transaction costs, subsequently at amortized cost.',
                    'Impairment' => 'Assessed at each reporting date for objective evidence of impairment.'
                ]
            ],
            [
                'id' => 'ppe',
                'title' => 'Property, Plant and Equipment',
                'content' => 'PPE is stated at historical cost less accumulated depreciation and impairment losses.',
                'subcategories' => [
                    'Depreciation Rates' => 'Buildings: 2%, Motor Vehicles: 25%, Equipment: 20%, Furniture: 12.5%',
                    'Method' => 'Depreciation is calculated on a straight-line basis over the estimated useful life.',
                    'Review' => 'Useful lives and residual values are reviewed annually.'
                ]
            ]
        ];
    }
    
    private function loadSignificantEstimates()
    {
        $this->significantEstimates = [
            [
                'id' => 'loan_impairment',
                'title' => 'Loan Loss Provisions',
                'description' => 'Management uses judgment in estimating the amount of loan loss provisions based on historical loss experience and current economic conditions.'
            ],
            [
                'id' => 'useful_lives',
                'title' => 'Useful Lives of Assets',
                'description' => 'The estimation of useful lives of assets is based on historical experience and industry standards.'
            ],
            [
                'id' => 'fair_value',
                'title' => 'Fair Value Measurements',
                'description' => 'Where market prices are not available, fair values are estimated using valuation techniques.'
            ]
        ];
    }
    
    private function loadFinancialData()
    {
        $this->financialData = [];
        
        // Load Assets (1000 series)
        $this->financialData['assets'] = $this->getAccountCategoryData('1000', 'ASSETS');
        
        // Load Liabilities (2000 series)
        $this->financialData['liabilities'] = $this->getAccountCategoryData('2000', 'LIABILITIES');
        
        // Load Capital/Equity (3000 series)
        $this->financialData['equity'] = $this->getAccountCategoryData('3000', 'EQUITY');
        
        // Load Income (4000 series)
        $this->financialData['income'] = $this->getAccountCategoryData('4000', 'INCOME');
        
        // Load Expenses (5000 series)
        $this->financialData['expenses'] = $this->getAccountCategoryData('5000', 'EXPENSES');
    }
    
    private function getAccountCategoryData($majorCode, $categoryName)
    {
        $data = [];
        
        // Get L2 accounts for this major category
        $l2Accounts = DB::table('accounts')
            ->where('major_category_code', $majorCode)
            ->where('account_level', '2')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('account_number')
            ->get();
        
        foreach ($l2Accounts as $l2Account) {
            $accountData = [
                'account_number' => $l2Account->account_number,
                'account_name' => $l2Account->account_name,
                'account_level' => $l2Account->account_level,
                'years' => [],
                'movements' => [],
                'composition' => []
            ];
            
            // Get balances for comparison years
            foreach ($this->comparisonYears as $year) {
                $accountData['years'][$year] = $this->getAccountBalance($l2Account->account_number, $year);
            }
            
            // Calculate movement
            if (count($this->comparisonYears) >= 2) {
                $currentYear = $this->comparisonYears[0];
                $previousYear = $this->comparisonYears[1];
                $currentBalance = $accountData['years'][$currentYear];
                $previousBalance = $accountData['years'][$previousYear];
                
                $accountData['movements'] = [
                    'opening' => $previousBalance,
                    'additions' => 0, // To be calculated from transactions
                    'disposals' => 0, // To be calculated from transactions
                    'closing' => $currentBalance,
                    'change' => $currentBalance - $previousBalance,
                    'change_percentage' => $previousBalance != 0 ? (($currentBalance - $previousBalance) / abs($previousBalance)) * 100 : 0
                ];
            }
            
            // Get L3 composition for this L2 account
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
                    'years' => []
                ];
                
                foreach ($this->comparisonYears as $year) {
                    $l3Data['years'][$year] = $this->getAccountBalance($l3Account->account_number, $year);
                }
                
                $accountData['composition'][] = $l3Data;
            }
            
            $data[] = $accountData;
        }
        
        return $data;
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
        
        // Assets and Expenses: Debit balance (debit - credit)
        // Liabilities, Equity, Income: Credit balance (credit - debit)
        if (in_array($majorCode, ['1000', '5000'])) {
            return ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
        } else {
            return ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
        }
    }
    
    public function formatNumber($number)
    {
        if ($number < 0) {
            return '(' . number_format(abs($number), 2) . ')';
        }
        return number_format($number, 2);
    }
    
    public function render()
    {
        return view('livewire.accounting.notes-to-accounts', [
            'corporateInfo' => $this->corporateInfo,
            'accountingPolicies' => $this->accountingPolicies,
            'significantEstimates' => $this->significantEstimates,
            'financialData' => $this->financialData,
            'comparisonYears' => $this->comparisonYears
        ]);
    }
}