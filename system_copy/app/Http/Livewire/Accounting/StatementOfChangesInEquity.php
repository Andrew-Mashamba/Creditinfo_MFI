<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

class StatementOfChangesInEquity extends Component
{
    // Period selection
    public $selectedYear;
    public $comparisonYears = [];
    public $companyName = 'NBC SACCOS LTD';
    
    // Equity components
    public $equityAccounts = [];
    public $equityMovements = [];
    public $equityData = [];
    
    // Entry management
    public $showAddEntry = false;
    public $entryType = '';
    public $entryDescription = '';
    public $entryAccounts = [];
    
    // Display options
    public $showDetailed = false;
    public $expandedAccounts = [];
    
    protected $rules = [
        'entryDescription' => 'required|min:3',
        'entryType' => 'required|in:dividend,appropriation,contribution,adjustment,other',
    ];
    
    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        
        $this->loadEquityData();
    }
    
    public function updatedSelectedYear()
    {
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->loadEquityData();
    }
    
    public function toggleAccount($accountNumber)
    {
        if (in_array($accountNumber, $this->expandedAccounts)) {
            $key = array_search($accountNumber, $this->expandedAccounts);
            unset($this->expandedAccounts[$key]);
            $this->expandedAccounts = array_values($this->expandedAccounts);
        } else {
            $this->expandedAccounts[] = $accountNumber;
        }
    }
    
    public function toggleDetailedView()
    {
        $this->showDetailed = !$this->showDetailed;
        $this->loadEquityData();
    }
    
    private function loadEquityData()
    {
        // Get all L2 equity accounts (major_category_code = 3000)
        $this->equityAccounts = DB::table('accounts')
            ->where('major_category_code', '3000')
            ->where('account_level', '2')
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('account_number')
            ->get()
            ->map(function($account) {
                // Put Retained Surplus/Earnings at the end
                $sortOrder = str_contains(strtolower($account->account_name), 'retained') ? 999 : $account->account_number;
                $account->sort_order = $sortOrder;
                return $account;
            })
            ->sortBy('sort_order')
            ->values();
        
        // Build the equity data structure
        $this->equityData = [];
        
        foreach ($this->comparisonYears as $year) {
            $yearData = [
                'opening_balance' => [],
                'profit_for_year' => 0,
                'other_comprehensive_income' => 0,
                'total_comprehensive_income' => 0,
                'dividends' => [],
                'contributions' => [],
                'appropriations' => [],
                'transfers' => [],
                'other_changes' => [],
                'closing_balance' => []
            ];
            
            // Calculate opening balance for each account
            foreach ($this->equityAccounts as $account) {
                $yearData['opening_balance'][$account->account_number] = $this->getOpeningBalance($account->account_number, $year);
            }
            
            // Calculate profit for the year
            $yearData['profit_for_year'] = $this->calculateProfitForYear($year);
            
            // Calculate other comprehensive income
            $yearData['other_comprehensive_income'] = $this->calculateOCI($year);
            
            // Total comprehensive income
            $yearData['total_comprehensive_income'] = $yearData['profit_for_year'] + $yearData['other_comprehensive_income'];
            
            // Get movements from entries table if it exists
            $movements = $this->getEquityMovements($year);
            
            // Process movements if they exist
            if ($movements->isNotEmpty()) {
                foreach ($movements as $movement) {
                    // Check if type property exists, otherwise categorize as other
                    $movementType = property_exists($movement, 'type') ? $movement->type : 'other';
                    
                    // Get the account number - check if it exists in the movement
                    $accountNumber = null;
                    if (property_exists($movement, 'account_number')) {
                        $accountNumber = $movement->account_number;
                    } elseif (property_exists($movement, 'account_id')) {
                        // If we have account_id, get the account_number
                        $accountNumber = DB::table('accounts')
                            ->where('id', $movement->account_id)
                            ->value('account_number');
                    }
                    
                    if (!$accountNumber) {
                        continue; // Skip if we can't determine the account
                    }
                    
                    $amount = property_exists($movement, 'amount') ? $movement->amount : 0;
                    
                    switch ($movementType) {
                        case 'dividend':
                            $yearData['dividends'][$accountNumber] = $amount;
                            break;
                        case 'contribution':
                            $yearData['contributions'][$accountNumber] = $amount;
                            break;
                        case 'appropriation':
                            $yearData['appropriations'][$accountNumber] = $amount;
                            break;
                        case 'transfer':
                            $yearData['transfers'][$accountNumber] = $amount;
                            break;
                        default:
                            $yearData['other_changes'][$accountNumber] = ($yearData['other_changes'][$accountNumber] ?? 0) + $amount;
                    }
                }
            }
            
            // Calculate appropriations based on profit distribution
            $yearData['appropriations'] = $this->calculateAppropriations($year, $yearData['profit_for_year']);
            
            // Calculate closing balance for each account
            foreach ($this->equityAccounts as $account) {
                $opening = $yearData['opening_balance'][$account->account_number] ?? 0;
                $dividends = $yearData['dividends'][$account->account_number] ?? 0;
                $contributions = $yearData['contributions'][$account->account_number] ?? 0;
                $appropriations = $yearData['appropriations'][$account->account_number] ?? 0;
                $transfers = $yearData['transfers'][$account->account_number] ?? 0;
                $other = $yearData['other_changes'][$account->account_number] ?? 0;
                
                // For Retained Earnings, add profit and subtract total appropriations
                if (str_contains(strtolower($account->account_name), 'retained')) {
                    $totalAppropriations = array_sum($yearData['appropriations']);
                    $yearData['closing_balance'][$account->account_number] = 
                        $opening + $yearData['profit_for_year'] - $totalAppropriations - $dividends + $contributions + $transfers + $other;
                } else {
                    $yearData['closing_balance'][$account->account_number] = 
                        $opening + $appropriations - $dividends + $contributions + $transfers + $other;
                }
            }
            
            $this->equityData[$year] = $yearData;
        }
    }
    
    private function getOpeningBalance($accountNumber, $year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        
        // Get balance up to the start of the year
        $result = DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->where('created_at', '<', $startDate)
            ->select(
                DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credit'),
                DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debit')
            )
            ->first();
        
        // Equity accounts have credit balance (credit - debit)
        return ($result->total_credit ?? 0) - ($result->total_debit ?? 0);
    }
    
    private function calculateProfitForYear($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Calculate income (4000 series)
        $incomeResult = DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.major_category_code', '4000')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(CAST(gl.credit AS DECIMAL(20,2))) - SUM(CAST(gl.debit AS DECIMAL(20,2))) as total_income')
            )
            ->first();
        
        // Calculate expenses (5000 series)
        $expenseResult = DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.major_category_code', '5000')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total_expense')
            )
            ->first();
        
        $grossProfit = ($incomeResult->total_income ?? 0) - ($expenseResult->total_expense ?? 0);
        
        // Apply estimated tax rate (30%)
        $taxRate = 0.30;
        $tax = $grossProfit * $taxRate;
        
        return $grossProfit - $tax;
    }
    
    private function calculateOCI($year)
    {
        // Calculate Other Comprehensive Income items
        // This would include items like revaluation surplus, foreign currency translation, etc.
        // For now, returning 0 as placeholder
        return 0;
    }
    
    private function getEquityMovements($year)
    {
        // Check if entries table exists
        if (!DB::getSchemaBuilder()->hasTable('entries')) {
            return collect([]);
        }
        
        // Check if entries_amount table exists for joining
        if (DB::getSchemaBuilder()->hasTable('entries_amount')) {
            try {
                // Get entries with their amounts - using proper type casting
                $entries = DB::table('entries as e')
                    ->leftJoin('entries_amount as ea', function($join) {
                        $join->on(DB::raw('CAST(e.id AS VARCHAR)'), '=', DB::raw('CAST(ea.entry_id AS VARCHAR)'));
                    })
                    ->leftJoin('accounts as a', function($join) {
                        $join->on(DB::raw('CAST(ea.account_id AS VARCHAR)'), '=', DB::raw('CAST(a.id AS VARCHAR)'));
                    })
                    ->whereYear('e.created_at', $year)
                    ->where('a.major_category_code', '=', '3000') // Only equity accounts
                    ->select(
                        'e.id',
                        'e.content',
                        DB::raw('CASE WHEN e.type IS NOT NULL THEN e.type ELSE NULL END as type'),
                        'ea.amount',
                        'a.account_number',
                        'a.id as account_id'
                    )
                    ->get();
                
                return $entries;
            } catch (\Exception $e) {
                // If the join fails, just return empty collection
                \Log::warning('Failed to get equity movements: ' . $e->getMessage());
                return collect([]);
            }
        } else {
            // Just get entries without amounts
            try {
                return DB::table('entries')
                    ->whereYear('created_at', $year)
                    ->get();
            } catch (\Exception $e) {
                return collect([]);
            }
        }
    }
    
    private function calculateAppropriations($year, $profit)
    {
        $appropriations = [];
        
        // Get allocation percentages from accounts
        $totalPercent = 0;
        foreach ($this->equityAccounts as $account) {
            if (!str_contains(strtolower($account->account_name), 'retained')) {
                $percent = $account->percent ?? 0;
                $totalPercent += $percent;
                $appropriations[$account->account_number] = ($percent / 100) * $profit;
            }
        }
        
        return $appropriations;
    }
    
    public function addEntry()
    {
        $this->showAddEntry = true;
        $this->reset(['entryType', 'entryDescription', 'entryAccounts']);
    }
    
    public function saveEntry()
    {
        $this->validate();
        
        try {
            // Check if entries table has a type column
            $hasTypeColumn = DB::getSchemaBuilder()->hasColumn('entries', 'type');
            
            // Prepare entry data
            $entryData = [
                'content' => $this->entryDescription,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Add type if column exists
            if ($hasTypeColumn) {
                $entryData['type'] = $this->entryType;
            }
            
            // Save entry to database
            $entryId = DB::table('entries')->insertGetId($entryData);
            
            // Save entry amounts for each account
            foreach ($this->entryAccounts as $accountNumber => $amount) {
                if ($amount > 0) {
                    $accountId = DB::table('accounts')
                        ->where('account_number', $accountNumber)
                        ->value('id');
                    
                    if ($accountId) {
                        DB::table('entries_amount')->insert([
                            'entry_id' => $entryId,
                            'account_id' => $accountId,
                            'amount' => $amount,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
            
            session()->flash('message', 'Entry saved successfully!');
            $this->showAddEntry = false;
            $this->loadEquityData();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save entry: ' . $e->getMessage());
        }
    }
    
    public function cancelEntry()
    {
        $this->showAddEntry = false;
        $this->reset(['entryType', 'entryDescription', 'entryAccounts']);
    }
    
    public function exportToExcel()
    {
        $exportData = $this->prepareExportData();
        
        $export = new class($exportData) implements FromArray {
            private $data;
            
            public function __construct(array $data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return $this->data;
            }
        };
        
        return Excel::download($export, 'statement_of_changes_in_equity_' . $this->selectedYear . '.xlsx');
    }
    
    public function exportToPDF()
    {
        session()->flash('message', 'PDF export will be implemented');
    }
    
    private function prepareExportData()
    {
        $data = [];
        
        // Header
        $data[] = [$this->companyName];
        $data[] = ['STATEMENT OF CHANGES IN EQUITY'];
        $data[] = ['For the year ended 31 December ' . $this->selectedYear];
        $data[] = [];
        
        // Column headers
        $headers = [''];
        foreach ($this->equityAccounts as $account) {
            $headers[] = $account->account_name;
        }
        $headers[] = 'Total Equity';
        $data[] = $headers;
        
        // Add data rows
        foreach ($this->comparisonYears as $year) {
            $yearData = $this->equityData[$year];
            
            // Opening balance
            $row = ['Opening Balance ' . $year];
            $total = 0;
            foreach ($this->equityAccounts as $account) {
                $value = $yearData['opening_balance'][$account->account_number] ?? 0;
                $row[] = $value;
                $total += $value;
            }
            $row[] = $total;
            $data[] = $row;
            
            // Add other rows similarly...
        }
        
        return $data;
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
        return view('livewire.accounting.statement-of-changes-in-equity', [
            'equityAccounts' => $this->equityAccounts,
            'equityData' => $this->equityData,
            'comparisonYears' => $this->comparisonYears
        ]);
    }
}