<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class ComparativeIncomeExpense extends Component
{
    // Period selection
    public $selectedYear;
    public $comparisonYears = [];
    
    // Display options
    public $viewMode = 'excel'; // 'excel' or 'detailed'
    public $showDetailed = false;
    public $expandedCategories = [];
    public $expandedSubcategories = [];
    public $expandedDetails = [];
    public $viewLevel = 2; // Start with L2 accounts
    public $selectedAccount = null;
    public $accountStatement = [];
    public $showStatement = false;
    
    // Note modal properties
    public $showNoteModal = false;
    public $selectedNote = null;
    public $noteTitle = '';
    public $noteContent = [];
    
    // Data arrays
    public $incomeData = [];
    public $expenseData = [];
    public $summaryData = [];
    
    public function mount()
    {
        // Set current year as default
        $this->selectedYear = date('Y');
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        
        $this->loadData();
    }
    
    public function updatedSelectedYear()
    {
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->loadData();
    }
    
    public function toggleCategory($categoryCode)
    {
        if (in_array($categoryCode, $this->expandedCategories)) {
            // Remove from expanded list
            $key = array_search($categoryCode, $this->expandedCategories);
            unset($this->expandedCategories[$key]);
            $this->expandedCategories = array_values($this->expandedCategories);
        } else {
            // Add to expanded list
            $this->expandedCategories[] = $categoryCode;
        }
        
        // Re-load data to include/exclude subcategories
        $this->loadData();
    }
    
    public function toggleSubcategory($subcategoryCode)
    {
        if (in_array($subcategoryCode, $this->expandedSubcategories)) {
            // Remove from expanded list
            $key = array_search($subcategoryCode, $this->expandedSubcategories);
            unset($this->expandedSubcategories[$key]);
            $this->expandedSubcategories = array_values($this->expandedSubcategories);
        } else {
            // Add to expanded list
            $this->expandedSubcategories[] = $subcategoryCode;
        }
        
        // Re-load data to include/exclude details
        $this->loadData();
    }
    
    public function toggleDetail($detailCode)
    {
        if (in_array($detailCode, $this->expandedDetails)) {
            $this->expandedDetails = array_diff($this->expandedDetails, [$detailCode]);
        } else {
            $this->expandedDetails[] = $detailCode;
        }
    }
    
    public function showAccountStatement($accountNumber, $accountName)
    {
        $this->selectedAccount = [
            'number' => $accountNumber,
            'name' => $accountName
        ];
        
        // Load general ledger entries for this account
        $this->accountStatement = DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function($entry) {
                return [
                    'date' => Carbon::parse($entry->created_at)->format('d/m/Y'),
                    'time' => Carbon::parse($entry->created_at)->format('H:i'),
                    'reference' => $entry->reference_number ?? $entry->transaction_number,
                    'description' => $entry->description ?? $entry->narration ?? 'Transaction',
                    'debit' => $entry->debit,
                    'credit' => $entry->credit,
                    'balance' => 0, // Will calculate running balance
                    'user' => $entry->created_by ?? 'System'
                ];
            })
            ->toArray();
        
        // Calculate running balance
        $runningBalance = 0;
        foreach ($this->accountStatement as $key => &$entry) {
            $runningBalance += ($entry['credit'] - $entry['debit']);
            $entry['balance'] = $runningBalance;
        }
        
        $this->showStatement = true;
    }
    
    public function closeStatement()
    {
        $this->showStatement = false;
        $this->selectedAccount = null;
        $this->accountStatement = [];
    }
    
    public function showNote($noteNumber, $title)
    {
        $this->selectedNote = $noteNumber;
        $this->noteTitle = "Note {$noteNumber}: {$title}";
        
        // Prepare note content based on note number
        switch($noteNumber) {
            case 1:
                $breakdown = $this->getInterestIncomeBreakdown();
                $this->noteContent = [
                    'description' => 'Interest earned from loans issued to members during the financial year.',
                    'policy' => 'Interest income is recognized on an accrual basis using the effective interest rate method in accordance with IFRS 9.',
                    'recognition' => 'Recognized when earned, regardless of when payment is received. Interest on non-performing loans is suspended after 90 days.',
                    'breakdown' => $breakdown,
                    'totals' => $this->calculateBreakdownTotals($breakdown)
                ];
                break;
            case 2:
                $breakdown = $this->getInterestOnSavingsBreakdown();
                $this->noteContent = [
                    'description' => 'Interest paid to members on their savings and deposit accounts.',
                    'policy' => 'Interest expense on member deposits is accrued daily and paid according to the terms of each savings product.',
                    'recognition' => 'Accrued daily based on account balances and applicable interest rates. Interest is compounded monthly unless otherwise specified.',
                    'breakdown' => $breakdown,
                    'totals' => $this->calculateBreakdownTotals($breakdown)
                ];
                break;
            case 3:
                $breakdown = $this->getOtherIncomeBreakdown();
                $this->noteContent = [
                    'description' => 'Non-interest income including fees, commissions, and other operating income.',
                    'policy' => 'Other income is recognized when the service is provided or the right to receive payment is established.',
                    'recognition' => 'Recognized on an accrual basis when earned. Fees are recognized at point of service delivery.',
                    'breakdown' => $breakdown,
                    'totals' => $this->calculateBreakdownTotals($breakdown),
                    'components' => ['Loan processing fees', 'Account maintenance fees', 'Transaction fees', 'Penalty charges', 'Commission income', 'Other service charges']
                ];
                break;
            case 4:
                $breakdown = $this->getAdminExpensesBreakdown();
                $this->noteContent = [
                    'description' => 'Costs related to general administration and office operations.',
                    'policy' => 'Administrative expenses are recognized when incurred on an accrual basis.',
                    'components' => ['Office rent', 'Utilities', 'Office supplies', 'Insurance', 'Communication', 'Professional fees', 'Legal fees', 'Audit fees'],
                    'breakdown' => $breakdown,
                    'totals' => $this->calculateBreakdownTotals($breakdown)
                ];
                break;
            case 5:
                $breakdown = $this->getPersonnelExpensesBreakdown();
                $this->noteContent = [
                    'description' => 'All costs related to employee compensation and benefits.',
                    'policy' => 'Personnel costs are recognized in the period in which the related services are rendered in accordance with IAS 19.',
                    'components' => ['Basic salaries', 'Allowances', 'NSSF contributions', 'NHIF contributions', 'Medical insurance', 'Staff training', 'Bonuses', 'Other benefits'],
                    'breakdown' => $breakdown,
                    'totals' => $this->calculateBreakdownTotals($breakdown)
                ];
                break;
            case 6:
                $breakdown = $this->getOperatingExpensesBreakdown();
                $this->noteContent = [
                    'description' => 'Other expenses directly related to the operations of the SACCOS.',
                    'policy' => 'Operating expenses are recognized when incurred using the accrual basis of accounting.',
                    'components' => ['Marketing expenses', 'Travel and transport', 'Repairs and maintenance', 'Security costs', 'Depreciation', 'Bad debt provisions', 'Other operating costs'],
                    'breakdown' => $breakdown,
                    'totals' => $this->calculateBreakdownTotals($breakdown)
                ];
                break;
            case 7:
                $this->noteContent = [
                    'description' => 'Corporate income tax calculated on taxable surplus.',
                    'policy' => 'Tax expense is calculated at the statutory rate of 30% on taxable income after adjustments for tax-exempt income and non-deductible expenses in accordance with IAS 12.',
                    'calculation' => 'Taxable income × 30% statutory rate',
                    'adjustments' => [
                        'Add: Non-deductible expenses',
                        'Add: Accounting depreciation', 
                        'Less: Tax-exempt income',
                        'Less: Capital allowances',
                        'Less: Investment deductions'
                    ],
                    'effective_rate' => 'The effective tax rate may differ from the statutory rate due to permanent and temporary differences.'
                ];
                break;
            default:
                $this->noteContent = [
                    'description' => 'Additional information about this line item.',
                    'policy' => 'Recognized in accordance with International Financial Reporting Standards (IFRS).'
                ];
                break;
        }
        
        $this->showNoteModal = true;
    }
    
    private function calculateBreakdownTotals($breakdown)
    {
        $totals = [];
        foreach ($this->comparisonYears as $year) {
            $total = 0;
            foreach ($breakdown as $item) {
                $total += $item['years'][$year] ?? 0;
            }
            $totals[$year] = $total;
        }
        return $totals;
    }
    
    public function closeNoteModal()
    {
        $this->showNoteModal = false;
        $this->selectedNote = null;
        $this->noteContent = [];
    }
    
    private function getInterestIncomeBreakdown()
    {
        return collect($this->incomeData)->filter(function($item) {
            $name = strtolower($item['account_name']);
            return str_contains($name, 'interest') && (str_contains($name, 'loan') || str_contains($name, 'mikopo'));
        })->values()->toArray();
    }
    
    private function getInterestOnSavingsBreakdown()
    {
        // For SACCOS, interest on savings is typically an expense, not income
        // So we should look in expense data for interest paid to members
        return collect($this->expenseData)->filter(function($item) {
            $name = strtolower($item['account_name']);
            return str_contains($name, 'interest') && (
                str_contains($name, 'saving') || 
                str_contains($name, 'deposit') || 
                str_contains($name, 'member') ||
                str_contains($name, 'akiba') ||
                str_contains($name, 'paid')
            );
        })->values()->toArray();
    }
    
    private function getOtherIncomeBreakdown()
    {
        return collect($this->incomeData)->filter(function($item) {
            $name = strtolower($item['account_name']);
            return !str_contains($name, 'interest');
        })->values()->toArray();
    }
    
    private function getAdminExpensesBreakdown()
    {
        return collect($this->expenseData)->filter(function($item) {
            $name = strtolower($item['account_name']);
            return str_contains($name, 'admin') || 
                   str_contains($name, 'office') || 
                   str_contains($name, 'rent') || 
                   str_contains($name, 'utility') ||
                   str_contains($name, 'insurance') ||
                   str_contains($name, 'communication') ||
                   str_contains($name, 'professional fee') ||
                   str_contains($name, 'legal') ||
                   str_contains($name, 'audit') ||
                   str_contains($name, 'utawala');
        })->values()->toArray();
    }
    
    private function getPersonnelExpensesBreakdown()
    {
        return collect($this->expenseData)->filter(function($item) {
            $name = strtolower($item['account_name']);
            return str_contains($name, 'salary') || 
                   str_contains($name, 'wage') || 
                   str_contains($name, 'staff') || 
                   str_contains($name, 'employee') ||
                   str_contains($name, 'payroll') || 
                   str_contains($name, 'pension') ||
                   str_contains($name, 'nssf') ||
                   str_contains($name, 'nhif') ||
                   str_contains($name, 'medical') ||
                   str_contains($name, 'allowance') ||
                   str_contains($name, 'bonus') ||
                   str_contains($name, 'training') ||
                   str_contains($name, 'utumishi') ||
                   str_contains($name, 'mishahara');
        })->values()->toArray();
    }
    
    private function getOperatingExpensesBreakdown()
    {
        return collect($this->expenseData)->filter(function($item) {
            $name = strtolower($item['account_name']);
            // Exclude admin and personnel expenses
            $isAdmin = str_contains($name, 'admin') || 
                      str_contains($name, 'office') || 
                      str_contains($name, 'rent') || 
                      str_contains($name, 'utility') ||
                      str_contains($name, 'insurance') ||
                      str_contains($name, 'professional fee');
            
            $isPersonnel = str_contains($name, 'salary') || 
                          str_contains($name, 'wage') || 
                          str_contains($name, 'staff') ||
                          str_contains($name, 'employee') ||
                          str_contains($name, 'payroll') ||
                          str_contains($name, 'pension') ||
                          str_contains($name, 'nssf') ||
                          str_contains($name, 'nhif');
            
            $isInterest = str_contains($name, 'interest');
            
            return !$isAdmin && !$isPersonnel && !$isInterest;
        })->values()->toArray();
    }
    
    public function setViewLevel($level)
    {
        $this->viewLevel = $level;
        // Auto-expand based on level
        if ($level >= 2) {
            // Expand all categories for level 2+
            $allCategories = collect($this->incomeData)->pluck('category_code')->merge(
                collect($this->expenseData)->pluck('category_code')
            )->toArray();
            $this->expandedCategories = $allCategories;
        }
    }
    
    public function toggleDetailedView()
    {
        $this->showDetailed = !$this->showDetailed;
    }
    
    private function loadData()
    {
        // Load income accounts (major_category_code = 4000)
        $this->incomeData = $this->getAccountsData('4000', 'INCOME');
        
        // Load expense accounts (major_category_code = 5000)
        $this->expenseData = $this->getAccountsData('5000', 'EXPENSE');
        
        // Calculate summary data
        $this->calculateSummary();
    }
    
    private function getAccountsData($majorCategoryCode, $type)
    {
        $data = [];
        
        // Start with Level 2 accounts as the main display level
        // Check for both uppercase and lowercase type values
        $typeVariants = [
            'INCOME' => ['income', 'income_accounts', 'INCOME'],
            'EXPENSE' => ['expense', 'expense_accounts', 'EXPENSE', 'expenses']
        ];
        
        $mainCategories = DB::table('accounts')
            ->where(function($query) use ($type, $typeVariants) {
                if ($type == 'INCOME') {
                    $query->whereIn('type', $typeVariants['INCOME'])
                          ->orWhere('major_category_code', '4000');
                } else {
                    $query->whereIn('type', $typeVariants['EXPENSE'])
                          ->orWhere('major_category_code', '5000');
                }
            })
            ->where('account_level', '2') // Start with L2
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('type')
            ->orderBy('major_category_code')
            ->orderBy('category_code')
            ->orderBy('account_number')
            ->get();
        
        // Group by type for better organization
        $groupedCategories = $mainCategories->groupBy('type');
        
        foreach ($groupedCategories as $parentType => $categories) {
            foreach ($categories as $category) {
                $categoryData = [
                    'account_number' => $category->account_number,
                    'account_name' => $category->account_name,
                    'account_type' => $category->type ?? $category->account_type,
                    'major_category_code' => $category->major_category_code,
                    'category_code' => $category->category_code,
                    'account_level' => $category->account_level,
                    'category_code_key' => $category->account_number, // Use account_number as unique key
                    'years' => [],
                    'subcategories' => []
                ];
                
                // Get data for each year including all child accounts
                foreach ($this->comparisonYears as $year) {
                    $yearData = $this->getYearDataWithChildren($category->account_number, $year);
                    $categoryData['years'][$year] = $yearData;
                }
                
                // Check if this category has children (L3 accounts)
                $hasChildren = DB::table('accounts')
                    ->where('parent_account_number', $category->account_number)
                    ->where('status', 'ACTIVE')
                    ->whereNull('deleted_at')
                    ->exists();
                
                $categoryData['has_children'] = $hasChildren;
                
                // Get L3 accounts (sub-categories) when L2 is expanded
                if (in_array($categoryData['category_code_key'], $this->expandedCategories)) {
                    $subcategories = DB::table('accounts')
                        ->where('parent_account_number', $category->account_number)
                        ->where('account_level', '3') // Get L3 accounts
                        ->where('status', 'ACTIVE')
                        ->whereNull('deleted_at')
                        ->orderBy('sub_category_code')
                        ->orderBy('account_number')
                        ->get();
                    
                    foreach ($subcategories as $subcategory) {
                        $subcategoryData = [
                            'account_number' => $subcategory->account_number,
                            'account_name' => $subcategory->account_name,
                            'account_type' => $subcategory->type ?? $subcategory->account_type,
                            'major_category_code' => $subcategory->major_category_code,
                            'category_code' => $subcategory->category_code,
                            'account_level' => $subcategory->account_level,
                            'subcategory_key' => $subcategory->account_number, // Use account_number as unique key
                            'sub_category_code' => $subcategory->sub_category_code,
                            'years' => [],
                            'details' => []
                        ];
                        
                        foreach ($this->comparisonYears as $year) {
                            $yearData = $this->getYearDataWithChildren($subcategory->account_number, $year);
                            $subcategoryData['years'][$year] = $yearData;
                        }
                        
                        // Check if this subcategory has children (L4 accounts)
                        $hasL4Children = DB::table('accounts')
                            ->where('parent_account_number', $subcategory->account_number)
                            ->where('status', 'ACTIVE')
                            ->whereNull('deleted_at')
                            ->exists();
                        
                        $subcategoryData['has_children'] = $hasL4Children;
                        
                        // Get L4 accounts (detail level) when L3 is expanded
                        if (in_array($subcategoryData['subcategory_key'], $this->expandedSubcategories)) {
                            $details = DB::table('accounts')
                                ->where('parent_account_number', $subcategory->account_number)
                                ->where('account_level', '4') // Get L4 accounts
                                ->where('status', 'ACTIVE')
                                ->whereNull('deleted_at')
                                ->orderBy('sub_category_code')
                                ->orderBy('account_number')
                                ->get();
                            
                            foreach ($details as $detail) {
                                $detailData = [
                                    'account_number' => $detail->account_number,
                                    'account_name' => $detail->account_name,
                                    'account_type' => $detail->type ?? $detail->account_type,
                                    'major_category_code' => $detail->major_category_code,
                                    'category_code' => $detail->category_code,
                                    'account_level' => $detail->account_level,
                                    'years' => []
                                ];
                                
                                foreach ($this->comparisonYears as $year) {
                                    $yearData = $this->getYearDataWithChildren($detail->account_number, $year);
                                    $detailData['years'][$year] = $yearData;
                                }
                                
                                $subcategoryData['details'][] = $detailData;
                            }
                        }
                        
                        $categoryData['subcategories'][] = $subcategoryData;
                    }
                }
                
                $data[] = $categoryData;
            }
        }
        
        // Sort final data by type and account_level
        usort($data, function($a, $b) {
            // First sort by major_category_code
            $codeCompare = strcmp($a['major_category_code'] ?? '', $b['major_category_code'] ?? '');
            if ($codeCompare !== 0) {
                return $codeCompare;
            }
            // Then by account_level
            $levelCompare = intval($a['account_level']) - intval($b['account_level']);
            if ($levelCompare !== 0) {
                return $levelCompare;
            }
            // Finally by account_number
            return strcmp($a['account_number'], $b['account_number']);
        });
        
        return $data;
    }
    
    private function getYearData($accountNumber, $year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Get total transactions for the account and its children
        $result = DB::table('general_ledger')
            ->where(function($query) use ($accountNumber) {
                $query->where('record_on_account_number', $accountNumber)
                      ->orWhere('record_on_account_number', 'LIKE', $accountNumber . '%');
            })
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
        
        $accountType = $accountInfo->type ?? $accountInfo->account_type;
        
        // For income accounts, credit is positive
        // For expense accounts, debit is positive
        if (in_array($accountType, ['income', 'income_accounts', 'INCOME']) || 
            $accountInfo->major_category_code == '4000' || 
            str_starts_with($accountNumber, '4')) {
            return ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
        } else {
            return ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
        }
    }
    
    private function getYearDataWithChildren($accountNumber, $year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Get all child account numbers
        $childAccounts = DB::table('accounts')
            ->where('parent_account_number', 'LIKE', $accountNumber . '%')
            ->orWhere('account_number', $accountNumber)
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->pluck('account_number');
        
        // Get total transactions for the account and all its children
        $result = DB::table('general_ledger')
            ->whereIn('record_on_account_number', $childAccounts)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit'),
                DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit')
            )
            ->first();
        
        // Determine account type
        $accountInfo = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        if (!$accountInfo) {
            return 0;
        }
        
        // Check the type field (it's lowercase in the database)
        $accountType = $accountInfo->type ?? $accountInfo->account_type;
        
        // For income accounts, credit is positive
        // For expense accounts, debit is positive
        if (in_array($accountType, ['income', 'income_accounts', 'INCOME']) || 
            $accountInfo->major_category_code == '4000' || 
            str_starts_with($accountNumber, '4')) {
            return ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
        } else {
            return ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
        }
    }
    
    private function calculateSummary()
    {
        $this->summaryData = [];
        
        foreach ($this->comparisonYears as $year) {
            $totalIncome = 0;
            $totalExpenses = 0;
            
            // Calculate total income
            foreach ($this->incomeData as $category) {
                $totalIncome += $category['years'][$year] ?? 0;
            }
            
            // Calculate total expenses
            foreach ($this->expenseData as $category) {
                $totalExpenses += $category['years'][$year] ?? 0;
            }
            
            $this->summaryData[$year] = [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_income' => $totalIncome - $totalExpenses,
                'profit_margin' => $totalIncome > 0 ? (($totalIncome - $totalExpenses) / $totalIncome) * 100 : 0
            ];
        }
    }
    
    public function calculateVariance($currentValue, $previousValue)
    {
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }
        return (($currentValue - $previousValue) / abs($previousValue)) * 100;
    }
    
    public function calculatePercentageOfTotal($value, $total)
    {
        if ($total == 0) {
            return 0;
        }
        return ($value / $total) * 100;
    }
    
    public function exportToExcel()
    {
        try {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\IncomeStatementExport(
                    $this->incomeData,
                    $this->expenseData,
                    $this->comparisonYears,
                    $this->selectedYear
                ),
                'income_statement_' . $this->selectedYear . '_' . date('Y_m_d') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to export Excel: ' . $e->getMessage());
        }
    }
    
    public function exportToPDF()
    {
        try {
            // Prepare data for PDF
            $data = $this->preparePDFData();
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.income-statement-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->download('income_statement_' . $this->selectedYear . '_' . date('Y_m_d') . '.pdf');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to export PDF: ' . $e->getMessage());
        }
    }
    
    private function preparePDFData()
    {
        // Calculate income categories
        $interestIncome = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $interestOnSavings = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $otherIncome = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        
        foreach($this->incomeData as $income) {
            $accountName = strtolower($income['account_name']);
            
            if (str_contains($accountName, 'interest') && (str_contains($accountName, 'loan') || str_contains($accountName, 'mikopo'))) {
                $interestIncome[$this->comparisonYears[0]] += $income['years'][$this->comparisonYears[0]] ?? 0;
                $interestIncome[$this->comparisonYears[1]] += $income['years'][$this->comparisonYears[1]] ?? 0;
            }
            elseif (str_contains($accountName, 'interest') && (str_contains($accountName, 'saving') || str_contains($accountName, 'deposit') || str_contains($accountName, 'akiba'))) {
                $interestOnSavings[$this->comparisonYears[0]] += $income['years'][$this->comparisonYears[0]] ?? 0;
                $interestOnSavings[$this->comparisonYears[1]] += $income['years'][$this->comparisonYears[1]] ?? 0;
            }
            else {
                $otherIncome[$this->comparisonYears[0]] += $income['years'][$this->comparisonYears[0]] ?? 0;
                $otherIncome[$this->comparisonYears[1]] += $income['years'][$this->comparisonYears[1]] ?? 0;
            }
        }
        
        // Also check expenses for interest on savings
        foreach($this->expenseData as $expense) {
            $accountName = strtolower($expense['account_name']);
            if (str_contains($accountName, 'interest') && (str_contains($accountName, 'saving') || str_contains($accountName, 'deposit') || str_contains($accountName, 'member'))) {
                $interestOnSavings[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $interestOnSavings[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
        }
        
        // Categorize expenses
        $administrativeExpenses = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $personnelExpenses = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $operatingExpenses = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        
        foreach($this->expenseData as $expense) {
            $accountName = strtolower($expense['account_name']);
            
            // Skip interest expenses
            if (str_contains($accountName, 'interest')) {
                continue;
            }
            
            if (str_contains($accountName, 'salary') || str_contains($accountName, 'wage') || 
                str_contains($accountName, 'staff') || str_contains($accountName, 'employee') ||
                str_contains($accountName, 'payroll') || str_contains($accountName, 'utumishi')) {
                $personnelExpenses[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $personnelExpenses[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
            elseif (str_contains($accountName, 'admin') || str_contains($accountName, 'office') || 
                    str_contains($accountName, 'rent') || str_contains($accountName, 'utility') ||
                    str_contains($accountName, 'utawala')) {
                $administrativeExpenses[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $administrativeExpenses[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
            else {
                $operatingExpenses[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $operatingExpenses[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
        }
        
        // Calculate totals
        $netInterestIncome = [
            $this->comparisonYears[0] => $interestIncome[$this->comparisonYears[0]] - $interestOnSavings[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $interestIncome[$this->comparisonYears[1]] - $interestOnSavings[$this->comparisonYears[1]]
        ];
        
        $totalIncome = [
            $this->comparisonYears[0] => $netInterestIncome[$this->comparisonYears[0]] + $otherIncome[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $netInterestIncome[$this->comparisonYears[1]] + $otherIncome[$this->comparisonYears[1]]
        ];
        
        $totalExpenses = [
            $this->comparisonYears[0] => $administrativeExpenses[$this->comparisonYears[0]] + $personnelExpenses[$this->comparisonYears[0]] + $operatingExpenses[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $administrativeExpenses[$this->comparisonYears[1]] + $personnelExpenses[$this->comparisonYears[1]] + $operatingExpenses[$this->comparisonYears[1]]
        ];
        
        $profitBeforeTax = [
            $this->comparisonYears[0] => $totalIncome[$this->comparisonYears[0]] - $totalExpenses[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $totalIncome[$this->comparisonYears[1]] - $totalExpenses[$this->comparisonYears[1]]
        ];
        
        $taxRate = 0.30;
        $taxExpense = [
            $this->comparisonYears[0] => max(0, $profitBeforeTax[$this->comparisonYears[0]] * $taxRate),
            $this->comparisonYears[1] => max(0, $profitBeforeTax[$this->comparisonYears[1]] * $taxRate)
        ];
        
        $netProfit = [
            $this->comparisonYears[0] => $profitBeforeTax[$this->comparisonYears[0]] - $taxExpense[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $profitBeforeTax[$this->comparisonYears[1]] - $taxExpense[$this->comparisonYears[1]]
        ];
        
        return [
            'selectedYear' => $this->selectedYear,
            'comparisonYears' => $this->comparisonYears,
            'interestIncome' => $interestIncome,
            'interestOnSavings' => $interestOnSavings,
            'otherIncome' => $otherIncome,
            'totalIncome' => $totalIncome,
            'administrativeExpenses' => $administrativeExpenses,
            'personnelExpenses' => $personnelExpenses,
            'operatingExpenses' => $operatingExpenses,
            'totalExpenses' => $totalExpenses,
            'profitBeforeTax' => $profitBeforeTax,
            'taxExpense' => $taxExpense,
            'netProfit' => $netProfit,
            'organizationName' => config('app.name', 'SACCOS Core System')
        ];
    }
    
    public function render()
    {
        return view('livewire.accounting.comparative-income-expense', [
            'incomeData' => $this->incomeData,
            'expenseData' => $this->expenseData,
            'summaryData' => $this->summaryData,
            'comparisonYears' => $this->comparisonYears
        ]);
    }
}