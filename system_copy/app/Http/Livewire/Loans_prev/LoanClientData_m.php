<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;


use App\Models\Clients;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;
use App\Models\LoansModel;

use App\Models\approvals;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;

class LoanClientData_m extends Component
{

    public $member;
    public $member_number = '';
    public $item = 100;
    public $product_number;

    public function render()
    {
        $this->member = Clients::where('client_number',$this->member_number)->get();
        return view('livewire.loans.loan-member-data');
    }

    public function back()
    {


        Session::put('memberToViewId', false);
        $this->emit('refreshClientsListComponent');
    }

    public function set()
    {

        $institution_id = '';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
            $institution_id = $User->team_id;
        }

        $accountNumber = str_pad(Clients::where('client_number',$this->member_number)->value('branch'), 2, '0', STR_PAD_LEFT) . '104' . str_pad(Clients::where('client_number',$this->member_number)->value('id'), 5, '0', STR_PAD_LEFT);
        $loan_id = time();

        $id = AccountsModel::create([
            'account_use' => 'external',
            'institution_number' => '999999',
            'branch_number' => str_pad(Clients::where('client_number',$this->member_number)->value('branch'), 2, '0', STR_PAD_LEFT),
            'member_number' => $this->member_number,
            'product_number' => $this->product_number,
            'sub_product_number' => $this->product_number,
            'account_name' => Clients::where('client_number',$this->member_number)->value('first_name') . ' ' . Clients::where('client_number',$this->member_number)->value('middle_name') . ' ' . Clients::where('client_number',$this->member_number)->value('last_name'),
            'account_number' => $accountNumber,

        ])->id;

        $this->sendApproval($id,'has created a new loan account','09');

        LoansModel::create([

            'loan_id' => $loan_id,
            'loan_account_number' => $accountNumber,
            'loan_sub_product' => $this->product_number,
            'member_number' => $this->member_number,
            'status' => 'Pending',
            'institution_id' => $institution_id,
            'branch_id' => Clients::where('client_number',$this->member_number)->value('branch'),
        ]);

        Session::put('loanEdited', $loan_id);
        $this->sendApproval($loan_id,'has created a new loan request','10');
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

}
