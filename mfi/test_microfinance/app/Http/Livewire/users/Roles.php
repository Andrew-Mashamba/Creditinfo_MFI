<?php

namespace App\Http\Livewire\Users;

use App\Models\approvals;
use Livewire\Component;
use App\Models\sub_menus;
use App\Models\UserSubMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Actions\UpdateTeamClientRole;
use Laravel\Jetstream\Contracts\AddsTeamClients;
use Laravel\Jetstream\Contracts\InvitesTeamClients;
use Laravel\Jetstream\Contracts\RemovesTeamClients;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;
use App\Models\Role;
use App\Models\AccountsModel;
use Laravel\Jetstream\Team;
use App\Models\User;
use App\Models\TeamUser;
use App\Models\departmentsList;
use App\Models\Department;
use App\Models\SubRole;
use App\Models\MenuAction;
use App\Models\RoleMenuAction;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class Roles extends Component
{
    use WithPagination;

    public $team;
    public $accounts;
    public $user;
    public $pendingUsers;
    public $department;
    public $departmentList;
    public $pendinguser;
    public $userrole;
    public $menusArray;
    public $sub_menus;
    public $setSubMenuPermission;

    // Enhanced properties
    public $departments;
    public $roles;
    public $menuActions;
    public $showCreateRole = false;
    public $showCreateSubRole = false;
    public $showDeleteModal = false;
    public $roleName;
    public $roleDepartment;
    public $roleDescription;
    public $subRoleName;
    public $subRoleParent;
    public $subRoleDescription;
    public $selectedPermissions = [];

    public $editingRole;
    public $editingSubRole;
    public $deletingType;
    public $deletingRole;

    // Enhanced filtering and search
    public $typeFilter = '';
    public $search = '';
    public $departmentFilter = '';
    public $statusFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Permissions modal properties
    public $showPermissionsModal = false;
    public $selectedRoleId = null;
    public $selectedRoleType = null; // 'role' or 'subrole'
    public $rolePermissions = [];
    public $allMenus = [];

    // Approval permission properties
    public $canApprove = false;
    public $canFirstCheck = false;
    public $canSecondCheck = false;
    public $canFinalApprove = false;

    // Enhanced properties for better UX
    public $showBulkActions = false;
    public $selectedRoles = [];
    public $bulkAction = '';
    public $showStatistics = true;
    public $viewMode = 'grid'; // 'grid' or 'table'
    public $perPage = 10;

    // Toast notifications
    public $showToast = false;
    public $toastMessage = '';
    public $toastType = 'success';

    protected $rules = [
        'pendinguser' => 'required|min:1',
        'department' => 'required|min:1',
        'roleName' => 'required|min:3|max:255',
        'roleDepartment' => 'required|exists:departments,id',
        'roleDescription' => 'nullable|max:1000',
        'subRoleName' => 'required|min:3|max:255',
        'subRoleParent' => 'required|exists:roles,id',
        'subRoleDescription' => 'nullable|max:1000',
        'selectedPermissions' => 'required|array|min:1',
        'selectedPermissions.*' => 'exists:menu_actions,id'
    ];

    public $menuItems;
    public $department_name;

    public function boot(){
        $permissions = [];
    }

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->departments = Department::with(['roles', 'roles.subRoles.role'])->get();
        $this->roles = Role::with(['department', 'users'])->get();
        $this->menuActions = MenuAction::all();
        $this->allMenus = Menu::orderBy('menu_number')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
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

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'table' : 'grid';
    }

    public function toggleBulkActions()
    {
        $this->showBulkActions = !$this->showBulkActions;
        if (!$this->showBulkActions) {
            $this->selectedRoles = [];
        }
    }

    public function selectAllRoles()
    {
        $filteredRoles = $this->getFilteredRoles();
        $this->selectedRoles = $filteredRoles->pluck('id')->toArray();
    }

    public function deselectAllRoles()
    {
        $this->selectedRoles = [];
    }

    public function performBulkAction()
    {
        if (empty($this->selectedRoles) || empty($this->bulkAction)) {
            $this->showToastMessage('Please select roles and an action', 'warning');
            return;
        }

        try {
            DB::beginTransaction();

            switch ($this->bulkAction) {
                case 'delete':
                    foreach ($this->selectedRoles as $roleId) {
                        $this->deleteRole($roleId);
                    }
                    $this->showToastMessage('Selected roles deleted successfully', 'success');
                    break;
                case 'activate':
                    Role::whereIn('id', $this->selectedRoles)->update(['status' => 'ACTIVE']);
                    $this->showToastMessage('Selected roles activated successfully', 'success');
                    break;
                case 'deactivate':
                    Role::whereIn('id', $this->selectedRoles)->update(['status' => 'INACTIVE']);
                    $this->showToastMessage('Selected roles deactivated successfully', 'success');
                    break;
            }

            DB::commit();
            $this->loadData();
            $this->selectedRoles = [];
            $this->bulkAction = '';
            $this->showBulkActions = false;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk action failed', ['error' => $e->getMessage()]);
            $this->showToastMessage('Bulk action failed. Please try again.', 'error');
        }
    }

    public function getFilteredRoles()
    {
        $query = Role::with(['department', 'users', 'subRoles']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        if ($this->typeFilter) {
            if ($this->typeFilter === 'role') {
                $query->whereNull('parent_role_id');
            } else {
                $query->whereNotNull('parent_role_id');
            }
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy($this->sortField, $this->sortDirection)->get();
    }

    public function getStatistics()
    {
        $totalRoles = Role::count();
        $activeRoles = Role::count();
        $inactiveRoles = Role::count();
        $systemRoles = Role::where('is_system_role', true)->count();
        $departmentRoles = Role::where('is_system_role', false)->count();
        $rolesWithUsers = Role::has('users')->count();

        return [
            'total' => $totalRoles,
            'active' => $activeRoles,
            'inactive' => $inactiveRoles,
            'system' => $systemRoles,
            'department' => $departmentRoles,
            'with_users' => $rolesWithUsers
        ];
    }

    public function createRole()
    {
        $this->validate([
            'roleName' => 'required|min:3|max:255',
            'roleDepartment' => 'required|exists:departments,id',
            'roleDescription' => 'nullable|max:1000'
        ]);

        try {
            DB::beginTransaction();
            Log::info('Creating new role', [
                'name' => $this->roleName,
                'department_id' => $this->roleDepartment,
                'description' => $this->roleDescription
            ]);

            $role = Role::create([
                'name' => $this->roleName,
                'department_id' => $this->roleDepartment,
                'description' => $this->roleDescription,
                'status' => 'ACTIVE',
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);

            DB::commit();
            Log::info('Role created successfully', ['role_id' => $role->id]);

            $this->reset(['roleName', 'roleDepartment', 'roleDescription', 'showCreateRole']);
            $this->loadData();
            $this->showToastMessage('Role created successfully.', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error creating role. Please try again.', 'error');
        }
    }

    public function createSubRole()
    {
        $this->validate([
            'subRoleName' => 'required|min:3|max:255',
            'subRoleParent' => 'required|exists:roles,id',
            'subRoleDescription' => 'nullable|max:1000',
            'selectedPermissions' => 'required|array|min:1',
            'selectedPermissions.*' => 'exists:menu_actions,id'
        ]);

        try {
            DB::beginTransaction();
            Log::info('Creating new sub-role', [
                'name' => $this->subRoleName,
                'parent_role_id' => $this->subRoleParent,
                'description' => $this->subRoleDescription,
                'permissions' => $this->selectedPermissions
            ]);

            $subRole = SubRole::create([
                'role_id' => $this->subRoleParent,
                'name' => $this->subRoleName,
                'description' => $this->subRoleDescription
            ]);

            // Save permissions
            foreach ($this->selectedPermissions as $permission) {
                $parts = explode('_', $permission, 2);
                if (count($parts) === 2) {
                    $menuId = $parts[0];
                    $action = $parts[1];
                    
                    RoleMenuAction::create([
                        'sub_role' => $subRole->name,
                        'menu_id' => $menuId,
                        'allowed_actions' => json_encode([$action])
                    ]);
                }
            }

            DB::commit();
            Log::info('Sub-role created successfully', ['sub_role_id' => $subRole->id]);

            $this->reset(['subRoleName', 'subRoleParent', 'subRoleDescription', 'selectedPermissions', 'showCreateSubRole']);
            $this->loadData();
            $this->showToastMessage('Sub-role created successfully.', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sub-role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error creating sub-role. Please try again.', 'error');
        }
    }

    public function saveRole()
    {
        $this->validate([
            'roleName' => 'required|min:3|max:255',
            'roleDepartment' => 'required|exists:departments,id',
            'roleDescription' => 'nullable|max:1000'
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingRole) {
                // Update existing role
                $role = Role::findOrFail($this->editingRole);
                $role->update([
                    'name' => $this->roleName,
                    'department_id' => $this->roleDepartment,
                    'description' => $this->roleDescription,
                    'institution_id' => Auth::user()->institution_id,
                ]);
                
                Log::info('Role updated successfully', ['role_id' => $role->id]);
                $message = 'Role updated successfully.';
            } else {
                // Create new role
                $role = Role::create([
                    'name' => $this->roleName,
                    'department_id' => $this->roleDepartment,
                    'description' => $this->roleDescription,
                    'institution_id' => Auth::user()->institution_id,
                    'status' => 'ACTIVE',
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]);
                
                Log::info('Role created successfully', ['role_id' => $role->id]);
                $message = 'Role created successfully.';
            }

            DB::commit();

            $this->reset(['roleName', 'roleDepartment', 'roleDescription', 'showCreateRole', 'editingRole']);
            $this->loadData();
            
            $this->showToastMessage($message, 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error saving role. Please try again.', 'error');
        }
    }

    public function saveSubRole()
    {
        $this->validate([
            'subRoleName' => 'required|min:3|max:255',
            'subRoleParent' => 'required|exists:roles,id',
            'subRoleDescription' => 'nullable|max:1000',
            'selectedPermissions' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingSubRole) {
                // Update existing sub-role
                $subRole = SubRole::findOrFail($this->editingSubRole);
                $subRole->update([
                    'role_id' => $this->subRoleParent,
                    'name' => $this->subRoleName,
                    'description' => $this->subRoleDescription
                ]);

                // Clear existing permissions
                RoleMenuAction::where('sub_role', $subRole->name)->delete();

                // Add new permissions
                foreach ($this->selectedPermissions as $permission) {
                    $parts = explode('_', $permission, 2);
                    if (count($parts) === 2) {
                        $menuId = $parts[0];
                        $action = $parts[1];
                        
                        RoleMenuAction::create([
                            'sub_role' => $subRole->name,
                            'menu_id' => $menuId,
                            'allowed_actions' => json_encode([$action])
                        ]);
                    }
                }

                Log::info('Sub-role updated successfully', ['sub_role_id' => $subRole->id]);
                $message = 'Sub-role updated successfully.';
            } else {
                // Create new sub-role
                $subRole = SubRole::create([
                    'role_id' => $this->subRoleParent,
                    'name' => $this->subRoleName,
                    'description' => $this->subRoleDescription
                ]);

                // Add permissions
                foreach ($this->selectedPermissions as $permission) {
                    $parts = explode('_', $permission, 2);
                    if (count($parts) === 2) {
                        $menuId = $parts[0];
                        $action = $parts[1];
                        
                        RoleMenuAction::create([
                            'sub_role' => $subRole->name,
                            'menu_id' => $menuId,
                            'allowed_actions' => json_encode([$action])
                        ]);
                    }
                }

                Log::info('Sub-role created successfully', ['sub_role_id' => $subRole->id]);
                $message = 'Sub-role created successfully.';
            }

            DB::commit();

            $this->reset(['subRoleName', 'subRoleParent', 'subRoleDescription', 'selectedPermissions', 'showCreateSubRole', 'editingSubRole']);
            $this->loadData();
            
            $this->showToastMessage($message, 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving sub-role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error saving sub-role. Please try again.', 'error');
        }
    }

    public function editRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        $this->editingRole = $roleId;
        $this->roleName = $role->name;
        $this->roleDepartment = $role->department_id;
        $this->roleDescription = $role->description;
        $this->showCreateRole = true;
    }

    public function editSubRole($subRoleId)
    {
        $subRole = SubRole::with('roleMenuActions')->findOrFail($subRoleId);
        $this->editingSubRole = $subRoleId;
        $this->subRoleName = $subRole->name;
        $this->subRoleParent = $subRole->role_id;
        $this->subRoleDescription = $subRole->description;
        $this->selectedPermissions = $subRole->roleMenuActions->pluck('menu_action_id')->toArray();
        $this->showCreateSubRole = true;
    }

    public function deleteRole($roleId)
    {
        try {
            DB::beginTransaction();
            Log::info('Deleting role', ['role_id' => $roleId]);

            $role = Role::findOrFail($roleId);

            // Delete associated sub-roles and their permissions
            foreach ($role->subRoles as $subRole) {
                Log::info('Deleting sub-role and permissions', [
                    'sub_role_id' => $subRole->id,
                    'role_id' => $roleId
                ]);
                RoleMenuAction::where('sub_role', $subRole->name)->delete();
                $subRole->delete();
            }

            // Delete role menu actions
            RoleMenuAction::where('role_id', $role->id)->delete();

            $role->delete();
            DB::commit();
            Log::info('Role deleted successfully', ['role_id' => $roleId]);

            $this->loadData();
            $this->showToastMessage('Role deleted successfully.', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error deleting role. Please try again.', 'error');
        }
    }

    public function deleteSubRole($subRoleId)
    {
        try {
            DB::beginTransaction();
            Log::info('Deleting sub-role', ['sub_role_id' => $subRoleId]);

            $subRole = SubRole::findOrFail($subRoleId);

            // Delete associated permissions
            RoleMenuAction::where('sub_role', $subRole->name)->delete();

            $subRole->delete();
            DB::commit();
            Log::info('Sub-role deleted successfully', ['sub_role_id' => $subRoleId]);

            $this->loadData();
            $this->showToastMessage('Sub-role deleted successfully.', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sub-role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error deleting sub-role. Please try again.', 'error');
        }
    }

    public function confirmDelete()
    {
        if ($this->deletingType === 'role') {
            $this->deleteRole($this->deletingRole);
        } else {
            $this->deleteSubRole($this->deletingRole);
        }
        $this->showDeleteModal = false;
        $this->deletingRole = null;
        $this->deletingType = null;
    }

    public function setPermission($value): void
    {
        $string = $value;
        $array = explode("-", $string);
        $permission = $array[0]; // "1"
        $menu_id = $array[2]; // "1"
        $sub_menu_id = $array[1];

        $this->validate();

        try {
            DB::beginTransaction();

            // Check if permission already exists
            $existingPermission = UserSubMenu::where([
                'user_id' => $this->pendinguser,
                'menu_id' => $menu_id,
                'sub_menu_id' => $sub_menu_id
            ])->first();

            if ($existingPermission) {
                // Update existing permission
                $existingPermission->update([
                    'user_action' => $permission
                ]);
            } else {
                // Create new permission
                UserSubMenu::create([
                    'user_id' => $this->pendinguser,
                    'menu_id' => $menu_id,
                    'sub_menu_id' => $sub_menu_id,
                    'user_action' => $permission
                ]);
            }

            DB::commit();
            $this->showToastMessage('Permission updated successfully.', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting permission', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error setting permission. Please try again.', 'error');
        }
    }

    public function showPermissionsModal($roleId, $type)
    {
        $this->selectedRoleId = $roleId;
        $this->selectedRoleType = $type;
        $this->selectedPermissions = [];

        // Load existing permissions
        if ($type === 'role') {
            $role = Role::find($roleId);
            if ($role) {
                $existingPermissions = RoleMenuAction::where('role_id', $role->id)->get();
                foreach ($existingPermissions as $permission) {
                    $actions = json_decode($permission->allowed_actions, true) ?? [];
                    foreach ($actions as $action) {
                        $this->selectedPermissions[] = $permission->menu_id . '_' . $action;
                    }
                }
            }
        } else {
            $subRole = SubRole::find($roleId);
            if ($subRole) {
                $existingPermissions = RoleMenuAction::where('sub_role', $subRole->name)->get();
                foreach ($existingPermissions as $permission) {
                    $actions = json_decode($permission->allowed_actions, true) ?? [];
                    foreach ($actions as $action) {
                        $this->selectedPermissions[] = $permission->menu_id . '_' . $action;
                    }
                }
            }
        }

        $this->showPermissionsModal = true;
    }

    public function savePermissions()
    {
        try {
            DB::beginTransaction();
            
            // Group permissions by menu_id
            $menuPermissions = [];
            foreach ($this->selectedPermissions as $permission) {
                $parts = explode('_', $permission, 2);
                if (count($parts) === 2) {
                    $menuId = $parts[0];
                    $action = $parts[1];
                    if (!isset($menuPermissions[$menuId])) {
                        $menuPermissions[$menuId] = [];
                    }
                    $menuPermissions[$menuId][] = $action;
                }
            }
            
            if ($this->selectedRoleType === 'subrole') {
                $subRole = SubRole::find($this->selectedRoleId);
                if ($subRole) {
                    // Clear existing permissions for this sub-role
                    RoleMenuAction::where('sub_role', $subRole->name)->delete();
                    
                    // Add new permissions grouped by menu
                    foreach ($menuPermissions as $menuId => $actions) {
                        RoleMenuAction::create([
                            'sub_role' => $subRole->name,
                            'menu_id' => $menuId,
                            'allowed_actions' => json_encode($actions)
                        ]);
                    }
                }
            } else {
                $role = Role::find($this->selectedRoleId);
                if ($role) {
                    // Clear existing permissions for this role
                    RoleMenuAction::where('role_id', $role->id)->delete();
                    
                    // Add new permissions grouped by menu
                    foreach ($menuPermissions as $menuId => $actions) {
                        RoleMenuAction::create([
                            'role_id' => $role->id,
                            'menu_id' => $menuId,
                            'allowed_actions' => json_encode($actions)
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            $this->showPermissionsModal = false;
            $this->selectedRoleId = null;
            $this->selectedRoleType = null;
            $this->selectedPermissions = [];
            
            $this->showToastMessage('Permissions updated successfully.', 'success');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating permissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showToastMessage('Error updating permissions. Please try again.', 'error');
        }
    }

    public function closePermissionsModal()
    {
        $this->showPermissionsModal = false;
        $this->selectedRoleId = null;
        $this->selectedRoleType = null;
        $this->selectedPermissions = [];
    }

    public function showToastMessage($message, $type = 'success')
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
        $this->showToast = true;
    }

    public function toggleStatistics()
    {
        $this->showStatistics = !$this->showStatistics;
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->pendingUsers = User::get();
        $this->departmentList = departmentsList::get();

        $filteredRoles = $this->getFilteredRoles();
        $statistics = $this->getStatistics();

        return view('livewire.users.roles', [
            'filteredRoles' => $filteredRoles,
            'statistics' => $statistics
        ]);
    }
}
