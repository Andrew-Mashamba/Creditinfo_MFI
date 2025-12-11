<?php

namespace App\Http\Livewire\ActiveLoan\ArrearsDashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendsForecasting extends Component
{
    // Historical trends (last 12 months)
    public $monthlyArrearsTrend = [];
    public $monthlyCollectionTrend = [];
    public $monthlyPARTrend = [];
    
    // Weekly trends (last 8 weeks)
    public $weeklyArrearsTrend = [];
    public $weeklyNewArrears = [];
    public $weeklyRecoveries = [];
    
    // Forecasting metrics
    public $predictedArrears30Days = 0;
    public $predictedArrears60Days = 0;
    public $predictedArrears90Days = 0;
    public $predictedCollectionRate = 0;
    
    // Seasonality analysis
    public $bestCollectionMonth = '';
    public $worstCollectionMonth = '';
    public $averageMonthlyArrears = 0;
    public $arrearsVolatility = 0;
    
    // Trend indicators
    public $arrearsTrendDirection = 'stable'; // up, down, stable
    public $collectionTrendDirection = 'stable';
    public $riskTrendDirection = 'stable';
    
    // Recovery probability
    public $recoveryProbability = [];
    
    public function mount()
    {
        $this->loadTrendsAndForecasting();
    }
    
    private function loadTrendsAndForecasting()
    {
        // Load monthly trends for the last 12 months
        $this->loadMonthlyTrends();
        
        // Load weekly trends for the last 8 weeks
        $this->loadWeeklyTrends();
        
        // Calculate forecasts
        $this->calculateForecasts();
        
        // Analyze seasonality
        $this->analyzeSeasonality();
        
        // Determine trend directions
        $this->determineTrendDirections();
        
        // Calculate recovery probabilities
        $this->calculateRecoveryProbabilities();
    }
    
    private function loadMonthlyTrends()
    {
        $startDate = Carbon::now()->subMonths(12)->startOfMonth();
        
        for ($i = 0; $i < 12; $i++) {
            $monthStart = $startDate->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthName = $monthStart->format('M Y');
            
            // Get arrears data for this month
            $monthData = DB::table('loans_schedules')
                ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
                ->whereBetween('loans_schedules.installment_date', [$monthStart, $monthEnd])
                ->select(
                    DB::raw('COUNT(CASE WHEN loans_schedules.days_in_arrears > 0 THEN 1 END) as arrears_count'),
                    DB::raw('SUM(CASE WHEN loans_schedules.days_in_arrears > 0 THEN COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) ELSE 0 END) as arrears_amount'),
                    DB::raw('SUM(loans_schedules.installment) as total_due'),
                    DB::raw('SUM(COALESCE(loans_schedules.payment, 0)) as total_collected')
                )
                ->first();
            
            // Calculate PAR for this month
            $totalPortfolio = DB::table('loans')
                ->where('status', 'ACTIVE')
                ->whereDate('created_at', '<=', $monthEnd)
                ->sum('principle');
            
            $par = $totalPortfolio > 0 && $monthData->arrears_amount > 0
                ? ($monthData->arrears_amount / $totalPortfolio) * 100
                : 0;
            
            $collectionRate = $monthData->total_due > 0
                ? ($monthData->total_collected / $monthData->total_due) * 100
                : 0;
            
            $this->monthlyArrearsTrend[] = [
                'month' => $monthName,
                'arrears_count' => $monthData->arrears_count ?? 0,
                'arrears_amount' => $monthData->arrears_amount ?? 0,
            ];
            
            $this->monthlyCollectionTrend[] = [
                'month' => $monthName,
                'collection_rate' => $collectionRate,
                'total_collected' => $monthData->total_collected ?? 0,
            ];
            
            $this->monthlyPARTrend[] = [
                'month' => $monthName,
                'par' => $par,
            ];
        }
    }
    
    private function loadWeeklyTrends()
    {
        $startDate = Carbon::now()->subWeeks(8)->startOfWeek();
        
        for ($i = 0; $i < 8; $i++) {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();
            $weekLabel = 'W' . $weekStart->format('W');
            
            // Get weekly arrears data
            $weekData = DB::table('loans_schedules')
                ->whereBetween('installment_date', [$weekStart, $weekEnd])
                ->select(
                    DB::raw('COUNT(CASE WHEN days_in_arrears > 0 THEN 1 END) as arrears_count'),
                    DB::raw('SUM(CASE WHEN days_in_arrears > 0 THEN COALESCE(amount_in_arrears, installment - COALESCE(payment, 0)) ELSE 0 END) as arrears_amount')
                )
                ->first();
            
            // Count new arrears (first time in arrears)
            $newArrears = DB::table('loans_schedules')
                ->whereBetween('installment_date', [$weekStart, $weekEnd])
                ->whereBetween('days_in_arrears', [1, 7])
                ->count();
            
            // Count recoveries (payments made on arrears)
            $recoveries = DB::table('loans_schedules')
                ->whereBetween('installment_date', [$weekStart, $weekEnd])
                ->where('days_in_arrears', '>', 0)
                ->whereNotNull('payment')
                ->where('payment', '>', 0)
                ->count();
            
            $this->weeklyArrearsTrend[] = [
                'week' => $weekLabel,
                'count' => $weekData->arrears_count ?? 0,
                'amount' => $weekData->arrears_amount ?? 0,
            ];
            
            $this->weeklyNewArrears[] = [
                'week' => $weekLabel,
                'count' => $newArrears,
            ];
            
            $this->weeklyRecoveries[] = [
                'week' => $weekLabel,
                'count' => $recoveries,
            ];
        }
    }
    
