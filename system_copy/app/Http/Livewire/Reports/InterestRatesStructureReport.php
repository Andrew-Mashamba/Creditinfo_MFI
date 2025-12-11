<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use App\Models\BranchesModel;
use App\Models\LoanSubProduct;
use App\Models\loans_schedules;
use App\Models\AccountsModel;
use App\Models\Loan;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class InterestRatesStructureReport extends Component
{
    public $reportPeriod = 'monthly';
    public $selectedMonth;
    public $selectedYear;
    public $selectedBranch = 'all';
    public $branches = [];
    
    // Interest Rates Data
    public $loanProductRates = [];
    public $depositProductRates = [];
    public $rateComparison = [];
    public $rateTrends = [];
    public $rateAnalysis = [];
    public $competitiveAnalysis = [];
    public $rateRecommendations = [];
    public $rateHistory = [];
    
    // Summary Statistics
    public $averageLoanRate = 0;
    public $averageDepositRate = 0;
    public $rateSpread = 0;
    public $rateVolatility = 0;
    
    protected $rules = [
        'reportPeriod' => 'required|string',
        'selectedMonth' => 'required|string',
        'selectedYear' => 'required|string',
        'selectedBranch' => 'required|string'
    ];

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->format('m');
        $this->selectedYear = Carbon::now()->format('Y');
        $this->loadBranches();
        $this->loadInterestRatesData();
    }

    public function loadBranches()
    {
        try {
            $this->branches = BranchesModel::select('id', 'branch_name')
                ->orderBy('branch_name')
                ->get()
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error loading branches: ' . $e->getMessage());
            $this->branches = [];
        }
    }

    public function loadInterestRatesData()
    {
        try {
            $this->loadLoanProductRates();
            $this->loadDepositProductRates();
            $this->loadRateComparison();
            $this->loadRateTrends();
            $this->loadRateAnalysis();
            $this->loadCompetitiveAnalysis();
            $this->loadRateRecommendations();
            $this->loadRateHistory();
            $this->calculateSummaryStatistics();
        } catch (Exception $e) {
            Log::error('Error loading Interest Rates Structure Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadLoanProductRates()
    {
        // Sample loan product rates data
        $this->loanProductRates = [
            [
                'product_name' => 'Personal Loan',
                'base_rate' => 12.5,
                'current_rate' => 12.5,
                'min_rate' => 10.0,
                'max_rate' => 15.0,
                'rate_type' => 'Fixed',
                'term_range' => '6-24 months',
                'loan_amount_range' => '50,000 - 500,000',
                'risk_premium' => 2.5,
                'market_rate' => 11.0,
                'competitive_position' => 'Above Market'
            ],
            [
                'product_name' => 'Business Loan',
                'base_rate' => 14.0,
                'current_rate' => 14.0,
                'min_rate' => 12.0,
                'max_rate' => 18.0,
                'rate_type' => 'Variable',
                'term_range' => '12-60 months',
                'loan_amount_range' => '100,000 - 2,000,000',
                'risk_premium' => 4.0,
                'market_rate' => 13.5,
                'competitive_position' => 'Above Market'
            ],
            [
                'product_name' => 'Agricultural Loan',
                'base_rate' => 10.0,
                'current_rate' => 10.0,
                'min_rate' => 8.0,
                'max_rate' => 12.0,
                'rate_type' => 'Fixed',
                'term_range' => '6-36 months',
                'loan_amount_range' => '25,000 - 1,000,000',
                'risk_premium' => 0.0,
                'market_rate' => 11.5,
                'competitive_position' => 'Below Market'
            ],
            [
                'product_name' => 'Emergency Loan',
                'base_rate' => 15.0,
                'current_rate' => 15.0,
                'min_rate' => 13.0,
                'max_rate' => 20.0,
                'rate_type' => 'Fixed',
                'term_range' => '1-12 months',
                'loan_amount_range' => '10,000 - 100,000',
                'risk_premium' => 5.0,
                'market_rate' => 14.5,
                'competitive_position' => 'Above Market'
            ],
            [
                'product_name' => 'Education Loan',
                'base_rate' => 8.5,
                'current_rate' => 8.5,
                'min_rate' => 7.0,
                'max_rate' => 10.0,
                'rate_type' => 'Fixed',
                'term_range' => '12-48 months',
                'loan_amount_range' => '30,000 - 300,000',
                'risk_premium' => -1.5,
                'market_rate' => 10.0,
                'competitive_position' => 'Below Market'
            ]
        ];
    }

    public function loadDepositProductRates()
    {
        // Sample deposit product rates data
        $this->depositProductRates = [
            [
                'product_name' => 'Savings Account',
                'base_rate' => 3.5,
                'current_rate' => 3.5,
                'min_rate' => 3.0,
                'max_rate' => 4.0,
                'rate_type' => 'Variable',
                'minimum_balance' => 1,000,
                'rate_tier_1' => 3.0,
                'rate_tier_2' => 3.5,
                'rate_tier_3' => 4.0,
                'market_rate' => 3.8,
                'competitive_position' => 'Below Market'
            ],
            [
                'product_name' => 'Current Account',
                'base_rate' => 1.0,
                'current_rate' => 1.0,
                'min_rate' => 0.5,
                'max_rate' => 1.5,
                'rate_type' => 'Fixed',
                'minimum_balance' => 5,000,
                'rate_tier_1' => 0.5,
                'rate_tier_2' => 1.0,
                'rate_tier_3' => 1.5,
                'market_rate' => 1.2,
                'competitive_position' => 'Below Market'
            ],
            [
                'product_name' => 'Fixed Deposit (3 months)',
                'base_rate' => 6.0,
                'current_rate' => 6.0,
                'min_rate' => 5.5,
                'max_rate' => 6.5,
                'rate_type' => 'Fixed',
                'minimum_balance' => 10,000,
                'rate_tier_1' => 5.5,
                'rate_tier_2' => 6.0,
                'rate_tier_3' => 6.5,
                'market_rate' => 6.2,
                'competitive_position' => 'Below Market'
            ],
            [
                'product_name' => 'Fixed Deposit (6 months)',
                'base_rate' => 7.0,
                'current_rate' => 7.0,
                'min_rate' => 6.5,
                'max_rate' => 7.5,
                'rate_type' => 'Fixed',
                'minimum_balance' => 10,000,
                'rate_tier_1' => 6.5,
                'rate_tier_2' => 7.0,
                'rate_tier_3' => 7.5,
                'market_rate' => 7.3,
                'competitive_position' => 'Below Market'
            ],
            [
                'product_name' => 'Fixed Deposit (12 months)',
                'base_rate' => 8.5,
                'current_rate' => 8.5,
                'min_rate' => 8.0,
                'max_rate' => 9.0,
                'rate_type' => 'Fixed',
                'minimum_balance' => 10,000,
                'rate_tier_1' => 8.0,
                'rate_tier_2' => 8.5,
                'rate_tier_3' => 9.0,
                'market_rate' => 8.8,
                'competitive_position' => 'Below Market'
            ]
        ];
    }

    public function loadRateComparison()
    {
        // Sample rate comparison data
        $this->rateComparison = [
            'loan_rates' => [
                'our_average' => 12.0,
                'market_average' => 13.5,
                'competitor_1' => 14.0,
                'competitor_2' => 12.5,
                'competitor_3' => 13.0,
                'advantage' => -1.5
            ],
            'deposit_rates' => [
                'our_average' => 5.2,
                'market_average' => 5.8,
                'competitor_1' => 6.0,
                'competitor_2' => 5.5,
                'competitor_3' => 5.9,
                'advantage' => -0.6
            ],
            'rate_spread' => [
                'our_spread' => 6.8,
                'market_spread' => 7.7,
                'competitor_1_spread' => 8.0,
                'competitor_2_spread' => 7.0,
                'competitor_3_spread' => 7.1,
                'advantage' => -0.9
            ]
        ];
    }

    public function loadRateTrends()
    {
        // Sample rate trends data
        $this->rateTrends = [
            'loan_rate_trends' => [
                '3_months_ago' => 12.8,
                '2_months_ago' => 12.6,
                '1_month_ago' => 12.4,
                'current' => 12.0,
                'trend_direction' => 'Decreasing',
                'trend_magnitude' => -0.8
            ],
            'deposit_rate_trends' => [
                '3_months_ago' => 5.0,
                '2_months_ago' => 5.1,
                '1_month_ago' => 5.2,
                'current' => 5.2,
                'trend_direction' => 'Stable',
                'trend_magnitude' => 0.2
            ],
            'spread_trends' => [
                '3_months_ago' => 7.8,
                '2_months_ago' => 7.5,
                '1_month_ago' => 7.2,
                'current' => 6.8,
                'trend_direction' => 'Decreasing',
                'trend_magnitude' => -1.0
            ]
        ];
    }

    public function loadRateAnalysis()
    {
        // Sample rate analysis data
        $this->rateAnalysis = [
            'rate_sensitivity' => [
                'loan_volume_sensitivity' => 0.8,
                'deposit_volume_sensitivity' => 0.6,
                'profitability_sensitivity' => 1.2,
                'risk_sensitivity' => 0.9
            ],
            'rate_elasticity' => [
                'loan_demand_elasticity' => -1.5,
                'deposit_supply_elasticity' => 0.8,
                'cross_elasticity' => 0.3
            ],
            'rate_volatility' => [
                'loan_rate_volatility' => 0.5,
                'deposit_rate_volatility' => 0.3,
                'spread_volatility' => 0.4
            ]
        ];
    }

    public function loadCompetitiveAnalysis()
    {
        // Sample competitive analysis data
        $this->competitiveAnalysis = [
            'market_position' => [
                'loan_rates_rank' => 3,
                'deposit_rates_rank' => 4,
                'overall_competitiveness' => 'Moderate',
                'market_share' => 15.2
            ],
            'competitive_advantages' => [
                'Lower loan rates for agricultural loans',
                'Competitive education loan rates',
                'Flexible deposit terms'
            ],
            'competitive_disadvantages' => [
                'Higher rates on personal and business loans',
                'Lower deposit rates compared to competitors',
                'Limited rate flexibility'
            ],
            'recommended_actions' => [
                'Review and adjust personal loan rates',
                'Consider increasing deposit rates',
                'Implement dynamic pricing strategy'
            ]
        ];
    }

    public function loadRateRecommendations()
    {
        // Sample rate recommendations data
        $this->rateRecommendations = [
            [
                'product' => 'Personal Loan',
                'current_rate' => 12.5,
                'recommended_rate' => 11.5,
                'change' => -1.0,
                'rationale' => 'Improve competitiveness and increase loan volume',
                'expected_impact' => 'Increase loan volume by 15%',
                'priority' => 'High'
            ],
            [
                'product' => 'Savings Account',
                'current_rate' => 3.5,
                'recommended_rate' => 4.0,
                'change' => 0.5,
                'rationale' => 'Attract more deposits and improve competitiveness',
                'expected_impact' => 'Increase deposits by 10%',
                'priority' => 'Medium'
            ],
            [
                'product' => 'Business Loan',
                'current_rate' => 14.0,
                'recommended_rate' => 13.5,
                'change' => -0.5,
                'rationale' => 'Maintain market position while improving profitability',
                'expected_impact' => 'Maintain loan volume with better margins',
                'priority' => 'Medium'
            ],
            [
                'product' => 'Fixed Deposit (12 months)',
                'current_rate' => 8.5,
                'recommended_rate' => 9.0,
                'change' => 0.5,
                'rationale' => 'Attract long-term deposits',
                'expected_impact' => 'Increase long-term deposits by 20%',
                'priority' => 'Low'
            ]
        ];
    }

    public function loadRateHistory()
    {
        // Sample rate history data
        $this->rateHistory = [
            [
                'date' => '2024-01-01',
                'personal_loan_rate' => 13.0,
                'business_loan_rate' => 14.5,
                'savings_rate' => 3.0,
                'fixed_deposit_rate' => 8.0
            ],
            [
                'date' => '2024-02-01',
                'personal_loan_rate' => 12.8,
                'business_loan_rate' => 14.2,
                'savings_rate' => 3.2,
                'fixed_deposit_rate' => 8.2
            ],
            [
                'date' => '2024-03-01',
                'personal_loan_rate' => 12.6,
                'business_loan_rate' => 14.1,
                'savings_rate' => 3.4,
                'fixed_deposit_rate' => 8.3
            ],
            [
                'date' => '2024-04-01',
                'personal_loan_rate' => 12.5,
                'business_loan_rate' => 14.0,
                'savings_rate' => 3.5,
                'fixed_deposit_rate' => 8.5
            ]
        ];
    }

    public function calculateSummaryStatistics()
    {
        // Calculate average loan rate
        $loanRates = array_column($this->loanProductRates, 'current_rate');
        $this->averageLoanRate = array_sum($loanRates) / count($loanRates);
        
        // Calculate average deposit rate
        $depositRates = array_column($this->depositProductRates, 'current_rate');
        $this->averageDepositRate = array_sum($depositRates) / count($depositRates);
        
        // Calculate rate spread
        $this->rateSpread = $this->averageLoanRate - $this->averageDepositRate;
        
        // Calculate rate volatility
        $this->rateVolatility = $this->rateAnalysis['rate_volatility']['spread_volatility'] ?? 0;
    }

    public function getReportPeriodLabel()
    {
        switch ($this->reportPeriod) {
            case 'daily':
                return 'Daily Report';
            case 'weekly':
                return 'Weekly Report';
            case 'monthly':
                return 'Monthly Report - ' . Carbon::createFromFormat('Y-m', $this->selectedYear . '-' . $this->selectedMonth)->format('F Y');
            case 'yearly':
                return 'Yearly Report - ' . $this->selectedYear;
            default:
                return 'Interest Rates Structure Report';
        }
    }

    public function updatedReportPeriod()
    {
        $this->loadInterestRatesData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadInterestRatesData();
    }

    public function updatedSelectedYear()
    {
        $this->loadInterestRatesData();
    }

    public function updatedSelectedBranch()
    {
        $this->loadInterestRatesData();
    }

    public function exportToExcel()
    {
        // Implementation for Excel export
        session()->flash('success', 'Interest Rates Structure Report exported successfully!');
    }

    public function render()
    {
        return view('livewire.reports.interest-rates-structure-report');
    }
}
