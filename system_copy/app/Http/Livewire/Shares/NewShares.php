<?php

namespace App\Http\Livewire\Shares;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\issured_shares;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\general_ledger;
use App\Models\ClientsModel;

use App\Models\TeamUser;
use App\Models\approvals;
use App\Services\TransactionPostingService;


class NewShares extends Component
{

    public $member;
    public $product;
    public $number_of_shares;
    public $linked_savings_account;
    public $account_number;
    public $balance;
    public $nominal_price =10000;
    public $accountSelected;
    public $sub_product_number;
    public $sharesAvailable;

    protected $rules = [
        'member'=> 'required|min:1',
        'product'=> 'required|min:1',
        'number_of_shares'=> 'required|min:1',
        'linked_savings_account'=> 'required|min:1',
        'account_number'=> 'required|min:1',
    ];

    public function setAccount($account){
        $this->accountSelected = $account;
        $this->product = AccountsModel::where('account_number', $account)->value('sub_product_number');
        dd($this->product_number);
    }

    public function save()
    {
        $institution_id='';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User){
            $institution_id=$User->team_id;
        }
        $this->validate();
        $transactionService = new TransactionPostingService();
        $transactionService->postTransaction([
            'first_account' => $this->linked_savings_account,
            'second_account' => $this->account_number,
            'amount' => $this->number_of_shares * $this->nominal_price,
            'narration' => 'Payment for shares issued',
        ]);
        $transactionService->postTransaction([
            'first_account' => $this->account_number,
            'second_account' => '5111108827',
            'amount' => $this->number_of_shares * $this->nominal_price,
            'narration' => 'Payment for shares issued',
        ]);
        $reference_number = time();


        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $this->linked_savings_account,
            'record_on_account_number_balance'=> $this->balance,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$this->linked_savings_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$this->linked_savings_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('sub_product_number'),
            'sender_id'=> $this->member,
            'beneficiary_id'=> $this->member,
            'sender_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'beneficiary_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=> $this->linked_savings_account,
            'beneficiary_account_number'=> $this->account_number,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'Payment for shares issued',
            'credit'=> 0,
            'debit'=> $this->number_of_shares * $this->nominal_price,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        //CREDIT RECORD SHARE ACCOUNT
        general_ledger::create([
            'record_on_account_number'=> $this->account_number,
            'record_on_account_number_balance'=> $this->balance,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$this->linked_savings_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$this->linked_savings_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('sub_product_number'),
            'sender_id'=> $this->member,
            'beneficiary_id'=> $this->member,
            'sender_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'beneficiary_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=> $this->linked_savings_account,
            'beneficiary_account_number'=> $this->account_number,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'Payment for shares issued',
            'credit'=> $this->number_of_shares * $this->nominal_price,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        //CREDIT RECORD GL
        general_ledger::create([
            'record_on_account_number'=> '5111108827',
            'record_on_account_number_balance'=> $this->balance,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$this->linked_savings_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$this->linked_savings_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number','5111108827')->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number','5111108827')->value('sub_product_number'),
            'sender_id'=> $this->member,
            'beneficiary_id'=> '999999',
            'sender_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'beneficiary_name'=> 'Organization',
            'sender_account_number'=> $this->linked_savings_account,
            'beneficiary_account_number'=> '5111108827',
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'Payment for shares issued',
            'credit'=> $this->number_of_shares * $this->nominal_price,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        issured_shares::create([
            'reference_number'=> $reference_number,
            'institution_id'=> $institution_id,
            'member'=> $this->member,
            'product'=> $this->product,
            'number_of_shares'=> $this->number_of_shares,
            'linked_savings_account'=> $this->linked_savings_account,
            'account_number'=> $this->account_number,
            'nominal_price'=> $this->nominal_price,
            'price'=> $this->number_of_shares * $this->nominal_price,
        ]);
        $this->sendApproval($reference_number,'New shares transaction','07');
        $this->resetData();

        Session::flash('message', 'Shares has been successfully issued!');
        Session::flash('alert-class', 'alert-success');

    }

    public function sendApproval($id,$msg,$code){

        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        approvals::create([
            'institution' => $institution,
            'process_name' => 'createBranch',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a transaction',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

    }

    public function resetData()
    {
        $this->member = '';
        $this->product = '';
        $this->number_of_shares = '';
        $this->linked_savings_account = '';
        $this->account_number = '';

    }

    public function back(){

        Session::put('memberNameInView', '');
        Session::put('memberIdInView', '');
        Session::put('showAddMember', false);
        $this->emit('refreshMembersListComponent');
    }





    public function render()
    {
        return view('livewire.shares.new-shares');
    }
}
