<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Verify CAPTCHA Middleware
 *
 * Verifies CAPTCHA for high-risk operations
 * Supports Google reCAPTCHA v2, v3, and hCaptcha
 *
 * @package App\Http\Middleware
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class VerifyCaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $scoreThreshold  Minimum score for reCAPTCHA v3 (0.0 to 1.0)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $scoreThreshold = null)
    {
        // Skip CAPTCHA verification in local development if configured
        if (config('captcha.skip_local') && app()->environment('local')) {
            return $next($request);
        }

        // Skip if CAPTCHA is disabled
        if (!config('captcha.enabled', false)) {
            return $next($request);
        }

        // Check if IP has too many failed CAPTCHA attempts
        if ($this->hasExcessiveFailedAttempts($request->ip())) {
            return response()->json([
                'success' => false,
                'message' => 'Too many failed CAPTCHA attempts. Please try again later.',
                'error_code' => 'CAPTCHA_RATE_LIMIT'
            ], 429);
        }

        // Get CAPTCHA response from request
        $captchaResponse = $this->getCaptchaResponse($request);

        if (!$captchaResponse) {
            return response()->json([
                'success' => false,
                'message' => 'CAPTCHA verification required',
                'error_code' => 'CAPTCHA_MISSING'
            ], 422);
        }

        // Verify CAPTCHA
        $provider = config('captcha.provider', 'recaptcha');
        $verificationResult = $this->verifyCaptcha($captchaResponse, $provider, $request);

        if (!$verificationResult['success']) {
            $this->recordFailedAttempt($request->ip());

            Log::warning('CAPTCHA Verification Failed', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'provider' => $provider,
                'error' => $verificationResult['error'] ?? 'Unknown error',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'CAPTCHA verification failed',
                'error_code' => 'CAPTCHA_FAILED',
                'details' => config('app.debug') ? $verificationResult['error'] : null,
            ], 422);
        }

        // For reCAPTCHA v3, check score threshold
        if ($scoreThreshold && isset($verificationResult['score'])) {
            $minScore = (float) $scoreThreshold;
            if ($verificationResult['score'] < $minScore) {
                $this->recordFailedAttempt($request->ip());

                Log::warning('CAPTCHA Score Too Low', [
                    'ip' => $request->ip(),
                    'endpoint' => $request->path(),
                    'score' => $verificationResult['score'],
                    'threshold' => $minScore,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'CAPTCHA score too low. Please try again.',
                    'error_code' => 'CAPTCHA_SCORE_LOW'
                ], 422);
            }
        }

        // CAPTCHA verified successfully
        $this->clearFailedAttempts($request->ip());

        return $next($request);
    }

    /**
     * Get CAPTCHA response from request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getCaptchaResponse(Request $request): ?string
    {
        // Check various possible field names
        return $request->input('g-recaptcha-response')
            ?? $request->input('h-captcha-response')
            ?? $request->input('captcha_token')
            ?? $request->input('captcha')
            ?? null;
    }

    /**
     * Verify CAPTCHA with provider
     *
     * @param  string  $response
     * @param  string  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function verifyCaptcha(string $response, string $provider, Request $request): array
    {
        switch ($provider) {
            case 'recaptcha':
            case 'recaptcha_v2':
            case 'recaptcha_v3':
                return $this->verifyRecaptcha($response, $request);

            case 'hcaptcha':
                return $this->verifyHcaptcha($response, $request);

            default:
                Log::error('Unknown CAPTCHA provider', ['provider' => $provider]);
                return ['success' => false, 'error' => 'Unknown CAPTCHA provider'];
        }
    }

    /**
     * Verify Google reCAPTCHA
     *
     * @param  string  $response
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function verifyRecaptcha(string $response, Request $request): array
    {
        $secretKey = config('captcha.recaptcha_secret');

        if (!$secretKey) {
            Log::error('reCAPTCHA secret key not configured');
            return ['success' => false, 'error' => 'CAPTCHA not configured'];
        }

        try {
            $verifyResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => $request->ip(),
            ]);

            if (!$verifyResponse->successful()) {
                return ['success' => false, 'error' => 'CAPTCHA verification request failed'];
            }

            $result = $verifyResponse->json();

            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => 'CAPTCHA verification failed',
                    'error_codes' => $result['error-codes'] ?? [],
                ];
            }

            return [
                'success' => true,
                'score' => $result['score'] ?? null, // For reCAPTCHA v3
                'action' => $result['action'] ?? null,
                'hostname' => $result['hostname'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception', [
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'CAPTCHA verification exception'];
        }
    }

    /**
     * Verify hCaptcha
     *
     * @param  string  $response
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function verifyHcaptcha(string $response, Request $request): array
    {
        $secretKey = config('captcha.hcaptcha_secret');

        if (!$secretKey) {
            Log::error('hCaptcha secret key not configured');
            return ['success' => false, 'error' => 'CAPTCHA not configured'];
        }

        try {
            $verifyResponse = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => $request->ip(),
            ]);

            if (!$verifyResponse->successful()) {
                return ['success' => false, 'error' => 'CAPTCHA verification request failed'];
            }

            $result = $verifyResponse->json();

            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => 'CAPTCHA verification failed',
                    'error_codes' => $result['error-codes'] ?? [],
                ];
            }

            return [
                'success' => true,
                'hostname' => $result['hostname'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('hCaptcha verification exception', [
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'CAPTCHA verification exception'];
        }
    }

    /**
     * Check if IP has excessive failed CAPTCHA attempts
     *
     * @param  string  $ip
     * @return bool
     */
    protected function hasExcessiveFailedAttempts(string $ip): bool
    {
        $cacheKey = "captcha_failed:{$ip}";
        $failedAttempts = Cache::get($cacheKey, 0);

        return $failedAttempts >= config('captcha.max_failed_attempts', 5);
    }

    /**
     * Record failed CAPTCHA attempt
     *
     * @param  string  $ip
     * @return void
     */
    protected function recordFailedAttempt(string $ip): void
    {
        $cacheKey = "captcha_failed:{$ip}";
        $failedAttempts = Cache::get($cacheKey, 0);

        Cache::put($cacheKey, $failedAttempts + 1, 3600); // Store for 1 hour
    }

    /**
     * Clear failed CAPTCHA attempts
     *
     * @param  string  $ip
     * @return void
     */
    protected function clearFailedAttempts(string $ip): void
    {
        $cacheKey = "captcha_failed:{$ip}";
        Cache::forget($cacheKey);
    }
}
