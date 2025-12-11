<?php

namespace App\Http\Livewire\Users;

use App\Models\Role;
use App\Models\SubRole;
use App\Models\Department;
use App\Models\departmentsList;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HierarchicalRoles extends Component
{
    use WithPagination;

    // View state
    public $showCreateRoleModal = false;
    public $showCreateSubRoleModal = false;
    public $showDetailsModal = false;
    public $editingRoleId = null;
    public $editingSubRoleId = null;
    public $viewingRole = null;
    
    // Filters
    public $search = '';
    public $departmentFilter = '';
    public $viewMode = 'grid'; // grid or tree
    
    // Role form fields
    public $role_name = '';
    public $role_department_id = '';
    public $role_description = '';
    public $role_level = 1;
    
    // Sub-role form fields
    public $subrole_name = '';
    public $subrole_parent_id = '';
    public $subrole_description = '';
    public $inherit_permissions = true;
    
    // Statistics
    public $stats = [
        'total_roles' => 0,
        'total_subroles' => 0,
        'active_roles' => 0,
        'roles_with_users' => 0
    ];
    
    protected $rules = [
        'role_name' => 'required|min:3|max:100',
        'role_department_id' => 'required|exists:departments,id',
        'role_description' => 'nullable|max:500',
    ];

    protected $subroleRules = [
        'subrole_name' => 'required|min:3|max:100',
        'subrole_parent_id' => 'required|exists:roles,id',
        'subrole_description' => 'nullable|max:500',
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        $this->stats['total_roles'] = Role::count();
        $this->stats['total_subroles'] = SubRole::count();
        $this->stats['active_roles'] = Role::where('status', 'ACTIVE')->count();
        $this->stats['roles_with_users'] = Role::has('users')->count();
    }

    public function render()
    {
        $query = Role::with(['department', 'subRoles', 'users'])
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->departmentFilter, function($q) {
                $q->where('department_id', $this->departmentFilter);
            })
            ->withCount(['subRoles', 'users']);
            
        if ($this->viewMode === 'tree') {
            $roles = $query->get()->groupBy('department_id');
        } else {
            $roles = $query->paginate(12);
        }
        
        $departments = departmentsList::where('status', true)->get();
        
        return view('livewire.users.hierarchical-roles', [
            'roles' => $roles,
            'departments' => $departments
        ]);
    }

    // Role Management
    public function createRole()
    {
        $this->resetRoleForm();
        $this->showCreateRoleModal = true;
    }

    public function editRole($id)
    {
        $role = Role::findOrFail($id);
        $this->editingRoleId = $id;
        $this->role_name = $role->name;
        $this->role_department_id = $role->department_id;
        $this->role_description = $role->description;
        $this->role_level = $role->level ?? 1;
        $this->showCreateRoleModal = true;
    }

    public function saveRole()
    {
        $this->validate($this->rules);
        
        try {
            DB::beginTransaction();
            
            $data = [
                'name' => $this->role_name,
                'department_id' => $this->role_department_id,
                'description' => $this->role_description,
                'level' => $this->role_level,
                'status' => 'ACTIVE',
                'institution_id' => Auth::user()->institution_id ?? 11
            ];
            
            if ($this->editingRoleId) {
                Role::find($this->editingRoleId)->update($data);
                $message = 'Role updated successfully';
            } else {
                $role = Role::create($data);
                
                // Create default sub-roles for certain role types
                $this->createDefaultSubRoles($role);
                
                $message = 'Role created successfully';
            }
            
            DB::commit();
            $this->closeRoleModal();
            $this->loadStatistics();
            session()->flash('message', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving role: ' . $e->getMessage());
            session()->flash('error', 'Error saving role');
        }
    }

    private function createDefaultSubRoles($role)
    {
        // Create default sub-roles based on role name patterns
        $roleName = strtolower($role->name);
        $defaultSubRoles = [];
        
        if (strpos($roleName, 'manager') !== false) {
            $defaultSubRoles = [
                ['name' => 'Assistant Manager', 'description' => 'Assistant to the manager'],
                ['name' => 'Deputy Manager', 'description' => 'Deputy manager role']
            ];
        } elseif (strpos($roleName, 'supervisor') !== false) {
            $defaultSubRoles = [
                ['name' => 'Senior Officer', 'description' => 'Senior operational officer'],
                ['name' => 'Junior Officer', 'description' => 'Junior operational officer']
            ];
        } elseif (strpos($roleName, 'officer') !== false) {
            $defaultSubRoles = [
                ['name' => 'Senior', 'description' => 'Senior level'],
                ['name' => 'Junior', 'description' => 'Junior level']
            ];
        }
        
        foreach ($defaultSubRoles as $subRoleData) {
            SubRole::create([
                'role_id' => $role->id,
                'name' => $role->name . ' - ' . $subRoleData['name'],
                'description' => $subRoleData['description']
            ]);
        }
    }

    // Sub-Role Management
    public function createSubRole($parentRoleId = null)
    {
        $this->resetSubRoleForm();
        if ($parentRoleId) {
            $this->subrole_parent_id = $parentRoleId;
        }
        $this->showCreateSubRoleModal = true;
    }

    public function editSubRole($id)
    {
        $subRole = SubRole::findOrFail($id);
        $this->editingSubRoleId = $id;
        $this->subrole_name = $subRole->name;
        $this->subrole_parent_id = $subRole->role_id;
        $this->subrole_description = $subRole->description;
        $this->showCreateSubRoleModal = true;
    }

    public function saveSubRole()
    {
        $this->validate($this->subroleRules);
        
        try {
            DB::beginTransaction();
            
            $data = [
                'role_id' => $this->subrole_parent_id,
                'name' => $this->subrole_name,
                'description' => $this->subrole_description
            ];
            
            if ($this->editingSubRoleId) {
                SubRole::find($this->editingSubRoleId)->update($data);
                $message = 'Sub-role updated successfully';
            } else {
                SubRole::create($data);
                $message = 'Sub-role created successfully';
            }
            
            DB::commit();
            $this->closeSubRoleModal();
            $this->loadStatistics();
            session()->flash('message', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving sub-role: ' . $e->getMessage());
            session()->flash('error', 'Error saving sub-role');
        }
    }

    public function deleteSubRole($id)
    {
        try {
            $subRole = SubRole::findOrFail($id);
            
            // Check if sub-role has users
            if ($subRole->users()->exists()) {
                session()->flash('error', 'Cannot delete sub-role with assigned users');
                return;
            }
            
            $subRole->delete();
            session()->flash('message', 'Sub-role deleted successfully');
            $this->loadStatistics();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting sub-role');
        }
    }

    public function viewRoleDetails($id)
    {
        $this->viewingRole = Role::with([
            'department',
            'subRoles.users',
            'users',
            'menuActions'
        ])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function toggleRoleStatus($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->status = $role->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
            $role->save();
            
            session()->flash('message', 'Role status updated');
            $this->loadStatistics();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating status');
        }
    }

    public function deleteRole($id)
    {
        try {
            $role = Role::findOrFail($id);
            
            // Check dependencies
            if ($role->subRoles()->exists()) {
                session()->flash('error', 'Cannot delete role with existing sub-roles');
                return;
            }
            
            if ($role->users()->exists()) {
                session()->flash('error', 'Cannot delete role with assigned users');
                return;
            }
            
            $role->delete();
            session()->flash('message', 'Role deleted successfully');
            $this->loadStatistics();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting role');
        }
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'tree' : 'grid';
    }

    public function resetRoleForm()
    {
        $this->reset(['role_name', 'role_department_id', 'role_description', 'editingRoleId']);
        $this->role_level = 1;
        $this->resetValidation();
    }

    public function resetSubRoleForm()
    {
        $this->reset(['subrole_name', 'subrole_parent_id', 'subrole_description', 'editingSubRoleId']);
        $this->inherit_permissions = true;
        $this->resetValidation();
    }

    public function closeRoleModal()
    {
        $this->showCreateRoleModal = false;
        $this->resetRoleForm();
    }

    public function closeSubRoleModal()
    {
        $this->showCreateSubRoleModal = false;
        $this->resetSubRoleForm();
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->viewingRole = null;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'departmentFilter']);
    }
}