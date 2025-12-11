<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

/**
 * Device Fingerprinting Service
 *
 * Tracks and analyzes device fingerprints for fraud detection
 * Features:
 * - Browser fingerprinting (User-Agent, Canvas, WebGL, Fonts)
 * - Device reputation scoring
 * - Multi-account detection from same device
 * - Bot/automation detection
 * - Device trust scoring
 */
class DeviceFingerprintService
{
    /**
     * Process device fingerprint from request
     *
     * @param Request $request
     * @return array Device fingerprint data and scores
     */
    public function processFingerprint(Request $request): array
    {
        // Extract fingerprint data from request
        $fingerprintData = $this->extractFingerprintData($request);

        // Generate unique fingerprint hash
        $fingerprintHash = $this->generateFingerprintHash($fingerprintData);

        // Get or create device fingerprint record
        $device = $this->getOrCreateDevice($fingerprintHash, $fingerprintData, $request);

        // Update device statistics
        $this->updateDeviceStats($device, $request);

        // Calculate device risk score
        $riskScore = $this->calculateDeviceRiskScore($device);

        // Detect automation/bot
        $botDetection = $this->detectBot($device, $fingerprintData);

        return [
            'fingerprint_hash' => $fingerprintHash,
            'device_id' => $device->id,
            'trust_score' => $device->trust_score,
            'risk_score' => $riskScore,
            'reputation' => $device->reputation,
            'is_blocked' => $device->is_blocked,
            'is_bot' => $botDetection['is_bot'],
            'bot_indicators' => $botDetection['indicators'],
            'is_new_device' => $device->total_sessions == 0,
            'total_sessions' => $device->total_sessions,
            'fraud_attempts' => $device->fraud_attempts,
            'unique_accounts' => $device->unique_accounts_count,
        ];
    }

    /**
     * Extract fingerprint data from request headers and payload
     */
    protected function extractFingerprintData(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';
        $parsed = $this->parseUserAgent($userAgent);

        return [
            // Browser data
            'user_agent' => $userAgent,
            'browser_name' => $parsed['browser_name'],
            'browser_version' => $parsed['browser_version'],
            'os_name' => $parsed['os_name'],
            'os_version' => $parsed['os_version'],
            'device_type' => $parsed['device_type'],
            'device_vendor' => $parsed['device_vendor'],

            // Screen & display (from request payload if available)
            'screen_resolution' => $request->header('X-Screen-Resolution') ?? $request->input('screen_resolution'),
            'screen_color_depth' => $request->header('X-Screen-Color-Depth') ?? $request->input('screen_color_depth'),
            'timezone' => $request->header('X-Timezone') ?? $request->input('timezone'),
            'language' => $request->header('Accept-Language') ?? $request->input('language'),

            // Advanced fingerprinting (from client-side JS if implemented)
            'canvas_hash' => $request->header('X-Canvas-Hash') ?? $request->input('canvas_hash'),
            'webgl_vendor' => $request->header('X-WebGL-Vendor') ?? $request->input('webgl_vendor'),
            'webgl_renderer' => $request->header('X-WebGL-Renderer') ?? $request->input('webgl_renderer'),
            'webgl_hash' => $request->header('X-WebGL-Hash') ?? $request->input('webgl_hash'),
            'audio_hash' => $request->header('X-Audio-Hash') ?? $request->input('audio_hash'),
            'fonts_hash' => $request->header('X-Fonts-Hash') ?? $request->input('fonts_hash'),

            // Features
            'cookies_enabled' => $request->header('X-Cookies-Enabled') ?? true,
            'local_storage_enabled' => $request->header('X-LocalStorage-Enabled') ?? true,
            'do_not_track' => $request->header('DNT') == '1',

            // Hardware
            'cpu_cores' => $request->header('X-CPU-Cores') ?? $request->input('cpu_cores'),
            'memory_gb' => $request->header('X-Memory-GB') ?? $request->input('memory_gb'),
            'touch_support' => $request->header('X-Touch-Support') ?? false,
            'platform' => $request->header('Sec-CH-UA-Platform') ?? $parsed['platform'],
            'mobile' => $request->header('Sec-CH-UA-Mobile') == '?1' || $parsed['is_mobile'],
        ];
    }

