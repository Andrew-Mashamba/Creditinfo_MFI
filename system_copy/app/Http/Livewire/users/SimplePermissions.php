<?php

namespace App\Http\Livewire\Users;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimplePermissions extends Component
{
    use WithPagination;

    // Basic properties
    public $selectedRole = '';
    public $search = '';
    
    // Permission checkboxes
    public $permissions = [];
    
    public function mount()
    {
        // Load first role by default
        $firstRole = Role::first();
        if ($firstRole) {
            $this->selectedRole = $firstRole->id;
            $this->loadPermissions();
        }
    }

    public function render()
    {
        $roles = Role::with('department')->get();
        
        // Get all permissions grouped by module
        $allPermissions = Permission::orderBy('name')->get();
        $groupedPermissions = $allPermissions->groupBy(function($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'other';
        });
        
        return view('livewire.users.simple-permissions', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions
        ]);
    }

    public function updatedSelectedRole()
    {
        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        if (!$this->selectedRole) return;
        
        // Load existing permissions for this role
        $role = Role::find($this->selectedRole);
        if (!$role) return;
        
        $this->permissions = [];
        foreach ($role->permissions as $permission) {
            $this->permissions[$permission->id] = true;
        }
    }

    public function togglePermission($permissionId)
    {
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
        }
    }

    public function savePermissions()
    {
        if (!$this->selectedRole) {
            session()->flash('error', 'Please select a role');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Get the role
            $role = Role::find($this->selectedRole);
            if (!$role) {
                session()->flash('error', 'Role not found');
                return;
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
            
            // Also update all users with this role
            $users = $role->users;
            foreach ($users as $user) {
                // Get permissions from all user's roles
                $allPermissions = collect();
                foreach ($user->roles as $userRole) {
                    $allPermissions = $allPermissions->merge($userRole->permissions);
                }
                
                // Add permissions from user's sub-roles
                foreach ($user->subRoles as $subRole) {
                    $allPermissions = $allPermissions->merge($subRole->permissions);
                }
                
                // Sync unique permissions to user
                $user->permissions()->sync($allPermissions->pluck('id')->unique());
            }
            
            DB::commit();
            session()->flash('message', 'Permissions saved successfully for role and all users with this role');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving permissions: ' . $e->getMessage());
            session()->flash('error', 'Error saving permissions: ' . $e->getMessage());
        }
    }

    public function quickAssign($template)
    {
        if (!$this->selectedRole) {
            session()->flash('error', 'Please select a role first');
            return;
        }
        
        $allPermissions = Permission::all();
        $this->permissions = [];
        
        switch ($template) {
            case 'admin':
                // Admin gets all permissions
                foreach ($allPermissions as $permission) {
                    $this->permissions[$permission->id] = true;
                }
                session()->flash('message', "Applied 'Admin' template - All permissions selected");
                break;
                
            case 'manager':
                // Manager gets view, create, edit permissions
                foreach ($allPermissions as $permission) {
                    if (strpos($permission->name, '.view') !== false ||
                        strpos($permission->name, '.create') !== false ||
                        strpos($permission->name, '.edit') !== false ||
                        strpos($permission->name, '.update') !== false ||
                        strpos($permission->name, '.list') !== false ||
                        strpos($permission->name, '.read') !== false) {
                        $this->permissions[$permission->id] = true;
                    }
                }
                session()->flash('message', "Applied 'Manager' template - View, Create, Edit permissions selected");
                break;
                
            case 'user':
                // User gets only view permissions
                foreach ($allPermissions as $permission) {
                    if (strpos($permission->name, '.view') !== false ||
                        strpos($permission->name, '.list') !== false ||
                        strpos($permission->name, '.read') !== false) {
                        $this->permissions[$permission->id] = true;
                    }
                }
                session()->flash('message', "Applied 'User' template - View permissions only");
                break;
                
            case 'none':
                // Clear all permissions
                $this->permissions = [];
                session()->flash('message', "All permissions cleared");
                break;
        }
    }
}