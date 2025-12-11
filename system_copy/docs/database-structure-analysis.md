# SACCOS Core System - Database Structure Analysis

## Overview
The SACCOS Core System has a comprehensive database structure with:
- **276 migration files** defining the database schema
- **247 seeder files** for populating initial and test data
- PostgreSQL as the primary database (based on `SET session_replication_role` commands)

## Database Architecture

### Core System Tables

#### 1. **Institutions & Organization**
- `institutions` - Master institution/SACCO configuration (85 columns!)
  - Includes settings for shares, accounts, depreciation, contact info
  - Multi-tenancy support with database connection configs
  - Financial account mappings for GL integration
- `branches` - Branch management
- `departments` - Organizational departments
- `groups` - Member groups
- `committees` - Committee management
- `committee_members` - Committee membership

#### 2. **User & Access Management**
- `users` - System users with authentication
- `roles` - Role definitions
- `subroles` - Sub-role hierarchy
- `permissions` - Granular permissions
- `role_permissions` - Role-permission mappings
- `user_roles` - User-role assignments
- `user_permissions` - Direct user permissions
- `user_subroles` - User sub-role assignments
- `menus` - Menu structure
- `sub_menus` - Sub-menu items
- `menu_actions` - Menu-based actions
- `role_menu_actions` - Role-based menu access

#### 3. **Member Management**
- `clients` - Member/client records
- `client_documents` - Member documentation
- `pending_registrations` - New member applications
- `member_categories` - Member classification
- `webportal_users` - Member portal access
- `member_exits` - Member exit/withdrawal tracking

#### 4. **Financial Accounts**
- `accounts` - Core accounts table
- `sub_accounts` - Sub-account hierarchy
- `general_ledger` - GL entries
- `account_historical_balances` - Balance history
- Account type tables:
  - `asset_accounts`
  - `capital_accounts`
  - `expense_accounts`
  - `income_accounts`
  - `liability_accounts`
  - `budget_accounts`

#### 5. **Loan Management**
- `loans` - Master loan records
- `loans_summary` - Loan summary data
- `loans_schedules` - Repayment schedules
- `loans_arreas` - Arrears tracking
- `loans_originated` - Loan origination details
- `loan_approvals` - Approval workflow
- `loan_stages` - Loan processing stages
- `current_loans_stages` - Current stage tracking
- `loan_process_progress` - Process monitoring
- `loan_product_charges` - Product-specific charges
- `loan_provision_settings` - Provision configurations
- `loan_collateral` - Collateral management
- `loan_guarantors` - Guarantor records
- `maendeleo_loans` - Special loan types
- `settled_loans` - Completed loans
- `short_long_term_loans` - Loan categorization

#### 6. **Transaction Processing**
- `transactions` - Core transaction records
- `transaction_audit_logs` - Transaction audit trail
- `transaction_reversals` - Reversal tracking
- `reconciled_transactions` - Reconciliation status
- `reconciliation_staging_table` - Reconciliation workspace
- `bank_transactions` - Bank-specific transactions
- `bank_transfers` - Transfer records
- `internal_transfers` - Internal fund movements
- `im_bank_transactions` - Mobile banking transactions
- `gepg_transactions` - Government payment gateway
- `payment_transactions` - Payment processing

#### 7. **Cash Management**
- `tills` - Till/cashier management
- `till_transactions` - Till transaction records
- `till_reconciliations` - Till reconciliation
- `teller_end_of_day_positions` - EOD positions
- `tellers` - Teller assignments
- `cash_movements` - Cash movement tracking
- `cashflow_configurations` - Cash flow settings
- `vaults` - Vault management
- `strongroom_ledgers` - Strong room tracking
- `security_transport_logs` - Cash transport logs

#### 8. **Billing & Payments**
- `bills` - Bill generation
- `billing_cycles` - Billing cycle configuration
- `payments` - Payment records
- `payment_methods` - Available payment methods
- `payment_notifications` - Payment alerts
- `orders` - Order management
- `cheques` - Cheque tracking
- `chequebooks` - Chequebook management

#### 9. **Budget & Expenses**
- `expenses` - Expense records
- `expense_approvals` - Expense approval workflow
- `budget_managements` - Budget configuration
- `budget_approvers` - Budget approval hierarchy
- `main_budget` - Main budget allocations
- `main_budget_pending` - Pending budget items
- `budget_allocations` - Department allocations
- `budget_advances` - Budget advances
- `supplementary_requests` - Additional budget requests
- `budget_alerts` - Budget alert configurations

#### 10. **HR & Payroll**
- `employees` - Employee records
- `employee_roles` - Employee role assignments
- `employee_requests` - Employee service requests
- `employee_files` - Employee documentation
- `employee_attendances` - Attendance tracking
- `payrolls` - Payroll processing
- `leaves` - Leave records
- `leave_management` - Leave configuration
- `benefits` - Employee benefits
- `hires_approvals` - Hiring approvals
- `interviews` - Interview scheduling
- `job_postings` - Job advertisements

#### 11. **Shares & Investments**
- `share_ownership` - Share ownership records
- `share_transfers` - Share transfer tracking
- `share_withdrawals` - Share withdrawal records
- `issued_shares` - Issued share tracking
- `investments_list` - Investment portfolio
- `investment_types` - Investment categories
- `dividends` - Dividend distributions

