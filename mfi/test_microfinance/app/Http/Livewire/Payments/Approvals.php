<?php

namespace App\Http\Livewire\Payments;


use App\Jobs\ProcessIFT;

use App\Models\Expenses;
use App\Models\MonthlyPaymentsStatus;
use App\Models\Processes;
use App\Models\Transactions;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

class Approvals extends Component
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
    public $statuses = '';
    public $order_status = '';
    public $internalAction = false;
    public $rejectionReason = '';
    public mixed $reasonError = '';
    public $prepareForRejection = false;

    protected $listeners = ['showEditModal', 'confirmDeleteOrder', 'deleteOrderConfirmed', 'refreshComponent' => '$refresh'];


    public function render(): Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $branch_id = '';

        //dd($branch_id);

        $this->accounts = DB::table('accounts')->get();

        $role = '';

        if ($role == 'firstAuthorizer') {

            $this->orders = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_status', 'Pending Approval')
                ->where('first_authorizer_action', 'Pending')
                ->get();

            $this->CurrentOrder = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_number', Session::get('orderNumber'))
                ->where('order_status', 'Pending Approval')
                ->where('first_authorizer_action', 'Pending')
                ->get();
            foreach ($this->CurrentOrder as $TheOrder) {
                $this->order_status = $TheOrder->order_status;
            }
        }elseif ($role == 'secondAuthorizer') {

            $this->orders = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_status', 'Pending Approval')
                ->where('second_authorizer_action', 'Pending')
                ->get();

            $this->CurrentOrder = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_number', Session::get('orderNumber'))
                ->where('order_status', 'Pending Approval')
                ->where('second_authorizer_action', 'Pending')
                ->get();
            foreach ($this->CurrentOrder as $TheOrder) {
                $this->order_status = $TheOrder->order_status;
            }

        }elseif ($role == 'thirdAuthorizer') {


            $this->orders = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_status', 'Pending Approval')
                ->where('third_authorizer_action', 'Pending')
                ->get();

            $this->CurrentOrder = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_number', Session::get('orderNumber'))
                ->where('order_status', 'Pending Approval')
                ->where('third_authorizer_action', 'Pending')
                ->get();
            foreach ($this->CurrentOrder as $TheOrder) {
                $this->order_status = $TheOrder->order_status;
            }

        }else {

            $this->orders = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_status', 'Pending Approval')
                ->where('first_authorizer_action', 'Pending')
                ->get();

            $this->CurrentOrder = DB::table('orders')
                ->where('team_id', $branch_id)
                ->where('order_number', Session::get('orderNumber'))
                ->where('order_status', 'Pending Approval')
                ->where('first_authorizer_action', 'Pending')
                ->get();
            foreach ($this->CurrentOrder as $TheOrder) {
                $this->order_status = $TheOrder->order_status;
            }

        }


        return view('livewire.payments.approvals');

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
        Session::put('orderNumber', $this->ordernumber);
        $this->emit('viewOrderToApprove');
        //return redirect(request()->header('Referer'));
    }


    public function preReject()
    {
        $this->prepareForRejection = true;
    }

    public function reject()
    {
        $this->ordernumber = Session::get('orderNumber');
        $this->reasonError = 'Enter reason for rejection';
        $role = '';

        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $role = $User->role;
        }


        if ($this->rejectionReason == '') {
            $this->reasonError = 'Enter reason for rejection';
        } else {
            $this->prepareForRejection = false;
            if ($role == 'firstAuthorizer') {

                Orders::where('order_number', $this->ordernumber)->update([
                    "first_authorizer_id" => $id,
                    "first_authorizer_action" => 'Rejected',
                    "first_authorizer_comments" => $this->rejectionReason,
                ]);
            }
            if ($role == 'secondAuthorizer') {

                Orders::where('order_number', $this->ordernumber)->update([
                    "second_authorizer_id" => $id,
                    "second_authorizer_action" => 'Rejected',
                    "second_authorizer_comments" => $this->rejectionReason,
                ]);
            }
            if ($role == 'thirdAuthorizer') {


                Orders::where('order_number', $this->ordernumber)->update([
                    "third_authorizer_id" => $id,
                    "third_authorizer_action" => 'Rejected',
                    "third_authorizer_comments" => $this->rejectionReason,
                ]);
            }
            Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Rejected']);
            Transactions::where('order_number', $this->ordernumber)->update(['trans_status' => 'Rejected']);

            $orders = DB::table('orders')->where('order_number', $this->ordernumber)->get();
            foreach ($orders as $order) {

                if ($order->first_authorizer_action == 'Approved' &&
                    $order->second_authorizer_action == 'Approved' &&
                    $order->third_authorizer_action == 'Approved'
                ) {
                    Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Approved']);
                    Transactions::where('order_number', $this->ordernumber)->update(['trans_status' => 'Approved']);
                } else {

                }
            }

            Session::put('listChanged', false);
        }

        //return redirect(request()->header('Referer'));
    }


    public function approve()
    {


        $this->ordernumber = Session::get('orderNumber');
        $comments = 'Approved';
        if (Session::get('listChanged')) {
            $comments = 'Approved with exceptions';
        }
        $role = '';

        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $role = $User->role;
        }


        if ($role == 'firstAuthorizer') {

            Orders::where('order_number', $this->ordernumber)->update([
                "first_authorizer_id" => $id,
                "first_authorizer_action" => 'Approved',
                "first_authorizer_comments" => $comments,
            ]);
        }
        if ($role == 'secondAuthorizer') {

            Orders::where('order_number', $this->ordernumber)->update([
                "second_authorizer_id" => $id,
                "second_authorizer_action" => 'Approved',
                "second_authorizer_comments" => $comments,
            ]);
        }
        if ($role == 'thirdAuthorizer') {


            Orders::where('order_number', $this->ordernumber)->update([
                "third_authorizer_id" => $id,
                "third_authorizer_action" => 'Approved',
                "third_authorizer_comments" => $comments,
            ]);
        }


        //$role= \App\Models\TeamUser::where('user_id' ,$user->id)->value('role');

        $orders = DB::table('orders')->where('order_number', $this->ordernumber)->get();
        foreach ($orders as $order) {

            if ($order->first_authorizer_action == 'Approved' &&
                $order->second_authorizer_action == 'Approved' &&
                $order->third_authorizer_action == 'Approved'
            ) {


                $transactionsCount = Transactions::where('order_number', $this->ordernumber)->where('selected', 0)->count();

                if ($transactionsCount > 0) {
                    Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'In Progress With Exceptions']);
                    Transactions::where('order_number', $this->ordernumber)
                        ->where('selected', 1)
                        ->update(['trans_status' => 'In Progress']);
                    Transactions::where('order_number', $this->ordernumber)
                        ->where('selected', 0)
                        ->update(['trans_status' => 'Skipped']);
                    if ($order->typeOfTransfer == 'IFT') {
                        $this->pushTransactions($this->ordernumber);
                    }

                } else {
                    Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'In Progress']);
                    Transactions::where('order_number', $this->ordernumber)->update(['trans_status' => 'In Progress']);
                    if ($order->typeOfTransfer == 'IFT') {
                        $this->pushTransactions($this->ordernumber);
                    }
                }


            } else {

            }
        }

        Session::put('listChanged', false);

        //return redirect(request()->header('Referer'));
    }

    public function pushTransactions($transaction)
    {
        $i = 1;

        $transactions = DB::table('transactions')
            ->where('order_number', '791126')
            ->where('selected', 1)
            ->where('trans_status', 'In Progress')
            ->get();
        foreach ($transactions as $transaction) {

            $emailJob = (new ProcessIFT($transaction))->delay(Carbon::now()->addSeconds($i));
            dispatch($emailJob);

            $i++;
            //Transactions::where('id',$transaction->id)->update(['trans_status'=>'Failed', 'payment_status'=>'Server is unreachable']);
        }

        //Orders::where('order_number',$this->ordernumber)->update(['order_status'=>'Processed With Errors']);

    }

    public function pushTransactionsx()
    {
        $i = 1;
        //$this->ordernumber
        $transactions = DB::table('transactions')
            ->where('order_number', '791126')
            ->where('selected', 1)
            //->where('trans_status','In Progress')
            ->get();
        foreach ($transactions as $transaction) {

            $emailJob = (new ProcessIFT($transaction))->delay(Carbon::now()->addSeconds($i));
            dispatch($emailJob);

            $i++;
            //Transactions::where('id',$transaction->id)->update(['trans_status'=>'Failed', 'payment_status'=>'Server is unreachable']);
        }

        Orders::where('order_number', $this->ordernumber)->update(['order_status' => 'Processed With Errors']);

    }


}
