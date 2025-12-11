<?php

namespace App\Jobs;

use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendControlTransactionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $debitAccount;
    protected $creditAccount;
    protected $amount;
    protected $debitNewBalance;
    protected $creditNewBalance;
    protected $referenceNumber;
    protected $narration;
    protected $status;
    protected $errorMessage;
    protected $transactionType; // 'internal' or 'mixed'

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param object $debitAccount
     * @param object $creditAccount
     * @param float $amount
     * @param float $debitNewBalance
     * @param float $creditNewBalance
     * @param string $referenceNumber
     * @param string $narration
     * @param string $status
     * @param string|null $errorMessage
     * @param string $transactionType
     */
    public function __construct(
        $email,
        $debitAccount,
        $creditAccount,
        $amount,
        $debitNewBalance,
        $creditNewBalance,
        $referenceNumber,
        $narration,
        $status = 'success',
        $errorMessage = null,
        $transactionType = 'internal'
    ) {
        $this->email = $email;
        $this->debitAccount = $debitAccount;
        $this->creditAccount = $creditAccount;
        $this->amount = $amount;
        $this->debitNewBalance = $debitNewBalance;
        $this->creditNewBalance = $creditNewBalance;
        $this->referenceNumber = $referenceNumber;
        $this->narration = $narration;
        $this->status = $status;
        $this->errorMessage = $errorMessage;
        $this->transactionType = $transactionType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Prepare notification message
            $message = $this->prepareMessage();

            // Send email notification
            $this->sendEmailNotification($this->email, $message['email'], $message['subject']);

            Log::info('Control transaction notification sent successfully', [
                'email' => $this->maskEmail($this->email),
                'reference_number' => $this->referenceNumber,
                'transaction_type' => 'internal_accounts'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send control transaction notification', [
                'email' => $this->maskEmail($this->email),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Prepare notification messages
     *
     * @return array
     */
    private function prepareMessage()
    {
        $formattedAmount = number_format($this->amount, 2);
        $formattedDebitBalance = number_format($this->debitNewBalance, 2);
        $formattedCreditBalance = number_format($this->creditNewBalance, 2);
        
        $institutionName = config('app.name', 'SACCOS System');
        $timestamp = now()->format('Y-m-d H:i:s');

        if ($this->status === 'success') {
            // Determine transaction description
            $transactionDescription = $this->transactionType === 'mixed' 
                ? "Mixed Transaction (Member + Internal Account)" 
                : "Internal Account Transaction";
            
            // Success message
            $emailSubject = "[{$institutionName}] {$transactionDescription} Alert - Ref: {$this->referenceNumber}";
            
            $emailMessage = "<html><body style='font-family: Arial, sans-serif;'>";
            $emailMessage .= "<div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px;'>";
            $emailMessage .= "<h2 style='color: #007bff;'>{$transactionDescription} Notification</h2>";
            
            if ($this->transactionType === 'mixed') {
                $emailMessage .= "<p>This is an automated notification for a transaction involving both a member account and an internal/system account.</p>";
            } else {
                $emailMessage .= "<p>This is an automated notification for a transaction between internal/system accounts.</p>";
            }
            
            $emailMessage .= "<div style='background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            $emailMessage .= "<h3 style='color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;'>Transaction Details</h3>";
            
            $emailMessage .= "<table style='width: 100%; border-collapse: collapse;'>";
            $emailMessage .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Reference Number:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$this->referenceNumber}</td></tr>";
            $emailMessage .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Transaction Date:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$timestamp}</td></tr>";
            $emailMessage .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Amount:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'><span style='font-size: 1.2em; color: #28a745;'>{$formattedAmount}</span></td></tr>";
            $emailMessage .= "<tr><td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Description:</strong></td><td style='padding: 8px; border-bottom: 1px solid #eee;'>{$this->narration}</td></tr>";
            $emailMessage .= "</table>";
            $emailMessage .= "</div>";
            
            // Debit Account Details
            $debitIsMember = !empty($this->debitAccount->client_number) && 
                            $this->debitAccount->client_number !== null && 
                            $this->debitAccount->client_number !== '0000' &&
                            $this->debitAccount->client_number !== '0';
            
            $emailMessage .= "<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            $emailMessage .= "<h4 style='color: #856404; margin-top: 0;'>DEBIT Account (Amount Increased)" . 
                            ($debitIsMember ? " - MEMBER ACCOUNT" : " - INTERNAL ACCOUNT") . "</h4>";
            $emailMessage .= "<table style='width: 100%;'>";
            $emailMessage .= "<tr><td><strong>Account Name:</strong></td><td>{$this->debitAccount->account_name}</td></tr>";
            $emailMessage .= "<tr><td><strong>Account Number:</strong></td><td>{$this->debitAccount->account_number}</td></tr>";
            $emailMessage .= "<tr><td><strong>Account Type:</strong></td><td>" . ucfirst(str_replace('_', ' ', $this->debitAccount->type)) . "</td></tr>";
            if ($debitIsMember && !empty($this->debitAccount->client_number)) {
                $emailMessage .= "<tr><td><strong>Member Number:</strong></td><td>{$this->debitAccount->client_number}</td></tr>";
            }
            $emailMessage .= "<tr><td><strong>New Balance:</strong></td><td><strong>{$formattedDebitBalance}</strong></td></tr>";
            $emailMessage .= "</table>";
            $emailMessage .= "</div>";
            
            // Credit Account Details
            $creditIsMember = !empty($this->creditAccount->client_number) && 
                             $this->creditAccount->client_number !== null && 
                             $this->creditAccount->client_number !== '0000' &&
                             $this->creditAccount->client_number !== '0';
            
            $emailMessage .= "<div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            $emailMessage .= "<h4 style='color: #0c5460; margin-top: 0;'>CREDIT Account (Amount Decreased)" . 
                            ($creditIsMember ? " - MEMBER ACCOUNT" : " - INTERNAL ACCOUNT") . "</h4>";
            $emailMessage .= "<table style='width: 100%;'>";
            $emailMessage .= "<tr><td><strong>Account Name:</strong></td><td>{$this->creditAccount->account_name}</td></tr>";
            $emailMessage .= "<tr><td><strong>Account Number:</strong></td><td>{$this->creditAccount->account_number}</td></tr>";
            $emailMessage .= "<tr><td><strong>Account Type:</strong></td><td>" . ucfirst(str_replace('_', ' ', $this->creditAccount->type)) . "</td></tr>";
            if ($creditIsMember && !empty($this->creditAccount->client_number)) {
                $emailMessage .= "<tr><td><strong>Member Number:</strong></td><td>{$this->creditAccount->client_number}</td></tr>";
            }
            $emailMessage .= "<tr><td><strong>New Balance:</strong></td><td><strong>{$formattedCreditBalance}</strong></td></tr>";
            $emailMessage .= "</table>";
            $emailMessage .= "</div>";
            
            // Warning if negative balance
            if ($this->debitNewBalance < 0 || $this->creditNewBalance < 0) {
                $emailMessage .= "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                $emailMessage .= "<h4 style='color: #721c24; margin-top: 0;'>⚠️ WARNING: Negative Balance Detected</h4>";
                if ($this->debitNewBalance < 0) {
                    $emailMessage .= "<p>Debit account ({$this->debitAccount->account_name}) has negative balance: {$formattedDebitBalance}</p>";
                }
                if ($this->creditNewBalance < 0) {
                    $emailMessage .= "<p>Credit account ({$this->creditAccount->account_name}) has negative balance: {$formattedCreditBalance}</p>";
                }
                $emailMessage .= "</div>";
            }
            
            $emailMessage .= "<div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 0.9em;'>";
            $emailMessage .= "<p>This is an automated notification for internal account transactions. These transactions do not involve member accounts.</p>";
            $emailMessage .= "<p>System: {$institutionName}<br>Generated: {$timestamp}</p>";
            $emailMessage .= "</div>";
            
            $emailMessage .= "</div>";
            $emailMessage .= "</body></html>";

        } else {
            // Failed transaction message
            $emailSubject = "[{$institutionName}] FAILED Internal Transaction - Ref: {$this->referenceNumber}";
            
            $emailMessage = "<html><body style='font-family: Arial, sans-serif;'>";
            $emailMessage .= "<div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px;'>";
            $emailMessage .= "<h2 style='color: #dc3545;'>⚠️ Failed Internal Transaction Alert</h2>";
            $emailMessage .= "<p>An internal account transaction attempt has failed.</p>";
            
            $emailMessage .= "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            $emailMessage .= "<h3 style='color: #721c24;'>Transaction Failure Details</h3>";
            $emailMessage .= "<table style='width: 100%;'>";
            $emailMessage .= "<tr><td style='padding: 5px;'><strong>Reference Number:</strong></td><td>{$this->referenceNumber}</td></tr>";
            $emailMessage .= "<tr><td style='padding: 5px;'><strong>Timestamp:</strong></td><td>{$timestamp}</td></tr>";
            $emailMessage .= "<tr><td style='padding: 5px;'><strong>Amount:</strong></td><td>{$formattedAmount}</td></tr>";
            $emailMessage .= "<tr><td style='padding: 5px;'><strong>Error:</strong></td><td style='color: #dc3545;'><strong>{$this->errorMessage}</strong></td></tr>";
            $emailMessage .= "</table>";
            $emailMessage .= "</div>";
            
            $emailMessage .= "<div style='background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            $emailMessage .= "<h4>Attempted Transaction:</h4>";
            $emailMessage .= "<p><strong>From (Debit):</strong> {$this->debitAccount->account_name} ({$this->debitAccount->account_number})</p>";
            $emailMessage .= "<p><strong>To (Credit):</strong> {$this->creditAccount->account_name} ({$this->creditAccount->account_number})</p>";
            $emailMessage .= "<p><strong>Description:</strong> {$this->narration}</p>";
            $emailMessage .= "</div>";
            
            $emailMessage .= "<div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 0.9em;'>";
            $emailMessage .= "<p>Please review this failed transaction and take appropriate action if necessary.</p>";
            $emailMessage .= "<p>System: {$institutionName}<br>Generated: {$timestamp}</p>";
            $emailMessage .= "</div>";
            
            $emailMessage .= "</div>";
            $emailMessage .= "</body></html>";
        }

        return [
            'email' => $emailMessage,
            'subject' => $emailSubject
        ];
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
                'from_email' => config('mail.from.address', 'noreply@saccos.co.tz'),
                'from_name' => config('app.name', 'SACCOS System'),
                'immediate' => true, // Send immediately without undo
            ];
            
            $result = $emailService->sendEmail($emailData, false); // false = disable undo
            
            Log::info('Control email notification sent', [
                'email' => $this->maskEmail($email),
                'reference_number' => $this->referenceNumber,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send control email notification', [
                'email' => $this->maskEmail($email),
                'error' => $e->getMessage()
            ]);
            
            // Re-throw to trigger retry
            throw $e;
        }
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
        
        if (strlen($username) <= 3) {
            return $username . '@' . $domain;
        }
        
        return substr($username, 0, 3) . '***@' . $domain;
    }
}