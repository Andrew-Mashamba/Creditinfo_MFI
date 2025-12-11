<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsTemplateService
{
    /**
     * Generate loan disbursement SMS for member
     * Returns array of message parts to prevent breaking control numbers and links
     */
    public function generateLoanDisbursementMemberSMS($memberName, $loanAmount, $monthlyInstallment, $controlNumber = null, $paymentLink = null, $firstPaymentDate = null, $saccoName = 'NBC SACCOS', $saccoPhone = '0800110022')
    {
        $amount = number_format($loanAmount, 0);
        $installment = number_format($monthlyInstallment, 0);

        // Build message parts - each part is a logical unit that should not be split
        $parts = [];

        // Part 1: Loan disbursement confirmation (most important)
        $part1 = "Dear {$memberName}, your loan of TZS {$amount} has been disbursed successfully. ";
        $part1 .= "Monthly installment: TZS {$installment}.";
        if ($firstPaymentDate) {
            $part1 .= " First payment: {$firstPaymentDate}.";
        }
        $parts[] = $part1;

        // Part 2: Control number (MUST NOT BE BROKEN - keep complete)
        if ($controlNumber) {
            $part2 = "Pay using Control No: {$controlNumber}";
            $parts[] = $part2;
        }

        // Part 3: Payment link (MUST NOT BE BROKEN - keep complete)
        if ($paymentLink) {
            $part3 = "Pay online: {$paymentLink}";
            $parts[] = $part3;
        }

        // Part 4: Payment methods and contact (using dynamic values)
        $part4 = "Via NBC Kiganjani, Wakala or branches. Contact: {$saccoPhone}. {$saccoName}";
        $parts[] = $part4;

        // Optimize parts - combine if they fit within SMS limit (150 chars for safety)
        return $this->optimizeLoanSmsMessages($parts);
    }

    /**
     * Optimize loan SMS messages by combining them if they fit within limits
     * Similar to optimizeSmsMessages but specifically for loan disbursements
     * Returns array of SMS messages ready to send
     */
    protected function optimizeLoanSmsMessages($parts)
    {
        $smsLimit = 150; // Conservative limit for single SMS (160 - safety margin)
        $optimized = [];
        $currentMessage = '';

        foreach ($parts as $index => $part) {
            // Try to combine with current message
            $separator = $currentMessage ? ' ' : '';
            $combined = $currentMessage . $separator . $part;

            if (strlen($combined) <= $smsLimit) {
                // Fits within limit, combine
                $currentMessage = $combined;
            } else {
                // Doesn't fit, save current message and start new
                if ($currentMessage) {
                    $optimized[] = $currentMessage;
                }
                // Start new message with current part
                // Check if single part exceeds limit (e.g., very long payment link)
                if (strlen($part) > $smsLimit) {
                    // Part is too long, send as-is (SMS service will handle)
                    $optimized[] = $part;
                    $currentMessage = '';
                } else {
                    $currentMessage = $part;
                }
            }
        }

        // Add the last message
        if ($currentMessage) {
            $optimized[] = $currentMessage;
        }

        return $optimized;
    }

    /**
     * Generate loan disbursement SMS for guarantor
     */
    public function generateLoanDisbursementGuarantorSMS($guarantorName, $memberName, $loanAmount)
    {
        $amount = number_format($loanAmount, 0);
        
        $message = "Dear {$guarantorName}, you are guarantor for {$memberName}'s loan of TZS {$amount}. ";
        $message .= "Loan has been disbursed. Please monitor payments. ";
        $message .= "Contact: +255 22 219 7000. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate member registration SMS
     */
    public function generateMemberRegistrationSMS($memberName, $controlNumber = null, $amount = null, $paymentLink = null)
    {
        $message = "Dear {$memberName}, welcome to NBC SACCOS! ";
        $message .= "Your account has been created successfully. ";
        
        if ($controlNumber && $amount) {
            $formattedAmount = number_format($amount, 0);
            $message .= "Control No: {$controlNumber}, Amount: TZS {$formattedAmount}. ";
        }
        
        if ($paymentLink) {
            $message .= "Pay online: {$paymentLink} ";
            $message .= "or ";
        }
        
        $message .= "Pay via NBC Kiganjani, Wakala or branches. ";
        $message .= "Contact: +255 22 219 7000. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate guarantor notification SMS
     */
    public function generateGuarantorNotificationSMS($guarantorName, $memberName)
    {
        $message = "Dear {$guarantorName}, you are guarantor for {$memberName} at NBC SACCOS. ";
        $message .= "Please ensure timely payments. ";
        $message .= "Contact: +255 22 219 7000. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate payment reminder SMS
     */
    public function generatePaymentReminderSMS($memberName, $controlNumber, $amount, $dueDate)
    {
        $formattedAmount = number_format($amount, 0);
        $formattedDate = date('j/m/Y', strtotime($dueDate));
        
        $message = "Dear {$memberName}, payment reminder. ";
        $message .= "Control No: {$controlNumber}, Amount: TZS {$formattedAmount}. ";
        $message .= "Due: {$formattedDate}. ";
        $message .= "Pay via NBC Kiganjani, Wakala or branches. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate payment confirmation SMS
     */
    public function generatePaymentConfirmationSMS($memberName, $controlNumber, $amount, $transactionId)
    {
        $formattedAmount = number_format($amount, 0);
        
        $message = "Dear {$memberName}, payment confirmed. ";
        $message .= "Control No: {$controlNumber}, Amount: TZS {$formattedAmount}. ";
        $message .= "Ref: {$transactionId}. ";
        $message .= "Thank you. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate loan approval SMS
     */
    public function generateLoanApprovalSMS($memberName, $loanAmount, $tenure)
    {
        $amount = number_format($loanAmount, 0);
        
        $message = "Dear {$memberName}, congratulations! ";
        $message .= "Your loan of TZS {$amount} for {$tenure} months has been approved. ";
        $message .= "We'll contact you for disbursement. ";
        $message .= "Contact: +255 22 219 7000. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate loan rejection SMS
     */
    public function generateLoanRejectionSMS($memberName, $reason = null)
    {
        $message = "Dear {$memberName}, your loan application has been reviewed. ";
        
        if ($reason) {
            $message .= "Reason: {$reason}. ";
        } else {
            $message .= "Unfortunately, it was not approved at this time. ";
        }
        
        $message .= "Contact us for more information. ";
        $message .= "Contact: +255 22 219 7000. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate account status update SMS
     */
    public function generateAccountStatusSMS($memberName, $status, $accountNumber)
    {
        $message = "Dear {$memberName}, your account status has been updated. ";
        $message .= "Account: {$accountNumber}, Status: {$status}. ";
        $message .= "Contact: +255 22 219 7000. NBC SACCOS";
        
        return $this->truncateMessage($message);
    }

    /**
     * Generate emergency notification SMS
     */
    public function generateEmergencyNotificationSMS($memberName, $message)
    {
        $emergencyMessage = "Dear {$memberName}, IMPORTANT: {$message}. ";
        $emergencyMessage .= "Contact: +255 22 219 7000. NBC SACCOS";
        
        return $this->truncateMessage($emergencyMessage);
    }

    /**
     * Generate custom SMS with template
     */
    public function generateCustomSMS($template, $variables = [])
    {
        $message = $template;
        
        foreach ($variables as $key => $value) {
            $message = str_replace("{{" . $key . "}}", $value, $message);
        }
        
        return $this->truncateMessage($message);
    }

    /**
     * Truncate message to fit SMS limits
     * Most SMS providers support 160 characters per message
     * For longer messages, they get split into multiple SMS
     */
    protected function truncateMessage($message, $maxLength = 160)
    {
        if (strlen($message) <= $maxLength) {
            return $message;
        }

        // Try to truncate at word boundaries
        $truncated = substr($message, 0, $maxLength - 3);
        $lastSpace = strrpos($truncated, ' ');
        
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . '...';
    }

    /**
     * Get SMS character count
     */
    public function getCharacterCount($message)
    {
        return strlen($message);
    }

    /**
     * Get SMS segment count (for messages longer than 160 characters)
     */
    public function getSegmentCount($message, $maxLength = 160)
    {
        return ceil(strlen($message) / $maxLength);
    }

    /**
     * Validate SMS message length
     */
    public function validateMessageLength($message, $maxLength = 160)
    {
        $length = strlen($message);
        $segments = $this->getSegmentCount($message, $maxLength);
        
        return [
            'valid' => $length <= $maxLength,
            'length' => $length,
            'segments' => $segments,
            'max_length' => $maxLength
        ];
    }
} 