<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\departmentsList;
use Illuminate\Support\Facades\DB;

class SACCOSRolesSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Get all departments
            $departments = departmentsList::all()->keyBy('department_code');
            
            // Define roles for each department
            $departmentRoles = [
                'GOV' => [
                    ['name' => 'Chairperson', 'description' => 'Head of Governance & Oversight'],
                    ['name' => 'Secretary', 'description' => 'Governance Secretary'],
                    ['name' => 'Member', 'description' => 'Governance Committee Member'],
                ],
                'BOD' => [
                    ['name' => 'Board Chairperson', 'description' => 'Chair of the Board of Directors'],
                    ['name' => 'Vice Chairperson', 'description' => 'Vice Chair of the Board'],
                    ['name' => 'Board Secretary', 'description' => 'Secretary to the Board'],
                    ['name' => 'Board Treasurer', 'description' => 'Treasurer of the Board'],
                    ['name' => 'Board Member', 'description' => 'Member of the Board of Directors'],
                ],
                'SUP' => [
                    ['name' => 'Supervisory Chairperson', 'description' => 'Chair of Supervisory Committee'],
                    ['name' => 'Supervisory Vice Chair', 'description' => 'Vice Chair of Supervisory Committee'],
                    ['name' => 'Supervisory Secretary', 'description' => 'Secretary of Supervisory Committee'],
                    ['name' => 'Supervisory Member', 'description' => 'Member of Supervisory Committee'],
                ],
                'CMO' => [
                    ['name' => 'CEO/General Manager', 'description' => 'Chief Executive Officer / General Manager'],
                    ['name' => 'Deputy CEO', 'description' => 'Deputy Chief Executive Officer'],
                    ['name' => 'Chief Operations Officer', 'description' => 'Head of Operations'],
                    ['name' => 'Executive Assistant', 'description' => 'Executive Assistant to CEO'],
                ],
                'AGS' => [
                    ['name' => 'Administration Manager', 'description' => 'Head of Administration'],
                    ['name' => 'Office Administrator', 'description' => 'Office Administration Officer'],
                    ['name' => 'Receptionist', 'description' => 'Front Desk Officer'],
                    ['name' => 'Office Assistant', 'description' => 'General Office Assistant'],
                    ['name' => 'Driver', 'description' => 'Company Driver'],
                    ['name' => 'Security Officer', 'description' => 'Security Personnel'],
                ],
                'FAC' => [
                    ['name' => 'Chief Finance Officer', 'description' => 'Head of Finance & Accounting'],
                    ['name' => 'Finance Manager', 'description' => 'Finance Department Manager'],
                    ['name' => 'Chief Accountant', 'description' => 'Chief Accountant'],
                    ['name' => 'Senior Accountant', 'description' => 'Senior Accounting Officer'],
                    ['name' => 'Accountant', 'description' => 'Accounting Officer'],
                    ['name' => 'Accounts Assistant', 'description' => 'Assistant Accountant'],
                    ['name' => 'Cashier', 'description' => 'Cash Management Officer'],
                    ['name' => 'Bookkeeper', 'description' => 'Books and Records Keeper'],
                ],
                'CRD' => [
                    ['name' => 'Credit Manager', 'description' => 'Head of Credit Department'],
                    ['name' => 'Senior Credit Officer', 'description' => 'Senior Loan Officer'],
                    ['name' => 'Credit Officer', 'description' => 'Loan Officer'],
                    ['name' => 'Credit Analyst', 'description' => 'Credit Risk Analyst'],
                    ['name' => 'Recovery Officer', 'description' => 'Loan Recovery Officer'],
                    ['name' => 'Collections Officer', 'description' => 'Debt Collections Officer'],
                    ['name' => 'Credit Assistant', 'description' => 'Credit Department Assistant'],
                ],
                'SVD' => [
                    ['name' => 'Savings Manager', 'description' => 'Head of Savings & Deposits'],
                    ['name' => 'Senior Teller', 'description' => 'Senior Teller Officer'],
                    ['name' => 'Teller', 'description' => 'Teller Officer'],
                    ['name' => 'Customer Service Officer', 'description' => 'Customer Service Representative'],
                    ['name' => 'Savings Mobilization Officer', 'description' => 'Savings Product Marketing Officer'],
                ],
                'MBS' => [
                    ['name' => 'Member Services Manager', 'description' => 'Head of Member Services'],
                    ['name' => 'Membership Officer', 'description' => 'Member Registration Officer'],
                    ['name' => 'Member Relations Officer', 'description' => 'Member Relations Representative'],
                    ['name' => 'Education Officer', 'description' => 'Member Education Coordinator'],
                    ['name' => 'Welfare Officer', 'description' => 'Member Welfare Coordinator'],
                ],
                'IAC' => [
                    ['name' => 'Chief Internal Auditor', 'description' => 'Head of Internal Audit'],
                    ['name' => 'Compliance Manager', 'description' => 'Head of Compliance'],
                    ['name' => 'Senior Auditor', 'description' => 'Senior Internal Auditor'],
                    ['name' => 'Internal Auditor', 'description' => 'Internal Audit Officer'],
                    ['name' => 'Compliance Officer', 'description' => 'Regulatory Compliance Officer'],
                    ['name' => 'Audit Assistant', 'description' => 'Audit Department Assistant'],
                ],
                'HRD' => [
                    ['name' => 'HR Manager', 'description' => 'Head of Human Resources'],
                    ['name' => 'HR Officer', 'description' => 'Human Resources Officer'],
                    ['name' => 'Training Coordinator', 'description' => 'Staff Training Coordinator'],
                    ['name' => 'Recruitment Officer', 'description' => 'Talent Acquisition Officer'],
                    ['name' => 'HR Assistant', 'description' => 'Human Resources Assistant'],
                ],
                'ICT' => [
                    ['name' => 'IT Manager', 'description' => 'Head of Information Technology'],
                    ['name' => 'Systems Administrator', 'description' => 'System Administration Officer'],
                    ['name' => 'Database Administrator', 'description' => 'Database Management Officer'],
                    ['name' => 'Network Administrator', 'description' => 'Network Management Officer'],
                    ['name' => 'Software Developer', 'description' => 'Application Developer'],
                    ['name' => 'IT Support Officer', 'description' => 'Technical Support Officer'],
                    ['name' => 'IT Security Officer', 'description' => 'Information Security Officer'],
                    ['name' => 'Data Analyst', 'description' => 'Data Analysis Officer'],
                ],
                'MBD' => [
                    ['name' => 'Marketing Manager', 'description' => 'Head of Marketing & Business Development'],
                    ['name' => 'Business Development Officer', 'description' => 'Business Development Representative'],
                    ['name' => 'Marketing Officer', 'description' => 'Marketing Coordinator'],
                    ['name' => 'Public Relations Officer', 'description' => 'PR and Communications Officer'],
                    ['name' => 'Digital Marketing Officer', 'description' => 'Online Marketing Specialist'],
                    ['name' => 'Field Marketing Officer', 'description' => 'Field Marketing Representative'],
                ],
                'LGD' => [
                    ['name' => 'Legal Manager', 'description' => 'Head of Legal Department'],
                    ['name' => 'Legal Officer', 'description' => 'Legal Counsel'],
                    ['name' => 'Company Secretary', 'description' => 'Corporate Secretary'],
                    ['name' => 'Legal Assistant', 'description' => 'Legal Department Assistant'],
                ],
                'RMD' => [
                    ['name' => 'Risk Manager', 'description' => 'Head of Risk Management'],
                    ['name' => 'Risk Officer', 'description' => 'Risk Assessment Officer'],
                    ['name' => 'Risk Analyst', 'description' => 'Risk Analysis Specialist'],
                    ['name' => 'Risk Assistant', 'description' => 'Risk Department Assistant'],
                ],
                'PRL' => [
                    ['name' => 'Procurement Manager', 'description' => 'Head of Procurement & Logistics'],
                    ['name' => 'Procurement Officer', 'description' => 'Purchasing Officer'],
                    ['name' => 'Logistics Officer', 'description' => 'Logistics Coordinator'],
                    ['name' => 'Stores Officer', 'description' => 'Inventory Management Officer'],
                    ['name' => 'Procurement Assistant', 'description' => 'Procurement Department Assistant'],
                ],
            ];
            
            // Create roles for each department
            foreach ($departmentRoles as $deptCode => $roles) {
                if (!isset($departments[$deptCode])) {
                    $this->command->warn("Department with code {$deptCode} not found, skipping...");
                    continue;
                }
                
                $department = $departments[$deptCode];
                
                foreach ($roles as $index => $roleData) {
                    // Calculate hierarchy level based on position in array
                    $level = 1; // Department level
                    if (str_contains(strtolower($roleData['name']), 'assistant') || 
                        str_contains(strtolower($roleData['name']), 'junior')) {
                        $level = 3;
                    } elseif (str_contains(strtolower($roleData['name']), 'senior') || 
                             str_contains(strtolower($roleData['name']), 'manager') ||
                             str_contains(strtolower($roleData['name']), 'chief') ||
                             str_contains(strtolower($roleData['name']), 'head')) {
                        $level = 1;
                    } else {
                        $level = 2;
                    }
                    
                    Role::create([
                        'name' => $roleData['name'],
                        'department_id' => $department->id,
                        'description' => $roleData['description'],
                        'status' => 'ACTIVE',
                        'level' => $level,
                        'institution_id' => 11, // Default institution
                        'is_system_role' => false,
                        'permission_inheritance_enabled' => true,
                        'department_specific' => true,
                    ]);
                    
                    $this->command->info("Created role: {$roleData['name']} for department {$department->department_name}");
                }
            }
            
            DB::commit();
            $this->command->info('SACCOS Roles seeding completed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding roles: ' . $e->getMessage());
            throw $e;
        }
    }
}