    /**
     * Generate unique hash for device fingerprint
     */
    protected function generateFingerprintHash(array $fingerprintData): string
    {
        // Combine key fingerprint components
        $components = [
            $fingerprintData['user_agent'] ?? '',
            $fingerprintData['screen_resolution'] ?? '',
            $fingerprintData['screen_color_depth'] ?? '',
            $fingerprintData['timezone'] ?? '',
            $fingerprintData['language'] ?? '',
            $fingerprintData['canvas_hash'] ?? '',
            $fingerprintData['webgl_hash'] ?? '',
            $fingerprintData['audio_hash'] ?? '',
            $fingerprintData['fonts_hash'] ?? '',
            $fingerprintData['platform'] ?? '',
        ];

        return hash('sha256', implode('|', array_filter($components)));
    }

    /**
     * Get existing or create new device fingerprint record
     */
    protected function getOrCreateDevice(string $fingerprintHash, array $fingerprintData, Request $request)
    {
        $device = DB::table('device_fingerprints')
            ->where('fingerprint_hash', $fingerprintHash)
            ->first();

        if ($device) {
            return $device;
        }

        // Create new device record
        $deviceId = DB::table('device_fingerprints')->insertGetId([
            'fingerprint_hash' => $fingerprintHash,
            'user_agent' => $fingerprintData['user_agent'],
            'browser_name' => $fingerprintData['browser_name'],
            'browser_version' => $fingerprintData['browser_version'],
            'os_name' => $fingerprintData['os_name'],
            'os_version' => $fingerprintData['os_version'],
            'device_type' => $fingerprintData['device_type'],
            'device_vendor' => $fingerprintData['device_vendor'],
            'screen_resolution' => $fingerprintData['screen_resolution'],
            'screen_color_depth' => $fingerprintData['screen_color_depth'],
            'timezone' => $fingerprintData['timezone'],
            'language' => $fingerprintData['language'],
            'canvas_hash' => $fingerprintData['canvas_hash'],
            'webgl_vendor' => $fingerprintData['webgl_vendor'],
            'webgl_renderer' => $fingerprintData['webgl_renderer'],
            'webgl_hash' => $fingerprintData['webgl_hash'],
            'audio_hash' => $fingerprintData['audio_hash'],
            'fonts_hash' => $fingerprintData['fonts_hash'],
            'cookies_enabled' => $fingerprintData['cookies_enabled'],
            'local_storage_enabled' => $fingerprintData['local_storage_enabled'],
            'do_not_track' => $fingerprintData['do_not_track'],
            'cpu_cores' => $fingerprintData['cpu_cores'],
            'memory_gb' => $fingerprintData['memory_gb'],
            'touch_support' => $fingerprintData['touch_support'],
            'platform' => $fingerprintData['platform'],
            'mobile' => $fingerprintData['mobile'],
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'last_ip' => $request->ip(),
            'last_api_key_id' => $request->developer_api_key->id ?? null,
            'raw_fingerprint' => json_encode($fingerprintData),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('device_fingerprints')->find($deviceId);
    }

    /**
     * Update device statistics
     */
    protected function updateDeviceStats($device, Request $request): void
    {
        DB::table('device_fingerprints')
            ->where('id', $device->id)
            ->update([
                'last_seen_at' => now(),
                'last_ip' => $request->ip(),
                'last_api_key_id' => $request->developer_api_key->id ?? null,
                'total_sessions' => DB::raw('total_sessions + 1'),
                'updated_at' => now(),
            ]);

        // Track unique IPs
        $this->trackUniqueIps($device->id, $request->ip());

        // Track unique accounts (will be updated by transaction processing)
    }

    /**
     * Track unique IPs for this device
     */
    protected function trackUniqueIps(int $deviceId, string $ip): void
    {
        $cacheKey = "device:{$deviceId}:ips";
        $ips = Cache::get($cacheKey, []);

        if (!in_array($ip, $ips)) {
            $ips[] = $ip;
            Cache::put($cacheKey, $ips, now()->addDays(30));

            DB::table('device_fingerprints')
                ->where('id', $deviceId)
                ->update(['unique_ips_count' => count($ips)]);
        }
    }

    /**
     * Calculate device risk score based on multiple factors
     */
    protected function calculateDeviceRiskScore($device): int
    {
        $score = 0;

        // New device = slightly elevated risk
        if ($device->total_sessions < 5) {
            $score += 15;
        }

        // Multiple accounts from same device (velocity fraud indicator)
        if ($device->unique_accounts_count > 10) {
            $score += 30;
        } elseif ($device->unique_accounts_count > 5) {
            $score += 20;
        } elseif ($device->unique_accounts_count > 3) {
            $score += 10;
        }

        // Fraud history
        if ($device->fraud_attempts > 0) {
            $score += min($device->fraud_attempts * 15, 50);
        }

        // Failed transaction rate
        if ($device->total_transactions > 0) {
            $failureRate = $device->failed_transactions / $device->total_transactions;
            if ($failureRate > 0.5) {
                $score += 25;
            } elseif ($failureRate > 0.3) {
                $score += 15;
            }
        }

        // Bot indicators
        if ($device->is_emulator || $device->is_virtual_machine) {
            $score += 30;
        }
        if ($device->is_headless || $device->is_automation_tool) {
            $score += 40;
        }

        // Multiple IPs (potential proxy/VPN rotation)
        if ($device->unique_ips_count > 20) {
            $score += 25;
        } elseif ($device->unique_ips_count > 10) {
            $score += 15;
        }

        // Already blocked
        if ($device->is_blocked) {
            $score = 100;
        }

        return min($score, 100);
    }

    /**
     * Detect bot/automation tools
     */
    protected function detectBot($device, array $fingerprintData): array
    {
        $indicators = [];
        $isBot = false;

        // Check for headless browser indicators
        if (strpos(strtolower($fingerprintData['user_agent'] ?? ''), 'headless') !== false) {
            $indicators[] = 'Headless browser detected in user agent';
            $isBot = true;
        }

        // Check for automation tools
        $automationSignatures = ['selenium', 'puppeteer', 'playwright', 'webdriver', 'phantom'];
        foreach ($automationSignatures as $signature) {
            if (stripos($fingerprintData['user_agent'] ?? '', $signature) !== false) {
                $indicators[] = 'Automation tool detected: ' . $signature;
                $isBot = true;
            }
        }

        // Missing critical fingerprints (bots often don't provide these)
        if (empty($fingerprintData['canvas_hash']) &&
            empty($fingerprintData['webgl_hash']) &&
            empty($fingerprintData['audio_hash'])) {
            $indicators[] = 'Missing advanced fingerprints';
            // Don't mark as bot yet, could be privacy-focused user
        }

        // Inconsistent data (e.g., mobile user agent but desktop screen resolution)
        if ($fingerprintData['mobile'] &&
            !empty($fingerprintData['screen_resolution']) &&
            strpos($fingerprintData['screen_resolution'], '1920x') !== false) {
            $indicators[] = 'Inconsistent mobile/desktop data';
        }

        // Update device record if bot detected
        if ($isBot && !$device->is_automation_tool) {
            DB::table('device_fingerprints')
                ->where('id', $device->id)
                ->update([
                    'is_automation_tool' => true,
                    'is_bot' => true,
                    'updated_at' => now(),
                ]);
        }

        return [
            'is_bot' => $isBot,
            'indicators' => $indicators,
            'confidence' => $isBot ? 'high' : 'low',
        ];
    }

    /**
     * Parse user agent string
     */
    protected function parseUserAgent(string $userAgent): array
    {
        // Simple user agent parsing (in production, use a library like Jenssegers\Agent)
        $data = [
            'browser_name' => 'Unknown',
            'browser_version' => 'Unknown',
            'os_name' => 'Unknown',
            'os_version' => 'Unknown',
            'device_type' => 'desktop',
            'device_vendor' => null,
            'platform' => 'Unknown',
            'is_mobile' => false,
        ];

        // Detect OS
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $data['os_name'] = 'Windows';
            $data['os_version'] = $matches[1];
            $data['platform'] = 'Windows';
        } elseif (preg_match('/Mac OS X ([0-9_]+)/', $userAgent, $matches)) {
            $data['os_name'] = 'macOS';
            $data['os_version'] = str_replace('_', '.', $matches[1]);
            $data['platform'] = 'macOS';
        } elseif (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            $data['os_name'] = 'Android';
            $data['os_version'] = $matches[1];
            $data['platform'] = 'Android';
            $data['is_mobile'] = true;
            $data['device_type'] = 'mobile';
        } elseif (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
            $data['os_name'] = 'iOS';
            $data['os_version'] = str_replace('_', '.', $matches[1]);
            $data['platform'] = 'iOS';
            $data['is_mobile'] = true;
            $data['device_type'] = 'mobile';
            $data['device_vendor'] = 'Apple';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $data['os_name'] = 'Linux';
            $data['platform'] = 'Linux';
        }

        // Detect browser
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $data['browser_name'] = 'Chrome';
            $data['browser_version'] = $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $data['browser_name'] = 'Firefox';
            $data['browser_version'] = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            $data['browser_name'] = 'Safari';
            $data['browser_version'] = $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $data['browser_name'] = 'Edge';
            $data['browser_version'] = $matches[1];
        }

        // Detect tablet
        if (preg_match('/(iPad|Tablet)/', $userAgent)) {
            $data['device_type'] = 'tablet';
        }

        return $data;
    }

