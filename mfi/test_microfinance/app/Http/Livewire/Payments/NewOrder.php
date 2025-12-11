<?php

namespace App\Http\Livewire\Payments;



use App\Models\StockPrice;
use App\Models\Expenses;
use App\Models\MonthlyPaymentsStatus;
use App\Models\Processes;
use App\Models\Transactions;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use JetBrains\PhpStorm\NoReturn;
use Livewire\Component;
use App\Models\Orders;
use App\Models\AccountsModel;
use Livewire\WithFileUploads;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Exceptions\DataTableConfigurationException;
use Rappasoft\LaravelLivewireTables\Traits\WithBulkActions;
use Rappasoft\LaravelLivewireTables\Traits\WithColumns;
use Rappasoft\LaravelLivewireTables\Traits\WithColumnSelect;
use Rappasoft\LaravelLivewireTables\Traits\WithData;
use Rappasoft\LaravelLivewireTables\Traits\WithDebugging;
use Rappasoft\LaravelLivewireTables\Traits\WithFilters;
use Rappasoft\LaravelLivewireTables\Traits\WithFooter;
use Rappasoft\LaravelLivewireTables\Traits\WithPagination;
use Rappasoft\LaravelLivewireTables\Traits\WithRefresh;
use Rappasoft\LaravelLivewireTables\Traits\WithReordering;
use Rappasoft\LaravelLivewireTables\Traits\WithSearch;
use Rappasoft\LaravelLivewireTables\Traits\WithSecondaryHeader;
use Rappasoft\LaravelLivewireTables\Traits\WithSorting;
use Rappasoft\LaravelLivewireTables\Views\Column;

//use Vtiful\Kernel\Excel;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportTransactions;
use App\Imports\ImportTransactions;
use App\Services\DisbursementService;

class NewOrder extends Component
{

    public $css;
    public $oneIsSetClicked = true;
    public $twoIsSetClicked = false;
    public $threeIsSetClicked = false;
    public $unCommitedOrderNumber = '';
    public $unCommitedOrderNumberissaved = false;
    public $selectedDestination = '';
    public $amountOfTransactions = '';
    public $amountOfTransactionsDisplay = '';
    public $selectedBank = '';
    public $recipientName = '';
    public $sourceAccount = '';
    public $description = '';
    public $amount = '';
    public $accountNumber = '';
    public $fromDB = false;
    public $destination_account;
    public $showSaveButton = false;
    public $xx = 'Select';
    public $accounts;
    public $selectedMno = '';
    public $mobileNumber = '';

    public $monthOnFocus = 'April';
    public $yearOnFocus = '2022';
    public $theselected;
    public $permissionId = [];
    public $Amountpending;
    public $level = [];
    public $ProcessExists;
    public $ProcessStatus;
    public $ProcessPaymentStatus;
    public $excelFile;
    public $showSendButton = false;
    public $bname;
    public $sourceaccount;
    public $typeOfTransfer;
    public $banks;
    public $title;

    public $reference_number;

    protected $listeners = ['refreshComponent' => '$refresh'];


    protected DisbursementService $disbursementService;

    protected $rules = [

        'amountOfTransactions' => 'required|min:3',
        'sourceAccount' => 'required|min:3',

        'bname' => 'required|min:3',
        'accountNumber' => 'required|min:3',
        'description' => 'required|min:3',

        'amount' => 'required|min:3',
        //'mobileNumber' => 'required|min:3',
        'selectedDestination' => 'required|min:3',

        //'selectedBank' => 'required|min:3',
        //'selectedMno' => 'required|min:3'

    ];


    function gerRNN(){
        return "TRNN_".time();
    }
    public function activateSaveButton()
    {
        //dd('yyy');
        $this->showSaveButton = true;
    }

    #[NoReturn] public function sourceAccountx()
    {
        dd($this->description);
    }

    public function changeTab($index)
    {

        if ($index == 1) {
            $this->oneIsSetClicked = true;
            $this->twoIsSetClicked = false;
            $this->title="External Transfer / Payment";

            $this->threeIsSetClicked = false;
        }
        if ($index == 2) {
            $this->oneIsSetClicked = false;
            $this->twoIsSetClicked = true;
            $this->title="Internal Transfer ";
            $this->threeIsSetClicked = false;
        }
        if ($index == 3) {
            $this->oneIsSetClicked = false;
            $this->twoIsSetClicked = false;
            $this->title="Utility  Payment";

            $this->threeIsSetClicked = true;
        }

    }

