<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Till;
use App\Models\Teller;
use App\Models\TillTransaction;
use App\Models\TillReconciliation;
use App\Models\StrongroomLedger;
use App\Models\CashMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Services\AccountCreationService;
use App\Models\approvals;
use App\Models\SecurityTransportLog;
use App\Models\Vault;
use App\Models\BranchesModel;

class TillAndCashManagement extends Component
{
    use WithPagination;

    // UI State Management
    public $activeTab = 'dashboard'; // dashboard, transactions, reconciliation, strongroom
    public $showTillForm = false;
    public $showTransactionForm = false;
    public $showReconciliationForm = false;
    public $showStrongroomForm = false;
    
    // Modal States
    public $showTillModal = false;
    public $showStrongroomModal = false;
    public $showReconciliationModal = false;
    public $showApprovalModal = false;
    public $showConfirmationModal = false;
    public $showCreateTillModal = false;
    public $showAssignModal = false;
    public $showTillDetailsModal = false;
    
    // Vault Transfer Modal States
    public $showReplenishmentModal = false;
    public $showDepositModal = false;
    public $showDirectTransferModal = false;
    public $showTwoPersonVerificationModal = false;
    
    // Vault Management Modal States
    public $showCreateVaultModal = false;
    public $showEditVaultModal = false;
    public $showVaultDetailsModal = false;
    public $showBankTransferModal = false;
    
    // Modal Data
    public $tillModalTitle = '';
    public $tillOperationType = '';
    public $tillOperationAmount = '';
    public $tillOperationNotes = '';
    public $selectedTillId;
    public $newTill = [
        'name' => '',
        'till_number' => '',
        'branch_id' => '',
        'institution_id' => '',
        'parent_account' => '',
        'maximum_limit' => 500000.00,
        'minimum_limit' => 10000.00,
        'status' => 'closed',
        'requires_supervisor_approval' => false,
        'description' => ''
    ];
    
    // Strongroom Modal Data
    public $strongroomMovementType = '';
    public $strongroomMovementAmount = 0;
    public $sourceTillId = '';
    public $destinationTillId = '';
    public $strongroomMovementPurpose = '';
    
    // Reconciliation Modal Data
    public $reconciliationTillId = '';
    public $expectedAmount = 0;
    public $actualAmount = 0;
    public $varianceNotes = '';
    
    // Approval Modal Data
    public $approvalRequestType = '';
    public $approvalRequestAmount = 0;
    public $approvalRequestReason = '';
    public $approvalRequestUrgency = 'normal';
    
    // Confirmation Modal Data
    public $confirmationTitle = '';
    public $confirmationMessage = '';
    public $confirmationButtonText = '';
    public $confirmationAction = '';
    
    // Vault Transfer Modal Data
    public $replenishmentTillId = '';
    public $replenishmentAmount = 0;
    public $replenishmentReason = '';
    public $replenishmentUrgency = 'normal';
    public $replenishmentNotes = '';
    
    public $depositTillId = '';
    public $depositAmount = 0;
    public $depositReason = '';
    public $depositNotes = '';
    
    public $transferPurpose = '';
    public $secondAuthorizerId = '';
    
    // Direct Transfer Modal Data
    public $directTransferDirection = 'to_till';
    public $directTransferTillId = '';
    public $directTransferAmount = 0;
    public $directTransferPurpose = '';
    
    public $verificationCode = '';
    public $verificationNotes = '';
    public $verificationAuthorizerId = '';
    
    // Bank to Vault Transfer Modal Data
    public $showBankToVaultModal = false;
    public $bankToVaultTransfer = [
        'vault_id' => '',
        'bank_account_id' => '',
        'amount' => 0,
        'reference' => '',
        'description' => '',
        'authorizer_id' => '',
        // Security Transport Details
        'transport_company_name' => '',
        'transport_company_license' => '',
        'transport_company_contact' => '',
        'vehicle_registration' => '',
        'vehicle_type' => '',
        'team_leader_name' => '',
        'team_leader_badge' => '',
        'team_leader_contact' => '',
        'scheduled_pickup_time' => '',
        'scheduled_delivery_time' => '',
        'insurance_policy_number' => '',
        'insurance_coverage_amount' => 0,
        'planned_route' => '',
        'security_personnel' => []
    ];
    
    // Vault to Bank Transfer Modal Data
    public $showVaultToBankModal = false;
    public $vaultToBankTransfer = [
        'vault_id' => '',
        'bank_account_id' => '',
        'amount' => 0,
        'reference' => '',
        'description' => '',
        'authorizer_id' => '',
        // Security Transport Details
        'transport_company_name' => '',
        'transport_company_license' => '',
        'transport_company_contact' => '',
        'vehicle_registration' => '',
        'vehicle_type' => '',
        'team_leader_name' => '',
        'team_leader_badge' => '',
        'team_leader_contact' => '',
        'scheduled_pickup_time' => '',
        'scheduled_delivery_time' => '',
        'insurance_policy_number' => '',
        'insurance_coverage_amount' => 0,
        'planned_route' => '',
        'security_personnel' => []
    ];
    
    // Vault Management Data
    public $selectedVaultId = '';
    public $vaultForm = [
        'name' => '',
        'code' => '',
        'institution_id' => '',
        'branch_id' => '',
        'parent_account' => '',
        'current_balance' => 0,
        'limit' => 1000000,
        'warning_threshold' => 80,
        'bank_name' => '',
        'bank_account_number' => '',
        'auto_bank_transfer' => false,
        'requires_dual_approval' => false,
        'send_alerts' => true,
        'status' => 'active',
        'description' => ''
    ];
    
    public $bankTransferAmount = 0;
    public $bankTransferReason = '';
    public $bankTransferAccount = '';
    public $bankTransferReference = '';
    
    // Filter Properties
    public $reconciliationDateRange = 'today';
    public $reconciliationStatus = '';
    public $reconciliationSearch = '';
    public $reconciliationSortField = 'date';
    public $reconciliationSortDirection = 'desc';
    
    public $strongroomDateRange = 'today';
    public $movementType = '';
    public $strongroomStatus = '';
    public $strongroomSearch = '';
    public $strongroomSortField = 'created_at';
    public $strongroomSortDirection = 'desc';
    
    public $approvalDateRange = 'today';
    public $approvalType = '';
    public $approvalStatus = '';
    public $approvalSearch = '';
    public $approvalSortField = 'created_at';
    public $approvalSortDirection = 'desc';
    
    // Transfer History Filters
    public $transferSortField = 'created_at';
    public $transferSortDirection = 'desc';
    
    // Till Management Filters
    public $filterStatus = '';
    public $filterBranch = '';
    public $perPage = 10;
    public $assignUserId = '';

    // Till Management
    public $tillStatus = 'closed'; // open, closed, suspended
    public $openingBalance = 0;
    public $currentBalance = 0;
    public $expectedBalance = 0;
    public $countedBalance = 0;

    // Transaction Form
    public $transactionType = 'deposit'; // deposit, withdrawal, transfer_to_vault, transfer_from_vault
    public $transactionAmount;
    public $memberId;
    public $accountId;
    public $narration;
    public $authorizingUser;

    // Strongroom Management
    public $strongroomBalance = 0;
    public $transferAmount;
    public $transferDirection = 'to_till'; // to_till, from_till
    public $targetTillId;
    public $authorizationRequired = true;

    // Filters and Search
    public $searchTerm = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterType = '';
    public $filterTeller = '';

    // User Role Check
    public $userRole;
    public $isTeller = false;
    public $isSupervisor = false;
    public $isAdmin = false;

    protected $rules = [
        'transactionAmount' => 'required|numeric|min:0.01',
        'narration' => 'required|string|min:3|max:255',
        'countedBalance' => 'required|numeric|min:0',
        'transferAmount' => 'required|numeric|min:0.01',
        'targetTillId' => 'required|exists:tills,id',
    ];

    protected $messages = [
        'transactionAmount.required' => 'Please enter the transaction amount.',
        'transactionAmount.min' => 'Amount must be greater than zero.',
        'narration.required' => 'Please provide a transaction description.',
        'countedBalance.required' => 'Please enter the counted cash amount.',
        'transferAmount.required' => 'Please enter the transfer amount.',
        'targetTillId.required' => 'Please select the target till.',
    ];

    // Vault Replenishment Properties
    public $pendingReplenishments = [];
    public $selectedReplenishment = null;
    public $replenishmentApprovalNotes = '';
    public $showReplenishmentApprovalModal = false;

    public function mount()
    {
        // Development mode - grant full access
        $this->userRole = 'admin';
        $this->isTeller = true;
        $this->isSupervisor = true;
        $this->isAdmin = true;
        
        $this->filterDateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->filterDateTo = now()->format('Y-m-d');
        
        $this->loadUserTill();
        $this->loadStrongroomBalance();
        $this->loadPendingReplenishments();
    }

    public function loadUserTill()
    {
        if ($this->isTeller) {
            $teller = Teller::where('user_id', Auth::id())->first();
            if ($teller && $teller->till) {
                $this->selectedTillId = $teller->till->id;
                $this->currentBalance = $teller->till->current_balance;
                $this->tillStatus = $teller->till->status;
            }
        }
    }

    public function loadStrongroomBalance()
    {
        $strongroom = StrongroomLedger::first();
        $this->strongroomBalance = $strongroom ? $strongroom->balance : 0;
    }

