<?php

namespace App\Http\Livewire\Setup;

use Livewire\Component;
use Illuminate\Support\Facades\Session;


class Setup extends Component
{


    public $tab_id = '1';
    public $title = 'Add Institution';


    public function menuItemClicked($tabId)
    {
        $this->tab_id = $tabId;
        if ($tabId == '1') {
            $this->title = 'Add Institution';

        }
        if ($tabId == '2') {
            $this->title = 'Institutions List';
        }
        if ($tabId == '3') {
            $this->title = 'Loans Committee';
        }

        Session::put('currentInstitutionID', null);
    }


    public function render()
    {
        return view('livewire.setup.setup');
    }
}


