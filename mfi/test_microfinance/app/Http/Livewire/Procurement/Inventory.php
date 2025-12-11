<?php

namespace App\Http\Livewire\Procurement;

use App\Models\approvals;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Inventory extends Component
{
    public $tab_id;
    public $createInventoryModal=false;
    public $editInventoryModalBoolean=false;
    public $deleteInventoryModal=false;

    // inventory attributes
    public $item_name;
    public $item_expiration_date;
    public $item_amount;
    public $item_description;
    public $selectedId;
    public $permission;

    protected $listeners=[
        'editInventoryModal'=>'editModal',
        'deleteInventoryModal'=>'deleteModal',
        'refreshComponent'=>'$refresh'
    ];

    protected $rules=[
        'item_amount'=>'required|numeric',
        'item_name'=>'required|string|min:3',
        'item_expiration_date'=>'required|date'
              ];


    public function deleteModal($id){
        $this->selectedId=$id;
        $this->deleteInventoryModal=true;
    }


    public function editModal($id){
        $this->selectedId=$id;
        $invertory= \App\Models\Inventory::where('id',$id)->first();
        $this->item_name=$invertory->item_name;
        $this->item_amount=$invertory->item_amount;
        $this->item_expiration_date=$invertory->item_expiration_date;
        $this->item_description=$invertory->item_description;
        $this->editInventoryModalBoolean=true;
    }

    public  function editInventory(){
        $this->validate();
        if(\App\Models\Inventory::where('id',$this->selectedId)->value('status')=="PENDING"){
            // be able to edit
            \App\Models\Inventory::where('id',$this->selectedId)->update([
                'item_amount'=>$this->item_amount,
                'item_name'=>$this->item_name,
                'item_expiration_date'=>$this->item_expiration_date,
                'item_description'=>$this->item_description,
            ]);

            $this->resetInventory();
            $this->emit('refreshComponent');
            session()->flash('message','successfully updated');

            sleep(3);

        }
        else{
            session()->flash('message_fail','You cannot edit for now');
        }
    }


    public function subMenuItemClicked(){
        $this->createInventoryModal=true;
    }

    public function render()
    {
        return view('livewire.procurement.inventory');
    }



    public function  createInventory(){
        $this->validate();
        // class object
        $inventory=new \App\Models\Inventory();
        $inventory->item_amount=$this->item_amount;
        $inventory->item_name=$this->item_name;
        $inventory->item_expiration_date=$this->item_expiration_date;
        $inventory->status="PENDING";
        $inventory->item_description=$this->item_description;
        $inventory->save();

        // approvals
        $approvals=new approvals();
        $approvals->sendApproval($inventory->id,'CreateInventory','has created Inventory','has created new inventory','102','');

        // session
        session()->flash('message','has created new Inventory');

        $this->resetInventory();

    }

    public function resetInventory(){
        $this->item_amount=null;
        $this->item_expiration_date=null;
        $this->item_name=null;
        $this->item_description=null;
    }

    public function action(){
        // pass approval for the contract;

        $this->validate(['permission'=>'required']);
        // approvals
        $approvals=new approvals();
        $approvals->sendApproval($this->selectedId,'deleteTender','  has requested to delete Tender','  has requested to delete Tender ','102','');
        session()->flash('message','Awaiting for approval');
        sleep(3);
        $this->emit('refreshComponent');
        $this->deleteInventoryModal=false;

    }

}
