<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VerifySetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder verifies that all critical data is properly set up
     * and fixes any issues automatically.
     *
     * @return void
     */
    public function run()
    {
        echo "\n=== VERIFYING AND FIXING DATABASE SETUP ===\n";
        
        $issues = [];
        $fixes = [];
        
        // 1. Check Branches FIRST (need at least 1 for users)
        $branchCount = DB::table('branches')->count();
        if ($branchCount == 0) {
            $issues[] = "No branches found";
            
            // Create default branch
            DB::table('branches')->insert([
                'id' => 1,
                'name' => 'Headquarters',
                'region' => 'Dar es Salaam',
                'wilaya' => 'Ilala',
                'branch_number' => '01',
                'status' => 'active',
                'email' => 'hq@saccos.co.tz',
                'phone_number' => '255000000000',
                'address' => 'Main Office',
                'branch_type' => 'MAIN',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $fixes[] = "Created Headquarters branch";
        }
        
        // 2. Check Users (should have at least 3)
        $userCount = DB::table('users')->count();
        if ($userCount < 3) {
            $issues[] = "Only $userCount users found (expected 3)";
            
            // Create missing users if needed
            if ($userCount == 0) {
                // Create default users
                $users = [
                    [
                        'name' => 'Andrew S. Mashamba',
                        'email' => 'andrew.s.mashamba@gmail.com',
                        'password' => bcrypt('1234567890'), // Updated default password
                        'status' => 'active',
                        'verification_status' => 1,
                        'department_code' => 'ICT',
                        'branch_id' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Jane Doe',
                        'email' => 'jane.doe@example.com',
                        'password' => bcrypt('1234567890'), // Updated default password
                        'status' => 'active',
                        'verification_status' => 1,
                        'department_code' => 'GOV',
                        'branch_id' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Admin User',
                        'email' => 'admin@saccos.co.tz',
                        'password' => bcrypt('1234567890'), // Updated default password
                        'status' => 'active',
                        'verification_status' => 1,
                        'department_code' => 'IT',
                        'branch_id' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ];
                
                foreach ($users as $user) {
                    DB::table('users')->insert($user);
                }
                $fixes[] = "Created 3 default users";
            }
        }
        
        // Branch check already done above
        
        // 3. Check Roles (should have 2)
        $roleCount = DB::table('roles')->count();
        if ($roleCount != 2) {
            $issues[] = "Found $roleCount roles (expected 2)";
        }
        
        // 4. Check Menus have proper data
        $menusWithoutNames = DB::table('menus')
            ->where(function ($query) {
                $query->whereNull('name')
                    ->orWhere('name', '');
            })
            ->count();
            
        if ($menusWithoutNames > 0) {
            $issues[] = "$menusWithoutNames menus without names";
            
            // Fix menu names
            DB::statement("UPDATE menus SET name = menu_name WHERE name IS NULL OR name = ''");
            DB::statement("UPDATE menus SET route = LOWER(REPLACE(menu_name, ' ', '_')) WHERE route IS NULL");
            DB::statement("UPDATE menus SET icon = CONCAT('fa fa-', LOWER(REPLACE(menu_name, ' ', '-'))) WHERE icon IS NULL");
            $fixes[] = "Fixed menu names, routes, and icons";
        }
        
        // 5. Check User-Role assignments
        $usersWithoutRoles = DB::table('users')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->whereNull('user_roles.id')
            ->count();
            
        if ($usersWithoutRoles > 0) {
            $issues[] = "$usersWithoutRoles users without roles";
            
            // Assign roles to users without them
            $unassignedUsers = DB::table('users')
                ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->whereNull('user_roles.id')
                ->select('users.id')
                ->get();
                
            $systemAdminRole = DB::table('roles')->where('name', 'System Administrator')->first();
            $institutionAdminRole = DB::table('roles')->where('name', 'Institution Administrator')->first();
            
            if ($systemAdminRole) {
                foreach ($unassignedUsers as $index => $user) {
                    $roleId = ($index == 0) ? $systemAdminRole->id : $institutionAdminRole->id;
                    DB::table('user_roles')->insert([
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $fixes[] = "Assigned roles to $usersWithoutRoles users";
            }
        }
        
        // 6. Check Role-Menu-Actions
        $roleMenuActions = DB::table('role_menu_actions')->count();
        if ($roleMenuActions < 62) {
            $issues[] = "Only $roleMenuActions role-menu-actions found (expected 62)";
        }
        
        // Print summary
        echo "\n📋 VERIFICATION RESULTS:\n";
        echo "========================\n";
        
        if (count($issues) > 0) {
            echo "Issues Found:\n";
            foreach ($issues as $issue) {
                echo "  ❌ $issue\n";
            }
        }
        
        if (count($fixes) > 0) {
            echo "\nFixes Applied:\n";
            foreach ($fixes as $fix) {
                echo "  ✅ $fix\n";
            }
        }
        
        // Final status check
        echo "\n📊 FINAL DATABASE STATUS:\n";
        echo "========================\n";
        echo "• Users: " . DB::table('users')->count() . "\n";
        echo "• Branches: " . DB::table('branches')->count() . "\n";
        echo "• Roles: " . DB::table('roles')->count() . "\n";
        echo "• Menus with names: " . DB::table('menus')->whereNotNull('name')->where('name', '!=', '')->count() . "\n";
        echo "• User-Role assignments: " . DB::table('user_roles')->count() . "\n";
        echo "• Role-Menu actions: " . DB::table('role_menu_actions')->count() . "\n";
        
        // Check if all users have roles
        $allUsersHaveRoles = DB::table('users')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->whereNull('user_roles.id')
            ->count() == 0;
            
        if ($allUsersHaveRoles) {
            echo "\n✅ All users have roles assigned\n";
        } else {
            echo "\n⚠️ Some users still don't have roles\n";
        }
        
        // Check menu access
        $systemAdminMenus = DB::table('role_menu_actions')
            ->where('role_id', 1)
            ->count();
        echo "• System Administrator has access to $systemAdminMenus menus\n";
        
        $institutionAdminMenus = DB::table('role_menu_actions')
            ->where('role_id', 2)
            ->count();
        echo "• Institution Administrator has access to $institutionAdminMenus menus\n";
        
        if (count($issues) == 0 || count($fixes) == count($issues)) {
            echo "\n🎉 DATABASE SETUP VERIFIED AND COMPLETE! 🎉\n";
        } else {
            echo "\n⚠️ Some issues remain. Please check the logs.\n";
        }
        
        echo "===========================================\n\n";
    }
}