<?php

namespace App\Http\Livewire\Branches;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\BranchesModel;

use App\Models\approvals;
use App\Models\TeamUser;


class EditBranch extends Component
{

    public $membershipNumber;
    public $name;
    public $region;
    public $wilaya;
    public $email;
    public $status;


    public function boot(){
        $branchData = BranchesModel::select('membershipNumber', 'name', 'region', 'wilaya', 'email')
            ->where('id', '=', Session::get('branchIdInView'))
            ->get();
        foreach ($branchData as $branch){
            $this->membershipNumber=$branch->membershipNumber;
            $this->name=$branch->name;
            $this->region=$branch->region;
            $this->wilaya=$branch->wilaya;
            $this->email=$branch->email;
            $this->status=$branch->status;
        }
    }

    public function updateBranch(){

        $institution = 1;

        $data = [
            'membershipNumber' =>$this->membershipNumber,
            'name' =>$this->name,
            'region' =>$this->region,
            'wilaya' =>$this->wilaya,
            'email' =>$this->email
        ];

        $update_value = approvals::updateOrCreate(
            [
                'process_id' => Session::get('branchIdInView'),
                'user_id' => Auth::user()->id

                ],
            [
                'institution' => $institution,
                'process_name' => 'editBranch',
                'process_description' => 'has edited a branch',
                'approval_process_description' => 'has approved changes to a branch',
                'process_code' => '02',
                'process_id' => Session::get('branchIdInView'),
                'process_status' => 'Pending',
                'user_id'  => Auth::user()->id,
                'team_id'  => "",
                'edit_package'=> json_encode($data)
            ]
        );


    }



    public function back(){


        Session::put('branchNameInView', '');
        Session::put('branchIdInView', '');
        Session::put('showEditBranch', false);
        $this->emit('refreshBranchesListComponent');
    }



    public function render()
    {
        return view('livewire.branches.edit-branch');
    }
}
