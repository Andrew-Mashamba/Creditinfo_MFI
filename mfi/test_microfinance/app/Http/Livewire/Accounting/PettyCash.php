<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\institutions;
use App\Services\DisbursementService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PettyCash extends Component
{
    public $action_button=1;

    public $source_account;
    public $destination_account;
    public $source_account_id;
    public $destination_account_id;
    public $amount;
    public $total_balance;
    public $total_expenses;
    public $pettySummary;

    function calculateSummary($sart_date=null, $end_date=null){

        $this->total_balance=AccountsModel::where('sub_category_code',1020)->sum('balance');

        $accounts=AccountsModel::where('sub_category_code',1020)->pluck('account_number')->toArray();
        return general_ledger::query()->whereIn('record_on_account_number',$accounts)->sum('debit');

    }

    public function render()
    {

        $this->total_expenses=$this->calculateSummary();

        $this->pettySummary=[
         'balance'=>$this->total_balance,
          'expenses'=>$this->total_expenses,
        ];
        $this->source_account= DB::table('accounts')->where('category_code',1000)->get();
        $this->destination_account=DB::table('accounts')->where('sub_category_code',1020)->get();
        return view('livewire.accounting.petty-cash');
    }



    public function changeMenu($id){

        $this->action_button=$id;
    }

    public function depositPettyAccount(){
        //validate input  and exists
        $this->validate([
       'destination_account_id'=>'required|exists:accounts,id',
       'source_account_id'=>'required|exists:accounts,id',
       'amount'=>'required|numeric'

        ]);


        $source_account=$this->getAccount($this->source_account_id);
        $destination_accounts =$this->getAccount($this->destination_account_id);
        $narration="Internal Funds Transfer";

        if($this->action_button==2){
            if($this->managePettyCash($this->amount)){


            }else{
                 session()->flash('message_fail','Amount is above the limit');

                 return true;
            }
        }


            if($this->checkBalance( $source_account) > $this->amount){
                $transaction= new DisbursementService();
               $outPut=  $transaction->makeTransaction($source_account,$this->amount,$destination_accounts ,$narration);
               
               session()->flash('message',$outPut);

               $this->clearData();
            }else{
                session()->flash('message_fail','insufficient Balance');
            }


    }


    public function discharge(){
        //validate input  and exists
        $this->validate([
       'destination_account_id'=>'required|exists:accounts,id',
       'source_account_id'=>'required|exists:accounts,id',
       'amount'=>'required|numeric'

        ]);


        $source_account=$this->getAccount($this->source_account_id);
        $destination_accounts =$this->getAccount($this->destination_account_id);
        $narration="Internal Funds Transfer";


        if($this->action_button==2){
            if($this->managePettyCash($this->amount)){
            }else{
                 session()->flash('message_fail','Amount is above the limit');
                 return true;
            }
        }


            if($this->checkBalance( $source_account) > $this->amount){
                $transaction= new DisbursementService();
               $outPut=  $transaction->makeTransaction($source_account,$this->amount,$destination_accounts ,$narration);
               session()->flash('message',$outPut);

               $this->clearData();
            }else{
                session()->flash('message_fail','insufficient Balance');
            }
    }

    function managePettyCash($amount){
        $institution_data= institutions::find(1);
        if($amount > $institution_data->petty_amount_limit){
            return false;
        }else{
            return true;
        }

    }

    function checkBalance($account_number){
        return  AccountsModel::where('account_number',$account_number)->value('balance');
    }

    function getAccount($account_id){
        return  AccountsModel::where('id',$account_id)->value('account_number');
    }

    function clearData(){
        $this->destination_account_id=null;
        $this->destination_account_id=null;
        $this->amount=null;

    }


}
