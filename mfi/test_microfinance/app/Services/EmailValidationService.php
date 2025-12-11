<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmailValidationService
{
    /**
     * Validate email address with better error handling
     */
    public function validateEmail($email, $fieldName = 'email')
    {
        $email = trim($email);
        
        // Check if empty
        if (empty($email)) {
            throw new ValidationException(
                validator([], []),
                "The {$fieldName} field is required."
            );
        }
        
        // Check length
        if (strlen($email) > 255) {
            throw new ValidationException(
                validator([], []),
                "The {$fieldName} cannot exceed 255 characters."
            );
        }
        
        // Basic email format check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // More flexible regex for common email patterns
            $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
            if (!preg_match($emailPattern, $email)) {
                throw new ValidationException(
                    validator([], []),
                    "Please enter a valid {$fieldName} address (e.g., user@domain.com)."
                );
            }
        }
        
        return true;
    }
    
    /**
     * Validate email subject
     */
    public function validateSubject($subject)
    {
        $subject = trim($subject);
        
        if (empty($subject)) {
            throw new ValidationException(
                validator([], []),
                "Email subject is required."
            );
        }
        
        if (strlen($subject) > 500) {
            throw new ValidationException(
                validator([], []),
                "Email subject cannot exceed 500 characters."
            );
        }
        
        return true;
    }
    
    /**
     * Validate email body
     */
    public function validateBody($body)
    {
        $body = trim($body);
        
        if (empty($body)) {
            throw new ValidationException(
                validator([], []),
                "Email body is required."
            );
        }
        
        if (strlen($body) > 100000) { // 100KB limit
            throw new ValidationException(
                validator([], []),
                "Email body cannot exceed 100,000 characters."
            );
        }
        
        return true;
    }
    
    /**
     * Validate CC/BCC fields
     */
    public function validateEmailList($emailList, $fieldName = 'email list')
    {
        if (empty($emailList)) {
            return true; // Empty is valid
        }
        
        $emails = array_map('trim', explode(',', $emailList));
        
        foreach ($emails as $email) {
            if (!empty($email)) {
                try {
                    $this->validateEmail($email, $fieldName);
                } catch (ValidationException $e) {
                    throw new ValidationException(
                        validator([], []),
                        "Invalid email in {$fieldName}: {$email}"
                    );
                }
            }
        }
        
        return true;
    }
    
    /**
     * Comprehensive email validation for compose
     */
    public function validateComposeData($data)
    {
        $errors = [];
        
        try {
            $this->validateEmail($data['to'] ?? '', 'recipient email');
        } catch (ValidationException $e) {
            $errors['to'] = $e->getMessage();
        }
        
        try {
            $this->validateSubject($data['subject'] ?? '');
        } catch (ValidationException $e) {
            $errors['subject'] = $e->getMessage();
        }
        
        try {
            $this->validateBody($data['body'] ?? '');
        } catch (ValidationException $e) {
            $errors['body'] = $e->getMessage();
        }
        
        // Optional CC/BCC validation
        if (!empty($data['cc'])) {
            try {
                $this->validateEmailList($data['cc'], 'CC list');
            } catch (ValidationException $e) {
                $errors['cc'] = $e->getMessage();
            }
        }
        
        if (!empty($data['bcc'])) {
            try {
                $this->validateEmailList($data['bcc'], 'BCC list');
            } catch (ValidationException $e) {
                $errors['bcc'] = $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            Log::warning('[EMAIL_VALIDATION] Validation failed', [
                'errors' => $errors,
                'data' => array_keys($data)
            ]);
            
            throw new ValidationException(
                validator([], []),
                'Please correct the following errors: ' . implode(', ', $errors)
            );
        }
        
        return true;
    }
    
    /**
     * Sanitize email data
     */
    public function sanitizeEmailData($data)
    {
        return [
            'to' => trim($data['to'] ?? ''),
            'cc' => trim($data['cc'] ?? '') ?: null,
            'bcc' => trim($data['bcc'] ?? '') ?: null,
            'subject' => trim($data['subject'] ?? ''),
            'body' => trim($data['body'] ?? ''),
            'attachments' => $data['attachments'] ?? [],
            'reply_to_id' => $data['reply_to_id'] ?? null,
            'is_reply' => $data['is_reply'] ?? false,
            'is_forward' => $data['is_forward'] ?? false,
        ];
    }
} 