<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuPermissionMappingSeeder extends Seeder
{
    /**
     * Map menus to their corresponding permissions based on module names
     */
    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Define menu to permission module mapping
            $menuPermissionMapping = [
                'Dashboard' => 'dashboard', // Menu ID 0 (special case)
                'Branches' => 'branches',
                'Members' => 'clients', // Members menu maps to clients permissions
                'Shares' => 'shares',
                'Savings' => 'savings',
                'Deposits' => 'deposits',
                'Loans' => 'loans',
                'Products Management' => 'products',
                'Accounting' => 'accounting',
                'Services' => 'services',
                'Expenses' => 'expenses',
                'Payments' => 'payments',
                'Investment' => 'investment',
                'Procurement' => 'procurement',
                'Budget Management' => 'budget',
                'Insurance' => 'insurance',
                'Teller Management' => 'teller',
                'Reconciliation' => 'reconciliation',
                'Human Resources' => 'hr',
                'Self Services' => 'self_services',
                'Approvals' => 'approvals',
                'Reports' => 'reports',
                'Profile' => 'profile',
                'Profile Setting' => 'profile', // Alternative name
                'User Management' => 'users',
                'Users' => 'users', // Alternative name
                'Active Loans' => 'active_loans',
                'Management' => 'management',
                'Cash Management' => 'cash_management',
                'Billing' => 'billing',
                'Transactions' => 'transactions',
                'Members Portal' => 'members_portal',
                'Email' => 'email',
                'Subscriptions' => 'subscriptions',
            ];
            
            // Create or update menu_permissions pivot table
            DB::statement('CREATE TABLE IF NOT EXISTS menu_permissions (
                id SERIAL PRIMARY KEY,
                menu_id INTEGER, -- Nullable to handle dashboard (0) or create a dashboard menu
                permission_id INTEGER NOT NULL,
                menu_identifier VARCHAR(100), -- For special cases like dashboard
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(menu_id, permission_id),
                FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            )');
            
            // Clear existing mappings
            DB::table('menu_permissions')->truncate();
            
            $mappingCount = 0;
            
            foreach ($menuPermissionMapping as $menuName => $permissionPrefix) {
                // Find the menu
                $menu = Menu::where('menu_name', 'LIKE', '%' . $menuName . '%')
                    ->orWhere('menu_title', 'LIKE', '%' . $menuName . '%')
                    ->first();
                    
                if (!$menu) {
                    if ($this->command) {
                        $this->command->warn("Menu not found: {$menuName}");
                    }
                    continue;
                }
                
                // Find all permissions for this module
                $permissions = Permission::where('name', 'LIKE', $permissionPrefix . '.%')
                    ->get();
                    
                if ($permissions->isEmpty()) {
                    if ($this->command) {
                        $this->command->warn("No permissions found for module: {$permissionPrefix}");
                    }
                    continue;
                }
                
                // Map each permission to the menu
                foreach ($permissions as $permission) {
                    DB::table('menu_permissions')->insertOrIgnore([
                        'menu_id' => $menu->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $mappingCount++;
                }
                
                if ($this->command) {
                    $this->command->info("Mapped {$permissions->count()} permissions to menu: {$menu->menu_name}");
                }
            }
            
            // Handle Dashboard (menu_number = 0) special case
            // First, try to find or create a Dashboard menu entry
            $dashboardMenu = Menu::where('menu_number', 0)
                ->orWhere('menu_name', 'Dashboard')
                ->orWhere('menu_name', 'LIKE', '%Dashboard%')
                ->first();
                
            if (!$dashboardMenu) {
                // Create a new Dashboard menu with a unique ID
                $maxId = Menu::max('id') ?? 0;
                $dashboardMenu = Menu::create([
                    'id' => $maxId + 1,
                    'menu_number' => 0,
                    'menu_name' => 'Dashboard',
                    'menu_title' => 'Dashboard',
                    'menu_description' => 'System Dashboard',
                    'status' => 'ACTIVE',
                    'route' => 'dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'order' => 0
                ]);
            }
            
            $dashboardPermissions = Permission::where('name', 'LIKE', 'dashboard.%')->get();
            if ($dashboardPermissions->isNotEmpty()) {
                foreach ($dashboardPermissions as $permission) {
                    DB::table('menu_permissions')->insertOrIgnore([
                        'menu_id' => $dashboardMenu->id,
                        'permission_id' => $permission->id,
                        'menu_identifier' => 'dashboard',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $mappingCount++;
                }
                
                if ($this->command) {
                    $this->command->info("Mapped {$dashboardPermissions->count()} permissions to Dashboard (menu_id: {$dashboardMenu->id})");
                }
            }
            
            // Add index for performance
            DB::statement('CREATE INDEX IF NOT EXISTS idx_menu_permissions_menu_id ON menu_permissions(menu_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_menu_permissions_permission_id ON menu_permissions(permission_id)');
            
            DB::commit();
            
            if ($this->command) {
                $this->command->info("✅ Menu-Permission mapping completed successfully!");
                $this->command->info("Total mappings created: {$mappingCount}");
                
                // Show summary
                $menuCount = DB::table('menu_permissions')
                    ->select('menu_id')
                    ->distinct()
                    ->count();
                    
                $this->command->info("Menus with permissions: {$menuCount}");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error mapping menu permissions: ' . $e->getMessage());
            if ($this->command) {
                $this->command->error('Error mapping menu permissions: ' . $e->getMessage());
            }
            throw $e;
        }
    }
}