<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * IP Intelligence Service
 *
 * Analyzes IP addresses for fraud detection
 * Features:
 * - VPN/Proxy/Tor detection
 * - Geolocation
 * - Datacenter/hosting detection
 * - IP reputation scoring
 * - Threat level assessment
 */
class IpIntelligenceService
{
    /**
     * Analyze IP address
     *
     * @param string $ip
     * @return array IP intelligence data
     */
    public function analyzeIp(string $ip): array
    {
        // Check cache first (1 hour TTL)
        $cacheKey = "ip_intel:{$ip}";
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return $cached;
        }

        // Get or create IP record
        $ipData = $this->getOrCreateIpRecord($ip);

        // Perform checks
        $vpnCheck = $this->checkVpnProxyTor($ip);
        $geoData = $this->getGeolocation($ip);
        $threatLevel = $this->assessThreatLevel($ipData, $vpnCheck);
        $riskScore = $this->calculateIpRiskScore($ipData, $vpnCheck);

        // Update IP record with fresh data
        $this->updateIpRecord($ip, $vpnCheck, $geoData, $riskScore, $threatLevel);

        $result = [
            'ip' => $ip,
            'country_code' => $geoData['country_code'],
            'country_name' => $geoData['country_name'],
            'city' => $geoData['city'],
            'is_vpn' => $vpnCheck['is_vpn'],
            'is_proxy' => $vpnCheck['is_proxy'],
            'is_tor' => $vpnCheck['is_tor'],
            'is_hosting' => $vpnCheck['is_hosting'],
            'is_mobile' => $vpnCheck['is_mobile'],
            'connection_type' => $vpnCheck['connection_type'],
            'risk_score' => $riskScore,
            'threat_level' => $threatLevel,
            'reputation_score' => $ipData->reputation_score ?? 50,
            'is_blocked' => $ipData->is_blocked ?? false,
            'fraud_attempts' => $ipData->fraud_attempts ?? 0,
        ];

        // Cache for 1 hour
        Cache::put($cacheKey, $result, 3600);

