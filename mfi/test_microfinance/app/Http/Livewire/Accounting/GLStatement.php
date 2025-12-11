<?php

namespace App\Http\Livewire\Accounting;

use App\Models\general_ledger;
use App\Models\AccountsModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class GLStatement extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind'; // Using Tailwind for consistency with design
    
    protected $queryString = [
        'selectedAccount' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'transactionType' => ['except' => ''],
    ];
    
    // Filter properties
    public $selectedAccount = '';
    public $startDate;
    public $endDate;
    public $transactionType = '';
    
    // Data properties
    public $accounts = [];
    public $accountDetails = null;
    public $openingBalance = 0;
    public $closingBalance = 0;
    public $totalCredits = 0;
    public $totalDebits = 0;
    public $netChange = 0;
    
    protected $listeners = ['refreshStatement' => '$refresh'];
    
    public function mount()
    {
        // Set default date range (last 30 days)
        $this->startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        
        // Load accounts for dropdown
        $this->loadAccounts();
    }
    
    public function loadAccounts()
    {
        $this->accounts = DB::table('accounts')
            ->select('id', 'account_number', 'account_name', 'category_code')
            ->where('status', 'ACTIVE')
            ->orderBy('account_number')
            ->get()
            ->toArray(); // Convert to array for Livewire
    }
    
    public function updatedSelectedAccount($value)
    {
        if ($value) {
            $this->accountDetails = DB::table('accounts')
                ->where('account_number', $value)
                ->first();
            
            $this->calculateBalances();
        } else {
            $this->accountDetails = null;
            $this->resetBalances();
        }
        
        // Reset to first page when account changes
        $this->resetPage();
    }
    
    public function updatedStartDate()
    {
        $this->calculateBalances();
        $this->resetPage();
    }
    
    public function updatedEndDate()
    {
        $this->calculateBalances();
        $this->resetPage();
    }
    
    public function applyFilters()
    {
        $this->calculateBalances();
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->selectedAccount = '';
        $this->startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->transactionType = '';
        $this->accountDetails = null;
        $this->resetBalances();
        $this->resetPage();
    }
    
    private function resetBalances()
    {
        $this->openingBalance = 0;
        $this->closingBalance = 0;
        $this->totalCredits = 0;
        $this->totalDebits = 0;
        $this->netChange = 0;
    }
    
    private function calculateBalances()
    {
        if (!$this->selectedAccount) {
            $this->resetBalances();
            return;
        }
        
        // Calculate opening balance (sum of transactions before start date)
        $openingQuery = DB::table('general_ledger')
            ->where('record_on_account_number', $this->selectedAccount)
            ->whereDate('created_at', '<', $this->startDate);
        
        $openingCredits = (float) (clone $openingQuery)->sum('credit');
        $openingDebits = (float) (clone $openingQuery)->sum('debit');
        $this->openingBalance = $openingCredits - $openingDebits;
        
        // Calculate totals for the period
        $periodQuery = DB::table('general_ledger')
            ->where('record_on_account_number', $this->selectedAccount)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        
        if ($this->transactionType == 'credit') {
            $periodQuery->where('credit', '>', 0);
        } elseif ($this->transactionType == 'debit') {
            $periodQuery->where('debit', '>', 0);
        }
        
        // Clone query for separate aggregations
        $this->totalCredits = (float) (clone $periodQuery)->sum('credit');
        $this->totalDebits = (float) (clone $periodQuery)->sum('debit');
        $this->netChange = $this->totalCredits - $this->totalDebits;
        $this->closingBalance = $this->openingBalance + $this->netChange;
    }
    
    public function exportPDF()
    {
        // TODO: Implement PDF export
        session()->flash('message', 'PDF export feature coming soon!');
    }
    
    public function exportExcel()
    {
        // TODO: Implement Excel export
        session()->flash('message', 'Excel export feature coming soon!');
    }
    
    public function render()
    {
        $query = DB::table('general_ledger');
        
        // Apply account filter
        if ($this->selectedAccount) {
            $query->where('record_on_account_number', $this->selectedAccount);
        }
        
        // Apply date range filter
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                $this->startDate . ' 00:00:00', 
                $this->endDate . ' 23:59:59'
            ]);
        }
        
        // Apply transaction type filter
        if ($this->transactionType == 'credit') {
            $query->where('credit', '>', 0);
        } elseif ($this->transactionType == 'debit') {
            $query->where('debit', '>', 0);
        }
        
        // Get transactions with pagination
        $transactions = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);
        
        // Calculate running balance for each transaction if single account is selected
        if ($this->selectedAccount && $transactions->count() > 0) {
            $runningBalance = $this->openingBalance;
            $items = $transactions->items();
            
            // Process in chronological order for balance calculation
            $reversedItems = array_reverse($items);
            foreach ($reversedItems as $transaction) {
                $runningBalance += ($transaction->credit - $transaction->debit);
                $transaction->running_balance = $runningBalance;
            }
            
            // Restore original order (newest first)
            $transactions->setCollection(collect($items));
        } else {
            // For all accounts view, just show individual transaction amounts
            foreach ($transactions as $transaction) {
                $transaction->running_balance = $transaction->credit - $transaction->debit;
            }
        }
        
        // Add account relationship for display
        foreach ($transactions as $transaction) {
            $transaction->account = DB::table('accounts')
                ->where('account_number', $transaction->record_on_account_number)
                ->first();
        }
        
        return view('livewire.accounting.g-l-statement', [
            'transactions' => $transactions,
            'accounts' => $this->accounts,
        ]);
    }
}