    /**
     * Check if device is blocked
     */
    public function isDeviceBlocked(string $fingerprintHash): bool
    {
        return DB::table('device_fingerprints')
            ->where('fingerprint_hash', $fingerprintHash)
            ->where('is_blocked', true)
            ->exists();
    }

    /**
     * Block a device
     */
    public function blockDevice(string $fingerprintHash, string $reason): bool
    {
        return DB::table('device_fingerprints')
            ->where('fingerprint_hash', $fingerprintHash)
            ->update([
                'is_blocked' => true,
                'blocked_at' => now(),
                'blocked_reason' => $reason,
                'reputation' => 'malicious',
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * Track account usage for device (called by transaction manager)
     */
    public function trackAccountUsage(int $deviceId, string $accountNumber): void
    {
        $cacheKey = "device:{$deviceId}:accounts";
        $accounts = Cache::get($cacheKey, []);

        if (!in_array($accountNumber, $accounts)) {
            $accounts[] = $accountNumber;
            Cache::put($cacheKey, $accounts, now()->addDays(30));

            DB::table('device_fingerprints')
                ->where('id', $deviceId)
                ->update(['unique_accounts_count' => count($accounts)]);
        }
    }

    /**
     * Get device fingerprint statistics
     */
    public function getDeviceStats(string $fingerprintHash): ?array
    {
        $device = DB::table('device_fingerprints')
            ->where('fingerprint_hash', $fingerprintHash)
            ->first();

        if (!$device) {
            return null;
        }

        return [
            'device_id' => $device->id,
            'first_seen' => $device->first_seen_at,
            'last_seen' => $device->last_seen_at,
            'total_sessions' => $device->total_sessions,
            'total_transactions' => $device->total_transactions,
            'successful_transactions' => $device->successful_transactions,
            'failed_transactions' => $device->failed_transactions,
            'fraud_attempts' => $device->fraud_attempts,
            'unique_accounts' => $device->unique_accounts_count,
            'unique_ips' => $device->unique_ips_count,
            'trust_score' => $device->trust_score,
            'risk_score' => $device->risk_score,
            'reputation' => $device->reputation,
            'is_blocked' => $device->is_blocked,
        ];
    }
}