#### 12. **Insurance & Charges**
- `charges` - Charge definitions
- `charges_list` - Available charges
- `insurances` - Insurance products
- `insurance_list` - Insurance catalog
- `interest_payables` - Interest obligations
- `product_has_charges` - Product-charge mappings
- `product_has_insurance` - Product-insurance mappings

#### 13. **Reports & Analytics**
- `reports` - Report definitions
- `scheduled_reports` - Automated reporting
- `datafeeds` - Data feed configurations
- `financial_data` - Financial metrics
- `financial_position` - Position snapshots
- `financial_ratios` - Calculated ratios
- `analysis_sessions` - Analysis history
- `scores` - Scoring metrics
- `daily_activity_status` - Daily activity tracking

#### 14. **Communication**
- `notifications` - System notifications
- `notification_logs` - Notification history
- `emails` - Email templates/logs
- `email_activity_logs` - Email tracking
- `mandatory_savings_notifications` - Savings alerts
- `query_responses` - Query management
- `complaints` - Complaint tracking
- `complaint_categories` - Complaint types
- `complaint_statuses` - Complaint workflow

#### 15. **Audit & Compliance**
- `audit_logs` - System audit trail
- `user_action_logs` - User activity tracking
- `approvals` - Generic approval records
- `approval_actions` - Approval decisions
- `approval_comments` - Approval feedback
- `approval_matrix_configs` - Approval hierarchy
- `committee_approvals` - Committee decisions

#### 16. **System Configuration**
- `process_code_configs` - Process configuration
- `api_keys` - API access management
- `currencies` - Currency configuration
- `document_types` - Document categorization
- `mobile_networks` - Mobile network configs
- `mnos` - Mobile network operators
- `taxes` - Tax configurations
- `banks` - Bank listings
- `bank_accounts` - Bank account management
- `password_policies` - Security policies

#### 17. **AI Integration**
- `ai_interactions` - AI interaction logs
- `analysis_sessions` - AI analysis tracking

## Seeding Strategy

### Seeding Order (from DatabaseSeeder.php)
The system follows a careful seeding order to handle dependencies:

1. **Core System Setup**
   - Institutions → Branches → Departments

2. **Access Control**
   - Roles → Permissions → Users → User assignments

3. **Menu System**
   - Menus → SubMenus → MenuActions → RoleMenuActions

4. **Members & Products**
   - Clients → Services → Products → Charges

5. **Financial Structure**
   - Accounts → SubAccounts → GL setup

6. **Operational Data**
   - Loans → Transactions → Bills → Payments

7. **Supporting Systems**
   - HR/Payroll → Reports → Notifications

8. **Verification**
   - VerifySetupSeeder runs last to ensure data integrity

### Key Observations

1. **Foreign Key Management**
   - Uses `SET session_replication_role = replica` to disable FK checks during seeding
   - Re-enables with `SET session_replication_role = DEFAULT` after completion

2. **Error Handling**
   - Each seeder runs individually with error catching
   - Failed seeders are tracked and reported
   - Seeding continues even if individual seeders fail

3. **Data Verification**
   - Critical tables verified after key seeders (branches, users)
   - Final data state verification for 7 core tables
   - Warning logs for missing critical data

4. **Commented Seeders**
   - Many seeders commented out due to missing tables
   - Indicates ongoing refactoring or incomplete features

## Database Patterns

### 1. **Soft Deletes**
Most tables include soft delete functionality for data retention

### 2. **JSON Fields**
Extensive use of JSON columns for flexible data storage (settings, metadata)

### 3. **Audit Trail**
Comprehensive audit logging across all major operations

### 4. **Status Management**
Multiple status fields for workflow management

### 5. **Hierarchical Structure**
- Institution → Branch → Department
- Account → SubAccount
- Menu → SubMenu → Actions
- Role → SubRole → Permissions

### 6. **Financial Integration**
Deep integration with double-entry accounting principles

## Recent Schema Changes

### Latest Migrations (2025)
- Payment transaction enhancements
- Budget management system expansion
- Employee attendance tracking
- Email activity logging
- Daily activity status monitoring
- Member exit workflows

## Recommendations

1. **Table Consolidation**
   - Many similar tables could be consolidated (e.g., multiple approval tables)
   - Consider unified audit log structure

2. **Missing Tables**
   - Several seeders reference non-existent tables
   - Need cleanup or table creation

3. **Performance Considerations**
   - 276 tables is substantial - consider archiving strategies
   - Index optimization needed for large tables

4. **Data Integrity**
   - Implement database-level constraints
   - Add check constraints for business rules

5. **Documentation**
   - Document table relationships
   - Create ER diagrams
   - Document business rules in migrations

## Migration Commands

```bash
# Run all migrations
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status

# Rollback migrations
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_tablename_table
```

## Seeder Commands

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UsersSeeder

# Create new seeder
php artisan make:seeder TableNameSeeder
```

---
*Analysis Date: 2025-09-07*
*Total Tables: ~276*
*Total Seeders: 247*
*Database: PostgreSQL*