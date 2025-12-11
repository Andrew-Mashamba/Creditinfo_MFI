<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\approvals;
use App\Models\ChannelsModel;
use App\Models\Committee;
use App\Models\committeeMembership;
use App\Models\menus;
use App\Models\sub_menus;
use App\Models\TempPermissions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\departmentsList;
use App\Models\menu_list;
use App\Models\TeamUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoanCommitteeUser extends Component
{

    public $role;
    //public $permissions;
    public $moduleList;
    public $menusArray;
    public $loan_category;
    public $sub_menus;

    public $role_name;
    public $description;

    public $selected_Committee;


    public $roles;
    public $selected_role_data;
    public $permissionsData;

    public $name;
    public $user_list=[];

    public $users = [];


    protected $rules = [
        'user_list' => 'required|array|min:1',
        'description' => 'required|string|max:255',
        'loan_category'=>'required'
    ];
    /**
     * @var array|mixed
     */
    public $newPermissions;

    public function boot(){
        $this->users = User::get();

    }


    public function updatedSelectedrole(){


    }



    public function isChecked($id): bool
    {
        //dd(in_array($id, json_decode($this->permissionsData)));
        return in_array($id, json_decode($this->permissionsData));
    }


    public function togglePermission($id,$value)
    {





    }



    public function save(){

        $this->validate();

        $id=Committee::create([
            'name'=>$this->name,
            'description'=>$this->description,
            'loan_category'=>$this->loan_category
            ])->id;
        foreach($this->user_list as $user_id){

            if($user_id==0){
                continue;
            }

          CommitteeMembership::updateOrCreate(
                [
                    'committee_id' => $id,
                    'user_id' => $user_id,
                ],
                [
                    'membership_date' => now(),
                ]
            );

        }


        Session::flash('message', 'successfully ');
        Session::flash('alert-class', 'alert-success');

        $this->resetFields();

    }

    public function resetFields()
    {
        $this->user_list = '';
        $this->users = [];
        $this->name=null;

        $this->description = '';
    }



    public function render()
    {
        return view('livewire.users.loan-committee-user');
    }
}
