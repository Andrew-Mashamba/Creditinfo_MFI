<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class GeographicalDistributionReport extends Component
{
    public $reportDate;
    public $branches = [];
    public $branchStatistics = [];
    public $totalBranches = 0;
    public $totalEmployees = 0;
    public $totalLoans = 0;
    public $totalLoanAmount = 0;
    public $totalClients = 0;
    public $geographicalDistribution = [];
    public $employeeDistribution = [];
    public $loanDistribution = [];
    public $clientDistribution = [];

    public function mount()
    {
        $this->reportDate = Carbon::now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->loadBranches();
            $this->calculateBranchStatistics();
            $this->calculateGeographicalDistribution();
            $this->calculateEmployeeDistribution();
            $this->calculateLoanDistribution();
            $this->calculateClientDistribution();
        } catch (Exception $e) {
            Log::error('Error loading Geographical Distribution Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadBranches()
    {
        $this->branches = BranchesModel::all()->map(function($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'location' => $branch->location ?? 'N/A',
                'region' => $branch->region ?? 'N/A',
                'district' => $branch->district ?? 'N/A',
                'address' => $branch->address ?? 'N/A',
                'phone' => $branch->phone ?? 'N/A',
                'email' => $branch->email ?? 'N/A',
                'status' => $branch->status ?? 'ACTIVE',
                'opening_date' => $branch->opening_date ?? null,
                'manager_name' => $branch->manager_name ?? 'N/A'
            ];
        });

        $this->totalBranches = $this->branches->count();
    }

    public function calculateBranchStatistics()
    {
        $this->branchStatistics = [];

        foreach ($this->branches as $branch) {
            $branchId = $branch['id'];
            
            // Get employee count for this branch
            $employeeCount = Employee::where('branch_id', $branchId)->count();
            
            // Get loan count and amount for this branch
            $loans = LoansModel::where('branch_id', $branchId)->get();
            $loanCount = $loans->count();
            $loanAmount = $loans->sum('principle');
            
            // Get client count for this branch
            $clientCount = ClientsModel::where('branch_id', $branchId)->count();
            
            // Get active loans
            $activeLoans = $loans->where('status', 'ACTIVE')->count();
            $activeLoanAmount = $loans->where('status', 'ACTIVE')->sum('principle');
            
            // Get overdue loans
            $overdueLoans = $loans->where('status', 'OVERDUE')->count();
            $overdueLoanAmount = $loans->where('status', 'OVERDUE')->sum('principle');

            $this->branchStatistics[] = [
                'branch_id' => $branchId,
                'branch_name' => $branch['name'],
                'location' => $branch['location'],
                'region' => $branch['region'],
                'district' => $branch['district'],
                'employee_count' => $employeeCount,
                'client_count' => $clientCount,
                'total_loans' => $loanCount,
                'total_loan_amount' => $loanAmount,
                'active_loans' => $activeLoans,
                'active_loan_amount' => $activeLoanAmount,
                'overdue_loans' => $overdueLoans,
                'overdue_loan_amount' => $overdueLoanAmount,
                'average_loan_amount' => $loanCount > 0 ? $loanAmount / $loanCount : 0,
                'loan_per_client_ratio' => $clientCount > 0 ? $loanCount / $clientCount : 0
            ];
        }

        // Calculate totals
        $this->totalEmployees = collect($this->branchStatistics)->sum('employee_count');
        $this->totalLoans = collect($this->branchStatistics)->sum('total_loans');
        $this->totalLoanAmount = collect($this->branchStatistics)->sum('total_loan_amount');
        $this->totalClients = collect($this->branchStatistics)->sum('client_count');
    }

    public function calculateGeographicalDistribution()
    {
        $this->geographicalDistribution = [];

        // Group by region
        $regions = collect($this->branchStatistics)->groupBy('region');
        
        foreach ($regions as $region => $branches) {
            $this->geographicalDistribution[] = [
                'region' => $region,
                'branch_count' => $branches->count(),
                'employee_count' => $branches->sum('employee_count'),
                'client_count' => $branches->sum('client_count'),
                'total_loans' => $branches->sum('total_loans'),
                'total_loan_amount' => $branches->sum('total_loan_amount'),
                'active_loans' => $branches->sum('active_loans'),
                'overdue_loans' => $branches->sum('overdue_loans'),
                'average_loan_amount' => $branches->sum('total_loans') > 0 ? $branches->sum('total_loan_amount') / $branches->sum('total_loans') : 0
            ];
        }

        // Sort by total loan amount
        $this->geographicalDistribution = collect($this->geographicalDistribution)
            ->sortByDesc('total_loan_amount')
            ->values()
            ->toArray();
    }

    public function calculateEmployeeDistribution()
    {
        $this->employeeDistribution = [];

        // Group by region for employee distribution
        foreach ($this->geographicalDistribution as $region) {
            $this->employeeDistribution[] = [
                'region' => $region['region'],
                'employee_count' => $region['employee_count'],
                'percentage' => $this->totalEmployees > 0 ? ($region['employee_count'] / $this->totalEmployees) * 100 : 0,
                'branches' => $region['branch_count'],
                'employees_per_branch' => $region['branch_count'] > 0 ? $region['employee_count'] / $region['branch_count'] : 0
            ];
        }
    }

    public function calculateLoanDistribution()
    {
        $this->loanDistribution = [];

        // Group by region for loan distribution
        foreach ($this->geographicalDistribution as $region) {
            $this->loanDistribution[] = [
                'region' => $region['region'],
                'total_loans' => $region['total_loans'],
                'total_loan_amount' => $region['total_loan_amount'],
                'active_loans' => $region['active_loans'],
                'overdue_loans' => $region['overdue_loans'],
                'percentage_of_total_loans' => $this->totalLoans > 0 ? ($region['total_loans'] / $this->totalLoans) * 100 : 0,
                'percentage_of_total_amount' => $this->totalLoanAmount > 0 ? ($region['total_loan_amount'] / $this->totalLoanAmount) * 100 : 0,
                'average_loan_amount' => $region['average_loan_amount'],
                'overdue_percentage' => $region['total_loans'] > 0 ? ($region['overdue_loans'] / $region['total_loans']) * 100 : 0
            ];
        }
    }

    public function calculateClientDistribution()
    {
        $this->clientDistribution = [];

        // Group by region for client distribution
        foreach ($this->geographicalDistribution as $region) {
            $this->clientDistribution[] = [
                'region' => $region['region'],
                'client_count' => $region['client_count'],
                'percentage' => $this->totalClients > 0 ? ($region['client_count'] / $this->totalClients) * 100 : 0,
                'loans_per_client' => $region['client_count'] > 0 ? $region['total_loans'] / $region['client_count'] : 0,
                'loan_amount_per_client' => $region['client_count'] > 0 ? $region['total_loan_amount'] / $region['client_count'] : 0
            ];
        }
    }

    public function getLoanAgeDistribution()
    {
        $ageDistribution = [
            '0-30_days' => 0,
            '31-90_days' => 0,
            '91-180_days' => 0,
            '181-365_days' => 0,
            'over_1_year' => 0
        ];

        $loans = LoansModel::all();
        
        foreach ($loans as $loan) {
            $disbursementDate = Carbon::parse($loan->disbursement_date);
            $ageInDays = $disbursementDate->diffInDays(Carbon::now());
            
            if ($ageInDays <= 30) {
                $ageDistribution['0-30_days']++;
            } elseif ($ageInDays <= 90) {
                $ageDistribution['31-90_days']++;
            } elseif ($ageInDays <= 180) {
                $ageDistribution['91-180_days']++;
            } elseif ($ageInDays <= 365) {
                $ageDistribution['181-365_days']++;
            } else {
                $ageDistribution['over_1_year']++;
            }
        }

        return $ageDistribution;
    }

    public function getTopPerformingBranches()
    {
        return collect($this->branchStatistics)
            ->sortByDesc('total_loan_amount')
            ->take(5)
            ->values()
            ->toArray();
    }

    public function getUnderperformingBranches()
    {
        return collect($this->branchStatistics)
            ->sortBy('total_loan_amount')
            ->take(5)
            ->values()
            ->toArray();
    }

    public function getBranchPerformanceMetrics()
    {
        $metrics = [];
        
        foreach ($this->branchStatistics as $branch) {
            $metrics[] = [
                'branch_name' => $branch['branch_name'],
                'region' => $branch['region'],
                'performance_score' => $this->calculatePerformanceScore($branch),
                'efficiency_ratio' => $branch['employee_count'] > 0 ? $branch['total_loans'] / $branch['employee_count'] : 0,
                'risk_score' => $branch['total_loans'] > 0 ? ($branch['overdue_loans'] / $branch['total_loans']) * 100 : 0
            ];
        }

        return collect($metrics)->sortByDesc('performance_score')->values()->toArray();
    }

    public function calculatePerformanceScore($branch)
    {
        // Simple performance scoring algorithm
        $score = 0;
        
        // Loan volume (40% weight)
        $score += min(40, ($branch['total_loan_amount'] / max($this->totalLoanAmount, 1)) * 40);
        
        // Client base (20% weight)
        $score += min(20, ($branch['client_count'] / max($this->totalClients, 1)) * 20);
        
        // Efficiency (20% weight)
        $efficiency = $branch['employee_count'] > 0 ? $branch['total_loans'] / $branch['employee_count'] : 0;
        $score += min(20, $efficiency * 2);
        
        // Risk management (20% weight)
        $riskScore = $branch['total_loans'] > 0 ? (1 - ($branch['overdue_loans'] / $branch['total_loans'])) * 20 : 20;
        $score += $riskScore;
        
        return round($score, 2);
    }

    public function exportReport($format = 'pdf')
    {
        try {
            session()->flash('success', "Geographical Distribution Report exported as {$format} successfully!");
            
            Log::info('Geographical Distribution Report exported', [
                'format' => $format,
                'report_date' => $this->reportDate,
                'total_branches' => $this->totalBranches,
                'total_employees' => $this->totalEmployees,
                'total_loans' => $this->totalLoans,
                'total_clients' => $this->totalClients,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
            Log::error('Geographical Distribution Report export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }

    public function updatedReportDate()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.geographical-distribution-report');
    }
}
