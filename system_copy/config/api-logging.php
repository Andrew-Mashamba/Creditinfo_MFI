<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for comprehensive API request/response logging
    |
    */

    'enabled' => env('API_LOGGING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'api' => env('API_LOG_CHANNEL', 'api'),
        'metrics' => env('API_METRICS_CHANNEL', 'metrics'),
        'errors' => env('API_ERROR_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'driver' => env('API_LOG_STORAGE', 'local'),
        'path' => 'api-logs',
        'retention_days' => env('API_LOG_RETENTION_DAYS', 30),
        'max_file_size_mb' => env('API_LOG_MAX_FILE_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'response_time_warning_ms' => env('API_RESPONSE_TIME_WARNING', 5000),
        'response_time_critical_ms' => env('API_RESPONSE_TIME_CRITICAL', 10000),
        'max_response_time_ms' => env('API_MAX_RESPONSE_TIME', 30000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'max_attempts' => env('API_RETRY_ATTEMPTS', 3),
        'backoff_multiplier' => env('API_RETRY_BACKOFF', 2),
        'max_backoff_seconds' => env('API_MAX_BACKOFF', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Privacy
    |--------------------------------------------------------------------------
    */
    'security' => [
        'sanitize_headers' => true,
        'sanitize_body' => true,
        'sensitive_headers' => [
            'authorization',
            'x-api-key',
            'api-key',
            'token',
            'secret',
            'password',
            'x-auth-token',
        ],
        'sensitive_fields' => [
            'password',
            'pin',
            'cvv',
            'card_number',
            'account_number',
            'secret',
            'token',
            'api_key',
            'private_key',
            'client_secret',
        ],
        'redacted_value' => '***REDACTED***',
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Alerts
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('API_MONITORING_ENABLED', true),
        'alert_on_failure' => env('API_ALERT_ON_FAILURE', true),
        'alert_on_slow_response' => env('API_ALERT_ON_SLOW', true),
        'alert_channels' => env('API_ALERT_CHANNELS', 'mail,slack'),
        'alert_recipients' => env('API_ALERT_RECIPIENTS', 'admin@example.com'),
        'metrics_enabled' => env('API_METRICS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service-Specific Configuration
    |--------------------------------------------------------------------------
    */
    'services' => [
        'bank_transaction' => [
            'enabled' => true,
            'base_url' => env('BANK_API_BASE_URL', 'https://api.bank.com'),
            'timeout' => env('BANK_API_TIMEOUT', 30),
            'api_key' => env('BANK_API_KEY'),
            'client_id' => env('BANK_API_CLIENT_ID'),
            'retry_attempts' => 3,
            'validation_rules' => [
                'max_amount' => 1000000000,
                'min_amount' => 1,
                'required_fields' => ['amount', 'currency', 'reference'],
            ],
        ],
        
        'gepg_gateway' => [
            'enabled' => true,
            'base_url' => env('GEPG_GATEWAY_URL'),
            'timeout' => env('GEPG_TIMEOUT', 45),
            'channel_id' => env('GEPG_CHANNEL_ID'),
            'channel_name' => env('GEPG_CHANNEL_NAME'),
            'retry_attempts' => 2,
            'verify_ssl' => env('GEPG_VERIFY_SSL', true),
            'validation_rules' => [
                'control_number_pattern' => '/^\d{12}$/',
                'required_fields' => ['control_number', 'amount'],
            ],
        ],
        
        'luku_gateway' => [
            'enabled' => true,
            'base_url' => env('LUKU_GATEWAY_BASE_URL'),
            'timeout' => env('LUKU_TIMEOUT', 30),
            'api_token' => env('LUKU_GATEWAY_API_TOKEN'),
            'retry_attempts' => 3,
            'verify_ssl' => env('LUKU_VERIFY_SSL', true),
            'validation_rules' => [
                'meter_number_pattern' => '/^\d{11}$/',
                'max_amount' => 500000,
                'min_amount' => 1000,
            ],
        ],
        
        'nbc_sms' => [
            'enabled' => true,
            'base_url' => env('NBC_SMS_BASE_URL'),
            'timeout' => env('NBC_SMS_TIMEOUT', 10),
            'api_key' => env('NBC_SMS_API_KEY'),
            'channel_id' => env('NBC_SMS_CHANNEL_ID'),
            'retry_attempts' => 2,
            'rate_limit' => env('NBC_SMS_RATE_LIMIT', 100),
            'validation_rules' => [
                'phone_pattern' => '/^255[67]\d{8}$/',
                'max_message_length' => 480,
            ],
        ],
        
        'ai_services' => [
            'groq' => [
                'enabled' => true,
                'base_url' => env('GROQ_BASE_URL'),
                'timeout' => env('GROQ_TIMEOUT', 30),
                'api_key' => env('GROQ_API_KEY'),
                'model' => env('GROQ_MODEL', 'llama3-8b-8192'),
                'retry_attempts' => 2,
            ],
            'openai' => [
                'enabled' => env('OPENAI_ENABLED', false),
                'base_url' => env('OPENAI_API_URL'),
                'timeout' => env('OPENAI_TIMEOUT', 60),
                'api_key' => env('OPENAI_API_KEY'),
                'model' => env('OPENAI_DEFAULT_MODEL', 'gpt-3.5-turbo'),
                'retry_attempts' => 2,
            ],
            'claude' => [
                'enabled' => env('CLAUDE_ENABLED', false),
                'base_url' => 'https://api.anthropic.com/v1',
                'timeout' => env('CLAUDE_TIMEOUT', 45),
                'api_key' => env('CLAUDE_API_KEY'),
                'retry_attempts' => 2,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'strict_mode' => env('API_STRICT_VALIDATION', false),
        'log_validation_failures' => true,
        'fail_on_validation_error' => false,
        'common_required_fields' => [
            'status',
            'message',
        ],
        'success_indicators' => [
            'status' => ['success', 'ok', 'completed'],
            'code' => [200, 201, 202],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting & Analytics
    |--------------------------------------------------------------------------
    */
    'reporting' => [
        'daily_summary' => env('API_DAILY_SUMMARY', true),
        'weekly_report' => env('API_WEEKLY_REPORT', true),
        'monthly_report' => env('API_MONTHLY_REPORT', true),
        'send_reports_to' => env('API_REPORT_RECIPIENTS', 'admin@example.com'),
        'include_metrics' => true,
        'include_errors' => true,
        'include_slow_requests' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled' => env('API_DASHBOARD_ENABLED', true),
        'route_prefix' => env('API_DASHBOARD_PREFIX', 'api-monitor'),
        'middleware' => ['web', 'auth', 'admin'],
        'refresh_interval_seconds' => 30,
        'chart_data_points' => 100,
        'show_sensitive_data' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing & Development
    |--------------------------------------------------------------------------
    */
    'testing' => [
        'mock_responses' => env('API_MOCK_RESPONSES', false),
        'log_mock_calls' => env('API_LOG_MOCK_CALLS', true),
        'simulate_failures' => env('API_SIMULATE_FAILURES', false),
        'failure_rate' => env('API_FAILURE_RATE', 0.1),
        'simulate_slow_responses' => env('API_SIMULATE_SLOW', false),
        'slow_response_delay_ms' => env('API_SLOW_DELAY', 5000),
    ],
];