    #[NoReturn] public function createOrder()
    {


        if (

            $this->sourceAccount == '' ||
            $this->bname == '' ||
            $this->accountNumber == '' ||
            $this->description == '' ||
            $this->amount == '' ||
            !$this->showSaveButton
        ) {
        } else {



            //$this->validate();
            if ($this->selectedBank == '') {
                $this->selectedBank = '59';
            }

            if ($this->unCommitedOrderNumber == '') {
                $this->unCommitedOrderNumber = sprintf("%06d", mt_rand(1, 999999));
            }

            $ref = time();
            $branch_id = '';
            $name = '';
            $bank_name = '';
            $id = auth()->user()->id;
            $currentUser = DB::table('team_user')->where('user_id', $id)->get();
            foreach ($currentUser as $User) {
                $branch_id = $User->team_id;
            }
            $currentBranch = DB::table('teams')->where('id', $branch_id)->get();
            foreach ($currentBranch as $branch) {
                $name = $branch->name;
            }
            $banks = DB::table('banks')->where('bank_number', $this->selectedBank)->get();
            foreach ($banks as $bank) {
                $bank_name = $bank->bank_name;
            }

            $order = new Orders;
            $order->team_id = $branch_id;
            $order->user_id = $id;
            $order->order_number = $this->unCommitedOrderNumber;
            $order->amountOfTransactions = $this->amountOfTransactions;
            $order->source_account = $this->sourceAccount;
            $order->typeOfTransfer = $this->selectedDestination;
            $order->first_authorizer_action="12";
            $order->second_authorizer_action="2332";
            $order->third_authorizer_action="234";
            $order->save();

            $transaction = new Transactions;
            $transaction->beneficiary_nane = $this->bname;
            $transaction->team_id = $branch_id;
            $transaction->order_id = $order->id;
            $transaction->order_number = $this->unCommitedOrderNumber;
            $transaction->source_branch_id = $branch_id;
            $transaction->source_branch_name = $name;
            $transaction->source_branch_account_number = $this->sourceAccount;
            $transaction->destination_branch_account_number = $this->accountNumber;
            $transaction->description = $this->description;
            $transaction->transaction_amount = str_replace(',', '', $this->amount);
            $transaction->credit = '';
            $transaction->debit = $this->amount;
            $transaction->reference_number = $ref;
            $transaction->phone_number = $this->mobileNumber;
            $transaction->institution = $this->selectedDestination;
            $transaction->institution_name = $this->selectedBank;

            $transaction->ordering_customer = '';
            $transaction->issuer_name = '';
            $transaction->employee_name = '';
             $transaction->selected="Yes";
            $transaction->swift_code = '';
            $transaction->destination_bank_name = $bank_name;
            $transaction->destination_bank_number = $this->selectedBank;

            $transaction->typeOfTransfer = $this->selectedDestination;
            $transaction->save();


            $audit = new StockPrice;
            $audit->institution_id = $branch_id;
            $audit->user_id = $id;
            $audit->activity = 'Order created. Order number - ' . $this->unCommitedOrderNumber;
            $audit->save();

            Session::put('unCommitedOrderNumber',$this->unCommitedOrderNumber);
            $this->resertInput();

            $this->emit('orderAdded');
            $this->showSendButton = true;

        }

    }

    public function start()
    {


        Session::put('unCommitedOrderNumber', $this->unCommitedOrderNumber);
    }


    public function render(): Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {


// ransaction refrenc no
       $this->reference_number =$this->gerRNN();
       $this->unCommitedOrderNumber=$this->reference_number;
        $this->accounts = DB::table('accounts')->where('account_use', 'internal')->get();
        //$this->unCommitedOrderNumber = sprintf("%06d", mt_rand(1, 999999));
        $this->banks = DB::table('banks')->get();

        //Session::put('unCommitedOrderNumber',null);

        return view('livewire.payments.new-order');

    }

