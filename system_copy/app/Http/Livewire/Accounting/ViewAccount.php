<?php

namespace App\Http\Livewire\Accounting;

use App\Models\general_ledger;
use App\Models\AccountsModel;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ViewAccount extends Component
{


    function accountInformation(){
        $id= session('accountId');
      $accounts=  AccountsModel::where('id',$id)->get();

      foreach($accounts as $account) {
        // Initialize default values to "N/A"
        $major_name = "N/A";
        $category_name = "N/A";
        // $sub_category_name = "N/A";

        // Fetch major category name
        $major_name = DB::table('accounts')->where('account_number', $account->account_number)->value('account_name') ?? "N/A";
        $account['major_category_name'] = $major_name;

        // Fetch category name if major_name is found
        // $category_name = $major_name !== "N/A" ? DB::table($major_name)->where('category_code', $account->category_code)->value('category_name') ?? "N/A" : "N/A";
        $category_name = DB::table('accounts')->where('account_number', $account->account_number)->value('type') ?? "N/A";
        $account['category_name'] = $category_name;

        // Fetch sub-category name if category_name is found
        // $sub_category_name = $category_name !== "N/A" ? DB::table($category_name)->where('sub_category_code', $account->sub_category_code)->value('sub_category_name') ?? "N/A" : "N/A";
        // $account['sub_category_name'] = $sub_category_name;
    }


      return $accounts;

    }
    public function render()
    {

        return view('livewire.accounting.view-account',
        ['accounts'=>$this->accountInformation(),
        'transactions'=>$this->accountTransactions(),



    ]);
    }


    function reverseTransaction($reference_number){

        $transaction= new TransactionPostingService();

        $outpu= $transaction->reverseTransaction($reference_number);

        session()->flash('message',$outpu? :'Your action was successful!');
    }

    function getAccountNumber(){
        $id=session('accountId');
        $account= AccountsModel::find($id);
        return $account->account_number;
    }

    public function accountTransactions(){

        return general_ledger::where('record_on_account_number',$this->getAccountNumber())->get();

    }
}
