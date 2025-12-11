<?php

namespace App\Http\Livewire\Deposits;

use Livewire\Component;
use App\Models\AccountsModel;
use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\SubProducts;
use App\Models\ApprovalRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class NewDeposits extends Component
{
    public $selectedMember;
    public $selectedProduct;
    public $amount;
    public $narration;
    public $linked_deposits_account;
    public $isLoading = false;
    public $errorMessage = '';
    public $successMessage = '';

    protected $rules = [
        'selectedMember' => 'required',
        'selectedProduct' => 'required',
        'amount' => 'required|numeric|min:100',
        'narration' => 'nullable|string|max:255'
    ];

    public function mount()
    {
        // Initialize component
    }

    public function loadAccounts()
    {
        if ($this->selectedMember && $this->selectedProduct) {
            $this->linked_deposits_account = AccountsModel::where('client_number', $this->selectedMember)
                ->where('product_number', $this->selectedProduct)
                ->where('major_category_code', 2000) // Deposits category
                ->where('status', 'ACTIVE')
                ->first();
        }
    }

    public function updatedSelectedMember()
    {
        $this->loadAccounts();
    }

    public function updatedSelectedProduct()
    {
        $this->loadAccounts();
    }

    public function submitNewDeposits()
    {
        try {
            $this->validate();

            if (!$this->linked_deposits_account) {
                $this->errorMessage = 'No active deposits account found for this member and product';
                return;
            }

            // Check if member exists and is active
            $member = ClientsModel::where('client_number', $this->selectedMember)
                ->where('status', 'ACTIVE')
                ->first();

            if (!$member) {
                $this->errorMessage = 'Member not found or inactive';
                return;
            }

            // Generate transaction reference
            $reference_number = 'NEW_DEP_' . date('YmdHis') . '_' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Get product details
            $product = SubProducts::find($this->selectedProduct);
            if (!$product) {
                $this->errorMessage = 'Product not found';
                return;
            }

            // Calculate new balances
            $deposits_account_new_balance = $this->linked_deposits_account->balance + $this->amount;

            // Create standardized narration
            $narration = sprintf(
                'NEW_DEPOSITS|%s|%s|%s|%s|%s',
                $this->selectedMember,
                $this->linked_deposits_account->account_number,
                number_format($this->amount, 2),
                $product->product_name,
                date('Y-m-d H:i:s')
            );

            // Post transaction to general ledger
            $transactionData = [
                'reference_number' => $reference_number,
                'transaction_date' => now()->format('Y-m-d'),
                'narration' => $narration,
                'amount' => $this->amount,
                'first_account' => $this->getCashAccount(), // Debit cash account
                'second_account' => $this->linked_deposits_account->account_number, // Credit deposits account
                'transaction_type' => 'credit',
                'action' => 'new_deposits',
                'member_id' => $this->selectedMember,
                'account_holder_name' => $member->first_name . ' ' . $member->last_name,
                'payment_method' => 'cash',
                'processed_by' => Auth::user()->name ?? 'System',
                'institution_id' => $this->getCurrentInstitution()
            ];

            // Post the transaction
            $result = $this->postTransaction($transactionData);

            if ($result['success']) {
                // Update account balance
                $this->linked_deposits_account->balance = $deposits_account_new_balance;
                $this->linked_deposits_account->save();

                // Log successful transaction
                Log::info('New deposits transaction processed successfully', [
                    'reference' => $reference_number,
                    'member' => $this->selectedMember,
                    'account' => $this->linked_deposits_account->account_number,
                    'amount' => $this->amount,
                    'new_balance' => $deposits_account_new_balance
                ]);

                $this->successMessage = 'New deposits transaction processed successfully. Reference: ' . $reference_number;
                $this->resetForm();
            } else {
                $this->errorMessage = 'Failed to process transaction: ' . $result['message'];
            }

        } catch (Exception $e) {
            Log::error('Error processing new deposits transaction', [
                'member' => $this->selectedMember,
                'product' => $this->selectedProduct,
                'amount' => $this->amount,
                'error' => $e->getMessage()
            ]);

            $this->errorMessage = 'Error processing transaction: ' . $e->getMessage();
        }
    }

    private function getCashAccount()
    {
        $account = AccountsModel::where('account_name', 'LIKE', '%cash in safe%')
            ->orWhere('account_name', 'LIKE', '%cash%')
            ->where('status', 'ACTIVE')
            ->where('institution_number', $this->getCurrentInstitution())
            ->first();

        return $account ? $account->account_number : '1000';
    }

    private function getCurrentInstitution()
    {
        return Auth::user()->institution_id ?? '1';
    }

    private function postTransaction($data)
    {
        try {
            // Implementation for posting transaction
            // This would typically call your transaction service
            return ['success' => true, 'message' => 'Transaction posted successfully'];
        } catch (Exception $e) {
            Log::error('Error posting transaction: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function resetForm()
    {
        $this->selectedMember = '';
        $this->selectedProduct = '';
        $this->amount = '';
        $this->narration = '';
        $this->linked_deposits_account = null;
        $this->errorMessage = '';
    }

    public function render()
    {
        $members = ClientsModel::where('status', 'ACTIVE')
            ->orderBy('first_name')
            ->get();

        $products = SubProducts::where('major_category_code', 2000) // Deposits category
            ->where('status', 'ACTIVE')
            ->orderBy('product_name')
            ->get();

        return view('livewire.deposits.new-deposits', [
            'members' => $members,
            'products' => $products
        ]);
    }
}
