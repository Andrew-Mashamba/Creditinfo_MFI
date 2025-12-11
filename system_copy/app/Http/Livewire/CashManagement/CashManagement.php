<?php

namespace App\Http\Livewire\CashManagement;

use Livewire\Component;
use App\Models\Till;
use App\Models\Teller;
use App\Models\CashMovement;
use App\Models\StrongroomLedger;
use App\Models\CashInTransitProvider;
use App\Models\Approval;
use App\Models\User;
use App\Models\Account;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Vault;
use App\Models\BranchesModel;

class CashManagement extends Component
{
    // Active tab management
    public $activeTab = 'vault';
    
    // User role context
    public $userRole;
    public $isVaultCustodian = false;
    public $isTeller = false;
    public $isHQ = false;
    
    // Vault Management - Updated for branch-specific vault
    public $vault = null;
    public $vaultBalance = 0;
    public $vaultLimit = 0;
    public $vaultAccount;
    public $vaultTransferAmount;
    public $vaultTransferType = 'to_till'; // to_till, to_bank, from_hq
    public $vaultReference;
    public $vaultDescription;
    public $selectedTillForVaultTransfer;
    
    // Vault Replenishment Request to HQ
    public $replenishmentAmount;
    public $replenishmentReason;
    public $replenishmentUrgency = 'normal'; // normal, urgent, emergency
    public $replenishmentNotes;
    
    // Till Operations
    public $selectedTillId;
    public $tillStatus = 'closed';
    public $tillCurrentBalance = 0;
    public $tillAccount;
    public $tillOpeningBalance;
    public $tillReplenishAmount;
    public $tillReplenishReason;
    public $replenishmentRequestId;
    
    // End of Day Operations
    public $eodTillId;
    public $eodCountedAmount;
    public $eodVarianceExplanation;
    public $eodPhysicalCashConfirmed = false;
    
    // Till Assignment (Supervisor/Manager function)
    public $assignTillId;
    public $assignTellerId;
    public $assignmentNotes;
    
    // CIT Management
    public $citProvider;
    public $citAmount;
    public $citFromLocation;
    public $citToLocation;
    public $citScheduledDate;
    public $citScheduledTime;
    public $citReference;
    public $citDescription;
    public $citProviders = [];
    public $citType = 'vault_to_bank'; // vault_to_bank, bank_to_vault
    
    // Legacy properties (for compatibility)
    public $selectedTeller;
    public $tellerFloatAmount;
    public $tellerFloatDescription;
    public $pettyCashBalance = 0;
    public $pettyCashAmount;
    public $pettyCashDescription;
    public $pettyCashType = 'advance';
    
    // Transaction properties
    public $transactionType = 'deposit';
    public $memberNumber;
    public $accountNumber;
    public $transactionReference;
    public $transactionNarration;
    
    // Statistics properties for Teller Operations
    public $todayTransactionCount = 0;
    public $pendingTellerRequests = 0;
    
    // Report properties
    public $reportType;
    public $reportDateFrom;
    public $reportDateTo;
    public $reportStatus;
    
    // Data collections
    public $availableTellers = [];
    public $availableTills = [];
    public $unassignedTills = [];
    public $myTills = [];
    public $recentMovements = [];
    public $pendingApprovals = [];
    public $tillSummary = [];
    public $pendingReplenishments = [];
    public $vaultAlerts = [];

