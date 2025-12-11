<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RateLimitViolationService;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Track Rate Limit Violations Middleware
 *
 * Tracks rate limit violations and automatically blocks abusive IPs
 *
 * @package App\Http\Middleware
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class TrackRateLimitViolations
{
    /**
     * Rate Limit Violation Service
     *
     * @var \App\Services\RateLimitViolationService
     */
    protected $violationService;

    /**
     * Create a new middleware instance.
     *
     * @param  \App\Services\RateLimitViolationService  $violationService
     * @return void
     */
    public function __construct(RateLimitViolationService $violationService)
    {
        $this->violationService = $violationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);

            // Check if response is 429 (Too Many Requests)
            if ($response->getStatusCode() === 429) {
                // Record the violation
                $limiter = $this->detectLimiterFromRequest($request);
                $this->violationService->recordViolation($request, $limiter);
            }

            return $response;
        } catch (TooManyRequestsHttpException $e) {
            // Record the violation
            $limiter = $this->detectLimiterFromRequest($request);
            $this->violationService->recordViolation($request, $limiter);

            // Re-throw the exception
            throw $e;
        }
    }

    /**
     * Detect which rate limiter was triggered based on the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function detectLimiterFromRequest(Request $request): string
    {
        $path = $request->path();

        // Detect based on endpoint patterns
        if (str_contains($path, 'auth') || str_contains($path, 'login')) {
            return 'auth';
        }

        if (str_contains($path, 'register') || str_contains($path, 'registration')) {
            return 'registration';
        }

        if (str_contains($path, 'upload')) {
            return 'uploads';
        }

        if (str_contains($path, 'search')) {
            return 'search';
        }

        if (str_contains($path, 'ai') || str_contains($path, 'assistant')) {
            return 'ai';
        }

        if (str_starts_with($path, 'api/')) {
            // Check if it's a write operation
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                return 'api-write';
            }
            return 'api';
        }

        // Check for sensitive operations
        $sensitivePaths = ['billing', 'payment', 'transfer', 'transaction'];
        foreach ($sensitivePaths as $sensitivePath) {
            if (str_contains($path, $sensitivePath)) {
                return 'sensitive';
            }
        }

        return 'general';
    }
}
