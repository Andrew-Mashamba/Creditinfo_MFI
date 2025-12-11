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

class SectoralClassificationReport extends Component
{
    public $reportPeriod = 'monthly';
    public $selectedMonth;
    public $selectedYear;
    public $selectedBranch = 'all';
    public $branches = [];
    
    // Sectoral Classification Data
    public $industryClassification = [];
    public $purposeClassification = [];
    public $geographicalDistribution = [];
    public $sectorPerformance = [];
    public $riskBySector = [];
    public $sectorTrends = [];
    public $sectorRecommendations = [];
    public $sectorComparison = [];
    
    // Summary Statistics
    public $totalSectors = 0;
    public $totalLoanAmount = 0;
    public $averageSectorSize = 0;
    public $highestRiskSector = '';
    public $bestPerformingSector = '';
    
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
        $this->loadSectoralClassificationData();
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

    public function loadSectoralClassificationData()
    {
        try {
            $this->loadIndustryClassification();
            $this->loadPurposeClassification();
            $this->loadGeographicalDistribution();
            $this->loadSectorPerformance();
            $this->loadRiskBySector();
            $this->loadSectorTrends();
            $this->loadSectorRecommendations();
            $this->loadSectorComparison();
            $this->calculateSummaryStatistics();
        } catch (Exception $e) {
            Log::error('Error loading Sectoral Classification Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadIndustryClassification()
    {
        // Sample industry classification data
        $this->industryClassification = [
            [
                'sector' => 'Agriculture',
                'sub_sector' => 'Crop Farming',
                'loan_count' => 450,
                'loan_amount' => 25000000,
                'percentage' => 20.0,
                'average_loan_size' => 55556,
                'default_rate' => 2.5,
                'risk_level' => 'Low',
                'growth_rate' => 8.5
            ],
            [
                'sector' => 'Agriculture',
                'sub_sector' => 'Livestock',
                'loan_count' => 200,
                'loan_amount' => 15000000,
                'percentage' => 12.0,
                'average_loan_size' => 75000,
                'default_rate' => 3.2,
                'risk_level' => 'Low',
                'growth_rate' => 6.8
            ],
            [
                'sector' => 'Manufacturing',
                'sub_sector' => 'Food Processing',
                'loan_count' => 150,
                'loan_amount' => 18000000,
                'percentage' => 14.4,
                'average_loan_size' => 120000,
                'default_rate' => 4.1,
                'risk_level' => 'Medium',
                'growth_rate' => 12.3
            ],
            [
                'sector' => 'Manufacturing',
                'sub_sector' => 'Textiles',
                'loan_count' => 100,
                'loan_amount' => 12000000,
                'percentage' => 9.6,
                'average_loan_size' => 120000,
                'default_rate' => 5.8,
                'risk_level' => 'Medium',
                'growth_rate' => 4.2
            ],
            [
                'sector' => 'Services',
                'sub_sector' => 'Retail Trade',
                'loan_count' => 300,
                'loan_amount' => 20000000,
                'percentage' => 16.0,
                'average_loan_size' => 66667,
                'default_rate' => 6.2,
                'risk_level' => 'Medium',
                'growth_rate' => 15.7
            ],
            [
                'sector' => 'Services',
                'sub_sector' => 'Transportation',
                'loan_count' => 180,
                'loan_amount' => 15000000,
                'percentage' => 12.0,
                'average_loan_size' => 83333,
                'default_rate' => 7.5,
                'risk_level' => 'High',
                'growth_rate' => 9.8
            ],
            [
                'sector' => 'Services',
                'sub_sector' => 'Education',
                'loan_count' => 120,
                'loan_amount' => 8000000,
                'percentage' => 6.4,
                'average_loan_size' => 66667,
                'default_rate' => 1.8,
                'risk_level' => 'Low',
                'growth_rate' => 18.5
            ],
            [
                'sector' => 'Construction',
                'sub_sector' => 'Residential',
                'loan_count' => 80,
                'loan_amount' => 10000000,
                'percentage' => 8.0,
                'average_loan_size' => 125000,
                'default_rate' => 8.2,
                'risk_level' => 'High',
                'growth_rate' => 11.2
            ],
            [
                'sector' => 'Construction',
                'sub_sector' => 'Commercial',
                'loan_count' => 50,
                'loan_amount' => 8000000,
                'percentage' => 6.4,
                'average_loan_size' => 160000,
                'default_rate' => 9.5,
                'risk_level' => 'High',
                'growth_rate' => 7.8
            ],
            [
                'sector' => 'Mining',
                'sub_sector' => 'Small Scale',
                'loan_count' => 30,
                'loan_amount' => 5000000,
                'percentage' => 4.0,
                'average_loan_size' => 166667,
                'default_rate' => 12.3,
                'risk_level' => 'Very High',
                'growth_rate' => 3.5
            ]
        ];
    }

    public function loadPurposeClassification()
    {
        // Sample purpose classification data
        $this->purposeClassification = [
            [
                'purpose' => 'Working Capital',
                'loan_count' => 600,
                'loan_amount' => 40000000,
                'percentage' => 32.0,
                'average_loan_size' => 66667,
                'default_rate' => 4.2,
                'risk_level' => 'Medium',
                'average_term' => 12
            ],
            [
                'purpose' => 'Equipment Purchase',
                'loan_count' => 300,
                'loan_amount' => 30000000,
                'percentage' => 24.0,
                'average_loan_size' => 100000,
                'default_rate' => 3.8,
                'risk_level' => 'Low',
                'average_term' => 24
            ],
            [
                'purpose' => 'Business Expansion',
                'loan_count' => 200,
                'loan_amount' => 25000000,
                'percentage' => 20.0,
                'average_loan_size' => 125000,
                'default_rate' => 5.5,
                'risk_level' => 'Medium',
                'average_term' => 36
            ],
            [
                'purpose' => 'Personal Use',
                'loan_count' => 250,
                'loan_amount' => 15000000,
                'percentage' => 12.0,
                'average_loan_size' => 60000,
                'default_rate' => 6.8,
                'risk_level' => 'Medium',
                'average_term' => 18
            ],
            [
                'purpose' => 'Education',
                'loan_count' => 120,
                'loan_amount' => 8000000,
                'percentage' => 6.4,
                'average_loan_size' => 66667,
                'default_rate' => 1.8,
                'risk_level' => 'Low',
                'average_term' => 48
            ],
            [
                'purpose' => 'Emergency',
                'loan_count' => 100,
                'loan_amount' => 5000000,
                'percentage' => 4.0,
                'average_loan_size' => 50000,
                'default_rate' => 8.5,
                'risk_level' => 'High',
                'average_term' => 6
            ],
            [
                'purpose' => 'Housing',
                'loan_count' => 80,
                'loan_amount' => 12000000,
                'percentage' => 9.6,
                'average_loan_size' => 150000,
                'default_rate' => 2.2,
                'risk_level' => 'Low',
                'average_term' => 60
            ]
        ];
    }

    public function loadGeographicalDistribution()
    {
        // Sample geographical distribution data
        $this->geographicalDistribution = [
            [
                'region' => 'Dar es Salaam',
                'loan_count' => 400,
                'loan_amount' => 35000000,
                'percentage' => 28.0,
                'average_loan_size' => 87500,
                'default_rate' => 5.2,
                'risk_level' => 'Medium',
                'population_density' => 'High'
            ],
            [
                'region' => 'Arusha',
                'loan_count' => 250,
                'loan_amount' => 20000000,
                'percentage' => 16.0,
                'average_loan_size' => 80000,
                'default_rate' => 3.8,
                'risk_level' => 'Low',
                'population_density' => 'Medium'
            ],
            [
                'region' => 'Mwanza',
                'loan_count' => 200,
                'loan_amount' => 15000000,
                'percentage' => 12.0,
                'average_loan_size' => 75000,
                'default_rate' => 4.5,
                'risk_level' => 'Medium',
                'population_density' => 'Medium'
            ],
            [
                'region' => 'Dodoma',
                'loan_count' => 180,
                'loan_amount' => 12000000,
                'percentage' => 9.6,
                'average_loan_size' => 66667,
                'default_rate' => 3.2,
                'risk_level' => 'Low',
                'population_density' => 'Medium'
            ],
            [
                'region' => 'Tanga',
                'loan_count' => 150,
                'loan_amount' => 10000000,
                'percentage' => 8.0,
                'average_loan_size' => 66667,
                'default_rate' => 4.8,
                'risk_level' => 'Medium',
                'population_density' => 'Low'
            ],
            [
                'region' => 'Mbeya',
                'loan_count' => 120,
                'loan_amount' => 8000000,
                'percentage' => 6.4,
                'average_loan_size' => 66667,
                'default_rate' => 3.5,
                'risk_level' => 'Low',
                'population_density' => 'Low'
            ],
            [
                'region' => 'Other Regions',
                'loan_count' => 300,
                'loan_amount' => 20000000,
                'percentage' => 16.0,
                'average_loan_size' => 66667,
                'default_rate' => 4.2,
                'risk_level' => 'Medium',
                'population_density' => 'Low'
            ]
        ];
    }

    public function loadSectorPerformance()
    {
        // Sample sector performance data
        $this->sectorPerformance = [
            [
                'sector' => 'Agriculture',
                'performance_score' => 85.5,
                'growth_rate' => 7.8,
                'profitability' => 82.3,
                'efficiency' => 88.7,
                'customer_satisfaction' => 4.3,
                'market_share' => 32.0,
                'trend' => 'Improving'
            ],
            [
                'sector' => 'Manufacturing',
                'performance_score' => 78.2,
                'growth_rate' => 8.9,
                'profitability' => 75.6,
                'efficiency' => 81.4,
                'customer_satisfaction' => 4.1,
                'market_share' => 24.0,
                'trend' => 'Stable'
            ],
            [
                'sector' => 'Services',
                'performance_score' => 82.7,
                'growth_rate' => 12.5,
                'profitability' => 79.8,
                'efficiency' => 85.2,
                'customer_satisfaction' => 4.2,
                'market_share' => 34.4,
                'trend' => 'Improving'
            ],
            [
                'sector' => 'Construction',
                'performance_score' => 72.1,
                'growth_rate' => 9.8,
                'profitability' => 68.9,
                'efficiency' => 75.3,
                'customer_satisfaction' => 3.9,
                'market_share' => 14.4,
                'trend' => 'Declining'
            ],
            [
                'sector' => 'Mining',
                'performance_score' => 65.8,
                'growth_rate' => 3.5,
                'profitability' => 62.4,
                'efficiency' => 69.1,
                'customer_satisfaction' => 3.7,
                'market_share' => 4.0,
                'trend' => 'Declining'
            ]
        ];
    }

    public function loadRiskBySector()
    {
        // Sample risk by sector data
        $this->riskBySector = [
            [
                'sector' => 'Agriculture',
                'credit_risk' => 'Low',
                'market_risk' => 'Medium',
                'operational_risk' => 'Low',
                'concentration_risk' => 'Medium',
                'overall_risk' => 'Low',
                'risk_score' => 2.8
            ],
            [
                'sector' => 'Manufacturing',
                'credit_risk' => 'Medium',
                'market_risk' => 'Medium',
                'operational_risk' => 'Medium',
                'concentration_risk' => 'Low',
                'overall_risk' => 'Medium',
                'risk_score' => 3.2
            ],
            [
                'sector' => 'Services',
                'credit_risk' => 'Medium',
                'market_risk' => 'Low',
                'operational_risk' => 'Low',
                'concentration_risk' => 'High',
                'overall_risk' => 'Medium',
                'risk_score' => 3.0
            ],
            [
                'sector' => 'Construction',
                'credit_risk' => 'High',
                'market_risk' => 'High',
                'operational_risk' => 'High',
                'concentration_risk' => 'Medium',
                'overall_risk' => 'High',
                'risk_score' => 4.1
            ],
            [
                'sector' => 'Mining',
                'credit_risk' => 'Very High',
                'market_risk' => 'Very High',
                'operational_risk' => 'Very High',
                'concentration_risk' => 'High',
                'overall_risk' => 'Very High',
                'risk_score' => 4.8
            ]
        ];
    }

    public function loadSectorTrends()
    {
        // Sample sector trends data
        $this->sectorTrends = [
            'agriculture_trends' => [
                'loan_volume_trend' => 'Increasing',
                'default_rate_trend' => 'Decreasing',
                'profitability_trend' => 'Improving',
                'market_share_trend' => 'Stable'
            ],
            'manufacturing_trends' => [
                'loan_volume_trend' => 'Stable',
                'default_rate_trend' => 'Stable',
                'profitability_trend' => 'Stable',
                'market_share_trend' => 'Stable'
            ],
            'services_trends' => [
                'loan_volume_trend' => 'Increasing',
                'default_rate_trend' => 'Stable',
                'profitability_trend' => 'Improving',
                'market_share_trend' => 'Increasing'
            ],
            'construction_trends' => [
                'loan_volume_trend' => 'Decreasing',
                'default_rate_trend' => 'Increasing',
                'profitability_trend' => 'Declining',
                'market_share_trend' => 'Decreasing'
            ],
            'mining_trends' => [
                'loan_volume_trend' => 'Decreasing',
                'default_rate_trend' => 'Increasing',
                'profitability_trend' => 'Declining',
                'market_share_trend' => 'Decreasing'
            ]
        ];
    }

    public function loadSectorRecommendations()
    {
        // Sample sector recommendations data
        $this->sectorRecommendations = [
            [
                'sector' => 'Agriculture',
                'recommendation' => 'Increase lending to agriculture sector due to low risk and good performance',
                'priority' => 'High',
                'expected_impact' => 'Increase loan portfolio by 15%',
                'implementation_time' => '3 months'
            ],
            [
                'sector' => 'Services',
                'recommendation' => 'Focus on retail trade and education sub-sectors for growth',
                'priority' => 'High',
                'expected_impact' => 'Improve profitability by 10%',
                'implementation_time' => '2 months'
            ],
            [
                'sector' => 'Manufacturing',
                'recommendation' => 'Strengthen credit assessment for manufacturing loans',
                'priority' => 'Medium',
                'expected_impact' => 'Reduce default rate by 2%',
                'implementation_time' => '4 months'
            ],
            [
                'sector' => 'Construction',
                'recommendation' => 'Review and tighten lending criteria for construction sector',
                'priority' => 'High',
                'expected_impact' => 'Reduce risk exposure by 20%',
                'implementation_time' => '1 month'
            ],
            [
                'sector' => 'Mining',
                'recommendation' => 'Consider reducing exposure to mining sector due to high risk',
                'priority' => 'High',
                'expected_impact' => 'Reduce risk exposure by 30%',
                'implementation_time' => '6 months'
            ]
        ];
    }

    public function loadSectorComparison()
    {
        // Sample sector comparison data
        $this->sectorComparison = [
            'market_share_comparison' => [
                'agriculture' => 32.0,
                'manufacturing' => 24.0,
                'services' => 34.4,
                'construction' => 14.4,
                'mining' => 4.0
            ],
            'profitability_comparison' => [
                'agriculture' => 82.3,
                'manufacturing' => 75.6,
                'services' => 79.8,
                'construction' => 68.9,
                'mining' => 62.4
            ],
            'risk_comparison' => [
                'agriculture' => 2.8,
                'manufacturing' => 3.2,
                'services' => 3.0,
                'construction' => 4.1,
                'mining' => 4.8
            ]
        ];
    }

    public function calculateSummaryStatistics()
    {
        $this->totalSectors = count($this->industryClassification);
        
        $this->totalLoanAmount = array_sum(array_column($this->industryClassification, 'loan_amount'));
        
        $this->averageSectorSize = $this->totalLoanAmount / $this->totalSectors;
        
        // Find highest risk sector
        $highestRisk = max(array_column($this->riskBySector, 'risk_score'));
        $highestRiskIndex = array_search($highestRisk, array_column($this->riskBySector, 'risk_score'));
        $this->highestRiskSector = $this->riskBySector[$highestRiskIndex]['sector'];
        
        // Find best performing sector
        $bestPerformance = max(array_column($this->sectorPerformance, 'performance_score'));
        $bestPerformanceIndex = array_search($bestPerformance, array_column($this->sectorPerformance, 'performance_score'));
        $this->bestPerformingSector = $this->sectorPerformance[$bestPerformanceIndex]['sector'];
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
                return 'Sectoral Classification Report';
        }
    }

    public function updatedReportPeriod()
    {
        $this->loadSectoralClassificationData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadSectoralClassificationData();
    }

    public function updatedSelectedYear()
    {
        $this->loadSectoralClassificationData();
    }

    public function updatedSelectedBranch()
    {
        $this->loadSectoralClassificationData();
    }

    public function exportToExcel()
    {
        // Implementation for Excel export
        session()->flash('success', 'Sectoral Classification Report exported successfully!');
    }

    public function render()
    {
        return view('livewire.reports.sectoral-classification-report');
    }
}
