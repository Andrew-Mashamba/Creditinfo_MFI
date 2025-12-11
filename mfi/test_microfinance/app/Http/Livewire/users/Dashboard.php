<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Permission;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Traits\Livewire\WithModulePermissions;

class Dashboard extends Component
{
    use WithModulePermissions;
    public $section = null;

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        
        // Set a default section to 'users' for better UX
        $this->section = 'users';
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'users';
    }

    public function setSection($section)
    {
        if (!$this->authorize('view', 'You do not have permission to view this section')) {
            return;
        }
        
        //dd($section);
        $this->section = $section;
        Log::info('Section changed to: ' . $section);
    }

    public function render()
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalDepartments = Department::count();
        $totalPermissions = Permission::count();

        // Debug: Log the current section
        Log::info('Dashboard section: ' . $this->section);

        return view('livewire.users.dashboard', array_merge(
            $this->permissions,
            [
                'totalUsers' => $totalUsers,
                'totalRoles' => $totalRoles,
                'totalDepartments' => $totalDepartments,
                'totalPermissions' => $totalPermissions,
                'permissions' => $this->permissions
            ]
        ));
    }
} 