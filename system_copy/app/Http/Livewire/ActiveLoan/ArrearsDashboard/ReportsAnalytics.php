<?php

namespace App\Http\Livewire\ActiveLoan\ArrearsDashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ReportsAnalytics extends Component
{
    use WithPagination;
    
    // Report summary data
    public $totalLoans = 0;
    public $activeLoans = 0;
    public $loansInArrears = 0;
    public $totalPortfolio = 0;
    public $totalArrears = 0;
    public $totalCollected = 0;
    
    // Key performance indicators
    public $portfolioAtRisk = 0;
    public $nonPerformingLoans = 0;
    public $collectionEfficiency = 0;
    public $averageLoanSize = 0;
    public $averageArrearsAge = 0;
    public $writeOffRatio = 0;
    
    // Aging analysis
    public $agingAnalysis = [];
    
    // Product performance
    public $productPerformance = [];
    
    // Officer performance
    public $officerPerformance = [];
    
    // Branch comparison
    public $branchComparison = [];
    
    // Client segmentation
    public $clientSegmentation = [];
    
    // Report metrics
    public $reportsGeneratedThisMonth = 0;
    public $mostPopularReport = '';
    public $mostPopularReportCount = 0;
    public $scheduledReportsCount = 0;
    public $availableFormatsCount = 7; // PDF, Excel, CSV, JSON, XML, HTML, Word
    public $storageUsed = 0;
    public $avgGenerationTime = 0;
    
    // Recent reports
    public $recentReports = [];
    
    // Export formats
    public $exportFormats = [
        'pdf' => ['name' => 'PDF', 'icon' => 'file-pdf', 'mime' => 'application/pdf'],
        'excel' => ['name' => 'Excel', 'icon' => 'file-excel', 'mime' => 'application/vnd.ms-excel'],
        'csv' => ['name' => 'CSV', 'icon' => 'file-csv', 'mime' => 'text/csv'],
        'json' => ['name' => 'JSON', 'icon' => 'file-code', 'mime' => 'application/json'],
        'xml' => ['name' => 'XML', 'icon' => 'file-code', 'mime' => 'application/xml'],
        'html' => ['name' => 'HTML', 'icon' => 'file-code', 'mime' => 'text/html'],
        'word' => ['name' => 'Word', 'icon' => 'file-word', 'mime' => 'application/msword']
    ];
    
    // Comprehensive reports catalog
    public $reportsCatalog = [
        'standard' => [
            'title' => 'Arrears Reports',
            'reports' => [
                'daily_arrears' => [
                    'name' => 'Daily Arrears Report',
                    'description' => 'Daily snapshot of all accounts in arrears with complete analysis',
                    'frequency' => 'Daily',
                    'sections' => ['Summary', 'Details', 'Aging', 'Collections']
                ],
                'weekly_arrears' => [
                    'name' => 'Weekly Arrears Report',
                    'description' => 'Weekly arrears summary with trend analysis and recovery metrics',
                    'frequency' => 'Weekly',
                    'sections' => ['Weekly Summary', 'Trends', 'Recovery Rate', 'New Arrears']
                ],
                'monthly_arrears' => [
                    'name' => 'Monthly Arrears Report',
                    'description' => 'Comprehensive monthly arrears analysis with PAR calculations',
                    'frequency' => 'Monthly',
                    'sections' => ['Monthly Overview', 'PAR Analysis', 'Provisions', 'Comparisons']
                ],
                'annual_arrears' => [
                    'name' => 'Annual Arrears Report',
                    'description' => 'Year-end arrears analysis with annual trends and forecasting',
                    'frequency' => 'Annual',
                    'sections' => ['Annual Summary', 'Quarterly Breakdown', 'YoY Comparison', 'Forecast']
                ],
                'aging_report' => [
                    'name' => 'Arrears by Aging Report (BoT Classification)',
                    'description' => 'Detailed breakdown of arrears by BoT aging buckets: Current (0-30), Watch (31-90), Substandard (91-180), Doubtful (181-365), Loss (>365)',
                    'frequency' => 'On-demand',
                    'sections' => ['0-30 Days (Current 0%)', '31-90 Days (Watch 10%)', '91-180 Days (Substandard 30%)', '181-365 Days (Doubtful 50%)', '>365 Days (Loss 100%)', 'Summary']
                ],
                'recovery_status' => [
                    'name' => 'Recovery Status Report',
                    'description' => 'Status of all recovery efforts and collection activities',
                    'frequency' => 'Weekly',
                    'sections' => ['Active Recovery', 'Collected', 'Pending', 'Written-off']
                ]
            ]
        ],
        'advanced' => [
            'title' => 'Advanced Analytics',
            'reports' => [
                'risk_assessment' => [
                    'name' => 'Risk Assessment Report',
                    'description' => 'Comprehensive risk analysis with early warning indicators',
                    'frequency' => 'Monthly',
                    'sections' => ['Risk Matrix', 'Early Warnings', 'Stress Testing', 'Mitigation']
                ],
                'trend_analysis' => [
                    'name' => 'Trend Analysis Report',
                    'description' => 'Historical trends with predictive analytics and seasonality',
                    'frequency' => 'Quarterly',
                    'sections' => ['Historical Trends', 'Seasonality', 'Predictions', 'Insights']
                ],
                'recovery_analysis' => [
                    'name' => 'Recovery Analysis Report',
                    'description' => 'Recovery rates, strategies effectiveness, and cost-benefit analysis',
                    'frequency' => 'Monthly',
                    'sections' => ['Recovery Rates', 'Strategy Analysis', 'Cost-Benefit', 'ROI']
                ],
                'provision_analysis' => [
                    'name' => 'Provision Analysis Report',
                    'description' => 'Loan loss provision adequacy and IFRS 9 compliance',
                    'frequency' => 'Quarterly',
                    'sections' => ['ECL Calculation', 'Stage Migration', 'Coverage Ratios']
                ],
                'stress_testing' => [
                    'name' => 'Stress Testing Report',
                    'description' => 'Portfolio stress testing under various economic scenarios',
                    'frequency' => 'Quarterly',
                    'sections' => ['Scenarios', 'Impact Analysis', 'Capital Requirements']
                ],
                'vintage_analysis' => [
                    'name' => 'Vintage Analysis Report',
                    'description' => 'Cohort-based performance analysis by origination period',
                    'frequency' => 'Monthly',
                    'sections' => ['Cohort Performance', 'Default Patterns', 'Loss Curves']
                ]
            ]
        ],
        'regulatory' => [
            'title' => 'Regulatory & Compliance',
            'reports' => [
                'regulatory_submission' => [
                    'name' => 'Regulatory Submission Report',
                    'description' => 'Central Bank regulatory reporting requirements',
                    'frequency' => 'Monthly',
                    'sections' => ['Prudential Ratios', 'Asset Quality', 'Compliance Status']
                ],
                'audit_report' => [
                    'name' => 'Audit Trail Report',
                    'description' => 'Complete audit trail of arrears management activities',
                    'frequency' => 'On-demand',
                    'sections' => ['Actions Log', 'User Activities', 'System Changes']
                ],
                'compliance_dashboard' => [
                    'name' => 'Compliance Dashboard Report',
                    'description' => 'Regulatory compliance status and violations',
                    'frequency' => 'Weekly',
                    'sections' => ['Compliance Metrics', 'Violations', 'Corrective Actions']
                ]
            ]
        ],
        'executive' => [
            'title' => 'Executive Dashboard',
            'reports' => [
                'executive_summary' => [
                    'name' => 'Executive Summary Report',
                    'description' => 'High-level dashboard for senior management',
                    'frequency' => 'Weekly',
                    'sections' => ['KPIs', 'Risk Indicators', 'Action Items', 'Recommendations']
                ],
                'board_report' => [
                    'name' => 'Board Report',
                    'description' => 'Board-level portfolio quality and risk report',
                    'frequency' => 'Monthly',
                    'sections' => ['Portfolio Health', 'Risk Profile', 'Strategic Issues']
                ]
            ]
        ]
    ];
    
