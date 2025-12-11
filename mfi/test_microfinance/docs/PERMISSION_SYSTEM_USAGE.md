# Permission System Usage Guide

## Overview
The SACCOS Core System uses a centralized permission management system that provides consistent permission checking across all modules.

## Available Permissions for Branches Module
- `branches.view` - View branches
- `branches.create` - Create new branches
- `branches.edit` - Edit branch details
- `branches.delete` - Delete branches
- `branches.activate` - Activate/Deactivate branches
- `branches.assign_users` - Assign users to branches
- `branches.manage_settings` - Manage branch settings

## Implementation in Livewire Components

### 1. Basic Setup in Your Component

```php
<?php

namespace App\Http\Livewire\YourModule;

use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;

class YourComponent extends Component
{
    use WithModulePermissions;
    
    // Define your module name (should match permission prefix)
    protected $module = 'your_module';
    
    public function mount()
    {
        // Initialize permissions for this module
        $this->initializeWithModulePermissions();
    }
    
    public function render()
    {
        // Permissions are automatically passed to the view
        return view('livewire.your-module.your-component', $this->permissions);
    }
}
```

### 2. Checking Permissions in Component Methods

```php
public function createRecord()
{
    // Method 1: Using authorize (shows error message)
    if (!$this->authorize('create')) {
        return;
    }
    
    // Your create logic here
}

public function deleteRecord($id)
{
    // Method 2: Using can (silent check)
    if (!$this->can('delete')) {
        session()->flash('error', 'Unauthorized');
        return;
    }
    
    // Your delete logic here
}

public function performAction()
{
    // Method 3: Check multiple permissions
    if ($this->canAny(['edit', 'approve'])) {
        // User has at least one of these permissions
    }
    
    if ($this->canAll(['view', 'export'])) {
        // User has all of these permissions
    }
}
```

## Usage in Blade Views

### 1. Using Permission Variables

The permission system automatically provides boolean variables for each permission:

```blade
{{-- Check individual permissions --}}
@if($permissions['canCreate'] ?? false)
    <button wire:click="openCreateModal">Create New</button>
@endif

@if($permissions['canEdit'] ?? false)
    <button wire:click="editRecord({{ $id }})">Edit</button>
@endif

@if($permissions['canDelete'] ?? false)
    <button wire:click="deleteRecord({{ $id }})">Delete</button>
@endif

@if($permissions['canView'] ?? false)
    <button wire:click="viewDetails({{ $id }})">View</button>
@endif
```

### 2. Using Blade Directives

Custom Blade directives for cleaner templates:

```blade
{{-- Check single permission --}}
@canModule('branches', 'create')
    <button wire:click="createBranch">Create Branch</button>
@endcanModule

@canModule('branches', 'edit')
    <button wire:click="editBranch({{ $branch->id }})">Edit</button>
@endcanModule

{{-- Check if user has any module access --}}
@hasModuleAccess('branches')
    <div>You have access to branches module</div>
@endhasModuleAccess
```

### 3. Conditional Rendering in Tables

```blade
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            @if($permissions['canEdit'] || $permissions['canDelete'] || $permissions['canView'])
                <th>Actions</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
            <tr>
                <td>{{ $record->name }}</td>
                <td>{{ $record->status }}</td>
                @if($permissions['canEdit'] || $permissions['canDelete'] || $permissions['canView'])
                    <td>
                        @if($permissions['canView'])
                            <button wire:click="view({{ $record->id }})">View</button>
                        @endif
                        @if($permissions['canEdit'])
                            <button wire:click="edit({{ $record->id }})">Edit</button>
                        @endif
                        @if($permissions['canDelete'])
                            <button wire:click="delete({{ $record->id }})">Delete</button>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
```

## Direct Service Usage

For non-Livewire contexts:

```php
use App\Services\PermissionService;

class YourController extends Controller
{
    protected $permissionService;
    
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    
    public function index()
    {
        if (!$this->permissionService->can('branches', 'view')) {
            abort(403);
        }
        
        // Your logic here
    }
}
```

## Available Module Names

Based on the system permissions, use these module names:

- `branches` - Branch management
- `clients` - Member/Client management
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
- `reconciliation` - Reconciliation processes
- `budget` - Budget management
- `expenses` - Expense management
- `procurement` - Procurement processes
- `insurance` - Insurance management
- `investment` - Investment management
- `billing` - Billing operations
- `email` - Email management
- `subscriptions` - Subscription management
- `products` - Product management
- `cash_management` - Cash management
- `members_portal` - Member portal
- `self_services` - Self-service operations
- `management` - Management dashboard
- `active_loans` - Active loan monitoring
- `profile` - User profile management
- `dashboard` - Dashboard access
- `system` - System administration

## Migration Guide for Existing Modules

To migrate an existing module to use the new permission system:

1. **Remove old permission checking code**:
   - Remove manual DB queries for permissions
   - Remove `HasRoles` trait if only used for permissions
   - Remove hardcoded permission arrays

2. **Add the new trait**:
   ```php
   use App\Traits\Livewire\WithModulePermissions;
   ```

3. **Set module name**:
   ```php
   protected $module = 'your_module_name';
   ```

4. **Initialize in mount()**:
   ```php
   public function mount()
   {
       $this->initializeWithModulePermissions();
       // Your other mount logic
   }
   ```

5. **Update method permission checks**:
   ```php
   // Old way
   if (!$this->hasPermission('create')) { ... }
   
   // New way
   if (!$this->authorize('create')) { ... }
   ```

6. **Update views**:
   ```blade
   {{-- Old way --}}
   @if($canCreate)
   
   {{-- New way --}}
   @if($permissions['canCreate'] ?? false)
   ```

## Performance Considerations

- Permissions are cached for 5 minutes per user
- Cache is automatically cleared when permissions are updated
- Use `$this->refreshPermissions()` to manually refresh cache in Livewire components

## Troubleshooting

1. **No permissions showing**: Check if user has roles and permissions assigned
2. **Cache issues**: Clear cache with `php artisan cache:clear`
3. **Module not found**: Ensure module name matches permission prefix in database
4. **Permission denied**: Check database for actual permission records

## Best Practices

1. Always use the centralized permission service
2. Use descriptive permission names following the pattern: `module.action`
3. Group related permissions by module
4. Use `authorize()` for user-facing actions (shows error messages)
5. Use `can()` for internal checks (silent)
6. Cache permissions appropriately
7. Test permission changes thoroughly
8. Document custom permissions in your module

## Example: Complete Branches Module Implementation

See `/app/Http/Livewire/Branches/Branches.php` for a complete implementation example that demonstrates:
- Module initialization
- Permission checking in methods
- Passing permissions to views
- Error handling
- Cache management