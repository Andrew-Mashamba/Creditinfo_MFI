<?php

namespace App\Http\Livewire\Branches;

use App\Models\User;
use Livewire\Component;
use App\Models\BranchesModel;
use App\Models\approvals;
use App\Models\Till;
use App\Models\Vault;
use App\Services\AccountCreationService;
use App\Traits\Livewire\WithModulePermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Branches extends Component
{
    use WithModulePermissions;

    public $tab_id = '1';
    public $title = 'Branches list';
    public $selected;
    public $activeBranchesCount;
    public $inactiveBranchesCount;
    public $showCreateNewBranch;
    public $name;
    public $region;
    public $wilaya;
    public $branch_number;
    public $parentBranch;
    public $showDeleteBranch;
    public $branchSelected;
    public $showEditBranch;
    public $pendingbranch;
    public $branchesList;
    public $pendingbranchname;
    public $branch;
    public $showAddBranch = false;
    public $viewBranchDetails = false;
    public $membershipNumber;
    public $permission;
    public $password;

    // New properties for additional fields
    public $email;
    public $phone_number;
    public $address;
    public $branch_type;
    public $opening_date;
    public $branch_manager;
    public $opening_time;
    public $closing_time;
    public $cit_provider_id;
    public $services_offered;

    public $branchManagers;

    protected $listeners = [
        'showUsersList' => 'showUsersList',
        'blockBranch' => 'blockBranchModal',
        'editBranch' => 'editBranchModal',
        'viewBranch' => 'viewBranch',
        'refreshStats' => 'loadBranchStats',
        'refreshBranchesList' => '$refresh',
        'refreshPermissions' => 'refreshPermissions'
    ];

    protected $rules = [
        'name' => 'required|min:3|max:100',
        'region' => 'required|min:3|max:50',
        'wilaya' => 'required|min:3|max:50',
        'branch_number' => 'required|unique:branches,branch_number',
        'email' => 'required|email|max:100',
        'phone_number' => 'required|max:20',
        'address' => 'required|max:255',
        'branch_type' => 'required|in:MAIN,SUB,MOBILE',
        'opening_date' => 'required|date',
        'branch_manager' => 'required',
        'opening_time' => 'required',
        'closing_time' => 'required',
        'cit_provider_id' => 'required|exists:cash_in_transit_providers,id'
    ];

    protected $messages = [
        'name.required' => 'Branch name is required',
        'name.min' => 'Branch name must be at least 3 characters',
        'region.required' => 'Region is required',
        'wilaya.required' => 'Wilaya is required',
        'branch_number.required' => 'Branch number is required',
        'branch_number.unique' => 'This branch number is already registered',
        'email.required' => 'Email address is required',
        'email.email' => 'Please enter a valid email address',
        'phone_number.required' => 'Phone number is required',
        'address.required' => 'Physical address is required',
        'branch_type.required' => 'Branch type is required',
        'branch_type.in' => 'Please select a valid branch type',
        'opening_date.required' => 'Opening date is required',
        'opening_date.date' => 'Please enter a valid date',
        'branch_manager.required' => 'Branch manager must be selected',
        'opening_time.required' => 'Opening time is required',
        'closing_time.required' => 'Closing time is required',
        'cit_provider_id.required' => 'CIT Service Provider is required',
        'cit_provider_id.exists' => 'Please select a valid CIT Service Provider'
    ];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        
        // Load branch statistics
        $this->loadBranchStats();
        
        Log::info('Branch module initialized', [
            'user_id' => auth()->id(),
            'permissions' => $this->permissions
        ]);
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



    public function loadBranchStats()
    {
        $this->activeBranchesCount = BranchesModel::where('status', 'ACTIVE')->count();
        $this->inactiveBranchesCount = BranchesModel::where('status', '!=', 'ACTIVE')->count();
    }

    public function showAddBranchModal($selected)
    {
        Log::info('Tab switching to', [
            'tab_id' => $selected,
            'user_id' => auth()->id()
        ]);

        // Handle tab switching
        $this->tab_id = $selected;
        
        if ($selected == 2) {
            // If switching to "New Branch" tab, prepare the form with auto-generated branch number
            $this->title = 'Enter new branch details';
            
            // Generate branch number automatically when accessing the form
            if (empty($this->branch_number)) {
                $latest_branch = DB::table('branches')->latest()->value('branch_number');
                $this->branch_number = $latest_branch ? (int)$latest_branch + 1 : 1;
                
                Log::info('Branch number auto-generated for new branch tab', [
                    'latest_branch' => $latest_branch,
                    'new_branch_number' => $this->branch_number
                ]);
            }
        } else {
            $this->title = 'Branches list';
        }
    }

    public function openCreateBranchModal()
    {
        Log::info('Opening create branch modal', [
            'user_id' => auth()->id(),
            'has_permission' => $this->can('create')
        ]);

        if (!$this->authorize('create', 'You do not have permission to create branches')) {
            return;
        }

        // Reset data first
        $this->resetData();
        
        // Generate branch number automatically
        $latest_branch = DB::table('branches')->latest()->value('branch_number');
        $this->branch_number = $latest_branch ? (int)$latest_branch + 1 : 1;
        
        Log::info('Branch number auto-generated for new branch modal', [
            'latest_branch' => $latest_branch,
            'new_branch_number' => $this->branch_number
        ]);
        
        // Open the modal
        $this->showAddBranch = true;
    }

    public function closeShowAddBranch(){
        $this->resetData();
        $this->branch_number = null; // Reset branch number when closing
        $this->showAddBranch = false;
    }

    public function updatedBranch(){
        $branchData = BranchesModel::select('branch_number', 'name', 'region', 'wilaya')
            ->where('id', '=', $this->branch)
            ->get();
        foreach ($branchData as $branch){
            $this->branch_number=$branch->branch_number;
            $this->name=$branch->name;
            $this->region=$branch->region;
            $this->wilaya=$branch->wilaya;
        }
    }

    public function updateBranch()
    {
        if (!$this->authorize('edit', 'You do not have permission to edit branches')) {
            return;
        }

        try {
            DB::beginTransaction();

           

            // Validate unique fields
            $rules = [
                'name' => 'required|min:3|max:100',
                'region' => 'required|min:3|max:50',
                'wilaya' => 'required|min:3|max:50',
                'email' => 'required|email|max:100|unique:branches,email,' . $this->branch,
                'phone_number' => 'required|max:20|unique:branches,phone_number,' . $this->branch,
                'address' => 'required|max:255',
                'branch_type' => 'required|in:MAIN,SUB,MOBILE',
                'opening_date' => 'required|date',
                'branch_manager' => 'required',
                'opening_time' => 'required',
                'closing_time' => 'required',
                'cit_provider_id' => 'required|exists:cash_in_transit_providers,id'
            ];

            $this->validate($rules);

            $data = [
                'branch_number' => $this->branch_number,
                'name' => strtoupper($this->name),
                'region' => strtoupper($this->region),
                'wilaya' => strtoupper($this->wilaya),
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'address' => $this->address,
                'branch_type' => $this->branch_type,
                'opening_date' => $this->opening_date,
                'branch_manager' => $this->branch_manager,
                'operating_hours' => $this->opening_time . ' - ' . $this->closing_time,
                'cit_provider_id' => $this->cit_provider_id
            ];

            // dd($data);
            // Get the current branch data
            $currentBranch = DB::table('branches')->where('id', $this->branch)->first();
            
            // Create comparison package
            $editPackage = [];
            foreach ($data as $field => $newValue) {
                $editPackage[$field] = [
                    'old' => $currentBranch->$field ?? null,
                    'new' => $newValue
                ];
            }

            // Create approval record
            approvals::create([
                'institution' => $this->branch,
                'process_name' => 'Edit Branch Details',
                'process_description' => auth()->user()->name . ' has requested to edit branch information: ' .
                    'name ' . $data['name'] . ', region ' . $data['region'] . ', wilaya ' . $data['wilaya'],
                'approval_process_description' => 'has approved changes to a branch',
                'process_code' => 'BRANCH_EDIT',
                'process_id' => $this->branch,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'team_id' => 1,
                'approver_id' => null,
                'edit_package' => json_encode($editPackage),
                'approval_status' => 'PENDING'
            ]);

            DB::commit();

            session()->flash('message', 'Branch update submitted for approval');
            session()->flash('alert-class', 'alert-success');

            $this->resetData();
            $this->closeModal();
            $this->emit('refreshBranchesList');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating branch', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', 'Failed to update branch: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function addBranch()
    {
        Log::info('Starting addBranch function', [
            'user_id' => auth()->id(),
            'has_permission' => $this->can('create')
        ]);

        if (!$this->authorize('create', 'You do not have permission to create branches')) {
            return;
        }

        try {
            Log::info('Validating branch data', [
                'data' => [
                    'name' => $this->name,
                    'region' => $this->region,
                    'wilaya' => $this->wilaya,
                    'email' => $this->email,
                    'phone_number' => $this->phone_number,
                    'branch_type' => $this->branch_type,
                    'opening_date' => $this->opening_date,
                    'branch_manager' => $this->branch_manager,
                    'cit_provider_id' => $this->cit_provider_id
                ]
            ]);

            $this->validate();

            // Pre-flight checks before starting transaction
            if (BranchesModel::where('branch_number', $this->branch_number)->exists()) {
                throw new \Exception('Branch number already exists');
            }

            // Get institution and validate main accounts exist
            $institution = DB::table('institutions')->where('id', 1)->first();
            if (!$institution) {
                throw new \Exception('Institution not found');
            }

            if (empty($institution->main_vaults_account) || empty($institution->main_till_account) || empty($institution->main_petty_cash_account)) {
                throw new \Exception('Institution main accounts not properly configured. Please configure them in Institution Accounts Setup first.');
            }

            

            // Validate that the main accounts actually exist
            $mainAccountsExist = DB::table('accounts')
                ->whereIn('account_number', [
                    $institution->main_vaults_account,
                    $institution->main_till_account,
                    $institution->main_petty_cash_account
                ])
                ->count();

            // if ($mainAccountsExist !== 3) {
            //     throw new \Exception('One or more main institution accounts do not exist in the system');
            // }

            //dd($institution);

            DB::beginTransaction();

            // Create branch record
            $branch = new BranchesModel();
            $branch->status = 'PENDING';
            $branch->branch_number = str_pad($this->branch_number, 2, 0, STR_PAD_LEFT);
            $branch->name = strtoupper($this->name);
            $branch->region = strtoupper($this->region);
            $branch->wilaya = strtoupper($this->wilaya);
            $branch->email = $this->email;
            $branch->phone_number = $this->phone_number;
            $branch->address = $this->address;
            $branch->branch_type = $this->branch_type;
            $branch->opening_date = $this->opening_date;
            $branch->branch_manager = $this->branch_manager;
            $branch->operating_hours = $this->opening_time . ' - ' . $this->closing_time;
            $branch->cit_provider_id = $this->cit_provider_id;

            Log::info('Attempting to save branch', [
                'branch_data' => $branch->toArray()
            ]);

            $branch->save();

            Log::info('Branch saved successfully', [
                'branch_id' => $branch->id
            ]);

            // Create branch accounts within transaction
            $accountService = new AccountCreationService();

            $branchVaultAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => 'VAULT ACCOUNT: ' . $this->name,
                'type' => 'asset_accounts',
                'product_number' => '0000',
                'member_number' => '00000',
                'branch_number' => $this->branch_number
            ], $institution->main_vaults_account);

            $branchTillAccount = $accountService->createAccount([
                'account_use' => 'internal', 
                'account_name' => 'TILL ACCOUNT: ' . $this->name,
                'type' => 'asset_accounts',
                'product_number' => '0000',
                'member_number' => '00000',
                'branch_number' => $this->branch_number
            ], $institution->main_till_account);

            $branchPettyCashAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => 'PETTY CASH ACCOUNT: ' . $this->name,
                'type' => 'asset_accounts',
                'product_number' => '0000',
                'member_number' => '00000',
                'branch_number' => $this->branch_number
            ], $institution->main_petty_cash_account);

            // Update branch with account numbers
            $branch->vault_account = $branchVaultAccount->account_number;
            $branch->till_account = $branchTillAccount->account_number;
            $branch->petty_cash_account = $branchPettyCashAccount->account_number;
            $branch->save();

            Log::info('Branch accounts created successfully', [
                'branch_id' => $branch->id,
                'vault_account' => $branchVaultAccount->account_number,
                'till_account' => $branchTillAccount->account_number,
                'petty_cash_account' => $branchPettyCashAccount->account_number
            ]);

            // Create till record within transaction
            Till::create([
                'branch_id' => $branch->id,
                'name' => $branchTillAccount->account_name,
                'till_number' => rand(1000, 9999), // four random digits
                'description' => 'Branch till account for ' . $this->name,
                'status' => 'closed', // Default status for new tills
                'till_account_number' => $branchTillAccount->account_number,
                'current_balance' => 0.00,
                'opening_balance' => 0.00
            ]);

            // Create vault record within transaction
            $vault = Vault::create([
                'branch_id' => $branch->id,
                'name' => $branchVaultAccount->account_name,
                'code' => 'VAULT-' . str_pad($branch->id, 4, '0', STR_PAD_LEFT),
                'parent_account' => $institution->main_vaults_account,
                'internal_account_number' => $branchVaultAccount->account_number,
                'description' => 'Branch vault account for ' . $this->name,
                'status' => 'active', // Default status for new vaults
                'current_balance' => 0.00,
                'limit' => 5000000.00, // Default limit
                'warning_threshold' => 80, // 80% warning threshold
                'auto_bank_transfer' => false,
                'requires_dual_approval' => true,
                'send_alerts' => true
            ]);

            Log::info('Branch vault created successfully', [
                'branch_id' => $branch->id,
                'vault_id' => $vault->id,
                'vault_code' => $vault->code,
                'vault_account' => $branchVaultAccount->account_number
            ]);


            // Create approval record within transaction
            approvals::create([
                'process_name' => 'Create New Branch',
                'process_description' => 'New branch registration: ' . $branch->name,
                'approval_process_description' => auth()->user()->name . ' has requested to add a new branch',
                'process_code' => 'BRANCH_CREATE',
                'process_id' => $branch->id,
                'process_status' => 'PENDING',
                'user_id' => Auth::user()->id,
                'team_id' => $branch->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode([])
            ]);

            DB::commit();

            Log::info('Branch creation completed successfully', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name
            ]);

            session()->flash('message', 'Branch registration submitted for approval. Vault, Till, and Petty Cash accounts created successfully.');
            session()->flash('alert-class', 'alert-success');

            $this->resetData();
            $this->closeShowAddBranch();
            $this->emit('refreshBranchesList');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            Log::warning('Validation failed for branch creation', [
                'errors' => $e->errors()
            ]);
            // Re-throw validation exception to show field-specific errors
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating branch', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            $errorMessage = 'Failed to create branch: ' . $e->getMessage();
            
            // Provide more user-friendly error messages for common issues
            if (str_contains($e->getMessage(), 'main accounts not properly configured')) {
                $errorMessage = 'Please configure the main institution accounts (Vaults, Till, Petty Cash) in Institution Settings before creating branches.';
            } elseif (str_contains($e->getMessage(), 'Branch number already exists')) {
                $errorMessage = 'Branch number already exists. Please choose a different branch number.';
            } elseif (str_contains($e->getMessage(), 'Institution not found')) {
                $errorMessage = 'Institution configuration not found. Please contact system administrator.';
            }
            
            session()->flash('message', $errorMessage);
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function resetData()
    {
        $this->reset([
            'name', 'region', 'wilaya',
            'email', 'phone_number', 'address', 'branch_type',
            'opening_date', 'branch_manager', 'opening_time',
            'closing_time', 'cit_provider_id'
        ]);
        // Note: branch_number is excluded as it's auto-generated
    }

    public function resetFormAndGenerateBranchNumber()
    {
        Log::info('Resetting form and generating new branch number', [
            'user_id' => auth()->id()
        ]);

        // Reset all form data
        $this->resetData();
        
        // Generate new branch number
        $latest_branch = DB::table('branches')->latest()->value('branch_number');
        $this->branch_number = $latest_branch ? (int)$latest_branch + 1 : 1;
        
        Log::info('Form reset and branch number generated', [
            'latest_branch' => $latest_branch,
            'new_branch_number' => $this->branch_number
        ]);
    }

    public function menuItemClicked($tabId){
        $this->tab_id = $tabId;
        if($tabId == '1'){
            $this->title = 'Branches list';
        }
        if($tabId == '2'){
            $this->title = 'Enter new branch details';
        }
    }

    public function createNewBranch()
    {
        $this->showCreateNewBranch = true;
    }

    public function blockBranchModal($id)
    {
        if (!$this->authorize('delete', 'You do not have permission to block branches')) {
            return;
        }

        $this->showDeleteBranch = true;
        $this->branchSelected = $id;
    }

    public function editBranchModal($id)
    {
        if (!$this->authorize('edit', 'You do not have permission to edit branches')) {
            return;
        }

        $branch = BranchesModel::findOrFail($id);
        
        $this->branch = $id;
        $this->branch_number = $branch->branch_number;
        $this->branch_number = $branch->branch_number;
        $this->name = $branch->name;
        $this->region = $branch->region;
        $this->wilaya = $branch->wilaya;
        $this->email = $branch->email;
        $this->phone_number = $branch->phone_number;
        $this->address = $branch->address;
        $this->branch_type = $branch->branch_type;
        $this->opening_date = $branch->opening_date;
        $this->branch_manager = $branch->branch_manager;
        
        // Split operating hours
        if ($branch->operating_hours) {
            $hours = explode(' - ', $branch->operating_hours);
            $this->opening_time = $hours[0] ?? '';
            $this->closing_time = $hours[1] ?? '';
        }
        
        // Set CIT provider
        $this->cit_provider_id = $branch->cit_provider_id;
        
        $this->showEditBranch = true;
    }

    public function closeModal(){
        $this->showCreateNewBranch = false;
        $this->showDeleteBranch = false;
        $this->showEditBranch = false;
    }

    public function confirmPassword(): void
    {
        if (Hash::check($this->password, auth()->user()->password)) {
            $this->delete();
        } else {
            Session::flash('message', 'This password does not match our records');
            Session::flash('alert-class', 'alert-warning');
        }
        $this->resetPassword();
    }

    public function resetPassword(): void
    {
        $this->password = null;
    }

    public function delete()
    {
        Log::info('Attempting to delete/change branch status', [
            'user_id' => auth()->id(),
            'branch_id' => $this->branchSelected,
            'action' => $this->permission,
            'has_permission' => $this->can('delete')
        ]);

        // Check for different permissions based on action
        $requiredPermission = ($this->permission === 'ACTIVE') ? 'activate' : 'delete';
        if (!$this->authorize($requiredPermission, "You do not have permission to {$requiredPermission} branches")) {
            return;
        }

        try {
            DB::beginTransaction();

            $branch = BranchesModel::findOrFail($this->branchSelected);

            // Check for dependencies
            $dependencies = [
                'clients' => DB::table('clients')->where('branch_id', $this->branchSelected)->exists(),
                'users' => DB::table('users')->where('branch', $this->branchSelected)->exists(),
                'transactions' => DB::table('transactions')->where('branch_id', $this->branchSelected)->exists(),
                'loans' => DB::table('loans')->where('branch_id', $this->branchSelected)->exists()
            ];

            $hasDependencies = collect($dependencies)->contains(true);
            
            if ($hasDependencies) {
                $dependencyList = collect($dependencies)
                    ->filter()
                    ->keys()
                    ->implode(', ');
                throw new \Exception("Cannot delete branch: It has associated records in: {$dependencyList}");
            }

            $action = '';
            $description = '';
            
            switch ($this->permission) {
                case 'BLOCKED':
                    $action = 'blockBranch';
                    $description = 'Block branch';
                    break;
                case 'ACTIVE':
                    $action = 'activateBranch';
                    $description = 'Activate branch';
                    break;
                case 'DELETED':
                    $action = 'deleteBranch';
                    $description = 'Delete branch';
                    break;
                default:
                    throw new \Exception('Invalid permission status');
            }

            // Create approval record with detailed information
            approvals::create([
                'institution' => $branch->id,
                'process_name' => $action,
                'process_description' => auth()->user()->name . ' has requested to ' . strtolower($description) . ': ' . $branch->name,
                'approval_process_description' => 'has approved ' . strtolower($description) . ' for branch: ' . $branch->name,
                'process_code' => 'BRANCH_DEACTIVATE',
                'process_id' => $this->branchSelected,
                'process_status' => 'PENDING',
                'user_id' => Auth::user()->id,
                'team_id' => 1, // Using institution ID instead of branch ID
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode([
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'branch_number' => $branch->branch_number,
                    'current_status' => $branch->status,
                    'requested_status' => $this->permission,
                    'action' => $action,
                    'requested_by' => auth()->user()->name,
                    'requested_at' => now()->toDateTimeString()
                ])
            ]);

            DB::commit();

            Log::info('Branch status change request submitted successfully', [
                'branch_id' => $this->branchSelected,
                'action' => $action,
                'requested_by' => auth()->user()->name
            ]);

            session()->flash('message', 'Branch status change submitted for approval');
            session()->flash('alert-class', 'alert-success');

            $this->closeModal();
            $this->emit('refreshBranchesList');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error changing branch status', [
                'error' => $e->getMessage(),
                'branch_id' => $this->branchSelected,
                'action' => $this->permission,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', 'Failed to change branch status: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function render()
    {
        $this->activeBranchesCount = BranchesModel::where('status', '=', 'ACTIVE')->count();
        $this->inactiveBranchesCount = BranchesModel::where('status', '!=', 'ACTIVE')
            ->count();
        $this->branchesList = BranchesModel::get();

        $this->branchManagers = DB::table('users')
                    //->where('role', 'Branch Manager')   //to be reviewed
                    ->whereNot('id', auth()->user()->id)
                    ->get();

        // Get CIT providers for dropdown
        $citProviders = DB::table('cash_in_transit_providers')
                    ->where('status', 'ACTIVE')
                    ->orderBy('name')
                    ->get();

        // Pass permissions to the view using the new system
        return view('livewire.branches.branches', array_merge(
            $this->permissions,
            [
                'branchManagers' => $this->branchManagers,
                'citProviders' => $citProviders,
                'permissions' => $this->permissions // Also pass the full permissions array
            ]
        ));
    }

    public function columns()
    {
        return [
            (object)['name' => 'branch_number', 'label' => 'Branch Number'],
            (object)['name' => 'name', 'label' => 'Branch Name'],
            (object)['name' => 'region', 'label' => 'Region'],
            (object)['name' => 'wilaya', 'label' => 'District'],
            (object)['name' => 'status', 'label' => 'Status'],
            (object)['name' => 'branch_type', 'label' => 'Type'],
            (object)['name' => 'branch_manager', 'label' => 'Manager']
        ];
    }

    public function generateBranchNumber()
    {
        $latest_branch = DB::table('branches')->latest()->value('branch_number');
        $this->branch_number = $latest_branch + 1;
    }

    public function edit($id)
    {
        $branchData = BranchesModel::select('branch_number', 'name', 'region', 'wilaya')
            ->where('id', $id)
            ->first();
        $branch = BranchesModel::find($id);
        $this->branch_number = $branch->branch_number;
        // ... rest of the method ...
    }

    public function save()
    {
        // ... existing code ...
        $branch = new BranchesModel();
        $branch->branch_number = str_pad($this->branch_number, 2, 0, STR_PAD_LEFT);
        // ... rest of the method ...
    }

    public function update()
    {
        if (BranchesModel::where('branch_number', $this->branch_number)->exists()) {
            // ... existing code ...
        }
        $branch = BranchesModel::find($this->branch_id);
        $branch->branch_number = str_pad($this->branch_number, 2, 0, STR_PAD_LEFT);
        // ... rest of the method ...
    }

    public function viewBranch($id)
    {
        Log::info('Attempting to view branch', [
            'user_id' => auth()->id(),
            'branch_id' => $id,
            'has_permission' => $this->can('view')
        ]);

        if (!$this->authorize('view', 'You do not have permission to view branch details')) {
            return;
        }

        $branch = BranchesModel::find($id);
        if (!$branch) {
            session()->flash('message', 'Branch not found');
            session()->flash('alert-class', 'alert-danger');
            return;
        }

        $this->branch = $id;
        $this->name = $branch->name;
        $this->region = $branch->region;
        $this->wilaya = $branch->wilaya;
        $this->branch_number = $branch->branch_number;
        $this->email = $branch->email;
        $this->phone_number = $branch->phone_number;
        $this->address = $branch->address;
        $this->branch_type = $branch->branch_type;
        $this->opening_date = $branch->opening_date;
        $this->branch_manager = $branch->branch_manager;
        
        // Split operating hours
        if ($branch->operating_hours) {
            $hours = explode(' - ', $branch->operating_hours);
            $this->opening_time = $hours[0] ?? '';
            $this->closing_time = $hours[1] ?? '';
        }
        
        $this->cit_provider_id = $branch->cit_provider_id;
        $this->services_offered = $branch->services_offered;

        $this->viewBranchDetails = true;
    }
}
