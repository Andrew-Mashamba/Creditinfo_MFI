<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestEmail extends Command
{
    protected $signature = 'email:test {to=andrew.s.mashamba@gmail.com}';
    protected $description = 'Test email configuration';

    public function handle()
    {
        $to = $this->argument('to');
        $this->info("Sending test email to: $to");
        
        $smtp_server = 'smtp.absa.co.za';
        $smtp_port = 25;
        $from = 'nbc_saccos@nbc.co.tz';
        $subject = 'NBC SACCOS - Test Email from System';
        $message = "This is a test email from NBC SACCOS system.\n\nSent at: " . now()->toString();
        
        try {
            // Open connection
            $connection = @fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
            
            if (!$connection) {
                $this->error("Failed to connect: $errstr ($errno)");
                return 1;
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
            
            // Send the message
            $headers = "From: NBC SACCOS <$from>\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "Date: " . date("r") . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "\r\n";
            
            fputs($connection, $headers . $message . "\r\n.\r\n");
            $response = fgets($connection, 515);
            
            // Check if message was accepted
            if (strpos($response, '250') !== false) {
                $this->info("✓ Email sent successfully to $to");
                $messageId = trim(str_replace('250 ', '', $response));
                $this->info("  Message ID: $messageId");
            } else {
                $this->error("Failed to send email. Server response: $response");
            }
            
            // Quit
            fputs($connection, "QUIT\r\n");
            fclose($connection);
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}