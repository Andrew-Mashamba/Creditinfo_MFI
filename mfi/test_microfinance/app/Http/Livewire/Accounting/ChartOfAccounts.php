<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Services\AccountCreationService;
use App\Models\ClientsModel;
use App\Models\Account;
use App\Models\general_ledger;
use Illuminate\Support\Facades\Auth;
use App\Models\approvals;
use Barryvdh\DomPDF\Facade\Pdf;

class ChartOfAccounts extends Component
{
    // Properties used by the frontend
    public $generalLedgerCount;
    public $revenueStats = [];
    public $expenseStats = [];
    public $assetStats = [];
    public $liabilityStats = [];
    public $equityStats = [];
    public $totalAccounts;
    public $activeAccounts;
    public $inactiveAccounts;
    public $totalBalance;

    // Properties for clickable cards and account table
    public $selectedAccountType = null;
    public $level2Accounts = [];
    public $showAccountTable = false;
    public $selectedAccountTypeName = '';
    public $level1ParentAccount = null; // Store the Level 1 parent account

    // Properties for Level 3 accounts
    public $selectedLevel2Account = null;
    public $level3Accounts = [];
    public $showLevel3Table = false;
    public $selectedLevel2AccountName = '';

    // Properties for Level 4 accounts
    public $selectedLevel3Account = null;
    public $level4Accounts = [];
    public $showLevel4Table = false;
    public $selectedLevel3AccountName = '';

    // Properties for Level 3 account creation
    public $showCreateLevel3Modal = false;
    public $level2ParentAccount = null;
    public $level3AccountName = '';

    // Properties for Level 4 account creation
    public $showCreateLevel4Modal = false;
    public $level3ParentAccount = null;
    public $level4AccountName = '';

    // Properties for create account modal
    public $showCreateAccountModal = false;
    public $parentAccount = null;
    public $parentAccountType = '';
    public $accountName = '';

    // Properties for edit account modal
    public $showEditModal = false;
    public $editingAccount = null;
    public $editAccountName = '';

    // Properties for account statement modal
    public $showStatementModal = false;
    public $statementAccount = null;
    public $statementTransactions = [];
    public $statementFilters = [
        'date_from' => '',
        'date_to' => '',
        'transaction_type' => '',
        'min_amount' => '',
        'max_amount' => '',
        'narration' => ''
    ];
    public $statementBalance = 0;
    public $statementRunningBalance = 0;

    // Enhanced UX Properties
    public $globalSearch = '';
    public $tableSearch = '';
    public $statusFilter = '';
    public $balanceFilter = '';
    public $sortBy = 'account_name';
    public $sortDirection = 'asc';
    public $loading = false;
    public $searchResults = [];
    public $filteredLevel2Accounts = [];
    public $filteredAccountsCount = 0;

    protected $listeners = ['refreshChartOfAccountsComponent' => '$refresh'];

    // Methods used by the frontend
    public function selectAccountType($type, $name)
    {
        $this->selectedAccountType = $type;
        $this->selectedAccountTypeName = $name;
        $this->showAccountTable = true;
        
        // Find the Level 1 parent account for this type
        $accountType = $this->getAccountType($type);
        $this->level1ParentAccount = Account::where('type', $accountType)
            ->where('account_level', 1)
            ->first();
        
        $this->loadLevel2Accounts($type);
    }

    public function loadLevel2Accounts($type)
    {
        $accountType = $this->getAccountType($type);
        
        $this->level2Accounts = Account::where('type', $accountType)
            ->where('account_level', '2')
            ->select([
                'id',
                'account_name',
                'account_number',
                'balance',
                'credit',
                'debit',
                'status',
                'type',
                'notes'
            ])
            ->orderBy('account_name', 'asc')
            ->get();
            
        // Initialize filtered accounts with the same data
        $this->filteredLevel2Accounts = $this->level2Accounts;
        $this->filteredAccountsCount = $this->level2Accounts->count();
    }

