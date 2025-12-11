<?php

namespace App\Http\Livewire\Loans;


use Illuminate\Support\Facades\Session;
use Livewire\Component;


class NewLoans extends Component
{

    protected $listeners = [
        "viewLoanDetails" => '$refresh',
        "viewLoanStages" => '$refresh'
    ];


    function boot(){
     //   session()->put('currentloanID',26);
    }


    public function render()
    {
      //  dd(session()->get('currentloanID'));


//        session::forget('viewClientDetails');
        return view('livewire.loans.new-loans');
    }
}
