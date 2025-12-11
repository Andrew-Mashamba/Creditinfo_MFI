<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentLinksUsageService
{
    /**
     * Get payment links usage statistics for a given period
     */
    public function getPaymentLinksUsageStats($period = 'month', $startDate = null, $endDate = null)
    {
        $query = DB::table('payment_links');

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

        $totalLinks = $query->count();
        $activeLinks = $query->clone()->where('status', 'ACTIVE')->count();
        $usedLinks = $query->clone()->where('status', 'USED')->count();
        $expiredLinks = $query->clone()->where('status', 'EXPIRED')->count();
        $cancelledLinks = $query->clone()->where('status', 'CANCELLED')->count();

        // Calculate conversion rate (used / total)
        $conversionRate = $totalLinks > 0 ? round(($usedLinks / $totalLinks) * 100, 2) : 0;

        // Calculate total amount
        $totalAmount = $query->clone()->sum('total_amount');

        return [
            'total' => $totalLinks,
            'active' => $activeLinks,
            'used' => $usedLinks,
            'expired' => $expiredLinks,
            'cancelled' => $cancelledLinks,
            'conversion_rate' => $conversionRate,
            'total_amount' => $totalAmount,
            'period' => $period,
            'start_date' => $startDate ?? $this->getPeriodStartDate($period),
            'end_date' => $endDate ?? now()
        ];
    }

    /**
     * Get payment links usage for today
     */
    public function getTodayPaymentLinksUsage()
    {
        return $this->getPaymentLinksUsageStats('today');
    }

    /**
     * Get payment links usage for current month
     */
    public function getCurrentMonthPaymentLinksUsage()
    {
        return $this->getPaymentLinksUsageStats('month');
    }

    /**
     * Get payment links usage by loan type
     */
    public function getPaymentLinksUsageByType($period = 'month')
    {
        $query = DB::table('payment_links');

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

        return $query->select(
                DB::raw('CASE WHEN loan_id IS NOT NULL THEN "loan_payment" ELSE "member_payment" END as payment_type'),
                DB::raw('count(*) as count'),
                DB::raw('sum(total_amount) as total_amount')
            )
            ->groupBy(DB::raw('CASE WHEN loan_id IS NOT NULL THEN "loan_payment" ELSE "member_payment" END'))
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->payment_type => [
                        'count' => $item->count,
                        'total_amount' => $item->total_amount
                    ]
                ];
            });
    }

    /**
     * Calculate payment links costs based on usage
     */
    public function calculatePaymentLinksCosts($linkCount, $costPerLink = 0)
    {
        return $linkCount * $costPerLink;
    }

    /**
     * Get payment links service billing information
     */
    public function getPaymentLinksBillingInfo($period = 'month')
    {
        $usage = $this->getPaymentLinksUsageStats($period);
        $costPerLink = 0; // Payment links service is free
        
        $totalLinks = $usage['total'];
        $totalCost = $this->calculatePaymentLinksCosts($totalLinks, $costPerLink);

        return [
            'base_price' => 0, // No base price - payment links service is free
            'included_links' => 0, // Not applicable for free service
            'used_links' => $totalLinks,
            'extra_links' => $totalLinks, // All links are "extra" since there's no base
            'cost_per_link' => $costPerLink,
            'extra_cost' => $totalCost,
            'total_cost' => $totalCost,
            'usage_percentage' => 0, // Not applicable for free service
            'conversion_rate' => $usage['conversion_rate'],
            'total_amount_processed' => $usage['total_amount'],
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
     * Get payment links service health
     */
    public function getPaymentLinksServiceHealth()
    {
        $lastWeek = $this->getPaymentLinksUsageStats('week');
        $conversionRate = $lastWeek['conversion_rate'];
        
        // Calculate health score based on conversion rate
        $healthScore = $conversionRate;

        return [
            'health_score' => round($healthScore, 1),
            'status' => $healthScore >= 80 ? 'excellent' : ($healthScore >= 60 ? 'good' : ($healthScore >= 40 ? 'fair' : 'poor')),
            'conversion_rate' => $conversionRate,
            'last_updated' => now()
        ];
    }

    /**
     * Get payment links trends for the last N days
     */
    public function getPaymentLinksTrends($days = 30)
    {
        $trends = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayStats = $this->getPaymentLinksUsageStats('day', $date->startOfDay(), $date->endOfDay());
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'total' => $dayStats['total'],
                'used' => $dayStats['used'],
                'conversion_rate' => $dayStats['conversion_rate']
            ];
        }

        return $trends;
    }
}
