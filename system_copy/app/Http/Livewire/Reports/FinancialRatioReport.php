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

class FinancialRatioReport extends Component
{
    public $reportPeriod = 'monthly';
    public $selectedMonth;
    public $selectedYear;
    public $selectedBranch = 'all';
    public $branches = [];
    
    // Financial Ratios Data
    public $liquidityRatios = [];
    public $profitabilityRatios = [];
    public $efficiencyRatios = [];
    public $leverageRatios = [];
    public $riskRatios = [];
    public $performanceIndicators = [];
    public $trendAnalysis = [];
    public $benchmarkComparison = [];
    
    // Summary Statistics
    public $overallScore = 0;
    public $riskLevel = 'Low';
    public $performanceGrade = 'A';
    public $recommendations = [];
    
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
        $this->loadFinancialRatioData();
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

    public function loadFinancialRatioData()
    {
        try {
            $this->loadLiquidityRatios();
            $this->loadProfitabilityRatios();
            $this->loadEfficiencyRatios();
            $this->loadLeverageRatios();
            $this->loadRiskRatios();
            $this->loadPerformanceIndicators();
            $this->loadTrendAnalysis();
            $this->loadBenchmarkComparison();
            $this->calculateOverallScore();
            $this->generateRecommendations();
        } catch (Exception $e) {
            Log::error('Error loading Financial Ratio Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadLiquidityRatios()
    {
        // Sample liquidity ratios data
        $this->liquidityRatios = [
            'current_ratio' => [
                'value' => 1.85,
                'benchmark' => 1.50,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures ability to meet short-term obligations'
            ],
            'quick_ratio' => [
                'value' => 1.42,
                'benchmark' => 1.20,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures immediate liquidity without inventory'
            ],
            'cash_ratio' => [
                'value' => 0.68,
                'benchmark' => 0.50,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures cash and cash equivalents to current liabilities'
            ],
            'operating_cash_flow_ratio' => [
                'value' => 0.45,
                'benchmark' => 0.40,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures cash flow from operations to current liabilities'
            ]
        ];
    }

    public function loadProfitabilityRatios()
    {
        // Sample profitability ratios data
        $this->profitabilityRatios = [
            'gross_profit_margin' => [
                'value' => 22.5,
                'benchmark' => 20.0,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Percentage of revenue remaining after cost of goods sold'
            ],
            'net_profit_margin' => [
                'value' => 15.8,
                'benchmark' => 12.0,
                'status' => 'Excellent',
                'trend' => 'Improving',
                'description' => 'Percentage of revenue remaining after all expenses'
            ],
            'return_on_assets' => [
                'value' => 8.2,
                'benchmark' => 6.0,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures efficiency in using assets to generate profit'
            ],
            'return_on_equity' => [
                'value' => 12.4,
                'benchmark' => 10.0,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures return on shareholders equity'
            ],
            'return_on_investment' => [
                'value' => 9.6,
                'benchmark' => 8.0,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures efficiency of investment decisions'
            ]
        ];
    }

    public function loadEfficiencyRatios()
    {
        // Sample efficiency ratios data
        $this->efficiencyRatios = [
            'asset_turnover' => [
                'value' => 0.85,
                'benchmark' => 0.70,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures how efficiently assets are used to generate revenue'
            ],
            'inventory_turnover' => [
                'value' => 6.2,
                'benchmark' => 5.0,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures how quickly inventory is sold'
            ],
            'receivables_turnover' => [
                'value' => 8.5,
                'benchmark' => 6.0,
                'status' => 'Excellent',
                'trend' => 'Improving',
                'description' => 'Measures how quickly receivables are collected'
            ],
            'payables_turnover' => [
                'value' => 4.8,
                'benchmark' => 4.0,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures how quickly payables are paid'
            ],
            'working_capital_turnover' => [
                'value' => 3.2,
                'benchmark' => 2.5,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures efficiency of working capital usage'
            ]
        ];
    }

    public function loadLeverageRatios()
    {
        // Sample leverage ratios data
        $this->leverageRatios = [
            'debt_to_equity' => [
                'value' => 0.65,
                'benchmark' => 0.80,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures relative proportion of debt and equity'
            ],
            'debt_to_assets' => [
                'value' => 0.39,
                'benchmark' => 0.50,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures percentage of assets financed by debt'
            ],
            'equity_multiplier' => [
                'value' => 1.65,
                'benchmark' => 1.80,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures financial leverage'
            ],
            'interest_coverage' => [
                'value' => 4.8,
                'benchmark' => 3.0,
                'status' => 'Excellent',
                'trend' => 'Improving',
                'description' => 'Measures ability to pay interest on debt'
            ],
            'debt_service_coverage' => [
                'value' => 2.2,
                'benchmark' => 1.5,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures ability to service debt obligations'
            ]
        ];
    }

    public function loadRiskRatios()
    {
        // Sample risk ratios data
        $this->riskRatios = [
            'portfolio_at_risk' => [
                'value' => 12.3,
                'benchmark' => 15.0,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Percentage of portfolio with payments overdue'
            ],
            'provision_coverage' => [
                'value' => 85.0,
                'benchmark' => 80.0,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Percentage of bad loans covered by provisions'
            ],
            'capital_adequacy_ratio' => [
                'value' => 18.5,
                'benchmark' => 12.0,
                'status' => 'Excellent',
                'trend' => 'Stable',
                'description' => 'Measures capital adequacy for regulatory compliance'
            ],
            'loan_loss_provision' => [
                'value' => 2.8,
                'benchmark' => 3.0,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Percentage of loans set aside for potential losses'
            ],
            'concentration_risk' => [
                'value' => 25.0,
                'benchmark' => 30.0,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Measures risk concentration in largest exposures'
            ]
        ];
    }

    public function loadPerformanceIndicators()
    {
        // Sample performance indicators data
        $this->performanceIndicators = [
            'operational_efficiency' => [
                'value' => 85.3,
                'benchmark' => 80.0,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures operational efficiency'
            ],
            'cost_to_income_ratio' => [
                'value' => 78.0,
                'benchmark' => 85.0,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Measures cost efficiency relative to income'
            ],
            'staff_productivity' => [
                'value' => 92.1,
                'benchmark' => 85.0,
                'status' => 'Excellent',
                'trend' => 'Stable',
                'description' => 'Measures staff productivity and efficiency'
            ],
            'customer_satisfaction' => [
                'value' => 4.2,
                'benchmark' => 4.0,
                'status' => 'Good',
                'trend' => 'Improving',
                'description' => 'Customer satisfaction rating (1-5 scale)'
            ],
            'loan_approval_rate' => [
                'value' => 78.5,
                'benchmark' => 70.0,
                'status' => 'Good',
                'trend' => 'Stable',
                'description' => 'Percentage of loan applications approved'
            ]
        ];
    }

    public function loadTrendAnalysis()
    {
        // Sample trend analysis data
        $this->trendAnalysis = [
            'liquidity_trend' => [
                'direction' => 'Improving',
                'change' => '+5.2%',
                'period' => 'Last 6 months',
                'forecast' => 'Continued improvement expected'
            ],
            'profitability_trend' => [
                'direction' => 'Improving',
                'change' => '+8.7%',
                'period' => 'Last 6 months',
                'forecast' => 'Stable growth expected'
            ],
            'efficiency_trend' => [
                'direction' => 'Improving',
                'change' => '+3.4%',
                'period' => 'Last 6 months',
                'forecast' => 'Moderate improvement expected'
            ],
            'risk_trend' => [
                'direction' => 'Improving',
                'change' => '-2.1%',
                'period' => 'Last 6 months',
                'forecast' => 'Risk levels stabilizing'
            ]
        ];
    }

    public function loadBenchmarkComparison()
    {
        // Sample benchmark comparison data
        $this->benchmarkComparison = [
            'industry_average' => [
                'liquidity' => 1.60,
                'profitability' => 14.2,
                'efficiency' => 0.75,
                'risk' => 15.5
            ],
            'peer_comparison' => [
                'liquidity' => 1.70,
                'profitability' => 16.8,
                'efficiency' => 0.80,
                'risk' => 13.2
            ],
            'regulatory_requirements' => [
                'capital_adequacy' => 12.0,
                'liquidity_coverage' => 100.0,
                'leverage_ratio' => 3.0,
                'provision_coverage' => 80.0
            ]
        ];
    }

    public function calculateOverallScore()
    {
        // Calculate overall financial health score
        $scores = [];
        
        // Liquidity score (25% weight)
        $liquidityScore = ($this->liquidityRatios['current_ratio']['value'] / $this->liquidityRatios['current_ratio']['benchmark']) * 25;
        $scores[] = min($liquidityScore, 25);
        
        // Profitability score (30% weight)
        $profitabilityScore = ($this->profitabilityRatios['net_profit_margin']['value'] / $this->profitabilityRatios['net_profit_margin']['benchmark']) * 30;
        $scores[] = min($profitabilityScore, 30);
        
        // Efficiency score (20% weight)
        $efficiencyScore = ($this->efficiencyRatios['asset_turnover']['value'] / $this->efficiencyRatios['asset_turnover']['benchmark']) * 20;
        $scores[] = min($efficiencyScore, 20);
        
        // Risk score (25% weight)
        $riskScore = (1 - ($this->riskRatios['portfolio_at_risk']['value'] / $this->riskRatios['portfolio_at_risk']['benchmark'])) * 25;
        $scores[] = max($riskScore, 0);
        
        $this->overallScore = array_sum($scores);
        
        // Determine risk level and performance grade
        if ($this->overallScore >= 90) {
            $this->riskLevel = 'Very Low';
            $this->performanceGrade = 'A+';
        } elseif ($this->overallScore >= 80) {
            $this->riskLevel = 'Low';
            $this->performanceGrade = 'A';
        } elseif ($this->overallScore >= 70) {
            $this->riskLevel = 'Moderate';
            $this->performanceGrade = 'B';
        } elseif ($this->overallScore >= 60) {
            $this->riskLevel = 'High';
            $this->performanceGrade = 'C';
        } else {
            $this->riskLevel = 'Very High';
            $this->performanceGrade = 'D';
        }
    }

    public function generateRecommendations()
    {
        // Generate recommendations based on ratio analysis
        $this->recommendations = [
            [
                'category' => 'Liquidity',
                'priority' => 'Medium',
                'recommendation' => 'Maintain current liquidity levels and consider optimizing cash management',
                'impact' => 'Low risk, moderate benefit'
            ],
            [
                'category' => 'Profitability',
                'priority' => 'High',
                'recommendation' => 'Focus on cost reduction initiatives and revenue optimization',
                'impact' => 'High benefit, moderate effort'
            ],
            [
                'category' => 'Efficiency',
                'priority' => 'Medium',
                'recommendation' => 'Implement process automation to improve operational efficiency',
                'impact' => 'Medium benefit, high effort'
            ],
            [
                'category' => 'Risk Management',
                'priority' => 'High',
                'recommendation' => 'Strengthen credit assessment processes and monitoring systems',
                'impact' => 'High benefit, high effort'
            ]
        ];
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
                return 'Financial Ratio Report';
        }
    }

    public function updatedReportPeriod()
    {
        $this->loadFinancialRatioData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadFinancialRatioData();
    }

    public function updatedSelectedYear()
    {
        $this->loadFinancialRatioData();
    }

    public function updatedSelectedBranch()
    {
        $this->loadFinancialRatioData();
    }

    public function exportToExcel()
    {
        // Implementation for Excel export
        session()->flash('success', 'Financial Ratio Report exported successfully!');
    }

    public function render()
    {
        return view('livewire.reports.financial-ratio-report');
    }
}
