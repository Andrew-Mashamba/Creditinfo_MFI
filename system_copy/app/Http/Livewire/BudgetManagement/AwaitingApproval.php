<?php

namespace App\Http\Livewire\BudgetManagement;

use App\Models\BudgetManagement;
use App\Models\approvals;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class AwaitingApproval extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'PENDING';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public function mount()
    {
        Log::channel('budget_management')->info('Pending approval page loaded', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);
    }

    public function approveBudget($id)
    {
        Log::channel('budget_management')->info('Budget approval started', [
            'budget_id' => $id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);

        try {
            $budget = BudgetManagement::findOrFail($id);
            
            Log::channel('budget_management')->info('Budget item found for approval', [
                'budget_id' => $id,
                'budget_name' => $budget->budget_name,
                'current_status' => $budget->status,
                'current_approval_status' => $budget->approval_status,
                'user_id' => auth()->id()
            ]);

            $budget->update([
                'approval_status' => 'APPROVED',
                'status' => 'ACTIVE'
            ]);

            // Update approval record if exists
            $approval = approvals::where('process_id', $id)
                ->where('process_code', 'BUDGET_CREATE')
                ->first();
            
            if ($approval) {
                $approval->update([
                    'approver_id' => auth()->id(),
                    'process_status' => 'APPROVED',
                    'approval_status' => 'APPROVED',
                    'approval_process_description' => 'Budget item approved by ' . auth()->user()->name,
                ]);
            }
            
            Log::channel('budget_management')->info('Budget item approved successfully', [
                'budget_id' => $id,
                'budget_name' => $budget->budget_name,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name
            ]);
            
            session()->flash('message', 'Budget item approved successfully.');
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Budget approval failed', [
                'budget_id' => $id,
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to approve budget item: ' . $e->getMessage());
        }
    }

    public function rejectBudget($id)
    {
        Log::channel('budget_management')->info('Budget rejection started', [
            'budget_id' => $id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);

        try {
            $budget = BudgetManagement::findOrFail($id);
            
            Log::channel('budget_management')->info('Budget item found for rejection', [
                'budget_id' => $id,
                'budget_name' => $budget->budget_name,
                'current_status' => $budget->status,
                'current_approval_status' => $budget->approval_status,
                'user_id' => auth()->id()
            ]);

            $budget->update([
                'approval_status' => 'REJECTED',
                'status' => 'INACTIVE'
            ]);

            // Update approval record if exists
            $approval = approvals::where('process_id', $id)
                ->where('process_code', 'BUDGET_CREATE')
                ->first();
            
            if ($approval) {
                $approval->update([
                    'approver_id' => auth()->id(),
                    'process_status' => 'REJECTED',
                    'approval_status' => 'REJECTED',
                    'approval_process_description' => 'Budget item rejected by ' . auth()->user()->name,
                ]);
            }
            
            Log::channel('budget_management')->info('Budget item rejected successfully', [
                'budget_id' => $id,
                'budget_name' => $budget->budget_name,
                'rejected_by' => auth()->id(),
                'rejected_by_name' => auth()->user()->name
            ]);
            
            session()->flash('message', 'Budget item rejected successfully.');
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Budget rejection failed', [
                'budget_id' => $id,
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to reject budget item: ' . $e->getMessage());
        }
    }

    public function approveAll()
    {
        Log::channel('budget_management')->info('Bulk approval started', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);

        try {
            $pendingBudgets = BudgetManagement::where('approval_status', 'PENDING')->get();
            $count = 0;

            foreach ($pendingBudgets as $budget) {
                $budget->update([
                    'approval_status' => 'APPROVED',
                    'status' => 'ACTIVE'
                ]);

                // Update approval record if exists
                $approval = approvals::where('process_id', $budget->id)
                    ->where('process_code', 'BUDGET_CREATE')
                    ->first();
                
                if ($approval) {
                    $approval->update([
                        'approver_id' => auth()->id(),
                        'process_status' => 'APPROVED',
                        'approval_status' => 'APPROVED',
                        'approval_process_description' => 'Bulk approved by ' . auth()->user()->name,
                    ]);
                }
                $count++;
            }
            
            Log::channel('budget_management')->info('Bulk approval completed', [
                'approved_count' => $count,
                'approved_by' => auth()->id(),
                'approved_by_name' => auth()->user()->name
            ]);
            
            session()->flash('message', "Successfully approved {$count} budget items.");
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Bulk approval failed', [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to approve budget items: ' . $e->getMessage());
        }
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = BudgetManagement::query();

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('budget_name', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('approval_status', $this->statusFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $pendingBudgets = $query->paginate($this->perPage);

        return view('livewire.budget-management.awaiting-approval', [
            'pendingBudgets' => $pendingBudgets
        ]);
    }
}
