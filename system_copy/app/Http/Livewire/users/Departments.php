<?php

namespace App\Http\Livewire\Users;

use Livewire\Component;
use App\Models\departmentsList;
use App\Models\menu_list;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use App\Models\Role;
use Livewire\WithPagination;
use Illuminate\Http\Request;
class Departments extends Component
{
    use WithPagination;

    protected $paginatedDepartments;
    protected $departments = [];
    public $existingDepartments;
    public $departmentsList;
    public $department_name;
    public $department;
    public $branches;
    public $search = '';
    public $statusFilter = '';
    public $branchFilter = '';

    // Modal states
    public $showCreateDepartment = false;
    public $showDeleteDepartment = false;
    public $editingDepartment = null;
    public $deletingDepartment;
    public $availableRoles;

    // Form properties
    public $name;
    public $code;
    public $branch;
    public $description;
    public $status = true;
    public $selectedRoles = [];
    public $selectedDashboardType;

    protected $rules = [
        'name' => 'required|min:3',
        // 'department_name' => 'required|min:4',
        'code' => 'required|min:2',
        'branch' => 'required|exists:branches,id',
        'status' => 'required|boolean',
        'selectedRoles' => 'required|array|min:1',
        'selectedDashboardType' => 'required|integer|min:1|max:10',
    ];

    // protected $messages = [
    //     'selectedRoles.required' => 'Please select at least one role.',
    //     'selectedRoles.min' => 'Please select at least one role.',
    //     'branch.required' => 'Please select a branch.',
    //     'branch.exists' => 'The selected branch is invalid.',
    // ];

    public function mount()
    {
        $this->loadDepartments();
    }

    public function loadDepartments()
    {
        $query = departmentsList::with('branch');

        if ($this->search) {
            $query->where('department_name', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter === 'true' ? true : false);
        }

        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        $this->paginatedDepartments = $query->paginate(9);
        $this->departments = $this->paginatedDepartments;
    }

    public function updatedSearch()
    {
        $this->loadDepartments();
    }

    public function updatedStatusFilter()
    {
        $this->loadDepartments();
    }

    public function updatedBranchFilter()
    {
        $this->loadDepartments();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->branchFilter = '';
        $this->loadDepartments();
    }

    public function save(){
        $this->validate();

        departmentsList::create([
            'institution' => TeamUser::where('user_id',Auth::user()->id)->value('institution'),
            'department_name' => $this->department_name,
            'modules' => json_encode($this->departments),
        ]);

        $this->paginatedDepartments = departmentsList::with('branch')->paginate(9);
        $this->departments = $this->paginatedDepartments;
        $this->department_name = null;
    }

    public function render()
    {
        $this->departmentsList = menu_list::get();
        $this->existingDepartments = departmentsList::get();
        $this->branches = Branch::get();
        $this->availableRoles = Role::get();
        
        // Always load departments to maintain pagination
        $this->loadDepartments();
        
        return view('livewire.users.departments', [
            'departments' => $this->departments,
            'paginatedDepartments' => $this->paginatedDepartments,
        ]);
    }

    public function editDepartment($id)
    {
        $department = departmentsList::findOrFail($id);
        $this->editingDepartment = $id;
        $this->name = $department->department_name;
        $this->code = $department->department_code ?? $department->code;
        $this->branch = $department->branch_id;
        $this->description = $department->description;
        $this->status = $department->status;
        $this->selectedDashboardType = $department->dashboard_type;
        //$this->selectedRoles = $department->roles->pluck('id')->toArray();
        $this->showCreateDepartment = true;
    }

    public function deleteDepartment($id)
    {
        $this->deletingDepartment = departmentsList::findOrFail($id);
        $this->showDeleteDepartment = true;
    }

    public function confirmDeleteDepartment()
    {
        if ($this->deletingDepartment) {
            $this->deletingDepartment->delete();
            $this->showDeleteDepartment = false;
            $this->deletingDepartment = null;
            session()->flash('message', 'Department deleted successfully.');
        }
    }

    public function saveDepartment()
    {
        $this->validate();
        $data = [
            'department_name' => $this->name,
            'department_code' => $this->code,
            'branch_id' => $this->branch,
            'description' => $this->description,
            'status' => $this->status,
            'dashboard_type' => $this->selectedDashboardType,
            'institution_id' => 11 // to be included later
        ];
        if ($this->editingDepartment) {
            $department = departmentsList::findOrFail($this->editingDepartment);
            $department->update($data);
            foreach ($this->selectedRoles as $roleId) {
                $department->roles()->updateOrCreate([
                    'department_id' => $department->id
                ]);
            }
            session()->flash('message', 'Department updated successfully.');
        } else {
            $department = departmentsList::create($data);
            foreach ($this->selectedRoles as $roleId) {
                $role = new Role();
                $role->department_id = $department->id;
                $role->name = $roleId;
                $role->save();
            }
            session()->flash('message', 'Department created successfully.');
        }
        $this->resetForm();
        $this->showCreateDepartment = false;
    }

    public function resetForm()
    {
        $this->editingDepartment = null;
        $this->showCreateDepartment = false;
        $this->name = '';
        $this->code = '';
        $this->branch = '';
        $this->description = '';
        $this->status = true;
        $this->selectedRoles = [];
        $this->selectedDashboardType = '';
    }
}
