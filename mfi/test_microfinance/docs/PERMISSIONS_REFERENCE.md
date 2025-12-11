# SACCOS System Permissions Reference

## Total Permissions: 301 across 31 Modules

---

## 1. Accounting Module (14 permissions)
- `accounting.approve_journal` - Approve journal entries
- `accounting.close_period` - Close accounting periods
- `accounting.create_journal` - Create journal entries
- `accounting.export_reports` - Export accounting reports
- `accounting.manage_budget` - Manage budgets
- `accounting.manage_coa` - Manage Chart of Accounts
- `accounting.manage_taxes` - Manage tax settings
- `accounting.reverse_journal` - Reverse journal entries
- `accounting.view_balance_sheet` - View balance sheet
- `accounting.view_cash_flow` - View cash flow statement
- `accounting.view_coa` - View Chart of Accounts
- `accounting.view_income_statement` - View income statement
- `accounting.view_ledger` - View general ledger
- `accounting.view_trial_balance` - View trial balance

## 2. Active Loans Module (7 permissions)
- `active_loans.export` - Export active loans data
- `active_loans.manage_repayment` - Manage loan repayments
- `active_loans.print_schedule` - Print loan schedules
- `active_loans.send_reminder` - Send payment reminders
- `active_loans.view` - View active loans
- `active_loans.view_arrears` - View loans in arrears
- `active_loans.view_schedule` - View loan schedules

## 3. Approvals Module (10 permissions)
- `approvals.approve_expense` - Approve expense requests
- `approvals.approve_journal` - Approve journal entries
- `approvals.approve_leave` - Approve leave requests
- `approvals.approve_loan` - Approve loan applications
- `approvals.approve_procurement` - Approve procurement requests
- `approvals.approve_withdrawal` - Approve withdrawal requests
- `approvals.bulk_approve` - Bulk approve multiple items
- `approvals.delegate` - Delegate approval authority
- `approvals.reject` - Reject approval requests
- `approvals.view` - View approval requests

## 4. Billing Module (10 permissions)
- `billing.approve` - Approve bills
- `billing.cancel` - Cancel bills
- `billing.create` - Create bills
- `billing.edit` - Edit bills
- `billing.export` - Export billing data
- `billing.manage_templates` - Manage billing templates
- `billing.recurring_billing` - Manage recurring bills
- `billing.send` - Send bills
- `billing.view` - View bills
- `billing.view_reports` - View billing reports

## 5. Branches Module (7 permissions)
- `branches.activate` - Activate/deactivate branches
- `branches.assign_users` - Assign users to branches
- `branches.create` - Create new branches
- `branches.delete` - Delete branches
- `branches.edit` - Edit branch details
- `branches.manage_settings` - Manage branch settings
- `branches.view` - View branches

## 6. Budget Module (9 permissions)
- `budget.allocate` - Allocate budget amounts
- `budget.approve` - Approve budgets
- `budget.create` - Create budgets
- `budget.edit` - Edit budgets
- `budget.export` - Export budget data
- `budget.monitor` - Monitor budget execution
- `budget.transfer` - Transfer between budget lines
- `budget.view` - View budgets
- `budget.view_variance` - View budget variance reports

## 7. Cash Management Module (10 permissions)
- `cash_management.approve_transfer` - Approve cash transfers
- `cash_management.cash_counting` - Perform cash counting
- `cash_management.cash_transfer` - Initiate cash transfers
- `cash_management.export` - Export cash management data
- `cash_management.manage_denominations` - Manage cash denominations
- `cash_management.manage_vault` - Manage vault operations
- `cash_management.set_limits` - Set cash limits
- `cash_management.view` - View cash management
- `cash_management.view_position` - View cash position
- `cash_management.view_reports` - View cash reports

## 8. Clients Module (10 permissions)
- `clients.activate` - Activate/deactivate clients
- `clients.approve` - Approve client applications
- `clients.create` - Create new clients
- `clients.delete` - Delete clients
- `clients.edit` - Edit client information
- `clients.export` - Export client data
- `clients.upload_documents` - Upload client documents
- `clients.view` - View clients
- `clients.view_documents` - View client documents
- `clients.view_financial` - View client financials

## 9. Dashboard Module (3 permissions)
- `dashboard.customize` - Customize dashboard layout
- `dashboard.export` - Export dashboard data
- `dashboard.view` - View dashboard

## 10. Deposits Module (8 permissions)
- `deposits.approve` - Approve deposits
- `deposits.create` - Create deposits
- `deposits.export` - Export deposit data
- `deposits.liquidate` - Liquidate deposits
- `deposits.manage_rates` - Manage deposit rates
- `deposits.renew` - Renew deposits
- `deposits.view` - View deposits
- `deposits.view_maturity` - View maturity schedules

