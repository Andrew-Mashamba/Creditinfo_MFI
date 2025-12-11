<?php

namespace App\Http\Livewire\Users;

use App\Models\departmentsList;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentsManagement extends Component
{
    use WithPagination;

    // Search and filters
    public $search = '';
    public $statusFilter = '';
    
    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingId = null;
    public $deletingId = null;
    
    // Form fields
    public $department_name;
    public $department_code;
    public $parent_department_id;
    public $description;
    public $status = true;
    public $level;
    
    // Available options
    public $parentDepartments = [];
    
    // Sorting
    public $sortField = 'department_name';
    public $sortDirection = 'asc';
    
    protected $listeners = ['refreshDepartments' => '$refresh'];
    
    protected function rules()
    {
        $rules = [
            'department_name' => 'required|string|min:3|max:255',
            'department_code' => 'required|string|max:10|unique:departments,department_code',
            'parent_department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'level' => 'nullable|integer|min:1|max:5',
        ];
        
        if ($this->editingId) {
            $rules['department_code'] = 'required|string|max:10|unique:departments,department_code,' . $this->editingId;
        }
        
        return $rules;
    }
    
    public function mount()
    {
        $this->loadParentDepartments();
    }
    
    public function loadParentDepartments()
    {
        $this->parentDepartments = departmentsList::where('status', true)
            ->whereNull('parent_department_id')
            ->orWhere('level', 1)
            ->orderBy('department_name')
            ->get();
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->loadParentDepartments();
        $this->showCreateModal = true;
    }
    
    public function openEditModal($id)
    {
        $this->resetForm();
        $this->editingId = $id;
        $this->loadParentDepartments();
        
        $department = departmentsList::find($id);
        if ($department) {
            $this->department_name = $department->department_name;
            $this->department_code = $department->department_code;
            $this->parent_department_id = $department->parent_department_id;
            $this->description = $department->description;
            $this->status = $department->status;
            $this->level = $department->level;
            $this->showEditModal = true;
        }
    }
    
    public function openDeleteModal($id)
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $data = [
                'department_name' => $this->department_name,
                'department_code' => strtoupper($this->department_code),
                'parent_department_id' => $this->parent_department_id,
                'description' => $this->description,
                'status' => $this->status,
                'level' => $this->level ?: ($this->parent_department_id ? 2 : 1),
            ];
            
            // Calculate path
            if ($this->parent_department_id) {
                $parent = departmentsList::find($this->parent_department_id);
                $data['path'] = $parent->path . '.' . $this->parent_department_id;
            } else {
                $data['path'] = '0';
            }
            
            if ($this->editingId) {
                departmentsList::find($this->editingId)->update($data);
                $message = 'Department updated successfully.';
            } else {
                departmentsList::create($data);
                $message = 'Department created successfully.';
            }
            
            DB::commit();
            
            session()->flash('message', $message);
            $this->showCreateModal = false;
            $this->showEditModal = false;
            $this->resetForm();
            $this->emit('refreshDepartments');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving department: ' . $e->getMessage());
            session()->flash('error', 'Error saving department: ' . $e->getMessage());
        }
    }
    
    public function delete()
    {
        try {
            $department = departmentsList::find($this->deletingId);
            
            if ($department) {
                // Check if department has roles
                if ($department->roles()->count() > 0) {
                    session()->flash('error', 'Cannot delete department with existing roles.');
                    $this->showDeleteModal = false;
                    return;
                }
                
                // Check if department has child departments
                if (departmentsList::where('parent_department_id', $this->deletingId)->exists()) {
                    session()->flash('error', 'Cannot delete department with child departments.');
                    $this->showDeleteModal = false;
                    return;
                }
                
                $department->delete();
                session()->flash('message', 'Department deleted successfully.');
            }
            
            $this->showDeleteModal = false;
            $this->deletingId = null;
            $this->emit('refreshDepartments');
            
        } catch (\Exception $e) {
            Log::error('Error deleting department: ' . $e->getMessage());
            session()->flash('error', 'Error deleting department: ' . $e->getMessage());
        }
    }
    
    public function resetForm()
    {
        $this->department_name = '';
        $this->department_code = '';
        $this->parent_department_id = null;
        $this->description = '';
        $this->status = true;
        $this->level = null;
        $this->editingId = null;
        $this->resetValidation();
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
    
    public function render()
    {
        $query = departmentsList::with('parentDepartment')
            ->withCount('roles')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('department_name', 'ilike', '%' . $this->search . '%')
                        ->orWhere('department_code', 'ilike', '%' . $this->search . '%')
                        ->orWhere('description', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($q) {
                $q->where('status', $this->statusFilter === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        return view('livewire.users.departments-management', [
            'departments' => $query->paginate(10),
            'totalDepartments' => departmentsList::count(),
            'activeDepartments' => departmentsList::where('status', true)->count(),
        ]);
    }
}