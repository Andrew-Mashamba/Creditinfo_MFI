<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/verify-account';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
            
            // API Monitoring Routes
            if (file_exists(base_path('routes/api-monitor.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/api-monitor.php'));
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * SECURITY: Protects against brute force, DoS, and abuse attacks
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Standard API rate limit (60 requests per minute)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // File upload rate limit (5 uploads per minute)
        // SECURITY: Prevents file upload abuse and DoS attacks
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many upload attempts. Please wait before trying again.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // Authentication rate limit (5 attempts per minute)
        // SECURITY: Prevents brute force password attacks
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many authentication attempts. Please wait before trying again.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // Sensitive operations rate limit (10 per minute)
        // SECURITY: For operations like password reset, account changes
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many requests. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // Search rate limit (30 per minute)
        // SECURITY: Prevents search abuse and data scraping
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip());
        });

        // AI service rate limit (10 per minute)
        // SECURITY: Prevents AI service abuse
        RateLimiter::for('ai', function (Request $request) {
            return [
                Limit::perMinute(10)
                    ->by($request->user()?->id ?: $request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'error' => 'AI service rate limit exceeded. Please wait before making more requests.',
                            'retry_after' => $headers['Retry-After'] ?? 60,
                        ], 429, $headers);
                    }),
                // Daily limit for AI services (100 per day)
                Limit::perDay(100)
                    ->by($request->user()?->id ?: $request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'error' => 'Daily AI service limit exceeded. Try again tomorrow.',
                            'retry_after' => $headers['Retry-After'] ?? 86400,
                        ], 429, $headers);
                    }),
            ];
        });

        // Registration rate limit (3 registrations per hour from same IP)
        // SECURITY: Prevents fake account creation
        RateLimiter::for('registration', function (Request $request) {
            return Limit::perHour(3)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many registration attempts. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 3600,
                    ], 429, $headers);
                });
        });

        // API write operations rate limit (20 per minute)
        // SECURITY: Prevents data manipulation abuse
        RateLimiter::for('api-write', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many write operations. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });
    }
}
