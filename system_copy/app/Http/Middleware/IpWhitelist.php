<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $clientIp = $request->ip();
        $allowedIps = $this->getAllowedIps();

        // If no IPs are configured, allow all (for development)
        if (empty($allowedIps)) {
            Log::info('IP Whitelist: No IPs configured, allowing all', [
                'client_ip' => $clientIp,
                'endpoint' => $request->path()
            ]);
            return $next($request);
        }

        // Check if client IP is in whitelist
        if (!$this->isIpAllowed($clientIp, $allowedIps)) {
            Log::warning('IP Whitelist: Access denied', [
                'client_ip' => $clientIp,
                'allowed_ips' => $allowedIps,
                'endpoint' => $request->path(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Access denied: IP not in whitelist',
                'error_code' => 'IP_NOT_WHITELISTED'
            ], 403);
        }

        Log::info('IP Whitelist: Access granted', [
            'client_ip' => $clientIp,
            'endpoint' => $request->path()
        ]);

        return $next($request);
    }

    /**
     * Get allowed IPs from configuration
     */
    protected function getAllowedIps()
    {
        // Cache the allowed IPs for performance
        return Cache::remember('api_allowed_ips', 300, function () {
            $ips = config('api.allowed_ips', []);
            
            // Also check database for dynamic IP management
            $dbIps = $this->getDatabaseAllowedIps();
            
            return array_merge($ips, $dbIps);
        });
    }

    /**
     * Get allowed IPs from database (if you have a table for this)
     */
    protected function getDatabaseAllowedIps()
    {
        // This would typically come from a database table
        // For now, return empty array
        return [];
    }

    /**
     * Check if IP is allowed (supports CIDR notation)
     */
    protected function isIpAllowed($clientIp, $allowedIps)
    {
        foreach ($allowedIps as $allowedIp) {
            if ($this->ipInRange($clientIp, $allowedIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if IP is in range (supports CIDR notation)
     */
    protected function ipInRange($ip, $range)
    {
        // Handle CIDR notation (e.g., 192.168.1.0/24)
        if (strpos($range, '/') !== false) {
            list($subnet, $mask) = explode('/', $range);
            
            $ipBinary = ip2long($ip);
            $subnetBinary = ip2long($subnet);
            $maskBinary = ~((1 << (32 - $mask)) - 1);
            
            return ($ipBinary & $maskBinary) == ($subnetBinary & $maskBinary);
        }
        
        // Handle single IP
        return $ip === $range;
    }
} 