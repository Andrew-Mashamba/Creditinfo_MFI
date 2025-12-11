<?php

namespace App\Http\Livewire\CashManagement;

use Livewire\Component;
use App\Models\Till;
use App\Models\Teller;
use App\Models\CashMovement;
use App\Models\Vault;
use App\Models\User;
use App\Models\Account;
use App\Models\Approval;
use App\Models\BranchesModel;
use App\Services\TransactionPostingService;
use App\Services\CashManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashManagementEnhanced extends Component
{
    // User Context
    public $userRole;
    public $userId;
    public $branchId;
    public $branchName;
    
    // Active Tab Management
    public $activeTab = 'morning-setup';
    
    // Time-based Properties
    public $currentTime;
    public $isBusinessHours = false;
    
    // Critical Alerts
    public $criticalAlerts = [];
    
    // Teller Properties
    public $tillStatus = 'closed';
    public $selectedTillId;
    public $tillCurrentBalance = 0;
    public $tillLimit = 10000000; // 10M default
    public $tillMinimum = 1000000; // 1M default
    public $tillUtilization = 0;
    public $myTillBalance = 0;
    public $tillOpeningBalance;
    public $tillDenominations = [];
    public $availableTills = [];
    public $myTills = [];
    
    // Morning Setup
    public $morningSetup = [
        'login' => 'completed',
        'vault_access' => 'pending',
        'cash_requisition' => 'pending',
        'drawer_prep' => 'pending',
        'balance_verify' => 'pending',
        'ready' => 'pending'
    ];
    
    // Customer Transaction Properties
    public $transactionType = 'deposit';
    public $memberNumber;
    public $accountNumber;
    public $transactionAmount;
    public $transactionReference;
    public $transactionNarration;
    public $memberDetails = null;
    public $suggestedDenominations = [];
    public $currentQueueNumber = 100;
    public $queueCount = 0;
    public $avgWaitTime = 3;
    public $recentTransactions = [];
    
    // Cash Management (Buy/Sell)
    public $vaultBuyAmount;
    public $vaultBuyReason;
    public $buyDenominations = [];
    public $vaultSellAmount;
    public $sellDenominations = [];
    
    // End of Day Properties
    public $eodSteps = [
        'last_customer' => false,
        'run_tape' => false,
        'count_cash' => false,
        'system_balance' => false,
        'print_report' => false,
        'investigate' => false,
        'bundle_cash' => false,
        'vault_deposit' => false,
        'signoff' => false
    ];
    public $eodDenominations = [];
    public $eodCoins = [];
    public $eodPhysicalCount = 0;
    public $systemBalance = 0;
    public $eodVariance = 0;
    public $eodVarianceExplanation;
    public $eodConfirmed = false;
    
    // Daily Statistics
    public $todayTransactionCount = 0;
    public $customersServed = 0;
    public $todayDeposits = 0;
    public $todayWithdrawals = 0;
    public $avgServiceTime = 4;
    public $accuracyRate = 99.8;
    public $pendingTellerRequests = 0;
    
    // Head Teller Properties
    public $vaultBalance = 0;
    public $vaultStatus = 'closed';
    public $activeTellerCount = 0;
    public $pendingApprovals = 0;
    public $lastAudit = 'Today';
    
    // Vault Custodian Properties
    public $vaultInventory = [];
    public $vaultMovements = [];
    public $dualControlPartner;
    public $vaultOpeningTime;
    public $vaultClosingTime;
    
    // Branch Manager Properties
    public $branchCashPosition = 0;
    public $branchUtilization = 0;
    public $branchPerformance = [];
    
    // Denomination Management
    public $denominations = [
        '50000' => 0,
        '20000' => 0,
        '10000' => 0,
        '5000' => 0,
        '2000' => 0,
        '1000' => 0
    ];
    
    public $coins = [
        '500' => 0,
        '200' => 0,
        '100' => 0,
        '50' => 0
    ];
    
    // Service Injection
    protected $transactionService;
    protected $cashManagementService;
    
    public function boot(
        TransactionPostingService $transactionService,
        CashManagementService $cashManagementService
    ) {
        $this->transactionService = $transactionService;
        $this->cashManagementService = $cashManagementService;
    }
    
    public function mount()
    {
        $this->initializeUserContext();
        $this->loadInitialData();
        $this->checkCriticalAlerts();
        $this->setDefaultTab();
    }
    
    protected function initializeUserContext()
    {
        $user = Auth::user();
        $this->userId = $user->id;
        
        // Determine user role based on permissions or role table
        $this->userRole = $this->determineUserRole($user);
        
        // Get branch information
        $this->branchId = $user->branch_id ?? session('branch_id');
        $branch = BranchesModel::find($this->branchId);
        $this->branchName = $branch->name ?? 'Main Branch';
        
        // Set current time
        $this->currentTime = now();
        $this->isBusinessHours = $this->currentTime->between('08:00', '17:00');
    }
    
    protected function determineUserRole($user)
    {
        // Check user permissions/roles to determine their cash management role
        if ($user->hasRole('teller')) {
            return 'teller';
        } elseif ($user->hasRole('head_teller')) {
            return 'head_teller';
        } elseif ($user->hasRole('vault_custodian')) {
            return 'vault_custodian';
        } elseif ($user->hasRole('branch_manager')) {
            return 'branch_manager';
        } elseif ($user->hasRole('auditor')) {
            return 'auditor';
        }
        
        return 'teller'; // Default to teller
    }
    
    protected function loadInitialData()
    {
        switch ($this->userRole) {
            case 'teller':
                $this->loadTellerData();
                break;
            case 'head_teller':
                $this->loadHeadTellerData();
                break;
            case 'vault_custodian':
                $this->loadVaultCustodianData();
                break;
            case 'branch_manager':
                $this->loadBranchManagerData();
                break;
        }
        
        $this->loadCommonData();
    }
    
    protected function loadTellerData()
    {
        // Load teller's assigned tills
        $this->myTills = Till::where('teller_id', $this->userId)
            ->where('branch_id', $this->branchId)
            ->get();
        
        // Get first till as default
        if ($this->myTills->isNotEmpty()) {
            $this->selectedTillId = $this->myTills->first()->id;
            $this->loadTillStatus();
        }
        
        // Load available tills for assignment
        $this->availableTills = Till::where('branch_id', $this->branchId)
            ->whereNull('teller_id')
            ->get();
        
        // Load today's transactions
        $this->loadTodayTransactions();
        
        // Load recent transactions for display
        $this->loadRecentTransactions();
    }
    
    protected function loadHeadTellerData()
    {
        // Load vault information
        $vault = Vault::where('branch_id', $this->branchId)->first();
        if ($vault) {
            $this->vaultBalance = $vault->balance;
            $this->vaultStatus = $vault->status;
        }
        
        // Count active tellers
        $this->activeTellerCount = Till::where('branch_id', $this->branchId)
            ->where('status', 'open')
            ->count();
        
        // Count pending approvals
        $this->pendingApprovals = Approval::where('branch_id', $this->branchId)
            ->where('status', 'pending')
            ->where('type', 'cash_management')
            ->count();
    }
    
    protected function loadVaultCustodianData()
    {
        // Load vault details
        $vault = Vault::where('branch_id', $this->branchId)->first();
        if ($vault) {
            $this->vaultBalance = $vault->balance;
            $this->vaultStatus = $vault->status;
            $this->vaultInventory = json_decode($vault->denomination_breakdown, true) ?? [];
        }
        
        // Load recent vault movements
        $this->vaultMovements = CashMovement::where('branch_id', $this->branchId)
            ->whereIn('type', ['vault_in', 'vault_out'])
            ->whereDate('created_at', today())
            ->latest()
            ->limit(10)
            ->get();
    }
    
    protected function loadBranchManagerData()
    {
        // Load branch-wide cash position
        $this->branchCashPosition = Till::where('branch_id', $this->branchId)
            ->where('status', 'open')
            ->sum('current_balance');
        
        // Add vault balance
        $vault = Vault::where('branch_id', $this->branchId)->first();
        if ($vault) {
            $this->branchCashPosition += $vault->balance;
        }
        
        // Calculate utilization
        $branchLimit = 100000000; // 100M example limit
        $this->branchUtilization = ($this->branchCashPosition / $branchLimit) * 100;
        
        // Load performance metrics
        $this->loadBranchPerformance();
    }
    
    protected function loadCommonData()
    {
        // Load denomination mix for the branch
        $this->loadDenominationMix();
        
        // Load any critical alerts
        $this->checkCriticalAlerts();
    }
    
    protected function setDefaultTab()
    {
        // Set default tab based on user role and time
        $hour = now()->hour;
        
        if ($this->userRole === 'teller') {
            if ($hour < 8) {
                $this->activeTab = 'morning-setup';
            } elseif ($hour >= 17) {
                $this->activeTab = 'end-of-day';
            } else {
                $this->activeTab = 'customer-transactions';
            }
        } elseif ($this->userRole === 'head_teller') {
            if ($hour < 8) {
                $this->activeTab = 'vault-opening';
            } else {
                $this->activeTab = 'vault-operations';
            }
        } elseif ($this->userRole === 'vault_custodian') {
            $this->activeTab = 'vault-management';
        } else {
            $this->activeTab = 'dashboard';
        }
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->emit('tabChanged', $tab);
        
        // Load specific data for the tab
        $this->loadTabData($tab);
    }
    
    protected function loadTabData($tab)
    {
        switch ($tab) {
            case 'customer-transactions':
                $this->loadRecentTransactions();
                break;
            case 'cash-management':
                $this->loadTillStatus();
                $this->calculateTillUtilization();
                break;
            case 'end-of-day':
                $this->loadEndOfDayData();
                break;
        }
    }
    
    // Teller Operations Methods
    
    public function requestTillOpening()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'tillOpeningBalance' => 'required|numeric|min:100000'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create approval request
            $approval = Approval::create([
                'type' => 'till_opening',
                'requester_id' => $this->userId,
                'branch_id' => $this->branchId,
                'amount' => $this->tillOpeningBalance,
                'reference_id' => $this->selectedTillId,
                'status' => 'pending',
                'description' => 'Till opening request',
                'metadata' => json_encode([
                    'till_id' => $this->selectedTillId,
                    'denominations' => $this->denominations
                ])
            ]);
            
            DB::commit();
            
            $this->emit('success', 'Till opening request submitted successfully');
            $this->morningSetup['vault_access'] = 'in_progress';
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->emit('error', 'Failed to submit till opening request: ' . $e->getMessage());
        }
    }
    
    public function loadTillStatus()
    {
        if (!$this->selectedTillId) {
            return;
        }
        
        $till = Till::find($this->selectedTillId);
        if ($till) {
            $this->tillStatus = $till->status;
            $this->tillCurrentBalance = $till->current_balance;
            $this->tillLimit = $till->max_limit ?? 10000000;
            $this->tillMinimum = $till->min_limit ?? 1000000;
            
            // Load denomination breakdown
            $this->tillDenominations = json_decode($till->denomination_breakdown, true) ?? [];
            
            // Calculate utilization
            $this->calculateTillUtilization();
        }
    }
    
    protected function calculateTillUtilization()
    {
        if ($this->tillLimit > 0) {
            $this->tillUtilization = ($this->tillCurrentBalance / $this->tillLimit) * 100;
        }
    }
    
    public function processTransaction()
    {
        $this->validate([
            'transactionType' => 'required|in:deposit,withdrawal',
            'transactionAmount' => 'required|numeric|min:1000',
            'transactionNarration' => 'required|string|min:5'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Process the transaction based on type
            if ($this->transactionType === 'deposit') {
                $this->processDeposit();
            } else {
                $this->processWithdrawal();
            }
            
            DB::commit();
            
            $this->emit('success', 'Transaction processed successfully');
            $this->resetTransaction();
            $this->loadRecentTransactions();
            $this->todayTransactionCount++;
            $this->customersServed++;
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->emit('error', 'Transaction failed: ' . $e->getMessage());
        }
    }
    
    protected function processDeposit()
    {
        // Update till balance
        $till = Till::find($this->selectedTillId);
        $till->current_balance += $this->transactionAmount;
        $till->save();
        
        // Record cash movement
        CashMovement::create([
            'type' => 'customer_deposit',
            'amount' => $this->transactionAmount,
            'from_type' => 'customer',
            'from_id' => $this->memberNumber,
            'to_type' => 'till',
            'to_id' => $this->selectedTillId,
            'branch_id' => $this->branchId,
            'user_id' => $this->userId,
            'reference' => $this->transactionReference,
            'description' => $this->transactionNarration,
            'status' => 'completed'
        ]);
        
        // Update today's deposits
        $this->todayDeposits += $this->transactionAmount;
        $this->tillCurrentBalance += $this->transactionAmount;
    }
    
    protected function processWithdrawal()
    {
        // Check if till has sufficient balance
        if ($this->tillCurrentBalance < $this->transactionAmount) {
            throw new \Exception('Insufficient till balance');
        }
        
        // Update till balance
        $till = Till::find($this->selectedTillId);
        $till->current_balance -= $this->transactionAmount;
        $till->save();
        
        // Record cash movement
        CashMovement::create([
            'type' => 'customer_withdrawal',
            'amount' => $this->transactionAmount,
            'from_type' => 'till',
            'from_id' => $this->selectedTillId,
            'to_type' => 'customer',
            'to_id' => $this->memberNumber,
            'branch_id' => $this->branchId,
            'user_id' => $this->userId,
            'reference' => $this->transactionReference,
            'description' => $this->transactionNarration,
            'status' => 'completed'
        ]);
        
        // Update today's withdrawals
        $this->todayWithdrawals += $this->transactionAmount;
        $this->tillCurrentBalance -= $this->transactionAmount;
    }
    
    public function resetTransaction()
    {
        $this->transactionAmount = null;
        $this->transactionReference = null;
        $this->transactionNarration = null;
        $this->memberNumber = null;
        $this->accountNumber = null;
        $this->memberDetails = null;
    }
    
    public function fetchMemberDetails()
    {
        if (!$this->memberNumber) {
            return;
        }
        
        // Simulate fetching member details
        $this->memberDetails = [
            'name' => 'John Doe',
            'id_type' => 'National ID',
            'balance' => 1500000
        ];
    }
    
    public function callNextCustomer()
    {
        $this->currentQueueNumber++;
        $this->emit('customerCalled', $this->currentQueueNumber);
    }
    
    // Cash Management Methods
    
    public function buyCashFromVault()
    {
        $this->validate([
            'vaultBuyAmount' => 'required|numeric|min:100000',
            'vaultBuyReason' => 'required|string|min:10'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create approval request for vault buy
            $approval = Approval::create([
                'type' => 'vault_buy',
                'requester_id' => $this->userId,
                'branch_id' => $this->branchId,
                'amount' => $this->vaultBuyAmount,
                'reference_id' => $this->selectedTillId,
                'status' => 'pending',
                'description' => $this->vaultBuyReason,
                'metadata' => json_encode([
                    'till_id' => $this->selectedTillId,
                    'denominations' => $this->buyDenominations
                ])
            ]);
            
            DB::commit();
            
            $this->emit('success', 'Cash buy request submitted for approval');
            $this->resetCashManagementForms();
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->emit('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }
    
    public function sellCashToVault()
    {
        $this->validate([
            'vaultSellAmount' => 'required|numeric|min:100000'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create cash movement record
            CashMovement::create([
                'type' => 'till_to_vault',
                'amount' => $this->vaultSellAmount,
                'from_type' => 'till',
                'from_id' => $this->selectedTillId,
                'to_type' => 'vault',
                'to_id' => Vault::where('branch_id', $this->branchId)->first()->id,
                'branch_id' => $this->branchId,
                'user_id' => $this->userId,
                'description' => 'Excess cash transfer to vault',
                'status' => 'pending_approval',
                'metadata' => json_encode([
                    'denominations' => $this->sellDenominations
                ])
            ]);
            
            DB::commit();
            
            $this->emit('success', 'Cash transfer to vault initiated');
            $this->resetCashManagementForms();
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->emit('error', 'Failed to transfer cash: ' . $e->getMessage());
        }
    }
    
    protected function resetCashManagementForms()
    {
        $this->vaultBuyAmount = null;
        $this->vaultBuyReason = null;
        $this->buyDenominations = [];
        $this->vaultSellAmount = null;
        $this->sellDenominations = [];
    }
    
    // End of Day Methods
    
    protected function loadEndOfDayData()
    {
        // Load today's transaction summary
        $this->loadTodayTransactions();
        
        // Get system balance
        $till = Till::find($this->selectedTillId);
        if ($till) {
            $this->systemBalance = $till->current_balance;
        }
    }
    
    public function calculateEodTotal()
    {
        $total = 0;
        
        // Calculate notes total
        foreach ($this->eodDenominations as $denom => $count) {
            $total += $denom * ($count ?? 0);
        }
        
        // Calculate coins total
        foreach ($this->eodCoins as $coin => $count) {
            $total += $coin * ($count ?? 0);
        }
        
        $this->eodPhysicalCount = $total;
        $this->eodVariance = $this->eodPhysicalCount - $this->systemBalance;
    }
    
    public function submitEndOfDay()
    {
        $this->validate([
            'eodConfirmed' => 'accepted'
        ]);
        
        if ($this->eodVariance != 0 && empty($this->eodVarianceExplanation)) {
            $this->addError('eodVarianceExplanation', 'Variance explanation is required');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Record EOD submission
            $eodRecord = [
                'till_id' => $this->selectedTillId,
                'user_id' => $this->userId,
                'branch_id' => $this->branchId,
                'system_balance' => $this->systemBalance,
                'physical_count' => $this->eodPhysicalCount,
                'variance' => $this->eodVariance,
                'variance_explanation' => $this->eodVarianceExplanation,
                'denomination_breakdown' => json_encode([
                    'notes' => $this->eodDenominations,
                    'coins' => $this->eodCoins
                ]),
                'status' => 'submitted',
                'submitted_at' => now()
            ];
            
            // Save EOD record (assuming EOD model exists)
            // EndOfDay::create($eodRecord);
            
            // Update till status to closed
            $till = Till::find($this->selectedTillId);
            $till->status = 'closed';
            $till->save();
            
            DB::commit();
            
            $this->emit('success', 'End of day submitted successfully');
            $this->tillStatus = 'closed';
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->emit('error', 'Failed to submit end of day: ' . $e->getMessage());
        }
    }
    
    // Helper Methods
    
    protected function loadTodayTransactions()
    {
        $today = today();
        
        // Count transactions
        $this->todayTransactionCount = CashMovement::where('user_id', $this->userId)
            ->whereDate('created_at', $today)
            ->count();
        
        // Sum deposits
        $this->todayDeposits = CashMovement::where('user_id', $this->userId)
            ->where('type', 'customer_deposit')
            ->whereDate('created_at', $today)
            ->sum('amount');
        
        // Sum withdrawals
        $this->todayWithdrawals = CashMovement::where('user_id', $this->userId)
            ->where('type', 'customer_withdrawal')
            ->whereDate('created_at', $today)
            ->sum('amount');
    }
    
    protected function loadRecentTransactions()
    {
        $this->recentTransactions = CashMovement::where('user_id', $this->userId)
            ->whereIn('type', ['customer_deposit', 'customer_withdrawal'])
            ->whereDate('created_at', today())
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($movement) {
                return (object) [
                    'created_at' => $movement->created_at,
                    'type' => str_replace('customer_', '', $movement->type),
                    'customer_name' => 'Customer ' . substr($movement->to_id ?? $movement->from_id, -4),
                    'amount' => $movement->amount
                ];
            });
    }
    
    protected function loadDenominationMix()
    {
        // Initialize default denomination mix
        $this->tillDenominations = [
            '50000' => 10,
            '20000' => 20,
            '10000' => 30,
            '5000' => 40,
            '2000' => 50,
            '1000' => 100
        ];
    }
    
    protected function loadBranchPerformance()
    {
        // Load branch performance metrics
        $this->branchPerformance = [
            'tellers_active' => $this->activeTellerCount,
            'transactions_today' => CashMovement::where('branch_id', $this->branchId)
                ->whereDate('created_at', today())
                ->count(),
            'cash_in_hand' => $this->branchCashPosition,
            'utilization' => $this->branchUtilization
        ];
    }
    
    protected function checkCriticalAlerts()
    {
        $alerts = [];
        
        // Check for low vault balance
        if ($this->vaultBalance < 5000000) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Vault Balance',
                'message' => 'Vault balance is below minimum threshold',
                'action' => 'requestVaultReplenishment',
                'actionLabel' => 'Request Replenishment'
            ];
        }
        
        // Check for till over limit
        if ($this->tillCurrentBalance > $this->tillLimit) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Till Over Limit',
                'message' => 'Your till balance exceeds the maximum limit',
                'action' => 'sellCashToVault',
                'actionLabel' => 'Transfer to Vault'
            ];
        }
        
        // Check for pending approvals (for supervisors)
        if ($this->userRole === 'head_teller' && $this->pendingApprovals > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Approvals',
                'message' => "You have {$this->pendingApprovals} pending approval requests",
                'action' => 'viewPendingApprovals',
                'actionLabel' => 'View Approvals'
            ];
        }
        
        $this->criticalAlerts = $alerts;
    }
    
    public function refreshData()
    {
        $this->loadInitialData();
        $this->emit('dataRefreshed');
    }
    
    public function refreshStats()
    {
        $this->loadTodayTransactions();
        $this->checkCriticalAlerts();
    }
    
    public function extendSession()
    {
        session()->regenerate();
        $this->emit('sessionExtended');
    }
    
    public function getTabTitle()
    {
        $titles = [
            'morning-setup' => 'Morning Setup',
            'customer-transactions' => 'Customer Transactions',
            'cash-management' => 'Cash Management',
            'till-transfers' => 'Till Transfers',
            'balance-inquiry' => 'Balance & Status',
            'denomination-management' => 'Denomination Management',
            'end-of-day' => 'End of Day Process',
            'transaction-history' => 'Transaction History',
            'vault-opening' => 'Vault Opening',
            'vault-operations' => 'Vault Operations',
            'vault-management' => 'Vault Management',
            'dashboard' => 'Dashboard'
        ];
        
        return $titles[$this->activeTab] ?? 'Cash Management';
    }
    
    public function getTabDescription()
    {
        $descriptions = [
            'morning-setup' => 'Complete morning procedures and prepare for operations',
            'customer-transactions' => 'Process customer deposits and withdrawals',
            'cash-management' => 'Manage till balance and vault transfers',
            'till-transfers' => 'Transfer cash between tills',
            'balance-inquiry' => 'Check current balance and till status',
            'denomination-management' => 'Manage cash denominations',
            'end-of-day' => 'Complete end of day reconciliation',
            'transaction-history' => 'View your transaction history',
            'vault-opening' => 'Open vault with dual control',
            'vault-operations' => 'Manage vault operations',
            'vault-management' => 'Vault inventory and security',
            'dashboard' => 'Overview and analytics'
        ];
        
        return $descriptions[$this->activeTab] ?? 'Manage cash operations';
    }
    
    public function render()
    {
        return view('livewire.cash-management.cash-management-enhanced');
    }
}