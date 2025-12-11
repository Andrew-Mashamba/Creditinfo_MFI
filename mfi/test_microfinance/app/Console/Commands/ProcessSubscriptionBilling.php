<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-billing 
                            {--dry-run : Show what would be processed without actually processing}
                            {--force : Force processing even if not due}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all subscriptions that are due for billing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subscriptionService = new SubscriptionService();
        
        $this->info('Processing subscription billing...');
        
        if ($this->option('dry-run')) {
            $this->info('DRY RUN MODE - No actual processing will occur');
            $this->showDueSubscriptions($subscriptionService);
            return;
        }
        
        $result = $subscriptionService->processDueSubscriptions();
        
        if ($result['total_processed'] > 0) {
            $this->info("✅ Successfully processed {$result['total_processed']} subscriptions:");
            
            foreach ($result['processed'] as $processed) {
                $this->line("  - {$processed['service_name']}: TSH " . number_format($processed['amount']));
            }
        } else {
            $this->info('ℹ️  No subscriptions were due for billing.');
        }
        
        if ($result['total_errors'] > 0) {
            $this->error("❌ Failed to process {$result['total_errors']} subscriptions:");
            
            foreach ($result['errors'] as $error) {
                $this->line("  - {$error['service_name']}: {$error['error']}");
            }
        }
        
        $this->info('Subscription billing processing completed.');
    }
    
    /**
     * Show subscriptions that would be processed in dry-run mode
     */
    private function showDueSubscriptions(SubscriptionService $subscriptionService)
    {
        $subscriptions = \App\Models\Subscription::dueForBilling()->get();
        
        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions are due for billing.');
            return;
        }
        
        $this->info("Found {$subscriptions->count()} subscriptions due for billing:");
        
        $headers = ['Service', 'Type', 'Amount', 'Next Billing Date'];
        $rows = [];
        
        foreach ($subscriptions as $subscription) {
            $rows[] = [
                $subscription->service_name,
                $subscription->subscription_type,
                'TSH ' . number_format($subscription->calculateCurrentMonthCost()),
                $subscription->next_billing_date ? $subscription->next_billing_date->format('Y-m-d') : 'N/A'
            ];
        }
        
        $this->table($headers, $rows);
    }
}
