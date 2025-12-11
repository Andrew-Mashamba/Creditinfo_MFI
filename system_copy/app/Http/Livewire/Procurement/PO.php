<?php

namespace App\Http\Livewire\Procurement;

use App\Mail\InstitutionRegistrationConfirmationMail;
use App\Models\PurchaseRequisition;
use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Random;

class PO extends Component
{
    public $approveModalBoolean=false;
    public $selectedId;
    public $selected=1;
    public $vendorId=[];
    public $pendingPo;
    public $approvedPO;
    public $completed;
    public $endDate;
    public bool $viewPoBoolean=false;

    protected $listeners=[
      'approvePo'=>'approveModal',
        'refreshPo'=>'$refresh',
        'viewPo'=>'viewBlade'
    ];



    public function viewBlade($id){
        $this->viewPoBoolean=true;


    }

    public function boot()
    {

        $this->selected=1;


    }

    public function approveModal($id){
        $this->selectedId=$id;
        $this->approveModalBoolean=true;

    }


    public function setView($id){
        $this->selected=$id;
        $this->emit('orderStatus',$id);


    }


    public function approve(){


        $this->validate(['vendorId'=>'required']);
        if(PurchaseRequisition::where('id',$this->selectedId)->value('status')=="PENDING"){
            PurchaseRequisition::where('id',$this->selectedId)->update([
                'status'=>"ACTIVE",
                'vendorId'=>$this->vendorId,
            ]);
            session()->flash('message','successfully approved and assigned');
            $this->emit('refreshPo');

            // send invitation email to vendors
            foreach ($this->vendorId as $vendor){

                // generate random passcode
                $passcode=Rand(100000,999999);
                DB::table('vendor_submissions')->insert([
                   'password'=>$passcode,
                    'vendorId'=>$vendor,
                    'endDate'=>$this->endDate,
                     'requestId'=>$this->selectedId,

                ]);

              $email=   Vendor::where('id',$vendor)->value('email');
              $email='percyegno@gmail.com';

                Mail::to($email)->send(new InstitutionRegistrationConfirmationMail('request for quotations'));
            }

            sleep(3);
            $this->approveModalBoolean=false;
        }else{
            session()->flash('message_fail',"You cannot edit vendor for now");
        }


    }

    public function render()
    {
        $this->pendingPo=PurchaseRequisition::where('status',"PENDING")->count();
        $this->approvedPO=PurchaseRequisition::where('status',"ACTIVE")->count();
        $this->completed=PurchaseRequisition::where('status',"COMPLETED")->count();

        return view('livewire.procurement.p-o');
    }
}
