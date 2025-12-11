<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use App\Mail\OTP;
use App\Jobs\SendOtpNotification;

class OtpService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 5000;
    private const MAX_ATTEMPTS = 300;
    private const ATTEMPT_WINDOW_MINUTES = 1500;
    private const IP_MAX_ATTEMPTS = 5000;
    private const IP_ATTEMPT_WINDOW_MINUTES = 3000;

    private $smsService;
    private $logger;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
        $this->logger = Log::channel('otp');
    }

    /**
     * Generate a new OTP for a user
     *
     * @param User $user
     * @return array
     */
    public function generateOtp(User $user): array
    {
        $this->logger->info('=== OTP GENERATION STARTED ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user->id,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_method' => request()->method(),
            'request_url' => request()->url()
        ]);

        try {
            // Check IP-based rate limiting
            $ipKey = 'otp_attempts_ip:' . request()->ip();
            $attempts = Cache::get($ipKey, 0);
            
            $this->logger->info('Rate limit check - IP', [
                'ip_key' => $ipKey,
                'current_attempts' => $attempts,
                'limit' => 5
            ]);

            if ($attempts >= 5) {
                $this->logger->warning('IP rate limit exceeded for OTP generation', [
                    'ip' => request()->ip(),
                    'attempts' => $attempts,
                    'timestamp' => now()->toDateTimeString()
                ]);
                return ['success' => false, 'message' => 'Too many attempts. Please try again later.'];
            }

            // Check user-specific maximum attempts
            $userKey = 'otp_attempts_user:' . $user->id;
            $userAttempts = Cache::get($userKey, 0);

            if ($userAttempts >= 300) {
                $this->logger->warning('User rate limit exceeded for OTP generation', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'attempts' => $userAttempts,
                    'timestamp' => now()->toDateTimeString()
                ]);
                return ['success' => false, 'message' => 'Maximum attempts reached. Please try again later.'];
            }

            // Generate OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(5);

            $this->logger->info('OTP generated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'otp_length' => strlen($otp),
                'expires_at' => $expiresAt->toDateTimeString(),
                'timestamp' => now()->toDateTimeString()
            ]);

            // Store OTP in cache
            Cache::put('otp:' . $user->id, $otp, $expiresAt);
            Cache::put($ipKey, $attempts + 1, now()->addMinutes(30));
            Cache::put($userKey, $userAttempts + 1, now()->addMinutes(30));

            // Update user's OTP fields
            $user->otp = $otp;
            $user->otp_expires_at = $expiresAt;
            $user->save();

            // Dispatch OTP notification job
            try {
                $this->logger->info('Dispatching SendOtpNotification job', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'otp_masked' => substr($otp, 0, 2) . '****',
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                SendOtpNotification::dispatch($user, $otp);
                
                $this->logger->info('✓ SendOtpNotification job dispatched successfully', [
                    'user_id' => $user->id,
                    'timestamp' => now()->toDateTimeString()
                ]);
            } catch (\Exception $e) {
                $this->logger->error('✗ Failed to dispatch OTP notification job', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'exception_class' => get_class($e),
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString()
                ]);
                return ['success' => false, 'message' => 'An error occurred while sending the verification code.'];
            }

            $this->logger->info('=== OTP GENERATION COMPLETED SUCCESSFULLY ===', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'otp_set' => !empty($user->otp),
                'timestamp' => now()->toDateTimeString()
            ]);

            return ['success' => true, 'message' => 'Verification code has been sent to your email and phone number.'];
        } catch (\Exception $e) {
            $this->logger->error('OTP generation failed with exception', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return ['success' => false, 'message' => 'An error occurred while generating the verification code.'];
        }
    }

    /**
     * Validate OTP for a user
     *
     * @param User $user
     * @param string $otp
     * @return array
     */
    public function validateOtp(User $user, string $otp): array
    {
        $this->logger->info('Starting OTP validation process', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user->id,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'otp_length' => strlen($otp)
        ]);

        try {
            $cachedOtp = Cache::get('otp:' . $user->id);

            if (!$cachedOtp) {
                $this->logger->warning('OTP validation failed: No OTP found in cache', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'timestamp' => now()->toDateTimeString()
                ]);
                return ['success' => false, 'message' => 'Verification code has expired. Please request a new one.'];
            }

            if ($otp !== $cachedOtp) {
                $this->logger->warning('OTP validation failed: Invalid OTP', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'timestamp' => now()->toDateTimeString()
                ]);
                return ['success' => false, 'message' => 'Invalid verification code. Please try again.'];
            }

            // Clear OTP from cache
            Cache::forget('otp:' . $user->id);
            Cache::forget('otp_attempts_ip:' . request()->ip());
            Cache::forget('otp_attempts_user:' . $user->id);

            // Update user's OTP fields
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            $this->logger->info('OTP validation successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'timestamp' => now()->toDateTimeString()
            ]);

            return ['success' => true, 'message' => 'Verification successful. Redirecting...'];
        } catch (\Exception $e) {
            $this->logger->error('OTP validation failed with exception', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return ['success' => false, 'message' => 'An error occurred while validating the verification code.'];
        }
    }

    /**
     * Check if OTP is valid
     *
     * @param User $user
     * @param string $otp
     * @return bool
     */
    private function isOtpValid(User $user, string $otp): bool
    {
        $cachedOtpHash = Cache::get("otp_{$user->id}");
        $isValid = $cachedOtpHash && hash_equals($cachedOtpHash, hash('sha256', $otp));

        $this->logger->info("OTP validity check", [
            'user_id' => $user->id,
            'is_valid' => $isValid,
            'has_cached_otp' => (bool)$cachedOtpHash,
            'timestamp' => now()->toDateTimeString()
        ]);

        return $isValid;
    }

    /**
     * Check if user has exceeded maximum attempts
     *
     * @param User $user
     * @return bool
     */
    private function hasExceededMaxAttempts(User $user): bool
    {
        $attempts = Cache::get("otp_attempts_{$user->id}", 0);
        $hasExceeded = $attempts >= self::MAX_ATTEMPTS;

        $this->logger->info("User attempt limit check", [
            'user_id' => $user->id,
            'attempts' => $attempts,
            'max_attempts' => self::MAX_ATTEMPTS,
            'has_exceeded' => $hasExceeded,
            'timestamp' => now()->toDateTimeString()
        ]);

        return $hasExceeded;
    }

    /**
     * Check if IP has exceeded rate limit
     *
     * @return bool
     */
    private function hasExceededIpRateLimit(): bool
    {
        $ip = Request::ip();
        $attempts = Cache::get("otp_ip_attempts_{$ip}", 0);
        $hasExceeded = $attempts >= self::IP_MAX_ATTEMPTS;

        $this->logger->info("IP attempt limit check", [
            'ip' => $ip,
            'attempts' => $attempts,
            'max_attempts' => self::IP_MAX_ATTEMPTS,
            'has_exceeded' => $hasExceeded,
            'timestamp' => now()->toDateTimeString()
        ]);

        return $hasExceeded;
    }

    /**
     * Increment OTP attempts for a user
     *
     * @param User $user
     * @return void
     */
    private function incrementAttempts(User $user): void
    {
        $attempts = Cache::get("otp_attempts_{$user->id}", 0);
        $newAttempts = $attempts + 1;
        Cache::put(
            "otp_attempts_{$user->id}",
            $newAttempts,
            Carbon::now()->addMinutes(self::ATTEMPT_WINDOW_MINUTES)
        );

        $this->logger->info("User attempt counter incremented", [
            'user_id' => $user->id,
            'old_attempts' => $attempts,
            'new_attempts' => $newAttempts,
            'expires_at' => Carbon::now()->addMinutes(self::ATTEMPT_WINDOW_MINUTES)->toDateTimeString(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Increment IP-based attempts
     *
     * @return void
     */
    private function incrementIpAttempts(): void
    {
        $ip = Request::ip();
        $attempts = Cache::get("otp_ip_attempts_{$ip}", 0);
        $newAttempts = $attempts + 1;
        Cache::put(
            "otp_ip_attempts_{$ip}",
            $newAttempts,
            Carbon::now()->addMinutes(self::IP_ATTEMPT_WINDOW_MINUTES)
        );

        $this->logger->info("IP attempt counter incremented", [
            'ip' => $ip,
            'old_attempts' => $attempts,
            'new_attempts' => $newAttempts,
            'expires_at' => Carbon::now()->addMinutes(self::IP_ATTEMPT_WINDOW_MINUTES)->toDateTimeString(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Clear OTP attempts for a user
     *
     * @param User $user
     * @return void
     */
    private function clearAttempts(User $user): void
    {
        Cache::forget("otp_attempts_{$user->id}");
        $this->logger->info("User attempt counter cleared", [
            'user_id' => $user->id,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Clear IP-based attempts
     *
     * @return void
     */
    private function clearIpAttempts(): void
    {
        $ip = Request::ip();
        Cache::forget("otp_ip_attempts_{$ip}");
        $this->logger->info("IP attempt counter cleared", [
            'ip' => $ip,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    private function sendOTP($user, $otp)
    {
        try {
            $this->logger->info('Attempting to send OTP via email', [
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_length' => strlen($otp),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            Mail::to($user->email)->send(new OTP(url('/'), $user->name, $otp));

            $this->logger->info('OTP email sent successfully', [
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_length' => strlen($otp),
                'mail_provider' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host')
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send OTP email', [
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'mail_provider' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host')
            ]);

            // Fallback to SMS if email fails
            return $this->sendOTPViaSMS($user, $otp);
        }
    }

    private function sendOTPViaSMS($user, $otp)
    {
        if (!$user->phone_number) {
            $this->logger->error('Cannot send SMS: No phone number available', [
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return false;
        }

        try {
            $this->logger->info('Attempting to send OTP via SMS', [
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $user->id,
                'phone_number' => $user->phone_number,
                'otp_length' => strlen($otp),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $smsService = app(SmsService::class);
            $smsService->sendOTP($user->phone_number, $otp);

            $this->logger->info('OTP SMS sent successfully', [
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $user->id,
                'phone_number' => $user->phone_number,
                'otp_length' => strlen($otp)
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send OTP via SMS', [
                'timestamp' => now()->toDateTimeString(),
                'user_id' => $user->id,
                'phone_number' => $user->phone_number,
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
