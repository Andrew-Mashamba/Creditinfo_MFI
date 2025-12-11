<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Rate Limit Violation Service
 *
 * Tracks rate limit violations and automatically blocks abusive IPs
 *
 * @package App\Services
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class RateLimitViolationService
{
    /**
     * Violation thresholds before automatic blocking
     */
    const VIOLATION_THRESHOLD = 10; // Number of violations before blocking
    const VIOLATION_WINDOW = 3600; // Time window in seconds (1 hour)
    const AUTO_BLOCK_DURATION = 1440; // Auto-block duration in minutes (24 hours)

    /**
     * Record a rate limit violation
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $limiter  Rate limiter name (auth, api, etc.)
     * @return void
     */
    public function recordViolation(Request $request, string $limiter = 'unknown'): void
    {
        $ip = $request->ip();
        $cacheKey = "rate_limit_violations:{$ip}";

        // Get current violations
        $violations = Cache::get($cacheKey, []);

        // Add new violation
        $violations[] = [
            'timestamp' => now()->timestamp,
            'limiter' => $limiter,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
        ];

        // Remove old violations outside the window
        $violations = array_filter($violations, function ($violation) {
            return ($violation['timestamp'] > (now()->timestamp - self::VIOLATION_WINDOW));
        });

        // Store violations
        Cache::put($cacheKey, $violations, self::VIOLATION_WINDOW);

        // Log violation
        Log::warning('Rate Limit Violation', [
            'ip' => $ip,
            'limiter' => $limiter,
            'endpoint' => $request->path(),
            'total_violations' => count($violations),
        ]);

        // Check if threshold exceeded
        if (count($violations) >= self::VIOLATION_THRESHOLD) {
            $this->autoBlockIp($ip, $violations);
        }

        // Store in database for analysis
        $this->storeViolationInDatabase($ip, $limiter, $request);
    }

    /**
     * Automatically block IP after excessive violations
     *
     * @param  string  $ip
     * @param  array  $violations
     * @return void
     */
    protected function autoBlockIp(string $ip, array $violations): void
    {
        $cacheKey = "auto_blocked:{$ip}";

        // Check if already blocked
        if (Cache::has($cacheKey)) {
            return;
        }

        // Block IP for configured duration
        Cache::put($cacheKey, true, self::AUTO_BLOCK_DURATION * 60);

        // Add to database blacklist
        $this->addToBlacklist($ip, $violations);

        // Log the auto-block
        Log::alert('IP Automatically Blocked', [
            'ip' => $ip,
            'violation_count' => count($violations),
            'duration' => self::AUTO_BLOCK_DURATION . ' minutes',
            'violations' => $violations,
        ]);

        // Notify administrators (optional)
        $this->notifyAdministrators($ip, $violations);
    }

    /**
     * Add IP to blacklist database
     *
     * @param  string  $ip
     * @param  array  $violations
     * @return void
     */
    protected function addToBlacklist(string $ip, array $violations): void
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('ip_blacklist')) {
                return;
            }

            $reason = sprintf(
                'Automatic block: %d rate limit violations in %d minutes',
                count($violations),
                self::VIOLATION_WINDOW / 60
            );

            DB::table('ip_blacklist')->insert([
                'ip_address' => $ip,
                'reason' => $reason,
                'expires_at' => now()->addMinutes(self::AUTO_BLOCK_DURATION),
                'is_active' => true,
                'auto_blocked' => true,
                'violation_count' => count($violations),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add IP to blacklist', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store violation in database for analysis
     *
     * @param  string  $ip
     * @param  string  $limiter
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function storeViolationInDatabase(string $ip, string $limiter, Request $request): void
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('rate_limit_violations')) {
                return;
            }

            DB::table('rate_limit_violations')->insert([
                'ip_address' => $ip,
                'limiter' => $limiter,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store rate limit violation', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify administrators about auto-blocked IP
     *
     * @param  string  $ip
     * @param  array  $violations
     * @return void
     */
    protected function notifyAdministrators(string $ip, array $violations): void
    {
        // TODO: Implement notification system
        // Could send email, Slack message, or SMS to administrators
        // For now, just log it (already logged in autoBlockIp method)
    }

    /**
     * Get violations for IP
     *
     * @param  string  $ip
     * @return array
     */
    public function getViolations(string $ip): array
    {
        $cacheKey = "rate_limit_violations:{$ip}";
        return Cache::get($cacheKey, []);
    }

    /**
     * Check if IP should be monitored (high violation count but not blocked yet)
     *
     * @param  string  $ip
     * @return bool
     */
    public function shouldMonitorIp(string $ip): bool
    {
        $violations = $this->getViolations($ip);
        $violationCount = count($violations);

        // Monitor if violations are at 50% of threshold or higher
        return $violationCount >= (self::VIOLATION_THRESHOLD / 2);
    }

    /**
     * Clear violations for IP (for testing or manual reset)
     *
     * @param  string  $ip
     * @return void
     */
    public function clearViolations(string $ip): void
    {
        $cacheKey = "rate_limit_violations:{$ip}";
        Cache::forget($cacheKey);

        Log::info('Rate limit violations cleared', ['ip' => $ip]);
    }

    /**
     * Get statistics about rate limit violations
     *
     * @param  int  $hours  Number of hours to look back
     * @return array
     */
    public function getStatistics(int $hours = 24): array
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('rate_limit_violations')) {
                return [
                    'total_violations' => 0,
                    'unique_ips' => 0,
                    'by_limiter' => [],
                    'by_endpoint' => [],
                    'top_violators' => [],
                ];
            }

            $since = now()->subHours($hours);

            $totalViolations = DB::table('rate_limit_violations')
                ->where('created_at', '>=', $since)
                ->count();

            $uniqueIps = DB::table('rate_limit_violations')
                ->where('created_at', '>=', $since)
                ->distinct('ip_address')
                ->count('ip_address');

            $byLimiter = DB::table('rate_limit_violations')
                ->select('limiter', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $since)
                ->groupBy('limiter')
                ->pluck('count', 'limiter')
                ->toArray();

            $byEndpoint = DB::table('rate_limit_violations')
                ->select('endpoint', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $since)
                ->groupBy('endpoint')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'endpoint')
                ->toArray();

            $topViolators = DB::table('rate_limit_violations')
                ->select('ip_address', DB::raw('COUNT(*) as violations'))
                ->where('created_at', '>=', $since)
                ->groupBy('ip_address')
                ->orderByDesc('violations')
                ->limit(10)
                ->get()
                ->toArray();

            return [
                'total_violations' => $totalViolations,
                'unique_ips' => $uniqueIps,
                'by_limiter' => $byLimiter,
                'by_endpoint' => $byEndpoint,
                'top_violators' => $topViolators,
                'time_period' => "{$hours} hours",
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get rate limit statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to retrieve statistics',
            ];
        }
    }
}
