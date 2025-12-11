<?php

namespace App\Http\Livewire\Procurement;

use App\Models\approvals;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PurchaseRequisition extends Component
{
    public $tab_id=1;
    public $createPurchaseModal=false;
    public $requisition_description;
    public $quantity;
    public $editPurchaseModalBoolean=false;
    public $selectedId;
    public $deletePurchaseModal=false;
    public $permission;
    public $password2;

    protected $listeners=[
        'actionPurchaseRequisition'=>'editPurchaseModal',
        'deletePurchaseRequisition'=>'deletePurchaseModal',
    ];


    public function render()
    {
        $this->totalPurchases=DB::table('purchases')
                              ->where('employeeId',auth()->user()->employeeId)->count();

        $this->totalCompletedPurchases=DB::table('purchases')
                              ->where('employeeId',auth()->user()->employeeId)
                           ->where('status','COMPLETED')->count();

        $this->rejectedPurchases=DB::table('purchases')
                              ->where('employeeId',auth()->user()->employeeId)
                           ->where('status','REJECTED')->count();

        return view('livewire.procurement.purchase-requisition');
    }

    public function subMenuItemClicked($id){
        $this->createPurchaseModal=true;
    }
    public function createPurchase(){
        $this->validate(['requisition_description'=>'required|string','quantity'=>'required|numeric']);

        // instatnce
        $purchases=new \App\Models\PurchaseRequisition();
        $purchases->requisition_description=$this->requisition_description;
        $purchases->employeeId=auth()->user()->employeeId;
        $purchases->status ="PENDING";
        $purchases->quantity=$this->quantity;
        $purchases->branchId=auth()->user()->branch;
        $purchases->save();

        //approvals
        $approvals=new approvals();
        $approvals->sendApproval($purchases->id,'createRequisition','has created purchase requisition','has created new requisition','102','');

        // session
        session()->flash('message','New requisition has been created successfully');

        $this->resetPurchase();

        $this->emit('refresh');

        sleep(2);
        $this->createPurchaseModal=false;

    }

    public function menuItemClicked($id){

        session()->put('assigned',$id);
        $this->tab_id=$id;
    }

    public function resetPurchase(){
        $this->requisition_description=null;

    }

    public function editPurchaseModal($id){
        $this->selectedId=$id;
        $purchases=\App\Models\PurchaseRequisition::where('id',$id)->first();
        $this->quantity=$purchases->quantity;
        $this->requisition_description=$purchases->requisition_description;
        $this->editPurchaseModalBoolean=true;
    }


    public function update(){
        if(\App\Models\PurchaseRequisition::where('id',$this->selectedId)->value('status')=="PENDING"){

            $this->validate(['requisition_description'=>'required|string','quantity'=>'required|numeric']);

            \App\Models\PurchaseRequisition::where('id',$this->selectedId)->update([
               'quantity'=>$this->quantity,
               'requisition_description' =>$this->requisition_description,
            ]);
            session()->flash('message','successfully updated');
            sleep(2);
            $this->emit('refresh');

            $this->editPurchaseModalBoolean=false;
        }
        else{
            session()->flash('message_fail','Requisition is onprogress you can not edit for now');
        }

    }

    public function deletePurchaseModal($id){
        $this->selectedId=$id;
       $this->deletePurchaseModal=true;
    }

    public function action(){

        $this->validate(['permission'=>'required']);

        if(\App\Models\PurchaseRequisition::where('id',$this->selectedId)->value('status')=="PENDING"){

            \App\Models\PurchaseRequisition::where('id',$this->selectedId)->update([
                'status'=>$this->permission,
            ]);
            session()->flash('message','successfully ');
            sleep(2);
            $this->emit('refresh');

            $this->deletePurchaseModal=false;
        }
        else{
            session()->flash('message_fail','You can not '.$this->permission);
        }

    }

}
