<?php
//
//namespace App\Http\Livewire\TellerManagement;
//
//use App\Models\AccountsModel;
//use App\Models\approvals;
//use App\Models\general_ledger;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\DB;
//use Livewire\Component;
//
//class StrongRoom extends Component
//{
//    public $item;
//    public $source_teller_account;
//    public $source_teller_amount;
//
//
//
//
//    public $destination_teller_account;
//    public $amount2;
//    public $notes;
//    public $teller_account_number;
//
//
//    public $mirror_account_number;
//    public $strong_room_note;
//    public $reference_number;
//    public $destination_account_number;
//    public $mirror_account_amount;
//
//
//    protected $rules=[
//        'destination_teller_account'=>'required',
//        'notes'=>'required',
//        'source_teller_amount'=>'required',
//        'source_teller_account'=>'required'
//    ];
//
//
//
//
//    public function process(){
//
//        $this->validate();
//        if($this->source_teller_account==$this->destination_teller_account){
//            session()->flash('message_fail',"sorry you provided wrong destination");
//        }else{
//
//            if($this->source_teller_amount <  AccountsModel::where('account_number',$this->source_teller_account)->value('balance')){
//
//                // do transaction otherwise block the transaction
//
//                // source teller
//                $new_teller_balance=(double) AccountsModel::where('account_number',$this->source_teller_account)->value('balance') -(double)$this->source_teller_amount;
//                // update source teller account
//                AccountsModel::where('account_number',$this->source_teller_account)->update(['balance'=>$new_teller_balance]);
//
//
//                // for destination teller account
//                $teller_new_account_balance=(double) AccountsModel::where('account_number',$this->destination_teller_account)->value('balance') + (double)$this->source_teller_amount;
//
//                // update destination teller account
//                AccountsModel::where('account_number',$this->destination_teller_account)->update(['balance'=>$teller_new_account_balance]);
//
//
//                // debit at the first teller account
//                $reference=time();
//                $general_ledger_records=new general_ledger();
//
//
//                $general_ledger_records->debit(auth()->user()->institution_id, $this->source_teller_account,$this->source_teller_amount,'0000','0000',$this->destination_teller_account,'successfully',' successfully','0000',$this->source_teller_account,$new_teller_balance,'0000','0000',$reference);
//
//
//                // credit to the account
//
//                $general_ledger_records->credit(auth()->user()->institution_id,$this->destination_teller_account,$this->source_teller_amount,$teller_new_account_balance,$this->destination_account_number,'000','000','000','internal transfer','000',$reference,'successfully','successfully','00000');
//                session()->flash('message',"process is successfully");
//
//            }
//            else{
//                session()->flash('message_fail','sorry amount is not valid');
//            }
//        }
//
//    }
//
//
//
//    public function strongRoomToTeller(){
//
//        // get source account number
//        $this->validate(['mirror_account_number'=>'required', 'strong_room_note'=>'required','reference_number'=>'required','destination_account_number'=>'required|string']);
//
//        $array_data=['source_account_number'=>$this->mirror_account_number,
//            'reference_number'=>$this->reference_number,
//            'amount'=>$this->mirror_account_amount,
//            'destination_account_number'=>$this->destination_account_number,
//            'notes'=>$this->strong_room_note,
//        ];
//
//
//        $process_id=AccountsModel::where('account_number',$this->mirror_account_number)->value('id');
//        $approvals=new approvals();
//        $approvals->sendApproval($process_id,'strongRoomDeposition',auth()->user()->name.'has deposit funds from bank','funds deposition','102',json_encode($array_data));
//
//        $this->resetStrongRoomDeposition();
//        session()->flash('message2',"Awaiting approval");
//
//
//
//
//    }
//
//    public function resetStrongRoomDeposition(){
//        $this->mirror_account_number=null;
//        $this->strong_room_note=null;
//        $this->destination_account_number=null;
//        $this->reference_number=null;
//        $this->mirror_account_amount=null;
//
//    }
//
//
//    public function render()
//    {
//        return view('livewire.teller-management.strong-room');
//    }
//}


