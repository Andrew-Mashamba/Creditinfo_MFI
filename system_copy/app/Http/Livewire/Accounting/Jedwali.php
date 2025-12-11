<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Jedwali extends Component
{

    public $income_accounts;

    public $term = "";
    public $showAddUser = false;
    public $memberStatus = 'All';
    public $numberOfProducts;
    public $products;
    public $item;
    public $category;
    public $account_name;
    public $notes;
    public $has_mirror_account=false;
    public $mirror_account;
    public $bank_id;
    public $account_number;
    public $createNewAccount;
    public $banks;


    // new
    public $sub_category_name;
    public $account_code;

    //public $asset_accounts;
    public $liability_accounts;
    public $capital_accounts;

    public $expense_accounts;



    public $accountName;

    public $asset_accounts = [];
    public $showCreateAccountInputs = false;
    public $editAccountId;
    public $newAccountName;
    public $newInitialAmount;

    public $main_total_amount = 0;

    public $source_account;
    public $amount;
    public $destination_accounts;
    public $narration;

    protected $creditAndDebitService;

    public $showEditModal;
    public $showEditModal_l2;
    public $showCreateAccountInputs_l2;

    public $newAccountName_l2 = [];
    public $newInitialAmount_l2 = [];
    public $accountId_l2;
    public $table_l2;
    public $selected_sub_category_code;
    public $category_name = '';

    public $selected_sub_category_code2;
    public $category_name2 = '';

    public $selected_sub_category_code3;
    public $category_name3 = '';

    public $showPostingModel;


    public function render()
    {

        $this->asset_accounts = DB::table('asset_accounts')->get();
        $this->liability_accounts = DB::table('liability_accounts')->get();
        $this->capital_accounts = DB::table('capital_accounts')->get();

        $this->income_accounts = DB::table('income_accounts')->get();
        $this->expense_accounts = DB::table('expense_accounts')->get();
        return view('livewire.accounting.jedwali');
    }
}
