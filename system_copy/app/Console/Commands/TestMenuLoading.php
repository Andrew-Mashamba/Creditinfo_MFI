<?php

namespace App\Console\Commands;

use App\Http\Livewire\Sidebar\Sidebar;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\MenuService;

class TestMenuLoading extends Command
{
    protected $signature = 'test:menu-loading {--role= : Specific role name to test} {--all : Test all roles}';
    protected $description = 'Test menu loading functionality for roles';

    public function handle()
    {
        $this->info('Starting menu loading tests...');

        if ($this->option('all')) {
            $this->testAllRoles();
        } elseif ($roleName = $this->option('role')) {
            $this->testSpecificRole($roleName);
        } else {
            $this->testDefaultRoles();
        }

        $this->info('Menu loading tests completed.');
    }

    private function testAllRoles()
    {
        $roles = Role::all();
        $this->info("Testing all roles ({$roles->count()} roles found)");

        foreach ($roles as $role) {
            $this->testRole($role);
        }
    }

    private function testDefaultRoles()
    {
        $this->info('Testing default test roles...');

        $testRoles = [
            'Test Parent Role',
            'Test Child Role',
            'Test Department Role'
        ];

        foreach ($testRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $this->testRole($role);
            } else {
                $this->warn("Role '{$roleName}' not found. Run MenuTestSeeder first.");
            }
        }
    }

    private function testSpecificRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("Role '{$roleName}' not found.");
            return;
        }

        $this->testRole($role);
    }

    private function testRole(Role $role)
    {
        $this->info("\nTesting role: {$role->name}");
        $this->line("Role ID: {$role->id}");
        $this->line("Permission Inheritance: " . ($role->permission_inheritance_enabled ? 'Enabled' : 'Disabled'));
        $this->line("Department Specific: " . ($role->department_specific ? 'Yes' : 'No'));
        
        if ($role->parent_role_id) {
            $parentRole = Role::find($role->parent_role_id);
            $this->line("Parent Role: {$parentRole->name} (ID: {$parentRole->id})");
        }

        // Test menu loading
        $menuService = app(MenuService::class);
        $menuItems = $menuService->getMenuItemsForRole($role);

        $this->info("\nMenu Items Results:");
        $this->line("Total Menu Items: {$menuItems->count()}");

        // Display menu details
        if ($menuItems->count() > 0) {
            $this->info("\nMenu Details:");
            $headers = ['ID', 'Name'];
            $rows = [];

            foreach ($menuItems as $menuId) {
                $menu = \App\Models\Menu::find($menuId);
                if ($menu) {
                    $rows[] = [
                        $menu->id,
                        $menu->menu_name
                    ];
                }
            }

            $this->table($headers, $rows);
        }

        // Check role menu actions
        $roleMenuActions = $role->menuActions()->with(['menu', 'menuAction'])->get();
        $this->info("\nRole Menu Actions:");
        $this->line("Total Actions: {$roleMenuActions->count()}");

        // Check permissions
        $permissions = $role->permissions;
        $this->info("\nRole Permissions:");
        $this->line("Total Permissions: {$permissions->count()}");

        // Log test results
        Log::info('Menu loading test completed for role', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'menu_items_count' => $menuItems->count(),
            'menu_actions_count' => $roleMenuActions->count(),
            'permissions_count' => $permissions->count()
        ]);

        $this->line(str_repeat('-', 50));
    }
} 