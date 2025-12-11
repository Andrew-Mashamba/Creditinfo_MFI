<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ControlNumbersUsageService
{
    /**
     * Get control numbers usage statistics for a given period
     */
    public function getControlNumbersUsageStats($period = 'month', $startDate = null, $endDate = null)
    {
        $query = DB::table('bills')->whereNotNull('control_number');

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

        $totalControlNumbers = $query->count();
        $pendingControlNumbers = $query->clone()->where('status', 'PENDING')->count();
        $paidControlNumbers = $query->clone()->where('status', 'PAID')->count();
        $overdueControlNumbers = $query->clone()->where('status', 'OVERDUE')->count();
        $cancelledControlNumbers = $query->clone()->where('status', 'CANCELLED')->count();

        // Calculate payment rate (paid / total)
        $paymentRate = $totalControlNumbers > 0 ? round(($paidControlNumbers / $totalControlNumbers) * 100, 2) : 0;

        // Calculate total amounts
        $totalAmountDue = $query->clone()->sum('amount_due');
        $totalAmountPaid = $query->clone()->sum('amount_paid');

        return [
            'total' => $totalControlNumbers,
            'pending' => $pendingControlNumbers,
            'paid' => $paidControlNumbers,
            'overdue' => $overdueControlNumbers,
            'cancelled' => $cancelledControlNumbers,
            'payment_rate' => $paymentRate,
            'total_amount_due' => $totalAmountDue,
            'total_amount_paid' => $totalAmountPaid,
            'period' => $period,
            'start_date' => $startDate ?? $this->getPeriodStartDate($period),
            'end_date' => $endDate ?? now()
        ];
    }

    /**
     * Get control numbers usage for today
     */
    public function getTodayControlNumbersUsage()
    {
        return $this->getControlNumbersUsageStats('today');
    }

    /**
     * Get control numbers usage for current month
     */
    public function getCurrentMonthControlNumbersUsage()
    {
        return $this->getControlNumbersUsageStats('month');
    }

    /**
     * Get control numbers usage by service type
     */
    public function getControlNumbersUsageByService($period = 'month')
    {
        $query = DB::table('bills')
            ->join('services', 'bills.service_id', '=', 'services.id')
            ->whereNotNull('bills.control_number');

        // Set date range
        switch ($period) {
            case 'today':
                $query->whereDate('bills.created_at', today());
                break;
            case 'week':
                $query->where('bills.created_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->where('bills.created_at', '>=', now()->startOfMonth());
                break;
            case 'year':
                $query->where('bills.created_at', '>=', now()->startOfYear());
                break;
        }

        return $query->select(
                'services.name as service_name',
                'services.code as service_code',
                DB::raw('count(*) as count'),
                DB::raw('sum(bills.amount_due) as total_amount_due'),
                DB::raw('sum(bills.amount_paid) as total_amount_paid')
            )
            ->groupBy('services.id', 'services.name', 'services.code')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->service_code => [
                        'service_name' => $item->service_name,
                        'count' => $item->count,
                        'total_amount_due' => $item->total_amount_due,
                        'total_amount_paid' => $item->total_amount_paid
                    ]
                ];
            });
    }

    /**
     * Calculate control numbers costs based on usage
     */
    public function calculateControlNumbersCosts($controlNumberCount, $costPerControlNumber = 0)
    {
        return $controlNumberCount * $costPerControlNumber;
    }

    /**
     * Get control numbers service billing information
     */
    public function getControlNumbersBillingInfo($period = 'month')
    {
        $usage = $this->getControlNumbersUsageStats($period);
        $costPerControlNumber = 0; // Control numbers service is free
        
        $totalControlNumbers = $usage['total'];
        $totalCost = $this->calculateControlNumbersCosts($totalControlNumbers, $costPerControlNumber);

        return [
            'base_price' => 0, // No base price - control numbers service is free
            'included_control_numbers' => 0, // Not applicable for free service
            'used_control_numbers' => $totalControlNumbers,
            'extra_control_numbers' => $totalControlNumbers, // All control numbers are "extra" since there's no base
            'cost_per_control_number' => $costPerControlNumber,
            'extra_cost' => $totalCost,
            'total_cost' => $totalCost,
            'usage_percentage' => 0, // Not applicable for free service
            'payment_rate' => $usage['payment_rate'],
            'total_amount_processed' => $usage['total_amount_due'],
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
     * Get control numbers service health
     */
    public function getControlNumbersServiceHealth()
    {
        $lastWeek = $this->getControlNumbersUsageStats('week');
        $paymentRate = $lastWeek['payment_rate'];
        
        // Calculate health score based on payment rate
        $healthScore = $paymentRate;

        return [
            'health_score' => round($healthScore, 1),
            'status' => $healthScore >= 90 ? 'excellent' : ($healthScore >= 80 ? 'good' : ($healthScore >= 70 ? 'fair' : 'poor')),
            'payment_rate' => $paymentRate,
            'last_updated' => now()
        ];
    }

    /**
     * Get control numbers trends for the last N days
     */
    public function getControlNumbersTrends($days = 30)
    {
        $trends = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayStats = $this->getControlNumbersUsageStats('day', $date->startOfDay(), $date->endOfDay());
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'total' => $dayStats['total'],
                'paid' => $dayStats['paid'],
                'payment_rate' => $dayStats['payment_rate']
            ];
        }

        return $trends;
    }
}
