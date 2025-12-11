<?php

/**
 * Grant All Menu Permissions to System Administrator Role
 * 
 * This script grants full access to all menus in the system for the System Administrator role.
 * It can be run whenever you need to reset or update admin permissions.
 * 
 * Usage:
 * 1. Via Artisan Tinker: php artisan tinker --file=database/scripts/grant_admin_permissions.php
 * 2. Via PHP: php database/scripts/grant_admin_permissions.php
 */

// Check if we're running in Tinker or standalone
if (!function_exists('app')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $app = require_once __DIR__ . '/../../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
}

use Illuminate\Support\Facades\DB;

function grantAdminPermissions() {
    try {
        echo "\n====================================\n";
        echo "Grant Admin Permissions Script\n";
        echo "====================================\n\n";
        
        // Get all menu IDs with their names for logging
        $menus = DB::table('menus')->select('id', 'menu_name')->get();
        
        if ($menus->isEmpty()) {
            echo "❌ No menus found in the database. Please run menu seeders first.\n";
            return false;
        }
        
        echo "📋 Found " . $menus->count() . " menus in the system\n\n";
        
        // System Administrator role ID
        $systemAdminRoleId = 1;
        
        // Verify the role exists
        $adminRole = DB::table('roles')->where('id', $systemAdminRoleId)->first();
        if (!$adminRole) {
            echo "❌ System Administrator role (ID: 1) not found. Please run role seeders first.\n";
            return false;
        }
        
        echo "👤 Found role: " . $adminRole->name . "\n\n";
        
        // Define all possible actions that can be performed on menus
        $allActions = [
            'view',      // View the menu item
            'create',    // Create new records
            'edit',      // Edit existing records
            'delete',    // Delete records
            'approve',   // Approve transactions/requests
            'reject',    // Reject transactions/requests
            'manage',    // Manage settings and configurations
            'configure', // Configure system settings
            'audit',     // View audit logs
            'export',    // Export data
            'import'     // Import data
        ];
        
        echo "🔑 Granting actions: " . implode(', ', $allActions) . "\n\n";
        
        // Prepare data for insertion
        $roleMenuActions = [];
        foreach ($menus as $menu) {
            $roleMenuActions[] = [
                'role_id' => $systemAdminRoleId,
                'menu_id' => $menu->id,
                'sub_role' => 'System Administrator',
                'allowed_actions' => json_encode(['value' => json_encode($allActions)]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Start transaction
        DB::beginTransaction();
        
        // Clear existing permissions for System Administrator
        $deletedCount = DB::table('role_menu_actions')
            ->where('role_id', $systemAdminRoleId)
            ->delete();
        
        if ($deletedCount > 0) {
            echo "🗑️  Removed $deletedCount existing permissions\n";
        }
        
        // Insert new permissions in chunks to avoid memory issues
        $chunks = array_chunk($roleMenuActions, 100);
        $totalInserted = 0;
        
        foreach ($chunks as $chunk) {
            DB::table('role_menu_actions')->insert($chunk);
            $totalInserted += count($chunk);
            echo "✅ Inserted " . count($chunk) . " permissions...\n";
        }
        
        // Commit transaction
        DB::commit();
        
        echo "\n====================================\n";
        echo "✅ SUCCESS!\n";
        echo "====================================\n";
        echo "📊 Summary:\n";
        echo "   - Role: " . $adminRole->name . "\n";
        echo "   - Total menus granted: " . count($menus) . "\n";
        echo "   - Actions per menu: " . count($allActions) . "\n";
        echo "   - Total permissions created: " . $totalInserted . "\n";
        echo "\n";
        
        // List all menus granted
        echo "📜 Menus granted access to:\n";
        foreach ($menus as $menu) {
            echo "   ✓ " . $menu->menu_name . "\n";
        }
        
        echo "\n🎉 System Administrator now has full access to all menus!\n\n";
        
        return true;
        
    } catch (Exception $e) {
        DB::rollBack();
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
        echo "Transaction rolled back. No changes were made.\n\n";
        return false;
    }
}

// Run the function
grantAdminPermissions();

// Add verification
echo "🔍 Verifying permissions...\n";
$count = DB::table('role_menu_actions')
    ->where('role_id', 1)
    ->count();
echo "✅ Verification complete: System Administrator has $count menu permissions\n\n";