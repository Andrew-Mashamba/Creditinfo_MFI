<?php

namespace App\Http\Livewire\Users;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SubRole;
use App\Models\Department;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ManagePermissions extends Component
{
    use WithPagination;

    // Properties for permission management
    public $showCreatePermission = false;
    public $showDeletePermission = false;
    public $showBulkAssignment = false;
    public $editingPermission = null;
    public $deletingPermission = null;
    public $search = '';
    public $moduleFilter = '';
    public $actionFilter = '';
    
    // Sorting properties
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Form properties
    public $name;
    public $description;
    public $module;
    public $action;
    public $is_system = false;

    // Role assignment properties
    public $selectedRoles = [];
    public $selectedSubRoles = [];
    public $selectedDepartments = [];
    public $selectedUsers = [];
    public $selectedPermission;
    public $selectedPermissionUser;

    // Bulk assignment properties
    public $bulkPermissions = [];
    public $bulkRoles = [];
    public $bulkSubRoles = [];
    public $bulkDepartments = [];
    public $bulkAssignmentType = 'roles'; // 'roles', 'sub_roles', 'users'
    public $bulkInheritFromParent = false;
    public $bulkConditions = [];

    // Enhanced assignment properties
    public $assignmentMode = 'individual'; // 'individual', 'bulk', 'template'
    public $selectedTemplate = '';
    public $showRoleHierarchy = false;
    public $selectedParentRole = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'module' => 'required|string|max:255',
        'action' => 'required|string|max:255',
        'is_system' => 'boolean'
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->module = '';
        $this->action = '';
        $this->is_system = false;
        $this->editingPermission = null;
        $this->selectedRoles = [];
        $this->selectedSubRoles = [];
        $this->selectedDepartments = [];
        $this->selectedUsers = [];
        $this->selectedPermission = '';
        $this->selectedPermissionUser = '';
        $this->bulkPermissions = [];
        $this->bulkRoles = [];
        $this->bulkSubRoles = [];
        $this->bulkDepartments = [];
        $this->bulkConditions = [];
    }

    public function createPermission()
    {
        $this->resetForm();
        $this->showCreatePermission = true;
    }

    public function editPermission($id)
    {
        $permission = Permission::findOrFail($id);
        $this->editingPermission = $permission;
        $this->name = $permission->name;
        $this->description = $permission->description;
        $this->module = $permission->module;
        $this->action = $permission->action;
        $this->is_system = $permission->is_system;
        $this->showCreatePermission = true;
    }

    public function savePermission()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'module' => $this->module,
            'action' => $this->action,
            'is_system' => $this->is_system
        ];

        if ($this->editingPermission) {
            $permission = $this->editingPermission;
            $permission->update($data);
        } else {
            $permission = Permission::create($data);
        }

        $this->showCreatePermission = false;
        $this->resetForm();
        session()->flash('message', 'Permission ' . ($this->editingPermission ? 'updated' : 'created') . ' successfully.');
    }

    public function deletePermission($id)
    {
        $this->deletingPermission = Permission::findOrFail($id);
        $this->showDeletePermission = true;
    }

    public function confirmDeletePermission()
    {
        if ($this->deletingPermission) {
            $this->deletingPermission->delete();
            $this->showDeletePermission = false;
            $this->deletingPermission = null;
            session()->flash('message', 'Permission deleted successfully.');
        }
    }

    // Enhanced assignment methods
    public function assignToRoles()
    {
        $this->validate([
            'selectedPermission' => 'required|exists:permissions,id',
            'selectedRoles' => 'required|array|min:1',
            'selectedDepartments' => 'nullable|array'
        ]);

        $permission = Permission::findOrFail($this->selectedPermission);

        foreach ($this->selectedRoles as $roleId) {
            $role = Role::findOrFail($roleId);
            
            foreach ($this->selectedDepartments as $departmentId) {
                $permission->roles()->syncWithoutDetaching([
                    $role->id => [
                        'department_id' => $departmentId
                    ]
                ]);
            }
        }

        session()->flash('message', 'Permission assigned to roles successfully.');
        $this->reset(['selectedPermission', 'selectedRoles', 'selectedDepartments']);
    }

    public function assignToSubRoles()
    {
        $this->validate([
            'selectedPermission' => 'required|exists:permissions,id',
            'selectedSubRoles' => 'required|array|min:1',
            'selectedDepartments' => 'nullable|array'
        ]);

        $permission = Permission::findOrFail($this->selectedPermission);

        foreach ($this->selectedSubRoles as $subRoleId) {
            $subRole = SubRole::findOrFail($subRoleId);
            
            foreach ($this->selectedDepartments as $departmentId) {
                $permission->subRoles()->syncWithoutDetaching([
                    $subRole->id => [
                        'department_id' => $departmentId
                    ]
                ]);
            }
        }

        session()->flash('message', 'Permission assigned to sub-roles successfully.');
        $this->reset(['selectedPermission', 'selectedSubRoles', 'selectedDepartments']);
    }

    public function assignToUsers()
    {
        $this->validate([
            'selectedPermissionUser' => 'required|exists:permissions,id',
            'selectedUsers' => 'required|array|min:1',
            'selectedDepartments' => 'nullable|array'
        ]);

        $permission = Permission::findOrFail($this->selectedPermissionUser);

        foreach ($this->selectedUsers as $userId) {
            $user = User::findOrFail($userId);
            
            foreach ($this->selectedDepartments as $departmentId) {
                $permission->users()->syncWithoutDetaching([
                    $user->id => [
                        'department_id' => $departmentId,
                        'is_granted' => true
                    ]
                ]);
            }
        }

        session()->flash('message', 'Permission assigned to users successfully.');
        $this->reset(['selectedPermissionUser', 'selectedUsers', 'selectedDepartments']);
    }

    // Bulk assignment methods
    public function openBulkAssignment()
    {
        $this->showBulkAssignment = true;
        $this->bulkPermissions = Permission::pluck('id')->toArray();
    }

    public function assignBulkPermissions()
    {
        $this->validate([
            'bulkPermissions' => 'required|array|min:1',
            'bulkAssignmentType' => 'required|in:roles,sub_roles,users'
        ]);

        $permissions = Permission::whereIn('id', $this->bulkPermissions)->get();

        switch ($this->bulkAssignmentType) {
            case 'roles':
                $this->assignBulkToRoles($permissions);
                break;
            case 'sub_roles':
                $this->assignBulkToSubRoles($permissions);
                break;
            case 'users':
                $this->assignBulkToUsers($permissions);
                break;
        }

        $this->showBulkAssignment = false;
        $this->reset(['bulkPermissions', 'bulkRoles', 'bulkSubRoles', 'bulkDepartments', 'bulkConditions']);
        session()->flash('message', 'Bulk permission assignment completed successfully.');
    }

    private function assignBulkToRoles($permissions)
    {
        foreach ($permissions as $permission) {
            foreach ($this->bulkRoles as $roleId) {
                $role = Role::findOrFail($roleId);
                
                foreach ($this->bulkDepartments as $departmentId) {
                    $permission->roles()->syncWithoutDetaching([
                        $role->id => [
                            'department_id' => $departmentId,
                            'conditions' => json_encode($this->bulkConditions)
                        ]
                    ]);
                }

                // Handle inheritance
                if ($this->bulkInheritFromParent && $role->children()->exists()) {
                    $this->assignToChildRoles($permission, $role, $this->bulkDepartments);
                }
            }
        }
    }

    private function assignBulkToSubRoles($permissions)
    {
        foreach ($permissions as $permission) {
            foreach ($this->bulkSubRoles as $subRoleId) {
                $subRole = SubRole::findOrFail($subRoleId);
                
                foreach ($this->bulkDepartments as $departmentId) {
                    $permission->subRoles()->syncWithoutDetaching([
                        $subRole->id => [
                            'department_id' => $departmentId,
                            'conditions' => json_encode($this->bulkConditions)
                        ]
                    ]);
                }
            }
        }
    }

    private function assignBulkToUsers($permissions)
    {
        foreach ($permissions as $permission) {
            foreach ($this->selectedUsers as $userId) {
                $user = User::findOrFail($userId);
                
                foreach ($this->bulkDepartments as $departmentId) {
                    $permission->users()->syncWithoutDetaching([
                        $user->id => [
                            'department_id' => $departmentId,
                            'is_granted' => true,
                            'conditions' => json_encode($this->bulkConditions)
                        ]
                    ]);
                }
            }
        }
    }

    private function assignToChildRoles($permission, $parentRole, $departments)
    {
        $children = $parentRole->children;
        foreach ($children as $child) {
            foreach ($departments as $departmentId) {
                $permission->roles()->syncWithoutDetaching([
                    $child->id => [
                        'department_id' => $departmentId,
                        'conditions' => json_encode($this->bulkConditions),
                        'is_inherited' => true
                    ]
                ]);
            }
            
            // Recursively assign to grandchildren
            if ($child->children()->exists()) {
                $this->assignToChildRoles($permission, $child, $departments);
            }
        }
    }

    // Template-based assignment
    public function applyTemplate()
    {
        if (!$this->selectedTemplate) {
            return;
        }

        $templates = $this->getPermissionTemplates();
        if (isset($templates[$this->selectedTemplate])) {
            $template = $templates[$this->selectedTemplate];
            $this->bulkPermissions = $template['permissions'];
            $this->bulkRoles = $template['roles'] ?? [];
            $this->bulkSubRoles = $template['sub_roles'] ?? [];
            $this->bulkDepartments = $template['departments'] ?? [];
            $this->bulkConditions = $template['conditions'] ?? [];
            $this->bulkAssignmentType = $template['assignment_type'] ?? 'roles';
        }
    }

    private function getPermissionTemplates()
    {
        return [
            'finance_manager' => [
                'name' => 'Finance Manager Template',
                'permissions' => Permission::where('module', 'finance')->pluck('id')->toArray(),
                'roles' => Role::where('name', 'like', '%finance%')->pluck('id')->toArray(),
                'assignment_type' => 'roles',
                'conditions' => ['max_amount' => 100000]
            ],
            'credit_officer' => [
                'name' => 'Credit Officer Template',
                'permissions' => Permission::where('module', 'credit')->pluck('id')->toArray(),
                'roles' => Role::where('name', 'like', '%credit%')->pluck('id')->toArray(),
                'assignment_type' => 'roles',
                'conditions' => ['max_loan_amount' => 50000]
            ],
            'hr_manager' => [
                'name' => 'HR Manager Template',
                'permissions' => Permission::where('module', 'hr')->pluck('id')->toArray(),
                'roles' => Role::where('name', 'like', '%hr%')->pluck('id')->toArray(),
                'assignment_type' => 'roles'
            ],
            'board_member' => [
                'name' => 'Board Member Template',
                'permissions' => Permission::where('module', 'governance')->pluck('id')->toArray(),
                'roles' => Role::where('name', 'like', '%board%')->pluck('id')->toArray(),
                'assignment_type' => 'roles'
            ]
        ];
    }

    // Utility methods
    public function selectAllPermissions()
    {
        $this->bulkPermissions = Permission::pluck('id')->toArray();
    }

    public function selectAllRoles()
    {
        $this->bulkRoles = Role::pluck('id')->toArray();
    }

    public function selectAllSubRoles()
    {
        $this->bulkSubRoles = SubRole::pluck('id')->toArray();
    }

    public function selectAllDepartments()
    {
        $this->bulkDepartments = Department::pluck('id')->toArray();
    }

    public function clearBulkSelection()
    {
        $this->reset(['bulkPermissions', 'bulkRoles', 'bulkSubRoles', 'bulkDepartments', 'bulkConditions']);
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

    public function clearFilters()
    {
        $this->reset(['search', 'moduleFilter', 'actionFilter']);
    }

    public function assignPermissionToRoles()
    {
        $this->assignToRoles();
    }

    public function assignPermissionToSubRoles()
    {
        $this->assignToSubRoles();
    }

    public function assignPermissionToUsers()
    {
        $this->assignToUsers();
    }

    public function render()
    {
        $query = Permission::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->moduleFilter, function ($query) {
                $query->where('module', $this->moduleFilter);
            })
            ->when($this->actionFilter, function ($query) {
                $query->where('action', $this->actionFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.users.manage-permissions', [
            'permissions' => $query->paginate(10),
            'roles' => Role::with('department')->get(),
            'subRoles' => SubRole::with('role')->get(),
            'departments' => Department::all(),
            'users' => User::with('roles')->get(),
            'modules' => Permission::distinct()->pluck('module'),
            'actions' => Permission::distinct()->pluck('action'),
            'templates' => $this->getPermissionTemplates(),
            'roleHierarchy' => Role::with('children', 'department')->whereNull('parent_role_id')->get()
        ]);
    }
}
