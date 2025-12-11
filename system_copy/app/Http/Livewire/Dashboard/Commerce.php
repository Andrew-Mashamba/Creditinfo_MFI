<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\ClientsModel;
use App\Models\LoansModel;
use Livewire\Component;

class Commerce extends Component
{
    public $item;
    public $newClients;
    public $TotalNewClientsAmount;
    public $onprogress;
    public $totalOnprogress;
    public $awaitingForApproval;
    public $totalAwaitingApproval;
    public $awaitingDis;
    public $totalAwaitingDis;
    public $activeLoan;
    public $totalActive;



    public function visit($id){

        $this->item=$id;
    }



    public function render()
    {
        $this->activeLoan=LoansModel::where('status',"ACTIVE")->count();
        $this->totalActive=LoansModel::where('status',"ACTIVE")->sum('principle');
            
            
        $this->awaitingForApproval=LoansModel::where('status',"Awaiting Approval")->count();
        $this->totalAwaitingApproval=LoansModel::where('status',"Awaiting Approval")->sum('principle');
        $this->totalAwaitingDis=LoansModel::where('status',"AWAITING DISBURSEMENT")->sum('principle');
        $this->awaitingDis=LoansModel::where('status',"AWAITING DISBURSEMENT")->count();

        $this->TotalNewClientsAmount=ClientsModel::where('client_status',"NEW CLIENT")->sum('amount');
        $this->newClients=ClientsModel::where('client_status',"NEW CLIENT")->count();
        $this->onprogress=LoansModel::where('status','ONPROGRESS')->count();
        $this->totalOnprogress=LoansModel::where('status',"ONPROGRESS")->sum('principle');

        return view('livewire.dashboard.commerce');
    }
}
