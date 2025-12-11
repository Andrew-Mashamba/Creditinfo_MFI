<?php

namespace App\Http\Livewire\BudgetManagement;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;

class Budget extends Component
{
    use WithModulePermissions;
    public $tab_id=1;
    public $selectYear;

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
    }

    public function refreshComponent(){

        $this->emitTo('budget-management.all-budget','updateYear',$this->selectYear);
    }

    public function menuItemClicked($id){
        // Check permissions based on the menu being accessed
        $permissionMap = [
            1 => 'view',         // Budget Overview
            2 => 'create',       // Budget Items
            3 => 'approve',      // Pending Approval
            4 => 'view',         // Budget Reports
            5 => 'view',         // Budget Analysis
            6 => 'view',         // Budget Monitor
            7 => 'manage'        // Advanced Features
        ];
        
        $requiredPermission = $permissionMap[$id] ?? 'view';
        
        if (!$this->authorize($requiredPermission, 'You do not have permission to access this budget management section')) {
            return;
        }
        
        $this->tab_id=$id;
    }

    public function boot(){
        $this->selectYear=Carbon::now()->year;
    }
    public function render()
    {
        return view('livewire.budget-management.budget', array_merge(
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
        return 'budget-management';
    }
}
