<?php

namespace App\Http\Middleware;

use App\Services\FraudDetectionService;
use App\Services\DeviceFingerprintService;
use App\Services\IpIntelligenceService;
use App\Services\EmailPhoneIntelligenceService;
use App\Services\AnomalyDetectionService;
use App\Services\SessionAnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FraudDetectionMiddleware
{
    protected $fraudDetectionService;
    protected $deviceFingerprintService;
    protected $ipIntelligenceService;
    protected $emailPhoneService;
    protected $anomalyDetectionService;
    protected $sessionAnalyticsService;

    public function __construct(
        FraudDetectionService $fraudDetectionService,
        DeviceFingerprintService $deviceFingerprintService,
        IpIntelligenceService $ipIntelligenceService,
        EmailPhoneIntelligenceService $emailPhoneService,
        AnomalyDetectionService $anomalyDetectionService,
        SessionAnalyticsService $sessionAnalyticsService
    ) {
        $this->fraudDetectionService = $fraudDetectionService;
        $this->deviceFingerprintService = $deviceFingerprintService;
        $this->ipIntelligenceService = $ipIntelligenceService;
        $this->emailPhoneService = $emailPhoneService;
        $this->anomalyDetectionService = $anomalyDetectionService;
        $this->sessionAnalyticsService = $sessionAnalyticsService;
    }

    /**
     * Handle an incoming request and perform fraud detection
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip fraud detection for certain routes (health checks, etc.)
        if ($this->shouldSkipFraudDetection($request)) {
            return $next($request);
        }

        try {
            // Extract transaction data from request
            $transactionData = $this->extractTransactionData($request);

            // ============================================================
            // ADVANCED FRAUD DETECTION - ALL 5 SERVICES
            // ============================================================

            $advancedChecks = [];

            // 1. DEVICE FINGERPRINTING
            $deviceData = $this->deviceFingerprintService->processFingerprint($request);
            $advancedChecks['device'] = $deviceData;

            // 2. IP INTELLIGENCE
            $ipData = $this->ipIntelligenceService->analyzeIp($request->ip());
            $advancedChecks['ip'] = $ipData;

            // Block if IP is blacklisted
            if ($ipData['is_blocked'] ?? false) {
                throw new \Exception('IP address is blocked due to previous fraud attempts');
            }

            // Block Tor traffic
            if ($ipData['is_tor'] ?? false) {
                $this->ipIntelligenceService->trackFraudAttempt($request->ip());
                throw new \Exception('Tor exit node detected - transaction blocked');
            }

            // 3. SESSION ANALYTICS - Start/continue session
            $sessionId = $this->sessionAnalyticsService->startSession(
                $request,
                $deviceData['device_id'] ?? null,
                $transactionData['api_key_id'] ?? null
            );
            $advancedChecks['session_id'] = $sessionId;

            // 4. EMAIL/PHONE INTELLIGENCE (if present in request)
            if ($request->has('email')) {
                $emailData = $this->emailPhoneService->validateEmail($request->input('email'));
                $advancedChecks['email'] = $emailData;

                // Block disposable emails
                if ($emailData['is_disposable'] ?? false) {
                    throw new \Exception('Disposable email addresses are not allowed');
                }
            }

            if ($request->has('phone') || $request->has('phone_number')) {
                $phone = $request->input('phone') ?? $request->input('phone_number');
                $phoneData = $this->emailPhoneService->validatePhone($phone);
                $advancedChecks['phone'] = $phoneData;
            }

            // 5. ML ANOMALY DETECTION (for transactions with amounts)
            if (!empty($transactionData['source_account']) && !empty($transactionData['amount'])) {
                $anomalyData = $this->anomalyDetectionService->detectTransactionAnomaly(
                    [
                        'amount' => $transactionData['amount'],
                        'service_type' => $transactionData['service_type'],
                    ],
                    $transactionData['source_account'],
                    $transactionData['service_type']
                );
                $advancedChecks['anomaly'] = $anomalyData;
            }

            // ============================================================
            // TRADITIONAL FRAUD CHECKS
            // ============================================================

            $fraudCheck = $this->fraudDetectionService->checkTransaction($transactionData, $request);

            // ============================================================
            // COMBINE ALL RISK SCORES
            // ============================================================

            $combinedRiskScore = $this->calculateCombinedRiskScore($fraudCheck, $advancedChecks);
            $riskLevel = $this->getRiskLevel($combinedRiskScore);

            // Add all results to request for later use
            $request->merge([
                'fraud_check_result' => $fraudCheck,
                'advanced_fraud_checks' => $advancedChecks,
                'combined_risk_score' => $combinedRiskScore,
                'risk_level' => $riskLevel,
                'session_id' => $sessionId,
            ]);

            // ============================================================
            // BLOCKING LOGIC
            // ============================================================

            // Critical risk = block immediately
            if ($riskLevel === 'critical' || $combinedRiskScore >= 80) {
                $this->trackFraudAttempt($request, $transactionData, $advancedChecks);
                throw new \Exception("Transaction blocked: Critical fraud risk detected (score: {$combinedRiskScore})");
            }

            // High risk = block for certain service types
            if ($riskLevel === 'high' || $combinedRiskScore >= 60) {
                $highRiskServices = ['external_transfer', 'utilities_payment'];
                if (in_array($transactionData['service_type'], $highRiskServices)) {
                    $this->trackFraudAttempt($request, $transactionData, $advancedChecks);
                    throw new \Exception("Transaction blocked: High fraud risk detected for sensitive service (score: {$combinedRiskScore})");
                }
            }

            // ============================================================
            // TRACK REQUEST IN SESSION
            // ============================================================

            $this->sessionAnalyticsService->trackRequest(
                $sessionId,
                $request,
                true, // success (for now - will be updated after transaction completes)
                $transactionData['amount'] ?? null
            );

            // Check if session itself is suspicious
            if ($this->sessionAnalyticsService->isSessionSuspicious($sessionId)) {
                Log::channel('transactions')->warning('SUSPICIOUS SESSION DETECTED', [
                    'session_id' => $sessionId,
                    'session_stats' => $this->sessionAnalyticsService->getSessionStats($sessionId),
                ]);
            }

            // ============================================================
            // LOGGING
            // ============================================================

            if ($combinedRiskScore > 30) {
                Log::channel('transactions')->warning('SUSPICIOUS TRANSACTION ALLOWED', [
                    'combined_risk_score' => $combinedRiskScore,
                    'risk_level' => $riskLevel,
                    'fraud_score' => $fraudCheck['fraud_score'],
                    'device_risk' => $deviceData['risk_score'] ?? 0,
                    'ip_risk' => $ipData['risk_score'] ?? 0,
                    'anomaly_score' => $anomalyData['anomaly_score'] ?? 0,
                    'indicators' => array_merge(
                        $fraudCheck['indicators'] ?? [],
                        $advancedChecks['device']['warnings'] ?? [],
                        $advancedChecks['ip']['is_vpn'] ? ['VPN detected'] : []
                    ),
                    'service_type' => $transactionData['service_type'],
                    'account' => $transactionData['source_account'] ?? null,
                    'amount' => $transactionData['amount'] ?? null,
                    'ip' => $request->ip(),
                    'session_id' => $sessionId,
                ]);
            }

            return $next($request);

        } catch (\Exception $e) {
            // Fraud detected - return error response
            Log::channel('transactions')->error('FRAUD CHECK BLOCKED TRANSACTION', [
                'error' => $e->getMessage(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'data' => $request->except(['password', 'api_key']),
                'advanced_checks' => $advancedChecks ?? null,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'FRAUD_DETECTION_BLOCKED',
            ], 403);
        }
    }

    /**
     * Calculate combined risk score from all checks
     */
    protected function calculateCombinedRiskScore(array $fraudCheck, array $advancedChecks): int
    {
        $scores = [];

        // Traditional fraud detection score
        $scores[] = $fraudCheck['fraud_score'] ?? 0;

        // Device fingerprint risk
        $scores[] = $advancedChecks['device']['risk_score'] ?? 0;

        // IP intelligence risk
        $scores[] = $advancedChecks['ip']['risk_score'] ?? 0;

        // Email risk (if checked)
        if (isset($advancedChecks['email'])) {
            $scores[] = $advancedChecks['email']['risk_score'] ?? 0;
        }

        // Phone risk (if checked)
        if (isset($advancedChecks['phone'])) {
            $scores[] = $advancedChecks['phone']['risk_score'] ?? 0;
        }

        // Anomaly detection score (if checked)
        if (isset($advancedChecks['anomaly'])) {
            $scores[] = $advancedChecks['anomaly']['anomaly_score'] ?? 0;
        }

        // Use maximum score (most suspicious indicator wins)
        // Alternative: weighted average, but max is more conservative
        return empty($scores) ? 0 : (int) max($scores);
    }

    /**
     * Get risk level from score
     */
    protected function getRiskLevel(int $score): string
    {
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        if ($score >= 20) return 'low';
        return 'minimal';
    }

    /**
     * Track fraud attempt across all services
     */
    protected function trackFraudAttempt(Request $request, array $transactionData, array $advancedChecks): void
    {
        // Track in IP intelligence
        $this->ipIntelligenceService->trackFraudAttempt($request->ip());

        // Track in device fingerprint
        if (isset($advancedChecks['device']['device_id'])) {
            $this->deviceFingerprintService->markDeviceAsFraudulent(
                $advancedChecks['device']['device_id']
            );
        }

        // Track in session analytics
        if (isset($advancedChecks['session_id'])) {
            $this->sessionAnalyticsService->trackFraudFlag($advancedChecks['session_id']);
        }
    }

    /**
     * Extract transaction data from request
     */
    protected function extractTransactionData(Request $request): array
    {
        $serviceType = $this->determineServiceType($request);

        return [
            'service_type' => $serviceType,
            'service_name' => $this->getServiceName($serviceType),
            'endpoint' => $request->path(),
            'api_key_id' => $request->developer_api_key->id ?? null,
            'api_environment' => $request->api_environment ?? 'development',
            'source_account' => $request->input('source_account')
                            ?? $request->input('account_number')
                            ?? $request->input('debit_account')
                            ?? null,
            'destination_account' => $request->input('destination_account')
                                  ?? $request->input('credit_account')
                                  ?? $request->input('beneficiary_account')
                                  ?? null,
            'amount' => $request->input('amount') ? (float) $request->input('amount') : null,
            'currency' => $request->input('currency', 'TZS'),
            'description' => $request->input('description') ?? $request->input('narration') ?? null,
            'request_payload' => $request->all(),
        ];
    }

    /**
     * Determine service type from request path
     */
    protected function determineServiceType(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'transfers/internal')) return 'internal_transfer';
        if (str_contains($path, 'transfers/external')) return 'external_transfer';
        if (str_contains($path, 'utilities')) return 'utilities_payment';
        if (str_contains($path, 'luku')) return 'luku';
        if (str_contains($path, 'gepg')) return 'gepg';
        if (str_contains($path, 'control-number')) return 'control_number_payment';
        if (str_contains($path, 'direct-debit')) return 'direct_debit';
        if (str_contains($path, 'pay-by-link')) return 'payment_link';
        if (str_contains($path, 'sms')) return 'sms_notification';
        if (str_contains($path, 'account/lookup')) return 'account_lookup';
        if (str_contains($path, 'account')) return 'account';
        if (str_contains($path, 'statements/mini')) return 'mini_statement';
        if (str_contains($path, 'statements/full')) return 'full_statement';

        return 'unknown';
    }

    /**
     * Get human-readable service name
     */
    protected function getServiceName(string $serviceType): string
    {
        $names = [
            'internal_transfer' => 'Internal Transfer',
            'external_transfer' => 'External Transfer',
            'utilities_payment' => 'Utilities Payment',
            'luku' => 'LUKU Token Purchase',
            'gepg' => 'GEPG Bill Generation',
            'control_number_payment' => 'Control Number Payment',
            'direct_debit' => 'Direct Debit',
            'payment_link' => 'Payment Link',
            'sms_notification' => 'SMS Notification',
            'account' => 'Account Details',
            'account_lookup' => 'Account Lookup',
            'mini_statement' => 'Mini Statement',
            'full_statement' => 'Full Statement',
        ];

        return $names[$serviceType] ?? 'Unknown Service';
    }

    /**
     * Determine if fraud detection should be skipped
     */
    protected function shouldSkipFraudDetection(Request $request): bool
    {
        $skipPaths = [
            'api/status',
            'api/health',
            'api/external/v1/account/', // GET requests for account details (read-only)
        ];

        $path = $request->path();

        foreach ($skipPaths as $skipPath) {
            if (str_contains($path, $skipPath) && $request->method() === 'GET') {
                return true;
            }
        }

        return false;
    }
}
