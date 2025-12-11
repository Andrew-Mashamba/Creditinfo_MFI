<?php

namespace App\Http\Livewire\Users;

use App\Models\Department;
use App\Models\departmentsList;
use App\Models\Branch;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HierarchicalDepartments extends Component
{
    use WithPagination;

    // View state
    public $showCreateModal = false;
    public $showDetailsModal = false;
    public $editingId = null;
    public $viewingDepartment = null;
    public $search = '';
    public $branchFilter = '';
    
    // Form fields
    public $department_name = '';
    public $department_code = '';
    public $branch_id = '';
    public $description = '';
    public $status = true;
    
    // Statistics
    public $stats = [
        'total' => 0,
        'active' => 0,
        'with_roles' => 0,
        'with_users' => 0
    ];
    
    protected $rules = [
        'department_name' => 'required|min:3|max:100',
        'department_code' => 'required|min:2|max:10|unique:departments,department_code',
        'branch_id' => 'required|exists:branches,id',
        'description' => 'nullable|max:500'
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        $this->stats['total'] = departmentsList::count();
        $this->stats['active'] = departmentsList::where('status', true)->count();
        $this->stats['with_roles'] = departmentsList::has('roles')->count();
        $this->stats['with_users'] = departmentsList::whereHas('roles', function($q) {
            $q->has('users');
        })->count();
    }

    public function render()
    {
        $departments = departmentsList::with(['branch', 'roles', 'roles.subRoles'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('department_name', 'like', '%' . $this->search . '%')
                      ->orWhere('department_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->branchFilter, function($query) {
                $query->where('branch_id', $this->branchFilter);
            })
            ->withCount('roles')
            ->paginate(10);
            
        // Calculate active roles count for each department
        foreach ($departments as $department) {
            $department->active_roles_count = $department->roles->where('status', 'ACTIVE')->count();
        }
            
        $branches = Branch::all();
        
        return view('livewire.users.hierarchical-departments', [
            'departments' => $departments,
            'branches' => $branches
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function edit($id)
    {
        $dept = departmentsList::findOrFail($id);
        $this->editingId = $id;
        $this->department_name = $dept->department_name;
        $this->department_code = $dept->department_code ?? $dept->code;
        $this->branch_id = $dept->branch_id;
        $this->description = $dept->description;
        $this->status = $dept->status;
        $this->showCreateModal = true;
    }

    public function save()
    {
        if ($this->editingId) {
            $this->rules['department_code'] = 'required|min:2|max:10|unique:departments,department_code,' . $this->editingId;
        }
        
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $data = [
                'department_name' => $this->department_name,
                'department_code' => strtoupper($this->department_code),
                'branch_id' => $this->branch_id,
                'description' => $this->description,
                'status' => $this->status,
                'institution_id' => Auth::user()->institution_id ?? 11
            ];
            
            if ($this->editingId) {
                departmentsList::find($this->editingId)->update($data);
                $message = 'Department updated successfully';
            } else {
                // Create department
                $department = departmentsList::create($data);
                
                // Create default roles for new department
                $this->createDefaultRoles($department);
                
                $message = 'Department created successfully with default roles';
            }
            
            DB::commit();
            $this->closeModal();
            $this->loadStatistics();
            session()->flash('message', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving department: ' . $e->getMessage());
            session()->flash('error', 'Error saving department: ' . $e->getMessage());
        }
    }

    private function createDefaultRoles($department)
    {
        $defaultRoles = [
            ['name' => 'Manager', 'description' => 'Department Manager with full access'],
            ['name' => 'Supervisor', 'description' => 'Department Supervisor with limited management access'],
            ['name' => 'Officer', 'description' => 'Department Officer with operational access']
        ];
        
        foreach ($defaultRoles as $roleData) {
            Role::create([
                'name' => $department->department_name . ' ' . $roleData['name'],
                'department_id' => $department->id,
                'description' => $roleData['description'],
                'status' => 'ACTIVE',
                'institution_id' => Auth::user()->institution_id ?? 11
            ]);
        }
    }

    public function viewDetails($id)
    {
        $this->viewingDepartment = departmentsList::with([
            'branch',
            'roles.subRoles',
            'roles.users'
        ])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function toggleStatus($id)
    {
        try {
            $dept = departmentsList::findOrFail($id);
            $dept->status = !$dept->status;
            $dept->save();
            
            session()->flash('message', 'Department status updated');
            $this->loadStatistics();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating status');
        }
    }

    public function delete($id)
    {
        try {
            $dept = departmentsList::findOrFail($id);
            
            // Check if department has roles
            if ($dept->roles()->exists()) {
                session()->flash('error', 'Cannot delete department with existing roles. Please remove roles first.');
                return;
            }
            
            $dept->delete();
            session()->flash('message', 'Department deleted successfully');
            $this->loadStatistics();
            
        } catch (\Exception $e) {
            Log::error('Error deleting department: ' . $e->getMessage());
            session()->flash('error', 'Cannot delete department');
        }
    }

    public function resetForm()
    {
        $this->reset(['department_name', 'department_code', 'branch_id', 'description', 'editingId']);
        $this->status = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showDetailsModal = false;
        $this->resetForm();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'branchFilter']);
    }
}