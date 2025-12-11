<?php

namespace App\Http\Livewire\Accounting;

use App\Models\Branches;
use App\Models\ClientsModel;
use App\Models\Search;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

use App\Models\approvals;
use App\Models\TeamUser;
use App\Models\Clients;
use Illuminate\Support\Facades\Auth;

class ClientClearance extends Component
{
    public $member;
    public $approvals;
    public $term = "";
    public $declineEndClientship=false;
    public $end_membership_description;

    public $showAddUser = false;
    protected $listeners = ['refreshBranchesListComponent' => '$refresh'];


    public function  declineEndClientship($id){
        Clients::where('id',$id)->update(['member_status'=>"ACTIVE",'supervising_officer'=>"FAIL",'end_membership_description'=>$this->end_membership_description]);
        session()->flash('message_endClientship','Process is completed');
        $this->declineEndClientshipModal();
    }

    public function declineEndClientshipModal(){
        if($this->declineEndClientship==false){
            $this->declineEndClientship=true;
        }else if($this->declineEndClientship==true){
            $this->declineEndClientship=false;
        }
    }

    public function reject ()
    {


        DB::table('clients')->where('id',session()->get('viewClientId_details'))->update(['member_status'=>'REJECTED']);
        session()->flash('message_fail','Successfully rejected');

    }

    public function accept ()
    {
        DB::table('clients')->where('id',session()->get('viewClientId_details'))->update(['member_status'=>'ACCEPTED']);
        // send for the approvals
        $approvals=new approvals();
        $approvals->sendApproval(session()->get('viewClientId_details'),'createClient','has created new member','appove new member','102','');
        session()->flash('message','Successfully accepted');
    }

    public function close(){
        session()->forget('viewClientId_details');
        $this->emitUp('financeViewClient',1);
    }


    public function exitClient($id){
        $approval=new approvals();
        $approval->sendApproval($id,'exitClient','request for end membership','has requested to end membership','102','');

        // update status

      $member= Clients::where('id',$id)->first();
      if($member->supervising_officer=="FAIL"){

      }else{
          $member->update(['supervising_officer'=>auth()->user()->id,'member_status'=>"AWAITING DISBURSEMENT" ]);
          session()->flash('success_message','successfully  approved');
      }
    }

    public function exitClientDecline($id){
          dd($id);
          Clients::where('id',$id)->update(['member_status'=>'ACTIVE']);
    }


    ////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////

    public function render()
    {
//        $institution = TeamUser::where('user_id', Auth::user()->id)->value('institution');
         $this->member=DB::table('clients')->where('id',session()->get('viewClientId_details'))->get();
        return view('livewire.accounting.client-clearance');
    }
}

