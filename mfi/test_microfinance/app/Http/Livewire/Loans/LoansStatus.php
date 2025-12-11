<?php

namespace App\Http\Livewire\Loans;

use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

use App\Models\approvals;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;

class LoansStatus extends Component
{

    public $showLoanDetails;
    public bool $account_delete_modal=false;




    protected $listeners = ['currentloanID' => '$refresh',
        'deleteLoan'=>'deleteLoanModalDisplay',
        'refreshClientInfoPage'=>'$refresh',

    ];

    public function boot(){
        $userDepartment = auth()->user()->department;
        $department_name = DB::table('departments')->where('id', $userDepartment)->value('department_name');
    }


    public $activeTab = 'client';

    public function showTab($tab)
    {
        $this->activeTab = $tab;
    }


    public function render()
    {

        if(DB::table('collaterals')->where('loan_id',session('currentloanID'))->whereNot('collateral_type','N/A')->exists()){
            session()->put('hasBusinessInformation',"YES");
        }else{
            session()->put('hasBusinessInformation',"NO");
        }


        return view('livewire.loans.loans-status');
    }
    public function close(){
        Session::put('currentloanID',null);
        Session::put('currentloanClient',null);
        $this->emit('viewClientDetails');
    }


    public function delete($id){
        LoansModel::where('id',$id)->delete();
    }

    public function commit($id){
        LoansModel::where('id',$id)->update(['status'=>'ONPROGRESS']);
    }

    public function approve($id){
        LoansModel::where('id',$id)->update(['status'=>'PENDING']);
        $this->sendApproval($id,'has requested for approval of a new loan','11');
    }

    public function reject($id){
        LoansModel::where('id',$id)->update(['status'=>'REJECTED']);
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


    public function deleteLoanModalDisplay(){
        if($this->account_delete_modal==false){
            $this->account_delete_modal=True;
        }
        else{
            session()->forget('loanAccountID');
            $this->account_delete_modal=false;

        }
//        LoansModel::where('id',$id)->delete();
//        session::flash('message',"The loan has been deleted successfully ");
    }


    public  function deleteLoan(){

         DB::table('loans')->where('id',session()->get('loanAccountID'))->update(['status'=>'DELETE']);
        $this->emit('deleteLoan');
        session::flash('message','Has been deleted successfully');

    }



}
