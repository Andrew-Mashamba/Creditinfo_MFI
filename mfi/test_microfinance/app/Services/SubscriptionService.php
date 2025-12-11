<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\TradePayable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Initialize system subscriptions
     */
    public function initializeSystemSubscriptions(): void
    {
        $systemServices = Subscription::getSystemServices();
        
        foreach ($systemServices as $serviceData) {
            $existing = Subscription::where('service_code', $serviceData['service_code'])->first();
            
            if (!$existing) {
                $subscription = Subscription::create(array_merge($serviceData, [
                    'start_date' => now(),
                    'next_billing_date' => $this->calculateNextBillingDate($serviceData['billing_frequency']),
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                    'updated_by' => auth()->id() ?? 1,
                ]));
                
                Log::info('System subscription created', [
                    'service_code' => $subscription->service_code,
                    'service_name' => $subscription->service_name
                ]);
            }
        }
    }

    /**
     * Get all subscriptions with usage data
     */
    public function getAllSubscriptions(): array
    {
        $subscriptions = Subscription::with(['createdBy', 'updatedBy', 'branch'])
            ->orderBy('service_type')
            ->orderBy('service_name')
            ->get();

        $result = [];
        
        foreach ($subscriptions as $subscription) {
            $usageData = $this->getUsageData($subscription);
            
            $result[] = [
                'id' => $subscription->id,
                'name' => $subscription->service_name,
                'description' => $subscription->description,
                'type' => $subscription->subscription_type,
                'status' => $subscription->status,
                'price' => $subscription->calculateCurrentMonthCost(),
                'billing_cycle' => $subscription->billing_frequency,
                'features' => $subscription->features ?? [],
                'usage' => $usageData,
                'can_pause' => $subscription->canBePaused(),
                'can_resume' => $subscription->canBeResumed(),
                'can_cancel' => $subscription->canBeCancelled(),
                'can_restart' => $subscription->isCancelled(),
                'pricing_model' => $subscription->pricing_model,
                'base_price' => $subscription->base_price,
                'cost_per_unit' => $subscription->cost_per_unit,
                'unit_type' => $subscription->unit_type,
                'included_units' => $subscription->included_units,
                'current_usage' => $subscription->current_usage,
                'next_billing_date' => $subscription->next_billing_date,
                'last_billed_date' => $subscription->last_billed_date,
                'total_cost_paid' => $subscription->total_cost_paid,
                'is_system_service' => $subscription->is_system_service,
            ];
        }
        
        return $result;
    }

    /**
     * Get usage data for a subscription
     */
    private function getUsageData(Subscription $subscription): array
    {
        switch ($subscription->service_type) {
            case 'sms':
                return $this->getSmsUsageData();
            case 'email':
                return $this->getEmailUsageData();
            case 'control_numbers':
                return $this->getControlNumbersUsageData();
            case 'payment_links':
                return $this->getPaymentLinksUsageData();
            case 'mobile_app':
                return $this->getMobileAppUsageData();
            default:
                return [
                    'sent' => $subscription->current_usage,
                    'limit' => $subscription->included_units,
                    'percentage' => $subscription->included_units > 0 ? round(($subscription->current_usage / $subscription->included_units) * 100, 2) : 0,
                ];
        }
    }

    /**
     * Get SMS usage data
     */
    private function getSmsUsageData(): array
    {
        $smsService = new SmsUsageService();
        $usage = $smsService->getCurrentMonthSmsUsage();
        $billing = $smsService->getSmsBillingInfo('month');
        
        return [
            'sent' => $usage['delivered'],
            'limit' => $billing['included_sms'],
            'percentage' => $billing['usage_percentage'],
            'total' => $usage['total'],
            'failed' => $usage['failed'],
            'success_rate' => $usage['success_rate']
        ];
    }

    /**
     * Get Email usage data
     */
    private function getEmailUsageData(): array
    {
        $emailService = new EmailUsageService();
        $usage = $emailService->getCurrentMonthEmailUsage();
        $billing = $emailService->getEmailBillingInfo('month');
        
        return [
            'sent' => $usage['delivered'],
            'limit' => $billing['included_emails'],
            'percentage' => $billing['usage_percentage'],
            'total' => $usage['total'],
            'failed' => $usage['failed'],
            'success_rate' => $usage['success_rate']
        ];
    }

    /**
     * Get Control Numbers usage data
     */
    private function getControlNumbersUsageData(): array
    {
        $controlNumbersService = new ControlNumbersUsageService();
        $usage = $controlNumbersService->getCurrentMonthControlNumbersUsage();
        $billing = $controlNumbersService->getControlNumbersBillingInfo('month');
        
        return [
            'generated' => $usage['total'],
            'limit' => $billing['included_control_numbers'],
            'percentage' => $billing['usage_percentage'],
            'paid' => $usage['paid'],
            'pending' => $usage['pending'],
            'payment_rate' => $usage['payment_rate']
        ];
    }

    /**
     * Get Payment Links usage data
     */
    private function getPaymentLinksUsageData(): array
    {
        $paymentLinksService = new PaymentLinksUsageService();
        $usage = $paymentLinksService->getCurrentMonthPaymentLinksUsage();
        $billing = $paymentLinksService->getPaymentLinksBillingInfo('month');
        
        return [
            'created' => $usage['total'],
            'limit' => $billing['included_links'],
            'percentage' => $billing['usage_percentage'],
            'used' => $usage['used'],
            'active' => $usage['active'],
            'conversion_rate' => $usage['conversion_rate']
        ];
    }

    /**
     * Get Mobile App usage data
     */
    private function getMobileAppUsageData(): array
    {
        $mobileAppService = new MobileAppUsageService();
        $usage = $mobileAppService->getCurrentMonthMobileAppUsage();
        $billing = $mobileAppService->getMobileAppBillingInfo('month');
        
        return [
            'active_users' => $usage['unique_users'],
            'total_logins' => $usage['total_logins'],
            'success_rate' => $usage['success_rate'],
            'web_portal_users' => $usage['web_portal_users']
        ];
    }

    /**
     * Pause a subscription
     */
    public function pauseSubscription(int $subscriptionId): bool
    {
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            return false;
        }

        $result = $subscription->pause();
        
        if ($result) {
            Log::info('Subscription paused', [
                'subscription_id' => $subscriptionId,
                'service_name' => $subscription->service_name,
                'user_id' => auth()->id()
            ]);
        }
        
        return $result;
    }

    /**
     * Resume a subscription
     */
    public function resumeSubscription(int $subscriptionId): bool
    {
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            return false;
        }

        $result = $subscription->resume();
        
        if ($result) {
            Log::info('Subscription resumed', [
                'subscription_id' => $subscriptionId,
                'service_name' => $subscription->service_name,
                'user_id' => auth()->id()
            ]);
        }
        
        return $result;
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(int $subscriptionId): bool
    {
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            return false;
        }

        $result = $subscription->cancel();
        
        if ($result) {
            Log::info('Subscription cancelled', [
                'subscription_id' => $subscriptionId,
                'service_name' => $subscription->service_name,
                'user_id' => auth()->id()
            ]);
        }
        
        return $result;
    }

    /**
     * Restart a subscription
     */
    public function restartSubscription(int $subscriptionId): bool
    {
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            return false;
        }

        $result = $subscription->restart();
        
        if ($result) {
            Log::info('Subscription restarted', [
                'subscription_id' => $subscriptionId,
                'service_name' => $subscription->service_name,
                'user_id' => auth()->id()
            ]);
        }
        
        return $result;
    }

    /**
     * Process all due subscriptions for billing
     */
    public function processDueSubscriptions(): array
    {
        $dueSubscriptions = Subscription::dueForBilling()->get();
        $processed = [];
        $errors = [];

        foreach ($dueSubscriptions as $subscription) {
            try {
                $cost = $subscription->calculateCurrentMonthCost();
                
                // Update subscription billing info
                $subscription->update([
                    'last_billed_date' => now(),
                    'next_billing_date' => $subscription->calculateNextBillingDate(),
                    'current_usage' => 0,
                    'current_month_cost' => 0,
                    'total_cost_paid' => $subscription->total_cost_paid + $cost,
                    'updated_by' => auth()->id() ?? 1,
                ]);

                // Create trade payable record if needed
                if ($cost > 0) {
                    $this->createTradePayableRecord($subscription, $cost);
                }

                $processed[] = [
                    'subscription_id' => $subscription->id,
                    'service_name' => $subscription->service_name,
                    'amount' => $cost,
                    'next_billing_date' => $subscription->next_billing_date,
                ];

                Log::info('Subscription billing processed', [
                    'subscription_id' => $subscription->id,
                    'service_name' => $subscription->service_name,
                    'amount' => $cost
                ]);

            } catch (\Exception $e) {
                $errors[] = [
                    'subscription_id' => $subscription->id,
                    'service_name' => $subscription->service_name,
                    'error' => $e->getMessage()
                ];

                Log::error('Failed to process subscription billing', [
                    'subscription_id' => $subscription->id,
                    'service_name' => $subscription->service_name,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
            'total_processed' => count($processed),
            'total_errors' => count($errors)
        ];
    }

    /**
     * Create trade payable record for subscription billing
     */
    private function createTradePayableRecord(Subscription $subscription, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        // Check if trade_payables table exists
        if (!DB::getSchemaBuilder()->hasTable('trade_payables')) {
            Log::warning('trade_payables table does not exist, skipping trade payable creation');
            return;
        }

        $tradePayable = TradePayable::create([
            'vendor_name' => $subscription->vendor_name ?? $subscription->service_name,
            'vendor_email' => $subscription->vendor_email,
            'vendor_phone' => $subscription->vendor_phone,
            'bill_number' => $this->generateBillNumber($subscription),
            'bill_date' => now(),
            'due_date' => now()->addDays(30),
            'amount' => $amount,
            'paid_amount' => 0,
            'balance' => $amount,
            'payment_terms' => 30,
            'description' => $subscription->service_name . ' - Monthly subscription billing for ' . now()->format('F Y'),
            'status' => 'pending',
            'is_recurring' => true,
            'recurring_frequency' => $subscription->billing_frequency,
            'recurring_start_date' => $subscription->start_date,
            'recurring_end_date' => $subscription->end_date,
            'next_billing_date' => $subscription->next_billing_date,
            'service_type' => $subscription->service_type,
            'subscription_status' => $subscription->status,
            'parent_subscription_id' => $subscription->id,
            'created_by' => auth()->id() ?? 1,
            'updated_by' => auth()->id() ?? 1,
        ]);

        // Update subscription with trade payable reference
        $subscription->update(['trade_payable_id' => $tradePayable->id]);
    }

    /**
     * Generate bill number for subscription
     */
    private function generateBillNumber(Subscription $subscription): string
    {
        $prefix = 'SUB-' . strtoupper($subscription->service_code);
        $date = now()->format('Ymd');
        $sequence = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $date . '-' . $sequence;
    }

    /**
     * Calculate next billing date based on frequency
     */
    private function calculateNextBillingDate(string $frequency): Carbon
    {
        switch ($frequency) {
            case 'monthly':
                return now()->addMonth();
            case 'quarterly':
                return now()->addMonths(3);
            case 'annually':
                return now()->addYear();
            default:
                return now()->addMonth();
        }
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats(): array
    {
        $subscriptions = Subscription::all();
        
        return [
            'total_subscriptions' => $subscriptions->count(),
            'active_subscriptions' => $subscriptions->where('status', 'active')->count(),
            'paused_subscriptions' => $subscriptions->where('status', 'paused')->count(),
            'cancelled_subscriptions' => $subscriptions->where('status', 'cancelled')->count(),
            'mandatory_subscriptions' => $subscriptions->where('subscription_type', 'mandatory')->count(),
            'optional_subscriptions' => $subscriptions->where('subscription_type', 'optional')->count(),
            'system_services' => $subscriptions->where('is_system_service', true)->count(),
            'user_services' => $subscriptions->where('is_system_service', false)->count(),
            'monthly_recurring_revenue' => $subscriptions->where('billing_frequency', 'monthly')->sum('current_month_cost'),
            'quarterly_recurring_revenue' => $subscriptions->where('billing_frequency', 'quarterly')->sum('current_month_cost'),
            'annual_recurring_revenue' => $subscriptions->where('billing_frequency', 'annually')->sum('current_month_cost'),
            'total_revenue' => $subscriptions->sum('total_cost_paid'),
            'due_for_billing' => $subscriptions->filter(function ($sub) {
                return $sub->isDueForBilling();
            })->count(),
        ];
    }

    /**
     * Update subscription usage
     */
    public function updateSubscriptionUsage(string $serviceType, int $usage): void
    {
        $subscription = Subscription::where('service_type', $serviceType)
            ->where('status', 'active')
            ->first();

        if ($subscription) {
            $subscription->updateUsage($usage);
        }
    }

    /**
     * Sync subscription with trade payables
     */
    public function syncWithTradePayables(): void
    {
        $subscriptions = Subscription::whereNull('trade_payable_id')
            ->where('status', 'active')
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->calculateCurrentMonthCost() > 0) {
                $this->createTradePayableRecord($subscription, $subscription->calculateCurrentMonthCost());
            }
        }
    }
}
