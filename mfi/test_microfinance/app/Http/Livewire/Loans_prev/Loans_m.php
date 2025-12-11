<?php

namespace App\Http\Livewire\Loans;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Session;



use App\Models\LoansModel;
use App\Models\approvals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;


use App\Models\AccountsModel;
use App\Models\Clients;
use App\Models\TeamUser;


use Livewire\WithFileUploads;
use App\Models\issured_loans;



use App\Models\general_ledger;
use App\Services\TransactionPostingService;


class Loans_m extends Component
{


    public $tab_id = '2';
    public $title = 'Loans list';

    public $selected;
    public $sortByBranch;
    public $filterLoanOfficer;
    public $activeLoansCount;
    public $inactiveLoansCount;
    public $showCreateNewLoansAccount;
    public $name;
    public $region;
    public $wilaya;
    public $membershipNumber;
    public $parentLoansAccount;
    public $showDeleteLoansAccount;
    public $LoansAccountSelected;
    public $showEditLoansAccount;
    public $pendingLoansAccount;
    public $LoansList;
    public $pendingLoansAccountname;
    public $LoansAccount;
    public $showAddLoansAccount;


    public $email;
    public $Loansstatus;
    public $permission = 'BLOCKED';
    public $password;

    public $member;
    public $product;
    public $number_of_loans;
    public $linked_loans_account;
    public $account_number;
    public $balance;
    public $nominal_price;
    public $showIssueNewLoans;




    public $accountSelected;
    public $sub_product_number;
    public $loansAvailable;

    protected $rules = [
        'member'=> 'required|min:1',
        'product'=> 'required|min:1',
        'number_of_loans'=> 'required|min:1',
        'linked_loans_account'=> 'required|min:1',
        'account_number'=> 'required|min:1',
    ];



    protected $listeners = [
        'showUsersList' => 'showUsersList',
        'blockLoansAccount' => 'blockLoansAccountModal',
        'editLoansAccount' => 'editLoansAccountModal',
        'refresh'=>'$refresh'
        ];



        public function setAccount($account){
            $this->accountSelected = $account;
            $this->product = AccountsModel::where('account_number', $account)->value('sub_product_number');
            //dd($this->product_number);
        }



    public function showAddLoansAccountModal($selected){
        $randomNumber = rand(9000, 9999);
        $this->membershipNumber= str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
        $this->selected = $selected;
        $this->showAddLoansAccount = true;
    }


    public function showIssueNewLoansModal($selected){
        $randomNumber = rand(9000, 9999);
        $this->membershipNumber= str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
        $this->selected = $selected;
        $this->showIssueNewLoans = true;
    }

    public function setView($selected){

            session()->forget('currentloanClient');
//            session::put('currentloanClient',null);
        $this->tab_id = $selected;
         session::put('loanStageId',$selected);


//        dd(session::get('loanStageId'));
    }


    public function updatedLoansAccount(){
        $LoansAccountData = LoansModel::select('membershipNumber', 'name', 'region', 'wilaya', 'email')
        ->where('id', '=', $this->LoansAccount)
        ->get();
    foreach ($LoansAccountData as $LoansAccount){
        $this->membershipNumber=$LoansAccount->membershipNumber;
        $this->name=$LoansAccount->name;
        $this->region=$LoansAccount->region;
        $this->wilaya=$LoansAccount->wilaya;
        $this->email=$LoansAccount->email;
        $this->status=$LoansAccount->status;
    }
    }



