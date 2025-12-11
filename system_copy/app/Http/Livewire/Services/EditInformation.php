<?php

namespace App\Http\Livewire\Services;
use App\Models\approvals;
use App\Models\Employee;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

use Livewire\Component;

class EditInformation extends Component
{ use WithFileUploads;

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
    public $employee_status;
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
    public $department;
    public $shares_ref_number;

    public $contributions;
    public $gross_salary;
    public $benefits;
    public $Employment_type;
    public $taxes;
    public $job_title;

    protected $listeners=['editEmployeeModal'=>'editModal'];

    protected $rules=[
        'first_name'=>'required',
        'middle_name'=>'required',
        'last_name'=>'required',
        'branch'=>'required',
        'phone_number'=>'required',

    ];



    public function editData(){

        $employee =Employee::where('id',session()->get('employeeIdForEdit'))->get();
//         $this->photo=$employee->photo;
//         $this->first_name=$employee->first_name;
//             $this->middle_name=$employee->middle_name;
//                 $this->last_name=$employee->last_name;
//                     $this->branch=$employee->branch;
    }

    public function back(){
        $this->emitUp('returnHome');
    }






    public function editEmployee(){

// Retrieve the employee record
$employee = Employee::where('id', auth()->user()->employeeId)->first();

if ($employee) {
    // Set the properties directly
    $this->first_name = $employee->first_name;
    $this->middle_name = $employee->middle_name;
    $this->last_name = $employee->last_name;
    $this->branch = $employee->branch;
    $this->email = $employee->email;
    $this->phone_number = $employee->phone;
    $this->department = $employee->department;
    $this->gross_salary = $employee->salary;
    $this->contributions = $employee->contribution;
    $this->taxes = $employee->taxes;
    $this->benefits = $employee->benefits;
    $this->Employment_type = $employee->Employment_type;
    $this->date_of_birth = $employee->date_of_birth;
    $this->gender = $employee->gender;
    $this->marital_status = $employee->marital_status;
    $this->employee_status = $employee->employee_status;
    $this->street = $employee->street;
    $this->address = $employee->address;
    $this->notes = $employee->notes;
    $this->place_of_birth = $employee->place_of_birth;
    $this->next_of_kin_name = $employee->next_of_kin_name;
    $this->next_of_kin_phone = $employee->next_of_kin_phone;
    $this->tin_number = $employee->tin_number;
    $this->nida_number = $employee->nida_number;
    $this->job_title = $employee->job_title;
}
   //

    }





    public function edit(){

        $this->validate();
        Employee::where('id',auth()->user()->employeeId)->update([
            'first_name'=> $this->first_name,
            'middle_name'=> $this->middle_name,
            'last_name'=> $this->last_name,
            'branch'=> $this->branch,
            'email'=> $this->email,
            'phone'=> $this->phone_number,
            'department'=> $this->department,
            'salary'=> $this->gross_salary,
            'contribution'=>$this->contributions,
            'taxes'=>$this->taxes,
            'benefits'=>$this->benefits,
            'Employment_type'=> $this->Employment_type,
            'date_of_birth'=> $this->date_of_birth,
            'gender'=> $this->gender,
            'marital_status'=> $this->marital_status,
            'employee_status'=>'PENDING',
            'street'=> $this->street,
            'address' => $this->address,
            'notes' => $this->notes,
            'place_of_birth' => $this->place_of_birth,
            'next_of_kin_name'=> $this->next_of_kin_name,
            'next_of_kin_phone'=> $this->next_of_kin_phone,
            'tin_number'=> $this->tin_number,
            'nida_number' => $this->nida_number,
            'job_title'=>$this->job_title,
        ]);



        session()->flash('message','successfully saved');

        // $approval=new approvals();
        // $approval->sendApproval(session()->get('employeeIdForEdit'),'editEmployee',session()->get('currentUser')->name.' has edit employee','has edited employee','102','');



    }


    public function resetData(){
        $this->nida_number='';
        $this->tin_number='';
        $this->next_of_kin_phone='';
        $this->first_name='';
    }



    public function render()
    {

        $this->editEmployee();
        // $this->editData();
         return view('livewire.services.edit-information');
    }
}
