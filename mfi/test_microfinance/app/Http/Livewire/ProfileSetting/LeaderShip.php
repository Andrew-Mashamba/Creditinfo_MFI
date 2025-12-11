<?php

namespace App\Http\Livewire\ProfileSetting;


use App\Models\approvals;
use App\Models\LeaderShipModel;

use App\Models\LoansModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads; // Import the trait
use Illuminate\Support\Facades\Storage;

class LeaderShip extends Component
{
    use WithFileUploads; // Add the trait

    public $register_new_saccos_leader=false;
    public $full_name;
    public $position;
    public $endDate;
    public $is_signatory;
    public $approval_option;
    public $leaderDescriptions;
    public $image;
    public $member_number;
    public $photo;
    public $profile_photo_path;
    public $activeSection = 'leadership';
    public $sidebarSearch = '';
    public $selectedCommitteeId = null;
    public $selectedMeetingId = null;
    public $showNewCommitteeModal = false;
    public $showNewLeaderModal = false;
    public $showScheduleMeetingModal = false;
    public $searchTerm = '';



    protected $rules=[
        'member_number'=>'required',
        'position'=>'required',
//        'leaderShip.startDate'=>'required',
        'endDate'=>'required',
           // 'approval_option'=>'required',
        'leaderDescriptions'=>'required',
//        'image'=>'required|mimes:png,jpg'
    ];


    public function resetLeaderData(){
        $this->full_name=null;
        $this->endDate=null;
        $this->leaderDescriptions=null;
        $this->approval_option=null;

    }

    public function newLeaderModal(){
        if($this->register_new_saccos_leader==false){
            $this->register_new_saccos_leader=true;
        }
    }

    public function openNewLeaderModal(){
        $this->showNewLeaderModal = true;
        $this->register_new_saccos_leader = true;
    }

    public function openNewCommitteeModal(){
        $this->showNewCommitteeModal = true;
    }

    public function openScheduleMeetingModal(){
        $this->showScheduleMeetingModal = true;
    }

    public function closeModals(){
        $this->showNewCommitteeModal = false;
        $this->showNewLeaderModal = false;
        $this->showScheduleMeetingModal = false;
        $this->register_new_saccos_leader = false;
    }

    public function exportData(){
        // Export functionality
        session()->flash('message', 'Export functionality will be implemented soon.');
    }

    public function refreshData(){
        $this->emit('refreshComponent');
        session()->flash('message', 'Data refreshed successfully.');
    }



    public function save(){
        $this->validate();
        $path='';

        if ($this->photo) {
            $imagePath = $this->photo->store('photos/product', 'public');
            $path = Storage::url($imagePath);
        }

      $id=  LeaderShipModel::create([
            'full_name'=> strtoupper($this->full_name),
            'position'=>strtoupper($this->position),
             'endDate'=>$this->endDate,
            'institution_id'=>auth()->user()->institution_id,
           // 'approval_option'=>$this->approval_option,
            'is_signatory'=>$this->is_signatory,
            'member_number'=>$this->member_number,
           'image'=>$path,
        ])->id;

        approvals::updateOrCreate(
            [
                'process_id' => $id,
                'user_id' => auth()->user()->id,
                'process_name' => 'addLeader',
                'institution' => ' ',
                'process_description' => auth()->user()->name.' has added new leader - '. $this->full_name,
                'approval_process_description' => auth()->user()->name.' has added new leader - '. $this->full_name,
                'process_code' => '24',
                'process_status' => 'Pending',
                'approval_status' => 'PENDING',
                'team_id'  => '',
                'edit_package'=> '',
            ]
        );

        session()->flash('message','successfully');
        $this->resetLeaderData();


    }







    public function render()
    {
        if($this->member_number){
            $member=DB::table('clients')->where('client_number',$this->member_number)->first();
            if($member){
                $this->full_name=$member->first_name.' '.$member->middle_name.' '.$member->last_name;

            }
        }
        return view('livewire.profile-setting.leader-ship');
    }
}
