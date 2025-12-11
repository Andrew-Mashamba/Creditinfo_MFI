<?php

namespace App\Http\Livewire\Loans;

use Illuminate\Support\Facades\Session;
use Livewire\Component;

class TopUp extends Component
{


    protected $listeners = ['viewLoanDetails' => '$refresh'];


    public function render()
    {

        return view('livewire.loans.top-up');
    }
}
