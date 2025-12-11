<?php

namespace App\Http\Livewire\Branches;

use App\Models\BranchesModel;
use App\Traits\Livewire\WithModulePermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class BranchesTable extends Component
{
    use WithPagination, WithModulePermissions;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    
    // Modal states
    public $showViewModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $selectedBranchId = null;
    public $selectedBranch = null;
    
    // Edit form fields
    public $editBranchNumber;
    public $editName;
    public $editRegion;
    public $editWilaya;
    public $editStatus;
    public $editBranchType;
    public $editBranchManager;
    public $editEmail;
    public $editPhoneNumber;
    public $editAddress;
    public $editOpeningDate;
    public $editOperatingHours;
    public $editServicesOffered;
    public $editCitProviderId;
    public $editVaultAccount;
    public $editTillAccount;
    public $editPettyCashAccount;

    protected $listeners = ['viewBranch', 'editBranch', 'blockBranch'];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'branches';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }


    // Branch action methods
    public function viewBranch($branchId)
    {
        if (!$this->authorize('view', 'You do not have permission to view branch details')) {
            return;
        }
        
        $this->selectedBranchId = $branchId;
        $this->selectedBranch = BranchesModel::find($branchId);
        $this->showViewModal = true;
    }

    public function editBranch($branchId)
    {
        if (!$this->authorize('edit', 'You do not have permission to edit branches')) {
            return;
        }
        
        $this->selectedBranchId = $branchId;
        $this->selectedBranch = BranchesModel::find($branchId);
        
        // Populate edit form fields
        if ($this->selectedBranch) {
            $this->editBranchNumber = $this->selectedBranch->branch_number;
            $this->editName = $this->selectedBranch->name;
            $this->editRegion = $this->selectedBranch->region;
            $this->editWilaya = $this->selectedBranch->wilaya;
            $this->editStatus = $this->selectedBranch->status;
            $this->editBranchType = $this->selectedBranch->branch_type;
            $this->editBranchManager = $this->selectedBranch->branch_manager;
            $this->editEmail = $this->selectedBranch->email;
            $this->editPhoneNumber = $this->selectedBranch->phone_number;
            $this->editAddress = $this->selectedBranch->address;
            $this->editOpeningDate = $this->selectedBranch->opening_date;
            $this->editOperatingHours = $this->selectedBranch->operating_hours;
            $this->editServicesOffered = is_string($this->selectedBranch->services_offered) 
                ? $this->selectedBranch->services_offered 
                : json_encode($this->selectedBranch->services_offered);
            $this->editCitProviderId = $this->selectedBranch->cit_provider_id;
            $this->editVaultAccount = $this->selectedBranch->vault_account;
            $this->editTillAccount = $this->selectedBranch->till_account;
            $this->editPettyCashAccount = $this->selectedBranch->petty_cash_account;
        }
        
        $this->showEditModal = true;
    }

    public function blockBranch($branchId)
    {
        if (!$this->authorize('delete', 'You do not have permission to block/delete branches')) {
            return;
        }
        
        $this->selectedBranchId = $branchId;
        $this->selectedBranch = BranchesModel::find($branchId);
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->selectedBranchId) {
            $branch = BranchesModel::find($this->selectedBranchId);
            if ($branch) {
                $branch->delete();
                session()->flash('message', 'Branch deleted successfully.');
            }
        }
        $this->closeDeleteModal();
    }

    public function updateBranch()
    {
        if ($this->selectedBranch) {
            // Update branch with edited values
            $this->selectedBranch->branch_number = $this->editBranchNumber;
            $this->selectedBranch->name = $this->editName;
            $this->selectedBranch->region = $this->editRegion;
            $this->selectedBranch->wilaya = $this->editWilaya;
            $this->selectedBranch->status = $this->editStatus;
            $this->selectedBranch->branch_type = $this->editBranchType;
            $this->selectedBranch->branch_manager = $this->editBranchManager;
            $this->selectedBranch->email = $this->editEmail;
            $this->selectedBranch->phone_number = $this->editPhoneNumber;
            $this->selectedBranch->address = $this->editAddress;
            $this->selectedBranch->opening_date = $this->editOpeningDate;
            $this->selectedBranch->operating_hours = $this->editOperatingHours;
            $this->selectedBranch->services_offered = $this->editServicesOffered;
            $this->selectedBranch->cit_provider_id = $this->editCitProviderId;
            $this->selectedBranch->vault_account = $this->editVaultAccount;
            $this->selectedBranch->till_account = $this->editTillAccount;
            $this->selectedBranch->petty_cash_account = $this->editPettyCashAccount;
            
            $this->selectedBranch->save();
            session()->flash('message', 'Branch updated successfully.');
        }
        $this->closeEditModal();
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedBranchId = null;
        $this->selectedBranch = null;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedBranchId = null;
        $this->selectedBranch = null;
        
        // Reset edit form fields
        $this->editBranchNumber = null;
        $this->editName = null;
        $this->editRegion = null;
        $this->editWilaya = null;
        $this->editStatus = null;
        $this->editBranchType = null;
        $this->editBranchManager = null;
        $this->editEmail = null;
        $this->editPhoneNumber = null;
        $this->editAddress = null;
        $this->editOpeningDate = null;
        $this->editOperatingHours = null;
        $this->editServicesOffered = null;
        $this->editCitProviderId = null;
        $this->editVaultAccount = null;
        $this->editTillAccount = null;
        $this->editPettyCashAccount = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedBranchId = null;
        $this->selectedBranch = null;
    }

    public function render()
    {
        $branches = BranchesModel::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('branch_number', 'like', "%{$this->search}%")
                      ->orWhere('name', 'ilike', "%{$this->search}%")
                      ->orWhere('region', 'ilike', "%{$this->search}%")
                      ->orWhere('wilaya', 'ilike', "%{$this->search}%")
                      ->orWhere('status', 'ilike', "%{$this->search}%")
                      ->orWhere('branch_type', 'ilike', "%{$this->search}%")
                      ->orWhere('branch_manager', 'ilike', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.branches.branches-table', array_merge(
            $this->permissions,
            [
                'branches' => $branches,
                'permissions' => $this->permissions
            ]
        ));
    }
}
