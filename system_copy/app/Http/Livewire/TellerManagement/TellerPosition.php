<?php

namespace App\Http\Livewire\TellerManagement;

use App\Models\AccountsModel;
use App\Models\Employee;
use App\Models\general_ledger;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use App\Models\TellerEndOfDayPositions;
use App\Services\TransactionPostingService;

class TellerPosition extends Component
{
    public $date;
    public $selectedId;
    public $approveBoolean;
    public  $employee_name;
    public $til_number;
    public $til_balance;
    public $cash;
    public $password;
    public $status;
    public $overAmount;
    public $underAmount;
    public $account_number;
    public $member_information;
    public $account_number_destination;


    protected $listeners=['approveCloseTill'=>'approveModal','refreshTellerPosition'=>'$refresh'];


    public function approveModal($id){
        $this->selectedId=$id;
        $data=TellerEndOfDayPositions::where('id',$id)->first();
        $this->employee_name = Employee::where('id', $data->employee_id)
            ->selectRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) as name")
            ->value('name');;
        $this->til_number=$data->til_account;
        $this->til_balance=$data->til_balance;
        $this->cash=$data->tiller_cash_at_hand;
        $this->status=$data->status;
        if($this->status=="OVER"){
            $this->overAmount= (double)$this->cash - (double)$this->til_balance;
        }
        elseif($this->status=="UNDER"){
            $this->underAmount=-((double)$this->cash - (double)$this->til_balance);
        }