    private function calculateForecasts()
    {
        // Simple linear regression forecast based on recent trends
        $recentArrears = collect($this->monthlyArrearsTrend)->pluck('arrears_amount')->take(-3)->values();
        $avgGrowthRate = 0;
        
        if ($recentArrears->count() >= 2) {
            $growthRates = [];
            $arrearsArray = $recentArrears->toArray();
            for ($i = 1; $i < count($arrearsArray); $i++) {
                if ($arrearsArray[$i-1] > 0) {
                    $growthRates[] = ($arrearsArray[$i] - $arrearsArray[$i-1]) / $arrearsArray[$i-1];
                }
            }
            $avgGrowthRate = count($growthRates) > 0 ? array_sum($growthRates) / count($growthRates) : 0;
        }
        
        $currentArrears = $recentArrears->last() ?? 0;
        
        // Predict future arrears
        $this->predictedArrears30Days = $currentArrears * (1 + $avgGrowthRate);
        $this->predictedArrears60Days = $currentArrears * (1 + $avgGrowthRate * 2);
        $this->predictedArrears90Days = $currentArrears * (1 + $avgGrowthRate * 3);
        
        // Predict collection rate based on recent trend
        $recentCollections = collect($this->monthlyCollectionTrend)->pluck('collection_rate')->take(-3);
        $this->predictedCollectionRate = $recentCollections->avg();
    }
    
    private function analyzeSeasonality()
    {
        // Find best and worst collection months
        $collections = collect($this->monthlyCollectionTrend);
        
        $best = $collections->sortByDesc('collection_rate')->first();
        $worst = $collections->sortBy('collection_rate')->first();
        
        $this->bestCollectionMonth = $best['month'] ?? 'N/A';
        $this->worstCollectionMonth = $worst['month'] ?? 'N/A';
        
        // Calculate average monthly arrears
        $arrearsAmounts = collect($this->monthlyArrearsTrend)->pluck('arrears_amount');
        $this->averageMonthlyArrears = $arrearsAmounts->avg();
        
        // Calculate volatility (standard deviation)
        if ($arrearsAmounts->count() > 1) {
            $mean = $arrearsAmounts->avg();
            $variance = $arrearsAmounts->map(function ($value) use ($mean) {
                return pow($value - $mean, 2);
            })->avg();
            $this->arrearsVolatility = sqrt($variance);
        }
    }
    
    private function determineTrendDirections()
    {
        // Determine arrears trend
        $recentArrears = collect($this->monthlyArrearsTrend)->pluck('arrears_amount')->take(-3);
        if ($recentArrears->count() >= 3) {
            $firstHalf = $recentArrears->take(floor($recentArrears->count() / 2))->avg();
            $secondHalf = $recentArrears->skip(floor($recentArrears->count() / 2))->avg();
            
            if ($secondHalf > $firstHalf * 1.1) {
                $this->arrearsTrendDirection = 'up';
            } elseif ($secondHalf < $firstHalf * 0.9) {
                $this->arrearsTrendDirection = 'down';
            } else {
                $this->arrearsTrendDirection = 'stable';
            }
        }
        
        // Determine collection trend
        $recentCollections = collect($this->monthlyCollectionTrend)->pluck('collection_rate')->take(-3);
        if ($recentCollections->count() >= 3) {
            $firstHalf = $recentCollections->take(floor($recentCollections->count() / 2))->avg();
            $secondHalf = $recentCollections->skip(floor($recentCollections->count() / 2))->avg();
            
            if ($secondHalf > $firstHalf * 1.05) {
                $this->collectionTrendDirection = 'up';
            } elseif ($secondHalf < $firstHalf * 0.95) {
                $this->collectionTrendDirection = 'down';
            } else {
                $this->collectionTrendDirection = 'stable';
            }
        }
        
        // Risk trend based on PAR
        $recentPAR = collect($this->monthlyPARTrend)->pluck('par')->take(-3);
        if ($recentPAR->count() >= 3) {
            $firstHalf = $recentPAR->take(floor($recentPAR->count() / 2))->avg();
            $secondHalf = $recentPAR->skip(floor($recentPAR->count() / 2))->avg();
            
            if ($secondHalf > $firstHalf * 1.1) {
                $this->riskTrendDirection = 'up';
            } elseif ($secondHalf < $firstHalf * 0.9) {
                $this->riskTrendDirection = 'down';
            } else {
                $this->riskTrendDirection = 'stable';
            }
        }
    }
    
    private function calculateRecoveryProbabilities()
    {
        // Calculate recovery probability by days in arrears
        $ranges = [
            '1-7 days' => [1, 7],
            '8-30 days' => [8, 30],
            '31-60 days' => [31, 60],
            '61-90 days' => [61, 90],
            '90+ days' => [91, 9999]
        ];
        
        foreach ($ranges as $label => $range) {
            // Get total arrears in this range
            $totalInRange = DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->whereBetween('days_in_arrears', $range)
                ->count();
            
            // Get recovered arrears (those that received payment)
            $recovered = DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->whereBetween('days_in_arrears', $range)
                ->whereNotNull('payment')
                ->where('payment', '>', 0)
                ->count();
            
            $probability = $totalInRange > 0 ? ($recovered / $totalInRange) * 100 : 0;
            
            $this->recoveryProbability[] = [
                'range' => $label,
                'probability' => round($probability, 2),
                'total' => $totalInRange,
                'recovered' => $recovered
            ];
        }
    }
    
    public function refreshData()
    {
        $this->loadTrendsAndForecasting();
        session()->flash('message', 'Trends and forecasting data refreshed successfully!');
    }

    public function render()
    {
        return view('livewire.active-loan.arrears-dashboard.trends-forecasting');
    }
}
