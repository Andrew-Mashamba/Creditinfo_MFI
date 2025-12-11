<?php

namespace App\Http\Livewire\ActiveLoan\ArrearsDashboard;

use Livewire\Component;
use App\Models\LoansModel;
use App\Models\LoanSchedule;
use Illuminate\Support\Facades\DB;

class Overview extends Component
{
    public $portfolioAtRisk = 0;
    public $loansInArrears = 0;
    public $avgDaysInArrears = 0;
    public $collectionRate = 0;
    public $totalActiveLoans = 0;
    
    // Risk categories
    public $criticalArrears = 0;
    public $criticalArrearsAmount = 0;
    public $highRisk = 0;
    public $highRiskAmount = 0;
    public $mediumRisk = 0;
    public $mediumRiskAmount = 0;
    public $performing = 0;
    public $performingSchedulesAmount = 0;
    
    // PAR metrics
    public $par30 = 0;
    public $par90 = 0;
    public $par30Amount = 0;
    public $par90Amount = 0;
    public $performingAmount = 0;
    
    // Arrears distribution
    public $arrears1to7 = 0;
    public $arrears8to30 = 0;
    public $arrears31to90 = 0;
    public $arrears90plus = 0;
    
    public $recentArrearsActivity = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Get total active loans
        $this->totalActiveLoans = LoansModel::where('status', 'ACTIVE')->count();
        
        // Get loans in arrears (from schedules)
        $loansWithArrears = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->distinct('loan_id')
            ->count('loan_id');
        $this->loansInArrears = $loansWithArrears;
        
        // Calculate average days in arrears
        $avgDays = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->avg('days_in_arrears');
        $this->avgDaysInArrears = round($avgDays ?? 0);
        
        // Calculate Portfolio at Risk (PAR) - using schedules data
        $totalLoanAmount = LoansModel::where('status', 'ACTIVE')->sum('principle');
        $arrearsAmount = $this->calculateTotalArrearsAmount();
        $this->portfolioAtRisk = $totalLoanAmount > 0 ? ($arrearsAmount / $totalLoanAmount) * 100 : 0;
        
        // Calculate collection rate (simplified - based on paid vs unpaid schedules)
        $totalSchedules = DB::table('loans_schedules')->count();
        $paidSchedules = DB::table('loans_schedules')
            ->whereNotNull('payment')
            ->where('payment', '>', 0)
            ->count();
        $this->collectionRate = $totalSchedules > 0 ? ($paidSchedules / $totalSchedules) * 100 : 0;
        
        // Risk categories - count schedules and calculate amounts properly
        $this->criticalArrears = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 90)
            ->count();
        $this->criticalArrearsAmount = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 90)
            ->sum(DB::raw('COALESCE(amount_in_arrears, 0)'));
            
        $this->highRisk = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 30)
            ->where('days_in_arrears', '<=', 90)
            ->count();
        $this->highRiskAmount = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 30)
            ->where('days_in_arrears', '<=', 90)
            ->sum(DB::raw('COALESCE(amount_in_arrears, 0)'));
            
        $this->mediumRisk = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->where('days_in_arrears', '<=', 30)
            ->count();
        $this->mediumRiskAmount = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->where('days_in_arrears', '<=', 30)
            ->sum(DB::raw('COALESCE(amount_in_arrears, 0)'));
            
        $this->performing = DB::table('loans_schedules')
            ->where(function($query) {
                $query->whereNull('days_in_arrears')
                      ->orWhere('days_in_arrears', '<=', 0);
            })
            ->count();
        $this->performingSchedulesAmount = DB::table('loans_schedules')
            ->where(function($query) {
                $query->whereNull('days_in_arrears')
                      ->orWhere('days_in_arrears', '<=', 0);
            })
            ->sum('installment');
        
        // PAR calculations
        $this->par30 = $this->calculatePAR(30);
        $this->par90 = $this->calculatePAR(90);
        $this->par30Amount = $this->calculatePARAmount(30);
        $this->par90Amount = $this->calculatePARAmount(90);
        $this->performingAmount = $totalLoanAmount - max($this->par30Amount, $this->par90Amount);
        
        // Arrears distribution with amounts
        $this->arrears1to7 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->where('days_in_arrears', '<=', 7)
            ->count();
            
        $this->arrears8to30 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 7)
            ->where('days_in_arrears', '<=', 30)
            ->count();
            
        $this->arrears31to90 = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 30)
            ->where('days_in_arrears', '<=', 90)
            ->count();
            
        $this->arrears90plus = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 90)
            ->count();
        
        // Recent arrears activity
        $this->loadRecentArrearsActivity();
    }
    
    private function calculateTotalArrearsAmount()
    {
        // Calculate total arrears amount from schedules
        $totalArrears = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->sum(DB::raw('COALESCE(amount_in_arrears, installment - COALESCE(payment, 0))'));
            
        return $totalArrears ?? 0;
    }
    
    private function calculatePAR($days)
    {
        $totalLoanAmount = LoansModel::where('status', 'ACTIVE')->sum('principle');
        if ($totalLoanAmount <= 0) return 0;
        
        // Get loans that have any schedule with arrears > days
        $loansWithArrears = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', $days)
            ->distinct('loans.id')
            ->pluck('loans.id');
        
        // Sum the outstanding balance for those loans
        $parAmount = LoansModel::whereIn('id', $loansWithArrears)
            ->where('status', 'ACTIVE')
            ->sum('principle');
            
        return ($parAmount / $totalLoanAmount) * 100;
    }
    
    private function calculatePARAmount($days)
    {
        // Get loans that have any schedule with arrears > days
        $loansWithArrears = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', $days)
            ->distinct('loans.id')
            ->pluck('loans.id');
        
        // Sum the outstanding balance for those loans
        return LoansModel::whereIn('id', $loansWithArrears)
            ->where('status', 'ACTIVE')
            ->sum('principle');
    }
    
    private function loadRecentArrearsActivity()
    {
        $this->recentArrearsActivity = DB::table('loans_schedules')
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
                'loans_schedules.days_in_arrears',
                DB::raw('COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) as arrears_amount'),
                'loans_schedules.installment_date'
            )
            ->orderBy('loans_schedules.days_in_arrears', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $item->risk_level = $this->getRiskLevel($item->days_in_arrears);
                return $item;
            })
            ->toArray();
    }
    
    private function getRiskLevel($days)
    {
        if ($days > 90) return 'Critical';
        if ($days > 30) return 'High';
        if ($days > 7) return 'Medium';
        return 'Low';
    }
    
    public function calculateArrearsAmountByDays($minDays, $maxDays)
    {
        $query = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0);
            
        if ($maxDays == 9999) {
            $query->where('days_in_arrears', '>', $minDays);
        } else {
            $query->whereBetween('days_in_arrears', [$minDays, $maxDays]);
        }
        
        return $query->sum(DB::raw('COALESCE(amount_in_arrears, installment - COALESCE(payment, 0))'));
    }
    
    public function refreshData()
    {
        $this->loadDashboardData();
        session()->flash('message', 'Dashboard data refreshed successfully!');
    }
    
    public function exportReport()
    {
        // Implement export functionality
        session()->flash('message', 'Report export initiated!');
    }

    public function render()
    {
        return view('livewire.active-loan.arrears-dashboard.overview');
    }
}
