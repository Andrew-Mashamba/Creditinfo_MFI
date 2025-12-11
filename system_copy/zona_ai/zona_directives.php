<?php

/**
 * Zona AI Directives
 * Core instructions and knowledge base for accurate responses
 */

return [
    'system_context' => [
        'name' => 'SACCOS Core System',
        'version' => '6.0',
        'database' => 'PostgreSQL',
        'framework' => 'Laravel 9.x with Livewire',
        'project_path' => '/Volumes/DATA/PROJECTS/SACCOS/SYSTEMS/SACCOS_CORE_SYSTEM',
    ],
    
    'core_directives' => [
        'always_use_real_data' => 'NEVER make up or hallucinate data. Always query the actual database.',
        'verify_before_response' => 'Check database tables before providing counts or statistics.',
        'handle_missing_data' => 'If data doesn\'t exist, say "No data available" or "0 records found".',
        'be_specific' => 'Provide exact numbers, names, and dates from the database.',
        'respect_privacy' => 'Don\'t expose sensitive information like passwords or private keys.',
        'maintain_context' => 'Remember previous questions in the conversation for context.',
    ],
    
    'database_tables' => [
        'users' => ['table' => 'users', 'key_fields' => ['id', 'name', 'email', 'role']],
        'clients' => ['table' => 'clients', 'key_fields' => ['id', 'first_name', 'last_name', 'membership_number']],
        'branches' => ['table' => 'branches', 'key_fields' => ['id', 'name', 'branch_code', 'address']],
        'accounts' => ['table' => 'accounts', 'key_fields' => ['id', 'account_number', 'account_name', 'balance']],
        'loans' => ['table' => 'loans', 'key_fields' => ['id', 'loan_account_number', 'principle', 'status']],
        'savings' => ['table' => 'savings', 'key_fields' => ['id', 'account_number', 'balance', 'interest_rate']],
        'shares' => ['table' => 'shares', 'key_fields' => ['id', 'client_id', 'number_of_shares', 'share_value']],
        'transactions' => ['table' => 'transactions', 'key_fields' => ['id', 'transaction_number', 'amount', 'type']],
        'approvals' => ['table' => 'approvals', 'key_fields' => ['id', 'model', 'status', 'approver_id']],
        'employees' => ['table' => 'employees', 'key_fields' => ['id', 'first_name', 'last_name', 'department_id']],
        'departments' => ['table' => 'departments', 'key_fields' => ['id', 'department_name', 'description']],
        'roles' => ['table' => 'roles', 'key_fields' => ['id', 'role_name', 'description']],
        'permissions' => ['table' => 'permissions', 'key_fields' => ['id', 'permission_name', 'description']],
        'institutions' => ['table' => 'institutions', 'key_fields' => ['id', 'name', 'region']],
    ],
    
    'common_queries' => [
        'total_members' => "SELECT COUNT(*) as count FROM clients",
        'active_members' => "SELECT COUNT(*) as count FROM clients WHERE status = 'ACTIVE'",
        'total_users' => "SELECT COUNT(*) as count FROM users",
        'user_list' => "SELECT name, email, role FROM users",
        'total_branches' => "SELECT COUNT(*) as count FROM branches",
        'branch_list' => "SELECT name, branch_code, address FROM branches",
        'total_loans' => "SELECT COUNT(*) as count FROM loans",
        'active_loans' => "SELECT COUNT(*) as count FROM loans WHERE status = 'ACTIVE'",
        'total_accounts' => "SELECT COUNT(*) as count FROM accounts",
        'total_savings' => "SELECT COALESCE(SUM(balance), 0) as total FROM accounts WHERE account_type = 'SAVINGS'",
        'total_shares' => "SELECT COALESCE(SUM(number_of_shares * share_value), 0) as total FROM shares",
    ],
    
    'response_templates' => [
        'count_response' => "There are {count} {entity} in the system.",
        'list_response' => "Here are the {entity} in the system:\n{list}",
        'no_data' => "No {entity} found in the system.",
        'error' => "I encountered an error accessing {entity} data: {error}",
        'detail_response' => "{entity} Details:\n{details}",
    ],
    
    'module_mappings' => [
        0 => ['name' => 'Dashboard', 'focus' => 'Overview statistics and KPIs'],
        1 => ['name' => 'Branches', 'focus' => 'Branch management and operations'],
        2 => ['name' => 'Members/Clients', 'focus' => 'Member registration and management'],
        3 => ['name' => 'Shares', 'focus' => 'Share capital management'],
        4 => ['name' => 'Savings', 'focus' => 'Savings accounts and deposits'],
        5 => ['name' => 'Deposits', 'focus' => 'Deposit transactions'],
        6 => ['name' => 'Loans', 'focus' => 'Loan management and disbursements'],
        7 => ['name' => 'Products', 'focus' => 'Product catalog and management'],
        8 => ['name' => 'Accounting', 'focus' => 'Financial accounting and GL'],
        9 => ['name' => 'Expenses', 'focus' => 'Expense tracking and management'],
        10 => ['name' => 'Payments', 'focus' => 'Payment processing'],
        11 => ['name' => 'Investment', 'focus' => 'Investment portfolio'],
        12 => ['name' => 'Procurement', 'focus' => 'Purchase and vendor management'],
        13 => ['name' => 'Budget', 'focus' => 'Budget planning and monitoring'],
        14 => ['name' => 'Insurance', 'focus' => 'Insurance services'],
        15 => ['name' => 'Teller', 'focus' => 'Teller and cash operations'],
        16 => ['name' => 'Reconciliation', 'focus' => 'Account reconciliation'],
        17 => ['name' => 'HR', 'focus' => 'Human resources management'],
        18 => ['name' => 'Self-Services', 'focus' => 'Member self-service portal'],
        19 => ['name' => 'Approvals', 'focus' => 'Approval workflows'],
        20 => ['name' => 'Reports', 'focus' => 'Report generation'],
        21 => ['name' => 'Profile', 'focus' => 'User profile settings'],
        22 => ['name' => 'Users', 'focus' => 'System user management'],
        23 => ['name' => 'Active Loans', 'focus' => 'Active loan monitoring'],
        24 => ['name' => 'Management', 'focus' => 'Executive dashboard'],
        26 => ['name' => 'Cash Management', 'focus' => 'Cash and vault operations'],
        27 => ['name' => 'Billing', 'focus' => 'Bill generation and collection'],
        28 => ['name' => 'Transactions', 'focus' => 'Transaction processing'],
        29 => ['name' => 'Members Portal', 'focus' => 'Online member services'],
        30 => ['name' => 'Email', 'focus' => 'Email communications'],
        31 => ['name' => 'Subscriptions', 'focus' => 'Subscription management'],
    ],
];