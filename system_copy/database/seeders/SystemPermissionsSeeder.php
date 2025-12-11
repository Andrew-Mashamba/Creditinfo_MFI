<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SystemPermissionsSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Define modules with their permissions
            $modules = [
                'dashboard' => [
                    'name' => 'Dashboard',
                    'permissions' => [
                        'view' => 'View dashboard',
                        'export' => 'Export dashboard data',
                        'customize' => 'Customize dashboard widgets',
                    ]
                ],
                
                'branches' => [
                    'name' => 'Branches',
                    'permissions' => [
                        'view' => 'View branches',
                        'create' => 'Create new branch',
                        'edit' => 'Edit branch details',
                        'delete' => 'Delete branch',
                        'activate' => 'Activate/Deactivate branch',
                        'assign_users' => 'Assign users to branch',
                        'manage_settings' => 'Manage branch settings',
                    ]
                ],
                
                'clients' => [
                    'name' => 'Clients/Members',
                    'permissions' => [
                        'view' => 'View clients',
                        'create' => 'Register new client',
                        'edit' => 'Edit client information',
                        'delete' => 'Delete client',
                        'approve' => 'Approve client registration',
                        'activate' => 'Activate/Deactivate client',
                        'view_documents' => 'View client documents',
                        'upload_documents' => 'Upload client documents',
                        'view_financial' => 'View client financial details',
                        'export' => 'Export client data',
                    ]
                ],
                
                'shares' => [
                    'name' => 'Shares',
                    'permissions' => [
                        'view' => 'View shares',
                        'create' => 'Create share account',
                        'buy' => 'Buy shares',
                        'sell' => 'Sell shares',
                        'transfer' => 'Transfer shares',
                        'approve_transaction' => 'Approve share transactions',
                        'view_reports' => 'View share reports',
                        'manage_dividends' => 'Manage dividends',
                        'export' => 'Export share data',
                    ]
                ],
                
                'savings' => [
                    'name' => 'Savings',
                    'permissions' => [
                        'view' => 'View savings accounts',
                        'create' => 'Create savings account',
                        'deposit' => 'Make deposits',
                        'withdraw' => 'Make withdrawals',
                        'approve_transaction' => 'Approve savings transactions',
                        'view_statement' => 'View account statements',
                        'manage_interest' => 'Manage interest rates',
                        'close_account' => 'Close savings account',
                        'reactivate_account' => 'Reactivate account',
                        'export' => 'Export savings data',
                    ]
                ],
                
                'deposits' => [
                    'name' => 'Fixed Deposits',
                    'permissions' => [
                        'view' => 'View fixed deposits',
                        'create' => 'Create fixed deposit',
                        'approve' => 'Approve fixed deposit',
                        'liquidate' => 'Liquidate fixed deposit',
                        'renew' => 'Renew fixed deposit',
                        'manage_rates' => 'Manage deposit rates',
                        'view_maturity' => 'View maturity reports',
                        'export' => 'Export deposit data',
                    ]
                ],
                
                'loans' => [
                    'name' => 'Loans',
                    'permissions' => [
                        'view' => 'View loans',
                        'create' => 'Create loan application',
                        'edit' => 'Edit loan application',
                        'approve' => 'Approve loan',
                        'disburse' => 'Disburse loan',
                        'manage_repayment' => 'Manage repayments',
                        'restructure' => 'Restructure loan',
                        'write_off' => 'Write off loan',
                        'view_reports' => 'View loan reports',
                        'manage_collateral' => 'Manage collateral',
                        'manage_guarantors' => 'Manage guarantors',
                        'calculate_interest' => 'Calculate interest',
                        'waive_charges' => 'Waive charges',
                        'export' => 'Export loan data',
                    ]
                ],
                
                'products' => [
                    'name' => 'Products Management',
                    'permissions' => [
                        'view' => 'View products',
                        'create' => 'Create new product',
                        'edit' => 'Edit product',
                        'delete' => 'Delete product',
                        'activate' => 'Activate/Deactivate product',
                        'manage_fees' => 'Manage product fees',
                        'manage_rates' => 'Manage interest rates',
                        'clone' => 'Clone product',
                        'export' => 'Export product data',
                    ]
                ],
                
                'accounting' => [
                    'name' => 'Accounting',
                    'permissions' => [
                        'view_coa' => 'View chart of accounts',
                        'manage_coa' => 'Manage chart of accounts',
                        'create_journal' => 'Create journal entries',
                        'approve_journal' => 'Approve journal entries',
                        'reverse_journal' => 'Reverse journal entries',
                        'view_ledger' => 'View general ledger',
                        'view_trial_balance' => 'View trial balance',
                        'view_balance_sheet' => 'View balance sheet',
                        'view_income_statement' => 'View income statement',
                        'view_cash_flow' => 'View cash flow statement',
                        'close_period' => 'Close accounting period',
                        'manage_budget' => 'Manage budgets',
                        'export_reports' => 'Export accounting reports',
                        'manage_taxes' => 'Manage tax settings',
                    ]
                ],
                
                'services' => [
                    'name' => 'Services',
                    'permissions' => [
                        'view' => 'View services',
                        'create' => 'Create service',
                        'edit' => 'Edit service',
                        'delete' => 'Delete service',
                        'manage_fees' => 'Manage service fees',
                        'process_request' => 'Process service requests',
                        'approve_request' => 'Approve service requests',
                    ]
                ],
                
                'expenses' => [
                    'name' => 'Expenses',
                    'permissions' => [
                        'view' => 'View expenses',
                        'create' => 'Create expense',
                        'edit' => 'Edit expense',
                        'delete' => 'Delete expense',
                        'approve' => 'Approve expense',
                        'reimburse' => 'Process reimbursement',
                        'manage_categories' => 'Manage expense categories',
                        'view_reports' => 'View expense reports',
                        'export' => 'Export expense data',
                    ]
                ],
                
                'payments' => [
                    'name' => 'Payments',
                    'permissions' => [
                        'view' => 'View payments',
                        'process' => 'Process payments',
                        'approve' => 'Approve payments',
                        'reverse' => 'Reverse payments',
                        'reconcile' => 'Reconcile payments',
                        'manage_methods' => 'Manage payment methods',
                        'view_reports' => 'View payment reports',
                        'export' => 'Export payment data',
                    ]
                ],
                
                'investment' => [
                    'name' => 'Investments',
                    'permissions' => [
                        'view' => 'View investments',
                        'create' => 'Create investment',
                        'edit' => 'Edit investment',
                        'approve' => 'Approve investment',
                        'liquidate' => 'Liquidate investment',
                        'manage_portfolio' => 'Manage investment portfolio',
                        'view_returns' => 'View investment returns',
                        'export' => 'Export investment data',
                    ]
                ],
                
                'procurement' => [
                    'name' => 'Procurement',
                    'permissions' => [
                        'view' => 'View procurement',
                        'create_requisition' => 'Create purchase requisition',
                        'approve_requisition' => 'Approve requisition',
                        'create_po' => 'Create purchase order',
                        'approve_po' => 'Approve purchase order',
                        'manage_vendors' => 'Manage vendors',
                        'receive_goods' => 'Receive goods',
                        'view_reports' => 'View procurement reports',
                        'export' => 'Export procurement data',
                    ]
                ],
                
                'budget' => [
                    'name' => 'Budget Management',
                    'permissions' => [
                        'view' => 'View budgets',
                        'create' => 'Create budget',
                        'edit' => 'Edit budget',
                        'approve' => 'Approve budget',
                        'allocate' => 'Allocate budget',
                        'transfer' => 'Transfer budget',
                        'monitor' => 'Monitor budget utilization',
                        'view_variance' => 'View budget variance',
                        'export' => 'Export budget data',
                    ]
                ],
                
                'insurance' => [
                    'name' => 'Insurance',
                    'permissions' => [
                        'view' => 'View insurance policies',
                        'create' => 'Create insurance policy',
                        'edit' => 'Edit insurance policy',
                        'approve' => 'Approve insurance',
                        'process_claim' => 'Process insurance claims',
                        'approve_claim' => 'Approve claims',
                        'manage_premiums' => 'Manage premiums',
                        'view_reports' => 'View insurance reports',
                        'export' => 'Export insurance data',
                    ]
                ],
                
                'teller' => [
                    'name' => 'Teller Management',
                    'permissions' => [
                        'view' => 'View teller operations',
                        'open_till' => 'Open teller till',
                        'close_till' => 'Close teller till',
                        'cash_deposit' => 'Process cash deposits',
                        'cash_withdrawal' => 'Process cash withdrawals',
                        'transfer' => 'Process transfers',
                        'view_float' => 'View teller float',
                        'reconcile' => 'Reconcile teller',
                        'view_reports' => 'View teller reports',
                        'export' => 'Export teller data',
                    ]
                ],
                
                'reconciliation' => [
                    'name' => 'Reconciliation',
                    'permissions' => [
                        'view' => 'View reconciliations',
                        'create' => 'Create reconciliation',
                        'approve' => 'Approve reconciliation',
                        'bank_reconciliation' => 'Perform bank reconciliation',
                        'gl_reconciliation' => 'Perform GL reconciliation',
                        'suspense_reconciliation' => 'Reconcile suspense accounts',
                        'view_discrepancies' => 'View discrepancies',
                        'resolve_discrepancies' => 'Resolve discrepancies',
                        'export' => 'Export reconciliation data',
                    ]
                ],
                
                'hr' => [
                    'name' => 'Human Resources',
                    'permissions' => [
                        'view_employees' => 'View employees',
                        'create_employee' => 'Create employee',
                        'edit_employee' => 'Edit employee',
                        'terminate_employee' => 'Terminate employee',
                        'manage_payroll' => 'Manage payroll',
                        'approve_payroll' => 'Approve payroll',
                        'manage_leaves' => 'Manage leaves',
                        'approve_leaves' => 'Approve leaves',
                        'manage_attendance' => 'Manage attendance',
                        'view_reports' => 'View HR reports',
                        'manage_benefits' => 'Manage benefits',
                        'performance_review' => 'Conduct performance reviews',
                        'training_management' => 'Manage training',
                        'export' => 'Export HR data',
                    ]
                ],
                
                'self_services' => [
                    'name' => 'Self Services',
                    'permissions' => [
                        'view_profile' => 'View own profile',
                        'edit_profile' => 'Edit own profile',
                        'view_payslip' => 'View payslips',
                        'apply_leave' => 'Apply for leave',
                        'view_leave_balance' => 'View leave balance',
                        'submit_expense' => 'Submit expense claims',
                        'view_benefits' => 'View benefits',
                        'update_documents' => 'Update personal documents',
                    ]
                ],
                
                'approvals' => [
                    'name' => 'Approvals',
                    'permissions' => [
                        'view' => 'View pending approvals',
                        'approve_loan' => 'Approve loans',
                        'approve_withdrawal' => 'Approve withdrawals',
                        'approve_expense' => 'Approve expenses',
                        'approve_leave' => 'Approve leaves',
                        'approve_procurement' => 'Approve procurement',
                        'approve_journal' => 'Approve journal entries',
                        'delegate' => 'Delegate approvals',
                        'bulk_approve' => 'Bulk approve items',
                        'reject' => 'Reject requests',
                    ]
                ],
                
                'reports' => [
                    'name' => 'Reports',
                    'permissions' => [
                        'view' => 'View reports',
                        'generate' => 'Generate reports',
                        'schedule' => 'Schedule reports',
                        'export' => 'Export reports',
                        'customize' => 'Customize reports',
                        'share' => 'Share reports',
                        'delete' => 'Delete reports',
                        'view_audit' => 'View audit reports',
                        'view_compliance' => 'View compliance reports',
                        'view_management' => 'View management reports',
                    ]
                ],
                
                'profile' => [
                    'name' => 'Profile Settings',
                    'permissions' => [
                        'view' => 'View profile',
                        'edit' => 'Edit profile',
                        'change_password' => 'Change password',
                        'manage_2fa' => 'Manage two-factor authentication',
                        'manage_notifications' => 'Manage notifications',
                        'manage_preferences' => 'Manage preferences',
                        'view_activity' => 'View activity log',
                    ]
                ],
                
                'users' => [
                    'name' => 'User Management',
                    'permissions' => [
                        'view' => 'View users',
                        'create' => 'Create user',
                        'edit' => 'Edit user',
                        'delete' => 'Delete user',
                        'activate' => 'Activate/Deactivate user',
                        'reset_password' => 'Reset user password',
                        'manage_roles' => 'Manage user roles',
                        'manage_permissions' => 'Manage user permissions',
                        'view_activity' => 'View user activity',
                        'impersonate' => 'Impersonate user',
                        'export' => 'Export user data',
                    ]
                ],
                
                'active_loans' => [
                    'name' => 'Active Loans',
                    'permissions' => [
                        'view' => 'View active loans',
                        'manage_repayment' => 'Manage loan repayments',
                        'view_schedule' => 'View repayment schedule',
                        'print_schedule' => 'Print repayment schedule',
                        'view_arrears' => 'View loan arrears',
                        'send_reminder' => 'Send payment reminders',
                        'export' => 'Export active loans data',
                    ]
                ],
                
                'management' => [
                    'name' => 'Management',
                    'permissions' => [
                        'view_dashboard' => 'View management dashboard',
                        'view_analytics' => 'View analytics',
                        'view_kpi' => 'View KPIs',
                        'manage_targets' => 'Manage targets',
                        'view_performance' => 'View performance metrics',
                        'strategic_planning' => 'Access strategic planning',
                        'risk_management' => 'Manage risks',
                        'compliance_management' => 'Manage compliance',
                        'audit_management' => 'Manage audits',
                        'export' => 'Export management data',
                    ]
                ],
                
                'cash_management' => [
                    'name' => 'Cash Management',
                    'permissions' => [
                        'view' => 'View cash management',
                        'manage_vault' => 'Manage vault',
                        'cash_transfer' => 'Transfer cash',
                        'approve_transfer' => 'Approve cash transfers',
                        'manage_denominations' => 'Manage denominations',
                        'cash_counting' => 'Perform cash counting',
                        'view_position' => 'View cash position',
                        'set_limits' => 'Set cash limits',
                        'view_reports' => 'View cash reports',
                        'export' => 'Export cash data',
                    ]
                ],
                
                'billing' => [
                    'name' => 'Billing',
                    'permissions' => [
                        'view' => 'View bills',
                        'create' => 'Create bill',
                        'edit' => 'Edit bill',
                        'approve' => 'Approve bill',
                        'send' => 'Send bill',
                        'cancel' => 'Cancel bill',
                        'manage_templates' => 'Manage billing templates',
                        'recurring_billing' => 'Manage recurring bills',
                        'view_reports' => 'View billing reports',
                        'export' => 'Export billing data',
                    ]
                ],
                
                'transactions' => [
                    'name' => 'Transactions',
                    'permissions' => [
                        'view' => 'View transactions',
                        'create' => 'Create transaction',
                        'approve' => 'Approve transaction',
                        'reverse' => 'Reverse transaction',
                        'void' => 'Void transaction',
                        'batch_upload' => 'Batch upload transactions',
                        'reconcile' => 'Reconcile transactions',
                        'view_audit_trail' => 'View transaction audit trail',
                        'export' => 'Export transaction data',
                    ]
                ],
                
                'members_portal' => [
                    'name' => 'Members Portal',
                    'permissions' => [
                        'view' => 'View members portal',
                        'manage_content' => 'Manage portal content',
                        'manage_announcements' => 'Manage announcements',
                        'manage_faqs' => 'Manage FAQs',
                        'manage_feedback' => 'Manage member feedback',
                        'view_analytics' => 'View portal analytics',
                        'manage_settings' => 'Manage portal settings',
                    ]
                ],
                
                'email' => [
                    'name' => 'Email Management',
                    'permissions' => [
                        'view' => 'View emails',
                        'send' => 'Send emails',
                        'manage_templates' => 'Manage email templates',
                        'manage_campaigns' => 'Manage email campaigns',
                        'view_analytics' => 'View email analytics',
                        'manage_settings' => 'Manage email settings',
                        'bulk_email' => 'Send bulk emails',
                    ]
                ],
                
                'subscriptions' => [
                    'name' => 'Subscriptions',
                    'permissions' => [
                        'view' => 'View subscriptions',
                        'create' => 'Create subscription',
                        'edit' => 'Edit subscription',
                        'cancel' => 'Cancel subscription',
                        'renew' => 'Renew subscription',
                        'manage_plans' => 'Manage subscription plans',
                        'manage_billing' => 'Manage subscription billing',
                        'view_reports' => 'View subscription reports',
                        'export' => 'Export subscription data',
                    ]
                ],
            ];
            
            // Clear existing permissions
            Permission::truncate();
            
            // Create permissions for each module
            foreach ($modules as $moduleKey => $moduleData) {
                foreach ($moduleData['permissions'] as $action => $description) {
                    $permissionName = $moduleKey . '.' . $action;
                    $slug = Str::slug($permissionName, '_');
                    
                    Permission::create([
                        'name' => $permissionName,
                        'slug' => $slug,
                        'description' => $description,
                        'module' => $moduleData['name'],
                        'action' => $action,
                        'is_system' => true,
                        'guard_name' => 'web',
                    ]);
                    
                    $this->command->info("Created permission: {$permissionName}");
                }
            }
            
            // Add system-wide permissions
            $systemPermissions = [
                ['name' => 'system.access', 'description' => 'Access the system', 'module' => 'System'],
                ['name' => 'system.admin', 'description' => 'Full system administration', 'module' => 'System'],
                ['name' => 'system.settings', 'description' => 'Manage system settings', 'module' => 'System'],
                ['name' => 'system.backup', 'description' => 'Manage system backups', 'module' => 'System'],
                ['name' => 'system.logs', 'description' => 'View system logs', 'module' => 'System'],
                ['name' => 'system.maintenance', 'description' => 'Manage system maintenance', 'module' => 'System'],
                ['name' => 'system.integrations', 'description' => 'Manage system integrations', 'module' => 'System'],
                ['name' => 'system.api', 'description' => 'Manage API access', 'module' => 'System'],
                ['name' => 'system.security', 'description' => 'Manage security settings', 'module' => 'System'],
                ['name' => 'system.audit', 'description' => 'View system audit logs', 'module' => 'System'],
            ];
            
            foreach ($systemPermissions as $permission) {
                Permission::create([
                    'name' => $permission['name'],
                    'slug' => Str::slug($permission['name'], '_'),
                    'description' => $permission['description'],
                    'module' => $permission['module'],
                    'action' => explode('.', $permission['name'])[1],
                    'is_system' => true,
                    'guard_name' => 'web',
                ]);
                
                $this->command->info("Created system permission: {$permission['name']}");
            }
            
            DB::commit();
            
            $totalPermissions = Permission::count();
            $this->command->info("System Permissions seeding completed successfully!");
            $this->command->info("Total permissions created: {$totalPermissions}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding permissions: ' . $e->getMessage());
            throw $e;
        }
    }
}