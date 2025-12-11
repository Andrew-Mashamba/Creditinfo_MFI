<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\LoanReport as Report;

class LoanReport extends Component
{

    public $client_type;

    public function render()
    {
        return view('livewire.reports.loan-report');
    }


    public function exportReport(){

        $ids=DB::table('loans')->pluck('id')->toArray();
        return Excel::download(new Report($ids ),'LoanReport.xlsx');


    }
}
