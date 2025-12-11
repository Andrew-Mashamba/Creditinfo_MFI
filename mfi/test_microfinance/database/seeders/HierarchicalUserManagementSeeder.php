<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\departmentsList;
use App\Models\Role;
use App\Models\SubRole;
use App\Models\RoleMenuAction;
use App\Models\Menu;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class HierarchicalUserManagementSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Ensure we have branches
            $branch = Branch::first();
            if (!$branch) {
                $branch = Branch::create([
                    'name' => 'Main Branch',
                    'code' => 'MAIN',
                    'status' => 'ACTIVE'
                ]);
            }
            
            // Create Departments
            $departments = $this->createDepartments($branch->id);
            
            // Create Roles for each Department
            $roles = $this->createRoles($departments);
            
            // Create Sub-Roles for each Role
            $this->createSubRoles($roles);
            
            // Assign Permissions
            $this->assignPermissions($roles);
            
            DB::commit();
            
            $this->command->info('Hierarchical User Management structure seeded successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding hierarchical structure: ' . $e->getMessage());
        }
    }
    
    private function createDepartments($branchId)
    {
        $departmentData = [
            [
                'name' => 'Loans Department',
                'code' => 'LNS',
                'description' => 'Handles all loan-related operations including disbursements and collections'
            ],
            [
                'name' => 'Savings Department',
                'code' => 'SAV',
                'description' => 'Manages member savings accounts and deposits'
            ],
            [
                'name' => 'Finance Department',
                'code' => 'FIN',
                'description' => 'Handles financial operations, accounting, and reporting'
            ],
            [
                'name' => 'Operations Department',
                'code' => 'OPS',
                'description' => 'Manages day-to-day operational activities'
            ],
            [
                'name' => 'Admin Department',
                'code' => 'ADM',
                'description' => 'Administrative and HR functions'
            ]
        ];
        
        $departments = [];
        
        foreach ($departmentData as $dept) {
            $department = departmentsList::updateOrCreate(
                ['department_code' => $dept['code']],
                [
                    'department_name' => $dept['name'],
                    'branch_id' => $branchId,
                    'description' => $dept['description'],
                    'status' => true,
                    'institution_id' => 11
                ]
            );
            
            $departments[$dept['code']] = $department;
            $this->command->info("Created department: {$dept['name']}");
        }
        
        return $departments;
    }
    
    private function createRoles($departments)
    {
        $roleStructure = [
            'LNS' => [
                ['name' => 'Loans Manager', 'description' => 'Head of loans department with full access'],
                ['name' => 'Credit Supervisor', 'description' => 'Supervises credit operations'],
                ['name' => 'Loan Officer', 'description' => 'Processes loan applications']
            ],
            'SAV' => [
                ['name' => 'Savings Manager', 'description' => 'Head of savings department'],
                ['name' => 'Customer Service Manager', 'description' => 'Manages customer service'],
                ['name' => 'Teller Supervisor', 'description' => 'Supervises teller operations']
            ],
            'FIN' => [
                ['name' => 'Finance Manager', 'description' => 'Head of finance department'],
                ['name' => 'Chief Accountant', 'description' => 'Chief accounting officer'],
                ['name' => 'Internal Auditor', 'description' => 'Internal audit and compliance']
            ],
            'OPS' => [
                ['name' => 'Operations Manager', 'description' => 'Head of operations'],
                ['name' => 'Branch Supervisor', 'description' => 'Supervises branch operations'],
                ['name' => 'Systems Administrator', 'description' => 'IT systems management']
            ],
            'ADM' => [
                ['name' => 'HR Manager', 'description' => 'Human resources management'],
                ['name' => 'Admin Officer', 'description' => 'Administrative support'],
                ['name' => 'Compliance Officer', 'description' => 'Regulatory compliance']
            ]
        ];
        
        $roles = [];
        
        foreach ($roleStructure as $deptCode => $deptRoles) {
            if (!isset($departments[$deptCode])) continue;
            
            $department = $departments[$deptCode];
            
            foreach ($deptRoles as $roleData) {
                $role = Role::updateOrCreate(
                    [
                        'name' => $roleData['name'],
                        'department_id' => $department->id
                    ],
                    [
                        'description' => $roleData['description'],
                        'status' => 'ACTIVE',
                        'institution_id' => 11
                    ]
                );
                
                $roles[] = $role;
                $this->command->info("Created role: {$roleData['name']} in {$department->department_name}");
            }
        }
        
        return $roles;
    }
    
    private function createSubRoles($roles)
    {
        $subRoleTemplates = [
            'Manager' => [
                ['name' => 'Deputy Manager', 'description' => 'Deputy to the manager'],
                ['name' => 'Assistant Manager', 'description' => 'Assistant manager role']
            ],
            'Supervisor' => [
                ['name' => 'Senior Officer', 'description' => 'Senior level officer'],
                ['name' => 'Junior Officer', 'description' => 'Junior level officer']
            ],
            'Officer' => [
                ['name' => 'Senior', 'description' => 'Senior level position'],
                ['name' => 'Junior', 'description' => 'Entry level position']
            ],
            'Accountant' => [
                ['name' => 'Senior Accountant', 'description' => 'Senior accounting position'],
                ['name' => 'Junior Accountant', 'description' => 'Junior accounting position'],
                ['name' => 'Accounts Assistant', 'description' => 'Accounting support']
            ]
        ];
        
        foreach ($roles as $role) {
            $roleType = null;
            
            // Determine role type
            foreach (['Manager', 'Supervisor', 'Officer', 'Accountant'] as $type) {
                if (stripos($role->name, $type) !== false) {
                    $roleType = $type;
                    break;
                }
            }
            
            if ($roleType && isset($subRoleTemplates[$roleType])) {
                foreach ($subRoleTemplates[$roleType] as $subRoleData) {
                    $subRole = SubRole::updateOrCreate(
                        [
                            'role_id' => $role->id,
                            'name' => $role->name . ' - ' . $subRoleData['name']
                        ],
                        [
                            'description' => $subRoleData['description']
                        ]
                    );
                    
                    $this->command->info("Created sub-role: {$subRole->name}");
                }
            }
        }
    }
    
    private function assignPermissions($roles)
    {
        // Get all menus
        $menus = Menu::all();
        if ($menus->isEmpty()) {
            $this->command->warn('No menus found. Skipping permission assignment.');
            return;
        }
        
        // Permission templates based on role patterns
        $permissionTemplates = [
            'Manager' => ['view', 'create', 'edit', 'delete', 'approve', 'export'],
            'Supervisor' => ['view', 'create', 'edit', 'approve'],
            'Officer' => ['view', 'create', 'edit'],
            'Auditor' => ['view', 'export'],
            'Teller' => ['view', 'create'],
            'Assistant' => ['view', 'create'],
            'Deputy' => ['view', 'create', 'edit', 'approve'],
            'Junior' => ['view']
        ];
        
        foreach ($roles as $role) {
            // Determine permission set based on role name
            $permissions = ['view']; // Default minimum permission
            
            foreach ($permissionTemplates as $pattern => $perms) {
                if (stripos($role->name, $pattern) !== false) {
                    $permissions = $perms;
                    break;
                }
            }
            
            // Assign permissions to specific menus based on department
            $relevantMenus = $this->getRelevantMenusForRole($role, $menus);
            
            foreach ($relevantMenus as $menu) {
                RoleMenuAction::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'menu_id' => $menu->id
                    ],
                    [
                        'allowed_actions' => json_encode($permissions)
                    ]
                );
            }
            
            $this->command->info("Assigned permissions to role: {$role->name}");
        }
    }
    
    private function getRelevantMenusForRole($role, $menus)
    {
        // Map departments to relevant menu patterns
        $deptMenuMap = [
            'Loans' => ['loan', 'credit', 'collateral', 'guarantee'],
            'Savings' => ['savings', 'deposit', 'withdraw', 'member'],
            'Finance' => ['account', 'report', 'journal', 'ledger', 'financial'],
            'Operations' => ['transaction', 'process', 'branch', 'system'],
            'Admin' => ['user', 'role', 'permission', 'setting', 'admin']
        ];
        
        $relevantMenus = [];
        $deptName = $role->department->department_name ?? '';
        
        foreach ($deptMenuMap as $dept => $patterns) {
            if (stripos($deptName, $dept) !== false) {
                foreach ($menus as $menu) {
                    foreach ($patterns as $pattern) {
                        if (stripos($menu->menu_name, $pattern) !== false) {
                            $relevantMenus[] = $menu;
                            break;
                        }
                    }
                }
                break;
            }
        }
        
        // If no specific menus found, give access to dashboard and reports
        if (empty($relevantMenus)) {
            foreach ($menus as $menu) {
                if (stripos($menu->menu_name, 'dashboard') !== false || 
                    stripos($menu->menu_name, 'report') !== false) {
                    $relevantMenus[] = $menu;
                }
            }
        }
        
        return $relevantMenus;
    }
}