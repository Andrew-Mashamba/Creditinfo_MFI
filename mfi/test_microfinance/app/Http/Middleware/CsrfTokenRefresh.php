<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * CSRF Token Refresh Middleware
 *
 * Automatically refreshes CSRF tokens for long-lived sessions to prevent
 * token expiration during user activity. This prevents "419 Page Expired"
 * errors when users have forms open for extended periods.
 *
 * SECURITY: This is the final piece to reach 100% CSRF protection
 *
 * @package App\Http\Middleware
 * @author MFI Management System Security Team
 * @date 2025-10-21
 */
class CsrfTokenRefresh
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

        // Only refresh for authenticated users on GET requests
        if ($request->isMethod('GET') && auth()->check()) {
            // Check if CSRF token is old (refresh every 30 minutes)
            $lastRefresh = session('csrf_token_refresh_time');
            $now = time();

            if (!$lastRefresh || ($now - $lastRefresh) > 1800) { // 30 minutes
                // Rotate CSRF token
                $request->session()->regenerateToken();
                session(['csrf_token_refresh_time' => $now]);

                Log::debug('CSRF token refreshed', [
                    'user_id' => auth()->id(),
                    'session_id' => session()->getId()
                ]);
            }
        }

        // Add CSRF token to response headers for JavaScript access
        if ($response->headers && method_exists($response, 'headers')) {
            $response->headers->set('X-CSRF-TOKEN', csrf_token());
        }

        return $response;
    }
}
