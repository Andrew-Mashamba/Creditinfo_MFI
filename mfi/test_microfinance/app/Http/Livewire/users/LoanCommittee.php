<?php

namespace App\Http\Livewire\Users;

use App\Models\Committee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LoanCommittee extends Component
{
    use WithPagination;

    // Properties for committee management
    public $showCreateCommittee = false;
    public $showDeleteCommittee = false;
    public $editingCommittee = null;
    public $deletingCommittee = null;
    public $search = '';
    public $statusFilter = '';
    public $departmentFilter = '';

    // Form properties
    public $name;
    public $description;
    public $department;
    public $status = 'ACTIVE';
    public $loan_category;
    public $min_approvals_required;
    public $selectedMembers = [];
    public $primaryApprovers = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'department' => 'required|exists:departments,id',
        'status' => 'required|in:ACTIVE,INACTIVE',
        'loan_category' => 'required|in:loan,payment',
        'min_approvals_required' => 'required|integer|min:1',
        'selectedMembers' => 'required|array|min:1',
        'primaryApprovers' => 'required|array|min:1',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->department = '';
        $this->status = 'ACTIVE';
        $this->loan_category = '';
        $this->min_approvals_required = 1;
        $this->selectedMembers = [];
        $this->primaryApprovers = [];
        $this->editingCommittee = null;
    }

    public function createCommittee()
    {
        $this->resetForm();
        $this->showCreateCommittee = true;
    }

    public function editCommittee($id)
    {
        $committee = Committee::with('members')->findOrFail($id);
        $this->editingCommittee = $committee;
        $this->name = $committee->name;
        $this->description = $committee->description;
        $this->department = $committee->department_id;
        $this->status = $committee->status;
        $this->loan_category = $committee->loan_category;
        $this->min_approvals_required = $committee->min_approvals_required;
        $this->selectedMembers = $committee->members->pluck('id')->toArray();
        $this->primaryApprovers = $committee->members()
            ->wherePivot('is_primary_approver', true)
            ->pluck('id')
            ->toArray();
        $this->showCreateCommittee = true;
    }

    public function saveCommittee()
    {
        $this->validate();

        // Ensure at least one primary approver
        if (count($this->primaryApprovers) < 1) {
            $this->addError('primaryApprovers', 'At least one primary approver is required.');
            return;
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'department_id' => $this->department,
            'status' => $this->status,
            'loan_category' => $this->loan_category,
            'min_approvals_required' => $this->min_approvals_required,
        ];

        if ($this->editingCommittee) {
            $committee = $this->editingCommittee;
            $committee->update($data);
        } else {
            $committee = Committee::create($data);
        }

        // Sync members with their roles, primary approver status, and approval order
        $membersData = [];
        foreach ($this->selectedMembers as $index => $memberId) {
            $membersData[$memberId] = [
                'is_primary_approver' => in_array($memberId, $this->primaryApprovers),
                'role' => 'member',
                'approval_order' => $index + 1
            ];
        }
        $committee->members()->sync($membersData);

        $this->showCreateCommittee = false;
        $this->resetForm();
        session()->flash('message', 'Committee ' . ($this->editingCommittee ? 'updated' : 'created') . ' successfully.');
    }

    public function deleteCommittee($id)
    {
        $this->deletingCommittee = Committee::withCount('members')->findOrFail($id);
        $this->showDeleteCommittee = true;
    }

    public function confirmDeleteCommittee()
    {
        if ($this->deletingCommittee) {
            $this->deletingCommittee->delete();
            $this->showDeleteCommittee = false;
            $this->deletingCommittee = null;
            session()->flash('message', 'Committee deleted successfully.');
        }
    }

    public function removeMember($committeeId, $memberId)
    {
        $committee = Committee::findOrFail($committeeId);
        $committee->members()->detach($memberId);
        session()->flash('message', 'Member removed successfully.');
    }

    public function render()
    {
        $query = Committee::with(['department', 'members'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->departmentFilter, function ($query) {
                $query->where('department_id', $this->departmentFilter);
            });

        return view('livewire.users.loan-committee', [
            'committees' => $query->paginate(10),
            'departments' => Department::active()->get(),
            'availableUsers' => User::with('roles')->get(),
        ]);
    }
}
