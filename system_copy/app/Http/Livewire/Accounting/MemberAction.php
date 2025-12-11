<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MemberAction extends Component
{


    public function render()
    {
        return view('livewire.accounting.member-action');
    }
}
