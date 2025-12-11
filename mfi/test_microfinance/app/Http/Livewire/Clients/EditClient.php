<?php

namespace App\Http\Livewire\Clients;

use Livewire\Component;
use App\Models\Clients;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use App\Models\approvals;
use App\Models\TeamUser;

class EditClient extends Component
{


    use WithFileUploads;

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

    public $member;

    public $confirmingUserDeletion = false;
    public $password;


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


    public function boot(){

        $memberData = Clients::where('id', Session::get('memberToEditId'))->get();
        foreach ($memberData as $member){


            $this->first_name=$member->first_name;
            $this->middle_name=$member->middle_name;
            $this->last_name=$member->last_name;
            $this->branch=$member->branch;

            $this->membership_type=$member->membership_type;
            $this->incorporation_number=$member->incorporation_number;
            $this->phone_number=$member->phone_number;
            $this->mobile_phone_number=$member->mobile_phone_number;
            $this->email=$member->email;
            $this->date_of_birth=$member->date_of_birth;
            $this->gender=$member->gender;
            $this->marital_status=$member->marital_status;
            $this->client_number=$member->client_number;
            $this->registration_date=$member->registration_date;
            $this->street=$member->street;
            $this->address=$member->address;
            $this->notes=$member->notes;
            $this->profile_photo_path=$member->profile_photo_path;
            $this->business_name=$member->business_name;

        }
    }

    public function back(){

        Session::put('memberToEditId', false);
        $this->emit('refreshClientsListComponent');
    }


    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024',// 1MB Max
        ]);
    }



    public function updateImage(){
        if($this->photo){
            $imageUrl = $this->photo->store('public', 'photos');
            Clients::where('id',Session::get('memberToEditId'))->update(['profile_photo_path'=>$imageUrl]);
        }

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
            'member_status' => 'Pending'
        ])->id;

        $this->sendApproval($id,'has edited member information','12');

        $this->resetData();

        Session::flash('message', 'A new user has been successfully created!');
        Session::flash('alert-class', 'alert-success');

    }


    public function updateNames()
    {
        $this->validate([
            'first_name'=> 'required|min:3',
            'last_name'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'first_name'=>$this->first_name,
                'last_name'=>$this->last_name
                ]);
    }

    public function updateIncorporationNumber()
    {
        $this->validate([
            'incorporation_number'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'incorporation_number'=>$this->incorporation_number
            ]);
    }

    public function updatePhoneNumber()
    {
        $this->validate([
            'phone_number'=> 'required|min:9',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'phone_number'=>$this->phone_number
            ]);
    }

    public function updateMobilePhoneNumber()
    {
        $this->validate([
            'mobile_phone_number'=> 'required|min:9',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'mobile_phone_number'=>$this->mobile_phone_number
            ]);
    }

    public function updateEmail()
    {
        $this->validate([
            'email'=> 'email',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'email'=>$this->email
            ]);
    }

    public function updateDateOfBirth()
    {
        $this->validate([
            'date_of_birth'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'date_of_birth'=>$this->date_of_birth
            ]);
    }

    public function updateGender()
    {
        $this->validate([
            'gender'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'gender'=>$this->gender
            ]);
    }
    public function updateMaritalStatus()
    {
        $this->validate([
            'marital_status'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'marital_status'=>$this->marital_status
            ]);
    }

    public function updateBranch()
    {
        $this->validate([
            'branch'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'branch'=>$this->branch
            ]);
    }

    public function updateClientshipType()
    {
        $this->validate([
            'membership_type'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'membership_type'=>$this->membership_type
            ]);
    }

    public function updateStreet()
    {
        $this->validate([
            'street'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'street'=>$this->street
            ]);
    }

    public function updateAddress()
    {
        $this->validate([
            'address'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'address'=>$this->address
            ]);
    }

    public function updateNotes()
    {
        $this->validate([
            'notes'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'notes'=>$this->notes
            ]);
    }

    public function updateBusinessName()
    {
        $this->validate([
            'business_name'=> 'required|min:3',
        ]);
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'business_name'=>$this->business_name
            ]);
    }

    public function confirmUserDeletion(){
        $this->confirmingUserDeletion = true;
    }

    public function suspendClient(){
        $this->confirmingUserDeletion = false;
        Clients::where('id',Session::get('memberToEditId'))
            ->update([
                'member_status'=>'Suspended'
            ]);
    }


    public function render()
    {
        return view('livewire.clients.edit-member');
    }
}
