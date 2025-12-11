<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Institution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaccosOrganizationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'NBC001')->first();

        // Clear existing data for clean structure
        DB::table('role_permissions')->truncate();
        DB::table('user_roles')->truncate();
        DB::table('roles')->truncate();
        
        // Don't truncate departments - use updateOrCreate instead

        // I. GOVERNANCE LEVEL (Elected by Members at the AGM)
        $this->createGovernanceStructure($institution);

        // II. MANAGEMENT LEVEL (Appointed by the Board of Directors)
        $this->createManagementStructure($institution);

        // III. KEY DEPARTMENTS UNDER MANAGEMENT
        $this->createOperationalDepartments($institution);

        // Create comprehensive permissions
        $this->createSaccosPermissions();

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function createGovernanceStructure($institution)
    {
        // 1. ANNUAL GENERAL MEETING (AGM) - Supreme authority
        $agm = Department::updateOrCreate(
            ['department_code' => 'AGM'],
            [
                'department_name' => 'Annual General Meeting (AGM)',
                'description' => 'The supreme authority of the SACCOS. Elected by members annually.',
                'status' => true,
                'level' => 1,
                'path' => 'AGM'
            ]
        );

        // 2. BOARD OF DIRECTORS (BOD)
        $bod = Department::updateOrCreate(
            ['department_code' => 'BOD'],
            [
                'department_name' => 'Board of Directors',
                'description' => 'Elected governing body responsible for strategic direction and oversight.',
                'status' => true,
                'level' => 2,
                'path' => 'AGM/BOD'
            ]
        );

        // Board roles
        $this->createBoardRoles($bod);

        // 3. BOARD SUB-COMMITTEES
        $this->createBoardSubCommittees($bod);

        // 4. SUPERVISORY COMMITTEE
        $supervisory = Department::updateOrCreate(
            ['department_code' => 'SUP'],
            [
                'department_name' => 'Supervisory Committee',
                'description' => 'Independent body that monitors internal controls and compliance.',
                'status' => true,
                'level' => 2,
                'path' => 'AGM/SUP'
            ]
        );

        $this->createSupervisoryRoles($supervisory);

        // 5. EXTERNAL AUDITOR
        $externalAuditor = Department::updateOrCreate(
            ['department_code' => 'EXT'],
            [
                'department_name' => 'External Auditor',
                'description' => 'Appointed by AGM for statutory financial audits.',
                'status' => true,
                'level' => 2,
                'path' => 'AGM/EXT'
            ]
        );

        Role::create([
            'name' => 'External Auditor',
            'department_id' => $externalAuditor->id,
            'description' => 'Independent external auditor for statutory compliance',
            'level' => 3,
            'is_system_role' => false,
            'permission_inheritance_enabled' => false,
            'department_specific' => true
        ]);
    }

    private function createBoardRoles($bodDepartment)
    {
        $boardRoles = [
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

        foreach ($boardRoles as $roleName => $description) {
            Role::create([
                'name' => $roleName,
                'department_id' => $bodDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createBoardSubCommittees($bodDepartment)
    {
        $subCommittees = [
            'AUD-COM' => 'Audit & Risk Committee',
            'FIN-COM' => 'Finance, Budget & Investment Committee',
            'CRD-COM' => 'Credit Committee',
            'EDU-COM' => 'Education & Training Committee',
            'ICT-COM' => 'ICT & Innovation Committee',
            'ETH-COM' => 'Ethics, Governance & Disciplinary Committee'
        ];

        foreach ($subCommittees as $code => $name) {
            $committee = Department::updateOrCreate(
                ['department_code' => $code],
                [
                'department_name' => $name,
                'description' => "Board sub-committee for {$name}",
                'status' => true,
                'level' => 3,
                'path' => "AGM/BOD/{$code}"
            ]);

            // Create committee roles
            $this->createCommitteeRoles($committee, $name);
        }
    }

    private function createCommitteeRoles($committee, $committeeName)
    {
        $roles = [
            "{$committeeName} Chairperson" => "Leads the {$committeeName}",
            "{$committeeName} Secretary" => "Maintains records for {$committeeName}",
            "{$committeeName} Member" => "Active member of {$committeeName}"
        ];

        foreach ($roles as $roleName => $description) {
            Role::create([
                'name' => $roleName,
                'department_id' => $committee->id,
                'description' => $description,
                'level' => 4,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createSupervisoryRoles($supervisoryDepartment)
    {
        $supervisoryRoles = [
            'Supervisory Committee Chairperson' => 'Leads the supervisory committee',
            'Supervisory Committee Secretary' => 'Maintains supervisory committee records',
            'Internal Controls Member' => 'Monitors internal control systems',
            'Financial Oversight Member' => 'Oversees financial integrity',
            'Compliance Monitoring Member' => 'Ensures policy compliance'
        ];

        foreach ($supervisoryRoles as $roleName => $description) {
            Role::create([
                'name' => $roleName,
                'department_id' => $supervisoryDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createManagementStructure($institution)
    {
        // General Manager / CEO
        $ceo = Department::updateOrCreate(
            ['department_code' => 'CEO'],
            [
                'department_name' => 'General Manager / CEO',
                'description' => 'Chief Executive Officer reporting to the Board',
                'status' => true,
                'level' => 2,
                'path' => 'CEO'
            ]
        );

        Role::create([
            'name' => 'General Manager / CEO',
            'department_id' => $ceo->id,
            'description' => 'Chief Executive Officer responsible for day-to-day operations',
            'level' => 3,
            'is_system_role' => false,
            'permission_inheritance_enabled' => true,
            'department_specific' => false
        ]);
    }

    private function createOperationalDepartments($institution)
    {
        // 1. OPERATIONS DEPARTMENT
        $operations = Department::updateOrCreate(
            ['department_code' => 'OPS'],
            [
                'department_name' => 'Operations Department',
                'description' => 'Core operational functions and branch management',
                'status' => true,
                'level' => 2,
                'path' => 'CEO/OPS'
            ]
        );

        $this->createOperationsRoles($operations);

        // 2. FINANCE & ACCOUNTS DEPARTMENT
        $finance = Department::updateOrCreate(
            ['department_code' => 'FIN'],
            [
                'department_name' => 'Finance & Accounts Department',
                'description' => 'Financial management, reporting, and accounting',
                'status' => true,
                'level' => 2,
                'path' => 'CEO/FIN'
            ]
        );

        $this->createFinanceRoles($finance);

        // 3. CREDIT & RECOVERY DEPARTMENT
        $credit = Department::updateOrCreate(
            ['department_code' => 'CRD'],
            [
                'department_name' => 'Credit & Recovery Department',
                'description' => 'Loan management, appraisal, and recovery',
                'status' => true,
                'level' => 2,
                'path' => 'CEO/CRD'
            ]
        );

        $this->createCreditRoles($credit);

        // 4. RISK & COMPLIANCE DEPARTMENT
        $risk = Department::updateOrCreate(
            ['department_code' => 'RCD'],
            [
                'department_name' => 'Risk & Compliance Department',
                'description' => 'Risk management and regulatory compliance',
                'status' => true,
                'level' => 2,
                'path' => 'CEO/RCD'
            ]
        );

        $this->createRiskRoles($risk);

        // 5. HUMAN RESOURCE & ADMINISTRATION
        $hr = Department::updateOrCreate(
            ['department_code' => 'HRA'],
            [
                'department_name' => 'Human Resource & Administration',
                'description' => 'HR management and administrative functions',
                'status' => true,
                'level' => 2,
                'path' => 'CEO/HRA'
            ]
        );

        $this->createHRRoles($hr);

        // 6. MARKETING & MEMBER RELATIONS
        $marketing = Department::updateOrCreate(
            ['department_code' => 'MMR'],
            [
                'department_name' => 'Marketing & Member Relations',
                'description' => 'Member services, business development, and marketing',
                'status' => true,
                'level' => 2,
                'path' => 'CEO/MMR'
            ]
        );

        $this->createMarketingRoles($marketing);
    }

    private function createOperationsRoles($operationsDepartment)
    {
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
            Role::create([
                'name' => $roleName,
                'department_id' => $operationsDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createFinanceRoles($financeDepartment)
    {
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
            Role::create([
                'name' => $roleName,
                'department_id' => $financeDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createCreditRoles($creditDepartment)
    {
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
            Role::create([
                'name' => $roleName,
                'department_id' => $creditDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createRiskRoles($riskDepartment)
    {
        $roles = [
            'Risk Manager' => 'Oversees risk management framework',
            'Compliance Officer' => 'Ensures regulatory compliance (TCDC, BOT, FCC)',
            'AML/CFT Officer' => 'Monitors anti-money laundering compliance',
            'Risk Assessment Officer' => 'Conducts risk assessments and analysis',
            'Internal Control Officer' => 'Performs internal control checks',
            'Fraud Prevention Officer' => 'Monitors and prevents fraudulent activities'
        ];

        foreach ($roles as $roleName => $description) {
            Role::create([
                'name' => $roleName,
                'department_id' => $riskDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createHRRoles($hrDepartment)
    {
        $roles = [
            'HR Manager' => 'Oversees human resource operations',
            'Recruitment Officer' => 'Handles staff recruitment and selection',
            'Training Officer' => 'Manages staff training and development',
            'Performance Management Officer' => 'Oversees staff performance and appraisals',
            'Administrative Officer' => 'Manages office facilities and logistics',
            'Legal Officer' => 'Handles legal matters and contracts'
        ];

        foreach ($roles as $roleName => $description) {
            Role::create([
                'name' => $roleName,
                'department_id' => $hrDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
        }
    }

    private function createMarketingRoles($marketingDepartment)
    {
        $roles = [
            'Marketing Manager' => 'Oversees marketing and member relations',
            'Member Services Officer' => 'Handles member inquiries and support',
            'Business Development Officer' => 'Focuses on member recruitment and retention',
            'Branding Officer' => 'Manages brand identity and communication',
            'Product Development Officer' => 'Develops new products and services',
            'Community Outreach Officer' => 'Manages community engagement programs'
        ];

        foreach ($roles as $roleName => $description) {
            Role::create([
                'name' => $roleName,
                'department_id' => $marketingDepartment->id,
                'description' => $description,
                'level' => 3,
                'is_system_role' => false,
                'permission_inheritance_enabled' => true,
                'department_specific' => true
            ]);
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
            Permission::updateOrCreate(
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
        $boardRoles = Role::where('department_id', Department::where('department_code', 'BOD')->first()->id)->get();
        foreach ($boardRoles as $role) {
            $role->permissions()->attach([
                $permissions['view_agm']->id,
                $permissions['manage_board']->id,
                $permissions['view_supervisory']->id,
                $permissions['manage_committees']->id
            ]);
        }

        // Supervisory committee gets oversight permissions
        $supervisoryRoles = Role::where('department_id', Department::where('department_code', 'SUP')->first()->id)->get();
        foreach ($supervisoryRoles as $role) {
            $role->permissions()->attach([
                $permissions['view_agm']->id,
                $permissions['view_supervisory']->id,
                $permissions['conduct_audits']->id,
                $permissions['view_audit_logs']->id
            ]);
        }
    }

    private function assignOperationalPermissions($permissions)
    {
        $operationsRoles = Role::where('department_id', Department::where('department_code', 'OPS')->first()->id)->get();
        foreach ($operationsRoles as $role) {
            $role->permissions()->attach([
                $permissions['manage_branches']->id,
                $permissions['process_transactions']->id,
                $permissions['manage_tellers']->id,
                $permissions['manage_it_systems']->id
            ]);
        }
    }

    private function assignFinancePermissions($permissions)
    {
        $financeRoles = Role::where('department_id', Department::where('department_code', 'FIN')->first()->id)->get();
        foreach ($financeRoles as $role) {
            $role->permissions()->attach([
                $permissions['view_financial_reports']->id,
                $permissions['manage_accounts']->id,
                $permissions['process_payroll']->id,
                $permissions['manage_budget']->id
            ]);
        }
    }

    private function assignCreditPermissions($permissions)
    {
        $creditRoles = Role::where('department_id', Department::where('department_code', 'CRD')->first()->id)->get();
        foreach ($creditRoles as $role) {
            $role->permissions()->attach([
                $permissions['approve_loans']->id,
                $permissions['view_credit_reports']->id,
                $permissions['manage_recovery']->id,
                $permissions['process_crb_reports']->id
            ]);
        }
    }

    private function assignRiskPermissions($permissions)
    {
        $riskRoles = Role::where('department_id', Department::where('department_code', 'RCD')->first()->id)->get();
        foreach ($riskRoles as $role) {
            $role->permissions()->attach([
                $permissions['view_risk_reports']->id,
                $permissions['manage_compliance']->id,
                $permissions['monitor_aml']->id,
                $permissions['conduct_audits']->id
            ]);
        }
    }

    private function assignHRPermissions($permissions)
    {
        $hrRoles = Role::where('department_id', Department::where('department_code', 'HRA')->first()->id)->get();
        foreach ($hrRoles as $role) {
            $role->permissions()->attach([
                $permissions['manage_staff']->id,
                $permissions['view_hr_reports']->id,
                $permissions['manage_training']->id,
                $permissions['process_recruitment']->id
            ]);
        }
    }

    private function assignMarketingPermissions($permissions)
    {
        $marketingRoles = Role::where('department_id', Department::where('department_code', 'MMR')->first()->id)->get();
        foreach ($marketingRoles as $role) {
            $role->permissions()->attach([
                $permissions['manage_members']->id,
                $permissions['view_marketing_reports']->id,
                $permissions['manage_products']->id,
                $permissions['conduct_outreach']->id
            ]);
    }
}
}