    protected $rules = [
        // Vault Operations
        'vaultTransferAmount' => 'required|numeric|min:1',
        'selectedTillForVaultTransfer' => 'required|exists:tills,id',
        'vaultReference' => 'required|string|max:100',
        'vaultDescription' => 'required|string|max:255',
        
        // Vault Replenishment
        'replenishmentAmount' => 'required|numeric|min:1',
        'replenishmentReason' => 'required|string|max:255',
        'replenishmentNotes' => 'nullable|string|max:500',
        
        // Till Operations
        'tillOpeningBalance' => 'required|numeric|min:0',
        'tillReplenishAmount' => 'required|numeric|min:1',
        'tillReplenishReason' => 'required|string|max:255',
        
        // Till Assignment
        'assignTillId' => 'required|exists:tills,id',
        'assignTellerId' => 'required|exists:users,id',
        'assignmentNotes' => 'nullable|string|max:255',
        
        // End of Day
        'eodCountedAmount' => 'required|numeric|min:0',
        'eodVarianceExplanation' => 'nullable|string|max:500',
        
        // CIT Operations
        'citAmount' => 'required|numeric|min:1',
        'citProvider' => 'required|exists:cash_in_transit_providers,id',
        'citFromLocation' => 'required|string|max:100',
        'citToLocation' => 'required|string|max:100',
        'citScheduledDate' => 'required|date|after_or_equal:today',
        'citScheduledTime' => 'required',
        'citDescription' => 'required|string|max:255',
        
        // Legacy properties (for compatibility)
        'selectedTeller' => 'nullable|exists:users,id',
        'tellerFloatAmount' => 'nullable|numeric|min:1',
        'tellerFloatDescription' => 'nullable|string|max:255',
        'pettyCashAmount' => 'nullable|numeric|min:1',
        'pettyCashDescription' => 'nullable|string|max:255',
        
        // Transaction properties
        'transactionType' => 'required|in:deposit,withdrawal',
        'memberNumber' => 'nullable|string|max:50',
        'accountNumber' => 'nullable|string|max:50',
        'transactionReference' => 'nullable|string|max:100',
        'transactionNarration' => 'nullable|string|max:255',
        
        // Report properties
        'reportType' => 'nullable|string',
        'reportDateFrom' => 'nullable|date',
        'reportDateTo' => 'nullable|date|after_or_equal:reportDateFrom',
        'reportStatus' => 'nullable|string',
    ];

    public function mount()
    {
        $this->determineUserRole();
        $this->loadInitialData();
    }

    public function determineUserRole()
    {
        $user = Auth::user();
        
        // TODO: Implement proper role checking based on your system
        // For now, using simple role determination
        $this->userRole = $user->role ?? 'teller';
        
        $this->isVaultCustodian = in_array($this->userRole, ['vault_custodian', 'manager', 'supervisor']);
        $this->isTeller = in_array($this->userRole, ['teller', 'cashier']);
        $this->isHQ = in_array($this->userRole, ['hq_manager', 'hq_operator']);
        
        // Set default tab based on role
        if ($this->isTeller) {
            $this->activeTab = 'teller';
        } elseif ($this->isVaultCustodian) {
            $this->activeTab = 'vault';
        }
    }

    public function loadInitialData()
    {
        // Load vault balance and account
        $this->loadVaultData();
        
        // Load all users
        $this->availableTellers = User::get();
        
        // Load tills data
        $this->loadTillsData();
        
        // Load CIT providers
        $this->citProviders = CashInTransitProvider::where('status', 'ACTIVE')->get();
        
        // Load recent movements
        $this->loadRecentMovements();
        
        // Load pending approvals
        $this->loadPendingApprovals();
        
        // Load pending replenishments
        $this->loadPendingReplenishments();
        
        // Load till summary
        $this->loadTillSummary();
        
        // Check vault alerts
        $this->checkVaultAlerts();
    }

    public function loadVaultData()
    {
        $userBranch = Auth::user()->branch ?? 1;
        
        $this->vault = Vault::where('branch_id', $userBranch)->first();
        
        if ($this->vault) {
            // Get vault account to check actual balance
            $this->vaultAccount = Account::where('account_number', $this->vault->account_number)->first();
            $this->vaultBalance = $this->vaultAccount ? $this->vaultAccount->balance : 0;
            $this->vaultLimit = $this->vault->limit;
        } else {
            $this->vaultBalance = 0;
            $this->vaultLimit = 0;
        }
    }

    public function loadTillsData()
    {
        $userBranch = Auth::user()->branch ?? 1;
        
        // All tills in the branch
        $this->availableTills = Till::where('branch_id', $userBranch)->get();
        
        // Unassigned tills
        $this->unassignedTills = Till::where('branch_id', $userBranch)
            ->whereNull('assigned_user_id')
            ->get();
            
        // Current user's assigned tills (for tellers)
        $this->myTills = Till::where('branch_id', $userBranch)
            ->where('assigned_user_id', Auth::id())
            ->get();
    }