    public function requestApproval()
    {

        $branch_id = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $branch_id = $User->team_id;
        }

        Orders::where('order_number', $this->unCommitedOrderNumber)->update(['order_status' => 'Pending Approval']);
        Transactions::where('order_number', $this->unCommitedOrderNumber)->update(['trans_status' => 'Pending Approval']);


        $audit = new StockPrice();
        $audit->institution_id = $branch_id;
        $audit->user_id = $id;
        $audit->activity = 'Batch order created. Order number - ' . $this->unCommitedOrderNumber;
        $audit->save();
        $this->resertInput();
       // return redirect(request()->header('Referer'));
    }

    public function cancelRequest(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {

        $result = Processes::where('process_id', 10)
            ->where('month_of_process', now()->month)
            ->where('year_of_process', now()->year)
            ->delete();

        return redirect(request()->header('Referer'));
    }

    public function resetData(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {

        $result = Processes::where('process_id', 10)
            ->where('month_of_process', now()->month)
            ->where('year_of_process', now()->year)
            ->delete();

        return redirect(request()->header('Referer'));
    }

    public function exportUsers()
    {
        return Excel::download(new ExportTransactions, 'users.xlsx');
    }

    public function importData()
    {


        if ($this->unCommitedOrderNumber == '') {
            $this->unCommitedOrderNumber = sprintf("%06d", mt_rand(1, 999999));
        }


        $branch_id = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $branch_id = $User->team_id;
        }

        $order = new Orders;
        $order->team_id = $branch_id;
        $order->user_id = $id;
        $order->order_number = $this->unCommitedOrderNumber;
        $order->amountOfTransactions = $this->amountOfTransactions;
        $order->source_account = '';
        $order->typeOfTransfer = $this->typeOfTransfer;
        $order->save();


        $audit = new StockPrice;
        $audit->institution_id = $branch_id;
        $audit->user_id = $id;
        $audit->activity = 'Batch order created. Order number - ' . $this->unCommitedOrderNumber;
        $audit->save();


        Session::put('typeOfTransfer', $this->typeOfTransfer);

        Excel::import(new ImportTransactions, $this->excelFile->store('files'));

        $this->resertInput();


        $this->emit('refreshComponent');


    }

    public function deleteOrder(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {

        $branch_id = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $branch_id = $User->team_id;
        }
        $audit = new StockPrice;
        $audit->institution_id = $branch_id;
        $audit->user_id = $id;
        $audit->activity = 'Order deletion. Order number - ' . $this->unCommitedOrderNumber;
        $audit->save();

        Orders::where('order_number', $this->unCommitedOrderNumber)->delete();
        Transactions::where('order_number', $this->unCommitedOrderNumber)->delete();
        $this->resertInput();
        return redirect(request()->header('Referer'));
    }

    public function resertInput()
    {


        $this->amountOfTransactions = '';
        $this->sourceAccount = '';
        $this->bname = '';
        $this->accountNumber = '';
        $this->description = '';
        $this->amount = '';
        $this->mobileNumber = '';
        $this->selectedDestination = '';
        $this->selectedBank = '';
        $this->selectedMno = '';
        $this->showSaveButton = false;

    }


    public function internalFundTransfer(){
        //validate input  and exists
        $this->validate([
       'destination_account'=>'required|exists:accounts,account_number',
       'sourceAccount'=>'required|exists:accounts,account_number',
       'amount'=>'required|numeric'

        ]);


        $source_account=$this->sourceAccount;
        $destination_accounts =$this->destination_account;
        $narration="Internal Funds Transfer";

        if($this->checkBalance( $source_account) > $this->amount){
            $transaction= new DisbursementService();
           $outPut=  $transaction->makeTransaction($source_account,$this->amount,$destination_accounts ,$narration);
           session()->flash('message',$outPut);

        }else{
            session()->flash('message_fail','insufficient Balance');
        }

        // session()->flash('message_fail','something went wrong');

        $this->clearData();

    }


    function clearData(){
        $this->sourceAccount=null;
        $this->destination_account=null;
        $this->amount=null;

    }


    function checkBalance($account_number){
        return  AccountsModel::where('account_number',$account_number)->value('balance');
    }


}
