<?php

namespace App\Http\Livewire\Branches;

use Livewire\Component;
use App\Models\BranchesModel;
use App\Models\approvals;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddBranch extends Component
{

    public $name;
    public $region;
    public $wilaya;
    public $membershipNumber;
    public $parentBranch;

    protected $rules = [
        'name' => 'required|min:3',
        'region' => 'required|min:3',
        'wilaya' => 'required|min:3',
        'membershipNumber' => 'required|min:3|unique:branches',
        //'parentBranch' => 'required|min:3',
    ];

    public function submit()
    {

        $institution_id='';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User){
            $institution_id=$User->team_id;
        }

        $this->validate();

        // Execution doesn't reach here if validation fails.

        $id =  BranchesModel::create([
            'name' => $this->name,
            'region' => $this->region,
            'wilaya' => $this->wilaya,
            'membershipNumber' => $this->membershipNumber,
            'parentBranch' => $this->parentBranch,
            'institution_id' => 1, // Default institution ID
            'status'  => 'Pending'
        ])->id;

        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        approvals::create([
     
            'process_name' => 'createBranch',
            'process_description' => 'has created a new branch',
            'approval_process_description' => 'has approved a new branch',
            'process_code' => '01',
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);



        $this->resetData();

        Session::flash('message', 'A new branch has been successfully created!');
        Session::flash('alert-class', 'alert-success');
    }

    public function resetData()
    {
        $this->name = '';
        $this->region = '';
        $this->wilaya = '';
        $this->membershipNumber = '';
        $this->parentBranch = '';

    }


    public function render()
    {
        return view('livewire.branches.add-branch');
    }
}
