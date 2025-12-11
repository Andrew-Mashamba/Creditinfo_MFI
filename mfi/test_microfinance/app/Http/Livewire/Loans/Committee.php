<?php

namespace App\Http\Livewire\Loans;

use App\Models\approvals;
use App\Models\LoansModel;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Committee;

class LoanCommittee extends Component
{

    protected $listeners = ['currentloanID' => '$refresh'];


    public $showLoanDetails;


    public $activeTab = 'member';

    public function showTab($tab)
    {
        $this->activeTab = $tab;
    }


    public function close(){
        Session::put('currentloanID',null);
        Session::put('currentloanClient',null);
    }

    public function delete($id){
        LoansModel::where('id',$id)->delete();
    }

    public function commit($id){
        LoansModel::where('id',$id)->update(['status'=>'ONPROGRESS']);
    }

    public function approve($id)
    {
        $loan = LoansModel::findOrFail($id);
        $committee = Committee::where('loan_category', $loan->loan_category)->first();

        if (!$committee) {
            session()->flash('error', 'No committee found for this loan category.');
            return;
        }

        if ($committee->hasEnoughApprovals($id)) {
            $loan->update(['status' => 'PENDING']);
            $this->sendApproval($id, 'has requested for approval of a new loan', '11');
        } else {
            session()->flash('error', 'Not enough approvals to proceed.');
        }
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

    public function render()
    {
        return view('livewire.loans.committee');
    }
}
