<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Portfolio extends Component
{

    public $tab_id = '2';
    public $title = 'Clients list';

    public function setView($selected){
        $this->tab_id = $selected;
        session::put('loanStageId',$selected);
    }

    public function menuItemClicked($tabId){
        $this->tab_id = $tabId;
        if($tabId == '1'){
            $this->title = 'Loans list';
        }
        if($tabId == '2'){
            $this->title = 'Enter new LoansAccount details';
        }
    }

    public function render()
    {
        return view('livewire.dashboard.portfolio');
    }
}
