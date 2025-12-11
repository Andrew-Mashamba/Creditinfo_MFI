<?php

namespace App\Http\Livewire\Subscriptions;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\Livewire\WithModulePermissions;
use App\Services\SmsUsageService;
use App\Services\EmailUsageService;
use App\Services\PaymentLinksUsageService;
use App\Services\ControlNumbersUsageService;
use App\Services\MobileAppUsageService;
use App\Services\AiUsageService;
use App\Services\SubscriptionService;
use App\Models\Subscription;

class Subscriptions extends Component
{
    use WithModulePermissions;
    public $activeTab = 'overview';
    public $loading = false;
    public $services = [];
    public $currentPlan = [];
    public $showUpgradeModal = false;
    public $selectedService = null;
    public $totalMonthlyBill = 0;
    public $usageStats = [];
    protected $smsUsageService;
    protected $emailUsageService;
    protected $paymentLinksUsageService;
    protected $controlNumbersUsageService;
    protected $mobileAppUsageService;
    protected $aiUsageService;
    protected $subscriptionService;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->smsUsageService = new SmsUsageService();
        $this->emailUsageService = new EmailUsageService();
        $this->paymentLinksUsageService = new PaymentLinksUsageService();
        $this->controlNumbersUsageService = new ControlNumbersUsageService();
        $this->mobileAppUsageService = new MobileAppUsageService();
        $this->aiUsageService = new AiUsageService();
        $this->subscriptionService = new SubscriptionService();
        
        // Initialize system subscriptions if they don't exist
        $this->subscriptionService->initializeSystemSubscriptions();
        
