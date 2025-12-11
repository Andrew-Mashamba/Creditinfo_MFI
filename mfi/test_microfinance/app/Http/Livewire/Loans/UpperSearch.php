<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;

class UpperSearch extends Component
{

    protected $listeners = [
        "refreshClientInfoPage" => '$refresh',
        "viewLoanStages" => '$refresh'
    ];

    public function render()
    {
        return view('livewire.loans.upper-search');
    }
}
