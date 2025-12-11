<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for SMS notifications
    | in the application.
    |
    */

    // SMS Provider Settings
    'provider' => env('SMS_PROVIDER', 'twilio'),

    // Default sender name for SMS messages
    'sender_name' => env('SMS_SENDER_NAME', 'NBCSACCOS'),

    // API Key for SMS provider
    'api_key' => env('SMS_API_KEY', ''),

    // API Secret for SMS provider
    'api_secret' => env('SMS_API_SECRET', ''),

    // Default country code for phone numbers
    'default_country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '255'),

    // Whether SMS notifications are enabled
    'enabled' => env('SMS_ENABLED', false),

    // Maximum length of SMS message
    'max_length' => env('SMS_MAX_LENGTH', 160),

    // Retry settings for failed SMS
    'retry_attempts' => env('SMS_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SMS_RETRY_DELAY', 60), // seconds

    // Queue settings for SMS
    'queue' => env('SMS_QUEUE', 'sms'),

    // Logging settings
    'log_enabled' => env('SMS_LOG_ENABLED', true),
    'log_channel' => env('SMS_LOG_CHANNEL', 'sms'),

    // Rate limiting
    'rate_limit' => [
        'enabled' => env('SMS_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('SMS_RATE_LIMIT_MAX_ATTEMPTS', 100),
        'decay_minutes' => env('SMS_RATE_LIMIT_DECAY_MINUTES', 60),
    ],

    // Templates for different types of SMS
    'templates' => [
        'share_purchase' => 'You have successfully purchased {shares} shares of {product} at {price} TZS per share.',
        'share_sale' => 'You have successfully sold {shares} shares of {product} at {price} TZS per share.',
        'dividend_payment' => 'A dividend of {rate}% has been declared for {year}. Your payment of {amount} TZS will be processed soon.',
        'transaction_approval' => 'Your {type} transaction of {shares} shares has been approved.',
        'transaction_rejection' => 'Your {type} transaction of {shares} shares has been rejected.',
        'balance_adjustment' => 'Your share account balance has been {action} by {shares} shares (Total: {amount} TZS).',
    ],
]; 