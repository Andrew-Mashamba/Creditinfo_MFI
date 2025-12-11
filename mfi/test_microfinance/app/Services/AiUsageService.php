<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiUsageService
{
    /**
     * Get AI usage statistics for a given period
     */
    public function getAiUsageStats($period = 'month', $startDate = null, $endDate = null)
    {
        try {
            // Since there's no specific AI usage table yet, we'll use activity logs
            // or create placeholder data until proper AI logging is implemented
            $query = DB::table('activity_log')
                ->where('description', 'LIKE', '%AI%')
                ->orWhere('description', 'LIKE', '%ai%')
                ->orWhere('description', 'LIKE', '%claude%')
                ->orWhere('description', 'LIKE', '%chat%');

            // Set date range based on period
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                switch ($period) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->where('created_at', '>=', now()->startOfWeek());
                        break;
                    case 'month':
                        $query->where('created_at', '>=', now()->startOfMonth());
                        break;
                    case 'year':
                        $query->where('created_at', '>=', now()->startOfYear());
                        break;
                }
            }

            $totalQueries = $query->count();
            $uniqueSessions = $query->distinct('session_id')->count('session_id');
            $uniqueUsers = $query->distinct('causer_id')->count('causer_id');

            return [
                'total_queries' => $totalQueries,
                'unique_sessions' => $uniqueSessions,
                'unique_users' => $uniqueUsers,
                'period' => $period,
                'start_date' => $startDate ?? $this->getPeriodStartDate($period),
                'end_date' => $endDate ?? now()
            ];

        } catch (\Exception $e) {
            // If activity_log table doesn't exist or has issues, return default values
            Log::warning('AI usage stats query failed, returning defaults: ' . $e->getMessage());
            
            return [
                'total_queries' => 0,
                'unique_sessions' => 0,
                'unique_users' => 0,
                'period' => $period,
                'start_date' => $startDate ?? $this->getPeriodStartDate($period),
                'end_date' => $endDate ?? now()
            ];
        }
    }

    /**
     * Get AI usage for today
     */
    public function getTodayAiUsage()
    {
        return $this->getAiUsageStats('today');
    }

    /**
     * Get AI usage for current month
     */
    public function getCurrentMonthAiUsage()
    {
        return $this->getAiUsageStats('month');
    }

    /**
     * Get AI usage trends for the last N days
     */
    public function getAiUsageTrends($days = 30)
    {
        $trends = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayStats = $this->getAiUsageStats('day', $date->startOfDay(), $date->endOfDay());
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'total_queries' => $dayStats['total_queries'],
                'unique_sessions' => $dayStats['unique_sessions'],
                'unique_users' => $dayStats['unique_users']
            ];
        }

        return $trends;
    }

    /**
     * Calculate AI costs based on usage
     * Placeholder pricing model - adjust as needed
     */
    public function calculateAiCosts($queryCount, $costPerQuery = 0.5)
    {
        return $queryCount * $costPerQuery;
    }

    /**
     * Get AI service billing information
     */
    public function getAiBillingInfo($period = 'month')
    {
        $usage = $this->getAiUsageStats($period);
        $costPerQuery = 0.5; // Cost per query in TZS - adjust as needed
        
        $totalQueries = $usage['total_queries'];
        $totalCost = $this->calculateAiCosts($totalQueries, $costPerQuery);

        return [
            'base_price' => 0, // No base price - purely usage-based
            'included_queries' => 0, // No included queries
            'used_queries' => $totalQueries,
            'extra_queries' => $totalQueries,
            'cost_per_query' => $costPerQuery,
            'extra_cost' => $totalCost,
            'total_cost' => $totalCost,
            'usage_percentage' => 0, // Not applicable for pure usage-based pricing
            'period' => $period
        ];
    }

    /**
     * Log AI usage (call this whenever AI service is used)
     */
    public function logAiUsage($userId, $queryType, $sessionId = null, $metadata = [])
    {
        try {
            // This would typically go to a dedicated ai_usage_logs table
            // For now, we'll use activity log if available
            if (class_exists('\Spatie\Activitylog\Models\Activity')) {
                activity()
                    ->causedBy($userId)
                    ->withProperties(array_merge([
                        'query_type' => $queryType,
                        'session_id' => $sessionId ?? session()->getId(),
                        'timestamp' => now()
                    ], $metadata))
                    ->log('AI Query: ' . $queryType);
            } else {
                // Fallback to Laravel logs
                Log::info('AI Usage', [
                    'user_id' => $userId,
                    'query_type' => $queryType,
                    'session_id' => $sessionId ?? session()->getId(),
                    'metadata' => $metadata,
                    'timestamp' => now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log AI usage: ' . $e->getMessage());
        }
    }

    /**
     * Get period start date
     */
    private function getPeriodStartDate($period)
    {
        switch ($period) {
            case 'today':
                return now()->startOfDay();
            case 'week':
                return now()->startOfWeek();
            case 'month':
                return now()->startOfMonth();
            case 'year':
                return now()->startOfYear();
            default:
                return now()->startOfMonth();
        }
    }

    /**
     * Get AI service status and health
     */
    public function getAiServiceHealth()
    {
        $last24Hours = $this->getAiUsageStats('day', now()->subDay(), now());
        $lastWeek = $this->getAiUsageStats('week');
        
        $avgDailyUsage = $lastWeek['total_queries'] / 7;
        $currentDailyUsage = $last24Hours['total_queries'];
        
        // Calculate health score based on usage patterns and availability
        $usageHealth = 100; // Assume healthy unless issues detected
        if ($currentDailyUsage > ($avgDailyUsage * 2)) {
            $usageHealth = 80; // Penalize if usage spikes too much (might indicate issues)
        }
        
        $healthScore = $usageHealth;

        return [
            'health_score' => round($healthScore, 1),
            'status' => $healthScore >= 90 ? 'excellent' : ($healthScore >= 80 ? 'good' : ($healthScore >= 70 ? 'fair' : 'poor')),
            'avg_daily_usage' => round($avgDailyUsage, 1),
            'current_daily_usage' => $currentDailyUsage,
            'unique_users_today' => $last24Hours['unique_users'],
            'last_updated' => now()
        ];
    }
}