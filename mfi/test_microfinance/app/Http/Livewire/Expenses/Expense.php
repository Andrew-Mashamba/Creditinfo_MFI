<?php

namespace App\Http\Livewire\Expenses;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;

class Expense extends Component
{
    use WithModulePermissions;
    public $selected;
    public $unusedBudget;
    public $selectedMenuItem = 1; // Default to Dashboard Overview
    public $search = '';
    public $showDropdown = false;
    public $results = [];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
    }

    public function selectedMenu($menuId)
    {
        // Check permissions based on the menu being accessed
        $permissionMap = [
            1 => 'view',         // Dashboard Overview
            2 => 'create',       // New Expense
            3 => 'view',         // Expense List
            4 => 'approve',      // Pending Approval
            5 => 'manage',       // Categories
            6 => 'view',         // Reports
            7 => 'view'          // Accrued Expenses
        ];
        
        $requiredPermission = $permissionMap[$menuId] ?? 'view';
        
        if (!$this->authorize($requiredPermission, 'You do not have permission to access this expense section')) {
            return;
        }
        
        $this->selectedMenuItem = $menuId;
        $this->showDropdown = false;
        $this->results = [];
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->results = DB::table('expenses')
                ->where('description', 'like', '%' . $this->search . '%')
                ->orWhere('category', 'like', '%' . $this->search . '%')
                ->limit(10)
                ->get();
            $this->showDropdown = true;
        } else {
            $this->showDropdown = false;
            $this->results = [];
        }
    }

    public function render()
    {
        return view('livewire.expenses.expense', array_merge(
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
        return 'expenses';
    }
}
