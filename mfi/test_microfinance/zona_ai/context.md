# SACCOS Core System AI Context

## System Information
- **Project Path**: /Volumes/DATA/PROJECTS/SACCOS/SYSTEMS/SACCOS_CORE_SYSTEM
- **Database**: PostgreSQL (saccos_core)
- **Framework**: Laravel 9.x with Livewire



 General System return [
            // Core User Management
            'users' => 'System users and administrators with roles, permissions, and authentication details',
            'user_roles' => 'User role assignments and permissions mapping',
            
            // Organization Structure
            'institutions' => 'This institutions information such as name, address, phone number, email, website, etc. and other settings',
            'branches' => 'Physical SACCO branch locations with fields: branch_number (unique), name, region, wilaya, status, branch_type (MAIN/SUB/MOBILE), email, phone_number, address, opening_date, branch_manager (user_id), operating_hours, services_offered (JSON), cit_provider_id, vault_account, till_account, petty_cash_account',
            'departments' => 'Organizational departments and functional units',
            'employees' => 'Staff records, employment details, and personnel information',
            // Teller Management System
            'tellers' => 'Teller accounts: employee_id (50 chars), status, branch_id, max_amount, account_id, user_id, till_id, transaction_limit (100000), permissions (JSON), last_login_at, assigned_at',
            'tills' => 'Cash tills: name, till_number (50 chars), branch_id, current_balance, opening_balance, max_limit (500000), min_limit (10000), status (open/closed), denomination_breakdown (JSON), variance tracking',
            'vaults' => 'Strong room management: name, code, branch_id, current_balance, limit, warning_threshold (80%), bank integration, auto_bank_transfer, dual_approval, status (active/inactive)',
            'teller_end_of_day_positions' => 'EOD reconciliation: employee_id, til_number, til_balance, tiller_cash_at_hand, business_date, status (BALANCED/VARIANCE), variance explanation',
            'till_transactions' => 'Cash transactions: reference (unique), till_id, teller_id, client_id, type (deposit/withdrawal/transfer), amount, balance tracking, denomination_breakdown, status (pending/completed/reversed)',
            'till_reconciliations' => 'Till balancing: till_id, teller_id, system_balance, physical_cash, variance, variance_explanation, reconciliation_date, approved_by, status',
            'cash_in_transit_providers' => 'CIT service providers for cash transportation: provider_name, contact_person, phone, email, contract_number, service_charge_rate',
            
            // Bank Reconciliation System
            'analysis_sessions' => 'Reconciliation sessions: account_name, account_number, statement_period, bank (I&M/CRDB/NMB/ABBSA), opening_balance, closing_balance, total_transactions, status (processing/completed), metadata (JSON)',
            'bank_transactions' => 'Bank statement transactions: session_id, transaction_date, value_date, reference_number, narration, withdrawal_amount, deposit_amount, balance, reconciliation_status (unreconciled/matched/partial/reconciled), match_confidence (0-100)',
            'reconciled_transactions' => 'Reconciled records: Institution_Id, Account_Code, Reference_Number, Value_Date, Gl_Details, Gl_Debit/Credit, Bank_Details, Bank_Debit/Credit - final reconciled data',
            'reconciliation_staging_table' => 'Staging area: Reference_Number, Account_code, Details, Value_Date, Debit, Credit, Book_Balance, Process_Status - temporary processing',
            'im_bank_transactions' => 'I&M Bank format: transaction_date, value_date, narration, withdrawal_amount, deposit_amount, balance - bank-specific format',

            // HR Management System
            'employees' => 'Employee records (58 fields): id, institution_user_id, first_name, middle_name, last_name, date_of_birth, gender, marital_status, nationality, address, phone, email, job_title, branch_id, department_id, hire_date, basic_salary, gross_salary, employee_status, employment_type, employee_number (unique), nssf_number, nhif_number, tin_number, nida_number, workers_compensation, life_insurance, tax_category, paye_rate, tax_paid, pension, education_level, emergency contacts, physical_address',
            'departments' => 'Organization structure: id, department_name, department_code (unique), parent_department_id (hierarchy), description, status, level (0=root), path, branch_id, dashboard_type, supports soft deletes',
            'leaves' => 'Leave requests: id, employee_id, leave_type (annual/sick/maternity), start_date, end_date, status (pending/approved/rejected), reason, description - requires approval workflow',
            'leave_management' => 'Leave balances: id, total_days (allocated), days_acquire (earned), leave_days_taken, balance (remaining), employee_number - balance=total_days-leave_days_taken',
            'employee_requests' => 'Various requests: id, employee_id, type (leave/materials/resignation), department, subject, details, status (PENDING/APPROVED/REJECTED), approver_id, approved_at, rejection_reason, attachments (JSON) - approval workflow',
            'employee_roles' => 'Employee-role mapping: id, employee_id, role_id - unique constraint on employee_id+role_id, cascade delete',
            'employeefiles' => 'Document storage: id, employeeN (number), empName, docName, path - stores contracts, certificates, etc.',
            'job_postings' => 'Recruitment: id, job_title, department, location, job_type, description, requirements, salary, status (open/closed/draft) - recruitment module',
            
            // Self-Services System - Employee self-service portal
            // NOTE: Self-services uses employee_requests table for all request types:
            // Leave requests, Material requests, Resignation, Travel/advance, Training, Overtime, Payslip, General
            'document_types' => 'Document categorization: document_id (PK), document_name, collateral_type - used for various document classifications',
            
            // Approvals System - Multi-level approval workflow engine
            'approvals' => 'Central approvals (29 fields): id, status (pending/approved/rejected), checker_level, first_checker_id, second_checker_id, first/second_checker_status, rejection_reason, approved_at, rejected_at, process_name, process_code, process_id, process_status, user_id (initiator), approver_id, edit_package (JSON), supports soft deletes',
            'process_code_configs' => 'Approval process configuration: id, process_code (unique), process_name, description, requires_first_checker, requires_second_checker, requires_approver, first/second_checker_roles (JSON), approver_roles (JSON), min_amount, max_amount, is_active - defines workflow rules',
            'approval_comments' => 'Approval discussion: id, approval_id, comment - cascade delete with approval',
            'approval_actions' => 'Approval audit trail: id, approver_id, status, comment, loan_id - tracks all approval actions',
            'approval_matrix_configs' => 'Matrix-based routing: id, process_type (loan/expense/budget/hire), process_name, process_code, level, approver_role, approver_sub_role, min/max_amount, is_active, additional_conditions (JSON) - hierarchical approval levels',
            'loan_approvals' => 'Loan approval stages: id, loan_id, stage_name, stage_type, approver_id, approver_name, status (PENDING/APPROVED/REJECTED), comments, approved_at, conditions (JSON) - stage-based workflow',
            'expense_approvals' => 'Expense approvals: id, user_id, user_name, status, expense_id, approval_level - expense workflow',
            'hires_approvals' => 'Recruitment approvals: id, hire_id, approver_id, status, comments, level - HR approval chain',
            'committee_approvals' => 'Committee decisions: id, committee_id, meeting_id, agenda_item, decision, votes_for, votes_against, abstentions, minutes - board-level approvals',
            
            // Reports & Analytics System - Comprehensive reporting engine
            'reports' => 'Generated reports: id, type, date, data (JSON), status (pending/completed/failed) - stores all generated reports',
            'scheduled_reports' => 'Automated reports: id, report_type, report_config (JSON), user_id, status (scheduled/processing/completed/failed/cancelled), frequency (once/daily/weekly/monthly/quarterly/annually), scheduled_at, last_run_at, next_run_at, error_message, output_path, email_recipients, email_sent, retry_count (0-3) - scheduled report automation',
            'financial_data' => 'Financial statement data: id, description, category, value, end_of_business_year, unit (Tshs.) - categorized financial data',
            'financial_position' => 'Income statement: id, interest_on_loans, other_income, total_income, expenses, annual_surplus, end_of_business_year - P&L data',
            'financial_ratios' => 'Key ratios: id, end_of_financial_year_date, core_capital, total_assets, net_capital, short_term_assets, short_term_liabilities, expenses, income - financial performance metrics',
            'audit_logs' => 'Audit trail: id, user_id, action, details - complete system audit trail',
            // Supported report types: Financial Position, Comprehensive Income, Cash Flow, Loan Classification,
            // Interest Rates, Disbursements, Delinquency, Portfolio at Risk, Client Details, Daily Reports,
            // Loan Status, Committee Reports, and 20+ other regulatory and management reports
            
            // Member Management - IMPORTANT: These are the actual SACCO members
            'clients' => 'SACCOS members table (156 fields!) - Core member data: client_number (unique ID), first_name, middle_name, last_name, email, phone_number, address, branch_id, client_status (ACTIVE/PENDING/BLOCKED), membership_type (Individual/Corporate), registration_date, nida_number, tin_number, employment details, income_available, next_of_kin info, guarantor details, portal_access_enabled, hisa/akiba/amana amounts. Links to accounts, loans, shares via client_number',
            'client_documents' => 'Member KYC document storage: client_id, document_type (profile_photo, id_card, etc), file_path, verification_status (PENDING/VERIFIED/REJECTED), verified_by, verified_at',
            'member_groups' => 'Member groupings for collective operations: group_name, group_type, members list, group loans/savings',
            'onboarding' => 'Member onboarding process: client_number, step, kyc_status, documents_status, account_opening_status, share_purchase_status, completed_at',
            'member_categories' => 'Member classification categories: category_name, minimum_shares, benefits, requirements',
           
           
            // Financial Products
            'accounts' => 'Chart of accounts - General Ledger accounts and member accounts. member share accounts are identified by the product_number = 1000, member loan accounts are identified by the product_number = 4000, member savings accounts are identified by the product_number = 2000, member deposit accounts are identified by the product_number = 3000. The main identifier is the account_number. The member accounts are identified by the client_number',
         
            'account_historical_balances' => 'Historical account balance tracking',
            
            // Loan Management - Complete Lending System
            'loans' => 'Main loan table (100+ fields!): loan_id (unique), loan_account_number, client_number (borrower), loan_sub_product, principle, interest (rate), tenure (months), status (PENDING/APPROVED/ACTIVE/REJECTED/CLOSED), loan_status (NORMAL/WATCH/SUBSTANDARD/DOUBTFUL/LOSS), days_in_arrears, arrears_in_amount, disbursement_date, disbursement_method, business assessment fields, collateral details, approval workflow',
            'loans_schedules' => 'Repayment schedules: loan_id, installment, interest (portion), principle (portion), opening_balance, closing_balance, installment_date, completion_status (PENDING/COMPLETED/OVERDUE), payment, penalties, amount_in_arrears, days_in_arrears, promise_date',
            'loan_sub_products' => 'Loan product configurations: sub_product_id, sub_product_name, interest_rate, min_amount, max_amount, min_term, max_term, processing_fee, insurance_fee, penalty_rate, grace_period, collateral_requirement, guarantor_requirement',
            'loan_collaterals' => 'Loan security: loan_guarantor_id, collateral_type (savings/deposits/shares/physical), account_id (for financial), collateral_amount, locked_amount, physical asset details, insurance info, status (active/inactive/released/forfeited)',
            'loan_guarantors' => 'Guarantor commitments: loan_id, guarantor_member_id, guarantee_amount, guarantor_type, relationship_with_borrower, monthly_income, net_worth, acceptance_status, acceptance_date, release_date',
            'loan_approvals' => 'Approval workflow: loan_id, stage_id, stage_name, approver_id, approval_status (PENDING/APPROVED/REJECTED), approval_date, comments, conditions, next_stage_id',
            'settled_loans' => 'Closed loans: loan_id, settlement_date, settlement_type (Full payment/Write-off/Restructure), total_paid, total_principal_paid, total_interest_paid, total_penalties_paid, waived_amount, write_off_amount',
            'loan_documents' => 'Supporting documents: loan_id, document_type, document_name, file_path, uploaded_by, verified_by, verification_status, verification_date',
            'loan_product_charges' => 'Fees configuration: loan_product_id, charge_name, charge_type (PERCENTAGE/FIXED), charge_value, charge_timing (UPFRONT/MONTHLY/ANNUALLY), gl_account, is_mandatory, is_negotiable',
            'query_responses' => 'NBC credit bureau data: message_id, connector_id, type, response_data (JSONB with contracts/credit history), CheckNumber (NIN/client_number), used for credit scoring and loan assessment',
            'loan_images' => 'Loan documents storage: loan_id, category (guarantor/add-document/collateral), filename, url, document_descriptions, file_size, mime_type, original_name',
            'loan_process_progress' => 'Loan application workflow: loan_id (unique), completed_tabs (JSON array), tab_data (JSON), tracks progress through client/guarantor/document/assessment tabs',
            
            // Product Management System
            'sub_products' => 'Unified product configuration: product_type (1000=shares, 2000=savings, 3000=deposits, 4000=loans), sub_product_name, currency, interest rates, fees, limits, approval settings, SMS settings, 80+ configuration fields',
            'savings_types' => 'Savings product categories: type, summary, status, institution_id - defines types like Regular, Target, Education, Emergency, Group, Junior savings',
            'deposit_types' => 'Deposit product categories: type, summary, status, institution_id - defines Fixed, Recurring, Call, Certificate deposits',
            
            // Accounting Management System
            'general_ledger' => 'Double-entry bookkeeping: 50+ fields for complete transaction tracking, debit/credit, multi-currency, bank reconciliation, reference_number, narration, trans_status',
            'expenses' => 'Expense management: account_id, amount, description, payment_type, budget tracking (budget_item_id, monthly_budget_amount, budget_utilization_percentage), approval workflow',
            
            // Budget Management System
            'main_budget' => 'Institutional budget with monthly allocations: institution_id, sub_category_code, sub_category_name, monthly fields (january-december with _init variants for initial budgets), total, total_init, year, type (revenue/expense)',
            'budget_managements' => 'Department budget planning: revenue, expenditure, capital_expenditure, budget_name, start_date, end_date, spent_amount, status (ACTIVE/INACTIVE), approval_status (PENDING/APPROVED/REJECTED), notes, department, currency (default TZS), expense_account_id - tracks utilization percentage',
            'budget_accounts' => 'Account-specific budget allocations: account_id (GL account), amount, year, branch - links budgets to specific GL accounts',
            'budget_approvers' => 'Budget approval workflow: user_id, user_name, status, budget_id - manages multi-level budget approval process',
            'strongroom_ledgers' => 'Vault cash management: vault_id, balance, denomination_breakdown (JSON), total_deposits, total_withdrawals, branch_id, last_transaction_at',
            'ppes' => 'Fixed assets register: 40+ fields for asset tracking, purchase_price, depreciation (accumulated, yearly, monthly), disposal management, supplier info, accounting integration',
            'receivables' => 'Accounts receivable: 45+ fields for comprehensive AR management, aging analysis, collection tracking, multiple receivable types, approval workflow',
            'payables' => 'Accounts payable: vendor invoices, due dates, liability/cash/expense accounts, payment tracking',
            'standing_instructions' => 'Recurring payments: member_id, source/destination accounts, frequency, start/end dates, automatic execution, status tracking',
            'interest_payables' => 'Interest obligations: deposit interest accrual, borrowing interest, payment schedules, tax withholding',
            'loan_loss_provisions' => 'Credit loss provisions: loan classification (PERFORMING/WATCH/SUBSTANDARD/DOUBTFUL/LOSS), provision rates, regulatory compliance',
            'provision_rates_config' => 'Provision rate settings: classification thresholds, provision percentages by risk category',
            'loan_loss_provision_summary' => 'Daily provision aggregates: portfolio analysis, NPL ratios, provision coverage, regulatory reporting',
            'cheque_books' => 'Cheque book management: bank, remaining leaves, status tracking, branch allocation',
            'cheques' => 'Individual cheques: amount, number, dual approval, clearing status (ISSUED/PRESENTED/CLEARED/BOUNCED/STOPPED)',
            'financial_position' => 'Balance sheet data: income, expenses, annual surplus, year-end positions',
            'financial_ratios' => 'Performance ratios: capital adequacy, liquidity, efficiency, ROA, debt-to-equity, regulatory compliance',
            'cash_flow_configurations' => 'Cash flow setup: section mapping (operating/investing/financing), GL account configuration',
            
            // Payment Management System
            'payments' => 'Payment processing records: bill_id, payment_ref (unique), transaction_reference (unique), control_number, amount, currency, payment_channel, payer details (name, msisdn, email, tin, nin), paid_at, received_at, status (Pending/Confirmed/Reversed), raw_payload, response_data',
            'payment_notifications' => 'Payment callbacks: control_number (13 chars), received_at, raw_payload (JSON), status (Pending/Processed/Failed), processed_at - handles async payment notifications',
            'payment_methods' => 'Payment method configuration: name - defines available payment options',
            'payment_links' => 'Online payment links: loan_id, client_number, link_id, short_code, payment_url, qr_code_data, total_amount, currency, description, expires_at, status (ACTIVE/EXPIRED/USED/CANCELLED)',
            'bills' => 'Service bills: service_id, control_number, amount_due, amount_paid, is_mandatory, is_recurring, due_date, member_id, client_number, payment_mode, status (PENDING/PAID/OVERDUE), credit/debit accounts, payment_link details',
            'bank_transfers' => 'Vault to bank transfers: vault_id, amount, reason (over_limit/manual/scheduled), status, reference (unique), initiated_by, approved_by, bank_response, processed_at',
            'internal_transfers' => 'GL account transfers: transfer_date, transfer_type, from_account_id, to_account_id, amount, narration, status (draft/posted), created_by - maintains double-entry balance',
            'gepg_transactions' => 'Government payment gateway: control_number, account_no, amount, currency, response_code, response_description, payload (JSON), transaction_type (verification/postpaid/prepaid), quote_reference',
            
            // Investment Management System
            'investments_list' => 'Investment portfolio: investment_type, principal_amount, investment_date, shares (number, price, brokerage_fees), dividends, bonds (type, coupon_rate, yield), mutual funds (fund_name, manager, NAV, units), real estate (property_value, location, rental_income), interest_rate, tenure, maturity_date, status',
            'investment_types' => 'Investment categories: investment_type (code), investment_name, description - defines STOCKS, BONDS, MUTUAL_FUNDS, REAL_ESTATE, FIXED_DEPOSITS, TREASURY_BILLS',
            'project' => 'Investment projects: tender_no, procuring_entity, supplier_name, award_date, award_amount, lot_name, expected_end_date, project_summary, status (ONPROGRESS/COMPLETED)',
            'dividends' => 'Member dividend payments: member_id, year, rate (percentage), amount, paid_at, payment_mode (bank/cash/shares), status (pending/paid), narration',
            
            // Services & Self-Service System
            'services' => 'Self-service configuration: service name, code (3 chars), mandatory flag, transaction limits, recurring flag, GL accounts (debit/credit), payment modes',
            
            // Human Resources Management
            'employees' => 'Employee master: 60+ fields including personal info, employment details, salary, statutory deductions (TIN, NSSF, NHIF), emergency contacts, department, reporting manager',
            'pay_rolls' => 'Payroll processing: employee_id, pay period, gross salary, deductions (tax, social security, health insurance), calculated net salary, payment method/date',
            'employee_requests' => 'HR requests: employee_id, type (leave/materials/resignation), department, approval workflow, status, attachments',
            'job_postings' => 'Recruitment: job title, department, location, type, requirements, salary range, status (open/closed/draft)',
            
            // Procurement Management System
            'vendors' => 'Supplier registry: organization_name (40 chars), organization_tin_number (TIN), organization_license_number, status (ACTIVE/PENDING/DELETED), email, organization_description',
            'purchases' => 'Purchase orders: requisition_description, status (PENDING/APPROVED/REJECTED/COMPLETED), invoice, employeeId, vendorId, branchId, quantity - tracks requisition to delivery',
            'contract_managements' => 'Vendor contracts: contract_name (40 chars), contract_description, contract_file_path, startDate, endDate, vendorId, status (ACTIVE/PENDING/EXPIRED)',
            'tenders' => 'Tender management: tender_number (unique), tender_name (30 chars), tender_description, status (OPEN/CLOSED/AWARDED/CANCELLED)',
            'inventories' => 'Stock management: item_name, item_amount (quantity), item_expiration_date, status (AVAILABLE/OUT_OF_STOCK/EXPIRED), item_description',
            'assets_list' => 'Fixed assets: name, type (category), value, acquisition_date, source - tracks organization assets with depreciation',
            
            // Savings Management - Complete Savings System
            'savings_accounts' => 'Member savings accounts (uses accounts table with product_number=2000): account_number (unique), client_number (member), account_name, balance, status (ACTIVE/DORMANT/CLOSED), locked_amount (for loan collateral), branch_number, sub_product_number (specific savings type)',
            'mandatory_savings_tracking' => 'Monthly mandatory savings tracking: client_number, account_number, year, month, required_amount, paid_amount, balance (outstanding), status (PAID/PARTIAL/UNPAID/OVERDUE), due_date, paid_date, months_in_arrears, total_arrears',
            'mandatory_savings_notifications' => 'Savings payment reminders: client_number, year, month, notification_type (FIRST_REMINDER/SECOND_REMINDER/FINAL_REMINDER/OVERDUE_NOTICE), notification_method (SMS/EMAIL/SYSTEM), message, status (PENDING/SENT/FAILED), sent_at, scheduled_at',
            'mandatory_savings_settings' => 'Mandatory savings configuration: institution_id, mandatory_savings_account, monthly_amount, due_day (default 5), grace_period_days, notification settings (first_reminder_days, second_reminder_days, final_reminder_days), sms_template, email_template',
            'savings_types' => 'Types of savings products: type (name), summary (description), status (active/inactive), institution_id. Links to sub_products for specific product variants',
            'savings_transactions' => 'Savings deposits/withdrawals (uses transactions table): account_number, transaction_type (DEPOSIT/WITHDRAWAL/INTEREST/TRANSFER), amount, balance_after, reference_number, narration, transaction_date, posted_by',
            'fixed_deposits' => 'Fixed term deposits: account_number, principal_amount, interest_rate, term_months, maturity_date, auto_renew, status (ACTIVE/MATURED/WITHDRAWN/RENEWED)',
            
            // Deposits Management - Complete Deposit System
            'deposit_accounts' => 'Member deposit accounts (uses accounts table with product_number=3000): account_number (unique), client_number (member), account_name, balance, status (ACTIVE/DORMANT/CLOSED), locked_amount (collateral), minimum_balance, interest_rate, branch_number, sub_product_number (specific deposit type)',
            'deposit_types' => 'Types of deposit products: type (name), summary (description), status (active/inactive), institution_id. Links to sub_products table via deposit_type_id for specific product configurations',
            'term_deposits' => 'Time/Term deposits with fixed maturity: account_number, client_number, principal_amount, interest_rate, term_months (3/6/12/24/36), deposit_date, maturity_date, maturity_amount, auto_renew, penalty_rate, status (ACTIVE/MATURED/WITHDRAWN/RENEWED/BROKEN)',
            'deposit_transactions' => 'Deposit account transactions (uses transactions table): account_number, transaction_type (DEPOSIT/WITHDRAWAL/INTEREST/TRANSFER), amount, balance_after, payment_method (cash/bank/internal_transfer/tips_mno/tips_bank), reference_number, bank_reference, narration',
            'deposit_interest_settings' => 'Interest configuration for deposits: product_id, interest_rate (annual %), interest_calculation_method (SIMPLE/COMPOUND/FLAT), interest_posting_frequency (DAILY/MONTHLY/QUARTERLY/ANNUALLY), minimum_balance_for_interest, withholding_tax_rate, penalty_for_early_withdrawal',
            'members_savings_and_deposits' => 'Category mapping table: category_code, sub_category_code, sub_category_name - used for classifying and reporting on savings and deposit accounts',
            'deposit_maturity_notifications' => 'Maturity notifications: account_number, notification_type (PRE_MATURITY/MATURITY/POST_MATURITY), days_to_maturity, notification_date, status (PENDING/SENT/FAILED), notification_method (SMS/EMAIL/SYSTEM)',
            
            // Share Management - Complete Share Capital System
            'share_registers' => 'Main share ownership table (73 fields!) - Core data: share_account_number (unique), member_id, member_number, product_id (share type), product_type (MANDATORY/VOLUNTARY/PREFERENCE), nominal_price, current_price, total_shares_issued, total_shares_redeemed, current_share_balance, total_share_value, dividend tracking (rate, amount, date, accumulated, paid, pending), linked_savings_account, dividend_payment_account, status (ACTIVE/INACTIVE/FROZEN/CLOSED), compliance flags (is_restricted, requires_approval)',
            'issued_shares' => 'Share issuance transactions: reference_number, share_id, client_number, number_of_shares, nominal_price, total_value, linked_savings_account, linked_share_account, status, branch, created_by',
            'share_transactions' => 'All share movements: share_id, transaction_type (PURCHASE/SALE/TRANSFER/DIVIDEND), quantity, price_per_share, total_amount, from_member, to_member, reference_number, status, approved_by',
            'share_withdrawals' => 'Share redemption records: member_id, share_account_number, number_of_shares, withdrawal_amount, reason, status (pending/approved/processed/rejected), payment_method, payment_reference',
            'share_transfers' => 'Share transfers between members: from_member_id, to_member_id, from_account, to_account, number_of_shares, transfer_value, transfer_reason, transfer_fee, status',
            'dividends' => 'Dividend payments: member_id, year, rate (%), amount, paid_at, payment_mode (bank/cash/shares), status (pending/approved/paid/cancelled), narration',
            'share_ownership' => 'Ownership tracking: member_id, share_class, number_of_shares, ownership_percentage, voting_rights',
            
            // Transaction Management
            'transactions' => 'Financial transactions and journal entries',
           
            'general_ledger' => 'General ledger account balances and movements',
            
            // Other key tables
            'expenses' => 'Expense tracking and management',
            'budget_managements' => 'Budget planning and management',
            'investments_list' => 'Investment portfolio and holdings',
            'dividends' => 'Dividend declarations and distributions',
            'meetings' => 'Meeting scheduling and management',
            'committees' => 'Management committees and governance structures',
            'notifications' => 'System notification definitions and templates',
            'audit_logs' => 'System audit trail and security logging',
            'reports' => 'Report definitions and configurations'
        ];




 Detailed System Information [
            'clients' => [
                'description' => 'Central member table - the heart of the SACCOS system (156 fields!)',
                'model' => 'ClientsModel',
                'key_fields' => [
                    // Primary Identifiers
                    'id' => 'Primary key',
                    'client_number' => 'Unique member identifier (indexed)',
                    'account_number' => 'Primary account number',
                    'membership_number' => 'Alternative member number',
                    'nida_number' => 'National ID number',
                    'tin_number' => 'Tax identification number',
                    
                    // Personal Information
                    'first_name' => 'Member first name',
                    'middle_name' => 'Middle name(s)',
                    'last_name' => 'Surname',
                    'full_name' => 'Complete name',
                    'business_name' => 'For corporate members',
                    'incorporation_number' => 'Company registration',
                    'gender' => 'Member gender',
                    'date_of_birth' => 'Birth date',
                    'place_of_birth' => 'Birth location',
                    'marital_status' => 'Marital status',
                    'nationality' => 'Member nationality',
                    'citizenship' => 'Citizenship status',
                    
                    // Contact Information
                    'email' => 'Email address',
                    'phone_number' => 'Primary phone',
                    'mobile_phone_number' => 'Mobile phone',
                    'address' => 'Physical address',
                    'street' => 'Street address',
                    'district' => 'District',
                    'region' => 'Region',
                    'ward' => 'Ward',
                    
                    // Employment & Income
                    'employment' => 'Employment status',
                    'employer_name' => 'Employer',
                    'occupation' => 'Job title',
                    'business_name' => 'Business if self-employed',
                    'income_available' => 'Available income',
                    'monthly_expenses' => 'Monthly expenses',
                    'basic_salary' => 'Basic salary',
                    'gross_salary' => 'Gross salary',
                    
                    // Membership Details
                    'membership_type' => 'Individual/Corporate',
                    'member_category' => 'Member classification',
                    'client_status' => 'ACTIVE/PENDING/BLOCKED/NEW CLIENT',
                    'registration_date' => 'Join date',
                    'branch_id' => 'Branch registered at',
                    'registering_officer' => 'Staff who registered',
                    'loan_officer' => 'Assigned loan officer',
                    'approving_officer' => 'Who approved membership',
                    
                    // Financial Accounts
                    'hisa' => 'Share amount',
                    'akiba' => 'Savings amount',
                    'amana' => 'Deposit amount',
                    'share_payment_status' => 'Share payment status',
                    
                    // Next of Kin
                    'next_of_kin_name' => 'Emergency contact name',
                    'next_of_kin_phone' => 'Emergency contact phone',
                    
                    // Guarantor Information
                    'guarantor_full_name' => 'Guarantor name',
                    'guarantor_membership_number' => 'Guarantor member number',
                    'guarantor_email' => 'Guarantor email',
                    'guarantor_phone' => 'Guarantor phone',
                    'guarantor_relationship' => 'Relationship to guarantor',
                    'guarantor_region' => 'Guarantor location',
                    
                    // Portal Access
                    'portal_access_enabled' => 'Can access member portal',
                    'password_hash' => 'Portal password',
                    'last_portal_login_at' => 'Last portal login',
                    'portal_session_token' => 'Active session',
                    
                    // System Fields
                    'created_at' => 'Record creation',
                    'updated_at' => 'Last update',
                    'deleted_at' => 'Soft delete timestamp',
                    'created_by' => 'Created by user',
                    'updated_by' => 'Updated by user'
                ],
                'relationships' => [
                    'has_many' => [
                        'accounts' => 'Multiple accounts (via client_number) - savings, loans, shares',
                        'loans' => 'Loan applications (via client_number)',
                        'savings' => 'Savings records (via client_number)',
                        'shares' => 'Share ownership (via client_number)',
                        'bills' => 'Bills and invoices (via client_number)',
                        'documents' => 'KYC documents (via client_id)',
                        'share_transactions' => 'Share buy/sell transactions',
                        'complaints' => 'Member complaints (via client_id)',
                        'notifications' => 'Member notifications',
                        'dividend_payments' => 'Dividend distributions',
                        'mandatory_savings_tracking' => 'Mandatory savings records',
                        'web_portal_users' => 'Portal access records'
                    ],
                    'belongs_to' => [
                        'branches' => 'Home branch (via branch_id)',
                        'loan_officers' => 'Assigned loan officer (via loan_officer)',
                        'registering_officers' => 'Registration officer (via registering_officer)'
                    ],
                    'has_one' => [
                        'guarantor' => 'Guarantor record',
                        'profile_photo' => 'Profile picture document',
                        'application_letter' => 'Membership application',
                        'onboarding' => 'Onboarding process status',
                        'member_category' => 'Member classification'
                    ],
                    'many_to_many' => [
                        'loan_guarantors' => 'Acts as guarantor for other members loans'
                    ]
                ],
                'data_flow' => 'Registration → KYC → Account Creation → Services → Transactions → Reports',
                'livewire_component' => 'App\\Http\\Livewire\\Clients\\Clients',
                'services' => [
                    'MembershipVerificationService' => 'Verify member details',
                    'MemberNumberGeneratorService' => 'Generate unique member numbers',
                    'AccountCreationService' => 'Create member accounts',
                    'BillingService' => 'Generate member bills',
                    'PaymentLinkService' => 'Generate payment links'
                ]
            ],
            
            'accounts' => [
                'description' => 'Chart of Accounts and member accounts - dual purpose table',
                'key_fields' => ['id', 'account_number', 'client_number', 'product_number'],
                'types' => [
                   
                    'member_shares' => 'product_number = 1000',
                    'member_savings' => 'product_number = 2000', 
                    'member_deposits' => 'product_number = 3000',
                    'member_loans' => 'product_number = 4000'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Member accounts linked via client_number',
                        'sub_products' => 'Account type/product via product_number'
                    ],
                    'has_many' => [
                        'transactions' => 'All financial transactions for this account',
                        'account_historical_balances' => 'Balance history tracking'
                    ]
                ],
                'data_flow' => 'Accounts → Transactions → General Ledger → Financial Statements'
            ],
            
            // ========= COMPREHENSIVE LOAN MANAGEMENT SYSTEM =========
            'loans' => [
                'description' => 'Main loan applications and management table - the core of lending operations',
                'model' => 'LoansModel',
                'key_fields' => [
                    // Primary Identifiers
                    'id' => 'Primary key',
                    'loan_id' => 'Unique loan identifier',
                    'loan_account_number' => 'Loan account number (uses accounts table with product_number=4000)',
                    'loan_sub_product' => 'Loan product type (references loan_sub_products)',
                    'client_number' => 'Borrower member number',
                    'branch_id' => 'Originating branch',
                    
                    // Loan Terms
                    'principle' => 'Principal loan amount',
                    'interest' => 'Interest rate (%)',
                    'tenure' => 'Loan term in months',
                    'interest_method' => 'Interest calculation method',
                    'monthly_installment' => 'Monthly payment amount',
                    'total_interest' => 'Total interest over loan term',
                    'total_principal' => 'Total principal to be repaid',
                    'total_payment' => 'Total amount (principal + interest)',
                    
                    // Business Assessment
                    'business_name' => 'Business name for business loans',
                    'business_age' => 'Years in business',
                    'business_category' => 'Business sector/category',
                    'business_type' => 'Type of business',
                    'business_licence_number' => 'Business license',
                    'business_tin_number' => 'Tax identification',
                    'business_inventory' => 'Inventory value',
                    'cash_at_hand' => 'Available cash',
                    'daily_sales' => 'Average daily sales',
                    'cost_of_goods_sold' => 'COGS',
                    'available_funds' => 'Available funds for repayment',
                    'operating_expenses' => 'Monthly operating expenses',
                    'monthly_taxes' => 'Monthly tax obligations',
                    'other_expenses' => 'Other monthly expenses',
                    
                    // Collateral Information
                    'collateral_value' => 'Total collateral value',
                    'collateral_location' => 'Collateral location',
                    'collateral_description' => 'Description of collateral',
                    'collateral_type' => 'Type of collateral',
                    
                    // Status & Tracking
                    'status' => 'Loan status (PENDING, APPROVED, ACTIVE, REJECTED, CLOSED)',
                    'loan_status' => 'Health status (NORMAL, WATCH, SUBSTANDARD, DOUBTFUL, LOSS)',
                    'heath' => 'Portfolio health (GOOD, FAIR, POOR)',
                    'stage_id' => 'Current approval stage',
                    'stage' => 'Stage name',
                    
                    // Arrears Tracking
                    'days_in_arrears' => 'Current days in arrears',
                    'total_days_in_arrears' => 'Total arrears days',
                    'arrears_in_amount' => 'Arrears amount',
                    
                    // Disbursement
                    'disbursement_date' => 'Date loan was disbursed',
                    'disbursement_method' => 'Disbursement channel',
                    'disbursement_account' => 'Account for disbursement',
                    'bank_account_number' => 'Bank account for transfer',
                    'bank' => 'Bank name',
                    'amount_to_be_credited' => 'Net disbursement amount',
                    'total_deductions' => 'Total deductions at disbursement',
                    'net_disbursement_amount' => 'Amount after deductions',
                    
                    // Loan Types & Categories
                    'loan_type' => 'Primary loan type',
                    'loan_type_2' => 'Secondary classification',
                    'loan_type_3' => 'Tertiary classification',
                    'client_type' => 'Individual or Group',
                    'group_number' => 'Group identifier for group loans',
                    'group_id' => 'Group ID',
                    
                    // Restructuring & Top-up
                    'restructure_loanId' => 'Original loan if restructured',
                    'parent_loan_id' => 'Parent loan for top-ups',
                    'selectedLoan' => 'Selected for bulk operations',
                    
                    // Approval Details
                    'approved_loan_value' => 'Approved amount',
                    'approved_term' => 'Approved term',
                    'supervisor_id' => 'Approving supervisor',
                    'supervisor_name' => 'Supervisor name',
                    'declined_at' => 'Decline timestamp',
                    'declined_by' => 'Who declined',
                    'decline_reason' => 'Reason for decline',
                    
                    // Assessment Data
                    'assessment_data' => 'JSON assessment details',
                    'take_home' => 'Net income after loan payment',
                    
                    // Account Numbers
                    'interest_account_number' => 'GL account for interest',
                    'charge_account_number' => 'GL account for charges',
                    'insurance_account_number' => 'GL account for insurance'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Borrower member (via client_number)',
                        'branches' => 'Originating branch (via branch_id)',
                        'loan_sub_products' => 'Loan product type (via loan_sub_product)',
                        'accounts' => 'Loan GL account (via loan_account_number)'
                    ],
                    'has_many' => [
                        'loans_schedules' => 'Repayment schedule',
                        'loan_collaterals' => 'Loan security',
                        'loan_guarantors' => 'Loan guarantors',
                        'loan_approvals' => 'Approval workflow',
                        'loan_documents' => 'Supporting documents',
                        'settled_loans' => 'Settlement records',
                        'loan_audit_logs' => 'Audit trail',
                        'loan_transactions' => 'Disbursements and repayments'
                    ]
                ],
                'data_flow' => 'Application → Assessment → Approval Workflow → Disbursement → Schedule Generation → Repayments → Closure',
                'livewire_component' => 'App\\Http\\Livewire\\Loans\\Loans'
            ],
            
            'loans_schedules' => [
                'description' => 'Loan repayment schedules with installment tracking',
                'model' => 'loans_schedules',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Related loan',
                    'installment' => 'Installment amount',
                    'interest' => 'Interest portion',
                    'principle' => 'Principal portion',
                    'opening_balance' => 'Balance before payment',
                    'closing_balance' => 'Balance after payment',
                    'installment_date' => 'Due date',
                    'completion_status' => 'Payment status (PENDING, COMPLETED, OVERDUE)',
                    'status' => 'Schedule status',
                    'payment' => 'Amount paid',
                    'interest_payment' => 'Interest paid',
                    'principle_payment' => 'Principal paid',
                    'penalties' => 'Penalty charges',
                    'amount_in_arrears' => 'Arrears amount',
                    'days_in_arrears' => 'Days overdue',
                    'promise_date' => 'Promise to pay date',
                    'next_check_date' => 'Next follow-up date',
                    'comment' => 'Payment notes',
                    'member_number' => 'Member identifier'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'Parent loan (via loan_id)',
                        'clients' => 'Member (via member_number)'
                    ]
                ],
                'data_flow' => 'Loan Disbursement → Generate Schedule → Track Payments → Update Status → Calculate Arrears'
            ],
            
            'loan_sub_products' => [
                'description' => 'Loan product configurations and parameters',
                'model' => 'Loan_sub_products',
                'key_fields' => [
                    'id' => 'Primary key',
                    'sub_product_id' => 'Product identifier',
                    'sub_product_name' => 'Product name',
                    'interest_rate' => 'Base interest rate',
                    'min_amount' => 'Minimum loan amount',
                    'max_amount' => 'Maximum loan amount',
                    'min_term' => 'Minimum term (months)',
                    'max_term' => 'Maximum term (months)',
                    'processing_fee' => 'Processing fee (%)',
                    'insurance_fee' => 'Insurance fee (%)',
                    'penalty_rate' => 'Late payment penalty rate',
                    'grace_period' => 'Grace period days',
                    'eligibility_criteria' => 'Eligibility requirements',
                    'required_documents' => 'Required documentation',
                    'collateral_requirement' => 'Collateral percentage',
                    'guarantor_requirement' => 'Number of guarantors'
                ],
                'relationships' => [
                    'has_many' => [
                        'loans' => 'Loans using this product',
                        'loan_product_charges' => 'Associated charges'
                    ]
                ],
                'data_flow' => 'Product Setup → Loan Application → Parameter Validation → Pricing Calculation'
            ],
            
            'loan_collaterals' => [
                'description' => 'Loan security and collateral management',
                'model' => 'LoanCollateral',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_guarantor_id' => 'Related guarantor',
                    'collateral_type' => 'Type (savings, deposits, shares, physical)',
                    'account_id' => 'Account for financial collateral',
                    'collateral_amount' => 'Collateral value',
                    'account_balance' => 'Current balance',
                    'locked_amount' => 'Amount locked',
                    'available_amount' => 'Available for withdrawal',
                    'physical_collateral_id' => 'Physical asset ID',
                    'physical_collateral_description' => 'Asset description',
                    'physical_collateral_location' => 'Asset location',
                    'physical_collateral_value' => 'Valuation amount',
                    'physical_collateral_valuation_date' => 'Valuation date',
                    'insurance_policy_number' => 'Insurance policy',
                    'insurance_company_name' => 'Insurer',
                    'insurance_expiration_date' => 'Insurance expiry',
                    'status' => 'Status (active, inactive, released, forfeited)',
                    'collateral_start_date' => 'Collateralization date',
                    'collateral_end_date' => 'Release date'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loan_guarantors' => 'Guarantor (via loan_guarantor_id)',
                        'accounts' => 'Financial collateral account (via account_id)'
                    ]
                ],
                'data_flow' => 'Collateral Registration → Valuation → Locking → Monitoring → Release/Foreclosure'
            ],
            
            'loan_guarantors' => [
                'description' => 'Loan guarantor information and commitments',
                'model' => 'LoanGuarantor',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Guaranteed loan',
                    'guarantor_member_id' => 'Guarantor member ID',
                    'guarantee_amount' => 'Amount guaranteed',
                    'guarantor_type' => 'Individual or Corporate',
                    'relationship_with_borrower' => 'Relationship',
                    'income_source' => 'Guarantor income source',
                    'monthly_income' => 'Guarantor monthly income',
                    'existing_obligations' => 'Other obligations',
                    'net_worth' => 'Guarantor net worth',
                    'acceptance_status' => 'Acceptance status',
                    'acceptance_date' => 'Date accepted',
                    'release_date' => 'Date released',
                    'status' => 'Active/Released'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'Guaranteed loan (via loan_id)',
                        'clients' => 'Guarantor member (via guarantor_member_id)'
                    ],
                    'has_many' => [
                        'loan_collaterals' => 'Collateral provided'
                    ]
                ],
                'data_flow' => 'Guarantor Nomination → Verification → Acceptance → Commitment → Release'
            ],
            
            'settled_loans' => [
                'description' => 'Settled and closed loan records',
                'model' => 'SettledLoan',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Original loan',
                    'settlement_date' => 'Settlement date',
                    'settlement_type' => 'Full payment, Write-off, Restructure',
                    'total_paid' => 'Total amount paid',
                    'total_principal_paid' => 'Principal paid',
                    'total_interest_paid' => 'Interest paid',
                    'total_penalties_paid' => 'Penalties paid',
                    'waived_amount' => 'Amount waived',
                    'write_off_amount' => 'Amount written off',
                    'settlement_officer' => 'Officer handling settlement',
                    'settlement_notes' => 'Settlement details'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'Original loan (via loan_id)',
                        'users' => 'Settlement officer'
                    ]
                ],
                'data_flow' => 'Loan Completion → Settlement Processing → Collateral Release → Account Closure'
            ],
            
            'loan_approvals' => [
                'description' => 'Loan approval workflow and stages',
                'model' => 'LoanApproval',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Loan being approved',
                    'stage_id' => 'Approval stage',
                    'stage_name' => 'Stage description',
                    'approver_id' => 'Approving user',
                    'approval_status' => 'PENDING, APPROVED, REJECTED',
                    'approval_date' => 'Decision date',
                    'comments' => 'Approval comments',
                    'conditions' => 'Approval conditions',
                    'next_stage_id' => 'Next stage in workflow'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'Loan (via loan_id)',
                        'users' => 'Approver (via approver_id)',
                        'approval_stages' => 'Stage configuration'
                    ]
                ],
                'data_flow' => 'Application → Branch Review → Credit Analysis → Risk Assessment → Committee → Final Approval'
            ],
            
            'loan_documents' => [
                'description' => 'Loan application supporting documents',
                'model' => 'LoanDocument',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Related loan',
                    'document_type' => 'Type of document',
                    'document_name' => 'Document name',
                    'file_path' => 'Storage location',
                    'uploaded_by' => 'Uploader',
                    'verified_by' => 'Verifier',
                    'verification_status' => 'Verification status',
                    'verification_date' => 'Verification date'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'Loan (via loan_id)',
                        'users' => 'Uploader and verifier'
                    ]
                ],
                'data_flow' => 'Document Upload → Storage → Verification → Approval Reference'
            ],
            
            'loan_product_charges' => [
                'description' => 'Loan fees and charges configuration',
                'model' => 'LoanProductCharge',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_product_id' => 'Loan product',
                    'charge_name' => 'Charge description',
                    'charge_type' => 'PERCENTAGE or FIXED',
                    'charge_value' => 'Charge amount/rate',
                    'charge_timing' => 'UPFRONT, MONTHLY, ANNUALLY',
                    'gl_account' => 'Income GL account',
                    'is_mandatory' => 'Required charge',
                    'is_negotiable' => 'Can be waived'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loan_sub_products' => 'Product (via loan_product_id)'
                    ]
                ],
                'data_flow' => 'Product Configuration → Loan Pricing → Charge Calculation → GL Posting'
            ],

            'query_responses' => [
                'description' => 'Credit bureau and external API query responses - stores NBC credit reports and other external data',
                'model' => 'QueryResponseModel',
                'key_fields' => [
                    'id' => 'Primary key',
                    'message_id' => 'Unique message identifier from external system',
                    'connector_id' => 'ID of the connector used (e.g., NBC, CRB)',
                    'type' => 'Type of query (credit_check, verification, etc.)',
                    'message' => 'Response message',
                    'response_data' => 'JSONB field containing full response data including contracts, credit history',
                    'timestamp' => 'When the query was made',
                    'CheckNumber' => 'Reference number for the check (e.g., NIN, client number)',
                    'created_at' => 'Record creation timestamp',
                    'updated_at' => 'Last update timestamp'
                ],
                'relationships' => [
                    'Used in loan assessment for credit scoring',
                    'Referenced in assessment.blade.php for NBC contract history',
                    'Calculates total contracts and amounts for risk assessment'
                ],
                'business_rules' => [
                    'NBC credit checks use CheckNumber field',
                    'response_data contains nested JSON with CustomReport.Contracts.ContractList',
                    'Used to determine creditworthiness and existing obligations'
                ]
            ],

            'loan_images' => [
                'description' => 'Document management for loans - stores all loan-related documents including guarantor docs, collateral images',
                'model' => 'loan_images',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Foreign key to loans table',
                    'category' => 'Document category (guarantor, add-document, collateral, etc.)',
                    'filename' => 'Stored filename',
                    'url' => 'Full URL or path to document',
                    'document_descriptions' => 'Detailed description of the document',
                    'document_category' => 'Additional categorization',
                    'file_size' => 'Size of file in bytes',
                    'mime_type' => 'MIME type of the document',
                    'original_name' => 'Original filename uploaded by user',
                    'created_at' => 'Upload timestamp',
                    'updated_at' => 'Last modification timestamp'
                ],
                'relationships' => [
                    'belongs_to' => 'loans (via loan_id)',
                    'Referenced by LoanTabStateService for document tracking',
                    'Used in guarantor and document tabs of loan application'
                ],
                'business_rules' => [
                    'category="guarantor" for guarantor documents',
                    'category="add-document" for additional loan documents',
                    'Required for loan application completion',
                    'Document tab considered complete when at least one document exists'
                ]
            ],

            'loan_process_progress' => [
                'description' => 'Tracks loan application workflow progress through different tabs/stages',
                'model' => 'LoanProcessProgress',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Foreign key to loans table (unique)',
                    'completed_tabs' => 'JSON array of completed tab names',
                    'tab_data' => 'JSON object with additional tab-specific data',
                    'created_at' => 'Progress record creation',
                    'updated_at' => 'Last progress update'
                ],
                'relationships' => [
                    'belongs_to' => 'loans (one-to-one via loan_id)',
                    'Managed by LoanTabStateService'
                ],
                'workflow_tabs' => [
                    'client' => 'Client information verification',
                    'guarantor' => 'Guarantor and collateral details',
                    'addDocument' => 'Document uploads',
                    'assessment' => 'Loan assessment and approval'
                ],
                'business_rules' => [
                    'Each loan has exactly one progress record',
                    'Tabs can be marked complete manually or based on data',
                    'All 4 tabs must be complete before loan can proceed',
                    'Used to track and restore application state'
                ]
            ],

            // ========= PRODUCT MANAGEMENT SYSTEM =========
            'sub_products' => [
                'description' => 'Unified product configuration table for all SACCO products (shares, savings, deposits, loans)',
                'model' => 'sub_products',
                'key_fields' => [
                    'id' => 'Primary key',
                    'product_name' => 'Product name',
                    'product_type' => 'Product type code (1000=shares, 2000=savings, 3000=deposits, 4000=loans)',
                    'sub_product_name' => 'Specific product variant name',
                    'sub_product_id' => 'Product variant identifier',
                    'sub_product_status' => 'Product status (1=active, 0=inactive)',
                    'currency' => 'Product currency (TZS, USD, etc.)',
                    // Share-specific fields
                    'share_type_id' => 'Type of share product',
                    'shares_allocated' => 'Total shares allocated for product',
                    'available_shares' => 'Shares available for purchase',
                    'issued_shares' => 'Shares already issued',
                    'nominal_price' => 'Price per share',
                    'minimum_required_shares' => 'Minimum shares required',
                    'lock_in_period' => 'Share lock-in period (days)',
                    'dividend_eligibility_period' => 'Period before dividend eligibility',
                    'dividend_payment_frequency' => 'Dividend payment schedule',
                    'allow_share_transfer' => 'Whether shares can be transferred',
                    'allow_share_withdrawal' => 'Whether shares can be withdrawn',
                    'enable_dividend_calculation' => 'Auto-calculate dividends',
                    // Savings-specific fields
                    'savings_type_id' => 'Type of savings product',
                    'min_balance' => 'Minimum balance requirement',
                    'interest_value' => 'Interest rate',
                    'interest_tenure' => 'Interest calculation period',
                    // Deposit-specific fields
                    'deposit_type_id' => 'Type of deposit product',
                    // Common transaction fields
                    'deposit' => 'Allow deposits (1/0)',
                    'deposit_charge' => 'Deposit fee',
                    'deposit_charge_min_value' => 'Minimum deposit charge',
                    'deposit_charge_max_value' => 'Maximum deposit charge',
                    'withdraw' => 'Allow withdrawals (1/0)',
                    'withdraw_charge' => 'Withdrawal fee',
                    'withdraw_charge_min_value' => 'Minimum withdrawal charge',
                    'withdraw_charge_max_value' => 'Maximum withdrawal charge',
                    // Fee fields
                    'maintenance_fees' => 'Account maintenance fee',
                    'maintenance_fees_value' => 'Maintenance fee amount',
                    'ledger_fees' => 'Ledger fees applicable',
                    'ledger_fees_value' => 'Ledger fee amount',
                    // Account fields
                    'product_account' => 'GL account for product',
                    'profit_account' => 'GL account for profits',
                    'collection_account_withdraw_charges' => 'GL for withdrawal charges',
                    'collection_account_deposit_charges' => 'GL for deposit charges',
                    'collection_account_interest_charges' => 'GL for interest charges',
                    // Settings fields
                    'inactivity' => 'Days before account becomes inactive',
                    'create_during_registration' => 'Auto-create during member registration',
                    'activated_by_lower_limit' => 'Activate when minimum balance reached',
                    'requires_approval' => 'Requires approval to open',
                    'generate_atm_card_profile' => 'Generate ATM card',
                    'allow_statement_generation' => 'Allow statement generation',
                    'send_notifications' => 'Send SMS/email notifications',
                    'require_image_member' => 'Require member photo',
                    'require_image_id' => 'Require ID photo',
                    'require_mobile_number' => 'Require mobile number',
                    'generate_mobile_profile' => 'Generate mobile banking profile',
                    // SMS Settings
                    'sms_sender_name' => 'SMS sender ID',
                    'sms_api_key' => 'SMS gateway API key',
                    'sms_enabled' => 'SMS notifications enabled',
                    // Other fields
                    'payment_methods' => 'JSON array of accepted payment methods',
                    'withdrawal_approval_level' => 'Approval level required (1-3)',
                    'notes' => 'Product notes/description',
                    'created_by' => 'User who created product',
                    'updated_by' => 'Last updated by'
                ],
                'relationships' => [
                    'has_many' => [
                        'accounts' => 'Member accounts using this product',
                        'loans' => 'Loans using this product (for loan products)',
                        'share_transactions' => 'Share transactions (for share products)'
                    ],
                    'belongs_to' => [
                        'savings_types' => 'Savings type (via savings_type_id)',
                        'deposit_types' => 'Deposit type (via deposit_type_id)',
                        'institutions' => 'Institution (via institution_id)',
                        'branches' => 'Branch (via branch)'
                    ]
                ],
                'business_rules' => [
                    'Product type 1000 = Shares',
                    'Product type 2000 = Savings',
                    'Product type 3000 = Deposits',
                    'Product type 4000 = Loans',
                    'Each product type uses specific fields from the unified table',
                    'Products can be configured with various fees, limits, and approval requirements'
                ]
            ],

            'savings_types' => [
                'description' => 'Categories of savings products offered by the SACCO',
                'model' => 'SavingsType',
                'key_fields' => [
                    'id' => 'Primary key',
                    'type' => 'Savings type name',
                    'summary' => 'Description of the savings type',
                    'status' => 'Active/inactive status',
                    'institution_id' => 'Institution offering this type'
                ],
                'relationships' => [
                    'has_many' => 'sub_products (savings products)',
                    'belongs_to' => 'institutions'
                ],
                'common_types' => [
                    'Regular Savings' => 'Standard savings account with low minimum balance',
                    'Target Savings' => 'Goal-based savings with fixed target amount',
                    'Education Savings' => 'Savings for education expenses',
                    'Emergency Savings' => 'Rainy day fund savings',
                    'Group Savings' => 'Collective savings for groups',
                    'Junior Savings' => 'Savings accounts for minors'
                ]
            ],

            'deposit_types' => [
                'description' => 'Categories of deposit products offered by the SACCO',
                'model' => 'DepositType',
                'key_fields' => [
                    'id' => 'Primary key',
                    'type' => 'Deposit type name',
                    'summary' => 'Description of the deposit type',
                    'status' => 'Active/inactive status',
                    'institution_id' => 'Institution offering this type'
                ],
                'relationships' => [
                    'has_many' => 'sub_products (deposit products)',
                    'belongs_to' => 'institutions'
                ],
                'common_types' => [
                    'Fixed Deposit' => 'Term deposit with fixed interest rate',
                    'Recurring Deposit' => 'Regular monthly deposits',
                    'Call Deposit' => 'Deposit withdrawable on demand with notice',
                    'Certificate of Deposit' => 'Negotiable certificate deposit',
                    'Tax Saver Deposit' => 'Tax-advantaged deposit scheme'
                ]
            ],

            // ========= ACCOUNTING MANAGEMENT SYSTEM =========
            'general_ledger' => [
                'description' => 'Core double-entry bookkeeping ledger tracking all financial transactions',
                'model' => 'GeneralLedgerModel',
                'key_fields' => [
                    'id' => 'Primary key',
                    'record_on_account_number' => 'Account being recorded',
                    'record_on_account_number_balance' => 'Account balance after transaction',
                    'sender_branch_id' => 'Originating branch',
                    'beneficiary_branch_id' => 'Destination branch',
                    'sender_product_id' => 'Sender product type',
                    'sender_sub_product_id' => 'Sender sub-product',
                    'beneficiary_product_id' => 'Beneficiary product type',
                    'beneficiary_sub_product_id' => 'Beneficiary sub-product',
                    'sender_id' => 'Sender user/member ID',
                    'beneficiary_id' => 'Beneficiary user/member ID',
                    'sender_name' => 'Sender name',
                    'beneficiary_name' => 'Beneficiary name',
                    'sender_account_number' => 'Debit account',
                    'beneficiary_account_number' => 'Credit account',
                    'transaction_type' => 'Type of transaction',
                    'sender_account_currency_type' => 'Debit account currency',
                    'beneficiary_account_currency_type' => 'Credit account currency',
                    'narration' => 'Transaction description',
                    'branch_id' => 'Processing branch',
                    'credit' => 'Credit amount',
                    'debit' => 'Debit amount',
                    'reference_number' => 'Unique transaction reference',
                    'trans_status' => 'Transaction status (PENDING/POSTED/REVERSED)',
                    'trans_status_description' => 'Status details',
                    'swift_code' => 'SWIFT code for international',
                    'destination_bank_name' => 'External bank name',
                    'destination_bank_number' => 'External bank account',
                    'partner_bank' => 'Partner bank ID',
                    'partner_bank_name' => 'Partner bank name',
                    'partner_bank_account_number' => 'Partner account number',
                    'partner_bank_transaction_reference_number' => 'External reference',
                    'payment_status' => 'Payment status',
                    'recon_status' => 'Reconciliation status',
                    'loan_id' => 'Related loan if applicable',
                    'bank_reference_number' => 'Bank statement reference',
                    'product_number' => 'Product code (1000/2000/3000/4000)',
                    'major_category_code' => 'Major GL category',
                    'category_code' => 'GL category',
                    'sub_category_code' => 'GL sub-category',
                    'gl_balance' => 'GL account balance',
                    'account_level' => 'Account hierarchy level'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Related accounts',
                        'branches' => 'Related branches',
                        'transactions' => 'Source transactions'
                    ]
                ],
                'business_rules' => [
                    'Every transaction must balance (debits = credits)',
                    'Cannot post to control accounts directly',
                    'Requires approval for amounts above threshold',
                    'Auto-reconciliation with bank statements'
                ]
            ],

            'expenses' => [
                'description' => 'Expense tracking with budget management and approval workflow',
                'model' => 'ExpenseModel',
                'key_fields' => [
                    'id' => 'Primary key',
                    'account_id' => 'Expense GL account',
                    'amount' => 'Expense amount',
                    'description' => 'Expense description',
                    'payment_type' => 'Payment method (cash/cheque/transfer)',
                    'user_id' => 'User who created expense',
                    'status' => 'Status (PENDING_APPROVAL/APPROVED/REJECTED/RETIRED)',
                    'approval_id' => 'Approval request ID',
                    'retirement_receipt_path' => 'Receipt document path',
                    'budget_item_id' => 'Related budget item',
                    'monthly_budget_amount' => 'Budgeted amount for month',
                    'monthly_spent_amount' => 'Amount spent this month',
                    'budget_utilization_percentage' => 'Budget usage percentage',
                    'budget_status' => 'WITHIN_BUDGET/OVER_BUDGET/AT_LIMIT',
                    'budget_resolution' => 'How over-budget was resolved',
                    'budget_notes' => 'Budget-related notes',
                    'expense_month' => 'Month of expense'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'GL account (via account_id)',
                        'users' => 'Creator (via user_id)',
                        'approvals' => 'Approval workflow (via approval_id)',
                        'budget_managements' => 'Budget item (via budget_item_id)'
                    ]
                ],
                'business_rules' => [
                    'Requires approval based on amount and user level',
                    'Must be within budget unless override approved',
                    'Retirement receipts required for accountability',
                    'Automatic budget tracking and alerts'
                ]
            ],

            'strongroom_ledgers' => [
                'description' => 'Vault and strongroom cash management tracking',
                'model' => 'StrongroomLedger',
                'key_fields' => [
                    'id' => 'Primary key',
                    'vault_id' => 'Related vault',
                    'balance' => 'Current cash balance',
                    'total_deposits' => 'Total deposits made',
                    'total_withdrawals' => 'Total withdrawals made',
                    'denomination_breakdown' => 'JSON of cash denominations',
                    'branch_id' => 'Branch location',
                    'vault_code' => 'Unique vault identifier',
                    'status' => 'Vault status (active/inactive/maintenance)',
                    'notes' => 'Vault notes',
                    'last_transaction_at' => 'Last activity timestamp'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'vaults' => 'Physical vault (via vault_id)',
                        'branches' => 'Branch (via branch_id)'
                    ],
                    'has_many' => [
                        'cash_movements' => 'Cash in/out movements'
                    ]
                ],
                'business_rules' => [
                    'Dual control required for access',
                    'Daily reconciliation required',
                    'Maximum cash limits enforced',
                    'Denomination tracking for cash planning'
                ]
            ],

            'ppes' => [
                'description' => 'Property, Plant, and Equipment asset register with depreciation tracking',
                'model' => 'PPE',
                'key_fields' => [
                    'id' => 'Primary key',
                    'name' => 'Asset name',
                    'category' => 'Asset category (Land/Building/Vehicle/Equipment/Furniture)',
                    'purchase_price' => 'Acquisition cost',
                    'purchase_date' => 'Acquisition date',
                    'salvage_value' => 'Residual value',
                    'useful_life' => 'Useful life in years',
                    'quantity' => 'Number of items',
                    'initial_value' => 'Starting book value',
                    'depreciation_rate' => 'Annual depreciation rate',
                    'accumulated_depreciation' => 'Total depreciation to date',
                    'depreciation_for_year' => 'Current year depreciation',
                    'depreciation_for_month' => 'Current month depreciation',
                    'closing_value' => 'Net book value',
                    'status' => 'Asset status (active/disposed/impaired)',
                    'location' => 'Physical location',
                    'account_number' => 'Asset GL account',
                    // Additional costs
                    'legal_fees' => 'Legal fees for acquisition',
                    'registration_fees' => 'Registration costs',
                    'renovation_costs' => 'Renovation expenses',
                    'transportation_costs' => 'Delivery costs',
                    'installation_costs' => 'Setup costs',
                    'other_costs' => 'Miscellaneous costs',
                    // Payment details
                    'payment_method' => 'How asset was paid for',
                    'payment_account_number' => 'Payment source account',
                    'payable_account_number' => 'Payables account used',
                    'accounting_transaction_id' => 'GL transaction ID',
                    'accounting_entry_created' => 'GL entry posted flag',
                    // Supplier information
                    'supplier_name' => 'Vendor name',
                    'invoice_number' => 'Purchase invoice number',
                    'invoice_date' => 'Invoice date',
                    // Disposal information
                    'disposal_date' => 'Date of disposal',
                    'disposal_method' => 'How disposed (sale/scrap/donation)',
                    'disposal_proceeds' => 'Amount received on disposal',
                    'disposal_notes' => 'Disposal details',
                    'disposal_approval_status' => 'Disposal approval status',
                    'disposal_approved_by' => 'Who approved disposal',
                    'disposal_approved_at' => 'When disposal approved',
                    'disposal_rejection_reason' => 'Why disposal rejected'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Asset GL account',
                        'users' => 'Disposal approver'
                    ],
                    'has_many' => [
                        'depreciation_schedules' => 'Monthly depreciation entries'
                    ]
                ],
                'business_rules' => [
                    'Straight-line depreciation method',
                    'Monthly depreciation posted to GL',
                    'Disposal requires approval',
                    'Gain/loss on disposal calculated automatically',
                    'Asset register reconciled with GL'
                ]
            ],

            // ========= COMPREHENSIVE ACCOUNTING TABLES =========
            'receivables' => [
                'description' => 'Accounts receivable management for tracking money owed to the SACCO',
                'model' => 'ReceivableModel',
                'key_fields' => [
                    'id' => 'Primary key',
                    'account_number' => 'GL account number',
                    'customer_id' => 'Customer/member ID',
                    'customer_name' => 'Customer name',
                    'invoice_number' => 'Invoice reference',
                    'due_date' => 'Payment due date',
                    'amount' => 'Amount owed',
                    'status' => 'Payment status (unpaid/partial/paid)',
                    'description' => 'Receivable description',
                    'source' => 'Source of receivable',
                    'income_account' => 'Income GL account',
                    'asset_account' => 'Asset GL account',
                    'payment' => 'Payment received',
                    'receivable_type' => 'Type (credit_sales/service/rental/etc)',
                    'service_type' => 'Type of service',
                    'property_type' => 'Property rental type',
                    'investment_type' => 'Investment return type',
                    'insurance_claim_type' => 'Insurance claim category',
                    'government_agency' => 'Government entity',
                    'contract_type' => 'Contract category',
                    'subscription_type' => 'Subscription service',
                    'installment_plan' => 'Payment installment details',
                    'royalty_type' => 'Royalty category',
                    'commission_type' => 'Commission structure',
                    'utility_type' => 'Utility service',
                    'healthcare_type' => 'Healthcare service',
                    'education_type' => 'Education fee type',
                    'aging_date' => 'Date for aging analysis',
                    'payment_terms' => 'Payment terms (NET30/NET60/etc)',
                    'collection_status' => 'Collection efforts status',
                    'collection_notes' => 'Collection follow-up notes',
                    'assigned_to' => 'Collection agent',
                    'last_payment_date' => 'Date of last payment',
                    'last_payment_amount' => 'Amount of last payment',
                    'payment_method' => 'How payment was received',
                    'reference_number' => 'Payment reference',
                    'revenue_category' => 'Revenue classification',
                    'cost_center' => 'Cost center allocation',
                    'project_code' => 'Project allocation',
                    'department' => 'Department allocation',
                    'document_reference' => 'Supporting document',
                    'approval_status' => 'Approval workflow status',
                    'approved_by' => 'Approver user',
                    'approved_at' => 'Approval timestamp'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Customer (via customer_id)',
                        'accounts' => 'GL accounts',
                        'users' => 'Approver and assignee'
                    ]
                ],
                'business_rules' => [
                    'Automatic aging analysis (30/60/90/120+ days)',
                    'Collection workflow automation',
                    'Bad debt provision calculation',
                    'Revenue recognition rules'
                ]
            ],

            'payables' => [
                'description' => 'Accounts payable for tracking money owed by the SACCO',
                'model' => 'PayableModel',
                'key_fields' => [
                    'id' => 'Primary key',
                    'customer_name' => 'Vendor/supplier name',
                    'due_date' => 'Payment due date',
                    'invoice_number' => 'Vendor invoice number',
                    'amount' => 'Amount owed',
                    'liability_account' => 'Payables GL account',
                    'cash_account' => 'Payment source account',
                    'expense_account' => 'Expense GL account'
                ],
                'relationships' => [
                    'belongs_to' => 'accounts (GL accounts)',
                    'has_many' => 'payments'
                ],
                'business_rules' => [
                    'Approval required based on amount',
                    'Early payment discount tracking',
                    'Vendor statement reconciliation'
                ]
            ],

            'standing_instructions' => [
                'description' => 'Automated recurring payment instructions',
                'model' => 'StandingInstruction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'member_id' => 'Member setting up instruction',
                    'source_account_number' => 'Debit account',
                    'source_bank_id' => 'Source bank for external',
                    'destination_account_name' => 'Beneficiary name',
                    'bank' => 'Destination bank',
                    'destination_account_id' => 'Credit account',
                    'saccos_branch_id' => 'Processing branch',
                    'amount' => 'Transfer amount',
                    'frequency' => 'Payment frequency (daily/weekly/monthly)',
                    'start_date' => 'First payment date',
                    'end_date' => 'Last payment date',
                    'reference_number' => 'Standing order reference',
                    'service' => 'Service description',
                    'status' => 'Status (active/suspended/cancelled)',
                    'description' => 'Payment description'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Member (via member_id)',
                        'banks' => 'External bank (via source_bank_id)',
                        'accounts' => 'SACCO accounts',
                        'branches' => 'Processing branch'
                    ]
                ],
                'business_rules' => [
                    'Automatic execution on scheduled dates',
                    'Insufficient funds handling',
                    'Notification on execution/failure',
                    'Audit trail of all executions'
                ]
            ],

            'interest_payables' => [
                'description' => 'Interest obligations on deposits and borrowings',
                'model' => 'InterestPayable',
                'key_fields' => [
                    'id' => 'Primary key',
                    'member_id' => 'Member for deposit interest',
                    'account_type' => 'Type of interest-bearing account',
                    'amount' => 'Principal amount',
                    'interest_rate' => 'Annual interest rate',
                    'deposit_date' => 'Deposit start date',
                    'maturity_date' => 'Maturity date',
                    'payment_frequency' => 'Interest payment schedule',
                    'accrued_interest' => 'Interest accrued to date',
                    'interest_payable' => 'Current interest liability',
                    'loan_provider' => 'External loan provider',
                    'loan_interest_rate' => 'Borrowing interest rate',
                    'loan_term' => 'Loan term',
                    'loan_start_date' => 'Loan start date',
                    'interest_payment_schedule' => 'Payment schedule',
                    'accrued_interest_loan' => 'Accrued on borrowings',
                    'interest_payable_loan' => 'Payable on borrowings'
                ],
                'relationships' => [
                    'belongs_to' => 'clients (members)',
                    'has_many' => 'interest_payments'
                ],
                'business_rules' => [
                    'Daily interest accrual calculation',
                    'Compound vs simple interest',
                    'Tax withholding on interest payments'
                ]
            ],

            'loan_loss_provisions' => [
                'description' => 'Provisions for expected credit losses on loans',
                'model' => 'LoanLossProvision',
                'key_fields' => [
                    'id' => 'Primary key',
                    'provision_date' => 'Date of provision',
                    'loan_id' => 'Related loan',
                    'client_number' => 'Borrower number',
                    'loan_classification' => 'Risk classification (PERFORMING/WATCH/SUBSTANDARD/DOUBTFUL/LOSS)',
                    'outstanding_balance' => 'Loan outstanding amount',
                    'provision_rate' => 'Provision percentage',
                    'provision_amount' => 'Provision amount',
                    'previous_provision' => 'Previous provision amount',
                    'provision_adjustment' => 'Change in provision',
                    'provision_type' => 'Type (general/specific)',
                    'days_in_arrears' => 'Days overdue',
                    'status' => 'Provision status (active/released/written_off)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'Loan (via loan_id)',
                        'provision_rates_config' => 'Rate configuration'
                    ]
                ],
                'business_rules' => [
                    'PERFORMING: 1% general provision',
                    'WATCH (1-30 days): 5% specific provision',
                    'SUBSTANDARD (31-90 days): 25% specific provision',
                    'DOUBTFUL (91-180 days): 50% specific provision',
                    'LOSS (180+ days): 100% specific provision'
                ]
            ],

            'provision_rates_config' => [
                'description' => 'Configuration for loan loss provision rates',
                'model' => 'ProvisionRateConfig',
                'key_fields' => [
                    'id' => 'Primary key',
                    'classification' => 'Risk classification',
                    'min_days' => 'Minimum days in arrears',
                    'max_days' => 'Maximum days in arrears',
                    'provision_rate' => 'Required provision percentage',
                    'provision_type' => 'General or specific',
                    'description' => 'Classification description',
                    'is_active' => 'Active status'
                ],
                'relationships' => [
                    'has_many' => 'loan_loss_provisions'
                ]
            ],

            'loan_loss_provision_summary' => [
                'description' => 'Daily aggregate summary of loan loss provisions',
                'model' => 'LoanLossProvisionSummary',
                'key_fields' => [
                    'id' => 'Primary key',
                    'summary_date' => 'Summary date',
                    'total_loans' => 'Total loan count',
                    'total_outstanding' => 'Total loan portfolio',
                    'performing_balance' => 'Performing loans amount',
                    'watch_balance' => 'Watch list amount',
                    'substandard_balance' => 'Substandard loans amount',
                    'doubtful_balance' => 'Doubtful loans amount',
                    'loss_balance' => 'Loss category amount',
                    'general_provisions' => 'Total general provisions',
                    'specific_provisions' => 'Total specific provisions',
                    'total_provisions' => 'Total provisions held',
                    'provision_coverage_ratio' => 'Provisions/NPL percentage',
                    'npl_ratio' => 'Non-performing loans ratio',
                    'statistics' => 'Additional statistics JSON'
                ],
                'business_rules' => [
                    'Daily calculation and aggregation',
                    'Regulatory reporting compliance',
                    'Trend analysis for risk management'
                ]
            ],

            'cheque_books' => [
                'description' => 'Cheque book inventory and management',
                'model' => 'ChequeBook',
                'key_fields' => [
                    'id' => 'Primary key',
                    'bank' => 'Bank ID',
                    'chequeBook_id' => 'Cheque book identifier',
                    'remaining_leaves' => 'Unused cheques count',
                    'leave_number' => 'Current cheque number',
                    'institution_id' => 'SACCO institution',
                    'branch_id' => 'Branch location',
                    'status' => 'Book status (PENDING/ACTIVE/EXHAUSTED)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'banks' => 'Bank (via bank)',
                        'branches' => 'Branch (via branch_id)',
                        'institutions' => 'Institution'
                    ],
                    'has_many' => 'cheques'
                ]
            ],

            'cheques' => [
                'description' => 'Individual cheque tracking and clearing',
                'model' => 'Cheque',
                'key_fields' => [
                    'id' => 'Primary key',
                    'customer_account' => 'Account to debit',
                    'amount' => 'Cheque amount',
                    'cheque_number' => 'Cheque number',
                    'branch' => 'Issuing branch',
                    'finance_approver' => 'Finance approval',
                    'manager_approver' => 'Manager approval',
                    'expiry_date' => 'Cheque expiry date',
                    'is_cleared' => 'Clearing status',
                    'status' => 'Cheque status (ISSUED/PRESENTED/CLEARED/BOUNCED/STOPPED)',
                    'bank_account' => 'Bank account number'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Customer account',
                        'branches' => 'Branch',
                        'cheque_books' => 'Cheque book'
                    ]
                ],
                'business_rules' => [
                    'Dual approval for amounts above threshold',
                    'Auto-expiry after validity period',
                    'Stop payment functionality',
                    'Clearing and reconciliation workflow'
                ]
            ],

            'financial_position' => [
                'description' => 'Statement of financial position (balance sheet) data',
                'model' => 'FinancialPosition',
                'key_fields' => [
                    'id' => 'Primary key',
                    'interest_on_loans' => 'Interest income from loans',
                    'other_income' => 'Non-interest income',
                    'total_income' => 'Total revenue',
                    'expenses' => 'Total expenses',
                    'annual_surplus' => 'Net surplus/deficit',
                    'end_of_business_year' => 'Financial year end date'
                ],
                'relationships' => [
                    'calculated_from' => 'general_ledger entries'
                ],
                'business_rules' => [
                    'Annual calculation',
                    'Must balance (Assets = Liabilities + Equity)',
                    'Regulatory format compliance'
                ]
            ],

            'financial_ratios' => [
                'description' => 'Key financial ratios for performance monitoring',
                'model' => 'FinancialRatio',
                'key_fields' => [
                    'id' => 'Primary key',
                    'end_of_financial_year_date' => 'Calculation date',
                    'core_capital' => 'Tier 1 capital',
                    'total_assets' => 'Total assets',
                    'net_capital' => 'Net capital',
                    'short_term_assets' => 'Current assets',
                    'short_term_liabilities' => 'Current liabilities',
                    'expenses' => 'Operating expenses',
                    'income' => 'Operating income'
                ],
                'calculated_ratios' => [
                    'capital_adequacy_ratio' => 'Core capital / Risk-weighted assets',
                    'liquidity_ratio' => 'Short-term assets / Short-term liabilities',
                    'efficiency_ratio' => 'Expenses / Income',
                    'return_on_assets' => 'Net income / Total assets',
                    'debt_to_equity' => 'Total liabilities / Total equity'
                ],
                'business_rules' => [
                    'Regulatory minimum thresholds',
                    'Quarterly/annual calculation',
                    'Trend analysis and alerts'
                ]
            ],

            'cash_flow_configurations' => [
                'description' => 'Configuration for cash flow statement preparation',
                'model' => 'CashFlowConfiguration',
                'key_fields' => [
                    'id' => 'Primary key',
                    'section' => 'Cash flow section (operating/investing/financing)',
                    'account_id' => 'GL account',
                    'operation' => 'Addition or subtraction'
                ],
                'relationships' => [
                    'belongs_to' => 'accounts (GL accounts)'
                ],
                'sections' => [
                    'operating' => 'Cash from operations',
                    'investing' => 'Cash from investments',
                    'financing' => 'Cash from financing'
                ]
            ],

            // ========= BUDGET MANAGEMENT SYSTEM =========
            'main_budget' => [
                'description' => 'Main institutional budget with monthly allocations',
                'model' => 'MainBudget',
                'key_fields' => [
                    'id' => 'Primary key',
                    'institution_id' => 'Institution identifier',
                    'sub_category_code' => 'Budget category code',
                    'sub_category_name' => 'Budget category name',
                    'january' => 'January allocation',
                    'january_init' => 'January initial budget',
                    'february' => 'February allocation',
                    'february_init' => 'February initial budget',
                    'march' => 'March allocation',
                    'march_init' => 'March initial budget',
                    'april' => 'April allocation',
                    'april_init' => 'April initial budget',
                    'may' => 'May allocation',
                    'may_init' => 'May initial budget',
                    'june' => 'June allocation',
                    'june_init' => 'June initial budget',
                    'july' => 'July allocation',
                    'july_init' => 'July initial budget',
                    'august' => 'August allocation',
                    'august_init' => 'August initial budget',
                    'september' => 'September allocation',
                    'september_init' => 'September initial budget',
                    'october' => 'October allocation',
                    'october_init' => 'October initial budget',
                    'november' => 'November allocation',
                    'november_init' => 'November initial budget',
                    'december' => 'December allocation',
                    'december_init' => 'December initial budget',
                    'total' => 'Total annual budget',
                    'total_init' => 'Total initial budget',
                    'year' => 'Budget year',
                    'type' => 'Budget type (revenue/expense)'
                ],
                'business_rules' => [
                    'Monthly budget tracking',
                    'Initial vs actual comparison',
                    'Category-wise allocation',
                    'Annual budget consolidation'
                ]
            ],

            'budget_managements' => [
                'description' => 'Department budget planning and tracking with approval workflow',
                'model' => 'BudgetManagement',
                'key_fields' => [
                    'id' => 'Primary key',
                    'revenue' => 'Expected revenue (double)',
                    'expenditure' => 'Planned operational expenditure (double)',
                    'capital_expenditure' => 'Capital expenses (double)',
                    'budget_name' => 'Budget identifier/title',
                    'start_date' => 'Budget period start date',
                    'end_date' => 'Budget period end date',
                    'spent_amount' => 'Actual spent amount (double)',
                    'status' => 'Budget status (ACTIVE/INACTIVE)',
                    'approval_status' => 'Approval status (PENDING/APPROVED/REJECTED)',
                    'notes' => 'Budget notes and justification',
                    'department' => 'Department ID (integer)',
                    'currency' => 'Budget currency (default TZS)',
                    'expense_account_id' => 'Link to expense GL account'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'departments' => 'Department (via department)',
                        'accounts' => 'Expense account (via expense_account_id)'
                    ],
                    'has_many' => [
                        'budget_approvers' => 'Approval workflow',
                        'expenses' => 'Related expenses (via budget_item_id)'
                    ]
                ],
                'calculated_fields' => [
                    'total_amount' => 'revenue + capital_expenditure',
                    'remaining_amount' => 'total_amount - spent_amount',
                    'utilization_percentage' => '(spent_amount / total_amount) * 100'
                ],
                'business_rules' => [
                    'Department-wise budget allocation',
                    'Revenue vs expenditure tracking',
                    'Budget utilization monitoring',
                    'Multi-level approval workflow',
                    'Track actual vs budgeted amounts',
                    'Generate variance reports'
                ]
            ],

            'budget_accounts' => [
                'description' => 'Account-specific budget allocations',
                'model' => 'BudgetAccount',
                'key_fields' => [
                    'id' => 'Primary key (bigInteger)',
                    'account_id' => 'GL account ID',
                    'amount' => 'Budget amount',
                    'year' => 'Budget year',
                    'branch' => 'Branch identifier'
                ],
                'relationships' => [
                    'belongs_to' => 'accounts'
                ],
                'business_rules' => [
                    'Account-level budget control',
                    'Branch-wise allocation',
                    'Annual budget planning'
                ]
            ],

            'budget_approvers' => [
                'description' => 'Budget approval workflow management',
                'model' => 'BudgetApprover',
                'key_fields' => [
                    'id' => 'Primary key',
                    'user_id' => 'Approver user ID',
                    'user_name' => 'Approver name',
                    'status' => 'Approval status',
                    'budget_id' => 'Related budget ID'
                ],
                'relationships' => [
                    'belongs_to' => ['users', 'budget_managements']
                ],
                'business_rules' => [
                    'Multi-level approval hierarchy',
                    'Sequential approval workflow',
                    'Approval status tracking'
                ]
            ],

            // ========= SERVICES & SELF-SERVICE SYSTEM =========
            'services' => [
                'description' => 'Self-service offerings and service configuration for members',
                'model' => 'ServicesList',
                'table' => 'services',
                'key_fields' => [
                    'id' => 'Primary key',
                    'name' => 'Service name',
                    'code' => 'Service code (3 chars)',
                    'description' => 'Service description',
                    'is_mandatory' => 'Whether service is mandatory',
                    'lower_limit' => 'Minimum transaction amount',
                    'upper_limit' => 'Maximum transaction amount',
                    'isRecurring' => 'Whether service recurs (legacy)',
                    'paymentMode' => 'Payment mode code (legacy)',
                    'debit_account' => 'GL account to debit',
                    'credit_account' => 'GL account to credit',
                    'payment_mode' => 'Payment method (1/2/3)',
                    'is_recurring' => 'Whether service recurs'
                ],
                'relationships' => [
                    'belongs_to' => 'accounts (GL accounts via debit/credit_account)'
                ],
                'business_rules' => [
                    'Service codes must be unique',
                    'Mandatory services cannot be disabled',
                    'Transaction limits enforced',
                    'GL accounts must balance'
                ]
            ],

            // ========= HUMAN RESOURCES MANAGEMENT =========
            'employees' => [
                'description' => 'Employee master data and HR management',
                'model' => 'Employee',
                'key_fields' => [
                    'id' => 'Primary key',
                    'institution_user_id' => 'Institution user link',
                    'first_name' => 'First name',
                    'middle_name' => 'Middle name',
                    'last_name' => 'Last name',
                    'date_of_birth' => 'Date of birth',
                    'gender' => 'Gender',
                    'marital_status' => 'Marital status',
                    'nationality' => 'Nationality',
                    'address' => 'Physical address',
                    'street' => 'Street address',
                    'city' => 'City',
                    'region' => 'Region/state',
                    'district' => 'District',
                    'ward' => 'Ward/area',
                    'postal_code' => 'Postal code',
                    'phone' => 'Phone number',
                    'email' => 'Email address',
                    'job_title' => 'Job title/position',
                    'branch_id' => 'Branch assignment',
                    'hire_date' => 'Employment start date',
                    'basic_salary' => 'Basic salary amount',
                    'gross_salary' => 'Gross salary amount',
                    'payment_frequency' => 'Salary payment frequency',
                    'employee_status' => 'Employment status',
                    'registering_officer' => 'Who registered employee',
                    'employment_type' => 'Full-time/Part-time/Contract',
                    'emergency_contact_name' => 'Emergency contact name',
                    'emergency_contact_relationship' => 'Emergency contact relationship',
                    'emergency_contact_phone' => 'Emergency contact phone',
                    'emergency_contact_email' => 'Emergency contact email',
                    'department_id' => 'Department assignment',
                    'role_id' => 'Employee role',
                    'reporting_manager_id' => 'Direct supervisor',
                    'employee_number' => 'Employee ID number',
                    'notes' => 'Additional notes',
                    'profile_photo_path' => 'Photo file path',
                    'next_of_kin_name' => 'Next of kin name',
                    'place_of_birth' => 'Birth place',
                    'next_of_kin_phone' => 'Next of kin phone',
                    'tin_number' => 'Tax identification number',
                    'nida_number' => 'National ID number',
                    'nssf_number' => 'Social security number',
                    'nssf_rate' => 'Social security rate',
                    'nhif_number' => 'Health insurance number',
                    'nhif_rate' => 'Health insurance rate',
                    'workers_compensation' => 'Workers comp amount',
                    'life_insurance' => 'Life insurance amount',
                    'tax_category' => 'Tax bracket',
                    'paye_rate' => 'PAYE tax rate',
                    'tax_paid' => 'Tax amount paid',
                    'pension' => 'Pension contribution',
                    'nhif' => 'Health insurance amount',
                    'education_level' => 'Education level',
                    'approval_stage' => 'Onboarding approval stage',
                    'user_id' => 'System user account',
                    'client_id' => 'Member account if applicable',
                    'physical_address' => 'Full physical address'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'branches' => 'Branch (via branch_id)',
                        'users' => 'User account (via user_id)',
                        'clients' => 'Member account (via client_id)',
                        'departments' => 'Department (via department_id)',
                        'employee_roles' => 'Role (via role_id)'
                    ],
                    'has_many' => [
                        'pay_rolls' => 'Payroll records',
                        'employee_requests' => 'Leave/material requests'
                    ]
                ],
                'business_rules' => [
                    'Employee number must be unique',
                    'TIN number must be valid',
                    'Salary cannot be below minimum wage',
                    'Reporting manager must be active employee',
                    'Statutory deductions automatically calculated'
                ]
            ],

            'pay_rolls' => [
                'description' => 'Payroll processing and salary payments',
                'model' => 'PayRoll',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employee_id' => 'Employee reference',
                    'pay_period_start' => 'Period start date',
                    'pay_period_end' => 'Period end date',
                    'gross_salary' => 'Gross salary amount',
                    'hours_worked' => 'Regular hours worked',
                    'overtime_hours' => 'Overtime hours worked',
                    'tax_deductions' => 'Income tax deducted',
                    'social_security' => 'Social security deduction',
                    'medicare' => 'Medicare deduction',
                    'retirement_contributions' => 'Pension contributions',
                    'health_insurance' => 'Health insurance deduction',
                    'other_deductions' => 'Other deductions',
                    'total_deductions' => 'Sum of all deductions (calculated)',
                    'net_salary' => 'Net pay (calculated)',
                    'payment_method' => 'Payment method',
                    'payment_date' => 'Payment date'
                ],
                'relationships' => [
                    'belongs_to' => 'employees (via employee_id)'
                ],
                'business_rules' => [
                    'Total deductions auto-calculated',
                    'Net salary = Gross - Total deductions',
                    'Cannot process for terminated employees',
                    'One payroll per period per employee'
                ]
            ],

            'employee_requests' => [
                'description' => 'Employee leave requests, material requests, and other HR requests',
                'model' => 'EmployeeRequest',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employee_id' => 'Requesting employee',
                    'type' => 'Request type (leave/materials/resignation/etc)',
                    'department' => 'Target department',
                    'subject' => 'Request subject/title',
                    'details' => 'Request details',
                    'status' => 'Status (PENDING/APPROVED/REJECTED)',
                    'approver_id' => 'Approving user',
                    'approved_at' => 'Approval timestamp',
                    'rejection_reason' => 'Reason if rejected',
                    'attachments' => 'Supporting documents (JSON)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'employees' => 'Employee (via employee_id)',
                        'users' => 'Approver (via approver_id)'
                    ]
                ],
                'business_rules' => [
                    'Leave requests check balance',
                    'Material requests require budget',
                    'Resignation requires notice period',
                    'Approval workflow based on type'
                ]
            ],

            'job_postings' => [
                'description' => 'Job vacancies and recruitment management',
                'model' => 'JobPosting',
                'key_fields' => [
                    'id' => 'Primary key',
                    'job_title' => 'Position title',
                    'department' => 'Department',
                    'location' => 'Job location',
                    'job_type' => 'Employment type',
                    'description' => 'Job description',
                    'requirements' => 'Job requirements',
                    'salary' => 'Salary range',
                    'status' => 'Posting status (open/closed/draft)'
                ],
                'relationships' => [
                    'has_many' => 'job_applications'
                ],
                'business_rules' => [
                    'Only open positions accept applications',
                    'Salary must be within approved budget',
                    'Requires department head approval'
                ]
            ],

            // ========= PROCUREMENT MANAGEMENT =========
            'vendors' => [
                'description' => 'Supplier and vendor management',
                'model' => 'Vendor',
                'key_fields' => [
                    'id' => 'Primary key',
                    'organization_name' => 'Vendor/supplier name (40 chars)',
                    'organization_tin_number' => 'Tax identification number (40 chars)',
                    'status' => 'Vendor status (ACTIVE/PENDING/DELETED)',
                    'email' => 'Contact email (60 chars)',
                    'organization_license_number' => 'Business license number (30 chars)',
                    'organization_description' => 'Business description'
                ],
                'relationships' => [
                    'has_many' => [
                        'purchases' => 'Purchase orders',
                        'contract_managements' => 'Vendor contracts',
                        'tenders' => 'Tender submissions'
                    ]
                ],
                'business_rules' => [
                    'TIN number must be valid',
                    'License must be current',
                    'Vendor evaluation required annually',
                    'Soft delete using status field'
                ]
            ],

            'purchases' => [
                'description' => 'Purchase requisitions and procurement orders',
                'model' => 'Purchase',
                'key_fields' => [
                    'id' => 'Primary key',
                    'requisition_description' => 'Purchase description',
                    'status' => 'Purchase status (PENDING/APPROVED/REJECTED/COMPLETED)',
                    'invoice' => 'Invoice reference number',
                    'employeeId' => 'Requesting employee',
                    'vendorId' => 'Selected supplier',
                    'branchId' => 'Requesting branch',
                    'quantity' => 'Quantity ordered'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'employees' => 'Requester (via employeeId)',
                        'vendors' => 'Supplier (via vendorId)',
                        'branches' => 'Branch (via branchId)'
                    ],
                    'has_many' => [
                        'purchase_items' => 'Line items',
                        'approvals' => 'Approval workflow'
                    ]
                ],
                'business_rules' => [
                    'Requires approval based on amount',
                    'Three quotes for amounts above threshold',
                    'Budget availability check',
                    'Vendor must be approved',
                    'Track from requisition to delivery'
                ]
            ],

            'contract_managements' => [
                'description' => 'Vendor contracts and agreements management',
                'model' => 'ContractManagement',
                'key_fields' => [
                    'id' => 'Primary key',
                    'contract_name' => 'Contract title (40 chars)',
                    'contract_description' => 'Contract details (255 chars)',
                    'contract_file_path' => 'Document storage path',
                    'startDate' => 'Contract start date',
                    'endDate' => 'Contract end date',
                    'vendorId' => 'Vendor/supplier ID',
                    'status' => 'Contract status (ACTIVE/PENDING/EXPIRED/CANCELLED)'
                ],
                'relationships' => [
                    'belongs_to' => 'vendors (via vendorId)',
                    'has_many' => [
                        'contract_renewals' => 'Renewal history',
                        'contract_amendments' => 'Contract changes'
                    ]
                ],
                'business_rules' => [
                    'Alert before expiry',
                    'Require renewal approval',
                    'Track contract value',
                    'Monitor vendor performance',
                    'Store signed documents'
                ]
            ],

            'tenders' => [
                'description' => 'Tender and bidding process management',
                'model' => 'Tender',
                'key_fields' => [
                    'id' => 'Primary key',
                    'tender_number' => 'Unique tender reference',
                    'tender_name' => 'Tender title (30 chars)',
                    'tender_description' => 'Tender details',
                    'status' => 'Tender status (OPEN/CLOSED/AWARDED/CANCELLED)'
                ],
                'relationships' => [
                    'has_many' => [
                        'tender_submissions' => 'Vendor bids',
                        'tender_evaluations' => 'Bid evaluations'
                    ]
                ],
                'business_rules' => [
                    'Publish tender notices',
                    'Set submission deadlines',
                    'Evaluate bids systematically',
                    'Award to best bidder',
                    'Document selection process'
                ]
            ],

            'inventories' => [
                'description' => 'Inventory and stock management',
                'model' => 'Inventory',
                'key_fields' => [
                    'id' => 'Primary key',
                    'item_name' => 'Item/product name',
                    'item_amount' => 'Quantity in stock',
                    'item_expiration_date' => 'Expiry date',
                    'status' => 'Item status (AVAILABLE/OUT_OF_STOCK/EXPIRED)',
                    'item_description' => 'Item details'
                ],
                'relationships' => [
                    'has_many' => [
                        'inventory_movements' => 'Stock in/out records',
                        'purchase_orders' => 'Replenishment orders'
                    ]
                ],
                'business_rules' => [
                    'Track stock levels',
                    'Alert on low stock',
                    'Monitor expiry dates',
                    'Calculate reorder points',
                    'FIFO/LIFO valuation'
                ]
            ],

            'assets_list' => [
                'description' => 'Fixed assets register and management',
                'model' => 'AssetsList',
                'key_fields' => [
                    'id' => 'Primary key',
                    'name' => 'Asset name',
                    'type' => 'Asset category',
                    'value' => 'Asset value',
                    'acquisition_date' => 'Purchase date',
                    'source' => 'Acquisition source'
                ],
                'relationships' => [
                    'has_many' => [
                        'asset_depreciation' => 'Depreciation schedule',
                        'asset_maintenance' => 'Maintenance records',
                        'asset_assignments' => 'User assignments'
                    ]
                ],
                'business_rules' => [
                    'Calculate depreciation',
                    'Schedule maintenance',
                    'Track asset location',
                    'Monitor asset condition',
                    'Disposal management'
                ]
            ],
            
            // ========= PAYMENT MANAGEMENT SYSTEM =========
            'payments' => [
                'description' => 'Payment processing and tracking for bills, transfers, and services',
                'model' => 'Payment',
                'key_fields' => [
                    'id' => 'Primary key',
                    'bill_id' => 'Related bill ID',
                    'payment_ref' => 'Payment reference (unique)',
                    'transaction_reference' => 'Transaction reference (unique)',
                    'control_number' => 'Control/reference number',
                    'amount' => 'Payment amount',
                    'currency' => 'Currency code (default TZS)',
                    'payment_channel' => 'Payment channel used',
                    'payer_name' => 'Payer full name',
                    'payer_msisdn' => 'Payer phone number',
                    'payer_email' => 'Payer email',
                    'payer_tin' => 'Tax identification number',
                    'payer_nin' => 'National ID number',
                    'paid_at' => 'Payment timestamp',
                    'received_at' => 'Receipt timestamp',
                    'status' => 'Payment status (Pending/Confirmed/Reversed)',
                    'raw_payload' => 'Raw request data (JSON)',
                    'response_data' => 'Response data (JSON)'
                ],
                'relationships' => [
                    'belongs_to' => 'bills (via bill_id)'
                ],
                'business_rules' => [
                    'Unique payment and transaction references',
                    'Status tracking through lifecycle',
                    'Audit trail of all payment attempts',
                    'Support for multiple payment channels'
                ]
            ],

            'payment_notifications' => [
                'description' => 'Payment notification and callback processing',
                'model' => 'PaymentNotification',
                'key_fields' => [
                    'id' => 'Primary key',
                    'control_number' => 'Control number (13 chars)',
                    'received_at' => 'Notification receipt time',
                    'raw_payload' => 'Raw notification data (JSON)',
                    'status' => 'Processing status (Pending/Processed/Failed)',
                    'processed_at' => 'Processing timestamp'
                ],
                'relationships' => [
                    'has_many' => 'payments (via control_number)'
                ],
                'business_rules' => [
                    'Process callbacks asynchronously',
                    'Retry failed notifications',
                    'Validate notification signatures'
                ]
            ],

            'payment_methods' => [
                'description' => 'Available payment methods configuration',
                'model' => 'PaymentMethod',
                'key_fields' => [
                    'id' => 'Primary key',
                    'name' => 'Payment method name'
                ],
                'business_rules' => [
                    'Define available payment options',
                    'Configure method-specific settings'
                ]
            ],

            'payment_links' => [
                'description' => 'Payment link generation for online payments',
                'model' => 'PaymentLink',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Related loan (optional)',
                    'client_number' => 'Member/client number',
                    'link_id' => 'Unique link identifier',
                    'short_code' => 'Short code for easy reference',
                    'payment_url' => 'Payment URL',
                    'qr_code_data' => 'QR code data',
                    'total_amount' => 'Amount to be paid',
                    'currency' => 'Currency (default TZS)',
                    'description' => 'Payment description',
                    'expires_at' => 'Link expiration time',
                    'response_data' => 'Provider response (JSON)',
                    'status' => 'Link status (ACTIVE/EXPIRED/USED/CANCELLED)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'Loan (via loan_id)',
                        'clients' => 'Member (via client_number)'
                    ]
                ],
                'business_rules' => [
                    'Links expire after specified time',
                    'Generate QR codes for mobile payments',
                    'Track link usage and conversion',
                    'Support partial payments if configured'
                ]
            ],

            'bills' => [
                'description' => 'Bill generation and management for services',
                'model' => 'Bill',
                'key_fields' => [
                    'id' => 'Primary key',
                    'service_id' => 'Related service',
                    'control_number' => 'Bill control number',
                    'amount_due' => 'Total amount due',
                    'amount_paid' => 'Amount already paid',
                    'is_mandatory' => 'Whether payment is mandatory',
                    'is_recurring' => 'Whether bill recurs',
                    'due_date' => 'Payment due date',
                    'member_id' => 'Member identifier',
                    'client_number' => 'Client/member number',
                    'created_by' => 'User who created bill',
                    'payment_mode' => 'Payment mode (default 2)',
                    'status' => 'Bill status (PENDING/PAID/OVERDUE)',
                    'credit_account_number' => 'Account to credit',
                    'debit_account_number' => 'Account to debit',
                    'payment_link' => 'Associated payment link',
                    'payment_link_id' => 'Payment link identifier',
                    'payment_link_generated_at' => 'Link generation time',
                    'payment_link_items' => 'Payment items (JSON)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'services' => 'Service (via service_id)',
                        'users' => 'Creator (via created_by)',
                        'clients' => 'Member (via client_number)'
                    ],
                    'has_many' => [
                        'payments' => 'Payment records',
                        'payment_links' => 'Generated payment links'
                    ]
                ],
                'business_rules' => [
                    'Generate control numbers for tracking',
                    'Track partial payments',
                    'Auto-generate recurring bills',
                    'Send payment reminders before due date'
                ]
            ],

            'bank_transfers' => [
                'description' => 'Bank transfer management for vault cash movements',
                'model' => 'BankTransfer',
                'key_fields' => [
                    'id' => 'Primary key',
                    'vault_id' => 'Source vault',
                    'amount' => 'Transfer amount',
                    'reason' => 'Transfer reason (over_limit/manual/scheduled)',
                    'status' => 'Transfer status (pending/completed/failed)',
                    'reference' => 'Unique transfer reference',
                    'initiated_by' => 'Initiating user',
                    'approved_by' => 'Approving user',
                    'bank_response' => 'Bank response message',
                    'processed_at' => 'Processing timestamp'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'vaults' => 'Vault (via vault_id)',
                        'users' => 'Initiator and approver'
                    ]
                ],
                'business_rules' => [
                    'Dual approval for large amounts',
                    'Auto-transfer when vault over limit',
                    'Scheduled transfers for cash management',
                    'Audit trail of all transfers'
                ]
            ],

            'internal_transfers' => [
                'description' => 'Internal fund transfers between GL accounts',
                'model' => 'InternalTransfer',
                'key_fields' => [
                    'id' => 'Primary key',
                    'transfer_date' => 'Transfer date',
                    'transfer_type' => 'Type (asset_to_asset/asset_to_liability/etc)',
                    'from_account_id' => 'Source GL account',
                    'to_account_id' => 'Destination GL account',
                    'amount' => 'Transfer amount',
                    'narration' => 'Transfer description',
                    'attachment_path' => 'Supporting document',
                    'status' => 'Status (draft/posted)',
                    'created_by' => 'User who created transfer'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Source and destination accounts',
                        'users' => 'Creator (via created_by)'
                    ]
                ],
                'business_rules' => [
                    'Maintain double-entry balance',
                    'Cannot transfer between incompatible account types',
                    'Require approval for large transfers',
                    'Generate GL entries automatically'
                ]
            ],

            'gepg_transactions' => [
                'description' => 'Government Electronic Payment Gateway transactions',
                'model' => 'GepgTransaction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'control_number' => 'GePG control number',
                    'account_no' => 'Account number',
                    'amount' => 'Transaction amount',
                    'currency' => 'Currency code',
                    'response_code' => 'GePG response code',
                    'response_description' => 'Response description',
                    'payload' => 'Full request/response data (JSON)',
                    'transaction_type' => 'Type (verification/postpaid/prepaid)',
                    'quote_reference' => 'Quote reference number'
                ],
                'relationships' => [
                    'belongs_to' => 'accounts (via account_no)'
                ],
                'business_rules' => [
                    'Verify bill before payment',
                    'Support prepaid and postpaid',
                    'Handle government fee payments',
                    'Reconcile with treasury systems'
                ]
            ],

            // ========= NBC/TIPS INTEGRATION =========
            'nbc_payment_services' => [
                'description' => 'NBC (National Bank of Commerce) and TIPS payment integration',
                'key_features' => [
                    'Bank-to-bank transfers via TIPS',
                    'Bank-to-wallet transfers (M-Pesa, Airtel Money, Azam Pesa)',
                    'Merchant payments',
                    'Bill payments (utilities, government services)',
                    'LUKU token purchases',
                    'Real-time beneficiary verification',
                    'Payment status tracking'
                ],
                'supported_banks' => [
                    'NMIBTZTZ' => 'NMB Bank',
                    'CORUTZTZ' => 'CRDB Bank',
                    'DTKETZTZ' => 'DTB Bank'
                ],
                'supported_wallets' => [
                    'VMCASHIN' => 'M-Pesa',
                    'AMCASHIN' => 'Airtel Money',
                    'APCASHIN' => 'Azam Pesa'
                ],
                'integration_points' => [
                    'NbcLookupService' => 'Beneficiary verification',
                    'NbcPaymentService' => 'Payment processing',
                    'NbcBillsPaymentService' => 'Bill payments',
                    'PaymentProcessorService' => 'Transaction orchestration',
                    'LukuService' => 'Electricity token purchases'
                ],
                'business_rules' => [
                    'Minimum transfer amount: 1000 TZS',
                    'Real-time beneficiary name verification',
                    'Transaction reference tracking',
                    'Async payment processing with callbacks',
                    'Dual approval for large transfers'
                ]
            ],
            
            // ========= INVESTMENT MANAGEMENT SYSTEM =========
            'investments_list' => [
                'description' => 'Comprehensive investment portfolio management',
                'model' => 'Investment',
                'table' => 'investments_list',
                'key_fields' => [
                    'id' => 'Primary key',
                    'investment_type' => 'Type of investment',
                    'principal_amount' => 'Initial investment amount',
                    'investment_date' => 'Date of investment',
                    // Stock investments
                    'number_of_shares' => 'Number of shares purchased',
                    'share_price' => 'Price per share',
                    'brokerage_fees' => 'Transaction fees',
                    'dividend_rate' => 'Dividend percentage',
                    'sale_price' => 'Selling price per share',
                    // Fixed deposits/Term investments
                    'interest_rate' => 'Interest rate percentage',
                    'tenure' => 'Investment period',
                    'maturity_date' => 'Maturity date',
                    'penalty' => 'Early withdrawal penalty',
                    // Bonds
                    'bond_type' => 'Type of bond',
                    'coupon_rate' => 'Bond coupon rate',
                    'bond_yield' => 'Bond yield percentage',
                    // Mutual funds
                    'fund_name' => 'Name of mutual fund',
                    'fund_manager' => 'Fund management company',
                    'expense_ratio' => 'Fund expense ratio',
                    'nav' => 'Net Asset Value',
                    'units_purchased' => 'Number of units',
                    // Real estate
                    'property_value' => 'Property valuation',
                    'location' => 'Property location',
                    'purchase_date' => 'Property purchase date',
                    'annual_property_taxes' => 'Annual tax amount',
                    'rental_income' => 'Monthly rental income',
                    'maintenance_costs' => 'Maintenance expenses',
                    // General fields
                    'description' => 'Investment description',
                    'interest_dividend_rate' => 'Return rate',
                    'status' => 'Investment status',
                    'cash_account' => 'Source cash account',
                    'investment_account' => 'Investment GL account'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'investment_types' => 'Investment type configuration',
                        'accounts' => 'GL accounts (cash and investment)'
                    ],
                    'has_many' => [
                        'investment_returns' => 'Return on investment records',
                        'investment_valuations' => 'Periodic valuations'
                    ]
                ],
                'business_rules' => [
                    'Track multiple investment types in single table',
                    'Calculate ROI and yields',
                    'Monitor maturity dates',
                    'Generate investment reports',
                    'Approval workflow for new investments'
                ]
            ],

            'investment_types' => [
                'description' => 'Investment type configuration and categories',
                'model' => 'InvestmentType',
                'key_fields' => [
                    'id' => 'Primary key',
                    'investment_type' => 'Type code/identifier',
                    'investment_name' => 'Investment category name',
                    'description' => 'Type description'
                ],
                'relationships' => [
                    'has_many' => 'investments_list'
                ],
                'investment_categories' => [
                    'STOCKS' => 'Equity investments',
                    'BONDS' => 'Fixed income securities',
                    'MUTUAL_FUNDS' => 'Collective investment schemes',
                    'REAL_ESTATE' => 'Property investments',
                    'FIXED_DEPOSITS' => 'Term deposits',
                    'TREASURY_BILLS' => 'Government securities',
                    'COMMODITIES' => 'Gold, silver, etc.'
                ],
                'business_rules' => [
                    'Define investment categories',
                    'Set risk profiles per type',
                    'Configure accounting treatment'
                ]
            ],

            'project' => [
                'description' => 'Investment projects and procurement contracts',
                'model' => 'Project',
                'key_fields' => [
                    'id' => 'Primary key',
                    'tender_no' => 'Tender reference number',
                    'procuring_entity' => 'Client/entity name',
                    'supplier_name' => 'Contractor/supplier',
                    'award_date' => 'Contract award date',
                    'award_amount' => 'Contract value',
                    'lot_name' => 'Project lot/section',
                    'expected_end_date' => 'Completion date',
                    'project_summary' => 'Project description',
                    'status' => 'Status (ONPROGRESS/COMPLETED/CANCELLED)'
                ],
                'relationships' => [
                    'has_many' => [
                        'project_milestones' => 'Project phases',
                        'project_payments' => 'Payment schedule',
                        'project_documents' => 'Supporting documents'
                    ]
                ],
                'business_rules' => [
                    'Track project lifecycle',
                    'Monitor contract performance',
                    'Calculate project profitability',
                    'Generate progress reports'
                ]
            ],

            'dividends' => [
                'description' => 'Dividend payments to members from investments',
                'model' => 'Dividend',
                'key_fields' => [
                    'id' => 'Primary key',
                    'member_id' => 'Member receiving dividend',
                    'year' => 'Dividend year',
                    'rate' => 'Dividend rate percentage',
                    'amount' => 'Dividend amount',
                    'paid_at' => 'Payment date',
                    'payment_mode' => 'Payment method (bank/cash/shares)',
                    'status' => 'Payment status (pending/paid/cancelled)',
                    'narration' => 'Payment notes'
                ],
                'relationships' => [
                    'belongs_to' => 'clients (members via member_id)'
                ],
                'business_rules' => [
                    'Calculate based on shareholding',
                    'Apply withholding tax',
                    'Track payment history',
                    'Generate dividend statements'
                ]
            ],
            
            'branches' => [
                'description' => 'Physical SACCO branch locations and service centers',
                'model' => 'BranchesModel',
                'key_fields' => [
                    'id' => 'Primary key',
                    'branch_number' => 'Unique branch identifier',
                    'name' => 'Branch name',
                    'region' => 'Geographic region',
                    'wilaya' => 'State/Province',
                    'status' => 'Branch status (ACTIVE, PENDING, BLOCKED)',
                    'branch_type' => 'Type (MAIN, SUB, MOBILE)',
                    'branch_manager' => 'Manager user ID',
                    'email' => 'Branch email',
                    'phone_number' => 'Contact number',
                    'address' => 'Physical address',
                    'opening_date' => 'Date branch opened',
                    'operating_hours' => 'Business hours',
                    'services_offered' => 'JSON array of services',
                    'cit_provider_id' => 'Cash in Transit provider',
                    'vault_account' => 'Vault GL account',
                    'till_account' => 'Till GL account',
                    'petty_cash_account' => 'Petty cash GL account'
                ],
                'relationships' => [
                    'has_many' => [
                        'clients' => 'Members registered at this branch (via branch_id)',
                        'users' => 'Staff assigned to this branch',
                        'loans' => 'Loans originated from this branch',
                        'accounts' => 'Accounts opened at this branch',
                        'transactions' => 'Transactions processed at this branch',
                        'tills' => 'Cash tills at this branch',
                        'vaults' => 'Cash vaults at this branch',
                        'approvals' => 'Pending approvals for this branch',
                        'bank_accounts' => 'Bank accounts linked to branch'
                    ],
                    'belongs_to' => [
                        'cash_in_transit_provider' => 'CIT service provider (via cit_provider_id)',
                        'manager' => 'Branch manager user (via branch_manager)',
                        'institution' => 'Parent SACCO institution'
                    ]
                ],
                'data_flow' => 'Institution → Branches → Tills/Vaults → Transactions → GL Accounts',
                'livewire_component' => 'App\\Http\\Livewire\\Branches\\Branches'
            ],
            
            'users' => [
                'description' => 'System users (staff/administrators) - NOT members',
                'key_fields' => ['id', 'email', 'branch_id', 'role_id'],
                'relationships' => [
                    'belongs_to' => [
                        'branches' => 'Staff assigned to branch',
                        'roles' => 'User role and permissions'
                    ],
                    'has_many' => [
                        'audit_logs' => 'User actions tracking',
                        'approvals' => 'Approvals made by user',
                        'user_permissions' => 'Specific permissions',
                        'login_history' => 'Login tracking'
                    ]
                ],
                'data_flow' => 'Users → Permissions → Actions → Audit Logs'
            ],
            
            'transactions' => [
                'description' => 'All financial transactions - the ledger',
                'key_fields' => ['id', 'account_id', 'transaction_type', 'amount', 'reference_number'],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Account involved in transaction',
                        'users' => 'User who processed transaction',
                        'branches' => 'Branch where processed'
                    ],
                    'has_many' => [
                        'transaction_audit_logs' => 'Audit trail',
                        'reconciliations' => 'Bank reconciliation records'
                    ],
                    'has_one' => [
                        'reversal' => 'Reversal transaction if reversed'
                    ]
                ],
                'data_flow' => 'Transaction Entry → Validation → GL Posting → Reports'
            ],
            
            'share_registers' => [
                'description' => 'Comprehensive share ownership and management system',
                'model' => 'ShareRegister',
                'key_fields' => [
                    // Primary Identifiers
                    'id' => 'Primary key',
                    'share_account_number' => 'Unique share account identifier',
                    'member_id' => 'Member owning the shares',
                    'member_number' => 'Member number',
                    'member_name' => 'Member full name',
                    
                    // Share Product Information
                    'product_id' => 'Share product type (references sub_products)',
                    'product_name' => 'Name of share product',
                    'product_type' => 'MANDATORY/VOLUNTARY/PREFERENCE',
                    
                    // Pricing Information
                    'nominal_price' => 'Original price per share at purchase',
                    'current_price' => 'Current market price per share',
                    'total_share_value' => 'Current total value (balance * current_price)',
                    
                    // Share Holdings & Transactions
                    'total_shares_issued' => 'Total shares ever issued',
                    'total_shares_redeemed' => 'Total shares redeemed/sold',
                    'total_shares_transferred_in' => 'Shares received via transfer',
                    'total_shares_transferred_out' => 'Shares sent via transfer',
                    'current_share_balance' => 'Current share balance',
                    
                    // Dividend Information
                    'last_dividend_rate' => 'Last dividend rate applied (%)',
                    'last_dividend_amount' => 'Last dividend amount received',
                    'last_dividend_date' => 'Date of last dividend',
                    'accumulated_dividends' => 'Unpaid dividend balance',
                    'total_paid_dividends' => 'Total dividends paid out',
                    'total_pending_dividends' => 'Dividends awaiting payment',
                    
                    // Linked Accounts
                    'linked_savings_account' => 'Account for share payments',
                    'dividend_payment_account' => 'Account for dividend deposits',
                    
                    // Status & Dates
                    'status' => 'ACTIVE/INACTIVE/FROZEN/CLOSED',
                    'opening_date' => 'Account opening date',
                    'last_activity_date' => 'Last transaction date',
                    'closing_date' => 'Account closure date',
                    
                    // Compliance
                    'is_restricted' => 'Transfer restriction flag',
                    'restriction_notes' => 'Restriction details',
                    'requires_approval' => 'Approval needed for transfers'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Share owner (via member_id)',
                        'sub_products' => 'Share product type (via product_id)',
                        'branches' => 'Branch (via branch_id)',
                        'institutions' => 'Institution (via institution_id)'
                    ],
                    'has_many' => [
                        'share_transactions' => 'All share transactions',
                        'issued_shares' => 'Share issuance records',
                        'dividends' => 'Dividend payments',
                        'share_transfers' => 'Transfer history',
                        'share_withdrawals' => 'Redemption/withdrawal history'
                    ]
                ],
                'data_flow' => 'Member Application → Share Purchase → Holdings → Dividends → Redemption/Transfer',
                'livewire_component' => 'App\\Http\\Livewire\\Shares\\Shares'
            ],
            
            'issued_shares' => [
                'description' => 'Individual share issuance transactions',
                'model' => 'IssuedShares',
                'key_fields' => [
                    'id' => 'Primary key',
                    'reference_number' => 'Transaction reference',
                    'share_id' => 'Share register ID',
                    'client_number' => 'Member identifier',
                    'account_number' => 'Share account number',
                    'number_of_shares' => 'Quantity issued',
                    'nominal_price' => 'Price per share',
                    'total_value' => 'Total transaction value',
                    'linked_savings_account' => 'Payment source account',
                    'linked_share_account' => 'Destination share account',
                    'status' => 'Transaction status',
                    'branch' => 'Issuing branch',
                    'created_by' => 'Issuing officer'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'share_registers' => 'Parent share account (via share_id)',
                        'clients' => 'Member (via client_number)',
                        'users' => 'Issuing officer (via created_by)',
                        'branches' => 'Branch (via branch)'
                    ]
                ],
                'data_flow' => 'Purchase Request → Payment → Share Issuance → Register Update'
            ],
            
            'dividends' => [
                'description' => 'Dividend declarations and payments',
                'model' => 'DividendModel',
                'key_fields' => [
                    'id' => 'Primary key',
                    'member_id' => 'Recipient member',
                    'year' => 'Dividend year',
                    'rate' => 'Dividend rate (%)',
                    'amount' => 'Dividend amount',
                    'paid_at' => 'Payment date',
                    'payment_mode' => 'Payment method (bank/cash/shares)',
                    'status' => 'pending/approved/paid/cancelled',
                    'narration' => 'Payment description'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Dividend recipient (via member_id)'
                    ],
                    'has_many' => [
                        'dividend_payments' => 'Individual payment records'
                    ]
                ],
                'data_flow' => 'Profit Calculation → Dividend Declaration → Approval → Payment → Member Account'
            ],
            
            'share_transactions' => [
                'description' => 'All share-related transactions',
                'model' => 'ShareTransaction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'share_id' => 'Share register ID',
                    'transaction_type' => 'PURCHASE/SALE/TRANSFER/DIVIDEND',
                    'quantity' => 'Number of shares',
                    'price_per_share' => 'Transaction price',
                    'total_amount' => 'Total value',
                    'from_member' => 'Source member (for transfers)',
                    'to_member' => 'Destination member (for transfers)',
                    'reference_number' => 'Transaction reference',
                    'status' => 'Transaction status',
                    'approved_by' => 'Approving officer',
                    'approved_at' => 'Approval timestamp'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'share_registers' => 'Share account (via share_id)',
                        'clients' => 'Member involved',
                        'users' => 'Processing/approving officer'
                    ]
                ],
                'data_flow' => 'Transaction Request → Validation → Approval → Execution → Ledger Update'
            ],
            
            'share_withdrawals' => [
                'description' => 'Share redemption and withdrawal records',
                'model' => 'ShareWithdrawal',
                'key_fields' => [
                    'id' => 'Primary key',
                    'member_id' => 'Member withdrawing',
                    'share_account_number' => 'Source share account',
                    'number_of_shares' => 'Shares to withdraw',
                    'withdrawal_amount' => 'Cash value',
                    'reason' => 'Withdrawal reason',
                    'status' => 'pending/approved/processed/rejected',
                    'payment_method' => 'Payment channel',
                    'payment_reference' => 'Payment reference',
                    'processed_by' => 'Processing officer',
                    'processed_at' => 'Processing date'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Withdrawing member (via member_id)',
                        'share_registers' => 'Share account',
                        'users' => 'Processing officer'
                    ]
                ],
                'data_flow' => 'Withdrawal Request → Validation → Approval → Payment → Share Balance Update'
            ],
            
            'share_transfers' => [
                'description' => 'Share transfer between members',
                'model' => 'ShareTransfer',
                'key_fields' => [
                    'id' => 'Primary key',
                    'from_member_id' => 'Transferring member',
                    'to_member_id' => 'Receiving member',
                    'from_account' => 'Source share account',
                    'to_account' => 'Destination share account',
                    'number_of_shares' => 'Shares transferred',
                    'transfer_value' => 'Value at transfer',
                    'transfer_reason' => 'Reason for transfer',
                    'transfer_fee' => 'Transaction fee',
                    'status' => 'pending/approved/completed/rejected',
                    'approved_by' => 'Approving officer',
                    'completed_at' => 'Completion timestamp'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'from_member' => 'Transferring member (clients)',
                        'to_member' => 'Receiving member (clients)',
                        'approver' => 'Approving officer (users)'
                    ]
                ],
                'data_flow' => 'Transfer Request → Validation → Approval → Debit Source → Credit Destination → Confirmation'
            ],
            
            'loan_guarantors' => [
                'description' => 'Links members who guarantee loans',
                'key_fields' => ['id', 'loan_id', 'guarantor_member_id', 'guarantee_amount'],
                'relationships' => [
                    'belongs_to' => [
                        'loans' => 'The guaranteed loan',
                        'clients' => 'The guarantor (who is also a member)'
                    ]
                ],
                'data_flow' => 'Loan → Guarantor (Member) → Risk Assessment'
            ],
            
            'general_ledger' => [
                'description' => 'General ledger entries for double-entry bookkeeping',
                'key_fields' => ['id', 'account_id', 'debit', 'credit', 'transaction_id'],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'GL account',
                        'transactions' => 'Source transaction'
                    ]
                ],
                'data_flow' => 'Transactions → GL Entries → Trial Balance → Financial Statements'
            ],
            
            // ========= TELLER MANAGEMENT SYSTEM =========
            'tellers' => [
                'description' => 'Teller user accounts and permissions management',
                'model' => 'Teller',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employee_id' => 'Employee identifier (50 chars)',
                    'status' => 'Teller status (ACTIVE/INACTIVE)',
                    'branch_id' => 'Assigned branch',
                    'max_amount' => 'Maximum transaction amount',
                    'account_id' => 'Linked GL account',
                    'registered_by_id' => 'User who registered teller',
                    'progress_status' => 'Workflow status',
                    'teller_name' => 'Teller display name (30 chars)',
                    'user_id' => 'Linked user account',
                    'till_id' => 'Assigned till',
                    'transaction_limit' => 'Transaction limit (default 100000)',
                    'permissions' => 'JSON permissions array',
                    'last_login_at' => 'Last login timestamp',
                    'assigned_at' => 'Till assignment time',
                    'assigned_by' => 'User who assigned till'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'users' => 'User account (via user_id)',
                        'tills' => 'Assigned till (via till_id)',
                        'branches' => 'Branch (via branch_id)',
                        'accounts' => 'GL account (via account_id)'
                    ],
                    'has_many' => [
                        'till_transactions' => 'Transactions processed',
                        'teller_end_of_day_positions' => 'EOD positions'
                    ]
                ],
                'business_rules' => [
                    'Transaction limits enforced',
                    'Dual approval for large amounts',
                    'Daily reconciliation required',
                    'Till assignment tracking'
                ]
            ],
            
            'tills' => [
                'description' => 'Cash tills for teller operations at branches',
                'model' => 'Till',
                'key_fields' => [
                    'id' => 'Primary key',
                    'name' => 'Till name (100 chars)',
                    'till_number' => 'Unique till identifier (50 chars)',
                    'branch_id' => 'Branch where till is located',
                    'current_balance' => 'Current cash balance',
                    'opening_balance' => 'Opening balance',
                    'maximum_limit' => 'Max balance limit (default 500000)',
                    'minimum_limit' => 'Min balance limit (default 10000)',
                    'status' => 'Till status (open/closed)',
                    'opened_at' => 'Till opening timestamp',
                    'closed_at' => 'Till closing timestamp',
                    'opened_by' => 'User who opened till',
                    'closed_by' => 'User who closed till',
                    'assigned_to' => 'Assigned user',
                    'denomination_breakdown' => 'JSON cash denominations',
                    'requires_supervisor_approval' => 'Supervisor approval flag',
                    'till_account_number' => 'GL account number (50 chars)',
                    'assigned_user_id' => 'Current assigned user',
                    'assigned_at' => 'Assignment timestamp',
                    'code' => 'Till code (20 chars)',
                    'variance' => 'Cash variance amount',
                    'variance_explanation' => 'Variance notes',
                    'closing_balance' => 'Closing balance'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'branches' => 'Branch location (via branch_id)',
                        'users' => 'Multiple (opened_by, closed_by, assigned_to, assigned_user_id)'
                    ],
                    'has_many' => [
                        'tellers' => 'Assigned tellers',
                        'till_transactions' => 'Cash transactions through this till',
                        'till_reconciliations' => 'Daily reconciliation records'
                    ]
                ],
                'business_rules' => [
                    'Balance limits monitored',
                    'Automatic vault transfer when over limit',
                    'Daily reconciliation required',
                    'Variance explanation required',
                    'Denomination tracking'
                ],
                'data_flow' => 'Till → Cash Transactions → End of Day → Vault Transfer'
            ],
            
            'vaults' => [
                'description' => 'Strong room/vault management for secure cash storage',
                'model' => 'Vault',
                'key_fields' => [
                    'id' => 'Primary key',
                    'name' => 'Vault name',
                    'code' => 'Vault code (100 chars)',
                    'branch_id' => 'Branch where vault is located',
                    'current_balance' => 'Current cash balance',
                    'limit' => 'Maximum capacity',
                    'warning_threshold' => 'Warning percentage (default 80)',
                    'bank_name' => 'Associated bank',
                    'bank_account_number' => 'Bank account for transfers',
                    'internal_account_number' => 'Internal GL account',
                    'auto_bank_transfer' => 'Auto-transfer flag',
                    'requires_dual_approval' => 'Dual approval flag',
                    'send_alerts' => 'Alert notification flag',
                    'status' => 'Vault status (active/inactive)',
                    'parent_account' => 'Parent GL account'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'branches' => 'Branch location (via branch_id)',
                        'accounts' => 'GL account (via internal_account_number)'
                    ],
                    'has_many' => [
                        'bank_transfers' => 'Bank transfer records',
                        'vault_transactions' => 'Cash movements in/out',
                        'strongroom_ledgers' => 'Ledger entries'
                    ]
                ],
                'business_rules' => [
                    'Auto-transfer when over limit',
                    'Dual approval for large withdrawals',
                    'Alert when threshold reached',
                    'Daily reconciliation required',
                    'Audit trail maintained'
                ],
                'data_flow' => 'Tills → End of Day → Vault → CIT Provider → Bank'
            ],
            
            'teller_end_of_day_positions' => [
                'description' => 'Daily teller position reconciliation records',
                'model' => 'TellerEndOfDayPosition',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employee_id' => 'Teller employee ID (100 chars)',
                    'institution_id' => 'Institution ID (20 chars)',
                    'branch_id' => 'Branch ID (20 chars)',
                    'til_number' => 'Till number (100 chars)',
                    'til_account' => 'Till GL account (100 chars)',
                    'til_balance' => 'System till balance',
                    'tiller_cash_at_hand' => 'Physical cash count',
                    'business_date' => 'Business date',
                    'message' => 'Reconciliation notes (250 chars)',
                    'status' => 'EOD status (BALANCED/VARIANCE)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'tellers' => 'Teller (via employee_id)',
                        'branches' => 'Branch (via branch_id)',
                        'tills' => 'Till (via til_number)'
                    ]
                ],
                'business_rules' => [
                    'Daily reconciliation mandatory',
                    'Variance explanation required',
                    'Supervisor approval for variances',
                    'Cash must be transferred to vault',
                    'System balance vs physical count'
                ]
            ],
            
            'till_transactions' => [
                'description' => 'Individual cash transactions through tills',
                'model' => 'TillTransaction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'reference' => 'Unique reference (100 chars)',
                    'till_id' => 'Till used',
                    'teller_id' => 'Teller who processed',
                    'client_id' => 'Member involved',
                    'type' => 'Transaction type (deposit/withdrawal/transfer/opening_balance/closing_balance/adjustment)',
                    'transaction_type' => 'Balance impact (cash_in/cash_out/neutral)',
                    'amount' => 'Transaction amount',
                    'balance_before' => 'Till balance before',
                    'balance_after' => 'Till balance after',
                    'account_number' => 'Related member account (50 chars)',
                    'description' => 'Transaction description',
                    'denomination_breakdown' => 'JSON cash denominations',
                    'receipt_number' => 'Receipt number (100 chars)',
                    'status' => 'Status (pending/completed/reversed/cancelled)',
                    'processed_at' => 'Processing timestamp',
                    'reversed_by' => 'User who reversed',
                    'reversed_at' => 'Reversal timestamp',
                    'reversal_reason' => 'Reason for reversal'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'tills' => 'Till used (via till_id)',
                        'tellers' => 'Teller (via teller_id)',
                        'clients' => 'Member (via client_id)',
                        'users' => 'Reversal user (via reversed_by)'
                    ]
                ],
                'business_rules' => [
                    'Sequential reference numbers',
                    'Balance tracking maintained',
                    'Reversal requires approval',
                    'Denomination breakdown for cash',
                    'Receipt generation required'
                ]
            ],
            
            'till_reconciliations' => [
                'description' => 'Till reconciliation records for cash balancing',
                'model' => 'TillReconciliation',
                'key_fields' => [
                    'id' => 'Primary key',
                    'till_id' => 'Till being reconciled',
                    'teller_id' => 'Teller performing reconciliation',
                    'system_balance' => 'System calculated balance',
                    'physical_cash' => 'Physical cash count',
                    'variance' => 'Difference amount',
                    'variance_explanation' => 'Explanation for variance',
                    'reconciliation_date' => 'Date of reconciliation',
                    'approved_by' => 'Supervisor who approved',
                    'status' => 'Reconciliation status'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'tills' => 'Till (via till_id)',
                        'tellers' => 'Teller (via teller_id)',
                        'users' => 'Approver (via approved_by)'
                    ]
                ],
                'business_rules' => [
                    'Daily reconciliation required',
                    'Variance must be explained',
                    'Supervisor approval for variances',
                    'System vs physical comparison'
                ]
            ],
            
            'cash_in_transit_providers' => [
                'description' => 'Cash in Transit service providers for secure cash transportation',
                'model' => 'CashInTransitProvider',
                'key_fields' => [
                    'id' => 'Primary key',
                    'provider_name' => 'CIT company name',
                    'contact_person' => 'Primary contact',
                    'phone' => 'Contact phone',
                    'email' => 'Contact email',
                    'contract_number' => 'Service contract number',
                    'service_charge_rate' => 'Service fee rate',
                    'status' => 'Provider status'
                ],
                'relationships' => [
                    'has_many' => [
                        'branches' => 'Branches served by this provider',
                        'cit_transactions' => 'Cash pickup/delivery records'
                    ]
                ],
                'data_flow' => 'Vault → CIT Request → Pickup → Transport → Bank Deposit'
            ],
            
            'till_transactions' => [
                'description' => 'Individual cash transactions through tills',
                'key_fields' => [
                    'id' => 'Primary key',
                    'till_id' => 'Till used',
                    'transaction_type' => 'Type (DEPOSIT, WITHDRAWAL, etc)',
                    'amount' => 'Transaction amount',
                    'reference_number' => 'Transaction reference',
                    'client_number' => 'Member involved',
                    'teller_id' => 'Teller who processed'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'tills' => 'Till used (via till_id)',
                        'clients' => 'Member (via client_number)',
                        'users' => 'Teller (via teller_id)',
                        'transactions' => 'Main transaction record'
                    ]
                ],
                'data_flow' => 'Member Request → Till Transaction → GL Posting → Receipt'
            ],
            
            // ========= BANK RECONCILIATION SYSTEM =========
            'analysis_sessions' => [
                'description' => 'Bank statement analysis and reconciliation sessions',
                'model' => 'AnalysisSession',
                'key_fields' => [
                    'id' => 'Primary key',
                    'account_name' => 'Bank account name',
                    'account_number' => 'Bank account number',
                    'statement_period' => 'Statement date range',
                    'bank' => 'Bank name (I&M, CRDB, NMB, ABBSA)',
                    'currency' => 'Currency code',
                    'opening_balance' => 'Opening balance',
                    'closing_balance' => 'Closing balance',
                    'total_transactions' => 'Number of transactions',
                    'status' => 'Session status (processing/completed)',
                    'notes' => 'Session notes',
                    'metadata' => 'Additional data (JSON)'
                ],
                'relationships' => [
                    'has_many' => 'bank_transactions (session transactions)'
                ],
                'calculated_fields' => [
                    'reconciliation_summary' => 'Total, reconciled, matched, unreconciled counts and rate'
                ],
                'business_rules' => [
                    'PDF bank statements parsed automatically',
                    'Supports multiple bank formats',
                    'Tracks reconciliation progress',
                    'Maintains audit trail'
                ]
            ],
            
            'bank_transactions' => [
                'description' => 'Bank statement transactions for reconciliation',
                'model' => 'BankTransaction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'session_id' => 'Analysis session ID',
                    'transaction_date' => 'Transaction date',
                    'value_date' => 'Value date',
                    'reference_number' => 'Bank reference',
                    'narration' => 'Transaction description',
                    'withdrawal_amount' => 'Debit amount',
                    'deposit_amount' => 'Credit amount',
                    'balance' => 'Running balance',
                    'matched_transaction_id' => 'Matched system transaction',
                    'reconciliation_status' => 'Status (unreconciled/matched/partial/reconciled)',
                    'match_confidence' => 'Confidence score (0-100)',
                    'reconciliation_notes' => 'Reconciliation notes',
                    'reconciled_at' => 'Reconciliation timestamp',
                    'reconciled_by' => 'User who reconciled',
                    'branch' => 'Bank branch',
                    'transaction_type' => 'Transaction type',
                    'raw_data' => 'Original parsed data (JSON)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'analysis_sessions' => 'Session (via session_id)',
                        'transactions' => 'Matched transaction (via matched_transaction_id)'
                    ]
                ],
                'business_rules' => [
                    'Auto-matching using multiple algorithms',
                    'Manual reconciliation option',
                    'Confidence scoring for matches',
                    'Maintains reconciliation audit trail'
                ]
            ],
            
            'reconciled_transactions' => [
                'description' => 'Successfully reconciled bank and GL transactions',
                'model' => 'ReconciledTransaction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'Institution_Id' => 'Institution ID (20 chars)',
                    'Account_Code' => 'GL account code (10 chars)',
                    'Reference_Number' => 'Transaction reference (80 chars)',
                    'Value_Date' => 'Transaction value date',
                    'Gl_Details' => 'GL transaction details',
                    'Gl_Debit' => 'GL debit amount',
                    'Gl_Credit' => 'GL credit amount',
                    'Bank_Details' => 'Bank transaction details',
                    'Bank_Debit' => 'Bank debit amount',
                    'Bank_Credit' => 'Bank credit amount'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'GL account (via Account_Code)',
                        'institutions' => 'Institution (via Institution_Id)'
                    ]
                ],
                'business_rules' => [
                    'Final reconciled records',
                    'GL and bank amounts must match',
                    'Immutable audit records',
                    'Used for reporting'
                ]
            ],
            
            'reconciliation_staging_table' => [
                'description' => 'Staging area for reconciliation processing',
                'model' => 'ReconciliationStaging',
                'key_fields' => [
                    'id' => 'Primary key',
                    'Reference_Number' => 'Transaction reference (80 chars)',
                    'Account_code' => 'Account code (20 chars)',
                    'Details' => 'Transaction details',
                    'Value_Date' => 'Transaction date',
                    'Debit' => 'Debit amount',
                    'Credit' => 'Credit amount',
                    'Book_Balance' => 'Book balance',
                    'Institution_Id' => 'Institution ID (20 chars)',
                    'Process_Status' => 'Processing status (20 chars)'
                ],
                'relationships' => [
                    'belongs_to' => 'institutions (via Institution_Id)'
                ],
                'business_rules' => [
                    'Temporary holding for processing',
                    'Data validation before reconciliation',
                    'Status tracking through workflow',
                    'Cleared after successful processing'
                ]
            ],
            
            'im_bank_transactions' => [
                'description' => 'I&M Bank specific transaction format',
                'model' => 'IMBankTransaction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'transaction_date' => 'Transaction date',
                    'value_date' => 'Value date',
                    'narration' => 'Transaction description',
                    'withdrawal_amount' => 'Withdrawal amount',
                    'deposit_amount' => 'Deposit amount',
                    'balance' => 'Running balance',
                    'reference_number' => 'Reference number',
                    'status' => 'Processing status'
                ],
                'relationships' => [
                    'has_many' => 'bank_transactions (converted records)'
                ],
                'business_rules' => [
                    'Bank-specific format parsing',
                    'Data normalization to standard format',
                    'Validation of bank-specific fields'
                ]
            ],

            // ========= HR MANAGEMENT SYSTEM =========
            'employees' => [
                'description' => 'Employee records and personal information',
                'model' => 'Employee',
                'key_fields' => [
                    'id' => 'Primary key',
                    'institution_user_id' => 'Institution user ID reference',
                    'first_name' => 'Employee first name',
                    'middle_name' => 'Employee middle name',
                    'last_name' => 'Employee last name',
                    'date_of_birth' => 'Date of birth',
                    'gender' => 'Gender',
                    'marital_status' => 'Marital status',
                    'nationality' => 'Nationality',
                    'address' => 'Physical address',
                    'phone' => 'Phone number',
                    'email' => 'Email address',
                    'job_title' => 'Job title/position',
                    'branch_id' => 'Branch assignment',
                    'department_id' => 'Department assignment',
                    'hire_date' => 'Employment start date',
                    'basic_salary' => 'Basic salary amount',
                    'gross_salary' => 'Gross salary amount',
                    'employee_status' => 'Employment status (active, inactive, terminated)',
                    'employment_type' => 'Employment type (full-time, part-time, contract)',
                    'employee_number' => 'Unique employee number',
                    'nssf_number' => 'NSSF number',
                    'nhif_number' => 'NHIF number',
                    'tin_number' => 'Tax identification number',
                    'nida_number' => 'National ID number'
                ],
                'relationships' => [
                    'belongs_to' => 'departments, branches, users',
                    'has_many' => 'leaves, employee_requests, employee_roles',
                    'has_one' => 'leave_management'
                ],
                'business_rules' => [
                    'Employee number must be unique',
                    'Salary details used for payroll calculations',
                    'Tax and social security numbers for statutory deductions'
                ]
            ],

            'departments' => [
                'description' => 'Organizational departments and hierarchy',
                'model' => 'Department',
                'key_fields' => [
                    'id' => 'Primary key',
                    'department_name' => 'Department name',
                    'department_code' => 'Unique department code',
                    'parent_department_id' => 'Parent department for hierarchy',
                    'description' => 'Department description',
                    'status' => 'Active/inactive status',
                    'level' => 'Hierarchy level (0 for root)',
                    'path' => 'Hierarchical path',
                    'branch_id' => 'Associated branch',
                    'dashboard_type' => 'Dashboard configuration type'
                ],
                'relationships' => [
                    'has_many' => 'employees, sub_departments',
                    'belongs_to' => 'parent_department, branches'
                ],
                'business_rules' => [
                    'Supports hierarchical structure',
                    'Department code must be unique',
                    'Soft deletes supported'
                ]
            ],

            'leaves' => [
                'description' => 'Employee leave requests and tracking',
                'model' => 'Leave',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employee_id' => 'Employee taking leave',
                    'leave_type' => 'Type of leave (annual, sick, maternity, etc.)',
                    'start_date' => 'Leave start date',
                    'end_date' => 'Leave end date',
                    'status' => 'Leave status (pending, approved, rejected)',
                    'reason' => 'Reason for leave',
                    'description' => 'Additional details'
                ],
                'relationships' => [
                    'belongs_to' => 'employees'
                ],
                'business_rules' => [
                    'Status values: pending, approved, rejected',
                    'Leave dates validated against leave balance',
                    'Requires approval workflow'
                ]
            ],

            'leave_management' => [
                'description' => 'Employee leave balance and allocation tracking',
                'model' => 'LeaveManagement',
                'key_fields' => [
                    'id' => 'Primary key',
                    'total_days' => 'Total leave days allocated',
                    'days_acquire' => 'Days acquired/earned',
                    'leave_days_taken' => 'Days already taken',
                    'balance' => 'Remaining leave balance',
                    'employee_number' => 'Employee number reference'
                ],
                'relationships' => [
                    'belongs_to' => 'employees'
                ],
                'business_rules' => [
                    'Balance = total_days - leave_days_taken',
                    'Updated when leave is approved',
                    'Annual reset cycle'
                ]
            ],

            'employee_requests' => [
                'description' => 'Various employee requests (leave, materials, resignation)',
                'model' => 'EmployeeRequest',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employee_id' => 'Requesting employee',
                    'type' => 'Request type (leave, materials, resignation, etc.)',
                    'department' => 'Target department',
                    'subject' => 'Request subject/title',
                    'details' => 'Request details',
                    'status' => 'Status (PENDING, APPROVED, REJECTED)',
                    'approver_id' => 'User who approved/rejected',
                    'approved_at' => 'Approval timestamp',
                    'rejection_reason' => 'Reason if rejected',
                    'attachments' => 'JSON array of attachments'
                ],
                'relationships' => [
                    'belongs_to' => 'employees, users (approver)'
                ],
                'business_rules' => [
                    'Default status is PENDING',
                    'Requires approval workflow',
                    'Supports file attachments'
                ]
            ],

            'employee_roles' => [
                'description' => 'Many-to-many relationship between employees and roles',
                'model' => 'EmployeeRole',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employee_id' => 'Employee reference',
                    'role_id' => 'Role reference'
                ],
                'relationships' => [
                    'belongs_to' => 'employees, roles'
                ],
                'business_rules' => [
                    'Unique constraint on employee_id + role_id',
                    'Cascade delete on employee deletion'
                ]
            ],

            'employeefiles' => [
                'description' => 'Employee document storage',
                'model' => 'Employeefiles',
                'key_fields' => [
                    'id' => 'Primary key',
                    'employeeN' => 'Employee number',
                    'empName' => 'Employee name',
                    'docName' => 'Document name',
                    'path' => 'File storage path'
                ],
                'relationships' => [
                    'belongs_to' => 'employees'
                ],
                'business_rules' => [
                    'Stores employee documents (contracts, certificates, etc.)',
                    'File path references physical storage'
                ]
            ],

            'job_postings' => [
                'description' => 'Job vacancies and recruitment postings',
                'model' => 'JobPosting',
                'key_fields' => [
                    'id' => 'Primary key',
                    'job_title' => 'Job position title',
                    'department' => 'Hiring department',
                    'location' => 'Job location',
                    'job_type' => 'Employment type',
                    'description' => 'Job description',
                    'requirements' => 'Job requirements',
                    'salary' => 'Salary offer',
                    'status' => 'Posting status (open, closed, draft)'
                ],
                'relationships' => [],
                'business_rules' => [
                    'Status values: open, closed, draft',
                    'Default status is open',
                    'Used for recruitment module'
                ]
            ],

            // ========= SELF-SERVICES SYSTEM =========
            // Note: Self-services primarily uses employee_requests table (documented in HR section)
            // The self-services module provides interfaces for:
            // - Leave requests
            // - Material/equipment requests
            // - Resignation requests
            // - Travel/advance requests
            // - Training/workshop requests
            // - Overtime requests
            // - Payslip/document requests
            // - General requests
            // All stored in employee_requests with different 'type' values

            'document_types' => [
                'description' => 'Document type definitions for various modules',
                'model' => 'DocumentType',
                'key_fields' => [
                    'document_id' => 'Primary key (auto-increment)',
                    'document_name' => 'Document name/title',
                    'collateral_type' => 'Associated collateral type'
                ],
                'relationships' => [],
                'business_rules' => [
                    'Used for document categorization',
                    'Links to loan collateral system',
                    'Supports employee documents and member documents'
                ]
            ],

            // ========= APPROVALS SYSTEM =========
            'approvals' => [
                'description' => 'Central approval workflow management',
                'model' => 'Approval',
                'key_fields' => [
                    'id' => 'Primary key',
                    'status' => 'Overall status (pending/approved/rejected)',
                    'checker_level' => 'Current approval level',
                    'first_checker_id' => 'First level approver',
                    'second_checker_id' => 'Second level approver',
                    'first_checker_status' => 'First checker decision',
                    'second_checker_status' => 'Second checker decision',
                    'rejection_reason' => 'Reason for rejection',
                    'approved_at' => 'Approval timestamp',
                    'rejected_at' => 'Rejection timestamp',
                    'process_name' => 'Type of process',
                    'process_description' => 'Process details',
                    'process_code' => 'Unique process code',
                    'process_id' => 'Related process ID',
                    'process_status' => 'Process status',
                    'approval_status' => 'Approval workflow status',
                    'user_id' => 'Initiator user',
                    'approver_id' => 'Final approver',
                    'edit_package' => 'JSON data for edits'
                ],
                'relationships' => [
                    'belongs_to' => 'users (multiple roles), process_code_configs',
                    'has_many' => 'approval_comments'
                ],
                'business_rules' => [
                    'Multi-level approval workflow',
                    'Supports first checker, second checker, and final approver',
                    'Soft deletes supported',
                    'Tracks timestamps for each approval stage'
                ]
            ],

            'process_code_configs' => [
                'description' => 'Configuration for approval processes',
                'model' => 'ProcessCodeConfig',
                'key_fields' => [
                    'id' => 'Primary key',
                    'process_code' => 'Unique process identifier',
                    'process_name' => 'Process name',
                    'description' => 'Process description',
                    'requires_first_checker' => 'Boolean for first level',
                    'requires_second_checker' => 'Boolean for second level',
                    'requires_approver' => 'Boolean for final approval',
                    'first_checker_roles' => 'JSON array of allowed roles',
                    'second_checker_roles' => 'JSON array of allowed roles',
                    'approver_roles' => 'JSON array of allowed roles',
                    'min_amount' => 'Minimum amount threshold',
                    'max_amount' => 'Maximum amount threshold',
                    'is_active' => 'Active status'
                ],
                'relationships' => [
                    'has_many' => 'approvals'
                ],
                'business_rules' => [
                    'Defines approval workflow rules',
                    'Role-based approval routing',
                    'Amount-based approval thresholds',
                    'Configurable approval levels'
                ]
            ],

            'approval_comments' => [
                'description' => 'Comments on approval requests',
                'model' => 'ApprovalComment',
                'key_fields' => [
                    'id' => 'Primary key',
                    'approval_id' => 'Related approval',
                    'comment' => 'Comment text'
                ],
                'relationships' => [
                    'belongs_to' => 'approvals'
                ],
                'business_rules' => [
                    'Cascade delete with approval',
                    'Tracks approval discussion'
                ]
            ],

            'approval_actions' => [
                'description' => 'Log of approval actions taken',
                'model' => 'ApprovalAction',
                'key_fields' => [
                    'id' => 'Primary key',
                    'approver_id' => 'Acting approver',
                    'status' => 'Action taken',
                    'comment' => 'Action comment',
                    'loan_id' => 'Related loan (if applicable)'
                ],
                'relationships' => [
                    'belongs_to' => 'users, loans'
                ],
                'business_rules' => [
                    'Audit trail of approval actions',
                    'Tracks who did what and when'
                ]
            ],

            'approval_matrix_configs' => [
                'description' => 'Matrix-based approval configuration',
                'model' => 'ApprovalMatrixConfig',
                'key_fields' => [
                    'id' => 'Primary key',
                    'process_type' => 'Type (loan, expense, budget, hire)',
                    'process_name' => 'Process name',
                    'process_code' => 'Process code',
                    'level' => 'Approval level',
                    'approver_role' => 'Required approver role',
                    'approver_sub_role' => 'Sub-role specification',
                    'min_amount' => 'Minimum amount',
                    'max_amount' => 'Maximum amount',
                    'is_active' => 'Active status',
                    'additional_conditions' => 'JSON complex rules'
                ],
                'relationships' => [],
                'business_rules' => [
                    'Hierarchical approval levels',
                    'Role and amount-based routing',
                    'Supports complex conditional logic',
                    'Process-specific configurations'
                ]
            ],

            'loan_approvals' => [
                'description' => 'Loan-specific approval tracking',
                'model' => 'LoanApproval',
                'key_fields' => [
                    'id' => 'Primary key',
                    'loan_id' => 'Related loan',
                    'stage_name' => 'Approval stage name',
                    'stage_type' => 'Stage type',
                    'approver_id' => 'Approver user',
                    'approver_name' => 'Approver name',
                    'status' => 'Stage status (PENDING/APPROVED/REJECTED)',
                    'comments' => 'Approval comments',
                    'approved_at' => 'Approval timestamp',
                    'conditions' => 'JSON approval conditions'
                ],
                'relationships' => [
                    'belongs_to' => 'loans, users'
                ],
                'business_rules' => [
                    'Tracks loan approval stages',
                    'Stage-based workflow',
                    'Supports conditional approvals',
                    'Cascade delete with loan'
                ]
            ],

            'expense_approvals' => [
                'description' => 'Expense-specific approval tracking',
                'model' => 'ExpenseApproval',
                'key_fields' => [
                    'id' => 'Primary key',
                    'user_id' => 'Approver user',
                    'user_name' => 'Approver name',
                    'status' => 'Approval status',
                    'expense_id' => 'Related expense',
                    'approval_level' => 'Approval level'
                ],
                'relationships' => [
                    'belongs_to' => 'expenses, users'
                ],
                'business_rules' => [
                    'Expense approval workflow',
                    'Multi-level approval support'
                ]
            ],

            'hires_approvals' => [
                'description' => 'Hiring/recruitment approval tracking',
                'model' => 'HiresApproval',
                'key_fields' => [
                    'id' => 'Primary key',
                    'hire_id' => 'Related hire request',
                    'approver_id' => 'Approver user',
                    'status' => 'Approval status',
                    'comments' => 'Approval comments',
                    'level' => 'Approval level'
                ],
                'relationships' => [
                    'belongs_to' => 'hires, users'
                ],
                'business_rules' => [
                    'Recruitment approval workflow',
                    'HR approval chain'
                ]
            ],

            'committee_approvals' => [
                'description' => 'Committee-based approval decisions',
                'model' => 'CommitteeApproval',
                'key_fields' => [
                    'id' => 'Primary key',
                    'committee_id' => 'Committee reference',
                    'meeting_id' => 'Meeting reference',
                    'agenda_item' => 'Agenda item discussed',
                    'decision' => 'Committee decision',
                    'votes_for' => 'Votes in favor',
                    'votes_against' => 'Votes against',
                    'abstentions' => 'Abstention votes',
                    'minutes' => 'Meeting minutes excerpt'
                ],
                'relationships' => [
                    'belongs_to' => 'committees, meetings'
                ],
                'business_rules' => [
                    'Board/committee level approvals',
                    'Voting record tracking',
                    'Meeting minutes integration'
                ]
            ],

            // ========= REPORTS & ANALYTICS SYSTEM =========
            'reports' => [
                'description' => 'Generated reports storage',
                'model' => 'Report',
                'key_fields' => [
                    'id' => 'Primary key',
                    'type' => 'Report type identifier',
                    'date' => 'Report generation date',
                    'data' => 'JSON report data',
                    'status' => 'Generation status (pending/completed/failed)'
                ],
                'relationships' => [],
                'business_rules' => [
                    'Stores generated report data',
                    'JSON format for flexible data storage',
                    'Status tracking for generation process'
                ]
            ],

            'scheduled_reports' => [
                'description' => 'Scheduled and automated report generation',
                'model' => 'ScheduledReport',
                'key_fields' => [
                    'id' => 'Primary key',
                    'report_type' => 'Type of report',
                    'report_config' => 'JSON configuration',
                    'user_id' => 'User who scheduled',
                    'status' => 'Status (scheduled/processing/completed/failed/cancelled)',
                    'frequency' => 'Frequency (once/daily/weekly/monthly/quarterly/annually)',
                    'scheduled_at' => 'Scheduled time',
                    'last_run_at' => 'Last execution time',
                    'next_run_at' => 'Next scheduled run',
                    'error_message' => 'Error details if failed',
                    'output_path' => 'Generated report path',
                    'email_recipients' => 'Email distribution list',
                    'email_sent' => 'Email sent flag',
                    'retry_count' => 'Number of retries',
                    'max_retries' => 'Maximum retry attempts'
                ],
                'relationships' => [
                    'belongs_to' => 'users'
                ],
                'business_rules' => [
                    'Automated report scheduling',
                    'Email distribution support',
                    'Retry mechanism for failures',
                    'Multiple frequency options',
                    'Cascade delete with user'
                ]
            ],

            'financial_data' => [
                'description' => 'Financial reporting data',
                'model' => 'FinancialData',
                'key_fields' => [
                    'id' => 'Primary key',
                    'description' => 'Data description',
                    'category' => 'Data category',
                    'value' => 'Financial value',
                    'end_of_business_year' => 'Financial year end',
                    'unit' => 'Unit of measure (default: Tshs.)'
                ],
                'relationships' => [],
                'business_rules' => [
                    'Stores financial statement data',
                    'Categorized financial information',
                    'Year-end reporting data'
                ]
            ],

            'financial_position' => [
                'description' => 'Statement of financial position data',
                'model' => 'FinancialPosition',
                'key_fields' => [
                    'id' => 'Primary key',
                    'interest_on_loans' => 'Interest income from loans',
                    'other_income' => 'Other income sources',
                    'total_income' => 'Total income',
                    'expenses' => 'Total expenses',
                    'annual_surplus' => 'Annual surplus/deficit',
                    'end_of_business_year' => 'Financial year end'
                ],
                'relationships' => [],
                'business_rules' => [
                    'Income statement data',
                    'Annual financial performance',
                    'Surplus calculation'
                ]
            ],

            'financial_ratios' => [
                'description' => 'Financial performance ratios',
                'model' => 'FinancialRatio',
                'key_fields' => [
                    'id' => 'Primary key',
                    'end_of_financial_year_date' => 'Financial year end',
                    'core_capital' => 'Core capital amount',
                    'total_assets' => 'Total assets',
                    'net_capital' => 'Net capital',
                    'short_term_assets' => 'Current assets',
                    'short_term_liabilities' => 'Current liabilities',
                    'expenses' => 'Total expenses',
                    'income' => 'Total income'
                ],
                'relationships' => [],
                'business_rules' => [
                    'Key financial ratios',
                    'Liquidity measurements',
                    'Capital adequacy tracking',
                    'Performance indicators'
                ]
            ],

            'audit_logs' => [
                'description' => 'System audit trail',
                'model' => 'AuditLog',
                'key_fields' => [
                    'id' => 'Primary key',
                    'user_id' => 'User performing action',
                    'action' => 'Action performed',
                    'details' => 'Action details'
                ],
                'relationships' => [
                    'belongs_to' => 'users'
                ],
                'business_rules' => [
                    'Complete audit trail',
                    'User action tracking',
                    'Cascade delete with user'
                ]
            ],

            // Report types supported by the system:
            // - Statement of Financial Position
            // - Statement of Comprehensive Income
            // - Statement of Cash Flow
            // - Sectoral Classification of Loans
            // - Interest Rates Structure
            // - Loans Disbursed Reports
            // - Loan Delinquency Reports
            // - Portfolio at Risk
            // - Client Details Reports
            // - Daily Transaction Reports
            // - Loan Status Reports
            // - Committee Reports
            // - And 20+ other report types
            
            'client_documents' => [
                'description' => 'KYC and other member documents storage',
                'model' => 'ClientDocument',
                'key_fields' => [
                    'id' => 'Primary key',
                    'client_id' => 'Member ID',
                    'document_type' => 'Type of document (profile_photo, id_card, etc)',
                    'document_name' => 'Document filename',
                    'file_path' => 'Storage location',
                    'file_size' => 'Document size',
                    'mime_type' => 'File type',
                    'verification_status' => 'PENDING/VERIFIED/REJECTED',
                    'verified_by' => 'User who verified',
                    'verified_at' => 'Verification date'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Document owner (via client_id)',
                        'users' => 'Verifier (via verified_by)'
                    ]
                ],
                'data_flow' => 'Upload → Storage → Verification → KYC Complete'
            ],
            
            'member_groups' => [
                'description' => 'Member groupings for collective operations',
                'key_fields' => [
                    'id' => 'Primary key',
                    'group_name' => 'Group identifier',
                    'group_type' => 'Type of group',
                    'description' => 'Group description',
                    'created_by' => 'Creator user',
                    'status' => 'Group status'
                ],
                'relationships' => [
                    'has_many' => [
                        'clients' => 'Members in this group',
                        'group_loans' => 'Group-based loans',
                        'group_savings' => 'Group savings accounts'
                    ],
                    'belongs_to' => [
                        'users' => 'Group administrator'
                    ]
                ],
                'data_flow' => 'Group Creation → Member Assignment → Collective Operations'
            ],
            
            'onboarding' => [
                'description' => 'Member onboarding process tracking',
                'key_fields' => [
                    'id' => 'Primary key',
                    'client_number' => 'Member being onboarded',
                    'step' => 'Current onboarding step',
                    'status' => 'Step status',
                    'kyc_status' => 'KYC verification status',
                    'documents_status' => 'Document collection status',
                    'account_opening_status' => 'Account creation status',
                    'share_purchase_status' => 'Initial share purchase',
                    'completed_at' => 'Onboarding completion date'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Member being onboarded (via client_number)'
                    ],
                    'has_many' => [
                        'onboarding_tasks' => 'Individual onboarding tasks',
                        'onboarding_documents' => 'Required documents'
                    ]
                ],
                'data_flow' => 'Application → KYC → Documents → Accounts → Shares → Active Member'
            ],
            
            'member_categories' => [
                'description' => 'Classification of members into categories',
                'key_fields' => [
                    'id' => 'Primary key',
                    'category_name' => 'Category name',
                    'description' => 'Category description',
                    'minimum_shares' => 'Required share amount',
                    'benefits' => 'Member benefits',
                    'requirements' => 'Category requirements'
                ],
                'relationships' => [
                    'has_many' => [
                        'clients' => 'Members in this category'
                    ]
                ],
                'data_flow' => 'Category Definition → Member Classification → Benefits Application'
            ],
            
            // ========= SAVINGS MANAGEMENT SYSTEM =========
            'savings_accounts' => [
                'description' => 'Member savings accounts - uses accounts table with product_number=2000 for savings products',
                'model' => 'SavingsModel',
                'actual_table' => 'accounts',
                'key_fields' => [
                    'id' => 'Primary key',
                    'client_number' => 'Member who owns the account',
                    'account_number' => 'Unique savings account number',
                    'account_name' => 'Account holder name',
                    'product_number' => '2000 for savings products',
                    'sub_product_number' => 'Specific savings product type',
                    'balance' => 'Current account balance',
                    'status' => 'Account status (ACTIVE, DORMANT, CLOSED)',
                    'locked_amount' => 'Amount locked (e.g., as loan collateral)',
                    'account_use' => 'Purpose of account',
                    'branch_number' => 'Branch where account was opened',
                    'institution_number' => 'Institution identifier'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Account owner (via client_number)',
                        'branches' => 'Branch (via branch_number)',
                        'institutions' => 'Institution (via institution_id)',
                        'sub_products' => 'Savings product type (via sub_product_number)'
                    ],
                    'has_many' => [
                        'transactions' => 'Savings deposits and withdrawals',
                        'locked_amounts' => 'Locked amount records',
                        'mandatory_savings_tracking' => 'Mandatory savings payment tracking'
                    ]
                ],
                'data_flow' => 'Member → Account Opening → Deposits/Withdrawals → Interest Calculation → Balance Update',
                'important_notes' => [
                    'SavingsModel uses accounts table (protected $table = "accounts")',
                    'Filtered by product_number = 2000 for savings products',
                    'Balance is maintained in the balance field',
                    'Can be locked as collateral via locked_amount field'
                ]
            ],
            
            'mandatory_savings_tracking' => [
                'description' => 'Tracks monthly mandatory savings payments for members',
                'model' => 'MandatorySavingsTracking',
                'key_fields' => [
                    'id' => 'Primary key',
                    'client_number' => 'Member/client number',
                    'account_number' => 'Mandatory savings account number',
                    'year' => 'Year for the payment',
                    'month' => 'Month for the payment (1-12)',
                    'required_amount' => 'Required amount for this month',
                    'paid_amount' => 'Amount actually paid',
                    'balance' => 'Outstanding balance (required - paid)',
                    'status' => 'PAID, PARTIAL, UNPAID, OVERDUE',
                    'due_date' => 'Due date for the payment',
                    'paid_date' => 'Date when payment was made',
                    'months_in_arrears' => 'Number of months in arrears',
                    'total_arrears' => 'Total arrears amount',
                    'notes' => 'Additional notes'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Member (via client_number)',
                        'accounts' => 'Savings account (via account_number)'
                    ],
                    'has_many' => [
                        'mandatory_savings_notifications' => 'Payment reminders'
                    ]
                ],
                'indexes' => [
                    'client_number, year, month',
                    'status, due_date',
                    'account_number, year, month',
                    'due_date'
                ],
                'data_flow' => 'Monthly Schedule → Track Payment → Update Status → Calculate Arrears → Send Reminders'
            ],
            
            'mandatory_savings_notifications' => [
                'description' => 'Notification system for mandatory savings reminders',
                'model' => 'MandatorySavingsNotification',
                'key_fields' => [
                    'id' => 'Primary key',
                    'client_number' => 'Member/client number',
                    'account_number' => 'Mandatory savings account number',
                    'year' => 'Year for the payment',
                    'month' => 'Month for the payment',
                    'notification_type' => 'FIRST_REMINDER, SECOND_REMINDER, FINAL_REMINDER, OVERDUE_NOTICE',
                    'notification_method' => 'SMS, EMAIL, SYSTEM',
                    'message' => 'Notification message',
                    'status' => 'PENDING, SENT, FAILED',
                    'sent_at' => 'When notification was sent',
                    'scheduled_at' => 'When notification should be sent',
                    'metadata' => 'Additional data (SMS/Email details) as JSON'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'mandatory_savings_tracking' => 'Related payment tracking record',
                        'clients' => 'Member receiving notification'
                    ]
                ],
                'indexes' => [
                    'client_number, year, month',
                    'status, scheduled_at',
                    'notification_type'
                ],
                'data_flow' => 'Payment Due → Schedule Notification → Send (SMS/Email/System) → Track Status'
            ],
            
            'mandatory_savings_settings' => [
                'description' => 'Configuration for mandatory savings requirements',
                'model' => 'MandatorySavingsSettings',
                'key_fields' => [
                    'id' => 'Primary key',
                    'institution_id' => 'Institution ID (default 1)',
                    'mandatory_savings_account' => 'Account number for mandatory savings',
                    'monthly_amount' => 'Monthly required amount',
                    'due_day' => 'Day of month when payment is due (default 5)',
                    'grace_period_days' => 'Grace period after due date (default 5)',
                    'enable_notifications' => 'Enable notification system',
                    'first_reminder_days' => 'Days before due date for first reminder (default 7)',
                    'second_reminder_days' => 'Days before due date for second reminder (default 3)',
                    'final_reminder_days' => 'Days before due date for final reminder (default 1)',
                    'enable_sms_notifications' => 'Enable SMS notifications',
                    'enable_email_notifications' => 'Enable email notifications',
                    'sms_template' => 'SMS message template',
                    'email_template' => 'Email message template',
                    'additional_settings' => 'Additional configuration as JSON'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'institutions' => 'Institution (via institution_id)'
                    ]
                ],
                'data_flow' => 'Settings → Apply Rules → Generate Tracking → Send Notifications'
            ],
            
            'savings_types' => [
                'description' => 'Types of savings products offered',
                'model' => 'SavingsType',
                'key_fields' => [
                    'id' => 'Primary key',
                    'type' => 'Savings type name',
                    'summary' => 'Description of the savings type',
                    'status' => 'Active/Inactive status',
                    'institution_id' => 'Institution offering this type'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'institutions' => 'Institution (via institution_id)'
                    ],
                    'has_many' => [
                        'sub_products' => 'Specific savings products under this type'
                    ]
                ],
                'data_flow' => 'Savings Type → Sub Products → Member Accounts'
            ],
            
            'savings_accounts_table' => [
                'description' => 'Alternative savings accounts table (if separate from main accounts)',
                'model' => 'SavingsAccount',
                'key_fields' => [
                    'id' => 'Primary key',
                    'member_id' => 'Member who owns the account',
                    'account_number' => 'Unique account number',
                    'balance' => 'Current balance',
                    'interest_rate' => 'Applied interest rate',
                    'status' => 'Account status'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'members' => 'Account owner (via member_id)'
                    ]
                ],
                'data_flow' => 'Member → Account → Transactions → Balance',
                'note' => 'This table may not be actively used if savings are managed through the main accounts table'
            ],
            
            'savings_transactions' => [
                'description' => 'Savings deposits, withdrawals, and interest postings',
                'actual_table' => 'transactions',
                'key_fields' => [
                    'id' => 'Primary key',
                    'account_number' => 'Savings account',
                    'transaction_type' => 'DEPOSIT, WITHDRAWAL, INTEREST, TRANSFER',
                    'amount' => 'Transaction amount',
                    'balance_after' => 'Account balance after transaction',
                    'reference_number' => 'Unique transaction reference',
                    'narration' => 'Transaction description',
                    'transaction_date' => 'Date of transaction',
                    'posted_by' => 'User who posted transaction'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Savings account (via account_number)',
                        'users' => 'Posting user (via posted_by)',
                        'clients' => 'Member (through account)'
                    ]
                ],
                'data_flow' => 'Initiate → Validate → Post → Update Balance → Generate Receipt'
            ],
            
            'savings_interest_calculations' => [
                'description' => 'Interest calculation and posting for savings accounts',
                'key_fields' => [
                    'account_number' => 'Savings account',
                    'calculation_date' => 'Date of calculation',
                    'average_balance' => 'Average balance for period',
                    'interest_rate' => 'Applied rate',
                    'interest_amount' => 'Calculated interest',
                    'posted' => 'Whether interest has been posted',
                    'posting_date' => 'Date interest was posted'
                ],
                'data_flow' => 'Monthly/Quarterly → Calculate Average Balance → Apply Rate → Post Interest → Update Account'
            ],
            
            'fixed_deposits' => [
                'description' => 'Fixed term deposit accounts (subset of savings)',
                'key_fields' => [
                    'account_number' => 'FD account number',
                    'principal_amount' => 'Initial deposit',
                    'interest_rate' => 'Fixed rate',
                    'term_months' => 'Deposit term in months',
                    'maturity_date' => 'When FD matures',
                    'auto_renew' => 'Whether to auto-renew on maturity',
                    'status' => 'ACTIVE, MATURED, WITHDRAWN, RENEWED'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Main account record',
                        'clients' => 'FD owner'
                    ]
                ],
                'data_flow' => 'Open FD → Lock Funds → Accrue Interest → Mature → Withdraw/Renew'
            ],
            
            // ========= DEPOSITS MANAGEMENT SYSTEM =========
            'deposit_accounts' => [
                'description' => 'Member deposit accounts - uses accounts table with product_number=3000 for deposit products',
                'model' => 'DepositsModel',
                'actual_table' => 'accounts',
                'key_fields' => [
                    'id' => 'Primary key',
                    'client_number' => 'Member who owns the deposit account',
                    'account_number' => 'Unique deposit account number',
                    'account_name' => 'Account holder name',
                    'product_number' => '3000 for deposit products',
                    'sub_product_number' => 'Specific deposit product type',
                    'balance' => 'Current deposit balance',
                    'status' => 'Account status (ACTIVE, DORMANT, CLOSED)',
                    'locked_amount' => 'Amount locked (e.g., as loan collateral)',
                    'account_use' => 'Purpose of deposit account',
                    'branch_number' => 'Branch where account was opened',
                    'institution_number' => 'Institution identifier',
                    'minimum_balance' => 'Minimum required balance',
                    'interest_rate' => 'Applicable interest rate'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Account owner (via client_number)',
                        'branches' => 'Branch (via branch_number)',
                        'institutions' => 'Institution (via institution_id)',
                        'sub_products' => 'Deposit product type (via sub_product_number)',
                        'deposit_types' => 'Deposit type configuration'
                    ],
                    'has_many' => [
                        'transactions' => 'Deposit and withdrawal transactions',
                        'interest_calculations' => 'Interest accrual records',
                        'locked_amounts' => 'Collateral lock records'
                    ]
                ],
                'data_flow' => 'Member → Open Deposit Account → Deposits/Withdrawals → Interest Accrual → Maturity/Renewal',
                'important_notes' => [
                    'DepositsModel uses accounts table (protected $table = "accounts")',
                    'Filtered by product_number = 3000 for deposit products',
                    'Interest calculated based on product configuration',
                    'Can serve as loan collateral'
                ]
            ],
            
            'deposit_types' => [
                'description' => 'Types of deposit products offered',
                'model' => 'DepositType',
                'key_fields' => [
                    'id' => 'Primary key',
                    'type' => 'Deposit type name',
                    'summary' => 'Description of the deposit type',
                    'status' => 'Active/Inactive status',
                    'institution_id' => 'Institution offering this type'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'institutions' => 'Institution (via institution_id)'
                    ],
                    'has_many' => [
                        'sub_products' => 'Specific deposit products under this type (via deposit_type_id)',
                        'deposit_accounts' => 'Accounts of this deposit type'
                    ]
                ],
                'data_flow' => 'Deposit Type → Sub Products → Member Deposit Accounts'
            ],
            
            'term_deposits' => [
                'description' => 'Time/Term deposit accounts with fixed maturity periods',
                'key_fields' => [
                    'account_number' => 'Term deposit account number',
                    'client_number' => 'Member owning the deposit',
                    'principal_amount' => 'Initial deposit amount',
                    'interest_rate' => 'Fixed interest rate',
                    'term_months' => 'Deposit term (3, 6, 12, 24, 36 months)',
                    'deposit_date' => 'Date of deposit',
                    'maturity_date' => 'Maturity date',
                    'maturity_amount' => 'Amount at maturity (principal + interest)',
                    'auto_renew' => 'Auto-renewal flag',
                    'penalty_rate' => 'Early withdrawal penalty rate',
                    'status' => 'ACTIVE, MATURED, WITHDRAWN, RENEWED, BROKEN'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'clients' => 'Depositor (via client_number)',
                        'accounts' => 'Main deposit account'
                    ],
                    'has_many' => [
                        'interest_postings' => 'Interest accrual records',
                        'renewal_history' => 'Previous term renewals'
                    ]
                ],
                'data_flow' => 'Open Term Deposit → Lock Funds → Accrue Interest → Maturity Notice → Withdraw/Renew'
            ],
            
            'deposit_transactions' => [
                'description' => 'Deposit account transactions',
                'actual_table' => 'transactions',
                'key_fields' => [
                    'id' => 'Primary key',
                    'account_number' => 'Deposit account',
                    'transaction_type' => 'DEPOSIT, WITHDRAWAL, INTEREST, TRANSFER',
                    'amount' => 'Transaction amount',
                    'balance_after' => 'Account balance after transaction',
                    'reference_number' => 'Unique transaction reference',
                    'payment_method' => 'cash, bank, internal_transfer, tips_mno, tips_bank',
                    'bank_reference' => 'Bank transaction reference',
                    'narration' => 'Transaction description',
                    'transaction_date' => 'Date of transaction',
                    'posted_by' => 'User who posted transaction'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Deposit account (via account_number)',
                        'users' => 'Posting user (via posted_by)',
                        'clients' => 'Member (through account)',
                        'bank_accounts' => 'Bank account used (if applicable)'
                    ]
                ],
                'data_flow' => 'Initiate → Validate Balance → Post → Update Account → Generate Receipt'
            ],
            
            'deposit_interest_settings' => [
                'description' => 'Interest configuration for deposit products',
                'key_fields' => [
                    'product_id' => 'Deposit product identifier',
                    'interest_rate' => 'Annual interest rate (%)',
                    'interest_calculation_method' => 'SIMPLE, COMPOUND, FLAT',
                    'interest_posting_frequency' => 'DAILY, MONTHLY, QUARTERLY, ANNUALLY',
                    'minimum_balance_for_interest' => 'Minimum balance to earn interest',
                    'withholding_tax_rate' => 'Tax rate on interest earned',
                    'penalty_for_early_withdrawal' => 'Early withdrawal penalty (%)'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'sub_products' => 'Deposit product (via product_id)'
                    ]
                ],
                'data_flow' => 'Product Setup → Interest Rules → Apply to Accounts → Calculate & Post Interest'
            ],
            
            'members_savings_and_deposits' => [
                'description' => 'Category mapping for member savings and deposits',
                'actual_table' => 'MEMBERS_SAVINGS_AND_DEPOSITS',
                'key_fields' => [
                    'id' => 'Primary key',
                    'category_code' => 'Category identifier',
                    'sub_category_code' => 'Sub-category identifier',
                    'sub_category_name' => 'Sub-category description'
                ],
                'relationships' => [
                    'has_many' => [
                        'accounts' => 'Related deposit and savings accounts'
                    ]
                ],
                'data_flow' => 'Category Definition → Account Classification → Reporting'
            ],
            
            'deposit_maturity_notifications' => [
                'description' => 'Notifications for deposit maturity and renewals',
                'key_fields' => [
                    'account_number' => 'Deposit account',
                    'notification_type' => 'PRE_MATURITY, MATURITY, POST_MATURITY',
                    'days_to_maturity' => 'Days remaining to maturity',
                    'notification_date' => 'Date to send notification',
                    'status' => 'PENDING, SENT, FAILED',
                    'notification_method' => 'SMS, EMAIL, SYSTEM'
                ],
                'relationships' => [
                    'belongs_to' => [
                        'accounts' => 'Deposit account',
                        'clients' => 'Account owner'
                    ]
                ],
                'data_flow' => 'Maturity Approaching → Schedule Notification → Send → Track Response'
            ]
        ];








