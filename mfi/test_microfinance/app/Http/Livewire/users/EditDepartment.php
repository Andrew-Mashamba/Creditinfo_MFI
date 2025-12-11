<?php

namespace App\Http\Livewire\Users;

use App\Models\approvals;
use App\Models\ChannelsModel;
use App\Models\menus;
use App\Models\sub_menus;
use App\Models\TempPermissions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Models\departmentsList;
use App\Models\menu_list;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;

class EditDepartment extends Component
{
    public $role;
    //public $permissions;
    public $moduleList;
    public $menusArray;
    public $sub_menus;

    public $role_name;
    public $description;

    public $selectedrole;


    public $roles;
    public $selected_role_data;
    public $permissionsData;

    public $permissions = [];


    protected $rules = [
        'permissions' => 'required|array|min:1',
        'role_name' => 'required|string|max:255',
        'description' => 'required|string|max:255',
    ];
    /**
     * @var array|mixed
     */
    public $newPermissions;

    public function boot(){

        $this->moduleList = menus::get();
        $this->roles = departmentsList::get();
        $this -> newPermissions = [];
    }


    public function updatedSelectedrole(){
        $this->selected_role_data = departmentsList::where('id',$this->selectedrole)->get();
        foreach ($this->selected_role_data as $data){
            $this->permissionsData = $data->permissions;
            $this->role_name = $data->department_name;
            $this->description = $data->description;
        }





        $array = json_decode($this->permissionsData, true);


        // dd($array);

        foreach ($array as $element) {
            $var = 't'.$element;

            if(TempPermissions::where('role_id',$this->selectedrole)->get()->count() > 0){

                TempPermissions::where('role_id',$this->selectedrole)->update([
                    't'.$element => true
                ]);
                //dd($value);
            }else{

                $temp = new TempPermissions;
                $temp->role_id = $this->selectedrole;
                $temp-> $var = true;
                $temp->save();

            }

        }


        $permissionIds = json_decode($this->permissionsData, true);
        $allPermissions = sub_menus::all();

        //dd($allPermissions);



        foreach ($allPermissions as $permission) {
            $this->permissions[$permission->id] = in_array($permission->id, $permissionIds);
        }
        //dd($this->permissions);
    }



    public function isChecked($id): bool
    {
        //dd(in_array($id, json_decode($this->permissionsData)));
        return in_array($id, json_decode($this->permissionsData));
    }


    public function togglePermission($id,$value)
    {

        if($value == 1){
            $value = false;
        }else{
            $value = true;
        }

        if(TempPermissions::where('role_id',$this->selectedrole)->get()->count() > 0){



            TempPermissions::where('role_id',$this->selectedrole)->update([
                't'.$id => $value
            ]);
            //dd($value);
        }else{
            $var = 't'.$id;
            $temp = new TempPermissions;
            $temp->role_id = $this->selectedrole;
            $temp-> $var = $value;
            $temp->save();

        }


    }



    public function save(){
        $tableRow = TempPermissions::get()
            ->where('role_id', $this->selectedrole)
            ->first();

        $stringArray = [];
        foreach($tableRow->toArray() as $key => $value) {

            if($key !== 'id' && $key !== 'updated_at' && $key !== 'created_at' && $key !== 'role_id') {
                $key = str_replace("t", "", $key);
                if($value ==1){
                    $stringArray[] = $key;
                }

            }
        }


        $data = [
            'status' => 'ACTIVE',
            'department_name' => $this->role_name,
            'permissions' => json_encode($stringArray),
            'description' => $this->description
        ];

        $update_value = approvals::updateOrCreate([

            'institution' => '',
            'process_name' => 'editRole',
            'process_description' => ' A request to edit a Role - '.$this->role_name,
            'approval_process_description' => '',
            'process_code' => '22',
            'process_id' => $this->selectedrole,
            'process_status' => 'PENDING',
            'approval_status' => 'PENDING',
            'user_id'  => Auth::user()->id,
            'team_id'  => '',
            'edit_package'=> json_encode($data),

        ] );
        Session::flash('message', 'Awaiting approval');
        Session::flash('alert-class', 'alert-success');

        $this->resetFields();

    }

    public function resetFields()
    {
        $this->status = 'ACTIVE';
        $this->department_name = '';
        $this->permissions = [];
        $this->description = '';
        $this->selectedrole = null;
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        return view('livewire.users.edit-department');
    }
}
