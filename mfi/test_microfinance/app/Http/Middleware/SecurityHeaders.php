<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Security Headers Middleware
 *
 * Adds comprehensive security headers to all responses:
 * - Content Security Policy (CSP)
 * - X-Frame-Options
 * - X-Content-Type-Options
 * - X-XSS-Protection
 * - Referrer-Policy
 * - Permissions-Policy
 * - Strict-Transport-Security (HSTS)
 *
 * @package App\Http\Middleware
 * @author MFI Management System Security Team
 * @date 2025-10-16
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Get security headers from config (backward compatibility)
        $securityHeaders = config('api.security_headers', []);
        foreach ($securityHeaders as $header => $value) {
            $response->headers->set($header, $value);
        }

        // Content Security Policy (CSP)
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
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Add custom headers for API (backward compatibility)
        if (!$response->headers->has('X-API-Version')) {
            $response->headers->set('X-API-Version', '1.0');
        }
        if (!$response->headers->has('X-Request-ID')) {
            $response->headers->set('X-Request-ID', $request->header('X-Request-ID') ?? uniqid());
        }

        return $response;
    }

    /**
     * Get Content Security Policy header value
     *
     * SECURITY: This CSP is configured for Laravel + Livewire + Alpine.js applications
     * Adjust these directives based on your specific application needs
     *
     * @return string
     */
    private function getCspHeader(): string
    {
        // CSP directives
        $directives = [
            // Default policy for content types not explicitly listed
            "default-src 'self'",

            // Script sources (JavaScript)
            // 'unsafe-inline' and 'unsafe-eval' required for Livewire and Alpine.js
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' " . $this->getTrustedScriptSources(),

            // Style sources (CSS)
            // 'unsafe-inline' required for Tailwind and inline styles
            "style-src 'self' 'unsafe-inline' " . $this->getTrustedStyleSources(),

            // Image sources
            "img-src 'self' data: blob: https:",

            // Font sources
            "font-src 'self' data:",

            // Connect sources (AJAX, WebSocket, EventSource)
            "connect-src 'self' " . $this->getTrustedConnectSources(),

            // Frame sources (iframes)
            "frame-src 'self'",

            // Object sources (Flash, Java, etc.) - BLOCKED
            "object-src 'none'",

            // Media sources (audio, video)
            "media-src 'self' blob:",

            // Form action destinations (allow external logout redirect)
            "form-action 'self' http://127.0.0.1:8003",

            // Frame ancestors (who can embed this page)
            "frame-ancestors 'self'",

            // Base URI
            "base-uri 'self'",
        ];

        // Add upgrade-insecure-requests only in production with HTTPS
        if (app()->environment('production') && request()->secure()) {
            $directives[] = "upgrade-insecure-requests";
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
    private function getTrustedScriptSources(): string
    {
        $sources = [];

        // Add CDNs if your application uses them
        // Example: Alpine.js CDN
        // $sources[] = 'https://cdn.jsdelivr.net';

        // Example: Chart.js CDN
        // $sources[] = 'https://cdn.jsdelivr.net';

        // Example: Google Analytics
        // $sources[] = 'https://www.google-analytics.com';
        // $sources[] = 'https://www.googletagmanager.com';

        return implode(' ', $sources);
    }

    /**
     * Get trusted style sources
     *
     * @return string
     */
    private function getTrustedStyleSources(): string
    {
        $sources = [];

        // Add CSS CDNs if your application uses them
        // Example: Google Fonts
        // $sources[] = 'https://fonts.googleapis.com';

        // Example: Font Awesome CDN
        // $sources[] = 'https://cdnjs.cloudflare.com';

        return implode(' ', $sources);
    }

    /**
     * Get trusted connect sources (API endpoints, WebSocket)
     *
     * @return string
     */
    private function getTrustedConnectSources(): string
    {
        $sources = [];

        // Add external API domains if your application calls them
        // Example: External payment gateway
        // $sources[] = 'https://api.payment-provider.com';

        // Add WebSocket connections
        if (config('broadcasting.default') === 'pusher') {
            $sources[] = 'wss://*.pusher.com';
        } elseif (config('broadcasting.default') === 'reverb') {
            $sources[] = 'wss://' . parse_url(config('app.url'), PHP_URL_HOST);
        }

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
    private function getPermissionsPolicy(): string
    {
        $policies = [
            'accelerometer=()',           // Disable accelerometer
            'ambient-light-sensor=()',    // Disable light sensor
            'autoplay=()',                // Disable autoplay
            'battery=()',                 // Disable battery status
            'camera=()',                  // Disable camera
            'cross-origin-isolated=()',   // Disable cross-origin isolation
            'display-capture=()',         // Disable screen capture
            'document-domain=()',         // Disable document.domain
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
}
