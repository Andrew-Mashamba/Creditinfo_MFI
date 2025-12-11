<?php

namespace App\Http\Livewire\Procurement;

use Livewire\Component;

class Assets extends Component
{
    public $selectedId;
    public bool $viewPageStatus=false;


    protected $listeners=[
        'viewEmployeeList'=>'viewEmployeeList',
        'refreshAssetList'=>'$refresh'
    ];

    public  function viewEmployeeList($id){
        if($this->viewPageStatus==false){

        $this->selectedId=$id;
        $this->viewPageStatus=true;
        }
        else{
            $this->viewPageStatus=false;
            $this->emit('refreshAssetList');
        }

    }
    public function render()
    {
        return view('livewire.procurement.assets');
    }
}
