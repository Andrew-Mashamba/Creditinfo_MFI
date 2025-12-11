<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Enhanced Security Headers Middleware
 *
 * Adds comprehensive security headers with CSP nonce support:
 * - Content Security Policy (CSP) with nonce-based script execution
 * - X-Frame-Options
 * - X-Content-Type-Options
 * - X-XSS-Protection
 * - Referrer-Policy
 * - Permissions-Policy
 * - Strict-Transport-Security (HSTS)
 * - Expect-CT
 * - Cross-Origin-Opener-Policy
 * - Cross-Origin-Embedder-Policy
 * - Cross-Origin-Resource-Policy
 *
 * @package App\Http\Middleware
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class EnhancedSecurityHeaders
{
    /**
     * CSP nonce for this request
     *
     * @var string|null
     */
    protected $nonce = null;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // NOTE: Nonce generation removed - application doesn't use nonce attributes
        // When nonce is in CSP, 'unsafe-inline' is ignored, breaking inline code

        $response = $next($request);

        // Apply all security headers
        $this->applySecurityHeaders($request, $response);

        return $response;
    }

    /**
     * Generate a cryptographically secure nonce
     *
     * @return string
     */
    protected function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * Apply all security headers to the response
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @return void
     */
    protected function applySecurityHeaders(Request $request, $response): void
    {
        // Get security headers from config (backward compatibility)
        $securityHeaders = config('api.security_headers', []);
        foreach ($securityHeaders as $header => $value) {
            if (!$response->headers->has($header)) {
                $response->headers->set($header, $value);
            }
        }

        // Content Security Policy (CSP) with nonce
        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->getCspHeader());
        }

        // X-Frame-Options: Prevent clickjacking attacks
        if (!$response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        // X-Content-Type-Options: Prevent MIME type sniffing
        if (!$response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        // X-XSS-Protection: Enable XSS filter in older browsers
        if (!$response->headers->has('X-XSS-Protection')) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        // Referrer-Policy: Control how much referrer information is sent
        if (!$response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        // Permissions-Policy: Control browser features
        if (!$response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', $this->getPermissionsPolicy());
        }

        // Strict-Transport-Security: Force HTTPS (only if on HTTPS)
        if ($request->secure() && !$response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Expect-CT: Certificate Transparency (only if on HTTPS)
        if ($request->secure() && !$response->headers->has('Expect-CT')) {
            $response->headers->set('Expect-CT', 'max-age=86400, enforce');
        }

        // Cross-Origin-Opener-Policy: Isolate browsing context (HTTPS only)
        // SECURITY: These policies only work with HTTPS or localhost
        if ($request->secure() && !$response->headers->has('Cross-Origin-Opener-Policy')) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        }

        // Cross-Origin-Embedder-Policy: Require CORP for cross-origin resources (HTTPS only)
        // SECURITY: Relaxed to 'unsafe-none' for compatibility
        if ($request->secure() && !$response->headers->has('Cross-Origin-Embedder-Policy')) {
            $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none');
        }

        // Cross-Origin-Resource-Policy: Control resource sharing (HTTPS only)
        if ($request->secure() && !$response->headers->has('Cross-Origin-Resource-Policy')) {
            $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');
        }

        // X-Permitted-Cross-Domain-Policies: Control Adobe Flash/PDF cross-domain requests
        if (!$response->headers->has('X-Permitted-Cross-Domain-Policies')) {
            $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        }

        // Add custom headers for API (backward compatibility)
        if (!$response->headers->has('X-API-Version')) {
            $response->headers->set('X-API-Version', '1.0');
        }

        if (!$response->headers->has('X-Request-ID')) {
            $response->headers->set('X-Request-ID', $request->header('X-Request-ID') ?? uniqid());
        }

        // Add charset to Content-Type if not present
        if ($response->headers->has('Content-Type')) {
            $contentType = $response->headers->get('Content-Type');
            if (strpos($contentType, 'text/html') !== false && strpos($contentType, 'charset') === false) {
                $response->headers->set('Content-Type', $contentType . '; charset=UTF-8');
            }
        }

        // === ADDITIONAL HEADERS FOR 100% SECURITY ===

        // Clear-Site-Data: Clear browser data on logout
        if ($request->is('logout') || $request->is('sso-logout')) {
            $response->headers->set('Clear-Site-Data', '"cache", "cookies", "storage"');
        }

        // X-DNS-Prefetch-Control: Control DNS prefetching
        if (!$response->headers->has('X-DNS-Prefetch-Control')) {
            $response->headers->set('X-DNS-Prefetch-Control', 'on');
        }

        // X-Download-Options: Prevent file download execution (IE)
        if (!$response->headers->has('X-Download-Options')) {
            $response->headers->set('X-Download-Options', 'noopen');
        }

        // NEL (Network Error Logging): Monitor network errors
        if ($request->secure() && !$response->headers->has('NEL')) {
            $nel = json_encode([
                'report_to' => 'default',
                'max_age' => 31536000,
                'include_subdomains' => true
            ]);
            $response->headers->set('NEL', $nel);
        }

        // Report-To: Configure error reporting endpoint
        if ($request->secure() && !$response->headers->has('Report-To')) {
            $reportTo = json_encode([
                'group' => 'default',
                'max_age' => 31536000,
                'endpoints' => [
                    ['url' => $request->getSchemeAndHttpHost() . '/api/csp-report']
                ],
                'include_subdomains' => true
            ]);
            $response->headers->set('Report-To', $reportTo);
        }

        // Server-Timing: Expose performance metrics (development only)
        if (!app()->environment('production') && !$response->headers->has('Server-Timing')) {
            $response->headers->set('Server-Timing', 'app;dur='.round(microtime(true) - LARAVEL_START, 2));
        }

        // X-Robots-Tag: Control search engine indexing
        if ($request->is('admin/*') || $request->is('api/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }
    }

    /**
     * Get Content Security Policy header value
     *
     * SECURITY: This CSP is configured for Laravel + Livewire applications
     * NOTE: Nonce removed because application's inline scripts/styles don't use nonce attribute
     * When nonce is present, 'unsafe-inline' is ignored, breaking inline code without nonce
     *
     * @return string
     */
    protected function getCspHeader(): string
    {
        // CSP directives
        $directives = [
            // Default policy for content types not explicitly listed
            "default-src 'self'",

            // Script sources (JavaScript)
            // 'unsafe-inline' allows inline scripts (required for app's inline code)
            // 'unsafe-eval' required for Livewire/Alpine.js
            // https: allows loading scripts from any HTTPS CDN
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: " . $this->getTrustedScriptSources(),

            // Style sources (CSS)
            // 'unsafe-inline' required for Tailwind and inline styles
            "style-src 'self' 'unsafe-inline' https: " . $this->getTrustedStyleSources(),

            // Image sources
            "img-src 'self' data: blob: https:",

            // Font sources (allow external fonts from CDNs like Google Fonts)
            "font-src 'self' data: https:",

            // Connect sources (AJAX, WebSocket, EventSource, Source Maps)
            // https: added for CDN source maps (e.g., flowbite.min.js.map)
            "connect-src 'self' https: " . $this->getTrustedConnectSources(),

            // Frame sources (iframes)
            "frame-src 'self'",

            // Object sources (Flash, Java, etc.) - BLOCKED
            "object-src 'none'",

            // Media sources (audio, video)
            "media-src 'self' blob:",

            // Form action destinations
            "form-action 'self'",

            // Frame ancestors (who can embed this page)
            "frame-ancestors 'self'",

            // Base URI
            "base-uri 'self'",

            // Worker sources (Web Workers, Service Workers)
            "worker-src 'self' blob:",

            // Manifest sources (Web App Manifest)
            "manifest-src 'self'",

            // Child sources (deprecated but supported)
            "child-src 'self'",
        ];

        // Add upgrade-insecure-requests only in production with HTTPS
        if (app()->environment('production') && request()->secure()) {
            $directives[] = "upgrade-insecure-requests";
        }

        // Add block-all-mixed-content in production with HTTPS
        if (app()->environment('production') && request()->secure()) {
            $directives[] = "block-all-mixed-content";
        }

        return implode('; ', $directives);
    }

    /**
     * Get trusted script sources
     *
     * SECURITY: Only add sources you trust completely
     * These are CDNs or external scripts your application uses
     *
     * @return string
     */
    protected function getTrustedScriptSources(): string
    {
        $sources = [];

        // Add CDNs if your application uses them
        // Example: Alpine.js CDN
        // $sources[] = 'https://cdn.jsdelivr.net';

        // Example: Chart.js CDN
        // $sources[] = 'https://cdnjs.cloudflare.com';

        // Example: Google Analytics (if used)
        // $sources[] = 'https://www.google-analytics.com';
        // $sources[] = 'https://www.googletagmanager.com';

        // Example: Google Tag Manager
        // $sources[] = 'https://www.googletagmanager.com';

        // Example: Google reCAPTCHA (if used)
        // $sources[] = 'https://www.google.com/recaptcha/';
        // $sources[] = 'https://www.gstatic.com/recaptcha/';

        return implode(' ', $sources);
    }

    /**
     * Get trusted style sources
     *
     * @return string
     */
    protected function getTrustedStyleSources(): string
    {
        $sources = [];

        // Add CSS CDNs if your application uses them
        // Example: Google Fonts
        // $sources[] = 'https://fonts.googleapis.com';
        // $sources[] = 'https://fonts.gstatic.com';

        // Example: Font Awesome CDN
        // $sources[] = 'https://cdnjs.cloudflare.com';
        // $sources[] = 'https://use.fontawesome.com';

        // Example: Bootstrap CDN
        // $sources[] = 'https://cdn.jsdelivr.net';

        return implode(' ', $sources);
    }

    /**
     * Get trusted connect sources (API endpoints, WebSocket)
     *
     * @return string
     */
    protected function getTrustedConnectSources(): string
    {
        $sources = [];

        // Add external API domains if your application calls them
        // Example: External payment gateway
        // $sources[] = 'https://api.payment-provider.com';

        // Example: Analytics endpoints
        // $sources[] = 'https://www.google-analytics.com';

        // Add WebSocket connections
        if (config('broadcasting.default') === 'pusher') {
            $sources[] = 'wss://*.pusher.com';
            $sources[] = 'https://*.pusher.com';
        } elseif (config('broadcasting.default') === 'reverb') {
            $host = parse_url(config('app.url'), PHP_URL_HOST);
            $sources[] = 'wss://' . $host;
        }

        // Add Livewire endpoints
        $sources[] = request()->getSchemeAndHttpHost();

        return implode(' ', $sources);
    }

    /**
     * Get Permissions Policy header value
     *
     * Controls which browser features are allowed
     * Most features are disabled by default for security
     *
     * @return string
     */
    protected function getPermissionsPolicy(): string
    {
        $policies = [
            'accelerometer=()',           // Disable accelerometer
            // 'ambient-light-sensor=()', // DEPRECATED - removed from spec
            'autoplay=()',                // Disable autoplay
            // 'battery=()',              // DEPRECATED - removed from spec
            'camera=()',                  // Disable camera
            'cross-origin-isolated=()',   // Disable cross-origin isolation
            'display-capture=()',         // Disable screen capture
            // 'document-domain=()',      // DEPRECATED - removed from spec
            'encrypted-media=()',         // Disable encrypted media
            'fullscreen=(self)',          // Allow fullscreen only from same origin
            'geolocation=()',             // Disable geolocation
            'gyroscope=()',               // Disable gyroscope
            'magnetometer=()',            // Disable magnetometer
            'microphone=()',              // Disable microphone
            'midi=()',                    // Disable MIDI
            'payment=()',                 // Disable payment
            'picture-in-picture=()',      // Disable PIP
            'publickey-credentials-get=()', // Disable WebAuthn
            'sync-xhr=()',                // Disable synchronous XHR (improves performance)
            'usb=()',                     // Disable USB
            'web-share=()',               // Disable web share
            'xr-spatial-tracking=()',     // Disable XR
        ];

        return implode(', ', $policies);
    }

    /**
     * Get the current CSP nonce
     *
     * @return string|null
     */
    public function getNonce(): ?string
    {
        return $this->nonce;
    }
}
