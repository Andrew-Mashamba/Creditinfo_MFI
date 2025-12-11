<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Institution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaccosStructureSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'NBC001')->first();

        // Create SACCOS structure without conflicts
        $this->createSaccosStructure($institution);
        $this->createSaccosPermissions();
        $this->assignPermissionsToRoles();
    }

    private function createSaccosStructure($institution)
    {
        // Create AGM if it doesn't exist
        $agm = Department::firstOrCreate(
            ['department_code' => 'AGM'],
            [
                'department_name' => 'Annual General Meeting (AGM)',
                'description' => 'The supreme authority of the SACCOS. Elected by members annually.',
                'status' => true,
                'level' => 1,
                'path' => 'AGM'
            ]
        );

        // Create Board of Directors if it doesn't exist
        $bod = Department::firstOrCreate(
            ['department_code' => 'BOD'],
            [
                'department_name' => 'Board of Directors',
                'description' => 'Elected governing body responsible for strategic direction and oversight.',
                'status' => true,
                'level' => 2,
                'path' => 'AGM/BOD'
            ]
        );

        // Create Supervisory Committee if it doesn't exist
        $supervisory = Department::firstOrCreate(
            ['department_code' => 'SUP'],
            [
                'department_name' => 'Supervisory Committee',
                'description' => 'Independent body that monitors internal controls and compliance.',
                'status' => true,
                'level' => 2,
                'path' => 'AGM/SUP'
            ]
        );

        // Create External Auditor if it doesn't exist
        $externalAuditor = Department::firstOrCreate(
            ['department_code' => 'EXT'],
            [
                'department_name' => 'External Auditor',
                'description' => 'Appointed by AGM for statutory financial audits.',
                'status' => true,
                'level' => 2,
                'path' => 'AGM/EXT'
            ]
        );

        // Create CEO if it doesn't exist
        $ceo = Department::firstOrCreate(
            ['department_code' => 'CEO'],
            [
                'department_name' => 'General Manager / CEO',
                'description' => 'Chief Executive Officer reporting to the Board',
                'status' => true,
                'level' => 2,
                'path' => 'CEO'
            ]
        );

        // Create Board Sub-Committees
        $this->createBoardSubCommittees($bod);

        // Create Operational Departments
        $this->createOperationalDepartments($ceo);

        // Create roles for each department
        $this->createDepartmentRoles();
    }

    private function createBoardSubCommittees($bodDepartment)
    {
        $subCommittees = [
            'AUD' => 'Audit & Risk Committee',
            'FIN' => 'Finance, Budget & Investment Committee',
            'CRD' => 'Credit Committee',
            'EDU' => 'Education & Training Committee',
            'ICT' => 'ICT & Innovation Committee',
            'ETH' => 'Ethics, Governance & Disciplinary Committee'
        ];

        foreach ($subCommittees as $code => $name) {
            Department::firstOrCreate(
                ['department_code' => $code],
                [
                    'department_name' => $name,
                    'description' => "Board sub-committee for {$name}",
                    'status' => true,
                    'level' => 3,
                    'path' => "AGM/BOD/{$code}"
                ]
            );
        }
    }

    private function createOperationalDepartments($ceoDepartment)
    {
        $departments = [
            'OPS' => 'Operations Department',
            'FIN' => 'Finance & Accounts Department',
            'CRD' => 'Credit & Recovery Department',
            'RCD' => 'Risk & Compliance Department',
            'HRA' => 'Human Resource & Administration',
            'MMR' => 'Marketing & Member Relations'
        ];

        foreach ($departments as $code => $name) {
            Department::firstOrCreate(
                ['department_code' => $code],
                [
                    'department_name' => $name,
                    'description' => "Operational department for {$name}",
                    'status' => true,
                    'level' => 2,
                    'path' => "CEO/{$code}"
                ]
            );
        }
    }

    private function createDepartmentRoles()
    {
        // Get all departments
        $departments = Department::all()->keyBy('department_code');

        // Create roles for each department
        $this->createBoardRoles($departments['BOD'] ?? null);
        $this->createSupervisoryRoles($departments['SUP'] ?? null);
        $this->createOperationsRoles($departments['OPS'] ?? null);
        $this->createFinanceRoles($departments['FIN'] ?? null);
        $this->createCreditRoles($departments['CRD'] ?? null);
        $this->createRiskRoles($departments['RCD'] ?? null);
        $this->createHRRoles($departments['HRA'] ?? null);
        $this->createMarketingRoles($departments['MMR'] ?? null);
    }

    private function createBoardRoles($bodDepartment)
    {
        if (!$bodDepartment) return;

        $roles = [
            'Board Chairperson' => 'Leads the board and ensures effective governance',
            'Vice Chairperson' => 'Assists the chairperson and acts in their absence',
            'Board Secretary' => 'Maintains board records and ensures compliance',
            'Board Treasurer' => 'Oversees financial oversight and reporting',
            'Board Member - Finance' => 'Specializes in financial governance',
            'Board Member - Risk' => 'Focuses on risk management oversight',
            'Board Member - Strategy' => 'Oversees strategic planning and development',
            'Board Member - ICT' => 'Monitors technology and digital transformation',
            'Board Member - Compliance' => 'Ensures regulatory compliance oversight'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $bodDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createSupervisoryRoles($supervisoryDepartment)
    {
        if (!$supervisoryDepartment) return;

        $roles = [
            'Supervisory Committee Chairperson' => 'Leads the supervisory committee',
            'Supervisory Committee Secretary' => 'Maintains supervisory committee records',
            'Internal Controls Member' => 'Monitors internal control systems',
            'Financial Oversight Member' => 'Oversees financial integrity',
            'Compliance Monitoring Member' => 'Ensures policy compliance'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $supervisoryDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createOperationsRoles($operationsDepartment)
    {
        if (!$operationsDepartment) return;

        $roles = [
            'Operations Manager' => 'Manages overall operations and branch coordination',
            'Branch Manager' => 'Manages individual branch operations',
            'Front Desk Officer' => 'Handles member services and inquiries',
            'Teller / Cashier' => 'Processes transactions and cash handling',
            'Credit Officer' => 'Handles loan applications and processing',
            'Field Officer' => 'Manages group lending and mobilization',
            'IT Systems Administrator' => 'Manages core banking systems',
            'Network Administrator' => 'Maintains hardware and network infrastructure',
            'Digital Channels Officer' => 'Manages USSD, mobile app, and SMS services'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $operationsDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createFinanceRoles($financeDepartment)
    {
        if (!$financeDepartment) return;

        $roles = [
            'Finance Manager' => 'Oversees financial operations and reporting',
            'Senior Accountant' => 'Manages accounting and financial records',
            'Financial Reporting Officer' => 'Prepares financial statements and reports',
            'Budget Officer' => 'Manages budgeting and cash flow planning',
            'Reconciliation Officer' => 'Handles account reconciliations',
            'Asset Management Officer' => 'Manages fixed assets and inventory',
            'Payroll Officer' => 'Processes staff payroll and benefits'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $financeDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createCreditRoles($creditDepartment)
    {
        if (!$creditDepartment) return;

        $roles = [
            'Credit Manager' => 'Oversees credit operations and portfolio',
            'Loan Appraisal Officer' => 'Evaluates loan applications and disbursements',
            'Portfolio Management Officer' => 'Manages loan portfolio and monitoring',
            'Loan Monitoring Officer' => 'Tracks loan performance and compliance',
            'Recovery Officer' => 'Handles loan recovery and collections',
            'CRB Reporting Officer' => 'Manages credit bureau reporting',
            'Collateral Officer' => 'Manages loan collateral and securities'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $creditDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createRiskRoles($riskDepartment)
    {
        if (!$riskDepartment) return;

        $roles = [
            'Risk Manager' => 'Oversees risk management framework',
            'Compliance Officer' => 'Ensures regulatory compliance (TCDC, BOT, FCC)',
            'AML/CFT Officer' => 'Monitors anti-money laundering compliance',
            'Risk Assessment Officer' => 'Conducts risk assessments and analysis',
            'Internal Control Officer' => 'Performs internal control checks',
            'Fraud Prevention Officer' => 'Monitors and prevents fraudulent activities'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $riskDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createHRRoles($hrDepartment)
    {
        if (!$hrDepartment) return;

        $roles = [
            'HR Manager' => 'Oversees human resource operations',
            'Recruitment Officer' => 'Handles staff recruitment and selection',
            'Training Officer' => 'Manages staff training and development',
            'Performance Management Officer' => 'Oversees staff performance and appraisals',
            'Administrative Officer' => 'Manages office facilities and logistics',
            'Legal Officer' => 'Handles legal matters and contracts'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $hrDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createMarketingRoles($marketingDepartment)
    {
        if (!$marketingDepartment) return;

        $roles = [
            'Marketing Manager' => 'Oversees marketing and member relations',
            'Member Services Officer' => 'Handles member inquiries and support',
            'Business Development Officer' => 'Focuses on member recruitment and retention',
            'Branding Officer' => 'Manages brand identity and communication',
            'Product Development Officer' => 'Develops new products and services',
            'Community Outreach Officer' => 'Manages community engagement programs'
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'department_id' => $marketingDepartment->id,
                    'description' => $description,
                    'level' => 3,
                    'is_system_role' => false,
                    'permission_inheritance_enabled' => true,
                    'department_specific' => true
                ]
            );
        }
    }

    private function createSaccosPermissions()
    {
        $permissions = [
            // Governance Permissions
            ['name' => 'view_agm', 'module' => 'governance', 'action' => 'view', 'resource_type' => 'agm'],
            ['name' => 'manage_board', 'module' => 'governance', 'action' => 'manage', 'resource_type' => 'board'],
            ['name' => 'view_supervisory', 'module' => 'governance', 'action' => 'view', 'resource_type' => 'supervisory'],
            ['name' => 'manage_committees', 'module' => 'governance', 'action' => 'manage', 'resource_type' => 'committees'],

            // Operations Permissions
            ['name' => 'manage_branches', 'module' => 'operations', 'action' => 'manage', 'resource_type' => 'branches'],
            ['name' => 'process_transactions', 'module' => 'operations', 'action' => 'process', 'resource_type' => 'transactions'],
            ['name' => 'manage_tellers', 'module' => 'operations', 'action' => 'manage', 'resource_type' => 'tellers'],
            ['name' => 'manage_it_systems', 'module' => 'operations', 'action' => 'manage', 'resource_type' => 'it_systems'],

            // Finance Permissions
            ['name' => 'view_financial_reports', 'module' => 'finance', 'action' => 'view', 'resource_type' => 'financial_reports'],
            ['name' => 'manage_accounts', 'module' => 'finance', 'action' => 'manage', 'resource_type' => 'accounts'],
            ['name' => 'process_payroll', 'module' => 'finance', 'action' => 'process', 'resource_type' => 'payroll'],
            ['name' => 'manage_budget', 'module' => 'finance', 'action' => 'manage', 'resource_type' => 'budget'],

            // Credit Permissions
            ['name' => 'approve_loans', 'module' => 'credit', 'action' => 'approve', 'resource_type' => 'loans'],
            ['name' => 'view_credit_reports', 'module' => 'credit', 'action' => 'view', 'resource_type' => 'credit_reports'],
            ['name' => 'manage_recovery', 'module' => 'credit', 'action' => 'manage', 'resource_type' => 'recovery'],
            ['name' => 'process_crb_reports', 'module' => 'credit', 'action' => 'process', 'resource_type' => 'crb'],

            // Risk & Compliance Permissions
            ['name' => 'view_risk_reports', 'module' => 'risk', 'action' => 'view', 'resource_type' => 'risk_reports'],
            ['name' => 'manage_compliance', 'module' => 'risk', 'action' => 'manage', 'resource_type' => 'compliance'],
            ['name' => 'monitor_aml', 'module' => 'risk', 'action' => 'monitor', 'resource_type' => 'aml'],
            ['name' => 'conduct_audits', 'module' => 'risk', 'action' => 'conduct', 'resource_type' => 'audits'],

            // HR Permissions
            ['name' => 'manage_staff', 'module' => 'hr', 'action' => 'manage', 'resource_type' => 'staff'],
            ['name' => 'view_hr_reports', 'module' => 'hr', 'action' => 'view', 'resource_type' => 'hr_reports'],
            ['name' => 'manage_training', 'module' => 'hr', 'action' => 'manage', 'resource_type' => 'training'],
            ['name' => 'process_recruitment', 'module' => 'hr', 'action' => 'process', 'resource_type' => 'recruitment'],

            // Marketing Permissions
            ['name' => 'manage_members', 'module' => 'marketing', 'action' => 'manage', 'resource_type' => 'members'],
            ['name' => 'view_marketing_reports', 'module' => 'marketing', 'action' => 'view', 'resource_type' => 'marketing_reports'],
            ['name' => 'manage_products', 'module' => 'marketing', 'action' => 'manage', 'resource_type' => 'products'],
            ['name' => 'conduct_outreach', 'module' => 'marketing', 'action' => 'conduct', 'resource_type' => 'outreach'],

            // System Permissions
            ['name' => 'system_administration', 'module' => 'system', 'action' => 'administer', 'resource_type' => 'system'],
            ['name' => 'view_audit_logs', 'module' => 'system', 'action' => 'view', 'resource_type' => 'audit_logs'],
            ['name' => 'manage_permissions', 'module' => 'system', 'action' => 'manage', 'resource_type' => 'permissions'],
            ['name' => 'backup_restore', 'module' => 'system', 'action' => 'backup', 'resource_type' => 'system']
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'slug' => str_replace('_', '-', $permissionData['name']),
                    'description' => ucfirst(str_replace('_', ' ', $permissionData['name'])),
                    'module' => $permissionData['module'],
                    'action' => $permissionData['action'],
                    'resource_type' => $permissionData['resource_type'],
                    'is_system' => true
                ]
            );
        }
    }

    private function assignPermissionsToRoles()
    {
        // Get all permissions
        $permissions = Permission::all()->keyBy('name');

        // Assign permissions based on role hierarchy
        $this->assignGovernancePermissions($permissions);
        $this->assignOperationalPermissions($permissions);
        $this->assignFinancePermissions($permissions);
        $this->assignCreditPermissions($permissions);
        $this->assignRiskPermissions($permissions);
        $this->assignHRPermissions($permissions);
        $this->assignMarketingPermissions($permissions);
    }

    private function assignGovernancePermissions($permissions)
    {
        // Board roles get governance permissions
        $boardRoles = Role::where('department_id', Department::where('department_code', 'BOD')->first()->id ?? 0)->get();
        foreach ($boardRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['view_agm']->id ?? 0,
                $permissions['manage_board']->id ?? 0,
                $permissions['view_supervisory']->id ?? 0,
                $permissions['manage_committees']->id ?? 0
            ]);
        }

        // Supervisory committee gets oversight permissions
        $supervisoryRoles = Role::where('department_id', Department::where('department_code', 'SUP')->first()->id ?? 0)->get();
        foreach ($supervisoryRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['view_agm']->id ?? 0,
                $permissions['view_supervisory']->id ?? 0,
                $permissions['conduct_audits']->id ?? 0,
                $permissions['view_audit_logs']->id ?? 0
            ]);
        }
    }

    private function assignOperationalPermissions($permissions)
    {
        $operationsRoles = Role::where('department_id', Department::where('department_code', 'OPS')->first()->id ?? 0)->get();
        foreach ($operationsRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['manage_branches']->id ?? 0,
                $permissions['process_transactions']->id ?? 0,
                $permissions['manage_tellers']->id ?? 0,
                $permissions['manage_it_systems']->id ?? 0
            ]);
        }
    }

    private function assignFinancePermissions($permissions)
    {
        $financeRoles = Role::where('department_id', Department::where('department_code', 'FIN')->first()->id ?? 0)->get();
        foreach ($financeRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['view_financial_reports']->id ?? 0,
                $permissions['manage_accounts']->id ?? 0,
                $permissions['process_payroll']->id ?? 0,
                $permissions['manage_budget']->id ?? 0
            ]);
        }
    }

    private function assignCreditPermissions($permissions)
    {
        $creditRoles = Role::where('department_id', Department::where('department_code', 'CRD')->first()->id ?? 0)->get();
        foreach ($creditRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['approve_loans']->id ?? 0,
                $permissions['view_credit_reports']->id ?? 0,
                $permissions['manage_recovery']->id ?? 0,
                $permissions['process_crb_reports']->id ?? 0
            ]);
        }
    }

    private function assignRiskPermissions($permissions)
    {
        $riskRoles = Role::where('department_id', Department::where('department_code', 'RCD')->first()->id ?? 0)->get();
        foreach ($riskRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['view_risk_reports']->id ?? 0,
                $permissions['manage_compliance']->id ?? 0,
                $permissions['monitor_aml']->id ?? 0,
                $permissions['conduct_audits']->id ?? 0
            ]);
        }
    }

    private function assignHRPermissions($permissions)
    {
        $hrRoles = Role::where('department_id', Department::where('department_code', 'HRA')->first()->id ?? 0)->get();
        foreach ($hrRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['manage_staff']->id ?? 0,
                $permissions['view_hr_reports']->id ?? 0,
                $permissions['manage_training']->id ?? 0,
                $permissions['process_recruitment']->id ?? 0
            ]);
        }
    }

    private function assignMarketingPermissions($permissions)
    {
        $marketingRoles = Role::where('department_id', Department::where('department_code', 'MMR')->first()->id ?? 0)->get();
        foreach ($marketingRoles as $role) {
            $role->permissions()->syncWithoutDetaching([
                $permissions['manage_members']->id ?? 0,
                $permissions['view_marketing_reports']->id ?? 0,
                $permissions['manage_products']->id ?? 0,
                $permissions['conduct_outreach']->id ?? 0
            ]);
    }
}
}