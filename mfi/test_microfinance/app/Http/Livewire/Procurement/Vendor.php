<?php

namespace App\Http\Livewire\Procurement;

use App\Models\approvals;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Vendor extends Component
{
    public $tab_id;
    public $createVendorModal=false;
    public $deleteVendor=false;
    public $editContractModalBoolean=false;
    public $organization_name;
    public $organization_tin_number;
    public $organization_license_number;
    public $organization_description;
    public $selectedId;
    public $editVendorModal=false;
    public $permission;
    public $email;

    protected $rules = [
        'organization_name' => 'required',
        'organization_tin_number' => 'required',
        'organization_license_number' => 'required',
        'organization_description' => 'required',
        'email'=>'required'
    ];

    protected $listeners=[
        'editVendor'=>'editModal',
        'deleteVendor'=>'deleteModal'
    ];


    public function editModal($id){
        $this->selectedId=$id;
        $vendor=\App\Models\Vendor::where('id',$id)->first();
        $this->organization_description=$vendor->organization_description;
        $this->organization_license_number=$vendor->organization_license_number;
        $this->organization_tin_number=$vendor->organization_tin_number;
        $this->organization_name=$vendor->organization_name;
        $this->email=$vendor->email;
        $this->editVendorModal=true;
    }


    public  function editVendor(){

        if(\App\Models\Vendor::where('id',$this->selectedId)->value('status')=="ACTIVE"){
            // can edit
            $edit_package=[
                'organization_description'=>$this->organization_description,
                'organization_license_number'=>$this->organization_license_number,
                'organization_name'=>$this->organization_name,
                'organization_tin_number'=>$this->organization_tin_number,
                'email'=>$this->email,
            ];

            $vendor_name=\App\Models\Vendor::where('id',$this->selectedId)->value('organization_name');

            $edit_package=json_encode($edit_package);



            session()->flash('message','Awaiting Approval');
            $approvals=new approvals();
            $approvals->sendApproval($this->selectedId,'editVendorInformation',auth()->user()->name.' has edited vendor name '.$vendor_name,' has edited Vendor','102',$edit_package);
            $this->vendorReset();
        }
        elseif(\App\Models\Vendor::where('id',$this->selectedId)->value('status')=="PENDING"){

            \App\Models\Vendor::where('id',$this->selectedId)->update([
                'organization_description'=>$this->organization_description,
                'organization_license_number'=>$this->organization_license_number,
                'organization_name'=>$this->organization_name,
                'organization_tin_number'=>$this->organization_tin_number,
                'email'=>$this->email,

            ]);
            session()->flash('message','Awaiting Approval');
            $this->vendorReset();


        }else{
            session()->flash('message_fail','You can not edit for now ');
        }

    }

    public function deleteModal($id){
        $this->selectedId=$id;
        $this->deleteVendor=true;

    }





    // vendor reset function
    public function vendorReset(){
        $this->organization_description=null;
        $this->organization_license_number=null;
        $this->organization_tin_number=null;
        $this->organization_name=null;
        $this->email=null;
    }


    public function createVendor(){
        $this->validate();
      $id=  \App\Models\Vendor::create([
            'status'=>"PENDING",
            'organization_name'=>$this->organization_name,
            'organization_tin_number'=>$this->organization_tin_number,
            'organization_license_number'=>$this->organization_license_number,
            'organization_description'=>$this->organization_description,
          'email'=>$this->email,
        ])->id;

        // approval
        $approvals=new approvals();
        $approvals->sendApproval($id,'registerVendor',auth()->user()->name.' has registered a new  vendor '.$this->organization_name,'has registered new vendor','102','');

        // session messages
        session()->flash('message','Successfully registered New vendor');

        $this->vendorReset();
    }

    public function subMenuItemClicked(){
        $this->createVendorModal=true;
    }


    public function render()
    {
        return view('livewire.procurement.vendor');
    }



    public function action(){
        // pass approval for the contract;

        $this->validate(['permission'=>'required']);

        $vendor_name=\App\Models\Vendor::where('id',$this->selectedId)->value('organization_name');

        // approvals
        $approvals=new approvals();
        $approvals->sendApproval($this->selectedId,'removeVendor',auth()->user()->name.' has requested to delete Vendor '.$vendor_name,'  has requested to delete Vendor ','102','');

        session()->flash('message','Awaiting for approval');
        $this->emit('refreshComponent');

    }
}
