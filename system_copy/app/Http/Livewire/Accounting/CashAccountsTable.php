<?php

namespace App\Http\Livewire\Accounting;

use App\Models\BankAccount;
use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\MembersModel;
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

class CashAccountsTable extends Component
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
        'description' => ''
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
        'description' => ''
    ];

    protected $rules = [
        'newBankAccount.bank_name' => 'required|min:3',
        'newBankAccount.account_name' => 'required|min:3',
        'newBankAccount.account_number' => 'required|min:3',
        'newBankAccount.branch_name' => 'required|min:3',
        'newBankAccount.swift_code' => 'required|min:3',
        'newBankAccount.currency' => 'required|min:3',
        'newBankAccount.opening_balance' => 'required|numeric',
        'newBankAccount.internal_mirror_account_number' => 'required|min:3',
        'newBankAccount.description' => 'nullable|min:3',
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
            'description' => ''
        ];
    }

    public function createBankAccount()
    {
        $this->validate();

        $bankAccount = BankAccount::create([
            'bank_name' => $this->newBankAccount['bank_name'],
            'account_name' => $this->newBankAccount['account_name'],
            'account_number' => $this->newBankAccount['account_number'],
            'branch_name' => $this->newBankAccount['branch_name'],
            'swift_code' => $this->newBankAccount['swift_code'],
            'currency' => $this->newBankAccount['currency'],
            'opening_balance' => $this->newBankAccount['opening_balance'],
            'current_balance' => $this->newBankAccount['opening_balance'],
            'internal_mirror_account_number' => $this->newBankAccount['internal_mirror_account_number'],
            'status' => $this->newBankAccount['status'],
            'description' => $this->newBankAccount['description'],
        ]);

        $this->closeAddModal();
        $this->emit('bankAccountAdded');
        session()->flash('message', 'Bank account created successfully.');
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
            'description' => ''
        ];
    }

    public function updateBankAccount()
    {
        $this->validate([
            'editing.bank_name' => 'required|min:3',
            'editing.account_name' => 'required|min:3',
            'editing.account_number' => 'required|min:3',
            'editing.branch_name' => 'required|min:3',
            'editing.swift_code' => 'required|min:3',
            'editing.currency' => 'required|min:3',
            'editing.internal_mirror_account_number' => 'required|min:3',
            'editing.description' => 'nullable|min:3',
        ]);

        $this->selectedBankAccount->update([
            'bank_name' => $this->editing['bank_name'],
            'account_name' => $this->editing['account_name'],
            'account_number' => $this->editing['account_number'],
            'branch_name' => $this->editing['branch_name'],
            'swift_code' => $this->editing['swift_code'],
            'currency' => $this->editing['currency'],
            'internal_mirror_account_number' => $this->editing['internal_mirror_account_number'],
            'description' => $this->editing['description'],
        ]);

        $this->closeEditModal();
        $this->emit('bankAccountUpdated');
        session()->flash('message', 'Bank account updated successfully.');
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

        return view('livewire.accounting.cash-accounts-table', [
            'bankAccounts' => $bankAccounts
        ]);
    }
}
