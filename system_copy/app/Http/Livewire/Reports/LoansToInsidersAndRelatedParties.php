<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoansToInsidersAndRelatedParties extends Component
{
    public $startDate;
    public $endDate;
    public $subProduct;
    public $insiderLoans = [];
    public $totalLoans = 0;
    public $totalAmount = 0;
    public $subProducts = [];

    public function mount()
    {
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->loadSubProducts();
        $this->loadData();
    }

    public function loadSubProducts()
    {
        $this->subProducts = DB::table('loan_sub_products')
            ->select('sub_product_id', 'sub_product_name')
            ->orderBy('sub_product_name')
            ->get();
    }

    public function loadData()
    {
        // Get loans to insiders and related parties
        $query = DB::table('loans')
            ->join('clients', 'loans.client_number', '=', 'clients.client_number')
            ->join('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.sub_product_id')
            ->whereBetween('loans.created_at', [$this->startDate, $this->endDate]);

        if ($this->subProduct) {
            $query->where('loans.loan_sub_product', $this->subProduct);
        }

        $this->insiderLoans = $query->select(
                'loans.loan_account_number',
                'clients.first_name',
                'clients.last_name',
                'clients.middle_name',
                'clients.client_number',
                DB::raw("CONCAT(COALESCE(clients.first_name, ''), ' ', COALESCE(clients.middle_name, ''), ' ', COALESCE(clients.last_name, '')) as member_name"),
                'loan_sub_products.sub_product_name',
                'loans.principle',
                'loans.interest',
                'loans.tenure as term',
                'loans.created_at',
                'loans.status',
                DB::raw("'Employee' as relationship_type")
            )
            ->orderBy('loans.created_at', 'desc')
            ->get();

        // Calculate totals
        $this->totalLoans = $this->insiderLoans->count();
        $this->totalAmount = $this->insiderLoans->sum('principle');
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function updatedSubProduct()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.loans-to-insiders-and-related-parties');
    }
} 