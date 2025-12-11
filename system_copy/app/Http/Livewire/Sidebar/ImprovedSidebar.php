<?php

namespace App\Http\Livewire\Sidebar;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ImprovedSidebar extends Component
{
    public $tab_id = 0;
    public $currentUserId = null;
    public $currentUserRoles;
    public array $visibleMenus = [];
    public array $menuGroups = [];

    protected $listeners = ['refreshSidebar' => 'loadVisibleMenus'];

    public function mount()
    {
        try {
            $this->currentUserId = Auth::id();
            $this->loadVisibleMenus();
        } catch (\Exception $e) {
            Log::error('Error in ImprovedSidebar mount', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error loading sidebar menu items.');
        }
    }

    /**
     * Load menus that the user has permissions to access
     */
    public function loadVisibleMenus()
    {
        try {
            $user = Auth::user();
            $this->currentUserId = $user->id;
            
            // Cache the menu visibility per user for performance
            $cacheKey = "user_visible_menus_{$this->currentUserId}";
            
            $this->visibleMenus = Cache::remember($cacheKey, 300, function () use ($user) {
                $visibleMenuIds = [];
                
                // Get all user's roles
                $userRoles = $user->roles()->with('permissions')->get();
                
                // Collect all permissions from all roles
                $allPermissions = collect();
                foreach ($userRoles as $role) {
                    $allPermissions = $allPermissions->merge($role->permissions);
                }
                
                // Get unique permission IDs
                $permissionIds = $allPermissions->pluck('id')->unique()->toArray();
                
                if (empty($permissionIds)) {
                    Log::warning("User {$user->id} has no permissions assigned");
                    return [];
                }
                
                // Find menus associated with these permissions
                $menuIds = DB::table('menu_permissions')
                    ->whereIn('permission_id', $permissionIds)
                    ->pluck('menu_id')
                    ->unique()
                    ->toArray();
                
                // Also check permissions by module matching
                $permissionModules = $allPermissions->pluck('module')->unique();
                
                // Get all menus
                $allMenus = Menu::all();
                
                foreach ($allMenus as $menu) {
                    // Check if menu ID is in the mapped permissions
                    if (in_array($menu->id, $menuIds)) {
                        $visibleMenuIds[] = $menu->id;
                        continue;
                    }
                    
                    // Check by menu name to module mapping
                    $menuNameLower = strtolower($menu->menu_name);
                    foreach ($permissionModules as $module) {
                        $moduleLower = strtolower($module);
                        
                        // Direct match or partial match
                        if (str_contains($menuNameLower, $moduleLower) || 
                            str_contains($moduleLower, $menuNameLower)) {
                            $visibleMenuIds[] = $menu->id;
                            break;
                        }
                        
                        // Special mappings
                        $specialMappings = [
                            'clients/members' => 'members',
                            'human resources' => 'hr',
                            'user management' => 'users',
                            'active loans' => 'loans',
                        ];
                        
                        foreach ($specialMappings as $menuPattern => $modulePattern) {
                            if (str_contains($menuNameLower, $menuPattern) && 
                                str_contains($moduleLower, $modulePattern)) {
                                $visibleMenuIds[] = $menu->id;
                                break 2;
                            }
                        }
                    }
                }
                
                // Always include Dashboard (menu_number = 0 or menu_id for dashboard)
                $dashboardMenu = Menu::where('menu_number', 0)
                    ->orWhere('menu_name', 'LIKE', '%Dashboard%')
                    ->first();
                    
                if ($dashboardMenu && !in_array($dashboardMenu->id, $visibleMenuIds)) {
                    // Only add dashboard if user has at least one permission
                    if (!empty($permissionIds)) {
                        $visibleMenuIds[] = $dashboardMenu->id;
                    }
                }
                
                return array_unique($visibleMenuIds);
            });
            
            // Group menus for display
            $this->groupMenus();
            
            Log::info("User {$this->currentUserId} has access to " . count($this->visibleMenus) . " menus");
            
        } catch (\Exception $e) {
            Log::error('Error loading visible menus', [
                'error' => $e->getMessage(),
                'user_id' => $this->currentUserId
            ]);
            $this->visibleMenus = [];
        }
    }
    
    /**
     * Group menus by category
     */
    private function groupMenus()
    {
        try {
            $groups = [
                'Core Operations' => [],
                'Financial Management' => [],
                'Administration' => [],
                'Reports & Analytics' => [],
                'System' => []
            ];
            
            $menuCategorization = [
                'Core Operations' => ['Members', 'Shares', 'Savings', 'Deposits', 'Loans', 'Active Loans'],
                'Financial Management' => ['Accounting', 'Payments', 'Billing', 'Cash Management', 'Teller', 'Investment', 'Insurance'],
                'Administration' => ['Branches', 'Human Resources', 'Procurement', 'Expenses', 'Budget'],
                'Reports & Analytics' => ['Reports', 'Reconciliation', 'Management', 'Dashboard'],
                'System' => ['Users', 'Profile', 'Approvals', 'Services', 'Products', 'Email', 'Transactions', 'Subscriptions', 'Self Services']
            ];
            
            foreach ($this->visibleMenus as $menuId) {
                $menu = Menu::find($menuId);
                if (!$menu) continue;
                
                $placed = false;
                foreach ($menuCategorization as $category => $menuNames) {
                    foreach ($menuNames as $menuName) {
                        if (stripos($menu->menu_name, $menuName) !== false) {
                            $groups[$category][] = $menuId;
                            $placed = true;
                            break 2;
                        }
                    }
                }
                
                // If not categorized, put in System
                if (!$placed) {
                    $groups['System'][] = $menuId;
                }
            }
            
            // Remove empty groups
            $this->menuGroups = array_filter($groups, function($items) {
                return !empty($items);
            });
            
        } catch (\Exception $e) {
            Log::error('Error grouping menus', ['error' => $e->getMessage()]);
            $this->menuGroups = [];
        }
    }

    /**
     * Handle menu item click
     */
    public function menuItemClicked($menuId)
    {
        try {
            // Verify user has permission to access this menu
            if (!in_array($menuId, $this->visibleMenus) && $menuId != 0) {
                Log::warning("User {$this->currentUserId} attempted to access unauthorized menu {$menuId}");
                session()->flash('error', 'You do not have permission to access this menu.');
                return;
            }
            
            // Get menu details
            $menu = $menuId == 0 ? null : Menu::find($menuId);
            
            // Store active menu context
            session()->put('active_menu_id', $menuId);
            $this->tab_id = $menuId;
            
            // Emit event to System component
            $this->emit('menuItemClicked', $menuId);
            
            Log::info('Menu item clicked', [
                'menu_id' => $menuId,
                'menu_name' => $menu ? $menu->menu_name : 'Dashboard',
                'user_id' => $this->currentUserId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in menuItemClicked', [
                'error' => $e->getMessage(),
                'menu_id' => $menuId
            ]);
            session()->flash('error', 'Failed to load menu item.');
        }
    }
    
    /**
     * Clear menu cache for current user
     */
    public function clearMenuCache()
    {
        $cacheKey = "user_visible_menus_{$this->currentUserId}";
        Cache::forget($cacheKey);
        $this->loadVisibleMenus();
    }

    public function render()
    {
        try {
            return view('livewire.sidebar.improved-sidebar', [
                'menus' => Menu::whereIn('id', $this->visibleMenus)->get(),
                'menuGroups' => $this->menuGroups
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ImprovedSidebar render', ['error' => $e->getMessage()]);
            return view('livewire.sidebar.improved-sidebar', [
                'menus' => collect(),
                'menuGroups' => []
            ]);
        }
    }
}