<?php
//
//namespace App\Http\Livewire\TellerManagement;
//
//use App\Models\AccountsModel;
//use App\Models\approvals;
//use App\Models\BranchesModel;
//use App\Models\Employee;
//use App\Models\general_ledger;
//use App\Models\Teller;
//use App\Models\TellerEndOfDayPositions;
//use Carbon\Carbon;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Session;
//use Livewire\Component;
//
//class TellerReport extends Component
//{
//    public $selectedDate;
//    public $disableProcess;
//
//    public $tilId;
//    public $businessDate;
//    public $strong_roomAccount;
//    public $tellers;
//    public $openingBalance;
//    public $closingBalance;
//    public $cashAtHand;
//    public $message;
//    public $state;
//    public $totalCredit;
//    public $totalDebit;
//    public TellerEndOfDayPositions  $tellerEndOfDay;
//
//    protected $rules = [
//        'tellerEndOfDay.tiller_cash_at_hand' => 'required',
//        'tellerEndOfDay.business_date' => 'required'
//    ];
//
//
//
//
//
//
//
//
//
//
//    public function updatedSelectedDate(){
//
//        $tellerEndOfDay = TellerEndOfDayPositions::where('til_number', $this->tilId)
//            ->whereDate('business_date',$this->selectedDate)->first();
//
//        if($tellerEndOfDay == null){
//            $this->tellerEndOfDay = new TellerEndOfDayPositions();
//        }else{
//            $this->tellerEndOfDay = $tellerEndOfDay;
//        }
//
//        Session::put('businessDate',$this->selectedDate);
//        $this->emit('refreshTilTable');
//
//
//        $this->processData();
//
//    }
//
//    public function updatedTilId(){
//
//        $tellerEndOfDay = TellerEndOfDayPositions::where('til_number', $this->tilId)
//            ->whereDate('business_date',$this->selectedDate)->first();
//
//        if($tellerEndOfDay == null){
//            $this->tellerEndOfDay = new TellerEndOfDayPositions();
//        }else{
//            $this->tellerEndOfDay = $tellerEndOfDay;
//        }
//
//
//        Session::put('sessionTillId',$this->tilId);
//        $this->emit('refreshTilTable');
//
//        $this->processData();
//
//    }
//
//    public function setAmount(){
//        if($this->tellerEndOfDay->tiller_cash_at_hand == $this->closingBalance)
//        {
//            $this->message = "This Till is balanced, you can now close it";
//            $this->state = "BALANCED";
//
//        }elseif($this->tellerEndOfDay->tiller_cash_at_hand > $this->closingBalance) {
//            $this->message = "This Till is over, it can not be closed";
//            $this->state = "OVER";
//        }else{
//            $this->message = "This Till is under, it can not be closed";
//            $this->state = "UNDER";
//        }
//
//
//        $this->tellerEndOfDay->employee_id = $this->employee_id;
//        $this->tellerEndOfDay->til_number = $this->tilId;
//        $this->tellerEndOfDay->til_account = $this->tilAccountNumber;
//        $this->tellerEndOfDay->til_balance = $this->closingBalance;
//
//        $this->tellerEndOfDay->message = $this->message;
//
//        $this->tellerEndOfDay->status = $this->state;
//        $this->tellerEndOfDay->business_date = $this->selectedDate;
//        $this->tellerEndOfDay->institution_id = session()->get('currentUser')->institution_id;
//        $this->tellerEndOfDay->branch_id =session()->get('currentUser')->branch;
//
//
//        $this->tellerEndOfDay->save();
//
//
//
//    }
//
//
//
//    public function closeTil(){
//
//        // teller account number
//        $teller_account_number= $this->tilAccountNumber;
//
//        $strong_roomAccount = AccountsModel::where('sub_category_code','1025')->value('account_number');
//        $this->strong_roomAccount=$strong_roomAccount;
//
//        $teller_prev_account_balance=AccountsModel::where('id',DB::table('tellers')->where('employee_id',session()->get('currentUser')->employeeId)->value('account_id'))->value('balance');
//        // get strong room amount
//        $strong_room_account_balance=AccountsModel::where('account_number',$strong_roomAccount)->value('balance');
//
//
//
//        // new teller balance
//        $new_teller_balance=$teller_prev_account_balance-$this->closingBalance;
//
//        //strong  room new balance
//        $strong_room_new_balance=(double)$strong_room_account_balance + $this->closingBalance;
//
//        // update previous teller account
//        AccountsModel::where('account_number',$teller_account_number)->where('sub_product_number','106')->update(['balance'=>$new_teller_balance]);
//
//        // strong room  new balance records
//        AccountsModel::where('account_number',$this->strong_roomAccount)->update(['balance'=>$strong_room_new_balance]);
//
//        $reference_number=time();
//
//
//        // record on general ledger
//
//        $record_on_general_ledger=new general_ledger();
//
//        $record_on_general_ledger->debit(auth()->user()->institution_id,$teller_account_number,$this->closingBalance,'0000','0000',$strong_roomAccount,'successfully','Closing Til','0000',$teller_account_number,0,'0000','0000',$reference_number);
//        //credit
//        $record_on_general_ledger->credit(auth()->user()->institution_id,$strong_roomAccount,$this->closingBalance,$strong_room_new_balance,$teller_account_number,'0000','0000','0000','Closing Til','0000',$reference_number,'successfully','Closing Til','0000');
//
//
//
//
//    }
//
//
//
//
//    public function processData(){
//
//
//
//        // Get the Till's account number using relationships (assuming relationships are set up correctly)
//        $tilAccountId = DB::table('tellers')->where('id', $this->tilId)->value('account_id');
//        $this->employee_id = DB::table('tellers')->where('id', $this->tilId)->value('employee_id');
//        $this->tilAccountNumber = DB::table('accounts')->where('id', $tilAccountId)->value('account_number');
//        //$tilAccountNumber = Teller::find($sessionTillId)->account->account_number;
//
//        // Query the general_ledger using Eloquent to get the first record of the day
//        $firstTransactionOfDay = general_ledger::where('institution_id', auth()->user()->institution_id)
//            ->whereDate('created_at', $this->selectedDate)
//            ->where('record_on_account_number', $this->tilAccountNumber)
//            ->orderBy('created_at', 'asc')
//            ->first();
//
//
//        // Check if a transaction was found
//        if ($firstTransactionOfDay) {
//            $this->openingBalance = $firstTransactionOfDay->credit;
//
//        } else {
//            $this->openingBalance = 0.00;
//        }
//
//        // Calculate the sum of credits for the entire day
//        $this->totalCredit = general_ledger::where('institution_id', auth()->user()->institution_id)
//            ->whereDate('created_at', $this->selectedDate)
//            ->where('record_on_account_number', $this->tilAccountNumber)
//            ->sum('credit');
//
//        // Calculate the sum of debits for the entire day
//        $this->totalDebit = general_ledger::where('institution_id', auth()->user()->institution_id)
//            ->whereDate('created_at', $this->selectedDate)
//            ->where('record_on_account_number', $this->tilAccountNumber)
//            ->sum('debit');
//
//        // Query the general_ledger using Eloquent to get the last record of the day
//        $lastTransactionOfDay = general_ledger::where('institution_id', auth()->user()->institution_id)
//            ->whereDate('created_at', $this->selectedDate)
//            ->where('record_on_account_number', $this->tilAccountNumber)
//            ->orderBy('created_at', 'desc')
//            ->first();
//
//        // Check if a transaction was found
//        if ($lastTransactionOfDay) {
//            $this->closingBalance = $lastTransactionOfDay->record_on_account_number_balance;
//
//        } else {
//            $this->closingBalance = 0.00;
//        }
//
//
//    }
//
//    public function render()
//    {
//
////        dd($this->tellerEndOfDay);
//        $this->tellers = DB::table('tellers')->get();
//
//
//
//        return view('livewire.teller-management.teller-report');
//    }
//}


