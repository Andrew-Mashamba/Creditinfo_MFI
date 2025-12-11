<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for API security features including
    | IP whitelisting, rate limiting, and authentication settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Allowed IP Addresses
    |--------------------------------------------------------------------------
    |
    | List of IP addresses or CIDR ranges that are allowed to access the API.
    | Leave empty to allow all IPs (not recommended for production).
    | Supports both single IPs and CIDR notation (e.g., 192.168.1.0/24)
    |
    */
    'allowed_ips' => env('API_ALLOWED_IPS', []),

    /*
    |--------------------------------------------------------------------------
    | Global Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Global rate limiting settings for all API endpoints.
    | These are applied in addition to per-key rate limits.
    |
    */
    'rate_limiting' => [
        'enabled' => env('API_RATE_LIMITING_ENABLED', true),
        'requests_per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
        'requests_per_hour' => env('API_RATE_LIMIT_PER_HOUR', 1000),
        'requests_per_day' => env('API_RATE_LIMIT_PER_DAY', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Key Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for API key authentication and management.
    |
    */
    'keys' => [
        'prefix' => env('API_KEY_PREFIX', 'sk_'),
        'length' => env('API_KEY_LENGTH', 32),
        'default_rate_limit' => env('API_DEFAULT_RATE_LIMIT', 1000),
        'default_expiry_days' => env('API_DEFAULT_EXPIRY_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Security headers to be added to API responses.
    |
    */
    'security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        // CSP removed - EnhancedSecurityHeaders middleware provides enhanced CSP with nonce support
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for API request logging and monitoring.
    |
    */
    'logging' => [
        'enabled' => env('API_LOGGING_ENABLED', true),
        'log_failed_attempts' => env('API_LOG_FAILED_ATTEMPTS', true),
        'log_successful_requests' => env('API_LOG_SUCCESSFUL_REQUESTS', false),
        'log_sensitive_data' => env('API_LOG_SENSITIVE_DATA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Cross-Origin Resource Sharing settings for API endpoints.
    |
    */
    'cors' => [
        'enabled' => env('API_CORS_ENABLED', false),
        'allowed_origins' => env('API_CORS_ALLOWED_ORIGINS', []),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
        'exposed_headers' => [],
        'max_age' => 86400,
        'supports_credentials' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Processing API Settings
    |--------------------------------------------------------------------------
    |
    | Specific settings for transaction processing endpoints.
    |
    */
    'transaction_processing' => [
        'max_amount' => env('API_MAX_TRANSACTION_AMOUNT', 1000000),
        'min_amount' => env('API_MIN_TRANSACTION_AMOUNT', 1),
        'allowed_currencies' => ['TZS', 'USD', 'EUR'],
        'require_idempotency' => env('API_REQUIRE_IDEMPOTENCY', true),
        'idempotency_window_hours' => env('API_IDEMPOTENCY_WINDOW', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerts
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring API usage and sending alerts.
    |
    */
    'monitoring' => [
        'alert_on_rate_limit_exceeded' => env('API_ALERT_ON_RATE_LIMIT', true),
        'alert_on_failed_authentication' => env('API_ALERT_ON_AUTH_FAILURE', true),
        'alert_on_suspicious_activity' => env('API_ALERT_ON_SUSPICIOUS_ACTIVITY', true),
        'suspicious_activity_threshold' => env('API_SUSPICIOUS_ACTIVITY_THRESHOLD', 10),
    ],
]; 