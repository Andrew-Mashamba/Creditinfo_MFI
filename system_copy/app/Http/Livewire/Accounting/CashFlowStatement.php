<?php
namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\CashFlowConfiguration;

use Livewire\Component;

class CashFlowStatement extends Component
{
    public $accounts;
    public $selectedAccounts = [
        'operating' => [],
        'investing' => [],
        'financing' => [],
    ];
    public $newAccountId = [
        'operating' => null,
        'investing' => null,
        'financing' => null,
    ];
    public $newOperation = [
        'operating' => 'add',
        'investing' => 'add',
        'financing' => 'add',
    ];

    public function mount()
    {
        // Load all accounts once
        $this->accounts = AccountsModel::all();  // Fetch all accounts from the Account model

        // Load existing configurations if they exist
        $this->loadConfigurations();
    }

    public function loadConfigurations()
    {
        // Load existing configurations from database if any
        $configurations = CashFlowConfiguration::all();

        foreach (['operating', 'investing', 'financing'] as $section) {
            $this->selectedAccounts[$section] = $configurations->where('section', $section)->map(function ($config) {
                // Fetch the account by its ID
                $account = AccountsModel::find($config->account_id);

                // Check if account exists, then return the relevant details
                if ($account) {
                    return [
                        'id' => $account->id,
                        'operation' => $config->operation,
                        'account_name' => $account->account_name,  // Include account name
                        'balance' => $account->balance,  // Include balance
                    ];
                }

                // If account doesn't exist, return a default array (empty balance and account name)
                return [
                    'id' => $config->account_id,
                    'operation' => $config->operation,
                    'account_name' => 'Unknown Account',  // Handle missing account
                    'balance' => 0,  // Default balance if account is missing
                ];
            })->toArray();
        }
    }


    public function saveConfiguration()
    {
        // Delete old configurations and save new ones
        foreach ($this->selectedAccounts as $section => $accounts) {
            CashFlowConfiguration::where('section', $section)->delete();

            foreach ($accounts as $account) {
                if ($account['id']) {
                    CashFlowConfiguration::create([
                        'section' => $section,
                        'account_id' => $account['id'],
                        'operation' => $account['operation'],
                    ]);
                }
            }
        }

        session()->flash('message', 'Cash flow configuration saved successfully!');
    }

    public function addRow($section)
    {
        $accountId = $this->newAccountId[$section];
        $operation = $this->newOperation[$section];

        // Find the selected account using the ID
        $account = $this->accounts->firstWhere('id', $accountId);

        if ($account) {
            // Add the account to the selected list with its balance and other information
            $this->selectedAccounts[$section][] = [
                'id' => $account->id,
                'account_name' => $account->account_name,
                'operation' => $operation,
                'balance' => $account->balance,  // Ensure balance is included here
            ];

            // Reset the fields for the next input
            $this->newAccountId[$section] = null;
            $this->newOperation[$section] = 'add';
        }
    }


    public function removeRow($section, $index)
    {
        unset($this->selectedAccounts[$section][$index]);
        // Reindex the array to avoid gaps in keys
        $this->selectedAccounts[$section] = array_values($this->selectedAccounts[$section]);
    }

    public function render()
    {
        return view('livewire.accounting.cash-flow-statement', [
            'accounts' => $this->accounts,
        ]);
    }
}
