<?php

namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use Livewire\Component;

use App\Models\Clients;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;
use App\Models\LoansModel;

use App\Models\approvals;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;

class LoanApplication_m extends Component
{

    public $member;
    public $member_number = '';
    public $item = 100;
    public $product_number;
    public bool $viewAllClients=false;

    protected $listeners=['viewMemberLoans'=>'viewMemberLoan'];



    public function viewMemberLoan($client_number){
       // dd($client_number);
        $this->viewAllClients=true;
        //$this->emit('');
    }


    public function render()
    {
        $this->member = ClientsModel::where('client_number',$this->member_number)->get();
        return view('livewire.loans.loan-application');
    }


    public function back()
    {


        Session::put('memberToViewId', false);
        $this->emit('refreshClientsListComponent');
    }

    public function set()
    {

    if($this->member_number && $this->product_number){

        $accountNumber = str_pad(ClientsModel::where('client_number',$this->member_number)->value('branch'), 2, '0', STR_PAD_LEFT) . '104' . str_pad(ClientsModel::where('client_number',$this->member_number)->value('id'), 5, '0', STR_PAD_LEFT);
         ClientsModel::where('client_number',$this->member_number)->update(['client_status'=>"ON PROGRESS"]);

        $institution_id =auth()->user()->institution_id;
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
//        foreach ($currentUser as $User) {
//            $institution_id = $User->team_id;
//        }

        $loan_id = time();

//        $id = AccountsModel::create([
//            'account_use' => 'external',
//            'institution_number' => '999999',
//            'branch_number' => str_pad(ClientsModel::where('client_number',$this->member_number)->value('branch'), 2, '0', STR_PAD_LEFT),
//            'client_number' => $this->member_number,
//            'product_number' => $this->product_number,
//            'sub_product_number' => $this->product_number,
//            'account_name' => ClientsModel::where('client_number',$this->member_number)->value('first_name') . ' ' . ClientsModel::where('client_number',$this->member_number)->value('middle_name') . ' ' . ClientsModel::where('client_number',$this->member_number)->value('last_name'),
//            'account_number' => $accountNumber,
//
//        ])->id;

        $this->sendApproval($id,'has created a new loan account','09');

        LoansModel::create([

            'loan_id' => '',
            'loan_account_number' => '',
            'loan_sub_product' => $this->product_number,
            'client_number' => $this->member_number,
            'status' => 'PENDING',
            'institution_id' => $institution_id,
            'branch_id' =>auth()->user()->branch,
        ]);

        Session::put('loanEdited', $loan_id);

        $this->sendApproval($loan_id,'has created a new loan request','10');

        $this->member_number = null;
        $this->product_number = null;

        Session::flash('loan_message', 'The loan has been applied!');
        Session::flash('alert-class', 'alert-success');


    }else{

        Session::flash('loan_message', 'Error, fill all the required details!');
        Session::flash('alert-class', 'alert-warning');
    }

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
            'process_status' => 'PENDING',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

    }
}