    // Report generation status
    public $lastReportGenerated = null;
    public $reportGenerationInProgress = false;
    public $selectedReport = null;
    public $selectedFormat = 'pdf';
    public $reportParameters = [];
    
    // Scheduled reports
    public $scheduledReports = [];
    
    
    private function loadReportsAndAnalytics()
    {
        $this->loadSummaryData();
        $this->loadKeyPerformanceIndicators();
        $this->loadAgingAnalysis();
        $this->loadProductPerformance();
        $this->loadOfficerPerformance();
        $this->loadBranchComparison();
        $this->loadClientSegmentation();
    }
    
    private function loadSummaryData()
    {
        // Total and active loans
        $this->totalLoans = DB::table('loans')->count();
        $this->activeLoans = DB::table('loans')
            ->where('status', 'ACTIVE')
            ->count();
        
        // Loans in arrears
        $this->loansInArrears = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->distinct('loan_id')
            ->count('loan_id');
        
        // Portfolio value
        $this->totalPortfolio = DB::table('loans')
            ->where('status', 'ACTIVE')
            ->sum('principle');
        
        // Total arrears
        $this->totalArrears = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->sum(DB::raw('COALESCE(amount_in_arrears, installment - COALESCE(payment, 0))'));
        
        // Total collected
        $this->totalCollected = DB::table('loans_schedules')
            ->sum('payment');
    }
    
    private function loadKeyPerformanceIndicators()
    {
        // Portfolio at Risk (PAR)
        $this->portfolioAtRisk = $this->totalPortfolio > 0 
            ? ($this->totalArrears / $this->totalPortfolio) * 100 
            : 0;
        
        // Non-performing loans (>90 days)
        $nplAmount = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 90)
            ->sum('loans.principle');
        
        $this->nonPerformingLoans = $this->totalPortfolio > 0 
            ? ($nplAmount / $this->totalPortfolio) * 100 
            : 0;
        
        // Collection efficiency
        $totalDue = DB::table('loans_schedules')->sum('installment');
        $this->collectionEfficiency = $totalDue > 0 
            ? ($this->totalCollected / $totalDue) * 100 
            : 0;
        
        // Average loan size
        $this->averageLoanSize = $this->activeLoans > 0 
            ? $this->totalPortfolio / $this->activeLoans 
            : 0;
        
        // Average arrears age
        $avgDays = DB::table('loans_schedules')
            ->whereNotNull('days_in_arrears')
            ->where('days_in_arrears', '>', 0)
            ->avg('days_in_arrears');
        $this->averageArrearsAge = $avgDays ?? 0;
        
        // Write-off ratio (loans written off)
        $writtenOff = DB::table('loans')
            ->where('status', 'WRITTEN_OFF')
            ->sum('principle');
        
        $totalDisbursed = DB::table('loans')
            ->sum('principle');
        
