<?php

namespace App\Http\Livewire\Payments;



use App\Models\Branch;
use App\Models\Expenses;
use App\Models\MonthlyPaymentsStatus;
use App\Models\Processes;
use App\Models\Transactions;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
use Session;


class ManageOrder extends Component
{


    public $css;
    public $oneIsSetClicked = true;
    public $twoIsSetClicked = false;
    public $threeIsSetClicked = false;
    public $ordernumber = '';
    public $ordernumberissaved = false;
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
    public $showSaveButton = false;
    public $xx = 'Select';
    public $accounts;
    public $selectedMno = 'Tigo';
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
    public $orders;
    public $CurrentOrder;
    public $modal = false;
    public $deleteOrderModal = false;
    public $orderToDeleteId = '';
    public $entryToDeleteId = '';
    public $deleteEntryModal = '';
    public $order_status = '';
    public $exceptions = false;
    public $rejectionReason = '';
    public mixed $reasonError = '';
    public $prepareForRejection = false;
    public $newOrderNumber = '';

    protected $listeners = [
        'showEditModal',
        'confirmDeleteOrder',
        'deleteOrderConfirmed',
        'closeEditEntryModal',
        'confirmDeleteEntry',
        'closeDeleteOrderModal',
        'closeDeleteEntryModal',
        'closeDeleteEntryModal',
        'refreshComponent' => '$refresh'
    ];

    public function preReject()
    {
        Session::put('orderNumber', $this->ordernumber);
        $this->prepareForRejection = true;
    }

    public function showEditModal($value)
    {
        //Session::get('orderNumber')
        Session::put('editTransactionID', $value);
        $this->modal = true;
    }

    public function confirmDeleteOrder($value)
    {
        $this->orderToDeleteId = $value;
        //Session::get('orderNumber')
        Session::put('deleteOrderID', $value);
        $this->deleteOrderModal = true;
    }

    public function confirmDeleteEntry($value)
    {

        $this->entryToDeleteId = $value;
        //Session::get('orderNumber')
        Session::put('deleteTransactionID', $value);
        $this->deleteEntryModal = true;
    }

    public function deleteOrderConfirmed()
    {

        $res = Orders::where('id', $this->orderToDeleteId)->delete();
        $this->deleteOrderModal = false;
    }


    public function closeDeleteOrderModal()
    {
        $this->deleteOrderModal = false;
    }

    public function closeDeleteEntryModal()
    {
        $this->deleteEntryModal = false;
    }


    public function closeEditEntryModal()
    {

        $this->modal = false;
    }

    protected $rules = [
        'expensename' => 'required|min:3',
        'amount' => 'required|min:3',
    ];

