# How to Apply Permissions to Any Module - Complete Guide

## Table of Contents
1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Step-by-Step Implementation](#step-by-step-implementation)
4. [Real Example: Implementing for Loans Module](#real-example-implementing-for-loans-module)
5. [Common Patterns](#common-patterns)
6. [Troubleshooting](#troubleshooting)
7. [Best Practices](#best-practices)

## Overview

The SACCOS permission system provides centralized, cached, and consistent permission management across all modules. This guide shows you how to implement it in any module.

### What You Get
- ✅ Automatic permission checking
- ✅ Cached permissions (5-minute cache)
- ✅ Consistent API across all modules
- ✅ Automatic UI permission variables
- ✅ Clean Blade templates
- ✅ Error handling built-in

## Quick Start

To add permissions to any module, you need to:

1. **Add the trait** to your Livewire component
2. **Initialize permissions** in `mount()`
3. **Override getModuleName()** to specify your module
4. **Use permission checks** in your methods
5. **Pass permissions to views** in `render()`

```php
use App\Traits\Livewire\WithModulePermissions;

class YourComponent extends Component
{
    use WithModulePermissions;
    
    public function mount()
    {
        $this->initializeWithModulePermissions();
    }
    
    protected function getModuleName(): string
    {
        return 'your_module'; // e.g., 'loans', 'savings', etc.
    }
    
    public function render()
    {
        return view('livewire.your-module.view', $this->permissions);
    }
}
```

## Step-by-Step Implementation

### Step 1: Update Your Livewire Component

#### 1.1 Remove Old Permission Code

**REMOVE** these if you have them:
```php
// Remove these properties
public $canView = false;
public $canEdit = false;
public $canDelete = false;
public $userPermissions = [];

// Remove these methods
private function loadUserPermissions() { ... }
private function hasPermission($permission) { ... }
private function setPermissions() { ... }

// Remove this trait if only used for permissions
use App\Traits\HasRoles;
```

#### 1.2 Add New Permission System

**ADD** the new trait and setup:
```php
<?php

namespace App\Http\Livewire\YourModule;

use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;

class YourComponent extends Component
{
    use WithModulePermissions;
    
    // Your existing properties here
    public $search = '';
    public $selectedItem;
    // etc...
    
    public function mount()
    {
        // Initialize permissions FIRST
        $this->initializeWithModulePermissions();
        
        // Your other mount logic
        $this->loadInitialData();
    }
    
    /**
     * IMPORTANT: Override this to specify your module name
     * The module name MUST match the prefix in your permissions
     * e.g., if permissions are loans.view, loans.create, then module = 'loans'
     */
    protected function getModuleName(): string
    {
        return 'your_module'; // CHANGE THIS to your actual module name
    }
}
```

### Step 2: Update Your Methods with Permission Checks

#### 2.1 For User-Facing Actions (Shows Error Messages)

Use `authorize()` for actions triggered by users:

```php
public function create()
{
    // This will show an error message if unauthorized
    if (!$this->authorize('create')) {
        return;
    }
    
    // Your create logic here
}

public function edit($id)
{
    // Custom error message
    if (!$this->authorize('edit', 'You cannot edit this record')) {
        return;
    }
    
    // Your edit logic here
}

public function delete($id)
{
    if (!$this->authorize('delete')) {
        return;
    }
    
    // Your delete logic here
}
```

#### 2.2 For Silent Checks (No Error Messages)

Use `can()` for internal checks:

```php
public function loadData()
{
    // Silent check - no error message shown
    if ($this->can('view')) {
        $this->data = Model::all();
    } else {
        $this->data = collect();
    }
}

public function processAction()
{
    // Check multiple permissions
    if ($this->canAny(['edit', 'approve'])) {
        // User has at least one permission
    }
    
    if ($this->canAll(['view', 'export'])) {
        // User has all permissions
    }
}
```

### Step 3: Update Your Render Method

Pass permissions to your view:

```php
public function render()
{
    $data = $this->loadData();
    
    // Method 1: Merge permissions with other data
    return view('livewire.your-module.index', array_merge(
        $this->permissions,  // Adds canView, canEdit, etc.
        [
            'data' => $data,
            'otherVariable' => $value,
            'permissions' => $this->permissions // Also pass full array
        ]
    ));
    
    // OR Method 2: Just pass permissions array
    return view('livewire.your-module.index', [
        'permissions' => $this->permissions,
        'data' => $data
    ]);
}
```

### Step 4: Update Your Blade Views

#### 4.1 Using Permission Variables

```blade
{{-- Check individual permissions --}}
@if($permissions['canCreate'] ?? false)
    <button wire:click="openCreateModal" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New
    </button>
@endif

{{-- In tables --}}
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            @if($permissions['canEdit'] ?? false || $permissions['canDelete'] ?? false)
                <th>Actions</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->status }}</td>
                @if($permissions['canEdit'] ?? false || $permissions['canDelete'] ?? false)
                    <td>
                        @if($permissions['canView'] ?? false)
                            <button wire:click="view({{ $item->id }})" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endif
                        
                        @if($permissions['canEdit'] ?? false)
                            <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                        @endif
                        
                        @if($permissions['canDelete'] ?? false)
                            <button wire:click="confirmDelete({{ $item->id }})" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
```

#### 4.2 Using Blade Directives

```blade
{{-- Single permission check --}}
@canModule('loans', 'create')
    <button wire:click="createLoan">Create Loan</button>
@endcanModule

{{-- Check if user has any module access --}}
@hasModuleAccess('loans')
    <div class="loans-section">
        <!-- Show loans interface -->
    </div>
@endhasModuleAccess
```

## Real Example: Implementing for Loans Module

Let's implement permissions for a complete Loans module:

### 1. Loans Component

```php
<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Loan;
use App\Traits\Livewire\WithModulePermissions;
use Illuminate\Support\Facades\Log;

class LoansManagement extends Component
{
    use WithPagination, WithModulePermissions;
    
    // Component properties
    public $search = '';
    public $selectedLoan;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showApprovalModal = false;
    
    // Form fields
    public $client_id;
    public $amount;
    public $interest_rate;
    public $duration;
    public $purpose;
    
    public function mount()
    {
        // Initialize permissions first
        $this->initializeWithModulePermissions();
        
        Log::info('Loans module initialized', [
            'user_id' => auth()->id(),
            'permissions' => $this->permissions
        ]);
    }
    
    /**
     * Specify the module name for permissions
     */
    protected function getModuleName(): string
    {
        return 'loans'; // Matches loans.* permissions
    }
    
    public function createLoan()
    {
        if (!$this->authorize('create', 'You cannot create loans')) {
            return;
        }
        
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function saveLoan()
    {
        if (!$this->authorize('create')) {
            return;
        }
        
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1000',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'duration' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500'
        ]);
        
        Loan::create([
            'client_id' => $this->client_id,
            'amount' => $this->amount,
            'interest_rate' => $this->interest_rate,
            'duration' => $this->duration,
            'purpose' => $this->purpose,
            'status' => 'PENDING',
            'created_by' => auth()->id()
        ]);
        
        $this->showCreateModal = false;
        session()->flash('success', 'Loan application created successfully');
    }
    
    public function editLoan($id)
    {
        if (!$this->authorize('edit', 'You cannot edit loans')) {
            return;
        }
        
        $this->selectedLoan = Loan::findOrFail($id);
        $this->fillForm($this->selectedLoan);
        $this->showEditModal = true;
    }
    
    public function approveLoan($id)
    {
        if (!$this->authorize('approve', 'You cannot approve loans')) {
            return;
        }
        
        $loan = Loan::findOrFail($id);
        
        if ($loan->status !== 'PENDING') {
            session()->flash('error', 'Only pending loans can be approved');
            return;
        }
        
        $loan->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        
        session()->flash('success', 'Loan approved successfully');
    }
    
    public function disburseLoan($id)
    {
        if (!$this->authorize('disburse', 'You cannot disburse loans')) {
            return;
        }
        
        $loan = Loan::findOrFail($id);
        
        if ($loan->status !== 'APPROVED') {
            session()->flash('error', 'Only approved loans can be disbursed');
            return;
        }
        
        // Disbursement logic here
        $loan->update([
            'status' => 'DISBURSED',
            'disbursed_by' => auth()->id(),
            'disbursed_at' => now()
        ]);
        
        session()->flash('success', 'Loan disbursed successfully');
    }
    
    public function exportLoans()
    {
        if (!$this->authorize('export', 'You cannot export loan data')) {
            return;
        }
        
        // Export logic here
    }
    
    private function resetForm()
    {
        $this->reset(['client_id', 'amount', 'interest_rate', 'duration', 'purpose']);
    }
    
    private function fillForm($loan)
    {
        $this->client_id = $loan->client_id;
        $this->amount = $loan->amount;
        $this->interest_rate = $loan->interest_rate;
        $this->duration = $loan->duration;
        $this->purpose = $loan->purpose;
    }
    
    public function render()
    {
        $loans = Loan::query()
            ->when($this->search, function ($query) {
                $query->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('client', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->when(!$this->can('view'), function ($query) {
                // If user can't view all loans, show only their own
                $query->where('created_by', auth()->id());
            })
            ->paginate(10);
        
        return view('livewire.loans.loans-management', array_merge(
            $this->permissions,
            [
                'loans' => $loans,
                'permissions' => $this->permissions
            ]
        ));
    }
}
```

### 2. Loans Blade View

```blade
{{-- resources/views/livewire/loans/loans-management.blade.php --}}
<div class="p-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Loans Management</h2>
        
        @if($permissions['canCreate'] ?? false)
            <button wire:click="createLoan" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> New Loan Application
            </button>
        @endif
    </div>
    
    {{-- Search --}}
    @if($permissions['canView'] ?? false)
        <div class="mb-4">
            <input type="text" 
                   wire:model.debounce.300ms="search" 
                   placeholder="Search loans..."
                   class="w-full md:w-1/3 px-4 py-2 border rounded-lg">
        </div>
    @endif
    
    {{-- Loans Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">Loan ID</th>
                    <th class="px-6 py-3 text-left">Client</th>
                    <th class="px-6 py-3 text-left">Amount</th>
                    <th class="px-6 py-3 text-left">Interest Rate</th>
                    <th class="px-6 py-3 text-left">Duration</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    @if($permissions['canEdit'] ?? false || 
                        $permissions['canApprove'] ?? false || 
                        $permissions['canDisburse'] ?? false)
                        <th class="px-6 py-3 text-left">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($loans as $loan)
                    <tr>
                        <td class="px-6 py-4">{{ $loan->id }}</td>
                        <td class="px-6 py-4">{{ $loan->client->name }}</td>
                        <td class="px-6 py-4">{{ number_format($loan->amount, 2) }}</td>
                        <td class="px-6 py-4">{{ $loan->interest_rate }}%</td>
                        <td class="px-6 py-4">{{ $loan->duration }} months</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($loan->status === 'APPROVED') bg-green-100 text-green-800
                                @elseif($loan->status === 'PENDING') bg-yellow-100 text-yellow-800
                                @elseif($loan->status === 'DISBURSED') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $loan->status }}
                            </span>
                        </td>
                        @if($permissions['canEdit'] ?? false || 
                            $permissions['canApprove'] ?? false || 
                            $permissions['canDisburse'] ?? false)
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    @if($permissions['canView'] ?? false)
                                        <button wire:click="viewLoan({{ $loan->id }})" 
                                                class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    
                                    @if($permissions['canEdit'] ?? false && $loan->status === 'PENDING')
                                        <button wire:click="editLoan({{ $loan->id }})" 
                                                class="text-yellow-600 hover:text-yellow-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif
                                    
                                    @if($permissions['canApprove'] ?? false && $loan->status === 'PENDING')
                                        <button wire:click="approveLoan({{ $loan->id }})" 
                                                class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    
                                    @if($permissions['canDisburse'] ?? false && $loan->status === 'APPROVED')
                                        <button wire:click="disburseLoan({{ $loan->id }})" 
                                                class="text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No loans found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Pagination --}}
        <div class="px-6 py-4 border-t">
            {{ $loans->links() }}
        </div>
    </div>
    
    {{-- Export Button --}}
    @if($permissions['canExport'] ?? false)
        <div class="mt-4">
            <button wire:click="exportLoans" class="btn btn-secondary">
                <i class="fas fa-download mr-2"></i> Export to Excel
            </button>
        </div>
    @endif
</div>
```

## Common Patterns

### Pattern 1: Conditional Data Loading

```php
public function loadDashboardData()
{
    $data = [];
    
    if ($this->can('view')) {
        $data['records'] = Model::all();
    }
    
    if ($this->can('view_reports')) {
        $data['reports'] = $this->generateReports();
    }
    
    if ($this->can('view_analytics')) {
        $data['analytics'] = $this->getAnalytics();
    }
    
    return $data;
}
```

### Pattern 2: Multi-Level Permission Checks

```php
public function performAction($id)
{
    // Check basic permission first
    if (!$this->can('edit')) {
        session()->flash('error', 'No edit permission');
        return;
    }
    
    $record = Model::find($id);
    
    // Additional business logic checks
    if ($record->status === 'LOCKED' && !$this->can('manage_locked')) {
        session()->flash('error', 'Cannot edit locked records');
        return;
    }
    
    // Proceed with action
}
```

### Pattern 3: Dynamic UI Based on Permissions

```blade
<div class="toolbar">
    @if($permissions['canCreate'] ?? false)
        <button class="btn-create">Create</button>
    @endif
    
    @if($permissions['canExport'] ?? false)
        <button class="btn-export">Export</button>
    @endif
    
    @if($permissions['canApprove'] ?? false)
        <button class="btn-bulk-approve">Bulk Approve</button>
    @endif
    
    @if(!$permissions['hasAnyPermission'] ?? true)
        <p class="text-gray-500">You have view-only access</p>
    @endif
</div>
```

## Troubleshooting

### Issue 1: Property Conflict Error
**Error**: "define the same property ($module) in the composition"

**Solution**: Don't define `protected $module` as a property. Override `getModuleName()` instead:
```php
// WRONG
protected $module = 'loans';

// CORRECT
protected function getModuleName(): string
{
    return 'loans';
}
```

### Issue 2: Permissions Not Loading
**Check**:
1. User has roles assigned
2. Roles have permissions
3. Module name matches permission prefix
4. Called `initializeWithModulePermissions()` in mount()

### Issue 3: Permission Always False
**Debug**:
```php
public function mount()
{
    $this->initializeWithModulePermissions();
    
    // Debug permissions
    \Log::info('Permissions loaded', [
        'module' => $this->getModuleName(),
        'permissions' => $this->permissions,
        'user_id' => auth()->id()
    ]);
}
```

### Issue 4: Cache Not Updating
**Solution**: Clear cache after permission changes
```bash
php artisan cache:clear
```

Or programmatically:
```php
app(PermissionService::class)->clearCache();
```

## Best Practices

### 1. Permission Naming Convention
Always use `module.action` format:
- ✅ `loans.create`
- ✅ `loans.approve`
- ❌ `create_loans`
- ❌ `loan_creation`

### 2. Initialize Early
Always initialize permissions first in `mount()`:
```php
public function mount()
{
    $this->initializeWithModulePermissions(); // FIRST
    $this->loadOtherData(); // THEN other logic
}
```

### 3. Use Appropriate Methods
- `authorize()` - For user actions (shows messages)
- `can()` - For internal checks (silent)
- `canAny()` - For OR conditions
- `canAll()` - For AND conditions

### 4. Handle No Permissions Gracefully
```blade
@if($permissions['hasAnyPermission'] ?? false)
    {{-- Show module interface --}}
@else
    <div class="alert alert-warning">
        You don't have permission to access this module.
    </div>
@endif
```

### 5. Document Custom Permissions
```php
/**
 * Module: loans
 * Required Permissions:
 * - loans.view: View loan list
 * - loans.create: Create new loan applications
 * - loans.edit: Edit existing loans
 * - loans.approve: Approve loan applications
 * - loans.disburse: Disburse approved loans
 * - loans.write_off: Write off bad loans
 * - loans.restructure: Restructure existing loans
 */
```

### 6. Test Permissions
Create a test command:
```php
php artisan make:command TestModulePermissions
```

```php
public function handle()
{
    $user = User::find($this->argument('user_id'));
    $module = $this->argument('module');
    
    $service = new PermissionService($user);
    $permissions = $service->getModulePermissions($module);
    
    $this->info("User: {$user->name}");
    $this->info("Module: {$module}");
    $this->table(['Permission', 'Has Access'], 
        collect($permissions)->map(fn($p) => [$p, '✓'])->toArray()
    );
}
```

## Module Names Reference

Use these exact module names:
- `branches` - Branch management
- `clients` - Client/Member management  
- `loans` - Loan management
- `savings` - Savings accounts
- `shares` - Share management
- `deposits` - Fixed deposits
- `accounting` - Accounting operations
- `reports` - Report generation
- `users` - User management
- `hr` - Human resources
- `payments` - Payment processing
- `transactions` - Transaction management
- `approvals` - Approval workflows
- `teller` - Teller operations
- `reconciliation` - Reconciliation
- `budget` - Budget management
- `expenses` - Expense management
- `procurement` - Procurement
- `insurance` - Insurance management
- `investment` - Investment management
- `billing` - Billing operations
- `email` - Email management
- `subscriptions` - Subscription management
- `products` - Product management
- `cash_management` - Cash management
- `members_portal` - Member portal
- `self_services` - Self-service
- `management` - Management dashboard
- `active_loans` - Active loan monitoring
- `profile` - User profile
- `dashboard` - Dashboard access
- `system` - System administration

## Summary Checklist

When implementing permissions for a module:

- [ ] Add `WithModulePermissions` trait
- [ ] Call `initializeWithModulePermissions()` in `mount()`
- [ ] Override `getModuleName()` method
- [ ] Update action methods with `authorize()` or `can()`
- [ ] Pass permissions to view in `render()`
- [ ] Update blade views to use `$permissions` array
- [ ] Remove old permission code
- [ ] Test with different user roles
- [ ] Clear cache after changes
- [ ] Document module permissions

---

*Last Updated: September 2025*
*System: SACCOS Core System*
*Version: 1.0*