namespace App\Http\Livewire\TellerManagement;

use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\general_ledger;
use App\Models\Teller;
use App\Models\TellerEndOfDayPositions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Services\TransactionPostingService;

class TellerReport extends Component
{
    
    public $selectedDate;
    public $disableProcess;

    public $tilId;
    public $businessDate;
    public $strong_roomAccount;
    public $tellers;
    public $openingBalance;
    public $closingBalance;
    public $cashAtHand;
    public $message;
    public $state;
    public TellerEndOfDayPositions $tellerEndOfDay;

    protected $rules = [
        'tellerEndOfDay.tiller_cash_at_hand' => 'required',
        'tellerEndOfDay.business_date' => 'required'
    ];


    public function boot()
    {

        $this->selectedDate = now()->format('d-m-Y');
        $this->openingBalance = 0.00;
        $this->closingBalance = 0.00;
        $this->totalCredit = 0.00;
        $this->totalDebit = 0.00;
        $this->cashAtHand = 0.00;
        $this->disableProcess = false;

    }


    public function updatedSelectedDate()
    {

        $tellerEndOfDay = TellerEndOfDayPositions::where('til_number', $this->tilId)
            ->whereDate('business_date', $this->selectedDate)->first();

        if ($tellerEndOfDay == null) {
            $this->tellerEndOfDay = new TellerEndOfDayPositions();
        } else {
            $this->tellerEndOfDay = $tellerEndOfDay;
        }

        Session::put('businessDate', $this->selectedDate);
        $this->emit('refreshTilTable');


        $this->processData();

    }