    public function loadTillSummary()
    {
        $userBranch = Auth::user()->branch ?? 1;
        
        $tills = Till::where('branch_id', $userBranch)->with(['assignedUser'])->get();
        
        $this->tillSummary = $tills->map(function($till) {
            // Get till account balance
            $tillAccount = Account::where('account_number', $till->till_account_number)->first();
            $currentBalance = $tillAccount ? $tillAccount->balance : 0;
            
            return [
                'id' => $till->id,
                'name' => $till->name,
                'assigned_user' => $till->assignedUser->name ?? 'Unassigned',
                'status' => $till->status,
                'current_balance' => $currentBalance,
                'opening_balance' => $till->opening_balance ?? 0,
                'account_number' => $till->till_account_number,
            ];
        });
    }

    public function loadPendingApprovals()
    {
        $query = Approval::whereIn('process_code', [
            'VAULT_TO_TILL', 'TILL_REPLENISHMENT', 'VAULT_REPLENISHMENT', 
            'VAULT_TO_BANK', 'CIT_TRANSFER'
        ])->where('approval_status', 'PENDING');
        
        // Filter based on user role
        if (!$this->isHQ) {
            $query->where('user_id', '!=', null); // Branch level approvals
        }
        
        $this->pendingApprovals = $query->with('user')->latest()->take(10)->get();
    }

    public function loadPendingReplenishments()
    {
        if ($this->isVaultCustodian) {
            $this->pendingReplenishments = Approval::where('process_code', 'TILL_REPLENISHMENT')
                ->where('approval_status', 'PENDING')
                ->with('user')
                ->latest()
                ->get();
        }
    }

    public function loadRecentMovements()
    {
        $this->recentMovements = CashMovement::with(['fromTill', 'toTill', 'initiator'])
            ->latest()
            ->take(15)
            ->get();
    }

