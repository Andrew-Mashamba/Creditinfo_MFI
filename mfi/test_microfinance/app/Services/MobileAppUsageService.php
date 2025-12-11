<?php

namespace App\Services;

use App\Models\UserLoginHistory;
use App\Models\WebPortalUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MobileAppUsageService
{
    /**
     * Get mobile app usage statistics for a given period
     */
    public function getMobileAppUsageStats($period = 'month', $startDate = null, $endDate = null)
    {
        // Get mobile app usage from user_action_logs (login actions)
        $query = DB::table('user_action_logs')
            ->where('action_type', 'login');

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

        $totalLogins = $query->count();
        $uniqueUsers = $query->clone()->distinct('user_id')->count();
        $activeSessions = $totalLogins; // Simplified - assume all logins are active sessions
        $failedLogins = 0; // No failed login tracking in user_action_logs

        // Calculate success rate
        $totalAttempts = $totalLogins + $failedLogins;
        $successRate = $totalAttempts > 0 ? round(($totalLogins / $totalAttempts) * 100, 2) : 0;

        // Get web portal users (members using the portal)
        $webPortalUsers = WebPortalUser::where('is_active', true)->count();

        return [
            'total_logins' => $totalLogins,
            'unique_users' => $uniqueUsers,
            'active_sessions' => $activeSessions,
            'failed_logins' => $failedLogins,
            'success_rate' => $successRate,
            'web_portal_users' => $webPortalUsers,
            'period' => $period,
            'start_date' => $startDate ?? $this->getPeriodStartDate($period),
            'end_date' => $endDate ?? now()
        ];
    }

    /**
     * Get mobile app usage for today
     */
    public function getTodayMobileAppUsage()
    {
        return $this->getMobileAppUsageStats('today');
    }

    /**
     * Get mobile app usage for current month
     */
    public function getCurrentMonthMobileAppUsage()
    {
        return $this->getMobileAppUsageStats('month');
    }

    /**
     * Get mobile app usage by device type
     */
    public function getMobileAppUsageByDevice($period = 'month')
    {
        $query = DB::table('user_action_logs')
            ->where('action_type', 'login');

        // Set date range
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

        // Since we don't have device info in user_action_logs, return simplified data
        $totalLogins = $query->count();
        $uniqueUsers = $query->distinct('user_id')->count();

        return collect([
            'Web Portal' => [
                'logins' => $totalLogins,
                'unique_users' => $uniqueUsers
            ]
        ]);
    }

    /**
     * Calculate mobile app costs based on usage
     */
    public function calculateMobileAppCosts($userCount, $costPerUser = 0)
    {
        return $userCount * $costPerUser;
    }

    /**
     * Get mobile app service billing information
     */
    public function getMobileAppBillingInfo($period = 'month')
    {
        $usage = $this->getMobileAppUsageStats($period);
        $costPerActiveUser = 0; // Mobile app service is free
        
        $activeUsers = $usage['unique_users'];
        $totalCost = $this->calculateMobileAppCosts($activeUsers, $costPerActiveUser);

        return [
            'base_price' => 0, // No base price - mobile app service is free
            'included_users' => 0, // Not applicable for free service
            'active_users' => $activeUsers,
            'extra_users' => $activeUsers, // All users are "extra" since there's no base
            'cost_per_user' => $costPerActiveUser,
            'extra_cost' => $totalCost,
            'total_cost' => $totalCost,
            'usage_percentage' => 0, // Not applicable for free service
            'total_logins' => $usage['total_logins'],
            'success_rate' => $usage['success_rate'],
            'period' => $period
        ];
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
     * Get mobile app service health
     */
    public function getMobileAppServiceHealth()
    {
        $lastWeek = $this->getMobileAppUsageStats('week');
        $successRate = $lastWeek['success_rate'];
        
        // Calculate health score based on success rate and user engagement
        $userEngagement = $lastWeek['unique_users'] > 0 ? min(100, ($lastWeek['total_logins'] / $lastWeek['unique_users']) * 10) : 0;
        $healthScore = ($successRate + $userEngagement) / 2;

        return [
            'health_score' => round($healthScore, 1),
            'status' => $healthScore >= 90 ? 'excellent' : ($healthScore >= 80 ? 'good' : ($healthScore >= 70 ? 'fair' : 'poor')),
            'success_rate' => $successRate,
            'user_engagement' => round($userEngagement, 1),
            'last_updated' => now()
        ];
    }

    /**
     * Get mobile app usage trends for the last N days
     */
    public function getMobileAppUsageTrends($days = 30)
    {
        $trends = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayStats = $this->getMobileAppUsageStats('day', $date->startOfDay(), $date->endOfDay());
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'total_logins' => $dayStats['total_logins'],
                'unique_users' => $dayStats['unique_users'],
                'success_rate' => $dayStats['success_rate']
            ];
        }

        return $trends;
    }
}
