<?php

namespace App\Http\Livewire\TellerManagement;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Teller extends Component
{
    public $teller_tab = 1;


    public function menu_sub_button($id)
    {
        $this->teller_tab = $id;
    }


    public function render()
    {
        return view('livewire.teller-management.teller');
    }

}