    public function checkVaultAlerts()
    {
        if (!$this->vault) return;
        
        $this->vaultAlerts = [];
        
        // Check if vault is over limit
        if ($this->vaultBalance > $this->vaultLimit) {
            $excess = $this->vaultBalance - $this->vaultLimit;
            $this->vaultAlerts[] = [
                'type' => 'over_limit',
                'message' => "Vault over limit by TZS " . number_format($excess) . ". Schedule CIT to bank.",
                'severity' => 'critical'
            ];
        }
        
        // Check if vault is at warning threshold
        $warningThreshold = $this->vaultLimit * 0.8;
        if ($this->vaultBalance > $warningThreshold && $this->vaultBalance <= $this->vaultLimit) {
            $this->vaultAlerts[] = [
                'type' => 'warning',
                'message' => "Vault at " . number_format(($this->vaultBalance / $this->vaultLimit) * 100, 1) . "% capacity.",
                'severity' => 'warning'
            ];
        }
        
        // Check if vault is low
        $lowThreshold = $this->vaultLimit * 0.2;
        if ($this->vaultBalance < $lowThreshold) {
            $this->vaultAlerts[] = [
                'type' => 'low_balance',
                'message' => "Vault balance low. Consider replenishment request.",
                'severity' => 'info'
            ];
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetValidation();
    }

    // =================================================
    // VAULT CUSTODIAN OPERATIONS
    // =================================================
    
    public function initiateVaultToTillTransfer()
    {
        $this->validate([
            'vaultTransferAmount' => 'required|numeric|min:1',
            'selectedTillForVaultTransfer' => 'required|exists:tills,id',
            'vaultReference' => 'required|string|max:100',
            'vaultDescription' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->selectedTillForVaultTransfer);
            
            // Check vault balance
            if ($this->vaultBalance < $this->vaultTransferAmount) {
                session()->flash('error', 'Insufficient vault balance');
                return;
            }

            // Create approval request with transaction data
            $transactionData = [
                'first_account' => $this->vaultAccount->account_number,
                'second_account' => $till->till_account_number,
                'amount' => $this->vaultTransferAmount,
                'narration' => $this->vaultDescription,
                'action' => 'till_funding'
            ];

            $approval = Approval::create([
                'process_name' => 'Vault to Till Transfer',
                'process_description' => "Transfer TZS " . number_format($this->vaultTransferAmount) . " from vault to {$till->name}",
                'approval_process_description' => 'Supervisor approval required for vault to till transfer',
                'process_code' => 'VAULT_TO_TILL',
                'process_id' => null,
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($transactionData)
            ]);

            // Log the movement request
            CashMovement::create([
                'reference' => $this->vaultReference,
                'type' => 'vault_to_till',
                'amount' => $this->vaultTransferAmount,
                'reason' => $this->vaultDescription,
                'status' => 'pending_approval',
                'user_id' => Auth::id(),
                'initiated_by' => Auth::id(),
                'notes' => "Approval ID: {$approval->id}, Till: {$till->name}"
            ]);

            DB::commit();

            $this->reset(['vaultTransferAmount', 'selectedTillForVaultTransfer', 'vaultReference', 'vaultDescription']);
            session()->flash('success', 'Vault to till transfer submitted for approval');
            $this->loadPendingApprovals();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to initiate transfer: ' . $e->getMessage());
        }
    }

    public function requestVaultReplenishment()
    {
        $this->validate([
            'replenishmentAmount' => 'required|numeric|min:1',
            'replenishmentReason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Create HQ replenishment request
            $requestData = [
                'amount' => $this->replenishmentAmount,
                'reason' => $this->replenishmentReason,
                'urgency' => $this->replenishmentUrgency,
                'notes' => $this->replenishmentNotes,
                'vault_account' => $this->vaultAccount->account_number,
                'current_balance' => $this->vaultBalance,
                'vault_limit' => $this->vaultLimit,
                'branch_id' => Auth::user()->branch,
                'requested_by' => Auth::user()->name,
                'timestamp' => now()->toISOString()
            ];

            $approval = Approval::create([
                'process_name' => 'Vault Replenishment Request',
                'process_description' => "HQ replenishment request of TZS " . number_format($this->replenishmentAmount),
                'approval_process_description' => 'HQ approval required for vault replenishment',
                'process_code' => 'VAULT_REPLENISHMENT',
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($requestData)
            ]);

            DB::commit();

            $this->reset(['replenishmentAmount', 'replenishmentReason', 'replenishmentNotes', 'replenishmentUrgency']);
            session()->flash('success', 'Vault replenishment request submitted to HQ');
            $this->loadPendingApprovals();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to submit replenishment request: ' . $e->getMessage());
        }
    }

    public function approveReplenishmentRequest($approvalId)
    {
        try {
            DB::beginTransaction();

            $approval = Approval::findOrFail($approvalId);
            $requestData = json_decode($approval->edit_package, true);
            
            // Post the transaction using TransactionPostingService
            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $this->vaultAccount->account_number,
                'second_account' => $requestData['till_account'],
                'amount' => $requestData['amount'],
                'narration' => "Till replenishment: " . $requestData['reason'],
                'action' => 'till_replenishment'
            ];
            
            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['success']) {
                // Update approval status
                $approval->update([
                    'approval_status' => 'APPROVED',
                    'approver_id' => Auth::id(),
                    'approved_at' => now()
                ]);
                
                // Update till opening balance if it's a new day opening
                if (isset($requestData['is_opening_balance']) && $requestData['is_opening_balance']) {
                    $till = Till::where('till_account_number', $requestData['till_account'])->first();
                    if ($till) {
                        $till->update([
                            'opening_balance' => $requestData['amount'],
                            'status' => 'open',
                            'opened_at' => now(),
                            'opened_by' => $requestData['requested_by_id']
                        ]);
                    }
                }

                session()->flash('success', 'Replenishment request approved and funds transferred');
            } else {
                session()->flash('error', 'Transaction posting failed: ' . $result['message']);
            }

            DB::commit();
            $this->loadPendingReplenishments();
            $this->loadVaultData();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    // =================================================
    // TELLER OPERATIONS
    // =================================================
    
    public function selectMyTill($tillId)
    {
        $this->selectedTillId = $tillId;
        $this->loadTillStatus();
    }

    public function loadTillStatus()
    {
        if ($this->selectedTillId) {
            $till = Till::find($this->selectedTillId);
            if ($till) {
                $this->tillStatus = $till->status;
                
                // Get current balance from account
                $tillAccount = Account::where('account_number', $till->till_account_number)->first();
                $this->tillCurrentBalance = $tillAccount ? $tillAccount->balance : 0;
                $this->tillAccount = $tillAccount;
            }
        }
    }

    public function requestTillOpening()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'tillOpeningBalance' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->selectedTillId);

            // Check if till is assigned to current user
            if ($till->assigned_user_id !== Auth::id()) {
                session()->flash('error', 'You can only open tills assigned to you');
                return;
            }

            // Create request for vault custodian to transfer opening balance
            $requestData = [
                'till_id' => $this->selectedTillId,
                'till_account' => $till->till_account_number,
                'amount' => $this->tillOpeningBalance,
                'reason' => "Opening balance for {$till->name}",
                'is_opening_balance' => true,
                'requested_by_id' => Auth::id()
            ];

            $approval = Approval::create([
                'process_name' => 'Till Opening Request',
                'process_description' => "Opening balance request for {$till->name}: TZS " . number_format($this->tillOpeningBalance),
                'approval_process_description' => 'Vault custodian approval required for till opening',
                'process_code' => 'TILL_REPLENISHMENT',
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($requestData)
            ]);

            DB::commit();

            $this->reset(['tillOpeningBalance']);
            session()->flash('success', 'Till opening request submitted to vault custodian');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to request till opening: ' . $e->getMessage());
        }
    }

    public function requestTillReplenishment()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'tillReplenishAmount' => 'required|numeric|min:1',
            'tillReplenishReason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->selectedTillId);

            $requestData = [
                'till_id' => $this->selectedTillId,
                'till_account' => $till->till_account_number,
                'amount' => $this->tillReplenishAmount,
                'reason' => $this->tillReplenishReason,
                'current_balance' => $this->tillCurrentBalance,
                'requested_by_id' => Auth::id()
            ];

            $approval = Approval::create([
                'process_name' => 'Till Replenishment Request',
                'process_description' => "Replenishment for {$till->name}: TZS " . number_format($this->tillReplenishAmount),
                'approval_process_description' => 'Vault custodian approval required',
                'process_code' => 'TILL_REPLENISHMENT',
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($requestData)
            ]);

            DB::commit();

            $this->reset(['tillReplenishAmount', 'tillReplenishReason']);
            session()->flash('success', 'Till replenishment request submitted');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to request replenishment: ' . $e->getMessage());
        }
    }

    public function initiateTillClosure()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'eodCountedAmount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->selectedTillId);

            // Check if till is assigned to current user
            if ($till->assigned_user_id !== Auth::id()) {
                session()->flash('error', 'You can only close tills assigned to you');
                return;
            }

            // Calculate variance
            $systemBalance = $this->tillCurrentBalance;
            $countedBalance = $this->eodCountedAmount;
            $variance = $countedBalance - $systemBalance;

            // Create closure request for vault custodian
            $closureData = [
                'till_id' => $this->selectedTillId,
                'till_account' => $till->till_account_number,
                'system_balance' => $systemBalance,
                'counted_balance' => $countedBalance,
                'variance' => $variance,
                'variance_explanation' => $this->eodVarianceExplanation,
                'requested_by_id' => Auth::id()
            ];

            $approval = Approval::create([
                'process_name' => 'Till Closure Request',
                'process_description' => "End of day closure for {$till->name}. Variance: TZS " . number_format($variance, 2),
                'approval_process_description' => 'Vault custodian physical confirmation required',
                'process_code' => 'TILL_CLOSURE',
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($closureData)
            ]);

            // Update till status to closing_requested
            $till->update([
                'status' => 'closing_requested',
                'closing_balance' => $countedBalance,
                'variance' => $variance,
                'variance_explanation' => $this->eodVarianceExplanation
            ]);

            DB::commit();

            $this->reset(['eodCountedAmount', 'eodVarianceExplanation']);
            session()->flash('success', 'Till closure request submitted. Waiting for vault custodian confirmation.');
            $this->loadTillStatus();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to initiate closure: ' . $e->getMessage());
        }
    }

    // =================================================
    // SUPERVISOR/MANAGER OPERATIONS
    // =================================================
    
    public function assignTillToTeller()
    {
        $this->validate([
            'assignTillId' => 'required|exists:tills,id',
            'assignTellerId' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->assignTillId);
            $teller = User::findOrFail($this->assignTellerId);

            // Check if till is already assigned
            if ($till->assigned_user_id && $till->assigned_user_id !== $this->assignTellerId) {
                session()->flash('error', 'Till is already assigned to another teller');
                return;
            }

            // Update till assignment
            $till->update([
                'assigned_user_id' => $this->assignTellerId,
                'assigned_at' => now(),
                'assignment_notes' => $this->assignmentNotes
            ]);

            // Log the assignment
            CashMovement::create([
                'reference' => 'ASSIGN-' . now()->format('YmdHis') . '-' . $till->id,
                'type' => 'till_to_till',
                'amount' => 0,
                'reason' => "Till {$till->name} assigned to {$teller->name}",
                'user_id' => Auth::id(),
                'initiated_by' => Auth::id(),
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => $this->assignmentNotes ?? 'Till assignment'
            ]);

            DB::commit();

            $this->reset(['assignTillId', 'assignTellerId', 'assignmentNotes']);
            $this->loadTillsData();
            $this->loadTillSummary();
            
            session()->flash('success', "Till {$till->name} successfully assigned to {$teller->name}");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to assign till: ' . $e->getMessage());
        }
    }

    // =================================================
    // CIT OPERATIONS
    // =================================================
    
    public function scheduleCIT()
    {
        $this->validate([
            'citProvider' => 'required|exists:cash_in_transit_providers,id',
            'citAmount' => 'required|numeric|min:1',
            'citFromLocation' => 'required|string|max:100',
            'citToLocation' => 'required|string|max:100',
            'citScheduledDate' => 'required|date|after_or_equal:today',
            'citScheduledTime' => 'required',
            'citDescription' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Create CIT movement approval request
            $citData = [
                'provider_id' => $this->citProvider,
                'amount' => $this->citAmount,
                'from_location' => $this->citFromLocation,
                'to_location' => $this->citToLocation,
                'scheduled_date' => $this->citScheduledDate,
                'scheduled_time' => $this->citScheduledTime,
                'description' => $this->citDescription,
                'reference' => $this->citReference,
                'type' => $this->citType
            ];

            $approval = Approval::create([
                'process_name' => 'CIT Transfer Schedule',
                'process_description' => "CIT transfer of TZS " . number_format($this->citAmount) . " scheduled for {$this->citScheduledDate}",
                'approval_process_description' => 'Management approval required for CIT operations',
                'process_code' => 'CIT_TRANSFER',
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($citData)
            ]);

            DB::commit();

            $this->reset(['citProvider', 'citAmount', 'citFromLocation', 'citToLocation', 'citScheduledDate', 'citScheduledTime', 'citReference', 'citDescription']);
            session()->flash('success', 'CIT transfer scheduled for approval');
            $this->loadPendingApprovals();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to schedule CIT transfer: ' . $e->getMessage());
        }
    }

    // =================================================
    // TELLER OPERATIONS HELPERS
    // =================================================
    
    public function getTillBalance($tillId)
    {
        $till = Till::find($tillId);
        if (!$till) return 0;
        
        $tillAccount = Account::where('account_number', $till->till_account_number)->first();
        return $tillAccount ? $tillAccount->balance : 0;
    }

    public function transferTillToVault()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'tillToVaultAmount' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->selectedTillId);

            // Check if till is assigned to current user
            if ($till->assigned_user_id !== Auth::id()) {
                session()->flash('error', 'You can only transfer from tills assigned to you');
                return;
            }

            // Check till balance
            $tillBalance = $this->getTillBalance($this->selectedTillId);
            if ($tillBalance < $this->tillToVaultAmount) {
                session()->flash('error', 'Insufficient till balance');
                return;
            }

            // Create transfer request for vault custodian approval
            $transferData = [
                'till_id' => $this->selectedTillId,
                'till_account' => $till->till_account_number,
                'vault_account' => $this->vaultAccount->account_number,
                'amount' => $this->tillToVaultAmount,
                'reason' => $this->tillToVaultNotes ?? 'Till to vault transfer',
                'requested_by_id' => Auth::id()
            ];

            $approval = Approval::create([
                'process_name' => 'Till to Vault Transfer',
                'process_description' => "Transfer TZS " . number_format($this->tillToVaultAmount) . " from {$till->name} to vault",
                'approval_process_description' => 'Vault custodian approval required for till to vault transfer',
                'process_code' => 'TILL_TO_VAULT',
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($transferData)
            ]);

            // Log the movement request
            CashMovement::create([
                'reference' => 'TILLVAULT-' . now()->format('YmdHis') . '-' . $till->id,
                'type' => 'till_to_vault',
                'amount' => $this->tillToVaultAmount,
                'reason' => $this->tillToVaultNotes ?? 'Till to vault transfer',
                'status' => 'pending_approval',
                'user_id' => Auth::id(),
                'initiated_by' => Auth::id(),
                'notes' => "Approval ID: {$approval->id}, Till: {$till->name}"
            ]);

            DB::commit();

            $this->reset(['tillToVaultAmount', 'tillToVaultNotes']);
            session()->flash('success', 'Till to vault transfer request submitted for approval');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to request transfer: ' . $e->getMessage());
        }
    }

    public function transferVaultToTill()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'vaultToTillAmount' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->selectedTillId);

            // Check if till is assigned to current user
            if ($till->assigned_user_id !== Auth::id()) {
                session()->flash('error', 'You can only request transfers for tills assigned to you');
                return;
            }

            // Create transfer request for vault custodian approval
            $transferData = [
                'till_id' => $this->selectedTillId,
                'till_account' => $till->till_account_number,
                'vault_account' => $this->vaultAccount->account_number,
                'amount' => $this->vaultToTillAmount,
                'reason' => $this->vaultToTillNotes ?? 'Vault to till transfer',
                'requested_by_id' => Auth::id()
            ];

            $approval = Approval::create([
                'process_name' => 'Vault to Till Transfer Request',
                'process_description' => "Transfer TZS " . number_format($this->vaultToTillAmount) . " from vault to {$till->name}",
                'approval_process_description' => 'Vault custodian approval required for vault to till transfer',
                'process_code' => 'VAULT_TO_TILL',
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($transferData)
            ]);

            DB::commit();

            $this->reset(['vaultToTillAmount', 'vaultToTillNotes']);
            session()->flash('success', 'Vault to till transfer request submitted for approval');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to request transfer: ' . $e->getMessage());
        }
    }

    public function processTillTransaction()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'transactionType' => 'required|in:deposit,withdrawal',
            'transactionAmount' => 'required|numeric|min:0.01',
            'transactionReference' => 'required|string|max:100',
            'transactionNarration' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $till = Till::findOrFail($this->selectedTillId);

            // Check if till is assigned to current user and open
            if ($till->assigned_user_id !== Auth::id()) {
                session()->flash('error', 'You can only process transactions on tills assigned to you');
                return;
            }

            if ($till->status !== 'open') {
                session()->flash('error', 'Till must be open to process transactions');
                return;
            }

            // Process using TransactionPostingService
            $transactionService = new TransactionPostingService();
            
            $transactionData = [
                'first_account' => $this->transactionType === 'deposit' ? 'CUSTOMER' : $till->till_account_number,
                'second_account' => $this->transactionType === 'deposit' ? $till->till_account_number : 'CUSTOMER',
                'amount' => $this->transactionAmount,
                'narration' => $this->transactionNarration,
                'action' => $this->transactionType,
                'reference' => $this->transactionReference,
                'member_number' => $this->memberNumber,
                'account_number' => $this->accountNumber,
            ];
            
            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['success']) {
                // Log the transaction
                CashMovement::create([
                    'reference' => $this->transactionReference,
                    'type' => 'customer_' . $this->transactionType,
                    'amount' => $this->transactionAmount,
                    'reason' => $this->transactionNarration,
                    'status' => 'completed',
                    'user_id' => Auth::id(),
                    'initiated_by' => Auth::id(),
                    'completed_at' => now(),
                    'notes' => "Till: {$till->name}, Member: {$this->memberNumber}, Account: {$this->accountNumber}"
                ]);

                session()->flash('success', ucfirst($this->transactionType) . ' transaction processed successfully');
            } else {
                session()->flash('error', 'Transaction failed: ' . $result['message']);
            }

            DB::commit();

            $this->reset(['transactionAmount', 'transactionReference', 'transactionNarration', 'memberNumber', 'accountNumber']);
            $this->loadTillStatus(); // Refresh till balance

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to process transaction: ' . $e->getMessage());
        }
    }

    // =================================================
    // LEGACY METHODS (for compatibility)
    // =================================================
    
    public function allocateTellerFloat()
    {
        // Legacy method - redirect to new workflow
        session()->flash('info', 'This feature has been restructured. Please use the Till Assignment and Vault Operations tabs.');
        $this->activeTab = 'vault';
    }

    public function processPettyCashRequest()
    {
        // Legacy method - placeholder
        session()->flash('info', 'Petty cash management has been moved to the new workflow.');
    }

    public function refreshTillData()
    {
        $this->loadTillsData();
        $this->loadTillSummary();
        session()->flash('success', 'Till data refreshed');
    }

    public function render()
    {
        return view('livewire.cash-management.cash-management');
    }
}
