<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailUsageService
{
    /**
     * Get email usage statistics for a given period
     */
    public function getEmailUsageStats($period = 'month', $startDate = null, $endDate = null)
    {
        $query = DB::table('emails');

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

        $totalEmails = $query->count();
        $sentEmails = $query->clone()->where('is_sent', true)->count();
        $deliveredEmails = $query->clone()->where('is_sent', true)->whereNotNull('delivery_receipt_sent_at')->count();
        $failedEmails = $query->clone()->whereNotNull('smtp_error')->count();
        $pendingEmails = $query->clone()->where('is_sent', false)->whereNull('smtp_error')->count();

        // Calculate success rate
        $successRate = $totalEmails > 0 ? round(($deliveredEmails / $totalEmails) * 100, 2) : 0;

        return [
            'total' => $totalEmails,
            'sent' => $sentEmails,
            'delivered' => $deliveredEmails,
            'failed' => $failedEmails,
            'pending' => $pendingEmails,
            'success_rate' => $successRate,
            'period' => $period,
            'start_date' => $startDate ?? $this->getPeriodStartDate($period),
            'end_date' => $endDate ?? now()
        ];
    }

    /**
     * Get email usage for today
     */
    public function getTodayEmailUsage()
    {
        return $this->getEmailUsageStats('today');
    }

    /**
     * Get email usage for current month
     */
    public function getCurrentMonthEmailUsage()
    {
        return $this->getEmailUsageStats('month');
    }

    /**
     * Get email usage by notification type
     */
    public function getEmailUsageByType($period = 'month')
    {
        $query = NotificationLog::where('channel', 'email');

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
     * Calculate email costs based on usage
     */
    public function calculateEmailCosts($emailCount, $costPerEmail = 0)
    {
        return $emailCount * $costPerEmail;
    }

    /**
     * Get email service billing information
     */
    public function getEmailBillingInfo($period = 'month')
    {
        $usage = $this->getEmailUsageStats($period);
        $costPerEmail = 0; // Email service is free
        
        $totalEmails = $usage['delivered']; // Only count delivered emails for billing
        $totalCost = $this->calculateEmailCosts($totalEmails, $costPerEmail);

        return [
            'base_price' => 0, // No base price - email service is free
            'included_emails' => 0, // Not applicable for free service
            'used_emails' => $totalEmails,
            'extra_emails' => $totalEmails, // All emails are "extra" since there's no base
            'cost_per_email' => $costPerEmail,
            'extra_cost' => $totalCost,
            'total_cost' => $totalCost,
            'usage_percentage' => 0, // Not applicable for free service
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
     * Get email service health
     */
    public function getEmailServiceHealth()
    {
        $lastWeek = $this->getEmailUsageStats('week');
        $successRate = $lastWeek['success_rate'];
        
        // Calculate health score based on success rate
        $healthScore = $successRate;

        return [
            'health_score' => round($healthScore, 1),
            'status' => $healthScore >= 95 ? 'excellent' : ($healthScore >= 90 ? 'good' : ($healthScore >= 80 ? 'fair' : 'poor')),
            'success_rate' => $successRate,
            'last_updated' => now()
        ];
    }
}
