<?php

namespace App\Http\Livewire\Branches;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\Clients;
use App\Models\approvals;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddUser extends Component
{

    //use WithFileUploads;

    public $photo;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $branch;
    public $registering_officer;
    public $supervising_officer;
    public $approving_officer;
    public $membership_type;
    public $incorporation_number;
    public $phone_number;
    public $mobile_phone_number;
    public $email;
    public $date_of_birth;
    public $gender;
    public $marital_status;
    public $membership_number;
    public $registration_date;
    public $street;
    public $address;
    public $notes;
    public $current_team_id;
    public $profile_photo_path;
    public $branch_id;
    public $business_name;

    protected $rules = [
        'first_name'=> 'required|min:3',
        'last_name'=> 'required|min:3',
        'membership_type'=> 'required|min:3',
        'incorporation_number'=> 'required|min:3|unique:clients',
        'mobile_phone_number'=> 'required|min:9|unique:clients',
        'date_of_birth'=> 'required|min:3',
        'gender'=> 'required|min:2',
        'marital_status'=> 'required|min:3',
        'client_number'=> 'required|min:3|unique:clients',
        'street'=> 'required|min:3',
        'address' => 'required|min:3',
        'notes' => 'required|min:3',
        'business_name' => 'required|min:3',
    ];


    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024',// 1MB Max
        ]);
    }

    public function save()
    {

        $institution_id='';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User){
            $institution_id=$User->team_id;
        }

        $this->validate();
        $imageUrl = $this->photo->store('avatars', 'public');

        $id =  Clients::create([

            'first_name'=> $this->first_name,
            'middle_name'=> $this->middle_name,
            'last_name'=> $this->last_name,
            'branch'=> $this->branch,
            'membership_type'=> $this->membership_type,
            'incorporation_number'=> $this->incorporation_number,
            'phone_number'=> $this->phone_number,
            'mobile_phone_number'=> $this->mobile_phone_number,
            'email' => $this->email,
            'date_of_birth'=> $this->date_of_birth,
            'gender'=> $this->gender,
            'marital_status'=> $this->marital_status,
            'client_number'=> $this->client_number,
            'registration_date'=> $this->registration_date,
            'street'=> $this->street,
            'address' => $this->address,
            'notes' => $this->notes,
            'business_name' => $this->business_name,
            'profile_photo_path' => $imageUrl,
            'registering_officer' => $id,
            'institution_id' => $institution_id,
            'branch_id'=> Session::get('branchIdInView'),
        ])->id;



        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        approvals::create([
            'institution' => $institution,
            'process_name' => 'createClient',
            'process_description' => 'has added a new member',
            'approval_process_description' => 'has approved a new member',
            'process_code' => '02',
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);



        $this->resetData();

        Session::flash('message', 'A new user has been successfully created!');
        Session::flash('alert-class', 'alert-success');

    }

    public function resetData()
    {
        $this->name = '';
        $this->first_name = '';
            $this->middle_name = '';
            $this->last_name = '';
            $this->branch = '';
            $this->membership_type = '';
            $this->incorporation_number = '';
            $this->phone_number = '';
            $this->mobile_phone_number = '';
            $this->email = '';
            $this->date_of_birth = '';
            $this->gender = '';
            $this->marital_status = '';
            $this->client_number = '';
            $this->registration_date = '';
            $this->street = '';
            $this->address = '';
           $this->notes = '';
            $this->business_name = '';
            $this->photo = null;

    }

    public function back(){

        Session::put('branchNameInView', '');
        Session::put('branchIdInView', '');
        Session::put('showAddUser', false);
        $this->emit('refreshBranchesListComponent');
    }


    public function render()
    {
        return view('livewire.branches.add-user');
    }
}
