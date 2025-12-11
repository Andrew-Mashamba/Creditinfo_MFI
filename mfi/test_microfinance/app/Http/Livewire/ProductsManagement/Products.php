<?php

namespace App\Http\Livewire\ProductsManagement;

use Livewire\Component;
use App\Models\AccountsModel;
use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;
use App\Traits\Livewire\WithModulePermissions;

class Products extends Component
{
    use WithModulePermissions;
    public $tab_id = 1;
    public $totalShares = 0;
    public $totalDeposits = 0;
    public $activeLoans = 0;
    public $search = '';



    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->refreshStatistics();
    }

    public function menuItemClicked($id)
    {
        // Check permissions based on the tab being accessed
        $permissionMap = [
            1 => 'shares',      // Share Management
            2 => 'savings',     // Savings Products  
            3 => 'deposits',    // Deposit Services
            4 => 'loans'        // Loan Products
        ];
        
        $requiredPermission = $permissionMap[$id] ?? 'view';
        
        if (!$this->authorize($requiredPermission, 'You do not have permission to access this product category')) {
            return;
        }
        
        $this->tab_id = $id;
    }

    public function refreshStatistics()
    {
        // Get total shares count
        $this->totalShares = AccountsModel::where('product_number', '1000')->count();

        // Get total deposits count
        $this->totalDeposits = AccountsModel::where('product_number', '2000')->count();

        // Get active loans count
        $this->activeLoans = LoansModel::count();
    }

    public function render()
    {
        return view('livewire.products-management.products', array_merge(
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
        return 'products-management';
    }
}
