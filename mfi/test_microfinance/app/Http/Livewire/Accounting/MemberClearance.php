<?php

namespace App\Http\Livewire\Accounting;

use App\Models\Branches;
use App\Models\MembersModel;
use App\Models\Search;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

use App\Models\approvals;
use App\Models\TeamUser;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\Auth;

class MemberClearance extends Component
{
    public $member;
    public $approvals;
    public $term = "";
    public $declineEndMembership=false;
    public $end_membership_description;

    public $showAddUser = false;
    protected $listeners = ['refreshBranchesListComponent' => '$refresh'];



    public function  declineEndMembership($id){
        ClientsModel::where('id',$id)->update(['member_status'=>"ACTIVE",'supervising_officer'=>"FAIL",'end_membership_description'=>$this->end_membership_description]);
        session()->flash('message_endMembership','Process is completed');
        $this->declineEndMembershipModal();
    }

    public function declineEndMembershipModal(){
        if($this->declineEndMembership==false){
            $this->declineEndMembership=true;
        }else if($this->declineEndMembership==true){
            $this->declineEndMembership=false;
        }
    }

    public function reject ()
    {


        DB::table('members')->where('id',session()->get('viewMemberId_details'))->update(['member_status'=>'REJECTED']);
        session()->flash('message_fail','Successfully rejected');

    }

    public function accept ()
    {
        DB::table('members')->where('id',session()->get('viewMemberId_details'))->update(['member_status'=>'ACCEPTED']);
        // send for the approvals
        $approvals=new approvals();
        $approvals->sendApproval(session()->get('viewMemberId_details'),'createMember','has created new member','appove new member','102','');
        session()->flash('message','Successfully accepted');
    }

    public function close(){
        session()->forget('viewMemberId_details');
        $this->emitUp('financeViewMember',1);
    }


    public function exitMember($id){
        $approval=new approvals();
        $approval->sendApproval($id,'exitMember','request for end membership','has requested to end membership','102','');

        // update status

      $member= ClientsModel::where('id',$id)->first();
      if($member->supervising_officer=="FAIL"){

      }else{
          $member->update(['supervising_officer'=>auth()->user()->id,'member_status'=>"AWAITING DISBURSEMENT" ]);
          session()->flash('success_message','successfully  approved');
      }
    }

    public function exitMemberDecline($id){
          ClientsModel::where('id',$id)->update(['member_status'=>'ACTIVE']);
    }


    ////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////

    public function render()
    {
//        $institution = TeamUser::where('user_id', Auth::user()->id)->value('institution');
         $this->member=DB::table('members')->where('id',session()->get('viewMemberId_details'))->get();
        return view('livewire.accounting.member-clearance');
    }
}