    public function activateSaveButton()
    {
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
            $this->threeIsSetClicked = false;
        }
        if ($index == 2) {
            $this->oneIsSetClicked = false;
            $this->twoIsSetClicked = true;
            $this->threeIsSetClicked = false;
        }
        if ($index == 3) {
            $this->oneIsSetClicked = false;
            $this->twoIsSetClicked = false;
            $this->threeIsSetClicked = true;
        }

    }


    public function render(): Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $branch_id = auth()->user()->branch;



        $this->accounts = DB::table('accounts')->get();

        $this->orders = DB::table('orders')->where('team_id', $branch_id)->get();

        $this->CurrentOrder = DB::table('orders')
            ->where('team_id', $branch_id)
            ->where('order_number', Session::get('orderNumber'))
            ->get();
        foreach ($this->CurrentOrder as $TheOrder) {
            $this->order_status = $TheOrder->order_status;
        }
        Session::put('orderNumber', $this->ordernumber);
        return view('livewire.payments.manage-order');

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

        Excel::import(new ImportTransactions, $this->excelFile->store('files'));


    }

    public function getOrderDetails($OrderId)
    {

        $order_number_new = '';
        $currentOrder = DB::table('orders')->where('id', $OrderId)->get();
        foreach ($currentOrder as $TheOrder) {
            $order_number_new = $TheOrder->order_number;
        }
        $this->ordernumber = $order_number_new;
        //$this->ordernumber->reflesh();
        Session::put('orderNumber', $order_number_new);
        //$this->emit('refreshComponent');
        //return redirect(request()->header('Referer'));
        $this->emit('viewOrder');
    }

    public function getOrderDetailsExceptions($OrderId)
    {
        //dd($OrderId);
        $this->exceptions = true;
        $order_number_new = '';
        $currentOrder = DB::table('orders')->where('id', $OrderId)->get();
        foreach ($currentOrder as $TheOrder) {
            $order_number_new = $TheOrder->order_number;
        }
        $this->ordernumber = $order_number_new;
        Session::put('orderNumber', $order_number_new);

        //return redirect(request()->header('Referer'));
    }


    public function requestApproval()
    {

        Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Pending Approval']);
        Transactions::where('order_number', $this->ordernumber)->update(['trans_status' => 'Pending Approval']);

        //return redirect(request()->header('Referer'));
    }

    public function cancelRequest()
    {

        Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Pending']);
        Transactions::where('order_number', $this->ordernumber)->update(['trans_status' => 'Pending']);

        //return redirect(request()->header('Referer'));
    }

    public function createNewOrder(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {


        if ($this->newOrderNumber == '') {
            $this->newOrderNumber = sprintf("%06d", mt_rand(1, 999999));
        }


        $ref = time();
        $branch_id = '';
        $name = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $branch_id = $User->team_id;
        }
        $currentBranch = DB::table('teams')->where('id', $branch_id)->get();
        foreach ($currentBranch as $branch) {
            $name = $branch->name;
        }


        $Orders = Orders::where('order_number', $this->ordernumber)->get();
        //dd($this->ordernumber);

        foreach ($Orders as $Orderx) {

            $order = new Orders;
            $order->team_id = $Orderx->team_id;
            $order->user_id = $Orderx->user_id;
            $order->order_number = $this->newOrderNumber;
            $order->order_status = $Orderx->order_status;
            $order->order_failed_transaction = $Orderx->order_failed_transaction;
            $order->source_account = $Orderx->source_account;
            $order->typeOfTransfer = $Orderx->typeOfTransfer;
            $order->amountOfTransactions = $Orderx->amountOfTransactions;
            $order->save();

        }

        $Transactions = Transactions::where('order_number', $this->ordernumber)->get();

        foreach ($Transactions as $Transactionx) {

            $transaction = new Transactions;
            $transaction->team_id = $branch_id;
            $transaction->order_id = '';
            $transaction->order_number = $this->newOrderNumber;
            $transaction->source_branch_id = $Transactionx->source_branch_id;
            $transaction->source_branch_name = $Transactionx->source_branch_name;
            $transaction->source_branch_account_number = $Transactionx->source_branch_account_number;
            $transaction->destination_branch_account_number = $Transactionx->destination_branch_account_number;
            $transaction->description = $Transactionx->description;
            $transaction->transaction_amount = $Transactionx->transaction_amount;
            $transaction->credit = $Transactionx->credit;
            $transaction->debit = $Transactionx->debit;
            $transaction->reference_number = $Transactionx->reference_number;
            $transaction->phone_number = $Transactionx->phone_number;
            $transaction->institution = $Transactionx->institution;
            $transaction->institution_name = $Transactionx->institution_name;
            $transaction->save();

        }


        return redirect(request()->header('Referer'));
    }

    public function createNewOrderExceptions(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {


        $newOrderNumber = sprintf("%06d", mt_rand(1, 999999));

        $ref = time();
        $branch_id = '';
        $name = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $branch_id = $User->team_id;
        }
        $currentBranch = DB::table('teams')->where('id', $branch_id)->get();
        foreach ($currentBranch as $branch) {
            $name = $branch->name;
        }


        $Orders = Orders::where('order_number', $this->ordernumber)->get();
        //dd($this->ordernumber);

        foreach ($Orders as $Orderx) {

            $order = new Orders;
            $order->team_id = $Orderx->team_id;
            $order->user_id = $Orderx->user_id;
            $order->order_number = $newOrderNumber;
            $order->amountOfTransactions = $Orderx->amountOfTransactions;
            $order->source_account = $Orderx->source_account;
            $order->save();

        }
        Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Processed']);

        $Transactions = Transactions::where('order_number', $this->ordernumber)
            ->where('selected', 0)
            ->get();

        foreach ($Transactions as $Transactionx) {

            $transaction = new Transactions;
            $transaction->team_id = $branch_id;
            $transaction->order_id = '';
            $transaction->order_number = $newOrderNumber;
            $transaction->source_branch_id = $Transactionx->source_branch_id;
            $transaction->source_branch_name = $Transactionx->source_branch_name;
            $transaction->source_branch_account_number = $Transactionx->source_branch_account_number;
            $transaction->destination_branch_account_number = $Transactionx->destination_branch_account_number;
            $transaction->description = $Transactionx->description;
            $transaction->transaction_amount = $Transactionx->transaction_amount;
            $transaction->credit = $Transactionx->credit;
            $transaction->debit = $Transactionx->debit;
            $transaction->reference_number = $Transactionx->reference_number;
            $transaction->phone_number = $Transactionx->phone_number;
            $transaction->institution = $Transactionx->institution;
            $transaction->institution_name = $Transactionx->institution_name;
            $transaction->save();

        }

        $res = Transactions::where('order_number', $this->ordernumber)
            ->where('selected', 0)
            ->delete();

        return redirect(request()->header('Referer'));
    }


    public function createNewOrderErrors(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {

        if ($this->newOrderNumber == '') {
            $this->newOrderNumber = sprintf("%06d", mt_rand(1, 999999));
        }


        $ref = time();
        $branch_id = '';
        $name = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $branch_id = $User->team_id;
        }
        $currentBranch = DB::table('teams')->where('id', $branch_id)->get();
        foreach ($currentBranch as $branch) {
            $name = $branch->name;
        }


        $Orders = Orders::where('order_number', $this->ordernumber)->get();
        //dd($this->ordernumber);

        foreach ($Orders as $Orderx) {

            $order = new Orders;
            $order->team_id = $Orderx->team_id;
            $order->user_id = $Orderx->user_id;
            $order->order_number = $this->newOrderNumber;
            $order->order_status = $Orderx->order_status;
            $order->order_failed_transaction = $Orderx->order_failed_transaction;
            $order->source_account = $Orderx->source_account;
            $order->typeOfTransfer = $Orderx->typeOfTransfer;
            $order->amountOfTransactions = $Orderx->amountOfTransactions;
            $order->save();


        }
        Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Processed']);

        $Transactions = Transactions::where('order_number', $this->ordernumber)
            ->where('selected', 1)
            ->where('trans_status', 'Failed')
            ->get();

        foreach ($Transactions as $Transactionx) {

            $transaction = new Transactions;
            $transaction->team_id = $branch_id;
            $transaction->order_id = '';
            $transaction->order_number = $this->newOrderNumber;
            $transaction->source_branch_id = $Transactionx->source_branch_id;
            $transaction->source_branch_name = $Transactionx->source_branch_name;
            $transaction->source_branch_account_number = $Transactionx->source_branch_account_number;
            $transaction->destination_branch_account_number = $Transactionx->destination_branch_account_number;
            $transaction->description = $Transactionx->description;
            $transaction->transaction_amount = $Transactionx->transaction_amount;
            $transaction->credit = $Transactionx->credit;
            $transaction->debit = $Transactionx->debit;
            $transaction->reference_number = $Transactionx->reference_number;
            $transaction->phone_number = $Transactionx->phone_number;
            $transaction->institution = $Transactionx->institution;
            $transaction->institution_name = $Transactionx->institution_name;
            $transaction->save();

        }

        $res = Transactions::where('order_number', $this->ordernumber)
            ->where('selected', 1)
            ->where('trans_status', 'Failed')
            ->delete();

        return redirect(request()->header('Referer'));
    }

    public function createNewOrderExceptionsAndErrors(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {

        if ($this->newOrderNumber == '') {
            $this->newOrderNumber = sprintf("%06d", mt_rand(1, 999999));
        }


        $ref = time();
        $branch_id = '';
        $name = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $branch_id = $User->team_id;
        }
        $currentBranch = DB::table('teams')->where('id', $branch_id)->get();
        foreach ($currentBranch as $branch) {
            $name = $branch->name;
        }


        $Orders = Orders::where('order_number', $this->ordernumber)->get();
        //dd($this->ordernumber);

        foreach ($Orders as $Orderx) {

            $order = new Orders;
            $order->team_id = $Orderx->team_id;
            $order->user_id = $Orderx->user_id;
            $order->order_number = $this->newOrderNumber;
            $order->order_status = $Orderx->order_status;
            $order->order_failed_transaction = $Orderx->order_failed_transaction;
            $order->source_account = $Orderx->source_account;
            $order->typeOfTransfer = $Orderx->typeOfTransfer;
            $order->amountOfTransactions = $Orderx->amountOfTransactions;
            $order->save();


        }
        Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Processed']);

        $Transactions = Transactions::where('order_number', $this->ordernumber)
            ->where('selected', 0)
            ->orWhere('trans_status', 'Failed')
            ->get();

        foreach ($Transactions as $Transactionx) {

            $transaction = new Transactions;
            $transaction->team_id = $branch_id;
            $transaction->order_id = '';
            $transaction->order_number = $this->newOrderNumber;
            $transaction->source_branch_id = $Transactionx->source_branch_id;
            $transaction->source_branch_name = $Transactionx->source_branch_name;
            $transaction->source_branch_account_number = $Transactionx->source_branch_account_number;
            $transaction->destination_branch_account_number = $Transactionx->destination_branch_account_number;
            $transaction->description = $Transactionx->description;
            $transaction->transaction_amount = $Transactionx->transaction_amount;
            $transaction->credit = $Transactionx->credit;
            $transaction->debit = $Transactionx->debit;
            $transaction->reference_number = $Transactionx->reference_number;
            $transaction->phone_number = $Transactionx->phone_number;
            $transaction->institution = $Transactionx->institution;
            $transaction->institution_name = $Transactionx->institution_name;
            $transaction->save();

        }

        $res = Transactions::where('order_number', $this->ordernumber)
            ->where('selected', 0)
            ->orWhere('trans_status', 'Failed')
            ->delete();

        return redirect(request()->header('Referer'));
    }


}
