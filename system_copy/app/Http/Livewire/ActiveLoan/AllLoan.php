<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;

class AllLoan extends Component
{
    use WithModulePermissions;
    public $tab_id = 1;
    
    // Filter visibility
    public $showFilters = false;
    
    // Sub-tabs for each main section
    public $loanTab = 'summary';
    public $paymentTab = 'new';
    public $arrearsTab = 'days';
    public $portfolioTab = 'par';
    public $collectionTab = 'ongoing';
    public $collateralTab = 'list';
    
    protected $listeners = [
        "displayLoanReport" => "setView",
        "refreshData" => "refreshData"
    ];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
    }

    public function boot()
    {
        session()->put('tab_id', 1);
    }

    public function setView($id)
    {
        // Check permissions based on the section being accessed
        $requiredPermission = $this->getRequiredPermissionForSection($id);
        $permissionKey = 'can' . ucfirst($requiredPermission);
        
        if (!($this->permissions[$permissionKey] ?? false)) {
            session()->flash('error', 'You do not have permission to access this loan management section');
            return;
        }
        
        $this->tab_id = $id;
        session()->put('tab_id', $id);
    }

    // Sub-tab setters for each main section
    public function setLoanTab($tab)
    {
        $this->loanTab = $tab;
    }

    public function setPaymentTab($tab)
    {
        $this->paymentTab = $tab;
    }

    public function setArrearsTab($tab)
    {
        $this->arrearsTab = $tab;
    }

    public function setPortfolioTab($tab)
    {
        $this->portfolioTab = $tab;
    }

    public function setCollectionTab($tab)
    {
        $this->collectionTab = $tab;
    }

    public function setCollateralTab($tab)
    {
        $this->collateralTab = $tab;
    }

    public function refreshData()
    {
        // Refresh data method
        $this->emit('dataRefreshed');
    }
    
    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function render()
    {
        return view('livewire.active-loan.all-loan', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }

    /**
     * Get the required permission for a specific loan management section
     */
    private function getRequiredPermissionForSection($sectionId)
    {
        $sectionPermissionMap = [
            1 => 'view',      // Active Loans
            2 => 'view',      // Arrears Overview
            3 => 'view',      // Arrears by Days
            4 => 'view',      // Arrears by Amount
            5 => 'manage',    // Collection Management (requires manage permission)
            6 => 'view',      // Risk Analysis
            7 => 'view',      // Branch Performance
            8 => 'view',      // Trends & Forecasting
            9 => 'view',      // Reports & Analytics
            10 => 'view',     // Loan Full Report
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
        return 'active-loan';
    }
}
