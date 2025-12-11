<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionService;
use App\Models\Subscription;

class InitializeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:initialize 
                            {--force : Force re-initialization even if subscriptions exist}
                            {--seed : Seed with sample data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize system subscriptions and create default service subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $seed = $this->option('seed');
        
        $this->info('Initializing subscription system...');
        
        // Check if subscriptions already exist
        $existingCount = Subscription::count();
        if ($existingCount > 0 && !$force) {
            $this->warn("Found {$existingCount} existing subscriptions. Use --force to re-initialize.");
            return;
        }
        
        if ($force && $existingCount > 0) {
            $this->warn("Force mode: Will re-initialize existing subscriptions.");
        }
        
        $subscriptionService = new SubscriptionService();
        
        // Initialize system subscriptions
        $this->info('Creating system subscriptions...');
        $subscriptionService->initializeSystemSubscriptions();
        
        // Get created subscriptions
        $subscriptions = Subscription::systemServices()->get();
        $this->info("✅ Created {$subscriptions->count()} system subscriptions:");
        
        foreach ($subscriptions as $subscription) {
            $this->line("  - {$subscription->service_name} ({$subscription->service_code}) - {$subscription->status}");
        }
        
        // Sync with trade payables if needed
        $this->info('Syncing with trade payables...');
        $subscriptionService->syncWithTradePayables();
        
        if ($seed) {
            $this->info('Seeding with sample data...');
            $this->seedSampleData();
        }
        
        $this->info('✅ Subscription system initialized successfully!');
        
        // Show summary
        $stats = $subscriptionService->getSubscriptionStats();
        $this->info("\n📊 Subscription Statistics:");
        $this->line("  Total Subscriptions: {$stats['total_subscriptions']}");
        $this->line("  Active: {$stats['active_subscriptions']}");
        $this->line("  Paused: {$stats['paused_subscriptions']}");
        $this->line("  Cancelled: {$stats['cancelled_subscriptions']}");
        $this->line("  Mandatory: {$stats['mandatory_subscriptions']}");
        $this->line("  Optional: {$stats['optional_subscriptions']}");
        $this->line("  Monthly Revenue: TSH " . number_format($stats['monthly_recurring_revenue']));
    }
    
    /**
     * Seed sample data for testing
     */
    private function seedSampleData()
    {
        // Create some sample user-managed subscriptions
        $sampleSubscriptions = [
            [
                'service_name' => 'Premium Analytics',
                'service_code' => 'ANALYTICS',
                'description' => 'Advanced analytics and reporting features',
                'service_type' => 'general',
                'subscription_type' => 'optional',
                'pricing_model' => 'fixed',
                'base_price' => 50000,
                'cost_per_unit' => 0,
                'billing_frequency' => 'monthly',
                'start_date' => now(),
                'next_billing_date' => now()->addMonth(),
                'status' => 'active',
                'features' => ['Advanced reports', 'Custom dashboards', 'Data export'],
                'is_system_service' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'service_name' => 'API Access',
                'service_code' => 'API',
                'description' => 'REST API access for third-party integrations',
                'service_type' => 'general',
                'subscription_type' => 'optional',
                'pricing_model' => 'usage_based',
                'base_price' => 25000,
                'cost_per_unit' => 5,
                'unit_type' => 'api_call',
                'included_units' => 10000,
                'billing_frequency' => 'monthly',
                'start_date' => now(),
                'next_billing_date' => now()->addMonth(),
                'status' => 'active',
                'features' => ['REST API', 'Webhooks', 'Rate limiting'],
                'is_system_service' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        ];
        
        foreach ($sampleSubscriptions as $data) {
            Subscription::create($data);
            $this->line("  ✅ Created: {$data['service_name']}");
        }
    }
}