## 11. Email Module (7 permissions)
- `email.bulk_email` - Send bulk emails
- `email.manage_campaigns` - Manage email campaigns
- `email.manage_settings` - Manage email settings
- `email.manage_templates` - Manage email templates
- `email.send` - Send emails
- `email.view` - View emails
- `email.view_analytics` - View email analytics

## 12. Expenses Module (9 permissions)
- `expenses.approve` - Approve expenses
- `expenses.create` - Create expenses
- `expenses.delete` - Delete expenses
- `expenses.edit` - Edit expenses
- `expenses.export` - Export expense data
- `expenses.manage_categories` - Manage expense categories
- `expenses.reimburse` - Process reimbursements
- `expenses.view` - View expenses
- `expenses.view_reports` - View expense reports

## 13. HR Module (14 permissions)
- `hr.approve_leaves` - Approve leave requests
- `hr.approve_payroll` - Approve payroll
- `hr.create_employee` - Create employee records
- `hr.edit_employee` - Edit employee records
- `hr.export` - Export HR data
- `hr.manage_attendance` - Manage attendance
- `hr.manage_benefits` - Manage employee benefits
- `hr.manage_leaves` - Manage leave system
- `hr.manage_payroll` - Manage payroll
- `hr.performance_review` - Conduct performance reviews
- `hr.terminate_employee` - Terminate employees
- `hr.training_management` - Manage training programs
- `hr.view_employees` - View employee records
- `hr.view_reports` - View HR reports

## 14. Insurance Module (9 permissions)
- `insurance.approve` - Approve insurance policies
- `insurance.approve_claim` - Approve insurance claims
- `insurance.create` - Create insurance policies
- `insurance.edit` - Edit insurance policies
- `insurance.export` - Export insurance data
- `insurance.manage_premiums` - Manage insurance premiums
- `insurance.process_claim` - Process insurance claims
- `insurance.view` - View insurance policies
- `insurance.view_reports` - View insurance reports

## 15. Investment Module (8 permissions)
- `investment.approve` - Approve investments
- `investment.create` - Create investments
- `investment.edit` - Edit investments
- `investment.export` - Export investment data
- `investment.liquidate` - Liquidate investments
- `investment.manage_portfolio` - Manage investment portfolio
- `investment.view` - View investments
- `investment.view_returns` - View investment returns

## 16. Loans Module (14 permissions)
- `loans.approve` - Approve loan applications
- `loans.calculate_interest` - Calculate loan interest
- `loans.create` - Create loan applications
- `loans.disburse` - Disburse loans
- `loans.edit` - Edit loan details
- `loans.export` - Export loan data
- `loans.manage_collateral` - Manage loan collateral
- `loans.manage_guarantors` - Manage loan guarantors
- `loans.manage_repayment` - Manage loan repayments
- `loans.restructure` - Restructure loans
- `loans.view` - View loans
- `loans.view_reports` - View loan reports
- `loans.waive_charges` - Waive loan charges
- `loans.write_off` - Write off bad loans

## 17. Management Module (10 permissions)
- `management.audit_management` - Manage audits
- `management.compliance_management` - Manage compliance
- `management.export` - Export management data
- `management.manage_targets` - Manage targets
- `management.risk_management` - Manage risks
- `management.strategic_planning` - Strategic planning
- `management.view_analytics` - View analytics
- `management.view_dashboard` - View management dashboard
- `management.view_kpi` - View KPIs
- `management.view_performance` - View performance metrics

## 18. Members Portal Module (7 permissions)
- `members_portal.manage_announcements` - Manage announcements
- `members_portal.manage_content` - Manage portal content
- `members_portal.manage_faqs` - Manage FAQs
- `members_portal.manage_feedback` - Manage feedback
- `members_portal.manage_settings` - Manage portal settings
- `members_portal.view` - View portal
- `members_portal.view_analytics` - View portal analytics

## 19. Payments Module (8 permissions)
- `payments.approve` - Approve payments
- `payments.export` - Export payment data
- `payments.manage_methods` - Manage payment methods
- `payments.process` - Process payments
- `payments.reconcile` - Reconcile payments
- `payments.reverse` - Reverse payments
- `payments.view` - View payments
- `payments.view_reports` - View payment reports

## 20. Procurement Module (9 permissions)
- `procurement.approve_po` - Approve purchase orders
- `procurement.approve_requisition` - Approve requisitions
- `procurement.create_po` - Create purchase orders
- `procurement.create_requisition` - Create requisitions
- `procurement.export` - Export procurement data
- `procurement.manage_vendors` - Manage vendors
- `procurement.receive_goods` - Receive goods
- `procurement.view` - View procurement
- `procurement.view_reports` - View procurement reports

