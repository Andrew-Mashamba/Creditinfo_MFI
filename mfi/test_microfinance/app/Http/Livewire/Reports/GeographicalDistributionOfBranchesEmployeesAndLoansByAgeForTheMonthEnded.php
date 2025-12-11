<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeographicalDistributionOfBranchesEmployeesAndLoansByAgeForTheMonthEnded extends Component
{
    public $startDate;
    public $endDate;
    public $branches = [];
    public $employees = [];
    public $loans = [];
    public $totalBranches = 0;
    public $totalEmployees = 0;
    public $totalLoans = 0;
    public $totalLoanAmount = 0;

    public function mount()
    {
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->branches = collect([]);
        $this->employees = collect([]);
        $this->loans = collect([]);
        $this->loadData();
    }

    public function loadData()
    {
        try {
            // Get branches data
            $this->branches = DB::table('branches')
                ->select(
                    'branches.name', 
                    'branches.address',
                    'branches.branch_number',               
                    'branches.branch_type',
                    'branches.region',
                    'branches.wilaya',
                    'branches.opening_date',
                    DB::raw('COUNT(DISTINCT employees.id) as employee_count'),
                    DB::raw('COUNT(DISTINCT loans.id) as loan_count'),
                    DB::raw('COALESCE(SUM(loans.principle), 0) as total_loan_amount')
                )
                ->leftJoin('employees', 'branches.id', '=', 'employees.branch_id')
                ->leftJoin('loans', 'branches.id', '=', DB::raw('CAST(loans.branch_id AS bigint)'))
                ->whereBetween('branches.opening_date', [$this->startDate, $this->endDate])
                ->groupBy('branches.id', 'branches.name', 'branches.region', 'branches.opening_date', 'branches.address', 'branches.branch_number', 'branches.branch_type', 'branches.wilaya')
                ->get();

            // Get employees data
            $this->employees = DB::table('employees')
                ->join('branches', 'employees.branch_id', '=', 'branches.id')
                ->select(
                    'branches.name',                
                    'branches.region',
                    'employees.first_name',
                    'employees.middle_name',
                    'employees.last_name',
                    'employees.employee_status',   
                    'employees.hire_date',
                    DB::raw('EXTRACT(YEAR FROM AGE(CURRENT_DATE, employees.date_of_birth)) as age')
                )
                ->whereBetween('employees.hire_date', [$this->startDate, $this->endDate])
                ->get();

            // Get loans data with age groups
            $this->loans = DB::table('loans')
                ->join('clients', 'loans.client_number', '=', 'clients.client_number')
                ->join('branches', DB::raw('CAST(loans.branch_id AS bigint)'), '=', 'branches.id')
                ->select(
                    'branches.name',                
                    'branches.region',
                    'loans.loan_account_number',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'clients.client_number',
                    'loans.principle',
                    'loans.created_at',
                    DB::raw('EXTRACT(YEAR FROM AGE(CURRENT_DATE, clients.date_of_birth)) as age'),
                    DB::raw('CASE 
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, clients.date_of_birth)) < 25 THEN \'Under 25\'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, clients.date_of_birth)) BETWEEN 25 AND 35 THEN \'25-35\'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, clients.date_of_birth)) BETWEEN 36 AND 45 THEN \'36-45\'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, clients.date_of_birth)) BETWEEN 46 AND 55 THEN \'46-55\'
                        ELSE \'Over 55\'
                    END as age_group')
                )
                ->whereBetween('loans.created_at', [$this->startDate, $this->endDate])
                ->get();

            // Calculate totals
            $this->totalBranches = $this->branches->count();
            $this->totalEmployees = $this->employees->count();
            $this->totalLoans = $this->loans->count();
            $this->totalLoanAmount = $this->loans->sum('principle');
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading data: ' . $e->getMessage());
            $this->branches = collect([]);
            $this->employees = collect([]);
            $this->loans = collect([]);
            $this->totalBranches = 0;
            $this->totalEmployees = 0;
            $this->totalLoans = 0;
            $this->totalLoanAmount = 0;
        }
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
        return view('livewire.reports.geographical-distribution-of-branches-employees-and-loans-by-age-for-the-month-ended');
    }
} 