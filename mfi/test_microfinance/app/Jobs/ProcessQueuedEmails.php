<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericEmail;
use App\Services\EmailService;
use App\Services\ImapService;
use Carbon\Carbon;

class ProcessQueuedEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[EMAIL_QUEUE] Starting to process queued emails');
        
        // Find all emails that are ready to be sent (undo window expired)
        $queuedEmails = DB::table('emails')
            ->where('is_sent', false)
            ->where('folder', 'sent')
            ->whereNotNull('undo_send_until')
            ->where('undo_send_until', '<=', Carbon::now())
            ->get();
            
        Log::info('[EMAIL_QUEUE] Found ' . $queuedEmails->count() . ' emails ready to send');
        
        foreach ($queuedEmails as $email) {
            try {
                Log::info('[EMAIL_QUEUE] Processing email ID: ' . $email->id, [
                    'to' => $email->recipient_email,
                    'subject' => $email->subject,
                    'undo_expired_at' => $email->undo_send_until
                ]);
                
                // Get SMTP configuration
                $server = config('email-servers.default');
                $smtpConfig = config("email-servers.servers.{$server}.smtp");
                
                if (!$smtpConfig) {
                    Log::error('[EMAIL_QUEUE] SMTP configuration not found for email ID: ' . $email->id);
                    continue;
                }
                
                // Configure Laravel mail settings
                config([
                    'mail.mailers.smtp.host' => $smtpConfig['host'],
                    'mail.mailers.smtp.port' => $smtpConfig['port'],
                    'mail.mailers.smtp.encryption' => $smtpConfig['encryption'],
                    'mail.mailers.smtp.username' => $smtpConfig['username'],
                    'mail.mailers.smtp.password' => $smtpConfig['password'],
                    'mail.from.address' => $smtpConfig['username'],
                    'mail.from.name' => 'SACCOS System',
                ]);
                
                // Decrypt email body
                $emailService = new EmailService();
                $decryptedBody = $emailService->decryptData($email->body);
                
                // Prepare email data
                $emailData = [
                    'subject' => $email->subject,
                    'body' => $decryptedBody,
                    'from_name' => 'SACCOS System',
                    'from_email' => $smtpConfig['username'],
                ];
                
                // Send the email
                Log::info('[EMAIL_QUEUE] Sending email via SMTP', ['email_id' => $email->id]);
                
                Mail::to($email->recipient_email)
                    ->cc($email->cc ?? [])
                    ->bcc($email->bcc ?? [])
                    ->send(new GenericEmail($emailData));
                
                Log::info('[EMAIL_QUEUE] Email sent successfully via SMTP', ['email_id' => $email->id]);
                
                // Update email status to sent
                DB::table('emails')
                    ->where('id', $email->id)
                    ->update([
                        'is_sent' => true,
                        'sent_at' => Carbon::now(),
                        'undo_send_until' => null,
                        'updated_at' => Carbon::now()
                    ]);
                
                Log::info('[EMAIL_QUEUE] Email marked as sent in database', ['email_id' => $email->id]);
                
                // Try to append to sent folder via IMAP (optional)
                try {
                    if (class_exists('\Webklex\PHPIMAP\ClientManager')) {
                        $this->appendToSentFolder($email, $decryptedBody);
                        Log::info('[EMAIL_QUEUE] Email appended to sent folder via IMAP', ['email_id' => $email->id]);
                    } else {
                        Log::info('[EMAIL_QUEUE] IMAP package not installed, skipping sent folder append', ['email_id' => $email->id]);
                    }
                } catch (\Exception $imapError) {
                    Log::warning('[EMAIL_QUEUE] Failed to append to sent folder via IMAP', [
                        'email_id' => $email->id,
                        'error' => $imapError->getMessage()
                    ]);
                }
                
                // Log email activity
                try {
                    DB::table('email_activity_logs')->insert([
                        'email_id' => $email->id,
                        'user_id' => $email->sender_id,
                        'action' => 'sent_via_queue',
                        'ip_address' => '127.0.0.1',
                        'user_agent' => 'Queue Worker',
                        'created_at' => Carbon::now()
                    ]);
                    Log::info('[EMAIL_QUEUE] Email activity logged', ['email_id' => $email->id]);
                } catch (\Exception $logError) {
                    Log::warning('[EMAIL_QUEUE] Failed to log email activity', [
                        'email_id' => $email->id,
                        'error' => $logError->getMessage()
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::error('[EMAIL_QUEUE] Failed to send email ID: ' . $email->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Mark email with error
                DB::table('emails')
                    ->where('id', $email->id)
                    ->update([
                        'smtp_error' => $e->getMessage(),
                        'updated_at' => Carbon::now()
                    ]);
            }
        }
        
        Log::info('[EMAIL_QUEUE] Finished processing queued emails');
    }
    
    /**
     * Append email to sent folder via IMAP
     */
    private function appendToSentFolder($email, $decryptedBody)
    {
        try {
            $imapService = new ImapService();
            $imapService->connect();
            
            $sentData = [
                'from' => 'SACCOS System <' . config('mail.from.address') . '>',
                'to' => $email->recipient_email,
                'cc' => $email->cc ?? '',
                'bcc' => $email->bcc ?? '',
                'subject' => $email->subject,
                'body' => $decryptedBody
            ];
            
            $imapService->appendToSent($sentData);
            $imapService->disconnect();
            
        } catch (\Exception $e) {
            Log::warning('[EMAIL_QUEUE] IMAP append failed: ' . $e->getMessage());
        }
    }
}