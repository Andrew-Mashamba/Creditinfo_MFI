<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\ApiKey;

class ApiKeyAuthentication
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->header('Authorization');
        
        // Remove 'Bearer ' prefix if present
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        if (!$apiKey) {
            Log::warning('API Key Missing', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'error_code' => 'MISSING_API_KEY'
            ], 401);
        }

        // Validate API key
        $validKey = $this->validateApiKey($apiKey);
        
        if (!$validKey) {
            Log::warning('Invalid API Key', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path(),
                'provided_key' => substr($apiKey, 0, 8) . '...' // Log partial key for debugging
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
                'error_code' => 'INVALID_API_KEY'
            ], 401);
        }

        // Check if API key is active
        if (!$validKey->is_active) {
            Log::warning('Inactive API Key', [
                'ip' => $request->ip(),
                'key_id' => $validKey->id,
                'endpoint' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'API key is inactive',
                'error_code' => 'INACTIVE_API_KEY'
            ], 401);
        }

        // Check rate limiting
        if ($this->isRateLimited($validKey)) {
            Log::warning('API Rate Limit Exceeded', [
                'ip' => $request->ip(),
                'key_id' => $validKey->id,
                'endpoint' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
                'error_code' => 'RATE_LIMIT_EXCEEDED'
            ], 429);
        }

        // Log successful authentication
        Log::info('API Key Authentication Successful', [
            'ip' => $request->ip(),
            'key_id' => $validKey->id,
            'client_name' => $validKey->client_name,
            'endpoint' => $request->path()
        ]);

        // Add API key info to request for later use
        $request->merge(['api_key' => $validKey]);

        return $next($request);
    }

    /**
     * Validate API key from database
     */
    protected function validateApiKey($apiKey)
    {
        // Use cache for performance
        $cacheKey = "api_key:{$apiKey}";
        
        return Cache::remember($cacheKey, 300, function () use ($apiKey) {
            return ApiKey::where('key', $apiKey)
                        ->where('is_active', true)
                        ->first();
        });
    }

    /**
     * Check rate limiting for API key
     */
    protected function isRateLimited($apiKey)
    {
        $rateLimitKey = "rate_limit:api_key:{$apiKey->id}";
        $maxRequests = $apiKey->rate_limit ?? 1000; // Default 1000 requests per hour
        $window = 3600; // 1 hour

        $currentRequests = Cache::get($rateLimitKey, 0);
        
        if ($currentRequests >= $maxRequests) {
            return true;
        }

        Cache::put($rateLimitKey, $currentRequests + 1, $window);
        return false;
    }
} 