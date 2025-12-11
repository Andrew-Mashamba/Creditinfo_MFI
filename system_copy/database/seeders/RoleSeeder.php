<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Institution;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'NBC001')->first();

        // Get ICT department for system roles
        $ictDepartment = Department::where('department_code', 'ICT')->first();
        
        // Create system roles
        Role::updateOrCreate(
            [
                'name' => 'System Administrator'
            ],
            [
                'description' => 'Full system access',
                'department_id' => $ictDepartment ? $ictDepartment->id : null,
                'level' => 1,
                'is_system_role' => true,
                'permission_inheritance_enabled' => true,
                'department_specific' => false
            ]
        );

        Role::updateOrCreate(
            [
                'name' => 'Institution Administrator'
            ],
            [
                'description' => 'Institution level administration',
                'department_id' => $ictDepartment ? $ictDepartment->id : null,
                'level' => 2,
                'is_system_role' => true,
                'permission_inheritance_enabled' => true,
                'department_specific' => false
            ]
        );

        // Get all departments
        $departments = Department::all();
        $departmentMap = $departments->pluck('id', 'department_code')->toArray();

        // Define roles for each department
        $departmentRoles = [
            'BOD' => [
                'Board Chairperson',
                'Vice Chairperson',
                'Secretary to the Board',
                'Board Member Finance Oversight',
                'Board Member Risk & Compliance',
                'Board Member Strategy & Development',
                'Board Member ICT Oversight'
            ],
            'SUP' => [
                'Chairperson Supervisory Committee',
                'Secretary Supervisory Committee',
                'Member Internal Controls',
                'Member Financial Oversight',
                'Member Compliance Monitoring'
            ],
            'AGS' => [
                'Administrative Officer',
                'Office Manager',
                'Records Management Clerk',
                'Receptionist',
                'Facilities & Maintenance Coordinator',
                'Office Assistant / Messenger',
                'Driver'
            ],
            'FAC' => [
                'Chief Finance Officer (CFO)',
                'Accountant',
                'Accounts Payable Officer',
                'Accounts Receivable Officer',
                'Cashier',
                'Financial Analyst',
                'Reconciliation Officer',
                'Payroll Officer'
            ],
            'CRD' => [
                'Credit Manager',
                'Loan Officer',
                'Credit Analyst',
                'Credit Risk Officer',
                'Recovery Officer',
                'Collateral & Securities Officer',
                'Loan Monitoring Officer'
            ],
            'SVD' => [
                'Savings Manager',
                'Deposit Teller',
                'Customer Account Officer',
                'Passbook Clerk',
                'Fixed Deposit Officer',
                'Mobile Banking Coordinator'
            ],
            'MBS' => [
                'Member Relations Officer',
                'Customer Service Representative',
                'Member Enrollment Officer',
                'Call Center Agent',
                'Member Education & Training Officer'
            ],
            'IAC' => [
                'Chief Internal Auditor',
                'Internal Audit Officer',
                'Compliance Officer',
                'Risk & Compliance Analyst',
                'Forensic Audit Specialist'
            ],
            'HRD' => [
                'Human Resources Manager',
                'HR Officer',
                'Training & Development Officer',
                'Employee Relations Officer',
                'Recruitment Specialist',
                'Performance Management Officer',
                'HR Assistant'
            ],
            'ICT' => [
                'ICT Manager',
                'Systems Administrator',
                'Database Administrator',
                'Network Engineer',
                'Help Desk Technician',
                'Cybersecurity Analyst',
                'Core Banking System Officer'
            ],
            'MBD' => [
                'Marketing Manager',
                'Business Development Officer',
                'Digital Marketing Specialist',
                'Market Research Analyst',
                'Brand & Communications Officer',
                'Member Outreach Coordinator',
                'Product Development Officer'
            ],
            'LGD' => [
                'Legal Counsel',
                'Governance Officer',
                'Legal Compliance Officer',
                'Contracts & Litigation Officer',
                'Company Secretary'
            ],
            'RMD' => [
                'Risk Manager',
                'Enterprise Risk Analyst',
                'Operational Risk Officer',
                'Risk & Fraud Prevention Officer',
                'Insurance & Claims Officer'
            ],
            'PRL' => [
                'Procurement Officer',
                'Logistics & Supply Chain Officer',
                'Inventory Officer',
                'Storekeeper',
                'Vendor Management Officer',
                'Procurement Compliance Officer'
            ]
        ];

        // Create roles for each department
        foreach ($departmentRoles as $deptCode => $roles) {
            if (isset($departmentMap[$deptCode])) {
                $departmentId = $departmentMap[$deptCode];
                foreach ($roles as $roleName) {
                    Role::updateOrCreate(
                        [
                            'name' => $roleName
                        ],
                        [
                            'department_id' => $departmentId,
                            'description' => $roleName . ' role in ' . Department::find($departmentId)->department_name,
                            'level' => 3, // Default level for all roles
                            'is_system_role' => false
                        ]
                    );
                }
            }
        }
    }
}