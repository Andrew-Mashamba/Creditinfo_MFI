<?php

namespace App\Http\Livewire\Users;

use App\Models\Role;
use App\Models\SubRole;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\departmentsList;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HierarchicalPermissions extends Component
{
    // Selection state
    public $selectedDepartment = '';
    public $selectedRole = '';
    public $selectedSubRole = '';
    public $selectedEntity = ''; // role:id or subrole:id
    
    // Permission state
    public $permissions = [];
    public $inheritedPermissions = [];
    public $overriddenPermissions = [];
    
    // View state
    public $viewMode = 'simple'; // simple, advanced, matrix
    public $showInherited = true;
    public $showPresets = false;
    
    // Statistics
    public $stats = [
        'total_permissions' => 0,
        'inherited' => 0,
        'overridden' => 0,
        'custom' => 0
    ];
    
    // Permission presets for quick assignment
    public $presets = [
        'manager' => [
            'name' => 'Manager Full Access',
            'permissions' => ['view', 'create', 'edit', 'delete', 'approve', 'export', 'manage', 'update', 'list', 'read']
        ],
        'supervisor' => [
            'name' => 'Supervisor',
            'permissions' => ['view', 'create', 'edit', 'approve', 'list', 'read', 'update']
        ],
        'officer' => [
            'name' => 'Officer',
            'permissions' => ['view', 'create', 'edit', 'list', 'read']
        ],
        'junior' => [
            'name' => 'Junior Staff',
            'permissions' => ['view', 'create', 'list', 'read']
        ],
        'viewer' => [
            'name' => 'View Only',
            'permissions' => ['view', 'list', 'read']
        ]
    ];

    public function mount()
    {
        // Auto-select first department if available
        $firstDept = departmentsList::where('status', true)->first();
        if ($firstDept) {
            $this->selectedDepartment = $firstDept->id;
            $this->loadRoles();
        }
    }

    public function render()
    {
        $departments = departmentsList::where('status', true)
            ->withCount('roles')
            ->get();
            
        $roles = [];
        if ($this->selectedDepartment) {
            $roles = Role::where('department_id', $this->selectedDepartment)
                ->where('status', 'ACTIVE')
                ->with('subRoles')
                ->get();
        }
        
        $subRoles = [];
        if ($this->selectedRole) {
            $subRoles = SubRole::where('role_id', $this->selectedRole)->get();
        }
        
        // Get all permissions grouped by module
        $allPermissions = Permission::orderBy('name')->get();
        $groupedPermissions = $allPermissions->groupBy(function($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'other';
        });
        
        return view('livewire.users.hierarchical-permissions', [
            'departments' => $departments,
            'roles' => $roles,
            'subRoles' => $subRoles,
            'groupedPermissions' => $groupedPermissions,
            'allPermissions' => $allPermissions
        ]);
    }

    public function updatedSelectedDepartment()
    {
        $this->reset(['selectedRole', 'selectedSubRole', 'selectedEntity', 'permissions']);
        $this->loadRoles();
    }

    public function updatedSelectedRole()
    {
        $this->reset(['selectedSubRole']);
        
        if ($this->selectedRole) {
            $this->selectedEntity = 'role:' . $this->selectedRole;
            $this->loadPermissions();
        }
    }

    public function updatedSelectedSubRole()
    {
        if ($this->selectedSubRole) {
            $this->selectedEntity = 'subrole:' . $this->selectedSubRole;
            $this->loadPermissions();
        } else if ($this->selectedRole) {
            // If sub-role is deselected, go back to role permissions
            $this->selectedEntity = 'role:' . $this->selectedRole;
            $this->loadPermissions();
        }
    }

    public function loadRoles()
    {
        // Roles are loaded in render method
    }

    public function loadPermissions()
    {
        if (!$this->selectedEntity) return;
        
        [$type, $id] = explode(':', $this->selectedEntity);
        
        $this->permissions = [];
        $this->inheritedPermissions = [];
        $this->overriddenPermissions = [];
        
        if ($type === 'role') {
            // Load role permissions
            $this->loadRolePermissions($id);
        } else {
            // Load sub-role permissions with inheritance
            $this->loadSubRolePermissions($id);
        }
        
        $this->calculateStatistics();
    }

    private function loadRolePermissions($roleId)
    {
        $role = Role::find($roleId);
        if (!$role) return;
        
        // Load permissions from the role
        foreach ($role->permissions as $permission) {
            $this->permissions[$permission->id] = true;
        }
    }

    private function loadSubRolePermissions($subRoleId)
    {
        $subRole = SubRole::with('role')->find($subRoleId);
        if (!$subRole) return;
        
        // Load parent role permissions as inherited
        $parentRole = $subRole->role;
        if ($parentRole) {
            foreach ($parentRole->permissions as $permission) {
                $this->inheritedPermissions[$permission->id] = true;
                $this->permissions[$permission->id] = true; // Initially inherit all
            }
        }
        
        // Load sub-role specific permissions (overrides)
        foreach ($subRole->permissions as $permission) {
            $this->permissions[$permission->id] = true;
            
            // Mark as overridden if it was inherited
            if (isset($this->inheritedPermissions[$permission->id])) {
                $this->overriddenPermissions[$permission->id] = true;
            }
        }
        
        // Mark permissions that are in parent but not in sub-role as removed overrides
        foreach ($this->inheritedPermissions as $permId => $value) {
            if (!isset($this->permissions[$permId])) {
                $this->overriddenPermissions[$permId] = true;
            }
        }
    }

    public function togglePermission($permissionId)
    {
        // Check if this is a sub-role overriding inherited permission
        if (strpos($this->selectedEntity, 'subrole:') === 0 && isset($this->inheritedPermissions[$permissionId])) {
            $this->overriddenPermissions[$permissionId] = true;
        }
        
        $this->permissions[$permissionId] = !($this->permissions[$permissionId] ?? false);
    }
    
    public function toggleModulePermissions($module, $permissionIds)
    {
        // Check if any permission in this module is unchecked
        $anyUnchecked = false;
        foreach ($permissionIds as $id) {
            if (!($this->permissions[$id] ?? false)) {
                $anyUnchecked = true;
                break;
            }
        }
        
        // If any unchecked, check all; otherwise uncheck all
        foreach ($permissionIds as $id) {
            $this->permissions[$id] = $anyUnchecked;
            
            // Mark as overridden if sub-role
            if (strpos($this->selectedEntity, 'subrole:') === 0 && isset($this->inheritedPermissions[$id])) {
                $this->overriddenPermissions[$id] = true;
            }
        }
    }

    public function savePermissions()
    {
        if (!$this->selectedEntity) {
            session()->flash('error', 'Please select a role or sub-role');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            [$type, $id] = explode(':', $this->selectedEntity);
            
            if ($type === 'role') {
                $this->saveRolePermissions($id);
            } else {
                $this->saveSubRolePermissions($id);
            }
            
            // Update all affected users' permissions
            $this->updateUserPermissions($type, $id);
            
            DB::commit();
            session()->flash('message', 'Permissions saved successfully and applied to all affected users');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving permissions: ' . $e->getMessage());
            session()->flash('error', 'Error saving permissions: ' . $e->getMessage());
        }
    }

    private function saveRolePermissions($roleId)
    {
        $role = Role::find($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }
        
        // Get selected permission IDs
        $selectedPermissionIds = [];
        foreach ($this->permissions as $permissionId => $enabled) {
            if ($enabled) {
                $selectedPermissionIds[] = $permissionId;
            }
        }
        
        // Sync permissions with the role
        $role->permissions()->sync($selectedPermissionIds);
    }

    private function saveSubRolePermissions($subRoleId)
    {
        $subRole = SubRole::find($subRoleId);
        if (!$subRole) {
            throw new \Exception('Sub-role not found');
        }
        
        // Get selected permission IDs
        $selectedPermissionIds = [];
        foreach ($this->permissions as $permissionId => $enabled) {
            if ($enabled) {
                $selectedPermissionIds[] = $permissionId;
            }
        }
        
        // Sync permissions with the sub-role
        $subRole->permissions()->sync($selectedPermissionIds);
    }
    
    private function updateUserPermissions($type, $id)
    {
        if ($type === 'role') {
            // Update all users with this role
            $role = Role::find($id);
            if ($role) {
                foreach ($role->users as $user) {
                    $this->syncUserPermissions($user);
                }
            }
        } else {
            // Update all users with this sub-role
            $subRole = SubRole::find($id);
            if ($subRole) {
                foreach ($subRole->users as $user) {
                    $this->syncUserPermissions($user);
                }
            }
        }
    }
    
    private function syncUserPermissions($user)
    {
        // Collect all permissions from user's roles and sub-roles
        $allPermissions = collect();
        
        foreach ($user->roles as $role) {
            $allPermissions = $allPermissions->merge($role->permissions);
        }
        
        foreach ($user->subRoles as $subRole) {
            $allPermissions = $allPermissions->merge($subRole->permissions);
        }
        
        // Sync unique permissions to user
        $user->permissions()->sync($allPermissions->pluck('id')->unique());
    }

    public function applyPreset($presetKey)
    {
        if (!isset($this->presets[$presetKey])) return;
        
        $preset = $this->presets[$presetKey];
        $allPermissions = Permission::all();
        
        // Clear current permissions
        $this->permissions = [];
        
        // Apply preset based on permission names
        foreach ($allPermissions as $permission) {
            $shouldInclude = false;
            foreach ($preset['permissions'] as $action) {
                if (strpos($permission->name, '.' . $action) !== false) {
                    $shouldInclude = true;
                    break;
                }
            }
            
            if ($shouldInclude) {
                $this->permissions[$permission->id] = true;
            }
        }
        
        session()->flash('message', "Applied '{$preset['name']}' template. Click Save to apply changes.");
    }

    public function clearPermissions()
    {
        $this->permissions = [];
        session()->flash('message', 'All permissions cleared. Click Save to apply changes.');
    }

    public function resetToInherited()
    {
        if (strpos($this->selectedEntity, 'subrole:') !== 0) {
            session()->flash('error', 'This action only applies to sub-roles');
            return;
        }
        
        $this->permissions = $this->inheritedPermissions;
        $this->overriddenPermissions = [];
        session()->flash('message', 'Reset to inherited permissions. Click Save to apply changes.');
    }

    private function calculateStatistics()
    {
        $enabledCount = 0;
        foreach ($this->permissions as $enabled) {
            if ($enabled) $enabledCount++;
        }
        
        $this->stats['total_permissions'] = $enabledCount;
        $this->stats['inherited'] = count($this->inheritedPermissions);
        $this->stats['overridden'] = count($this->overriddenPermissions);
        $this->stats['custom'] = max(0, $enabledCount - count($this->inheritedPermissions));
    }

    public function toggleViewMode()
    {
        $modes = ['simple', 'advanced', 'matrix'];
        $currentIndex = array_search($this->viewMode, $modes);
        $this->viewMode = $modes[($currentIndex + 1) % count($modes)];
    }

    public function exportPermissions()
    {
        // TODO: Implement permission export to CSV/Excel
        session()->flash('message', 'Export functionality coming soon');
    }
}