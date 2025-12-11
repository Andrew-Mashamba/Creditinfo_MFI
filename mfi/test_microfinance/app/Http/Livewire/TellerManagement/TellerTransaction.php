<?php
//
//namespace App\Http\Livewire\TellerManagement;
//
//use App\Models\AccountsModel;
//use App\Models\approvals;
//use App\Models\BranchesModel;
//use App\Models\Employee;
//use App\Models\general_ledger;
//use App\Models\ClientsModel;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Session;
//use Livewire\Component;
//
//class TellerTransaction extends Component
//{
//    public $makeTransaction=false;
//    public $member;
//    public $accountSelected;
//    public $member1;
//    public $accountSelected1;
//    public $item=7;
//
//
//    // send part system
//    public $account_number;
//    public  $amount;
//    public $notes;
//
//
//    // to strong room
//    public $amount_to_strong_room;
//    public $strong_roomAccount;
//    public $note_to_strong_room;
//
//
//    public function makePaymentForm(){
//        if($this->makeTransaction==false){
//            $this->makeTransaction=true;
//        }else if($this->makeTransaction==true){
//            $this->makeTransaction=false;
//        }
//    }
//
//    public function boot(): void
//    {
//        $this->branches = BranchesModel::get();
//
//    }
//
//
//
//
//    public function render()
//    {
//        return view('livewire.teller-management.teller-transaction');
//    }
//
//
//
//    public function process(){
//        // valiate input
//        $this->validate(['account_number'=>'required','amount'=>'required|numeric|gt:0','notes'=>'required']);
//
//
//        // check if available amount is greater than transfer amount
//        $available_amount=AccountsModel::where('id',DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id'))->where('institution_number',auth()->user()->institution_id)->value('balance');
//
//        // source account number
//        $source_account_number=AccountsModel::where('id',DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id'))->where('institution_number',auth()->user()->institution_id)->value('account_number');;
//        if($available_amount> $this->amount){
//            // transfer amount from teller account to another
//            $institution_id=1;
//            $id = auth()->user()->id;
//            $currentUser = DB::table('team_user')->where('user_id', $id)->get();
//            foreach ($currentUser as $User){
//                $institution_id=$User->team_id;
//            }
//            //for source teller new balance
//            $prev_account_balance = (double)($available_amount - $this->amount);
//            // destination teller total balance calculation
//            // get destination acount number
//            $destination_teller_account=$this->account_number;
//            //  balance for next teller
//            $second_teller_account_balance=AccountsModel::where('institution_number',auth()->user()->institution_id)->where('account_number',$destination_teller_account)->value('balance');
//            // second teller new balance
//            $second_teller_new_balance=(double)$second_teller_account_balance+$this->amount;
//
//
//            // update previous teller account
//            AccountsModel::where('account_number',$source_account_number)->where('institution_number',auth()->user()->institution_id)->update(['balance'=>$prev_account_balance]);
//
//            // second teller new balance records
//            AccountsModel::where('account_number',$destination_teller_account)->update(['balance'=>$second_teller_new_balance]);
//
//            $loan_ledger_account_new_balance = (double)AccountsModel::where('account_number','5111108827')->value('balance') + $this->amount;
//            AccountsModel::where('account_number','5111108827')->update(['balance'=>$loan_ledger_account_new_balance]);
//
//            $reference_number = time();
//
//
//            //DEBIT RECORD MEMBER
//            general_ledger::create([
//                'record_on_account_number'=> $destination_teller_account,
//                'record_on_account_number_balance'=> $second_teller_new_balance,
//                'sender_branch_id'=> $institution_id,
//                'beneficiary_branch_id'=> $institution_id,
//                'sender_product_id'=>  AccountsModel::where('account_number',$source_account_number)->value('product_number'),
//                'sender_sub_product_id'=> AccountsModel::where('account_number',$source_account_number)->value('sub_product_number'),
//                'beneficiary_product_id'=> AccountsModel::where('account_number',$destination_teller_account)->value('product_number'),
//                'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$destination_teller_account)->value('sub_product_number'),
//                'sender_id'=> auth()->user()->employeeId,
//                'beneficiary_id'=>AccountsModel::where('account_number',$destination_teller_account)->value('employeeId'),
//                'sender_name'=> Employee::where('id',auth()->user()->employeeId)->value('first_name').' '.Employee::where('id',auth()->user()->employeeId)->value('middle_name').' '.Employee::where('id',auth()->user()->employeeId)->value('last_name'),
//
//                'beneficiary_name'=> AccountsModel::where('account_number',$destination_teller_account)->value('account_name'),
//                'sender_account_number'=> $source_account_number,
//                'beneficiary_account_number'=> $destination_teller_account,
//                'transaction_type'=> 'IFT',
//                'sender_account_currency_type'=> 'TZS',
//                'beneficiary_account_currency_type'=> 'TZS',
//                'narration'=> $this->notes,
//                'credit'=> 0,
//                'debit'=>$this->amount ,
//                'reference_number'=> $reference_number,
//                'trans_status'=> 'Successful',
//                'trans_status_description'=> 'Successful',
//                'swift_code'=> '',
//                'destination_bank_name'=> '',
//                'destination_bank_number'=> null,
//                'payment_status'=> 'Successful',
//                'recon_status'=> 'Pending',
//            ]);
//
//            //CREDIT RECORD LOAN ACCOUNT
//            general_ledger::create([
//                'record_on_account_number'=> $source_account_number,
//                'record_on_account_number_balance'=> $prev_account_balance,
//                'sender_branch_id'=> $institution_id,
//                'beneficiary_branch_id'=> $institution_id,
//
//                'sender_product_id'=>  AccountsModel::where('account_number',$source_account_number)->value('product_number'),
//                'sender_sub_product_id'=> AccountsModel::where('account_number',$source_account_number)->value('sub_product_number'),
//
//                'beneficiary_product_id'=> AccountsModel::where('account_number',$destination_teller_account)->value('product_number'),
//                'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$destination_teller_account)->value('sub_product_number'),
//                'sender_id'=>auth()->user()->employeeId,
//                'beneficiary_id'=>AccountsModel::where('account_number',$destination_teller_account)->value('employeeId'),
//                'sender_name'=> Employee::where('id',auth()->user()->employeeId)->value('first_name').' '.Employee::where('id',auth()->user()->employeeId)->value('middle_name').' '.Employee::where('id',auth()->user()->employeeId)->value('last_name'),
//                'beneficiary_name'=>AccountsModel::where('account_number',$destination_teller_account)->value('account_name'),
//                'sender_account_number'=> $destination_teller_account,
//                'beneficiary_account_number'=> $source_account_number,
//                'transaction_type'=> 'IFT',
//                'sender_account_currency_type'=> 'TZS',
//                'beneficiary_account_currency_type'=> 'TZS',
//                'narration'=>$this->notes,
//                'credit'=> $this->amount,
//                'debit'=> 0,
//                'reference_number'=> $reference_number,
//                'trans_status'=> 'Successful',
//                'trans_status_description'=> 'Successful',
//                'swift_code'=> '',
//                'destination_bank_name'=> '',
//                'destination_bank_number'=> null,
//                'payment_status'=> 'Successful',
//                'recon_status'=> 'Pending',
//            ]);
//
//            //CREDIT RECORD GL
//            general_ledger::create([
//                'record_on_account_number'=> '5111108827',
//                'record_on_account_number_balance'=> $this->amount ,
//                'sender_branch_id'=> $institution_id,
//                'beneficiary_branch_id'=> $institution_id,
//                'sender_product_id'=>  AccountsModel::where('account_number',$source_account_number)->value('product_number'),
//                'sender_sub_product_id'=>AccountsModel::where('account_number',$source_account_number)->value('sub_product_number'),
//                'beneficiary_product_id'=> AccountsModel::where('account_number',$destination_teller_account)->value('product_number'),
//                'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$destination_teller_account)->value('sub_product_number'),
//                'sender_id'=> auth()->user()->employeeId,
//                'beneficiary_id'=> '999999',
//                'sender_name'=> Employee::where('id',auth()->user()->employeeId)->value('first_name').' '.Employee::where('id',auth()->user()->employeeId)->value('middle_name').' '.Employee::where('id',auth()->user()->employeeId)->value('last_name'),
//                'beneficiary_name'=> 'Organization',
//                'sender_account_number'=> $source_account_number,
//                'beneficiary_account_number'=> '5111108827',
//                'transaction_type'=> 'IFT',
//                'sender_account_currency_type'=> 'TZS',
//                'beneficiary_account_currency_type'=> 'TZS',
//                'narration'=> $this->notes,
//                'credit'=> $this->amount,
//                'debit'=> 0,
//                'reference_number'=> $reference_number,
//                'trans_status'=> 'Successful',
//                'trans_status_description'=> 'Successful',
//                'swift_code'=> '',
//                'destination_bank_name'=> '',
//                'destination_bank_number'=> null,
//                'payment_status'=> 'Successful',
//                'recon_status'=> 'Pending',
//            ]);
//
//
//
//            // aprovals
//            $approvals=new approvals();
//            $approvals->sendApproval($reference_number,'New fund transaction','initiate new fund transfer','teller to teller transaction','07','');
//            Session::flash('message', 'Transaction has been successfully issued!');
//            $this->resetAccountDetails();
//
//
//        }
//        else {
//            session()->flash('message_fail','Invalid amount');
//        }
//
//    }
//
//
//    public function resetAccountDetails(){
//        $this->amount=null;
//        $this->notes=null;
//        $this->account_number=null;
//
//    }
//
//    public function strongRoomTransaction(){
//
//        $this->validate(['amount_to_strong_room'=>'required|numeric|gt:0',
//            'strong_roomAccount'=>'required',
//            'note_to_strong_room'=>'required',
//        ]);
//        // teller account number
//        $teller_account_number= AccountsModel::where('id',DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id'))->where('institution_number',auth()->user()->institution_id)->value('account_number');
////        $teller_account_number=AccountsModel::where('sub_product_number', '106')->where('employeeId',auth()->user()->employeeId)->value('account_number');
//        // teller available balance;
//        $teller_prev_account_balance=AccountsModel::where('id',DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id'))->where('institution_number',auth()->user()->institution_id)->value('balance');
//        // get strong room amount
//        $strong_room_account_balance=AccountsModel::where('account_number',$this->strong_roomAccount)->value('balance');
//
//        if($teller_prev_account_balance>=$this->amount_to_strong_room){
//            $institution_id=1;
//            $id = auth()->user()->id;
//            $currentUser = DB::table('team_user')->where('user_id', $id)->get();
//            foreach ($currentUser as $User){
//                $institution_id=$User->team_id;
//            }
//            // new teller balance
//            $new_teller_balance=$teller_prev_account_balance-$this->amount_to_strong_room;
//
//            //strong  room new balance
//            $strong_room_new_balance=(double)$strong_room_account_balance + $this->amount_to_strong_room;
//
//            // update previous teller account
//            AccountsModel::where('account_number',$teller_account_number)->where('sub_product_number','106')->update(['balance'=>$new_teller_balance]);
//
//            // strong room  new balance records
//            AccountsModel::where('account_number',$this->strong_roomAccount)->update(['balance'=>$strong_room_new_balance]);
//            $loan_ledger_account_new_balance = (double)AccountsModel::where('account_number','5111108827')->value('balance') + $this->amount_to_strong_room;
//            AccountsModel::where('account_number','5111108827')->update(['balance'=>$loan_ledger_account_new_balance]);
//
//            $reference_number=time();
//
//
//            //DEBIT RECORD strong room
//            general_ledger::create([
//                'record_on_account_number'=> $this->strong_roomAccount,
//                'record_on_account_number_balance'=> $strong_room_new_balance,
//                'sender_branch_id'=> $institution_id,
//                'beneficiary_branch_id'=> $institution_id,
//                'sender_product_id'=>  AccountsModel::where('account_number',$teller_account_number)->value('product_number'),
//                'sender_sub_product_id'=> AccountsModel::where('account_number',$teller_account_number)->value('sub_product_number'),
//                'beneficiary_product_id'=> AccountsModel::where('account_number',$this->strong_roomAccount)->value('product_number'),
//                'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->strong_roomAccount)->value('sub_product_number'),
//                'sender_id'=> auth()->user()->employeeId,
//                'beneficiary_id'=>AccountsModel::where('account_number',$this->strong_roomAccount)->value('employeeId'),
//                'sender_name'=> Employee::where('id',auth()->user()->employeeId)->value('first_name').' '.Employee::where('id',auth()->user()->employeeId)->value('middle_name').' '.Employee::where('id',auth()->user()->employeeId)->value('last_name'),
//                'beneficiary_name'=> AccountsModel::where('account_number',$this->strong_roomAccount)->value('account_name'),
//                'sender_account_number'=> $teller_account_number,
//                'beneficiary_account_number'=> $this->strong_roomAccount,
//                'transaction_type'=> 'IFT',
//                'sender_account_currency_type'=> 'TZS',
//                'beneficiary_account_currency_type'=> 'TZS',
//                'narration'=> $this->notes,
//                'credit'=> 0,
//                'debit'=>$this->amount ,
//                'reference_number'=> $reference_number,
//                'trans_status'=> 'Successful',
//                'trans_status_description'=> 'Successful',
//                'swift_code'=> '',
//                'destination_bank_name'=> '',
//                'destination_bank_number'=> null,
//                'payment_status'=> 'Successful',
//                'recon_status'=> 'Pending',
//            ]);
//
//            //CREDIT RECORD TELLER ACCOUNT
//            general_ledger::create([
//                'record_on_account_number'=> $teller_account_number,
//                'record_on_account_number_balance'=> $new_teller_balance,
//                'sender_branch_id'=> $institution_id,
//                'beneficiary_branch_id'=> $institution_id,
//
//                'sender_product_id'=>  AccountsModel::where('account_number',$teller_account_number)->value('product_number'),
//                'sender_sub_product_id'=> AccountsModel::where('account_number',$teller_account_number)->value('sub_product_number'),
//
//                'beneficiary_product_id'=> AccountsModel::where('account_number',$this->strong_roomAccount)->value('product_number'),
//                'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->strong_roomAccount)->value('sub_product_number'),
//                'sender_id'=>auth()->user()->employeeId,
//
//                'beneficiary_id'=>AccountsModel::where('account_number',$this->strong_roomAccount)->value('employeeId'),
//                'sender_name'=> Employee::where('id',auth()->user()->employeeId)->value('first_name').' '.Employee::where('id',auth()->user()->employeeId)->value('middle_name').' '.Employee::where('id',auth()->user()->employeeId)->value('last_name'),
//                'beneficiary_name'=>AccountsModel::where('account_number',$this->strong_roomAccount)->value('account_name'),
//                'sender_account_number'=> $teller_account_number,
//                'beneficiary_account_number'=> $this->strong_roomAccount,
//                'transaction_type'=> 'IFT',
//                'sender_account_currency_type'=> 'TZS',
//                'beneficiary_account_currency_type'=> 'TZS',
//                'narration'=>$this->notes,
//                'credit'=> $this->amount,
//                'debit'=> 0,
//                'reference_number'=> $reference_number,
//                'trans_status'=> 'Successful',
//                'trans_status_description'=> 'Successful',
//                'swift_code'=> '',
//                'destination_bank_name'=> '',
//                'destination_bank_number'=> null,
//                'payment_status'=> 'Successful',
//                'recon_status'=> 'Pending',
//            ]);
//
//            //CREDIT RECORD GL
//            general_ledger::create([
//                'record_on_account_number'=> '5111108827',
//                'record_on_account_number_balance'=> $this->amount_to_strong_room ,
//                'sender_branch_id'=> $institution_id,
//                'beneficiary_branch_id'=> $institution_id,
//                'sender_product_id'=>  AccountsModel::where('account_number',$teller_account_number)->value('product_number'),
//                'sender_sub_product_id'=>AccountsModel::where('account_number',$teller_account_number)->value('sub_product_number'),
//                'beneficiary_product_id'=> AccountsModel::where('account_number',$this->strong_roomAccount)->value('product_number'),
//                'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->strong_roomAccount)->value('sub_product_number'),
//                'sender_id'=> auth()->user()->employeeId,
//                'beneficiary_id'=> '999999',
//                'sender_name'=> Employee::where('id',auth()->user()->employeeId)->value('first_name').' '.Employee::where('id',auth()->user()->employeeId)->value('middle_name').' '.Employee::where('id',auth()->user()->employeeId)->value('last_name'),
//                'beneficiary_name'=> 'Organization',
//                'sender_account_number'=> $teller_account_number,
//                'beneficiary_account_number'=> '5111108827',
//                'transaction_type'=> 'IFT',
//                'sender_account_currency_type'=> 'TZS',
//                'beneficiary_account_currency_type'=> 'TZS',
//                'narration'=> $this->notes,
//                'credit'=> $this->amount,
//                'debit'=> 0,
//                'reference_number'=> $reference_number,
//                'trans_status'=> 'Successful',
//                'trans_status_description'=> 'Successful',
//                'swift_code'=> '',
//                'destination_bank_name'=> '',
//                'destination_bank_number'=> null,
//                'payment_status'=> 'Successful',
//                'recon_status'=> 'Pending',
//            ]);
//
//            // aprovals
//            $approvals=new approvals();
//            $approvals->sendApproval($reference_number,'New fund transaction','initiate new fund transfer','teller to teller transaction','07','');
//            Session::flash('message2', 'Transaction has been successfully issued!');
//            $this->resetTellerToStrongRoomData();
//
//        }
//        else {
//            session()->flash('message_fail1','Invalid amount');
//
//        }
//
//    }
//    public function resetTellerToStrongRoomData(){
//        $this->amount_to_strong_room=null;
//        $this->strong_roomAccount=null;
//        $this->note_to_strong_room=null;
//
//    }
//}