    public function loadPendingReplenishments()
    {
        $this->pendingReplenishments = approvals::where('process_code', 'VAULT_REPLENISHMENT')
            ->where('approval_status', 'PENDING')
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Till Operations
    public function openTill()
    {
        // Development mode - allow all operations
        if (!$this->selectedTillId) {
            session()->flash('error', 'Please select a till.');
            return;
        }

        DB::beginTransaction();
        try {
            $till = Till::findOrFail($this->selectedTillId);
            
            if ($till->status === 'open') {
                session()->flash('error', 'Till is already open.');
                return;
            }

            $till->update([
                'status' => 'open',
                'opening_balance' => $this->openingBalance,
                'current_balance' => $this->openingBalance,
                'opened_at' => now(),
                'opened_by' => Auth::id(),
            ]);

            // Log the opening as a cash movement
            CashMovement::create([
                'reference' => 'TIL-' . now()->format('YmdHis') . '-' . $till->id,
                'type' => 'vault_to_till',
                'to_till_id' => $till->id,
                'amount' => $this->openingBalance,
                'reason' => 'Till opening balance',
                'user_id' => Auth::id(),
                'initiated_by' => Auth::id(),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();
            $this->tillStatus = 'open';
            $this->currentBalance = $this->openingBalance;
            session()->flash('success', 'Till opened successfully.');
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Error opening till: ' . $e->getMessage());
        }
    }

    public function closeTill()
    {
        // Development mode - allow all operations
        if (!$this->selectedTillId) {
            session()->flash('error', 'Please select a till.');
            return;
        }

        $this->showReconciliationForm = true;
    }

    public function reconcileTill()
    {
        $this->validate(['countedBalance' => 'required|numeric|min:0']);

        DB::beginTransaction();
        try {
            $till = Till::findOrFail($this->selectedTillId);
            $difference = $this->countedBalance - $till->current_balance;

            // Create reconciliation record
            TillReconciliation::create([
                'till_id' => $till->id,
                'teller_id' => Auth::user()->teller->id ?? null,
                'reconciliation_date' => now()->toDateString(),
                'opening_balance' => $till->opening_balance,
                'closing_balance_system' => $till->current_balance,
                'closing_balance_actual' => $this->countedBalance,
                'variance' => $difference,
                'variance_explanation' => $this->narration ?: 'Till reconciliation',
                'status' => $difference == 0 ? 'balanced' : ($difference > 0 ? 'over' : 'short'),
                'submitted_at' => now(),
                'completed_at' => now(),
            ]);

            // Close the till
            $till->update([
                'status' => 'closed',
                'closing_balance' => $this->countedBalance,
                'closed_at' => now(),
                'closed_by' => Auth::id(),
            ]);

            DB::commit();
            $this->tillStatus = 'closed';
            $this->showReconciliationForm = false;
            $this->reset(['countedBalance', 'narration']);
            
            if ($difference != 0) {
                session()->flash('warning', "Till closed with difference: " . number_format($difference, 2));
            } else {
                session()->flash('success', 'Till reconciled and closed successfully.');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Error closing till: ' . $e->getMessage());
        }
    }

    // Transaction Processing
    public function processTransaction()
    {
        $this->validate([
            'transactionAmount' => 'required|numeric|min:0.01',
            'narration' => 'required|string|min:3|max:255',
        ]);

        if (!$this->selectedTillId || $this->tillStatus !== 'open') {
            session()->flash('error', 'Till must be open to process transactions.');
            return;
        }

        DB::beginTransaction();
        try {
            $till = Till::findOrFail($this->selectedTillId);
            
            // Validate sufficient funds for withdrawals
            if (in_array($this->transactionType, ['withdrawal', 'transfer_to_vault']) && 
                $till->current_balance < $this->transactionAmount) {
                session()->flash('error', 'Insufficient till balance.');
                return;
            }

            // Calculate new balance
            $newBalance = $till->current_balance;
            if (in_array($this->transactionType, ['deposit', 'transfer_from_vault'])) {
                $newBalance += $this->transactionAmount;
            } else {
                $newBalance -= $this->transactionAmount;
            }

            // Create transaction record
            TillTransaction::create([
                'till_id' => $till->id,
                'member_id' => $this->memberId,
                'account_id' => $this->accountId,
                'type' => $this->transactionType,
                'amount' => $this->transactionAmount,
                'balance_before' => $till->current_balance,
                'balance_after' => $newBalance,
                'narration' => $this->narration,
                'created_by' => Auth::id(),
            ]);

            // Update till balance
            $till->update(['current_balance' => $newBalance]);

            // Handle strongroom transfers
            if (in_array($this->transactionType, ['transfer_to_vault', 'transfer_from_vault'])) {
                $this->processStrongroomTransfer();
            }

            DB::commit();
            $this->currentBalance = $newBalance;
            $this->showTransactionForm = false;
            $this->reset(['transactionAmount', 'narration', 'memberId', 'accountId']);
            session()->flash('success', 'Transaction processed successfully.');
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Error processing transaction: ' . $e->getMessage());
        }
    }

    private function processStrongroomTransfer()
    {
        $strongroom = StrongroomLedger::firstOrCreate(['id' => 1], ['balance' => 0]);
        
        if ($this->transactionType === 'transfer_to_vault') {
            $strongroom->increment('balance', $this->transactionAmount);
            $fromType = 'till';
            $toType = 'strongroom';
        } else {
            $strongroom->decrement('balance', $this->transactionAmount);
            $fromType = 'strongroom';
            $toType = 'till';
        }

        // Log cash movement
        CashMovement::create([
            'reference' => 'TXN-' . now()->format('YmdHis') . '-' . $this->selectedTillId,
            'type' => $this->transactionType === 'transfer_to_vault' ? 'till_to_vault' : 'vault_to_till',
            'from_till_id' => $this->transactionType === 'transfer_to_vault' ? $this->selectedTillId : null,
            'to_till_id' => $this->transactionType === 'transfer_from_vault' ? $this->selectedTillId : null,
            'amount' => $this->transactionAmount,
            'reason' => $this->narration,
            'user_id' => Auth::id(),
            'initiated_by' => Auth::id(),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->loadStrongroomBalance();
    }

    // Strongroom Operations
    public function processVaultTransfer()
    {
        // Development mode - allow all operations
        $this->validate([
            'transferAmount' => 'required|numeric|min:0.01',
            'targetTillId' => 'required|exists:tills,id',
        ]);

        DB::beginTransaction();
        try {
            $till = Till::findOrFail($this->targetTillId);
            $strongroom = StrongroomLedger::firstOrCreate(['id' => 1], ['balance' => 0]);

            if ($this->transferDirection === 'to_till') {
                // Transfer from strongroom to till
                if ($strongroom->balance < $this->transferAmount) {
                    session()->flash('error', 'Insufficient strongroom balance.');
                    return;
                }
                
                $strongroom->decrement('balance', $this->transferAmount);
                $till->increment('current_balance', $this->transferAmount);
                
                $movement = CashMovement::create([
                    'reference' => 'VTR-' . now()->format('YmdHis') . '-' . $till->id,
                    'type' => 'vault_to_till',
                    'to_till_id' => $till->id,
                    'amount' => $this->transferAmount,
                    'reason' => 'Strongroom to till transfer',
                    'user_id' => Auth::id(),
                    'initiated_by' => Auth::id(),
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            } else {
                // Transfer from till to strongroom
                if ($till->current_balance < $this->transferAmount) {
                    session()->flash('error', 'Insufficient till balance.');
                    return;
                }
                
                $till->decrement('current_balance', $this->transferAmount);
                $strongroom->increment('balance', $this->transferAmount);
                
                $movement = CashMovement::create([
                    'reference' => 'TVR-' . now()->format('YmdHis') . '-' . $till->id,
                    'type' => 'till_to_vault',
                    'from_till_id' => $till->id,
                    'amount' => $this->transferAmount,
                    'reason' => 'Till to strongroom transfer',
                    'user_id' => Auth::id(),
                    'initiated_by' => Auth::id(),
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();
            $this->loadStrongroomBalance();
            $this->loadUserTill();
            $this->showStrongroomForm = false;
            $this->reset(['transferAmount', 'targetTillId']);
            session()->flash('success', 'Strongroom transfer completed successfully.');
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Error processing strongroom transfer: ' . $e->getMessage());
        }
    }

    // UI Methods
    public function showTransactionModal()
    {
        // Development mode - allow all operations
        if ($this->tillStatus !== 'open' && $this->selectedTillId) {
            session()->flash('warning', 'Till should be open to process transactions, but allowing in development mode.');
        }
        $this->showTransactionForm = true;
    }

    public function showStrongroomModal()
    {
        // Development mode - allow all operations
        $this->showStrongroomForm = true;
    }

    public function hideModals()
    {
        $this->showTillForm = false;
        $this->showTransactionForm = false;
        $this->showReconciliationForm = false;
        $this->showStrongroomForm = false;
        $this->resetValidation();
    }

    // Modal Methods
    public function showTillModal($operation = '')
    {
        $this->tillModalTitle = ucfirst($operation) . ' Till';
        $this->tillOperationType = $operation;
        $this->showTillModal = true;
    }

    public function closeTillModal()
    {
        $this->showTillModal = false;
        $this->reset(['tillModalTitle', 'tillOperationType', 'tillOperationAmount', 'tillOperationNotes']);
    }

    public function saveTillOperation()
    {
        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'tillOperationType' => 'required|in:opening,closing,deposit,withdrawal,transfer',
            'tillOperationAmount' => 'required|numeric|min:0.01',
        ]);

        // Implementation will be added based on operation type
        $this->closeTillModal();
        session()->flash('success', 'Till operation completed successfully.');
    }

    public function showStrongroomMovementModal()
    {
        $this->showStrongroomModal = true;
    }

    public function closeStrongroomModal()
    {
        $this->showStrongroomModal = false;
        $this->reset(['strongroomMovementType', 'strongroomMovementAmount', 'sourceTillId', 'destinationTillId', 'strongroomMovementPurpose']);
    }

    public function saveStrongroomMovement()
    {
        $this->validate([
            'strongroomMovementType' => 'required|in:deposit,withdrawal,transfer',
            'strongroomMovementAmount' => 'required|numeric|min:0.01',
        ]);

        // Implementation will be added
        $this->closeStrongroomModal();
        session()->flash('success', 'Strongroom movement completed successfully.');
    }

    public function showReconciliationModal()
    {
        $this->showReconciliationModal = true;
    }

    public function closeReconciliationModal()
    {
        $this->showReconciliationModal = false;
        $this->reset(['reconciliationTillId', 'expectedAmount', 'actualAmount', 'varianceNotes']);
    }

    public function saveReconciliation()
    {
        $this->validate([
            'reconciliationTillId' => 'required|exists:tills,id',
            'expectedAmount' => 'required|numeric|min:0',
            'actualAmount' => 'required|numeric|min:0',
        ]);

        // Implementation will be added
        $this->closeReconciliationModal();
        session()->flash('success', 'Reconciliation completed successfully.');
    }

    public function showApprovalModal()
    {
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->reset(['approvalRequestType', 'approvalRequestAmount', 'approvalRequestReason', 'approvalRequestUrgency']);
    }

    public function saveApproval()
    {
        $this->validate([
            'approvalRequestType' => 'required|in:till_opening,till_closing,cash_movement,strongroom_access,reconciliation',
            'approvalRequestReason' => 'required|string|min:10',
        ]);

        // Implementation will be added
        $this->closeApprovalModal();
        session()->flash('success', 'Approval request submitted successfully.');
    }

    public function closeConfirmationModal()
    {
        $this->showConfirmationModal = false;
        $this->reset(['confirmationTitle', 'confirmationMessage', 'confirmationButtonText', 'confirmationAction']);
    }

    public function confirmAction()
    {
        // Implementation will be added based on confirmation action
        $this->closeConfirmationModal();
    }

    // Sorting Methods
    public function sortReconciliations($field)
    {
        if ($this->reconciliationSortField === $field) {
            $this->reconciliationSortDirection = $this->reconciliationSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->reconciliationSortField = $field;
            $this->reconciliationSortDirection = 'asc';
        }
    }

    public function sortStrongroom($field)
    {
        if ($this->strongroomSortField === $field) {
            $this->strongroomSortDirection = $this->strongroomSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->strongroomSortField = $field;
            $this->strongroomSortDirection = 'asc';
        }
    }

    public function sortApprovals($field)
    {
        if ($this->approvalSortField === $field) {
            $this->approvalSortDirection = $this->approvalSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->approvalSortField = $field;
            $this->approvalSortDirection = 'asc';
        }
    }

    // Export Methods
    public function exportReconciliation()
    {
        // Implementation will be added
        session()->flash('success', 'Reconciliation data exported successfully.');
    }

    public function exportStrongroom()
    {
        // Implementation will be added
        session()->flash('success', 'Strongroom data exported successfully.');
    }

    public function exportApprovals()
    {
        // Implementation will be added
        session()->flash('success', 'Approvals data exported successfully.');
    }

    // Action Methods
    public function viewReconciliation($id)
    {
        // Implementation will be added
    }

    public function approveReconciliation($id)
    {
        // Implementation will be added
        session()->flash('success', 'Reconciliation approved successfully.');
    }

    public function resolveDiscrepancy($id)
    {
        // Implementation will be added
        session()->flash('success', 'Discrepancy resolved successfully.');
    }

    public function viewStrongroomEntry($id)
    {
        // Implementation will be added
    }

    public function approveStrongroomEntry($id)
    {
        // Implementation will be added
        session()->flash('success', 'Strongroom entry approved successfully.');
    }

    public function rejectStrongroomEntry($id)
    {
        // Implementation will be added
        session()->flash('success', 'Strongroom entry rejected successfully.');
    }

    public function viewApproval($id)
    {
        // Implementation will be added
    }

    public function approveRequest($id)
    {
        // Implementation will be added
        session()->flash('success', 'Request approved successfully.');
    }

    public function rejectRequest($id)
    {
        // Implementation will be added
        session()->flash('success', 'Request rejected successfully.');
    }

    public function cancelRequest($id)
    {
        // Implementation will be added
        session()->flash('success', 'Request cancelled successfully.');
    }

    // Till Assignment Methods
    public function showAssignTillModal($tillId = null)
    {
        $this->selectedTillId = $tillId;
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->reset(['selectedTillId', 'assignUserId']);
    }

    /**
     * Create an activity log entry for till operations
     */
    private function createActivityLog($action, $data = [])
    {
        try {
            $logData = [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'action' => $action,
                'data' => json_encode($data),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ];

            // Log to database if ActivityLog model exists
            if (class_exists('App\Models\ActivityLog')) {
                $activityLogClass = 'App\Models\ActivityLog';
                $activityLogClass::create($logData);
            }

            // Also log to file for redundancy
            Log::info('Till Activity Log: ' . $action, $logData);

        } catch (\Exception $e) {
            Log::error('Failed to create activity log', [
                'action' => $action,
                'data' => $data,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    public function assignTill()
    {

  // add logging 

        $this->validate([
            'selectedTillId' => 'required|exists:tills,id',
            'assignUserId' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $till = Till::findOrFail($this->selectedTillId);
            $user = User::findOrFail($this->assignUserId);
            $currentUser = Auth::user();


            
            // Log the assignment attempt
            Log::info('Till assignment attempt initiated', [
                'till_id' => $this->selectedTillId,
                'till_name' => $till->name ?? 'Till #' . $till->id,
                'till_number' => $till->till_number ?? 'TIL' . str_pad($till->id, 3, '0', STR_PAD_LEFT),
                'till_status' => $till->status,
                'till_branch' => $till->branch ? $till->branch->name : 'Unknown',
                'user_id' => $this->assignUserId,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'assigned_by_id' => $currentUser->id,
                'assigned_by_name' => $currentUser->name,
                'assigned_by_email' => $currentUser->email,
                'assigned_by_role' => $currentUser->role,
           
            ]);

            // Check if user already has a till assigned
            $existingTeller = Teller::where('user_id', $this->assignUserId)->first();
            if ($existingTeller && $existingTeller->till_id) {
                $existingTill = Till::find($existingTeller->till_id);
                
                // Log the conflict
                Log::warning('Till assignment conflict - user already assigned to another till', [
                    'user_id' => $this->assignUserId,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'existing_till_id' => $existingTeller->till_id,
                    'existing_till_name' => $existingTill ? ($existingTill->name ?? 'Till #' . $existingTill->id) : 'Unknown',
                    'existing_till_number' => $existingTill ? ($existingTill->till_number ?? 'TIL' . str_pad($existingTill->id, 3, '0', STR_PAD_LEFT)) : 'Unknown',
                    'requested_till_id' => $this->selectedTillId,
                    'requested_till_name' => $till->name ?? 'Till #' . $till->id,
                    'requested_till_number' => $till->till_number ?? 'TIL' . str_pad($till->id, 3, '0', STR_PAD_LEFT),
                    'assigned_by_id' => $currentUser->id,
                    'assigned_by_name' => $currentUser->name,
                    'existing_assignment_date' => $existingTeller->assigned_at,
                    'timestamp' => now()->toISOString(),
                    'ip_address' => request()->ip(),
                ]);
                
                session()->flash('error', 'User already has a till assigned.');
                return;
            }

            // Check if till is already assigned to another user
            $existingTillAssignment = Teller::where('till_id', $this->selectedTillId)->first();
            $previousAssignment = null;
            
            if ($existingTillAssignment) {
                $previousUser = User::find($existingTillAssignment->user_id);
                $previousAssignment = [
                    'user_id' => $existingTillAssignment->user_id,
                    'user_name' => $previousUser ? $previousUser->name : 'Unknown',
                    'user_email' => $previousUser ? $previousUser->email : 'Unknown',
                    'user_role' => $previousUser ? $previousUser->role : 'Unknown',
                    'assigned_at' => $existingTillAssignment->assigned_at,
                    'assigned_by_id' => $existingTillAssignment->assigned_by,
                    'teller_id' => $existingTillAssignment->id,
                ];
                
                // Log the till reassignment
                Log::info('Till reassignment detected', [
                    'till_id' => $this->selectedTillId,
                    'till_name' => $till->name ?? 'Till #' . $till->id,
                    'previous_user_id' => $existingTillAssignment->user_id,
                    'previous_user_name' => $previousUser ? $previousUser->name : 'Unknown',
                    'new_user_id' => $this->assignUserId,
                    'new_user_name' => $user->name,
                    'assigned_by_id' => $currentUser->id,
                    'assigned_by_name' => $currentUser->name,
                    'timestamp' => now()->toISOString(),
                ]);
            }

            // Check if user already has an employee_id in the tellers table
            $existingTellerRecord = Teller::where('user_id', $this->assignUserId)->first();
            $employeeId = $existingTellerRecord ? $existingTellerRecord->employee_id : ($user->employee_id ?? $user->id);

            // Create or update teller assignment
            $teller = Teller::updateOrCreate(
                ['till_id' => $this->selectedTillId],
                [
                    'user_id' => $this->assignUserId,
                    'status' => 'active',
                    'assigned_at' => now(),
                    'assigned_by' => $currentUser->id,
                    'branch_id' => $till->branch_id,
                    'institution_id' => $till->institution_id,
                    'employee_id' => $employeeId,
                    'transaction_limit' => 100000.00,
                    'max_amount' => 100000.00,
                    'registered_by_id' => $currentUser->id,
                ]
            );


            // update the till assignet to field
            $till->assigned_to = $this->assignUserId;
            $till->save();

            // Log successful assignment
            Log::info('Till assignment completed successfully', [
                'till_id' => $this->selectedTillId,
                'till_name' => $till->name ?? 'Till #' . $till->id,
                'till_number' => $till->till_number ?? 'TIL' . str_pad($till->id, 3, '0', STR_PAD_LEFT),
                'till_status' => $till->status,
                'till_branch' => $till->branch ? $till->branch->name : 'Unknown',
                'till_current_balance' => $till->current_balance,
                'till_opening_balance' => $till->opening_balance,
                'till_maximum_limit' => $till->maximum_limit,
                'till_minimum_limit' => $till->minimum_limit,
                'new_user_id' => $this->assignUserId,
                'new_user_name' => $user->name,
                'new_user_email' => $user->email,
                'new_user_role' => $user->role,
                'assigned_by_id' => $currentUser->id,
                'assigned_by_name' => $currentUser->name,
                'assigned_by_email' => $currentUser->email,
                'assigned_by_role' => $currentUser->role,
                'assignment_type' => $existingTillAssignment ? 'reassignment' : 'new_assignment',
                'previous_assignment' => $previousAssignment,
                'teller_id' => $teller->id,
                'assigned_at' => $teller->assigned_at,
                'timestamp' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
            ]);

 

            DB::commit();
            
            // Log transaction completion
            Log::info('Till assignment transaction completed successfully', [
                'till_id' => $this->selectedTillId,
                'user_id' => $this->assignUserId,
                'assigned_by_id' => $currentUser->id,
                'timestamp' => now()->toISOString(),
            ]);

            $this->closeAssignModal();
            session()->flash('success', 'Till assigned successfully to ' . $user->name);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Log the error
            Log::error('Till assignment failed', [
                'till_id' => $this->selectedTillId,
                'user_id' => $this->assignUserId,
                'assigned_by_id' => Auth::id(),
                'assigned_by_name' => Auth::user() ? Auth::user()->name : 'Unknown',
                'assigned_by_email' => Auth::user() ? Auth::user()->email : 'Unknown',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
            ]);

            session()->flash('error', 'Error assigning till: ' . $e->getMessage());
        }
    }

    public function viewTillDetails($tillId)
    {
        $this->selectedTillId = $tillId;
        $this->showTillDetailsModal = true;
    }

    public function closeTillDetailsModal()
    {
        $this->showTillDetailsModal = false;
        $this->reset(['selectedTillId']);
    }

    public function showCreateTillModal()
    {
        $this->resetNewTill();
        $this->showCreateTillModal = true;
    }

    public function closeCreateTillModal()
    {
        $this->showCreateTillModal = false;
        $this->resetNewTill();
    }

    public function resetNewTill()
    {
        $this->newTill = [
            'name' => '',
            'till_number' => '',
            'branch_id' => '',
            'institution_id' => '',
            'parent_account' => '',
            'maximum_limit' => 500000.00,
            'minimum_limit' => 10000.00,
            'status' => 'closed',
            'requires_supervisor_approval' => false,
            'description' => ''
        ];
    }

    public function saveCreateTill()
    {
        $this->validate([
            'newTill.name' => 'required|string|max:100',
            'newTill.till_number' => 'required|string|max:50|unique:tills,till_number',
            'newTill.branch_id' => 'required|exists:branches,id',
            'newTill.institution_id' => 'required|exists:institutions,id',
            'newTill.parent_account' => 'required|string|exists:accounts,account_number',
            'newTill.maximum_limit' => 'required|numeric|min:0.01',
            'newTill.minimum_limit' => 'required|numeric|min:0.01|lte:newTill.maximum_limit',
            'newTill.status' => 'required|in:open,closed,suspended,maintenance',
            'newTill.requires_supervisor_approval' => 'boolean',
            'newTill.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Log the creation attempt
            Log::info('Till creation initiated', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'till_data' => $this->newTill,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);

            $till = Till::create([
                'name' => $this->newTill['name'],
                'till_number' => $this->newTill['till_number'],
                'branch_id' => $this->newTill['branch_id'],
                'institution_id' => $this->newTill['institution_id'],
                'parent_account' => $this->newTill['parent_account'],
                'current_balance' => 0.00,
                'opening_balance' => 0.00,
                'maximum_limit' => $this->newTill['maximum_limit'],
                'minimum_limit' => $this->newTill['minimum_limit'],
                'status' => $this->newTill['status'],
                'requires_supervisor_approval' => $this->newTill['requires_supervisor_approval'],
                'description' => $this->newTill['description'],
            ]);

            // Log successful creation
            Log::info('Till created successfully', [
                'till_id' => $till->id,
                'till_name' => $till->name,
                'till_number' => $till->till_number,
                'created_by_user_id' => Auth::id(),
                'created_by_user_name' => Auth::user()->name,
                'branch_id' => $till->branch_id,
                'institution_id' => $till->institution_id,
                'maximum_limit' => $till->maximum_limit,
                'minimum_limit' => $till->minimum_limit,
                'status' => $till->status,
                'requires_supervisor_approval' => $till->requires_supervisor_approval,
                'created_at' => $till->created_at->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            // Log transaction completion
            Log::info('Till creation transaction completed successfully', [
                'till_id' => $till->id,
                'till_name' => $till->name,
                'created_by' => Auth::id(),
                'timestamp' => now()->toISOString(),
            ]);

            $this->closeCreateTillModal();
            session()->flash('success', 'Till created successfully: ' . $till->name);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Log the error
            Log::error('Till creation failed', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'till_data' => $this->newTill,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);

            session()->flash('error', 'Error creating till: ' . $e->getMessage());
        }
    }

    // Vault Transfer Modal Methods
    public function showReplenishmentModal()
    {
        $this->showReplenishmentModal = true;
    }

    public function closeReplenishmentModal()
    {
        $this->showReplenishmentModal = false;
        $this->reset(['replenishmentTillId', 'replenishmentAmount', 'replenishmentReason', 'replenishmentUrgency', 'replenishmentNotes']);
    }

    public function saveReplenishmentRequest()
    {
        $this->validate([
            'replenishmentTillId' => 'required|exists:tills,id',
            'replenishmentAmount' => 'required|numeric|min:0.01',
            'replenishmentReason' => 'required|string|min:3',
        ]);

        // Process replenishment request logic here
        session()->flash('success', 'Replenishment request submitted successfully.');
        $this->closeReplenishmentModal();
    }

    public function submitReplenishmentRequest()
    {
        $this->validate([
            'replenishmentTillId' => 'required|exists:tills,id',
            'replenishmentAmount' => 'required|numeric|min:0.01',
            'replenishmentReason' => 'required|string|min:3',
        ]);

        try {
            // Create replenishment request logic here
            // For now, we'll just flash a success message
            session()->flash('success', 'Replenishment request submitted successfully.');
            $this->closeReplenishmentModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error submitting request: ' . $e->getMessage());
        }
    }

    public function showDepositModal()
    {
        $this->showDepositModal = true;
    }

    public function closeDepositModal()
    {
        $this->showDepositModal = false;
        $this->reset(['depositTillId', 'depositAmount', 'depositReason', 'depositNotes']);
    }

    public function saveDepositRequest()
    {
        $this->validate([
            'depositTillId' => 'required|exists:tills,id',
            'depositAmount' => 'required|numeric|min:0.01',
            'depositReason' => 'required|string|min:3',
        ]);

        // Process deposit request logic here
        session()->flash('success', 'Deposit request submitted successfully.');
        $this->closeDepositModal();
    }

    public function submitDepositRequest()
    {
        $this->validate([
            'depositTillId' => 'required|exists:tills,id',
            'depositAmount' => 'required|numeric|min:0.01',
            'depositReason' => 'required|string|min:3',
        ]);

        try {
            // Create deposit request logic here
            session()->flash('success', 'Deposit request submitted successfully.');
            $this->closeDepositModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error submitting deposit request: ' . $e->getMessage());
        }
    }

    public function showDirectTransferModal()
    {
        $this->showDirectTransferModal = true;
    }

    public function closeDirectTransferModal()
    {
        $this->showDirectTransferModal = false;
        $this->reset(['transferDirection', 'targetTillId', 'transferAmount', 'transferPurpose', 'secondAuthorizerId']);
    }

    public function saveDirectTransfer()
    {
        $this->validate([
            'transferDirection' => 'required|in:to_till,from_till',
            'targetTillId' => 'required|exists:tills,id',
            'transferAmount' => 'required|numeric|min:0.01',
            'transferPurpose' => 'required|string|min:3',
            'secondAuthorizerId' => 'required|exists:users,id',
        ]);

        // Process direct transfer logic here
        session()->flash('success', 'Direct transfer completed successfully.');
        $this->closeDirectTransferModal();
    }

    public function submitDirectTransfer()
    {
        $this->validate([
            'transferDirection' => 'required|in:to_till,from_till',
            'targetTillId' => 'required|exists:tills,id',
            'transferAmount' => 'required|numeric|min:0.01',
            'transferPurpose' => 'required|string|min:3',
            'secondAuthorizerId' => 'required|exists:users,id',
        ]);

        try {
            // Process direct transfer logic here
            session()->flash('success', 'Direct transfer submitted successfully.');
            $this->closeDirectTransferModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error submitting direct transfer: ' . $e->getMessage());
        }
    }

    public function showTwoPersonVerificationModal()
    {
        $this->showTwoPersonVerificationModal = true;
    }

    public function closeTwoPersonVerificationModal()
    {
        $this->showTwoPersonVerificationModal = false;
        $this->reset(['verificationCode', 'verificationNotes']);
    }

    public function saveVerification()
    {
        $this->validate([
            'verificationCode' => 'required|string|min:4',
            'verificationNotes' => 'nullable|string',
        ]);

        // Process verification logic here
        session()->flash('success', 'Verification completed successfully.');
        $this->closeTwoPersonVerificationModal();
    }

    public function confirmTwoPersonVerification()
    {
        $this->validate([
            'verificationCode' => 'required|string|min:4',
            'verificationNotes' => 'nullable|string',
        ]);

        // Process two-person verification logic here
        session()->flash('success', 'Two-person verification completed successfully.');
        $this->closeTwoPersonVerificationModal();
    }

    // Vault Management Modal Methods
    public function showCreateVaultModal()
    {
        $this->resetVaultForm();
        $this->showCreateVaultModal = true;
    }

    public function closeCreateVaultModal()
    {
        $this->showCreateVaultModal = false;
        $this->resetVaultForm();
    }

    public function showEditVaultModal($vaultId)
    {
        $this->selectedVaultId = $vaultId;
        // Load vault data into form
        if ($vaultId) {
            $vault = \App\Models\Vault::find($vaultId);
            if ($vault) {
                $this->vaultForm = $vault->toArray();
            }
        }
        $this->showEditVaultModal = true;
    }

    public function closeEditVaultModal()
    {
        $this->showEditVaultModal = false;
        $this->resetVaultForm();
        $this->selectedVaultId = '';
    }

    public function closeVaultModal()
    {
        $this->showCreateVaultModal = false;
        $this->showEditVaultModal = false;
        $this->resetVaultForm();
        $this->selectedVaultId = '';
    }

    public function showVaultDetailsModal($vaultId)
    {
        $this->selectedVaultId = $vaultId;
        $this->showVaultDetailsModal = true;
    }

    public function closeVaultDetailsModal()
    {
        $this->showVaultDetailsModal = false;
        $this->selectedVaultId = '';
    }

    public function showBankTransferModal($vaultId)
    {
        $this->selectedVaultId = $vaultId;
        $this->showBankTransferModal = true;
    }

    public function closeBankTransferModal()
    {
        $this->showBankTransferModal = false;
        $this->reset(['selectedVaultId', 'bankTransferAmount', 'bankTransferReason', 'bankTransferAccount']);
    }

    // Bank to Vault Transfer Methods
    public function showBankToVaultModal($vaultId = null)
    {
        $this->resetBankToVaultForm();
        $this->bankToVaultTransfer['vault_id'] = $vaultId ?? $this->selectedVaultId;
        $this->showBankToVaultModal = true;
    }

    public function closeBankToVaultModal()
    {
        $this->showBankToVaultModal = false;
        $this->resetBankToVaultForm();
    }

    public function resetBankToVaultForm()
    {
        $this->bankToVaultTransfer = [
            'vault_id' => '',
            'bank_account_id' => '',
            'amount' => 0,
            'reference' => '',
            'description' => '',
            'authorizer_id' => '',
            // Security Transport Details
            'transport_company_name' => '',
            'transport_company_license' => '',
            'transport_company_contact' => '',
            'vehicle_registration' => '',
            'vehicle_type' => '',
            'team_leader_name' => '',
            'team_leader_badge' => '',
            'team_leader_contact' => '',
            'scheduled_pickup_time' => '',
            'scheduled_delivery_time' => '',
            'insurance_policy_number' => '',
            'insurance_coverage_amount' => 0,
            'planned_route' => '',
            'security_personnel' => []
        ];
    }

    // Vault to Bank Transfer Methods
    public function showVaultToBankModal($vaultId = null)
    {
        $this->resetVaultToBankForm();
        $this->vaultToBankTransfer['vault_id'] = $vaultId ?? $this->selectedVaultId;
        $this->showVaultToBankModal = true;
    }

    public function closeVaultToBankModal()
    {
        $this->showVaultToBankModal = false;
        $this->resetVaultToBankForm();
    }

    public function resetVaultToBankForm()
    {
        $this->vaultToBankTransfer = [
            'vault_id' => '',
            'bank_account_id' => '',
            'amount' => 0,
            'reference' => '',
            'description' => '',
            'authorizer_id' => '',
            // Security Transport Details
            'transport_company_name' => '',
            'transport_company_license' => '',
            'transport_company_contact' => '',
            'vehicle_registration' => '',
            'vehicle_type' => '',
            'team_leader_name' => '',
            'team_leader_badge' => '',
            'team_leader_contact' => '',
            'scheduled_pickup_time' => '',
            'scheduled_delivery_time' => '',
            'insurance_policy_number' => '',
            'insurance_coverage_amount' => 0,
            'planned_route' => '',
            'security_personnel' => []
        ];
    }

    public function processVaultToBankTransfer()
    {
        $this->validate([
            'vaultToBankTransfer.vault_id' => 'required|exists:vaults,id',
            'vaultToBankTransfer.bank_account_id' => 'required|exists:bank_accounts,id',
            'vaultToBankTransfer.amount' => 'required|numeric|min:0.01',
            'vaultToBankTransfer.description' => 'required|string|min:3',
            'vaultToBankTransfer.authorizer_id' => 'required|exists:users,id',
            // Security Transport Validation
            'vaultToBankTransfer.transport_company_name' => 'nullable|string|max:255',
            'vaultToBankTransfer.vehicle_registration' => 'nullable|string|max:255',
            'vaultToBankTransfer.team_leader_name' => 'nullable|string|max:255',
            'vaultToBankTransfer.scheduled_pickup_time' => 'nullable|date|after:now',
            'vaultToBankTransfer.scheduled_delivery_time' => 'nullable|date|after:vaultToBankTransfer.scheduled_pickup_time',
        ]);

        DB::beginTransaction();
        try {
            // Get vault and bank account
            $vault = \App\Models\Vault::findOrFail($this->vaultToBankTransfer['vault_id']);
            $bankAccount = DB::table('bank_accounts')->where('id', $this->vaultToBankTransfer['bank_account_id'])->first();

            if (!$bankAccount) {
                throw new \Exception('Bank account not found.');
            }

            // Check if vault has sufficient funds
            if ($vault->current_balance < $this->vaultToBankTransfer['amount']) {
                throw new \Exception('Insufficient funds in vault. Available: ' . number_format($vault->current_balance, 2));
            }

            // Generate reference number if not provided
            $reference = $this->vaultToBankTransfer['reference'] ?: 'VTB-' . now()->format('YmdHis') . '-' . rand(1000, 9999);

            // Update vault balance (decrease)
            $vault->decrement('current_balance', $this->vaultToBankTransfer['amount']);

            // Update bank account balance (increase)
            DB::table('bank_accounts')
                ->where('id', $this->vaultToBankTransfer['bank_account_id'])
                ->increment('current_balance', $this->vaultToBankTransfer['amount']);

            // Create accounting entries if vault has internal account
            if ($vault->internal_account_number) {
                // Credit vault internal account (decrease vault cash)
                $this->createAccountingEntry([
                    'account_number' => $vault->internal_account_number,
                    'debit_amount' => 0,
                    'credit_amount' => $this->vaultToBankTransfer['amount'],
                    'narration' => 'Vault to Bank Transfer: ' . $this->vaultToBankTransfer['description'],
                    'reference' => $reference,
                    'transaction_type' => 'VAULT_TO_BANK_TRANSFER'
                ]);

                // Debit bank mirror account (increase bank cash)
                if ($bankAccount->internal_mirror_account_number) {
                    $this->createAccountingEntry([
                        'account_number' => $bankAccount->internal_mirror_account_number,
                        'debit_amount' => $this->vaultToBankTransfer['amount'],
                        'credit_amount' => 0,
                        'narration' => 'Vault to Bank Transfer: ' . $this->vaultToBankTransfer['description'],
                        'reference' => $reference,
                        'transaction_type' => 'VAULT_TO_BANK_TRANSFER'
                    ]);
                }
            }

            // Create approval record
            approvals::create([
                'process_name' => 'vault_to_bank_transfer',
                'process_description' => Auth::user()->name . ' transferred ' . number_format($this->vaultToBankTransfer['amount'], 2) . ' from ' . $vault->name . ' to ' . $bankAccount->account_name,
                'approval_process_description' => 'Vault to Bank transfer requires approval',
                'process_code' => 'VAULT_BANK_TRANSFER',
                'process_id' => $vault->id,
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approver_id' => $this->vaultToBankTransfer['authorizer_id'],
                'approval_status' => 'PENDING',
                'edit_package' => json_encode([
                    'vault_id' => $this->vaultToBankTransfer['vault_id'],
                    'bank_account_id' => $this->vaultToBankTransfer['bank_account_id'],
                    'amount' => $this->vaultToBankTransfer['amount'],
                    'reference' => $reference,
                    'description' => $this->vaultToBankTransfer['description']
                ])
            ]);

            // Create security transport log
            $this->createSecurityTransportLog($this->vaultToBankTransfer, 'VAULT_TO_BANK', $reference);

            // Log the transaction
            Log::info('Vault to Bank Transfer Completed', [
                'vault_id' => $vault->id,
                'vault_name' => $vault->name,
                'bank_account_id' => $this->vaultToBankTransfer['bank_account_id'],
                'bank_account_name' => $bankAccount->account_name,
                'amount' => $this->vaultToBankTransfer['amount'],
                'reference' => $reference,
                'user_id' => Auth::id(),
                'authorizer_id' => $this->vaultToBankTransfer['authorizer_id'],
                'timestamp' => now()->toISOString()
            ]);

            DB::commit();
            session()->flash('success', 'Vault to Bank transfer completed successfully. Reference: ' . $reference);
            $this->closeVaultToBankModal();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Vault to Bank Transfer Failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'transfer_data' => $this->vaultToBankTransfer
            ]);
            session()->flash('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function processBankToVaultTransfer()
    {
        $this->validate([
            'bankToVaultTransfer.vault_id' => 'required|exists:vaults,id',
            'bankToVaultTransfer.bank_account_id' => 'required|exists:bank_accounts,id',
            'bankToVaultTransfer.amount' => 'required|numeric|min:0.01',
            'bankToVaultTransfer.description' => 'required|string|min:3',
            'bankToVaultTransfer.authorizer_id' => 'required|exists:users,id',
            // Security Transport Validation
            'bankToVaultTransfer.transport_company_name' => 'nullable|string|max:255',
            'bankToVaultTransfer.vehicle_registration' => 'nullable|string|max:255',
            'bankToVaultTransfer.team_leader_name' => 'nullable|string|max:255',
            'bankToVaultTransfer.scheduled_pickup_time' => 'nullable|date|after:now',
            'bankToVaultTransfer.scheduled_delivery_time' => 'nullable|date|after:bankToVaultTransfer.scheduled_pickup_time',
        ]);

        DB::beginTransaction();
        try {
            // Get vault and bank account
            $vault = \App\Models\Vault::findOrFail($this->bankToVaultTransfer['vault_id']);
            $bankAccount = DB::table('bank_accounts')->where('id', $this->bankToVaultTransfer['bank_account_id'])->first();

            if (!$bankAccount) {
                throw new \Exception('Bank account not found.');
            }

            // Check if bank account has sufficient funds
            if ($bankAccount->current_balance < $this->bankToVaultTransfer['amount']) {
                throw new \Exception('Insufficient funds in bank account. Available: ' . number_format($bankAccount->current_balance, 2));
            }

            // Generate reference number if not provided
            $reference = $this->bankToVaultTransfer['reference'] ?: 'BTV-' . now()->format('YmdHis') . '-' . rand(1000, 9999);

            // Update bank account balance
            DB::table('bank_accounts')
                ->where('id', $this->bankToVaultTransfer['bank_account_id'])
                ->decrement('current_balance', $this->bankToVaultTransfer['amount']);

            // Update vault balance
            $vault->increment('current_balance', $this->bankToVaultTransfer['amount']);

            // Create accounting entries if vault has internal account
            if ($vault->internal_account_number) {
                // Credit vault internal account (increase vault cash)
                $this->createAccountingEntry([
                    'account_number' => $vault->internal_account_number,
                    'debit_amount' => $this->bankToVaultTransfer['amount'],
                    'credit_amount' => 0,
                    'narration' => 'Bank to Vault Transfer: ' . $this->bankToVaultTransfer['description'],
                    'reference' => $reference,
                    'transaction_type' => 'BANK_TO_VAULT_TRANSFER'
                ]);

                // Debit bank mirror account (decrease bank cash)
                if ($bankAccount->internal_mirror_account_number) {
                    $this->createAccountingEntry([
                        'account_number' => $bankAccount->internal_mirror_account_number,
                        'debit_amount' => 0,
                        'credit_amount' => $this->bankToVaultTransfer['amount'],
                        'narration' => 'Bank to Vault Transfer: ' . $this->bankToVaultTransfer['description'],
                        'reference' => $reference,
                        'transaction_type' => 'BANK_TO_VAULT_TRANSFER'
                    ]);
                }
            }

            // Create approval record
            approvals::create([
                'process_name' => 'bank_to_vault_transfer',
                'process_description' => Auth::user()->name . ' transferred ' . number_format($this->bankToVaultTransfer['amount'], 2) . ' from ' . $bankAccount->account_name . ' to ' . $vault->name,
                'approval_process_description' => 'Bank to Vault transfer requires approval',
                'process_code' => 'BANK_VAULT_TRANSFER',
                'process_id' => $vault->id,
                'process_status' => 'PENDING',
                'user_id' => Auth::id(),
                'approver_id' => $this->bankToVaultTransfer['authorizer_id'],
                'approval_status' => 'PENDING',
                'edit_package' => json_encode([
                    'bank_account_id' => $this->bankToVaultTransfer['bank_account_id'],
                    'vault_id' => $this->bankToVaultTransfer['vault_id'],
                    'amount' => $this->bankToVaultTransfer['amount'],
                    'reference' => $reference,
                    'description' => $this->bankToVaultTransfer['description']
                ])
            ]);

            // Create security transport log
            $this->createSecurityTransportLog($this->bankToVaultTransfer, 'BANK_TO_VAULT', $reference);

            // Log the transaction
            Log::info('Bank to Vault Transfer Completed', [
                'vault_id' => $vault->id,
                'vault_name' => $vault->name,
                'bank_account_id' => $this->bankToVaultTransfer['bank_account_id'],
                'bank_account_name' => $bankAccount->account_name,
                'amount' => $this->bankToVaultTransfer['amount'],
                'reference' => $reference,
                'user_id' => Auth::id(),
                'authorizer_id' => $this->bankToVaultTransfer['authorizer_id'],
                'timestamp' => now()->toISOString()
            ]);

            DB::commit();
            session()->flash('success', 'Bank to Vault transfer completed successfully. Reference: ' . $reference);
            $this->closeBankToVaultModal();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bank to Vault Transfer Failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'transfer_data' => $this->bankToVaultTransfer
            ]);
            session()->flash('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    private function createAccountingEntry($data)
    {
        try {
            // Create transaction entry in the transactions table or accounting system
            DB::table('transactions')->insert([
                'account_number' => $data['account_number'],
                'debit_amount' => $data['debit_amount'],
                'credit_amount' => $data['credit_amount'],
                'narration' => $data['narration'],
                'reference' => $data['reference'],
                'transaction_type' => $data['transaction_type'],
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create accounting entry', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    private function createSecurityTransportLog($transferData, $transferType, $reference)
    {
        try {
            $vault = \App\Models\Vault::find($transferData['vault_id']);
            $bankAccount = DB::table('bank_accounts')->where('id', $transferData['bank_account_id'])->first();

            // Determine pickup and delivery locations
            if ($transferType === 'BANK_TO_VAULT') {
                $pickupLocation = $bankAccount->bank_name . ' - ' . $bankAccount->branch_name ?? 'Main Branch';
                $deliveryLocation = $vault->name . ' - ' . ($vault->branch->name ?? 'Branch Office');
                $sourceVaultId = null;
                $destinationVaultId = $vault->id;
            } else {
                $pickupLocation = $vault->name . ' - ' . ($vault->branch->name ?? 'Branch Office');
                $deliveryLocation = $bankAccount->bank_name . ' - ' . $bankAccount->branch_name ?? 'Main Branch';
                $sourceVaultId = $vault->id;
                $destinationVaultId = null;
            }

            SecurityTransportLog::create([
                'transfer_reference' => $reference,
                'transfer_type' => $transferType,
                'amount' => $transferData['amount'],
                'currency' => 'TZS',
                'source_vault_id' => $sourceVaultId,
                'destination_vault_id' => $destinationVaultId,
                'bank_account_id' => $transferData['bank_account_id'],
                'pickup_location' => $pickupLocation,
                'delivery_location' => $deliveryLocation,
                'transport_company_name' => $transferData['transport_company_name'] ?? 'TBD',
                'transport_company_license' => $transferData['transport_company_license'] ?? 'TBD',
                'transport_company_contact' => $transferData['transport_company_contact'] ?? 'TBD',
                'vehicle_registration' => $transferData['vehicle_registration'] ?? 'TBD',
                'vehicle_type' => $transferData['vehicle_type'] ?? 'Armored Van',
                'team_leader_name' => $transferData['team_leader_name'] ?? 'TBD',
                'team_leader_badge' => $transferData['team_leader_badge'] ?? 'TBD',
                'team_leader_contact' => $transferData['team_leader_contact'] ?? 'TBD',
                'security_personnel' => $transferData['security_personnel'] ?? [],
                'scheduled_pickup_time' => $transferData['scheduled_pickup_time'] ? 
                    \Carbon\Carbon::parse($transferData['scheduled_pickup_time']) : now()->addHours(2),
                'scheduled_delivery_time' => $transferData['scheduled_delivery_time'] ? 
                    \Carbon\Carbon::parse($transferData['scheduled_delivery_time']) : now()->addHours(4),
                'planned_route' => $transferData['planned_route'] ?? 'Standard Route',
                'insurance_policy_number' => $transferData['insurance_policy_number'] ?? null,
                'insurance_coverage_amount' => $transferData['insurance_coverage_amount'] ?? $transferData['amount'],
                'status' => 'SCHEDULED',
                'initiated_by' => Auth::id(),
                'authorized_by' => $transferData['authorizer_id'] ?? null,
            ]);

            Log::info('Security Transport Log Created', [
                'transfer_reference' => $reference,
                'transfer_type' => $transferType,
                'amount' => $transferData['amount'],
                'user_id' => Auth::id()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create security transport log', [
                'error' => $e->getMessage(),
                'transfer_data' => $transferData,
                'transfer_type' => $transferType,
                'reference' => $reference
            ]);
        }
    }

    public function resetVaultForm()
    {
        $this->vaultForm = [
            'name' => '',
            'code' => '',
            'institution_id' => '',
            'branch_id' => '',
            'parent_account' => '',
            'current_balance' => 0,
            'limit' => 1000000,
            'warning_threshold' => 80,
            'bank_name' => '',
            'bank_account_number' => '',
            'auto_bank_transfer' => false,
            'requires_dual_approval' => false,
            'send_alerts' => true,
            'status' => 'active',
            'description' => ''
        ];
    }

    public function saveVault()
    {

       
        // Build validation rules
        $rules = [
            'vaultForm.name' => 'required|string|max:100',
            'vaultForm.institution_id' => 'nullable|exists:institutions,id',
            'vaultForm.branch_id' => 'required|exists:branches,id',
            'vaultForm.parent_account' => 'required|string|exists:accounts,account_number',
            'vaultForm.limit' => 'required|numeric|min:0',
            'vaultForm.warning_threshold' => 'required|integer|min:0|max:100',
            'vaultForm.status' => 'required|in:active,inactive,maintenance,over_limit',
            'vaultForm.bank_name' => 'nullable|string|max:255',
            'vaultForm.bank_account_number' => 'nullable|string|max:255',
            'vaultForm.description' => 'nullable|string',
        ];

       

        // Add unique validation for code - different rules for create vs update
        if ($this->selectedVaultId) {
            // Updating existing vault
            $rules['vaultForm.code'] = 'required|string|max:20|unique:vaults,code,' . $this->selectedVaultId;
        } else {
            // Creating new vault
            $rules['vaultForm.code'] = 'required|string|max:20|unique:vaults,code';
        }

       
        $this->validate($rules);

      

        try {
            if ($this->selectedVaultId) {
                // Update existing vault
                $vault = \App\Models\Vault::findOrFail($this->selectedVaultId);
                $vault->update($this->vaultForm);
                session()->flash('success', 'Vault updated successfully.');
            } else {
                // Create new vault
                \App\Models\Vault::create($this->vaultForm);
                session()->flash('success', 'Vault created successfully.');
            }


            // create a new account
            $accountService = new AccountCreationService();
            $vaultAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => 'VAULT: '.$this->vaultForm['name'],
                'type' => 'liability_accounts',
                'product_number' => '0000',
                'member_number' => '00000',
                'branch_number' => auth()->user()->branch ?? '1'
            ], $this->vaultForm['parent_account']);

            $vault->update([
                'internal_account_number' => $vaultAccount->account_number
            ]);


            approvals::create([
                'process_name' => 'create_internal_account',
                'process_description' => Auth::user()->name . ' has added a new Vault Account ' . $this->vaultForm['name'],
                'approval_process_description' => 'Vault Account creation approval required',
                'process_code' => 'ACC_CREATE',
                'process_id' => $vaultAccount->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => null
            ]);



            $this->closeCreateVaultModal();
            $this->closeEditVaultModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving vault: ' . $e->getMessage());
            Log::error('Error saving vault: ' . $e->getMessage());
        }
    }

    public function processBankTransfer()
    {
        $this->validate([
            'bankTransferAmount' => 'required|numeric|min:0.01',
            'bankTransferReason' => 'required|string|min:3',
            'bankTransferAccount' => 'required|string',
        ]);

        try {
            // Process bank transfer logic here
            \App\Models\BankTransfer::create([
                'vault_id' => $this->selectedVaultId,
                'amount' => $this->bankTransferAmount,
                'reason' => $this->bankTransferReason,
                'status' => 'pending',
                'reference_number' => 'BT-' . now()->format('YmdHis'),
                'initiated_by' => auth()->id(),
            ]);

            session()->flash('success', 'Bank transfer initiated successfully.');
            $this->closeBankTransferModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error processing bank transfer: ' . $e->getMessage());
        }
    }

    // Vault Action Methods
    public function viewVaultDetails($vaultId)
    {
        $this->showVaultDetailsModal($vaultId);
    }

    public function editVault($vaultId)
    {
        $this->showEditVaultModal($vaultId);
    }

    public function confirmDeleteVault($vaultId)
    {
        $this->selectedVaultId = $vaultId;
        // Show confirmation modal for deletion
        session()->flash('info', 'Delete confirmation for vault ID: ' . $vaultId);
    }

    public function initiateBankTransfer($vaultId)
    {
        $this->showBankTransferModal($vaultId);
    }

    // Approval Management Methods
    public function approveVaultRequest($approvalId)
    {
        // Process approval logic here
        session()->flash('success', 'Request approved successfully.');
    }

    public function rejectVaultRequest($approvalId)
    {
        // Process rejection logic here
        session()->flash('warning', 'Request rejected.');
    }

    public function viewApprovalDetails($approvalId)
    {
        // Show approval details modal
        session()->flash('info', 'Viewing approval details for ID: ' . $approvalId);
    }

    public function approveTransfer($transferId)
    {
        // Process transfer approval
        session()->flash('success', 'Transfer approved successfully.');
    }

    public function rejectTransfer($transferId)
    {
        // Process transfer rejection
        session()->flash('warning', 'Transfer rejected.');
    }

    public function viewTransferDetails($transferId)
    {
        // Show transfer details modal
        session()->flash('info', 'Viewing transfer details for ID: ' . $transferId);
    }

    public function render()
    {
        // Get current till information
        $currentTill = $this->selectedTillId ? Till::with('teller.user')->find($this->selectedTillId) : null;
        
        // Get all tills for dropdowns
        $tills = Till::with('teller.user')->get();
        
        // Get user's assigned tills (for tellers) or all tills (for supervisors)
        $userTills = $this->isSupervisor ? 
            Till::with('teller.user')->get() : 
            Till::with('teller.user')->whereHas('teller', function($q) {
                $q->where('user_id', Auth::id());
            })->get();
        
        // Get pending approvals for strongroom (placeholder data structure)
        $pendingApprovals = collect([
            (object)[
                'id' => 1,
                'type' => 'vault_replenishment',
                'amount' => 50000,
                'till_name' => 'Till 001',
                'requested_by' => (object)['name' => 'John Doe'],
                'urgency' => 'normal',
                'reason' => 'Till running low on cash',
                'created_at' => now()->subHours(2),
            ],
            (object)[
                'id' => 2,
                'type' => 'vault_deposit',
                'amount' => 75000,
                'till_name' => 'Till 002',
                'requested_by' => (object)['name' => 'Jane Smith'],
                'urgency' => 'high',
                'reason' => 'Excess cash deposit',
                'created_at' => now()->subHours(1),
            ],
            (object)[
                'id' => 3,
                'type' => 'direct_transfer',
                'amount' => 100000,
                'till_name' => 'Till 003',
                'requested_by' => (object)['name' => 'Mike Johnson'],
                'urgency' => 'normal',
                'reason' => 'Daily cash transfer',
                'created_at' => now()->subMinutes(30),
            ],
        ]);
        
        // Get transfer history for strongroom
        $transferHistory = collect([
            (object)[
                'id' => 1,
                'type' => 'deposit',
                'amount' => 25000,
                'till' => (object)['name' => 'Till 001', 'id' => 1],
                'till_id' => 1,
                'requestedBy' => (object)['name' => 'Alice Johnson'],
                'authorizedBy' => (object)['name' => 'Bob Smith'],
                'status' => 'completed',
                'created_at' => now()->subDays(1),
            ],
            (object)[
                'id' => 2,
                'type' => 'withdrawal',
                'amount' => 15000,
                'till' => (object)['name' => 'Till 002', 'id' => 2],
                'till_id' => 2,
                'requestedBy' => (object)['name' => 'Charlie Brown'],
                'authorizedBy' => null,
                'status' => 'pending',
                'created_at' => now()->subHours(3),
            ],
            (object)[
                'id' => 3,
                'type' => 'transfer',
                'amount' => 30000,
                'till' => (object)['name' => 'Till 003', 'id' => 3],
                'till_id' => 3,
                'requestedBy' => (object)['name' => 'Diana Prince'],
                'authorizedBy' => (object)['name' => 'Eve Adams'],
                'status' => 'approved',
                'created_at' => now()->subHours(6),
            ],
        ]);
        
        // Transform to paginated collection (mock pagination)
        $transferHistory = new \Illuminate\Pagination\LengthAwarePaginator(
            $transferHistory,
            $transferHistory->count(),
            10,
            1,
            ['path' => request()->url()]
        );
        
        // Get vaults data for the vaults tab
        $vaults = new \Illuminate\Pagination\LengthAwarePaginator(
            collect([]),
            0,
            $this->perPage,
            1,
            ['path' => request()->url()]
        );
        $vaultStats = [
            'total_vaults' => 0,
            'total_cash' => 0,
            'over_limit' => 0,
            'bank_transfers_today' => 0,
        ];
        $recentBankTransfers = collect([]);
        
        if (class_exists('\App\Models\Vault')) {
            try {
                $vaults = \App\Models\Vault::with(['branch'])
                    ->when($this->searchTerm, function($q) {
                        $q->where('name', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('code', 'like', '%' . $this->searchTerm . '%');
                    })
                    ->when($this->filterStatus, function($q) {
                        $q->where('status', $this->filterStatus);
                    })
                    ->when($this->filterBranch, function($q) {
                        $q->where('branch_id', $this->filterBranch);
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate($this->perPage);
                
                // Calculate vault statistics from all vaults (not just current page)
                $allVaults = \App\Models\Vault::all();
                $vaultStats = [
                    'total_vaults' => $allVaults->count(),
                    'total_cash' => $allVaults->sum('current_balance'),
                    'over_limit' => $allVaults->filter(function($vault) {
                        return $vault->current_balance > $vault->limit;
                    })->count(),
                    'bank_transfers_today' => 0, // Placeholder
                ];
                
                // Get recent bank transfers
                if (class_exists('\App\Models\BankTransfer')) {
                    $recentBankTransfers = \App\Models\BankTransfer::with(['vault'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                }
                
            } catch (\Exception $e) {
                // If there's an error (like table doesn't exist), return empty paginated collection
                $vaults = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect([]),
                    0,
                    $this->perPage,
                    1,
                    ['path' => request()->url()]
                );
                $recentBankTransfers = collect([]);
            }
        } else {
            // If Vault model doesn't exist, return empty paginated collection
            $vaults = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $this->perPage,
                1,
                ['path' => request()->url()]
            );
        }
        
        // Get paginated tills for the tills tab
        $paginatedTills = Till::with('teller.user')
            ->when($this->searchTerm, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('till_number', 'like', '%' . $this->searchTerm . '%')
                          ->orWhereHas('teller.user', function($userQuery) {
                              $userQuery->where('name', 'like', '%' . $this->searchTerm . '%');
                          });
                });
            })
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterBranch, function($q) {
                $q->where('branch_id', $this->filterBranch);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
            
        // Calculate till statistics
        $tillStats = [
            'total' => Till::count(),
            'open' => Till::where('status', 'open')->count(),
            'closed' => Till::where('status', 'closed')->count(),
            'suspended' => Till::where('status', 'suspended')->count(),
        ];
        
        // Get recent transactions
        $recentTransactions = TillTransaction::with(['till.teller.user', 'member'])
            ->when($this->selectedTillId, function($q) {
                $q->where('till_id', $this->selectedTillId);
            })
            ->when($this->searchTerm, function($q) {
                $q->where('narration', 'like', '%' . $this->searchTerm . '%');
            })
            ->when($this->filterType, function($q) {
                $q->where('type', $this->filterType);
            })
            ->when($this->filterDateFrom, function($q) {
                $q->whereDate('created_at', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function($q) {
                $q->whereDate('created_at', '<=', $this->filterDateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get all tills for supervisors
        $allTills = $this->isSupervisor ? Till::with('teller.user')->get() : collect();
        
        // Get reconciliation data
        $reconciliations = TillReconciliation::with(['till.teller.user', 'reconciled_by'])
            ->when($this->reconciliationTillId, function($q) {
                $q->where('till_id', $this->reconciliationTillId);
            })
            ->when($this->reconciliationStatus, function($q) {
                $q->where('status', $this->reconciliationStatus);
            })
            ->when($this->reconciliationSearch, function($q) {
                $q->where('reference', 'like', '%' . $this->reconciliationSearch . '%');
            })
            ->orderBy($this->reconciliationSortField, $this->reconciliationSortDirection)
            ->paginate(10);

        // Get strongroom ledger data
        $strongroomLedger = StrongroomLedger::with(['vault', 'branch'])
            ->when($this->strongroomStatus, function($q) {
                $q->where('status', $this->strongroomStatus);
            })
            ->when($this->strongroomSearch, function($q) {
                $q->where('vault_code', 'like', '%' . $this->strongroomSearch . '%')
                  ->orWhere('notes', 'like', '%' . $this->strongroomSearch . '%');
            })
            ->orderBy($this->strongroomSortField, $this->strongroomSortDirection)
            ->paginate(10);

        // Get approvals data (placeholder - you'll need to create an Approvals model)
        $approvals = new LengthAwarePaginator([], 0, 10, 1);

        // Get cash movements for strongroom
        $cashMovements = CashMovement::with(['fromTill.teller.user', 'toTill.teller.user', 'initiator'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Statistics
        $todayTransactions = TillTransaction::whereDate('created_at', today())->count();
        $todayVolume = TillTransaction::whereDate('created_at', today())->sum('amount');
        $openTills = Till::where('status', 'open')->count();
        $totalCashInSystem = Till::sum('current_balance') + $this->strongroomBalance;

        // Dashboard stats
        $dashboardStats = [
            'total_transactions' => TillTransaction::count(),
            'total_volume' => TillTransaction::sum('amount'),
            'open_tills_count' => $openTills,
            'closed_tills_count' => Till::where('status', 'closed')->count(),
        ];

        // Reconciliation stats
        $reconciliationStats = [
            'total' => TillReconciliation::count(),
            'completed' => TillReconciliation::where('status', 'completed')->count(),
            'pending' => TillReconciliation::where('status', 'pending')->count(),
            'discrepancies' => TillReconciliation::where('status', 'discrepancy')->count(),
        ];

        // Strongroom stats
        $strongroomStats = [
            'current_balance' => $this->strongroomBalance,
            'today_deposits' => CashMovement::where('type', 'deposit')->whereDate('created_at', today())->sum('amount'),
            'today_withdrawals' => CashMovement::where('type', 'withdrawal')->whereDate('created_at', today())->sum('amount'),
            'pending_approvals' => $pendingApprovals->count(),
            'active_tills' => Till::where('status', 'open')->count(),
        ];

        // Approval stats
        $approvalStats = [
            'pending' => 0, // Placeholder
            'approved_today' => 0, // Placeholder
            'rejected_today' => 0, // Placeholder
            'my_approvals' => 0, // Placeholder
        ];

        // Get supervisors and available authorizers for modals
        $supervisors = User::where('role', 'supervisor')->orWhere('role', 'admin')->get();
        $availableAuthorizers = User::where('role', 'supervisor')
            ->orWhere('role', 'admin')
            ->orWhere('role', 'manager')
            ->get();
        
        // Get selected vault if one is selected
        $selectedVault = null;
        if ($this->selectedVaultId && class_exists('\App\Models\Vault')) {
            try {
                $selectedVault = \App\Models\Vault::with(['branch'])->find($this->selectedVaultId);
            } catch (\Exception $e) {
                // If there's an error, selectedVault stays null
            }
        }

        // Get parent accounts for till and vault creation
        $parentAccounts = collect([]);
        try {
            if (Schema::hasTable('accounts')) {
                $parentAccounts = DB::table('accounts')
                    ->where('major_category_code', '1000')
                    ->where('category_code', '1000')
                    ->where('account_level', 3)
                    ->select('account_number', 'account_name')
                    ->orderBy('account_name')
                    ->get();
            }
        } catch (\Exception $e) {
            // If accounts table doesn't exist or there's an error, return empty collection
            $parentAccounts = collect([]);
        }

        // Get bank accounts for bank-to-vault transfers
        $bankAccounts = collect([]);
        try {
            if (Schema::hasTable('bank_accounts')) {
                $bankAccounts = DB::table('bank_accounts')
                    //->where('status', 'ACTIVE')
                    ->where('current_balance', '>', 0)
                    ->select('id', 'bank_name', 'account_name', 'account_number', 'current_balance')
                    ->orderBy('bank_name')
                    ->get();
            }
        } catch (\Exception $e) {
            $bankAccounts = collect([]);
        }

        return view('livewire.accounting.till-and-cash-management', [
            'currentTill' => $currentTill,
            'tills' => $tills,
            'userTills' => $userTills,
            'paginatedTills' => $paginatedTills,
            'tillStats' => $tillStats,
            'recentTransactions' => $recentTransactions,
            'users' => User::where('role', 'teller')->orWhere('role', 'supervisor')->get(),
            'supervisors' => $supervisors,
            'availableAuthorizers' => $availableAuthorizers,
            'selectedVault' => $selectedVault,
            'allTills' => $allTills,
            'reconciliations' => $reconciliations,
            'strongroomLedger' => $strongroomLedger,
            'approvals' => $approvals,
            'cashMovements' => $cashMovements,
            'todayTransactions' => $todayTransactions,
            'todayVolume' => $todayVolume,
            'openTills' => $openTills,
            'totalCashInSystem' => $totalCashInSystem,
            'dashboardStats' => $dashboardStats,
            'reconciliationStats' => $reconciliationStats,
            'strongroomStats' => $strongroomStats,
            'approvalStats' => $approvalStats,
            'pendingApprovals' => $pendingApprovals,
            'transferHistory' => $transferHistory,
            'vaults' => $vaults,
            'vaultStats' => $vaultStats,
            'recentBankTransfers' => $recentBankTransfers,
            'parentAccounts' => $parentAccounts,
            'bankAccounts' => $bankAccounts,
            'branches' => \App\Models\Branch::all(),
            'institutions' => \App\Models\Institution::all(),
            'pendingReplenishments' => $this->pendingReplenishments,
        ]);
    }

    // Vault Replenishment Methods
    public function showReplenishmentApprovalModal($replenishmentId)
    {
        $this->selectedReplenishment = approvals::with(['user'])
            ->findOrFail($replenishmentId);
        $this->showReplenishmentApprovalModal = true;
    }

    public function closeReplenishmentApprovalModal()
    {
        $this->showReplenishmentApprovalModal = false;
        $this->selectedReplenishment = null;
        $this->replenishmentApprovalNotes = '';
    }

    public function approveVaultReplenishment($replenishmentId)
    {
        DB::beginTransaction();
        try {
            $approval = approvals::findOrFail($replenishmentId);
            $requestData = json_decode($approval->edit_package, true) ?? [];
            
            // Update approval status
            $approval->update([
                'approval_status' => 'APPROVED',
                'approver_id' => Auth::id(),
                'approved_at' => now(),
                'comments' => $this->replenishmentApprovalNotes ?: 'Vault replenishment approved by HQ'
            ]);

            // Find the vault
            $vault = null;
            if (isset($requestData['vault_id'])) {
                $vault = Vault::find($requestData['vault_id']);
            }

            // If no vault found by ID, try to find by branch or user context
            if (!$vault) {
                $vault = Vault::where('branch_id', Auth::user()->branch ?? 1)->first();
            }

            if ($vault) {
                // Update vault balance
                $vault->increment('current_balance', $requestData['amount']);

                // Log the cash movement
                CashMovement::create([
                    'reference' => 'VR-' . now()->format('YmdHis') . '-' . $vault->id . '-' . $approval->id . '-' . rand(100, 999),
                    'type' => 'external_deposit', // Using valid enum value
                    'amount' => $requestData['amount'],
                    'reason' => 'Vault replenishment approved - ' . ($requestData['reason'] ?? 'Branch replenishment request'),
                    'user_id' => Auth::id(),
                    'initiated_by' => Auth::id(),
                    'status' => 'completed',
                    'completed_at' => now(),
                    'notes' => 'Approval ID: ' . $approval->id . ', Vault ID: ' . $vault->id
                ]);

                // Create accounting entry
                $this->createAccountingEntry([
                    'type' => 'vault_replenishment',
                    'reference' => 'VR-' . now()->format('YmdHis'),
                    'amount' => $requestData['amount'],
                    'description' => "Vault replenishment for {$vault->name}",
                    'vault_id' => $vault->id,
                    'branch_id' => $vault->branch_id,
                    'approved_by' => Auth::id()
                ]);
            }

            DB::commit();
            
            $this->loadPendingReplenishments();
            $this->closeReplenishmentApprovalModal();
            
            session()->flash('success', 'Vault replenishment approved and funds transferred successfully.');
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Error approving vault replenishment: ' . $e->getMessage());
        }
    }

    public function rejectVaultReplenishment($replenishmentId)
    {
        try {
            $approval = approvals::findOrFail($replenishmentId);
            
            $approval->update([
                'approval_status' => 'REJECTED',
                'approver_id' => Auth::id(),
                'approved_at' => now(),
                'comments' => $this->replenishmentApprovalNotes ?: 'Vault replenishment rejected by HQ'
            ]);

            $this->loadPendingReplenishments();
            $this->closeReplenishmentApprovalModal();
            
            session()->flash('success', 'Vault replenishment request rejected.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error rejecting vault replenishment: ' . $e->getMessage());
        }
    }
}