    private function getAccountType($type)
    {
        $mapping = [
            'assets' => 'asset_accounts',
            'liabilities' => 'liability_accounts',
            'equity' => 'capital_accounts',
            'revenue' => 'income_accounts',
            'expenses' => 'expense_accounts',
            'general_ledger' => 'asset_accounts', // Default to assets for general ledger
        ];
        
        return $mapping[$type] ?? 'asset_accounts';
    }

    public function closeAccountTable()
    {
        $this->showAccountTable = false;
        $this->selectedAccountType = null;
        $this->selectedAccountTypeName = '';
        $this->level2Accounts = [];
        $this->level1ParentAccount = null;
        // Also close Level 3 table when closing Level 2
        $this->closeLevel3Table();
    }

    // Level 3 account methods
    public function selectLevel2Account($accountId, $accountName)
    {
        $this->selectedLevel2Account = Account::find($accountId);
        $this->selectedLevel2AccountName = $accountName;
        $this->showLevel3Table = true;
        $this->loadLevel3Accounts($accountId);
    }

    public function loadLevel3Accounts($level2AccountId)
    {
        $level2Account = Account::find($level2AccountId);
        if ($level2Account) {
            $this->level3Accounts = Account::where('parent_account_number', $level2Account->account_number)
                ->where('account_level', 3)
                ->select([
                    'id',
                    'account_name',
                    'account_number',
                    'balance',
                    'credit',
                    'debit',
                    'status',
                    'type',
                    'notes'
                ])
                ->orderBy('account_name', 'asc')
                ->get();
        }
    }

    public function closeLevel3Table()
    {
        $this->showLevel3Table = false;
        $this->selectedLevel2Account = null;
        $this->selectedLevel2AccountName = '';
        $this->level3Accounts = [];
        // Also close Level 4 table when closing Level 3
        $this->closeLevel4Table();
    }

    // Level 4 account methods
    public function selectLevel3Account($accountId, $accountName)
    {
        $this->selectedLevel3Account = Account::find($accountId);
        $this->selectedLevel3AccountName = $accountName;
        $this->showLevel4Table = true;
        $this->loadLevel4Accounts($accountId);
    }

    public function loadLevel4Accounts($level3AccountId)
    {
        $level3Account = Account::find($level3AccountId);
        if ($level3Account) {
            $this->level4Accounts = Account::where('parent_account_number', $level3Account->account_number)
                ->where('account_level', '4')
                ->select([
                    'id',
                    'account_name',
                    'account_number',
                    'balance',
                    'credit',
                    'debit',
                    'status',
                    'type',
                    'notes'
                ])
                ->orderBy('account_name', 'asc')
                ->get();
        }
    }

    public function closeLevel4Table()
    {
        $this->showLevel4Table = false;
        $this->selectedLevel3Account = null;
        $this->selectedLevel3AccountName = '';
        $this->level4Accounts = [];
    }

    // Level 3 account creation methods
    public function showCreateLevel3Account()
    {
        if ($this->selectedLevel2Account) {
            $this->level2ParentAccount = $this->selectedLevel2Account;
            $this->showCreateLevel3Modal = true;
            $this->level3AccountName = '';
        }
    }

    public function createLevel3Account()
    {
        $this->validate([
            'level3AccountName' => 'required|string|max:255',
        ]);

        if (!$this->level2ParentAccount) {
            session()->flash('error', 'Parent account not found.');
            return;
        }

        try {
            // For internal level 3 accounts, client_number is always '00000'
            $clientNumber = '00000';
            
            // Create account using service
            $accountService = new AccountCreationService();
            $newAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => strtoupper($this->level3AccountName),
                'type' => $this->level2ParentAccount->type,
                'product_number' => $this->level2ParentAccount->product_number ?: '0000',
                'member_number' => $clientNumber, // Set to '00000' for internal accounts
                'branch_number' => auth()->user()->branch
            ], $this->level2ParentAccount->account_number);