        $this->approveBoolean=true;
    }


    public function closeTil(){
        // authenticate user


        if(Hash::check($this->password,auth()->user()->password)){
            // close till

            // assign ammount required
            $amount_required=0;
            if($this->status=="OVER" ){
                $amount_required=$this->overAmount;
                // for the over case
                // get source teller account number
                $teller_account_number=$this->til_number;

                $tellerData=AccountsModel::where('account_number',$teller_account_number)->first();
                // debit over amount

                $teller_new_balance= (double)$tellerData->balance - (double)$amount_required;
                // update teller account
                // AccountsModel::where('account_number',$teller_account_number)->update(['balance'=>$teller_new_balance]);


                // get destination account
                $destination_account_number= $this->account_number_destination;
                // credit over amount
                $destinationAccountData=AccountsModel::where('account_number',$destination_account_number)->first();


                $destination_new_balance=(double)$destinationAccountData->balance + (double)$amount_required;

                // update destination account
                // AccountsModel::where('account_number',$destination_account_number)->update(['balance'=>$destination_new_balance]);


                $reference=time();

                $transactionService = new TransactionPostingService();
                $transactionService->postTransaction([
                    'first_account' => $teller_account_number,
                    'second_account' => $destination_account_number,
                    'amount' => $amount_required,
                    'narration' => 'til over amount',
                ]);

                // record on general ledger
                $record_on_generalLG=new general_ledger();
                $record_on_generalLG->debit(auth()->user()->institution_id,$teller_account_number,$amount_required,'000','00',
                $destination_account_number,'successfully','til over amount','000',$teller_account_number,$teller_new_balance,'00',
                    '00',$reference
                );
                $record_on_generalLG->credit(auth()->user()->institution_id,$destination_account_number,$amount_required,$destination_new_balance,$teller_account_number,'00','00',
                    '00','transfer of over amount','000',$reference,'successfully','successfully','00');

                // updte teller end of day table
                TellerEndOfDayPositions::where('id',$this->selectedId)->update([
                    'status'=>'BALANCED',
                    'tiller_cash_at_hand'=> (double)$this->cash - (double)$amount_required,
                ]);
                session()->flash('message','successfully');
                $this->emit('refreshTellerPosition');

            }
            elseif($this->status=="UNDER"){
                $this->validate(['account_number'=>'required']);
                $amount_required=$this->underAmount;

                $member_account=AccountsModel::where('account_number',$this->account_number)->first();


                if($member_account->balance >= $amount_required){
                    // debit member account
                    $member_new_balance = (double)$member_account->balance - (double)$amount_required;

                    // update member balance
                    // AccountsModel::where('account_number',$member_account->account_number)->update(['balance'=>$member_new_balance]);


                    // credit strong room accounts
                    $strongRoom=AccountsModel::where('sub_category_code',1025)->first();
                    $strong_room_new_balance=(double)$strongRoom->balance + (double)$amount_required;
                    // update strong room account
                    // AccountsModel::where('sub_category_code',1025)->update(['balance'=>$strong_room_new_balance]);

                    $reference=time();
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $member_account->account_number,
                        'second_account' => $strongRoom->account_number,
                        'amount' => $amount_required,
                        'narration' => 'force close til',
                    ]);
                    // record on debit
                    $record_on_general_ledger=new general_ledger();
                    $record_on_general_ledger->debit(auth()->user()->institution_id,$member_account->account_number,$amount_required,'force close til',
                        '000',$strongRoom->account_number,'successfully','close til','Strong Room',
                        $member_account->account_number,$member_new_balance,'0000','0000',$reference
                    );
                    // record on credit
                    $record_on_general_ledger->credit(auth()->user()->institution_id,$strongRoom->account_number,$amount_required,$strong_room_new_balance,$member_account->account_number,
                        '0000','0000','strongroom','force close til','0000',$reference,'successfully','close til'
                        ,'000'
                    );

                    // updte teller end of day table
                    TellerEndOfDayPositions::where('id',$this->selectedId)->update([
                        'status'=>'BALANCED',
                        'tiller_cash_at_hand'=>$this->cash,
                    ]);
                    session()->flash('message','successfully');
                    $this->emit('refreshTellerPosition');

                }

                elseif($member_account->balance>0){


                    $member_new_balance = 0;

                    // update member balance
                    // AccountsModel::where('account_number',$member_account->account_number)->update(['balance'=>$member_new_balance]);


                    // credit strong room accounts
                    $strongRoom=AccountsModel::where('sub_category_code',1025)->first();
                    $strong_room_new_balance=(double)$strongRoom->balance + (double)$member_account->balance;
                    // update strong room account
                    // AccountsModel::where('sub_category_code',1025)->update(['balance'=>$strong_room_new_balance]);

                    $reference=time();
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $member_account->account_number,
                        'second_account' => $strongRoom->account_number,
                        'amount' => $member_account->balance,
                        'narration' => 'force close til',
                    ]);
                    // record on debit
                    $record_on_general_ledger=new general_ledger();
                    $record_on_general_ledger->debit(auth()->user()->institution_id,$member_account->account_number,$member_account->balance,'force close til',
                        '000',$strongRoom->account_number,'successfully','close til','Strong Room',
                        $member_account->account_number,$member_new_balance,'0000','0000',$reference
                    );
                    // record on credit
                    $record_on_general_ledger->credit(auth()->user()->institution_id,$strongRoom->account_number,$member_account->balance,$strong_room_new_balance,$member_account->account_number,
                        '0000','0000','strongroom','force close til','0000',$reference,'successfully','close til'
                        ,'000'
                    );

                    // updte teller end of day table
                    TellerEndOfDayPositions::where('id',$this->selectedId)->update([
                        'status'=>'UNDER',
                        'tiller_cash_at_hand'=> (double)$this->cash +(double)$member_account->balance,
                    ]);
                    session()->flash('message','successfully');
                    $this->emit('refreshTellerPosition');
                }





            }


        }

    }


    public function refreshComponent(){

        $this->emitTo('teller-management.teller-position-table','updateDate',$this->date);
    }
    public function render()
    {

        $memberData=AccountsModel::where('account_number',$this->account_number)->first();
        $this->member_information=$memberData;

        return view('livewire.teller-management.teller-position');
    }
}
