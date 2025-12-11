<?php

namespace App\Http\Livewire\ActiveLoan\ArrearsDashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RiskAnalysis extends Component
{
    // Risk categories
    public $performingLoans = 0;
    public $watchLoans = 0;
    public $substandardLoans = 0;
    public $doubtfulLoans = 0;
    public $lossLoans = 0;
    
    // Risk amounts
    public $performingAmount = 0;
    public $watchAmount = 0;
    public $substandardAmount = 0;
    public $doubtfulAmount = 0;
    public $lossAmount = 0;
    
    // Provision requirements
    public $totalProvisionRequired = 0;
    public $currentProvision = 0;
    public $provisionGap = 0;
    
    // Risk metrics
    public $portfolioAtRisk = 0;
    public $nonPerformingLoans = 0;
    public $nplRatio = 0;
    public $provisionCoverage = 0;
    
    // Risk concentration
    public $riskByProduct = [];
    public $riskByBranch = [];
    public $riskBySector = [];
    
    // High risk loans
    public $highRiskLoans = [];
    
    public function mount()
    {
        $this->loadRiskAnalysisData();
    }
    
    private function loadRiskAnalysisData()
    {
        // Get total portfolio
        $totalPortfolio = DB::table('loans')
            ->where('status', 'ACTIVE')
            ->sum('principle');
        
        // Classify loans by risk category based on days in arrears
        // Performing (0 days)
        $performing = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->where(function($query) {
                $query->whereNull('loans_schedules.days_in_arrears')
                      ->orWhere('loans_schedules.days_in_arrears', '<=', 0);
            })
            ->select(
                DB::raw('COUNT(DISTINCT loans.id) as count'),
                DB::raw('SUM(DISTINCT loans.principle) as amount')
            )
            ->first();
        
        $this->performingLoans = $performing->count ?? 0;
        $this->performingAmount = $performing->amount ?? 0;
        
        // Watch (1-30 days)
        $watch = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->whereBetween('loans_schedules.days_in_arrears', [1, 30])
            ->select(
                DB::raw('COUNT(DISTINCT loans.id) as count'),
                DB::raw('SUM(DISTINCT loans.principle) as amount')
            )
            ->first();
        
        $this->watchLoans = $watch->count ?? 0;
        $this->watchAmount = $watch->amount ?? 0;
        
        // Substandard (31-90 days)
        $substandard = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->whereBetween('loans_schedules.days_in_arrears', [31, 90])
            ->select(
                DB::raw('COUNT(DISTINCT loans.id) as count'),
                DB::raw('SUM(DISTINCT loans.principle) as amount')
            )
            ->first();
        
        $this->substandardLoans = $substandard->count ?? 0;
        $this->substandardAmount = $substandard->amount ?? 0;
        
        // Doubtful (91-180 days)
        $doubtful = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->whereBetween('loans_schedules.days_in_arrears', [91, 180])
            ->select(
                DB::raw('COUNT(DISTINCT loans.id) as count'),
                DB::raw('SUM(DISTINCT loans.principle) as amount')
            )
            ->first();
        
        $this->doubtfulLoans = $doubtful->count ?? 0;
        $this->doubtfulAmount = $doubtful->amount ?? 0;
        
        // Loss (>180 days)
        $loss = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 180)
            ->select(
                DB::raw('COUNT(DISTINCT loans.id) as count'),
                DB::raw('SUM(DISTINCT loans.principle) as amount')
            )
            ->first();
        
        $this->lossLoans = $loss->count ?? 0;
        $this->lossAmount = $loss->amount ?? 0;
        
        // Calculate provision requirements (based on regulatory requirements)
        $this->totalProvisionRequired = 
            ($this->performingAmount * 0.01) +  // 1% for performing
            ($this->watchAmount * 0.05) +       // 5% for watch
            ($this->substandardAmount * 0.25) + // 25% for substandard
            ($this->doubtfulAmount * 0.50) +    // 50% for doubtful
            ($this->lossAmount * 1.00);         // 100% for loss
        
        // Get current provision from loan_loss_provisions table
        $this->currentProvision = DB::table('loan_loss_provisions')
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('provision_amount') ?? 0;
        
        $this->provisionGap = $this->totalProvisionRequired - $this->currentProvision;
        
        // Calculate risk metrics
        $this->portfolioAtRisk = $totalPortfolio > 0 
            ? (($this->watchAmount + $this->substandardAmount + $this->doubtfulAmount + $this->lossAmount) / $totalPortfolio) * 100 
            : 0;
        
        $this->nonPerformingLoans = $this->substandardAmount + $this->doubtfulAmount + $this->lossAmount;
        $this->nplRatio = $totalPortfolio > 0 
            ? ($this->nonPerformingLoans / $totalPortfolio) * 100 
            : 0;
        
        $this->provisionCoverage = $this->nonPerformingLoans > 0 
            ? ($this->currentProvision / $this->nonPerformingLoans) * 100 
            : 0;
        
        // Risk by product type
        $this->riskByProduct = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.sub_product_name')
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 0)
            ->select(
                DB::raw('COALESCE(loan_sub_products.sub_product_name, \'Unknown\') as product_name'),
                DB::raw('COUNT(DISTINCT loans.id) as loans_count'),
                DB::raw('SUM(DISTINCT loans.principle) as total_amount'),
                DB::raw('AVG(loans_schedules.days_in_arrears) as avg_days_arrears')
            )
            ->groupBy('loan_sub_products.sub_product_name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
        
        // Risk by branch
        $this->riskByBranch = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('branches', 'loans.branch_id', '=', DB::raw('CAST(branches.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 0)
            ->select(
                DB::raw('COALESCE(branches.name, \'Unknown\') as branch_name'),
                DB::raw('COUNT(DISTINCT loans.id) as loans_count'),
                DB::raw('SUM(DISTINCT loans.principle) as total_amount'),
                DB::raw('AVG(loans_schedules.days_in_arrears) as avg_days_arrears')
            )
            ->groupBy('branches.name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
        
        // Get high risk loans (>90 days arrears)
        $this->highRiskLoans = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 90)
            ->select(
                'loans.id as loan_id',
                'loans.client_number',
                DB::raw('COALESCE(clients.first_name || \' \' || clients.last_name, loans.client_number) as client_name'),
                'loans.principle as loan_amount',
                DB::raw('MAX(loans_schedules.days_in_arrears) as days_in_arrears'),
                DB::raw('SUM(COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0))) as total_arrears'),
                DB::raw('
                    CASE 
                        WHEN MAX(loans_schedules.days_in_arrears) > 180 THEN \'Loss\'
                        WHEN MAX(loans_schedules.days_in_arrears) > 90 THEN \'Doubtful\'
                    END as risk_category
                ')
            )
            ->groupBy('loans.id', 'loans.client_number', 'clients.first_name', 
                     'clients.last_name', 'loans.principle')
            ->orderBy('days_in_arrears', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }
    
    public function calculateProvision($amount, $category)
    {
        $rates = [
            'performing' => 0.01,
            'watch' => 0.05,
            'substandard' => 0.25,
            'doubtful' => 0.50,
            'loss' => 1.00
        ];
        
        return $amount * ($rates[$category] ?? 0);
    }
    
    public function refreshData()
    {
        $this->loadRiskAnalysisData();
        session()->flash('message', 'Risk analysis data refreshed successfully!');
    }

    public function render()
    {
        return view('livewire.active-loan.arrears-dashboard.risk-analysis');
    }
}
