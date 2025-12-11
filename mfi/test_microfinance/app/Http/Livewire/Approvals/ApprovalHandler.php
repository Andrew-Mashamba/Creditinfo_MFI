<?php

namespace App\Http\Livewire\Approvals;

use Livewire\Component;
use App\Models\ProcessCodeConfig;
use App\Models\Role;
use App\Models\Approval;
use Illuminate\Support\Facades\Auth;

class ApprovalHandler extends Component
{
    public $comment;
    public $showApprovalModal = false;
    public $showRejectionModal = false;
    public $selectedApproval;
    public $config;
    public $searchTerm = '';
    public $statusFilter = 'PENDING';

    protected $listeners = ['showApprovalModal', 'showRejectionModal', 'refreshApprovals' => '$refresh'];

    public function mount()
    {
        // No parameters needed for initial mount
    }

    public function showApprovalModal($approvalId)
    {
        $this->selectedApproval = Approval::findOrFail($approvalId);
        $this->config = ProcessCodeConfig::where('process_code', $this->selectedApproval->process_code)
            ->where('is_active', true)
            ->first();
        $this->resetValidation();
        $this->comment = '';
        $this->showApprovalModal = true;
    }

    public function showRejectionModal($approvalId)
    {
        $this->selectedApproval = Approval::findOrFail($approvalId);
        $this->config = ProcessCodeConfig::where('process_code', $this->selectedApproval->process_code)
            ->where('is_active', true)
            ->first();
        $this->resetValidation();
        $this->comment = '';
        $this->showRejectionModal = true;
    }

    public function approve()
    {
        $this->validate([
            'comment' => 'nullable|string|max:500'
        ]);

        try {
            $this->selectedApproval->approvable->approve($this->comment);
            $this->showApprovalModal = false;
            $this->emit('notify', ['type' => 'success', 'message' => 'Request approved successfully']);
            $this->emit('refreshApprovals');
        } catch (\Exception $e) {
            $this->emit('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function reject()
    {
        $this->validate([
            'comment' => 'required|string|max:500'
        ]);

        try {
            $this->selectedApproval->approvable->reject($this->comment);
            $this->showRejectionModal = false;
            $this->emit('notify', ['type' => 'success', 'message' => 'Request rejected successfully']);
            $this->emit('refreshApprovals');
        } catch (\Exception $e) {
            $this->emit('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getApprovalChainProperty()
    {
        if (!$this->config || !$this->selectedApproval) {
            return collect();
        }

        $chain = collect();
        $roles = Role::all();

        if ($this->config->requires_first_checker) {
            $chain->push([
                'level' => 1,
                'title' => 'First Checker',
                'roles' => collect($this->config->first_checker_roles)->map(function($roleId) use ($roles) {
                    return $roles->firstWhere('id', $roleId)?->name;
                })->filter()->join(', ')
            ]);
        }

        if ($this->config->requires_second_checker) {
            $chain->push([
                'level' => 2,
                'title' => 'Second Checker',
                'roles' => collect($this->config->second_checker_roles)->map(function($roleId) use ($roles) {
                    return $roles->firstWhere('id', $roleId)?->name;
                })->filter()->join(', ')
            ]);
        }

        if ($this->config->requires_approver) {
            $chain->push([
                'level' => 3,
                'title' => 'Approver',
                'roles' => collect($this->config->approver_roles)->map(function($roleId) use ($roles) {
                    return $roles->firstWhere('id', $roleId)?->name;
                })->filter()->join(', ')
            ]);
        }

        return $chain;
    }

    public function getPendingApprovalsProperty()
    {
        $query = Approval::query()
            ->with(['maker', 'firstChecker', 'secondChecker', 'approver'])
            ->where('institution', Auth::user()->institution_id);

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('process_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('process_description', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('approval_status', $this->statusFilter);
        }

        return $query->latest()->get();
    }

    public function canApprove($approval)
    {
        $user = Auth::user();
        $currentLevel = $approval->checker_level;

        $config = ProcessCodeConfig::where('process_code', $approval->process_code)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return false;
        }

        return $config->canUserApprove($user, $currentLevel);
    }

    public function render()
    {
        return view('livewire.approvals.approval-handler', [
            'pendingApprovals' => $this->pendingApprovals,
            'approvalChain' => $this->approvalChain
        ]);
    }
} 