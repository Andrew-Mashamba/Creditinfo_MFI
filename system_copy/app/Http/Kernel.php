<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\IpBlacklist::class, // Block blacklisted IPs
        \App\Http\Middleware\TrackRateLimitViolations::class, // Track rate limit violations
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\EnhancedSecurityHeaders::class,
            \App\Http\Middleware\XssProtection::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'menu.permission' => \App\Http\Middleware\CheckMenuPermission::class,
        'api.key' => \App\Http\Middleware\ApiKeyAuthentication::class,
        'ip.whitelist' => \App\Http\Middleware\IpWhitelist::class,
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        'enhanced.security' => \App\Http\Middleware\EnhancedSecurityHeaders::class,
        'xss.protection' => \App\Http\Middleware\XssProtection::class,
        'fraud.detection' => \App\Http\Middleware\FraudDetectionMiddleware::class,
        'webhook.signature' => \App\Http\Middleware\VerifyWebhookSignature::class,
        'validate.upload' => \App\Http\Middleware\ValidateFileUpload::class,
        'ip.blacklist' => \App\Http\Middleware\IpBlacklist::class,
        'captcha' => \App\Http\Middleware\VerifyCaptcha::class,
        'api.headers' => \App\Http\Middleware\ValidateApiHeaders::class,
        'api.scopes' => \App\Http\Middleware\ValidateApiScopes::class,
    ];

    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'menu.action' => \App\Http\Middleware\CheckMenuAction::class,
        'enhanced.security' => \App\Http\Middleware\EnhancedSecurityHeaders::class,
        'xss.protection' => \App\Http\Middleware\XssProtection::class,
        'webhook.signature' => \App\Http\Middleware\VerifyWebhookSignature::class,
        'ip.blacklist' => \App\Http\Middleware\IpBlacklist::class,
        'captcha' => \App\Http\Middleware\VerifyCaptcha::class,
        'api.headers' => \App\Http\Middleware\ValidateApiHeaders::class,
        'api.scopes' => \App\Http\Middleware\ValidateApiScopes::class,
    ];
}
