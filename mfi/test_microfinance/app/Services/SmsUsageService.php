<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsUsageService
{
    /**
     * Get SMS usage statistics for a given period
     */
    public function getSmsUsageStats($period = 'month', $startDate = null, $endDate = null)
    {
        $query = NotificationLog::where('channel', 'sms');

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

        $totalSms = $query->count();
        $sentSms = $query->clone()->where('status', 'sent')->count();
        $deliveredSms = $query->clone()->where('status', 'delivered')->count();
        $failedSms = $query->clone()->where('status', 'failed')->count();
        $pendingSms = $query->clone()->where('status', 'pending')->count();

        // Calculate success rate
        $successRate = $totalSms > 0 ? round(($deliveredSms / $totalSms) * 100, 2) : 0;

        return [
            'total' => $totalSms,
            'sent' => $sentSms,
            'delivered' => $deliveredSms,
            'failed' => $failedSms,
            'pending' => $pendingSms,
            'success_rate' => $successRate,
            'period' => $period,
            'start_date' => $startDate ?? $this->getPeriodStartDate($period),
            'end_date' => $endDate ?? now()
        ];
    }

    /**
     * Get SMS usage for today
     */
    public function getTodaySmsUsage()
    {
        return $this->getSmsUsageStats('today');
    }

    /**
     * Get SMS usage for current month
     */
    public function getCurrentMonthSmsUsage()
    {
        return $this->getSmsUsageStats('month');
    }

    /**
     * Get SMS usage trends for the last N days
     */
    public function getSmsUsageTrends($days = 30)
    {
        $trends = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayStats = $this->getSmsUsageStats('day', $date->startOfDay(), $date->endOfDay());
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'total' => $dayStats['total'],
                'delivered' => $dayStats['delivered'],
                'failed' => $dayStats['failed']
            ];
        }

        return $trends;
    }

    /**
     * Get SMS usage by notification type
     */
    public function getSmsUsageByType($period = 'month')
    {
        $query = NotificationLog::where('channel', 'sms');

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

        return $query->select('notification_type', DB::raw('count(*) as count'))
            ->groupBy('notification_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->notification_type => $item->count];
            });
    }

    /**
     * Calculate SMS costs based on usage
     */
    public function calculateSmsCosts($smsCount, $costPerSms = 15)
    {
        return $smsCount * $costPerSms;
    }

    /**
     * Get SMS service billing information
     */
    public function getSmsBillingInfo($period = 'month')
    {
        $usage = $this->getSmsUsageStats($period);
        $costPerSms = 15; // Cost per SMS in TZS
        
        $totalSms = $usage['delivered']; // Only count delivered SMS for billing
        $totalCost = $this->calculateSmsCosts($totalSms, $costPerSms);

        return [
            'base_price' => 0, // No base price - purely usage-based
            'included_sms' => 0, // No included SMS
            'used_sms' => $totalSms,
            'extra_sms' => $totalSms, // All SMS are "extra" since there's no base
            'cost_per_sms' => $costPerSms,
            'extra_cost' => $totalCost,
            'total_cost' => $totalCost,
            'usage_percentage' => 0, // Not applicable for pure usage-based pricing
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
     * Get SMS service status and health
     */
    public function getSmsServiceHealth()
    {
        $last24Hours = $this->getSmsUsageStats('day', now()->subDay(), now());
        $lastWeek = $this->getSmsUsageStats('week');
        
        $avgDailyUsage = $lastWeek['total'] / 7;
        $currentDailyUsage = $last24Hours['total'];
        
        // Calculate health score based on success rate and usage patterns
        $successRate = $lastWeek['success_rate'];
        $usageHealth = $currentDailyUsage <= ($avgDailyUsage * 1.5) ? 100 : 80; // Penalize if usage spikes too much
        $healthScore = ($successRate + $usageHealth) / 2;

        return [
            'health_score' => round($healthScore, 1),
            'status' => $healthScore >= 90 ? 'excellent' : ($healthScore >= 80 ? 'good' : ($healthScore >= 70 ? 'fair' : 'poor')),
            'success_rate' => $successRate,
            'avg_daily_usage' => round($avgDailyUsage, 1),
            'current_daily_usage' => $currentDailyUsage,
            'last_updated' => now()
        ];
    }
}