namespace App\Http\Livewire\TellerManagement;

use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\general_ledger;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Services\TransactionPostingService;

class TellerTransaction extends Component
{
    public $makeTransaction = false;
    public $member;
    public $accountSelected;
    public $member1;
    public $accountSelected1;
    public $item = 7;


    // send part system
    public $account_number;
    public $amount;
    public $notes;
    public $tellerMyAccountNumber;
    public $tellerMyAccountBalance;


    // to strong room
    public $amount_to_strong_room;
    public $strong_roomAccount;
    public $note_to_strong_room;
    public $tellerList;


    public function makePaymentForm()
    {
        if ($this->makeTransaction == false) {
            $this->makeTransaction = true;
        } else if ($this->makeTransaction == true) {
            $this->makeTransaction = false;
        }
    }

    public function boot(): void
    {

        $this->branches = BranchesModel::get();

    }

    public function visit($id){

    }


    public function render()
    {
        // all tellers
        // get account id from teller table
        $account_id = \App\Models\Teller::where('employee_id', '!=', null)->where('employee_id', '!=', 0)->pluck('account_id');
        // get account array
        $getValiable = DB::table('accounts')->where('sub_category_code', 2240)->whereIn('id', $account_id)->get();
        // declare to public variable
        $this->tellerList = $getValiable;

        // get account id
        $accountId = \App\Models\Teller::where('employee_id', auth()->user()->employeeId)->first() ;
        if($accountId){

             // teller account details
        $accountData = DB::table('accounts')->where('id', $accountId->account_id)->first();
        // declare to public variables
        $this->tellerMyAccountNumber = $accountData->account_number;
        $this->tellerMyAccountBalance = $accountData->balance;
        }else{

            $this->tellerMyAccountNumber='';
              $this->tellerMyAccountBalance='';

        }





        return view('livewire.teller-management.teller-transaction');
    }