        $this->loadServices();
        $this->calculateTotalBill();
        $this->loadUsageStats();
    }

    public function loadServices()
    {
        // Use the new subscription service to get all services
        $this->services = $this->subscriptionService->getAllSubscriptions();
    }


    public function loadUsageStats()
    {
        // Get real usage data for today
        $todaySmsUsage = $this->smsUsageService->getTodaySmsUsage();
        $todayEmailUsage = $this->emailUsageService->getTodayEmailUsage();
        $todayPaymentLinksUsage = $this->paymentLinksUsageService->getTodayPaymentLinksUsage();
        $todayControlNumbersUsage = $this->controlNumbersUsageService->getTodayControlNumbersUsage();
        $todayMobileAppUsage = $this->mobileAppUsageService->getTodayMobileAppUsage();
        $todayAiUsage = $this->aiUsageService->getTodayAiUsage();
        
        $this->usageStats = [
            'sms_sent_today' => $todaySmsUsage['delivered'],
            'sms_total_today' => $todaySmsUsage['total'],
            'sms_failed_today' => $todaySmsUsage['failed'],
            'sms_success_rate' => $todaySmsUsage['success_rate'],
            'emails_sent_today' => $todayEmailUsage['delivered'],
            'emails_total_today' => $todayEmailUsage['total'],
            'emails_failed_today' => $todayEmailUsage['failed'],
            'emails_success_rate' => $todayEmailUsage['success_rate'],
            'payment_links_today' => $todayPaymentLinksUsage['total'],
            'payment_links_used_today' => $todayPaymentLinksUsage['used'],
            'payment_links_conversion_rate' => $todayPaymentLinksUsage['conversion_rate'],
            'control_numbers_today' => $todayControlNumbersUsage['total'],
            'control_numbers_paid_today' => $todayControlNumbersUsage['paid'],
            'control_numbers_payment_rate' => $todayControlNumbersUsage['payment_rate'],
            'app_logins_today' => $todayMobileAppUsage['total_logins'],
            'app_unique_users_today' => $todayMobileAppUsage['unique_users'],
            'app_success_rate' => $todayMobileAppUsage['success_rate'],
            'ai_queries_today' => $todayAiUsage['total_queries'],
            'ai_sessions_today' => $todayAiUsage['unique_sessions'],
            'ai_users_today' => $todayAiUsage['unique_users'],
            'crb_checks_today' => 0 // TODO: Implement CRB usage service
        ];
    }

    public function calculateTotalBill()
    {
        $this->totalMonthlyBill = collect($this->services)
            ->where('status', 'active')
            ->sum('price');
    }

    public function toggleService($serviceId)
    {
        // Check permission to manage subscriptions
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to manage service subscriptions');
            return;
        }
        
        $service = collect($this->services)->firstWhere('id', $serviceId);
        
        if ($service && $service['type'] === 'optional') {
            $this->selectedService = $service;
            $this->showUpgradeModal = true;
        }
    }

    /**
     * Pause a subscription
     */
    public function pauseSubscription($subscriptionId)
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to manage subscriptions');
            return;
        }

        $result = $this->subscriptionService->pauseSubscription($subscriptionId);
        
        if ($result) {
            $this->loadServices();
            $this->calculateTotalBill();
            session()->flash('message', 'Subscription paused successfully!');
        } else {
            session()->flash('error', 'Failed to pause subscription. It may not be pausable.');
        }
    }

    /**
     * Resume a subscription
     */
    public function resumeSubscription($subscriptionId)
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to manage subscriptions');
            return;
        }

        $result = $this->subscriptionService->resumeSubscription($subscriptionId);
        
        if ($result) {
            $this->loadServices();
            $this->calculateTotalBill();
            session()->flash('message', 'Subscription resumed successfully!');
        } else {
            session()->flash('error', 'Failed to resume subscription. It may not be resumable.');
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to manage subscriptions');
            return;
        }

        $result = $this->subscriptionService->cancelSubscription($subscriptionId);
        
        if ($result) {
            $this->loadServices();
            $this->calculateTotalBill();
            session()->flash('message', 'Subscription cancelled successfully!');
        } else {
            session()->flash('error', 'Failed to cancel subscription. It may not be cancellable.');
        }
    }

    /**
     * Restart a subscription
     */
    public function restartSubscription($subscriptionId)
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to manage subscriptions');
            return;
        }

        $result = $this->subscriptionService->restartSubscription($subscriptionId);
        
        if ($result) {
            $this->loadServices();
            $this->calculateTotalBill();
            session()->flash('message', 'Subscription restarted successfully!');
        } else {
            session()->flash('error', 'Failed to restart subscription. It may not be restartable.');
        }
    }

    /**
     * Process due subscriptions for billing
     */
    public function processDueSubscriptions()
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to process subscriptions');
            return;
        }

        $result = $this->subscriptionService->processDueSubscriptions();
        
        if ($result['total_processed'] > 0) {
            $this->loadServices();
            $this->calculateTotalBill();
            session()->flash('message', "Processed {$result['total_processed']} subscriptions for billing.");
        } else {
            session()->flash('message', 'No subscriptions were due for billing.');
        }

        if ($result['total_errors'] > 0) {
            session()->flash('error', "Failed to process {$result['total_errors']} subscriptions. Check logs for details.");
        }
    }

    public function confirmServiceToggle()
    {
        // Check permission to manage subscriptions
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to manage service subscriptions');
            return;
        }
        
        if ($this->selectedService) {
            // Update service status
            $this->services = collect($this->services)->map(function ($service) {
                if ($service['id'] === $this->selectedService['id']) {
                    $service['status'] = $service['status'] === 'active' ? 'inactive' : 'active';
                }
                return $service;
            })->toArray();
            
            $this->calculateTotalBill();
            $this->showUpgradeModal = false;
            $this->selectedService = null;
            
            session()->flash('message', 'Service updated successfully!');
        }
    }

    public function cancelServiceToggle()
    {
        $this->showUpgradeModal = false;
        $this->selectedService = null;
    }


    public function setActiveTab($tab)
    {
        // Check permissions based on the tab being accessed
        $requiredPermission = $this->getRequiredPermissionForTab($tab);
        $permissionKey = 'can' . ucfirst($requiredPermission);
        
        if (!($this->permissions[$permissionKey] ?? false)) {
            session()->flash('error', 'You do not have permission to access this subscriptions section');
            return;
        }
        
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.subscriptions.subscriptions', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }

    /**
     * Get the required permission for a specific subscriptions tab
     */
    private function getRequiredPermissionForTab($tab)
    {
        $tabPermissionMap = [
            'overview' => 'view',    // Services Overview
            'usage' => 'view',       // Usage Statistics
        ];
        
        return $tabPermissionMap[$tab] ?? 'view';
    }

    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'subscriptions';
    }
}
