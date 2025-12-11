<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * IP Blacklist Middleware
 *
 * Blocks requests from blacklisted IPs (manually added or automatically blocked)
 *
 * @package App\Http\Middleware
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class IpBlacklist
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
        $clientIp = $request->ip();

        // Check if IP is blacklisted
        if ($this->isIpBlacklisted($clientIp)) {
            Log::warning('IP Blacklist: Access denied', [
                'client_ip' => $clientIp,
                'endpoint' => $request->path(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'reason' => $this->getBlockReason($clientIp),
            ]);

            // Return 403 Forbidden
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'error_code' => 'IP_BLACKLISTED'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if IP is blacklisted
     *
     * @param  string  $ip
     * @return bool
     */
    protected function isIpBlacklisted(string $ip): bool
    {
        // Check cache first for performance
        $cacheKey = "ip_blacklist:{$ip}";

        return Cache::remember($cacheKey, 60, function () use ($ip) {
            // Check configuration blacklist
            $configBlacklist = config('security.ip_blacklist', []);
            if (in_array($ip, $configBlacklist)) {
                return true;
            }

            // Check if IP is in CIDR range blacklist
            foreach ($configBlacklist as $range) {
                if ($this->ipInRange($ip, $range)) {
                    return true;
                }
            }

            // Check database blacklist
            if ($this->isIpInDatabaseBlacklist($ip)) {
                return true;
            }

            // Check automatic blocking (based on rate limit violations)
            if ($this->isIpAutomaticallyBlocked($ip)) {
                return true;
            }

            return false;
        });
    }

    /**
     * Check if IP is in database blacklist
     *
     * @param  string  $ip
     * @return bool
     */
    protected function isIpInDatabaseBlacklist(string $ip): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('ip_blacklist')) {
                return false;
            }

            return DB::table('ip_blacklist')
                ->where('ip_address', $ip)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->where('is_active', true)
                ->exists();
        } catch (\Exception $e) {
            Log::error('IP Blacklist: Database check failed', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if IP is automatically blocked (based on rate limit violations)
     *
     * @param  string  $ip
     * @return bool
     */
    protected function isIpAutomaticallyBlocked(string $ip): bool
    {
        $cacheKey = "auto_blocked:{$ip}";
        return Cache::has($cacheKey);
    }

    /**
     * Get block reason for IP
     *
     * @param  string  $ip
     * @return string
     */
    protected function getBlockReason(string $ip): string
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('ip_blacklist')) {
                $record = DB::table('ip_blacklist')
                    ->where('ip_address', $ip)
                    ->where('is_active', true)
                    ->first();

                if ($record) {
                    return $record->reason ?? 'Manually blacklisted';
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        if ($this->isIpAutomaticallyBlocked($ip)) {
            return 'Automatic block due to excessive rate limit violations';
        }

        return 'IP blacklisted';
    }

    /**
     * Check if IP is in range (supports CIDR notation)
     *
     * @param  string  $ip
     * @param  string  $range
     * @return bool
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        // Handle CIDR notation (e.g., 192.168.1.0/24)
        if (strpos($range, '/') !== false) {
            list($subnet, $mask) = explode('/', $range);

            $ipBinary = ip2long($ip);
            $subnetBinary = ip2long($subnet);

            if ($ipBinary === false || $subnetBinary === false) {
                return false;
            }

            $maskBinary = ~((1 << (32 - $mask)) - 1);

            return ($ipBinary & $maskBinary) == ($subnetBinary & $maskBinary);
        }

        // Handle single IP
        return $ip === $range;
    }

    /**
     * Add IP to blacklist
     *
     * @param  string  $ip
     * @param  string  $reason
     * @param  int|null  $duration Duration in minutes (null = permanent)
     * @return void
     */
    public static function blockIp(string $ip, string $reason = 'Manual block', ?int $duration = null): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('ip_blacklist')) {
                DB::table('ip_blacklist')->insert([
                    'ip_address' => $ip,
                    'reason' => $reason,
                    'expires_at' => $duration ? now()->addMinutes($duration) : null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Clear cache
            Cache::forget("ip_blacklist:{$ip}");

            Log::info('IP Blacklist: IP blocked', [
                'ip' => $ip,
                'reason' => $reason,
                'duration' => $duration ? "{$duration} minutes" : 'permanent',
            ]);
        } catch (\Exception $e) {
            Log::error('IP Blacklist: Failed to block IP', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove IP from blacklist
     *
     * @param  string  $ip
     * @return void
     */
    public static function unblockIp(string $ip): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('ip_blacklist')) {
                DB::table('ip_blacklist')
                    ->where('ip_address', $ip)
                    ->update([
                        'is_active' => false,
                        'updated_at' => now(),
                    ]);
            }

            // Clear cache
            Cache::forget("ip_blacklist:{$ip}");
            Cache::forget("auto_blocked:{$ip}");

            Log::info('IP Blacklist: IP unblocked', ['ip' => $ip]);
        } catch (\Exception $e) {
            Log::error('IP Blacklist: Failed to unblock IP', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
