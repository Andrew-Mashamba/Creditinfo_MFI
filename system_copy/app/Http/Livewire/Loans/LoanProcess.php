<?php

namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use App\Models\LoansModel;
use App\Models\approvals;
use App\Services\LoanTabStateService;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Services\TransactionPostingService;
use App\Services\AccountCreationService;
use App\Models\AccountsModel;
use App\Services\BillingService;
use App\Services\PaymentLinkService;
use App\Jobs\ProcessMemberNotifications;
use App\Models\loans_schedules;

class LoanProcess extends Component
{
    use WithFileUploads;
    
    public $activeTab = 'client';
    public $loan;
    public $client;
    public $loanStatus;
    public $progressData = [];
    public $isLoading = false;
    public $errorMessage = '';
    public $successMessage = '';
    public $approvalComment = '';
    public $showApprovalModal = false;
    public $policyAdherenceConfirmed = false;
    
    // Committee minutes handling
    public $committeeMinutesFile;
    public $committeeMinutesPath;

    // Disbursement handling
    public $selectedBankAccount;

    // Tab configuration - removed 'completed' property since we'll get it from DB
    protected $tabs = [
        'client' => [
            'label' => 'Client Information',
            'icon' => 'user',
            'required' => true
        ],
        'assessment' => [
            'label' => 'Affordability',
            'icon' => 'calculator',
            'required' => true
        ],
        'guarantor' => [
            'label' => 'Guarantor And Pledged Collateral',
            'icon' => 'shield-check',
            'required' => true
        ],
        'addDocument' => [
            'label' => 'Documents',
            'icon' => 'document',
            'required' => true
        ],
        'accounting' => [
            'label' => 'Accounting',
            'icon' => 'calculator',
            'required' => false
        ],
  
    ];

    protected $listeners = [
        'refreshLoanProcess' => '$refresh',
        'tabCompleted' => 'markTabAsCompleted',
        'showError' => 'showErrorMessage',
        'showSuccess' => 'showSuccessMessage'
    ];

    protected $tabStateService;

    public function boot(LoanTabStateService $tabStateService)
    {
        $this->tabStateService = $tabStateService;
    }

    public function mount()
    {
        $this->loadLoanData();
        $this->calculateProgress();
        $this->restoreApprovalState();
        
        // Set default approval comment if not already set
        if (empty($this->approvalComment)) {
            $this->approvalComment = 'Approved';
        }
    }

    /**
     * Get the tab state service with fallback
     */
    protected function getTabStateService()
    {
        if (!$this->tabStateService) {
            try {
                $this->tabStateService = app(LoanTabStateService::class);
            } catch (\Exception $e) {
                Log::error('Error resolving LoanTabStateService: ' . $e->getMessage());
                return null;
            }
        }
        return $this->tabStateService;
    }

