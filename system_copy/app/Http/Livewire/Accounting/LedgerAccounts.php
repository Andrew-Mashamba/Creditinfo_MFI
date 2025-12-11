<?php

namespace App\Http\Livewire\Accounting;

use App\Services\AccountCreationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class LedgerAccounts extends Component
{
    use WithPagination;

    protected $listeners = [
        'accountCreated' => '$refresh',
        'accountUpdated' => '$refresh',
        'accountBlocked' => '$refresh',
        'accountUnblocked' => '$refresh'
    ];

    // Filter properties
    public $searchTerm = '';
    public $selectedType = '';
    public $selectedLevel = '';
    public $selectedCategory = '';
    public $expandedAccounts = [];
    
    // Modal properties
    public $showLedgerModal = false;
    public $showDetailsModal = false;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showBlockModal = false;
    public $selectedAccount = null;
    public $selectedAccountData = null;
    public $ledgerEntries = [];
    
    // Create/Edit account modal properties
    public $parentAccountNumber = null;
    public $parentAccountData = null;
    public $editingAccount = null;
    public $blockingAccount = null;
    
    // New account properties
    public $newAccount = [
        'account_name' => '',
        'account_use' => 'internal',
        'product_number' => '1001', // Default product number
        'type' => '',
        'notes' => ''
    ];
    
    // Account types based on IAS standards
    public $accountTypes = [
        'ASSET' => 'Assets',
        'LIABILITY' => 'Liabilities', 
        'EQUITY' => 'Equity',
        'INCOME' => 'Income',
        'EXPENSE' => 'Expenses'
    ];
    
    // Account levels for hierarchy
    public $accountLevels = [
        1 => 'Main Account',
        2 => 'Sub Account',
        3 => 'Detail Account',
        4 => 'Transaction Account'
    ];

    public function mount()
    {
        // Initialize expanded accounts for better UX
        $this->expandedAccounts = [];
    }

    public function toggleAccount($accountNumber)
    {
        if (in_array($accountNumber, $this->expandedAccounts)) {
            $this->expandedAccounts = array_diff($this->expandedAccounts, [$accountNumber]);
        } else {
            $this->expandedAccounts[] = $accountNumber;
        }
    }

    public function getAccountsHierarchy()
    {
        // Build hierarchical structure following IAS/IFRS standards
        $query = DB::table('accounts')
            ->select(
                'accounts.*',
                DB::raw('COALESCE(CAST(accounts.balance AS DECIMAL(20,2)), 0) as current_balance'),
                DB::raw("
                    CASE 
                        WHEN UPPER(accounts.account_name) LIKE '%ASSET%' OR accounts.major_category_code = '1000' THEN 1
                        WHEN UPPER(accounts.account_name) LIKE '%LIABILIT%' OR accounts.major_category_code = '2000' THEN 2
                        WHEN UPPER(accounts.account_name) LIKE '%EQUITY%' OR UPPER(accounts.account_name) LIKE '%CAPITAL%' OR accounts.major_category_code = '3000' THEN 3
                        WHEN UPPER(accounts.account_name) LIKE '%REVENUE%' OR UPPER(accounts.account_name) LIKE '%INCOME%' OR accounts.major_category_code = '4000' THEN 4
                        WHEN UPPER(accounts.account_name) LIKE '%EXPENSE%' OR accounts.major_category_code = '5000' THEN 5
                        ELSE 6
                    END as type_order
                "),
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
            )
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at');

        // Apply filters
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('account_name', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('account_number', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('major_category_code', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('category_code', 'ILIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('sub_category_code', 'ILIKE', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->selectedType) {
            $query->where('type', $this->selectedType);
        }

        if ($this->selectedLevel) {
            $query->where('account_level', $this->selectedLevel);
        }

        if ($this->selectedCategory) {
            $query->where('major_category_code', $this->selectedCategory);
        }

        $accounts = $query
            ->orderBy('type_order')
            ->orderBy('major_category_code')
            ->orderBy('category_code')
            ->orderBy('sub_category_code')
            ->orderBy('account_number')
            ->get();

        // Build hierarchical structure
        return $this->buildHierarchy($accounts);
    }

    private function buildHierarchy($accounts)
    {
        $hierarchy = [];
        $accountMap = [];

        // First pass: create account map
        foreach ($accounts as $account) {
            $accountMap[$account->account_number] = $account;
            $account->children = [];
            $account->total_balance = floatval($account->current_balance);
        }

        // Second pass: build hierarchy
        foreach ($accounts as $account) {
            if ($account->parent_account_number && isset($accountMap[$account->parent_account_number])) {
                $parent = $accountMap[$account->parent_account_number];
                $parent->children[] = $account;
                // Roll up balances to parent
                $parent->total_balance += floatval($account->current_balance);
            } elseif (!$account->parent_account_number || $account->account_level == 1) {
                // Top level account
                $hierarchy[] = $account;
            }
        }

        return $hierarchy;
    }

    public function getAccountSummary()
    {
        // Get summary totals by type following IAS 1 presentation requirements
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
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
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

        // Calculate key financial metrics per IAS 1
        $assets = $formattedSummary['ASSET']['balance'] ?? 0;
        $liabilities = $formattedSummary['LIABILITY']['balance'] ?? 0;
        $equity = $formattedSummary['EQUITY']['balance'] ?? 0;
        $income = $formattedSummary['INCOME']['balance'] ?? 0;
        $expenses = $formattedSummary['EXPENSE']['balance'] ?? 0;

        return [
            'summary' => $formattedSummary,
            'metrics' => [
                'total_assets' => $assets,
                'total_liabilities' => $liabilities,
                'total_equity' => $equity,
                'net_income' => $income - $expenses,
                'accounting_equation_check' => abs(($assets) - ($liabilities + $equity)) < 0.01
            ]
        ];
    }

    public function getTrialBalance()
    {
        // Get trial balance data following double-entry principles
        $accounts = DB::table('accounts')
            ->select(
                'account_number',
                'account_name',
                'type',
                DB::raw('CAST(debit AS DECIMAL(20,2)) as debit_balance'),
                DB::raw('CAST(credit AS DECIMAL(20,2)) as credit_balance'),
                DB::raw('CAST(balance AS DECIMAL(20,2)) as net_balance')
            )
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->where(function($q) {
                $q->whereRaw('CAST(debit AS DECIMAL(20,2)) != 0')
                  ->orWhereRaw('CAST(credit AS DECIMAL(20,2)) != 0');
            })
            ->orderBy('account_number')
            ->get();

        $totalDebits = $accounts->sum('debit_balance');
        $totalCredits = $accounts->sum('credit_balance');

        return [
            'accounts' => $accounts,
            'totals' => [
                'debits' => $totalDebits,
                'credits' => $totalCredits,
                'difference' => abs($totalDebits - $totalCredits),
                'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
            ]
        ];
    }

    public function resetFilters()
    {
        $this->searchTerm = '';
        $this->selectedType = '';
        $this->selectedLevel = '';
        $this->selectedCategory = '';
        $this->resetPage();
    }

    public function viewLedger($accountNumber)
    {
        $this->selectedAccount = $accountNumber;
        
        // Get account details and convert to array
        $accountData = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        $this->selectedAccountData = $accountData ? (array) $accountData : null;
        
        // For parent accounts, also look for transactions on child accounts
        // Account numbers in general_ledger might be longer versions
        $accountPattern = $accountNumber . '%';
        
        // Get ledger entries from general_ledger table
        // Check both exact match and pattern match for child accounts
        $entries = DB::table('general_ledger')
            ->where(function($query) use ($accountNumber, $accountPattern) {
                $query->where('record_on_account_number', $accountNumber)
                      ->orWhere('record_on_account_number', 'LIKE', $accountPattern);
            })
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        $this->ledgerEntries = $entries->map(function($entry) {
            return (array) $entry;
        })->toArray();
        
        $this->showLedgerModal = true;
    }

    public function viewAccountDetails($accountNumber)
    {
        // Get account details with related information
        $accountData = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
            
        if ($accountData) {
            $this->selectedAccountData = (array) $accountData;
            
            // Get parent account if exists
            if ($accountData->parent_account_number) {
                $parent = DB::table('accounts')
                    ->where('account_number', $accountData->parent_account_number)
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
    
    public function closeLedgerModal()
    {
        $this->showLedgerModal = false;
        $this->selectedAccount = null;
        $this->selectedAccountData = null;
        $this->ledgerEntries = [];
    }
    
    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedAccountData = null;
    }
    
    public function openCreateModal($parentAccountNumber = null)
    {
        $this->resetNewAccount();
        $this->parentAccountNumber = $parentAccountNumber;
        
        if ($parentAccountNumber) {
            // Get parent account data
            $parent = DB::table('accounts')
                ->where('account_number', $parentAccountNumber)
                ->first();
            
            if ($parent) {
                $this->parentAccountData = (array) $parent;
                
                // For sub-accounts, inherit the product_number from parent
                $this->newAccount['product_number'] = $parent->product_number ?? '1001';
                $this->newAccount['account_use'] = $parent->account_use ?? 'internal';
            }
        } else {
            // Creating a top-level account
            $this->parentAccountData = null;
        }
        
        $this->showCreateModal = true;
    }
    
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetNewAccount();
        $this->parentAccountData = null;
    }
    
    private function resetNewAccount()
    {
        $this->newAccount = [
            'account_name' => '',
            'account_use' => 'internal',
            'product_number' => '1001', // Default product number
            'type' => '',
            'notes' => ''
        ];
        $this->parentAccountNumber = null;
    }
    
    
    public function createAccount()
    {
        // Validate based on whether it's a sub-account or top-level account
        if ($this->parentAccountNumber) {
            // For sub-accounts, only account_name is required
            $this->validate([
                'newAccount.account_name' => 'required|string|max:200'
            ], [
                'newAccount.account_name.required' => 'Account name is required'
            ]);
        } else {
            // For top-level accounts, require additional fields
            $this->validate([
                'newAccount.account_name' => 'required|string|max:200',
                'newAccount.type' => 'required|string',
                'newAccount.product_number' => 'required|string'
            ], [
                'newAccount.account_name.required' => 'Account name is required',
                'newAccount.type.required' => 'Account type is required',
                'newAccount.product_number.required' => 'Product number is required'
            ]);
        }
        
        try {
            // Use the AccountCreationService
            $accountService = new AccountCreationService();
            
            // Prepare the data for the service
            $accountData = [
                'account_name' => $this->newAccount['account_name'],
                'account_use' => $this->newAccount['account_use'],
                'product_number' => $this->newAccount['product_number'] ?: '1001', // Default if empty
                'notes' => $this->newAccount['notes'] ?? '',
                'branch_number' => auth()->user()->branch ?? '01' // Get branch from logged-in user or default
            ];
            
            // Add type only for top-level accounts
            if (!$this->parentAccountNumber) {
                $accountData['type'] = $this->newAccount['type'];
            }
            
            // Create the account using the service
            $account = $accountService->createAccount($accountData, $this->parentAccountNumber);
            
            // Close modal and refresh
            $this->closeCreateModal();
            
            // Expand parent account to show new account
            if ($this->parentAccountNumber && !in_array($this->parentAccountNumber, $this->expandedAccounts)) {
                $this->expandedAccounts[] = $this->parentAccountNumber;
            }
            
            session()->flash('message', 'Account "' . $account->account_name . '" created successfully with number: ' . $account->account_number);
            
            // Trigger a refresh of the accounts list
            $this->emit('accountCreated');
            
        } catch (\Exception $e) {
            Log::error('Failed to create account: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'parent_account' => $this->parentAccountNumber,
                'account_data' => $this->newAccount
            ]);
            session()->flash('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    public function openEditModal($accountNumber)
    {
        $account = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        if ($account) {
            $this->editingAccount = (array) $account;
            $this->showEditModal = true;
        }
    }
    
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingAccount = null;
    }
    
    public function updateAccount()
    {
        $this->validate([
            'editingAccount.account_name' => 'required|string|max:200'
        ], [
            'editingAccount.account_name.required' => 'Account name is required'
        ]);
        
        try {
            DB::table('accounts')
                ->where('account_number', $this->editingAccount['account_number'])
                ->update([
                    'account_name' => $this->editingAccount['account_name'],
                    'notes' => $this->editingAccount['notes'] ?? '',
                    'updated_at' => now()
                ]);
            
            $this->closeEditModal();
            session()->flash('message', 'Account updated successfully!');
            $this->emit('accountUpdated');
            
        } catch (\Exception $e) {
            Log::error('Failed to update account: ' . $e->getMessage());
            session()->flash('error', 'Failed to update account: ' . $e->getMessage());
        }
    }
    
    public function confirmBlockAccount($accountNumber)
    {
        $account = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        if ($account) {
            // Check if account has children
            $hasChildren = DB::table('accounts')
                ->where('parent_account_number', $accountNumber)
                ->where('status', 'ACTIVE')
                ->exists();
            
            if ($hasChildren) {
                session()->flash('error', 'Cannot block account with active sub-accounts. Please block all sub-accounts first.');
                return;
            }
            
            // Check if account has non-zero balance
            if (floatval($account->balance) != 0) {
                session()->flash('error', 'Cannot block account with non-zero balance. Please clear the balance first.');
                return;
            }
            
            $this->blockingAccount = (array) $account;
            $this->showBlockModal = true;
        }
    }
    
    public function blockAccount()
    {
        if (!$this->blockingAccount) {
            return;
        }
        
        try {
            DB::table('accounts')
                ->where('account_number', $this->blockingAccount['account_number'])
                ->update([
                    'status' => 'BLOCKED',
                    'updated_at' => now()
                ]);
            
            $this->showBlockModal = false;
            $this->blockingAccount = null;
            
            session()->flash('message', 'Account blocked successfully!');
            $this->emit('accountBlocked');
            
        } catch (\Exception $e) {
            Log::error('Failed to block account: ' . $e->getMessage());
            session()->flash('error', 'Failed to block account: ' . $e->getMessage());
        }
    }
    
    public function unblockAccount($accountNumber)
    {
        try {
            DB::table('accounts')
                ->where('account_number', $accountNumber)
                ->update([
                    'status' => 'ACTIVE',
                    'updated_at' => now()
                ]);
            
            session()->flash('message', 'Account unblocked successfully!');
            $this->emit('accountUnblocked');
            
        } catch (\Exception $e) {
            Log::error('Failed to unblock account: ' . $e->getMessage());
            session()->flash('error', 'Failed to unblock account: ' . $e->getMessage());
        }
    }
    
    public function closeBlockModal()
    {
        $this->showBlockModal = false;
        $this->blockingAccount = null;
    }

    public function render()
    {
        return view('livewire.accounting.ledger-accounts', [
            'accountsHierarchy' => $this->getAccountsHierarchy(),
            'accountSummary' => $this->getAccountSummary(),
            'trialBalance' => $this->getTrialBalance(),
            'selectedAccountData' => $this->selectedAccountData,
            'ledgerEntries' => $this->ledgerEntries,
            'parentAccountData' => $this->parentAccountData,
            'editingAccount' => $this->editingAccount,
            'blockingAccount' => $this->blockingAccount
        ]);
    }
}