<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Till;
use App\Models\Teller;
use App\Models\User;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Tills extends Component
{
    use WithPagination;

    // UI State
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showAssignModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    
    // Form Data
    public $till = [
        'name' => '',
        'till_number' => '',
        'branch_id' => '',
        'institution_id' => '',
        'maximum_limit' => 500000.00,
        'minimum_limit' => 10000.00,
        'status' => 'closed',
        'requires_supervisor_approval' => false,
        'description' => ''
    ];
    
    public $editingTillId;
    public $selectedTillId;
    public $assignUserId;
    public $assignUserType = 'teller'; // teller, employee
    
    // Filters and Search
    public $searchTerm = '';
    public $filterStatus = '';
    public $filterBranch = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Pagination
    protected $paginationTheme = 'bootstrap';
    
    protected $rules = [
        'till.name' => 'required|string|max:255',
        'till.till_number' => 'required|string|max:50|unique:tills,till_number',
        'till.branch_id' => 'required|exists:branches,id',
        'till.institution_id' => 'required|exists:institutions,id',
        'till.maximum_limit' => 'required|numeric|min:0',
        'till.minimum_limit' => 'required|numeric|min:0',
        'till.status' => 'required|in:open,closed,suspended',
        'till.requires_supervisor_approval' => 'boolean',
        'till.description' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'till.name.required' => 'Till name is required.',
        'till.till_number.required' => 'Till number is required.',
        'till.till_number.unique' => 'This till number already exists.',
        'till.branch_id.required' => 'Please select a branch.',
        'till.institution_id.required' => 'Please select an institution.',
        'till.maximum_limit.required' => 'Maximum limit is required.',
        'till.minimum_limit.required' => 'Minimum limit is required.',
    ];

    public function mount()
    {
        // Set default institution ID if available
        $this->till['institution_id'] = auth()->user()->institution_id ?? 1;
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterBranch()
    {
        $this->resetPage();
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

    public function showCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function showEditModal($tillId)
    {
        $till = Till::findOrFail($tillId);
        $this->editingTillId = $tillId;
        $this->till = $till->toArray();
        $this->showEditModal = true;
    }

    public function showAssignModal($tillId)
    {
        $this->selectedTillId = $tillId;
        $this->assignUserId = '';
        $this->assignUserType = 'teller';
        $this->showAssignModal = true;
    }

    public function showViewModal($tillId)
    {
        $this->selectedTillId = $tillId;
        $this->showViewModal = true;
    }

    public function showDeleteModal($tillId)
    {
        $this->selectedTillId = $tillId;
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showAssignModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->till = [
            'name' => '',
            'till_number' => '',
            'branch_id' => '',
            'institution_id' => auth()->user()->institution_id ?? 1,
            'maximum_limit' => 500000.00,
            'minimum_limit' => 10000.00,
            'status' => 'closed',
            'requires_supervisor_approval' => false,
            'description' => ''
        ];
        $this->editingTillId = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            if ($this->editingTillId) {
                // Update existing till
                $till = Till::findOrFail($this->editingTillId);
                
                // Check if till number is unique (excluding current till)
                if ($till->till_number !== $this->till['till_number']) {
                    $this->validate([
                        'till.till_number' => 'unique:tills,till_number,' . $this->editingTillId
                    ]);
                }
                
                $till->update($this->till);
                
                Log::info('Till updated successfully', [
                    'till_id' => $till->id,
                    'till_name' => $till->name,
                    'updated_by' => Auth::id(),
                    'changes' => $till->getChanges(),
                ]);
                
                session()->flash('success', 'Till updated successfully.');
            } else {
                // Create new till
                $till = Till::create($this->till);
                
                Log::info('Till created successfully', [
                    'till_id' => $till->id,
                    'till_name' => $till->name,
                    'created_by' => Auth::id(),
                ]);
                
                session()->flash('success', 'Till created successfully.');
            }

            DB::commit();
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Till save failed', [
                'error' => $e->getMessage(),
                'till_data' => $this->till,
            ]);
            session()->flash('error', 'Error saving till: ' . $e->getMessage());
        }
    }

    public function assignUser()
    {
        $this->validate([
            'assignUserId' => 'required|exists:users,id',
            'assignUserType' => 'required|in:teller,employee',
        ]);

        DB::beginTransaction();
        try {
            $till = Till::findOrFail($this->selectedTillId);
            $user = User::findOrFail($this->assignUserId);

            if ($this->assignUserType === 'teller') {
                // Check if user is already assigned to another till
                $existingTeller = Teller::where('user_id', $this->assignUserId)->first();
                if ($existingTeller) {
                    session()->flash('error', 'This user is already assigned to another till.');
                    return;
                }

                // Create or update teller record
                Teller::updateOrCreate(
                    ['user_id' => $this->assignUserId],
                    [
                        'till_id' => $this->selectedTillId,
                        'branch_id' => $till->branch_id,
                        'institution_id' => $till->institution_id,
                        'status' => 'active',
                    ]
                );
            }

            Log::info('User assigned to till', [
                'till_id' => $till->id,
                'user_id' => $this->assignUserId,
                'user_type' => $this->assignUserType,
                'assigned_by' => Auth::id(),
            ]);

            DB::commit();
            session()->flash('success', 'User assigned to till successfully.');
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User assignment failed', [
                'error' => $e->getMessage(),
                'till_id' => $this->selectedTillId,
                'user_id' => $this->assignUserId,
            ]);
            session()->flash('error', 'Error assigning user: ' . $e->getMessage());
        }
    }

    public function unassignUser($tellerId)
    {
        DB::beginTransaction();
        try {
            $teller = Teller::findOrFail($tellerId);
            $tillId = $teller->till_id;
            $userId = $teller->user_id;
            
            $teller->delete();
            
            Log::info('User unassigned from till', [
                'till_id' => $tillId,
                'user_id' => $userId,
                'unassigned_by' => Auth::id(),
            ]);
            
            DB::commit();
            session()->flash('success', 'User unassigned from till successfully.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User unassignment failed', [
                'error' => $e->getMessage(),
                'teller_id' => $tellerId,
            ]);
            session()->flash('error', 'Error unassigning user: ' . $e->getMessage());
        }
    }

    public function deleteTill()
    {
        DB::beginTransaction();
        try {
            $till = Till::findOrFail($this->selectedTillId);
            
            // Check if till has any transactions
            if ($till->transactions()->count() > 0) {
                session()->flash('error', 'Cannot delete till with existing transactions.');
                return;
            }
            
            // Check if till is currently assigned
            if ($till->teller) {
                session()->flash('error', 'Cannot delete till that is currently assigned to a teller.');
                return;
            }
            
            $tillName = $till->name;
            $till->delete();
            
            Log::info('Till deleted successfully', [
                'till_name' => $tillName,
                'deleted_by' => Auth::id(),
            ]);
            
            DB::commit();
            session()->flash('success', 'Till deleted successfully.');
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Till deletion failed', [
                'error' => $e->getMessage(),
                'till_id' => $this->selectedTillId,
            ]);
            session()->flash('error', 'Error deleting till: ' . $e->getMessage());
        }
    }

    public function toggleStatus($tillId)
    {
        DB::beginTransaction();
        try {
            $till = Till::findOrFail($tillId);
            $oldStatus = $till->status;
            
            $till->status = $till->status === 'open' ? 'closed' : 'open';
            $till->save();
            
            Log::info('Till status toggled', [
                'till_id' => $till->id,
                'old_status' => $oldStatus,
                'new_status' => $till->status,
                'toggled_by' => Auth::id(),
            ]);
            
            DB::commit();
            session()->flash('success', 'Till status updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Till status toggle failed', [
                'error' => $e->getMessage(),
                'till_id' => $tillId,
            ]);
            session()->flash('error', 'Error updating till status: ' . $e->getMessage());
        }
    }

    public function exportTills()
    {
        // Implementation for exporting tills data
        session()->flash('info', 'Export functionality will be implemented.');
    }

    public function render()
    {
        $tills = Till::with(['teller.user', 'branch'])
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('till_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->filterStatus, function($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterBranch, function($query) {
                $query->where('branch_id', $this->filterBranch);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        $branches = Branch::all();
        $users = User::where('institution_id', auth()->user()->institution_id ?? 1)->get();
        $tellers = Teller::with('user')->get();

        // Statistics
        $stats = [
            'total_tills' => Till::count(),
            'open_tills' => Till::where('status', 'open')->count(),
            'closed_tills' => Till::where('status', 'closed')->count(),
            'assigned_tills' => Till::whereHas('teller')->count(),
            'unassigned_tills' => Till::whereDoesntHave('teller')->count(),
        ];

        return view('livewire.accounting.tills', [
            'tills' => $tills,
            'branches' => $branches,
            'users' => $users,
            'tellers' => $tellers,
            'stats' => $stats,
        ]);
    }
}
