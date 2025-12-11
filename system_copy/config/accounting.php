<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Accounting Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all accounting-related configuration options for
    | the SACCOS Core System, including transaction limits, validation rules,
    | and posting requirements.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Transaction Limits
    |--------------------------------------------------------------------------
    */

    // Minimum transaction amount allowed
    'min_transaction_amount' => env('MIN_TRANSACTION_AMOUNT', 0.01),

    // Maximum transaction amount allowed
    'max_transaction_amount' => env('MAX_TRANSACTION_AMOUNT', 999999999.99),

    // Maximum negative balance allowed for expense accounts
    'max_negative_expense_balance' => env('MAX_NEGATIVE_EXPENSE_BALANCE', 1000000),

    /*
    |--------------------------------------------------------------------------
    | Account Posting Rules
    |--------------------------------------------------------------------------
    */

    // Minimum account level that can be posted to (1=Major, 2=Category, 3=SubCategory, 4=Detail)
    'min_posting_level' => env('MIN_POSTING_LEVEL', 3),

    // Allow posting to inactive accounts
    'allow_inactive_posting' => env('ALLOW_INACTIVE_POSTING', false),

    // Allow overdraft on any accounts (set to false for strict no-negative-balance policy)
    'allow_overdraft' => env('ALLOW_OVERDRAFT', false),
    
    // Enforce no negative balances on all accounts
    'strict_no_negative_balance' => env('STRICT_NO_NEGATIVE_BALANCE', true),

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    // Require approval for manual postings
    'require_manual_posting_approval' => env('REQUIRE_MANUAL_POSTING_APPROVAL', false),

    // Maximum days to reverse a transaction
    'max_reversal_days' => env('MAX_REVERSAL_DAYS', 30),

    // Require narration for all transactions
    'require_narration' => env('REQUIRE_NARRATION', true),

    /*
    |--------------------------------------------------------------------------
    | Account Types Configuration
    |--------------------------------------------------------------------------
    */

    'account_types' => [
        'asset_accounts' => [
            'name' => 'Asset Accounts',
            'normal_balance' => 'debit',
            'increase_by' => 'debit',
            'decrease_by' => 'credit',
            'allow_negative' => false, // Strict policy: No negative balances
        ],
        'liability_accounts' => [
            'name' => 'Liability Accounts',
            'normal_balance' => 'credit',
            'increase_by' => 'credit',
            'decrease_by' => 'debit',
            'allow_negative' => false, // Strict policy: No negative balances
        ],
        'equity_accounts' => [
            'name' => 'Equity Accounts',
            'normal_balance' => 'credit',
            'increase_by' => 'credit',
            'decrease_by' => 'debit',
            'allow_negative' => false, // Strict policy: No negative balances
        ],
        'capital_accounts' => [
            'name' => 'Capital Accounts',
            'normal_balance' => 'credit',
            'increase_by' => 'credit',
            'decrease_by' => 'debit',
            'allow_negative' => false, // Strict policy: No negative balances
        ],
        'income_accounts' => [
            'name' => 'Income/Revenue Accounts',
            'normal_balance' => 'credit',
            'increase_by' => 'credit',
            'decrease_by' => 'debit',
            'allow_negative' => false, // Strict policy: No negative balances
        ],
        'expense_accounts' => [
            'name' => 'Expense Accounts',
            'normal_balance' => 'debit',
            'increase_by' => 'debit',
            'decrease_by' => 'credit',
            'allow_negative' => false, // Strict policy: No negative balances (changed from true)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Special Accounts
    |--------------------------------------------------------------------------
    */

    // Account numbers that require special handling
    'restricted_accounts' => [
        // Add account numbers that need special permissions to post to
        // Example: '0101100010001000', // Main Cash Account
    ],

    // Suspense account settings
    'suspense_account' => [
        'enabled' => env('ENABLE_SUSPENSE_ACCOUNT', true),
        'auto_clear_days' => env('SUSPENSE_AUTO_CLEAR_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail Settings
    |--------------------------------------------------------------------------
    */

    'audit_trail' => [
        'enabled' => env('ENABLE_AUDIT_TRAIL', true),
        'detailed_logging' => env('DETAILED_AUDIT_LOGGING', true),
        'retention_days' => env('AUDIT_RETENTION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Notification Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        // Enable/disable transaction notifications
        'enabled' => env('ENABLE_TRANSACTION_NOTIFICATIONS', true),
        
        // Send notifications for successful transactions
        'notify_on_success' => env('NOTIFY_ON_SUCCESS', true),
        
        // Send notifications for failed transactions
        'notify_on_failure' => env('NOTIFY_ON_FAILURE', true),
        
        // Notification channels to use
        'channels' => [
            'sms' => env('ENABLE_SMS_NOTIFICATIONS', true),
            'email' => env('ENABLE_EMAIL_NOTIFICATIONS', true),
        ],
        
        // Minimum transaction amount to trigger notification
        'min_amount_for_notification' => env('MIN_NOTIFICATION_AMOUNT', 100),
        
        // Queue name for notifications
        'queue_name' => env('NOTIFICATION_QUEUE', 'notifications'),
        
        // Retry failed notifications
        'retry_failed' => env('RETRY_FAILED_NOTIFICATIONS', true),
        'max_retries' => env('MAX_NOTIFICATION_RETRIES', 3),
    ],

];