    public function updatedTilId()
    {

        $this->selectedDate=Carbon::parse($this->selectedDate);
        $tellerEndOfDay = TellerEndOfDayPositions::where('til_number', $this->tilId)
            ->whereDate('business_date', $this->selectedDate)->first();

        if ($tellerEndOfDay == null) {
            $this->tellerEndOfDay = new TellerEndOfDayPositions();
        } else {
            $this->tellerEndOfDay = $tellerEndOfDay;
        }


        Session::put('sessionTillId', $this->tilId);
        $this->emit('refreshTilTable');

        $this->processData();

    }

    public function setAmount()
    {
        if ($this->tellerEndOfDay->tiller_cash_at_hand == $this->closingBalance) {
            $this->message = "This Till is balanced, you can now close it";
            $this->state = "BALANCED";

        } elseif ($this->tellerEndOfDay->tiller_cash_at_hand > $this->closingBalance) {
            $this->message = "This Till is over, it can not be closed";
            $this->state = "OVER";
        } else {
            $this->message = "This Till is under, it can not be closed";
            $this->state = "UNDER";
        }


        $this->tellerEndOfDay->employee_id = $this->employee_id;
        $this->tellerEndOfDay->til_number = $this->tilId;
        $this->tellerEndOfDay->til_account = $this->tilAccountNumber;
        $this->tellerEndOfDay->til_balance = $this->closingBalance;
        $this->tellerEndOfDay->message = $this->message;
        $this->tellerEndOfDay->status = $this->state;
        $this->tellerEndOfDay->business_date = $this->selectedDate;
        // Removed institution_id assignment as it's no longer used
        $this->tellerEndOfDay->branch_id = auth()->user()->branch;


        $this->tellerEndOfDay->save();


    }


