<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\SubRole;
use Illuminate\Support\Facades\DB;

class SACCOSSubRolesSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Define sub-roles for specific key roles that need delegation
            $roleSubRoles = [
                // CEO/General Manager sub-roles
                'CEO/General Manager' => [
                    ['name' => 'Acting CEO', 'description' => 'Acting Chief Executive Officer in CEO absence'],
                    ['name' => 'CEO Delegate', 'description' => 'Delegated CEO authority for specific tasks'],
                ],
                
                // Department Manager sub-roles (applicable to all manager roles)
                'Manager' => [
                    ['name' => 'Deputy Manager', 'description' => 'Deputy to the Department Manager'],
                    ['name' => 'Assistant Manager', 'description' => 'Assistant to the Department Manager'],
                    ['name' => 'Acting Manager', 'description' => 'Acting Manager in manager absence'],
                ],
                
                // Senior Officer sub-roles
                'Senior' => [
                    ['name' => 'Team Lead', 'description' => 'Team Leader for specific functions'],
                    ['name' => 'Acting Senior', 'description' => 'Acting Senior Officer'],
                ],
                
                // Board Chairperson sub-roles
                'Board Chairperson' => [
                    ['name' => 'Acting Board Chair', 'description' => 'Acting Chairperson in Chair absence'],
                    ['name' => 'Board Chair Delegate', 'description' => 'Delegated Board Chair authority'],
                ],
                
                // Supervisory Chairperson sub-roles
                'Supervisory Chairperson' => [
                    ['name' => 'Acting Supervisory Chair', 'description' => 'Acting Supervisory Chairperson'],
                    ['name' => 'Supervisory Delegate', 'description' => 'Delegated Supervisory authority'],
                ],
            ];
            
            // Get all roles
            $roles = Role::all();
            
            foreach ($roles as $role) {
                $subRolesToCreate = [];
                
                // Check if this is the CEO role
                if ($role->name === 'CEO/General Manager') {
                    $subRolesToCreate = $roleSubRoles['CEO/General Manager'];
                }
                // Check if this is Board Chairperson
                elseif ($role->name === 'Board Chairperson') {
                    $subRolesToCreate = $roleSubRoles['Board Chairperson'];
                }
                // Check if this is Supervisory Chairperson
                elseif ($role->name === 'Supervisory Chairperson') {
                    $subRolesToCreate = $roleSubRoles['Supervisory Chairperson'];
                }
                // Check if this is a Manager role
                elseif (str_contains(strtolower($role->name), 'manager') || 
                       str_contains(strtolower($role->name), 'chief')) {
                    $subRolesToCreate = $roleSubRoles['Manager'];
                }
                // Check if this is a Senior role
                elseif (str_contains(strtolower($role->name), 'senior')) {
                    $subRolesToCreate = $roleSubRoles['Senior'];
                }
                
                // Create sub-roles for this role
                foreach ($subRolesToCreate as $subRoleData) {
                    // Customize the sub-role name to include the parent role context
                    $subRoleName = $subRoleData['name'];
                    
                    // For manager roles, prepend department name
                    if (str_contains($subRoleData['name'], 'Manager') && 
                        !in_array($role->name, ['CEO/General Manager'])) {
                        $deptName = str_replace([' Manager', ' Chief', ' Officer'], '', $role->name);
                        $subRoleName = $deptName . ' ' . $subRoleData['name'];
                    }
                    // For senior roles, include the specific senior role
                    elseif (str_contains($subRoleData['name'], 'Senior')) {
                        $subRoleName = str_replace('Senior ', '', $role->name) . ' ' . $subRoleData['name'];
                    }
                    
                    SubRole::create([
                        'name' => $subRoleName,
                        'role_id' => $role->id,
                        'description' => $subRoleData['description'],
                    ]);
                    
                    $this->command->info("Created sub-role: {$subRoleName} for role {$role->name}");
                }
            }
            
            // Create additional functional sub-roles for specific departments
            
            // IT Department functional sub-roles
            $itManager = Role::where('name', 'IT Manager')->first();
            if ($itManager) {
                $itSubRoles = [
                    ['name' => 'Infrastructure Lead', 'description' => 'Lead for IT Infrastructure'],
                    ['name' => 'Applications Lead', 'description' => 'Lead for Application Systems'],
                    ['name' => 'Security Lead', 'description' => 'Lead for Information Security'],
                    ['name' => 'Database Lead', 'description' => 'Lead for Database Systems'],
                ];
                
                foreach ($itSubRoles as $subRole) {
                    SubRole::create([
                        'name' => $subRole['name'],
                        'role_id' => $itManager->id,
                        'description' => $subRole['description'],
                    ]);
                    $this->command->info("Created IT sub-role: {$subRole['name']}");
                }
            }
            
            // Finance Department functional sub-roles
            $financeManager = Role::where('name', 'Finance Manager')->first();
            if ($financeManager) {
                $financeSubRoles = [
                    ['name' => 'Budget Controller', 'description' => 'Controls budget and expenditure'],
                    ['name' => 'Treasury Lead', 'description' => 'Lead for Treasury Operations'],
                    ['name' => 'Reporting Lead', 'description' => 'Lead for Financial Reporting'],
                ];
                
                foreach ($financeSubRoles as $subRole) {
                    SubRole::create([
                        'name' => $subRole['name'],
                        'role_id' => $financeManager->id,
                        'description' => $subRole['description'],
                    ]);
                    $this->command->info("Created Finance sub-role: {$subRole['name']}");
                }
            }
            
            // Credit Department functional sub-roles  
            $creditManager = Role::where('name', 'Credit Manager')->first();
            if ($creditManager) {
                $creditSubRoles = [
                    ['name' => 'Credit Committee Secretary', 'description' => 'Secretary to Credit Committee'],
                    ['name' => 'Loan Appraisal Lead', 'description' => 'Lead for Loan Appraisals'],
                    ['name' => 'Recovery Team Lead', 'description' => 'Lead for Recovery Operations'],
                ];
                
                foreach ($creditSubRoles as $subRole) {
                    SubRole::create([
                        'name' => $subRole['name'],
                        'role_id' => $creditManager->id,
                        'description' => $subRole['description'],
                    ]);
                    $this->command->info("Created Credit sub-role: {$subRole['name']}");
                }
            }
            
            DB::commit();
            $this->command->info('SACCOS Sub-Roles seeding completed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding sub-roles: ' . $e->getMessage());
            throw $e;
        }
    }
}