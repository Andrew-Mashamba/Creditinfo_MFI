<?php
namespace App\Http\Livewire\Accounting;

use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\AccountsModel;

class ManualPostingChartOfAccounts extends Component
{
    public $left_category;
    public $left_category_code;
    public $left_sub_category_code;
    public $left_account;
    public $right_category;
    public $right_subcategory;
    public $right_account;
    public $amount;
    public $narration;
    public $transactionPosted;
    public $left_account_id;

    public $role;
    public $left_account_details;
    public $right_subcategories = [];
    public $right_accounts = [];

    // Dependency injection property
    protected $transactionService;

    // Use mount to inject dependencies
    public function mount(TransactionPostingService $transactionService)
    {
        // Initialize your properties as needed
        $this->left_account_id = session()->get('accountId2_l2');
        $this->left_category = session()->get('category');

        $this->left_account_details = AccountsModel::where("sub_category_code", $this->left_account_id)->first();
        $this->left_category_code = $this->left_account_details->category_code;
        $this->left_sub_category_code = $this->left_account_details->sub_category_code;
        $this->left_account = $this->left_account_details->account_number;
    }

    public function updatedRightCategory($value)
    {
        $this->right_subcategory = null;
        $this->right_account = null;
        
        $this->right_subcategories = collect(DB::table('accounts')
            ->where('major_category_code', $value)
            ->where('account_level', 2)
            ->select('category_code', 'account_name')
            ->get())
            ->map(function ($item) {
                return [
                    'category_code' => $item->category_code,
                    'account_name' => $item->account_name
                ];
            })->toArray();
    }

    public function updatedRightSubcategory($value)
    {
        $this->right_account = null;
        
        $this->right_accounts = collect(DB::table('accounts')
            ->where('major_category_code', $this->right_category)
            ->where('category_code', $value)
            ->where('account_level', 3)
            ->select('account_number', 'account_name')
            ->get())
            ->map(function ($item) {
                return [
                    'account_number' => $item->account_number,
                    'account_name' => $item->account_name
                ];
            })->toArray();
    }

    public function post()
    {
        $this->validate([
            'right_category' => 'required',
            'right_subcategory' => 'required',
            'right_account' => 'required',
            'amount' => 'required|numeric|min:0',
            'narration' => 'required|string'
        ]);

        $narration = $this->narration;
        $first_account = $this->left_account_details;
        $second_account = AccountsModel::where("account_number", $this->right_account)->first();

        try{
        // Process payment if linked to savings account
        if (!empty($first_account) && !empty($second_account)) {
            $totalAmount = $this->amount;
            
            // Post the transaction using TransactionPostingService
            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $first_account, // Debit account (savings)
                'second_account' => $second_account, // Credit account (shares)
                'amount' => $totalAmount,
                'narration' => $narration,
                'action' => 'manual_posting',
            ];

            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['status'] !== 'success') {
                throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
            }
        }
        }catch(\Exception $e){
            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
        }

        $this->resetInputFields();
        //$this->emit('refreshChartOfAccountsComponent');
    }

    private function resetInputFields()
    {
        $this->right_category = null;
        $this->right_subcategory = null;
        $this->right_account = null;
        $this->amount = null;
        $this->narration = null;
        $this->right_subcategories = [];
        $this->right_accounts = [];
    }

    public function render()
    {
        $gl_accounts = DB::table('GL_accounts')->get();
        
        return view('livewire.accounting.manual-posting-chart-of-accounts', [
            'gl_accounts' => $gl_accounts,
        ]);
    }
}

