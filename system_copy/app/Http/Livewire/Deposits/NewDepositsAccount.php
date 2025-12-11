<?php

namespace App\Http\Livewire\Deposits;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\sub_products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewDepositsAccount extends Component
{
    public $client_number;
    public $product_id;
    public $account_number;
    public $account_name;
    public $balance = 0;
    public $linked_deposits_account;

    protected $rules = [
        'client_number' => 'required|exists:clients,client_number',
        'product_id' => 'required|exists:sub_products,id',
        'account_number' => 'required|unique:accounts,account_number',
        'account_name' => 'required|string|max:255',
        'balance' => 'nullable|numeric|min:0',
    ];

    public function mount()
    {
        // Initialize component
    }

    public function createAccount()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Get product details
            $product = sub_products::find($this->product_id);
            if (!$product) {
                throw new \Exception('Product not found');
            }

            // Create new deposits account
            $account = new AccountsModel();
            $account->client_number = $this->client_number;
            $account->product_number = $product->product_number;
            $account->account_number = $this->account_number;
            $account->account_name = $this->account_name;
            $account->balance = $this->balance;
            $account->major_category_code = 2000; // Deposits category
            $account->status = 'ACTIVE';
            $account->save();

            DB::commit();

            $this->dispatchBrowserEvent('show-success', ['message' => 'Deposits account created successfully']);
            $this->reset(['client_number', 'product_id', 'account_number', 'account_name', 'balance']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating deposits account: ' . $e->getMessage());
            $this->dispatchBrowserEvent('show-error', ['message' => 'Failed to create account: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $clients = ClientsModel::where('status', 'ACTIVE')->get();
        $products = sub_products::where('major_category_code', 2000)->get();

        return view('livewire.deposits.new-deposits-account', [
            'clients' => $clients,
            'products' => $products,
        ]);
    }
}

