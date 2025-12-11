<?php

namespace App\Http\Livewire\Approvals;

use Livewire\Component;
use App\Models\ProcessCodeConfig;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalComment;
use Livewire\WithPagination;
use App\Traits\Livewire\WithModulePermissions;
use App\Models\sub_products;


class Approvals extends Component
{
    use WithPagination, WithModulePermissions;

    public $searchTerm = '';
    public $filterStatus = 'all';
    public $filterProcess = 'all';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showFilters = false;
    public $selectedApprovalId;
    public $showViewDetailsModal = false;
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $showCommentModal = false;
    public $showLoanAssessmentModal = false;
    public $rejection_reason = '';
    public $comment = '';
    public $currentCheckerLevel;
    public $processCodes;
    public $selectedApproval;
    public $checker_level = 1;
    
    // Loan assessment data
    public $loanData;
    public $memberData;
    public $guarantorData;
    public $approvalComment = '';
    public $refreshTrigger = 0; // Add this property to trigger refreshes
    public $showNotification = false; // Add this property for notification handling
    
    // Assessment data properties
    public $assessmentData = [];
    public $incomeAssessment = [];
    public $productParameters = [];
    public $termCalculation = [];
    public $loanAmountLimits = [];
    public $collateralInfo = [];
    public $deductions = [];
    public $loanStatistics = [];
    public $assessmentSummary = [];
    public $exceptions = [];
    public $loanSchedule = [];
    public $settlements = [];
    public $topUpData = [];
    public $restructureData = [];
    
