<?php

namespace App\Http\Livewire\ActiveLoan\ArrearsDashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\LoansModel;

class ByAmount extends Component
{
    // Amount ranges in arrears
    public $arrears0to10k = 0;
    public $arrears10kto50k = 0;
    public $arrears50kto100k = 0;
    public $arrears100kto500k = 0;
    public $arrears500kto1m = 0;
    public $arrears1mPlus = 0;
    
    // Total amounts
    public $totalArrearsAmount = 0;
    public $totalArrearsCount = 0;
    public $avgArrearsAmount = 0;
    
    // Concentration metrics
    public $top5ArrearsAmount = 0;
    public $top10ArrearsAmount = 0;
    public $top20ArrearsAmount = 0;
    
    // Detailed arrears by amount
    public $arrearsByAmountDetails = [];
    
    public function mount()
    {
        $this->loadArrearsByAmount();
    }
    
    private function loadArrearsByAmount()
    {
        // Get arrears amounts from loans_schedules
        $arrearsData = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->select(
                DB::raw('COALESCE(amount_in_arrears, installment - COALESCE(payment, 0)) as arrears_amount')
            )
            ->get();
        
        // Initialize counters
        $this->arrears0to10k = 0;
        $this->arrears10kto50k = 0;
        $this->arrears50kto100k = 0;
        $this->arrears100kto500k = 0;
        $this->arrears500kto1m = 0;
        $this->arrears1mPlus = 0;
        $this->totalArrearsAmount = 0;
        $this->totalArrearsCount = 0;
        
        // Categorize arrears by amount ranges
        foreach ($arrearsData as $arrear) {
            $amount = $arrear->arrears_amount;
            $this->totalArrearsAmount += $amount;
            $this->totalArrearsCount++;
            
            if ($amount <= 10000) {
                $this->arrears0to10k++;
            } elseif ($amount <= 50000) {
                $this->arrears10kto50k++;
            } elseif ($amount <= 100000) {
                $this->arrears50kto100k++;
            } elseif ($amount <= 500000) {
                $this->arrears100kto500k++;
            } elseif ($amount <= 1000000) {
                $this->arrears500kto1m++;
            } else {
                $this->arrears1mPlus++;
            }
        }
        
        // Calculate average
        $this->avgArrearsAmount = $this->totalArrearsCount > 0 
            ? $this->totalArrearsAmount / $this->totalArrearsCount 
            : 0;
        
        // Get top arrears for concentration analysis
        $topArrears = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 0)
            ->select(
                'loans.id',
                'loans.client_number',
                DB::raw('SUM(COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0))) as total_arrears')
            )
            ->groupBy('loans.id', 'loans.client_number')
            ->orderBy('total_arrears', 'desc')
            ->limit(20)
            ->get();
        
        // Calculate concentration metrics
        $this->top5ArrearsAmount = $topArrears->take(5)->sum('total_arrears');
        $this->top10ArrearsAmount = $topArrears->take(10)->sum('total_arrears');
        $this->top20ArrearsAmount = $topArrears->take(20)->sum('total_arrears');
        
        // Get detailed arrears by amount for display
        $this->arrearsByAmountDetails = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
            ->leftJoin('branches', 'loans.branch_id', '=', DB::raw('CAST(branches.id AS TEXT)'))
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 0)
            ->select(
                'loans.id as loan_id',
                'loans.client_number',
                DB::raw('COALESCE(clients.first_name || \' \' || clients.last_name, loans.client_number) as client_name'),
                DB::raw('COALESCE(branches.name, \'Unknown\') as branch_name'),
                'loans.principle as loan_amount',
                DB::raw('SUM(COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0))) as total_arrears'),
                DB::raw('MAX(loans_schedules.days_in_arrears) as max_days_arrears')
            )
            ->groupBy('loans.id', 'loans.client_number', 'clients.first_name', 'clients.last_name', 
                     'branches.name', 'loans.principle')
            ->orderBy('total_arrears', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }
    
    public function getPercentage($count)
    {
        if ($this->totalArrearsCount == 0) {
            return 0;
        }
        return ($count / $this->totalArrearsCount) * 100;
    }
    
    public function getAmountPercentage($amount)
    {
        if ($this->totalArrearsAmount == 0) {
            return 0;
        }
        return ($amount / $this->totalArrearsAmount) * 100;
    }
    
    public function refreshData()
    {
        $this->loadArrearsByAmount();
        session()->flash('message', 'Data refreshed successfully!');
    }

    public function render()
    {
        return view('livewire.active-loan.arrears-dashboard.by-amount');
    }
}
