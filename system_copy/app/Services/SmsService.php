<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Cache;
use Exception;

class SmsService
{
    protected $apiKey;
    protected $baseUrl;
    protected $channelId;
    protected $rateLimit;
    protected $rateLimitWindow;
    protected $processId;
    protected $maxRetries;
    protected $retryDelay;

    public function __construct()
    {
        $this->apiKey = config('services.nbc_sms.api_key');
        $this->baseUrl = config('services.nbc_sms.base_url');
        $this->channelId = config('services.nbc_sms.channel_id', '101_SYSTEM');
        $this->rateLimit = config('services.nbc_sms.rate_limit', 100);
        $this->rateLimitWindow = config('services.nbc_sms.rate_limit_window', 3600);
        $this->maxRetries = config('services.nbc_sms.max_retries', 3);
        $this->retryDelay = config('services.nbc_sms.retry_delay', 60);
        $this->processId = \Illuminate\Support\Str::uuid()->toString();
    }

    /**
     * Send OTP via SMS
     */
    public function sendOTP($phoneNumber, $otp)
    {
        $message = "NBC SACCOS OTP: {$otp}\n\nValid for 5 minutes. Do not share this code with anyone.";
        return $this->send($phoneNumber, $message, null, [
            'smsType' => 'TRANSACTIONAL',
            'serviceName' => 'SACCOSS',
            'language' => 'English'
        ]);
    }

