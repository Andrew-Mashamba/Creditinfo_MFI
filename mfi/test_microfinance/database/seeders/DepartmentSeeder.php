<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Institution;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'NBC001')->first();

        // Governance & Oversight Departments
        $governance = Department::create([
            'department_name' => 'Governance & Oversight',
            'department_code' => 'GOV',
            'description' => 'Governance and oversight functions',
            'status' => true,
            'level' => 1,
            'path' => 'GOV'
        ]);

        // Board of Directors
        Department::create([
            'department_name' => 'Board of Directors',
            'department_code' => 'BOD',
            'parent_department_id' => $governance->id,
            'description' => 'Elected governing body responsible for strategic direction and oversight. Approves policies and budgets, hires and evaluates the General Manager/CEO, oversees risk and compliance, ensures member interests are protected.',
            'status' => true,
            'level' => 2,
            'path' => 'GOV/BOD'
        ]);

        // Supervisory Committee
        Department::create([
            'department_name' => 'Supervisory Committee',
            'department_code' => 'SUP',
            'parent_department_id' => $governance->id,
            'description' => 'Independent body that monitors internal controls, financial integrity, and board conduct. Conducts internal audits, policy compliance checks, and reports irregularities to members.',
            'status' => true,
            'level' => 2,
            'path' => 'GOV/SUP'
        ]);

        // Core Management & Operational Departments
        $operations = Department::create([
            'department_name' => 'Core Management & Operations',
            'department_code' => 'CMO',
            'description' => 'Core management and operational departments',
            'status' => true,
            'level' => 1,
            'path' => 'CMO'
        ]);

        // Administration & General Services
        Department::create([
            'department_name' => 'Administration & General Services',
            'department_code' => 'AGS',
            'parent_department_id' => $operations->id,
            'description' => 'Handles day-to-day office operations, facilities, and logistics. Office management, document control, transport, security, maintenance, procurement and logistics support.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/AGS']);

        // Finance & Accounting
        Department::create([
            'department_name' => 'Finance & Accounting',
            'department_code' => 'FAC',
            'parent_department_id' => $operations->id,
            'description' => 'Manages all financial activities and reporting. Budgeting and forecasting, financial statements and audits, payroll and expense management, tax and statutory compliance.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/FAC']);

        // Credit Department
        Department::create([
            'department_name' => 'Credit Department',
            'department_code' => 'CRD',
            'parent_department_id' => $operations->id,
            'description' => 'Manages the full loan lifecycle. Loan application processing, risk and creditworthiness assessment, loan monitoring and recovery, delinquency management.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/CRD']);

        // Savings & Deposits
        Department::create([
            'department_name' => 'Savings & Deposits',
            'department_code' => 'SVD',
            'parent_department_id' => $operations->id,
            'description' => 'Handles member contributions and savings products. Savings account management, fixed deposits and share capital, withdrawal and interest processing.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/SVD']);

        // Member Services
        Department::create([
            'department_name' => 'Member Services',
            'department_code' => 'MBS',
            'parent_department_id' => $operations->id,
            'description' => 'Ensures quality service and member satisfaction. Member registration and onboarding, queries and support, member education and outreach.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/MBS']);

        // Internal Audit & Compliance
        Department::create([
            'department_name' => 'Internal Audit & Compliance',
            'department_code' => 'IAC',
            'parent_department_id' => $operations->id,
            'description' => 'Provides assurance on internal controls and regulatory adherence. Regular audits and compliance reviews, fraud detection and investigations, risk monitoring.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/IAC']);

        // Human Resources
        Department::create([
            'department_name' => 'Human Resources',
            'department_code' => 'HRD',
            'parent_department_id' => $operations->id,
            'description' => 'Manages personnel and workplace development. Recruitment and onboarding, staff performance and training, leave and benefits administration, policy development.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/HRD']);

        // ICT Department
        Department::create([
            'department_name' => 'Information Systems',
            'department_code' => 'ICT',
            'parent_department_id' => $operations->id,
            'description' => 'Maintains technology infrastructure and digital services. Core banking system support, network security and data backups, mobile and online platform support, IT policy compliance.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/ICT']);

        // Marketing & Business Development
        Department::create([
            'department_name' => 'Marketing & Business Development',
            'department_code' => 'MBD',
            'parent_department_id' => $operations->id,
            'description' => 'Drives member growth and product innovation. Branding and promotions, member recruitment, partnership building, product development.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/MBD']);

        // Legal & Governance
        Department::create([
            'department_name' => 'Legal & Governance',
            'department_code' => 'LGD',
            'parent_department_id' => $operations->id,
            'description' => 'Manages legal matters and supports good governance practices. Contract review and drafting, legal dispute resolution, governance compliance, advising board and management.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/LGD']);

        // Risk Management
        Department::create([
            'department_name' => 'Risk Management',
            'department_code' => 'RMD',
            'parent_department_id' => $operations->id,
            'description' => 'Assesses and mitigates organizational risks. Risk identification and registers, business continuity planning, insurance and crisis management.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/RMD']);

        // Procurement & Logistics
        Department::create([
            'department_name' => 'Procurement & Logistics',
            'department_code' => 'PRL',
            'parent_department_id' => $operations->id,
            'description' => 'Handles supply chain and acquisition of goods/services. Vendor selection and tendering, inventory and asset tracking, purchasing and delivery logistics.',
            'status' => true,
            'level' => 2,
            'path' => 'CMO/PRL']);
    }
}