    public function closeTil()
    {
        // teller account number
        $teller_account_number = $this->tilAccountNumber;
        $strong_roomAccount = AccountsModel::where('sub_category_code', '1025')->value('account_number');
        $this->strong_roomAccount = $strong_roomAccount;
        $teller_prev_account_balance = AccountsModel::where('id', DB::table('tellers')->where('employee_id', auth()->user()->employeeId)->value('account_id'))->value('balance');
        // get strong room amount
        $strong_room_account_balance = AccountsModel::where('account_number', $strong_roomAccount)->value('balance');
        // new teller balance
        $new_teller_balance = $teller_prev_account_balance - $this->closingBalance;
        //strong  room new balance
        $strong_room_new_balance = (double)$strong_room_account_balance + $this->closingBalance;
        // update previous teller account
        // AccountsModel::where('account_number', $teller_account_number)->where('sub_product_number', '106')->update(['balance' => $new_teller_balance]);
        // strong room  new balance records
        // AccountsModel::where('account_number', $this->strong_roomAccount)->update(['balance' => $strong_room_new_balance]);
        $reference_number = time();
        // Use TransactionPostingService for double-entry
        $transactionService = new TransactionPostingService();
        $transactionService->postTransaction([
            'first_account' => $teller_account_number,
            'second_account' => $strong_roomAccount,
            'amount' => $this->closingBalance,
            'narration' => 'Closing Til',
        ]);
        // record on general ledger
        $record_on_general_ledger = new general_ledger();
        $record_on_general_ledger->debit($teller_account_number, $this->closingBalance, '0000', '0000', $strong_roomAccount, 'successfully', 'Closing Til', '0000', $teller_account_number, 0, '0000', '0000', $reference_number);
        //credit
        $record_on_general_ledger->credit($strong_roomAccount, $this->closingBalance, $strong_room_new_balance, $teller_account_number, '0000', '0000', '0000', 'Closing Til', '0000', $reference_number, 'successfully', 'Closing Til', '0000');
        $this->closingBalance = null;
    }


    public function processData()
    {


        // Get the Till's account number using relationships (assuming relationships are set up correctly)
        $tilAccountId = DB::table('tellers')->where('id', $this->tilId)->value('account_id');
        $this->employee_id = DB::table('tellers')->where('id', $this->tilId)->value('employee_id');
        $this->tilAccountNumber = DB::table('accounts')->where('id', $tilAccountId)->value('account_number');
        //$tilAccountNumber = Teller::find($sessionTillId)->account->account_number;

        // Query the general_ledger using Eloquent to get the first record of the day
        $firstTransactionOfDay = general_ledger::whereDate('created_at', $this->selectedDate)
            ->where('record_on_account_number', $this->tilAccountNumber)
            ->orderBy('created_at', 'asc')
            ->first();


        // Check if a transaction was found
        if ($firstTransactionOfDay) {
            $this->openingBalance = $firstTransactionOfDay->credit;

        } else {
            $this->openingBalance = 0.00;
        }

        // Calculate the sum of credits for the entire day
        $this->totalCredit = general_ledger::whereDate('created_at', $this->selectedDate)
            ->where('record_on_account_number', $this->tilAccountNumber)
            ->sum('credit');

        // Calculate the sum of debits for the entire day
        $this->totalDebit = general_ledger::whereDate('created_at', $this->selectedDate)
            ->where('record_on_account_number', $this->tilAccountNumber)
            ->sum('debit');

        // Query the general_ledger using Eloquent to get the last record of the day
        $lastTransactionOfDay = general_ledger::
        whereDate('created_at', $this->selectedDate)
            ->where('record_on_account_number', $this->tilAccountNumber)
            ->orderBy('created_at', 'desc')
            ->first();

        // Check if a transaction was found
        if ($lastTransactionOfDay) {
            $this->closingBalance = $lastTransactionOfDay->record_on_account_number_balance;

        } else {
            $this->closingBalance = 0.00;
        }


    }

    public function render()
    {

//        dd($this->tellerEndOfDay);
        $this->tellers = DB::table('tellers')->where('employee_id', '!=', null)->where('employee_id', '!=', 0)->get();


        return view('livewire.teller-management.teller-report');
    }
}
