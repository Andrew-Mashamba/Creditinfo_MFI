<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;

class ActiveLoanByOfficer extends Component
{
    public $loanOfficer;

    function updatedLoanOfficer($value){
        $this->emit('loanOfficer',$value);
    }
    public function render()
    {
        return view('livewire.reports.active-loan-by-officer');
    }
}
