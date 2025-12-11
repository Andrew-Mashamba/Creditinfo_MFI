<?php

namespace App\Services;

use App\Models\LoanWriteOff;
use App\Models\LoanWriteoffRecovery;
use App\Models\LoanCollectionEffort;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WriteoffAnalyticsService
{
    protected $cacheTime = 3600; // 1 hour cache

    /**
     * Generate comprehensive writeoff analytics report
     */
    public function generateReport($dateFrom, $dateTo, $periodType = 'monthly')
    {
        $cacheKey = "writeoff_analytics_{$dateFrom}_{$dateTo}_{$periodType}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function() use ($dateFrom, $dateTo, $periodType) {
            try {
                Log::info("📊 Generating writeoff analytics report for {$dateFrom} to {$dateTo}");
                
                $report = [
                    'period' => ['from' => $dateFrom, 'to' => $dateTo, 'type' => $periodType],
                    'summary' => $this->generateSummary($dateFrom, $dateTo),
                    'trends' => $this->generateTrends($dateFrom, $dateTo, $periodType),
                    'breakdown' => $this->generateBreakdown($dateFrom, $dateTo),
                    'recovery_analysis' => $this->generateRecoveryAnalysis($dateFrom, $dateTo),
                    'collection_analysis' => $this->generateCollectionAnalysis($dateFrom, $dateTo),
                    'portfolio_impact' => $this->calculatePortfolioImpact($dateFrom, $dateTo),
                    'regulatory_metrics' => $this->calculateRegulatoryMetrics($dateFrom, $dateTo),
                    'recommendations' => $this->generateRecommendations($dateFrom, $dateTo)
                ];
                
                // Store analytics for future reference
                $this->storeAnalytics($report);
                
                Log::info("✅ Writeoff analytics report generated successfully");
                return $report;
                
            } catch (\Exception $e) {
                Log::error("❌ Error generating writeoff analytics: " . $e->getMessage());
                throw $e;
            }
        });
    }
    
    /**
     * Generate summary statistics
     */
    private function generateSummary($dateFrom, $dateTo)
    {
        $writeOffs = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo]);
        $recoveries = LoanWriteoffRecovery::whereBetween('recovery_date', [$dateFrom, $dateTo])
            ->where('status', 'approved');
        
        $totalWrittenOff = $writeOffs->sum('total_amount');
        $totalRecovered = $recoveries->sum('recovery_amount');
        $netLoss = $totalWrittenOff - $totalRecovered;
        
        return [
            'total_writeoffs_count' => $writeOffs->count(),
            'total_writeoffs_amount' => $totalWrittenOff,
            'total_recovered_amount' => $totalRecovered,
            'net_loss_amount' => $netLoss,
            'recovery_rate' => $totalWrittenOff > 0 ? round(($totalRecovered / $totalWrittenOff) * 100, 2) : 0,
            'average_writeoff_amount' => $writeOffs->count() > 0 ? round($totalWrittenOff / $writeOffs->count(), 2) : 0,
            'pending_approval_count' => $writeOffs->clone()->where('status', 'pending_approval')->count(),
            'board_pending_count' => $writeOffs->clone()->where('requires_board_approval', true)
                ->whereNull('board_approval_date')->count(),
            'formatted' => [
                'total_writeoffs' => 'TZS ' . number_format($totalWrittenOff, 2),
                'total_recovered' => 'TZS ' . number_format($totalRecovered, 2),
                'net_loss' => 'TZS ' . number_format($netLoss, 2),
                'average_writeoff' => 'TZS ' . number_format($writeOffs->count() > 0 ? $totalWrittenOff / $writeOffs->count() : 0, 2)
            ]
        ];
    }
    
    /**
     * Generate trend analysis
     */
    private function generateTrends($dateFrom, $dateTo, $periodType)
    {
        $periods = $this->getPeriods($dateFrom, $dateTo, $periodType);
        $trends = [];
        
        foreach ($periods as $period) {
            $writeOffs = LoanWriteOff::whereBetween('write_off_date', [$period['start'], $period['end']]);
            $recoveries = LoanWriteoffRecovery::whereBetween('recovery_date', [$period['start'], $period['end']])
                ->where('status', 'approved');
            
            $totalWrittenOff = $writeOffs->sum('total_amount');
            $totalRecovered = $recoveries->sum('recovery_amount');
            
            $trends[] = [
                'period_label' => $period['label'],
                'period_start' => $period['start'],
                'period_end' => $period['end'],
                'writeoffs_count' => $writeOffs->count(),
                'writeoffs_amount' => $totalWrittenOff,
                'recoveries_amount' => $totalRecovered,
                'net_loss' => $totalWrittenOff - $totalRecovered,
                'recovery_rate' => $totalWrittenOff > 0 ? round(($totalRecovered / $totalWrittenOff) * 100, 2) : 0
            ];
        }
        
        return $trends;
    }
    
    /**
     * Generate detailed breakdown analysis
     */
    private function generateBreakdown($dateFrom, $dateTo)
    {
        $writeOffs = LoanWriteOff::with(['loan'])
            ->whereBetween('write_off_date', [$dateFrom, $dateTo]);
        
        return [
            'by_amount_range' => $this->getAmountRangeBreakdown($writeOffs->get()),
            'by_loan_product' => $this->getLoanProductBreakdown($writeOffs->get()),
            'by_loan_officer' => $this->getLoanOfficerBreakdown($writeOffs->get()),
            'by_branch' => $this->getBranchBreakdown($writeOffs->get()),
            'by_client_segment' => $this->getClientSegmentBreakdown($writeOffs->get()),
            'by_days_in_arrears' => $this->getDaysInArrearsBreakdown($writeOffs->get()),
            'by_loan_age' => $this->getLoanAgeBreakdown($writeOffs->get())
        ];
    }
    
    /**
     * Generate recovery analysis
     */
    private function generateRecoveryAnalysis($dateFrom, $dateTo)
    {
        $recoveries = LoanWriteoffRecovery::with(['writeOff'])
            ->whereBetween('recovery_date', [$dateFrom, $dateTo])
            ->where('status', 'approved')
            ->get();
        
        return [
            'total_recoveries' => $recoveries->count(),
            'total_recovery_amount' => $recoveries->sum('recovery_amount'),
            'by_method' => $recoveries->groupBy('recovery_method')
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => $group->sum('recovery_amount'),
                        'percentage' => 0 // Will be calculated below
                    ];
                }),
            'by_source' => $recoveries->groupBy('recovery_source')
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => $group->sum('recovery_amount')
                    ];
                }),
            'time_to_recovery' => $this->calculateTimeToRecovery($recoveries),
            'recovery_success_rate' => $this->calculateRecoverySuccessRate($dateFrom, $dateTo),
            'top_performing_officers' => $this->getTopRecoveryOfficers($recoveries)
        ];
    }
    
    /**
     * Generate collection efforts analysis
     */
    private function generateCollectionAnalysis($dateFrom, $dateTo)
    {
        $efforts = LoanCollectionEffort::whereBetween('effort_date', [$dateFrom, $dateTo])->get();
        
        $totalEfforts = $efforts->count();
        $successfulEfforts = $efforts->whereIn('outcome', ['promise_to_pay', 'payment_made'])->count();
        
        return [
            'total_efforts' => $totalEfforts,
            'successful_efforts' => $successfulEfforts,
            'success_rate' => $totalEfforts > 0 ? round(($successfulEfforts / $totalEfforts) * 100, 2) : 0,
            'total_cost' => $efforts->sum('cost_incurred'),
            'average_cost_per_effort' => $totalEfforts > 0 ? round($efforts->sum('cost_incurred') / $totalEfforts, 2) : 0,
            'by_type' => $efforts->groupBy('effort_type')
                ->map(function($group) {
                    $total = $group->count();
                    $successful = $group->whereIn('outcome', ['promise_to_pay', 'payment_made'])->count();
                    return [
                        'count' => $total,
                        'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                        'cost' => $group->sum('cost_incurred')
                    ];
                }),
            'by_outcome' => $efforts->groupBy('outcome')
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'cost' => $group->sum('cost_incurred')
                    ];
                }),
            'staff_performance' => LoanCollectionEffort::getEffortivenessByStaff($dateFrom, $dateTo)
        ];
    }
    
    /**
     * Calculate portfolio impact
     */
    private function calculatePortfolioImpact($dateFrom, $dateTo)
    {
        $totalPortfolio = DB::table('loans')
            ->where('loan_status', 'active')
            ->sum(DB::raw('principle - COALESCE(total_principal_paid, 0)'));
        
        $totalWrittenOff = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])
            ->sum('total_amount');
        
        $totalProvisions = DB::table('loan_loss_provisions')
            ->whereBetween('provision_date', [$dateFrom, $dateTo])
            ->sum('provision_amount');
        
        return [
            'total_portfolio_value' => $totalPortfolio,
            'writeoffs_as_percentage_of_portfolio' => $totalPortfolio > 0 
                ? round(($totalWrittenOff / $totalPortfolio) * 100, 4) : 0,
            'provision_coverage' => $totalWrittenOff > 0 
                ? round(($totalProvisions / $totalWrittenOff) * 100, 2) : 100,
            'impact_on_profitability' => $totalWrittenOff - $totalProvisions,
            'formatted' => [
                'total_portfolio' => 'TZS ' . number_format($totalPortfolio, 2),
                'impact_on_profitability' => 'TZS ' . number_format($totalWrittenOff - $totalProvisions, 2)
            ]
        ];
    }
    
    /**
     * Calculate regulatory compliance metrics
     */
    private function calculateRegulatoryMetrics($dateFrom, $dateTo)
    {
        $nplBalance = DB::table('loans')
            ->whereIn('loan_classification', ['DOUBTFUL', 'LOSS'])
            ->sum(DB::raw('principle - COALESCE(total_principal_paid, 0)'));
        
        $totalLoans = DB::table('loans')
            ->where('loan_status', 'active')
            ->sum(DB::raw('principle - COALESCE(total_principal_paid, 0)'));
        
        $writeOffsThisPeriod = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])
            ->sum('total_amount');
        
        return [
            'npl_ratio' => $totalLoans > 0 ? round(($nplBalance / $totalLoans) * 100, 2) : 0,
            'writeoff_ratio' => $totalLoans > 0 ? round(($writeOffsThisPeriod / $totalLoans) * 100, 4) : 0,
            'board_approval_compliance' => $this->checkBoardApprovalCompliance($dateFrom, $dateTo),
            'documentation_compliance' => $this->checkDocumentationCompliance($dateFrom, $dateTo),
            'regulatory_limits' => [
                'max_npl_ratio' => 5.0, // 5% regulatory limit
                'max_writeoff_ratio' => 2.0, // 2% annual limit
                'min_provision_coverage' => 100.0 // 100% coverage required
            ]
        ];
    }
    
    /**
     * Generate recommendations based on analysis
     */
    private function generateRecommendations($dateFrom, $dateTo)
    {
        $recommendations = [];
        
        $summary = $this->generateSummary($dateFrom, $dateTo);
        $recoveryAnalysis = $this->generateRecoveryAnalysis($dateFrom, $dateTo);
        $collectionAnalysis = $this->generateCollectionAnalysis($dateFrom, $dateTo);
        
        // Recovery rate recommendations
        if ($summary['recovery_rate'] < 10) {
            $recommendations[] = [
                'type' => 'recovery_improvement',
                'priority' => 'high',
                'title' => 'Improve Recovery Efforts',
                'description' => 'Recovery rate is below 10%. Consider enhancing collection strategies and recovery processes.',
                'suggested_actions' => [
                    'Review and improve collection procedures',
                    'Implement more aggressive recovery strategies',
                    'Consider engaging external collection agencies',
                    'Enhance collateral realization processes'
                ]
            ];
        }
        
        // Collection efficiency recommendations
        if ($collectionAnalysis['success_rate'] < 30) {
            $recommendations[] = [
                'type' => 'collection_efficiency',
                'priority' => 'medium',
                'title' => 'Enhance Collection Effectiveness',
                'description' => 'Collection efforts success rate is below 30%. Training and process improvement needed.',
                'suggested_actions' => [
                    'Provide additional training to collection staff',
                    'Review and optimize collection strategies',
                    'Implement better client profiling and segmentation',
                    'Consider technology solutions for collection management'
                ]
            ];
        }
        
        // Board approval compliance
        $boardPending = $summary['board_pending_count'];
        if ($boardPending > 5) {
            $recommendations[] = [
                'type' => 'governance',
                'priority' => 'high',
                'title' => 'Address Board Approval Backlog',
                'description' => "There are {$boardPending} writeoffs pending board approval.",
                'suggested_actions' => [
                    'Schedule urgent board meeting for approvals',
                    'Review board approval thresholds',
                    'Implement interim approval mechanisms for urgent cases',
                    'Improve board meeting scheduling and documentation'
                ]
            ];
        }
        
        // Cost optimization
        $avgCost = $collectionAnalysis['average_cost_per_effort'];
        if ($avgCost > 50000) { // TZS 50,000 per effort
            $recommendations[] = [
                'type' => 'cost_optimization',
                'priority' => 'medium',
                'title' => 'Optimize Collection Costs',
                'description' => 'Average collection cost per effort is high. Consider cost-effective strategies.',
                'suggested_actions' => [
                    'Implement digital collection channels (SMS, email, mobile apps)',
                    'Use data analytics for targeted collection efforts',
                    'Optimize field visit scheduling and routing',
                    'Consider centralized collection operations'
                ]
            ];
        }
        
        return $recommendations;
    }
    
    // Helper methods for detailed breakdowns
    private function getAmountRangeBreakdown($writeOffs)
    {
        $ranges = [
            '0-100k' => ['min' => 0, 'max' => 100000],
            '100k-500k' => ['min' => 100000, 'max' => 500000],
            '500k-1M' => ['min' => 500000, 'max' => 1000000],
            '1M-5M' => ['min' => 1000000, 'max' => 5000000],
            '5M+' => ['min' => 5000000, 'max' => PHP_INT_MAX]
        ];
        
        $breakdown = [];
        foreach ($ranges as $label => $range) {
            $filtered = $writeOffs->whereBetween('total_amount', [$range['min'], $range['max']]);
            $breakdown[$label] = [
                'count' => $filtered->count(),
                'amount' => $filtered->sum('total_amount'),
                'percentage' => $writeOffs->count() > 0 ? round(($filtered->count() / $writeOffs->count()) * 100, 1) : 0
            ];
        }
        
        return $breakdown;
    }
    
    private function getLoanProductBreakdown($writeOffs)
    {
        return $writeOffs->groupBy('loan.loan_sub_product')
            ->map(function($group) use ($writeOffs) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                    'percentage' => $writeOffs->count() > 0 ? round(($group->count() / $writeOffs->count()) * 100, 1) : 0
                ];
            });
    }
    
    private function calculateTimeToRecovery($recoveries)
    {
        $times = [];
        foreach ($recoveries as $recovery) {
            if ($recovery->writeOff && $recovery->writeOff->write_off_date) {
                $days = $recovery->recovery_date->diffInDays($recovery->writeOff->write_off_date);
                $times[] = $days;
            }
        }
        
        if (empty($times)) {
            return ['average' => 0, 'median' => 0, 'min' => 0, 'max' => 0];
        }
        
        sort($times);
        $count = count($times);
        
        return [
            'average' => round(array_sum($times) / $count, 1),
            'median' => $count % 2 === 0 
                ? ($times[$count/2 - 1] + $times[$count/2]) / 2 
                : $times[floor($count/2)],
            'min' => min($times),
            'max' => max($times)
        ];
    }
    
    private function calculateRecoverySuccessRate($dateFrom, $dateTo)
    {
        $totalWriteOffs = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])->count();
        $writeOffsWithRecovery = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])
            ->where('recovered_amount', '>', 0)->count();
        
        return $totalWriteOffs > 0 ? round(($writeOffsWithRecovery / $totalWriteOffs) * 100, 2) : 0;
    }
    
    private function checkBoardApprovalCompliance($dateFrom, $dateTo)
    {
        $requiringBoard = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])
            ->where('requires_board_approval', true)->count();
        
        $boardApproved = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])
            ->where('requires_board_approval', true)
            ->whereNotNull('board_approval_date')->count();
        
        return [
            'total_requiring_board' => $requiringBoard,
            'board_approved' => $boardApproved,
            'compliance_rate' => $requiringBoard > 0 ? round(($boardApproved / $requiringBoard) * 100, 2) : 100
        ];
    }
    
    private function checkDocumentationCompliance($dateFrom, $dateTo)
    {
        $writeOffs = LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])->get();
        $compliant = 0;
        
        foreach ($writeOffs as $writeOff) {
            $efforts = LoanCollectionEffort::where('loan_id', $writeOff->loan_id)->count();
            $minEfforts = 3; // Minimum required efforts
            
            if ($efforts >= $minEfforts) {
                $compliant++;
            }
        }
        
        return [
            'total_writeoffs' => $writeOffs->count(),
            'compliant' => $compliant,
            'compliance_rate' => $writeOffs->count() > 0 ? round(($compliant / $writeOffs->count()) * 100, 2) : 100
        ];
    }
    
    private function getPeriods($dateFrom, $dateTo, $periodType)
    {
        $periods = [];
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        
        switch ($periodType) {
            case 'weekly':
                while ($start <= $end) {
                    $periodEnd = $start->copy()->endOfWeek();
                    if ($periodEnd > $end) $periodEnd = $end;
                    
                    $periods[] = [
                        'label' => 'Week of ' . $start->format('M d'),
                        'start' => $start->format('Y-m-d'),
                        'end' => $periodEnd->format('Y-m-d')
                    ];
                    
                    $start->addWeek();
                }
                break;
                
            case 'monthly':
                while ($start <= $end) {
                    $periodEnd = $start->copy()->endOfMonth();
                    if ($periodEnd > $end) $periodEnd = $end;
                    
                    $periods[] = [
                        'label' => $start->format('M Y'),
                        'start' => $start->format('Y-m-d'),
                        'end' => $periodEnd->format('Y-m-d')
                    ];
                    
                    $start->addMonth();
                }
                break;
                
            case 'quarterly':
                while ($start <= $end) {
                    $periodEnd = $start->copy()->endOfQuarter();
                    if ($periodEnd > $end) $periodEnd = $end;
                    
                    $periods[] = [
                        'label' => 'Q' . $start->quarter . ' ' . $start->year,
                        'start' => $start->format('Y-m-d'),
                        'end' => $periodEnd->format('Y-m-d')
                    ];
                    
                    $start->addQuarter();
                }
                break;
        }
        
        return $periods;
    }
    
    /**
     * Store analytics data for historical tracking
     */
    private function storeAnalytics($report)
    {
        try {
            DB::table('writeoff_analytics')->updateOrInsert(
                [
                    'analysis_date' => now()->format('Y-m-d'),
                    'period_type' => $report['period']['type'],
                    'period_start' => $report['period']['from'],
                    'period_end' => $report['period']['to']
                ],
                [
                    'total_writeoffs_count' => $report['summary']['total_writeoffs_count'],
                    'total_writeoffs_amount' => $report['summary']['total_writeoffs_amount'],
                    'total_recoveries_amount' => $report['summary']['total_recovered_amount'],
                    'net_writeoffs_amount' => $report['summary']['net_loss_amount'],
                    'recovery_rate' => $report['summary']['recovery_rate'],
                    'by_loan_product' => json_encode($report['breakdown']['by_loan_product'] ?? []),
                    'by_client_segment' => json_encode($report['breakdown']['by_client_segment'] ?? []),
                    'by_branch' => json_encode($report['breakdown']['by_branch'] ?? []),
                    'trends_data' => json_encode($report['trends'] ?? []),
                    'provision_coverage_ratio' => $report['portfolio_impact']['provision_coverage'] ?? 0,
                    'analysis_notes' => json_encode($report['recommendations'] ?? []),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            Log::warning("Could not store analytics data: " . $e->getMessage());
        }
    }
    
    // Additional helper methods for specific breakdowns
    private function getLoanOfficerBreakdown($writeOffs) { return []; }
    private function getBranchBreakdown($writeOffs) { return []; }
    private function getClientSegmentBreakdown($writeOffs) { return []; }
    private function getDaysInArrearsBreakdown($writeOffs) { return []; }
    private function getLoanAgeBreakdown($writeOffs) { return []; }
    private function getTopRecoveryOfficers($recoveries) { return []; }
}