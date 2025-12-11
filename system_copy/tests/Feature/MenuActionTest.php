<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_actions_are_properly_seeded()
    {
        // Run the seeders
        $this->seed([
            \Database\Seeders\InstitutionSeeder::class,
            \Database\Seeders\MenuSeeder::class,
            \Database\Seeders\MenuActionsSeeder::class,
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\SubRoleSeeder::class,
            \Database\Seeders\RoleMenuActionSeeder::class,
        ]);

        // Check if menu_actions table has data
        $menuActionsCount = DB::table('menu_actions')->count();
        $this->assertGreaterThan(0, $menuActionsCount, 'Menu actions table should not be empty');

        // Check if role_menu_actions table has data for System Administrator
        $systemAdminRole = DB::table('roles')
            ->where('name', 'System Administrator')
            ->first();
        
        $this->assertNotNull($systemAdminRole, 'System Administrator role should exist');

        $roleMenuActions = DB::table('role_menu_actions')
            ->where('sub_role', 'Systems Administrators')
            ->count();

        $this->assertGreaterThan(0, $roleMenuActions, 'System Administrator should have menu actions assigned');

        // Log the actual counts for debugging
        Log::info('Menu actions count', ['count' => $menuActionsCount]);
        Log::info('Role menu actions count', ['count' => $roleMenuActions]);
    }

    public function test_system_administrator_has_all_permissions()
    {
        // Run the seeders
        $this->seed([
            \Database\Seeders\InstitutionSeeder::class,
            \Database\Seeders\MenuSeeder::class,
            \Database\Seeders\MenuActionsSeeder::class,
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\SubRoleSeeder::class,
            \Database\Seeders\RoleMenuActionSeeder::class,
        ]);

        // Get all menu actions for System Administrator
        $systemAdminActions = DB::table('role_menu_actions')
            ->where('sub_role', 'Systems Administrators')
            ->get();

        // Verify that each menu has the full set of permissions
        foreach ($systemAdminActions as $action) {
            $allowedActions = json_decode($action->allowed_actions, true);
            $this->assertContains('view', $allowedActions, 'System Administrator should have view permission');
            $this->assertContains('create', $allowedActions, 'System Administrator should have create permission');
            $this->assertContains('edit', $allowedActions, 'System Administrator should have edit permission');
            $this->assertContains('delete', $allowedActions, 'System Administrator should have delete permission');
            $this->assertContains('approve', $allowedActions, 'System Administrator should have approve permission');
            $this->assertContains('reject', $allowedActions, 'System Administrator should have reject permission');
        }
    }
} 