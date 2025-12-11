<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\BankAccount;

class CashAccountsTableActions extends Component
{
    public $accountId;
    public $status;
    public $showDeleteModal = false;
    public $showDisableModal = false;
    public $showEnableModal = false;
    public $disableReason = '';

    public function mount($accountId, $status)
    {
        $this->accountId = $accountId;
        $this->status = $status;
    }

    public function view()
    {
        $this->emit('viewBankAccount', $this->accountId);
    }

    public function edit()
    {
        $this->emit('editBankAccount', $this->accountId);
    }

    public function confirmDelete()
    {
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->emit('deleteBankAccount', $this->accountId);
        $this->showDeleteModal = false;
    }

    public function confirmDisable()
    {
        $this->showDisableModal = true;
    }

    public function disable()
    {
        $this->validate([
            'disableReason' => 'required|min:3'
        ]);

        $this->emit('disableBankAccount', $this->accountId, $this->disableReason);
        $this->showDisableModal = false;
        $this->disableReason = '';
    }

    public function confirmEnable()
    {
        $this->showEnableModal = true;
    }

    public function enable()
    {
        $this->emit('enableBankAccount', $this->accountId);
        $this->showEnableModal = false;
    }

    public function render()
    {
        return view('livewire.accounting.cash-accounts-table-actions');
    }
}
