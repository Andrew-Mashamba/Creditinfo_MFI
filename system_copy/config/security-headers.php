<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Content Security Policy directives
    |
    */

    'csp' => [
        'enabled' => env('CSP_ENABLED', true),

        // Trusted script sources (CDNs, external scripts)
        'script_src' => [
            // Example: 'https://cdn.jsdelivr.net',
            // Example: 'https://www.google-analytics.com',
        ],

        // Trusted style sources (CSS CDNs)
        'style_src' => [
            // Example: 'https://fonts.googleapis.com',
            // Example: 'https://cdnjs.cloudflare.com',
        ],

        // Trusted connect sources (APIs, WebSockets)
        'connect_src' => [
            // Example: 'https://api.external-service.com',
        ],

        // Allow unsafe-eval (required for some frameworks like Livewire)
        'allow_unsafe_eval' => env('CSP_ALLOW_UNSAFE_EVAL', true),

        // Allow unsafe-inline for styles (required for Tailwind/inline styles)
        'allow_unsafe_inline_styles' => env('CSP_ALLOW_UNSAFE_INLINE_STYLES', true),

        // Upgrade insecure requests in production
        'upgrade_insecure_requests' => env('CSP_UPGRADE_INSECURE', true),

        // Block mixed content in production
        'block_mixed_content' => env('CSP_BLOCK_MIXED_CONTENT', true),

        // Report-Only mode (for testing CSP without blocking)
        'report_only' => env('CSP_REPORT_ONLY', false),

        // CSP violation reporting endpoint
        'report_uri' => env('CSP_REPORT_URI', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Transport Security (HSTS) Configuration
    |--------------------------------------------------------------------------
    |
    | Force HTTPS connections
    |
    */

    'hsts' => [
        'enabled' => env('HSTS_ENABLED', true),

        // Maximum age in seconds (1 year = 31536000)
        'max_age' => env('HSTS_MAX_AGE', 31536000),

        // Include subdomains
        'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),

        // Preload list (requires manual submission to browsers)
        'preload' => env('HSTS_PRELOAD', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | X-Frame-Options Configuration
    |--------------------------------------------------------------------------
    |
    | Prevent clickjacking attacks
    |
    */

    'x_frame_options' => [
        'enabled' => env('X_FRAME_OPTIONS_ENABLED', true),

        // Options: DENY, SAMEORIGIN, ALLOW-FROM https://example.com
        'value' => env('X_FRAME_OPTIONS', 'SAMEORIGIN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | X-Content-Type-Options Configuration
    |--------------------------------------------------------------------------
    |
    | Prevent MIME type sniffing
    |
    */

    'x_content_type_options' => [
        'enabled' => env('X_CONTENT_TYPE_OPTIONS_ENABLED', true),
        'value' => 'nosniff',
    ],

    /*
    |--------------------------------------------------------------------------
    | Referrer-Policy Configuration
    |--------------------------------------------------------------------------
    |
    | Control referrer information leakage
    |
    */

    'referrer_policy' => [
        'enabled' => env('REFERRER_POLICY_ENABLED', true),

        // Options:
        // - no-referrer: Never send referrer
        // - no-referrer-when-downgrade: Send referrer except HTTPS -> HTTP
        // - origin: Send only origin (no path)
        // - origin-when-cross-origin: Full URL for same-origin, origin only for cross-origin
        // - same-origin: Send referrer only for same-origin
        // - strict-origin: Send origin except HTTPS -> HTTP
        // - strict-origin-when-cross-origin: Full for same-origin, origin for cross-origin HTTPS
        // - unsafe-url: Always send full URL (not recommended)
        'value' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions-Policy Configuration
    |--------------------------------------------------------------------------
    |
    | Control which browser features are allowed
    |
    */

    'permissions_policy' => [
        'enabled' => env('PERMISSIONS_POLICY_ENABLED', true),

        // Feature policies
        // Options: () = disabled, (self) = same origin only, (*) = allow all
        'features' => [
            'accelerometer' => '()',
            'ambient-light-sensor' => '()',
            'autoplay' => '()',
            'battery' => '()',
            'camera' => '()',
            'cross-origin-isolated' => '()',
            'display-capture' => '()',
            'document-domain' => '()',
            'encrypted-media' => '()',
            'fullscreen' => '(self)',
            'geolocation' => '()',
            'gyroscope' => '()',
            'magnetometer' => '()',
            'microphone' => '()',
            'midi' => '()',
            'payment' => '()',
            'picture-in-picture' => '()',
            'publickey-credentials-get' => '()',
            'sync-xhr' => '()',
            'usb' => '()',
            'web-share' => '()',
            'xr-spatial-tracking' => '()',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Policies
    |--------------------------------------------------------------------------
    |
    | Control cross-origin resource sharing and isolation
    |
    */

    'cross_origin' => [
        // Cross-Origin-Opener-Policy
        // Options: unsafe-none, same-origin-allow-popups, same-origin
        'opener_policy' => env('COOP', 'same-origin'),

        // Cross-Origin-Embedder-Policy
        // Options: unsafe-none, require-corp, credentialless
        'embedder_policy' => env('COEP', 'require-corp'),

        // Cross-Origin-Resource-Policy
        // Options: same-site, same-origin, cross-origin
        'resource_policy' => env('CORP', 'same-origin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Security Headers
    |--------------------------------------------------------------------------
    */

    'additional' => [
        // X-XSS-Protection (legacy, but still useful for older browsers)
        'x_xss_protection' => env('X_XSS_PROTECTION', '1; mode=block'),

        // X-Permitted-Cross-Domain-Policies (Flash/PDF)
        'x_permitted_cross_domain_policies' => 'none',

        // Expect-CT (Certificate Transparency)
        'expect_ct' => [
            'enabled' => env('EXPECT_CT_ENABLED', true),
            'max_age' => 86400,
            'enforce' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development/Testing Overrides
    |--------------------------------------------------------------------------
    |
    | Relax certain restrictions in non-production environments
    |
    */

    'development' => [
        // Disable HSTS in local development (prevents HTTPS requirement)
        'disable_hsts_local' => env('DISABLE_HSTS_LOCAL', true),

        // Less strict CSP in development
        'relaxed_csp_local' => env('RELAXED_CSP_LOCAL', false),
    ],

];
