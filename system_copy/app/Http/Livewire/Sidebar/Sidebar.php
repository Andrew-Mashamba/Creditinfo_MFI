<?php

namespace App\Http\Livewire\Sidebar;

use App\Models\RoleMenuAction;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\MenuService;
use Illuminate\Support\Facades\Session;
class Sidebar extends Component
{
    public $tab_id = 0;
    public $currentUserId = null;
    public \Illuminate\Database\Eloquent\Collection $currentUserRoles;
    public $currentDepartment = null;
    public array $menuItems = [];
    public array $menuGroups = [];

    protected $listeners = ['refreshSidebar' => '$refresh'];

    private MenuService $menuService;

    public function __construct()
    {
        parent::__construct();
        $this->menuService = app(MenuService::class);
    }

    public function mount()
    {
        try {
            $this->loadUserRoles();

            //dd($this->currentUserRoles);
            $this->loadMenuItems();
        } catch (\Exception $e) {
            Log::error('Error in Sidebar mount', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error loading sidebar menu items.');
        }
    }

    protected function loadUserRoles()
    {
        try {
            $this->currentUserRoles = auth()->user()->roles()
                ->with(['permissions', 'department'])
                ->get();

            
            $this->currentDepartment = auth()->user()->department;
        } catch (\Exception $e) {
            Log::error('Error loading user roles', ['error' => $e->getMessage()]);
            $this->currentUserRoles = collect();
            $this->currentDepartment = null;
        }
    }

    public function loadMenuItems()
    {
        try {
            $this->currentUserId = auth()->id();
            $menuItems = collect();

            // Get all permissions for the user through their roles
            $allPermissions = collect();
            foreach ($this->currentUserRoles as $role) {
                $allPermissions = $allPermissions->merge($role->permissions);
            }
            
            // Map permissions to menu IDs based on module/permission patterns
            $permissionToMenuMap = $this->getPermissionToMenuMapping();
            
            // For each permission, add the corresponding menu ID
            foreach ($allPermissions as $permission) {
                $permissionName = $permission->name;
                $permissionPrefix = explode('.', $permissionName)[0]; // Get module prefix
                
                if (isset($permissionToMenuMap[$permissionPrefix])) {
                    $menuId = $permissionToMenuMap[$permissionPrefix];
                    $menuItems->push($menuId);
                }
            }
            
            // Also check the original menu service logic
            foreach ($this->currentUserRoles as $role) {
                $roleMenuItems = $this->menuService->getMenuItemsForRole($role);
                $menuItems = $menuItems->merge($roleMenuItems);
            }

            // Handle permissions with resource_type and resource_id
            $permissions = $this->getUserPermissions();
            foreach ($permissions as $permission) {
                // Handle both array and object types
                if (is_object($permission)) {
                    if (isset($permission->resource_type) && 
                        $permission->resource_type === 'menu' && 
                        isset($permission->resource_id)) {
                        $menuItems->push($permission->resource_id);
                    }
                } elseif (is_array($permission)) {
                    if (isset($permission['resource_type']) && 
                        $permission['resource_type'] === 'menu' && 
                        isset($permission['resource_id'])) {
                        $menuItems->push($permission['resource_id']);
                    }
                }
            }

            // Always include Dashboard (0) if user has any permissions
            if ($allPermissions->isNotEmpty()) {
                $menuItems->push(0); // Dashboard menu ID is 0 based on menu_number
            }

            // Get unique menu IDs
            $uniqueMenuIds = $menuItems->unique()->values()->toArray();
            
            // Sort menu IDs based on menu_number from the database
            $sortedMenus = Menu::whereIn('id', $uniqueMenuIds)
                ->orderBy('menu_number', 'asc')
                ->pluck('id')
                ->toArray();
            
            $this->menuItems = $sortedMenus;
            
            Log::info('Menu items loaded for user', [
                'user_id' => $this->currentUserId,
                'permission_count' => $allPermissions->count(),
                'menu_count' => count($this->menuItems),
                'menu_ids' => $this->menuItems
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading menu items', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error loading menu items.');
        }
    }

    public function menuItemClicked($menuId)
    {
       
        try {
            // Get menu and its actions
            $menu = Menu::find($menuId);
            if (!$menu && $menuId != 0) {
                throw new \Exception("Menu not found");
            }

            // Store active menu context in session
            session()->put('active_menu_id', $menuId);
            // session()->put('tab_id', $menuId);
            $this->tab_id = $menuId;
            
            // Emit event to System component
            $this->emit('menuItemClicked', $menuId);
            
            Log::info('Menu item clicked', [
                'menu_id' => $menuId,
                'menu_name' => $menu->menu_name,
                'user_id' => Auth::id()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in menuItemClicked', [
                'error' => $e->getMessage(),
                'menu_id' => $menuId
            ]);
            session()->flash('error', 'Failed to load menu item. Please try again.');
        }
    }

    private function getAggregatedPermissions($menuId)
    {
        try {
            $permissions = collect();

            foreach ($this->currentUserRoles as $role) {
                // Get direct permissions
                $rolePermissions = $role->permissions()
                    ->where('resource_type', 'menu')
                    ->where('resource_id', $menuId)
                    ->get();

                // Get inherited permissions if enabled
                if ($role->permission_inheritance_enabled) {
                    $inheritedPermissions = $role->getAllPermissions()
                        ->where('resource_type', 'menu')
                        ->where('resource_id', $menuId);
                    $rolePermissions = $rolePermissions->merge($inheritedPermissions);
                }

                // Get department-specific permissions
                if ($role->department_specific && $role->department) {
                    $departmentPermissions = $role->getDepartmentPermissions($role->department)
                        ->where('resource_type', 'menu')
                        ->where('resource_id', $menuId);
                    $rolePermissions = $rolePermissions->merge($departmentPermissions);
                }

                $permissions = $permissions->merge($rolePermissions);
            }

            return $permissions->unique('id')->values()->all();
        } catch (\Exception $e) {
            Log::error('Error getting aggregated permissions', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getUserPermissions(): array
    {
        try {
            // Convert Collection of objects to array, keeping objects as objects
            $permissions = Auth::user()->getAllPermissions();
            return $permissions->all(); // This returns array of objects
        } catch (Exception $e) {
            Log::error('Error fetching user permissions', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get mapping of permission prefixes to menu IDs
     * Maps permission module names to their corresponding menu IDs
     */
    private function getPermissionToMenuMapping(): array
    {
        // Based on the actual menu IDs in the system
        // This maps permission module prefixes to menu IDs
        return [
            'dashboard' => 0,        // Dashboard (menu_number: 0)
            'branches' => 1,         // Branches (menu_number: 1)
            'clients' => 2,          // Members (menu_number: 2)
            'shares' => 3,           // Shares (menu_number: 3)
            'savings' => 4,          // Savings (menu_number: 4)
            'deposits' => 5,         // Deposits (menu_number: 5)
            'loans' => 6,            // Loans (menu_number: 6)
            'products' => 7,         // Products Management (menu_number: 8)
            'accounting' => 8,       // Accounting (menu_number: 7)
            // 'services' => 181,    // Services menu doesn't exist - removed
            'expenses' => 9,         // Expenses (menu_number: 16)
            'payments' => 10,        // Payments (menu_number: 9)
            'investment' => 11,      // Investments (menu_number: 17)
            'procurement' => 12,     // Procurement (menu_number: 14)
            'budget' => 13,          // Budget Management (menu_number: 17)
            'insurance' => 14,       // Insurance (menu_number: 4)
            'teller' => 15,          // Teller Management (menu_number: 18)
            'reconciliation' => 16,  // Reconciliation (menu_number: 12)
            'hr' => 17,              // Human Resources (menu_number: 10)
            'self_services' => 18,   // Self Services (menu_number: 21)
            'approvals' => 19,       // Approvals (menu_number: 49)
            'reports' => 20,         // Reports Manager (menu_number: 13)
            'profile' => 21,         // Profile Settings (menu_number: 19)
            'users' => 22,           // Users Manager (menu_number: 50)
            'active_loans' => 23,    // Active Loans (menu_number: 24)
            'management' => 24,      // Management (menu_number: 3)
            'cash_management' => 26, // Cash Management (menu_number: 11)
            'billing' => 27,         // Billing (menu_number: 15)
            'transactions' => 28,    // Transactions (menu_number: 20)
            'members_portal' => 29,  // Mobile & Web Portal (menu_number: 29)
            'email' => 30,           // Email (menu_number: 30)
            'subscriptions' => 31,   // Subscriptions (menu_number: 31)
            'system' => 0,           // System permissions map to dashboard
        ];
    }

    private function getGroupedMenuItems(): array
    {
        try {
            $groups = [];
            foreach ($this->menuItems as $menuId) {
                $menu = Menu::find($menuId);
                if (!$menu) continue;

                $group = $this->currentDepartment 
                    ? $this->currentDepartment->getFullPath() . ' > ' . $menu->group 
                    : $menu->group;

                if (!isset($groups[$group])) {
                    $groups[$group] = [];
                }
                $groups[$group][] = $menuId;
            }

            return $groups;
        } catch (Exception $e) {
            Log::error('Error grouping menu items', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function render()
    {
        try {
            $user = Auth::user();
            $this->currentUserId = $user->id;
            $this->currentUserRoles = $user->roles;

            if (empty($this->currentUserRoles)) {
                throw new Exception('User roles not found');
            }

            $permissions = $this->getUserPermissions();
            if (!empty($permissions)) {
                session()->put('permissions', $permissions);
                $this->menuGroups = $this->getGroupedMenuItems();
                
                // Handle permissions as objects
                $permissionItems = collect($permissions)->map(function($permission) {
                    if (is_object($permission) && isset($permission->allowed_actions)) {
                        return $permission->allowed_actions;
                    } elseif (is_array($permission) && isset($permission['allowed_actions'])) {
                        return $permission['allowed_actions'];
                    }
                    return null;
                })->filter()
                  ->flatten()
                  ->unique()
                  ->values()
                  ->toArray();
                  
                session()->put('permission_items', $permissionItems);
            }

            return view('livewire.sidebar.sidebar');
        } catch (Exception $e) {
            Log::error('Error in Sidebar render', ['error' => $e->getMessage()]);
            $this->menuItems = [];
            $this->menuGroups = [];
            return view('livewire.sidebar.sidebar');
        }
    }
}
