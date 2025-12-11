<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProfitAndLossStatement extends Component
{


    public function render()
    {
        $this->income_accounts = DB::table('income_accounts')->get();
        $this->expense_accounts = DB::table('expense_accounts')->get();
        return view('livewire.accounting.profit-and-loss-statement');
    }
}
