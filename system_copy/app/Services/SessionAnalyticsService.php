<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/**
 * Session Analytics Service
 *
 * Tracks and analyzes user sessions for behavioral patterns
 * Features:
 * - Session tracking
 * - Request pattern analysis
 * - Bot detection
 * - Timing anomaly detection
 * - Impossible travel detection
 */
class SessionAnalyticsService
{
    /**
     * Start or get existing session
     *
     * @param Request $request
     * @param int|null $deviceFingerprintId
     * @param int|null $apiKeyId
     * @return string Session ID
     */
    public function startSession(Request $request, ?int $deviceFingerprintId = null, ?int $apiKeyId = null): string
    {
        // Check if session already exists (from request attribute)
        $existingSessionId = $request->attributes->get('session_id');

        if ($existingSessionId) {
            // Update existing session as active
            DB::table('session_analytics')
                ->where('session_id', $existingSessionId)
                ->where('is_active', true)
                ->update(['updated_at' => now()]);

            return $existingSessionId;
        }

        // Create new session
        $sessionId = Str::random(64);

        DB::table('session_analytics')->insert([
            'session_id' => $sessionId,
            'api_key_id' => $apiKeyId ?? $request->developer_api_key->id ?? null,
            'device_fingerprint_id' => $deviceFingerprintId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_start' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Store in request attributes for subsequent calls
        $request->attributes->set('session_id', $sessionId);

        return $sessionId;
    }

    /**
     * Track a request within session
     *
     * @param string $sessionId
     * @param Request $request
     * @param bool $success
     * @param float|null $amount
     * @return void
     */
    public function trackRequest(string $sessionId, Request $request, bool $success = true, ?float $amount = null): void
    {
        $session = DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return;
        }

        $now = now()->timestamp * 1000; // milliseconds

        // Update request pattern
        $requestPattern = json_decode($session->request_pattern ?? '[]', true);
        $requestPattern[] = $now;

        // Keep only last 100 timestamps
        $requestPattern = array_slice($requestPattern, -100);

        // Calculate intervals
        $intervals = $this->calculateIntervals($requestPattern);

        // Track endpoint sequence
        $endpointSequence = json_decode($session->endpoint_sequence ?? '[]', true);
        $endpointSequence[] = $request->path();

        // Keep only last 50 endpoints
        $endpointSequence = array_slice($endpointSequence, -50);

        // Prepare update data
        $updateData = [
            'total_requests' => $session->total_requests + 1,
            'successful_requests' => $success ? $session->successful_requests + 1 : $session->successful_requests,
            'failed_requests' => !$success ? $session->failed_requests + 1 : $session->failed_requests,
            'request_pattern' => json_encode($requestPattern),
            'endpoint_sequence' => json_encode($endpointSequence),
            'updated_at' => now(),
        ];

        if (!empty($intervals)) {
            $updateData['min_request_interval_ms'] = min($intervals);
            $updateData['max_request_interval_ms'] = max($intervals);
            $updateData['avg_request_interval_ms'] = (int)(array_sum($intervals) / count($intervals));
        }

        if ($amount) {
            $updateData['total_transaction_amount'] = $session->total_transaction_amount + $amount;
        }

        // Update session
        DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->update($updateData);

        // Detect anomalies
        $this->detectSessionAnomalies($sessionId);
    }

    /**
     * Calculate intervals between requests
     */
    protected function calculateIntervals(array $timestamps): array
    {
        if (count($timestamps) < 2) {
            return [];
        }

        $intervals = [];
        for ($i = 1; $i < count($timestamps); $i++) {
            $intervals[] = $timestamps[$i] - $timestamps[$i - 1];
        }

        return $intervals;
    }

    /**
     * Detect session anomalies
     */
    protected function detectSessionAnomalies(string $sessionId): void
    {
        $session = DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return;
        }

        $anomalyScore = 0;
        $anomalyIndicators = [];
        $botSuspected = false;

        // 1. Too fast requests (bot indicator)
        if ($session->min_request_interval_ms && $session->min_request_interval_ms < 100) {
            $anomalyScore += 40;
            $anomalyIndicators[] = 'Extremely fast requests (< 100ms)';
            $botSuspected = true;
        } elseif ($session->min_request_interval_ms && $session->min_request_interval_ms < 500) {
            $anomalyScore += 20;
            $anomalyIndicators[] = 'Very fast requests (< 500ms)';
        }

        // 2. Perfect timing (bot indicator)
        if ($session->min_request_interval_ms && $session->max_request_interval_ms) {
            $variance = $session->max_request_interval_ms - $session->min_request_interval_ms;
            if ($variance < 50 && $session->total_requests > 5) {
                $anomalyScore += 35;
                $anomalyIndicators[] = 'Perfect timing intervals (< 50ms variance)';
                $botSuspected = true;
            } elseif ($variance < 200 && $session->total_requests > 10) {
                $anomalyScore += 20;
                $anomalyIndicators[] = 'Consistent timing pattern';
            }
        }

        // 3. High velocity
        $durationMinutes = now()->diffInMinutes($session->session_start);
        $durationMinutes = max($durationMinutes, 1); // Avoid division by zero

        $requestsPerMinute = $session->total_requests / $durationMinutes;
        if ($requestsPerMinute > 10) {
            $anomalyScore += 30;
            $anomalyIndicators[] = "High velocity ({$requestsPerMinute} req/min)";
        } elseif ($requestsPerMinute > 5) {
            $anomalyScore += 15;
            $anomalyIndicators[] = "Elevated velocity ({$requestsPerMinute} req/min)";
        }

        // 4. High failure rate
        if ($session->total_requests > 5) {
            $failureRate = $session->failed_requests / $session->total_requests;
            if ($failureRate > 0.5) {
                $anomalyScore += 25;
                $anomalyIndicators[] = "High failure rate (" . round($failureRate * 100) . "%)";
            }
        }

        // 5. Unusual endpoint pattern
        $endpointSequence = json_decode($session->endpoint_sequence ?? '[]', true);
        if (!empty($endpointSequence) && $this->hasUnusualEndpointPattern($endpointSequence)) {
            $anomalyScore += 15;
            $anomalyIndicators[] = 'Unusual endpoint access pattern';
        }

        // Update anomaly data
        if ($anomalyScore > 0) {
            $riskLevel = $this->getRiskLevel($anomalyScore);

            DB::table('session_analytics')
                ->where('session_id', $sessionId)
                ->update([
                    'anomaly_score' => $anomalyScore,
                    'anomaly_indicators' => json_encode($anomalyIndicators),
                    'bot_suspected' => $botSuspected,
                    'unusual_velocity' => $requestsPerMinute > 5,
                    'unusual_timing' => isset($variance) && $variance < 200,
                    'risk_score' => $anomalyScore,
                    'risk_level' => $riskLevel,
                ]);
        }
    }