    public function process()
    {
        // valiate input
        $this->validate(['account_number' => 'required', 'amount' => 'required|numeric|gt:0', 'notes' => 'required']);


        // check if available amount is greater than transfer amount
        $available_amount = AccountsModel::where('id', DB::table('tellers')->where('employee_id', auth()->user()->employeeId)->value('account_id'))->value('balance');

        // source account number
        $source_account_number = AccountsModel::where('id', DB::table('tellers')->where('employee_id', auth()->user()->employeeId)->value('account_id'))->where('institution_number', auth()->user()->institution_id)->value('account_number');;
        if ($this->account_number != $this->tellerMyAccountNumber) {


            if ($available_amount > $this->amount) {
                // incase for institutions
                $institution_id = auth()->user()->institution_id;
                // transfer amount from teller account to another

                //for source teller new balance
                $prev_account_balance = (double)($available_amount - $this->amount);
                // destination teller total balance calculation
                // get destination acount number
                $destination_teller_account = $this->account_number;
                //  balance for next teller
                $second_teller_account_balance = AccountsModel::where('institution_number', auth()->user()->institution_id)->where('account_number', $destination_teller_account)->value('balance');
                // second teller new balance
                $second_teller_new_balance = (double)$second_teller_account_balance + $this->amount;


                // update previous teller account
                AccountsModel::where('account_number', $source_account_number)->where('institution_number', auth()->user()->institution_id)->update(['balance' => $prev_account_balance]);


                // second teller new balance records
                AccountsModel::where('account_number', $destination_teller_account)->update(['balance' => $second_teller_new_balance]);

                $loan_ledger_account_new_balance = (double)AccountsModel::where('account_number', '5111108827')->value('balance') + $this->amount;
                AccountsModel::where('account_number', '5111108827')->update(['balance' => $loan_ledger_account_new_balance]);

                $reference_number = time();


                //DEBIT RECORD MEMBER
                $transactionService = new TransactionPostingService();
                $transactionService->postTransaction([
                    'first_account' => $source_account_number,
                    'second_account' => $destination_teller_account,
                    'amount' => $this->amount,
                    'narration' => $this->notes,
                ]);
                $transactionService->postTransaction([
                    'first_account' => $destination_teller_account,
                    'second_account' => '5111108827',
                    'amount' => $this->amount,
                    'narration' => $this->notes,
                ]);

                //CREDIT RECORD LOAN ACCOUNT
                $transactionService = new TransactionPostingService();
                $transactionService->postTransaction([
                    'first_account' => $source_account_number,
                    'second_account' => $destination_teller_account,
                    'amount' => $this->amount,
                    'narration' => $this->notes,
                ]);

                //CREDIT RECORD GL
                $transactionService = new TransactionPostingService();
                $transactionService->postTransaction([
                    'first_account' => $source_account_number,
                    'second_account' => '5111108827',
                    'amount' => $this->amount,
                    'narration' => $this->notes,
                ]);


                // aprovals
                $approvals = new approvals();
                $approvals->sendApproval($reference_number, 'New fund transaction', 'initiate new fund transfer', 'teller to teller transaction', '07', '');
                Session::flash('message', 'Transaction has been successfully issued!');
                $this->resetAccountDetails();


            } else {
                session()->flash('message_fail', 'Invalid amount');
            }
        } else {
            session()->flash('message_fail', "Invalid destination account number");
        }

    }


