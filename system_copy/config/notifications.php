<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the notification system
    | including retry logic, delays, and other notification-related settings.
    |
    */

    // Maximum number of retry attempts for failed notifications
    'max_retries' => env('NOTIFICATION_MAX_RETRIES', 3),

    // Retry delays in seconds (for each retry attempt)
    'retry_delays' => [
        env('NOTIFICATION_RETRY_DELAY_1', 60),   // 1 minute
        env('NOTIFICATION_RETRY_DELAY_2', 300),  // 5 minutes
        env('NOTIFICATION_RETRY_DELAY_3', 900),  // 15 minutes
    ],

    // Default notification channels
    'default_channels' => [
        'email' => env('NOTIFICATION_EMAIL_ENABLED', true),
        'sms' => env('NOTIFICATION_SMS_ENABLED', true),
    ],

    // Notification queue settings
    'queue' => [
        'name' => env('NOTIFICATION_QUEUE_NAME', 'notifications'),
        'connection' => env('NOTIFICATION_QUEUE_CONNECTION', 'default'),
    ],

    // Rate limiting settings
    'rate_limit' => [
        'enabled' => env('NOTIFICATION_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('NOTIFICATION_RATE_LIMIT_MAX_ATTEMPTS', 100),
        'decay_minutes' => env('NOTIFICATION_RATE_LIMIT_DECAY_MINUTES', 60),
    ],

    // Logging settings
    'logging' => [
        'enabled' => env('NOTIFICATION_LOGGING_ENABLED', true),
        'channel' => env('NOTIFICATION_LOG_CHANNEL', 'notifications'),
        'level' => env('NOTIFICATION_LOG_LEVEL', 'info'),
    ],

    // Cleanup settings
    'cleanup' => [
        'enabled' => env('NOTIFICATION_CLEANUP_ENABLED', true),
        'retention_days' => env('NOTIFICATION_RETENTION_DAYS', 90),
        'schedule' => env('NOTIFICATION_CLEANUP_SCHEDULE', 'daily'),
    ],

    // Bulk notification settings
    'bulk' => [
        'max_recipients' => env('NOTIFICATION_BULK_MAX_RECIPIENTS', 250),
        'batch_size' => env('NOTIFICATION_BULK_BATCH_SIZE', 50),
        'delay_between_batches' => env('NOTIFICATION_BULK_DELAY', 5), // seconds
    ],

    // Template settings
    'templates' => [
        'default_language' => env('NOTIFICATION_DEFAULT_LANGUAGE', 'English'),
        'fallback_language' => env('NOTIFICATION_FALLBACK_LANGUAGE', 'English'),
    ],

    // Provider-specific settings
    'providers' => [
        'nbc_sms' => [
            'enabled' => env('NBC_SMS_ENABLED', true),
            'base_url' => env('NBC_SMS_BASE_URL'),
            'api_key' => env('NBC_SMS_API_KEY'),
            'channel_id' => env('NBC_SMS_CHANNEL_ID', '101_SYSTEM'),
            'timeout' => env('NBC_SMS_TIMEOUT', 30),
        ],
        'email' => [
            'enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
            'from_address' => env('MAIL_FROM_ADDRESS'),
            'from_name' => env('MAIL_FROM_NAME', 'NBC SACCOS'),
        ],
    ],
]; 