## 21. Products Module (9 permissions)
- `products.activate` - Activate/deactivate products
- `products.clone` - Clone products
- `products.create` - Create products
- `products.delete` - Delete products
- `products.edit` - Edit products
- `products.export` - Export product data
- `products.manage_fees` - Manage product fees
- `products.manage_rates` - Manage product rates
- `products.view` - View products

## 22. Profile Module (7 permissions)
- `profile.change_password` - Change password
- `profile.edit` - Edit profile
- `profile.manage_2fa` - Manage 2FA settings
- `profile.manage_notifications` - Manage notifications
- `profile.manage_preferences` - Manage preferences
- `profile.view` - View profile
- `profile.view_activity` - View activity log

## 23. Reconciliation Module (9 permissions)
- `reconciliation.approve` - Approve reconciliations
- `reconciliation.bank_reconciliation` - Perform bank reconciliation
- `reconciliation.create` - Create reconciliations
- `reconciliation.export` - Export reconciliation data
- `reconciliation.gl_reconciliation` - Perform GL reconciliation
- `reconciliation.resolve_discrepancies` - Resolve discrepancies
- `reconciliation.suspense_reconciliation` - Perform suspense reconciliation
- `reconciliation.view` - View reconciliations
- `reconciliation.view_discrepancies` - View discrepancies

## 24. Reports Module (10 permissions)
- `reports.customize` - Customize reports
- `reports.delete` - Delete reports
- `reports.export` - Export reports
- `reports.generate` - Generate reports
- `reports.schedule` - Schedule reports
- `reports.share` - Share reports
- `reports.view` - View reports
- `reports.view_audit` - View audit reports
- `reports.view_compliance` - View compliance reports
- `reports.view_management` - View management reports

## 25. Savings Module (10 permissions)
- `savings.approve_transaction` - Approve savings transactions
- `savings.close_account` - Close savings accounts
- `savings.create` - Create savings accounts
- `savings.deposit` - Make deposits
- `savings.export` - Export savings data
- `savings.manage_interest` - Manage interest rates
- `savings.reactivate_account` - Reactivate accounts
- `savings.view` - View savings accounts
- `savings.view_statement` - View account statements
- `savings.withdraw` - Make withdrawals

## 26. Self Services Module (8 permissions)
- `self_services.apply_leave` - Apply for leave
- `self_services.edit_profile` - Edit own profile
- `self_services.submit_expense` - Submit expense claims
- `self_services.update_documents` - Update documents
- `self_services.view_benefits` - View benefits
- `self_services.view_leave_balance` - View leave balance
- `self_services.view_payslip` - View payslips
- `self_services.view_profile` - View own profile

## 27. Services Module (7 permissions)
- `services.approve_request` - Approve service requests
- `services.create` - Create services
- `services.delete` - Delete services
- `services.edit` - Edit services
- `services.manage_fees` - Manage service fees
- `services.process_request` - Process service requests
- `services.view` - View services

## 28. Shares Module (9 permissions)
- `shares.approve_transaction` - Approve share transactions
- `shares.buy` - Buy shares
- `shares.create` - Create share accounts
- `shares.export` - Export share data
- `shares.manage_dividends` - Manage dividends
- `shares.sell` - Sell shares
- `shares.transfer` - Transfer shares
- `shares.view` - View shares
- `shares.view_reports` - View share reports

## 29. Subscriptions Module (9 permissions)
- `subscriptions.cancel` - Cancel subscriptions
- `subscriptions.create` - Create subscriptions
- `subscriptions.edit` - Edit subscriptions
- `subscriptions.export` - Export subscription data
- `subscriptions.manage_billing` - Manage subscription billing
- `subscriptions.manage_plans` - Manage subscription plans
- `subscriptions.renew` - Renew subscriptions
- `subscriptions.view` - View subscriptions
- `subscriptions.view_reports` - View subscription reports

## 30. System Module (10 permissions)
- `system.access` - Access system settings
- `system.admin` - System administration
- `system.api` - Manage API settings
- `system.audit` - View system audit logs
- `system.backup` - Manage system backups
- `system.integrations` - Manage integrations
- `system.logs` - View system logs
- `system.maintenance` - Perform system maintenance
- `system.security` - Manage security settings
- `system.settings` - Manage system settings

## 31. Teller Module (10 permissions)
- `teller.cash_deposit` - Process cash deposits
- `teller.cash_withdrawal` - Process cash withdrawals
- `teller.close_till` - Close teller till
- `teller.export` - Export teller data
- `teller.open_till` - Open teller till
- `teller.reconcile` - Reconcile till
- `teller.transfer` - Process transfers
- `teller.view` - View teller operations
- `teller.view_float` - View till float
- `teller.view_reports` - View teller reports