    /**
     * Check for unusual endpoint patterns
     */
    protected function hasUnusualEndpointPattern(array $endpoints): bool
    {
        if (count($endpoints) < 5) {
            return false;
        }

        // Check if accessing same endpoint repeatedly (bot testing)
        $lastFive = array_slice($endpoints, -5);
        if (count(array_unique($lastFive)) === 1) {
            return true;
        }

        // Check if accessing endpoints in strict sequence multiple times
        $patterns = array_chunk($endpoints, 3);
        $patternCounts = array_count_values(array_map('serialize', $patterns));
        $maxRepeat = max($patternCounts);

        return $maxRepeat >= 3; // Same 3-endpoint pattern repeated 3+ times
    }

    /**
     * Get risk level from score
     */
    protected function getRiskLevel(int $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }

    /**
     * End a session
     */
    public function endSession(string $sessionId): void
    {
        $session = DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return;
        }

        $durationSeconds = now()->diffInSeconds($session->session_start);

        DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->update([
                'session_end' => now(),
                'duration_seconds' => $durationSeconds,
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * Track fraud flag in session
     */
    public function trackFraudFlag(string $sessionId): void
    {
        DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->update([
                'fraud_flags' => DB::raw('fraud_flags + 1'),
                'updated_at' => now(),
            ]);
    }

    /**
     * Track completed transaction
     */
    public function trackTransaction(string $sessionId, string $serviceType, string $accountNumber, float $amount): void
    {
        $session = DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return;
        }

        // Update services used
        $servicesUsed = json_decode($session->services_used ?? '[]', true);
        if (!in_array($serviceType, $servicesUsed)) {
            $servicesUsed[] = $serviceType;
        }

        // Update accounts accessed
        $accountsAccessed = json_decode($session->accounts_accessed ?? '[]', true);
        if (!in_array($accountNumber, $accountsAccessed)) {
            $accountsAccessed[] = $accountNumber;
        }

        DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->update([
                'transactions_completed' => $session->transactions_completed + 1,
                'total_transaction_amount' => $session->total_transaction_amount + $amount,
                'services_used' => json_encode($servicesUsed),
                'accounts_accessed' => json_encode($accountsAccessed),
                'updated_at' => now(),
            ]);
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(string $sessionId): ?array
    {
        $session = DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return null;
        }

        $durationSeconds = $session->duration_seconds
            ?? now()->diffInSeconds($session->session_start);

        return [
            'session_id' => $session->session_id,
            'is_active' => $session->is_active,
            'duration_seconds' => $durationSeconds,
            'total_requests' => $session->total_requests,
            'successful_requests' => $session->successful_requests,
            'failed_requests' => $session->failed_requests,
            'transactions_completed' => $session->transactions_completed,
            'total_amount' => $session->total_transaction_amount,
            'anomaly_score' => $session->anomaly_score,
            'risk_level' => $session->risk_level,
            'bot_suspected' => $session->bot_suspected,
            'fraud_flags' => $session->fraud_flags,
            'avg_interval_ms' => $session->avg_request_interval_ms,
        ];
    }

    /**
     * Check if session is suspicious
     */
    public function isSessionSuspicious(string $sessionId): bool
    {
        $session = DB::table('session_analytics')
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return false;
        }

        return $session->bot_suspected
            || $session->anomaly_score >= 60
            || $session->fraud_flags >= 3
            || in_array($session->risk_level, ['high', 'critical']);
    }

    /**
     * Get active sessions with high risk
     */
    public function getHighRiskSessions(int $limit = 20): array
    {
        return DB::table('session_analytics')
            ->where('is_active', true)
            ->whereIn('risk_level', ['high', 'critical'])
            ->orderBy('risk_score', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Clean up old inactive sessions
     */
    public function cleanupOldSessions(int $daysOld = 7): int
    {
        return DB::table('session_analytics')
            ->where('is_active', false)
            ->where('updated_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
