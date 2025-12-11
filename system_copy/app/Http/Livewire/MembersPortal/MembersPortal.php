<?php

namespace App\Http\Livewire\MembersPortal;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\Livewire\WithModulePermissions;

class MembersPortal extends Component
{
    use WithModulePermissions;
    public $activeTab = 'user-management';
    public $loading = false;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        
        // Initialize component
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'members_portal';
    }

    public function setActiveTab($tab)
    {
        if (!$this->authorize('view', 'You do not have permission to view this section')) {
            return;
        }
        
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.members-portal.members-portal', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }
}
