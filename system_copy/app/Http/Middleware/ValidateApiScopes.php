<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Validate API Scopes Middleware
 *
 * Validates that the API token has the required scopes/permissions
 *
 * @package App\Http\Middleware
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class ValidateApiScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$requiredScopes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$requiredScopes)
    {
        // Get API key from request (set by ApiKeyAuthentication middleware)
        $apiKey = $request->get('api_key');

        if (!$apiKey) {
            Log::error('ValidateApiScopes: API key not found in request', [
                'endpoint' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'AUTHENTICATION_REQUIRED',
            ], 401);
        }

        // Check if API key has required scopes
        if (!$this->hasRequiredScopes($apiKey, $requiredScopes)) {
            Log::warning('API Scope Validation Failed', [
                'key_id' => $apiKey->id,
                'client_name' => $apiKey->client_name ?? 'Unknown',
                'required_scopes' => $requiredScopes,
                'granted_scopes' => $apiKey->scopes ?? [],
                'endpoint' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_scopes' => $requiredScopes,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if API key has all required scopes
     *
     * @param  object  $apiKey
     * @param  array  $requiredScopes
     * @return bool
     */
    protected function hasRequiredScopes($apiKey, array $requiredScopes): bool
    {
        // If no scopes required, allow access
        if (empty($requiredScopes)) {
            return true;
        }

        // Get granted scopes from API key
        $grantedScopes = $this->getGrantedScopes($apiKey);

        // Check if API key has wildcard scope (admin)
        if (in_array('*', $grantedScopes) || in_array('admin', $grantedScopes)) {
            return true;
        }

        // Check if all required scopes are granted
        foreach ($requiredScopes as $requiredScope) {
            if (!$this->hasScopePermission($grantedScopes, $requiredScope)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get granted scopes from API key
     *
     * @param  object  $apiKey
     * @return array
     */
    protected function getGrantedScopes($apiKey): array
    {
        // Check if scopes are stored as JSON
        if (isset($apiKey->scopes)) {
            if (is_string($apiKey->scopes)) {
                return json_decode($apiKey->scopes, true) ?? [];
            }

            if (is_array($apiKey->scopes)) {
                return $apiKey->scopes;
            }
        }

        // Check if scopes are stored as comma-separated string
        if (isset($apiKey->permissions)) {
            return array_map('trim', explode(',', $apiKey->permissions));
        }

        // No scopes defined
        return [];
    }

    /**
     * Check if a specific scope permission is granted
     *
     * Supports wildcard matching (e.g., "transactions.*" matches "transactions.read")
     *
     * @param  array  $grantedScopes
     * @param  string  $requiredScope
     * @return bool
     */
    protected function hasScopePermission(array $grantedScopes, string $requiredScope): bool
    {
        // Exact match
        if (in_array($requiredScope, $grantedScopes)) {
            return true;
        }

        // Wildcard matching
        foreach ($grantedScopes as $grantedScope) {
            if ($this->scopeMatchesWildcard($requiredScope, $grantedScope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if required scope matches granted wildcard scope
     *
     * Examples:
     * - "transactions.*" grants "transactions.read", "transactions.write"
     * - "users.*.read" grants "users.1.read", "users.2.read"
     *
     * @param  string  $requiredScope
     * @param  string  $grantedScope
     * @return bool
     */
    protected function scopeMatchesWildcard(string $requiredScope, string $grantedScope): bool
    {
        // If no wildcard, no match
        if (!str_contains($grantedScope, '*')) {
            return false;
        }

        // Convert wildcard to regex pattern
        $pattern = '/^' . str_replace('\*', '.*', preg_quote($grantedScope, '/')) . '$/';

        return preg_match($pattern, $requiredScope) === 1;
    }
}
