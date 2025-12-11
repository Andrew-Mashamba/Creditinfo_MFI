<?php

namespace App\Http\Livewire\Clients;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\Clients;
use App\Models\AccountsModel;
use App\Models\Branches;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\approvals;
use App\Models\TeamUser;

class AddClient extends Component
{


    use WithFileUploads;

    public $photo;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $branch;
    public $branches;
    public $registering_officer;
    public $supervising_officer;
    public $approving_officer;
    public $membership_type = 'Individual';
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
    public $sub_product_number_shares ='1199';
    public $sub_product_number_savings='1279';
    public $sub_product_number_deposits='1321';
    public $place_of_birth;
    public $next_of_kin_name;
    public $next_of_kin_phone;
    public $tin_number;
    public $nida_number;
    public $ref_number;
    public $shares_ref_number;




    protected $listeners=['employesEditModal'=>'editEmployee'];

    public function boot(){
        $this->branch = '1000';
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024',// 1MB Max
        ]);
    }

    public function save()
    {


        if($this->membership_type == "Individual"){
            $this->validate([
                'first_name'=> 'required|min:3',
                'last_name'=> 'required|min:3',
                'membership_type'=> 'required|min:3',

                'date_of_birth'=> 'required|min:3',
                'gender'=> 'required|min:2',
                'marital_status'=> 'required|min:3',
                'place_of_birth'=> 'required|min:3',
                'street'=> 'required|min:3',
                'address' => 'required|min:3',
                'notes' => 'required|min:3',
                'branch' => 'required|min:1',

                'next_of_kin_name'=> 'required|min:3',
                'next_of_kin_phone'=> 'required|min:3',
                'tin_number'=> 'required|min:3',
                'nida_number' => 'required|min:3',
                'ref_number' => 'required|min:3',
                'shares_ref_number' => 'required|min:1',
            ]);
        }else{
            $this->validate([

                'membership_type'=> 'required|min:3',
                'incorporation_number'=> 'required|min:3|unique:clients',


                'street'=> 'required|min:3',
                'address' => 'required|min:3',
                'notes' => 'required|min:3',
                'business_name' => 'required|min:3',
                'branch' => 'required|min:1',
            ]);
        }


        $institution_id='';
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User){
            $institution_id=$User->team_id;
        }

        if($this->photo){
            $imageUrl = $this->photo->store('avatars', 'public');
        }




        $last_member= Clients::latest()->first();
        if($last_member){
            $last_member_id = $last_member->id;
            $last_member_id = $last_member_id  + 1;
            $newClientId = Session::get('branchIdInView').str_pad($last_member_id, 5, '0', STR_PAD_LEFT);

        }else{
            $last_member_id = 0;
            $last_member_id = $last_member_id  + 1;
            $newClientId = Session::get('branchIdInView').str_pad($last_member_id, 5, '0', STR_PAD_LEFT);

        }

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
            'client_number'=> $newClientId,
            'registration_date'=> $this->registration_date,
            'street'=> $this->street,
            'address' => $this->address,
            'notes' => $this->notes,
            'business_name' => $this->business_name,
            'profile_photo_path' => $imageUrl,
            'registering_officer' => $id,
            'institution_id' => $institution_id,
            'place_of_birth' => $this->place_of_birth,
            'branch_id'=> Session::get('branchIdInView'),

            'next_of_kin_name'=> $this->next_of_kin_name,
            'next_of_kin_phone'=> $this->next_of_kin_phone,
            'tin_number'=> $this->tin_number,
            'nida_number' => $this->nida_number,
            'ref_number' => $this->ref_number,
            'shares_ref_number' => $this->shares_ref_number,
        ])->id;

        $this->sendApproval($id,'has registered a new member','12');

        $idx =  AccountsModel::create([
            'account_use' => 'external',
            'institution_number'=> '1001',
            'branch_number'=> str_pad($this->branch, 2, '0', STR_PAD_LEFT),
            'member_number'=> $newClientId,
            'product_number'=> '11',
            'sub_product_number'=> $this->sub_product_number_shares,
            'account_name'=> $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'account_number'=> str_pad($this->branch, 2, '0', STR_PAD_LEFT).'111'.str_pad($last_member_id, 5, '0', STR_PAD_LEFT),

        ])->id;
        //$this->sendApproval($idx,'has created a new savings account','09');

        $idy =  AccountsModel::create([
            'account_use' => 'external',
            'institution_number'=> '1001',
            'branch_number'=> str_pad($this->branch, 2, '0', STR_PAD_LEFT),
            'member_number'=> $newClientId,
            'product_number'=> '12',
            'sub_product_number'=> $this->sub_product_number_savings,
            'account_name'=> $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'account_number'=> str_pad($this->branch, 2, '0', STR_PAD_LEFT).'121'.str_pad($last_member_id, 5, '0', STR_PAD_LEFT),

        ])->id;
        //$this->sendApproval($idy,'has created a new amana account','09');


        $idz =  AccountsModel::create([
            'account_use' => 'external',
            'institution_number'=> '1001',
            'branch_number'=> str_pad($this->branch, 2, '0', STR_PAD_LEFT),
            'member_number'=> $newClientId,
            'product_number'=> '13',
            'sub_product_number'=> $this->sub_product_number_deposits,
            'account_name'=> $this->first_name.' '.$this->middle_name.' '.$this->last_name,
            'account_number'=> str_pad($this->branch, 2, '0', STR_PAD_LEFT).'131'.str_pad($last_member_id, 5, '0', STR_PAD_LEFT),

        ])->id;
        //$this->sendApproval($idz,'has created a new deposit account','09');


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

        Session::put('memberNameInView', '');
        Session::put('memberIdInView', '');
        Session::put('showAddClient', false);
        $this->emit('refreshClientsListComponent');
    }

    public function sendApproval($id,$msg,$code){

        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        approvals::create([
            'institution' => $institution,
            'process_name' => 'createClient',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a transaction',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

    }



    public function render()
    {
        $this->branches = Branches::all();
        return view('livewire.clients.add-member');
    }
}
