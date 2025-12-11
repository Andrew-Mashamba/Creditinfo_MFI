<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoansDisbursedForTheMonthEnded extends Component
{
    public $startDate;
    public $endDate;
    public $disbursedLoans = [];
    public $totalLoans = 0;
    public $totalAmount = 0;

    public function mount()
    {
        $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        // Get disbursed loans for the period
        $this->disbursedLoans = DB::table('loans')
            ->join('clients', 'loans.client_number', '=', 'clients.client_number')
            ->join('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.sub_product_id')
            ->whereBetween('loans.disbursement_date', [$this->startDate, $this->endDate])
            ->select(
                'loans.loan_account_number',
                'clients.first_name',
                'clients.last_name',
                'clients.middle_name',
                DB::raw("CONCAT(COALESCE(clients.first_name, ''), ' ', COALESCE(clients.middle_name, ''), ' ', COALESCE(clients.last_name, '')) as member_name"),
                'loan_sub_products.sub_product_name',
                'loans.principle',
                'loans.interest',
                'loans.tenure as term',
                'loans.disbursement_date'
            )
            ->orderBy('loans.disbursement_date', 'desc')
            ->get();

        // Calculate totals
        $this->totalLoans = $this->disbursedLoans->count();
        $this->totalAmount = $this->disbursedLoans->sum('principle');
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.loans-disbursed-for-the-month-ended');
    }
} 