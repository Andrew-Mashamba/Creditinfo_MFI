<?php

namespace App\Http\Livewire\ProfileSetting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Http\Livewire\ProfileSetting\Config;
use App\Traits\Livewire\WithModulePermissions;

class Profile extends Component
{
    use WithModulePermissions;
    public $teller_tab = 1;
    public $institution_id;

    protected $listeners = ['refreshAccounts' => '$refresh'];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        // Get the institution ID from the database
        $institution = DB::table('institutions')->first();
        $this->institution_id = $institution ? $institution->id : null;
    }

    public function render()
    {
        return view('livewire.profile-setting.profile', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }

    public function menu_sub_button($id)
    {
        // Check permissions based on the section being accessed
        $requiredPermission = $this->getRequiredPermissionForSection($id);
        
        if (!$this->authorize($requiredPermission, 'You do not have permission to access this settings section')) {
            return;
        }
        
        $this->teller_tab = $id;
    }

    /**
     * Get the required permission for a specific settings section
     */
    private function getRequiredPermissionForSection($sectionId)
    {
        $sectionPermissionMap = [
            1 => 'view',      // Organization Settings
            2 => 'view',      // Leadership
            3 => 'view',      // End of Day
            5 => 'view',      // End of Year
            6 => 'view',      // Statistics
            7 => 'view',      // Key Financial Ratios
            8 => 'view',      // Financial Position
            9 => 'view',      // Capital Summary
            10 => 'view',     // Shares Ownership
            11 => 'view',     // Loan Provision Setting
            12 => 'manage',   // Accounts Setup (requires manage permission)
            13 => 'manage',   // Bills Manager (requires manage permission)
            14 => 'manage',   // Institution Accounts (requires manage permission)
            15 => 'manage',   // Approvals Manager (requires manage permission)
            16 => 'manage',   // Data Migration (requires manage permission)
            17 => 'manage',   // Domain Management (requires manage permission)
        ];
        
        return $sectionPermissionMap[$sectionId] ?? 'view';
    }

    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'profile-setting';
    }
}