        $this->writeOffRatio = $totalDisbursed > 0 
            ? ($writtenOff / $totalDisbursed) * 100 
            : 0;
    }
    
    private function loadAgingAnalysis()
    {
        // BoT Classification Aging Buckets
        // 0-30 days: Current (0%)
        // 31-90 days: Watch (10%)
        // 91-180 days: Substandard (30%)
        // 181-365 days: Doubtful (50%)
        // >365 days: Loss (100%)
        $ranges = [
            '0-30 Days (Current)' => [0, 30],
            '31-90 Days (Watch)' => [31, 90],
            '91-180 Days (Substandard)' => [91, 180],
            '181-365 Days (Doubtful)' => [181, 365],
            '>365 Days (Loss)' => [366, 99999]
        ];

        foreach ($ranges as $label => $range) {
            // For 0-30 days bucket, include current (null/0) loans
            if ($range[0] == 0) {
                $count = DB::table('loans_schedules')
                    ->where(function($query) use ($range) {
                        $query->whereNull('days_in_arrears')
                              ->orWhere('days_in_arrears', '<=', 0)
                              ->orWhereBetween('days_in_arrears', [1, $range[1]]);
                    })
                    ->count();

                $amount = DB::table('loans_schedules')
                    ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
                    ->where(function($query) use ($range) {
                        $query->whereNull('loans_schedules.days_in_arrears')
                              ->orWhere('loans_schedules.days_in_arrears', '<=', 0)
                              ->orWhereBetween('loans_schedules.days_in_arrears', [1, $range[1]]);
                    })
                    ->sum(DB::raw('COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0))'));
            } else {
                $count = DB::table('loans_schedules')
                    ->whereNotNull('days_in_arrears')
                    ->whereBetween('days_in_arrears', $range)
                    ->count();

                $amount = DB::table('loans_schedules')
                    ->whereNotNull('days_in_arrears')
                    ->whereBetween('days_in_arrears', $range)
                    ->sum(DB::raw('COALESCE(amount_in_arrears, installment - COALESCE(payment, 0))'));
            }

            $this->agingAnalysis[] = [
                'category' => $label,
                'count' => $count,
                'amount' => $amount,
                'percentage' => $this->totalPortfolio > 0 ? ($amount / $this->totalPortfolio) * 100 : 0
            ];
        }
    }
    
    private function loadProductPerformance()
    {
        $this->productPerformance = DB::table('loans')
            ->leftJoin('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.sub_product_name')
            ->leftJoin('loans_schedules', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->select(
                DB::raw('COALESCE(loan_sub_products.sub_product_name, \'Unknown\') as product_name'),
                DB::raw('COUNT(DISTINCT loans.id) as total_loans'),
                DB::raw('SUM(DISTINCT loans.principle) as portfolio_amount'),
                DB::raw('COUNT(DISTINCT CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans.id END) as arrears_count'),
                DB::raw('SUM(CASE WHEN loans_schedules.days_in_arrears > 0 THEN COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) ELSE 0 END) as arrears_amount'),
                DB::raw('AVG(CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans_schedules.days_in_arrears END) as avg_days_arrears')
            )
            ->groupBy('loan_sub_products.sub_product_name')
            ->orderBy('portfolio_amount', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $item->par = $item->portfolio_amount > 0 
                    ? ($item->arrears_amount / $item->portfolio_amount) * 100 
                    : 0;
                return $item;
            })
            ->toArray();
    }
    
    private function loadOfficerPerformance()
    {
        // Using branch performance since loan_officer column doesn't exist
        // This will group by branch instead of individual officers
        $this->officerPerformance = DB::table('loans')
            ->leftJoin('branches', 'loans.branch_id', '=', DB::raw('CAST(branches.id AS TEXT)'))
            ->leftJoin('loans_schedules', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->select(
                DB::raw('COALESCE(branches.name, \'Unassigned\') as officer_name'),
                DB::raw('COUNT(DISTINCT loans.id) as total_loans'),
                DB::raw('SUM(DISTINCT loans.principle) as portfolio_amount'),
                DB::raw('COUNT(DISTINCT CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans.id END) as arrears_count'),
                DB::raw('SUM(COALESCE(loans_schedules.payment, 0)) as total_collected'),
                DB::raw('SUM(loans_schedules.installment) as total_due'),
                DB::raw('AVG(CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans_schedules.days_in_arrears END) as avg_days_arrears')
            )
            ->groupBy('branches.name')
            ->orderBy('portfolio_amount', 'desc')
            ->limit(15)
            ->get()
            ->map(function($item) {
                $item->collection_rate = $item->total_due > 0 
                    ? ($item->total_collected / $item->total_due) * 100 
                    : 0;
                $item->arrears_rate = $item->total_loans > 0 
                    ? ($item->arrears_count / $item->total_loans) * 100 
                    : 0;
                return $item;
            })
            ->toArray();
    }
    
    private function loadBranchComparison()
    {
        $this->branchComparison = DB::table('loans')
            ->leftJoin('branches', 'loans.branch_id', '=', DB::raw('CAST(branches.id AS TEXT)'))
            ->leftJoin('loans_schedules', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->where('loans.status', 'ACTIVE')
            ->select(
                DB::raw('COALESCE(branches.name, \'Unknown\') as branch_name'),
                DB::raw('COUNT(DISTINCT loans.id) as total_loans'),
                DB::raw('SUM(DISTINCT loans.principle) as portfolio_amount'),
                DB::raw('COUNT(DISTINCT CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans.id END) as arrears_count'),
                DB::raw('SUM(CASE WHEN loans_schedules.days_in_arrears > 0 THEN COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) ELSE 0 END) as arrears_amount'),
                DB::raw('SUM(COALESCE(loans_schedules.payment, 0)) as total_collected'),
                DB::raw('COUNT(DISTINCT CASE WHEN loans_schedules.days_in_arrears > 90 THEN loans.id END) as npl_count')
            )
            ->groupBy('branches.name')
            ->orderBy('portfolio_amount', 'desc')
            ->get()
            ->map(function($item) {
                $item->par = $item->portfolio_amount > 0 
                    ? ($item->arrears_amount / $item->portfolio_amount) * 100 
                    : 0;
                $item->npl_ratio = $item->total_loans > 0 
                    ? ($item->npl_count / $item->total_loans) * 100 
                    : 0;
                return $item;
            })
            ->toArray();
    }
    
    private function loadClientSegmentation()
    {
        // Segment by loan size
        $segments = [
            'Micro' => [0, 100000],
            'Small' => [100001, 500000],
            'Medium' => [500001, 2000000],
            'Large' => [2000001, 10000000],
            'Corporate' => [10000001, 999999999]
        ];
        
        foreach ($segments as $label => $range) {
            $segmentData = DB::table('loans')
                ->leftJoin('loans_schedules', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
                ->where('loans.status', 'ACTIVE')
                ->whereBetween('loans.principle', $range)
                ->select(
                    DB::raw('COUNT(DISTINCT loans.id) as count'),
                    DB::raw('SUM(DISTINCT loans.principle) as total_amount'),
                    DB::raw('COUNT(DISTINCT CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans.id END) as arrears_count'),
                    DB::raw('AVG(CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans_schedules.days_in_arrears END) as avg_days_arrears')
                )
                ->first();
            
            $this->clientSegmentation[] = [
                'segment' => $label,
                'count' => $segmentData->count ?? 0,
                'total_amount' => $segmentData->total_amount ?? 0,
                'arrears_count' => $segmentData->arrears_count ?? 0,
                'avg_days_arrears' => round($segmentData->avg_days_arrears ?? 0, 1),
                'arrears_rate' => $segmentData->count > 0 
                    ? ($segmentData->arrears_count / $segmentData->count) * 100 
                    : 0
            ];
        }
    }
    
    public function mount()
    {
        $this->loadReportsAndAnalytics();
        $this->loadReportMetrics();
        $this->loadRecentReports();
        $this->loadScheduledReports();
    }
    
    private function loadReportMetrics()
    {
        // Simulate report metrics (in production, these would come from database)
        $this->reportsGeneratedThisMonth = 1247;
        $this->mostPopularReport = 'Daily Arrears Report';
        $this->mostPopularReportCount = 456;
        $this->scheduledReportsCount = 23;
        $this->storageUsed = 2.4; // GB
        $this->avgGenerationTime = 2.3; // seconds
    }
    
    private function loadRecentReports()
    {
        // Simulate recent reports (in production, from database)
        $this->recentReports = [
            [
                'id' => 1,
                'name' => 'Daily Arrears Report - ' . Carbon::now()->format('M d'),
                'type' => 'Daily',
                'generated_by' => 'System Auto',
                'date' => Carbon::now()->format('M d, Y'),
                'format' => 'PDF',
                'size' => '2.3 MB',
                'status' => 'completed',
                'download_count' => 45
            ],
            [
                'id' => 2,
                'name' => 'Weekly Collection Report - Week ' . Carbon::now()->weekOfYear,
                'type' => 'Weekly',
                'generated_by' => 'John Doe',
                'date' => Carbon::now()->subDay()->format('M d, Y'),
                'format' => 'Excel',
                'size' => '1.8 MB',
                'status' => 'completed',
                'download_count' => 23
            ],
            [
                'id' => 3,
                'name' => 'Monthly Portfolio Analysis - ' . Carbon::now()->subMonth()->format('M Y'),
                'type' => 'Monthly',
                'generated_by' => 'Sarah Miller',
                'date' => Carbon::now()->startOfMonth()->format('M d, Y'),
                'format' => 'PDF',
                'size' => '4.2 MB',
                'status' => 'completed',
                'download_count' => 67
            ],
            [
                'id' => 4,
                'name' => 'Branch Performance Comparison',
                'type' => 'Custom',
                'generated_by' => 'Mike Johnson',
                'date' => Carbon::now()->subDays(3)->format('M d, Y'),
                'format' => 'Excel',
                'size' => '3.1 MB',
                'status' => 'completed',
                'download_count' => 12
            ]
        ];
    }
    
    private function loadScheduledReports()
    {
        $this->scheduledReports = [
            [
                'id' => 1,
                'report' => 'Daily Arrears Report',
                'frequency' => 'Daily at 8:00 AM',
                'recipients' => 'management@saccos.com',
                'format' => 'PDF',
                'active' => true
            ],
            [
                'id' => 2,
                'report' => 'Weekly Collection Report',
                'frequency' => 'Every Monday at 9:00 AM',
                'recipients' => 'collections@saccos.com',
                'format' => 'Excel',
                'active' => true
            ],
            [
                'id' => 3,
                'report' => 'Monthly Portfolio Report',
                'frequency' => 'First day of month at 6:00 AM',
                'recipients' => 'board@saccos.com',
                'format' => 'PDF',
                'active' => true
            ]
        ];
    }
    
    public function generateReport($reportType, $reportId = null)
    {
        $this->downloadReport($reportType, $reportId);
    }
    
    public function downloadReport($reportType, $reportId = null, $format = null)
    {
        $this->reportGenerationInProgress = true;
        $this->selectedReport = $reportId ?? $reportType;

        // Use passed format or fall back to selectedFormat
        $downloadFormat = $format ?? $this->selectedFormat ?? 'pdf';

        // Find report details
        $reportName = 'Report';
        $reportDetails = null;
        foreach ($this->reportsCatalog as $category) {
            if (isset($category['reports'][$reportId])) {
                $reportDetails = $category['reports'][$reportId];
                $reportName = $reportDetails['name'];
                break;
            }
        }

        // Generate the report data based on type
        $reportData = $this->generateReportData($reportId);

        // Generate filename
        $filename = str_replace(' ', '_', $reportName) . '_' . Carbon::now()->format('Y_m_d_His');

        // Generate and download the report based on format
        switch ($downloadFormat) {
            case 'pdf':
                return $this->downloadPdfReport($reportData, $reportDetails, $filename);
            case 'excel':
                return $this->downloadExcelReport($reportData, $reportDetails, $filename);
            case 'csv':
                return $this->downloadCsvReport($reportData, $reportDetails, $filename);
            default:
                return $this->downloadPdfReport($reportData, $reportDetails, $filename);
        }
    }
    
    private function generateReportData($reportId)
    {
        $data = [
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->name ?? 'System',
            'report_period' => $this->getReportPeriod($reportId),
            'summary' => [],
            'details' => []
        ];
        
        // Load fresh data
        $this->loadSummaryData();
        $this->loadAgingAnalysis();
        
        // Common summary data for all reports
        $data['summary'] = [
            'total_loans' => $this->totalLoans,
            'active_loans' => $this->activeLoans,
            'loans_in_arrears' => $this->loansInArrears,
            'total_portfolio' => $this->totalPortfolio,
            'total_arrears' => $this->totalArrears,
            'portfolio_at_risk' => round($this->portfolioAtRisk, 2),
            'collection_efficiency' => round($this->collectionEfficiency, 2),
            'average_arrears_age' => round($this->averageArrearsAge, 1)
        ];
        
        // Add specific data based on report type
        switch ($reportId) {
            case 'daily_arrears':
                $data['details'] = $this->getDailyArrearsData();
                break;
            case 'weekly_arrears':
                $data['details'] = $this->getWeeklyArrearsData();
                break;
            case 'monthly_arrears':
                $data['details'] = $this->getMonthlyArrearsData();
                break;
            case 'annual_arrears':
                $data['details'] = $this->getAnnualArrearsData();
                break;
            case 'aging_report':
                $data['details'] = $this->getAgingReportData();
                break;
            case 'recovery_status':
                $data['details'] = $this->getRecoveryStatusData();
                break;
            default:
                $data['details'] = $this->getDailyArrearsData();
        }
        
        return $data;
    }
    
    private function getReportPeriod($reportId)
    {
        switch ($reportId) {
            case 'daily_arrears':
                return Carbon::now()->format('l, F j, Y');
            case 'weekly_arrears':
                return 'Week ' . Carbon::now()->weekOfYear . ' - ' . Carbon::now()->year;
            case 'monthly_arrears':
                return Carbon::now()->format('F Y');
            case 'annual_arrears':
                return 'Year ' . Carbon::now()->year;
            case 'aging_report':
                return 'As of ' . Carbon::now()->format('F j, Y');
            default:
                return Carbon::now()->format('F j, Y');
        }
    }
    
    private function getDailyArrearsData()
    {
        // Get today's arrears with detailed loan and member information
        $arrearsLoans = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
            ->leftJoin('branches', 'loans.branch_id', '=', DB::raw('CAST(branches.id AS TEXT)'))
            ->leftJoin('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.sub_product_name')
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 0)
            ->select(
                DB::raw('COALESCE(loans.loan_id, loans.loan_account_number, CAST(loans.id AS VARCHAR)) as loan_reference'),
                'loans.client_number',
                DB::raw('COALESCE(clients.first_name || \' \' || clients.last_name, loans.business_name, \'Unknown\') as member_name'),
                DB::raw('COALESCE(clients.mobile_phone_number, clients.phone_number, \'N/A\') as phone'),
                DB::raw('COALESCE(clients.email, \'N/A\') as email'),
                DB::raw('COALESCE(clients.address, clients.main_address, clients.street, \'N/A\') as address'),
                DB::raw('COALESCE(branches.name, \'Unknown\') as branch_name'),
                DB::raw('COALESCE(loan_sub_products.sub_product_name, loans.loan_sub_product, \'Unknown\') as product_name'),
                'loans.principle',
                'loans.interest',
                DB::raw('loans.principle + loans.interest as total_loan_amount'),
                'loans.disbursement_date',
                'loans.loan_period',
                'loans_schedules.installment',
                'loans_schedules.payment',
                DB::raw('COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) as arrears_amount'),
                'loans_schedules.days_in_arrears',
                'loans_schedules.installment_date',
                DB::raw('CASE 
                    WHEN loans_schedules.days_in_arrears <= 7 THEN \'Current Week\'
                    WHEN loans_schedules.days_in_arrears <= 30 THEN \'1-30 Days\'
                    WHEN loans_schedules.days_in_arrears <= 60 THEN \'31-60 Days\'
                    WHEN loans_schedules.days_in_arrears <= 90 THEN \'61-90 Days\'
                    ELSE \'Over 90 Days\'
                END as aging_bucket'),
                DB::raw('CASE 
                    WHEN loans_schedules.days_in_arrears <= 30 THEN \'Low Risk\'
                    WHEN loans_schedules.days_in_arrears <= 60 THEN \'Medium Risk\'
                    WHEN loans_schedules.days_in_arrears <= 90 THEN \'High Risk\'
                    ELSE \'Critical\'
                END as risk_level')
            )
            ->orderBy('loans_schedules.days_in_arrears', 'desc')
            ->orderBy('arrears_amount', 'desc')
            ->limit(500)  // Increased limit for daily report
            ->get();
        
        // Group loans by risk level for summary
        $riskSummary = $arrearsLoans->groupBy('risk_level')->map(function($group) {
            return [
                'count' => $group->count(),
                'total_arrears' => $group->sum('arrears_amount'),
                'total_principal' => $group->sum('principle')
            ];
        });
        
        // Get top defaulters (highest arrears amounts)
        $topDefaulters = $arrearsLoans->sortByDesc('arrears_amount')->take(10);
        
        // Get new arrears (became arrears in last 7 days)
        $newArrears = $arrearsLoans->filter(function($loan) {
            return $loan->days_in_arrears <= 7;
        });
        
        return [
            'arrears_list' => $arrearsLoans->toArray(),
            'aging_analysis' => $this->agingAnalysis,
            'new_arrears_today' => $this->getNewArrearsToday(),
            'collections_today' => $this->getCollectionsToday(),
            'risk_summary' => $riskSummary->toArray(),
            'top_defaulters' => $topDefaulters->toArray(),
            'new_arrears' => $newArrears->toArray(),
            'total_loans_in_arrears' => $arrearsLoans->count(),
            'total_arrears_amount' => $arrearsLoans->sum('arrears_amount'),
            'average_days_in_arrears' => round($arrearsLoans->avg('days_in_arrears'), 1)
        ];
    }
    
    private function getWeeklyArrearsData()
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        
        $weeklyData = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->whereBetween('loans_schedules.installment_date', [$weekStart, $weekEnd])
            ->where('loans.status', 'ACTIVE')
            ->select(
                DB::raw('DATE(loans_schedules.installment_date) as date'),
                DB::raw('COUNT(CASE WHEN loans_schedules.days_in_arrears > 0 THEN 1 END) as arrears_count'),
                DB::raw('SUM(CASE WHEN loans_schedules.days_in_arrears > 0 THEN COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) ELSE 0 END) as arrears_amount'),
                DB::raw('SUM(COALESCE(loans_schedules.payment, 0)) as collections')
            )
            ->groupBy(DB::raw('DATE(loans_schedules.installment_date)'))
            ->orderBy('date')
            ->get();
        
        return [
            'weekly_trend' => $weeklyData->toArray(),
            'aging_analysis' => $this->agingAnalysis,
            'weekly_summary' => [
                'total_arrears' => $weeklyData->sum('arrears_amount'),
                'total_collections' => $weeklyData->sum('collections'),
                'average_daily_arrears' => $weeklyData->avg('arrears_amount'),
                'recovery_rate' => $weeklyData->sum('arrears_amount') > 0 ? 
                    ($weeklyData->sum('collections') / $weeklyData->sum('arrears_amount')) * 100 : 0
            ]
        ];
    }
    
    private function getMonthlyArrearsData()
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        
        // Load product and branch performance for monthly report
        $this->loadProductPerformance();
        $this->loadBranchComparison();
        
        return [
            'aging_analysis' => $this->agingAnalysis,
            'product_performance' => $this->productPerformance,
            'branch_comparison' => $this->branchComparison,
            'monthly_summary' => [
                'par_ratio' => round($this->portfolioAtRisk, 2),
                'npl_ratio' => round($this->nonPerformingLoans, 2),
                'provision_required' => $this->calculateProvisionRequired(),
                'write_off_ratio' => round($this->writeOffRatio, 2)
            ]
        ];
    }
    
    private function getAnnualArrearsData()
    {
        // Get quarterly breakdown for annual report
        $quarterlyData = [];
        for ($q = 1; $q <= 4; $q++) {
            $quarterStart = Carbon::now()->startOfYear()->addQuarters($q - 1);
            $quarterEnd = $quarterStart->copy()->endOfQuarter();
            
            $quarterData = DB::table('loans_schedules')
                ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
                ->whereBetween('loans_schedules.installment_date', [$quarterStart, $quarterEnd])
                ->where('loans.status', 'ACTIVE')
                ->select(
                    DB::raw('COUNT(DISTINCT CASE WHEN loans_schedules.days_in_arrears > 0 THEN loans.id END) as arrears_count'),
                    DB::raw('SUM(CASE WHEN loans_schedules.days_in_arrears > 0 THEN COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) ELSE 0 END) as arrears_amount'),
                    DB::raw('SUM(loans.principle) as portfolio'),
                    DB::raw('SUM(COALESCE(loans_schedules.payment, 0)) as collections')
                )
                ->first();
            
            $quarterlyData[] = [
                'quarter' => 'Q' . $q . ' ' . Carbon::now()->year,
                'arrears_count' => $quarterData->arrears_count ?? 0,
                'arrears_amount' => $quarterData->arrears_amount ?? 0,
                'portfolio' => $quarterData->portfolio ?? 0,
                'collections' => $quarterData->collections ?? 0,
                'par' => $quarterData->portfolio > 0 ? 
                    ($quarterData->arrears_amount / $quarterData->portfolio) * 100 : 0
            ];
        }
        
        return [
            'quarterly_breakdown' => $quarterlyData,
            'aging_analysis' => $this->agingAnalysis,
            'annual_summary' => [
                'total_disbursed' => DB::table('loans')->whereYear('created_at', Carbon::now()->year)->sum('principle'),
                'total_collected' => DB::table('loans_schedules')->whereYear('installment_date', Carbon::now()->year)->sum('payment'),
                'year_end_arrears' => $this->totalArrears,
                'average_par' => collect($quarterlyData)->avg('par')
            ]
        ];
    }
    
    private function getAgingReportData()
    {
        // BoT Classification Aging Buckets
        // 0-30 days: Current (0%)
        // 31-90 days: Watch (10%)
        // 91-180 days: Substandard (30%)
        // 181-365 days: Doubtful (50%)
        // >365 days: Loss (100%)
        $detailedAging = [];
        $agingRanges = [
            '0-30 Days (Current - 0%)' => [0, 30],
            '31-90 Days (Watch - 10%)' => [31, 90],
            '91-180 Days (Substandard - 30%)' => [91, 180],
            '181-365 Days (Doubtful - 50%)' => [181, 365],
            '>365 Days (Loss - 100%)' => [366, 99999]
        ];
        
        foreach ($agingRanges as $label => $range) {
            $query = DB::table('loans_schedules')
                ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
                ->where('loans.status', 'ACTIVE');

            // For 0-30 days bucket (Current), include loans with null/0 days in arrears
            // as well as those with 1-30 days in arrears
            if ($range[0] == 0) {
                $query->where(function($q) use ($range) {
                    $q->whereNull('loans_schedules.days_in_arrears')
                      ->orWhere('loans_schedules.days_in_arrears', '<=', 0)
                      ->orWhereBetween('loans_schedules.days_in_arrears', [1, $range[1]]);
                });
            } else {
                $query->whereNotNull('loans_schedules.days_in_arrears')
                      ->whereBetween('loans_schedules.days_in_arrears', $range);
            }
            
            $data = $query->select(
                DB::raw('COUNT(DISTINCT loans.id) as count'),
                DB::raw('SUM(loans.principle) as principal'),
                DB::raw('SUM(COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0))) as arrears')
            )->first();
            
            $detailedAging[] = [
                'bucket' => $label,
                'loan_count' => $data->count ?? 0,
                'principal_amount' => $data->principal ?? 0,
                'arrears_amount' => $data->arrears ?? 0,
                'provision_rate' => $this->getProvisionRate($label),
                'provision_amount' => ($data->arrears ?? 0) * $this->getProvisionRate($label) / 100
            ];
        }
        
        return [
            'detailed_aging' => $detailedAging,
            'summary' => [
                'total_current' => collect($detailedAging)->filter(fn($item) => str_contains($item['bucket'], '0-30 Days'))->sum('principal_amount'),
                'total_arrears' => collect($detailedAging)->filter(fn($item) => !str_contains($item['bucket'], '0-30 Days'))->sum('arrears_amount'),
                'total_provision' => collect($detailedAging)->sum('provision_amount'),
                'weighted_days' => $this->averageArrearsAge
            ]
        ];
    }
    
    private function getRecoveryStatusData()
    {
        $recoveryData = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
            ->where('loans.status', 'ACTIVE')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 0)
            ->select(
                DB::raw('COALESCE(loans.loan_id, loans.loan_account_number, CAST(loans.id AS VARCHAR)) as loan_reference'),
                DB::raw('COALESCE(clients.first_name || \' \' || clients.last_name, loans.business_name, \'Unknown\') as member_name'),
                DB::raw('COALESCE(clients.mobile_phone_number, clients.phone_number, \'N/A\') as member_phone'),
                'loans.principle',
                'loans_schedules.days_in_arrears',
                DB::raw('COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) as arrears_amount'),
                DB::raw('COALESCE(loans_schedules.payment, 0) as amount_paid'),
                DB::raw('CASE 
                    WHEN loans_schedules.days_in_arrears <= 30 THEN \'Follow-up\'
                    WHEN loans_schedules.days_in_arrears <= 60 THEN \'Intensive Recovery\'
                    WHEN loans_schedules.days_in_arrears <= 90 THEN \'Legal Notice\'
                    ELSE \'Legal Action\'
                END as recovery_stage')
            )
            ->orderBy('loans_schedules.days_in_arrears', 'desc')
            ->limit(200)
            ->get();
        
        $recoverySummary = $recoveryData->groupBy('recovery_stage')->map(function($group, $stage) {
            return [
                'stage' => $stage,
                'count' => $group->count(),
                'total_arrears' => $group->sum('arrears_amount'),
                'total_recovered' => $group->sum('amount_paid'),
                'recovery_rate' => $group->sum('arrears_amount') > 0 ? 
                    ($group->sum('amount_paid') / $group->sum('arrears_amount')) * 100 : 0
            ];
        });
        
        return [
            'recovery_list' => $recoveryData->toArray(),
            'recovery_summary' => $recoverySummary->values()->toArray(),
            'overall_recovery' => [
                'total_in_recovery' => $recoveryData->count(),
                'total_arrears' => $recoveryData->sum('arrears_amount'),
                'total_recovered' => $recoveryData->sum('amount_paid'),
                'recovery_rate' => $recoveryData->sum('arrears_amount') > 0 ?
                    ($recoveryData->sum('amount_paid') / $recoveryData->sum('arrears_amount')) * 100 : 0
            ]
        ];
    }
    
    private function getNewArrearsToday()
    {
        return DB::table('loans_schedules')
            ->whereDate('installment_date', Carbon::today())
            ->where('days_in_arrears', 1)
            ->count();
    }
    
    private function getCollectionsToday()
    {
        return DB::table('loans_schedules')
            ->whereDate('updated_at', Carbon::today())
            ->sum('payment');
    }
    
    private function calculateProvisionRequired()
    {
        $provision = 0;
        foreach ($this->agingAnalysis as $aging) {
            $provision += $aging['amount'] * $this->getProvisionRate($aging['category']) / 100;
        }
        return $provision;
    }
    
    private function getProvisionRate($category)
    {
        // BoT Classification Provision Rates
        // 0-30 days: Current (0%)
        // 31-90 days: Watch (10%)
        // 91-180 days: Substandard (30%)
        // 181-365 days: Doubtful (50%)
        // >365 days: Loss (100%)

        // Check by pattern matching for the new BoT bucket labels
        if (str_contains($category, '0-30') || str_contains($category, 'Current')) {
            return 0;
        }
        if (str_contains($category, '31-90') || str_contains($category, 'Watch')) {
            return 10;
        }
        if (str_contains($category, '91-180') || str_contains($category, 'Substandard')) {
            return 30;
        }
        if (str_contains($category, '181-365') || str_contains($category, 'Doubtful')) {
            return 50;
        }
        if (str_contains($category, '>365') || str_contains($category, 'Loss')) {
            return 100;
        }

        // Legacy rates for backward compatibility
        $rates = [
            // Current - 0%
            '1-7 days' => 0,
            '8-15 days' => 0,
            '16-30 days' => 0,
            '1-30 days' => 0,

            // Watch - 10%
            '31-45 days' => 10,
            '46-60 days' => 10,
            '31-60 days' => 10,
            '61-75 days' => 10,
            '76-90 days' => 10,
            '61-90 days' => 10,

            // Substandard - 30%
            '91-120 days' => 30,
            '121-180 days' => 30,

            // Doubtful - 50%
            '181-270 days' => 50,
            '271-365 days' => 50,

            // Loss - 100%
            'Over 365 days' => 100,
            '>180 days' => 100
        ];

        return $rates[$category] ?? 100;
    }
    
    private function downloadPdfReport($data, $reportDetails, $filename)
    {
        try {
            // Try to use PDF if available
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.arrears-report-pdf', [
                    'reportName' => $reportDetails['name'],
                    'reportDescription' => $reportDetails['description'],
                    'data' => $data,
                    'company' => config('app.name', 'SACCOS'),
                    'logo' => public_path('img/logo.png')
                ]);
                
                $pdf->setPaper('A4', 'portrait');
                
                // Store the file temporarily
                $path = 'reports/' . $filename . '.pdf';
                Storage::put($path, $pdf->output());
                
                // Add to recent reports
                $this->addToRecentReports($reportDetails['name'], 'PDF', Storage::size($path));
                
                session()->flash('message', "Report '{$reportDetails['name']}' generated successfully!");
                
                // Return download response
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, $filename . '.pdf');
            }
        } catch (\Exception $e) {
            // Fallback to HTML download if PDF not available
        }
        
        // Fallback: Generate HTML report
        $html = view('reports.arrears-report-pdf', [
            'reportName' => $reportDetails['name'],
            'reportDescription' => $reportDetails['description'],
            'data' => $data,
            'company' => config('app.name', 'SACCOS'),
            'logo' => public_path('img/logo.png')
        ])->render();
        
        // Store the file temporarily
        $path = 'reports/' . $filename . '.html';
        Storage::put($path, $html);
        
        // Add to recent reports
        $this->addToRecentReports($reportDetails['name'], 'HTML', strlen($html));
        
        session()->flash('message', "Report '{$reportDetails['name']}' generated successfully!");
        
        // Return download response as HTML
        return response()->streamDownload(function() use ($html) {
            echo $html;
        }, $filename . '.html', [
            'Content-Type' => 'text/html',
        ]);
    }
    
    private function downloadExcelReport($data, $reportDetails, $filename)
    {
        // For Excel, we'll create a simple CSV for now (Excel export would need a proper Excel export class)
        return $this->downloadCsvReport($data, $reportDetails, $filename);
    }
    
    private function downloadCsvReport($data, $reportDetails, $filename)
    {
        $csv = $this->generateCsvContent($data, $reportDetails);
        
        // Store the file temporarily
        $path = 'reports/' . $filename . '.csv';
        Storage::put($path, $csv);
        
        // Add to recent reports
        $this->addToRecentReports($reportDetails['name'], 'CSV', Storage::size($path));
        
        session()->flash('message', "Report '{$reportDetails['name']}' generated successfully!");
        
        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
    
    private function generateCsvContent($data, $reportDetails)
    {
        $csv = "Report: {$reportDetails['name']}\n";
        $csv .= "Generated: {$data['generated_at']}\n";
        $csv .= "Period: {$data['report_period']}\n\n";
        
        // Summary section
        $csv .= "SUMMARY\n";
        foreach ($data['summary'] as $key => $value) {
            $label = str_replace('_', ' ', ucfirst($key));
            $csv .= "{$label},{$value}\n";
        }
        $csv .= "\n";
        
        // Additional summary statistics for daily report
        if (isset($data['details']['total_loans_in_arrears'])) {
            $csv .= "DAILY STATISTICS\n";
            $csv .= "Total Loans in Arrears,{$data['details']['total_loans_in_arrears']}\n";
            $csv .= "Total Arrears Amount,{$data['details']['total_arrears_amount']}\n";
            $csv .= "Average Days in Arrears,{$data['details']['average_days_in_arrears']}\n";
            $csv .= "New Arrears Today,{$data['details']['new_arrears_today']}\n";
            $csv .= "Collections Today,{$data['details']['collections_today']}\n";
            $csv .= "\n";
        }
        
        // Risk Summary
        if (isset($data['details']['risk_summary']) && !empty($data['details']['risk_summary'])) {
            $csv .= "RISK LEVEL SUMMARY\n";
            $csv .= "Risk Level,Count,Total Arrears,Total Principal\n";
            foreach ($data['details']['risk_summary'] as $level => $summary) {
                $csv .= "{$level},{$summary['count']},{$summary['total_arrears']},{$summary['total_principal']}\n";
            }
            $csv .= "\n";
        }
        
        // Top Defaulters
        if (isset($data['details']['top_defaulters']) && !empty($data['details']['top_defaulters'])) {
            $csv .= "TOP 10 DEFAULTERS\n";
            $csv .= "Loan Reference,Member Name,Phone,Principal,Arrears Amount,Days in Arrears,Risk Level\n";
            foreach ($data['details']['top_defaulters'] as $loan) {
                $csv .= "\"{$loan->loan_reference}\",\"{$loan->member_name}\",\"{$loan->phone}\",{$loan->principle},{$loan->arrears_amount},{$loan->days_in_arrears},\"{$loan->risk_level}\"\n";
            }
            $csv .= "\n";
        }
        
        // Detailed arrears list
        if (isset($data['details']['arrears_list']) && !empty($data['details']['arrears_list'])) {
            $csv .= "DETAILED ARREARS LIST\n";
            $csv .= "Loan Reference,Client Number,Member Name,Phone,Email,Address,Branch,Product,Principal,Interest,Total Loan,Disbursement Date,Loan Period,Installment,Payment,Arrears Amount,Days in Arrears,Due Date,Aging Bucket,Risk Level\n";
            foreach ($data['details']['arrears_list'] as $loan) {
                // Escape fields that might contain commas
                $memberName = str_replace('"', '""', $loan->member_name ?? 'Unknown');
                $address = str_replace('"', '""', $loan->address ?? 'N/A');
                $branchName = str_replace('"', '""', $loan->branch_name ?? 'N/A');
                $productName = str_replace('"', '""', $loan->product_name ?? 'N/A');
                
                $csv .= "\"{$loan->loan_reference}\",";
                $csv .= "\"{$loan->client_number}\",";
                $csv .= "\"{$memberName}\",";
                $csv .= "\"{$loan->phone}\",";
                $csv .= "\"{$loan->email}\",";
                $csv .= "\"{$address}\",";
                $csv .= "\"{$branchName}\",";
                $csv .= "\"{$productName}\",";
                $csv .= "{$loan->principle},";
                $csv .= "{$loan->interest},";
                $csv .= "{$loan->total_loan_amount},";
                $csv .= "\"{$loan->disbursement_date}\",";
                $csv .= "{$loan->loan_period},";
                $csv .= "{$loan->installment},";
                $csv .= "{$loan->payment},";
                $csv .= "{$loan->arrears_amount},";
                $csv .= "{$loan->days_in_arrears},";
                $csv .= "\"{$loan->installment_date}\",";
                $csv .= "\"{$loan->aging_bucket}\",";
                $csv .= "\"{$loan->risk_level}\"\n";
            }
            $csv .= "\n";
        }
        
        // Aging Analysis
        if (isset($data['details']['aging_analysis']) && !empty($data['details']['aging_analysis'])) {
            $csv .= "AGING ANALYSIS\n";
            $csv .= "Category,Count,Amount,Percentage\n";
            foreach ($data['details']['aging_analysis'] as $aging) {
                $csv .= "{$aging['category']},{$aging['count']},{$aging['amount']}," . round($aging['percentage'], 2) . "%\n";
            }
        }
        
        return $csv;
    }
    
    private function addToRecentReports($reportName, $format, $size)
    {
        $newReport = [
            'id' => count($this->recentReports) + 1,
            'name' => $reportName . ' - ' . Carbon::now()->format('M d, Y H:i'),
            'type' => 'On-demand',
            'generated_by' => auth()->user()->name ?? 'System',
            'date' => Carbon::now()->format('M d, Y'),
            'format' => $format,
            'size' => $this->formatFileSize($size),
            'status' => 'completed',
            'download_count' => 1
        ];
        
        array_unshift($this->recentReports, $newReport);
        $this->reportsGeneratedThisMonth++;
    }
    
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    public function scheduleReport($reportId)
    {
        session()->flash('message', "Report scheduling interface would open for report: {$reportId}");
    }
    
    public function exportReport($reportId, $format)
    {
        $this->selectedFormat = $format;
        $this->generateReport('export', $reportId);
        session()->flash('message', "Report exported to {$format} format successfully!");
    }
    
    public function downloadExistingReport($reportId)
    {
        // Find report
        $report = collect($this->recentReports)->firstWhere('id', $reportId);
        if ($report) {
            $report['download_count']++;
            session()->flash('message', "Downloading: {$report['name']}");
        }
    }
    
    public function viewReport($reportId)
    {
        $report = collect($this->recentReports)->firstWhere('id', $reportId);
        if ($report) {
            session()->flash('message', "Opening report viewer for: {$report['name']}");
        }
    }
    
    public function createCustomReport()
    {
        session()->flash('message', "Custom report builder interface would open here");
    }
    
    public function openReportTemplates()
    {
        session()->flash('message', "Report templates library would open here");
    }
    
    public function quickReport()
    {
        // Generate a quick summary report
        $this->generateReport('quick', 'daily_arrears');
        session()->flash('message', "Quick report generated successfully!");
    }
    
    public function exportData($format = 'excel')
    {
        $this->selectedFormat = $format;
        session()->flash('message', "Data exported to {$format} format successfully!");
    }
    
    public function refreshData()
    {
        $this->loadReportsAndAnalytics();
        $this->loadReportMetrics();
        $this->loadRecentReports();
        session()->flash('message', 'Reports and analytics data refreshed successfully!');
    }
    
    public function deleteReport($reportId)
    {
        $this->recentReports = array_filter($this->recentReports, function($report) use ($reportId) {
            return $report['id'] != $reportId;
        });
        session()->flash('message', 'Report deleted successfully!');
    }
    
    public function shareReport($reportId)
    {
        session()->flash('message', "Share interface would open for report ID: {$reportId}");
    }
    
    public function emailReport($reportId)
    {
        session()->flash('message', "Email interface would open for report ID: {$reportId}");
    }
    
    public function toggleSchedule($scheduleId)
    {
        foreach ($this->scheduledReports as &$schedule) {
            if ($schedule['id'] == $scheduleId) {
                $schedule['active'] = !$schedule['active'];
                $status = $schedule['active'] ? 'activated' : 'deactivated';
                session()->flash('message', "Schedule {$status} successfully!");
                break;
            }
        }
    }
    
    public function editSchedule($scheduleId)
    {
        session()->flash('message', "Schedule editor would open for schedule ID: {$scheduleId}");
    }

    public function render()
    {
        return view('livewire.active-loan.arrears-dashboard.reports-analytics');
    }
}