    public function loadLoanData()
    {
        try {
            $loanId = Session::get('currentloanID');
            $clientNumber = Session::get('currentloanClient');

            if ($loanId) {
                // Use fresh() to ensure we get the latest data from database
                $this->loan = LoansModel::find($loanId);
                if ($this->loan) {
                    $this->loan = $this->loan->fresh(); // Force refresh from database
                    $this->client = ClientsModel::where('client_number', $this->loan->client_number)->first();
                    $this->loanStatus = $this->loan->status ?? 'DRAFT';
                    
                    // Log current loan state for debugging
                    Log::info('Loan data loaded', [
                        'loan_id' => $this->loan->id,
                        'status' => $this->loan->status,
                        'approval_stage' => $this->loan->approval_stage,
                        'has_exceptions' => $this->loan->has_exceptions
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error loading loan data: ' . $e->getMessage());
            $this->errorMessage = 'Error loading loan data. Please try again.';
        }
    }

    /**
     * Get tab completion status directly from database
     * This checks both data-based completion and manually marked completion
     */
    public function isTabCompleted($tabName)
    {
        if (!$this->loan) {
            return false;
        }

        try {
            $service = $this->getTabStateService();
            if (!$service) {
                return false;
            }

            // Check if tab is manually marked as completed
            $manuallyCompleted = $service->isTabManuallyCompleted($this->loan->id, $tabName);
            
            // Check if tab is completed based on actual data
            $dataCompleted = $service->isTabCompleted($this->loan->id, $tabName);
            
            // Return true if either condition is met
            return $manuallyCompleted || $dataCompleted;
        } catch (\Exception $e) {
            Log::error('Error checking tab completion for ' . $tabName . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all tab completion statuses from database
     */
    public function getAllTabStatuses()
    {
        if (!$this->loan) {
            return [];
        }

        try {
            $service = $this->getTabStateService();
            if (!$service) {
                return [];
            }

            return $service->getAllTabStatus($this->loan->id);
        } catch (\Exception $e) {
            Log::error('Error getting all tab statuses: ' . $e->getMessage());
            return [];
        }
    }

    public function showTab($tabName)
    {
        // Allow Credit Score, Lending Framework, and Approvals tabs even if not in $tabs array
        $allowedExtraTabs = ['creditScore', 'lendingFramework', 'approvals'];
        
        if (isset($this->tabs[$tabName]) || in_array($tabName, $allowedExtraTabs)) {
            // Auto-mark client tab as completed when navigating away from it
            if ($this->activeTab === 'client' && $tabName !== 'client') {
                $this->markTabAsCompleted('client');
            }
            
            $this->activeTab = $tabName;
        }
    }

    public function markTabAsCompleted($tabName)
    {
        if (!isset($this->tabs[$tabName]) || !$this->loan) {
            return;
        }

        try {
            $service = $this->getTabStateService();
            if (!$service) {
                return;
            }

            // Get current completed tabs from database
            $completedTabs = $service->getCompletedTabs($this->loan->id);
            
            // Add the new completed tab if not already present
            if (!in_array($tabName, $completedTabs)) {
                $completedTabs[] = $tabName;
                
                Log::info('Marking tab as completed', [
                    'loanId' => $this->loan->id,
                    'tabName' => $tabName,
                    'completedTabs' => $completedTabs
                ]);
            }

            // Save updated completion status to database
            $service->saveTabCompletionStatus($this->loan->id, $completedTabs);
            
            $this->calculateProgress();
            $this->showSuccessMessage("Tab '{$this->tabs[$tabName]['label']}' marked as completed!");
            
        } catch (\Exception $e) {
            Log::error('Error marking tab as completed: ' . $e->getMessage());
            $this->showErrorMessage('Error marking tab as completed. Please try again.');
        }
    }

    public function calculateProgress()
    {
        if (!$this->loan) {
            $this->progressData = [
                'total' => count($this->tabs),
                'completed' => 0,
                'percentage' => 0
            ];
            return;
        }

        try {
            $service = $this->getTabStateService();
            if (!$service) {
                $this->progressData = [
                    'total' => count($this->tabs),
                    'completed' => 0,
                    'percentage' => 0
                ];
                return;
            }

            $completedTabs = $service->getCompletedTabs($this->loan->id);
            $totalTabs = count($this->tabs);
            $completedCount = count($completedTabs);

            $this->progressData = [
                'total' => $totalTabs,
                'completed' => $completedCount,
                'percentage' => $totalTabs > 0 ? round(($completedCount / $totalTabs) * 100, 1) : 0
            ];

            Log::info('Progress calculated', [
                'loanId' => $this->loan->id,
                'completedTabs' => $completedTabs,
                'totalTabs' => $totalTabs,
                'completedCount' => $completedCount,
                'percentage' => $this->progressData['percentage']
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating progress: ' . $e->getMessage());
            $this->progressData = [
                'total' => count($this->tabs),
                'completed' => 0,
                'percentage' => 0
            ];
        }
    }

    public function getTabStatus($tabName)
    {
        // Allow Credit Score and Lending Framework tabs
        $allowedExtraTabs = ['creditScore', 'lendingFramework'];
        
        if (!isset($this->tabs[$tabName]) && !in_array($tabName, $allowedExtraTabs)) {
            return 'inactive';
        }

        if ($this->activeTab === $tabName) {
            return 'active';
        }

        // Extra tabs don't have completion status
        if (in_array($tabName, $allowedExtraTabs)) {
            return 'incomplete';
        }

        // Get completion status directly from database for regular tabs
        return $this->isTabCompleted($tabName) ? 'completed' : 'incomplete';
    }

    public function areAllTabsCompleted()
    {
        if (!$this->loan) {
            return false;
        }

        try {
            $service = $this->getTabStateService();
            if (!$service) {
                return false;
            }
            return $service->areAllTabsCompleted($this->loan->id);
        } catch (\Exception $e) {
            Log::error('Error checking if all tabs are completed: ' . $e->getMessage());
            return false;
        }
    }

    public function getProgressPercentage()
    {
        return $this->progressData['percentage'] ?? 0;
    }

    public function hasCollaterals()
    {
        if (!$this->loan) {
            return false;
        }
        
        try {
            $collateralCount = DB::table('collaterals')
                ->where('loan_id', $this->loan->id)
                ->count();
                
            return $collateralCount > 0;
        } catch (\Exception $e) {
            Log::error('Error checking collaterals: ' . $e->getMessage());
            return false;
        }
    }

    public function sendForApproval()
    {
        try {
            if (!$this->areAllTabsCompleted()) {
                $this->showErrorMessage('All tabs must be completed before sending for approval.');
                return;
            }

            // Update loan status to pending approval
            $this->loan->update(['status' => 'PENDING_APPROVAL']);
            
            $this->showSuccessMessage('Loan application sent for approval successfully!');
            
            // Emit event to refresh the loan table
            $this->emit('refreshLoanTable');
            
        } catch (\Exception $e) {
            Log::error('Error sending for approval: ' . $e->getMessage());
            $this->showErrorMessage('Error sending for approval. Please try again.');
        }
    }

    public function showErrorMessage($message)
    {
        $this->errorMessage = $message;
        $this->dispatchBrowserEvent('show-error', ['message' => $message]);
    }

    public function showSuccessMessage($message)
    {
        $this->successMessage = $message;
        $this->dispatchBrowserEvent('show-success', ['message' => $message]);
    }

    public function close()
    {
        try {
            Session::forget(['currentloanID', 'currentloanClient', 'LoanStage']);
            $this->emit('viewLoanStages');
            $this->showSuccessMessage('Loan process closed successfully.');
        } catch (\Exception $e) {
            Log::error('Error closing loan process: ' . $e->getMessage());
            $this->showErrorMessage('Error closing loan process.');
        }
    }

    public function render()
    {
        // Calculate progress on every render to ensure fresh data
        $this->calculateProgress();
        
        return view('livewire.loans.loan-process', [
            'tabs' => $this->tabs,
            'progressData' => $this->progressData
        ]);
    }
    
    /**
     * Move loan to a specific stage
     */
    public function moveToStage($stage)
    {
        try {
            if (!$this->loan) {
                $this->showErrorMessage('No loan found to update.');
                return;
            }
            
            // Get current stage before moving
            $currentStage = $this->loan->approval_stage ?? 'Inputter';
            
            // Determine if this is a backward movement (Return to Sender)
            $isBackwardMovement = $this->isBackwardMovement($currentStage, $stage);
            
            // If moving backward and there's an approval record, reverse the updates
            if ($isBackwardMovement) {
                $this->reverseApprovalTableUpdates($currentStage, $stage);
            }
            
            // Update the loan's approval stage
            $this->loan->approval_stage = $stage;
            
            // Get and set the role names for the new stage
            $stageRoles = $this->getStageRoles($stage);
            $roleNames = count($stageRoles) > 1 ? 'Loan Committee' : implode(', ', $stageRoles);
            $this->loan->approval_stage_role_name = $roleNames;
            
            // Log the stage update for debugging
            Log::info('Updating loan approval stage', [
                'loan_id' => $this->loan->id,
                'old_stage' => $currentStage,
                'new_stage' => $stage,
                'role_names' => $roleNames,
                'is_backward' => $isBackwardMovement
            ]);
            
            // Update status based on stage movement
            if ($stage === 'Exception') {
                $this->loan->status = 'PENDING-EXCEPTIONS';
            } elseif ($stage === 'Approver' && $this->loan->status !== 'APPROVED' && $this->loan->status !== 'REJECTED') {
                $this->loan->status = 'PENDING_APPROVAL';
            } elseif ($this->loan->status === 'PENDING-EXCEPTIONS' && $stage !== 'Exception') {
                // Moving out of exception stage
                $this->loan->status = 'PENDING';
            } elseif ($isBackwardMovement) {
                // When moving backward, set status to RETURNED
                $this->loan->status = 'RETURNED';
            }
            
            $this->loan->save();
            
            // Refresh the loan data to ensure we have the latest state
            $this->loan->refresh();
            
            // Log the stage transition
            DB::table('loan_approval_logs')->insert([
                'loan_id' => $this->loan->id,
                'stage' => $stage,
                'action' => $isBackwardMovement ? 'RETURNED_TO_STAGE' : 'MOVED_TO_STAGE',
                'comment' => $this->approvalComment ?: ($isBackwardMovement ? 'Returned to previous stage' : 'Stage transition'),
                'performed_by' => auth()->user()->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->approvalComment = '';
            $this->showSuccessMessage("Loan moved to {$stage} stage successfully.");
            $this->loadLoanData(); // Reload loan data to reflect changes
            $this->emit('refreshLoanProcess');
            
        } catch (\Exception $e) {
            Log::error('Error moving loan to stage: ' . $e->getMessage());
            $this->showErrorMessage('Error updating loan stage. Please try again.');
        }
    }
    
    /**
     * Clear exceptions and move to next stage
     */
    public function clearExceptions()
    {
        try {
            if (!$this->loan) {
                $this->showErrorMessage('No loan found to update.');
                return;
            }
            
            // Move from Exception to Inputter
            $this->loan->approval_stage = 'Inputter';
            $this->loan->approval_stage_role_name = 'Loan Officer'; // Set role name for Inputter stage
            $this->loan->status = 'PENDING';
            $this->loan->save();
            
            // Refresh the loan data to ensure we have the latest state
            $this->loan->refresh();
            
            // Log the action
            DB::table('loan_approval_logs')->insert([
                'loan_id' => $this->loan->id,
                'stage' => 'Inputter',
                'action' => 'EXCEPTIONS_CLEARED',
                'comment' => $this->approvalComment ?: 'Exceptions cleared, loan moved to approval workflow',
                'performed_by' => auth()->user()->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->approvalComment = '';
            $this->showSuccessMessage('Exceptions cleared successfully. Loan moved to Inputter stage.');
            $this->loadLoanData(); // Reload loan data to reflect changes
            $this->emit('refreshLoanProcess');
            
        } catch (\Exception $e) {
            Log::error('Error clearing exceptions: ' . $e->getMessage());
            $this->showErrorMessage('Error clearing exceptions. Please try again.');
        }
    }
    
    /**
     * Return loan for correction
     */
    public function returnForCorrection()
    {
        try {
            if (!$this->loan) {
                $this->showErrorMessage('No loan found to update.');
                return;
            }
            
            $this->loan->status = 'RETURNED';
            $this->loan->save();
            
            // Log the action
            DB::table('loan_approval_logs')->insert([
                'loan_id' => $this->loan->id,
                'stage' => $this->loan->approval_stage,
                'action' => 'RETURNED_FOR_CORRECTION',
                'comment' => $this->approvalComment ?: 'Loan returned to applicant for corrections',
                'performed_by' => auth()->user()->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->approvalComment = '';
            $this->showSuccessMessage('Loan returned to applicant for corrections.');
            $this->emit('refreshLoanProcess');
            
        } catch (\Exception $e) {
            Log::error('Error returning loan: ' . $e->getMessage());
            $this->showErrorMessage('Error returning loan. Please try again.');
        }
    }
    
    /**
     * Approve the loan
     */
    public function approveLoan()
    {
        try {
            if (!$this->loan) {
                $this->showErrorMessage('No loan found to approve.');
                return;
            }
            
            if ($this->loan->approval_stage !== 'Approver') {
                $this->showErrorMessage('Loan must be at Approver stage to be approved.');
                return;
            }

            // Update loan status to APPROVED and move to FINANCE stage
            $this->loan->status = 'APPROVED';
            $this->loan->approval_stage = 'FINANCE'; // Move to FINANCE stage after approval
            $this->loan->approval_stage_role_name = 'Finance'; // Set role name for FINANCE stage
            $this->loan->approved_at = now();
            $this->loan->approved_by = auth()->user()->id ?? null;
            $this->loan->save();
            
            // Log the action
            DB::table('loan_approval_logs')->insert([
                'loan_id' => $this->loan->id,
                'stage' => 'Approver',
                'action' => 'APPROVED',
                'comment' => $this->approvalComment ?: 'Loan approved',
                'performed_by' => auth()->user()->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Refresh the loan to ensure we have the latest state
            $this->loan->refresh();
            
            $this->approvalComment = '';
            $this->showSuccessMessage('Loan approved successfully!');
            $this->loadLoanData(); // Reload loan data to reflect new status
            $this->emit('refreshLoanProcess');
            
        } catch (\Exception $e) {
            Log::error('Error approving loan: ' . $e->getMessage());
            $this->showErrorMessage('Error approving loan. Please try again.');
        }
    }
    
    /**
     * Reject the loan
     */
    public function rejectLoan()
    {
        try {
            if (!$this->loan) {
                $this->showErrorMessage('No loan found to reject.');
                return;
            }
            
            $this->loan->status = 'REJECTED';
            $this->loan->rejected_at = now();
            $this->loan->rejected_by = auth()->user()->id ?? null;
            $this->loan->rejection_reason = $this->approvalComment;
            $this->loan->save();
            
            // Log the action
            DB::table('loan_approval_logs')->insert([
                'loan_id' => $this->loan->id,
                'stage' => $this->loan->approval_stage,
                'action' => 'REJECTED',
                'comment' => $this->approvalComment ?: 'Loan rejected',
                'performed_by' => auth()->user()->id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->approvalComment = '';
            $this->showSuccessMessage('Loan rejected.');
            $this->loadLoanData(); // Reload loan data to reflect new status
            $this->emit('refreshLoanProcess');
            
        } catch (\Exception $e) {
            Log::error('Error rejecting loan: ' . $e->getMessage());
            $this->showErrorMessage('Error rejecting loan. Please try again.');
        }
    }
    
    /**
     * Decline the loan (same as reject)
     */
    public function declineLoan()
    {
        $this->rejectLoan();
    }
    
    /**
     * Show approval confirmation modal
     */
    public function showApprovalModal()
    {

       // dd('showApprovalModal');
        try {
            if (!$this->loan) {
                $this->showErrorMessage('No loan found.');
                return;
            }
            
            if ($this->loan->approval_stage !== 'Approver') {
                $this->showErrorMessage('Loan must be at Approver stage to be approved.');
                return;
            }
            
            // Pre-fill the approval comment
            $this->approvalComment = 'Approved';
            $this->policyAdherenceConfirmed = false;
            $this->showApprovalModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error showing approval modal: ' . $e->getMessage());
            $this->showErrorMessage('Error showing approval modal. Please try again.');
        }
    }
    
    /**
     * Close approval confirmation modal
     */
    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->policyAdherenceConfirmed = false;
        $this->approvalComment = '';
    }
    
    /**
     * Confirm approval after policy adherence check
     */
    public function confirmApproval()
    {
        try {
            if (!$this->policyAdherenceConfirmed) {
                $this->showErrorMessage('You must confirm policy adherence before approving the loan.');
                return;
            }
            
            // Check if committee minutes are required
            if ($this->isLoanCommitteeStage()) {
                if (!$this->committeeMinutesPath) {
                    $this->showErrorMessage('Please upload committee minutes before approving.');
                    return;
                }
            }
            
            if (!$this->loan) {
                $this->showErrorMessage('No loan found to approve.');
                return;
            }
            
            if ($this->loan->approval_stage !== 'Approver') {
                $this->showErrorMessage('Loan must be at Approver stage to be approved.');
                return;
            }

            // Start database transaction for atomicity
            DB::beginTransaction();
            
            try {
                // Update loan status to APPROVED and move to FINANCE stage
                $this->loan->status = 'APPROVED';
                $this->loan->approval_stage = 'FINANCE'; // Move to FINANCE stage after approval
                $this->loan->approval_stage_role_name = 'Finance'; // Set role name for FINANCE stage
                $this->loan->approved_at = now();
                $this->loan->approved_by = auth()->user()->id ?? null;
                $this->loan->save();
                
                // Update the existing approval record to mark it as approved
                $this->updateApprovalRecordForFinalApproval();
                
                // Log the action with committee minutes reference
                DB::table('loan_approval_logs')->insert([
                    'loan_id' => $this->loan->id,
                    'stage' => 'Approver',
                    'action' => 'APPROVED',
                    'comment' => $this->approvalComment ?: 'Loan approved',
                    'performed_by' => auth()->user()->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Process disbursement using the saved accounting configuration
                $disbursementResult = $this->processDisbursementFromAccountingConfig();
                
                if ($disbursementResult['status'] === 'success') {
                    // Get accounting data to store deduction amounts
                    $accountingData = json_decode($this->loan->accounting_data, true);
                    $principal = floatval($this->loan->principle);
                    $charges = floatval($accountingData['charges'] ?? 0);
                    $insurance = floatval($accountingData['insurance'] ?? 0);
                    $firstInterest = floatval($accountingData['first_interest'] ?? 0);
                    $totalDeductions = $charges + $insurance + $firstInterest;
                    $netDisbursement = $principal - $totalDeductions;

                    // Mark loan as active (disbursed) and store deduction amounts
                    $this->loan->status = 'ACTIVE';
                    $this->loan->loan_status = 'ACTIVE';
                    $this->loan->disbursement_date = now();
                    $this->loan->total_deductions = $totalDeductions;
                    $this->loan->future_interest = $firstInterest; // Store first interest as future_interest
                    $this->loan->save();

                    Log::info('Loan disbursement amounts recorded', [
                        'loan_id' => $this->loan->id,
                        'principal' => $principal,
                        'charges' => $charges,
                        'insurance' => $insurance,
                        'first_interest' => $firstInterest,
                        'total_deductions' => $totalDeductions,
                        'net_disbursement' => $netDisbursement
                    ]);

                    // Create repayment schedule FIRST (before notifications)
                    $scheduleResult = $this->createLoanRepaymentSchedule();
                    if (!$scheduleResult['success']) {
                        Log::warning('Failed to create repayment schedule', [
                            'loan_id' => $this->loan->id,
                            'error' => $scheduleResult['message']
                        ]);
                    }
                    
                    // Generate control number and send notifications (after schedule is created)
                    $this->generateControlNumberAndNotifications();
                    
                    // Commit transaction
                    DB::commit();
                    
                    // Close modal and reset
                    $this->closeApprovalModal();
                    $this->showSuccessMessage('Loan approved and disbursed successfully! Notification sent to member.');
                } else {
                    // Rollback on disbursement failure
                    DB::rollback();
                    // Show detailed error message to user
                    $errorMessage = $disbursementResult['message'] ?? 'Unknown error occurred during disbursement';
                    $this->showErrorMessage('Loan approved but disbursement failed: ' . $errorMessage);
                    Log::error('Disbursement failed after approval', [
                        'loan_id' => $this->loan->id,
                        'error' => $errorMessage,
                        'full_result' => $disbursementResult
                    ]);
                }
                
                $this->loadLoanData(); // Reload loan data to reflect new status
                $this->emit('refreshLoanProcess');
                
            } catch (\Exception $disbursementException) {
                // Rollback transaction on any error
                DB::rollback();
                Log::error('Error during loan approval and disbursement: ' . $disbursementException->getMessage());
                $this->showErrorMessage('Error processing loan approval and disbursement. Please try again.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error confirming approval: ' . $e->getMessage());
            $this->showErrorMessage('Error approving loan. Please try again.');
        }
    }

    /**
     * Disburse an approved loan manually
     * This is called from the "Disburse Loan" button after approval
     */
    public function disburseLoan()
    {
        try {
            Log::info('Manual disbursement initiated', [
                'loan_id' => $this->loan->id ?? null,
                'user_id' => auth()->id(),
                'selected_bank_account' => $this->selectedBankAccount
            ]);

            // Validate loan exists
            if (!$this->loan) {
                $this->showErrorMessage('No loan found to disburse.');
                return;
            }

            // Validate loan is approved
            if ($this->loan->status !== 'APPROVED') {
                $this->showErrorMessage('Only approved loans can be disbursed. Current status: ' . $this->loan->status);
                return;
            }

            // Validate bank account is selected
            if (!$this->selectedBankAccount) {
                $this->showErrorMessage('Please select a disbursement bank account.');
                return;
            }

            // Check if loan is already disbursed
            if ($this->loan->status === 'ACTIVE' || $this->loan->disbursement_date) {
                $this->showErrorMessage('This loan has already been disbursed.');
                return;
            }

            // Validate sufficient funds in selected bank account
            $bankAccount = DB::table('bank_accounts')
                ->where('internal_mirror_account_number', $this->selectedBankAccount)
                ->first();

            if (!$bankAccount) {
                $this->showErrorMessage('Selected bank account not found.');
                return;
            }

            // Calculate net disbursement amount
            $accountingData = json_decode($this->loan->accounting_data ?? '{}', true);
            $principal = floatval($this->loan->principle);
            $charges = floatval($accountingData['charges'] ?? 0);
            $insurance = floatval($accountingData['insurance'] ?? 0);
            $firstInterest = floatval($accountingData['first_interest'] ?? 0);
            $topUpAmount = floatval($accountingData['deduction_entries']['top_up']['total'] ?? 0);
            $totalDeductions = $charges + $insurance + $firstInterest + $topUpAmount;
            $netDisbursement = $principal - $totalDeductions;

            if ($bankAccount->current_balance < $netDisbursement) {
                $shortfall = $netDisbursement - $bankAccount->current_balance;
                $this->showErrorMessage('Insufficient funds in selected bank account. Shortfall: ' . number_format($shortfall, 2) . ' TZS');
                return;
            }

            // Update the accounting data with the selected bank account
            $accountingData['selected_bank_mirror'] = $this->selectedBankAccount;
            $this->loan->accounting_data = json_encode($accountingData);
            $this->loan->save();

            Log::info('Updated accounting data with selected bank account', [
                'loan_id' => $this->loan->id,
                'bank_account' => $this->selectedBankAccount
            ]);

            // Start database transaction for atomicity
            DB::beginTransaction();

            try {
                // Process disbursement using the saved accounting configuration
                $disbursementResult = $this->processDisbursementFromAccountingConfig();

                if ($disbursementResult['status'] === 'success') {
                    // Mark loan as active (disbursed) and store deduction amounts
                    $this->loan->status = 'ACTIVE';
                    $this->loan->loan_status = 'ACTIVE';
                    $this->loan->disbursement_date = now();
                    $this->loan->disbursement_status = 'DISBURSED';
                    $this->loan->total_deductions = $totalDeductions;
                    $this->loan->future_interest = $firstInterest;
                    $this->loan->disbursed_by = auth()->user()->id ?? null;
                    $this->loan->save();

                    Log::info('Loan disbursement amounts recorded', [
                        'loan_id' => $this->loan->id,
                        'principal' => $principal,
                        'charges' => $charges,
                        'insurance' => $insurance,
                        'first_interest' => $firstInterest,
                        'top_up_amount' => $topUpAmount,
                        'total_deductions' => $totalDeductions,
                        'net_disbursement' => $netDisbursement
                    ]);

                    // Create repayment schedule
                    $scheduleResult = $this->createLoanRepaymentSchedule();
                    if (!$scheduleResult['success']) {
                        Log::warning('Failed to create repayment schedule', [
                            'loan_id' => $this->loan->id,
                            'error' => $scheduleResult['message']
                        ]);
                    }

                    // Generate control number and send notifications
                    $this->generateControlNumberAndNotifications();

                    // Commit transaction
                    DB::commit();

                    $this->showSuccessMessage('Loan disbursed successfully! Notification sent to member.');

                    // Reload loan data and refresh component
                    $this->loadLoanData();
                    $this->emit('refreshLoanProcess');

                } else {
                    // Rollback on disbursement failure
                    DB::rollback();
                    $errorMessage = $disbursementResult['message'] ?? 'Unknown error occurred during disbursement';
                    $this->showErrorMessage('Disbursement failed: ' . $errorMessage);
                    Log::error('Disbursement failed', [
                        'loan_id' => $this->loan->id,
                        'error' => $errorMessage,
                        'full_result' => $disbursementResult
                    ]);
                }

            } catch (\Exception $disbursementException) {
                DB::rollback();
                Log::error('Error during loan disbursement: ' . $disbursementException->getMessage(), [
                    'loan_id' => $this->loan->id,
                    'trace' => $disbursementException->getTraceAsString()
                ]);
                $this->showErrorMessage('Error processing loan disbursement: ' . $disbursementException->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Error in disburseLoan method: ' . $e->getMessage(), [
                'loan_id' => $this->loan->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            $this->showErrorMessage('Error disbursing loan: ' . $e->getMessage());
        }
    }

    /**
     * Validate and move to next stage (with committee minutes check)
     */
    public function validateAndMoveToStage($stage)
    {
        try {
            // Check if policy adherence is confirmed
            if (!$this->policyAdherenceConfirmed) {
                $this->showErrorMessage('Please confirm policy adherence before proceeding.');
                return;
            }
            
            // Check if committee minutes are required
            if ($this->isLoanCommitteeStage()) {
                if (!$this->committeeMinutesPath) {
                    $this->showErrorMessage('Please upload committee minutes before proceeding.');
                    return;
                }
            }
            
            // Save state before moving
            $this->persistApprovalState();
            
            // Get current stage before moving
            $currentStage = $this->loan->approval_stage ?? 'Inputter';
            
            // Handle approval record creation and updates based on stage transitions
            if ($currentStage === 'Inputter' && $stage === 'First Checker') {
                // Create new approval record when moving from Inputter to First Checker
                $nextStageRoles = $this->getStageRoles($stage);
                $nextRoleName = count($nextStageRoles) > 1 ? 'Loan Committee' : implode(', ', $nextStageRoles);
                
                // Create approval record for the approval workflow
                $this->createApprovalRecord($stage, $nextRoleName);
                
                Log::info('Created approval record for loan moving from Inputter to First Checker', [
                    'loan_id' => $this->loan->id,
                    'current_stage' => $currentStage,
                    'next_stage' => $stage,
                    'next_role_name' => $nextRoleName,
                    'purpose' => 'This initiates the formal approval workflow in the approvals table'
                ]);
            } elseif (in_array($currentStage, ['First Checker', 'Second Checker', 'Third Checker']) || 
                     ($currentStage === 'Approver' && $this->loan->status !== 'APPROVED')) {
                // Update existing approval record for subsequent stages
                $this->updateApprovalRecord($currentStage, $stage);
                
                Log::info('Updated approval record for stage transition', [
                    'loan_id' => $this->loan->id,
                    'current_stage' => $currentStage,
                    'next_stage' => $stage,
                    'purpose' => 'Updating existing approval record with checker/approver action'
                ]);
            } else {
                Log::info('Stage transition without approval record action', [
                    'loan_id' => $this->loan->id,
                    'current_stage' => $currentStage,
                    'next_stage' => $stage,
                    'reason' => 'No approval record action needed for this transition'
                ]);
            }
            
            // Proceed with stage movement
            $this->moveToStage($stage);
            
        } catch (\Exception $e) {
            Log::error('Error validating and moving stage: ' . $e->getMessage());
            $this->showErrorMessage('Error processing approval. Please try again.');
        }
    }
    
    /**
     * Check if current stage requires loan committee (multiple roles)
     */
    protected function isLoanCommitteeStage()
    {
        if (!$this->loan) {
            return false;
        }
        
        $currentStage = $this->loan->approval_stage ?? 'Inputter';
        $roles = $this->getStageRoles($currentStage);
        return count($roles) > 1;
    }
    
    /**
     * Get roles for a specific stage
     */
    protected function getStageRoles($stage)
    {
        $loanProcessConfig = DB::table('process_code_configs')
            ->where('process_code', 'LOAN_APP')
            ->where('is_active', true)
            ->first();
            
        if (!$loanProcessConfig) {
            return [];
        }
        
        $roleNames = [];
        $roleIds = [];
        
        // Get role IDs based on stage
        switch($stage) {
            case 'First Checker':
                $roleIds = json_decode($loanProcessConfig->first_checker_roles ?? '[]', true);
                break;
            case 'Second Checker':
                $roleIds = json_decode($loanProcessConfig->second_checker_roles ?? '[]', true);
                break;
            case 'Approver':
                $roleIds = json_decode($loanProcessConfig->approver_roles ?? '[]', true);
                break;
            case 'Inputter':
            case 'Exception':
                return ['Loan Officer'];
            default:
                return [];
        }
        
        // Get role names
        if (!empty($roleIds)) {
            $names = DB::table('roles')
                ->whereIn('id', $roleIds)
                ->pluck('name')
                ->toArray();
            return $names;
        }
        
        return [];
    }
    
    /**
     * Handle committee minutes file upload
     */
    public function updatedCommitteeMinutesFile()
    {
        try {
            $this->validate([
                'committeeMinutesFile' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            ]);
            
            if ($this->committeeMinutesFile) {
                // Store the file
                $filename = 'committee_minutes_' . $this->loan->id . '_' . time() . '.' . $this->committeeMinutesFile->extension();
                $path = $this->committeeMinutesFile->storeAs('loan_documents/committee_minutes', $filename, 'public');
                
                $this->committeeMinutesPath = $path;
                
                // Save to session for persistence
                Session::put('loan_' . $this->loan->id . '_committee_minutes', $path);
                
                $this->showSuccessMessage('Committee minutes uploaded successfully.');
            }
        } catch (\Exception $e) {
            Log::error('Error uploading committee minutes: ' . $e->getMessage());
            $this->showErrorMessage('Error uploading committee minutes. Please try again.');
        }
    }
    
    /**
     * Remove committee minutes
     */
    public function removeCommitteeMinutes()
    {
        try {
            if ($this->committeeMinutesPath) {
                // Delete the file
                if (Storage::disk('public')->exists($this->committeeMinutesPath)) {
                    Storage::disk('public')->delete($this->committeeMinutesPath);
                }
                
                $this->committeeMinutesPath = null;
                $this->committeeMinutesFile = null;
                
                // Remove from session
                Session::forget('loan_' . $this->loan->id . '_committee_minutes');
                
                $this->showSuccessMessage('Committee minutes removed.');
            }
        } catch (\Exception $e) {
            Log::error('Error removing committee minutes: ' . $e->getMessage());
            $this->showErrorMessage('Error removing committee minutes.');
        }
    }
    
    /**
     * Persist approval state to session
     */
    protected function persistApprovalState()
    {
        if ($this->loan) {
            $sessionKey = 'loan_' . $this->loan->id . '_approval_state';
            Session::put($sessionKey, [
                'policyAdherenceConfirmed' => $this->policyAdherenceConfirmed,
                'committeeMinutesPath' => $this->committeeMinutesPath,
                'approvalComment' => $this->approvalComment
            ]);
        }
    }
    
    /**
     * Restore approval state from session
     */
    protected function restoreApprovalState()
    {
        if ($this->loan) {
            $sessionKey = 'loan_' . $this->loan->id . '_approval_state';
            $state = Session::get($sessionKey, []);
            
            $this->policyAdherenceConfirmed = $state['policyAdherenceConfirmed'] ?? false;
            $this->committeeMinutesPath = $state['committeeMinutesPath'] ?? null;
            $this->approvalComment = $state['approvalComment'] ?? 'Approved';
            
            // Also check for committee minutes separately
            $minutesKey = 'loan_' . $this->loan->id . '_committee_minutes';
            if (Session::has($minutesKey)) {
                $this->committeeMinutesPath = Session::get($minutesKey);
            }
        }
    }
    
    /**
     * Check if the stage movement is backward (Return to Sender)
     */
    protected function isBackwardMovement($currentStage, $targetStage)
    {
        // Define stage hierarchy
        $stageHierarchy = [
            'Exception' => 0,
            'Inputter' => 1,
            'First Checker' => 2,
            'Second Checker' => 3,
            'Third Checker' => 4,
            'Approver' => 5
        ];
        
        $currentLevel = $stageHierarchy[$currentStage] ?? 0;
        $targetLevel = $stageHierarchy[$targetStage] ?? 0;
        
        return $targetLevel < $currentLevel;
    }
    
    /**
     * Reverse approval table updates when moving backward
     */
    protected function reverseApprovalTableUpdates($currentStage, $targetStage)
    {
        try {
            if (!$this->loan) {
                return;
            }
            
            // Find the existing approval record for this loan
            $approval = approvals::where('process_code', 'LOAN_APP')
                ->where('process_id', $this->loan->id)
                ->whereIn('process_status', ['PENDING', 'APPROVED'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$approval) {
                Log::info('No approval record found to reverse', [
                    'loan_id' => $this->loan->id,
                    'current_stage' => $currentStage,
                    'target_stage' => $targetStage
                ]);
                return;
            }
            
            // Prepare reversal updates based on current stage
            $updateData = [];
            
            switch ($currentStage) {
                case 'Approver':
                    // Reverting from Approver stage
                    if ($approval->approval_status === 'APPROVED') {
                        $updateData['approver_id'] = null;
                        $updateData['approval_status'] = 'PENDING';
                        $updateData['process_status'] = 'PENDING';
                        $updateData['approved_at'] = null;
                    }
                    
                    // Update next role based on target stage
                    if ($targetStage === 'Second Checker') {
                        $nextStageRoles = $this->getStageRoles('Second Checker');
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                        
                        // Also clear third/second checker if returning to Second Checker
                        if ($approval->second_checker_status === 'APPROVED') {
                            $updateData['second_checker_id'] = null;
                            $updateData['second_checker_status'] = 'PENDING';
                            $updateData['checker_level'] = 1;
                        }
                    } elseif ($targetStage === 'First Checker') {
                        // Clear first checker approval but keep the record
                        $updateData['first_checker_id'] = null;
                        $updateData['first_checker_status'] = 'PENDING';
                        $updateData['checker_level'] = 1;
                        
                        $nextStageRoles = $this->getStageRoles('First Checker');
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                    } elseif ($targetStage === 'Inputter') {
                        // If returning all the way to Inputter, delete the approval record
                        $approval->delete();
                        Log::info('Deleted approval record as loan returned to Inputter', [
                            'approval_id' => $approval->id,
                            'loan_id' => $this->loan->id
                        ]);
                        return;
                    }
                    break;
                    
                case 'Third Checker':
                    // Reverting from Third Checker stage
                    $updateData['checker_level'] = 2;
                    
                    if ($targetStage === 'Second Checker') {
                        // Clear second checker approval
                        $updateData['second_checker_id'] = null;
                        $updateData['second_checker_status'] = 'PENDING';
                        
                        $nextStageRoles = $this->getStageRoles('Second Checker');
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                    } elseif ($targetStage === 'First Checker') {
                        // Clear first and second checker approvals but keep the record
                        $updateData['first_checker_id'] = null;
                        $updateData['first_checker_status'] = 'PENDING';
                        $updateData['second_checker_id'] = null;
                        $updateData['second_checker_status'] = 'PENDING';
                        $updateData['checker_level'] = 1;
                        
                        $nextStageRoles = $this->getStageRoles('First Checker');
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                    } elseif ($targetStage === 'Inputter') {
                        // Delete approval record if returning to Inputter
                        $approval->delete();
                        Log::info('Deleted approval record as loan returned to Inputter', [
                            'approval_id' => $approval->id,
                            'loan_id' => $this->loan->id
                        ]);
                        return;
                    }
                    break;
                    
                case 'Second Checker':
                    // Reverting from Second Checker stage
                    if ($targetStage === 'First Checker') {
                        // Clear first checker approval but keep the record
                        $updateData['first_checker_id'] = null;
                        $updateData['first_checker_status'] = 'PENDING';
                        $updateData['checker_level'] = 1;
                        
                        $nextStageRoles = $this->getStageRoles('First Checker');
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                    } elseif ($targetStage === 'Inputter') {
                        // Delete the approval record as it was created at Inputter -> First Checker transition
                        $approval->delete();
                        Log::info('Deleted approval record as loan returned to Inputter', [
                            'approval_id' => $approval->id,
                            'loan_id' => $this->loan->id
                        ]);
                        return;
                    }
                    break;
                    
                case 'First Checker':
                    // Reverting from First Checker stage
                    if ($targetStage === 'Inputter') {
                        // Delete the approval record as it was created at Inputter -> First Checker transition
                        $approval->delete();
                        Log::info('Deleted approval record as loan returned to Inputter', [
                            'approval_id' => $approval->id,
                            'loan_id' => $this->loan->id
                        ]);
                        return;
                    }
                    break;
            }
            
            // Add return comment
            $returnComment = $this->approvalComment ?: 'Returned to ' . $targetStage . ' for review';
            $updateData['comments'] = $returnComment;
            $updateData['updated_at'] = now();
            
            // Apply the updates
            if (!empty($updateData)) {
                $approval->update($updateData);
                
                Log::info('Reversed approval table updates', [
                    'approval_id' => $approval->id,
                    'loan_id' => $this->loan->id,
                    'from_stage' => $currentStage,
                    'to_stage' => $targetStage,
                    'updates' => array_keys($updateData)
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error reversing approval table updates: ' . $e->getMessage(), [
                'loan_id' => $this->loan->id ?? null,
                'current_stage' => $currentStage,
                'target_stage' => $targetStage,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - let the process continue
        }
    }
    
    /**
     * Update approval record for final approval
     */
    protected function updateApprovalRecordForFinalApproval()
    {
        try {
            if (!$this->loan) {
                return;
            }
            
            // Find the existing approval record for this loan
            $approval = approvals::where('process_code', 'LOAN_APP')
                ->where('process_id', $this->loan->id)
                ->where('process_status', 'PENDING')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$approval) {
                Log::warning('No pending approval record found for final approval', [
                    'loan_id' => $this->loan->id
                ]);
                return;
            }
            
            // Update for final approval
            $updateData = [
                'approver_id' => auth()->user()->id,
                'approval_status' => 'APPROVED',
                'process_status' => 'APPROVED',
                'approved_at' => now(),
                'comments' => $this->approvalComment ?: 'Loan approved',
                'committee_minutes_path' => $this->committeeMinutesPath,
                'policy_adherence_confirmed' => $this->policyAdherenceConfirmed,
                'next_role_name' => null, // No next role after final approval
                'updated_at' => now()
            ];
            
            // Update the approval record
            $approval->update($updateData);
            
            Log::info('Updated approval record for final approval', [
                'approval_id' => $approval->id,
                'loan_id' => $this->loan->id,
                'approved_by' => auth()->user()->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating approval record for final approval: ' . $e->getMessage(), [
                'loan_id' => $this->loan->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - let the process continue
        }
    }
    
    /**
     * Update existing approval record when checkers/approvers act
     */
    protected function updateApprovalRecord($currentStage, $nextStage)
    {
        try {
            if (!$this->loan) {
                return;
            }
            
            // Find the existing approval record for this loan
            $approval = approvals::where('process_code', 'LOAN_APP')
                ->where('process_id', $this->loan->id)
                ->where('process_status', 'PENDING')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$approval) {
                Log::warning('No pending approval record found for loan', [
                    'loan_id' => $this->loan->id,
                    'current_stage' => $currentStage
                ]);
                return;
            }
            
            // Get the process configuration
            $config = DB::table('process_code_configs')
                ->where('process_code', 'LOAN_APP')
                ->where('is_active', true)
                ->first();
            
            if (!$config) {
                Log::error('Process configuration not found for LOAN_APP');
                return;
            }
            
            // Update based on current stage
            $updateData = [];
            $user = auth()->user();
            
            switch ($currentStage) {
                case 'First Checker':
                    // First checker is acting
                    if ($nextStage === 'Second Checker' || $nextStage === 'Approver') {
                        $updateData['first_checker_id'] = $user->id;
                        $updateData['first_checker_status'] = 'APPROVED';
                        $updateData['checker_level'] = $nextStage === 'Second Checker' ? 2 : 1;
                        
                        // Update next role name for the next stage
                        $nextStageRoles = $this->getStageRoles($nextStage);
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                    }
                    break;
                    
                case 'Second Checker':
                    // Second checker is acting
                    if ($nextStage === 'Third Checker' || $nextStage === 'Approver') {
                        $updateData['second_checker_id'] = $user->id;
                        $updateData['second_checker_status'] = 'APPROVED';
                        $updateData['checker_level'] = $nextStage === 'Third Checker' ? 3 : 2;
                        
                        // Update next role name for the next stage
                        $nextStageRoles = $this->getStageRoles($nextStage);
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                    }
                    break;
                    
                case 'Third Checker':
                    // Third checker is acting (if exists in workflow)
                    if ($nextStage === 'Approver') {
                        // Assuming third checker maps to additional checker fields
                        // You might need to add third_checker fields to approvals table
                        $updateData['checker_level'] = 3;
                        
                        // Update next role name for approver stage
                        $nextStageRoles = $this->getStageRoles($nextStage);
                        $updateData['next_role_name'] = count($nextStageRoles) > 1 ? 
                            'Loan Committee (' . implode(', ', $nextStageRoles) . ')' : 
                            implode(', ', $nextStageRoles);
                    }
                    break;
                    
                case 'Approver':
                    // Final approver is acting
                    $updateData['approver_id'] = $user->id;
                    $updateData['approval_status'] = 'APPROVED';
                    $updateData['process_status'] = 'APPROVED';
                    $updateData['approved_at'] = now();
                    $updateData['next_role_name'] = null; // No next role after approval
                    break;
            }
            
            // Add common fields
            $updateData['comments'] = $this->approvalComment ?: 'Stage approved';
            $updateData['updated_at'] = now();
            
            // Add committee minutes if this is a loan committee stage
            if ($this->isLoanCommitteeStage() && $this->committeeMinutesPath) {
                $updateData['committee_minutes_path'] = $this->committeeMinutesPath;
            }
            
            // Update policy adherence confirmation
            $updateData['policy_adherence_confirmed'] = $this->policyAdherenceConfirmed;
            
            // Update the approval record
            $approval->update($updateData);
            
            Log::info('Updated approval record for stage action', [
                'approval_id' => $approval->id,
                'loan_id' => $this->loan->id,
                'current_stage' => $currentStage,
                'next_stage' => $nextStage,
                'updates' => array_keys($updateData)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating approval record: ' . $e->getMessage(), [
                'loan_id' => $this->loan->id ?? null,
                'current_stage' => $currentStage,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - let the process continue
        }
    }
    
    /**
     * Create approval record for tracking
     */
    protected function createApprovalRecord($nextStage, $nextRoleName)
    {
        try {
            if (!$this->loan) {
                return;
            }
            
            // Prepare loan data for approval
            $loanData = [
                'loan_id' => $this->loan->id,
                'client_number' => $this->loan->client_number,
                'loan_amount' => $this->loan->loan_amount,
                'loan_type' => $this->loan->loan_type,
                'repayment_period' => $this->loan->repayment_period,
                'interest_rate' => $this->loan->interest_rate,
                'purpose' => $this->loan->purpose,
                'current_stage' => $this->loan->approval_stage,
                'next_stage' => $nextStage,
                'policy_confirmed' => $this->policyAdherenceConfirmed,
                'committee_minutes' => $this->committeeMinutesPath,
                'approval_comment' => $this->approvalComment
            ];
            
            $editPackage = json_encode($loanData);
            
            approvals::create([
                'process_name' => 'approve_loan',
                'process_description' => Auth::user()->name . ' has processed loan ' . $this->loan->id . ' at stage ' . $this->loan->approval_stage,
                'approval_process_description' => 'Loan approval workflow - moving to ' . $nextStage,
                'process_code' => 'LOAN_APP',
                'process_id' => $this->loan->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'next_role_name' => $nextRoleName,
                'committee_minutes_path' => $this->committeeMinutesPath,
                'policy_adherence_confirmed' => $this->policyAdherenceConfirmed,
                'comments' => $this->approvalComment,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => $editPackage
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating approval record: ' . $e->getMessage());
            // Don't throw - let the process continue
        }
    }
    
    /**
     * Updated mount to persist state on tab changes
     */
    public function updatedActiveTab()
    {
        $this->persistApprovalState();
    }
    
    /**
     * Create final approval record
     */
    protected function createFinalApprovalRecord()
    {
        try {
            if (!$this->loan) {
                return;
            }
            
            // Prepare loan data for final approval
            $loanData = [
                'loan_id' => $this->loan->id,
                'client_number' => $this->loan->client_number,
                'loan_amount' => $this->loan->loan_amount,
                'loan_type' => $this->loan->loan_type,
                'repayment_period' => $this->loan->repayment_period,
                'interest_rate' => $this->loan->interest_rate,
                'purpose' => $this->loan->purpose,
                'approval_stage' => 'APPROVED',
                'approved_by' => auth()->user()->id,
                'approved_at' => now(),
                'policy_confirmed' => $this->policyAdherenceConfirmed,
                'committee_minutes' => $this->committeeMinutesPath,
                'approval_comment' => $this->approvalComment
            ];
            
            $editPackage = json_encode($loanData);
            
            approvals::create([
                'process_name' => 'approve_loan',
                'process_description' => Auth::user()->name . ' has approved loan ' . $this->loan->id,
                'approval_process_description' => 'Loan fully approved and ready for disbursement',
                'process_code' => 'LOAN_APP',
                'process_id' => $this->loan->id,
                'process_status' => 'APPROVED',
                'user_id' => auth()->user()->id,
                'next_role_name' => 'Disbursement Officer',
                'committee_minutes_path' => $this->committeeMinutesPath,
                'policy_adherence_confirmed' => $this->policyAdherenceConfirmed,
                'comments' => $this->approvalComment,
                'approver_id' => auth()->user()->id,
                'approval_status' => 'APPROVED',
                'approved_at' => now(),
                'edit_package' => $editPackage
            ]);
            
            // Clear session state after approval
            Session::forget('loan_' . $this->loan->id . '_approval_state');
            Session::forget('loan_' . $this->loan->id . '_committee_minutes');
            
        } catch (\Exception $e) {
            Log::error('Error creating final approval record: ' . $e->getMessage());
            // Don't throw - let the process continue
        }
    }
    
    /**
     * Process loan disbursement using the saved accounting configuration
     * This method retrieves the accounting configuration saved during the Accounting tab
     * and posts the journal entries for loan disbursement
     * 
     * @return array Result of disbursement processing
     */
    protected function processDisbursementFromAccountingConfig()
    {
        try {
            Log::info('Starting loan disbursement from accounting configuration', [
                'loan_id' => $this->loan->id,
                'client_number' => $this->loan->client_number
            ]);
            
            // Get the saved accounting configuration
            if (!$this->loan->accounting_data) {
                Log::warning('No accounting configuration found for loan', ['loan_id' => $this->loan->id]);
                return ['status' => 'error', 'message' => 'No accounting configuration found'];
            }
            
            $accountingData = json_decode($this->loan->accounting_data, true);
            
            // Check if parameters have been applied
            if (!isset($accountingData['parameters_applied']) || !$accountingData['parameters_applied']) {
                Log::warning('Accounting parameters not applied for loan', ['loan_id' => $this->loan->id]);
                return ['status' => 'error', 'message' => 'Accounting parameters not applied'];
            }
            
            // Get the journal entries or generate from deduction entries (backward compatibility)
            $journalEntries = $accountingData['journal_entries'] ?? [];
            
            // If no journal entries, try to generate from deduction entries for backward compatibility
            if (empty($journalEntries) && isset($accountingData['deduction_entries'])) {
                Log::info('Generating journal entries from deduction entries for backward compatibility', [
                    'loan_id' => $this->loan->id
                ]);
                $journalEntries = $this->generateJournalEntriesFromDeductions($accountingData);
            }
            
            if (empty($journalEntries)) {
                Log::warning('No journal entries found or generated', ['loan_id' => $this->loan->id]);
                return ['status' => 'error', 'message' => 'No journal entries found. Please go to Accounting tab and apply parameters.'];
            }
            
            // STEP 1: Get member's NBC account from clients table
            $client = DB::table('clients')
                ->where('client_number', $this->loan->client_number)
                ->first();
                
            if (!$client || !$client->account_number) {
                Log::error('Member NBC account not found', ['client_number' => $this->loan->client_number]);
                return ['status' => 'error', 'message' => 'Member NBC account not found. Please ensure member has a valid NBC account.'];
            }
            
            $memberNBCAccount = $client->account_number;
            Log::info('Member NBC account retrieved', [
                'client_number' => $this->loan->client_number,
                'nbc_account' => $memberNBCAccount,
                'member_name' => $client->first_name . ' ' . $client->last_name
            ]);
            
            // STEP 2: Get the selected bank account from accounting configuration
            $bankMirrorAccount = $accountingData['selected_bank_mirror'] ?? null;
            if (!$bankMirrorAccount) {
                Log::error('Bank account not selected in accounting configuration');
                return ['status' => 'error', 'message' => 'Bank account not selected in accounting configuration.'];
            }
            
            // Get the actual bank account details from bank_accounts table
            $bankAccount = DB::table('bank_accounts')
                ->where('internal_mirror_account_number', $bankMirrorAccount)
                ->first();
                
            if (!$bankAccount || !$bankAccount->account_number) {
                Log::error('Bank account details not found', ['mirror_account' => $bankMirrorAccount]);
                return ['status' => 'error', 'message' => 'Bank account details not found for selected bank.'];
            }
            
            Log::info('Bank account retrieved', [
                'mirror_account' => $bankMirrorAccount,
                'bank_account' => $bankAccount->account_number,
                'bank_name' => $bankAccount->bank_name ?? 'NBC'
            ]);
            
            // STEP 3: Calculate net disbursement amount
            $principal = floatval($this->loan->principle);

            // Get deductions from deduction_entries (correct structure)
            $deductionEntries = $accountingData['deduction_entries'] ?? [];
            $charges = floatval($deductionEntries['charges']['total'] ?? 0);
            $insurance = floatval($deductionEntries['insurance']['total'] ?? 0);
            $firstInterest = floatval($deductionEntries['first_interest']['total'] ?? 0);
            $topUp = floatval($deductionEntries['top_up']['total'] ?? 0);

            $totalDeductions = $charges + $insurance + $firstInterest + $topUp;
            $netDisbursement = $principal - $totalDeductions;

            Log::info('Disbursement calculation', [
                'principal' => $principal,
                'charges' => $charges,
                'insurance' => $insurance,
                'first_interest' => $firstInterest,
                'top_up' => $topUp,
                'total_deductions' => $totalDeductions,
                'net_disbursement' => $netDisbursement
            ]);

            // VALIDATION: Block negative net disbursement
            if ($netDisbursement < 0) {
                $shortfall = abs($netDisbursement);
                Log::error('Disbursement blocked: Deductions exceed loan amount', [
                    'loan_id' => $this->loan->id,
                    'principal' => $principal,
                    'total_deductions' => $totalDeductions,
                    'shortfall' => $shortfall,
                    'breakdown' => [
                        'charges' => $charges,
                        'insurance' => $insurance,
                        'first_interest' => $firstInterest,
                        'top_up' => $topUp
                    ]
                ]);

                return [
                    'status' => 'error',
                    'message' => sprintf(
                        'Cannot disburse loan: Total deductions (TZS %s) exceed loan amount (TZS %s). Shortfall: TZS %s. Please reduce charges or increase loan amount.',
                        number_format($totalDeductions, 2),
                        number_format($principal, 2),
                        number_format($shortfall, 2)
                    )
                ];
            }

            // VALIDATION: Warn if net disbursement is very small (less than 10% of principal)
            if ($netDisbursement < ($principal * 0.10)) {
                Log::warning('Low net disbursement amount (less than 10% of principal)', [
                    'loan_id' => $this->loan->id,
                    'principal' => $principal,
                    'net_disbursement' => $netDisbursement,
                    'percentage' => ($netDisbursement / $principal * 100)
                ]);
            }

            // STEP 4: Perform NBC Internal Funds Transfer
            // SKIP for Restructure loans (client receives no money - buffer covers all deductions)
            $isRestructureLoan = in_array($this->loan->loan_type_2 ?? '', ['Restructure', 'Restructuring']);

            if ($isRestructureLoan) {
                Log::info('Skipping NBC transfer for restructure loan (client receives no money)', [
                    'loan_id' => $this->loan->id,
                    'loan_type' => $this->loan->loan_type_2,
                    'net_disbursement' => $netDisbursement,
                    'reason' => 'Buffer covers all deductions, no actual disbursement to client'
                ]);
            } else {
                // Perform NBC transfer for New and Top-up loans
                $iftService = new \App\Services\Payments\InternalFundsTransferService();

                // Generate unique narration to avoid NBC duplicate detection (Error 602 CBS Code 57)
                $uniqueTimestamp = date('YmdHis');

                $transferData = [
                    'from_account' => $bankAccount->account_number,  // SACCOS bank account at NBC
                    'to_account' => $memberNBCAccount,               // Member's NBC account
                    'amount' => $netDisbursement,
                    'narration' => 'Loan Disbursement - ' . $client->first_name . ' ' . $client->last_name . ' - Loan ID: ' . $this->loan->id . ' - Ref: ' . $uniqueTimestamp,
                    'sender_name' => 'SACCOS',
                    'from_currency' => 'TZS',
                    'to_currency' => 'TZS'
                ];

                Log::info('Initiating NBC Internal Funds Transfer', $transferData);

                try {
                    $transferResult = $iftService->transfer($transferData);

                    if (!$transferResult['success']) {
                        $errorDetails = $transferResult['message'] ?? 'Unknown error - please check bank connectivity';
                        Log::error('NBC Internal Transfer failed', [
                            'loan_id' => $this->loan->id,
                            'error' => $errorDetails,
                            'response' => $transferResult
                        ]);
                        return [
                            'status' => 'error',
                            'message' => 'NBC Bank Transfer Failed: ' . $errorDetails . '. Please verify bank account details and try again.'
                        ];
                    }

                    Log::info('NBC Internal Transfer successful', [
                        'loan_id' => $this->loan->id,
                        'reference' => $transferResult['reference'],
                        'nbc_reference' => $transferResult['nbc_reference'] ?? null,
                        'message' => $transferResult['message']
                    ]);

                    // Store the NBC transfer reference in loan record
                    $this->loan->nbc_transfer_reference = $transferResult['nbc_reference'] ?? $transferResult['reference'];
                    $this->loan->save();

                } catch (\Exception $transferException) {
                    $errorMessage = $transferException->getMessage();
                    Log::error('NBC Internal Transfer exception', [
                        'loan_id' => $this->loan->id,
                        'error' => $errorMessage,
                        'trace' => $transferException->getTraceAsString()
                    ]);

                    // Provide user-friendly error message
                    $userMessage = 'Bank transfer error: ';
                    if (strpos($errorMessage, 'Connection') !== false || strpos($errorMessage, 'timeout') !== false) {
                        $userMessage .= 'Unable to connect to NBC bank. Please try again later.';
                    } elseif (strpos($errorMessage, 'insufficient') !== false) {
                        $userMessage .= 'Insufficient funds in SACCOS bank account.';
                    } elseif (strpos($errorMessage, 'account') !== false) {
                        $userMessage .= 'Invalid account details. Please verify member\'s NBC account.';
                    } else {
                        $userMessage .= $errorMessage;
                    }

                    return [
                        'status' => 'error',
                        'message' => $userMessage
                    ];
                }
            }

            // STEP 5: Proceed with GL postings
            Log::info('NBC transfer successful, proceeding with GL account creation and posting', [
                'loan_id' => $this->loan->id
            ]);
            
            // Create loan accounts if they don't exist
            $accountsCreated = $this->createLoanAccounts();
            if (!$accountsCreated['success']) {
                Log::error('Failed to create loan accounts', [
                    'loan_id' => $this->loan->id,
                    'error' => $accountsCreated['message']
                ]);
                return ['status' => 'error', 'message' => 'Failed to create loan accounts: ' . $accountsCreated['message']];
            }
            
            // Refresh the loan model to get the updated account numbers
            $this->loan = $this->loan->fresh();
            
            // Get the newly created account numbers from the refreshed loan model
            $newLoanAccount = $this->loan->loan_account_number;
            $newInterestAccount = $this->loan->interest_account_number;
            $newChargesAccount = $this->loan->charge_account_number;
            $newInsuranceAccount = $this->loan->insurance_account_number;
            
            Log::info('Using newly created accounts from loan record', [
                'loan_account' => $newLoanAccount,
                'interest_account' => $newInterestAccount,
                'charges_account' => $newChargesAccount,
                'insurance_account' => $newInsuranceAccount
            ]);
            
            // Update journal entries with the new account numbers
            // Replace any reference to the template ID (like BUS202596153) with actual GL accounts
            foreach ($journalEntries as $index => &$entry) {
                // Check if this entry uses the old loan account number (template ID)
                if (isset($entry['account_number'])) {
                    $oldAccount = $entry['account_number'];
                    $originalAccount = $oldAccount; // Keep track of original for debugging
                    
                    // If it's the loan template ID (starts with BUS, MKP, etc.), replace with new loan account
                    if (preg_match('/^[A-Z]{3}\d+/', $oldAccount)) {
                        $entry['account_number'] = $newLoanAccount;
                        Log::info('Replaced template ID with actual loan account', [
                            'index' => $index,
                            'old' => $oldAccount,
                            'new' => $newLoanAccount,
                            'entry_description' => $entry['description'] ?? 'unknown'
                        ]);
                    }
                    // Replace parent charge account with new child account
                    elseif ($oldAccount === '0101400041004120') {
                        $entry['account_number'] = $newChargesAccount;
                        Log::info('Replaced parent charges account with child account', [
                            'old' => $oldAccount,
                            'new' => $newChargesAccount
                        ]);
                    }
                    // Replace parent insurance account with new child account
                    elseif ($oldAccount === '0101400041004111' || $oldAccount === '0101400041004110') {
                        $entry['account_number'] = $newInsuranceAccount;
                        Log::info('Replaced parent insurance account with child account', [
                            'old' => $oldAccount,
                            'new' => $newInsuranceAccount
                        ]);
                    }
                    // Replace parent interest account with new child account
                    elseif ($oldAccount === '0101400040004010') {
                        $entry['account_number'] = $newInterestAccount;
                        Log::info('Replaced parent interest account with child account', [
                            'index' => $index,
                            'old' => $oldAccount,
                            'new' => $newInterestAccount,
                            'entry_description' => $entry['description'] ?? 'unknown',
                            'debit' => $entry['debit'] ?? 0,
                            'credit' => $entry['credit'] ?? 0
                        ]);
                    }
                }
            }
            unset($entry); // CRITICAL: Break the reference to avoid PHP reference bug
            
            // Log each entry after replacement for debugging
            foreach ($journalEntries as $idx => $entry) {
                if (isset($entry['description']) && strpos($entry['description'], 'Interest') !== false) {
                    Log::info('Interest entry after replacement', [
                        'index' => $idx,
                        'description' => $entry['description'],
                        'account_number' => $entry['account_number'],
                        'debit' => $entry['debit'] ?? 0,
                        'credit' => $entry['credit'] ?? 0
                    ]);
                }
            }
            
            Log::info('Journal entries updated with new account numbers', [
                'loan_id' => $this->loan->id,
                'entries_count' => count($journalEntries)
            ]);
            
            // Initialize transaction posting service
            $transactionService = new TransactionPostingService();
            $successfulTransactions = [];
            $failedTransactions = [];

            // CORRECTED: Process ALL journal entries together as ONE balanced transaction
            // Calculate total debits and credits across ALL entries
            $totalDebits = 0;
            $totalCredits = 0;

            foreach ($journalEntries as $entry) {
                $totalDebits += $entry['debit'] ?? 0;
                $totalCredits += $entry['credit'] ?? 0;
            }

            Log::info('Processing complete journal entry set', [
                'entries_count' => count($journalEntries),
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'all_entries' => $journalEntries
            ]);

            // Validate that the complete transaction is balanced
            if (abs($totalDebits - $totalCredits) > 0.01) {
                Log::error('Unbalanced journal entries for complete transaction', [
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                    'difference' => $totalDebits - $totalCredits,
                    'entries' => $journalEntries
                ]);

                return [
                    'status' => 'error',
                    'message' => 'Journal entries are unbalanced. Total Debits: ' . number_format($totalDebits, 2) . ', Total Credits: ' . number_format($totalCredits, 2)
                ];
            }

            // Separate debit and credit entries for processing
            $debitEntries = array_filter($journalEntries, fn($e) => ($e['debit'] ?? 0) > 0);
            $creditEntries = array_filter($journalEntries, fn($e) => ($e['credit'] ?? 0) > 0);

            Log::info('Separated entries for posting', [
                'debit_entries_count' => count($debitEntries),
                'credit_entries_count' => count($creditEntries)
            ]);

            // Post each credit entry against the single debit (Loan Account)
            // ONE DEBIT to Loan Account, MULTIPLE CREDITS to other accounts
            foreach ($creditEntries as $creditEntry) {
                try {
                    $creditAmount = $creditEntry['credit'];
                    $creditDescription = $creditEntry['description'] ?? 'Credit Entry';
                    $debitEntry = reset($debitEntries); // Get the single debit entry (Loan Account)

                    if (!$debitEntry) {
                        Log::error('No debit entry found for credit', [
                            'credit_entry' => $creditEntry
                        ]);
                        $failedTransactions[] = [
                            'description' => $creditDescription,
                            'error' => 'No debit entry found'
                        ];
                        continue;
                    }

                    // Post the transaction
                    // Each credit is paired with the Loan Account debit
                    $transactionData = [
                        'first_account' => $debitEntry['account_number'],      // Loan account (debited)
                        'second_account' => $creditEntry['account_number'],    // Credit account (bank, income, etc.)
                        'source_account' => $creditEntry['account_number'],    // Source of funds
                        'destination_account' => $debitEntry['account_number'], // Loan account receives
                        'amount' => $creditAmount,
                        'narration' => $creditDescription . ' - Loan ID: ' . $this->loan->id,
                        'action' => 'loan_disbursement'
                    ];

                    Log::info('Posting disbursement transaction', [
                        'description' => $creditDescription,
                        'debit_account' => $debitEntry['account_number'],
                        'credit_account' => $creditEntry['account_number'],
                        'amount' => $creditAmount
                    ]);

                    $result = $transactionService->postTransaction($transactionData);

                    if ($result['status'] === 'success') {
                        $successfulTransactions[] = [
                            'description' => $creditDescription,
                            'debit_account' => $debitEntry['account_number'],
                            'credit_account' => $creditEntry['account_number'],
                            'amount' => $creditAmount,
                            'reference' => $result['reference_number'] ?? null
                        ];

                        Log::info('Transaction posted successfully', [
                            'description' => $creditDescription,
                            'reference' => $result['reference_number'] ?? null
                        ]);
                    } else {
                        $failedTransactions[] = [
                            'description' => $creditDescription,
                            'error' => $result['message'] ?? 'Unknown error',
                            'debit_account' => $debitEntry['account_number'],
                            'credit_account' => $creditEntry['account_number'],
                            'amount' => $creditAmount
                        ];

                        Log::error('Transaction failed', [
                            'description' => $creditDescription,
                            'error' => $result['message'] ?? 'Unknown error'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing credit entry', [
                        'credit_entry' => $creditEntry,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $failedTransactions[] = [
                        'description' => $creditEntry['description'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Log the results
            Log::info('Disbursement processing completed', [
                'loan_id' => $this->loan->id,
                'successful_transactions' => count($successfulTransactions),
                'failed_transactions' => count($failedTransactions)
            ]);
            
            // Log disbursement results
            Log::info('Disbursement processing completed', [
                'loan_id' => $this->loan->id,
                'successful_transactions' => count($successfulTransactions),
                'failed_transactions' => count($failedTransactions),
                'successful_details' => $successfulTransactions,
                'failed_details' => $failedTransactions
            ]);

            // STEP 6: Close old loans for Top-up and Restructure loan types
            if (count($successfulTransactions) > 0) {
                $this->closeOldLoanIfApplicable();
            }

            // Return result
            if (count($failedTransactions) > 0) {
                return [
                    'status' => 'partial',
                    'message' => 'Some transactions failed',
                    'successful' => $successfulTransactions,
                    'failed' => $failedTransactions
                ];
            } else if (count($successfulTransactions) > 0) {
                return [
                    'status' => 'success',
                    'message' => 'All transactions posted successfully',
                    'transactions' => $successfulTransactions
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'No transactions were processed'
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Error in processDisbursementFromAccountingConfig: ' . $e->getMessage(), [
                'loan_id' => $this->loan->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Error processing disbursement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Close old loan for Top-up and Restructure loan types
     * Updates the old loan status to CLOSED and marks all schedules as CLOSED
     *
     * @return void
     */
    protected function closeOldLoanIfApplicable()
    {
        try {
            $loanType = $this->loan->loan_type_2 ?? '';
            $oldLoanId = null;
            $loanTypeName = '';

            // Determine old loan ID based on loan type
            if (in_array($loanType, ['Top-up', 'TopUp', 'Top Up'])) {
                $oldLoanId = $this->loan->top_up_loan_id;
                $loanTypeName = 'Top-up';
            } elseif (in_array($loanType, ['Restructure', 'Restructuring'])) {
                $oldLoanId = $this->loan->restructure_loan_id;
                $loanTypeName = 'Restructure';
            }

            // If no old loan ID, nothing to close
            if (!$oldLoanId) {
                Log::info('No old loan to close', [
                    'loan_id' => $this->loan->id,
                    'loan_type' => $loanType
                ]);
                return;
            }

            // Get the old loan
            $oldLoan = DB::table('loans')->where('id', $oldLoanId)->first();

            if (!$oldLoan) {
                Log::warning('Old loan not found for closure', [
                    'loan_id' => $this->loan->id,
                    'old_loan_id' => $oldLoanId,
                    'loan_type' => $loanTypeName
                ]);
                return;
            }

            Log::info('Closing old loan after ' . $loanTypeName . ' disbursement', [
                'new_loan_id' => $this->loan->id,
                'old_loan_id' => $oldLoanId,
                'old_loan_number' => $oldLoan->loan_id,
                'loan_type' => $loanTypeName
            ]);

            // Update old loan status to CLOSED
            DB::table('loans')
                ->where('id', $oldLoanId)
                ->update([
                    'status' => 'CLOSED',
                    'loan_status' => 'CLOSED',
                    'closure_date' => now()->toDateString(),
                    'updated_at' => now()
                ]);

            Log::info('Old loan status updated to CLOSED', [
                'old_loan_id' => $oldLoanId,
                'closed_by' => auth()->user()->id ?? 'system'
            ]);

            // Close all schedules for the old loan
            $schedulesUpdated = DB::table('loans_schedules')
                ->where('loan_id', $oldLoanId)
                ->update([
                    'completion_status' => 'CLOSED',
                    'updated_at' => now()
                ]);

            Log::info('Old loan schedules closed', [
                'old_loan_id' => $oldLoanId,
                'schedules_updated' => $schedulesUpdated
            ]);

            // Get the old loan account and set balance to zero
            if (!empty($oldLoan->loan_account_number)) {
                DB::table('accounts')
                    ->where('account_number', $oldLoan->loan_account_number)
                    ->update([
                        'balance' => 0,
                        'status' => 'CLOSED',
                        'updated_at' => now()
                    ]);

                Log::info('Old loan account closed and balance zeroed', [
                    'loan_account' => $oldLoan->loan_account_number
                ]);
            }

            Log::info('Old loan closure completed successfully', [
                'old_loan_id' => $oldLoanId,
                'old_loan_number' => $oldLoan->loan_id,
                'loan_type' => $loanTypeName,
                'schedules_closed' => $schedulesUpdated
            ]);

        } catch (\Exception $e) {
            Log::error('Error closing old loan: ' . $e->getMessage(), [
                'loan_id' => $this->loan->id,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception - just log it. The new loan has already been disbursed.
            // The old loan can be closed manually if needed.
        }
    }

    /**
     * Create loan accounts (loan, interest, charges, insurance)
     * Based on the loan sub-product configuration
     *
     * @return array Result with success status and message
     */
    protected function createLoanAccounts()
    {
        try {
            // ALWAYS create fresh accounts for disbursement - don't check for existing
            // The loan_account_number like "BUS202596153" is just a template/reference
            Log::info('Creating loan accounts for disbursement', [
                'loan_id' => $this->loan->id,
                'client_number' => $this->loan->client_number
            ]);
            
            // Get loan sub-product configuration
            $loanSubProduct = DB::table('loan_sub_products')
                ->where('sub_product_id', $this->loan->loan_sub_product)
                ->first();
                
            if (!$loanSubProduct) {
                throw new \Exception('Loan sub-product not found');
            }
            
            // Initialize account creation service
            $accountService = new AccountCreationService();
            $loanID = $this->loan->id;
            $branchNumber = auth()->user()->branch ?? '001';
            
            // STEP 1: Create loan account
            Log::info('Creating loan account');
            
            // Get loan parent account from product configuration
            // NOTE: loan_sub_products table only has 'loan_product_account', not 'loan_account'
            $loanParentAccountNumber = $loanSubProduct->loan_product_account;
            if (!$loanParentAccountNumber || $loanParentAccountNumber === 'N/A') {
                throw new \Exception('Loan parent account not configured for this product');
            }
            
            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $loanParentAccountNumber = str_pad($loanParentAccountNumber, 16, '0', STR_PAD_LEFT);
            
            Log::info('Looking for loan parent account', [
                'raw_value' => $loanSubProduct->loan_product_account,
                'padded_value' => $loanParentAccountNumber
            ]);
            
            $loanParentAccount = AccountsModel::where('account_number', $loanParentAccountNumber)->first();
            if (!$loanParentAccount) {
                // Try without padding if not found
                $loanParentAccount = AccountsModel::where('account_number', $loanSubProduct->loan_product_account)->first();
            }
            
            if (!$loanParentAccount) {
                throw new \Exception('Loan parent account not found in database. Looking for: ' . $loanParentAccountNumber);
            }
            
            // Create loan account as child of parent account
            $loanAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $loanParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => $branchNumber,
                'sub_product_number' => $loanID,
                'notes' => 'Loan Account: Loan ID ' . $loanID
            ], $loanParentAccount->account_number);
            
            // STEP 2: Create interest account
            Log::info('Creating interest account');
            
            // NOTE: Use interest_account first, fallback to loan_interest_account if empty
            $interestParentAccountNumber = $loanSubProduct->interest_account ?: $loanSubProduct->loan_interest_account;
            if (!$interestParentAccountNumber || $interestParentAccountNumber === 'N/A') {
                throw new \Exception('Interest parent account not configured for this product');
            }
            
            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $interestParentAccountNumber = str_pad($interestParentAccountNumber, 16, '0', STR_PAD_LEFT);
            
            Log::info('Looking for interest parent account', [
                'raw_value' => $loanSubProduct->interest_account ?: $loanSubProduct->loan_interest_account,
                'padded_value' => $interestParentAccountNumber
            ]);
            
            $interestParentAccount = AccountsModel::where('account_number', $interestParentAccountNumber)->first();
            if (!$interestParentAccount) {
                // Try without padding if not found
                $interestParentAccount = AccountsModel::where('account_number', $loanSubProduct->interest_account ?: $loanSubProduct->loan_interest_account)->first();
            }
            
            if (!$interestParentAccount) {
                throw new \Exception('Interest parent account not found. Looking for: ' . $interestParentAccountNumber);
            }
            
            $interestAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $interestParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => $branchNumber,
                'sub_product_number' => $loanID,
                'notes' => 'Interest Account: Loan ID ' . $loanID
            ], $interestParentAccount->account_number);
            
            // STEP 3: Create charges account
            Log::info('Creating charges account');
            
            // NOTE: Use fees_account first, then loan_charges_account, then charge_product_account
            $chargesParentAccountNumber = $loanSubProduct->fees_account ?: ($loanSubProduct->loan_charges_account ?: $loanSubProduct->charge_product_account);
            if (!$chargesParentAccountNumber || $chargesParentAccountNumber === 'N/A') {
                throw new \Exception('Charges parent account not configured for this product');
            }
            
            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $chargesParentAccountNumber = str_pad($chargesParentAccountNumber, 16, '0', STR_PAD_LEFT);
            
            Log::info('Looking for charges parent account', [
                'raw_value' => $loanSubProduct->fees_account ?: ($loanSubProduct->loan_charges_account ?: $loanSubProduct->charge_product_account),
                'padded_value' => $chargesParentAccountNumber
            ]);
            
            $chargesParentAccount = AccountsModel::where('account_number', $chargesParentAccountNumber)->first();
            if (!$chargesParentAccount) {
                // Try without padding if not found
                $chargesParentAccount = AccountsModel::where('account_number', $loanSubProduct->fees_account ?: ($loanSubProduct->loan_charges_account ?: $loanSubProduct->charge_product_account))->first();
            }
            
            if (!$chargesParentAccount) {
                throw new \Exception('Charges parent account not found. Looking for: ' . $chargesParentAccountNumber);
            }
            
            $chargesAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $chargesParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => $branchNumber,
                'sub_product_number' => $loanID,
                'notes' => 'Charges Account: Loan ID ' . $loanID
            ], $chargesParentAccount->account_number);
            
            // STEP 4: Create insurance account
            Log::info('Creating insurance account');
            
            // NOTE: Use insurance_account first, then loan_insurance_account, then insurance_product_account
            $insuranceParentAccountNumber = $loanSubProduct->insurance_account ?: ($loanSubProduct->loan_insurance_account ?: $loanSubProduct->insurance_product_account);
            if (!$insuranceParentAccountNumber || $insuranceParentAccountNumber === 'N/A') {
                throw new \Exception('Insurance parent account not configured for this product');
            }
            
            // Ensure the account number preserves leading zeros (16 digits for account numbers)
            $insuranceParentAccountNumber = str_pad($insuranceParentAccountNumber, 16, '0', STR_PAD_LEFT);
            
            Log::info('Looking for insurance parent account', [
                'raw_value' => $loanSubProduct->insurance_account ?: ($loanSubProduct->loan_insurance_account ?: $loanSubProduct->insurance_product_account),
                'padded_value' => $insuranceParentAccountNumber
            ]);
            
            $insuranceParentAccount = AccountsModel::where('account_number', $insuranceParentAccountNumber)->first();
            if (!$insuranceParentAccount) {
                // Try without padding if not found  
                $insuranceParentAccount = AccountsModel::where('account_number', $loanSubProduct->insurance_account ?: ($loanSubProduct->loan_insurance_account ?: $loanSubProduct->insurance_product_account))->first();
            }
            
            if (!$insuranceParentAccount) {
                throw new \Exception('Insurance parent account not found. Looking for: ' . $insuranceParentAccountNumber);
            }
            
            $insuranceAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $insuranceParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => $branchNumber,
                'sub_product_number' => $loanID,
                'notes' => 'Insurance Account: Loan ID ' . $loanID
            ], $insuranceParentAccount->account_number);
            
            // Update loan record with the newly created account numbers
            Log::info('Updating loan with account numbers');
            DB::table('loans')->where('id', $loanID)->update([
                'loan_account_number' => $loanAccount->account_number,
                'interest_account_number' => $interestAccount->account_number,
                'charge_account_number' => $chargesAccount->account_number,
                'insurance_account_number' => $insuranceAccount->account_number
            ]);
            
            // Refresh loan model
            $this->loan = $this->loan->fresh();
            
            Log::info('All loan accounts created successfully', [
                'loan_id' => $loanID,
                'loan_account' => $loanAccount->account_number,
                'interest_account' => $interestAccount->account_number,
                'charges_account' => $chargesAccount->account_number,
                'insurance_account' => $insuranceAccount->account_number
            ]);
            
            return [
                'success' => true,
                'message' => 'Accounts created successfully',
                'accounts' => [
                    'loan' => $loanAccount->account_number,
                    'interest' => $interestAccount->account_number,
                    'charges' => $chargesAccount->account_number,
                    'insurance' => $insuranceAccount->account_number
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Error creating loan accounts: ' . $e->getMessage(), [
                'loan_id' => $this->loan->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create loan repayment schedule in loans_schedules table
     *
     * IMPORTANT: All deduction amounts (charges, insurance, first_interest) are read from
     * loan.accounting_data JSON, which was calculated and saved by the Accounting tab.
     * DO NOT recalculate these values here - use the accounting_data as the source of truth.
     *
     * This ensures:
     * - Schedule matches disbursement deductions
     * - First interest uses payroll-based grace period calculation
     * - Charges and insurance amounts are consistent with GL postings
     */
    protected function createLoanRepaymentSchedule()
    {
        try {
            Log::info('Creating loan repayment schedule', [
                'loan_id' => $this->loan->id,
                'principal' => $this->loan->principle,
                'tenure' => $this->loan->tenure
            ]);

            // Get loan product for interest calculation method
            $loanProduct = DB::table('loan_sub_products')
                ->where('sub_product_id', $this->loan->loan_sub_product)
                ->first();

            if (!$loanProduct) {
                throw new \Exception('Loan product not found');
            }

            // Get loan parameters - matching LoansDisbursement component exactly
            $principal = (float)$this->loan->principle;
            $annualInterestRate = (float)($loanProduct->interest_value ?? 0);
            $monthlyInterestRate = $annualInterestRate / 12 / 100; // Convert to decimal monthly rate
            $termMonths = (int)$this->loan->tenure;
            $remainingBalance = $principal;

            // Validate parameters to avoid division by zero
            if ($termMonths <= 0) {
                Log::error('Invalid loan tenure', [
                    'loan_id' => $this->loan->id,
                    'tenure' => $this->loan->tenure
                ]);
                throw new \Exception('Invalid loan tenure. Tenure must be greater than 0 months.');
            }

            if ($principal <= 0) {
                Log::error('Invalid loan principal', [
                    'loan_id' => $this->loan->id,
                    'principal' => $this->loan->principle
                ]);
                throw new \Exception('Invalid loan principal amount.');
            }

            if ($annualInterestRate <= 0) {
                Log::warning('Zero or negative interest rate', [
                    'loan_id' => $this->loan->id,
                    'interest_rate' => $annualInterestRate
                ]);
            }

            Log::info('Schedule generation parameters', [
                'principal' => $principal,
                'annualInterestRate' => $annualInterestRate,
                'monthlyInterestRate' => $monthlyInterestRate,
                'termMonths' => $termMonths,
                'interest_method' => $loanProduct->interest_method
            ]);

            // Calculate monthly installment using amortization formula
            // PMT = P * (r * (1 + r)^n) / ((1 + r)^n - 1)
            if ($monthlyInterestRate > 0) {
                $monthlyInstallment = $principal * ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $termMonths)) /
                                     (pow(1 + $monthlyInterestRate, $termMonths) - 1);
            } else {
                $monthlyInstallment = $principal / $termMonths; // If no interest, equal principal payments
            }

            Log::info('Monthly installment calculated', [
                'monthly_installment' => $monthlyInstallment
            ]);

            // Clear any existing schedules for this loan
            DB::table('loans_schedules')->where('loan_id', $this->loan->id)->delete();

            $totalPayment = 0;
            $totalInterest = 0;
            $totalPrincipal = 0;

            // Calculate dates
            $disbursementDate = \Carbon\Carbon::now();
            $firstRegularDate = $disbursementDate->copy()->addMonth();

            // STEP 1: Add First Interest installment (interest from disbursement to first regular installment)
            // This is the interest that was collected upfront during disbursement
            // IMPORTANT: Use the EXACT value from accounting_data that was used during disbursement
            // Do NOT recalculate as the Accounting tab uses payroll-based calculation
            $accountingData = json_decode($this->loan->accounting_data, true);
            $firstInterestAmount = floatval($accountingData['first_interest'] ?? 0);

            // For logging purposes, calculate days
            $daysToFirstInstallment = $disbursementDate->diffInDays($firstRegularDate);

            // If no accounting data, fallback to simple calculation (backward compatibility)
            if ($firstInterestAmount == 0 && $annualInterestRate > 0) {
                $firstInterestAmount = $principal * ($annualInterestRate / 100) * ($daysToFirstInstallment / 365);
                Log::info('No accounting_data found, using fallback first interest calculation', [
                    'calculated_first_interest' => $firstInterestAmount
                ]);
            }

            if ($firstInterestAmount > 0) {
                Log::info('Creating first interest installment (PAID)', [
                    'first_interest_amount' => $firstInterestAmount,
                    'first_interest_source' => isset($accountingData['first_interest']) ? 'accounting_data' : 'calculated',
                    'installment_date' => $firstRegularDate->format('Y-m-d'),
                    'days_to_first_installment' => $daysToFirstInstallment,
                    'note' => 'This interest was collected upfront during disbursement'
                ]);

                // Save first interest installment - MARKED AS PAID
                DB::table('loans_schedules')->insert([
                    'loan_id' => (string)$this->loan->id,
                    'installment' => round($firstInterestAmount, 2),
                    'interest' => round($firstInterestAmount, 2),
                    'principle' => 0,
                    'opening_balance' => $principal,
                    'closing_balance' => $principal, // Balance doesn't change for interest-only payment
                    'bank_account_number' => $this->loan->bank1 ?? null,
                    'completion_status' => 'PAID', // MARKED AS PAID since collected during disbursement
                    'status' => 'PAID', // MARKED AS PAID
                    'installment_date' => $firstRegularDate->format('Y-m-d'),
                    'payment' => round($firstInterestAmount, 2), // Record the payment
                    'interest_payment' => round($firstInterestAmount, 2), // Record interest payment
                    'principle_payment' => 0,
                    'last_payment_date' => now(), // Payment date is now (disbursement date)
                    'member_number' => $this->loan->client_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $totalPayment += $firstInterestAmount;
                $totalInterest += $firstInterestAmount;
                // Principal remains unchanged for first interest installment
            }

            // STEP 2: Generate regular installments starting from the second month
            $regularStartDate = $firstRegularDate->copy()->addMonth();

            for ($i = 0; $i < $termMonths; $i++) {
                $openingBalance = $remainingBalance;

                // Calculate interest for this month
                $monthlyInterest = $remainingBalance * $monthlyInterestRate;

                // Calculate principal for this month
                $monthlyPrincipal = $monthlyInstallment - $monthlyInterest;

                // Ensure we don't overpay in the last installment
                if ($i == $termMonths - 1) {
                    $monthlyPrincipal = $remainingBalance;
                    $monthlyInstallment = $monthlyPrincipal + $monthlyInterest;
                }

                // Update remaining balance
                $remainingBalance -= $monthlyPrincipal;
                if ($remainingBalance < 0.01) $remainingBalance = 0; // Round to zero if very small

                Log::info('Creating regular installment', [
                    'installment_number' => $i + 1,
                    'installment_date' => $regularStartDate->format('Y-m-d'),
                    'opening_balance' => $openingBalance,
                    'payment' => $monthlyInstallment,
                    'principal' => $monthlyPrincipal,
                    'interest' => $monthlyInterest,
                    'closing_balance' => $remainingBalance
                ]);

                // Save regular installment
                DB::table('loans_schedules')->insert([
                    'loan_id' => (string)$this->loan->id,
                    'installment' => round($monthlyInstallment, 2),
                    'interest' => round($monthlyInterest, 2),
                    'principle' => round($monthlyPrincipal, 2),
                    'opening_balance' => round($openingBalance, 2),
                    'closing_balance' => round($remainingBalance, 2),
                    'bank_account_number' => $this->loan->bank1 ?? null,
                    'completion_status' => 'ACTIVE',
                    'status' => 'ACTIVE',
                    'installment_date' => $regularStartDate->format('Y-m-d'),
                    'member_number' => $this->loan->client_number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $totalPayment += $monthlyInstallment;
                $totalInterest += $monthlyInterest;
                $totalPrincipal += $monthlyPrincipal;

                $regularStartDate->addMonth();
            }

            // Update loan with schedule summary
            DB::table('loans')->where('id', $this->loan->id)->update([
                'monthly_installment' => round($monthlyInstallment, 2),
                'total_interest' => round($totalInterest, 2),
                'total_principal' => round($totalPrincipal, 2),
                'total_payment' => round($totalPayment, 2),
                'interest' => round($totalInterest, 2),
                'updated_at' => now()
            ]);

            // Verify schedules were created
            $createdSchedules = DB::table('loans_schedules')
                ->where('loan_id', $this->loan->id)
                ->count();

            Log::info('Loan repayment schedule created successfully', [
                'loan_id' => $this->loan->id,
                'total_installments' => $createdSchedules,
                'first_interest_installment' => $firstInterestAmount > 0 ? 'PAID' : 'N/A',
                'regular_installments' => $termMonths,
                'monthly_installment' => round($monthlyInstallment, 2),
                'total_payment' => round($totalPayment, 2),
                'total_interest' => round($totalInterest, 2),
                'total_principal' => round($totalPrincipal, 2),
                'final_balance' => round($remainingBalance, 2),
                'first_interest_amount' => round($firstInterestAmount, 2)
            ]);

            return [
                'success' => true,
                'message' => 'Repayment schedule created successfully',
                'total_installments' => $createdSchedules,
                'total_interest' => round($totalInterest, 2),
                'monthly_payment' => round($monthlyInstallment, 2),
                'first_interest_amount' => round($firstInterestAmount, 2)
            ];

        } catch (\Exception $e) {
            Log::error('Error creating loan repayment schedule', [
                'loan_id' => $this->loan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create repayment schedule: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate control number for loan repayment and send notifications
     */
    protected function generateControlNumberAndNotifications()
    {
        try {
            Log::info('Starting control number and notification generation', [
                'loan_id' => $this->loan->id,
                'client_number' => $this->loan->client_number
            ]);
            
            // Get member information
            $client = ClientsModel::where('client_number', $this->loan->client_number)->first();
            if (!$client) {
                Log::warning('Client not found for notification', ['client_number' => $this->loan->client_number]);
                return;
            }
            
            // Generate control number for loan repayment
            $billingService = new BillingService();
            $controlNumbers = [];
            
            // Check if there's a loan repayment service defined
            $repaymentService = DB::table('services')
                ->where('code', 'REP')
                ->orWhere('code', 'LOAN_REP')
                ->first();
                
            if ($repaymentService) {
                // Use service configuration for control number generation
                $isRecurring = $repaymentService->is_recurring ? 1 : 0;
                $paymentMode = $repaymentService->payment_mode;

                Log::info('Generating control number with service configuration', [
                    'service_code' => $repaymentService->code,
                    'service_id' => $repaymentService->id,
                    'is_recurring' => $isRecurring,
                    'payment_mode' => $paymentMode,
                    'client_number' => $this->loan->client_number
                ]);

                $controlNumber = $billingService->generateControlNumber(
                    $this->loan->client_number,
                    $repaymentService->id,
                    $isRecurring,
                    $paymentMode
                );

                // Create bill using BillingService to ensure all required fields are set
                try {
                    $billId = $billingService->createBill(
                        $this->loan->client_number,
                        $repaymentService->id,
                        $isRecurring,
                        $paymentMode,
                        $controlNumber,
                        $this->loan->principle
                    );
                
                    Log::info('Bill created successfully for loan repayment', [
                        'bill_id' => $billId,
                        'loan_id' => $this->loan->id,
                        'control_number' => $controlNumber,
                        'amount' => $this->loan->principle
                    ]);
                    
                    $controlNumbers[] = [
                        'service_code' => $repaymentService->code,
                        'service_name' => $repaymentService->name ?? 'Loan Repayment',
                        'control_number' => $controlNumber,
                        'amount' => $this->loan->principle
                    ];
                    
                } catch (\Exception $billException) {
                    Log::error('Error creating bill for loan repayment', [
                        'loan_id' => $this->loan->id,
                        'error' => $billException->getMessage()
                    ]);
                    // Continue without bill creation - don't stop the process
                }
            } else {
                Log::warning('No loan repayment service found in services table', [
                    'loan_id' => $this->loan->id
                ]);
            }
            
            // Don't generate additional control numbers - we only need ONE with partial payment capability
            // $this->generateInstallmentControlNumbers($billingService, $controlNumbers);
            
            // Generate payment link
            $paymentLink = $this->generateLoanPaymentLink($client, $controlNumbers);
            
            // Get loan schedule for notification
            $loanSchedule = $this->getLoanScheduleForNotification();
            
            // Prepare loan summary
            $loanSummary = $this->prepareLoanSummary();
            
            // Dispatch notification job
            $this->dispatchNotification($client, $controlNumbers, $paymentLink, $loanSummary, $loanSchedule);
            
            Log::info('Control number and notifications completed successfully', [
                'loan_id' => $this->loan->id,
                'control_numbers_count' => count($controlNumbers),
                'payment_link' => $paymentLink
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating control number and notifications', [
                'loan_id' => $this->loan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - this shouldn't stop the disbursement process
        }
    }
    
    /**
     * Generate control numbers for individual installments (optional)
     */
    protected function generateInstallmentControlNumbers($billingService, &$controlNumbers)
    {
        try {
            // Get first 3 installments from loan schedule
            $installments = DB::table('loans_schedules')
                ->where('loan_id', $this->loan->id)
                ->where('completion_status', 'PENDING')
                ->orderBy('installment_date')
                ->limit(3)
                ->get();
                
            if ($installments->isEmpty()) {
                Log::info('No installments found for control number generation', [
                    'loan_id' => $this->loan->id
                ]);
                return;
            }
            
            // Check if there's an installment payment service
            $installmentService = DB::table('services')
                ->where('code', 'INST')
                ->orWhere('code', 'LOAN_INST')
                ->first();
                
            if (!$installmentService) {
                Log::info('No installment service found, skipping individual control numbers');
                return;
            }
            
            foreach ($installments as $index => $installment) {
                $isRecurring = 0; // 0 for one-time payment
                $paymentMode = 'full'; // Full payment for individual installments
                
                $controlNumber = $billingService->generateControlNumber(
                    $this->loan->client_number,
                    $installmentService->id,
                    $isRecurring,
                    $paymentMode
                );
                
                try {
                    $billId = $billingService->createBill(
                        $this->loan->client_number,
                        $installmentService->id,
                        $isRecurring,
                        $paymentMode,
                        $controlNumber,
                        $installment->installment
                    );
                    
                    // Update just the due date (reference columns don't exist)
                    DB::table('bills')->where('id', $billId)->update([
                        'due_date' => $installment->installment_date
                    ]);
                    
                    $controlNumbers[] = [
                        'service_code' => 'INST_' . ($index + 1),
                        'service_name' => 'Loan Installment ' . ($index + 1),
                        'control_number' => $controlNumber,
                        'amount' => $installment->installment,
                        'due_date' => $installment->installment_date
                    ];
                    
                    Log::info('Control number generated for installment', [
                        'installment_id' => $installment->id,
                        'control_number' => $controlNumber,
                        'amount' => $installment->installment
                    ]);
                    
                } catch (\Exception $e) {
                    Log::warning('Failed to create control number for installment', [
                        'installment_id' => $installment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error generating installment control numbers', [
                'loan_id' => $this->loan->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate payment link for loan repayments
     */
    protected function generateLoanPaymentLink($client, $controlNumbers)
    {
        try {
            Log::info('Generating payment link for loan disbursement', [
                'loan_id' => $this->loan->id,
                'client_number' => $client->client_number
            ]);

            $paymentService = new PaymentLinkService();

            // Get all bills for this client (including the loan repayment bill just created)
            $bills = DB::table('bills')
                ->join('services', 'bills.service_id', '=', 'services.id')
                ->where('bills.client_number', $client->client_number)
                ->where('bills.status', '!=', 'PAID') // Only unpaid bills
                ->whereNotNull('bills.id') // Ensure bill_id exists
                ->select(
                    'bills.id as bill_id',
                    'bills.control_number',
                    'bills.amount_due as bill_amount',
                    'bills.payment_mode',
                    'services.code as service_code',
                    'services.name as service_name'
                )
                ->get();

            // Check if bills exist
            if ($bills->isEmpty()) {
                Log::warning('No bills found for client', [
                    'client_number' => $client->client_number,
                    'loan_id' => $this->loan->id
                ]);
                return null;
            }

            // Prepare payment items from bills (include all bills with amount > 0)
            $items = [];
            foreach ($bills as $bill) {
                // Skip bills with zero or negative amounts
                if ($bill->bill_amount <= 0) {
                    Log::info('Skipping bill with invalid amount', [
                        'bill_id' => $bill->bill_id,
                        'amount' => $bill->bill_amount,
                        'service' => $bill->service_name
                    ]);
                    continue;
                }

                $items[] = [
                    'type' => 'service',
                    'product_service_reference' => (string) $bill->bill_id,
                    'product_service_name' => $bill->service_name,
                    'amount' => $bill->bill_amount,
                    'is_required' => true,
                    'allow_partial' => $bill->payment_mode === 'partial'
                ];
            }

            Log::info('Bills found for payment link', [
                'client_number' => $client->client_number,
                'loan_id' => $this->loan->id,
                'bills_count' => count($bills),
                'total_amount' => $bills->sum('bill_amount')
            ]);

            if (!empty($items)) {
                $paymentData = [
                    'description' => 'Loan Repayment - ' . $client->first_name . ' ' . $client->last_name,
                    'target' => 'individual',
                    'customer_reference' => $client->client_number,
                    'customer_name' => $client->first_name . ' ' . $client->last_name,
                    'customer_phone' => $client->phone_number,
                    'customer_email' => $client->email,
                    'expires_at' => now()->addDays(30)->toIso8601String(), // 30 days for loan payments
                    'items' => $items
                ];

                $paymentResponse = $paymentService->generateUniversalPaymentLink($paymentData);
                $paymentUrl = $paymentResponse['data']['payment_url'] ?? null;

                if ($paymentUrl) {
                    Log::info('Payment link generated successfully', [
                        'loan_id' => $this->loan->id,
                        'payment_url' => $paymentUrl,
                        'link_id' => $paymentResponse['data']['link_id'] ?? null,
                        'total_amount' => $paymentResponse['data']['total_amount'] ?? null
                    ]);

                    // Store payment link in bills table for all bills included in this payment
                    $billIds = $bills->pluck('bill_id')->toArray();
                    DB::table('bills')->whereIn('id', $billIds)->update([
                        'payment_link' => $paymentUrl,
                        'payment_link_id' => $paymentResponse['data']['link_id'] ?? null,
                        'payment_link_generated_at' => now(),
                        'payment_link_items' => json_encode($paymentResponse['data']['items'] ?? [])
                    ]);

                    return $paymentUrl;
                } else {
                    Log::warning('Payment link generation did not return URL', [
                        'loan_id' => $this->loan->id,
                        'response' => $paymentResponse
                    ]);
                    return null;
                }
            } else {
                Log::info('No payment items to generate link for', [
                    'client_number' => $client->client_number,
                    'loan_id' => $this->loan->id
                ]);
                return null;
            }

        } catch (\Exception $e) {
            Log::error('Failed to generate payment link', [
                'error' => $e->getMessage(),
                'loan_id' => $this->loan->id,
                'client_number' => $client->client_number ?? null
            ]);
            return null;
        }
    }
    
    /**
     * Get loan schedule for notification
     */
    protected function getLoanScheduleForNotification()
    {
        try {
            $schedules = loans_schedules::where('loan_id', $this->loan->id)
                ->orderBy('installment')
                ->limit(6) // Show first 6 installments in notification
                ->get(['installment', 'installment_date', 'principle', 'interest', 'closing_balance'])
                ->map(function ($schedule, $index) {
                    $totalPayment = $schedule->principle + $schedule->interest;
                    // Handle installment_date as string since it's not cast to date in the model
                    $dateFormatted = '';
                    if ($schedule->installment_date) {
                        $dateFormatted = is_string($schedule->installment_date) 
                            ? $schedule->installment_date 
                            : $schedule->installment_date->format('Y-m-d');
                    }
                    return [
                        'installment' => $index + 1,
                        'date' => $dateFormatted,
                        'principal' => number_format($schedule->principle, 2),
                        'interest' => number_format($schedule->interest, 2),
                        'total' => number_format($totalPayment, 2),
                        'balance' => number_format($schedule->closing_balance, 2)
                    ];
                })
                ->toArray();
                
            return $schedules;
            
        } catch (\Exception $e) {
            Log::error('Error getting loan schedule for notification', [
                'loan_id' => $this->loan->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Prepare loan summary for notification
     */
    protected function prepareLoanSummary()
    {
        try {
            $accountingData = json_decode($this->loan->accounting_data, true);
            
            // Get loan product for interest rate
            $loanProduct = DB::table('loan_sub_products')
                ->where('sub_product_id', $this->loan->loan_sub_product)
                ->first();
            
            $interestRate = $loanProduct ? $loanProduct->interest_value : 0;
            
            return [
                'loan_id' => $this->loan->id,
                'principle' => number_format($this->loan->principle, 2),
                'interest' => number_format($this->loan->interest, 2),
                'charges' => number_format($accountingData['charges'] ?? 0, 2),
                'insurance' => number_format($accountingData['insurance'] ?? 0, 2),
                'net_disbursement' => number_format($accountingData['net_disbursement'] ?? $this->loan->principle, 2),
                'tenure' => $this->loan->tenure . ' months',
                'interest_rate' => $interestRate . '%',
                'disbursement_date' => $this->loan->disbursement_date ? $this->loan->disbursement_date->format('Y-m-d') : now()->format('Y-m-d'),
                'first_payment_date' => now()->addMonth()->format('Y-m-d')
            ];
            
        } catch (\Exception $e) {
            Log::error('Error preparing loan summary', [
                'loan_id' => $this->loan->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Dispatch notification to member
     */
    protected function dispatchNotification($client, $controlNumbers, $paymentLink, $loanSummary, $loanSchedule)
    {
        try {
            // Enhance control numbers with loan-specific data
            $enhancedControlNumbers = array_map(function($control) use ($loanSummary, $loanSchedule) {
                $control['loan_summary'] = $loanSummary;
                $control['loan_schedule'] = $loanSchedule;
                return $control;
            }, $controlNumbers);
            
            // Dispatch the notification job
            if (class_exists('App\Jobs\ProcessMemberNotifications')) {
                ProcessMemberNotifications::dispatch(
                    $client,
                    $enhancedControlNumbers,
                    $paymentLink,
                    $this->loan->id
                )->onQueue('notifications');

                Log::info('Loan disbursement notification dispatched', [
                    'loan_id' => $this->loan->id,
                    'client_number' => $client->client_number,
                    'payment_link' => $paymentLink
                ]);
            } else {
                Log::warning('ProcessMemberNotifications job not found');
            }
            
        } catch (\Exception $e) {
            Log::error('Error dispatching notification', [
                'loan_id' => $this->loan->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
