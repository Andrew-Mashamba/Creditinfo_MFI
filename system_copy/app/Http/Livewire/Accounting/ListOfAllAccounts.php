<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\AccountCreationService;

class ListOfAllAccounts extends Component
{
    use WithPagination;

    // Filter and search properties
    public $searchTerm = '';
    public $selectedType = '';
    public $selectedLevel = '';
    public $selectedStatus = '';
    public $selectedCategory = '';
    public $selectedUse = '';
    
    // Sorting properties
    public $sortField = 'account_number';
    public $sortDirection = 'asc';
    
    // Modal properties
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;
    public $showStatementModal = false;
    public $showLedgerModal = false;
    
    // Selected account properties
    public $selectedAccount = null;
    public $selectedAccountData = null;
    public $ledgerEntries = [];
    public $statementData = [];
    
    // Form properties
    public $editingAccount = [
        'account_name' => '',
        'account_number' => '',
        'parent_account_number' => '',
        'account_level' => '',
        'account_use' => '',
        'notes' => '',
        'type' => '',
        'major_category_code' => '',
        'category_code' => '',
        'sub_category_code' => '',
        'status' => '',
        'balance' => '',
        'debit' => '',
        'credit' => ''
    ];
    
    public $newAccount = [
        'account_name' => '',
        'account_number' => '',
        'parent_account_number' => '',
        'account_level' => '',
        'account_use' => 'internal',
        'notes' => '',
        'type' => '',
        'major_category_code' => '',
        'category_code' => '',
        'sub_category_code' => '',
        'status' => 'ACTIVE',
        'balance' => '0',
        'debit' => '0',
        'credit' => '0'
    ];
    
    // Account types and options
    public $accountTypes = [
        'asset_accounts' => 'Assets',
        'liability_accounts' => 'Liabilities', 
        'equity_accounts' => 'Equity',
        'income_accounts' => 'Income',
        'expense_accounts' => 'Expenses'
    ];
    
    public $accountLevels = [
        '1' => 'Level 1 - Main Account',
        '2' => 'Level 2 - Sub Account',
        '3' => 'Level 3 - Detail Account',
        '4' => 'Level 4 - Transaction Account'
    ];
    
    public $accountStatuses = [
        'ACTIVE' => 'Active',
        'INACTIVE' => 'Inactive',
        'PENDING' => 'Pending',
        'SUSPENDED' => 'Suspended'
    ];
    
    public $accountUses = [
        'internal' => 'Internal',
        'external' => 'External',
        'control' => 'Control',
        'detail' => 'Detail',
        'header' => 'Header'
    ];
    
    public $majorCategories = [
        '1000' => '1000 - Assets',
        '2000' => '2000 - Liabilities',
        '3000' => '3000 - Equity',
        '4000' => '4000 - Income',
        '5000' => '5000 - Expenses'
    ];

    protected $paginationTheme = 'tailwind';

    public $perPage = 15;