            // Create approval record for the new account
            $newAccountData = [
                'account_use' => 'internal',
                'institution_number' => $this->level2ParentAccount->institution_number ?? '1000',
                'branch_number' => auth()->user()->branch,
                'client_number' => $clientNumber,
                'product_number' => $this->level2ParentAccount->product_number ?? '0000',
                'sub_product_number' => $this->level2ParentAccount->sub_product_number ?? '0000',
                'major_category_code' => $this->level2ParentAccount->major_category_code ?? '1000',
                'category_code' => $this->level2ParentAccount->category_code ?? '1000',
                'sub_category_code' => $this->level2ParentAccount->sub_category_code ?? '1000',
                'balance' => 0,
                'account_name' => strtoupper($this->level3AccountName),
                'account_number' => $newAccount->account_number,
                'parent_account_number' => $this->level2ParentAccount->account_number,
                'account_level' => '3',
            ];
            
            $editPackage = json_encode($newAccountData);
            approvals::create([
                'process_name' => 'create_internal_account',
                'process_description' => Auth::user()->name . ' has added a new Level 3 internal account ' . $this->level3AccountName,
                'approval_process_description' => 'Internal Level 3 account creation approval required',
                'process_code' => 'ACC_CREATE',
                'process_id' => $newAccount->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => $editPackage
            ]);

            session()->flash('success', 'Level 3 account "' . $this->level3AccountName . '" created successfully and pending approval.');
            
            // Refresh Level 3 accounts list
            $this->loadLevel3Accounts($this->selectedLevel2Account->id);
            
