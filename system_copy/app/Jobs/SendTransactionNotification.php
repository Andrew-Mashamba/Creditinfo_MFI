<?php

namespace App\Jobs;

use App\Models\AccountsModel;
use App\Models\ClientsModel;
use App\Services\SmsService;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTransactionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $accountNumber;
    protected $transactionType;
    protected $amount;
    protected $balance;
    protected $referenceNumber;
    protected $narration;
    protected $status;
    protected $counterpartyName;
    protected $errorMessage;

    /**
     * Create a new job instance.
     *
     * @param string $accountNumber
     * @param string $transactionType (debit/credit)
     * @param float $amount
     * @param float $balance
     * @param string $referenceNumber
     * @param string $narration
     * @param string $status (success/failed)
     * @param string|null $counterpartyName
     * @param string|null $errorMessage
     */
    public function __construct(
        $accountNumber,
        $transactionType,
        $amount,
        $balance,
        $referenceNumber,
        $narration,
        $status = 'success',
        $counterpartyName = null,
        $errorMessage = null
    ) {
        $this->accountNumber = $accountNumber;
        $this->transactionType = $transactionType;
        $this->amount = $amount;
        $this->balance = $balance;
        $this->referenceNumber = $referenceNumber;
        $this->narration = $narration;
        $this->status = $status;
        $this->counterpartyName = $counterpartyName;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get account details
            $account = AccountsModel::where('account_number', $this->accountNumber)->first();
            
            if (!$account) {
                Log::warning('Transaction notification: Account not found', ['account_number' => $this->accountNumber]);
                return;
            }

            // Check if this is a member account
            if (!$this->isMemberAccount($account)) {
                Log::info('Transaction notification: Not a member account, skipping notification', [
                    'account_number' => $this->accountNumber,
                    'client_number' => $account->client_number
                ]);
                return;
            }

            // Get member details
            $member = ClientsModel::where('client_number', $account->client_number)->first();
            
            if (!$member) {
                Log::warning('Transaction notification: Member not found', [
                    'client_number' => $account->client_number
                ]);
                return;
            }

            // Prepare notification message
            $message = $this->prepareMessage($account, $member);

            // Send SMS notification if phone number exists
            if ($member->phone_number && $this->isValidPhoneNumber($member->phone_number)) {
                $this->sendSmsNotification($member->phone_number, $message['sms']);
            }

            // Send Email notification if email exists
            if ($member->email && filter_var($member->email, FILTER_VALIDATE_EMAIL)) {
                $this->sendEmailNotification($member->email, $message['email'], $message['subject']);
            }

            Log::info('Transaction notification sent successfully', [
                'account_number' => $this->accountNumber,
                'client_number' => $account->client_number,
                'reference_number' => $this->referenceNumber
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send transaction notification', [
                'account_number' => $this->accountNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if the account belongs to a member
     *
     * @param $account
     * @return bool
     */
    private function isMemberAccount($account)
    {
        return !empty($account->client_number) && 
               $account->client_number !== null && 
               $account->client_number !== '0000' &&
               $account->client_number !== '0';
    }

    /**
     * Validate phone number format
     *
     * @param string $phoneNumber
     * @return bool
     */
    private function isValidPhoneNumber($phoneNumber)
    {
        // Remove spaces and special characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Check if it's a valid length (adjust based on your country)
        return strlen($phoneNumber) >= 10 && strlen($phoneNumber) <= 15;
    }

    /**
     * Prepare notification messages
     *
     * @param $account
     * @param $member
     * @return array
     */
    private function prepareMessage($account, $member)
    {
        $formattedAmount = number_format($this->amount, 2);
        $formattedBalance = number_format($this->balance, 2);
        $transactionTypeText = strtoupper($this->transactionType);
        $memberName = $member->first_name ?? 'Member';

        if ($this->status === 'success') {
            // Success message
            $smsMessage = "Dear {$memberName}, your A/C {$this->maskAccountNumber($this->accountNumber)} has been {$transactionTypeText}ED with {$formattedAmount}. ";
            $smsMessage .= "Balance: {$formattedBalance}. Ref: {$this->referenceNumber}";
            
            if ($this->counterpartyName) {
                $smsMessage .= " " . ($this->transactionType === 'credit' ? "From" : "To") . ": {$this->counterpartyName}";
            }

            $emailSubject = "Transaction Alert - {$transactionTypeText}";
            
            $emailMessage = "Dear {$memberName},<br><br>";
            $emailMessage .= "This is to notify you that a transaction has been processed on your account.<br><br>";
            $emailMessage .= "<strong>Transaction Details:</strong><br>";
            $emailMessage .= "Account Number: {$this->maskAccountNumber($this->accountNumber)}<br>";
            $emailMessage .= "Transaction Type: {$transactionTypeText}<br>";
            $emailMessage .= "Amount: {$formattedAmount}<br>";
            $emailMessage .= "New Balance: {$formattedBalance}<br>";
            $emailMessage .= "Reference Number: {$this->referenceNumber}<br>";
            $emailMessage .= "Description: {$this->narration}<br>";
            
            if ($this->counterpartyName) {
                $emailMessage .= ($this->transactionType === 'credit' ? "From" : "To") . ": {$this->counterpartyName}<br>";
            }
            
            $emailMessage .= "<br>Thank you for banking with us.<br><br>";
            $emailMessage .= "This is an automated message. Please do not reply.";

        } else {
            // Failed transaction message
            $smsMessage = "Dear {$memberName}, a {$transactionTypeText} of {$formattedAmount} on A/C {$this->maskAccountNumber($this->accountNumber)} failed. ";
            $smsMessage .= "Reason: {$this->errorMessage}. Ref: {$this->referenceNumber}";

            $emailSubject = "Transaction Failed - {$transactionTypeText}";
            
            $emailMessage = "Dear {$memberName},<br><br>";
            $emailMessage .= "We regret to inform you that a transaction on your account could not be processed.<br><br>";
            $emailMessage .= "<strong>Transaction Details:</strong><br>";
            $emailMessage .= "Account Number: {$this->maskAccountNumber($this->accountNumber)}<br>";
            $emailMessage .= "Transaction Type: {$transactionTypeText}<br>";
            $emailMessage .= "Amount: {$formattedAmount}<br>";
            $emailMessage .= "Reference Number: {$this->referenceNumber}<br>";
            $emailMessage .= "Reason: {$this->errorMessage}<br>";
            $emailMessage .= "<br>Please contact our support team if you need assistance.<br><br>";
            $emailMessage .= "This is an automated message. Please do not reply.";
        }

        return [
            'sms' => $smsMessage,
            'email' => $emailMessage,
            'subject' => $emailSubject
        ];
    }

    /**
     * Mask account number for security
     *
     * @param string $accountNumber
     * @return string
     */
    private function maskAccountNumber($accountNumber)
    {
        $length = strlen($accountNumber);
        if ($length <= 4) {
            return $accountNumber;
        }
        
        $visibleDigits = 4;
        $maskedPart = str_repeat('*', $length - $visibleDigits);
        $visiblePart = substr($accountNumber, -$visibleDigits);
        
        return $maskedPart . $visiblePart;
    }

    /**
     * Send SMS notification
     *
     * @param string $phoneNumber
     * @param string $message
     */
    private function sendSmsNotification($phoneNumber, $message)
    {
        try {
            $smsService = new SmsService();
            $result = $smsService->send($phoneNumber, $message);
            
            Log::info('SMS notification sent', [
                'phone_number' => $this->maskPhoneNumber($phoneNumber),
                'reference_number' => $this->referenceNumber,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'phone_number' => $this->maskPhoneNumber($phoneNumber),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send Email notification
     *
     * @param string $email
     * @param string $message
     * @param string $subject
     */
    private function sendEmailNotification($email, $message, $subject)
    {
        try {
            $emailService = new EmailService();
            
            // Prepare email data in the format EmailService expects
            $emailData = [
                'to' => $email,
                'subject' => $subject,
                'body' => $message,
                'from_name' => config('app.name', 'SACCOS System'),
                'immediate' => true, // Send immediately without undo
            ];
            
            $result = $emailService->sendEmail($emailData, false); // false = disable undo
            
            Log::info('Email notification sent', [
                'email' => $this->maskEmail($email),
                'reference_number' => $this->referenceNumber,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'email' => $this->maskEmail($email),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mask phone number for logs
     *
     * @param string $phoneNumber
     * @return string
     */
    private function maskPhoneNumber($phoneNumber)
    {
        $length = strlen($phoneNumber);
        if ($length <= 4) {
            return $phoneNumber;
        }
        
        $visibleDigits = 4;
        $maskedPart = str_repeat('*', $length - $visibleDigits);
        $visiblePart = substr($phoneNumber, -$visibleDigits);
        
        return $maskedPart . $visiblePart;
    }

    /**
     * Mask email for logs
     *
     * @param string $email
     * @return string
     */
    private function maskEmail($email)
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        $maskedUsername = substr($username, 0, 2) . str_repeat('*', max(0, strlen($username) - 2));
        
        return $maskedUsername . '@' . $domain;
    }
}