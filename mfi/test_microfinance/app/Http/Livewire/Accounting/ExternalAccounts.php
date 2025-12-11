<?php

namespace App\Http\Livewire\Accounting;

use App\Models\BankAccount;
use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\MembersModel;
use App\Services\AccountDetailsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;
use Exception;
use Illuminate\Support\Facades\Schema;

class ExternalAccounts extends Component
{

    use WithPagination;

    public $search = '';
    public $sortField = 'bank_name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Modal states
    public $showAddModal = false;
    public $showViewModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDisableModal = false;
    public $showEnableModal = false;
    public $selectedBankAccount;
    public $disableReason = '';
    public $debit_category;
    public $debit_subcategory;
    public $right_category;
    public $right_account;
    public $debit_category_code;
    public $debit_subcategory_code;
    public $right_category_code;
    public $debit_account;
    public $menuSearch = '';

    // Form data
    public $newBankAccount = [
        'bank_name' => '',
        'account_name' => '',
        'account_number' => '',
        'branch_name' => '',
        'swift_code' => '',
        'currency' => 'TZS',
        'opening_balance' => 0,
        'current_balance' => 0,
        'internal_mirror_account_number' => '',
        'status' => 'active',
        'description' => '',
        'account_type' => 'main_operations',
        'branch_id' => null
    ];

    public $editing = [
        'bank_name' => '',
        'account_name' => '',
        'account_number' => '',
        'branch_name' => '',
        'swift_code' => '',
        'currency' => 'TZS',
        'current_balance' => 0,
        'internal_mirror_account_number' => '',
        'status' => 'active',
        'description' => '',
        'account_type' => 'main_operations',
        'branch_id' => null
    ];

    protected $rules = [
        'newBankAccount.bank_name' => 'required|min:3',
        'newBankAccount.account_name' => 'required|min:3',
        'newBankAccount.account_number' => 'required|min:3',
        'newBankAccount.branch_name' => 'nullable|min:3',
        'newBankAccount.swift_code' => 'required|min:3',
        'newBankAccount.currency' => 'required|min:3',
        'newBankAccount.opening_balance' => 'required|numeric',
        'newBankAccount.internal_mirror_account_number' => 'required|min:3',
        'newBankAccount.description' => 'nullable|min:3',
        'newBankAccount.account_type' => 'required|in:main_operations,branch',
        'newBankAccount.branch_id' => 'nullable|exists:branches,id'
    ];

    protected $listeners = [
        'bankAccountAdded' => '$refresh',
        'bankAccountUpdated' => '$refresh',
        'bankAccountDeleted' => '$refresh',
        'bankAccountDisabled' => '$refresh',
        'bankAccountEnabled' => '$refresh',
        'viewBankAccount' => 'view',
        'editBankAccount' => 'edit',
        'confirmDelete' => 'confirmDelete',
        'confirmDisable' => 'confirmDisable',
        'confirmEnable' => 'confirmEnable'
    ];

