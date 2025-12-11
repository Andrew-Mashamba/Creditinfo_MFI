<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

class TrialBalance extends Component
{
    // Period selection
    public $selectedDate;
    public $endDate;
    public $comparisonDate;
    public $showComparison = false;
    public $companyName = 'NBC SACCOS LTD';
    
    // Display options
    public $showZeroBalances = false;
    public $accountLevel = 'all'; // all, L1, L2, L3, L4
    public $sortBy = 'account_number'; // account_number, account_name, debit, credit
    public $sortDirection = 'asc';
    
    // Account data
    public $accounts = [];
    public $totals = [
        'current' => [
            'debit' => 0,
            'credit' => 0,
            'balance' => 0
        ],
        'previous' => [
            'debit' => 0,
            'credit' => 0,
            'balance' => 0
        ]
    ];
    
    // Search and filter
    public $searchTerm = '';
    public $selectedCategory = 'all'; // all, assets, liabilities, equity, income, expenses
    
    public function mount()
    {
        $this->selectedDate = date('Y-m-d');
        $this->endDate = date('Y-m-d');
        $this->comparisonDate = Carbon::now()->subYear()->format('Y-m-d');
        
        $this->loadTrialBalance();
    }
    
    public function updatedSelectedDate()
    {
        $this->endDate = $this->selectedDate;
        $this->comparisonDate = Carbon::parse($this->selectedDate)->subYear()->format('Y-m-d');
        $this->loadTrialBalance();
    }
    
    public function updatedShowZeroBalances()
    {
        $this->loadTrialBalance();
    }
    
    public function updatedAccountLevel()
    {
        $this->loadTrialBalance();
    }
    
    public function updatedSearchTerm()
    {
        $this->loadTrialBalance();
    }
    
    public function updatedSelectedCategory()
    {
        $this->loadTrialBalance();
    }
    
    public function toggleComparison()
    {
        $this->showComparison = !$this->showComparison;
        if ($this->showComparison) {
            $this->loadTrialBalance();
        }
    }
    
    public function sortByColumn($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        
        $this->loadTrialBalance();
    }
    