namespace App\Http\Livewire\TellerManagement;

use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\general_ledger;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Services\TransactionPostingService;

class StrongRoom extends Component
{
    public $item;
    public $source_teller_account;
    public $source_teller_amount;


    public $destination_teller_account;
    public $amount2;
    public $notes;
    public $teller_account_number;


    public $mirror_account_number;
    public $strong_room_note;
    public $reference_number;
    public $destination_account_number;
    public $mirror_account_amount;
    public $tellerAccount;


    protected $rules = [
        'destination_teller_account' => 'required',
        'notes' => 'required',
        'source_teller_amount' => 'required',
        'source_teller_account' => 'required'
    ];


    public function process()
    {
        $this->validate();
        if ($this->source_teller_account == $this->destination_teller_account) {
            session()->flash('message_fail', "sorry you provided wrong destination");
        } else {
            if ($this->source_teller_amount < AccountsModel::where('account_number', $this->source_teller_account)->value('balance')) {
                // do transaction otherwise block the transaction
                $transactionService = new TransactionPostingService();
                $transactionService->postTransaction([
                    'first_account' => $this->source_teller_account,
                    'second_account' => $this->destination_teller_account,
                    'amount' => $this->source_teller_amount,
                    'narration' => $this->notes,
                ]);
                $reference = time();
                $general_ledger_records = new general_ledger();
                $general_ledger_records->debit(auth()->user()->institution_id, $this->source_teller_account, $this->source_teller_amount, '0000', '0000', $this->destination_teller_account, 'successfully', ' successfully', '0000', $this->source_teller_account, 0, '0000', '0000', $reference);
                $general_ledger_records->credit(auth()->user()->institution_id, $this->destination_teller_account, $this->source_teller_amount, 0, $this->destination_account_number, '000', '000', '000', 'internal transfer', '000', $reference, 'successfully', 'successfully', '00000');
                session()->flash('message', "process is successfully");
                $this->resetStrongRoomDeposition();
            } else {
                session()->flash('message_fail', 'sorry amount is not valid');
            }
        }
    }


    public function strongRoomToTeller()
    {

        // get source account number
        $this->validate(['mirror_account_number' => 'required', 'strong_room_note' => 'required', 'reference_number' => 'required', 'destination_account_number' => 'required|string']);
        $array_data = ['source_account_number' => $this->mirror_account_number,
            'reference_number' => $this->reference_number,
            'amount' => $this->mirror_account_amount,
            'destination_account_number' => $this->destination_account_number,
            'notes' => $this->strong_room_note,
        ];
        $process_id = AccountsModel::where('account_number', $this->mirror_account_number)->value('id');
        $approvals = new approvals();
        $approvals->sendApproval($process_id, 'strongRoomDeposition', auth()->user()->name . 'has deposit funds from bank', 'funds deposition', '102', json_encode($array_data));
        $this->resetStrongRoomDeposition();
        session()->flash('message2', "Awaiting approval");
    }

    public function resetStrongRoomDeposition()
    {
        $this->mirror_account_number = null;
        $this->strong_room_note = null;
        $this->destination_account_number = null;
        $this->reference_number = null;
        $this->mirror_account_amount = null;
        $this->destination_teller_account = null;
        $this->source_teller_account = null;
        $this->source_teller_amount = null;
        $this->notes = null;


    }


    public function render()
    {

        // get account id
        $accountId = \App\Models\Teller::where('employee_id', '!=', null)->where('employee_id', '!=', 0)->pluck('account_id');
        // teller account details
        $accountData = DB::table('accounts')->whereIn('id', $accountId)->orWhere('sub_category_code', 1025)->get();
        $this->tellerAccount = $accountData;


        return view('livewire.teller-management.strong-room');
    }
}
