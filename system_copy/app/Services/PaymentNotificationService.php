<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentNotificationMail;
use App\Mail\PaymentSummaryMail;

class PaymentNotificationService
{
    /**
     * Process daily payment notifications
     */
    public function processDailyNotifications()
    {
        Log::info('Starting daily payment notification process');
        
        try {
            // Process upcoming payments
            $this->processUpcomingPayments();
            
            // Process overdue payments
            $this->processOverduePayments();
            
            // Send summary to management
            $this->sendDailySummary();
            
            Log::info('Daily payment notification process completed successfully');
            
            return [
                'status' => 'success',
                'message' => 'Payment notifications processed successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Payment notification process failed: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process upcoming payments (due in next 7 days)
     */
    protected function processUpcomingPayments()
    {
        $upcomingDays = 7;
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays($upcomingDays);
        
        // Get upcoming payables
        $upcomingPayables = DB::table('trade_payables')
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$today, $endDate])
            ->where('is_enabled', true)
            ->get();
            
        Log::info('Found ' . $upcomingPayables->count() . ' upcoming payables');
        
        foreach ($upcomingPayables as $payable) {
            $daysUntilDue = Carbon::parse($payable->due_date)->diffInDays($today);
            
            // Create notification record
            $notificationId = DB::table('payable_notifications')->insertGetId([
                'type' => 'upcoming_payment',
                'category' => 'payable',
                'reference_id' => $payable->id,
                'reference_type' => 'trade_payables',
                'vendor_or_customer_name' => $payable->vendor_name,
                'amount' => $payable->balance,
                'due_date' => $payable->due_date,
                'days_until_due' => $daysUntilDue,
                'description' => "Payment due to {$payable->vendor_name} in {$daysUntilDue} days",
                'priority' => $daysUntilDue <= 2 ? 'high' : 'medium',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Send email notification
            $this->sendPaymentNotification($notificationId, $payable, 'upcoming');
        }
        
        // Get upcoming receivables
        $upcomingReceivables = DB::table('trade_receivables')
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$today, $endDate])
            ->get();
            
        Log::info('Found ' . $upcomingReceivables->count() . ' upcoming receivables');
        
        foreach ($upcomingReceivables as $receivable) {
            $daysUntilDue = Carbon::parse($receivable->due_date)->diffInDays($today);
            
            // Create notification record
            $notificationId = DB::table('payable_notifications')->insertGetId([
                'type' => 'upcoming_payment',
                'category' => 'receivable',
                'reference_id' => $receivable->id,
                'reference_type' => 'trade_receivables',
                'vendor_or_customer_name' => $receivable->customer_name ?? 'Customer',
                'amount' => $receivable->balance ?? $receivable->amount,
                'due_date' => $receivable->due_date,
                'days_until_due' => $daysUntilDue,
                'description' => "Payment expected from customer in {$daysUntilDue} days",
                'priority' => $daysUntilDue <= 2 ? 'high' : 'medium',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Send email notification
            $this->sendPaymentNotification($notificationId, $receivable, 'upcoming_receivable');
        }
    }
    
    /**
     * Process overdue payments
     */
    protected function processOverduePayments()
    {
        $today = Carbon::today();
        
        // Get overdue payables
        $overduePayables = DB::table('trade_payables')
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', $today)
            ->where('is_enabled', true)
            ->get();
            
        Log::info('Found ' . $overduePayables->count() . ' overdue payables');
        
        foreach ($overduePayables as $payable) {
            $daysOverdue = Carbon::parse($payable->due_date)->diffInDays($today);
            
            // Create notification record
            $notificationId = DB::table('payable_notifications')->insertGetId([
                'type' => 'overdue_payment',
                'category' => 'payable',
                'reference_id' => $payable->id,
                'reference_type' => 'trade_payables',
                'vendor_or_customer_name' => $payable->vendor_name,
                'amount' => $payable->balance,
                'due_date' => $payable->due_date,
                'days_until_due' => -$daysOverdue, // Negative for overdue
                'description' => "Payment to {$payable->vendor_name} is {$daysOverdue} days overdue",
                'priority' => $daysOverdue > 30 ? 'urgent' : 'high',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Send email notification
            $this->sendPaymentNotification($notificationId, $payable, 'overdue');
        }
        
        // Get overdue receivables
        $overdueReceivables = DB::table('trade_receivables')
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', $today)
            ->get();
            
        Log::info('Found ' . $overdueReceivables->count() . ' overdue receivables');
        
        foreach ($overdueReceivables as $receivable) {
            $daysOverdue = Carbon::parse($receivable->due_date)->diffInDays($today);
            
            // Create notification record
            $notificationId = DB::table('payable_notifications')->insertGetId([
                'type' => 'overdue_payment',
                'category' => 'receivable',
                'reference_id' => $receivable->id,
                'reference_type' => 'trade_receivables',
                'vendor_or_customer_name' => $receivable->customer_name ?? 'Customer',
                'amount' => $receivable->balance ?? $receivable->amount,
                'due_date' => $receivable->due_date,
                'days_until_due' => -$daysOverdue, // Negative for overdue
                'description' => "Payment from customer is {$daysOverdue} days overdue",
                'priority' => $daysOverdue > 30 ? 'urgent' : 'high',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Send email notification
            $this->sendPaymentNotification($notificationId, $receivable, 'overdue_receivable');
        }
    }
    
    /**
     * Send individual payment notification
     */
    protected function sendPaymentNotification($notificationId, $payment, $type)
    {
        try {
            $recipients = [];
            
            // Get accounting email
            $accountingEmail = env('ACCOUNTING_EMAIL');
            if ($accountingEmail) {
                $recipients[] = $accountingEmail;
            }
            
            // For urgent/overdue, also notify management
            if (in_array($type, ['overdue', 'overdue_receivable'])) {
                $managementEmail = env('MANAGEMENT_EMAIL');
                if ($managementEmail) {
                    $recipients[] = $managementEmail;
                }
            }
            
            if (empty($recipients)) {
                Log::warning('No recipients configured for payment notifications');
                return;
            }
            
            // Send email
            Mail::to($recipients)->send(new PaymentNotificationMail($payment, $type));
            
            // Update notification status
            DB::table('payable_notifications')
                ->where('id', $notificationId)
                ->update([
                    'notification_status' => 'sent',
                    'sent_at' => now(),
                    'recipients' => json_encode($recipients),
                    'updated_at' => now()
                ]);
                
            Log::info("Payment notification sent for {$type}", [
                'notification_id' => $notificationId,
                'recipients' => $recipients
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment notification: ' . $e->getMessage());
            
            // Update notification status
            DB::table('payable_notifications')
                ->where('id', $notificationId)
                ->update([
                    'notification_status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);
        }
    }
    
    /**
     * Send daily summary to management
     */
    protected function sendDailySummary()
    {
        $today = Carbon::today();
        
        // Get summary statistics
        $summary = [
            'date' => $today->format('Y-m-d'),
            'upcoming_payables' => DB::table('trade_payables')
                ->where('status', '!=', 'paid')
                ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
                ->where('is_enabled', true)
                ->selectRaw('COUNT(*) as count, SUM(balance) as total')
                ->first(),
            'overdue_payables' => DB::table('trade_payables')
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', $today)
                ->where('is_enabled', true)
                ->selectRaw('COUNT(*) as count, SUM(balance) as total')
                ->first(),
            'upcoming_receivables' => DB::table('trade_receivables')
                ->where('status', '!=', 'paid')
                ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance), SUM(amount)) as total')
                ->first(),
            'overdue_receivables' => DB::table('trade_receivables')
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', $today)
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance), SUM(amount)) as total')
                ->first(),
        ];
        
        // Get detailed lists for the summary
        $summary['upcoming_payables_list'] = DB::table('trade_payables')
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->where('is_enabled', true)
            ->orderBy('due_date')
            ->limit(10)
            ->get();
            
        $summary['overdue_payables_list'] = DB::table('trade_payables')
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', $today)
            ->where('is_enabled', true)
            ->orderBy('due_date')
            ->limit(10)
            ->get();
        
        // Send summary email to management
        $recipients = [];
        
        $managementEmail = env('MANAGEMENT_EMAIL');
        if ($managementEmail) {
            $recipients[] = $managementEmail;
        }
        
        $accountingEmail = env('ACCOUNTING_EMAIL');
        if ($accountingEmail) {
            $recipients[] = $accountingEmail;
        }
        
        if (!empty($recipients)) {
            try {
                Mail::to($recipients)->send(new PaymentSummaryMail($summary));
                Log::info('Daily payment summary sent to management', ['recipients' => $recipients]);
            } catch (\Exception $e) {
                Log::error('Failed to send daily summary: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Get notifications for display in UI
     */
    public function getNotifications($filters = [])
    {
        $query = DB::table('payable_notifications');
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }
        
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        // Return array instead of paginated results for Livewire compatibility
        return $query->orderBy('created_at', 'desc')
                    ->limit(50) // Limit to reasonable number
                    ->get()
                    ->toArray();
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        return DB::table('payable_notifications')
            ->where('id', $notificationId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'read_by' => auth()->id(),
                'updated_at' => now()
            ]);
    }
    
    /**
     * Get notification statistics
     */
    public function getStatistics()
    {
        return [
            'total_unread' => DB::table('payable_notifications')
                ->where('is_read', false)
                ->count(),
            'urgent_unread' => DB::table('payable_notifications')
                ->where('is_read', false)
                ->where('priority', 'urgent')
                ->count(),
            'overdue_count' => DB::table('payable_notifications')
                ->where('type', 'overdue_payment')
                ->where('is_read', false)
                ->count(),
            'upcoming_count' => DB::table('payable_notifications')
                ->where('type', 'upcoming_payment')
                ->where('is_read', false)
                ->count(),
        ];
    }
}