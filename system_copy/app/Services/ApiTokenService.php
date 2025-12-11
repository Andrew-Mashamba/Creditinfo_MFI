<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * API Token Service
 *
 * Manages API token generation, rotation, and lifecycle
 *
 * @package App\Services
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class ApiTokenService
{
    /**
     * Token expiration periods (in days)
     */
    const SHORT_LIVED_TOKEN_DAYS = 30;   // 1 month
    const MEDIUM_LIVED_TOKEN_DAYS = 90;  // 3 months
    const LONG_LIVED_TOKEN_DAYS = 365;   // 1 year (not recommended)

    /**
     * Generate a new API token
     *
     * @param  string  $clientName
     * @param  array  $scopes
     * @param  int|null  $expiresInDays
     * @param  array  $metadata
     * @return array ['token' => string, 'api_key' => ApiKey]
     */
    public function generateToken(
        string $clientName,
        array $scopes = [],
        ?int $expiresInDays = self::SHORT_LIVED_TOKEN_DAYS,
        array $metadata = []
    ): array {
        // Generate cryptographically secure token
        $token = $this->generateSecureToken();

        // Hash token for storage (never store plain token)
        $hashedToken = Hash::make($token);

        // Calculate expiration date
        $expiresAt = $expiresInDays ? now()->addDays($expiresInDays) : null;

        // Create API key record
        $apiKey = ApiKey::create([
            'client_name' => $clientName,
            'key' => $token, // Will be hashed in model
            'scopes' => json_encode($scopes),
            'expires_at' => $expiresAt,
            'is_active' => true,
            'rate_limit' => $metadata['rate_limit'] ?? 1000,
            'last_used_at' => null,
            'metadata' => json_encode($metadata),
        ]);

        Log::info('API Token Generated', [
            'client_name' => $clientName,
            'key_id' => $apiKey->id,
            'scopes' => $scopes,
            'expires_at' => $expiresAt,
        ]);

        // Return both the plain token and the API key record
        // WARNING: This is the ONLY time the plain token is returned
        return [
            'token' => $token,
            'api_key' => $apiKey,
        ];
    }

    /**
     * Generate a cryptographically secure token
     *
     * Format: nbc_saccos_{version}_{random}
     *
     * @return string
     */
    protected function generateSecureToken(): string
    {
        $prefix = 'nbc_saccos';
        $version = 'v1';
        $random = bin2hex(random_bytes(32)); // 64 characters

        return sprintf('%s_%s_%s', $prefix, $version, $random);
    }

    /**
     * Rotate an API token (generate new token, invalidate old)
     *
     * @param  ApiKey  $apiKey
     * @param  int|null  $expiresInDays
     * @return array ['token' => string, 'api_key' => ApiKey]
     */
    public function rotateToken(ApiKey $apiKey, ?int $expiresInDays = self::SHORT_LIVED_TOKEN_DAYS): array
    {
        // Generate new token with same scopes
        $scopes = is_string($apiKey->scopes) ? json_decode($apiKey->scopes, true) : $apiKey->scopes;
        $metadata = is_string($apiKey->metadata) ? json_decode($apiKey->metadata, true) : $apiKey->metadata ?? [];

        $newToken = $this->generateToken(
            $apiKey->client_name,
            $scopes ?? [],
            $expiresInDays,
            array_merge($metadata, ['rotated_from' => $apiKey->id])
        );

        // Mark old token as rotated (don't delete for audit trail)
        $apiKey->update([
            'is_active' => false,
            'revoked_at' => now(),
            'revocation_reason' => 'Token rotated',
            'rotated_to' => $newToken['api_key']->id,
        ]);

        // Clear cache for old token
        Cache::forget("api_key:{$apiKey->key}");

        Log::info('API Token Rotated', [
            'old_key_id' => $apiKey->id,
            'new_key_id' => $newToken['api_key']->id,
            'client_name' => $apiKey->client_name,
        ]);

        return $newToken;
    }

    /**
     * Revoke an API token
     *
     * @param  ApiKey  $apiKey
     * @param  string  $reason
     * @return bool
     */
    public function revokeToken(ApiKey $apiKey, string $reason = 'Manual revocation'): bool
    {
        $apiKey->update([
            'is_active' => false,
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);

        // Clear cache
        Cache::forget("api_key:{$apiKey->key}");

        Log::warning('API Token Revoked', [
            'key_id' => $apiKey->id,
            'client_name' => $apiKey->client_name,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Check if token is expired
     *
     * @param  ApiKey  $apiKey
     * @return bool
     */
    public function isTokenExpired(ApiKey $apiKey): bool
    {
        if (!$apiKey->expires_at) {
            return false; // No expiration set
        }

        return now()->gt($apiKey->expires_at);
    }

    /**
     * Get tokens expiring soon (within N days)
     *
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTokensExpiringSoon(int $days = 30)
    {
        return ApiKey::where('is_active', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->get();
    }

    /**
     * Automatically rotate expiring tokens
     *
     * Call this from a scheduled command
     *
     * @param  int  $daysBeforeExpiration
     * @return int Number of tokens rotated
     */
    public function autoRotateExpiringTokens(int $daysBeforeExpiration = 7): int
    {
        $expiringTokens = $this->getTokensExpiringSoon($daysBeforeExpiration);
        $rotatedCount = 0;

        foreach ($expiringTokens as $apiKey) {
            // Skip tokens that don't allow auto-rotation
            if ($apiKey->metadata && isset($apiKey->metadata['auto_rotate']) && !$apiKey->metadata['auto_rotate']) {
                continue;
            }

            try {
                $this->rotateToken($apiKey);
                $rotatedCount++;

                // TODO: Notify client about token rotation
                // $this->notifyClientAboutRotation($apiKey);
            } catch (\Exception $e) {
                Log::error('Failed to auto-rotate token', [
                    'key_id' => $apiKey->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Auto-rotated expiring tokens', [
            'count' => $rotatedCount,
            'days_before_expiration' => $daysBeforeExpiration,
        ]);

        return $rotatedCount;
    }

    /**
     * Update token scopes
     *
     * @param  ApiKey  $apiKey
     * @param  array  $scopes
     * @return bool
     */
    public function updateTokenScopes(ApiKey $apiKey, array $scopes): bool
    {
        $apiKey->update([
            'scopes' => json_encode($scopes),
        ]);

        // Clear cache
        Cache::forget("api_key:{$apiKey->key}");

        Log::info('API Token Scopes Updated', [
            'key_id' => $apiKey->id,
            'client_name' => $apiKey->client_name,
            'new_scopes' => $scopes,
        ]);

        return true;
    }

    /**
     * Record token usage
     *
     * @param  ApiKey  $apiKey
     * @param  array  $metadata
     * @return void
     */
    public function recordTokenUsage(ApiKey $apiKey, array $metadata = []): void
    {
        $apiKey->update([
            'last_used_at' => now(),
            'usage_count' => ($apiKey->usage_count ?? 0) + 1,
        ]);

        // Store detailed usage in cache for analytics
        $usageKey = "api_usage:{$apiKey->id}:" . now()->format('Y-m-d');
        $currentUsage = Cache::get($usageKey, 0);
        Cache::put($usageKey, $currentUsage + 1, 86400 * 30); // Keep for 30 days
    }

    /**
     * Get token usage statistics
     *
     * @param  ApiKey  $apiKey
     * @param  int  $days
     * @return array
     */
    public function getTokenUsageStats(ApiKey $apiKey, int $days = 30): array
    {
        $stats = [
            'key_id' => $apiKey->id,
            'client_name' => $apiKey->client_name,
            'total_usage' => $apiKey->usage_count ?? 0,
            'last_used_at' => $apiKey->last_used_at,
            'daily_usage' => [],
        ];

        // Get daily usage from cache
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $usageKey = "api_usage:{$apiKey->id}:{$date}";
            $usage = Cache::get($usageKey, 0);

            if ($usage > 0) {
                $stats['daily_usage'][$date] = $usage;
            }
        }

        return $stats;
    }

    /**
     * Clean up expired and revoked tokens
     *
     * @param  int  $daysOld
     * @return int Number of tokens cleaned up
     */
    public function cleanupOldTokens(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);

        $count = ApiKey::where('is_active', false)
            ->where(function ($query) use ($cutoffDate) {
                $query->where('revoked_at', '<', $cutoffDate)
                    ->orWhere('expires_at', '<', $cutoffDate);
            })
            ->delete();

        Log::info('Cleaned up old API tokens', [
            'count' => $count,
            'days_old' => $daysOld,
        ]);

        return $count;
    }
}