    public function mount()
    {
        // Initialize component
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingSelectedType()
    {
        $this->resetPage();
    }

    public function updatingSelectedLevel()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatingSelectedUse()
    {
        $this->resetPage();
    }

    public function updatingSortField()
    {
        $this->resetPage();
    }

    public function updatingSortDirection()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getAccounts()
    {
        $query = DB::table('accounts')
            ->select(
                'accounts.*',
                DB::raw('COALESCE(CAST(accounts.balance AS DECIMAL(20,2)), 0) as current_balance'),
                DB::raw('COALESCE(CAST(accounts.debit AS DECIMAL(20,2)), 0) as current_debit'),
                DB::raw('COALESCE(CAST(accounts.credit AS DECIMAL(20,2)), 0) as current_credit'),
                DB::raw("
                    CASE 
                        WHEN UPPER(accounts.account_name) LIKE '%ASSET%' OR accounts.major_category_code = '1000' THEN 'ASSET'
                        WHEN UPPER(accounts.account_name) LIKE '%LIABILIT%' OR accounts.major_category_code = '2000' THEN 'LIABILITY'
                        WHEN UPPER(accounts.account_name) LIKE '%EQUITY%' OR UPPER(accounts.account_name) LIKE '%CAPITAL%' OR accounts.major_category_code = '3000' THEN 'EQUITY'
                        WHEN UPPER(accounts.account_name) LIKE '%REVENUE%' OR UPPER(accounts.account_name) LIKE '%INCOME%' OR accounts.major_category_code = '4000' THEN 'INCOME'
                        WHEN UPPER(accounts.account_name) LIKE '%EXPENSE%' OR accounts.major_category_code = '5000' THEN 'EXPENSE'
                        ELSE COALESCE(accounts.type, 'OTHER')
                    END as display_type
                ")
            );

        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('account_name', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('account_number', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('major_category_code', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('category_code', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('sub_category_code', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('notes', 'ILIKE', '%' . $this->searchTerm . '%');
            });
        }

        // Apply filters
        if ($this->selectedType) {
            $query->where('type', $this->selectedType);
        }

        if ($this->selectedLevel) {
            $query->where('account_level', $this->selectedLevel);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if ($this->selectedCategory) {
            $query->where('major_category_code', $this->selectedCategory);
        }

        if ($this->selectedUse) {
            $query->where('account_use', $this->selectedUse);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function getAccountSummary()
    {
        $summary = DB::table('accounts')
            ->select(
                DB::raw("
                    CASE 
                        WHEN UPPER(accounts.account_name) LIKE '%ASSET%' OR accounts.major_category_code = '1000' THEN 'ASSET'
                        WHEN UPPER(accounts.account_name) LIKE '%LIABILIT%' OR accounts.major_category_code = '2000' THEN 'LIABILITY'
                        WHEN UPPER(accounts.account_name) LIKE '%EQUITY%' OR UPPER(accounts.account_name) LIKE '%CAPITAL%' OR accounts.major_category_code = '3000' THEN 'EQUITY'
                        WHEN UPPER(accounts.account_name) LIKE '%REVENUE%' OR UPPER(accounts.account_name) LIKE '%INCOME%' OR accounts.major_category_code = '4000' THEN 'INCOME'
                        WHEN UPPER(accounts.account_name) LIKE '%EXPENSE%' OR accounts.major_category_code = '5000' THEN 'EXPENSE'
                        ELSE 'OTHER'
                    END as type
                "),
                DB::raw('COUNT(*) as account_count'),
                DB::raw('SUM(CAST(balance AS DECIMAL(20,2))) as total_balance')
            )
            ->groupBy(DB::raw("
                CASE 
                    WHEN UPPER(accounts.account_name) LIKE '%ASSET%' OR accounts.major_category_code = '1000' THEN 'ASSET'
                    WHEN UPPER(accounts.account_name) LIKE '%LIABILIT%' OR accounts.major_category_code = '2000' THEN 'LIABILITY'
                    WHEN UPPER(accounts.account_name) LIKE '%EQUITY%' OR UPPER(accounts.account_name) LIKE '%CAPITAL%' OR accounts.major_category_code = '3000' THEN 'EQUITY'
                    WHEN UPPER(accounts.account_name) LIKE '%REVENUE%' OR UPPER(accounts.account_name) LIKE '%INCOME%' OR accounts.major_category_code = '4000' THEN 'INCOME'
                    WHEN UPPER(accounts.account_name) LIKE '%EXPENSE%' OR accounts.major_category_code = '5000' THEN 'EXPENSE'
                    ELSE 'OTHER'
                END
            "))
            ->get();

        $formattedSummary = [];
        foreach ($summary as $item) {
            $formattedSummary[$item->type] = [
                'count' => $item->account_count,
                'balance' => $item->total_balance ?? 0
            ];
        }

        return $formattedSummary;
    }

    public function resetFilters()
    {
        $this->searchTerm = '';
        $this->selectedType = '';
        $this->selectedLevel = '';
        $this->selectedStatus = '';
        $this->selectedCategory = '';
        $this->selectedUse = '';
        $this->resetPage();
    }

    // CRUD Operations
    public function openCreateModal()
    {
        $this->resetNewAccount();
        $this->showCreateModal = true;
    }

    public function openEditModal($accountNumber)
    {
        $account = DB::table('accounts')->where('account_number', $accountNumber)->first();
        
        if ($account) {
            $this->editingAccount = [
                'account_name' => $account->account_name,
                'account_number' => $account->account_number,
                'parent_account_number' => $account->parent_account_number,
                'account_level' => $account->account_level,
                'account_use' => $account->account_use,
                'notes' => $account->notes,
                'type' => $account->type,
                'major_category_code' => $account->major_category_code,
                'category_code' => $account->category_code,
                'sub_category_code' => $account->sub_category_code,
                'status' => $account->status,
                'balance' => $account->balance,
                'debit' => $account->debit,
                'credit' => $account->credit
            ];
            $this->showEditModal = true;
        }
    }

    public function openDeleteModal($accountNumber)
    {
        $this->selectedAccount = $accountNumber;
        $account = DB::table('accounts')->where('account_number', $accountNumber)->first();
        $this->selectedAccountData = $account ? (array) $account : null;
        $this->showDeleteModal = true;
    }

    public function openDetailsModal($accountNumber)
    {
        $account = DB::table('accounts')->where('account_number', $accountNumber)->first();
        
        if ($account) {
            $this->selectedAccountData = (array) $account;
            
            // Get parent account if exists
            if ($account->parent_account_number) {
                $parent = DB::table('accounts')
                    ->where('account_number', $account->parent_account_number)
                    ->first();
                $this->selectedAccountData['parent'] = $parent ? (array) $parent : null;
            }
            
            // Get child accounts
            $children = DB::table('accounts')
                ->where('parent_account_number', $accountNumber)
                ->get();
            
            $this->selectedAccountData['children'] = $children->map(function($child) {
                return (array) $child;
            })->toArray();
            
            $this->showDetailsModal = true;
        }
    }

    public function openLedgerModal($accountNumber)
    {
        $this->selectedAccount = $accountNumber;
        
        $accountData = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        $this->selectedAccountData = $accountData ? (array) $accountData : null;
        
        // Get ledger entries
        $entries = DB::table('general_ledger')
            ->where('record_on_account_number', $accountNumber)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        $this->ledgerEntries = $entries->map(function($entry) {
            return (array) $entry;
        })->toArray();
        
        $this->showLedgerModal = true;
    }

    public function openStatementModal($accountNumber)
    {
        $this->selectedAccount = $accountNumber;
        
        $accountData = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        $this->selectedAccountData = $accountData ? (array) $accountData : null;
        
        // Get statement data (monthly summary)
        $statementData = DB::table('general_ledger')
            ->select(
                DB::raw('EXTRACT(YEAR FROM created_at) as year'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('SUM(CAST(debit AS DECIMAL(20,2))) as total_debits'),
                DB::raw('SUM(CAST(credit AS DECIMAL(20,2))) as total_credits'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->where('record_on_account_number', $accountNumber)
            ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at)'), DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        $this->statementData = $statementData->map(function($entry) {
            return (array) $entry;
        })->toArray();
        
        $this->showStatementModal = true;
    }

    public function createAccount()
    {
        $this->validate([
            'newAccount.account_name' => 'required|string|max:200',
            'newAccount.type' => 'required|string',
            'newAccount.account_use' => 'required|string'
        ], [
            'newAccount.account_name.required' => 'Account name is required',
            'newAccount.type.required' => 'Account type is required',
            'newAccount.account_use.required' => 'Account use is required'
        ]);
        
        try {
            // Use AccountCreationService to create the account
            $accountService = new AccountCreationService();
            
            // Prepare account data
            $accountData = [
                'account_use' => $this->newAccount['account_use'] ?: 'internal',
                'account_name' => $this->newAccount['account_name'],
                'type' => $this->newAccount['type'],
                'product_number' => $this->newAccount['product_number'] ?? '0000',
                'member_number' => $this->newAccount['account_use'] === 'external' ? 
                    ($this->newAccount['member_number'] ?? '00000') : '00000',
                'branch_number' => auth()->user()->branch,
                'notes' => $this->newAccount['notes'] ?? null
            ];
            
            // Create account with parent if specified
            $parentAccountNumber = $this->newAccount['parent_account_number'] ?: null;
            $account = $accountService->createAccount($accountData, $parentAccountNumber);
            
            // Update the account status to ACTIVE if needed
            if ($this->newAccount['status'] ?? false) {
                DB::table('accounts')
                    ->where('account_number', $account->account_number)
                    ->update(['status' => $this->newAccount['status']]);
            }
            
            $this->closeCreateModal();
            session()->flash('message', 'Account created successfully with account number: ' . $account->account_number);
            
        } catch (\Exception $e) {
            Log::error('Error creating account: ' . $e->getMessage());
            session()->flash('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    public function updateAccount()
    {
        $this->validate([
            'editingAccount.account_name' => 'required|string|max:200',
            'editingAccount.type' => 'required|string',
            'editingAccount.account_level' => 'required|string',
            'editingAccount.major_category_code' => 'required|string|max:20',
            'editingAccount.category_code' => 'required|string|max:20',
            'editingAccount.sub_category_code' => 'required|string|max:20'
        ]);
        
        try {
            DB::table('accounts')
                ->where('account_number', $this->editingAccount['account_number'])
                ->update([
                    'account_name' => $this->editingAccount['account_name'],
                    'parent_account_number' => $this->editingAccount['parent_account_number'] ?: null,
                    'account_level' => $this->editingAccount['account_level'],
                    'account_use' => $this->editingAccount['account_use'],
                    'notes' => $this->editingAccount['notes'],
                    'type' => $this->editingAccount['type'],
                    'major_category_code' => $this->editingAccount['major_category_code'],
                    'category_code' => $this->editingAccount['category_code'],
                    'sub_category_code' => $this->editingAccount['sub_category_code'],
                    'status' => $this->editingAccount['status'],
                    'balance' => $this->editingAccount['balance'],
                    'debit' => $this->editingAccount['debit'],
                    'credit' => $this->editingAccount['credit'],
                    'updated_at' => now()
                ]);
            
            $this->closeEditModal();
            session()->flash('message', 'Account updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error updating account: ' . $e->getMessage());
            session()->flash('error', 'Failed to update account: ' . $e->getMessage());
        }
    }

    public function deleteAccount()
    {
        try {
            // Check if account has children
            $childrenCount = DB::table('accounts')
                ->where('parent_account_number', $this->selectedAccount)
                ->count();
            
            if ($childrenCount > 0) {
                session()->flash('error', 'Cannot delete account with child accounts. Please delete child accounts first.');
                return;
            }
            
            // Check if account has transactions
            $transactionsCount = DB::table('general_ledger')
                ->where('record_on_account_number', $this->selectedAccount)
                ->count();
            
            if ($transactionsCount > 0) {
                session()->flash('error', 'Cannot delete account with transaction history. Please archive instead.');
                return;
            }
            
            DB::table('accounts')
                ->where('account_number', $this->selectedAccount)
                ->delete();
            
            $this->closeDeleteModal();
            session()->flash('message', 'Account deleted successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete account: ' . $e->getMessage());
        }
    }

    public function toggleAccountStatus($accountNumber, $newStatus)
    {
        try {
            DB::table('accounts')
                ->where('account_number', $accountNumber)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);
            
            session()->flash('message', 'Account status updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error updating account status: ' . $e->getMessage());
            session()->flash('error', 'Failed to update account status: ' . $e->getMessage());
        }
    }

    // Modal close methods
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetNewAccount();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingAccount = [];
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedAccount = null;
        $this->selectedAccountData = null;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedAccountData = null;
    }

    public function closeLedgerModal()
    {
        $this->showLedgerModal = false;
        $this->selectedAccount = null;
        $this->selectedAccountData = null;
        $this->ledgerEntries = [];
    }

    public function closeStatementModal()
    {
        $this->showStatementModal = false;
        $this->selectedAccount = null;
        $this->selectedAccountData = null;
        $this->statementData = [];
    }

    private function resetNewAccount()
    {
        $this->newAccount = [
            'account_name' => '',
            'account_number' => '',
            'parent_account_number' => '',
            'account_level' => '',
            'account_use' => 'internal',
            'notes' => '',
            'type' => '',
            'major_category_code' => '',
            'category_code' => '',
            'sub_category_code' => '',
            'status' => 'ACTIVE',
            'balance' => '0',
            'debit' => '0',
            'credit' => '0'
        ];
    }

    public function render()
    {
        return view('livewire.accounting.list-of-all-accounts', [
            'accounts' => $this->getAccounts(),
            'accountSummary' => $this->getAccountSummary(),
            'selectedAccountData' => $this->selectedAccountData,
            'ledgerEntries' => $this->ledgerEntries,
            'statementData' => $this->statementData
        ]);
    }
}
