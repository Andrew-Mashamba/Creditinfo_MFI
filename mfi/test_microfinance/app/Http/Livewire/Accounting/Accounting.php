<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;
use App\Traits\Livewire\WithModulePermissions;

class Accounting extends Component
{
    use WithModulePermissions;
    public $tab_id = '37'; // Default to Chart Of Accounts
    public $menuSearch = '';
    public $viewMemberDetails = false;
    
    // Statistics properties
    public $totalInstitutionAccounts;
    public $totalMemberAccounts;
    public $pendingActivities;
    public $menuItems;
    public $menuCategories;
    public $title;

    protected $listeners = [
        'financeViewMember' => 'viewMembersData'
    ];
    
    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
    }
    
    /**
     * Stub method to prevent errors when child components try to call this
     * The actual implementation is in LoanLossReserveManager component
     */
    public function loadYearEndData()
    {
        // This is a stub method - actual implementation is in LoanLossReserveManager
        return;
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'accounting';
    }

    public function viewMembersData($id)
    {
        if ($this->viewMemberDetails == false) {
            $this->viewMemberDetails = true;
            session()->put('viewMemberId_details', $id);
        } else if ($this->viewMemberDetails == true) {
            $this->viewMemberDetails = false;
        }
    }

    public function menuItemClicked($tabId)
    {
        if (!$this->authorize('view', 'You do not have permission to view accounting sections')) {
            return;
        }
        
        $this->tab_id = $tabId;
        if ($tabId == '1') {
            $this->title = 'Internal accounts';
        }
        if ($tabId == '2') {
            $this->title = 'Enter new shares details';
        }
        if ($tabId == '3') {
            $this->title = 'External accounts';
        }
        if ($tabId == '4') {
            $this->title = 'Loan Disbursements';
            Session::put('viewAccountsWithCategory', 'Accounting');
            Session::put('currentloanID', null);
            Session::put('currentloanMember', null);
            Session::put('disableInputs', true);
        }
        if ($tabId == '5') {
            $this->title = 'PO / Invoices';
            Session::put('viewAccountsWithCategory', 'AccountingPO');
            Session::put('currentloanID', null);
            Session::put('currentloanMember', null);
            Session::put('disableInputs', true);
        }
    }

    public function render()
    {
        // Prepare statistics
        $this->totalInstitutionAccounts = AccountsModel::count();
        $this->totalMemberAccounts = AccountsModel::where('account_use', 'internal')->count();
        $this->pendingActivities = AccountsModel::where('status', 'PENDING')->count();

        // Prepare menu items
        $this->menuItems = [
            // Removed: ['id' => 1, 'label' => 'Chart of accounts'],
            ['id' => 37, 'label' => 'Chart Of Accounts'],
            ['id' => 47, 'label' => 'Ledger Accounts'],
            ['id' => 48, 'label' => 'Journal Entry'],
            // Removed: ['id' => 2, 'label' => 'Manual Posting'],
            ['id' => 3, 'label' => 'External Bank Accounts'],
            ['id' => 4, 'label' => 'Loan Disbursement'],
            ['id' => 6, 'label' => 'Standing Instructions'],
            ['id' => 12, 'label' => 'GL Statement'],
            ['id' => 10, 'label' => 'Expenditure Control'],
            // Removed: ['id' => 40, 'label' => 'Income Statement'],
            // Removed: ['id' => 41, 'label' => 'Jedwali La Mahesabu'],
            // Removed: ['id' => 42, 'label' => 'Financial Position'],
            ['id' => 50, 'label' => 'Financial Statements'],
            // Removed: ['id' => 28, 'label' => 'Changes In Equity'],
            // Removed: ['id' => 8, 'label' => 'Cash Flow Statement'],
            // Removed: ['id' => 5, 'label' => 'Trial Balance'],
            ['id' => 9, 'label' => 'Members'],
            // Removed: ['id' => 16, 'label' => 'Assets Management'],
            ['id' => 17, 'label' => 'Loan Loss Reserves (LLR)'],
            ['id' => 18, 'label' => 'Trade And Other Receivable'],
            ['id' => 30, 'label' => 'Trade And Other Payable'],
            // Removed: ['id' => 19, 'label' => 'Insurance'],
            ['id' => 20, 'label' => 'PPE Management'],
            ['id' => 21, 'label' => 'Other Income'],
            // Removed: ['id' => 22, 'label' => 'Financial Insurance'],
            ['id' => 24, 'label' => 'Creditors'],
            ['id' => 25, 'label' => 'Interest Payable'],
            ['id' => 49, 'label' => 'Interest Receivable'],
            ['id' => 26, 'label' => 'Short-term / Long-term Loans'],
            ['id' => 27, 'label' => 'Unearned / deferred revenue'],
            ['id' => 29, 'label' => 'Investments'],
            ['id' => 32, 'label' => 'Depreciation'],
            ['id' => 33, 'label' => 'Shares'],
            ['id' => 34, 'label' => 'Savings'],
            ['id' => 35, 'label' => 'Deposits'],
            ['id' => 36, 'label' => 'Loan Outstanding'],
            // Also removing as requested from Quick Actions:
            // ['id' => 43, 'label' => 'Approvers Manager'],
            // ['id' => 7, 'label' => 'Balance Sheet'],
        ];

        $this->menuCategories = [
            'Core Accounting' => [37, 47, 48, 3, 4, 6], // Added 48 (Journal Entry)
            'Financial Statements' => [12, 50], // Removed 40, 41, 42, 28, 8, 5
            'Asset Management' => [20, 32], // Removed 16
            'Risk Management' => [17], // Removed 23, 31
            'Operations' => [18, 30, 21, 24, 25, 49, 26, 27, 29, 36], // Added 49 (Interest Receivable)
        ];

        return view('livewire.accounting.accounting', array_merge(
            $this->permissions,
            [
                'totalInstitutionAccounts' => $this->totalInstitutionAccounts,
                'totalMemberAccounts' => $this->totalMemberAccounts,
                'pendingActivities' => $this->pendingActivities,
                'menuItems' => $this->menuItems,
                'menuCategories' => $this->menuCategories,
                'menuSearch' => $this->menuSearch,
                'permissions' => $this->permissions
            ]
        ));
    }
} 