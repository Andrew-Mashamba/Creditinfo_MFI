<?php

namespace App\Http\Livewire\Procurement;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Invoice extends Component
{


    public function render()
    {
        return view('livewire.procurement.invoice');
    }
}
