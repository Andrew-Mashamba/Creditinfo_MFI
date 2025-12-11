<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Verify Webhook Signature Middleware
 *
 * Verifies signatures from external webhook/callback endpoints
 * to ensure requests are legitimate and haven't been tampered with.
 *
 * This replaces CSRF protection for stateless webhook endpoints.
 *
 * @package App\Http\Middleware
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $provider  The webhook provider (gepg, tigopesa, etc.)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $provider = 'default')
    {
        // Get signature from headers (common patterns)
        $signature = $request->header('X-Signature')
            ?? $request->header('X-Hub-Signature')
            ?? $request->header('X-Webhook-Signature')
            ?? $request->input('signature');

        if (!$signature) {
            Log::warning('Webhook signature missing', [
                'provider' => $provider,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'error' => 'Signature verification failed',
                'message' => 'Missing signature header',
            ], 403);
        }

        // Verify signature based on provider
        $isValid = $this->verifySignature($request, $signature, $provider);

        if (!$isValid) {
            Log::warning('Webhook signature verification failed', [
                'provider' => $provider,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'signature' => substr($signature, 0, 20) . '...',
            ]);

            return response()->json([
                'error' => 'Signature verification failed',
                'message' => 'Invalid signature',
            ], 403);
        }

        // Signature valid - proceed
        Log::info('Webhook signature verified successfully', [
            'provider' => $provider,
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }

    /**
     * Verify webhook signature based on provider
     *
     * @param  Request  $request
     * @param  string  $signature
     * @param  string  $provider
     * @return bool
     */
    protected function verifySignature(Request $request, string $signature, string $provider): bool
    {
        switch ($provider) {
            case 'gepg':
                return $this->verifyGepgSignature($request, $signature);

            case 'tigopesa':
                return $this->verifyTigoPesaSignature($request, $signature);

            case 'mpesa':
                return $this->verifyMpesaSignature($request, $signature);

            default:
                return $this->verifyDefaultSignature($request, $signature);
        }
    }

    /**
     * Verify GePG webhook signature
     *
     * @param  Request  $request
     * @param  string  $signature
     * @return bool
     */
    protected function verifyGepgSignature(Request $request, string $signature): bool
    {
        $secret = config('services.gepg.webhook_secret');

        if (!$secret) {
            Log::error('GePG webhook secret not configured');
            return false;
        }

        // GePG typically uses HMAC-SHA256
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify Tigo Pesa webhook signature
     *
     * @param  Request  $request
     * @param  string  $signature
     * @return bool
     */
    protected function verifyTigoPesaSignature(Request $request, string $signature): bool
    {
        $secret = config('services.tigopesa.webhook_secret');

        if (!$secret) {
            Log::error('Tigo Pesa webhook secret not configured');
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify M-Pesa webhook signature
     *
     * @param  Request  $request
     * @param  string  $signature
     * @return bool
     */
    protected function verifyMpesaSignature(Request $request, string $signature): bool
    {
        $secret = config('services.mpesa.webhook_secret');

        if (!$secret) {
            Log::error('M-Pesa webhook secret not configured');
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify default webhook signature (generic HMAC-SHA256)
     *
     * @param  Request  $request
     * @param  string  $signature
     * @return bool
     */
    protected function verifyDefaultSignature(Request $request, string $signature): bool
    {
        $secret = config('app.webhook_secret');

        if (!$secret) {
            Log::error('Default webhook secret not configured');
            // In development, allow without signature if no secret configured
            return app()->environment('local') || app()->environment('development');
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
