<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InterestRatesStructureForLoans extends Component
{
    public $startDate;
    public $endDate;
    public $loanProducts = [];
    public $totalLoans = 0;
    public $totalAmount = 0;

    public function mount()
    {
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        // Get loans grouped by product and interest rate
        $this->loanProducts = DB::table('loans')
            ->join('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.sub_product_id')
            ->whereBetween('loans.created_at', [$this->startDate, $this->endDate])
            ->select(
                'loan_sub_products.sub_product_name',
                'loan_sub_products.interest_value',
                'loan_sub_products.interest_tenure',
                DB::raw('COUNT(*) as number_of_loans'),
                DB::raw('SUM(loans.principle) as total_amount')
            )
            ->groupBy('loan_sub_products.sub_product_id', 'loan_sub_products.sub_product_name', 'loan_sub_products.interest_value', 'loan_sub_products.interest_tenure')
            ->get();

        // Calculate totals
        $this->totalLoans = $this->loanProducts->sum('number_of_loans');
        $this->totalAmount = $this->loanProducts->sum('total_amount');
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
        return view('livewire.reports.interest-rates-structure-for-loans');
    }
} 