    public function updateLoansAccount(){

        $user = auth()->user();


        $data = [
            'membershipNumber' =>$this->membershipNumber,
            'name' =>$this->name,
            'region' =>$this->region,
            'wilaya' =>$this->wilaya,
            'email' =>$this->email
        ];

        $update_value = approvals::updateOrCreate(
            [
                'process_id' => $this->LoansAccount,
                'user_id' => Auth::user()->id

                ],
            [
                'institution' => $this->LoansAccount,
                'process_name' => 'editLoansAccount',
                'process_description' => 'has edited a LoansAccount',
                'approval_process_description' => 'has approved changes to a LoansAccount',
                'process_code' => '02',
                'process_id' => $this->LoansAccount,
                'process_status' => 'PENDING',
                'user_id'  => Auth::user()->id,
                'team_id'  => $this->LoansAccount,
                'edit_package'=> json_encode($data)
            ]
        );
        Session::flash('message', 'Awaiting approval');
        Session::flash('alert-class', 'alert-success');
        $this->resetData();
        $this->showAddLoansAccount = false;
    }



    public function addLoansAccount(){
        $branch = Clients::where('client_number',$this->member)->value('branch');
        $id = Clients::where('client_number', $this->member)->value('id');

        $id = AccountsModel::create([
            'account_use' => 'external',
            'institution_number'=> '1001',
            'branch_number'=> str_pad($branch, 2, '0', STR_PAD_LEFT),
            'member_number'=> $this->member,
            'product_number'=> '11',
            'sub_product_number'=> $this->product,
            'account_name'=> Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
            'account_number'=> str_pad($branch, 2, '0', STR_PAD_LEFT).'111'.str_pad($id, 5, '0', STR_PAD_LEFT),

        ])->id;


        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        approvals::create([
            'institution' => $institution,
            'process_name' => 'createAccount',
            'process_description' => 'has added a new account',
            'approval_process_description' => 'has approved a new account',
            'process_code' => '04',
            'process_id' => $id,
            'process_status' => 'PENDING',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

    }



    public function issueLoans()
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
            'first_account' => $this->linked_loans_account,
            'second_account' => $this->account_number,
            'amount' => $this->number_of_loans * $this->nominal_price,
            'narration' => 'Payment for loans issued',
        ]);
        $transactionService->postTransaction([
            'first_account' => $this->account_number,
            'second_account' => '5111108827',
            'amount' => $this->number_of_loans * $this->nominal_price,
            'narration' => 'Payment for loans issued',
        ]);

        $reference_number = time();


        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $this->linked_loans_account,
            'record_on_account_number_balance'=> $this->number_of_loans * $this->nominal_price,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$this->linked_loans_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$this->linked_loans_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('sub_product_number'),
            'sender_id'=> $this->member,
            'beneficiary_id'=> $this->member,
            'sender_name'=> Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
            'beneficiary_name'=> Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=> $this->linked_loans_account,
            'beneficiary_account_number'=> $this->account_number,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'Payment for loans issued',
            'credit'=> 0,
            'debit'=> $this->number_of_loans * $this->nominal_price,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        //CREDIT RECORD LOAN ACCOUNT
        general_ledger::create([
            'record_on_account_number'=> $this->account_number,
            'record_on_account_number_balance'=> $this->number_of_loans * $this->nominal_price,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$this->linked_loans_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$this->linked_loans_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->account_number)->value('sub_product_number'),
            'sender_id'=> $this->member,
            'beneficiary_id'=> $this->member,
            'sender_name'=> Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
            'beneficiary_name'=> Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=> $this->linked_loans_account,
            'beneficiary_account_number'=> $this->account_number,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'Payment for loans issued',
            'credit'=> $this->number_of_loans * $this->nominal_price,
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
            'record_on_account_number_balance'=> $this->number_of_loans * $this->nominal_price,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number',$this->linked_loans_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$this->linked_loans_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number','5111108827')->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number','5111108827')->value('sub_product_number'),
            'sender_id'=> $this->member,
            'beneficiary_id'=> '999999',
            'sender_name'=> Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
            'beneficiary_name'=> 'Organization',
            'sender_account_number'=> $this->linked_loans_account,
            'beneficiary_account_number'=> '5111108827',
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'Payment for loans issued',
            'credit'=> $this->number_of_loans * $this->nominal_price,
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

