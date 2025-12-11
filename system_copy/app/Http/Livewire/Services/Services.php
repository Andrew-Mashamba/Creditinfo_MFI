<?php

namespace App\Http\Livewire\Services;

use App\Models\UserSubMenu;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;

use App\Models\ServicesList;

class Services extends Component
{
    use WithModulePermissions;

    public $totalNodes;
    public $inActiveNodes;
    public $nodes;
    public $selected;
    public $changeView;
    public $totalServices;
    public $inActiveSevices;
    public $services;
    public $user_sub_menus;
    public $tab_id=1;

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
    }

    function menuItemClicked($id){
        // Check permissions based on the tab being accessed
        $permissionMap = [
            1 => 'hr',           // Human Resources
            2 => 'procurement',  // Procurement
            3 => 'others'        // Others
        ];
        
        $requiredPermission = $permissionMap[$id] ?? 'view';
        
        if (!$this->authorize($requiredPermission, 'You do not have permission to access this service category')) {
            return;
        }
        
        $this->tab_id=$id;
    }
    
    public function boot(): void
    {
        $this->totalServices = \App\Models\ServicesList::count();
        $this->inActiveSevices = \App\Models\ServicesList::where('status', 'INACTIVE')->orWhere('status', 'DELETED')->count();
        $this->selected = 1;
        $this->user_sub_menus = UserSubMenu::where('menu_id',2)->where('user_id', Auth::user()->id)->get();
    }

    public function setView($page): void
    {
        $this->selected = $page;
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->services = \App\Models\ServicesList::get();
        $this->user_sub_menus = UserSubMenu::where('menu_id',2)->where('user_id', Auth::user()->id)->get();
        return view('livewire.services.services', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }

    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'services';
    }
}