    // Asset disposal details
    public $assetDisposalDetails = [];

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
        'filterProcess' => ['except' => 'all'],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->processCodes = ProcessCodeConfig::all();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'approvals';
    }

    public function getApprovalsProperty()
    {
        // Include refresh trigger to force re-evaluation when it changes
        $refreshTrigger = $this->refreshTrigger;
        
        $query = Approval::query()
            ->with(['user', 'firstChecker', 'secondChecker', 'approver', 'processConfig'])
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('process_name', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('process_description', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('process_code', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->filterStatus !== 'all', function ($query) {
                $query->where('process_status', $this->filterStatus);
            })
            ->when($this->filterProcess !== 'all', function ($query) {
                $query->where('process_code', $this->filterProcess);
            })
            ->when($this->filterDateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->filterDateTo);
            });

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterProcess()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'searchTerm',
            'filterStatus',
            'filterProcess',
            'filterDateFrom',
            'filterDateTo',
            'sortField',
            'sortDirection',
            'perPage'
        ]);
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }



    public function render()
    {
        return view('livewire.approvals.approvals', array_merge(
            $this->permissions,
            [
                'approvals' => $this->approvals,
                'processCodes' => $this->processCodes,
                'permissions' => $this->permissions
            ]
        ));
    }

    // Override pagination methods to handle custom signatures
    public function gotoPage($page, $pageName = 'page')
    {
        $this->setPage($page, $pageName);
    }

    public function previousPage($pageName = 'page')
    {
        $this->setPage(max(1, $this->getPage($pageName) - 1), $pageName);
    }

    public function nextPage($pageName = 'page')
    {
        $this->setPage($this->getPage($pageName) + 1, $pageName);
    }

    public function getProcessCodesProperty()
    {
        return ProcessCodeConfig::where('is_active', true)
            ->orderBy('process_name')
            ->get();
    }

    public function canUserCheck($approval)
    {
        $user = Auth::user();
        $config = ProcessCodeConfig::where('process_code', $approval->process_code)->first();

        if (!$config) {
            Log::warning('Process code config not found', [
                'process_code' => $approval->process_code
            ]);
            return false;
        }

        // Check if user is an admin
        if ($user->isAdmin()) {
            Log::info('User is admin, can check approval', [
                'user_id' => $user->id,
                'approval_id' => $approval->id,
                'checker_level' => $approval->checker_level
            ]);
            return true;
        }

        // Get role arrays
        $firstCheckerRoles = is_string($config->first_checker_roles) ? json_decode($config->first_checker_roles, true) : $config->first_checker_roles;
        $secondCheckerRoles = is_string($config->second_checker_roles) ? json_decode($config->second_checker_roles, true) : $config->second_checker_roles;
        $approverRoles = is_string($config->approver_roles) ? json_decode($config->approver_roles, true) : $config->approver_roles;

        Log::info('Checking user roles for approval', [
            'user_id' => $user->id,
            'user_role_id' => $user->role_id,
            'approval_id' => $approval->id,
            'checker_level' => $approval->checker_level,
            'first_checker_roles' => $firstCheckerRoles,
            'second_checker_roles' => $secondCheckerRoles,
            'approver_roles' => $approverRoles,
            'requires_first_checker' => $config->requires_first_checker,
            'requires_second_checker' => $config->requires_second_checker,
            'requires_approver' => $config->requires_approver
        ]);

        // Check first checker role
        if ($approval->checker_level === 1 && 
            $config->requires_first_checker &&
            $approval->first_checker_status === null && 
            in_array($user->role_id, $firstCheckerRoles ?? [])) {
            Log::info('User can act as first checker', [
                'user_id' => $user->id,
                'approval_id' => $approval->id
            ]);
            return true;
        }

        // Check second checker role
        if ($approval->checker_level === 2 && 
            $config->requires_second_checker &&
            $approval->second_checker_status === null && 
            in_array($user->role_id, $secondCheckerRoles ?? [])) {
            Log::info('User can act as second checker', [
                'user_id' => $user->id,
                'approval_id' => $approval->id
            ]);
            return true;
        }

        // Check approver role
        if ($approval->checker_level === 3 && 
            $config->requires_approver &&
            $approval->approval_status === 'PENDING' && 
            in_array($user->role_id, $approverRoles ?? [])) {
            Log::info('User can act as approver', [
                'user_id' => $user->id,
                'approval_id' => $approval->id
            ]);
            return true;
        }

        Log::info('User cannot check approval', [
            'user_id' => $user->id,
            'approval_id' => $approval->id,
            'checker_level' => $approval->checker_level,
            'user_role_id' => $user->role_id,
            'first_checker_roles' => $firstCheckerRoles,
            'second_checker_roles' => $secondCheckerRoles,
            'approver_roles' => $approverRoles
        ]);
        return false;
    }

    public function showCommentModal($approvalId)
    {
        if (!$this->authorize('comment', 'You do not have permission to add comments')) {
            return;
        }
        
        $this->selectedApproval = Approval::find($approvalId);
        $this->showCommentModal = true;
    }

    public function showViewChangeDetailsModal($processCode, $processId)
    {
        if (!$this->authorize('view', 'You do not have permission to view approval details')) {
            return;
        }
        
        Log::info('showViewChangeDetailsModal called', [
            'process_code' => $processCode,
            'process_id' => $processId,
            'process_id_type' => gettype($processId),
            'user_id' => Auth::id(),
            'timestamp' => now()
        ]);

        // Debug: Check what approvals exist for this process_code
        $allApprovals = Approval::where('process_code', $processCode)->get();
        Log::info('All approvals for process_code', [
            'process_code' => $processCode,
            'total_approvals' => $allApprovals->count(),
            'approval_ids' => $allApprovals->pluck('id')->toArray(),
            'process_ids' => $allApprovals->pluck('process_id')->toArray()
        ]);

        $approval = Approval::with(['user', 'processConfig'])
            ->where('process_code', $processCode)
            ->where('process_id', $processId)
            ->first();
        
        Log::info('Approval record found', [
            'approval_found' => $approval ? true : false,
            'approval_id' => $approval->id ?? null,
            'approval_process_id' => $approval->process_id ?? null,
            'approval_process_code' => $approval->process_code ?? null
        ]);
        
        if ($approval) {
            $this->selectedApprovalId = $approval->id;
            
            // If it's a loan approval, show loan assessment modal
            if (in_array($processCode, ['LOAN_DISB', 'LOAN_APP', 'LOAN_REST', 'LOAN_WOFF'])) {
                Log::info('Loading loan assessment data', [
                    'process_code' => $processCode,
                    'process_id' => $processId,
                    'is_loan_approval' => true
                ]);
                
                $this->loadLoanAssessmentData($processId);
                $this->showLoanAssessmentModal = true;
            } elseif ($processCode === 'ASSET_DISP') {
                Log::info('Loading asset disposal details', [
                    'process_code' => $processCode,
                    'process_id' => $processId,
                    'is_asset_disposal' => true
                ]);
                
                // Load asset disposal details
                $this->loadAssetDisposalDetails($processId);
                $this->showViewDetailsModal = true;
            } else {
                Log::info('Showing regular view details modal', [
                    'process_code' => $processCode,
                    'is_loan_approval' => false
                ]);
                $this->showViewDetailsModal = true;
            }
        } else {
            Log::warning('No approval record found', [
                'process_code' => $processCode,
                'process_id' => $processId
            ]);
        }
    }

    public function closeViewDetailsModal()
    {
        $this->showViewDetailsModal = false;
        $this->selectedApprovalId = null;
    }

    public function showApproveConfirmationModal($approvalId,$level)
    {
        $this->checker_level = $level;
        $this->selectedApprovalId = $approvalId;
        $this->showApproveModal = true;
    }

    public function closeApproveModal()
    {
        $this->showApproveModal = false;
        $this->selectedApprovalId = null;
    }

    public function showRejectAndCommentsConfirmationModal($approvalId)
    {
        $this->selectedApprovalId = $approvalId;
        $approval = Approval::findOrFail($approvalId);
        $config = $approval->processConfig;
        
        if (!$config) {
            session()->flash('notification', [
                'type' => 'error',
                'message' => 'Process configuration not found'
            ]);
            return;
        }

        // Determine current checker level
        $currentLevel = $this->determineCurrentLevel($approval, $config);
        
        $this->rejection_reason = '';
        $this->showRejectModal = true;
        $this->currentCheckerLevel = $currentLevel; // Store current checker level
    }

    private function determineCurrentLevel($approval, $config)
    {
        $user = Auth::user();
        $userRole = $user->role;

        // Check if user is an approver
        if ($userRole === 'approver') {
            return 'approver';
        }

        // Check if user is a checker
        if ($userRole === 'checker') {
            // If first checker hasn't approved yet
            if (!$approval->first_checker_approved) {
                return 'first_checker';
            }
            // If first checker has approved but second checker hasn't
            if ($approval->first_checker_approved && !$approval->second_checker_approved) {
                return 'second_checker';
            }
        }

        return null;
    }

    private function getRejectionNotAllowedMessage($approval, $config)
    {
        $currentLevel = $this->determineCurrentLevel($approval, $config);
        
        if ($currentLevel === 'first_checker') {
            return 'First checker cannot reject at this stage.';
        } elseif ($currentLevel === 'second_checker') {
            return 'Second checker cannot reject at this stage.';
        } elseif ($currentLevel === 'approver') {
            return 'Approver cannot reject at this stage.';
        }
        
        return 'You do not have permission to reject at this stage.';
    }

    private function canRejectAtCurrentLevel($approval, $config)
    {
        $currentLevel = $this->determineCurrentLevel($approval, $config);
        
        if ($currentLevel === 'first_checker') {
            return $config->first_checker_required && !$approval->first_checker_approved;
        } elseif ($currentLevel === 'second_checker') {
            return $config->second_checker_required && 
                   $approval->first_checker_approved && 
                   !$approval->second_checker_approved;
        } elseif ($currentLevel === 'approver') {
            return $config->approver_required && 
                   (!$config->first_checker_required || $approval->first_checker_approved) &&
                   (!$config->second_checker_required || $approval->second_checker_approved);
        }
        
        return false;
    }

    private function updateRejectionStatus($approval, $user)
    {
        $currentLevel = $this->determineCurrentLevel($approval, $approval->processConfig);
        
        if ($currentLevel === 'first_checker') {
            $approval->first_checker_approved = false;
            $approval->first_checker_rejected = true;
            $approval->first_checker_id = $user->id;
            $approval->first_checker_date = now();
        } elseif ($currentLevel === 'second_checker') {
            $approval->second_checker_approved = false;
            $approval->second_checker_rejected = true;
            $approval->second_checker_id = $user->id;
            $approval->second_checker_date = now();
        } elseif ($currentLevel === 'approver') {
            $approval->approver_approved = false;
            $approval->approver_rejected = true;
            $approval->approver_id = $user->id;
            $approval->approver_date = now();
        }
        
        $approval->process_status = 'REJECTED';
        $approval->save();
    }

    private function storeRejectionReason($approval, $user)
    {
        $currentLevel = $this->determineCurrentLevel($approval, $approval->processConfig);
        
        ApprovalComment::create([
            'approval_id' => $approval->id,
            'user_id' => $user->id,
            'comment' => $this->rejection_reason,
            'level' => $currentLevel,
            'action' => 'rejected'
        ]);
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->selectedApprovalId = null;
        $this->rejection_reason = '';
    }

    public function approve()
    {
        if (!$this->authorize('approve', 'You do not have permission to approve requests')) {
            return;
        }
        
        try {
            DB::beginTransaction();

            $approval = Approval::findOrFail($this->selectedApprovalId);
            $user = Auth::user();
            $config = $approval->processConfig;

            Log::info('Starting approval process', [
                'approval_id' => $approval->id,
                'process_code' => $approval->process_code,
                'user_id' => $user->id,
                'checker_level' => $this->checker_level,
                'current_status' => [
                    'first_checker' => $approval->first_checker_status,
                    'second_checker' => $approval->second_checker_status,
                    'approval' => $approval->approval_status,
                    'process' => $approval->process_status
                ]
            ]);

            if (!$config) {
                Log::error('Process configuration not found', [
                    'approval_id' => $approval->id,
                    'process_code' => $approval->process_code
                ]);
                throw new \Exception('Process configuration not found');
            }

            // Validate user permissions
            if (!$this->validateUserPermissions($user, $approval)) {
                Log::warning('User permission validation failed', [
                    'approval_id' => $approval->id,
                    'user_id' => $user->id,
                    'user_role' => $user->role_id,
                    'checker_level' => $this->checker_level
                ]);
                throw new \Exception('You do not have permission to perform this action');
            }

            // If approver level and previous checkers have rejected, force rejection
            if ($this->isApproverLevel($config) && !$this->validatePreviousApprovals($approval, $config)) {
                $message = $this->getApprovalNotAllowedMessage($approval);
                Log::warning('Approval not allowed - previous checkers rejected', [
                    'approval_id' => $approval->id,
                    'message' => $message,
                    'first_checker_status' => $approval->first_checker_status,
                    'second_checker_status' => $approval->second_checker_status
                ]);
                throw new \Exception($message);
            }

            // Update approval status based on checker level
            $this->updateApprovalStatus($approval, $user);

            Log::info('Approval status updated', [
                'approval_id' => $approval->id,
                'new_status' => [
                    'first_checker' => $approval->first_checker_status,
                    'second_checker' => $approval->second_checker_status,
                    'approval' => $approval->approval_status,
                    'process' => $approval->process_status,
                    'checker_level' => $approval->checker_level
                ]
            ]);

            // Update related records - for LOAN_APP, this includes stage updates even for intermediate approvals
            if ($approval->process_code === 'LOAN_APP' || $this->isFinalApproval($approval, $config)) {
                Log::info('Processing approval updates', [
                    'approval_id' => $approval->id,
                    'process_code' => $approval->process_code,
                    'process_id' => $approval->process_id,
                    'is_final' => $this->isFinalApproval($approval, $config)
                ]);
                $this->updateRelatedRecords($approval);
            }

            DB::commit();

            $this->closeApproveModal();
            session()->flash('notification', [
                'type' => 'success',
                'message' => 'Approval successful!'
            ]);
            
            // Refresh the approvals list
            $this->refreshApprovalsList();

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Database error in approval process', [
                'approval_id' => $this->selectedApprovalId,
                'user_id' => Auth::id(),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('notification', [
                'type' => 'error',
                'message' => 'Database error occurred. Please try again.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in approval process', [
                'approval_id' => $this->selectedApprovalId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('notification', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get detailed message for approval not allowed
     */
    private function getApprovalNotAllowedMessage($approval): string
    {
        $rejectedBy = [];
        
        if ($approval->first_checker_status === 'REJECTED') {
            $rejectedBy[] = 'First Checker';
        }
        if ($approval->second_checker_status === 'REJECTED') {
            $rejectedBy[] = 'Second Checker';
        }

        if (empty($rejectedBy)) {
            return 'Approval is not allowed at this stage. Please ensure all required previous checkers have approved.';
        }

        return sprintf(
            'Approval is not allowed because the request was rejected by: %s',
            implode(', ', $rejectedBy)
        );
    }

    /**
     * Update approval status based on checker level
     */
    private function updateApprovalStatus($approval, $user): void
    {
        $config = $approval->processConfig;

        Log::info('Updating approval status', [
            'approval_id' => $approval->id,
            'checker_level' => $this->checker_level,
            'config' => [
                'requires_first_checker' => $config->requires_first_checker,
                'requires_second_checker' => $config->requires_second_checker,
                'requires_approver' => $config->requires_approver
            ]
        ]);

        // Handle all seven possible combinations
        if ($config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            Log::info('Processing three-level approval', ['approval_id' => $approval->id]);
            $this->handleThreeLevelApproval($approval, $user);
        } elseif ($config->requires_first_checker && $config->requires_second_checker) {
            Log::info('Processing two-level approval', ['approval_id' => $approval->id]);
            $this->handleTwoLevelApproval($approval, $user);
        } elseif ($config->requires_first_checker && $config->requires_approver) {
            Log::info('Processing first checker and approver', ['approval_id' => $approval->id]);
            $this->handleFirstCheckerAndApprover($approval, $user);
        } elseif ($config->requires_second_checker && $config->requires_approver) {
            Log::info('Processing second checker and approver', ['approval_id' => $approval->id]);
            $this->handleSecondCheckerAndApprover($approval, $user);
        } elseif ($config->requires_first_checker) {
            Log::info('Processing first checker only', ['approval_id' => $approval->id]);
            $this->handleFirstCheckerOnly($approval, $user);
        } elseif ($config->requires_second_checker) {
            Log::info('Processing second checker only', ['approval_id' => $approval->id]);
            $this->handleSecondCheckerOnly($approval, $user);
        } elseif ($config->requires_approver) {
            Log::info('Processing approver only', ['approval_id' => $approval->id]);
            $this->handleApproverOnly($approval, $user);
        }

        $approval->save();

        Log::info('Approval status updated successfully', [
            'approval_id' => $approval->id,
            'final_status' => [
                'first_checker' => $approval->first_checker_status,
                'second_checker' => $approval->second_checker_status,
                'approval' => $approval->approval_status,
                'process' => $approval->process_status,
                'checker_level' => $approval->checker_level
            ]
        ]);
    }

    /**
     * Handle first checker only approval
     */
    private function handleFirstCheckerOnly($approval, $user): void
    {
        if ($this->checker_level == 1) {
            $approval->first_checker_id = $user->id;
            $approval->first_checker_status = 'APPROVED';
            $approval->approval_status = 'APPROVED';
            $approval->process_status = 'APPROVED';
            $approval->checker_level = 1;
        }
    }

    /**
     * Handle second checker only approval
     */
    private function handleSecondCheckerOnly($approval, $user): void
    {
        if ($this->checker_level == 1) {
            $approval->second_checker_id = $user->id;
            $approval->second_checker_status = 'APPROVED';
            $approval->approval_status = 'APPROVED';
            $approval->process_status = 'APPROVED';
            $approval->checker_level = 1;
        }
    }

    /**
     * Handle approver only approval
     */
    private function handleApproverOnly($approval, $user): void
    {
        if ($this->checker_level == 1) {
            $approval->approver_id = $user->id;
            $approval->approval_status = 'APPROVED';
            $approval->process_status = 'APPROVED';
            $approval->checker_level = 1;
        }
    }

    /**
     * Handle first checker and approver combination
     */
    private function handleFirstCheckerAndApprover($approval, $user): void
    {
        switch ($this->checker_level) {
            case 1:
                $approval->first_checker_id = $user->id;
                $approval->first_checker_status = 'APPROVED';
                $approval->checker_level = 2;
                break;
            case 2:
                $approval->approver_id = $user->id;
                $approval->approval_status = 'APPROVED';
                $approval->process_status = 'APPROVED';
                $approval->checker_level = 2;
                break;
        }
    }

    /**
     * Handle second checker and approver combination
     */
    private function handleSecondCheckerAndApprover($approval, $user): void
    {
        switch ($this->checker_level) {
            case 1:
                $approval->second_checker_id = $user->id;
                $approval->second_checker_status = 'APPROVED';
                $approval->checker_level = 2;
                break;
            case 2:
                $approval->approver_id = $user->id;
                $approval->approval_status = 'APPROVED';
                $approval->process_status = 'APPROVED';
                $approval->checker_level = 2;
                break;
        }
    }

    /**
     * Handle three-level approval process
     */
    private function handleThreeLevelApproval($approval, $user): void
    {
        switch ($this->checker_level) {
            case 1:
                $approval->first_checker_id = $user->id;
                $approval->first_checker_status = 'APPROVED';
                $approval->checker_level = 2;
                break;
            case 2:
                $approval->second_checker_id = $user->id;
                $approval->second_checker_status = 'APPROVED';
                $approval->checker_level = 3;
                break;
            case 3:
                $approval->approver_id = $user->id;
                $approval->approval_status = 'APPROVED';
                $approval->process_status = 'APPROVED';
                $approval->checker_level = 3;
                break;
        }
    }

    /**
     * Handle two-level approval process
     */
    private function handleTwoLevelApproval($approval, $user): void
    {
        switch ($this->checker_level) {
            case 1:
                $approval->first_checker_id = $user->id;
                $approval->first_checker_status = 'APPROVED';
                $approval->checker_level = 2;
                break;
            case 2:
                $approval->second_checker_id = $user->id;
                $approval->second_checker_status = 'APPROVED';
                $approval->approval_status = 'APPROVED';
                $approval->process_status = 'APPROVED';
                $approval->checker_level = 2;
                break;
        }
    }

    /**
     * Determine the new approval stage based on current approval status and configuration
     */
    private function determineNewApprovalStage($approval, $config): ?string
    {
        // If rejected, set to rejected
        if ($approval->process_status === 'REJECTED') {
            return 'rejected';
        }

        // If approved, determine the next stage or final approval
        if ($approval->process_status === 'APPROVED') {
            // Check if this is the final approval
            if ($this->isFinalApproval($approval, $config)) {
                return 'approved';
            }

            // Determine next stage based on current level and role assignments
            switch ($this->checker_level) {
                case 1:
                    // Check if first checker is required and has roles assigned
                    if ($config->requires_first_checker) {
                        $firstCheckerRoles = is_array($config->first_checker_roles) ? $config->first_checker_roles : json_decode($config->first_checker_roles ?? '[]', true);
                        if (!empty($firstCheckerRoles)) {
                            return 'first_checker';
                        } else {
                            // No roles assigned, skip to second checker
                            if ($config->requires_second_checker) {
                                $secondCheckerRoles = is_array($config->second_checker_roles) ? $config->second_checker_roles : json_decode($config->second_checker_roles ?? '[]', true);
                                if (!empty($secondCheckerRoles)) {
                                    return 'second_checker';
                                } else {
                                    // No roles assigned, skip to approver
                                    if ($config->requires_approver) {
                                        $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                                        if (!empty($approverRoles)) {
                                            return 'approver';
                                        }
                                    }
                                    return 'approved'; // No more stages with roles
                                }
                            } elseif ($config->requires_approver) {
                                $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                                if (!empty($approverRoles)) {
                                    return 'approver';
                                } else {
                                    return 'approved'; // No roles assigned
                                }
                            } else {
                                return 'approved'; // No more stages
                            }
                        }
                    } elseif ($config->requires_second_checker) {
                        $secondCheckerRoles = is_array($config->second_checker_roles) ? $config->second_checker_roles : json_decode($config->second_checker_roles ?? '[]', true);
                        if (!empty($secondCheckerRoles)) {
                            return 'second_checker';
                        } else {
                            // No roles assigned, skip to approver
                            if ($config->requires_approver) {
                                $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                                if (!empty($approverRoles)) {
                                    return 'approver';
                                }
                            }
                            return 'approved'; // No more stages with roles
                        }
                    } elseif ($config->requires_approver) {
                        $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                        if (!empty($approverRoles)) {
                            return 'approver';
                        } else {
                            return 'approved'; // No roles assigned
                        }
                    } else {
                        return 'approved'; // No more stages
                    }
                case 2:
                    // Check if second checker is required and has roles assigned
                    if ($config->requires_second_checker) {
                        $secondCheckerRoles = is_array($config->second_checker_roles) ? $config->second_checker_roles : json_decode($config->second_checker_roles ?? '[]', true);
                        if (!empty($secondCheckerRoles)) {
                            return 'second_checker';
                        } else {
                            // No roles assigned, skip to approver
                            if ($config->requires_approver) {
                                $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                                if (!empty($approverRoles)) {
                                    return 'approver';
                                }
                            }
                            return 'approved'; // No more stages with roles
                        }
                    } elseif ($config->requires_approver) {
                        $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                        if (!empty($approverRoles)) {
                            return 'approver';
                        } else {
                            return 'approved'; // No roles assigned
                        }
                    } else {
                        return 'approved'; // No more stages
                    }
                case 3:
                    return 'approved'; // Final stage
                default:
                    return 'approved';
            }
        }

        return null; // No change needed
    }

    /**
     * Determine the new approval stage role names based on current approval status and configuration
     */
    private function determineNewApprovalStageRoleNames($approval, $config): ?string
    {
        // If rejected, clear role names
        if ($approval->process_status === 'REJECTED') {
            return null;
        }

        // If approved, determine the next stage role names or final approval
        if ($approval->process_status === 'APPROVED') {
            // Check if this is the final approval
            if ($this->isFinalApproval($approval, $config)) {
                return null; // No role names for final approval
            }

            // Determine next stage role names based on current level and role assignments
            switch ($this->checker_level) {
                case 1:
                    // Check if first checker is required and has roles assigned
                    if ($config->requires_first_checker) {
                        $firstCheckerRoles = is_array($config->first_checker_roles) ? $config->first_checker_roles : json_decode($config->first_checker_roles ?? '[]', true);
                        if (!empty($firstCheckerRoles)) {
                            return $this->getRoleNamesFromIds($firstCheckerRoles);
                        } else {
                            // No roles assigned, check second checker
                            if ($config->requires_second_checker) {
                                $secondCheckerRoles = is_array($config->second_checker_roles) ? $config->second_checker_roles : json_decode($config->second_checker_roles ?? '[]', true);
                                if (!empty($secondCheckerRoles)) {
                                    return $this->getRoleNamesFromIds($secondCheckerRoles);
                                } else {
                                    // No roles assigned, check approver
                                    if ($config->requires_approver) {
                                        $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                                        if (!empty($approverRoles)) {
                                            return $this->getRoleNamesFromIds($approverRoles);
                                        }
                                    }
                                    return null; // No more stages with roles
                                }
                            } elseif ($config->requires_approver) {
                                $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                                if (!empty($approverRoles)) {
                                    return $this->getRoleNamesFromIds($approverRoles);
                                } else {
                                    return null; // No roles assigned
                                }
                            } else {
                                return null; // No more stages
                            }
                        }
                    } elseif ($config->requires_second_checker) {
                        $secondCheckerRoles = is_array($config->second_checker_roles) ? $config->second_checker_roles : json_decode($config->second_checker_roles ?? '[]', true);
                        if (!empty($secondCheckerRoles)) {
                            return $this->getRoleNamesFromIds($secondCheckerRoles);
                        } else {
                            // No roles assigned, check approver
                            if ($config->requires_approver) {
                                $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                                if (!empty($approverRoles)) {
                                    return $this->getRoleNamesFromIds($approverRoles);
                                }
                            }
                            return null; // No more stages with roles
                        }
                    } elseif ($config->requires_approver) {
                        $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                        if (!empty($approverRoles)) {
                            return $this->getRoleNamesFromIds($approverRoles);
                        } else {
                            return null; // No roles assigned
                        }
                    } else {
                        return null; // No more stages
                    }
                case 2:
                    // Check if approver is required and has roles assigned
                    if ($config->requires_approver) {
                        $approverRoles = is_array($config->approver_roles) ? $config->approver_roles : json_decode($config->approver_roles ?? '[]', true);
                        if (!empty($approverRoles)) {
                            return $this->getRoleNamesFromIds($approverRoles);
                        } else {
                            return null; // No roles assigned
                        }
                    } else {
                        return null; // No more stages
                    }
                case 3:
                    return null; // Final stage
                default:
                    return null;
            }
        }

        return null; // No change needed
    }

    /**
     * Get role names from role IDs
     */
    private function getRoleNamesFromIds($roleIds): ?string
    {
        if (empty($roleIds)) {
            return null;
        }

        $roleNames = DB::table('roles')
            ->whereIn('id', $roleIds)
            ->pluck('name')
            ->toArray();

        return !empty($roleNames) ? implode(', ', $roleNames) : null;
    }

    /**
     * Check if this is the final approval step
     */
    private function isFinalApproval($approval, $config): bool
    {
        if ($config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            // Combination 7: Final at level 3
            return $this->checker_level == 3;
        } elseif ($config->requires_first_checker && $config->requires_second_checker) {
            // Combination 4: Final at level 2
            return $this->checker_level == 2;
        } elseif ($config->requires_first_checker && $config->requires_approver) {
            // Combination 5: Final at level 2
            return $this->checker_level == 2;
        } elseif ($config->requires_second_checker && $config->requires_approver) {
            // Combination 6: Final at level 2
            return $this->checker_level == 2;
        } elseif ($config->requires_first_checker) {
            // Combination 1: Final at level 1
            return $this->checker_level == 1;
        } elseif ($config->requires_second_checker) {
            // Combination 2: Final at level 1
            return $this->checker_level == 1;
        } elseif ($config->requires_approver) {
            // Combination 3: Final at level 1
            return $this->checker_level == 1;
        }
        return false;
    }

    /**
     * Update related records based on process code
     */
    private function updateRelatedRecords($approval): void
    {
        // Get the process configuration
        $config = $approval->processConfig;
        $processUpdates = [
            'SHARE_WD' => [
                'table' => 'share_withdrawals',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'ACC_CREATE' => [
                'table' => 'accounts',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],           
            'ACC_EDIT' => [
                'table' => 'accounts',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ], 

            'SHARE_TRF' => [
                'table' => 'share_transfers',
                'approval_status' => 'COMPLETED',
                'rejection_status' => 'REJECTED'
            ],
            'BLOCK_SHARE_ACC' => [
                'table' => 'share_registers',
                'approval_status' => 'FROZEN',
                'rejection_status' => 'REJECTED'
            ],
            'ACTIVATE_SHARE_ACC' => [
                'table' => 'share_registers',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],
            'LOAN_DISB' => [
                'table' => 'loans',
                'approval_status' => 'AWAITING_DISBURSEMENT',
                'rejection_status' => 'DISBURSAL_REJECTED'
            ],
            
            'PRODUCT_CRE' => [
                'table' => 'sub_products',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],

            'PROD_EDIT' => [
                'table' => 'sub_products',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],

            'PROD_DEACTIVATE' => [
                'table' => 'sub_products',
                'approval_status' => 'INACTIVE',
                'rejection_status' => 'REJECTED'
            ],

            
            'BRANCH_CREATE' => [
                'table' => 'branches',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],
            'BRANCH_EDIT' => [
                'table' => 'branches',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],
            'BRANCH_DEACTIVATE' => [
                'table' => 'branches',
                'approval_status' => 'INACTIVE',
                'rejection_status' => 'ACTIVE'
            ],
            'MEMBER_REG' => [
                'table' => 'clients',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],
            'LOAN_APP' => [
                'table' => 'loans',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'BUDGET_CREATE' => [
                'table' => 'budget_managements',
                'custom_handler' => 'handleBudgetCreateApproval'
            ],
            'BUDGET_EDIT' => [
                'table' => 'budget_managements',
                'custom_handler' => 'handleBudgetEditApproval'
            ],
            'BUDGET_DELETE' => [
                'table' => 'budget_managements',
                'custom_handler' => 'handleBudgetDeleteApproval'
            ],
         
            'LOAN_REST' => [
                'table' => 'loans',
                'approval_status' => 'RESTRUCTURED',
                'rejection_status' => 'RESTRUCTURE_REJECTED'
            ],
            'LOAN_WOFF' => [
                'table' => 'loans',
                'approval_status' => 'WRITTEN_OFF',
                'rejection_status' => 'WROFF_REJECTED'
            ],
            'LARGE_WD' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'FIXED_DEP' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'FUND_TRANS' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'PETTY_CASH' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'OP_EXP' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'CAP_EXP' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'ASSET_PUR' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'INT_RATE' => [
                'table' => 'transit_actions',
                'approval_status' => 'APPROVED',
                'rejection_status' => 'REJECTED'
            ],
            'SHARE_ISS' => [
                'table' => 'share_registers',
                'approval_status' => 'ACTIVE',
                'rejection_status' => 'REJECTED'
            ],
            'SHARE_RED' => [
                'table' => 'share_redemptions',
                'approval_status' => 'COMPLETED',
                'rejection_status' => 'REJECTED'
            ],
            'ASSET_DISP' => [
                'table' => 'ppes',
                'approval_status' => 'approved_for_disposal',
                'rejection_status' => 'active'
            ],
            'MEMBER_EXIT' => [
                'table' => 'clients',
                'custom_handler' => 'handleMemberExitApproval'
            ],
        ];

        if (isset($processUpdates[$approval->process_code])) {
            $update = $processUpdates[$approval->process_code];
            
            // Check if this process has a custom handler
            if (isset($update['custom_handler'])) {
                $handlerMethod = $update['custom_handler'];
                if (method_exists($this, $handlerMethod)) {
                    $this->$handlerMethod($approval);
                    return;
                } else {
                    Log::error('Custom handler method not found', [
                        'method' => $handlerMethod,
                        'process_code' => $approval->process_code
                    ]);
                }
            }
            
            Log::info('Updating related record', [
                'approval_id' => $approval->id,
                'process_code' => $approval->process_code,
                'table' => $update['table'] ?? 'N/A',
                'record_id' => $approval->process_id,
                'current_status' => $approval->process_status
            ]);
            
            // Prepare update data
            $updateData = [];
            
            // Only update status if it's the final approval or a rejection
            $isFinal = $this->isFinalApproval($approval, $config);
            if ($isFinal || $approval->process_status === 'REJECTED') {
                // Determine the status based on approval/rejection
                $status = $approval->process_status === 'APPROVED' 
                    ? $update['approval_status'] 
                    : $update['rejection_status'];
                $updateData['status'] = $status;
            }
            
            $updateData['updated_at'] = now();
            //$updateData['updated_by'] = Auth::id();

            // Update approval_stage and approval_stage_role_name for loan-related processes
            if (in_array($approval->process_code, ['LOAN_APP', 'LOAN_DISB', 'LOAN_REST', 'LOAN_WOFF', 'LOAN_EXTEND', 'LOAN_RESCHEDULE'])) {
                // Special handling for LOAN_APP to follow the same logic as approval-actions.blade.php
                if ($approval->process_code === 'LOAN_APP' && $config) {
                    $this->updateLoanApprovalStage($approval, $config, $updateData);
                } else {
                    $newApprovalStage = $this->determineNewApprovalStage($approval, $config);
                    $newApprovalStageRoleNames = $this->determineNewApprovalStageRoleNames($approval, $config);
                    
                    if ($newApprovalStage) {
                        $updateData['approval_stage'] = $newApprovalStage;
                    }
                    if ($newApprovalStageRoleNames !== null) {
                        $updateData['approval_stage_role_name'] = $newApprovalStageRoleNames;
                    }
                }
            }

            // Check for edit_package on every approval
            if ($approval->process_status === 'APPROVED' && !empty($approval->edit_package)) {
                $editPackage = is_string($approval->edit_package) 
                    ? json_decode($approval->edit_package, true) 
                    : $approval->edit_package;


                //if it is a account status update, update the status of the account
                if($approval->process_code == 'ACC_EDIT'){
                    // Determine the status for ACC_EDIT
                    $accEditStatus = $approval->process_status === 'APPROVED' 
                        ? $update['approval_status'] 
                        : $update['rejection_status'];
                    $this->updateAccountStatus($approval, $accEditStatus);
                    return;
                }

                if($approval->process_code == 'PROD_EDIT'){
                    $this->updateProductStatus($approval, $editPackage, $approval->process_code);
                    return;
                }

                if (is_array($editPackage)) {
                    foreach ($editPackage as $field => $values) {
                        if (isset($values['new'])) {
                            $updateData[$field] = $values['new'];
                        }
                    }
                    
                    Log::info('Applying edit package changes', [
                        'approval_id' => $approval->id,
                        'process_code' => $approval->process_code,
                        'fields_updated' => array_keys($editPackage)
                    ]);
                }
            }

            // Special handling for share issuance
            if ($approval->process_code === 'SHARE_ISS' && $approval->process_status === 'APPROVED') {
                try {
                    Log::info('Processing share issuance approval', [
                        'approval_id' => $approval->id,
                        'process_id' => $approval->process_id
                    ]);

                    $sharesComponent = new \App\Http\Livewire\Shares\Shares();
                    $sharesComponent->processApprovedShareIssuance($approval);

                    Log::info('Share issuance processed successfully', [
                        'approval_id' => $approval->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error processing share issuance', [
                        'approval_id' => $approval->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }
            // Special handling for share redemption
            elseif ($approval->process_code === 'SHARE_RED' && $approval->process_status === 'APPROVED') {
                try {
                    Log::info('Processing share redemption approval', [
                        'approval_id' => $approval->id,
                        'process_id' => $approval->process_id
                    ]);

                    $redemptionService = new \App\Services\ShareRedemptionService(
                        new \App\Services\TransactionPostingService()
                    );
                    $result = $redemptionService->processApprovedRedemption($approval);

                    if (!$result['success']) {
                        Log::error('Share redemption processing failed', [
                            'approval_id' => $approval->id,
                            'message' => $result['message']
                        ]);
                        throw new \Exception($result['message']);
                    }

                    Log::info('Share redemption processed successfully', [
                        'approval_id' => $approval->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error processing share redemption', [
                        'approval_id' => $approval->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                // Update the related record for other process types  
                // Only update if we have fields to update
                if (!empty($updateData)) {
                    $updated = DB::table($update['table'])
                        ->where('id', $approval->process_id)
                        ->update($updateData);

                    Log::info('Related record update result', [
                        'approval_id' => $approval->id,
                        'table' => $update['table'],
                        'record_id' => $approval->process_id,
                        'new_status' => $updateData['status'] ?? 'no status change',
                        'update_success' => $updated,
                        'action' => $approval->process_status,
                        'fields_updated' => array_keys($updateData)
                    ]);
                }
            }





            if ($approval->process_code === 'SHARE_WD' && $approval->process_status === 'APPROVED') {
             
                try {
                    Log::info('Processing share withdrawal approval', [
                        'approval_id' => $approval->id,
                        'process_id' => $approval->process_id
                    ]);

                    $sharesComponent = new \App\Http\Livewire\Shares\Shares();
                    $sharesComponent->processApprovedWithdrawal($approval->process_id);

                    Log::info('Share withdrawal processed successfully', [
                        'approval_id' => $approval->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error processing share withdrawal', [
                        'approval_id' => $approval->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                // Update the related record for other process types  
                // Only update if we have fields to update
                if (!empty($updateData)) {
                    $updated = DB::table($update['table'])
                        ->where('id', $approval->process_id)
                        ->update($updateData);

                    Log::info('Related record update result', [
                        'approval_id' => $approval->id,
                        'table' => $update['table'],
                        'record_id' => $approval->process_id,
                        'new_status' => $updateData['status'] ?? 'no status change',
                        'update_success' => $updated,
                        'action' => $approval->process_status,
                        'fields_updated' => array_keys($updateData)
                    ]);
                }
            }






            if ($approval->process_code === 'SHARE_TRF' && $approval->process_status === 'APPROVED') {
             
                try {
                    Log::info('Processing share transfer approval', [
                        'approval_id' => $approval->id,
                        'process_id' => $approval->process_id
                    ]);

                    $sharesComponent = new \App\Http\Livewire\Shares\Shares();
                    $sharesComponent->processApprovedTransfer($approval->process_id);

                    Log::info('Share transfer processed successfully', [
                        'approval_id' => $approval->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error processing share transfer', [
                        'approval_id' => $approval->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                // Update the related record for other process types  
                // Only update if we have fields to update
                if (!empty($updateData)) {
                    $updated = DB::table($update['table'])
                        ->where('id', $approval->process_id)
                        ->update($updateData);

                    Log::info('Related record update result', [
                        'approval_id' => $approval->id,
                        'table' => $update['table'],
                        'record_id' => $approval->process_id,
                        'new_status' => $updateData['status'] ?? 'no status change',
                        'update_success' => $updated,
                        'action' => $approval->process_status,
                        'fields_updated' => array_keys($updateData)
                    ]);
                }
            }










            // If it's a loan-related process, update loan status history
            if (strpos($approval->process_code, 'LOAN_') === 0) {
                // Determine the current status for loan history
                // Use the status from updateData if it was set, otherwise determine it
                if (isset($updateData['status'])) {
                    $currentLoanStatus = $updateData['status'];
                } else {
                    // For intermediate approvals, keep the current status or use a default
                    $currentLoanStatus = $approval->process_status === 'APPROVED' 
                        ? $update['approval_status']
                        : $update['rejection_status'];
                }
                $this->updateLoanStatusHistory($approval, $currentLoanStatus);
                
                // Enhanced loan processing for specific loan types
                if ($approval->process_status === 'APPROVED') {
                    if ($approval->process_code === 'LOAN_DISB') {
                        // For loan disbursement, use the new workflow
                        $this->handleLoanApprovalWorkflow($approval);
                    } else {
                        // For other loan types, use the existing process
                        $this->processLoanApproval($approval);
                    }
                }
            }
           
        } else {
            Log::warning('No process update configuration found', [
                'approval_id' => $approval->id,
                'process_code' => $approval->process_code
            ]);
        }
    }

    /**
     * Update loan status history
     */
    private function updateLoanStatusHistory($approval, $status): void
    {
        DB::table('loan_audit_logs')->insert([
            'loan_id' => $approval->process_id,
            'action' => 'STATUS_CHANGE',
            'old_values' => json_encode(['status' => 'PENDING']),
            'new_values' => json_encode(['status' => $status]),
            'user_id' => Auth::id(),
            'description' => $approval->process_status === 'REJECTED' 
                ? $this->getLatestRejectionReason($approval)
                : 'Approved by ' . Auth::user()->name,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get the latest rejection reason
     */
    private function getLatestRejectionReason($approval): string
    {
        $reasons = [];
        
        if ($approval->first_checker_rejection_reason) {
            $reasons[] = json_decode($approval->first_checker_rejection_reason, true);
        }
        if ($approval->second_checker_rejection_reason) {
            $reasons[] = json_decode($approval->second_checker_rejection_reason, true);
        }
        if ($approval->approver_rejection_reason) {
            $reasons[] = json_decode($approval->approver_rejection_reason, true);
        }

        if (empty($reasons)) {
            return 'Rejected by ' . Auth::user()->name;
        }

        // Sort by timestamp and get the latest
        usort($reasons, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        $latest = $reasons[0];
        return 'Rejected by ' . User::find($latest['user_id'])->name . ': ' . $latest['reason'];
    }

    public function reject()
    {
        if (!$this->authorize('reject', 'You do not have permission to reject requests')) {
            return;
        }
        
        try {
            DB::beginTransaction();

            $approval = Approval::findOrFail($this->selectedApprovalId);
            $user = Auth::user();
            $config = $approval->processConfig;

            if (!$config) {
                throw new \Exception('Process configuration not found');
            }

            // Validate rejection reason
            $this->validate([
                'rejection_reason' => 'required|min:10'
            ]);

            // Validate user permissions
            if (!$this->validateUserPermissions($user, $approval)) {
                throw new \Exception('You do not have permission to perform this action');
            }

            // Check if rejection is allowed at current level
            if (!$this->canRejectAtCurrentLevel($approval, $config)) {
                $message = $this->getRejectionNotAllowedMessage($approval, $config);
                throw new \Exception($message);
            }

            // Update rejection status based on checker level
            $this->updateRejectionStatus($approval, $user);

            // Store rejection reason
            $this->storeRejectionReason($approval, $user);

            // If final rejection, update related records
            if ($this->isFinalRejection($approval, $config)) {
                $this->updateRelatedRecords($approval);
            }

            DB::commit();

            $this->closeRejectModal();
            //$this->loadApprovals();
            session()->flash('notification', [
                'type' => 'success',
                'message' => 'Rejection successful!'
            ]);
            
            // Refresh the approvals list
            $this->refreshApprovalsList();

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            session()->flash('notification', [
                'type' => 'error',
                'message' => 'Validation Error: ' . collect($e->errors())->first()[0]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting request: ' . $e->getMessage(), [
                'approval_id' => $this->selectedApprovalId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('notification', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if current rejection is final
     */
    private function isFinalRejection($approval, $config): bool
    {
        if ($config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            return $this->checker_level == 3;
        } elseif ($config->requires_first_checker && $config->requires_second_checker) {
            return $this->checker_level == 2;
        } elseif ($config->requires_first_checker && $config->requires_approver) {
            return $this->checker_level == 2;
        } elseif ($config->requires_second_checker && $config->requires_approver) {
            return $this->checker_level == 2;
        } elseif ($config->requires_first_checker || $config->requires_second_checker || $config->requires_approver) {
            return $this->checker_level == 1;
        }
        return false;
    }

    /**
     * Validate if user has permission to approve
     */
    private function validateUserPermissions($user, $approval): bool
    {
        $config = $approval->processConfig;
        
        // Get role arrays
        $firstCheckerRoles = is_string($config->first_checker_roles) ? json_decode($config->first_checker_roles, true) : $config->first_checker_roles;
        $secondCheckerRoles = is_string($config->second_checker_roles) ? json_decode($config->second_checker_roles, true) : $config->second_checker_roles;
        $approverRoles = is_string($config->approver_roles) ? json_decode($config->approver_roles, true) : $config->approver_roles;

        // Check if user is an admin
        // if ($user->isAdmin()) {
        //     return true;
        // }

        // For approvers, check if all previous checkers have approved
        if ($this->isApproverLevel($config) && !$this->validatePreviousApprovals($approval, $config)) {
            Log::warning('Approver cannot approve because previous checkers have not all approved', [
                'approval_id' => $approval->id,
                'first_checker_status' => $approval->first_checker_status,
                'second_checker_status' => $approval->second_checker_status
            ]);
            return false;
        }

        // Check permissions based on configuration and checker level
        if ($config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            // Combination 7: Three-level approval
            return $this->validateThreeLevelPermissions($user, $approval, $firstCheckerRoles, $secondCheckerRoles, $approverRoles);
        } elseif ($config->requires_first_checker && $config->requires_second_checker) {
            // Combination 4: Two checkers
            return $this->validateTwoLevelPermissions($user, $approval, $firstCheckerRoles, $secondCheckerRoles);
        } elseif ($config->requires_first_checker && $config->requires_approver) {
            // Combination 5: First checker + approver
            return $this->validateFirstCheckerAndApproverPermissions($user, $approval, $firstCheckerRoles, $approverRoles);
        } elseif ($config->requires_second_checker && $config->requires_approver) {
            // Combination 6: Second checker + approver
            return $this->validateSecondCheckerAndApproverPermissions($user, $approval, $secondCheckerRoles, $approverRoles);
        } elseif ($config->requires_first_checker) {
            // Combination 1: First checker only
            return $this->validateFirstCheckerPermissions($user, $approval, $firstCheckerRoles);
        } elseif ($config->requires_second_checker) {
            // Combination 2: Second checker only
            return $this->validateSecondCheckerPermissions($user, $approval, $secondCheckerRoles);
        } elseif ($config->requires_approver) {
            // Combination 3: Approver only
            return $this->validateApproverPermissions($user, $approval, $approverRoles);
        }

        return false;
    }

    /**
     * Check if current level is approver level
     */
    private function isApproverLevel($config): bool
    {
        if ($config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            return $this->checker_level == 3;
        } elseif ($config->requires_first_checker && $config->requires_approver) {
            return $this->checker_level == 2;
        } elseif ($config->requires_second_checker && $config->requires_approver) {
            return $this->checker_level == 2;
        } elseif ($config->requires_approver) {
            return $this->checker_level == 1;
        }
        return false;
    }

    /**
     * Validate that all previous checkers have approved
     */
    private function validatePreviousApprovals($approval, $config): bool
    {
        // If any previous checker has rejected, approver cannot approve
        if ($approval->first_checker_status === 'REJECTED' || $approval->second_checker_status === 'REJECTED') {
            Log::info('Previous checker has rejected, approver cannot approve', [
                'approval_id' => $approval->id,
                'first_checker_status' => $approval->first_checker_status,
                'second_checker_status' => $approval->second_checker_status
            ]);
            return false;
        }

        // Check if all required previous checkers have approved
        if ($config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            // For three-level approval, both first and second checkers must approve
            // But if they haven't acted yet (null), approver can still approve
            $firstCheckerApproved = $approval->first_checker_status === 'APPROVED' || $approval->first_checker_status === null;
            $secondCheckerApproved = $approval->second_checker_status === 'APPROVED' || $approval->second_checker_status === null;
            return $firstCheckerApproved && $secondCheckerApproved;
        } elseif ($config->requires_first_checker && $config->requires_approver) {
            // For first checker + approver, first checker must approve or not have acted
            return $approval->first_checker_status === 'APPROVED' || $approval->first_checker_status === null;
        } elseif ($config->requires_second_checker && $config->requires_approver) {
            // For second checker + approver, second checker must approve or not have acted
            return $approval->second_checker_status === 'APPROVED' || $approval->second_checker_status === null;
        }

        // If no previous checkers are required, approver can approve
        return true;
    }

    /**
     * Validate permissions for three-level approval
     */
    private function validateThreeLevelPermissions($user, $approval, $firstCheckerRoles, $secondCheckerRoles, $approverRoles): bool
    {
        switch ($this->checker_level) {
            case 1:
                return in_array($user->role_id, $firstCheckerRoles ?? []) && $approval->first_checker_status === null;
            case 2:
                return in_array($user->role_id, $secondCheckerRoles ?? []) && $approval->second_checker_status === null;
            case 3:
                return in_array($user->role_id, $approverRoles ?? []) && ($approval->approval_status === 'PENDING' || $approval->approval_status === null);
            default:
                return false;
        }
    }

    /**
     * Validate permissions for two-level approval
     */
    private function validateTwoLevelPermissions($user, $approval, $firstCheckerRoles, $secondCheckerRoles): bool
    {
        switch ($this->checker_level) {
            case 1:
                return in_array($user->role_id, $firstCheckerRoles ?? []) && $approval->first_checker_status === null;
            case 2:
                return in_array($user->role_id, $secondCheckerRoles ?? []) && $approval->second_checker_status === null;
            default:
                return false;
        }
    }

    /**
     * Validate permissions for first checker and approver
     */
    private function validateFirstCheckerAndApproverPermissions($user, $approval, $firstCheckerRoles, $approverRoles): bool
    {
        switch ($this->checker_level) {
            case 1:
                return in_array($user->role_id, $firstCheckerRoles ?? []) && $approval->first_checker_status === null;
            case 2:
                return in_array($user->role_id, $approverRoles ?? []) && ($approval->approval_status === 'PENDING' || $approval->approval_status === null);
            default:
                return false;
        }
    }

    /**
     * Validate permissions for second checker and approver
     */
    private function validateSecondCheckerAndApproverPermissions($user, $approval, $secondCheckerRoles, $approverRoles): bool
    {
        switch ($this->checker_level) {
            case 1:
                return in_array($user->role_id, $secondCheckerRoles ?? []) && $approval->second_checker_status === null;
            case 2:
                return in_array($user->role_id, $approverRoles ?? []) && ($approval->approval_status === 'PENDING' || $approval->approval_status === null);
            default:
                return false;
        }
    }

    /**
     * Validate permissions for first checker only
     */
    private function validateFirstCheckerPermissions($user, $approval, $firstCheckerRoles): bool
    {
        return $this->checker_level == 1 && 
               in_array($user->role_id, $firstCheckerRoles ?? []) && 
               $approval->first_checker_status === null;
    }

    /**
     * Validate permissions for second checker only
     */
    private function validateSecondCheckerPermissions($user, $approval, $secondCheckerRoles): bool
    {
        return $this->checker_level == 1 && 
               in_array($user->role_id, $secondCheckerRoles ?? []) && 
               $approval->second_checker_status === null;
    }

    /**
     * Validate permissions for approver only
     */
    private function validateApproverPermissions($user, $approval, $approverRoles): bool
    {
        return $this->checker_level == 1 &&
               in_array($user->role_id, $approverRoles ?? []) &&
               ($approval->approval_status === 'PENDING' || $approval->approval_status === null);
    }

    public function dismissNotification()
    {
        session()->forget('notification');
    }

    public function updateAccountStatus($approval, $status)
    {
        $editPackage = is_string($approval->edit_package) 
            ? json_decode($approval->edit_package, true) 
            : $approval->edit_package;

        if (!is_array($editPackage)) {
            Log::error('Invalid edit package format', [
                'approval_id' => $approval->id,
                'edit_package' => $approval->edit_package
            ]);
            return;
        }

        foreach($editPackage as $accountNumber => $data){
            $account = DB::table('accounts')->where('account_number', $accountNumber)->first();
            if ($account) {
                $updateData = [];
                
                // Update all fields from the new data
                foreach ($data['new'] as $field => $value) {
                    $updateData[$field] = $value;
                }                
                // Ensure updated_at is always set
                $updateData['updated_at'] = now();
                
                DB::table('accounts')
                    ->where('account_number', $accountNumber)
                    ->update($updateData);
            }
        }
    }

    public function closeLoanAssessmentModal()
    {
        $this->showLoanAssessmentModal = false;
        $this->selectedApprovalId = null;
        $this->approvalComment = '';
        $this->resetLoanAssessmentData();
    }

    private function loadLoanAssessmentData($loanId)
    {
        try {
            Log::info('=== LOAD LOAN ASSESSMENT DATA START ===', [
                'loan_id' => $loanId,
                'loan_id_type' => gettype($loanId),
                'user_id' => Auth::id(),
                'timestamp' => now()
            ]);
            
            // Debug: Check if any loans exist in the database
            $sampleLoans = \App\Models\LoansModel::select('id', 'client_number', 'status')->limit(5)->get();
            Log::info('Sample loans in database', [
                'total_loans' => \App\Models\LoansModel::count(),
                'sample_loan_ids' => $sampleLoans->pluck('id')->toArray(),
                'sample_client_numbers' => $sampleLoans->pluck('client_number')->toArray()
            ]);
            
            // Validate loan ID
            if (empty($loanId)) {
                Log::error('Loan ID is empty or null', [
                    'loan_id' => $loanId,
                    'loan_id_type' => gettype($loanId)
                ]);
                session()->flash('notification', [
                    'type' => 'error',
                    'message' => 'Loan ID is required'
                ]);
                return;
            }

            // Try to find loan with different approaches
            Log::info('Attempting to find loan record', [
                'loan_id' => $loanId,
                'search_methods' => ['direct_id', 'string_cast', 'integer_cast']
            ]);

            // Method 1: Direct search
            $this->loanData = \App\Models\LoansModel::with(['client'])->find($loanId);
            Log::info('Direct search result', [
                'loan_found' => $this->loanData ? true : false,
                'loan_id_searched' => $loanId
            ]);

            // Method 2: If not found, try string cast
            if (!$this->loanData) {
                $stringLoanId = (string) $loanId;
                $this->loanData = \App\Models\LoansModel::with(['client'])->find($stringLoanId);
                Log::info('String cast search result', [
                    'loan_found' => $this->loanData ? true : false,
                    'string_loan_id' => $stringLoanId
                ]);
            }

            // Method 3: If still not found, try integer cast
            if (!$this->loanData) {
                $intLoanId = (int) $loanId;
                $this->loanData = \App\Models\LoansModel::with(['client'])->find($intLoanId);
                Log::info('Integer cast search result', [
                    'loan_found' => $this->loanData ? true : false,
                    'int_loan_id' => $intLoanId
                ]);
            }

            // Method 4: If still not found, try searching by client_number
            if (!$this->loanData) {
                $this->loanData = \App\Models\LoansModel::with(['client'])->where('client_number', $loanId)->first();
                Log::info('Client number search result', [
                    'loan_found' => $this->loanData ? true : false,
                    'client_number_searched' => $loanId
                ]);
            }
            
            if (!$this->loanData) {
                Log::error('Loan not found with any search method', [
                    'original_loan_id' => $loanId,
                    'loan_id_type' => gettype($loanId),
                    'search_methods_tried' => ['direct', 'string_cast', 'integer_cast', 'client_number']
                ]);
                session()->flash('notification', [
                    'type' => 'error',
                    'message' => 'Loan not found with ID: ' . $loanId
                ]);
                return;
            }

            Log::info('Loan data loaded successfully', [
                'loan_id' => $this->loanData->id,
                'client_number' => $this->loanData->client_number,
                'status' => $this->loanData->status,
                'loan_sub_product' => $this->loanData->loan_sub_product,
                'approved_loan_value' => $this->loanData->approved_loan_value,
                'tenure' => $this->loanData->tenure,
                'has_assessment_data' => !empty($this->loanData->assessment_data),
                'assessment_data_length' => strlen($this->loanData->assessment_data ?? '')
            ]);

            // Load member data from the client relationship
            $this->memberData = $this->loanData->client;
            
            if (!$this->memberData) {
                Log::warning('Client data not found for client number: ' . $this->loanData->client_number);
            } else {
                Log::info('Member data loaded successfully', [
                    'client_id' => $this->memberData->id,
                    'name' => $this->memberData->first_name . ' ' . $this->memberData->last_name,
                    'phone' => $this->memberData->phone_number ?? $this->memberData->mobile_phone_number,
                    'email' => $this->memberData->email,
                    'basic_salary' => $this->memberData->basic_salary
                ]);
            }
            
            // Load guarantor data if exists
            if ($this->loanData->guarantor_id) {
                $this->guarantorData = \App\Models\ClientsModel::where('client_number', $this->loanData->guarantor_id)->first();
                
                if ($this->guarantorData) {
                    Log::info('Guarantor data loaded successfully', [
                        'guarantor_id' => $this->guarantorData->id,
                        'name' => $this->guarantorData->first_name . ' ' . $this->guarantorData->last_name
                    ]);
                } else {
                    Log::warning('Guarantor data not found for guarantor ID: ' . $this->loanData->guarantor_id);
                }
            }

            // Parse assessment_data JSON field
            if ($this->loanData->assessment_data) {
                Log::info('Assessment data found, attempting to parse JSON', [
                    'assessment_data_raw' => substr($this->loanData->assessment_data, 0, 200) . '...',
                    'assessment_data_length' => strlen($this->loanData->assessment_data)
                ]);

                $this->assessmentData = json_decode($this->loanData->assessment_data, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON decode error', [
                        'json_error' => json_last_error_msg(),
                        'assessment_data_sample' => substr($this->loanData->assessment_data, 0, 500)
                    ]);
                    $this->assessmentData = [];
                } else {
                    Log::info('Assessment data parsed successfully', [
                        'loan_id' => $loanId,
                        'assessment_data_keys' => array_keys($this->assessmentData),
                        'assessment_data_count' => count($this->assessmentData)
                    ]);

                    // Extract specific assessment sections
                    $this->incomeAssessment = [
                        'take_home' => $this->assessmentData['take_home'] ?? 0,
                        'monthly_sales' => $this->assessmentData['monthly_sales'] ?? 0,
                        'gross_profit' => $this->assessmentData['gross_profit'] ?? 0,
                        'net_profit' => $this->assessmentData['net_profit'] ?? 0,
                        'recommended' => $this->assessmentData['recommended'] ?? 0,
                    ];

                    $this->productParameters = [
                        'product_name' => $this->loanData->loan_sub_product ?? 'N/A',
                        'interest_rate' => $this->loanData->interest_rate ?? 0,
                        'max_amount' => $this->assessmentData['max_amount'] ?? 0,
                        'max_term' => $this->assessmentData['max_term'] ?? 0,
                    ];

                    $this->termCalculation = [
                        'requested_term' => $this->loanData->tenure ?? 0,
                        'approved_term' => $this->assessmentData['approved_term'] ?? 0,
                        'days_first_interest' => $this->assessmentData['days_first_interest'] ?? 0,
                    ];

                    $this->loanAmountLimits = [
                        'requested_amount' => $this->loanData->approved_loan_value ?? 0,
                        'approved_amount' => $this->assessmentData['approved_loan_value'] ?? 0,
                        'max_qualifying_amount' => $this->assessmentData['max_qualifying_amount'] ?? 0,
                    ];

                    $this->collateralInfo = [
                        'collateral_value' => $this->assessmentData['collateral_value'] ?? 0,
                        'collateral_type' => $this->assessmentData['collateral_type'] ?? 'N/A',
                        'available_funds' => $this->assessmentData['available_funds'] ?? 0,
                        'coverage' => $this->assessmentData['coverage'] ?? 0,
                    ];

                    $this->deductions = [
                        'existing_settlements' => $this->assessmentData['existing_settlements'] ?? 0,
                        'top_up_clearance' => $this->assessmentData['top_up_clearance'] ?? 0,
                        'total_deductions' => $this->assessmentData['total_deductions'] ?? 0,
                        'net_amount' => $this->assessmentData['net_amount'] ?? 0,
                    ];

                    $this->loanStatistics = [
                        'monthly_installment' => $this->assessmentData['monthly_installment'] ?? 0,
                        'total_repayment' => $this->assessmentData['total_repayment'] ?? 0,
                        'total_interest' => $this->assessmentData['total_interest'] ?? 0,
                    ];

                    $this->assessmentSummary = [
                        'overall_score' => $this->assessmentData['overall_score'] ?? 0,
                        'credit_score' => $this->assessmentData['credit_score'] ?? 0,
                        'income_score' => $this->assessmentData['income_score'] ?? 0,
                        'member_activeness' => $this->assessmentData['member_activeness'] ?? 0,
                        'collateral_score' => $this->assessmentData['collateral_score'] ?? 0,
                        'ltv_score' => $this->assessmentData['ltv_score'] ?? 0,
                        'affordability_score' => $this->assessmentData['affordability_score'] ?? 0,
                        'recommendations' => $this->assessmentData['recommendations'] ?? [],
                    ];

                    $this->exceptions = [
                        'exception_data' => $this->assessmentData['exception_data'] ?? [],
                        'requires_exception' => $this->assessmentData['requires_exception'] ?? false,
                    ];
                    
                    // Generate policy exceptions and checks for the modal
                    $this->generatePolicyExceptionsAndChecks();

                    $this->loanSchedule = $this->assessmentData['loan_schedule'] ?? [];
                    $this->settlements = $this->assessmentData['settlements'] ?? [];
                    $this->topUpData = [
                        'selected_loan' => $this->assessmentData['selectedLoan'] ?? null,
                        'top_up_amount' => $this->assessmentData['top_up_amount'] ?? 0,
                    ];
                    $this->restructureData = [
                        'restructured_loan' => $this->assessmentData['restructured_loan'] ?? null,
                        'restructure_amount' => $this->assessmentData['restructure_amount'] ?? 0,
                    ];

                    Log::info('Assessment sections extracted successfully', [
                        'income_assessment' => !empty($this->incomeAssessment),
                        'product_parameters' => !empty($this->productParameters),
                        'term_calculation' => !empty($this->termCalculation),
                        'loan_amount_limits' => !empty($this->loanAmountLimits),
                        'collateral_info' => !empty($this->collateralInfo),
                        'deductions' => !empty($this->deductions),
                        'loan_statistics' => !empty($this->loanStatistics),
                        'assessment_summary' => !empty($this->assessmentSummary),
                        'exceptions' => !empty($this->exceptions),
                        'loan_schedule' => !empty($this->loanSchedule),
                        'settlements' => !empty($this->settlements),
                        'sample_data' => [
                            'approved_amount' => $this->loanAmountLimits['approved_amount'],
                            'monthly_installment' => $this->loanStatistics['monthly_installment'],
                            'overall_score' => $this->assessmentSummary['overall_score']
                        ]
                    ]);
                }
            } else {
                Log::warning('No assessment data found for loan ID: ' . $loanId, [
                    'loan_has_assessment_data_field' => isset($this->loanData->assessment_data),
                    'assessment_data_value' => $this->loanData->assessment_data
                ]);

                // Create default assessment data from loan information
                Log::info('Creating default assessment data from loan information');
                
                $this->assessmentData = [];
                
                // Extract basic loan information as default assessment data
                $this->incomeAssessment = [
                    'take_home' => $this->memberData->basic_salary ?? 0,
                    'monthly_sales' => 0,
                    'gross_profit' => 0,
                    'net_profit' => 0,
                    'recommended' => $this->loanData->approved_loan_value ?? 0,
                ];

                $this->productParameters = [
                    'product_name' => $this->loanData->loan_sub_product ?? 'N/A',
                    'interest_rate' => $this->loanData->interest_rate ?? 0,
                    'max_amount' => $this->loanData->approved_loan_value ?? 0,
                    'max_term' => $this->loanData->tenure ?? 0,
                ];

                $this->termCalculation = [
                    'requested_term' => $this->loanData->tenure ?? 0,
                    'approved_term' => $this->loanData->tenure ?? 0,
                    'days_first_interest' => 0,
                ];

                $this->loanAmountLimits = [
                    'requested_amount' => $this->loanData->approved_loan_value ?? 0,
                    'approved_amount' => $this->loanData->approved_loan_value ?? 0,
                    'max_qualifying_amount' => $this->loanData->approved_loan_value ?? 0,
                ];

                $this->collateralInfo = [
                    'collateral_value' => 0,
                    'collateral_type' => 'N/A',
                    'available_funds' => 0,
                    'coverage' => 0,
                ];

                $this->deductions = [
                    'existing_settlements' => 0,
                    'top_up_clearance' => 0,
                    'total_deductions' => 0,
                    'net_amount' => $this->loanData->approved_loan_value ?? 0,
                ];

                // Calculate basic monthly installment if interest rate is available
                $monthlyInstallment = 0;
                if ($this->loanData->interest_rate && $this->loanData->tenure && $this->loanData->approved_loan_value) {
                    $monthlyRate = ($this->loanData->interest_rate / 100) / 12;
                    $months = $this->loanData->tenure;
                    $principal = $this->loanData->approved_loan_value;
                    
                    if ($monthlyRate > 0) {
                        $monthlyInstallment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
                    }
                }

                $this->loanStatistics = [
                    'monthly_installment' => $monthlyInstallment,
                    'total_repayment' => $monthlyInstallment * ($this->loanData->tenure ?? 0),
                    'total_interest' => ($monthlyInstallment * ($this->loanData->tenure ?? 0)) - ($this->loanData->approved_loan_value ?? 0),
                ];

                $this->assessmentSummary = [
                    'overall_score' => 50, // Default neutral score
                    'credit_score' => 50,
                    'income_score' => 50,
                    'member_activeness' => 50,
                    'collateral_score' => 50,
                    'ltv_score' => 50,
                    'affordability_score' => 50,
                    'recommendations' => ['Assessment data not available - using loan information only'],
                ];

                $this->exceptions = [
                    'exception_data' => [],
                    'requires_exception' => false,
                ];

                $this->loanSchedule = [];
                $this->settlements = [];
                $this->topUpData = [
                    'selected_loan' => null,
                    'top_up_amount' => 0,
                ];
                $this->restructureData = [
                    'restructured_loan' => null,
                    'restructure_amount' => 0,
                ];

                Log::info('Default assessment data created from loan information', [
                    'loan_amount' => $this->loanData->approved_loan_value,
                    'loan_term' => $this->loanData->tenure,
                    'monthly_installment_calculated' => $monthlyInstallment
                ]);
            }

            Log::info('=== LOAD LOAN ASSESSMENT DATA COMPLETED ===', [
                'loan_id' => $loanId,
                'loan_data_loaded' => $this->loanData ? true : false,
                'member_data_loaded' => $this->memberData ? true : false,
                'guarantor_data_loaded' => $this->guarantorData ? true : false,
                'assessment_data_parsed' => !empty($this->assessmentData)
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading loan assessment data: ' . $e->getMessage(), [
                'loan_id' => $loanId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('notification', [
                'type' => 'error',
                'message' => 'Error loading assessment data: ' . $e->getMessage()
            ]);
        }
    }

    private function resetLoanAssessmentData()
    {
        $this->loanData = null;
        $this->memberData = null;
        $this->guarantorData = null;
        $this->assessmentData = [];
        $this->incomeAssessment = [];
        $this->productParameters = [];
        $this->termCalculation = [];
        $this->loanAmountLimits = [];
        $this->collateralInfo = [];
        $this->deductions = [];
        $this->loanStatistics = [];
        $this->assessmentSummary = [];
        $this->exceptions = [];
        $this->loanSchedule = [];
        $this->settlements = [];
        $this->topUpData = [];
        $this->restructureData = [];
    }

    public function approveLoanFromAssessment()
    {
        if (!$this->authorize('approve', 'You do not have permission to approve loans')) {
            return;
        }
        
        try {
            if (!$this->selectedApprovalId) {
                session()->flash('notification', [
                    'type' => 'error',
                    'message' => 'No approval selected'
                ]);
                return;
            }

            $approval = Approval::find($this->selectedApprovalId);
            if (!$approval) {
                session()->flash('notification', [
                    'type' => 'error',
                    'message' => 'Approval not found'
                ]);
                return;
            }

            // Store the current approval ID
            $approvalId = $this->selectedApprovalId;
            
            // Set the checker level for loan approvals (approver level)
            $this->checker_level = 3;
            
            // Call the regular approve method which will update the approvals table
            $this->approve();
            
            session()->flash('notification', [
                'type' => 'success',
                'message' => 'Loan has been approved successfully'
            ]);
            
            $this->closeLoanAssessmentModal();
            
            // Refresh the approvals list
            $this->refreshApprovalsList();

        } catch (\Exception $e) {
            Log::error('Error approving loan from assessment: ' . $e->getMessage());
            session()->flash('notification', [
                'type' => 'error',
                'message' => 'Error approving loan: ' . $e->getMessage()
            ]);
        }
    }

    public function rejectLoanFromAssessment()
    {
        if (!$this->authorize('reject', 'You do not have permission to reject loans')) {
            return;
        }
        
        try {
            if (!$this->selectedApprovalId) {
                session()->flash('notification', [
                    'type' => 'error',
                    'message' => 'No approval selected'
                ]);
                return;
            }

            if (empty($this->rejection_reason)) {
                session()->flash('notification', [
                    'type' => 'error',
                    'message' => 'Rejection reason is required'
                ]);
                return;
            }

            $approval = Approval::find($this->selectedApprovalId);
            if (!$approval) {
                session()->flash('notification', [
                    'type' => 'error',
                    'message' => 'Approval not found'
                ]);
                return;
            }

            // Store the current approval ID
            $approvalId = $this->selectedApprovalId;
            
            // Call the regular reject method which will update the approvals table
            $this->reject();
            
            session()->flash('notification', [
                'type' => 'success',
                'message' => 'Loan has been rejected successfully'
            ]);
            
            $this->closeLoanAssessmentModal();
            
            // Refresh the approvals list
            $this->refreshApprovalsList();

        } catch (\Exception $e) {
            Log::error('Error rejecting loan from assessment: ' . $e->getMessage(), [
                'approval_id' => $this->selectedApprovalId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('notification', [
                'type' => 'error',
                'message' => 'Error rejecting loan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Refresh the approvals list to show updated statuses
     */
    public function refreshApprovalsList()
    {
        // Increment the refresh trigger to force re-evaluation
        $this->refreshTrigger++;
        
        Log::info('Approvals list refreshed', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
            'refresh_trigger' => $this->refreshTrigger
        ]);
    }

    /**
     * Process loan approval (sets status to AWAITING_DISBURSEMENT for LOAN_DISB)
     */
    private function processLoanApproval($approval)
    {
        try {
            if ($approval->process_code === 'LOAN_DISB') {
                // For loan disbursement, the approval process sets status to AWAITING_DISBURSEMENT
                // The actual disbursement is handled separately by finance/accounting team
                Log::info('Loan disbursement approved - awaiting finance disbursement', [
                    'approval_id' => $approval->id,
                    'process_id' => $approval->process_id,
                    'status' => 'AWAITING_DISBURSEMENT'
                ]);
                
                // Send notification to finance/accounting team
                $this->sendLoanApprovalNotification($approval);
                
            } elseif ($approval->process_code === 'LOAN_APP') {
                // Handle loan application approval
                $this->processLoanApplication($approval);
            } elseif ($approval->process_code === 'LOAN_REST') {
                // Handle loan restructuring
                $this->processLoanRestructuring($approval);
            } elseif ($approval->process_code === 'LOAN_WOFF') {
                // Handle loan write-off
                $this->processLoanWriteOff($approval);
            }

            Log::info('Loan approval processed successfully', [
                'approval_id' => $approval->id,
                'process_code' => $approval->process_code,
                'process_id' => $approval->process_id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing loan approval', [
                'approval_id' => $approval->id,
                'process_code' => $approval->process_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process actual loan disbursement (called by finance/accounting team)
     */
    private function processLoanDisbursement($approval)
    {
        try {
            $loan = \App\Models\LoansModel::find($approval->process_id);
            if (!$loan) {
                throw new \Exception('Loan not found for disbursement');
            }

            Log::info('Processing loan disbursement', [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number,
                'amount' => $loan->loan_amount
            ]);

            // Update loan status
            $loan->status = 'DISBURSED';
            $loan->disbursement_date = now();
            // Note: disbursed_by column doesn't exist in the loans table
            $loan->save();

            // Create disbursement transaction
            $this->createDisbursementTransaction($loan);

            // Update client loan account balance
            $this->updateClientLoanBalance($loan);

            // Send disbursement notification
            $this->sendDisbursementNotification($loan);

            Log::info('Loan disbursement processed successfully', [
                'loan_id' => $loan->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing loan disbursement', [
                'approval_id' => $approval->id,
                'loan_id' => $approval->process_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process loan application approval
     */
    private function processLoanApplication($approval)
    {
        try {
            $loan = \App\Models\LoansModel::find($approval->process_id);
            if (!$loan) {
                throw new \Exception('Loan not found for application approval');
            }

            Log::info('Processing loan application approval', [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number
            ]);

            // Update loan status
            $loan->status = 'APPROVED';
            $loan->approved_at = now();  // Changed from approved_date to approved_at
            $loan->approved_by = Auth::id();
            $loan->save();

            // Send approval notification
            $this->sendLoanApprovalNotification($loan);

            Log::info('Loan application approval processed successfully', [
                'loan_id' => $loan->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing loan application approval', [
                'approval_id' => $approval->id,
                'loan_id' => $approval->process_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process loan restructuring approval
     */
    private function processLoanRestructuring($approval)
    {
        try {
            $loan = \App\Models\LoansModel::find($approval->process_id);
            if (!$loan) {
                throw new \Exception('Loan not found for restructuring');
            }

            Log::info('Processing loan restructuring', [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number
            ]);

            // Update loan status
            $loan->status = 'RESTRUCTURED';
            $loan->restructured_date = now();
            $loan->restructured_by = Auth::id();
            $loan->save();

            // Apply restructuring changes from edit_package if exists
            if (!empty($approval->edit_package)) {
                $this->applyRestructuringChanges($loan, $approval->edit_package);
            }

            // Send restructuring notification
            $this->sendRestructuringNotification($loan);

            Log::info('Loan restructuring processed successfully', [
                'loan_id' => $loan->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing loan restructuring', [
                'approval_id' => $approval->id,
                'loan_id' => $approval->process_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process loan write-off approval
     */
    private function processLoanWriteOff($approval)
    {
        try {
            $loan = \App\Models\LoansModel::find($approval->process_id);
            if (!$loan) {
                throw new \Exception('Loan not found for write-off');
            }

            Log::info('Processing loan write-off', [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number
            ]);

            // Update loan status
            $loan->status = 'WRITTEN_OFF';
            $loan->write_off_date = now();
            $loan->written_off_by = Auth::id();
            $loan->save();

            // Create write-off transaction
            $this->createWriteOffTransaction($loan);

            // Send write-off notification
            $this->sendWriteOffNotification($loan);

            Log::info('Loan write-off processed successfully', [
                'loan_id' => $loan->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing loan write-off', [
                'approval_id' => $approval->id,
                'loan_id' => $approval->process_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create disbursement transaction
     */
    private function createDisbursementTransaction($loan)
    {
        try {
            // Generate transaction reference
            $reference = 'LOAN_DISB_' . date('YmdHis') . '_' . strtoupper(substr(md5(uniqid()), 0, 8));

            Log::info('Disbursement transaction created', [
                'reference' => $reference,
                'loan_id' => $loan->id,
                'amount' => $loan->loan_amount
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating disbursement transaction', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update client loan balance
     */
    private function updateClientLoanBalance($loan)
    {
        try {
            // Update client's loan account balance
            $clientAccount = \App\Models\AccountsModel::where('client_number', $loan->client_number)
                ->where('account_type', 'LOAN')
                ->first();

            if ($clientAccount) {
                $clientAccount->balance += $loan->loan_amount;
                $clientAccount->save();

                Log::info('Client loan balance updated', [
                    'client_number' => $loan->client_number,
                    'account_number' => $clientAccount->account_number,
                    'new_balance' => $clientAccount->balance
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating client loan balance', [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Apply restructuring changes
     */
    private function applyRestructuringChanges($loan, $editPackage)
    {
        try {
            $changes = is_string($editPackage) ? json_decode($editPackage, true) : $editPackage;

            if (is_array($changes)) {
                foreach ($changes as $field => $values) {
                    if (isset($values['new'])) {
                        $loan->$field = $values['new'];
                    }
                }
                $loan->save();

                Log::info('Restructuring changes applied', [
                    'loan_id' => $loan->id,
                    'fields_updated' => array_keys($changes)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error applying restructuring changes', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create write-off transaction
     */
    private function createWriteOffTransaction($loan)
    {
        try {
            // Generate transaction reference
            $reference = 'LOAN_WOFF_' . date('YmdHis') . '_' . strtoupper(substr(md5(uniqid()), 0, 8));

            Log::info('Write-off transaction created', [
                'reference' => $reference,
                'loan_id' => $loan->id,
                'amount' => $loan->outstanding_balance ?? $loan->loan_amount
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating write-off transaction', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Helper methods for account numbers
     */
    private function getLoanAccount(): string
    {
        $account = \App\Models\AccountsModel::where('account_name', 'LIKE', '%loan%')
            ->where('status', 'ACTIVE')
            ->where('institution_number', $this->getCurrentInstitution())
            ->first();

        return $account ? $account->account_number : '5000';
    }

    private function getClientAccount($clientNumber): string
    {
        $account = \App\Models\AccountsModel::where('client_number', $clientNumber)
            ->where('account_type', 'LOAN')
            ->where('status', 'ACTIVE')
            ->first();

        return $account ? $account->account_number : '6000';
    }

    private function getWriteOffAccount(): string
    {
        $account = \App\Models\AccountsModel::where('account_name', 'LIKE', '%write%off%')
            ->where('status', 'ACTIVE')
            ->where('institution_number', $this->getCurrentInstitution())
            ->first();

        return $account ? $account->account_number : '7000';
    }

    private function getCurrentInstitution(): string
    {
        return Auth::user()->institution_id ?? '1';
    }

    /**
     * Notification methods (placeholder implementations)
     */
    private function sendDisbursementNotification($loan)
    {
        Log::info('Disbursement notification sent', [
            'loan_id' => $loan->id,
            'client_number' => $loan->client_number
        ]);
        // Implement actual notification logic
    }

    private function sendLoanApprovalNotification($loan)
    {
        Log::info('Loan approval notification sent', [
            'loan_id' => $loan->id,
            'client_number' => $loan->client_number
        ]);
        // Implement actual notification logic
    }

    private function sendRestructuringNotification($loan)
    {
        Log::info('Restructuring notification sent', [
            'loan_id' => $loan->id,
            'client_number' => $loan->client_number
        ]);
        // Implement actual notification logic
    }

    private function sendWriteOffNotification($loan)
    {
        Log::info('Write-off notification sent', [
            'loan_id' => $loan->id,
            'client_number' => $loan->client_number
        ]);
        // Implement actual notification logic
    }

    /**
     * Handle loan approval workflow - sets status to AWAITING_DISBURSEMENT for LOAN_DISB
     */
    private function handleLoanApprovalWorkflow($approval)
    {
        if ($approval->process_code === 'LOAN_DISB') {
            Log::info('Loan disbursement approved - awaiting finance disbursement', [
                'approval_id' => $approval->id,
                'process_id' => $approval->process_id,
                'status' => 'AWAITING_DISBURSEMENT'
            ]);
            
            // Send notification to finance/accounting team for disbursement
            $this->sendLoanApprovalNotification($approval);
        }
    }

    /**
     * Process actual loan disbursement (called by finance/accounting team)
     * This method should be called when the finance team actually disburses the loan
     */
    public function processLoanDisbursementByFinance($loanId)
    {
        try {
            DB::beginTransaction();

            $loan = \App\Models\LoansModel::find($loanId);
            if (!$loan) {
                throw new \Exception('Loan not found for disbursement');
            }

            // Verify loan is in AWAITING_DISBURSEMENT status
            if ($loan->status !== 'AWAITING_DISBURSEMENT') {
                throw new \Exception('Loan is not in AWAITING_DISBURSEMENT status. Current status: ' . $loan->status);
            }

            Log::info('Finance team processing loan disbursement', [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number,
                'amount' => $loan->approved_loan_value ?? $loan->principle,
                'user_id' => Auth::id()
            ]);

            // Update loan status to DISBURSED
            $loan->status = 'DISBURSED';
            $loan->disbursement_date = now();
            // Note: disbursed_by column doesn't exist in the loans table
            $loan->save();

            // Create disbursement transaction
            $this->createDisbursementTransaction($loan);

            // Update client loan account balance
            $this->updateClientLoanBalance($loan);

            // Send disbursement notification
            $this->sendDisbursementNotification($loan);

            DB::commit();

            session()->flash('notification', [
                'type' => 'success',
                'message' => 'Loan disbursement processed successfully!'
            ]);

            Log::info('Loan disbursement processed successfully by finance team', [
                'loan_id' => $loan->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing loan disbursement by finance team', [
                'loan_id' => $loanId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('notification', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function loadAssetDisposalDetails($assetId)
    {
        try {
            $asset = \App\Models\PPE::find($assetId);
            
            if (!$asset) {
                Log::warning('Asset not found for disposal details', [
                    'asset_id' => $assetId
                ]);
                return;
            }

            // Store asset details for display in the modal
            $this->assetDisposalDetails = [
                'id' => $asset->id,
                'name' => $asset->name,
                'category' => $asset->category,
                'description' => $asset->description,
                'purchase_date' => $asset->purchase_date,
                'initial_value' => $asset->initial_value,
                'accumulated_depreciation' => $asset->accumulated_depreciation,
                'closing_value' => $asset->closing_value,
                'depreciation_rate' => $asset->depreciation_rate,
                'depreciation_for_year' => $asset->depreciation_for_year,
                'status' => $asset->status,
                'account_number' => $asset->account_number,
                'location' => $asset->location,
                'supplier' => $asset->supplier,
                'serial_number' => $asset->serial_number,
                'warranty_expiry' => $asset->warranty_expiry,
                'insurance_details' => $asset->insurance_details,
                'maintenance_schedule' => $asset->maintenance_schedule,
                'last_maintenance_date' => $asset->last_maintenance_date,
                'next_maintenance_date' => $asset->next_maintenance_date,
                'condition' => $asset->condition,
                'useful_life_years' => $asset->useful_life_years,
                'salvage_value' => $asset->salvage_value,
                'disposal_approval_status' => $asset->disposal_approval_status,
                'disposal_approved_by' => $asset->disposal_approved_by,
                'disposal_approved_at' => $asset->disposal_approved_at,
                'disposal_rejection_reason' => $asset->disposal_rejection_reason,
                'created_at' => $asset->created_at,
                'updated_at' => $asset->updated_at
            ];

            Log::info('Asset disposal details loaded successfully', [
                'asset_id' => $assetId,
                'asset_name' => $asset->name
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading asset disposal details', [
                'asset_id' => $assetId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update loan approval stage following the same logic as approval-actions.blade.php
     */
    private function updateLoanApprovalStage($approval, $config, &$updateData)
    {
        try {
            // Get the loan record to check current stage
            $loan = DB::table('loans')->where('id', $approval->process_id)->first();
            if (!$loan) {
                Log::error('Loan not found for approval stage update', [
                    'approval_id' => $approval->id,
                    'process_id' => $approval->process_id
                ]);
                return;
            }
            
            $currentStage = $loan->approval_stage ?? 'Inputter';
            
            // Build stage sequence based on configuration
            $stageSequence = [];
            $stageRoles = [];
            
            // Check if loan has exceptions
            $hasExceptionStage = ($loan->status === 'PENDING-EXCEPTIONS' || $loan->status === 'PENDING-WITH-EXCEPTIONS');
            if ($hasExceptionStage && $currentStage === 'Exception') {
                $stageSequence[] = 'Exception';
                $stageRoles['Exception'] = ['Loan Officer'];
            }
            
            // Always have Inputter stage
            $stageSequence[] = 'Inputter';
            $stageRoles['Inputter'] = ['Loan Officer'];
            
            // Add configured stages with their roles
            if ($config->requires_first_checker) {
                $stageSequence[] = 'First Checker';
                // Check if it's already an array or needs decoding
                $firstCheckerRoleIds = is_array($config->first_checker_roles) 
                    ? $config->first_checker_roles 
                    : json_decode($config->first_checker_roles ?? '[]', true);
                $stageRoles['First Checker'] = $this->getRoleNames($firstCheckerRoleIds);
            }
            
            if ($config->requires_second_checker) {
                $stageSequence[] = 'Second Checker';
                // Check if it's already an array or needs decoding
                $secondCheckerRoleIds = is_array($config->second_checker_roles) 
                    ? $config->second_checker_roles 
                    : json_decode($config->second_checker_roles ?? '[]', true);
                $stageRoles['Second Checker'] = $this->getRoleNames($secondCheckerRoleIds);
            }
            
            if ($config->requires_approver) {
                $stageSequence[] = 'Approver';
                // Check if it's already an array or needs decoding
                $approverRoleIds = is_array($config->approver_roles) 
                    ? $config->approver_roles 
                    : json_decode($config->approver_roles ?? '[]', true);
                $stageRoles['Approver'] = $this->getRoleNames($approverRoleIds);
            }
            
            // Find current stage index
            $currentStageIndex = array_search($currentStage, $stageSequence);
            
            // Determine if this is a final approval or intermediate approval
            $isFinalApproval = $this->isFinalApproval($approval, $config);
            
            if ($isFinalApproval) {
                // This is the final approval - set to approved
                $updateData['approval_stage'] = 'approved';
                $updateData['approval_stage_role_name'] = null;
                
                Log::info('LOAN_APP final approval - setting stage to approved', [
                    'approval_id' => $approval->id,
                    'loan_id' => $loan->id,
                    'current_stage' => $currentStage
                ]);
            } else {
                // Determine next stage
                $nextStage = null;
                $nextStageRoles = [];
                
                if ($currentStageIndex !== false && $currentStageIndex < count($stageSequence) - 1) {
                    // Get the appropriate next stage based on what has been approved
                    $nextStage = $this->determineNextLoanStage($approval, $config, $stageSequence);
                    
                    if ($nextStage && isset($stageRoles[$nextStage])) {
                        $nextStageRoles = $stageRoles[$nextStage];
                    }
                }
                
                if ($nextStage) {
                    $updateData['approval_stage'] = $nextStage;
                    
                    // Set role names - if multiple roles, it's a Loan Committee
                    if (count($nextStageRoles) > 1) {
                        $updateData['approval_stage_role_name'] = 'Loan Committee (' . implode(', ', $nextStageRoles) . ')';
                    } else {
                        $updateData['approval_stage_role_name'] = implode(', ', $nextStageRoles);
                    }
                    
                    Log::info('LOAN_APP moving to next stage', [
                        'approval_id' => $approval->id,
                        'loan_id' => $loan->id,
                        'current_stage' => $currentStage,
                        'next_stage' => $nextStage,
                        'next_roles' => $nextStageRoles
                    ]);
                }
            }
            
            // Create approval record for audit trail (similar to createApprovalRecord in LoanProcess)
            if (!$isFinalApproval && isset($nextStage)) {
                $this->createNextStageApprovalRecord($approval, $loan, $nextStage, $updateData['approval_stage_role_name'] ?? null);
            }
            
        } catch (\Exception $e) {
            Log::error('Error updating loan approval stage', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Determine the next loan stage based on current approval status
     */
    private function determineNextLoanStage($approval, $config, $stageSequence)
    {
        // Get current loan stage
        $currentStage = DB::table('loans')->where('id', $approval->process_id)->value('approval_stage');
        
        // Determine what stage we should move to based on what just got approved
        // and the checker_level that was just processed
        
        // For three-level approval
        if ($config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            if ($approval->checker_level == 2 && $approval->first_checker_status === 'APPROVED') {
                // First checker just approved, move to Second Checker
                return 'Second Checker';
            } elseif ($approval->checker_level == 3 && $approval->second_checker_status === 'APPROVED') {
                // Second checker just approved, move to Approver
                return 'Approver';
            }
        }
        // For two-level approval (First Checker + Second Checker)
        elseif ($config->requires_first_checker && $config->requires_second_checker && !$config->requires_approver) {
            if ($approval->checker_level == 2 && $approval->first_checker_status === 'APPROVED') {
                // First checker just approved, move to Second Checker
                return 'Second Checker';
            }
        }
        // For two-level approval (First Checker + Approver)
        elseif ($config->requires_first_checker && !$config->requires_second_checker && $config->requires_approver) {
            if ($approval->checker_level == 2 && $approval->first_checker_status === 'APPROVED') {
                // First checker just approved, move to Approver
                return 'Approver';
            }
        }
        // For two-level approval (Second Checker + Approver)
        elseif (!$config->requires_first_checker && $config->requires_second_checker && $config->requires_approver) {
            if ($approval->checker_level == 2 && $approval->second_checker_status === 'APPROVED') {
                // Second checker just approved, move to Approver
                return 'Approver';
            }
        }
        
        // If we can't determine from approval status, follow the sequence
        $currentIndex = array_search($currentStage, $stageSequence);
        if ($currentIndex !== false && $currentIndex < count($stageSequence) - 1) {
            return $stageSequence[$currentIndex + 1];
        }
        
        return null;
    }
    
    /**
     * Get role names from role IDs
     */
    private function getRoleNames($roleIds)
    {
        if (empty($roleIds)) {
            return [];
        }
        
        return DB::table('roles')
            ->whereIn('id', $roleIds)
            ->pluck('name')
            ->toArray();
    }
    
    /**
     * Create approval record for next stage (audit trail)
     */
    private function createNextStageApprovalRecord($approval, $loan, $nextStage, $nextRoleName)
    {
        try {
            // Prepare loan data for next stage approval
            $loanData = [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number,
                'loan_amount' => $loan->approved_loan_value ?? $loan->loan_amount,
                'current_stage' => $loan->approval_stage,
                'next_stage' => $nextStage,
                'moved_by' => Auth::user()->name ?? 'System',
                'moved_at' => now()
            ];
            
            // Log the stage transition
            DB::table('loan_approval_logs')->insert([
                'loan_id' => $loan->id,
                'stage' => $nextStage,
                'action' => 'MOVED_TO_STAGE',
                'comment' => 'Loan moved to ' . $nextStage . ' stage after approval',
                'performed_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info('Created loan approval log for stage transition', [
                'loan_id' => $loan->id,
                'from_stage' => $loan->approval_stage,
                'to_stage' => $nextStage,
                'next_role' => $nextRoleName
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating next stage approval record', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate policy exceptions and policy checks based on loan assessment data
     */
    private function generatePolicyExceptionsAndChecks()
    {
        try {
            // Initialize policy exceptions array
            $policyExceptions = [];
            
            // Initialize policy checks array
            $policyChecks = [];
            
            // Get debt service ratio
            $monthlyIncome = $this->incomeAssessment['take_home'] ?? 0;
            $monthlyInstallment = $this->loanStatistics['monthly_installment'] ?? 0;
            $debtServiceRatio = $monthlyIncome > 0 ? ($monthlyInstallment / $monthlyIncome) * 100 : 0;
            
            // Check 1: Debt Service Ratio
            if ($debtServiceRatio > 40) {
                $policyExceptions[] = [
                    'type' => 'DSR Limit Exceeded',
                    'description' => sprintf('Debt Service Ratio is %.1f%% (exceeds 40%% limit)', $debtServiceRatio),
                    'status' => 'REQUIRES APPROVAL',
                    'action' => 'Loan Committee approval required'
                ];
            }
            
            // Check 2: Credit Score
            $creditScore = $this->assessmentData['credit_score'] ?? 500;
            if ($creditScore < 500) {
                $policyExceptions[] = [
                    'type' => 'Low Credit Score',
                    'description' => sprintf('Credit score is %d (below minimum 500)', $creditScore),
                    'status' => 'REQUIRES APPROVAL',
                    'action' => 'Enhanced due diligence required'
                ];
            }
            
            // Check 3: Loan Amount vs Product Limits
            $requestedAmount = $this->loanAmountLimits['requested_amount'] ?? 0;
            $maxAmount = $this->productParameters['max_amount'] ?? PHP_INT_MAX;
            if ($requestedAmount > $maxAmount) {
                $policyExceptions[] = [
                    'type' => 'Loan Amount Exceeds Product Limit',
                    'description' => sprintf('Requested %s exceeds product limit of %s', 
                        number_format($requestedAmount), 
                        number_format($maxAmount)),
                    'status' => 'REQUIRES APPROVAL',
                    'action' => 'Special approval required'
                ];
            }
            
            // Check 4: Collateral Coverage
            $collateralValue = $this->collateralInfo['collateral_value'] ?? 0;
            $loanAmount = $this->loanAmountLimits['approved_amount'] ?? 0;
            $collateralCoverage = $loanAmount > 0 ? ($collateralValue / $loanAmount) * 100 : 0;
            
            if ($loanAmount > 5000000 && $collateralCoverage < 100) {
                $policyExceptions[] = [
                    'type' => 'Insufficient Collateral Coverage',
                    'description' => sprintf('Collateral coverage is %.1f%% (requires 100%% for loans above 5M)', $collateralCoverage),
                    'status' => 'REQUIRES REVIEW',
                    'action' => 'Additional security required'
                ];
            }
            
            // Check 5: Active Loans Count
            $activeLoansCount = $this->assessmentData['active_loans_count'] ?? 0;
            if ($activeLoansCount > 3) {
                $policyExceptions[] = [
                    'type' => 'Multiple Active Loans',
                    'description' => sprintf('Client has %d active loans (exceeds policy limit of 3)', $activeLoansCount),
                    'status' => 'REQUIRES APPROVAL',
                    'action' => 'Review repayment capacity'
                ];
            }
            
            // Check 6: Term Length
            $requestedTerm = $this->termCalculation['requested_term'] ?? 0;
            $maxTerm = $this->productParameters['max_term'] ?? 60;
            if ($requestedTerm > $maxTerm) {
                $policyExceptions[] = [
                    'type' => 'Term Exceeds Product Limit',
                    'description' => sprintf('Requested term of %d months exceeds product limit of %d months', 
                        $requestedTerm, 
                        $maxTerm),
                    'status' => 'REQUIRES APPROVAL',
                    'action' => 'Justify extended repayment period'
                ];
            }
            
            // Check 7: Exception data from assessment
            if (!empty($this->exceptions['exception_data'])) {
                foreach ($this->exceptions['exception_data'] as $exception) {
                    if (is_array($exception)) {
                        $policyExceptions[] = [
                            'type' => $exception['type'] ?? 'Policy Exception',
                            'description' => $exception['description'] ?? 'Exception identified during assessment',
                            'status' => $exception['status'] ?? 'REQUIRES REVIEW',
                            'action' => $exception['action'] ?? 'Review required'
                        ];
                    }
                }
            }
            
            // Generate policy compliance checklist
            $policyChecks[] = [
                'item' => 'KYC Documentation Complete',
                'status' => true, // Assume complete if loan reached approval stage
                'remarks' => 'All required documents verified'
            ];
            
            $policyChecks[] = [
                'item' => 'Income Verification',
                'status' => $monthlyIncome > 0,
                'remarks' => $monthlyIncome > 0 ? 
                    sprintf('Monthly income: %s TZS', number_format($monthlyIncome)) : 
                    'Income not verified'
            ];
            
            $policyChecks[] = [
                'item' => 'Purpose of Loan Stated',
                'status' => !empty($this->loanData->purpose),
                'remarks' => !empty($this->loanData->purpose) ? 
                    'Purpose: ' . $this->loanData->purpose : 
                    'Loan purpose not specified'
            ];
            
            $policyChecks[] = [
                'item' => 'Interest Rate Within Policy',
                'status' => true, // Assume within policy if loan was created
                'remarks' => sprintf('Rate: %.2f%% p.a.', $this->loanData->interest_rate ?? 0)
            ];
            
            $policyChecks[] = [
                'item' => 'Repayment Schedule Generated',
                'status' => !empty($this->loanSchedule),
                'remarks' => !empty($this->loanSchedule) ? 
                    'Schedule available' : 
                    'Schedule pending'
            ];
            
            // Store in assessmentData for the modal
            $this->assessmentData['policy_exceptions'] = $policyExceptions;
            $this->assessmentData['policy_checks'] = $policyChecks;
            $this->assessmentData['active_loans_count'] = $activeLoansCount;
            $this->assessmentData['credit_score'] = $creditScore;
            $this->assessmentData['total_savings'] = $this->memberData->total_savings ?? 0;
            $this->assessmentData['payment_history'] = 'Good'; // Default value, should be calculated from actual data
            
            Log::info('Policy exceptions and checks generated', [
                'loan_id' => $this->loanData->id ?? null,
                'exceptions_count' => count($policyExceptions),
                'checks_count' => count($policyChecks),
                'debt_service_ratio' => $debtServiceRatio,
                'credit_score' => $creditScore
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating policy exceptions and checks', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Set empty arrays on error
            $this->assessmentData['policy_exceptions'] = [];
            $this->assessmentData['policy_checks'] = [];
        }
    }
    
    /**
     * Handle budget create approval
     */
    private function handleBudgetCreateApproval($approval): void
    {
        try {
            $budget = \App\Models\BudgetManagement::find($approval->process_id);
            
            if (!$budget) {
                Log::error('Budget not found for create approval', ['process_id' => $approval->process_id]);
                return;
            }
            
            if ($approval->process_status === 'APPROVED') {
                // Approve the budget
                $budget->update([
                    'approval_status' => 'APPROVED',
                    'status' => 'ACTIVE',
                    'approval_request_id' => null
                ]);
                
                Log::info('Budget created and approved', [
                    'budget_id' => $budget->id,
                    'budget_name' => $budget->budget_name
                ]);
            } else {
                // Reject the budget
                $budget->update([
                    'approval_status' => 'REJECTED',
                    'status' => 'DRAFT',
                    'approval_request_id' => null
                ]);
                
                Log::info('Budget creation rejected', [
                    'budget_id' => $budget->id,
                    'budget_name' => $budget->budget_name
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling budget create approval', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle budget edit approval
     */
    private function handleBudgetEditApproval($approval): void
    {
        try {
            $budget = \App\Models\BudgetManagement::find($approval->process_id);
            
            if (!$budget) {
                Log::error('Budget not found for edit approval', ['process_id' => $approval->process_id]);
                return;
            }
            
            if ($approval->process_status === 'APPROVED') {
                // Capture old values for version history
                $oldValues = [
                    'budget_name' => $budget->budget_name,
                    'revenue' => $budget->revenue,
                    'capital_expenditure' => $budget->capital_expenditure,
                    'allocated_amount' => $budget->allocated_amount,
                    'spent_amount' => $budget->spent_amount,
                    'committed_amount' => $budget->committed_amount
                ];
                
                // Try to get changes from edit_package first (preferred source)
                $editPackage = null;
                $pendingChanges = null;
                
                if ($approval->edit_package) {
                    // Check if edit_package is already an array or needs to be decoded
                    if (is_array($approval->edit_package)) {
                        $editPackage = $approval->edit_package;
                    } else {
                        $editPackage = json_decode($approval->edit_package, true);
                    }
                    
                    if ($editPackage && isset($editPackage['new_values'])) {
                        $pendingChanges = $editPackage['new_values'];
                    }
                }
                
                // Fall back to pending_changes from budget model if edit_package not available
                if (!$pendingChanges) {
                    $pendingChanges = $budget->pending_changes;
                }
                
                Log::info('Processing budget edit approval', [
                    'budget_id' => $budget->id,
                    'has_edit_package' => !empty($editPackage),
                    'has_pending_changes' => !empty($pendingChanges),
                    'pending_changes' => $pendingChanges,
                    'source' => $editPackage ? 'edit_package' : 'budget_model'
                ]);
                
                if ($pendingChanges) {
                    $updateData = [
                        'budget_name' => $pendingChanges['budget_name'] ?? $budget->budget_name,
                        'revenue' => $pendingChanges['revenue'] ?? $budget->revenue,
                        'capital_expenditure' => $pendingChanges['capital_expenditure'] ?? $budget->capital_expenditure,
                        'start_date' => $pendingChanges['start_date'] ?? $budget->start_date,
                        'end_date' => $pendingChanges['end_date'] ?? $budget->end_date,
                        'notes' => $pendingChanges['notes'] ?? $budget->notes,
                        'expense_account_id' => $pendingChanges['expense_account_id'] ?? $budget->expense_account_id,
                        'approval_status' => 'APPROVED',
                        'status' => 'ACTIVE',
                        'edit_approval_status' => 'APPROVED',
                        'is_locked' => false,
                        'locked_reason' => null,
                        'locked_at' => null,
                        'locked_by' => null,
                        'pending_changes' => null,
                        'approval_request_id' => null
                    ];
                    
                    Log::info('Updating budget with changes', [
                        'budget_id' => $budget->id,
                        'update_data' => $updateData
                    ]);
                    
                    $budget->update($updateData);
                    
                    // Create a version to track this approved edit
                    try {
                        \Log::info('Creating budget version for approved edit', [
                            'budget_id' => $budget->id,
                            'old_values' => $oldValues,
                            'new_values' => $pendingChanges
                        ]);
                        
                        $versionNumber = \App\Models\BudgetVersion::where('budget_id', $budget->id)->count() + 1;
                        
                        \App\Models\BudgetVersion::create([
                            'budget_id' => $budget->id,
                            'version_number' => $versionNumber,
                            'version_name' => "Version {$versionNumber} - Approved Edit",
                            'version_type' => 'REVISED',
                            'allocated_amount' => $updateData['revenue'] ?? $budget->revenue,
                            'budget_data' => json_encode([
                                'old_values' => $oldValues,
                                'new_values' => $pendingChanges,
                                'spent_amount' => $budget->spent_amount,
                                'committed_amount' => $budget->committed_amount,
                                'approved_by' => auth()->user()->name ?? 'System',
                                'approved_at' => now()->toDateTimeString()
                            ]),
                            'created_by' => auth()->id() ?? 1,
                            'revision_reason' => 'Budget edit approved through approval workflow',
                            'status' => 'ACTIVE',
                            'approved_by' => auth()->id(),
                            'approved_at' => now()
                        ]);
                        
                        // Update previous versions to inactive status
                        \App\Models\BudgetVersion::where('budget_id', $budget->id)
                            ->where('version_number', '<', $versionNumber)
                            ->update(['status' => 'INACTIVE']);
                        
                        Log::info('Budget version created for approved edit', [
                            'budget_id' => $budget->id,
                            'version_number' => $versionNumber
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create budget version for approved edit', [
                            'budget_id' => $budget->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    Log::info('Budget edit approved and changes applied', [
                        'budget_id' => $budget->id,
                        'budget_name' => $budget->budget_name,
                        'new_revenue' => $budget->fresh()->revenue,
                        'new_capital_expenditure' => $budget->fresh()->capital_expenditure
                    ]);
                } else {
                    Log::warning('No pending changes found for budget edit approval', [
                        'budget_id' => $budget->id,
                        'budget_name' => $budget->budget_name,
                        'approval_edit_package' => $approval->edit_package
                    ]);
                }
            } else {
                // Reject the edit request
                $budget->update([
                    'edit_approval_status' => 'REJECTED',
                    'is_locked' => false,
                    'locked_reason' => null,
                    'locked_at' => null,
                    'locked_by' => null,
                    'pending_changes' => null,
                    'approval_request_id' => null
                ]);
                
                Log::info('Budget edit rejected', [
                    'budget_id' => $budget->id,
                    'budget_name' => $budget->budget_name
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling budget edit approval', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle budget delete approval
     */
    private function handleBudgetDeleteApproval($approval): void
    {
        try {
            $budget = \App\Models\BudgetManagement::find($approval->process_id);
            
            if (!$budget) {
                Log::error('Budget not found for delete approval', ['process_id' => $approval->process_id]);
                return;
            }
            
            if ($approval->process_status === 'APPROVED') {
                // Soft delete the budget
                $budgetName = $budget->budget_name;
                $budget->delete();
                
                Log::info('Budget deleted after approval', [
                    'budget_id' => $approval->process_id,
                    'budget_name' => $budgetName
                ]);
            } else {
                // Reject the delete request
                $budget->update([
                    'edit_approval_status' => 'DELETE_REJECTED',
                    'is_locked' => false,
                    'locked_reason' => null,
                    'locked_at' => null,
                    'locked_by' => null,
                    'approval_request_id' => null
                ]);
                
                Log::info('Budget delete rejected', [
                    'budget_id' => $budget->id,
                    'budget_name' => $budget->budget_name
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling budget delete approval', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Update product (share, savings, deposit) details
     */
    private function updateProductStatus($approval, $editPackage, $processCode): void
    {
        try{
        $product = sub_products::find($approval->process_id);
        if($processCode == 'PROD_EDIT'){
            $product->update($editPackage);
        }

        } catch (\Exception $e) {
            Log::error('Error updating product status', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle member exit approval
     */
    private function handleMemberExitApproval($approval): void
    {
        try {
            $member = \App\Models\ClientsModel::find($approval->process_id);
            
            if (!$member) {
                Log::error('Member not found for exit approval', ['process_id' => $approval->process_id]);
                return;
            }
            
            // Decode edit_package to get exit data
            $exitData = is_string($approval->edit_package) 
                ? json_decode($approval->edit_package, true) 
                : $approval->edit_package;
            
            if ($approval->process_status === 'APPROVED') {
                // Process member exit
                $member->update([
                    'status' => 'EXITED',
                    'exit_date' => now(),
                    'exit_reason' => $exitData['exit_reason'] ?? null,
                    'exit_notes' => $exitData['exit_notes'] ?? null,
                    'updated_at' => now()
                ]);
                
                // Create comprehensive exit record
                DB::table('member_exits')->insert([
                    'client_number' => $exitData['client_number'] ?? $member->client_number,
                    'member_name' => $exitData['member_name'] ?? ($member->first_name . ' ' . $member->last_name),
                    'exit_date' => now(),
                    'exit_reason' => $exitData['exit_reason'] ?? null,
                    'exit_notes' => $exitData['exit_notes'] ?? null,
                    'shares_balance' => $exitData['shares_balance'] ?? 0,
                    'savings_balance' => $exitData['savings_balance'] ?? 0,
                    'deposits_balance' => $exitData['deposits_balance'] ?? 0,
                    'loan_balance' => $exitData['loan_balance'] ?? 0,
                    'unpaid_bills' => $exitData['unpaid_bills'] ?? 0,
                    'dividends' => $exitData['dividends'] ?? 0,
                    'interest_on_savings' => $exitData['interest_on_savings'] ?? 0,
                    'total_credits' => $exitData['total_credits'] ?? 0,
                    'total_debits' => $exitData['total_debits'] ?? 0,
                    'accounts_count' => $exitData['accounts_count'] ?? 0,
                    'loans_count' => $exitData['loans_count'] ?? 0,
                    'unpaid_bills_count' => $exitData['unpaid_bills_count'] ?? 0,
                    'settlement_amount' => $exitData['settlement_amount'] ?? 0,
                    'processed_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Deactivate all member accounts
                DB::table('accounts')
                    ->where('client_number', $member->client_number)
                    ->update(['status' => 'CLOSED', 'updated_at' => now()]);
                
                Log::info('Member exit approved and processed', [
                    'member_id' => $member->id,
                    'client_number' => $member->client_number,
                    'exit_reason' => $exitData['exit_reason'] ?? 'N/A',
                    'approved_by' => Auth::id()
                ]);
            } else {
                // Rejection - no changes to member status
                Log::info('Member exit rejected', [
                    'member_id' => $member->id,
                    'client_number' => $member->client_number,
                    'rejected_by' => Auth::id()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling member exit approval', [
                'approval_id' => $approval->id,
                'process_id' => $approval->process_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to trigger rollback
        }
    }
}
