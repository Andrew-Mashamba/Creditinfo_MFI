<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionService;
use App\Models\Subscription;

class ManageSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:manage 
                            {action : The action to perform (pause|resume|cancel|restart|list)}
                            {service_code? : The service code to manage}
                            {--all : Apply action to all applicable subscriptions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage subscription lifecycle (pause, resume, cancel, restart)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $serviceCode = $this->argument('service_code');
        $all = $this->option('all');
        
        $subscriptionService = new SubscriptionService();
        
        switch ($action) {
            case 'list':
                $this->listSubscriptions();
                break;
                
            case 'pause':
                $this->pauseSubscriptions($subscriptionService, $serviceCode, $all);
                break;
                
            case 'resume':
                $this->resumeSubscriptions($subscriptionService, $serviceCode, $all);
                break;
                
            case 'cancel':
                $this->cancelSubscriptions($subscriptionService, $serviceCode, $all);
                break;
                
            case 'restart':
                $this->restartSubscriptions($subscriptionService, $serviceCode, $all);
                break;
                
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Available actions: list, pause, resume, cancel, restart');
                return 1;
        }
        
        return 0;
    }
    
    /**
     * List all subscriptions
     */
    private function listSubscriptions()
    {
        $subscriptions = Subscription::with(['createdBy', 'updatedBy'])->get();
        
        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions found.');
            return;
        }
        
        $this->info("Found {$subscriptions->count()} subscriptions:");
        
        $headers = ['ID', 'Service', 'Code', 'Type', 'Status', 'Price', 'Next Billing'];
        $rows = [];
        
        foreach ($subscriptions as $subscription) {
            $rows[] = [
                $subscription->id,
                $subscription->service_name,
                $subscription->service_code,
                $subscription->subscription_type,
                $subscription->status,
                'TSH ' . number_format($subscription->calculateCurrentMonthCost()),
                $subscription->next_billing_date ? $subscription->next_billing_date->format('Y-m-d') : 'N/A'
            ];
        }
        
        $this->table($headers, $rows);
    }
    
    /**
     * Pause subscriptions
     */
    private function pauseSubscriptions(SubscriptionService $service, $serviceCode, $all)
    {
        if ($all) {
            $subscriptions = Subscription::optional()->active()->get();
            $this->info("Pausing all optional active subscriptions...");
        } elseif ($serviceCode) {
            $subscriptions = Subscription::where('service_code', $serviceCode)->get();
            $this->info("Pausing subscription: {$serviceCode}");
        } else {
            $this->error('Please specify a service code or use --all option');
            return;
        }
        
        $paused = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->canBePaused()) {
                if ($service->pauseSubscription($subscription->id)) {
                    $paused++;
                    $this->line("✅ Paused: {$subscription->service_name}");
                } else {
                    $this->line("❌ Failed to pause: {$subscription->service_name}");
                }
            } else {
                $this->line("⚠️  Cannot pause: {$subscription->service_name} (not pausable)");
            }
        }
        
        $this->info("Paused {$paused} subscriptions.");
    }
    
    /**
     * Resume subscriptions
     */
    private function resumeSubscriptions(SubscriptionService $service, $serviceCode, $all)
    {
        if ($all) {
            $subscriptions = Subscription::optional()->paused()->get();
            $this->info("Resuming all optional paused subscriptions...");
        } elseif ($serviceCode) {
            $subscriptions = Subscription::where('service_code', $serviceCode)->get();
            $this->info("Resuming subscription: {$serviceCode}");
        } else {
            $this->error('Please specify a service code or use --all option');
            return;
        }
        
        $resumed = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->canBeResumed()) {
                if ($service->resumeSubscription($subscription->id)) {
                    $resumed++;
                    $this->line("✅ Resumed: {$subscription->service_name}");
                } else {
                    $this->line("❌ Failed to resume: {$subscription->service_name}");
                }
            } else {
                $this->line("⚠️  Cannot resume: {$subscription->service_name} (not resumable)");
            }
        }
        
        $this->info("Resumed {$resumed} subscriptions.");
    }
    
    /**
     * Cancel subscriptions
     */
    private function cancelSubscriptions(SubscriptionService $service, $serviceCode, $all)
    {
        if ($all) {
            $subscriptions = Subscription::optional()->whereIn('status', ['active', 'paused'])->get();
            $this->info("Cancelling all optional active/paused subscriptions...");
        } elseif ($serviceCode) {
            $subscriptions = Subscription::where('service_code', $serviceCode)->get();
            $this->info("Cancelling subscription: {$serviceCode}");
        } else {
            $this->error('Please specify a service code or use --all option');
            return;
        }
        
        if (!$this->confirm('Are you sure you want to cancel these subscriptions?')) {
            $this->info('Cancellation cancelled.');
            return;
        }
        
        $cancelled = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->canBeCancelled()) {
                if ($service->cancelSubscription($subscription->id)) {
                    $cancelled++;
                    $this->line("✅ Cancelled: {$subscription->service_name}");
                } else {
                    $this->line("❌ Failed to cancel: {$subscription->service_name}");
                }
            } else {
                $this->line("⚠️  Cannot cancel: {$subscription->service_name} (not cancellable)");
            }
        }
        
        $this->info("Cancelled {$cancelled} subscriptions.");
    }
    
    /**
     * Restart subscriptions
     */
    private function restartSubscriptions(SubscriptionService $service, $serviceCode, $all)
    {
        if ($all) {
            $subscriptions = Subscription::optional()->cancelled()->get();
            $this->info("Restarting all optional cancelled subscriptions...");
        } elseif ($serviceCode) {
            $subscriptions = Subscription::where('service_code', $serviceCode)->get();
            $this->info("Restarting subscription: {$serviceCode}");
        } else {
            $this->error('Please specify a service code or use --all option');
            return;
        }
        
        $restarted = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->isCancelled()) {
                if ($service->restartSubscription($subscription->id)) {
                    $restarted++;
                    $this->line("✅ Restarted: {$subscription->service_name}");
                } else {
                    $this->line("❌ Failed to restart: {$subscription->service_name}");
                }
            } else {
                $this->line("⚠️  Cannot restart: {$subscription->service_name} (not cancelled)");
            }
        }
        
        $this->info("Restarted {$restarted} subscriptions.");
    }
}
