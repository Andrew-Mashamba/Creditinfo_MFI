<?php

namespace App\Http\Livewire\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberGroups extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    // Search and filter
    public $searchTerm = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Form fields
    public $group_id = '';
    public $group_name = '';
    public $bank_name = '';
    public $bank_account = '';
    public $payrol_date = '';
    public $status = 'active';
    
    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingGroupId = null;
    public $deletingGroupId = null;
    
    protected $rules = [
        'group_id' => 'required|string|unique:member_groups,group_id',
        'group_name' => 'required|string|max:255',
        'bank_name' => 'nullable|string|max:255',
        'bank_account' => 'nullable|string|max:255',
        'payrol_date' => 'nullable|integer|min:1|max:31',
        'status' => 'required|in:active,inactive',
    ];
    
    protected $messages = [
        'group_id.required' => 'Group ID is required.',
        'group_id.unique' => 'This Group ID already exists.',
        'group_name.required' => 'Group name is required.',
        'payrol_date.integer' => 'Payroll date must be a number.',
        'payrol_date.min' => 'Payroll date must be between 1 and 31.',
        'payrol_date.max' => 'Payroll date must be between 1 and 31.',
        'status.required' => 'Status is required.',
    ];

    public function render()
    {
        $query = DB::table('member_groups');
        
        // Apply search filter
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('group_id', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('group_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('bank_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('bank_account', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }
        
        $memberGroups = $query->orderBy('created_at', 'desc')->paginate($this->perPage);
        
        return view('livewire.clients.member-groups', [
            'memberGroups' => $memberGroups
        ]);
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
        $this->resetValidation();
    }
    
    public function createGroup()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            DB::table('member_groups')->insert([
                'group_id' => $this->group_id,
                'group_name' => $this->group_name,
                'bank_name' => $this->bank_name,
                'bank_account' => $this->bank_account,
                'payrol_date' => $this->payrol_date,
                'status' => $this->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Member group created', [
                'group_id' => $this->group_id,
                'group_name' => $this->group_name,
                'created_by' => auth()->id()
            ]);
            
            session()->flash('success', 'Member group created successfully.');
            $this->closeCreateModal();
            $this->resetPage();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating member group', [
                'error' => $e->getMessage(),
                'group_id' => $this->group_id
            ]);
            session()->flash('error', 'Failed to create member group: ' . $e->getMessage());
        }
    }
    
    public function openEditModal($id)
    {
        $group = DB::table('member_groups')->find($id);
        
        if ($group) {
            $this->editingGroupId = $id;
            $this->group_id = $group->group_id;
            $this->group_name = $group->group_name;
            $this->bank_name = $group->bank_name;
            $this->bank_account = $group->bank_account;
            $this->payrol_date = $group->payrol_date;
            $this->status = $group->status;
            $this->showEditModal = true;
        }
    }
    
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingGroupId = null;
        $this->resetForm();
        $this->resetValidation();
    }
    
    public function updateGroup()
    {
        $rules = $this->rules;
        // Modify unique rule for editing
        $rules['group_id'] = 'required|string|unique:member_groups,group_id,' . $this->editingGroupId;
        
        $this->validate($rules);
        
        try {
            DB::beginTransaction();
            
            DB::table('member_groups')
                ->where('id', $this->editingGroupId)
                ->update([
                    'group_id' => $this->group_id,
                    'group_name' => $this->group_name,
                    'bank_name' => $this->bank_name,
                    'bank_account' => $this->bank_account,
                    'payrol_date' => $this->payrol_date,
                    'status' => $this->status,
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            Log::info('Member group updated', [
                'id' => $this->editingGroupId,
                'group_id' => $this->group_id,
                'updated_by' => auth()->id()
            ]);
            
            session()->flash('success', 'Member group updated successfully.');
            $this->closeEditModal();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating member group', [
                'error' => $e->getMessage(),
                'id' => $this->editingGroupId
            ]);
            session()->flash('error', 'Failed to update member group: ' . $e->getMessage());
        }
    }
    
    public function confirmDelete($id)
    {
        $this->deletingGroupId = $id;
        $this->showDeleteModal = true;
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletingGroupId = null;
    }
    
    public function deleteGroup()
    {
        if (!$this->deletingGroupId) {
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $group = DB::table('member_groups')->find($this->deletingGroupId);
            
            if ($group) {
                DB::table('member_groups')->where('id', $this->deletingGroupId)->delete();
                
                DB::commit();
                
                Log::info('Member group deleted', [
                    'id' => $this->deletingGroupId,
                    'group_id' => $group->group_id,
                    'deleted_by' => auth()->id()
                ]);
                
                session()->flash('success', 'Member group deleted successfully.');
            }
            
            $this->closeDeleteModal();
            $this->resetPage();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting member group', [
                'error' => $e->getMessage(),
                'id' => $this->deletingGroupId
            ]);
            session()->flash('error', 'Failed to delete member group: ' . $e->getMessage());
        }
    }
    
    public function resetForm()
    {
        $this->group_id = '';
        $this->group_name = '';
        $this->bank_name = '';
        $this->bank_account = '';
        $this->payrol_date = '';
        $this->status = 'active';
    }
    
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatedPerPage()
    {
        $this->resetPage();
    }
}