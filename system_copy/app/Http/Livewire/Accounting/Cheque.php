<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\ChequeBookModel;
use App\Models\ChequeModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Cheque extends Component
{
    public $tab_id;
    public $selectedCheque;
    public $selected=1;
    public $account_id;
    public $chequeBookNumber;
    public $leave_number;
    public $newChqueBookModal=false;
    public $chequeModal=false;
    public $cheque_number;
    public $selected_bank;
    public $bank_present_value;
    public $cheque_book_present_value=null;

    protected $listeners=['cheques'=>'chequeIssues'];




    public function addChequeBook(){
        $this->validate([
           'leave_number'=>'required|numeric',
           'account_id'=>'required|numeric'
        ]);
// get institution id
        $institution_id=auth()->user()->institution_id;
        $branch_id=auth()->user()->branch;
// get latest cheque book number/id
      $chequeBook_id=  ChequeBookModel::where('branch_id',$branch_id)->
                          where('bank',$this->account_id)->latest()->value('chequeBook_id');

      if($chequeBook_id){
          $this->chequeBookNumber=(int)$chequeBook_id+1;
      }else{

              $account_number=AccountsModel::where('id',$this->account_id)->value('account_number');
             $chequeBook_id=$account_number.''.$branch_id.'000001';
          $this->chequeBookNumber=(int)$chequeBook_id ;
      }

       $id= ChequeBookModel::create([
           'chequeBook_id'=> $this->chequeBookNumber,
            'bank'=>$this->account_id,
            'leave_number'=>$this->leave_number,
            'remaining_leaves'=>$this->leave_number,
            'branch_id'=>$branch_id,
            'institution_id'=>$institution_id,
           'status'=>'PENDING'
        ])->id;

      session()->flash('message','Awaiting Approval');
      $approval=new approvals();
      $approval->sendApproval($id,'newChequeBook',auth()->user()->name.'has create new cheque book','new cheque book ','102','');

      $this->reseteChequeBoookData();

    }


    public function reseteChequeBoookData(){
        $this->account_id=null;
        $this->leave_number=null;
         $this->cheque_number=null;
         $this->selected_bank=null;
         $this->cheque_book_present_value=null;
    }

    public function issueNewCheque(){

        $value=ChequeBookModel::where('status','ACTIVE')->where('bank',$this->selected_bank)
            ->where('branch_id',auth()->user()->branch)->value('chequeBook_id');
        $this->cheque_book_present_value=$value?:null;

        //check if leave is present
        $numbe_of_leaves=DB::table('cheque_books')->where('status','ACTIVE')->where('bank',$this->selected_bank)->value('remaining_leaves');

        if($numbe_of_leaves>0){

           $this->validate(['cheque_number'=>'required|unique:cheques','selected_bank'=>'required','cheque_book_present_value'=>'required','selectedCheque'=>'required']);
           ChequeModel::where('id',$this->selectedCheque)->update([
               'finance_approver'=>auth()->user()->name,
               'bank_account'=>$this->selected_bank,
               'cheque_number'=>$this->cheque_number,

           ]);
           // send for the approval
        $pproval=new approvals();
        $pproval->sendApproval($this->selectedCheque,'newCheque',auth()->user()->name.'has issued new cheque','created new cheque','102','');
        session()->flash('message','Awaiting approval');

        // update leaves now
            DB::table('cheque_books')->where('status','ACTIVE')->where('bank',$this->selected_bank)->update(['remaining_leaves'=>$numbe_of_leaves-1]);

        if($numbe_of_leaves==1){
            DB::table('cheque_books')->where('status','ACTIVE')->where('bank',$this->selected_bank)->update(['status'=>"CLOSED"]);
        }
            $this->reseteChequeBoookData();
        }

    }

    public function chequeIssues($id){
        $this->selectedCheque=$id;
        $this->chequeModal=true;
    }

    public function setView($id){
        $this->selected=$id;
    }

    public function newCheque(){
        $this->newChqueBookModal=true;
    }

    public function render()
    {
         $this->bank_present_value=AccountsModel::where('id',$this->selected_bank)->value('balance')?:null;




        return view('livewire.accounting.cheque');
    }
}