## 32. Transactions Module (9 permissions)
- `transactions.approve` - Approve transactions
- `transactions.batch_upload` - Batch upload transactions
- `transactions.create` - Create transactions
- `transactions.export` - Export transaction data
- `transactions.reconcile` - Reconcile transactions
- `transactions.reverse` - Reverse transactions
- `transactions.view` - View transactions
- `transactions.view_audit_trail` - View audit trail
- `transactions.void` - Void transactions

## 33. Users Module (11 permissions)
- `users.activate` - Activate/deactivate users
- `users.create` - Create users
- `users.delete` - Delete users
- `users.edit` - Edit users
- `users.export` - Export user data
- `users.impersonate` - Impersonate users
- `users.manage_permissions` - Manage user permissions
- `users.manage_roles` - Manage user roles
- `users.reset_password` - Reset user passwords
- `users.view` - View users
- `users.view_activity` - View user activity

---

## Role Permission Templates

### 1. IT Manager
- **Full System Access**: All 301 permissions

### 2. Chief Accountant
- Dashboard (view, export, customize)
- Accounting (all permissions)
- Billing (all permissions)
- Budget (all permissions)
- Expenses (all permissions)
- Payments (all permissions)
- Reconciliation (all permissions)
- Reports (all permissions)
- Deposits (view, export, view_maturity)
- Loans (view, view_reports, export)
- Shares (view, view_reports, export)
- Savings (view, view_statement, export)
- Investment (all permissions)
- Insurance (view, view_reports)
- Cash Management (view, view_position, view_reports)
- Transactions (all permissions)

### 3. Accountant
- Dashboard (view)
- Accounting (view_coa, create_journal, view_ledger, view_trial_balance, view_balance_sheet, view_income_statement, view_cash_flow)
- Billing (view, create, edit, send, view_reports)
- Expenses (view, create, edit, view_reports)
- Payments (view, process, view_reports)
- Reconciliation (view, create, view_discrepancies)
- Reports (view, generate, export)
- Transactions (view, create, view_audit_trail)

### 4. Branch Manager
- Dashboard (view, export, customize)
- Branches (view, edit, manage_settings, assign_users)
- Clients (all permissions)
- Loans (all permissions)
- Deposits (all permissions)
- Savings (all permissions)
- Shares (all permissions)
- Cash Management (all permissions)
- Teller (view, view_float, view_reports)
- Reports (all permissions)
- Management (view_dashboard, view_analytics, view_kpi, view_performance)
- Approvals (all permissions)

### 5. Loan Officer
- Dashboard (view)
- Clients (view, create, edit, view_documents, view_financial)
- Loans (view, create, edit, manage_repayment, manage_collateral, manage_guarantors, view_reports)
- Active Loans (all permissions)
- Reports (view, generate, export)

### 6. Teller
- Dashboard (view)
- Teller (all permissions)
- Cash Management (view, cash_counting, view_position)
- Deposits (create, view)
- Savings (deposit, withdraw, view)
- Payments (process, view)
- Transactions (create, view)

### 7. HR Manager
- Dashboard (view, export)
- HR (all permissions)
- Self Services (all permissions)
- Users (view, create, edit, manage_roles, view_activity)
- Reports (view, generate, export)

### 8. Compliance Officer
- Dashboard (view)
- Reports (all permissions)
- Management (audit_management, compliance_management, risk_management)
- Reconciliation (view, view_discrepancies)
- Transactions (view, view_audit_trail)
- System (audit, logs)

### 9. Customer Service Representative
- Dashboard (view)
- Clients (view, create, edit, upload_documents)
- Shares (view)
- Savings (view, view_statement)
- Deposits (view)
- Loans (view)
- Members Portal (view, manage_faqs, manage_feedback)

### 10. Auditor
- Dashboard (view)
- Accounting (view_coa, view_ledger, view_trial_balance, view_balance_sheet, view_income_statement, view_cash_flow)
- Reports (all permissions)
- Transactions (view, view_audit_trail)
- System (audit, logs)
- Management (audit_management, view_analytics)

---

## Permission Naming Convention
All permissions follow the pattern: `module.action`

Examples:
- `loans.create` - Permission to create loans
- `accounting.view_ledger` - Permission to view general ledger
- `users.manage_roles` - Permission to manage user roles

## Notes
1. Permissions are hierarchical - roles inherit permissions, and sub-roles can inherit from roles
2. Users can have multiple roles, and their permissions are aggregated
3. Some permissions may require additional business logic validation
4. The system supports both role-based and individual permission assignment
5. All permission changes are logged for audit purposes