            $this->closeCreateLevel3Modal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create Level 3 account: ' . $e->getMessage());
        }
    }

    public function closeCreateLevel3Modal()
    {
        $this->showCreateLevel3Modal = false;
        $this->level2ParentAccount = null;
        $this->level3AccountName = '';
    }

    // Level 4 account creation methods
    public function showCreateLevel4Account($level3AccountId)
    {
        $level3Account = Account::find($level3AccountId);
        if ($level3Account) {
            $this->level3ParentAccount = $level3Account;
            $this->showCreateLevel4Modal = true;
            $this->level4AccountName = '';
        }
    }

    public function createLevel4Account()
    {
        $this->validate([
            'level4AccountName' => 'required|string|max:255',
        ]);

        if (!$this->level3ParentAccount) {
            session()->flash('error', 'Parent account not found.');
            return;
        }

        try {
            // For internal level 4 accounts, client_number is always '00000'
            $clientNumber = '00000';
            
            // Create account using service
            $accountService = new AccountCreationService();
            $newAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => strtoupper($this->level4AccountName),
                'type' => $this->level3ParentAccount->type,
                'product_number' => $this->level3ParentAccount->product_number ?: '0000',
                'member_number' => $clientNumber, // Set to '00000' for internal accounts
                'branch_number' => auth()->user()->branch
            ], $this->level3ParentAccount->account_number);

            // Create approval record for the new account
            $newAccountData = [
                'account_use' => 'internal',
                'institution_number' => $this->level3ParentAccount->institution_number ?? '1000',
                'branch_number' => auth()->user()->branch,
                'client_number' => $clientNumber,
                'product_number' => $this->level3ParentAccount->product_number ?? '0000',
                'sub_product_number' => $this->level3ParentAccount->sub_product_number ?? '0000',
                'major_category_code' => $this->level3ParentAccount->major_category_code ?? '1000',
                'category_code' => $this->level3ParentAccount->category_code ?? '1000',
                'sub_category_code' => $this->level3ParentAccount->sub_category_code ?? '1000',
                'balance' => 0,
                'account_name' => strtoupper($this->level4AccountName),
                'account_number' => $newAccount->account_number,
                'parent_account_number' => $this->level3ParentAccount->account_number,
                'account_level' => '4',
            ];
            
            $editPackage = json_encode($newAccountData);
            approvals::create([
                'process_name' => 'create_internal_account',
                'process_description' => Auth::user()->name . ' has added a new Level 4 internal account ' . $this->level4AccountName,
                'approval_process_description' => 'Internal Level 4 account creation approval required',
                'process_code' => 'ACC_CREATE',
                'process_id' => $newAccount->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => $editPackage
            ]);

            session()->flash('success', 'Level 4 account "' . $this->level4AccountName . '" created successfully and pending approval.');
            
            // Refresh Level 3 accounts list
            $this->loadLevel3Accounts($this->selectedLevel2Account->id);
            
            $this->closeCreateLevel4Modal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create Level 4 account: ' . $e->getMessage());
        }
    }

    public function closeCreateLevel4Modal()
    {
        $this->showCreateLevel4Modal = false;
        $this->level3ParentAccount = null;
        $this->level4AccountName = '';
    }

    // Create account methods
    public function showCreateAccountModal()
    {
        if (!$this->level1ParentAccount) {
            session()->flash('error', 'Parent account not found. Please select an account type first.');
            return;
        }
        
        $this->parentAccount = $this->level1ParentAccount;
        $this->parentAccountType = $this->selectedAccountType;
        $this->showCreateAccountModal = true;
    }

    public function closeCreateAccountModal()
    {
        $this->showCreateAccountModal = false;
        $this->parentAccount = null;
        $this->parentAccountType = '';
        $this->accountName = '';
    }

    public function createNewAccount()
    {
        $this->validate([
            'accountName' => 'required|string',
        ]);

        if (!$this->parentAccount) {
            session()->flash('error', 'Parent account not found.');
            return;
        }

        try {
            // For internal level 2 accounts, client_number is always '00000'
            $clientNumber = '00000';
            
            // Create account using service
            $accountService = new AccountCreationService();
            $newAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => strtoupper($this->accountName),
                'type' => $this->parentAccount->type,
                'product_number' => $this->parentAccount->product_number ?: '0000',
                'member_number' => $clientNumber, // Set to '00000' for internal accounts
                'branch_number' => auth()->user()->branch
            ], $this->parentAccount->account_number);


            $newAccountData = [
                'account_use' => 'internal',
                'institution_number'=> '1000',
                'branch_number'=> Auth::user()->branch,
                'client_number'=> $clientNumber,
                'product_number'=> $this->parentAccount->product_number ?: '0000',
                'sub_product_number'=> $this->parentAccount->sub_product_number ?? '0000',
                'major_category_code'=> $this->parentAccount->major_category_code ?? '1000',
                'category_code'=>  $this->parentAccount->category_code ?? '1000',
                'sub_category_code'=>  $this->parentAccount->sub_category_code ?? '1000',
                'balance'=>  0,
                'account_name'=> strtoupper($this->accountName),
                'account_number'=>$newAccount->account_number,
                'parent_account_number'=>$this->parentAccount->account_number,
            ];
            $editPackage = json_encode($newAccountData);
            approvals::create([
                'process_name' => 'create_internal_account',
                'process_description' => Auth::user()->name .  ' has added a new internal account ' .$this->accountName,
                'approval_process_description' => 'Internal account creation approval required',
                'process_code' => 'ACC_CREATE',
                'process_id' => $newAccount->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => $editPackage
            ]);

            // Close modal and refresh
            $this->closeCreateAccountModal();
            $this->loadLevel2Accounts($this->selectedAccountType);
            
            session()->flash('message', 'Internal account created successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    // Account action methods
    public function editAccount($accountId)
    {
        $account = Account::find($accountId);
        if ($account) {
            $this->editingAccount = $account;
            $this->editAccountName = $account->account_name;
            $this->showEditModal = true;
        }
    }

    public function updateAccount()
    {
        $this->validate([
            'editAccountName' => 'required|string|max:255',
        ]);

        if ($this->editingAccount) {
            $this->editingAccount->update([
                'account_name' => $this->editAccountName
            ]);
            
            $this->closeEditModal();
            $this->loadLevel2Accounts($this->selectedAccountType);
            session()->flash('message', 'Account updated successfully!');
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingAccount = null;
        $this->editAccountName = '';
    }

    public function deleteOrDeactivateAccount($accountId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            session()->flash('error', 'Account not found.');
            return;
        }

        // Check if account has children
        $hasChildren = Account::where('parent_account_number', $account->account_number)->exists();
        
        // Check if account has balance
        $hasBalance = $account->balance != 0;

        if ($hasBalance) {
            // If account has balance, only deactivate
            $this->deactivateAccount($account, $hasChildren);
        } else {
            // If no balance, can delete or deactivate
            if ($hasChildren) {
                // Has children, so deactivate
                $this->deactivateAccount($account, true);
            } else {
                // No children, can delete
                $this->deleteAccount($account);
            }
        }
    }

    private function deactivateAccount($account, $hasChildren)
    {
        try {
            // Deactivate the account
            $account->update(['status' => 'INACTIVE']);
            
            // If has children, deactivate all children
            if ($hasChildren) {
                Account::where('parent_account_number', $account->account_number)
                    ->update(['status' => 'INACTIVE']);
            }
            
            $this->loadLevel2Accounts($this->selectedAccountType);
            session()->flash('message', 'Account and its children deactivated successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to deactivate account: ' . $e->getMessage());
        }
    }

    private function deleteAccount($account)
    {
        try {
            // Check if account has any transactions in general_ledger
            $hasTransactions = \App\Models\general_ledger::where('record_on_account_number', $account->account_number)->exists();
            
            if ($hasTransactions) {
                session()->flash('error', 'Cannot delete account with transaction history. Please deactivate instead.');
                return;
            }
            
            $account->delete();
            $this->loadLevel2Accounts($this->selectedAccountType);
            session()->flash('message', 'Account deleted successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete account: ' . $e->getMessage());
        }
    }

    public function activateAccount($accountId)
    {
        $account = Account::find($accountId);
        if ($account) {
            $account->update(['status' => 'ACTIVE']);
            $this->loadLevel2Accounts($this->selectedAccountType);
            session()->flash('message', 'Account activated successfully!');
        }
    }

    public function viewAccountStatement($accountId)
    {
        $account = Account::find($accountId);
        if ($account) {
            $this->statementAccount = $account;
            $this->loadAccountTransactions($account->account_number);
            $this->showStatementModal = true;
        } else {
            session()->flash('error', 'Account not found.');
        }
    }

    public function loadAccountTransactions($accountNumber)
    {
        $query = general_ledger::where('record_on_account_number', $accountNumber);

        // Apply filters
        if (!empty($this->statementFilters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->statementFilters['date_from']);
        }
        if (!empty($this->statementFilters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->statementFilters['date_to']);
        }
        if (!empty($this->statementFilters['transaction_type'])) {
            $query->where('transaction_type', $this->statementFilters['transaction_type']);
        }
        if (!empty($this->statementFilters['min_amount'])) {
            $query->where(function($q) {
                $q->where('credit', '>=', $this->statementFilters['min_amount'])
                  ->orWhere('debit', '>=', $this->statementFilters['min_amount']);
            });
        }
        if (!empty($this->statementFilters['max_amount'])) {
            $query->where(function($q) {
                $q->where('credit', '<=', $this->statementFilters['max_amount'])
                  ->orWhere('debit', '<=', $this->statementFilters['max_amount']);
            });
        }
        if (!empty($this->statementFilters['narration'])) {
            $query->where('narration', 'like', '%' . $this->statementFilters['narration'] . '%');
        }

        $this->statementTransactions = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate running balance
        $this->calculateRunningBalance();
    }

    public function applyStatementFilters()
    {
        if ($this->statementAccount) {
            $this->loadAccountTransactions($this->statementAccount->account_number);
        }
    }

    public function clearStatementFilters()
    {
        $this->statementFilters = [
            'date_from' => '',
            'date_to' => '',
            'transaction_type' => '',
            'min_amount' => '',
            'max_amount' => '',
            'narration' => ''
        ];
        
        if ($this->statementAccount) {
            $this->loadAccountTransactions($this->statementAccount->account_number);
        }
    }

    private function calculateRunningBalance()
    {
        $balance = $this->statementAccount->balance ?? 0;
        $this->statementBalance = $balance;
        
        // Calculate running balance for each transaction
        $runningBalance = $balance;
        foreach ($this->statementTransactions as $transaction) {
            $credit = (float)($transaction->credit ?? 0);
            $debit = (float)($transaction->debit ?? 0);
            $runningBalance = $runningBalance - $credit + $debit;
            $transaction->running_balance = $runningBalance;
        }
    }

    public function closeStatementModal()
    {
        $this->showStatementModal = false;
        $this->statementAccount = null;
        $this->statementTransactions = [];
        $this->statementFilters = [
            'date_from' => '',
            'date_to' => '',
            'transaction_type' => '',
            'min_amount' => '',
            'max_amount' => '',
            'narration' => ''
        ];
        $this->statementBalance = 0;
        $this->statementRunningBalance = 0;
    }

    public function downloadAccountStatement($accountId)
    {
        $account = Account::find($accountId);
        if ($account) {
            // Get transactions from general_ledger
            $transactions = \App\Models\general_ledger::where('record_on_account_number', $account->account_number)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return $this->generateStatementPDF($account, $transactions);
        }
        session()->flash('error', 'Account not found.');
    }

    private function generateStatementPDF($account, $transactions)
    {
        try {
            // Generate PDF using a library like DomPDF or similar
            $pdf = Pdf::loadView('pdf.account-statement', [
                'accounts' => collect([$account]), // Pass as collection to match view expectation
                'transactions' => $transactions,
                'generated_at' => now()
            ]);
            
            return $pdf->download('account-statement-' . $account->account_number . '.pdf');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate PDF: ' . $e->getMessage());
            return null;
        }
    }

    public function getAccountTransactions($accountNumber)
    {
        return \App\Models\general_ledger::where('record_on_account_number', $accountNumber)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Enhanced UX Methods
    public function resetNavigation()
    {
        $this->showAccountTable = false;
        $this->showLevel3Table = false;
        $this->showLevel4Table = false;
        $this->selectedAccountType = null;
        $this->selectedAccountTypeName = '';
        $this->selectedLevel2Account = null;
        $this->selectedLevel2AccountName = '';
        $this->selectedLevel3Account = null;
        $this->selectedLevel3AccountName = '';
        $this->clearAllFilters();
    }

    public function clearGlobalSearch()
    {
        $this->globalSearch = '';
        $this->searchResults = [];
    }

    public function clearTableFilters()
    {
        $this->tableSearch = '';
        $this->statusFilter = '';
        $this->balanceFilter = '';
        $this->applyFilters();
    }

    public function clearAllFilters()
    {
        $this->globalSearch = '';
        $this->tableSearch = '';
        $this->statusFilter = '';
        $this->balanceFilter = '';
        $this->searchResults = [];
        $this->applyFilters();
    }

    public function sortTable($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->applyFilters();
    }

    public function applyFilters()
    {
        $this->loading = true;
        
        // Start with the base level2Accounts
        $accounts = $this->level2Accounts;
        
        // Apply table-specific filters
        if ($this->tableSearch) {
            $searchTerm = strtolower($this->tableSearch);
            $accounts = $accounts->filter(function($account) use ($searchTerm) {
                return str_contains(strtolower($account->account_name), $searchTerm) ||
                       str_contains(strtolower($account->account_number), $searchTerm);
            });
        }
        
        if ($this->statusFilter) {
            $accounts = $accounts->where('status', $this->statusFilter);
        }
        
        if ($this->balanceFilter) {
            switch ($this->balanceFilter) {
                case 'positive':
                    $accounts = $accounts->where('balance', '>', 0);
                    break;
                case 'negative':
                    $accounts = $accounts->where('balance', '<', 0);
                    break;
                case 'zero':
                    $accounts = $accounts->where('balance', 0);
                    break;
            }
        }
        
        // Apply sorting
        if ($this->sortBy && $this->sortDirection) {
            $accounts = $accounts->sortBy($this->sortBy);
            if ($this->sortDirection === 'desc') {
                $accounts = $accounts->reverse();
            }
        }
        
        $this->filteredLevel2Accounts = $accounts;
        $this->filteredAccountsCount = $accounts->count();
        
        $this->loading = false;
    }

    public function exportAccounts()
    {
        $this->loading = true;
        
        try {
            $accounts = $this->filteredLevel2Accounts->count() > 0 
                ? $this->filteredLevel2Accounts 
                : $this->level2Accounts;
            
            $filename = 'accounts_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            $callback = function() use ($accounts) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, array(
                    'Account Name',
                    'Account Number',
                    'Balance',
                    'Credit',
                    'Debit',
                    'Status',
                    'Type',
                    'Notes'
                ));
                
                // Add data
                foreach ($accounts as $account) {
                    fputcsv($file, array(
                        $account->account_name,
                        $account->account_number,
                        $account->balance,
                        $account->credit,
                        $account->debit,
                        $account->status,
                        $account->type,
                        $account->notes
                    ));
                }
                
                fclose($file);
            };
            
            session()->flash('success', 'Account export completed successfully.');
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to export accounts: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    // Enhanced search functionality
    public function updatedGlobalSearch()
    {
        if (strlen($this->globalSearch) >= 2) {
            $this->searchResults = Account::where('account_name', 'like', '%' . $this->globalSearch . '%')
                ->orWhere('account_number', 'like', '%' . $this->globalSearch . '%')
                ->orWhere('notes', 'like', '%' . $this->globalSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function updatedTableSearch()
    {
        $this->applyFilters();
    }

    public function updatedStatusFilter()
    {
        $this->applyFilters();
    }

    public function updatedBalanceFilter()
    {
        $this->applyFilters();
    }

    public function refreshStats()
    {
        $this->loading = true;
        
        // Refresh all statistics
        $this->render();
        
        session()->flash('success', 'Statistics refreshed successfully.');
        $this->loading = false;
    }

    public function render()
    {
        // General Ledger
        $this->generalLedgerCount = DB::table('accounts')->distinct('major_category_code')->count();

        // Revenue (income_accounts)
        $revenueQuery = DB::table('accounts')
            ->where('type', 'income_accounts')
            ->where('account_level', 2);
        $this->revenueStats = [
            'count' => $revenueQuery->count(),
            'balance' => (float) $revenueQuery->sum(DB::raw('CAST(credit AS DECIMAL)')) - (float) $revenueQuery->sum(DB::raw('CAST(debit AS DECIMAL)')),
            'active' => $revenueQuery->where('status', 'ACTIVE')->count(),
            'inactive' => $revenueQuery->where('status', 'INACTIVE')->count(),
        ];

        // Expenses (expense_accounts)
        $expenseQuery = DB::table('accounts')
            ->where('type', 'expense_accounts')
            ->where('account_level', 2);
        $this->expenseStats = [
            'count' => $expenseQuery->count(),
            'balance' => (float) $expenseQuery->sum(DB::raw('CAST(debit AS DECIMAL)')) - (float) $expenseQuery->sum(DB::raw('CAST(credit AS DECIMAL)')),
            'active' => $expenseQuery->where('status', 'ACTIVE')->count(),
            'inactive' => $expenseQuery->where('status', 'INACTIVE')->count(),
        ];

        // Assets (asset_accounts)
        $assetQuery = DB::table('accounts')
            ->where('type', 'asset_accounts')
            ->where('account_level', 2);
        $this->assetStats = [
            'count' => $assetQuery->count(),
            'balance' => (float) $assetQuery->sum(DB::raw('CAST(debit AS DECIMAL)')) - (float) $assetQuery->sum(DB::raw('CAST(credit AS DECIMAL)')),
            'active' => $assetQuery->where('status', 'ACTIVE')->count(),
            'inactive' => $assetQuery->where('status', 'INACTIVE')->count(),
        ];

        // Liabilities (liability_accounts)
        $liabilityQuery = DB::table('accounts')
            ->where('type', 'liability_accounts')
            ->where('account_level', 2);
        $this->liabilityStats = [
            'count' => $liabilityQuery->count(),
            'balance' => (float) $liabilityQuery->sum(DB::raw('CAST(credit AS DECIMAL)')) - (float) $liabilityQuery->sum(DB::raw('CAST(debit AS DECIMAL)')),
            'active' => $liabilityQuery->where('status', 'ACTIVE')->count(),
            'inactive' => $liabilityQuery->where('status', 'INACTIVE')->count(),
        ];

        // Equity (capital_accounts)
        $equityQuery = DB::table('accounts')
            ->where('type', 'capital_accounts')
            ->where('account_level', 2);
        $this->equityStats = [
            'count' => $equityQuery->count(),
            'balance' => (float) $equityQuery->sum(DB::raw('CAST(credit AS DECIMAL)')) - (float) $equityQuery->sum(DB::raw('CAST(debit AS DECIMAL)')),
            'active' => $equityQuery->where('status', 'ACTIVE')->count(),
            'inactive' => $equityQuery->where('status', 'INACTIVE')->count(),
        ];

        // Totals
        $this->totalAccounts = DB::table('accounts')->count();
        $this->activeAccounts = DB::table('accounts')->where('status', 'ACTIVE')->count();
        $this->inactiveAccounts = DB::table('accounts')->where('status', 'INACTIVE')->count();
        $this->totalBalance = DB::table('accounts')->sum(DB::raw('CAST(balance AS DECIMAL)'));

        return view('livewire.accounting.chart-of-accounts', [
            'generalLedgerCount' => $this->generalLedgerCount,
            'revenueStats' => $this->revenueStats,
            'expenseStats' => $this->expenseStats,
            'assetStats' => $this->assetStats,
            'liabilityStats' => $this->liabilityStats,
            'equityStats' => $this->equityStats,
            'totalAccounts' => $this->totalAccounts,
            'activeAccounts' => $this->activeAccounts,
            'inactiveAccounts' => $this->inactiveAccounts,
            'totalBalance' => $this->totalBalance,
            'selectedAccountType' => $this->selectedAccountType,
            'level2Accounts' => $this->level2Accounts,
            'showAccountTable' => $this->showAccountTable,
            'selectedAccountTypeName' => $this->selectedAccountTypeName,
            'level1ParentAccount' => $this->level1ParentAccount,
            'selectedLevel2Account' => $this->selectedLevel2Account,
            'level3Accounts' => $this->level3Accounts,
            'showLevel3Table' => $this->showLevel3Table,
            'selectedLevel2AccountName' => $this->selectedLevel2AccountName,
            'showCreateLevel3Modal' => $this->showCreateLevel3Modal,
            'level2ParentAccount' => $this->level2ParentAccount,
            'level3AccountName' => $this->level3AccountName,
            'showCreateLevel4Modal' => $this->showCreateLevel4Modal,
            'level3ParentAccount' => $this->level3ParentAccount,
            'level4AccountName' => $this->level4AccountName,
            'showCreateAccountModal' => $this->showCreateAccountModal,
            'parentAccount' => $this->parentAccount,
            'parentAccountType' => $this->parentAccountType,
            'accountName' => $this->accountName,
            'showEditModal' => $this->showEditModal,
            'editingAccount' => $this->editingAccount,
            'editAccountName' => $this->editAccountName,
            'showStatementModal' => $this->showStatementModal,
            'statementAccount' => $this->statementAccount,
            'statementTransactions' => $this->statementTransactions,
            'statementFilters' => $this->statementFilters,
            'statementBalance' => $this->statementBalance,
        ]);
    }
}
