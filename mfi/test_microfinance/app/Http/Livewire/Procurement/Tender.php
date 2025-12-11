<?php

namespace App\Http\Livewire\Procurement;

use App\Models\approvals;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Tender extends Component
{

    public $tab_id;
    public $createTenderModal=false;
    public $editTenderModalBoolean=false;
    public $deleteTenderModal=false;

    // tender attributes
    public $tender_name;
    public $tender_description;
    public $tender_number;
    public $selectedId;
    public $permission;

    protected $listeners=['editTenderModal'=>'editModal',
                      'deleteTenderModal'=>'deleteModal',
                     'refreshComponent'=>'$refresh'
    ];


    public function deleteModal($id){
        $this->selectedId=$id;
        $this->deleteTenderModal=true;
    }

    public function editModal($id){
        $this->selectedId=$id;
        $tenders=\App\Models\Tender::where('id',$id)->first();
        $this->tender_name= $tenders->tender_name;
        $this->tender_description=$tenders->tender_description;

        $this->editTenderModalBoolean=true;
    }


    public function editedTender(){
        $this->validate(['tender_name'=>'required|string|min:3',
            'tender_description'=>'required|string|min:3',
        ]);
        if(\App\Models\Tender::where('id',$this->selectedId)->value('status')=="ACTIVE"){
               session()->flash('message_fail','You cannot edit for now');
        }
        else{
            \App\Models\Tender::where('id',$this->selectedId)->update([
                'tender_name'=>$this->tender_name,
                'tender_description'=>$this->tender_description,
            ]);
            session()->flash('message','successfully');
            $approvals=new approvals();
            $approvals->sendApproval($this->selectedId,'editTender','has edited tender',' tender has  been edited ','102','');

      $this->reseteTenderData();
        }


    }



    public function subMenuItemClicked(){
        $this->createTenderModal=true;

    }

    public function render()
    {
        return view('livewire.procurement.tender');
    }


    public function createTender(){
        $this->validate(['tender_name'=>'required|string|min:3',
            'tender_description'=>'required|string|min:3',
        ]);

        // class object
        $tenders=new \App\Models\Tender();
        $tenders->tender_number= 'TN'.random_int(0000000001,9999999999);
        $tenders->tender_description=$this->tender_description;
        $tenders->tender_name=$this->tender_name;
        $tenders->status="PENDING";
        $tenders->save();
        // uprovale

        $approvals=new approvals();
        $approvals->sendApproval($tenders->id,'CreatedTender','has created tender','new tender has created','102','');

        // session message
        session()->flash('message','Has been created successfully');

        $this->reseteTenderData();

    }


    public function reseteTenderData(){
        $this->tender_number=null;
        $this->tender_description=null;
        $this->tender_name=null;
    }

    public function action(){
        // pass approval for the contract;

        $this->validate(['permission'=>'required']);

        // approvals
        $approvals=new approvals();
        $approvals->sendApproval($this->selectedId,'deleteTender','  has requested to delete Tender','  has requested to delete Tender ','102','');

        session()->flash('message','Awaiting for approval');
        $this->emit('refreshComponent');

    }


}
