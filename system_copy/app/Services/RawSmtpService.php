<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class RawSmtpService
{
    protected $smtpServer;
    protected $smtpPort;
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->smtpServer = config('mail.mailers.smtp.host', 'smtp.absa.co.za');
        $this->smtpPort = config('mail.mailers.smtp.port', 25);
        $this->fromEmail = config('mail.from.address', 'nbc_saccos@nbc.co.tz');
        $this->fromName = config('mail.from.name', 'NBC SACCOS');
    }

    public function send($to, $subject, $body, $isHtml = false)
    {
        try {
            // Open connection
            $connection = @fsockopen($this->smtpServer, $this->smtpPort, $errno, $errstr, 30);

            if (!$connection) {
                throw new Exception("Failed to connect to SMTP server: $errstr ($errno)");
            }

            // Read server response
            $response = fgets($connection, 515);

            // Send HELO command (not EHLO to avoid STARTTLS)
            fputs($connection, "HELO nbc.co.tz\r\n");
            $response = fgets($connection, 515);

            // Send MAIL FROM
            fputs($connection, "MAIL FROM: <{$this->fromEmail}>\r\n");
            $response = fgets($connection, 515);

            // Send RCPT TO
            fputs($connection, "RCPT TO: <$to>\r\n");
            $response = fgets($connection, 515);

            // Send DATA command
            fputs($connection, "DATA\r\n");
            $response = fgets($connection, 515);

            // Build headers
            $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "Date: " . date("r") . "\r\n";
            $headers .= "Content-Type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
            $headers .= "\r\n";

            // Send the message
            fputs($connection, $headers . $body . "\r\n.\r\n");
            $response = fgets($connection, 515);

            // Check if message was accepted
            if (strpos($response, '250') === false) {
                throw new Exception("Failed to send email. Server response: $response");
            }

            $messageId = trim(str_replace('250 ', '', $response));

            // Quit
            fputs($connection, "QUIT\r\n");
            fclose($connection);

            Log::info('Raw SMTP email sent successfully', [
                'to' => $to,
                'subject' => $subject,
                'message_id' => $messageId
            ]);

            return [
                'success' => true,
                'message_id' => $messageId
            ];

        } catch (Exception $e) {
            Log::error('Raw SMTP email failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
