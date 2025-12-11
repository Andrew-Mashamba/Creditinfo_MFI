<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CAPTCHA settings for high-risk operations
    |
    */

    // Enable/disable CAPTCHA globally
    'enabled' => env('CAPTCHA_ENABLED', false),

    // Skip CAPTCHA in local development
    'skip_local' => env('CAPTCHA_SKIP_LOCAL', true),

    // CAPTCHA provider: 'recaptcha', 'recaptcha_v2', 'recaptcha_v3', 'hcaptcha'
    'provider' => env('CAPTCHA_PROVIDER', 'recaptcha'),

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA Configuration
    |--------------------------------------------------------------------------
    */

    'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'recaptcha_secret' => env('RECAPTCHA_SECRET_KEY', ''),

    // reCAPTCHA v3 score threshold (0.0 to 1.0)
    // Lower scores indicate likely bots, higher scores indicate likely humans
    'recaptcha_v3_threshold' => env('RECAPTCHA_V3_THRESHOLD', 0.5),

    /*
    |--------------------------------------------------------------------------
    | hCaptcha Configuration
    |--------------------------------------------------------------------------
    */

    'hcaptcha_site_key' => env('HCAPTCHA_SITE_KEY', ''),
    'hcaptcha_secret' => env('HCAPTCHA_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting for CAPTCHA Failures
    |--------------------------------------------------------------------------
    */

    // Maximum failed CAPTCHA attempts before temporary block
    'max_failed_attempts' => env('CAPTCHA_MAX_FAILED_ATTEMPTS', 5),

    // Block duration in minutes after excessive failures
    'block_duration' => env('CAPTCHA_BLOCK_DURATION', 60),

    /*
    |--------------------------------------------------------------------------
    | High-Risk Operations Requiring CAPTCHA
    |--------------------------------------------------------------------------
    |
    | Define which operations should require CAPTCHA verification
    |
    */

    'protected_routes' => [
        // Authentication
        'login' => env('CAPTCHA_REQUIRE_LOGIN', false),
        'register' => env('CAPTCHA_REQUIRE_REGISTER', true),
        'password_reset' => env('CAPTCHA_REQUIRE_PASSWORD_RESET', true),

        // Financial Operations
        'payment' => env('CAPTCHA_REQUIRE_PAYMENT', false),
        'transfer' => env('CAPTCHA_REQUIRE_TRANSFER', false),
        'withdrawal' => env('CAPTCHA_REQUIRE_WITHDRAWAL', false),

        // Account Operations
        'account_creation' => env('CAPTCHA_REQUIRE_ACCOUNT_CREATION', true),
        'profile_update' => env('CAPTCHA_REQUIRE_PROFILE_UPDATE', false),

        // Other High-Risk Operations
        'bulk_operations' => env('CAPTCHA_REQUIRE_BULK_OPERATIONS', true),
        'data_export' => env('CAPTCHA_REQUIRE_DATA_EXPORT', true),
    ],

];
