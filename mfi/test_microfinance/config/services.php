<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'claude' => [
        'api_key' => env('CLAUDE_API_KEY'),
    ],

    'nbc_payments' => [
        'base_url' => env('NBC_PAYMENTS_BASE_URL'),
        'api_key' => env('NBC_PAYMENTS_API_KEY'),
        'client_id' => env('NBC_PAYMENTS_CLIENT_ID'),
        'private_key' => env('NBC_PAYMENTS_PRIVATE_KEY'),
        'private_key_path' => env('NBC_PAYMENTS_PRIVATE_KEY_PATH', storage_path('keys/private.pem')),
        'saccos_account' => env('NBC_PAYMENTS_SACCOS_ACCOUNT', '015103001490'),
    ],

    'nbc_internal_fund_transfer' => [
        'base_url' => env('NBC_INTERNAL_FUND_TRANSFER_BASE_URL'),
        'api_key' => env('NBC_INTERNAL_FUND_TRANSFER_API_KEY'),
        'username' => env('NBC_INTERNAL_FUND_TRANSFER_USERNAME'),
        'password' => env('NBC_INTERNAL_FUND_TRANSFER_PASSWORD'),
        'private_key' => 'file://' . env('NBC_INTERNAL_FUND_TRANSFER_PRIVATE_KEY'),
        'service_name' => env('NBC_INTERNAL_FUND_TRANSFER_SERVICE_NAME', 'internal-fund-transfer'),
        'channel_id' => env('NBC_INTERNAL_FUND_TRANSFER_CHANNEL_ID'),
        'verify_ssl' => env('NBC_INTERNAL_FUND_TRANSFER_VERIFY_SSL', true),
        'timeout' => env('NBC_INTERNAL_FUND_TRANSFER_TIMEOUT', 30),
        'max_retries' => env('NBC_INTERNAL_FUND_TRANSFER_MAX_RETRIES', 3),
        'retry_delay' => env('NBC_INTERNAL_FUND_TRANSFER_RETRY_DELAY', 2),
    ],

    'nbc_statement' => [
        'base_url' => env('NBC_STATEMENT_BASE_URL'),
        'username' => env('NBC_STATEMENT_USERNAME'),
        'password' => env('NBC_STATEMENT_PASSWORD'),
        'private_key_path' => env('NBC_STATEMENT_PRIVATE_KEY_PATH', storage_path('keys/private.pem')),
        'public_key_path' => env('NBC_STATEMENT_PUBLIC_KEY_PATH', storage_path('keys/public.pem')),
        'timeout' => env('NBC_STATEMENT_TIMEOUT', 30),
        'verify_ssl' => env('NBC_STATEMENT_VERIFY_SSL', true),
        // Note: Account number is fetched from bank_accounts table (id=1), not configured here
    ],

    'billing' => [
        'url' => env('BILLING_SERVICE_URL', 'http://billing-service.test'),
    ],

    'sms' => [
        'enabled' => env('SMS_ENABLED', true),
        'api_url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
    ],

    'nbc_sms' => [
        'base_url' => env('NBC_SMS_BASE_URL', 'https://sms-engine.tz.af.absa.local'),
        'api_key' => env('NBC_SMS_API_KEY'),
        'channel_id' => env('NBC_SMS_CHANNEL_ID', 'KRWT43976'),
        'rate_limit' => env('NBC_SMS_RATE_LIMIT', 100),
        'rate_limit_window' => env('NBC_SMS_RATE_LIMIT_WINDOW', 3600),
        'max_retries' => env('NBC_SMS_MAX_RETRIES', 3),
        'retry_delay' => env('NBC_SMS_RETRY_DELAY', 60),
    ],


    'nbc_gateway' => [
        'base_url' => env('NBC_GATEWAY_BASE_URL'),
        'authorization' => env('NBC_GATEWAY_AUTHORIZATION'),
        'channel_id' => env('NBC_GATEWAY_CHANNEL_ID'),
        'channel_name' => env('NBC_GATEWAY_CHANNEL_NAME'),
        'verify_ssl' => env('NBC_GATEWAY_VERIFY_SSL', true),
        'endpoints' => [
            'billquery' => '/api/nbc-sg/v2/billquery',
            'bill_pay' => '/api/nbc-sg/v2/bill-pay',
            'status_check' => '/api/nbc-sg/v2/status-check',
            'luku_customer_info' => '/api/nbc-sg/v2/customerInfo',
            'luku_pay' => '/api/nbc-sg/v2/luku-pay',
            'luku_status_check' => '/api/nbc-sg/v2/status-check-luku',
        ],
    ],
    'luku_gateway' => [
        'base_url' => env('LUKU_GATEWAY_BASE_URL'),
        'channel_id' => env('LUKU_GATEWAY_CHANNEL_ID'),
        'channel_name' => env('LUKU_GATEWAY_CHANNEL_NAME'),
        'api_token' => env('LUKU_GATEWAY_API_TOKEN'),
        'status_check_url' => env('LUKU_GATEWAY_STATUS_CHECK_URL'),
        'ssl' => [
            'verify' => env('LUKU_GATEWAY_VERIFY_SSL', true),
            'cert_path' => storage_path('app/keys/public_key.pem'),
            'key_path' => storage_path('app/keys/private_key.pem'),
            'ca_path' => storage_path('app/keys/public_key.pem'), // Using public key as CA cert for now
        ],
        'credit_account' => env('LUKU_GATEWAY_CREDIT_ACCOUNT', '012202001486'),
    ],

    'gepg' => [
        'base_url' => env('GEPG_BASE_URL'),
        'authorization' => env('GEPG_AUTHORIZATION'),
    ],

    'account_details' => [
        'base_url' => env('ACCOUNT_DETAILS_BASE_URL', 'https://api.example.com'),
        'api_key' => env('ACCOUNT_DETAILS_API_KEY'),
        'private_key_path' => env('ACCOUNT_DETAILS_PRIVATE_KEY_PATH', storage_path('keys/private.pem')),
        'channel_name' => env('ACCOUNT_DETAILS_CHANNEL_NAME', 'NBC_SACCOS'),
        'channel_code' => env('ACCOUNT_DETAILS_CHANNEL_CODE', 'NBC001'),
        'timeout' => env('ACCOUNT_DETAILS_TIMEOUT', 30),
    ],

    // AI Service Providers
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'llama3-8b-8192'),
        'timeout' => env('GROQ_TIMEOUT', 30),
        'rate_limit' => env('GROQ_RATE_LIMIT', 1000),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
        'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-3.5-turbo'),
        'timeout' => env('OPENAI_TIMEOUT', 60),
        'rate_limit' => env('OPENAI_RATE_LIMIT', 3000),
    ],

    'together' => [
        'api_key' => env('TOGETHER_API_KEY'),
        'url' => env('TOGETHER_API_URL', 'https://api.together.xyz/v1/chat/completions'),
        'default_model' => env('TOGETHER_DEFAULT_MODEL', 'meta-llama/Llama-2-70b-chat-hf'),
        'timeout' => env('TOGETHER_TIMEOUT', 45),
        'rate_limit' => env('TOGETHER_RATE_LIMIT', 500),
    ],
    
    'payment_gateway' => [
        'base_url' => env('PAYMENT_GATEWAY_BASE_URL', 'http://172.240.241.188'),
        'frontend_url' => env('PAYMENT_GATEWAY_FRONTEND_URL', env('PAYMENT_GATEWAY_BASE_URL', 'http://172.240.241.188')),
        'api_key' => env('PAYMENT_GATEWAY_API_KEY', 'sample_client_key_ABC123DEF456'),
        'api_secret' => env('PAYMENT_GATEWAY_API_SECRET', 'sample_client_secret_XYZ789GHI012'),
        'timeout' => env('PAYMENT_GATEWAY_TIMEOUT', 30),
    ],
    
    'payment_link' => [
        'url' => env('PAYMENT_LINK_API_URL', 'http://172.240.241.188/api/payment-links/generate-universal'),
        'api_key' => env('PAYMENT_LINK_API_KEY'),
        'api_secret' => env('PAYMENT_LINK_API_SECRET'),
        'timeout' => env('PAYMENT_LINK_TIMEOUT', 30),
    ],

    /*
    Reseller API Service Configuration for domain management
    */

    'reseller' => [
        'api_url' => env('RESELLER_API_URL'),
        'api_key' => env('RESELLER_API_KEY'),
        'timeout' => env('RESELLER_TIMEOUT', 30),
    ],

];
