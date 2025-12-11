<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * SECURITY NOTE: Only exclude endpoints that:
     * 1. Use stateless authentication (API tokens, JWT, OAuth)
     * 2. Receive external webhooks/callbacks with signature verification
     * 3. Are SSO/external authentication endpoints
     *
     * @var array<int, string>
     */
    protected $except = [
        // AI Agent endpoints (use session-based auth but need streaming)
        'ai/process',
        'ai/stream/*',
        'ai-agent',

        // API endpoints (use stateless token authentication)
        'api/*',

        // SSO authentication endpoints (external authentication system)
        'auth',           // SSO authentication endpoint
        'sso-logout',     // SSO logout endpoint

        // Payment gateway webhooks (use signature verification)
        'api/payment-notification',
        'api/gepg-callback',

        // Testing endpoints (should be removed in production)
        // TODO: Remove test-ai endpoints before production deployment
        'test-ai/*',
        'test-ai/process',
        'test-ai/stream/*',
    ];
}
