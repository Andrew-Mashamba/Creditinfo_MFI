<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\MenuService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestAllRolesMenuLoading extends Command
{
    protected $signature = 'test:all-roles-menu';
    protected $description = 'Test menu loading for all roles in the database';

    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        parent::__construct();
        $this->menuService = $menuService;
    }

    public function handle()
    {
        $this->info('Starting menu loading test for all roles...');

        $roles = Role::with('institution')->get();
        $totalRoles = $roles->count();
        $this->info("Found {$totalRoles} roles to test");

        $successCount = 0;
        $failureCount = 0;
        $results = [];

        foreach ($roles as $role) {
            $this->info("\nTesting role: {$role->name} (ID: {$role->id})");
            $this->info("Institution: {$role->institution->name}");

            try {
                $menuItems = $this->menuService->getMenuItemsForRole($role);
                $menuCount = count($menuItems);
                
                $result = [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'institution' => $role->institution->name,
                    'menu_items_count' => $menuCount,
                    'status' => 'success',
                    'menu_items' => $menuItems
                ];
                
                $successCount++;
                $this->info("✓ Success - Found {$menuCount} menu items");
            } catch (\Exception $e) {
                $result = [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'institution' => $role->institution->name,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
                
                $failureCount++;
                $this->error("✗ Failed - {$e->getMessage()}");
            }

            $results[] = $result;
        }

        // Log results
        Log::info('Menu loading test results for all roles', [
            'total_roles' => $totalRoles,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ]);

        // Display summary
        $this->newLine(2);
        $this->info('=== Test Summary ===');
        $this->info("Total roles tested: {$totalRoles}");
        $this->info("Successful tests: {$successCount}");
        $this->info("Failed tests: {$failureCount}");

        if ($failureCount > 0) {
            $this->error("\nFailed roles:");
            foreach ($results as $result) {
                if ($result['status'] === 'failed') {
                    $this->error("- {$result['role_name']} ({$result['institution']}): {$result['error']}");
                }
            }
        }

        return $failureCount === 0 ? 0 : 1;
    }
} 