        return $result;
    }

    /**
     * Get or create IP intelligence record
     */
    protected function getOrCreateIpRecord(string $ip)
    {
        $record = DB::table('ip_intelligence')
            ->where('ip_address', $ip)
            ->first();

        if ($record) {
            // Update last seen
            DB::table('ip_intelligence')
                ->where('ip_address', $ip)
                ->update([
                    'last_seen_at' => now(),
                    'total_requests' => DB::raw('total_requests + 1'),
                    'requests_last_hour' => DB::raw('requests_last_hour + 1'),
                    'requests_last_day' => DB::raw('requests_last_day + 1'),
                ]);

            return $record;
        }

        // Create new record
        $id = DB::table('ip_intelligence')->insertGetId([
            'ip_address' => $ip,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'total_requests' => 1,
            'requests_last_hour' => 1,
            'requests_last_day' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('ip_intelligence')->find($id);
    }

    /**
     * Check for VPN/Proxy/Tor
     */
    protected function checkVpnProxyTor(string $ip): array
    {
        $result = [
            'is_vpn' => false,
            'is_proxy' => false,
            'is_tor' => false,
            'is_hosting' => false,
            'is_mobile' => false,
            'connection_type' => 'residential',
        ];

        // Check if Tor exit node
        $result['is_tor'] = $this->isTorExitNode($ip);

        // Check if datacenter/hosting IP
        $result['is_hosting'] = $this->isDatacenterIp($ip);

        if ($result['is_hosting']) {
            $result['connection_type'] = 'hosting';
        }

        // Check known VPN ranges (basic check)
        $result['is_vpn'] = $this->isKnownVpnIp($ip);

        // Mobile carrier detection (basic)
        $result['is_mobile'] = $this->isMobileCarrierIp($ip);

        if ($result['is_mobile']) {
            $result['connection_type'] = 'cellular';
        }

        return $result;
    }

    /**
     * Check if IP is Tor exit node
     */
    protected function isTorExitNode(string $ip): bool
    {
        // Cache Tor exit node list for 24 hours
        $torExits = Cache::remember('tor_exit_nodes', 86400, function () {
            // In production, download from https://check.torproject.org/exit-addresses
            // For now, return empty array
            return [];
        });

        return in_array($ip, $torExits);
    }

    /**
     * Check if IP belongs to datacenter/cloud provider
     */
    protected function isDatacenterIp(string $ip): bool
    {
        // Check against known datacenter IP ranges
        // This is a simplified version - in production use proper ASN database

        $datacenterRanges = [
            // AWS
            '3.0.0.0/8', '13.0.0.0/8', '18.0.0.0/8', '52.0.0.0/8', '54.0.0.0/8',
            // Google Cloud
            '34.0.0.0/8', '35.0.0.0/8',
            // Microsoft Azure
            '13.64.0.0/11', '40.0.0.0/8', '104.0.0.0/8',
            // DigitalOcean
            '104.131.0.0/16', '159.65.0.0/16', '206.189.0.0/16',
        ];

        foreach ($datacenterRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is known VPN provider
     */
    protected function isKnownVpnIp(string $ip): bool
    {
        // This is a basic check - in production use VPN detection API
        // or maintain database of known VPN IP ranges

        // For now, just check if it's a datacenter IP (VPNs often use datacenter IPs)
        return $this->isDatacenterIp($ip);
    }

    /**
     * Check if IP belongs to mobile carrier
     */
    protected function isMobileCarrierIp(string $ip): bool
    {
        // Tanzania mobile carrier IP ranges (simplified)
        $mobileRanges = [
            '196.192.0.0/10',  // Example range
            '41.220.0.0/14',   // Example range
        ];

        foreach ($mobileRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $mask) = explode('/', $range);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int)$mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Get geolocation data for IP
     */
    protected function getGeolocation(string $ip): array
    {
        // Basic geolocation without external API
        // In production, use MaxMind GeoIP2, IPGeolocation.io, or ip-api.com

        // For now, return basic data
        // You can use free APIs like ip-api.com (150 requests/min free)

        return [
            'country_code' => 'TZ', // Default to Tanzania
            'country_name' => 'Tanzania',
            'region' => 'Dar es Salaam',
            'city' => 'Dar es Salaam',
            'latitude' => -6.7924,
            'longitude' => 39.2083,
            'timezone' => 'Africa/Dar_es_Salaam',
        ];
    }

    /**
     * Assess threat level
     */
    protected function assessThreatLevel($ipData, array $vpnCheck): string
    {
        if ($ipData->is_known_attacker ?? false) {
            return 'critical';
        }

        if ($vpnCheck['is_tor']) {
            return 'high';
        }

        if (($ipData->fraud_attempts ?? 0) > 5) {
            return 'high';
        }

        if ($vpnCheck['is_vpn'] || $vpnCheck['is_hosting']) {
            return 'medium';
        }

        if (($ipData->fraud_attempts ?? 0) > 2) {
            return 'medium';
        }

        if (($ipData->fraud_attempts ?? 0) > 0) {
            return 'low';
        }

        return 'none';
    }

    /**
     * Calculate IP risk score
     */
    protected function calculateIpRiskScore($ipData, array $vpnCheck): int
    {
        $score = 0;

        // VPN/Proxy/Tor
        if ($vpnCheck['is_tor']) {
            $score += 60;
        } elseif ($vpnCheck['is_vpn']) {
            $score += 40;
        } elseif ($vpnCheck['is_proxy']) {
            $score += 40;
        }

        // Hosting/Datacenter
        if ($vpnCheck['is_hosting'] && !$vpnCheck['is_mobile']) {
            $score += 30;
        }

        // Fraud history
        $fraudAttempts = $ipData->fraud_attempts ?? 0;
        if ($fraudAttempts > 0) {
            $score += min($fraudAttempts * 20, 50);
        }

        // Known attacker
        if ($ipData->is_known_attacker ?? false) {
            $score += 80;
        }

        // Known abuser
        if ($ipData->is_known_abuser ?? false) {
            $score += 50;
        }

        // Already blocked
        if ($ipData->is_blocked ?? false) {
            return 100;
        }

        // High request rate (potential attack)
        if (($ipData->requests_last_hour ?? 0) > 100) {
            $score += 30;
        } elseif (($ipData->requests_last_hour ?? 0) > 50) {
            $score += 15;
        }

        return min($score, 100);
    }

    /**
     * Update IP record with fresh data
     */
    protected function updateIpRecord(string $ip, array $vpnCheck, array $geoData, int $riskScore, string $threatLevel): void
    {
        DB::table('ip_intelligence')
            ->where('ip_address', $ip)
            ->update([
                'country_code' => $geoData['country_code'],
                'country_name' => $geoData['country_name'],
                'region' => $geoData['region'],
                'city' => $geoData['city'],
                'latitude' => $geoData['latitude'],
                'longitude' => $geoData['longitude'],
                'timezone' => $geoData['timezone'],
                'is_vpn' => $vpnCheck['is_vpn'],
                'is_proxy' => $vpnCheck['is_proxy'],
                'is_tor' => $vpnCheck['is_tor'],
                'is_hosting' => $vpnCheck['is_hosting'],
                'is_mobile' => $vpnCheck['is_mobile'],
                'connection_type' => $vpnCheck['connection_type'],
                'risk_score' => $riskScore,
                'threat_level' => $threatLevel,
                'last_updated_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Track fraud attempt for IP
     */
    public function trackFraudAttempt(string $ip): void
    {
        DB::table('ip_intelligence')
            ->where('ip_address', $ip)
            ->update([
                'fraud_attempts' => DB::raw('fraud_attempts + 1'),
                'last_fraud_attempt_at' => now(),
                'updated_at' => now(),
            ]);

        // Clear cache to force refresh
        Cache::forget("ip_intel:{$ip}");
    }

    /**
     * Block an IP address
     */
    public function blockIp(string $ip, string $reason, ?int $durationDays = null): bool
    {
        $expiresAt = $durationDays ? now()->addDays($durationDays) : null;

        $updated = DB::table('ip_intelligence')
            ->where('ip_address', $ip)
            ->update([
                'is_blocked' => true,
                'blocked_at' => now(),
                'blocked_reason' => $reason,
                'block_expires_at' => $expiresAt,
                'updated_at' => now(),
            ]);

        if ($updated) {
            // Clear cache
            Cache::forget("ip_intel:{$ip}");

            Log::channel('transactions')->warning('IP BLOCKED', [
                'ip' => $ip,
                'reason' => $reason,
                'expires_at' => $expiresAt,
            ]);
        }

        return $updated > 0;
    }

    /**
     * Check if IP is blocked
     */
    public function isIpBlocked(string $ip): bool
    {
        $ipData = DB::table('ip_intelligence')
            ->where('ip_address', $ip)
            ->where('is_blocked', true)
            ->first();

        if (!$ipData) {
            return false;
        }

        // Check if block has expired
        if ($ipData->block_expires_at && now()->greaterThan($ipData->block_expires_at)) {
            // Unblock expired IP
            DB::table('ip_intelligence')
                ->where('ip_address', $ip)
                ->update([
                    'is_blocked' => false,
                    'updated_at' => now(),
                ]);

            Cache::forget("ip_intel:{$ip}");
            return false;
        }

        return true;
    }

    /**
     * Get IP statistics
     */
    public function getIpStats(string $ip): ?array
    {
        $ipData = DB::table('ip_intelligence')
            ->where('ip_address', $ip)
            ->first();

        if (!$ipData) {
            return null;
        }

        return [
            'ip' => $ipData->ip_address,
            'first_seen' => $ipData->first_seen_at,
            'last_seen' => $ipData->last_seen_at,
            'total_requests' => $ipData->total_requests,
            'successful_requests' => $ipData->successful_requests,
            'failed_requests' => $ipData->failed_requests,
            'fraud_attempts' => $ipData->fraud_attempts,
            'unique_accounts' => $ipData->unique_accounts,
            'unique_devices' => $ipData->unique_devices,
            'risk_score' => $ipData->risk_score,
            'threat_level' => $ipData->threat_level,
            'is_blocked' => $ipData->is_blocked,
            'country_code' => $ipData->country_code,
            'is_vpn' => $ipData->is_vpn,
            'is_tor' => $ipData->is_tor,
        ];
    }
}
