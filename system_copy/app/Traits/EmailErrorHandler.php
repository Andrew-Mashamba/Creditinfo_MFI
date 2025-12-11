<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Exception;

trait EmailErrorHandler
{
    /**
     * Handle email errors with proper logging and user feedback
     */
    protected function handleEmailError(Exception $e, $context = [])
    {
        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();
        
        // Log the error with context
        Log::channel('email')->error('Email Error', [
            'message' => $errorMessage,
            'code' => $errorCode,
            'context' => $context,
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        // Determine user-friendly message
        $userMessage = $this->getUserFriendlyMessage($errorCode, $errorMessage);
        
        return [
            'success' => false,
            'message' => $userMessage,
            'debug_message' => config('app.debug') ? $errorMessage : null
        ];
    }
    
    /**
     * Get user-friendly error messages
     */
    protected function getUserFriendlyMessage($code, $message)
    {
        $messages = [
            'SMTP_AUTH_FAILED' => 'Unable to authenticate with email server. Please check your email settings.',
            'SMTP_CONNECTION_FAILED' => 'Unable to connect to email server. Please try again later.',
            'INVALID_RECIPIENT' => 'The recipient email address is invalid.',
            'RATE_LIMIT_EXCEEDED' => 'You have sent too many emails. Please wait before sending more.',
            'ATTACHMENT_TOO_LARGE' => 'The attachment is too large. Maximum size is 10MB.',
            'STORAGE_QUOTA_EXCEEDED' => 'Your email storage quota has been exceeded.',
            'SPAM_DETECTED' => 'This email has been flagged as potential spam.',
            'NETWORK_ERROR' => 'Network error occurred. Please check your connection.',
            'PERMISSION_DENIED' => 'You do not have permission to perform this action.',
            'EMAIL_NOT_FOUND' => 'The requested email could not be found.',
        ];
        
        // Check for known error patterns
        foreach ($messages as $pattern => $userMessage) {
            if (stripos($message, $pattern) !== false || $code === $pattern) {
                return $userMessage;
            }
        }
        
        // Default message
        return 'An error occurred while processing your email. Please try again.';
    }
    
    /**
     * Log email activity
     */
    protected function logEmailActivity($action, $details = [])
    {
        Log::channel('email')->info("Email Activity: {$action}", array_merge([
            'user_id' => auth()->id(),
            'timestamp' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ], $details));
    }
}