    public function loadTrialBalance()
    {
        $this->accounts = [];
        $this->resetTotals();
        
        // Get all accounts - simpler approach
        $query = DB::table('accounts');
        
        // Only get active accounts
        $query->where('status', 'ACTIVE')
              ->whereNull('deleted_at');
        
        // Apply level filter if needed
        if ($this->accountLevel !== 'all') {
            $level = str_replace('L', '', $this->accountLevel);
            $query->where('account_level', $level);
        }
        
        // Apply category filter
        if ($this->selectedCategory !== 'all') {
            switch ($this->selectedCategory) {
                case 'assets':
                    $query->where('major_category_code', '1000');
                    break;
                case 'liabilities':
                    $query->where('major_category_code', '2000');
                    break;
                case 'equity':
                    $query->where('major_category_code', '3000');
                    break;
                case 'income':
                    $query->where('major_category_code', '4000');
                    break;
                case 'expenses':
                    $query->where('major_category_code', '5000');
                    break;
            }
        }
        
        // Apply search filter
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('account_name', 'LIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('account_number', 'LIKE', '%' . $this->searchTerm . '%');
            });
        }
        
        // Get accounts
        $accounts = $query->orderBy('account_number')->get();
        
        // Process each account
        foreach ($accounts as $account) {
            // Use the balance directly from accounts table if it exists
            $accountBalance = floatval($account->balance ?? 0);
            
            // If we need more accurate balance, get from general ledger
            if ($accountBalance == 0 || true) { // Always get from GL for accuracy
                $ledgerData = DB::table('general_ledger')
                    ->where('record_on_account_number', $account->account_number)
                    ->select(
                        DB::raw('COALESCE(SUM(CAST(debit AS DECIMAL(20,2))), 0) as total_debit'),
                        DB::raw('COALESCE(SUM(CAST(credit AS DECIMAL(20,2))), 0) as total_credit')
                    );
                
                // Add date filter if needed
                if ($this->endDate) {
                    $ledgerData->where('created_at', '<=', $this->endDate . ' 23:59:59');
                }
                
                $ledgerResult = $ledgerData->first();
                
                $totalDebit = $ledgerResult ? floatval($ledgerResult->total_debit) : 0;
                $totalCredit = $ledgerResult ? floatval($ledgerResult->total_credit) : 0;
            } else {
                // Use account balance and determine debit/credit based on natural balance
                $totalDebit = floatval($account->debit ?? 0);
                $totalCredit = floatval($account->credit ?? 0);
            }
            
            // Determine natural balance
            $naturalBalance = $this->getNaturalBalance($account->major_category_code);
            
            // Calculate net balance
            if ($naturalBalance === 'debit') {
                $balance = $totalDebit - $totalCredit;
            } else {
                $balance = $totalCredit - $totalDebit;
            }
            
            // Skip zero balances if option is unchecked
            if (!$this->showZeroBalances && $balance == 0 && $totalDebit == 0 && $totalCredit == 0) {
                continue;
            }
            
            // For trial balance display, show proper debit/credit columns
            $displayDebit = 0;
            $displayCredit = 0;
            
            if ($balance != 0) {
                if ($balance > 0) {
                    if ($naturalBalance === 'debit') {
                        $displayDebit = abs($balance);
                    } else {
                        $displayCredit = abs($balance);
                    }
                } else {
                    if ($naturalBalance === 'debit') {
                        $displayCredit = abs($balance);
                    } else {
                        $displayDebit = abs($balance);
                    }
                }
            }
            
            // Get comparison period data if enabled
            $previousDebit = 0;
            $previousCredit = 0;
            
            if ($this->showComparison) {
                $previousData = $this->getPreviousPeriodData($account->account_number);
                $previousDebit = $previousData['debit'];
                $previousCredit = $previousData['credit'];
            }
            
            // Add to accounts array
            $this->accounts[] = [
                'account_number' => $account->account_number,
                'account_name' => $account->account_name,
                'account_level' => $account->account_level ?? 'N/A',
                'category' => $this->getCategoryName($account->major_category_code),
                'current_debit' => $displayDebit,
                'current_credit' => $displayCredit,
                'previous_debit' => $previousDebit,
                'previous_credit' => $previousCredit,
                'variance_debit' => $displayDebit - $previousDebit,
                'variance_credit' => $displayCredit - $previousCredit
            ];
            
            // Update totals
            $this->totals['current']['debit'] += $displayDebit;
            $this->totals['current']['credit'] += $displayCredit;
            $this->totals['previous']['debit'] += $previousDebit;
            $this->totals['previous']['credit'] += $previousCredit;
        }
        
        // Sort accounts
        $this->sortAccounts();
        
        // Calculate balance differences
        $this->totals['current']['balance'] = abs($this->totals['current']['debit'] - $this->totals['current']['credit']);
        $this->totals['previous']['balance'] = abs($this->totals['previous']['debit'] - $this->totals['previous']['credit']);
    }
    
    private function getNaturalBalance($majorCode)
    {
        switch ($majorCode) {
            case '1000': // Assets
            case '5000': // Expenses
                return 'debit';
            case '2000': // Liabilities
            case '3000': // Equity
            case '4000': // Income
                return 'credit';
            default:
                return 'debit';
        }
    }
    
    private function getCategoryName($majorCode)
    {
        switch ($majorCode) {
            case '1000':
                return 'Assets';
            case '2000':
                return 'Liabilities';
            case '3000':
                return 'Equity';
            case '4000':
                return 'Income';
            case '5000':
                return 'Expenses';
            default:
                return 'Other';
        }
    }
    
    private function getPreviousPeriodData($accountNumber)
    {
        $previousEndDate = Carbon::parse($this->comparisonDate)->format('Y-m-d') . ' 23:59:59';
        
        $result = DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->where('created_at', '<=', $previousEndDate)
            ->select(
                DB::raw('COALESCE(SUM(CAST(debit AS DECIMAL(20,2))), 0) as total_debit'),
                DB::raw('COALESCE(SUM(CAST(credit AS DECIMAL(20,2))), 0) as total_credit')
            )
            ->first();
        
        $debit = $result ? floatval($result->total_debit) : 0;
        $credit = $result ? floatval($result->total_credit) : 0;
        
        // Get account's natural balance
        $account = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        if (!$account) {
            return ['debit' => 0, 'credit' => 0];
        }
        
        $naturalBalance = $this->getNaturalBalance($account->major_category_code);
        $balance = $naturalBalance === 'debit' ? ($debit - $credit) : ($credit - $debit);
        
        if ($balance > 0) {
            if ($naturalBalance === 'debit') {
                return ['debit' => abs($balance), 'credit' => 0];
            } else {
                return ['debit' => 0, 'credit' => abs($balance)];
            }
        } elseif ($balance < 0) {
            if ($naturalBalance === 'debit') {
                return ['debit' => 0, 'credit' => abs($balance)];
            } else {
                return ['debit' => abs($balance), 'credit' => 0];
            }
        }
        
        return ['debit' => 0, 'credit' => 0];
    }
    
    private function sortAccounts()
    {
        usort($this->accounts, function($a, $b) {
            switch ($this->sortBy) {
                case 'account_number':
                    $result = strcmp($a['account_number'], $b['account_number']);
                    break;
                case 'account_name':
                    $result = strcmp($a['account_name'], $b['account_name']);
                    break;
                case 'debit':
                    $result = $a['current_debit'] <=> $b['current_debit'];
                    break;
                case 'credit':
                    $result = $a['current_credit'] <=> $b['current_credit'];
                    break;
                default:
                    $result = 0;
            }
            
            return $this->sortDirection === 'asc' ? $result : -$result;
        });
    }
    
    private function resetTotals()
    {
        $this->totals = [
            'current' => [
                'debit' => 0,
                'credit' => 0,
                'balance' => 0
            ],
            'previous' => [
                'debit' => 0,
                'credit' => 0,
                'balance' => 0
            ]
        ];
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
        
        return Excel::download($export, 'trial_balance_' . $this->selectedDate . '.xlsx');
    }
    
    public function exportToPDF()
    {
        // Placeholder for PDF export
        session()->flash('message', 'PDF export will be implemented');
    }
    
    private function prepareExportData()
    {
        $data = [];
        
        // Header
        $data[] = [$this->companyName];
        $data[] = ['TRIAL BALANCE'];
        $data[] = ['As at ' . Carbon::parse($this->selectedDate)->format('d F Y')];
        $data[] = [];
        
        // Column headers
        if ($this->showComparison) {
            $data[] = [
                'Account Number',
                'Account Name',
                'Current Debit',
                'Current Credit',
                'Previous Debit',
                'Previous Credit',
                'Variance Debit',
                'Variance Credit'
            ];
        } else {
            $data[] = [
                'Account Number',
                'Account Name',
                'Debit',
                'Credit'
            ];
        }
        
        // Add account data
        foreach ($this->accounts as $account) {
            if ($this->showComparison) {
                $data[] = [
                    $account['account_number'],
                    $account['account_name'],
                    $account['current_debit'],
                    $account['current_credit'],
                    $account['previous_debit'],
                    $account['previous_credit'],
                    $account['variance_debit'],
                    $account['variance_credit']
                ];
            } else {
                $data[] = [
                    $account['account_number'],
                    $account['account_name'],
                    $account['current_debit'],
                    $account['current_credit']
                ];
            }
        }
        
        // Add totals
        $data[] = [];
        if ($this->showComparison) {
            $data[] = [
                '',
                'TOTAL',
                $this->totals['current']['debit'],
                $this->totals['current']['credit'],
                $this->totals['previous']['debit'],
                $this->totals['previous']['credit'],
                $this->totals['current']['debit'] - $this->totals['previous']['debit'],
                $this->totals['current']['credit'] - $this->totals['previous']['credit']
            ];
        } else {
            $data[] = [
                '',
                'TOTAL',
                $this->totals['current']['debit'],
                $this->totals['current']['credit']
            ];
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
        return view('livewire.accounting.trial-balance', [
            'accounts' => $this->accounts,
            'totals' => $this->totals
        ]);
    }
}