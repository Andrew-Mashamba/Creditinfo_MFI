<?php

namespace App\Http\Livewire\Payments;

use App\Models\Orders;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use JetBrains\PhpStorm\NoReturn;
use Livewire\Component;

class EditData extends Component
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
    public $modal= false;
    public $deleteOrderModal= false;
    public $orderToDeleteId = '';
    public $entryToDeleteId = '';
    public $deleteEntryModal = '';
    public $order_status = '';
    public $exceptions = false;

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $branch_id='';

        $this->accounts = DB::table('accounts')->get();

        return view('livewire.payments.edit-data');
    }

    public function activateSaveButton(){
        $this->showSaveButton = true;
    }

     public function createOrder(){
        $ref = time();
        $branch_id='';
        $name='';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User){
            $branch_id=$User->team_id;
        }
        $currentBranch = DB::table('teams')->where('id', $branch_id)->get();
        foreach ($currentBranch as $branch){
            $name=$branch->name;
        }

        //Session::get('editTransactionID')
        //Session::put('editTransactionID', $value);

        Transactions::where('id',Session::get('editTransactionID'))
            ->update([


                    'source_branch_account_number' => $this->sourceAccount,
                    'destination_branch_account_number' => $this->accountNumber,
                    'description' => $this->description,
                    'transaction_amount' => $this->amount,
                    'credit' => '',
                    'debit' => $this->amount,
                    'phone_number' => $this->mobileNumber,
                    'institution' => $this->selectedDestination,
                    'institution_name' => $this->selectedBank.$this->selectedMno

            ]);


        $this->emit('closeEditEntryModal');
    }


}