    public function resetAccountDetails()
    {
        $this->amount = null;
        $this->notes = null;
        $this->account_number = null;

    }

    public function strongRoomTransaction()
    {

        $this->validate(['amount_to_strong_room' => 'required|numeric|gt:0',
            'strong_roomAccount' => 'required',
            'note_to_strong_room' => 'required',
        ]);
        // teller account number
        $teller_account_number = AccountsModel::where('id', DB::table('tellers')->where('employee_id', auth()->user()->employeeId)->value('account_id'))->where('institution_number', auth()->user()->institution_id)->value('account_number');
//        $teller_account_number=AccountsModel::where('sub_product_number', '106')->where('employeeId',auth()->user()->employeeId)->value('account_number');
        // teller available balance;
        $teller_prev_account_balance = AccountsModel::where('id', DB::table('tellers')->where('employee_id', auth()->user()->employeeId)->value('account_id'))->where('institution_number', auth()->user()->institution_id)->value('balance');
        // get strong room amount
        $strong_room_account_balance = AccountsModel::where('account_number', $this->strong_roomAccount)->value('balance');

        if ($teller_prev_account_balance >= $this->amount_to_strong_room) {
            $institution_id = auth()->user()->institution_id;

            // new teller balance
            $new_teller_balance = $teller_prev_account_balance - $this->amount_to_strong_room;

            //strong  room new balance
            $strong_room_new_balance = (double)$strong_room_account_balance + $this->amount_to_strong_room;

            // update previous teller account
            AccountsModel::where('account_number', $teller_account_number)->update(['balance' => $new_teller_balance]);


            // strong room  new balance records
            AccountsModel::where('account_number', $this->strong_roomAccount)->update(['balance' => $strong_room_new_balance]);

            $reference_number = time();


            //DEBIT RECORD strong room
            $transactionService = new TransactionPostingService();
            $transactionService->postTransaction([
                'first_account' => $this->strong_roomAccount,
                'second_account' => $teller_account_number,
                'amount' => $this->amount_to_strong_room,
                'narration' => $this->note_to_strong_room,
            ]);

            //CREDIT RECORD TELLER ACCOUNT
            $transactionService = new TransactionPostingService();
            $transactionService->postTransaction([
                'first_account' => $teller_account_number,
                'second_account' => $this->strong_roomAccount,
                'amount' => $this->amount_to_strong_room,
                'narration' => $this->note_to_strong_room,
            ]);


            Session::flash('message2', 'Transaction has been successfully issued!');
            $this->resetTellerToStrongRoomData();

        } else {
            session()->flash('message_fail1', 'Invalid amount');

        }

    }


    public function resetTellerToStrongRoomData()
    {
        $this->amount_to_strong_room = null;
        $this->strong_roomAccount = null;
        $this->note_to_strong_room = null;

    }


}