        issured_loans::create([
            'reference_number'=> $reference_number,
            'institution_id'=> $institution_id,
            'member'=> $this->member,
            'product'=> $this->product,
            'number_of_loans'=> $this->number_of_loans,
            'linked_loans_account'=> $this->linked_loans_account,
            'account_number'=> $this->account_number,
            'nominal_price'=> $this->nominal_price,
            'price'=> $this->number_of_loans * $this->nominal_price,
        ]);
        $this->sendApproval($reference_number,'New loans transaction','07');
        $this->resetData();

        Session::flash('message', 'Loans has been successfully issued!');
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
        $this->number_of_loans = '';
        $this->linked_loans_account = '';
        $this->account_number = '';

    }





    public function menuItemClicked($tabId){
        $this->tab_id = $tabId;
        if($tabId == '1'){
            $this->title = 'Loans list';
        }
        if($tabId == '2'){
            $this->title = 'Enter new LoansAccount details';
        }
    }


    public function createNewLoansAccount()
    {


        $this->showCreateNewLoansAccount = true;
    }

    public function blockLoansAccountModal($id)
    {

        $this->showDeleteLoansAccount = true;
        $this->LoansAccountSelected = $id;
    }

    public function editLoansAccountModal($id)
    {
        $this->showEditLoansAccount = true;
        $this->pendingLoansAccount = $id;
        $this->LoansAccount = $id;
        $this->pendingLoansAccountname = LoansModel::where('id',$id)->value('name');
        $this->updatedLoansAccount();

    }

        public function closeModal(){
            $this->showCreateNewLoansAccount = false;
            $this->showDeleteLoansAccount = false;
            $this->showEditLoansAccount = false;
        }

        public function confirmPassword(): void
        {
            // Check if password matches for logged-in user
            if (Hash::check($this->password, auth()->user()->password)) {
                //dd('password matches');
                $this->delete();
            } else {
                //dd('password does not match');
                Session::flash('message', 'This password does not match our records');
                Session::flash('alert-class', 'alert-warning');
            }
            $this->resetPassword();


        }

        public function resetPassword(): void
        {
            $this->password = null;
        }

    public function delete():void
    {
        $user = User::where('id',$this->userSelected)->first();
        $action = '';
        if ($user) {

            if($this->permission == 'BLOCKED'){
                $action = 'blockUser';
            }
            if($this->permission == 'ACTIVE'){
                $action = 'activateUser';
            }
            if($this->permission == 'DELETED'){
                $action = 'deleteUser';
            }

            $update_value = approvals::updateOrCreate(
                [
                    'process_id' => $this->userSelected,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => '',
                    'process_name' => $action,
                    'process_description' => $this->permission.' user - '.$user->name,
                    'approval_process_description' => '',
                    'process_code' => '29',
                    'process_id' => $this->userSelected,
                    'process_status' => $this->permission,
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => '',
                    'edit_package'=> null
                ]
            );


            // Delete the record
            //$node->delete();
            // Add your logic here for successful deletion
            Session::flash('message', 'Awaiting approval');
            Session::flash('alert-class', 'alert-success');

            $this->closeModal();
            $this->render();


        } else {
            // Handle case where record was not found
            // Add your logic here
            Session::flash('message', 'Node error');
            Session::flash('alert-class', 'alert-warning');
        }

    }




    public function render()
    {
        $this->activeLoansCount = LoansModel::where('status', 'ACTIVE')->count();
        $this->inactiveLoansCount = LoansModel::where('status', 'PENDING')->count();
        $this->LoansList = LoansModel::get();
        return view('livewire.loans.loans');
    }

    public function updatedSortByBranch($value)
    {
        $this->emit('sortByBranchChanged', $value);
        session::put('sortByBranch',$value);
        $this->emit('refresh');

    }

    public function updatedFilterLoanOfficer($value)
    {
        $this->emit('filterLoanOfficerChanged', $value);
        session::put('filterLoanOfficer',$value);
        $this->emit('refresh');

    }

}
