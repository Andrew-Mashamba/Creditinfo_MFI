<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\VendorModel;
use App\Services\BalanceSheetItemIntegrationService;
use App\Services\AccountCreationService;
use App\Services\TransactionPostingService;
use App\Services\EmailService;
use App\Services\SmsService;
use App\Services\PaymentNotificationService;
use App\Jobs\ProcessTradePayableInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class TradeAndOtherPayables extends Component
{
    use WithPagination, WithFileUploads;

    // View Control
    public $activeTab = 'overview';
    public $showCreateModal = false;
    public $showPaymentModal = false;
    
    // Notification properties
    public $notifications = [];
    public $notificationStats = [];
    public $showApprovalModal = false;
    public $showDetailsModal = false;
    public $showConfirmModal = false;
    public $showSubscriptionModal = false;
    public $editMode = false;
    
    // Confirmation modal properties
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmButtonText = 'Confirm';
    public $confirmAction = '';
    public $confirmActionId = null;
    
    // Subscription Management
    public $subscriptions = [];
    public $institutionServices = [];
    public $totalMonthlySubscriptions = 0;
    public $upcomingRenewals = [];
    public $subscriptionStats = [];
    
    // Search and Filters
    public $search = '';
    public $statusFilter = 'all';
    public $priorityFilter = 'all';
    public $vendorFilter = '';
    public $ageFilter = 'all';
    public $dateFrom;
    public $dateTo;
    
    // Payable Form Data
    public $payableId;
    public $vendor_id;
    public $vendor_name = '';
    public $vendor_email = '';
    public $vendor_phone = '';
    public $vendor_address = '';
    public $vendor_tax_id = '';
    public $vendor_bank_name = '';
    public $vendor_bank_account_number = '';
    public $vendor_bank_branch = '';
    public $vendor_swift_code = '';
    public $invoice_number = '';
    public $invoice_date;
    public $bill_number = '';
    public $purchase_order_number = '';
    public $amount = 0;
    public $vat_amount = 0;
    public $total_amount = 0;
    public $currency = 'TZS';
    public $due_date;
    public $payment_terms = 30;
    public $description = '';
    public $expense_account_id;
    public $payable_account_id;
    public $bank_account_id;
    public $payment_type = 'cash';
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create payable account under
    public $other_account_id; // The other account for double-entry (Expense/Inventory - debit side)
    public $created_payable_account_number; // The created payable account number
    public $notes = '';
    public $priority = 'normal';
    public $approval_status = 'pending';
    
    // Payable Type Management
    public $payable_type = 'once_off'; // once_off, installment, subscription
    
    // Installment fields
    public $installment_count = 2;
    public $installment_frequency = 'monthly'; // weekly, bi_weekly, monthly, quarterly
    public $installments_paid = 0;
    public $installment_amount = 0;
    public $next_installment_date;
    public $last_installment_date;
    public $installmentSchedule = []; // Preview of installment schedule
    
    // Subscription/Recurring fields
    public $is_recurring = false;
    public $recurring_frequency = 'monthly'; // monthly, quarterly, annually
    public $recurring_start_date;
    public $recurring_end_date;
    public $next_billing_date;
    public $service_type = 'general'; // general, sms, email, payment_gateway, mobile_app, etc.
    public $subscription_status = 'active';
    
    // Payment Form Data
    public $payment_payable_id;
    public $selectedBalance = 0; // Track the balance for validation
    public $payment_amount = 0;
    public $payment_date;
    public $payment_method = 'bank_transfer';
    public $payment_reference = '';
    public $payment_account_id;
    public $bank_charges = 0;
    public $early_payment_discount = 0;
    public $withholding_tax = 0;
    public $payment_notes = '';
    public $paymentAccounts = []; // Available payment accounts
    
    // Batch Payment
    public $selectedPayables = [];
    public $batchPaymentMode = false;
    public $batchPaymentDate;
    public $batchPaymentAccount;
    
    // File Uploads
    public $invoice_attachment;
    public $purchase_order_attachment;
    public $payment_voucher;
    public $supporting_documents = [];
    
    // Statistics
    public $totalPayables = 0;
    public $totalOverdue = 0;
    public $totalPaid = 0;
    public $totalApproved = 0;
    public $upcomingPayments = 0;
    public $averagePaymentPeriod = 0;
    public $cashFlowProjection = [];
    public $agingBuckets = [];
    
    // Collections
    public $vendors = [];
    public $payableAccounts = [];
    public $expenseAccounts = [];
    public $bankAccounts = [];
    public $approvers = [];
    
    protected $rules = [
        'vendor_name' => 'required|min:3',
        'bill_number' => 'required',
        'invoice_date' => 'required|date',
        'amount' => 'required|numeric|min:0',
        'due_date' => 'required|date|after_or_equal:invoice_date',
        'currency' => 'nullable|in:TZS,USD,EUR,GBP',
        'payment_terms' => 'required|integer|min:0',
        'vat_amount' => 'nullable|numeric|min:0',
        'invoice_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        // parent_account_number is set automatically from institution settings
        'other_account_id' => 'required|integer',
    ];
    
    protected $listeners = [
        'refreshPayables' => 'loadStatistics',
        'deletePayable' => 'delete',
        'approvePayable' => 'approve',
        'processBatchPayment' => 'processBatch',
        'writeOffPayable' => 'writeOff',
    ];
    
    public function mount()
    {
        $this->initializeData();
        $this->loadStatistics();
        $this->loadSubscriptions();
        $this->loadInstitutionServices();
        $this->loadNotifications();
        $this->ensureSystemPayablesExist(); // Create system payables if they don't exist
        $this->invoice_date = now()->format('Y-m-d');
        $this->payment_date = now()->format('Y-m-d');
        $this->batchPaymentDate = now()->format('Y-m-d');
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        
        // Set default parent account from institution settings
        $institution = DB::table('institutions')->where('id', 1)->first();
        if ($institution && $institution->trade_payables_account) {
            $this->parent_account_number = $institution->trade_payables_account;
        }
        
        // Auto-calculate due date based on default payment terms
        $this->updatedPaymentTerms();
        // Initialize payment accounts as empty array
        $this->paymentAccounts = [];
    }
    
    public function initializeData()
    {
        // Load vendors (create table if doesn't exist)
        if (Schema::hasTable('vendors')) {
            $this->vendors = DB::table('vendors')
                ->where('status', 'ACTIVE')
                ->orderBy('organization_name')
                ->get();
        } else {
            $this->vendors = collect();
        }
        
        // Load payable accounts (Liability accounts)
        // First try to get specific trade payables accounts
        $this->payableAccounts = AccountsModel::where('type', 'liability_accounts')
            ->where(function($query) {
                $query->whereRaw('LOWER(account_name) LIKE ?', ['%payable%'])
                      ->orWhereRaw('LOWER(account_name) LIKE ?', ['%creditor%'])
                      ->orWhereRaw('LOWER(account_name) LIKE ?', ['%trade%']);
            })
            ->where('status', 'ACTIVE')
            ->whereIn('account_level', ['2', '3']) // Get parent level accounts
            ->orderBy('account_name')
            ->get();
            
        // If no accounts found, get all liability parent accounts
        if ($this->payableAccounts->isEmpty()) {
            $this->payableAccounts = AccountsModel::where('type', 'liability_accounts')
                ->where('status', 'ACTIVE')
                ->whereIn('account_level', ['2', '3'])
                ->orderBy('account_name')
                ->get();
        }
        
        // Load expense accounts (level 3 or 4 only for detailed accounts)
        $this->expenseAccounts = AccountsModel::where('type', 'expense_accounts')
            ->whereIn('account_level', ['3', '4'])
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        // Load bank accounts for payments
        // Check for bank accounts in the accounts table with specific account types
        $this->bankAccounts = AccountsModel::where(function($query) {
                $query->where('account_name', 'LIKE', '%BANK%')
                      ->orWhere('account_name', 'LIKE', '%CASH%')
                      ->orWhere('major_category_code', '1000'); // Asset accounts that could be bank/cash
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
            
        // Load approvers
        $this->approvers = DB::table('users')
            ->select('users.id', 'users.name')
            ->distinct()
            ->get();
    }
    
    public function loadStatistics()
    {
        // Check if payables table exists
        if (!Schema::hasTable('trade_payables')) {
            $this->totalPayables = 0;
            $this->totalOverdue = 0;
            $this->totalPaid = 0;
            $this->totalApproved = 0;
            $this->upcomingPayments = 0;
            $this->averagePaymentPeriod = 0;
            $this->cashFlowProjection = [];
            return;
        }
        
        $query = DB::table('trade_payables');
        
        // Apply date filters
        if ($this->dateFrom) {
            $query->where('bill_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('bill_date', '<=', $this->dateTo);
        }
        
        // Calculate totals
        $this->totalPayables = $query->sum('amount') ?? 0;
        
        $this->totalOverdue = DB::table('trade_payables')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->sum('balance') ?? 0;
        
        $this->totalPaid = DB::table('trade_payables')
            ->where('status', 'paid')
            ->sum('paid_amount') ?? 0;
        
        $this->totalApproved = DB::table('trade_payables')
            ->where('status', '!=', 'paid')
            ->sum('balance') ?? 0;  // No approval_status column in trade_payables
        
        // Calculate upcoming payments (next 7 days)
        $this->upcomingPayments = DB::table('trade_payables')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->where('status', '!=', 'paid')
            ->sum('balance') ?? 0;
        
        // Calculate average payment period (PostgreSQL syntax)
        $avgDays = DB::table('trade_payables')
            ->where('status', 'paid')
            ->whereNotNull('updated_at')
            ->selectRaw("AVG(DATE_PART('day', updated_at - bill_date)) as avg_days")
            ->value('avg_days');
        
        $this->averagePaymentPeriod = round($avgDays ?? 0);
        
        // Calculate aging buckets
        $this->calculateAgingBuckets();
        
        // Calculate cash flow projection
        $this->calculateCashFlowProjection();
    }
    
    public function calculateAgingBuckets()
    {
        $today = Carbon::now();
        
        $this->agingBuckets = [
            'current' => [
                'label' => 'Current',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '30_days' => [
                'label' => '1-30 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '60_days' => [
                'label' => '31-60 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '90_days' => [
                'label' => '61-90 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '120_days' => [
                'label' => '91-120 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            'over_120' => [
                'label' => 'Over 120 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ]
        ];
        
        if (!Schema::hasTable('trade_payables')) {
            return;
        }
        
        $payables = DB::table('trade_payables')
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->get();
        
        $totalOutstanding = 0;
        
        foreach ($payables as $payable) {
            $daysOverdue = $today->diffInDays(Carbon::parse($payable->due_date), false);
            $amount = $payable->balance;
            $totalOutstanding += $amount;
            
            if ($daysOverdue >= 0) {
                $this->agingBuckets['current']['amount'] += $amount;
                $this->agingBuckets['current']['count']++;
            } elseif ($daysOverdue >= -30) {
                $this->agingBuckets['30_days']['amount'] += $amount;
                $this->agingBuckets['30_days']['count']++;
            } elseif ($daysOverdue >= -60) {
                $this->agingBuckets['60_days']['amount'] += $amount;
                $this->agingBuckets['60_days']['count']++;
            } elseif ($daysOverdue >= -90) {
                $this->agingBuckets['90_days']['amount'] += $amount;
                $this->agingBuckets['90_days']['count']++;
            } elseif ($daysOverdue >= -120) {
                $this->agingBuckets['120_days']['amount'] += $amount;
                $this->agingBuckets['120_days']['count']++;
            } else {
                $this->agingBuckets['over_120']['amount'] += $amount;
                $this->agingBuckets['over_120']['count']++;
            }
        }
        
        // Calculate percentages
        if ($totalOutstanding > 0) {
            foreach ($this->agingBuckets as $key => &$bucket) {
                $bucket['percentage'] = round(($bucket['amount'] / $totalOutstanding) * 100, 2);
            }
        }
    }
    
    /**
     * Ensure system payables exist for all mandatory institution services
     */
    public function ensureSystemPayablesExist()
    {
        if (!Schema::hasTable('trade_payables')) {
            return;
        }
        
        // Get all mandatory services
        $mandatoryServices = collect($this->institutionServices)->where('type', 'mandatory');
        
        foreach ($mandatoryServices as $service) {
            // Check if system payable already exists
            $existingPayable = DB::table('trade_payables')
                ->where('is_system', true)
                ->where('system_code', $service['code'])
                ->first();
            
            if (!$existingPayable) {
                // Create system payable
                $this->createSystemPayable($service);
            }
        }
        
        // Also create system payables for active optional services
        $activeOptionalServices = collect($this->institutionServices)
            ->where('type', 'optional')
            ->where('status', 'active');
        
        foreach ($activeOptionalServices as $service) {
            $existingPayable = DB::table('trade_payables')
                ->where('is_system', true)
                ->where('system_code', $service['code'])
                ->first();
            
            if (!$existingPayable) {
                $this->createSystemPayable($service);
            }
        }
    }
    
    /**
     * Create a system payable from service definition
     */
    private function createSystemPayable($service)
    {
        Log::info('Creating system payable', [
            'service_code' => $service['code'],
            'service_name' => $service['name'],
            'vendor' => $service['vendor']
        ]);
        
        $billNumber = 'SYS-' . $service['code'] . '-' . date('Ym');
        
        $data = [
            'vendor_id' => null,
            'vendor_name' => $service['vendor'],
            'vendor_email' => null,
            'vendor_phone' => null,
            'bill_number' => $billNumber,
            'bill_date' => now()->startOfMonth(),
            'due_date' => now()->endOfMonth(),
            'amount' => $service['price'],
            'paid_amount' => 0,
            'balance' => $service['price'],
            'payment_terms' => 30,
            'description' => $service['description'],
            'status' => 'pending',
            'is_recurring' => true,
            'recurring_frequency' => $service['billing_cycle'] ?? 'monthly',
            'recurring_start_date' => now()->startOfMonth(),
            'next_billing_date' => now()->addMonth()->startOfMonth(),
            'service_type' => strtolower($service['code']),
            'subscription_status' => $service['status'],
            'is_system' => true,
            'is_enabled' => $service['status'] === 'active',
            'system_code' => $service['code'],
            'priority' => $service['type'] === 'mandatory' ? 'high' : 'normal',
            'created_by' => 1, // System user
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('trade_payables')->insert($data);
        
        Log::info('System payable created', [
            'bill_number' => $billNumber,
            'service_code' => $service['code'],
            'amount' => $service['price']
        ]);
    }
    
    /**
     * Toggle system payable enabled/disabled status
     */
    public function toggleSystemPayable($payableId)
    {
        $payable = DB::table('trade_payables')->find($payableId);
        
        if (!$payable || !$payable->is_system) {
            session()->flash('error', 'Invalid system payable');
            return;
        }
        
        // Check if it's a mandatory service
        $service = collect($this->institutionServices)->firstWhere('code', $payable->system_code);
        if ($service && $service['type'] === 'mandatory') {
            session()->flash('error', 'Mandatory services cannot be disabled');
            return;
        }
        
        $newStatus = !$payable->is_enabled;
        
        DB::table('trade_payables')
            ->where('id', $payableId)
            ->update([
                'is_enabled' => $newStatus,
                'subscription_status' => $newStatus ? 'active' : 'paused',
                'updated_at' => now(),
                'updated_by' => auth()->id()
            ]);
        
        $this->loadStatistics();
        $this->loadSubscriptions();
        
        $message = $newStatus ? 'Service enabled' : 'Service disabled';
        session()->flash('success', $message);
        
        Log::info('System payable toggled', [
            'payable_id' => $payableId,
            'system_code' => $payable->system_code,
            'new_status' => $newStatus ? 'enabled' : 'disabled'
        ]);
    }
    
    /**
     * Load institution subscription services
     */
    public function loadInstitutionServices()
    {
        $this->institutionServices = [
            [
                'id' => 1,
                'name' => 'SMS Service',
                'code' => 'SMS',
                'description' => 'Send SMS notifications to members for transactions, alerts, and reminders',
                'type' => 'mandatory',
                'status' => 'active',
                'price' => 50000,
                'billing_cycle' => 'monthly',
                'vendor' => 'Vodacom Business',
                'features' => ['Transaction alerts', 'Payment reminders', 'Marketing messages', 'OTP verification'],
                'usage' => ['sent' => 12500, 'limit' => 20000, 'percentage' => 62.5]
            ],
            [
                'id' => 2,
                'name' => 'Email Service',
                'code' => 'EMAIL',
                'description' => 'Professional email communications for statements, notifications, and marketing',
                'type' => 'mandatory',
                'status' => 'active',
                'price' => 30000,
                'billing_cycle' => 'monthly',
                'vendor' => 'SendGrid',
                'features' => ['Transaction emails', 'Monthly statements', 'Marketing campaigns', 'Email templates'],
                'usage' => ['sent' => 8500, 'limit' => 50000, 'percentage' => 17]
            ],
            [
                'id' => 3,
                'name' => 'Control Number Payment',
                'code' => 'CTRL',
                'description' => 'Generate control numbers for member payments through banks and mobile networks',
                'type' => 'mandatory',
                'status' => 'active',
                'price' => 100000,
                'billing_cycle' => 'monthly',
                'vendor' => 'NMB Bank',
                'features' => ['Automated control numbers', 'Multi-bank integration', 'Real-time reconciliation', 'Payment tracking'],
                'usage' => ['generated' => 450, 'limit' => 1000, 'percentage' => 45]
            ],
            [
                'id' => 4,
                'name' => 'Pay by Link',
                'code' => 'PAYLINK',
                'description' => 'Send payment links to members for easy online payments',
                'type' => 'mandatory',
                'status' => 'active',
                'price' => 75000,
                'billing_cycle' => 'monthly',
                'vendor' => 'Flutterwave',
                'features' => ['Secure payment links', 'Multiple payment methods', 'Automatic receipts', 'Link expiration control'],
                'usage' => ['created' => 320, 'limit' => 500, 'percentage' => 64]
            ],
            [
                'id' => 5,
                'name' => 'Mobile Application',
                'code' => 'MOBILE',
                'description' => 'Branded mobile app for Android and iOS for member self-service',
                'type' => 'optional',
                'status' => 'active',
                'price' => 200000,
                'billing_cycle' => 'monthly',
                'vendor' => 'AppTech Solutions',
                'features' => ['Account access', 'Loan applications', 'Transfer funds', 'Push notifications', 'Biometric login'],
                'usage' => ['downloads' => 850, 'active_users' => 620]
            ],
            [
                'id' => 6,
                'name' => 'Members Portal',
                'code' => 'PORTAL',
                'description' => 'Web-based self-service portal for members to access accounts online',
                'type' => 'optional',
                'status' => 'inactive',
                'price' => 150000,
                'billing_cycle' => 'monthly',
                'vendor' => 'WebDev Pro',
                'features' => ['Online account access', 'Loan applications', 'Document downloads', 'Support tickets'],
                'usage' => null
            ],
            [
                'id' => 7,
                'name' => 'Zona AI Assistant',
                'code' => 'AI',
                'description' => 'AI-powered chatbot for 24/7 customer support and assistance',
                'type' => 'optional',
                'status' => 'inactive',
                'price' => 250000,
                'billing_cycle' => 'monthly',
                'vendor' => 'Anthropic',
                'features' => ['24/7 availability', 'Multi-language support', 'Loan eligibility check', 'FAQ responses', 'Escalation to human agents'],
                'usage' => null
            ],
            [
                'id' => 8,
                'name' => 'CRB Integration',
                'code' => 'CRB',
                'description' => 'Credit Reference Bureau integration for credit scoring and reporting',
                'type' => 'optional',
                'status' => 'inactive',
                'price' => 300000,
                'billing_cycle' => 'monthly',
                'vendor' => 'CreditInfo Tanzania',
                'features' => ['Credit score checking', 'Automated reporting', 'Member credit history', 'Risk assessment'],
                'usage' => null
            ]
        ];
        
        // Calculate total monthly subscriptions
        $this->totalMonthlySubscriptions = collect($this->institutionServices)
            ->where('status', 'active')
            ->sum('price');
    }

    /**
     * Load recurring vendor bills/subscriptions
     */
    public function loadSubscriptions()
    {
        if (!Schema::hasTable('trade_payables')) {
            $this->subscriptions = [];
            return;
        }
        
        // Load recurring payables
        $this->subscriptions = DB::table('trade_payables')
            ->where('is_recurring', true)
            ->orderBy('next_billing_date', 'asc')
            ->get()
            ->map(function ($subscription) {
                // Calculate next billing date if not set
                if (!$subscription->next_billing_date && $subscription->due_date) {
                    $lastDate = Carbon::parse($subscription->due_date);
                    $frequency = $subscription->recurring_frequency ?? 'monthly';
                    
                    switch ($frequency) {
                        case 'monthly':
                            $subscription->next_billing_date = $lastDate->addMonth();
                            break;
                        case 'quarterly':
                            $subscription->next_billing_date = $lastDate->addMonths(3);
                            break;
                        case 'annually':
                            $subscription->next_billing_date = $lastDate->addYear();
                            break;
                        default:
                            $subscription->next_billing_date = $lastDate->addMonth();
                    }
                }
                
                return $subscription;
            });
        
        // Get upcoming renewals (next 30 days)
        $this->upcomingRenewals = $this->subscriptions
            ->filter(function ($subscription) {
                $nextDate = Carbon::parse($subscription->next_billing_date ?? now());
                return $nextDate->between(now(), now()->addDays(30));
            });
        
        // Calculate subscription statistics
        $this->calculateSubscriptionStats();
    }

    /**
     * Calculate subscription statistics
     */
    public function calculateSubscriptionStats()
    {
        $this->subscriptionStats = [
            'total_subscriptions' => $this->subscriptions->count(),
            'active_subscriptions' => $this->subscriptions->where('subscription_status', 'active')->count(),
            'monthly_recurring' => $this->subscriptions->where('recurring_frequency', 'monthly')->sum('amount'),
            'quarterly_recurring' => $this->subscriptions->where('recurring_frequency', 'quarterly')->sum('amount'),
            'annual_recurring' => $this->subscriptions->where('recurring_frequency', 'annually')->sum('amount'),
            'upcoming_renewals' => $this->upcomingRenewals->count(),
            'upcoming_amount' => $this->upcomingRenewals->sum('amount'),
        ];
    }

    /**
     * Create subscription from institution service
     */
    public function createSubscriptionFromService($serviceId)
    {
        $service = collect($this->institutionServices)->firstWhere('id', $serviceId);
        
        if (!$service) {
            session()->flash('error', 'Service not found');
            return;
        }
        
        // Pre-fill form with service details
        $this->reset(['payableId', 'editMode']);
        $this->vendor_name = $service['vendor'];
        $this->description = $service['description'];
        $this->amount = $service['price'];
        $this->is_recurring = true;
        $this->recurring_frequency = $service['billing_cycle'] ?? 'monthly';
        $this->service_type = strtolower($service['code']);
        $this->subscription_status = $service['status'];
        $this->recurring_start_date = now()->format('Y-m-d');
        $this->payment_terms = 30;
        $this->invoice_date = now()->format('Y-m-d');
        $this->updatedPaymentTerms();
        $this->generateBillNumber();
        
        // Set priority based on service type
        $this->priority = $service['type'] === 'mandatory' ? 'high' : 'normal';
        
        $this->showCreateModal = true;
    }

    /**
     * Process recurring bills (to be called by scheduler/cron)
     */
    public function processRecurringBills()
    {
        Log::info('Processing recurring bills', [
            'timestamp' => now(),
            'user_id' => auth()->id()
        ]);
        
        $dueSubscriptions = $this->subscriptions
            ->filter(function ($subscription) {
                $nextDate = Carbon::parse($subscription->next_billing_date ?? now());
                return $nextDate->isToday() || $nextDate->isPast();
            });
        
        foreach ($dueSubscriptions as $subscription) {
            try {
                // Create new bill from subscription
                $this->createBillFromSubscription($subscription);
                
                // Update next billing date
                $this->updateNextBillingDate($subscription->id);
                
                Log::info('Recurring bill created', [
                    'subscription_id' => $subscription->id,
                    'vendor' => $subscription->vendor_name,
                    'amount' => $subscription->amount
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create recurring bill', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->loadSubscriptions();
        $this->loadStatistics();
    }

    /**
     * Create bill from subscription
     */
    private function createBillFromSubscription($subscription)
    {
        $data = [
            'vendor_id' => $subscription->vendor_id,
            'vendor_name' => $subscription->vendor_name,
            'vendor_email' => $subscription->vendor_email ?? null,
            'vendor_phone' => $subscription->vendor_phone ?? null,
            'vendor_bank_name' => $subscription->vendor_bank_name ?? null,
            'vendor_bank_account_number' => $subscription->vendor_bank_account_number ?? null,
            'vendor_bank_branch' => $subscription->vendor_bank_branch ?? null,
            'vendor_swift_code' => $subscription->vendor_swift_code ?? null,
            'bill_number' => $this->generateNewBillNumber(),
            'purchase_order_number' => $subscription->purchase_order_number ?? null,
            'bill_date' => now(),
            'due_date' => now()->addDays($subscription->payment_terms ?? 30),
            'amount' => $subscription->amount,
            'paid_amount' => 0,
            'balance' => $subscription->amount,
            'payment_terms' => $subscription->payment_terms ?? 30,
            'description' => $subscription->description . ' - Recurring billing for ' . now()->format('F Y'),
            'status' => 'pending',
            'is_recurring' => false, // The new bill itself is not recurring
            'parent_subscription_id' => $subscription->id, // Reference to parent subscription
            'created_by' => auth()->id() ?? 1,
            'updated_by' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        DB::table('trade_payables')->insert($data);
    }

    /**
     * Update next billing date for subscription
     */
    private function updateNextBillingDate($subscriptionId)
    {
        $subscription = DB::table('trade_payables')->find($subscriptionId);
        
        if ($subscription) {
            $currentDate = Carbon::parse($subscription->next_billing_date ?? now());
            $frequency = $subscription->recurring_frequency ?? 'monthly';
            
            switch ($frequency) {
                case 'monthly':
                    $nextDate = $currentDate->addMonth();
                    break;
                case 'quarterly':
                    $nextDate = $currentDate->addMonths(3);
                    break;
                case 'annually':
                    $nextDate = $currentDate->addYear();
                    break;
                default:
                    $nextDate = $currentDate->addMonth();
            }
            
            DB::table('trade_payables')
                ->where('id', $subscriptionId)
                ->update([
                    'next_billing_date' => $nextDate,
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Generate new bill number
     */
    private function generateNewBillNumber()
    {
        $prefix = 'BILL';
        $year = date('Y');
        $month = date('m');
        
        if (Schema::hasTable('trade_payables')) {
            $lastBill = DB::table('trade_payables')
                ->where('bill_number', 'like', "$prefix-$year$month-%")
                ->orderBy('bill_number', 'desc')
                ->first();
            
            if ($lastBill) {
                $lastNumber = intval(substr($lastBill->bill_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
        } else {
            $newNumber = '0001';
        }
        
        return "$prefix-$year$month-$newNumber";
    }
    
    public function calculateCashFlowProjection()
    {
        $this->cashFlowProjection = [];
        
        if (!Schema::hasTable('trade_payables')) {
            return;
        }
        
        for ($i = 0; $i < 6; $i++) {
            $startDate = now()->addMonths($i)->startOfMonth();
            $endDate = now()->addMonths($i)->endOfMonth();
            
            $amount = DB::table('trade_payables')
                ->whereBetween('due_date', [$startDate, $endDate])
                ->where('status', '!=', 'paid')
                ->sum('balance') ?? 0;
            
            $this->cashFlowProjection[] = [
                'month' => $startDate->format('M Y'),
                'amount' => $amount,
            ];
        }
    }
    
    public function updatedAmount()
    {
        $this->calculateTotal();
    }
    
    public function updatedVatAmount()
    {
        $this->calculateTotal();
    }
    
    public function calculateTotal()
    {
        $this->total_amount = (float)($this->amount ?? 0) + (float)($this->vat_amount ?? 0);
    }
    
    public function updatedPaymentTerms()
    {
        if ($this->invoice_date && $this->payment_terms) {
            $this->due_date = Carbon::parse($this->invoice_date)
                ->addDays($this->payment_terms)
                ->format('Y-m-d');
        }
    }
    
    public function updatedInvoiceDate()
    {
        $this->updatedPaymentTerms();
    }
    
    public function updatedVendorId()
    {
        if ($this->vendor_id && Schema::hasTable('vendors')) {
            $vendor = DB::table('vendors')->find($this->vendor_id);
            if ($vendor) {
                $this->vendor_name = $vendor->organization_name;
                $this->vendor_email = $vendor->email ?? '';
                $this->vendor_phone = $vendor->phone ?? '';
                $this->vendor_address = $vendor->address ?? '';
                $this->vendor_tax_id = $vendor->tax_id ?? '';
                $this->payment_terms = $vendor->payment_terms ?? 30;
            }
        }
    }
    
    public function openCreateModal()
    {
        $this->reset(['payableId', 'vendor_id', 'vendor_name', 'vendor_email', 
                     'vendor_phone', 'vendor_address', 'vendor_tax_id',
                     'vendor_bank_name', 'vendor_bank_account_number', 
                     'vendor_bank_branch', 'vendor_swift_code',
                     'invoice_number', 'bill_number', 'purchase_order_number',
                     'amount', 'vat_amount', 'total_amount', 'description', 'notes',
                     'expense_account_id', 'payable_account_id', 'bank_account_id',
                     'parent_account_number', 'other_account_id', 'created_payable_account_number']);
        
        $this->editMode = false;
        $this->invoice_date = now()->format('Y-m-d');
        $this->payment_type = 'cash';
        $this->priority = 'normal';
        $this->approval_status = 'pending';
        $this->payable_type = 'once_off'; // Reset to default payable type
        
        // Set default parent account from institution settings
        $institution = DB::table('institutions')->where('id', 1)->first();
        if ($institution && $institution->trade_payables_account) {
            $this->parent_account_number = $institution->trade_payables_account;
        }
        
        $this->generateBillNumber();
        $this->showCreateModal = true;
    }
    
    public function generateBillNumber()
    {
        $prefix = 'BILL';
        $year = date('Y');
        $month = date('m');
        
        if (Schema::hasTable('trade_payables')) {
            $lastBill = DB::table('trade_payables')
                ->where('bill_number', 'like', "$prefix-$year$month-%")
                ->orderBy('bill_number', 'desc')
                ->first();
            
            if ($lastBill) {
                $lastNumber = intval(substr($lastBill->bill_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
        } else {
            $newNumber = '0001';
        }
        
        $this->bill_number = "$prefix-$year$month-$newNumber";
    }
    
    /**
     * Ensure VAT Receivable account exists and is configured
     */
    private function ensureVatReceivableAccountExists($institution)
    {
        try {
            // Check if VAT Receivable account exists
            $vatReceivableAccount = AccountsModel::where('account_name', 'LIKE', '%VAT RECEIVABLE%')
                ->orWhere('account_name', 'LIKE', '%INPUT VAT%')
                ->first();
            
            if (!$vatReceivableAccount) {
                // Find parent account (Accounts Receivable or Current Assets)
                $parentAccount = AccountsModel::where('type', 'asset_accounts')
                    ->where('account_level', '2')
                    ->where(function($query) {
                        $query->where('account_name', 'LIKE', '%RECEIVABLE%')
                              ->orWhere('account_name', 'LIKE', '%CURRENT ASSET%');
                    })
                    ->first();
                
                if ($parentAccount) {
                    // Generate new account number
                    $lastChild = AccountsModel::where('parent_account_number', $parentAccount->account_number)
                        ->orderBy('account_number', 'desc')
                        ->first();
                    
                    $newAccountNumber = $parentAccount->account_number . '1540'; // Default suffix
                    if ($lastChild) {
                        // Extract last 4 digits and increment
                        $lastDigits = substr($lastChild->account_number, -4);
                        $nextNumber = str_pad((intval($lastDigits) + 10), 4, '0', STR_PAD_LEFT);
                        $newAccountNumber = $parentAccount->account_number . $nextNumber;
                    }
                    
                    // Create VAT Receivable account
                    $vatReceivableAccount = AccountsModel::create([
                        'account_number' => $newAccountNumber,
                        'account_name' => 'VAT RECEIVABLE (INPUT VAT)',
                        'type' => 'asset_accounts',
                        'account_level' => '3',
                        'parent_account_number' => $parentAccount->account_number,
                        'status' => 'ACTIVE',
                        'balance' => 0,
                        'account_use' => 'internal',
                        'major_category_code' => $parentAccount->major_category_code,
                        'category_code' => $parentAccount->category_code,
                        'sub_category_code' => $parentAccount->sub_category_code,
                    ]);
                    
                    Log::info('Created VAT Receivable account', [
                        'account_number' => $newAccountNumber,
                        'parent' => $parentAccount->account_number
                    ]);
                }
            }
            
            // Update institution with VAT receivable account if found or created
            if ($vatReceivableAccount) {
                // Check if column exists first
                if (!Schema::hasColumn('institutions', 'vat_receivable_account')) {
                    Schema::table('institutions', function ($table) {
                        $table->string('vat_receivable_account')->nullable();
                    });
                }
                
                DB::table('institutions')
                    ->where('id', $institution->id)
                    ->update(['vat_receivable_account' => $vatReceivableAccount->account_number]);
                    
                Log::info('Updated institution with VAT receivable account', [
                    'account_number' => $vatReceivableAccount->account_number
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to ensure VAT receivable account exists', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Calculate installment schedule based on parameters
     */
    public function calculateInstallmentSchedule()
    {
        $this->installmentSchedule = [];
        
        if ($this->payable_type !== 'installment' || !$this->installment_count || !$this->total_amount) {
            return;
        }
        
        $installmentAmount = round($this->total_amount / $this->installment_count, 2);
        $this->installment_amount = $installmentAmount;
        
        // Handle rounding difference in last installment
        $lastInstallmentAmount = $this->total_amount - ($installmentAmount * ($this->installment_count - 1));
        
        $startDate = $this->due_date ? Carbon::parse($this->due_date) : Carbon::now();
        
        for ($i = 0; $i < $this->installment_count; $i++) {
            $dueDate = clone $startDate;
            
            // Calculate due date based on frequency
            switch ($this->installment_frequency) {
                case 'weekly':
                    $dueDate->addWeeks($i);
                    break;
                case 'bi_weekly':
                    $dueDate->addWeeks($i * 2);
                    break;
                case 'monthly':
                    $dueDate->addMonths($i);
                    break;
                case 'quarterly':
                    $dueDate->addMonths($i * 3);
                    break;
            }
            
            $this->installmentSchedule[] = [
                'installment_number' => $i + 1,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => ($i === $this->installment_count - 1) ? $lastInstallmentAmount : $installmentAmount,
                'status' => 'pending'
            ];
        }
        
        // Set the next installment date
        if (count($this->installmentSchedule) > 0) {
            $this->next_installment_date = $this->installmentSchedule[0]['due_date'];
        }
    }
    
    /**
     * Update calculations when payable type changes
     */
    public function updatedPayableType($value)
    {
        if ($value === 'subscription') {
            $this->is_recurring = true;
        } else {
            $this->is_recurring = false;
        }
        
        if ($value === 'installment') {
            $this->calculateInstallmentSchedule();
        } else {
            $this->installmentSchedule = [];
        }
    }
    
    /**
     * Recalculate installments when related fields change
     */
    public function updatedInstallmentCount()
    {
        $this->calculateInstallmentSchedule();
    }
    
    public function updatedInstallmentFrequency()
    {
        $this->calculateInstallmentSchedule();
    }
    
    public function updatedTotalAmount()
    {
        if ($this->payable_type === 'installment') {
            $this->calculateInstallmentSchedule();
        }
    }

    public function save()
    {
        Log::info('Save method called for Trade and Other Payables', [
            'user_id' => auth()->id(),
            'edit_mode' => $this->editMode,
            'payable_id' => $this->payableId
        ]);

        try {
            Log::info('Validating form data', [
                'vendor_name' => $this->vendor_name,
                'bill_number' => $this->bill_number,
                'invoice_date' => $this->invoice_date,
                'amount' => $this->amount,
                'total_amount' => $this->total_amount,
                'vat_amount' => $this->vat_amount
            ]);

            // Auto-calculate due_date if not provided
            if (!$this->due_date && $this->invoice_date && $this->payment_terms) {
                $this->due_date = Carbon::parse($this->invoice_date)
                    ->addDays($this->payment_terms)
                    ->format('Y-m-d');
                Log::info('Auto-calculated due_date', [
                    'invoice_date' => $this->invoice_date,
                    'payment_terms' => $this->payment_terms,
                    'due_date' => $this->due_date
                ]);
            }
            
            $this->validate();
            
            Log::info('Validation passed, starting transaction');
        } catch (\Exception $e) {
            Log::error('Validation failed', [
                'error' => $e->getMessage(),
                'errors' => $this->getErrorBag()->toArray()
            ]);
            throw $e;
        }
        
        DB::beginTransaction();
        try {
            // Get institution configuration for accounts
            $institution = DB::table('institutions')->where('id', 1)->first();
            
            if (!$institution || !$institution->trade_payables_account) {
                throw new \Exception('Trade payables account not configured in institution settings');
            }
            
            // Always use the institution's trade payables account as parent
            $this->parent_account_number = $institution->trade_payables_account;
            
            // Prepare data - match trade_payables table structure
            $data = [
                'vendor_id' => $this->vendor_id,
                'vendor_name' => $this->vendor_name,
                'vendor_bank_name' => $this->vendor_bank_name,
                'vendor_bank_account_number' => $this->vendor_bank_account_number,
                'vendor_bank_branch' => $this->vendor_bank_branch,
                'vendor_swift_code' => $this->vendor_swift_code,
                'bill_number' => $this->bill_number ?: $this->invoice_number,
                'payable_type' => $this->payable_type,
                'purchase_order_number' => $this->purchase_order_number,
                'bill_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'amount' => $this->total_amount,  // total amount maps to amount
                'paid_amount' => 0,  // initially no payment
                'balance' => $this->total_amount,  // initial balance equals total
                'payment_terms' => $this->payment_terms,
                'description' => $this->description,
                'processing_status' => 'pending', // Will be updated by job
                'status' => 'pending',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Add payable type specific fields
            if ($this->payable_type === 'installment') {
                $data['installment_count'] = $this->installment_count;
                $data['installment_frequency'] = $this->installment_frequency;
                $data['installment_amount'] = $this->installment_amount;
                $data['next_installment_date'] = $this->next_installment_date;
                $data['installments_paid'] = 0;
            } elseif ($this->payable_type === 'subscription') {
                $data['is_recurring'] = true;
                $data['recurring_frequency'] = $this->recurring_frequency;
                $data['recurring_start_date'] = $this->recurring_start_date;
                $data['recurring_end_date'] = $this->recurring_end_date;
                $data['next_billing_date'] = $this->next_billing_date;
                $data['service_type'] = $this->service_type;
                $data['subscription_status'] = $this->subscription_status;
            }

            Log::info('Prepared data for saving', ['data' => $data]);
            
            // Handle file uploads
            if ($this->invoice_attachment) {
                $path = $this->invoice_attachment->store('payables/invoices', 'public');
                $data['invoice_attachment'] = $path;
            }
            
            if ($this->purchase_order_attachment) {
                $path = $this->purchase_order_attachment->store('payables/purchase_orders', 'public');
                $data['purchase_order_attachment'] = $path;
            }
            
            if ($this->editMode && $this->payableId) {
                Log::info('Updating existing payable', ['id' => $this->payableId]);
                // Update existing payable
                unset($data['created_at'], $data['created_by']);
                DB::table('trade_payables')
                    ->where('id', $this->payableId)
                    ->update($data);
                
                Log::info('Payable updated successfully', ['id' => $this->payableId]);
                $message = 'Payable updated successfully!';
            } else {
                Log::info('Creating new payable');
                // Create new payable
                $payableId = DB::table('trade_payables')->insertGetId($data);
                Log::info('Payable created with ID', ['id' => $payableId]);
                
                // If it's an installment payable, create individual installment records
                if ($this->payable_type === 'installment' && count($this->installmentSchedule) > 0) {
                    foreach ($this->installmentSchedule as $installment) {
                        $installmentData = [
                            'vendor_id' => $this->vendor_id,
                            'vendor_name' => $this->vendor_name,
                            'vendor_bank_name' => $this->vendor_bank_name,
                            'vendor_bank_account_number' => $this->vendor_bank_account_number,
                            'vendor_bank_branch' => $this->vendor_bank_branch,
                            'vendor_swift_code' => $this->vendor_swift_code,
                            'bill_number' => $this->bill_number . '-' . str_pad($installment['installment_number'], 2, '0', STR_PAD_LEFT),
                            'payable_type' => 'once_off', // Each installment is treated as once-off
                            'parent_payable_id' => $payableId,
                            'bill_date' => $this->invoice_date,
                            'due_date' => $installment['due_date'],
                            'amount' => $installment['amount'],
                            'paid_amount' => 0,
                            'balance' => $installment['amount'],
                            'payment_terms' => $this->payment_terms,
                            'description' => $this->description . ' - Installment ' . $installment['installment_number'] . ' of ' . $this->installment_count,
                            'processing_status' => 'pending',
                            'status' => 'pending',
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        DB::table('trade_payables')->insert($installmentData);
                    }
                    
                    Log::info('Created installment payables', [
                        'parent_id' => $payableId,
                        'installment_count' => count($this->installmentSchedule)
                    ]);
                }
                
                // Create child account under Trade Payables parent and post GL entries
                try {
                    // Get parent account for Trade Payables
                    $parentAccount = DB::table('accounts')
                        ->where('account_number', $institution->trade_payables_account)
                        ->first();
                    
                    if (!$parentAccount) {
                        throw new \Exception('Parent Trade Payables account not found');
                    }
                    
                    // Create account using service
                    $accountService = new AccountCreationService();
                    $newAccount = $accountService->createAccount([
                        'account_use' => 'internal',
                        'account_name' => 'TP - ' . $this->bill_number . ' - ' . $this->vendor_name,
                        'type' => $parentAccount->type,
                        'product_number' => $parentAccount->product_number ?: '2110',
                        'member_number' => '00000', // Set to '00000' for internal accounts
                        'branch_number' => auth()->user()->branch
                    ], $institution->trade_payables_account);
                    
                    $accountNumber = $newAccount->account_number;
                    
                    // Update the account status to ACTIVE immediately after creation
                    DB::table('accounts')
                        ->where('account_number', $accountNumber)
                        ->update(['status' => 'ACTIVE']);
                    
                    // Update payable with created account number
                    DB::table('trade_payables')
                        ->where('id', $payableId)
                        ->update([
                            'account_number' => $accountNumber,  // For backward compatibility
                            'created_payable_account_number' => $accountNumber  // Store the created account number
                        ]);
                    
                    Log::info('Child account created for payable using AccountCreationService', [
                        'account_number' => $accountNumber,
                        'account_id' => $newAccount->id,
                        'parent_account' => $institution->trade_payables_account
                    ]);
                    
                    // Post GL entries using TransactionPostingService
                    $transactionService = new TransactionPostingService();
                    
                    // Get the expense account
                    $expenseAccount = DB::table('accounts')->find($this->other_account_id);
                    if (!$expenseAccount) {
                        throw new \Exception('Expense account not found');
                    }
                    
                    // Entry 1: Record the payable (excluding VAT)
                    if ($this->amount > 0) {
                        $payableEntry = [
                            'first_account' => $expenseAccount->account_number, // Debit: Expense Account
                            'second_account' => $accountNumber, // Credit: Trade Payable child account
                            'amount' => $this->amount,
                            'narration' => 'Bill ' . $this->bill_number . ' - ' . $this->vendor_name,
                            'action' => 'bill_creation'
                        ];
                        
                        $result = $transactionService->postTransaction($payableEntry);
                        if ($result['status'] !== 'success') {
                            throw new \Exception('Failed to post payable entry: ' . ($result['message'] ?? 'Unknown error'));
                        }
                        
                        Log::info('Posted payable GL entry', [
                            'debit' => $expenseAccount->account_number,
                            'credit' => $accountNumber,
                            'amount' => $this->amount
                        ]);
                    }
                    
                    // Entry 2: Record Input VAT if applicable
                    if ($this->vat_amount > 0) {
                        // Check if VAT receivable account is configured
                        if (!isset($institution->vat_receivable_account) || !$institution->vat_receivable_account) {
                            // Create VAT Receivable account if it doesn't exist
                            $this->ensureVatReceivableAccountExists($institution);
                            // Refresh institution data
                            $institution = DB::table('institutions')->where('id', 1)->first();
                        }
                        
                        if ($institution->vat_receivable_account) {
                            $vatEntry = [
                                'first_account' => $institution->vat_receivable_account, // Debit: VAT Receivable (Input VAT)
                                'second_account' => $accountNumber, // Credit: Trade Payable child account
                                'amount' => $this->vat_amount,
                                'narration' => 'Input VAT on Bill ' . $this->bill_number,
                                'action' => 'bill_creation'
                            ];
                            
                            $result = $transactionService->postTransaction($vatEntry);
                            if ($result['status'] !== 'success') {
                                throw new \Exception('Failed to post VAT entry: ' . ($result['message'] ?? 'Unknown error'));
                            }
                            
                            Log::info('Posted VAT GL entry', [
                                'debit' => $institution->vat_receivable_account,
                                'credit' => $accountNumber,
                                'amount' => $this->vat_amount
                            ]);
                        } else {
                            Log::warning('VAT amount specified but VAT receivable account not configured', [
                                'vat_amount' => $this->vat_amount
                            ]);
                        }
                    }
                    
                    Log::info('Trade payable fully integrated with accounts and GL', [
                        'payable_id' => $payableId,
                        'account_number' => $accountNumber,
                        'vendor' => $this->vendor_name,
                        'total_amount' => $this->total_amount
                    ]);
                    
                    // Dispatch job to send notifications asynchronously if vendor email exists
                    if ($this->vendor_email) {
                        try {
                            ProcessTradePayableInvoice::dispatch($payableId, auth()->id())
                                ->onQueue('invoices')
                                ->delay(now()->addSeconds(2)); // Small delay to ensure DB transaction is committed
                            
                            Log::info('Bill notification job dispatched', [
                                'payable_id' => $payableId,
                                'user_id' => auth()->id()
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to dispatch bill job', [
                                'error' => $e->getMessage(),
                                'payable_id' => $payableId
                            ]);
                            // Don't throw - payable creation was successful even if job dispatch failed
                        }
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Failed to integrate payable with accounts table', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                
                $message = 'Payable created successfully!';
            }
            
            DB::commit();
            
            $this->resetForm();
            $this->showCreateModal = false;
            $this->reset(['search', 'statusFilter', 'ageFilter']); // Reset filters to show all records
            $this->loadStatistics();
            session()->flash('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving payable: ' . $e->getMessage());
            
            session()->flash('error', 'Error saving payable: ' . $e->getMessage());
        }
    }

    /**
     * Reset form fields to default values
     */
    private function resetForm()
    {
        $this->payableId = null;
        $this->vendor_id = null;
        $this->vendor_name = '';
        $this->vendor_email = '';
        $this->vendor_phone = '';
        $this->vendor_address = '';
        $this->vendor_tax_id = '';
        $this->vendor_bank_name = '';
        $this->vendor_bank_account_number = '';
        $this->vendor_bank_branch = '';
        $this->vendor_swift_code = '';
        $this->invoice_number = '';
        $this->invoice_date = null;
        $this->bill_number = '';
        $this->purchase_order_number = '';
        $this->amount = 0;
        $this->vat_amount = 0;
        $this->total_amount = 0;
        $this->currency = 'TZS';
        $this->due_date = null;
        $this->payment_terms = 30;
        $this->description = '';
        $this->expense_account_id = null;
        $this->payable_account_id = null;
        $this->bank_account_id = null;
        $this->payment_type = 'cash';
        $this->parent_account_number = null;
        $this->other_account_id = null;
        $this->created_payable_account_number = null;
        $this->notes = '';
        $this->priority = 'normal';
        $this->approval_status = 'pending';
        $this->editMode = false;
    }

    private function createGLEntries($payableId, $data)
    {
        $reference = 'PAY-' . $data['invoice_number'];
        $description = 'Bill ' . $data['invoice_number'] . ' - ' . $data['vendor_name'];
        
        // Debit Expense Account
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'PAYABLE',
            'transaction_date' => $data['invoice_date'],
            'account_id' => $data['expense_account_id'],
            'debit_amount' => $data['amount'],
            'credit_amount' => 0,
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $payableId,
            'source_type' => 'payables'
        ]);
        
        // Debit VAT if applicable
        if ($data['vat_amount'] > 0) {
            $vatAccount = AccountsModel::where('account_name', 'like', '%VAT%Input%')
                ->where('account_type', 'ASSET')
                ->first();
            
            if (!$vatAccount) {
                $vatAccount = AccountsModel::where('account_name', 'like', '%VAT%')
                    ->where('account_type', 'ASSET')
                    ->first();
            }
            
            if ($vatAccount) {
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'PAYABLE',
                    'transaction_date' => $data['invoice_date'],
                    'account_id' => $vatAccount->id,
                    'debit_amount' => $data['vat_amount'],
                    'credit_amount' => 0,
                    'description' => 'Input VAT on ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $payableId,
                    'source_type' => 'payables'
                ]);
            }
        }
        
        // Credit Accounts Payable
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'PAYABLE',
            'transaction_date' => $data['invoice_date'],
            'account_id' => $data['payable_account_id'],
            'debit_amount' => 0,
            'credit_amount' => $data['total_amount'],
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $payableId,
            'source_type' => 'payables'
        ]);
    }
    
    public function openPaymentModal($payableId)
    {
        Log::info('Payment modal opened', [
            'payable_id' => $payableId,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $payable = DB::table('trade_payables')->find($payableId);
        
        if ($payable) {
            // Check if already paid
            if ($payable->status === 'paid') {
                session()->flash('error', 'This payable has already been paid.');
                return;
            }
            
            Log::info('Payment modal data loaded', [
                'payable_id' => $payableId,
                'bill_number' => $payable->bill_number,
                'balance' => $payable->balance,
                'vendor' => $payable->vendor_name
            ]);
            
            $this->payment_payable_id = $payableId;
            $this->selectedBalance = $payable->balance; // Set the balance for validation
            $this->payment_amount = $payable->balance;
            $this->payment_date = now()->format('Y-m-d');
            $this->payment_method = 'bank_transfer';
            
            // Calculate early payment discount if applicable
            $daysEarly = Carbon::parse($payable->due_date)->diffInDays(now(), false);
            if ($daysEarly > 0 && $payable->payment_terms > 0) {
                // 2% discount if paid 10 days early
                if ($daysEarly >= 10) {
                    $this->early_payment_discount = $payable->balance * 0.02;
                }
            }
            
            // Ensure payment accounts is an array before loading
            if (!is_array($this->paymentAccounts)) {
                $this->paymentAccounts = [];
            }
            
            // Load payment accounts
            $this->loadPaymentAccounts();
            
            // Open modal after loading accounts
            $this->showPaymentModal = true;
        }
    }
    
    /**
     * Load available payment accounts from bank_accounts table
     */
    private function loadPaymentAccounts()
    {
        // Get bank accounts for payment
        $accounts = DB::table('bank_accounts')
            ->where('status', 'ACTIVE')
            ->whereNotNull('internal_mirror_account_number') // Must have mirror account
            ->select(
                'internal_mirror_account_number as account_number',
                'bank_name',
                'account_name',
                'account_number as bank_account_number',
                'current_balance',
                'currency'
            )
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->get();
        
        // Convert to array for Livewire
        $this->paymentAccounts = $accounts ? $accounts->map(function ($account) {
            return (array) $account;
        })->toArray() : [];
        
        // Set default payment account if available
        if (!empty($this->paymentAccounts) && !$this->payment_account_id) {
            $this->payment_account_id = $this->paymentAccounts[0]['account_number'];
        }
    }
    
    public function processPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $this->selectedBalance,
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'payment_account_id' => 'required', // This will be the internal_mirror_account_number
        ], [
            'payment_amount.max' => 'Payment amount cannot exceed the outstanding balance of ' . number_format($this->selectedBalance, 2),
            'payment_date.before_or_equal' => 'Payment date cannot be in the future',
            'payment_account_id.required' => 'Please select a bank account for payment'
        ]);
        
        DB::beginTransaction();
        try {
            $payable = DB::table('trade_payables')->find($this->payment_payable_id);
            
            if (!$payable) {
                throw new \Exception('Payable not found');
            }
            
            // Get bank account details for the transfer
            $bankAccount = DB::table('bank_accounts')
                ->where('internal_mirror_account_number', $this->payment_account_id)
                ->where('status', 'ACTIVE')
                ->first();
                
            if (!$bankAccount) {
                throw new \Exception('Bank account not found for payment. Account: ' . $this->payment_account_id);
            }
            
            // Get the mirror account for GL posting
            $mirrorAccount = AccountsModel::where('account_number', $bankAccount->internal_mirror_account_number)->first();
            if (!$mirrorAccount) {
                throw new \Exception('Mirror account not found for GL posting: ' . $bankAccount->internal_mirror_account_number);
            }
            
            // Check if payable has created account number for double-entry
            if (!$payable->created_payable_account_number) {
                // Try to use a default trade payables account
                $defaultPayableAccount = AccountsModel::where('type', 'liability_accounts')
                    ->where(function($query) {
                        $query->where('account_name', 'TRADE PAYABLES')
                              ->orWhere('account_name', 'ACCOUNTS PAYABLE')
                              ->orWhere('account_name', 'like', '%TRADE PAYABLE%');
                    })
                    ->first();
                    
                if ($defaultPayableAccount) {
                    // Update the payable with this account number
                    DB::table('trade_payables')
                        ->where('id', $this->payment_payable_id)
                        ->update(['created_payable_account_number' => $defaultPayableAccount->account_number]);
                    
                    $payable->created_payable_account_number = $defaultPayableAccount->account_number;
                    
                    Log::info('Using default payable account', [
                        'payable_id' => $this->payment_payable_id,
                        'account_number' => $defaultPayableAccount->account_number,
                        'account_name' => $defaultPayableAccount->account_name
                    ]);
                } else {
                    throw new \Exception('Payable account not configured. Please create the payable properly or configure a default trade payables account.');
                }
            }
            
            // Get the payable account for double-entry
            $payableAccount = AccountsModel::where('account_number', $payable->created_payable_account_number)->first();
            if (!$payableAccount) {
                throw new \Exception('Payable account not found: ' . $payable->created_payable_account_number);
            }
            
            // Calculate net payment
            $netPayment = (float)$this->payment_amount - (float)$this->early_payment_discount - (float)$this->withholding_tax + (float)$this->bank_charges;
            
            // Calculate new balance
            $newBalance = (float)$payable->balance - (float)$this->payment_amount - (float)$this->early_payment_discount;
            $totalPaid = (float)$payable->paid_amount + (float)$this->payment_amount + (float)$this->early_payment_discount;
            
            // Determine if it's internal or external transfer based on vendor bank
            // Internal: NBC to NBC transfers
            // External: NBC to other banks
            
            $isInternalTransfer = false;
            $vendorBankCode = null;
            
            // Check vendor's bank name to determine transfer type
            if ($payable->vendor_bank_name) {
                // Check if vendor bank is NBC (case insensitive)
                $vendorBankName = strtoupper(trim($payable->vendor_bank_name));
                
                // Check against NBC variations
                $nbcVariations = ['NBC', 'NBC BANK', 'NATIONAL BANK OF COMMERCE', 'NBC LIMITED', 'NBC LTD'];
                $isNBC = false;
                
                foreach ($nbcVariations as $variation) {
                    if (strpos($vendorBankName, $variation) !== false) {
                        $isNBC = true;
                        break;
                    }
                }
                
                if ($isNBC) {
                    $isInternalTransfer = true;
                    $vendorBankCode = 'NLCBTZTX'; // NBC bank code from FSP config
                } else {
                    // Try to match with other banks from FSP config
                    $fspBanks = config('fsp_providers.banks', []);
                    foreach ($fspBanks as $code => $bank) {
                        if (strpos($vendorBankName, strtoupper($bank['name'])) !== false || 
                            strpos($vendorBankName, $code) !== false) {
                            $vendorBankCode = $bank['code'];
                            break;
                        }
                    }
                }
            }
            
            // Prepare transfer data
            $transferData = [
                'source_bank_account' => $bankAccount->account_number,  // Actual bank account for transfer
                'mirror_account' => $bankAccount->internal_mirror_account_number,  // Mirror account for GL posting
                'destination_account' => $payableAccount->account_number,  // Created payable account for double-entry
                'amount' => $netPayment,
                'description' => 'Payment for Bill: ' . $payable->bill_number . ' - ' . $payable->vendor_name,
                'reference_number' => 'PAY-' . $payable->bill_number . '-' . date('YmdHis'),
                'transaction_date' => $this->payment_date,
            ];
            
            // Process the transfer using appropriate service
            if ($isInternalTransfer) {
                // NBC to NBC transfer - Use Internal Funds Transfer Service
                $internalTransferService = new \App\Services\Payments\InternalFundsTransferService();
                
                // Prepare internal transfer data according to service requirements
                $internalTransferData = [
                    'from_account' => $bankAccount->account_number,  // Source NBC bank account (actual bank account)
                    'to_account' => $payable->vendor_bank_account_number, // Destination NBC account (vendor's bank)
                    'amount' => $netPayment,
                    'narration' => 'Payment for Bill: ' . $payable->bill_number . ' - ' . $payable->vendor_name,
                    'sender_name' => config('app.name', 'SACCOS'),
                    'from_currency' => 'TZS',
                    'to_currency' => 'TZS'
                ];
                
                $transferResult = $internalTransferService->transfer($internalTransferData);
                
                if (!$transferResult['success']) {
                    throw new \Exception('Internal NBC transfer failed: ' . ($transferResult['error'] ?? $transferResult['message'] ?? 'Unknown error'));
                }
                
                Log::info('Internal NBC vendor payment processed', [
                    'payable_id' => $this->payment_payable_id,
                    'amount' => $netPayment,
                    'vendor_account' => $payable->vendor_bank_account_number,
                    'vendor_bank' => 'NBC',
                    'reference' => $transferResult['reference'] ?? null
                ]);
                
                // Store transfer reference for payment record
                $transferData['transfer_reference'] = $transferResult['reference'] ?? null;
                $transferData['nbc_reference'] = $transferResult['nbc_reference'] ?? null;
                
                // Post GL entries using TransactionPostingService
                $postingService = new \App\Services\TransactionPostingService();
                
                // Main payment posting - Debit Payables, Credit Bank Mirror Account
                $grossPaymentAmount = (float)$this->payment_amount + (float)$this->early_payment_discount;
                $postingData = [
                    'source_account' => $payableAccount->account_number, // Debit: Payable Account (liability decreases)
                    'destination_account' => $bankAccount->internal_mirror_account_number, // Credit: Bank Mirror Account (asset decreases)
                    'amount' => $grossPaymentAmount,
                    'description' => 'Payment for Bill: ' . $payable->bill_number . ' - ' . $payable->vendor_name . ' (Internal Transfer)',
                    'reference_number' => $transferData['reference_number'],
                    'transaction_date' => $this->payment_date,
                    'transaction_type' => 'payable_payment'
                ];
                
                $postingResult = $postingService->postTransaction($postingData);
                
                if (!$postingResult) {
                    Log::warning('GL posting failed for internal transfer payment', [
                        'payable_id' => $this->payment_payable_id,
                        'reference' => $transferData['reference_number']
                    ]);
                }
                
                // Handle bank charges if any (Debit Bank Charges Expense, Credit Bank)
                if ($this->bank_charges > 0) {
                    $bankChargesAccount = \App\Models\AccountsModel::where('account_name', 'like', '%bank charges%')
                        ->where('type', 'expense_accounts')
                        ->first();
                    
                    if ($bankChargesAccount) {
                        $chargesData = [
                            'source_account' => $bankChargesAccount->account_number, // Debit: Bank Charges
                            'destination_account' => $bankAccount->internal_mirror_account_number, // Credit: Bank Mirror Account
                            'amount' => $this->bank_charges,
                            'description' => 'Bank charges for payment: ' . $payable->bill_number,
                            'reference_number' => $transferData['reference_number'] . '-CHG',
                            'transaction_date' => $this->payment_date,
                            'transaction_type' => 'bank_charges'
                        ];
                        $postingService->postTransaction($chargesData);
                    }
                }
                
            } elseif ($vendorBankCode && $payable->vendor_bank_account_number) {
                // External bank transfer - Use External Funds Transfer Service
                $externalTransferService = new \App\Services\Payments\ExternalFundsTransferService();
                
                // Prepare external transfer data according to service requirements
                $externalTransferData = [
                    'from_account' => $bankAccount->account_number,  // Source NBC bank account (actual bank account)
                    'to_account' => $payable->vendor_bank_account_number, // Destination external bank account
                    'bank_code' => $vendorBankCode,
                    'amount' => $netPayment,
                    'narration' => 'Payment for Bill: ' . $payable->bill_number . ' - ' . $payable->vendor_name,
                    'beneficiary_name' => $payable->vendor_name,
                    'from_currency' => 'TZS',
                    'to_currency' => 'TZS'
                ];
                
                $transferResult = $externalTransferService->transfer($externalTransferData);
                
                if (!$transferResult['success']) {
                    throw new \Exception('External bank transfer failed: ' . ($transferResult['error'] ?? $transferResult['message'] ?? 'Unknown error'));
                }
                
                Log::info('External bank vendor payment processed', [
                    'payable_id' => $this->payment_payable_id,
                    'amount' => $netPayment,
                    'vendor_bank' => $payable->vendor_bank_name,
                    'vendor_account' => $payable->vendor_bank_account_number,
                    'bank_code' => $vendorBankCode,
                    'reference' => $transferResult['reference'] ?? null,
                    'routing_system' => $transferResult['routing_system'] ?? null
                ]);
                
                // Store transfer references for payment record
                $transferData['transfer_reference'] = $transferResult['reference'] ?? null;
                $transferData['nbc_reference'] = $transferResult['nbc_reference'] ?? null;
                $transferData['routing_system'] = $transferResult['routing_system'] ?? null;
                
                // Post GL entries using TransactionPostingService
                $postingService = new \App\Services\TransactionPostingService();
                
                // Main payment posting - Debit Payables, Credit Bank Mirror Account
                $grossPaymentAmount = (float)$this->payment_amount + (float)$this->early_payment_discount;
                $postingData = [
                    'source_account' => $payableAccount->account_number, // Debit: Payable Account (liability decreases)
                    'destination_account' => $bankAccount->internal_mirror_account_number, // Credit: Bank Mirror Account (asset decreases)
                    'amount' => $grossPaymentAmount,
                    'description' => 'Payment for Bill: ' . $payable->bill_number . ' - ' . $payable->vendor_name . ' (External Transfer to ' . $payable->vendor_bank_name . ')',
                    'reference_number' => $transferData['reference_number'],
                    'transaction_date' => $this->payment_date,
                    'transaction_type' => 'payable_payment'
                ];
                
                $postingResult = $postingService->postTransaction($postingData);
                
                if (!$postingResult) {
                    Log::warning('GL posting failed for external transfer payment', [
                        'payable_id' => $this->payment_payable_id,
                        'reference' => $transferData['reference_number']
                    ]);
                }
                
                // Handle bank charges if any (Debit Bank Charges Expense, Credit Bank)
                if ($this->bank_charges > 0) {
                    $bankChargesAccount = \App\Models\AccountsModel::where('account_name', 'like', '%bank charges%')
                        ->where('type', 'expense_accounts')
                        ->first();
                    
                    if ($bankChargesAccount) {
                        $chargesData = [
                            'source_account' => $bankChargesAccount->account_number, // Debit: Bank Charges
                            'destination_account' => $bankAccount->internal_mirror_account_number, // Credit: Bank Mirror Account
                            'amount' => $this->bank_charges,
                            'description' => 'Bank charges for external payment: ' . $payable->bill_number,
                            'reference_number' => $transferData['reference_number'] . '-CHG',
                            'transaction_date' => $this->payment_date,
                            'transaction_type' => 'bank_charges'
                        ];
                        $postingService->postTransaction($chargesData);
                    }
                }
                
            } else {
                // Fallback to standard posting for cases without bank details
                $postingService = new \App\Services\TransactionPostingService();
                
                // Post the payment transaction using mirror account
                // Debit: Payable Account (liability decreases)
                // Credit: Bank Mirror Account (asset decreases)
                $postingData = [
                    'first_account' => $payableAccount->account_number,  // Debit - Payable Account
                    'second_account' => $bankAccount->internal_mirror_account_number,   // Credit - Bank Mirror Account
                    'amount' => $netPayment,
                    'narration' => $transferData['description'],
                    'reference_number' => $transferData['reference_number'],
                    'transaction_date' => $this->payment_date,
                    'action' => 'vendor_payment'
                ];
                
                $postingResult = $postingService->postTransaction($postingData);
                
                if (!$postingResult) {
                    throw new \Exception('Transaction posting failed');
                }
                
                Log::info('Standard vendor payment processed (no bank details)', [
                    'payable_id' => $this->payment_payable_id,
                    'amount' => $netPayment,
                    'vendor_name' => $payable->vendor_name
                ]);
            }
            
            // Update payable status
            DB::table('trade_payables')
                ->where('id', $this->payment_payable_id)
                ->update([
                    'balance' => max(0, $newBalance),
                    'paid_amount' => $totalPaid,
                    'last_payment_date' => $this->payment_date,
                    'payment_date' => $newBalance <= 0 ? $this->payment_date : null,
                    'status' => $newBalance <= 0 ? 'paid' : 'partial',
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            
            // Record payment in payable_payments table for audit trail
            $paymentId = DB::table('payable_payments')->insertGetId([
                'payable_id' => $this->payment_payable_id,
                'payment_date' => $this->payment_date,
                'amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'reference_number' => $transferData['reference_number'],
                'bank_charges' => $this->bank_charges,
                'early_payment_discount' => $this->early_payment_discount,
                'withholding_tax' => $this->withholding_tax,
                'notes' => $this->payment_notes,
                'transfer_type' => $isInternalTransfer ? 'internal' : ($vendorBankCode ? 'external' : 'standard'),
                'transfer_reference' => $transferData['transfer_reference'] ?? null,
                'nbc_reference' => $transferData['nbc_reference'] ?? null,
                'routing_system' => $transferData['routing_system'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
            
            // Note: GL entries are created via TransactionPostingService during transfer processing
            
            // Handle file upload
            if ($this->payment_voucher) {
                $path = $this->payment_voucher->store('payables/payments', 'public');
                DB::table('payable_payments')
                    ->where('id', $paymentId)
                    ->update(['payment_voucher' => $path]);
            }
            
            DB::commit();
            
            $this->showPaymentModal = false;
            $this->reset(['payment_amount', 'payment_reference', 'payment_notes', 
                         'bank_charges', 'early_payment_discount', 'withholding_tax']);
            $this->loadStatistics();
            
            session()->flash('success', 'Payment processed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment: ' . $e->getMessage());
            
            session()->flash('error', 'Error processing payment: ' . $e->getMessage());
        }
    }
    
    private function createPaymentGLEntries($payable, $paymentId)
    {
        $reference = 'PAYMENT-' . str_pad($paymentId, 6, '0', STR_PAD_LEFT);
        $description = 'Payment for Bill ' . $payable->bill_number;
        
        // Get default accounts payable account
        $payableAccount = AccountsModel::where('account_type', 'LIABILITY')
            ->where('account_name', 'like', '%payable%')
            ->first();
            
        if (!$payableAccount) {
            Log::warning('No accounts payable account found, skipping GL entries');
            return;
        }
        
        // Debit Accounts Payable
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'PAYMENT',
            'transaction_date' => $this->payment_date,
            'account_id' => $payableAccount->id,
            'debit_amount' => (float)$this->payment_amount + (float)$this->early_payment_discount,
            'credit_amount' => 0,
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $paymentId,
            'source_type' => 'payable_payments'
        ]);
        
        // Credit Bank/Cash Account
        $creditAmount = (float)$this->payment_amount - (float)$this->withholding_tax + (float)$this->bank_charges;
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'PAYMENT',
            'transaction_date' => $this->payment_date,
            'account_id' => $this->payment_account_id,
            'debit_amount' => 0,
            'credit_amount' => $creditAmount,
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $paymentId,
            'source_type' => 'payable_payments'
        ]);
        
        // Record bank charges if any
        if ($this->bank_charges > 0) {
            $bankChargesAccount = AccountsModel::where('account_name', 'like', '%bank charges%')
                ->where('account_type', 'EXPENSE')
                ->first();
            
            if ($bankChargesAccount) {
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'PAYMENT',
                    'transaction_date' => $this->payment_date,
                    'account_id' => $bankChargesAccount->id,
                    'debit_amount' => $this->bank_charges,
                    'credit_amount' => 0,
                    'description' => 'Bank charges for ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $paymentId,
                    'source_type' => 'payable_payments'
                ]);
            }
        }
        
        // Record early payment discount if any
        if ($this->early_payment_discount > 0) {
            $discountAccount = AccountsModel::where('account_name', 'like', '%discount%received%')
                ->orWhere('account_name', 'like', '%discount%')
                ->where('account_type', 'REVENUE')
                ->first();
            
            if ($discountAccount) {
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'PAYMENT',
                    'transaction_date' => $this->payment_date,
                    'account_id' => $discountAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $this->early_payment_discount,
                    'description' => 'Early payment discount on ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $paymentId,
                    'source_type' => 'payable_payments'
                ]);
            }
        }
        
        // Record withholding tax if any
        if ($this->withholding_tax > 0) {
            $withholdingAccount = AccountsModel::where('account_name', 'like', '%withholding%tax%')
                ->where('account_type', 'LIABILITY')
                ->first();
            
            if ($withholdingAccount) {
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'PAYMENT',
                    'transaction_date' => $this->payment_date,
                    'account_id' => $withholdingAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $this->withholding_tax,
                    'description' => 'Withholding tax on ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $paymentId,
                    'source_type' => 'payable_payments'
                ]);
            }
        }
    }
    
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $payable = DB::table('trade_payables')->find($id);
            
            if (!$payable) {
                throw new \Exception('Payable not found');
            }
            
            // Update approval status (Note: approval_status columns don't exist in trade_payables)
            DB::table('trade_payables')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',  // Using status column instead
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            
            // Skip GL entries on approval as required columns don't exist in trade_payables
            // GL entries will be created when payment is processed
            
            // Log approval
            if (Schema::hasTable('payable_approvals')) {
                DB::table('payable_approvals')->insert([
                    'payable_id' => $id,
                    'action' => 'approved',
                    'comments' => 'Approved for payment',
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                ]);
            }
            
            DB::commit();
            
            $this->loadStatistics();
            
            session()->flash('success', 'Payable approved successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            session()->flash('error', 'Error approving payable: ' . $e->getMessage());
        }
    }
    
    public function edit($id)
    {
        Log::info('Edit initiated for payable', [
            'payable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $payable = DB::table('trade_payables')->find($id);
        
        if ($payable) {
            Log::info('Loading payable data for editing', [
                'payable_id' => $id,
                'bill_number' => $payable->bill_number,
                'vendor' => $payable->vendor_name,
                'amount' => $payable->amount,
                'status' => $payable->status
            ]);
            $this->payableId = $id;
            $this->vendor_id = $payable->vendor_id;
            $this->vendor_name = $payable->vendor_name;
            
            // Load vendor details if vendor_id exists
            if ($payable->vendor_id && Schema::hasTable('vendors')) {
                $vendor = DB::table('vendors')->find($payable->vendor_id);
                if ($vendor) {
                    $this->vendor_email = $vendor->email ?? '';
                    $this->vendor_phone = $vendor->phone ?? '';
                    $this->vendor_address = $vendor->address ?? '';
                    $this->vendor_tax_id = $vendor->organization_tin_number ?? '';
                }
            } else {
                $this->vendor_email = '';
                $this->vendor_phone = '';
                $this->vendor_address = '';
                $this->vendor_tax_id = '';
            }
            
            // Load vendor bank details
            $this->vendor_bank_name = $payable->vendor_bank_name ?? '';
            $this->vendor_bank_account_number = $payable->vendor_bank_account_number ?? '';
            $this->vendor_bank_branch = $payable->vendor_bank_branch ?? '';
            $this->vendor_swift_code = $payable->vendor_swift_code ?? '';
            
            $this->invoice_number = $payable->bill_number;
            $this->bill_number = $payable->bill_number;
            $this->purchase_order_number = $payable->purchase_order_number;
            $this->invoice_date = $payable->bill_date;
            $this->due_date = $payable->due_date;
            $this->amount = (float)$payable->amount;
            $this->vat_amount = 0;  // Not in trade_payables
            $this->total_amount = (float)$payable->amount;
            $this->currency = 'TZS';  // Default currency as not in trade_payables
            $this->payment_terms = $payable->payment_terms;
            $this->description = $payable->description;
            
            // Load the saved account references
            $this->parent_account_number = $payable->parent_account_number ?? null;
            $this->other_account_id = $payable->other_account_id ?? null;
            $this->payable_account_id = $payable->payable_account_id ?? null;
            $this->created_payable_account_number = $payable->created_payable_account_number ?? null;
            $this->expense_account_id = $payable->other_account_id ?? null;  // For backward compatibility
            
            $this->notes = '';  // Not in trade_payables
            $this->priority = 'normal';  // Default as not in trade_payables
            
            $this->editMode = true;
            $this->showCreateModal = true;
            
            Log::info('Edit modal opened', [
                'payable_id' => $id,
                'edit_mode' => true
            ]);
        } else {
            Log::warning('Edit failed - payable not found', [
                'payable_id' => $id
            ]);
            
            session()->flash('error', 'Payable not found');
        }
    }
    
    /**
     * Show confirmation dialog for delete action
     */
    public function confirmDelete($id)
    {
        Log::info('Cancel/Delete confirmation initiated', [
            'payable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $this->confirmActionId = $id;
        $this->confirmAction = 'delete';
        $this->confirmTitle = 'Cancel Bill';
        $this->confirmMessage = 'Are you sure you want to cancel this bill? This will create reversal entries in the GL and cannot be undone.';
        $this->confirmButtonText = 'Yes, Cancel Bill';
        $this->showConfirmModal = true;
    }
    
    /**
     * Proceed with the confirmed action
     */
    public function proceedWithAction()
    {
        Log::info('Proceeding with confirmed action', [
            'action' => $this->confirmAction,
            'payable_id' => $this->confirmActionId,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $this->showConfirmModal = false;
        
        if ($this->confirmAction && $this->confirmActionId) {
            switch ($this->confirmAction) {
                case 'delete':
                    Log::info('Executing delete/cancel action', [
                        'payable_id' => $this->confirmActionId
                    ]);
                    $this->delete($this->confirmActionId);
                    break;
                default:
                    Log::warning('Unknown action attempted', [
                        'action' => $this->confirmAction,
                        'payable_id' => $this->confirmActionId
                    ]);
            }
        }
        
        // Reset confirmation properties
        $this->confirmAction = '';
        $this->confirmActionId = null;
        $this->confirmTitle = '';
        $this->confirmMessage = '';
        $this->confirmButtonText = 'Confirm';
    }
    
    /**
     * View payable details
     */
    public function viewDetails($id)
    {
        Log::info('View details initiated', [
            'payable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        // You can implement a details modal here
        // For now, just log the action
        session()->flash('success', 'Details view will be implemented');
    }
    
    public function delete($id)
    {
        // First check if it's a system payable
        $payable = DB::table('trade_payables')->find($id);
        
        if ($payable && $payable->is_system) {
            session()->flash('error', 'System payables cannot be deleted. You can only disable them.');
            return;
        }
        
        DB::beginTransaction();
        try {
            // Check if payable has payments
            if (Schema::hasTable('payable_payments')) {
                $hasPayments = DB::table('payable_payments')
                    ->where('payable_id', $id)
                    ->exists();
                
                if ($hasPayments) {
                    throw new \Exception('Cannot delete payable with payments. Please reverse payments first.');
                }
            }
            
            // Delete GL entries
            general_ledger::where('source_type', 'payables')
                ->where('source_id', $id)
                ->delete();
            
            // Delete payable
            DB::table('trade_payables')->where('id', $id)->delete();
            
            DB::commit();
            
            $this->loadStatistics();
            
            session()->flash('success', 'Payable deleted successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            session()->flash('error', $e->getMessage());
        }
    }
    
    public function render()
    {
        // Initialize empty collection for payables
        $payables = collect();
        
        // Check if the table exists before querying
        if (Schema::hasTable('trade_payables')) {
            $query = DB::table('trade_payables')
                ->select([
                    'trade_payables.*',
                    DB::raw("(CURRENT_DATE - trade_payables.due_date::date) as days_overdue")
                ]);
            
            // Apply filters
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('trade_payables.vendor_name', 'like', '%' . $this->search . '%')
                      ->orWhere('trade_payables.bill_number', 'like', '%' . $this->search . '%')
                      ->orWhere('trade_payables.description', 'like', '%' . $this->search . '%');
                });
            }
            
            if ($this->statusFilter && $this->statusFilter !== 'all') {
                $query->where('trade_payables.status', $this->statusFilter);
            }
            
            if ($this->ageFilter && $this->ageFilter !== 'all') {
                $today = Carbon::now();
                switch($this->ageFilter) {
                    case 'current':
                        $query->where('trade_payables.due_date', '>=', $today);
                        break;
                    case '30':
                        $query->whereBetween('trade_payables.due_date', [$today->copy()->subDays(30), $today]);
                        break;
                    case '60':
                        $query->whereBetween('trade_payables.due_date', [$today->copy()->subDays(60), $today->copy()->subDays(31)]);
                        break;
                    case '90':
                        $query->whereBetween('trade_payables.due_date', [$today->copy()->subDays(90), $today->copy()->subDays(61)]);
                        break;
                    case 'over90':
                        $query->where('trade_payables.due_date', '<', $today->copy()->subDays(90));
                        break;
                }
            }
            
            if ($this->dateFrom) {
                $query->where('trade_payables.bill_date', '>=', $this->dateFrom);
            }
            
            if ($this->dateTo) {
                $query->where('trade_payables.bill_date', '<=', $this->dateTo);
            }
            
            $payables = $query->orderBy('trade_payables.created_at', 'desc')
                ->paginate(10);
        } else {
            // Return empty paginator if table doesn't exist
            $payables = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                10,
                1,
                ['path' => request()->url()]
            );
        }
        
        // Get accounts for GL posting
        $expenseAccounts = DB::table('accounts')
            ->where('type', 'expense_accounts') // Expense accounts
            ->where('account_level', 3) // Level 3 expense accounts
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
            
        $parentAccounts = DB::table('accounts')
            ->where('type', 'liability_accounts') // Liability accounts
            ->where('account_level', 3) // Level 3 liability accounts
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        if (Schema::hasTable('trade_payables')) {
            $query = DB::table('trade_payables')
                ->select([
                    'trade_payables.*',
                    DB::raw("CASE 
                        WHEN trade_payables.due_date IS NOT NULL 
                        THEN (trade_payables.due_date::date - CURRENT_DATE)
                        ELSE 0 
                    END as days_until_due")
                ]);
            
            // Apply filters
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('trade_payables.vendor_name', 'like', '%' . $this->search . '%')
                      ->orWhere('trade_payables.bill_number', 'like', '%' . $this->search . '%')
                      ->orWhere('trade_payables.purchase_order_number', 'like', '%' . $this->search . '%');
                });
            }
            
            if ($this->statusFilter && $this->statusFilter !== 'all') {
                $query->where('trade_payables.status', $this->statusFilter);
            }
            
            if ($this->priorityFilter && $this->priorityFilter !== 'all') {
                // Priority column doesn't exist in trade_payables
                // Removed priority filter
            }
            
            if ($this->dateFrom) {
                $query->where('trade_payables.bill_date', '>=', $this->dateFrom);
            }
            
            if ($this->dateTo) {
                $query->where('trade_payables.bill_date', '<=', $this->dateTo);
            }
            
            $payables = $query->orderBy('trade_payables.due_date', 'asc')
                ->paginate(10);
        } else {
            $payables = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10
            );
        }
        
        return view('livewire.accounting.trade-and-other-payables', [
            'payables' => $payables,
            'accountsPayable' => $payables,  // For compatibility with existing view
            'expenseAccounts' => $expenseAccounts,
            'parentAccounts' => $parentAccounts
        ]);
    }
    
    /**
     * Load notifications for display
     */
    public function loadNotifications()
    {
        try {
            $notificationService = new PaymentNotificationService();
            
            // Get filters based on current view
            $filters = [];
            if ($this->dateFrom) {
                $filters["date_from"] = $this->dateFrom;
            }
            if ($this->dateTo) {
                $filters["date_to"] = $this->dateTo . " 23:59:59";
            }
            
            // Get notifications as array
            $this->notifications = $notificationService->getNotifications($filters);
            
            // Get statistics
            $this->notificationStats = $notificationService->getStatistics();
            
        } catch (\Exception $e) {
            Log::error("Failed to load notifications: " . $e->getMessage());
            $this->notifications = [];
            $this->notificationStats = [
                "total_unread" => 0,
                "urgent_unread" => 0,
                "overdue_count" => 0,
                "upcoming_count" => 0,
            ];
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId)
    {
        try {
            $notificationService = new PaymentNotificationService();
            $notificationService->markAsRead($notificationId);
            
            // Reload notifications
            $this->loadNotifications();
            
            session()->flash("success", "Notification marked as read");
        } catch (\Exception $e) {
            Log::error("Failed to mark notification as read: " . $e->getMessage());
            session()->flash("error", "Failed to mark notification as read");
        }
    }
    
    /**
     * Manually trigger payment notifications
     */
    public function triggerPaymentNotifications()
    {
        try {
            $notificationService = new PaymentNotificationService();
            $result = $notificationService->processDailyNotifications();
            
            if ($result["status"] === "success") {
                session()->flash("success", "Payment notifications sent successfully");
            } else {
                session()->flash("error", "Failed to send notifications: " . $result["message"]);
            }
            
            // Reload notifications
            $this->loadNotifications();
            
        } catch (\Exception $e) {
            Log::error("Failed to trigger payment notifications: " . $e->getMessage());
            session()->flash("error", "Failed to trigger payment notifications");
        }
    }
    
    /**
     * Get available banks from FSP providers configuration
     * Returns array of banks with their details
     */
    public function getAvailableBanks()
    {
        $banks = config('fsp_providers.banks', []);
        $availableBanks = [];
        
        foreach ($banks as $code => $bank) {
            if ($bank['active'] ?? false) {
                $availableBanks[] = [
                    'code' => $code,
                    'name' => $bank['name'],
                    'swift_code' => $bank['code'] ?? '',
                    'fsp_id' => $bank['fsp_id'] ?? '',
                    'is_nbc' => $bank['is_self'] ?? false
                ];
            }
        }
        
        // Sort by name
        usort($availableBanks, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        return $availableBanks;
    }
    
    /**
     * Update vendor bank details when bank is selected
     */
    public function updatedVendorBankName($value)
    {
        // When a bank is selected, we can auto-populate the SWIFT code if available
        $banks = $this->getAvailableBanks();
        foreach ($banks as $bank) {
            if ($bank['name'] === $value) {
                $this->vendor_swift_code = $bank['swift_code'];
                break;
            }
        }
    }
}
