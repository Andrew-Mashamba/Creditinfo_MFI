<?php

namespace App\Http\Livewire\Setup;


use Livewire\Component;


use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\Clients;
use App\Models\AccountsModel;
use App\Models\institutions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\loan_images;
use App\Models\TeamUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\departmentsList;

class AddInstitution extends Component
{


    use WithFileUploads;


    public $photo;
    public $business_category;
    public $business_type;
    public $business_licence_number;
    public $business_tin_number;
    public $business_inventory;
    public $cash_at_hand;
    public $daily_sales;
    public $loan;
    public $admin;

    public $cost_of_goods_sold;
    public $institution_id;
    public $name;
    public $region;
    public $wilaya;
    public $phone_number;
    public $email;
    public $status;
    public $aname;



    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024',// 1MB Max
        ]);
    }

    public function save()
    {

        $pin = rand(100000,1000000);
        $id = User::create([
            'name' => $this->aname,
            'email' => $this->email,
            'password' => Hash::make($pin),
        ])->id;

        institutions::create([
            'institution_id'=>$this->currentInstitutionID,
            'name'=>$this->name,
            'region'=>$this->region,
            'wilaya'=>$this->wilaya,
            'phone_number'=>$this->phone_number,
            'email'=>$this->email,
            'admin'=>$id

        ]);


        $department = departmentsList::create([
            'institution' => $this->currentInstitutionID,
            'department_name' => 'IT',
            'modules' => '["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13"]'
        ])->id;

        TeamUser::create([
            'institution' => $this->currentInstitutionID,
            'department' => $department,
            'user_id' => $id,
            'role' => 'admin'
        ])->id;

        $this->aname = null;
        $this->email = null;
        $this->currentInstitutionID = null;
        $this->name = null;
        $this->region = null;
        $this->wilaya = null;
        $this->phone_number = null;

        dd($pin);

    }

    public function boot()
    {
        $institutions = institutions::get();
    }

    public function render()
    {

        $lastx = institutions::latest()->get();
        if ($lastx){
            $last = $lastx->value('institution_id');
        }else{
            $last = null;
        }
        if($last){
            $this->currentInstitutionID = $last + 1;
        }else{
            $this->currentInstitutionID = 1001;
        }


        return view('livewire.setup.add-institution');
    }

    public function saveImage(){

        $imageUrl = $this->photo->store('avatars', 'public');

        institutions::where('institution_id',$this->currentInstitutionID)->update([
            'imgUrl'=>$imageUrl

        ]);

        $this->photo = null;
    }
}
