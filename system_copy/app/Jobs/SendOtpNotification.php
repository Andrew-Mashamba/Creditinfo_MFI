<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\OTP;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOtpNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $otp;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('otp')->info('=== SEND OTP NOTIFICATION JOB STARTED ===', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'phone_number' => $this->user->phone_number,
            'otp_masked' => substr($this->otp, 0, 2) . '****',
            'timestamp' => now()->toDateTimeString()
        ]);
        
        try {
            $emailSent = false;
            $smsSent = false;
            
            // Send OTP via SMS first (primary method)
            if ($this->user->phone_number) {
                try {
                    Log::channel('otp')->info('Attempting to send SMS', [
                        'user_id' => $this->user->id,
                        'phone_number' => $this->user->phone_number,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    
                    $message = "NBC SACCOS OTP: {$this->otp}\n\nValid for 5 minutes. Do not share this code with anyone.";
                    app(SmsService::class)->send($this->user->phone_number, $message, $this->user, [
                        'smsType' => 'TRANSACTIONAL',
                        'serviceName' => 'SACCOSS',
                        'language' => 'English'
                    ]);
                    $smsSent = true;
                    
                    Log::channel('otp')->info('✓ SMS sent successfully', [
                        'user_id' => $this->user->id,
                        'phone_number' => $this->user->phone_number,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } catch (\Exception $smsError) {
                    Log::channel('otp')->error('✗ SMS sending failed', [
                        'user_id' => $this->user->id,
                        'phone_number' => $this->user->phone_number,
                        'exception_class' => get_class($smsError),
                        'sms_error' => $smsError->getMessage(),
                        'error_code' => $smsError->getCode(),
                        'error_file' => $smsError->getFile(),
                        'error_line' => $smsError->getLine(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                }
            } else {
                Log::channel('otp')->warning('No phone number available for SMS', [
                    'user_id' => $this->user->id,
                    'email' => $this->user->email,
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
            
            // Send OTP via email as fallback (secondary method)
            if (!$smsSent && config('mail.mailers.smtp.host')) {
                try {
                    Log::channel('otp')->info('SMS failed, attempting email as fallback', [
                        'user_id' => $this->user->id,
                        'email' => $this->user->email,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    
                    // Use custom SMTP method for servers without authentication
                    $this->sendOtpViaSmtp($this->user->email, $this->otp);
                    $emailSent = true;
                    
                    Log::channel('otp')->info('✓ Email sent successfully (fallback)', [
                        'user_id' => $this->user->id,
                        'email' => $this->user->email,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } catch (\Exception $emailError) {
                    Log::channel('otp')->error('✗ Email sending also failed', [
                        'user_id' => $this->user->id,
                        'email' => $this->user->email,
                        'exception_class' => get_class($emailError),
                        'email_error' => $emailError->getMessage(),
                        'error_code' => $emailError->getCode(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                }
            } else if ($smsSent) {
                // SMS was sent successfully, email is optional
                try {
                    Log::channel('otp')->info('SMS successful, sending email as additional channel', [
                        'user_id' => $this->user->id,
                        'email' => $this->user->email,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    
                    $this->sendOtpViaSmtp($this->user->email, $this->otp);
                    $emailSent = true;
                    
                    Log::channel('otp')->info('✓ Email sent successfully (additional)', [
                        'user_id' => $this->user->id,
                        'email' => $this->user->email,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } catch (\Exception $e) {
                    // Email is optional when SMS succeeds, so just log
                    Log::channel('otp')->info('Email sending failed but SMS was successful', [
                        'user_id' => $this->user->id,
                        'exception_class' => get_class($e),
                        'error' => $e->getMessage(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                }
            }

            // If neither email nor SMS was sent successfully, log the OTP for manual retrieval
            if (!$emailSent && !$smsSent) {
                Log::channel('otp')->critical('OTP delivery failed - manual intervention required', [
                    'user_id' => $this->user->id,
                    'user_email' => $this->user->email,
                    'user_phone' => $this->user->phone_number,
                    'otp_code' => $this->otp,
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                // Don't throw exception to prevent infinite retries
                // Instead, mark as completed but log the failure
                return;
            }

            Log::channel('otp')->info('OTP sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'phone_number' => $this->user->phone_number,
                'email_sent' => $emailSent,
                'sms_sent' => $smsSent,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            Log::channel('otp')->error('Failed to send OTP notification', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);

            // If the job fails, throw the exception to trigger retry
            throw $e;
        }
    }

    /**
     * The number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }

    /**
     * The number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Retry after 30 seconds, then 1 minute, then 2 minutes
    }

    /**
     * Send OTP via SMTP without authentication
     */
    private function sendOtpViaSmtp($to, $otp)
    {
        $smtp_server = config('mail.mailers.smtp.host', 'smtp.absa.co.za');
        $smtp_port = config('mail.mailers.smtp.port', 25);
        $from = config('mail.from.address', 'nbc_saccos@nbc.co.tz');
        $from_name = config('mail.from.name', 'NBC SACCOS');
        
        // Open connection
        $connection = @fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
        
        if (!$connection) {
            throw new \Exception("Failed to connect to SMTP server: $errstr ($errno)");
        }
        
        // Read server response
        $response = fgets($connection, 515);
        
        // Send HELO command
        fputs($connection, "HELO nbc.co.tz\r\n");
        $response = fgets($connection, 515);
        
        // Send MAIL FROM
        fputs($connection, "MAIL FROM: <$from>\r\n");
        $response = fgets($connection, 515);
        
        // Send RCPT TO
        fputs($connection, "RCPT TO: <$to>\r\n");
        $response = fgets($connection, 515);
        
        // Send DATA command
        fputs($connection, "DATA\r\n");
        $response = fgets($connection, 515);
        
        // Build the email message
        $subject = "Your NBC SACCOS Login OTP";
        $message = "Dear " . ($this->user->name ?? 'User') . ",\r\n\r\n";
        $message .= "Your One-Time Password (OTP) for NBC SACCOS login is:\r\n\r\n";
        $message .= "    $otp\r\n\r\n";
        $message .= "This code is valid for 5 minutes.\r\n\r\n";
        $message .= "If you did not request this code, please ignore this email.\r\n\r\n";
        $message .= "Best regards,\r\n";
        $message .= "NBC SACCOS Team\r\n";
        $message .= config('app.url');
        
        // Send headers and message
        $headers = "From: $from_name <$from>\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "Date: " . date("r") . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "\r\n";
        
        fputs($connection, $headers . $message . "\r\n.\r\n");
        $response = fgets($connection, 515);
        
        // Check if message was accepted
        if (strpos($response, '250') === false) {
            fclose($connection);
            throw new \Exception("Failed to send email. Server response: $response");
        }
        
        // Quit
        fputs($connection, "QUIT\r\n");
        fclose($connection);
        
        Log::channel('otp')->info('OTP email sent via custom SMTP', [
            'user_id' => $this->user->id,
            'email' => $to,
            'server' => $smtp_server,
            'message_id' => trim(str_replace('250 ', '', $response))
        ]);
    }
} 