    /**
     * Send SMS using NBC SMS Notification Engine API v2.0.0
     */
    public function send($phoneNumber, $message, $recipient = null, $options = [])
    {
        try {
            // Validate phone number
            if (!$this->isValidPhoneNumber($phoneNumber)) {
                throw new Exception("Invalid phone number format: {$phoneNumber}");
            }

            // Check rate limit
            if (!$this->checkRateLimit($phoneNumber)) {
                throw new Exception("Rate limit exceeded for phone number: {$phoneNumber}");
            }

            // Format phone number for NBC API (should start with 255)
            $formattedNumber = $this->formatPhoneNumber($phoneNumber);

            // Create initial log entry
            $log = NotificationLog::logNotification([
                'process_id' => $this->processId,
                'recipient_type' => $recipient ? get_class($recipient) : null,
                'recipient_id' => $recipient ? $recipient->id : null,
                'notification_type' => 'sms',
                'channel' => 'sms',
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            // Prepare NBC API payload
            $payload = $this->prepareNbcApiPayload($formattedNumber, $message, $recipient, $options);

            // Send SMS with retry logic
            $response = $this->sendWithRetry($payload, $log);

            return $response;

        } catch (Exception $e) {
            Log::error('SMS sending failed', [
                'process_id' => $this->processId,
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Send bulk SMS to multiple recipients
     */
    public function sendBulk($recipients, $message, $options = [])
    {
        try {
            if (count($recipients) > 250) {
                throw new Exception("Bulk SMS limit exceeded. Maximum 250 recipients allowed.");
            }

            $phoneNumbers = [];
            $recipientNames = [];

            foreach ($recipients as $recipient) {
                $phoneNumber = $recipient['phone'] ?? $recipient->mobile_phone ?? $recipient->contact_number;
                if ($this->isValidPhoneNumber($phoneNumber)) {
                    $phoneNumbers[] = $this->formatPhoneNumber($phoneNumber);
                    $recipientNames[] = $recipient['name'] ?? $recipient->full_name ?? $recipient->present_surname ?? 'Member';
                }
            }

            if (empty($phoneNumbers)) {
                throw new Exception("No valid phone numbers found in recipients list");
            }

            // Create initial log entry
            $log = NotificationLog::logNotification([
                'process_id' => $this->processId,
                'recipient_type' => 'bulk',
                'recipient_id' => null,
                'notification_type' => 'sms',
                'channel' => 'sms',
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            // Prepare bulk SMS payload
            $payload = [
                'notificationRefNo' => $this->generateNotificationRefNo(),
                'recipientPhone' => '',
                'sms' => $message,
                'recipientName' => implode(', ', $recipientNames),
                'language' => $options['language'] ?? 'English',
                'smsType' => $options['smsType'] ?? 'BULK',
                'serviceName' => $options['serviceName'] ?? 'SACCOSS',
                'channelId' => $this->channelId,
                'recipientsPhones' => $phoneNumbers
            ];

            // Send bulk SMS
            $response = $this->sendWithRetry($payload, $log, true);

            return $response;

        } catch (Exception $e) {
            Log::error('Bulk SMS sending failed', [
                'process_id' => $this->processId,
                'recipients_count' => count($recipients),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Prepare payload for NBC SMS API
     */
    protected function prepareNbcApiPayload($phoneNumber, $message, $recipient = null, $options = [])
    {
        $recipientName = $this->getRecipientName($recipient);

        return [
            'notificationRefNo' => $this->generateNotificationRefNo(),
            'recipientPhone' => $phoneNumber,
            'sms' => $message,
            'recipientName' => $recipientName,
            'language' => $options['language'] ?? 'English',
            'smsType' => $options['smsType'] ?? 'TRANSACTIONAL',
            'serviceName' => $options['serviceName'] ?? 'SACCOSS',
            'channelId' => $this->channelId
        ];
    }

    /**
     * Send SMS with retry logic
     */
    protected function sendWithRetry($payload, $log, $isBulk = false)
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < $this->maxRetries) {
            try {
                $attempts++;
                
                Log::info('Sending SMS attempt', [
                    'process_id' => $this->processId,
                    'attempt' => $attempts,
                    'is_bulk' => $isBulk
                ]);

                // Make API request to NBC SMS Engine
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-API-Key' => $this->apiKey
                ])->withoutVerifying()  // Disable SSL verification for internal API
                  ->timeout(30)
                  ->post($this->baseUrl . '/nbc-sms-engine/api/v1/direct-sms', $payload);

                // Handle response
                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    // Update log with success
                    $log->markAsSent();
                    $log->update([
                        'response_data' => $responseData,
                        'delivered_at' => now()
                    ]);
                    $log->markAsDelivered();
                    
                    Log::info('SMS sent successfully via NBC API', [
                        'process_id' => $this->processId,
                        'notification_ref' => $responseData['body']['notificationRefNo'] ?? null,
                        'sms_engine_uuid' => $responseData['smsEngineUuid'] ?? null,
                        'is_bulk' => $isBulk
                    ]);

                    return [
                        'success' => true,
                        'notification_ref' => $responseData['body']['notificationRefNo'] ?? null,
                        'sms_engine_uuid' => $responseData['smsEngineUuid'] ?? null,
                        'response' => $responseData
                    ];
                }

                // Handle API error
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? 'Unknown API error';
                $statusCode = $response->status();

                Log::warning('SMS API error', [
                    'process_id' => $this->processId,
                    'attempt' => $attempts,
                    'status_code' => $statusCode,
                    'error' => $errorMessage,
                    'response' => $errorData
                ]);

                // Don't retry on 4XX errors (client errors)
                if ($statusCode >= 400 && $statusCode < 500) {
                    $log->markAsFailed($errorMessage, [
                        'status_code' => $statusCode,
                        'response' => $errorData,
                        'attempts' => $attempts
                    ]);
                    throw new Exception("SMS API client error: {$errorMessage}");
                }

                $lastException = new Exception("SMS API error: {$errorMessage}");

            } catch (Exception $e) {
                $lastException = $e;
                
                
                // Don't retry if it's a client error (4XX)
                if (strpos($e->getMessage(), 'SMS API client error') === 0) {
                    Log::info('Not retrying client error', [
                        'process_id' => $this->processId,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
                if ($attempts < $this->maxRetries) {
                    Log::info('Retrying SMS send', [
                        'process_id' => $this->processId,
                        'attempt' => $attempts + 1,
                        'delay' => $this->retryDelay
                    ]);
                    
                    sleep($this->retryDelay);
                }
            }
        }

        // All retries failed
        $log->markAsFailed($lastException->getMessage(), [
            'attempts' => $attempts,
            'last_error' => $lastException->getMessage()
        ]);

        throw $lastException;
    }

    /**
     * Generate unique notification reference number
     */
    protected function generateNotificationRefNo()
    {
        return 'NBC_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Get recipient name from recipient object
     */
    protected function getRecipientName($recipient)
    {
        if (!$recipient) {
            return 'Valued Member';
        }

        return $recipient->full_name ?? 
               $recipient->present_surname ?? 
               $recipient->first_name ?? 
               'Valued Member';
    }



    protected function isValidPhoneNumber($phoneNumber)
    {
        // Remove any non-digit characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Check if it's a valid Tanzanian number (local or international format)
        // Local: 0XXXXXXXXX (10 digits starting with 0)
        // International: 255XXXXXXXXX or +255XXXXXXXXX
        if (preg_match('/^0[1-9]\d{8}$/', $cleaned)) {
            return true; // Local format (e.g., 0628286302)
        }
        
        if (preg_match('/^\+?255[1-9]\d{8}$/', $cleaned)) {
            return true; // International format (e.g., 255628286302 or +255628286302)
        }
        
        // Also accept other international formats
        if (preg_match('/^\+?[1-9]\d{9,14}$/', $cleaned)) {
            return true; // Other international numbers
        }
        
        return false;
    }

    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-digit characters
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Convert local Tanzanian numbers to international format
        if (str_starts_with($number, '0')) {
            // Local format (0XXXXXXXXX) - convert to international (255XXXXXXXXX)
            $number = '255' . ltrim($number, '0');
        } elseif (!str_starts_with($number, '255')) {
            // If it doesn't start with 255, assume it needs the country code
            $number = '255' . ltrim($number, '0');
        }
        // If already starts with 255, keep as is
        
        return $number; // Return without + for NBC API
    }

    protected function checkRateLimit($phoneNumber)
    {
        $key = "sms_rate_limit:{$phoneNumber}";
        $count = Cache::get($key, 0);

        if ($count >= $this->rateLimit) {
            return false;
        }

        Cache::put($key, $count + 1, $this->rateLimitWindow);
        return true;
    }

    /**
     * Alias for send() method for compatibility
     * Used by ProcessTradePayableInvoice job
     */
    public function sendSms($phoneNumber, $message)
    {
        return $this->send($phoneNumber, $message);
    }
}
