<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PreviousIncAndExp extends Component
{

    public $term = "";
    public $showAddUser = false;
    public $memberStatus = 'All';
    public $numberOfProducts;
    public $products,$is_view_account=false;
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
    public $income_accounts;
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
    public $showStatementModel = false;
    public $showPostingModel = false;
    public $accountId2_l2;


    public $selected_sub_category_code;
    public $category_name = '';

    public $selected_sub_category_code2;
    public $category_name2 = '';

    public $selected_sub_category_code3;
    public $category_name3 = '';



    public function setSubCode($code,$category_name){
        $this->selected_sub_category_code = $code;
        $this->category_name = $category_name;
        $this->selected_sub_category_code2 = null;
        $this->category_name2 = null;
    }

    public function setSubCode2($code,$category_name){
        $this->selected_sub_category_code2 = $code;
        $this->category_name2 = $category_name;

        $this->selected_sub_category_code3 = null;
        $this->category_name3 = null;

    }

    public function setSubCode3($code,$category_name){
        $this->selected_sub_category_code3 = $code;
        $this->category_name3 = $category_name;
        $account_number = DB::table('sub_accounts')
            ->where('sub_category_code', $this->selected_sub_category_code3)
            ->value('account_number');
        session()->put('account_number2',$account_number);
        //$this->emit('changeStartDate');
        $this->showPostingModel = true;

    }

    public function closePostingModel(){
        $this->showPostingModel = false;
    }



    public function render()
    {

        $this->income_accounts = DB::table('income_accounts')->get();
        $this->expense_accounts = DB::table('expense_accounts')->get();
        return view('livewire.accounting.previous-inc-and-exp');
    }
}
