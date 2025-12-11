<?php

namespace App\Http\Livewire\Savings;


use Illuminate\Support\Facades\Config;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\issured_shares;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\ClientsModel;


use App\Models\approvals;
use App\Models\TeamUser;
use App\Services\TransactionPostingService;

class NewSavings extends Component
{

    public $member;
    public $product;
    public $number_of_shares;
    public $linked_savings_account;
    public $account_number;
    public $balance;
    public $deposit_charge_min_value;
    public $accountSelected;
    public $amount;
    public $notes;
    public $bank;
    public $reference_number;

    protected $rules = [
        'reference_number' => 'required|min:4',
        'bank' => 'required|min:1',
        'amount' => 'required|min:1',
        'account_number' => 'required|min:1',
    ];




    public function save()
    {

        $institution_id='';
       
        $this->validate();

        $mirror_account = AccountsModel::where('account_number',$this->bank)->value('mirror_account');
        $transactionService = new TransactionPostingService();
        $transactionService->postTransaction([
            'first_account' => $this->accountSelected,
            'second_account' => $this->bank,
            'amount' => $this->amount,
            'narration' => $this->notes,
        ]);
        $transactionService->postTransaction([
            'first_account' => $mirror_account,
            'second_account' => $this->accountSelected,
            'amount' => $this->amount,
            'narration' => $this->notes,
        ]);
        $transactionService->postTransaction([
            'first_account' => $this->bank,
            'second_account' => $mirror_account,
            'amount' => $this->amount,
            'narration' => $this->notes,
        ]);

        $reference_number = time();


        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $this->accountSelected,
            'record_on_account_number_balance'=> $savings_account_new_balance,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$mirror_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$mirror_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('sub_product_number'),
            'sender_id'=> '999999',
            'beneficiary_id'=> $this->member,
            'sender_name'=> 'Organization',
            'beneficiary_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=> $mirror_account,
            'beneficiary_account_number'=> $this->accountSelected,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> $this->notes,
            'credit'=> (double)$this->amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> '',
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
            'partner_bank'=> AccountsModel::where('account_number',$this->bank)->value('institution_number'),
            'partner_bank_name'=> AccountsModel::where('account_number',$this->bank)->value('account_name'),
            'partner_bank_account_number'=> $this->bank,
            'partner_bank_transaction_reference_number'=> $this->reference_number,
        ]);

        //CREDIT RECORD SHARE ACCOUNT
        general_ledger::create([
            'record_on_account_number'=> $this->bank,
            'record_on_account_number_balance'=> $partner_bank_account_new_balance,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$this->accountSelected)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->bank)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->bank)->value('sub_product_number'),
            'sender_id'=> $this->member,
            'beneficiary_id'=> AccountsModel::where('account_number',$this->bank)->value('institution_number'),
            'sender_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'beneficiary_name'=> AccountsModel::where('account_number',$this->bank)->value('account_name'),
            'sender_account_number'=> $this->accountSelected,
            'beneficiary_account_number'=> $this->bank,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> $this->notes,
            'credit'=> (double)$this->amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> '',
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
            'partner_bank'=> AccountsModel::where('account_number',$this->bank)->value('institution_number'),
            'partner_bank_name'=> AccountsModel::where('account_number',$this->bank)->value('account_name'),
            'partner_bank_account_number'=> $this->bank,
            'partner_bank_transaction_reference_number'=> $this->reference_number,
        ]);

        //CREDIT RECORD GL
        general_ledger::create([
            'record_on_account_number'=> $mirror_account,
            'record_on_account_number_balance'=> $savings_ledger_account_new_balance ,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$mirror_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$mirror_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('sub_product_number'),
            'sender_id'=> '999999',
            'beneficiary_id'=> $this->member,
            'sender_name'=> AccountsModel::where('account_number',$mirror_account)->value('account_name'),

            'beneficiary_name'=>  ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=> $mirror_account,
            'beneficiary_account_number'=> $this->accountSelected,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> $this->notes,
            'credit'=> 0,
            'debit'=> (double)$this->amount,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> '',
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
            'partner_bank'=> AccountsModel::where('account_number',$this->bank)->value('institution_number'),
            'partner_bank_name'=> AccountsModel::where('account_number',$this->bank)->value('account_name'),
            'partner_bank_account_number'=> $this->bank,
            'partner_bank_transaction_reference_number'=> $this->reference_number,
        ]);

        $this->sendApproval($reference_number,'New savings transaction','07');
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
        $this->accountSelected = '';
        $this->amount = '';
        $this->account_number = '';
        $this->notes = '';
        $this->bank = '';
        $this->reference_number = '';


    }

    public function back()
    {

        Session::put('memberNameInView', '');
        Session::put('memberIdInView', '');
        Session::put('showAddMember', false);
        $this->emit('refreshMembersListComponent');
    }

    public function setAccount($account){
        $this->accountSelected = $account;
    }

    public function render()
    {
        return view('livewire.savings.new-savings');
    }
}