    /**
     * Get account balance from external API
     *
     * @return float
     */
    public function getAccountBalance()
    {
        try {
            $accountNumber = $this->newBankAccount['account_number'];
            
            if (empty($accountNumber)) {
                Log::warning('Account number is empty, cannot fetch balance from external API');
                return 0.0;
            }

            $accountDetailsService = new AccountDetailsService();
            $result = $accountDetailsService->getAccountDetails($accountNumber);

            Log::info('Account balance fetched from external API', [
                'account_number' => $accountNumber,
                'status_code' => $result['statusCode'],
                'balance' => $result['body']['availableBalance'] ?? 0
            ]);

            if ($result['statusCode'] === 600 && isset($result['body']['availableBalance'])) {
                return (float) $result['body']['availableBalance'];
            } else {
                Log::warning('Failed to get account balance from external API', [
                    'account_number' => $accountNumber,
                    'status_code' => $result['statusCode'],
                    'message' => $result['message'] ?? 'Unknown error'
                ]);
                return 0.0;
            }

        } catch (Exception $e) {
            Log::error('Error fetching account balance from external API', [
                'account_number' => $accountNumber ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0.0;
        }
    }

    public function updatedDebitCategory($value)
    {
        if ($value) {
            $this->debit_category_code = $value;
            $this->debit_subcategory = null;
            $this->debit_subcategory_code = null;
            $this->debit_account = null;
        } else {
            $this->debit_category_code = null;
            $this->debit_subcategory = null;
            $this->debit_subcategory_code = null;
            $this->debit_account = null;
        }
    }

    public function updatedDebitSubcategory($value)
    {
        if ($value) {
            $this->debit_subcategory_code = $value;
            $this->debit_account = null;
        } else {
            $this->debit_subcategory_code = null;
            $this->debit_account = null;
        }
    }

    public function updatedRightCategory($value)
    {
        if ($value) {
            $this->right_category_code = $value;
            $this->right_account = null;
        } else {
            $this->right_category_code = null;
            $this->right_account = null;
        }
    }

    public function updatedNewBankAccountAccountType($value)
    {
        if ($value === 'branch') {
            // Reset branch_id when changing to branch type to force user selection
            $this->newBankAccount['branch_id'] = null;
        } else {
            // Clear branch_id and branch_name when changing to main operations
            $this->newBankAccount['branch_id'] = null;
            $this->newBankAccount['branch_name'] = '';
        }
    }

    public function updatedEditingAccountType($value)
    {
        if ($value === 'branch') {
            // Reset branch_id when changing to branch type to force user selection
            $this->editing['branch_id'] = null;
        } else {
            // Clear branch_id and branch_name when changing to main operations
            $this->editing['branch_id'] = null;
            $this->editing['branch_name'] = '';
        }
    }

    public function updatingSearch()
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

    public function showAddBankAccountModal()
    {
        $this->resetNewBankAccount();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetNewBankAccount();
    }

    public function resetNewBankAccount()
    {
        $this->newBankAccount = [
            'bank_name' => '',
            'account_name' => '',
            'account_number' => '',
            'branch_name' => '',
            'swift_code' => '',
            'currency' => 'TZS',
            'opening_balance' => 0,
            'current_balance' => 0,
            'internal_mirror_account_number' => '',
            'status' => 'active',
            'description' => '',
            'account_type' => 'main_operations',
            'branch_id' => null
        ];
    }

    public function createBankAccount()
    {
        // Custom validation for branch accounts
        $this->validateBankAccountForm();

        try {
            // Get account balance from external API
            $externalBalance = $this->getAccountBalance();
            
            Log::info('Creating bank account with external balance check', [
                'account_number' => $this->newBankAccount['account_number'],
                'external_balance' => $externalBalance,
                'user_id' => Auth::id()
            ]);

            $bankAccount = BankAccount::create([
                'bank_name' => $this->newBankAccount['bank_name'],
                'account_name' => $this->newBankAccount['account_name'],
                'account_number' => $this->newBankAccount['account_number'],
                'branch_name' => $this->newBankAccount['branch_name'],
                'swift_code' => $this->newBankAccount['swift_code'],
                'currency' => $this->newBankAccount['currency'],
                'opening_balance' => $externalBalance,
                'current_balance' => $externalBalance,
                'internal_mirror_account_number' => $this->newBankAccount['internal_mirror_account_number'],
                //'status' => $this->newBankAccount['status'],
                'description' => $this->newBankAccount['description'],
                'account_type' => $this->newBankAccount['account_type'],
                'branch_id' => $this->newBankAccount['branch_id']
            ]);

            $this->closeAddModal();
            $this->emit('bankAccountAdded');
            
            if ($externalBalance > 0) {
                session()->flash('message', "Bank account created successfully with balance: " . number_format($externalBalance, 2) . " " . $this->newBankAccount['currency']);
            } else {
                session()->flash('message', 'Bank account created successfully. (Balance could not be retrieved from external API)');
            }

        } catch (Exception $e) {
            Log::error('Error creating bank account', [
                'account_number' => $this->newBankAccount['account_number'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            session()->flash('error', 'Failed to create bank account: ' . $e->getMessage());
        }
    }

    private function validateBankAccountForm()
    {
        $rules = $this->rules;
        
        // If account type is branch, make branch_id and branch_name required
        if ($this->newBankAccount['account_type'] === 'branch') {
            $rules['newBankAccount.branch_id'] = 'required|exists:branches,id';
            $rules['newBankAccount.branch_name'] = 'required|min:3';
        }
        
        $this->validate($rules);
    }

    public function view($id)
    {
        $this->selectedBankAccount = BankAccount::findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedBankAccount = null;
    }

    public function edit($id)
    {
        $this->selectedBankAccount = BankAccount::findOrFail($id);
        $this->editing = $this->selectedBankAccount->toArray();
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedBankAccount = null;
        $this->editing = [
            'bank_name' => '',
            'account_name' => '',
            'account_number' => '',
            'branch_name' => '',
            'swift_code' => '',
            'currency' => 'TZS',
            'current_balance' => 0,
            'internal_mirror_account_number' => '',
            'status' => 'active',
            'description' => '',
            'account_type' => 'main_operations',
            'branch_id' => null
        ];
    }

    /**
     * Refresh account balance from external API for existing bank account
     *
     * @param int $bankAccountId
     * @return void
     */
    public function refreshAccountBalance($bankAccountId)
    {
        try {
            $bankAccount = BankAccount::findOrFail($bankAccountId);
            $accountNumber = $bankAccount->account_number;

            Log::info('========== EXTERNAL BALANCE REFRESH START ==========', [
                'bank_account_id' => $bankAccountId,
                'account_number' => $accountNumber,
                'bank_name' => $bankAccount->bank_name,
                'account_name' => $bankAccount->account_name,
                'user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString()
            ]);

            // Log API configuration being used
            Log::info('External API Configuration', [
                'base_url' => config('services.account_details.base_url'),
                'api_key' => substr(config('services.account_details.api_key'), 0, 20) . '...',
                'channel_name' => config('services.account_details.channel_name'),
                'channel_code' => config('services.account_details.channel_code'),
                'private_key_path' => config('services.account_details.private_key_path'),
                'timeout' => config('services.account_details.timeout', 30)
            ]);

            $accountDetailsService = new AccountDetailsService();
            $result = $accountDetailsService->getAccountDetails($accountNumber);

            // Log the FULL API response for debugging
            Log::info('External API Full Response', [
                'bank_account_id' => $bankAccountId,
                'account_number' => $accountNumber,
                'full_result' => json_encode($result, JSON_PRETTY_PRINT),
                'result_status_code' => $result['statusCode'] ?? 'MISSING',
                'result_message' => $result['message'] ?? 'MISSING',
                'result_body' => json_encode($result['body'] ?? [], JSON_PRETTY_PRINT),
                'has_available_balance' => isset($result['body']['availableBalance']) ? 'YES' : 'NO',
                'available_balance_value' => $result['body']['availableBalance'] ?? 'NOT_SET'
            ]);

            if ($result['statusCode'] === 600 && isset($result['body']['availableBalance'])) {
                $newBalance = (float) $result['body']['availableBalance'];
                $oldBalance = $bankAccount->current_balance;

                $bankAccount->update([
                    'current_balance' => $newBalance,
                    'updated_at' => now()
                ]);

                Log::info('✓ Account balance updated successfully', [
                    'bank_account_id' => $bankAccountId,
                    'account_number' => $accountNumber,
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'difference' => $newBalance - $oldBalance,
                    'user_id' => Auth::id()
                ]);

                session()->flash('message', "Account balance updated: " . number_format($newBalance, 2) . " " . $bankAccount->currency);
            } else {
                Log::warning('✗ Failed to refresh account balance from external API', [
                    'bank_account_id' => $bankAccountId,
                    'account_number' => $accountNumber,
                    'status_code' => $result['statusCode'] ?? 'MISSING',
                    'message' => $result['message'] ?? 'Unknown error',
                    'expected_status_code' => 600,
                    'actual_status_code' => $result['statusCode'] ?? 'NULL',
                    'status_code_match' => ($result['statusCode'] ?? null) === 600 ? 'YES' : 'NO',
                    'body_keys' => array_keys($result['body'] ?? []),
                    'full_body' => json_encode($result['body'] ?? [], JSON_PRETTY_PRINT)
                ]);

                session()->flash('error', 'Failed to refresh account balance: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('========== EXTERNAL BALANCE REFRESH END ==========');

        } catch (Exception $e) {
            Log::error('✗ Exception during account balance refresh', [
                'bank_account_id' => $bankAccountId,
                'account_number' => $accountNumber ?? 'NOT_SET',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            session()->flash('error', 'Failed to refresh account balance: ' . $e->getMessage());
        }
    }

    public function updateBankAccount()
    {
        // Custom validation for branch accounts
        $this->validateEditBankAccountForm();

        try {
            // Check if account number has changed and refresh balance
            $accountNumberChanged = $this->selectedBankAccount->account_number !== $this->editing['account_number'];
            
            $this->selectedBankAccount->update([
                'bank_name' => $this->editing['bank_name'],
                'account_name' => $this->editing['account_name'],
                'account_number' => $this->editing['account_number'],
                'branch_name' => $this->editing['branch_name'],
                'swift_code' => $this->editing['swift_code'],
                'currency' => $this->editing['currency'],
                'internal_mirror_account_number' => $this->editing['internal_mirror_account_number'],
                'description' => $this->editing['description'],
                'account_type' => $this->editing['account_type'],
                'branch_id' => $this->editing['branch_id']
            ]);

            // If account number changed, refresh balance from external API
            if ($accountNumberChanged) {
                $this->refreshAccountBalance($this->selectedBankAccount->id);
            }

            $this->closeEditModal();
            $this->emit('bankAccountUpdated');
            session()->flash('message', 'Bank account updated successfully.');

        } catch (Exception $e) {
            Log::error('Error updating bank account', [
                'bank_account_id' => $this->selectedBankAccount->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            session()->flash('error', 'Failed to update bank account: ' . $e->getMessage());
        }
    }

    private function validateEditBankAccountForm()
    {
        $rules = [
            'editing.bank_name' => 'required|min:3',
            'editing.account_name' => 'required|min:3',
            'editing.account_number' => 'required|min:3',
            'editing.branch_name' => 'nullable|min:3',
            'editing.swift_code' => 'required|min:3',
            'editing.currency' => 'required|min:3',
            'editing.internal_mirror_account_number' => 'required|min:3',
            'editing.description' => 'nullable|min:3',
            'editing.account_type' => 'required|in:main_operations,branch',
            'editing.branch_id' => 'nullable|exists:branches,id'
        ];
        
        // If account type is branch, make branch_id and branch_name required
        if ($this->editing['account_type'] === 'branch') {
            $rules['editing.branch_id'] = 'required|exists:branches,id';
            $rules['editing.branch_name'] = 'required|min:3';
        }
        
        $this->validate($rules);
    }

    public function confirmDelete($id)
    {
        $this->selectedBankAccount = BankAccount::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->selectedBankAccount->delete();
        $this->showDeleteModal = false;
        $this->selectedBankAccount = null;
        $this->emit('bankAccountDeleted');
        session()->flash('message', 'Bank account deleted successfully.');
    }

    public function confirmDisable($id)
    {
        $this->selectedBankAccount = BankAccount::findOrFail($id);
        $this->showDisableModal = true;
    }

    public function disable()
    {
        $this->validate([
            'disableReason' => 'required|min:3'
        ]);

        $this->selectedBankAccount->update([
            'status' => 'disabled',
            'disable_reason' => $this->disableReason
        ]);

        $this->showDisableModal = false;
        $this->selectedBankAccount = null;
        $this->disableReason = '';
        $this->emit('bankAccountDisabled');
        session()->flash('message', 'Bank account disabled successfully.');
    }

    public function confirmEnable($id)
    {
        $this->selectedBankAccount = BankAccount::findOrFail($id);
        $this->showEnableModal = true;
    }

    public function enable()
    {
        $this->selectedBankAccount->update([
            'status' => 'active',
            'disable_reason' => null
        ]);

        $this->showEnableModal = false;
        $this->selectedBankAccount = null;
        $this->emit('bankAccountEnabled');
        session()->flash('message', 'Bank account enabled successfully.');
    }

    public function render()
    {
        $bankAccounts = BankAccount::query()
            ->when($this->search, function($query) {
                $query->where(function($query) {
                    $query->where('bank_name', 'like', '%' . $this->search . '%')
                        ->orWhere('account_name', 'like', '%' . $this->search . '%')
                        ->orWhere('account_number', 'like', '%' . $this->search . '%')
                        ->orWhere('branch_name', 'like', '%' . $this->search . '%')
                        ->orWhere('swift_code', 'like', '%' . $this->search . '%')
                        ->orWhere('currency', 'like', '%' . $this->search . '%')
                        ->orWhere('internal_mirror_account_number', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Get branches for dropdown
        $branches = collect();
        if (Schema::hasTable('branches')) {
            $branches = DB::table('branches')
                ->select('id', 'name')
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        }

        // Get mirror accounts for dropdown (accounts with major_category_code = 1000)
        $mirrorAccounts = collect();
        if (Schema::hasTable('accounts')) {
            $mirrorAccounts = DB::table('accounts')
                ->select('account_number', 'account_name', 'id')
                ->where('major_category_code', '1000')
                ->orderBy('account_name')
                ->get();
        }

        return view('livewire.accounting.external-accounts', [
            'bankAccounts' => $bankAccounts,
            'branches' => $branches,
            'mirrorAccounts' => $mirrorAccounts
        ]);
    }
}
