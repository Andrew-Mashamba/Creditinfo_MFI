<?php

namespace App\Http\Livewire\Procurement;

use App\Models\approvals;
use App\Models\ContractManagement;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Contract extends Component
{
    use WithFileUploads;

    public $editContractModalBoolean=false;
    public $deleteContractModal=false;
    public $tab_id;
    public $contract_name;
    public $contract_description;
    public  $contract_file_path;
    public $createContractModal=false;
    public $vendorId;
    public $endDate;
    public $startDate;
    public $permission;



    protected $listeners=[
      'editVendor'=>'editVendorModal',
        'actionModal'=>'contractActionModal',
    ];


    public function editVendorModal(){
        $this->editContractModalBoolean=true;
    }

    public function contractActionModal($id){
        $this->selectedId=$id;
        $this->deleteContractModal=true;

    }




    public function render()
    {
        return view('livewire.procurement.contract');
    }

    public function  subMenuItemClicked ($id){
        $this->createContractModal=true;

    }



    // contract add method
    public function createContract(){
        $this->validate(['contract_description'=>'required|string|min:3',
            'contract_name'=>'required|string',
            'contract_file_path'=>'required'
//                             'contract_file_path'=>'required|max:100000|mimes:doc,docx,pdf'
        ]);



        // Generate a unique filename
        $filename = time() . '_' . $this->contract_file_path->getClientOriginalName();

        // Store the file in the 'public' disk under the 'uploads' directory
        $this->contract_file_path->storeAs('procurementContract', $filename, 'public');

        // Save the file path
        $this->contract_file_path = 'procurementContract/' . $filename;



        // contract vaalidation
        $contract=new ContractManagement();
        $contract->contract_name=$this->contract_name;
        $contract->contract_description=$this->contract_description;
        $contract->contract_file_path=$this->contract_file_path;
        $contract->vendorId=$this->vendorId;
        $contract->startDate=$this->startDate;
        $contract->endDate=$this->endDate;
        $contract->status="PENDING";
        $contract->save();

        // approvals
        $approvals=new approvals();
        $approvals->sendApproval($contract->id,'createdContract','has create Contract','new contract has created','102','');
        // message
        session()->flash('message',"Has been created successfully");
        // reset data
        $this->resetContract();
    }


    public function resetContract(){
        $this->endDate=null;
        $this->startDate=null;
        $this->vendorId=null;
        $this->contract_description=null;
        $this->contract_file_path=null;
        $this->contract_name=null;
    }


    public function action(){
        // pass approval for the contract;

        $this->validate(['permission'=>'required']);

        // approvals
        $approvals=new approvals();
        $approvals->sendApproval($this->selectedId,'endContract','  has requested to end Contract','  has requested to end Contract','102','');

     session()->flash('message','Awaiting for approval');

    }
}
