<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Mail\GenericEmail;
use Carbon\Carbon;
use Exception;
use Swift_SmtpTransport;
use Swift_Mailer;
use App\Services\ImapService;
use App\Services\UndoSendService;
use App\Services\EmailReceiptService;

class EmailService
{
    protected $rateLimitPerMinute = 5;
    protected $rateLimitPerHour = 50;
    protected $maxAttachmentSize = 10485760; // 10MB
    
    /**
     * Send email via SMTP and save to database
     */
    public function sendEmail($data, $enableUndo = true)
    {
        $userId = Auth::id();
        $logContext = [
            'user_id' => $userId,
            'to' => $data['to'] ?? 'not_provided',
            'subject' => $data['subject'] ?? 'no_subject',
            'has_attachments' => !empty($data['attachments']),
            'enable_undo' => $enableUndo
        ];
        
        Log::info('[EMAIL_SEND] Starting email send process', $logContext);
        
        try {
            // Check rate limiting (skip for system/background jobs)
            if ($userId) {
                Log::info('[EMAIL_SEND] Checking rate limit', ['user_id' => $userId]);
                if (!$this->checkRateLimit($userId)) {
                    Log::warning('[EMAIL_SEND] Rate limit exceeded', ['user_id' => $userId]);
                    throw new Exception('Rate limit exceeded. Please wait before sending more emails.');
                }
                Log::info('[EMAIL_SEND] Rate limit check passed', ['user_id' => $userId]);
            } else {
                Log::info('[EMAIL_SEND] Skipping rate limit check for system/background email');
            }
            
            // Validate recipient email with better error handling
            Log::info('[EMAIL_SEND] Validating recipient email', ['email' => $data['to']]);
            $email = trim($data['to']);
            if (empty($email)) {
                Log::error('[EMAIL_SEND] Empty recipient email');
                throw new Exception('Recipient email address is required.');
            }
            
            // More flexible email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Check for common email patterns that might be valid but fail strict validation
                $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
                if (!preg_match($emailPattern, $email)) {
                    Log::error('[EMAIL_SEND] Invalid recipient email format', ['email' => $email]);
                    throw new Exception('Please enter a valid email address format (e.g., user@domain.com).');
                }
            }
            Log::info('[EMAIL_SEND] Email validation passed', ['email' => $email]);
            
            // Encrypt email body for storage
            Log::info('[EMAIL_SEND] Encrypting email body');
            $encryptedBody = $this->encryptData($data['body']);
            Log::info('[EMAIL_SEND] Email body encrypted successfully');
            
            // Get Zima email configuration
            Log::info('[EMAIL_SEND] Loading email configuration');
            $server = config('email-servers.default');
            Log::info('[EMAIL_SEND] Using email server', ['server' => $server]);
            
            $smtpConfig = config("email-servers.servers.{$server}.smtp");
            if (!$smtpConfig) {
                Log::error('[EMAIL_SEND] SMTP configuration not found', ['server' => $server]);
                throw new Exception('Email server configuration not found.');
            }
            Log::info('[EMAIL_SEND] SMTP config loaded', [
                'host' => $smtpConfig['host'] ?? 'not_set',
                'port' => $smtpConfig['port'] ?? 'not_set',
                'encryption' => $smtpConfig['encryption'] ?? 'not_set',
                'username' => isset($smtpConfig['username']) ? 'set' : 'not_set'
            ]);
            
            // Configure mail for Zima
            Log::info('[EMAIL_SEND] Configuring Laravel mail settings');
            $default_from = $smtpConfig['username'] ?? config('mail.from.address', 'noreply@saccos.co.tz');
            config([
                'mail.mailers.smtp.host' => $smtpConfig['host'],
                'mail.mailers.smtp.port' => $smtpConfig['port'],
                'mail.mailers.smtp.encryption' => $smtpConfig['encryption'],
                'mail.mailers.smtp.username' => $smtpConfig['username'],
                'mail.mailers.smtp.password' => $smtpConfig['password'],
                'mail.from.address' => $default_from,
                'mail.from.name' => Auth::user() ? Auth::user()->name : ($data['from_name'] ?? 'SACCOS System'),
            ]);
            Log::info('[EMAIL_SEND] Mail configuration applied successfully', ['mail.from.address' => $default_from]);
            
            // If undo is enabled, don't send immediately
            $sent = false;
            $error = null;
            $undoUntil = null;
            
            if ($enableUndo && !isset($data['immediate'])) {
                Log::info('[EMAIL_SEND] Using undo-enabled send mode');
                
                // Save to database first for undo capability
                Log::info('[EMAIL_SEND] Saving email to database for undo capability');
                try {
                    $emailId = DB::table('emails')->insertGetId([
                        'sender_id' => Auth::id() ?? null,
                        'recipient_email' => $data['to'],
                        'cc' => $data['cc'] ?? null,
                        'bcc' => $data['bcc'] ?? null,
                        'subject' => $data['subject'],
                        'body' => $encryptedBody,
                        'folder' => 'sent',
                        'is_sent' => false,
                        'sent_at' => null,
                        'request_read_receipt' => $data['request_read_receipt'] ?? false,
                        'request_delivery_receipt' => $data['request_delivery_receipt'] ?? false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('[EMAIL_SEND] Email saved to database', ['email_id' => $emailId]);
                } catch (Exception $e) {
                    Log::error('[EMAIL_SEND] Failed to save email to database', ['error' => $e->getMessage()]);
                    throw $e;
                }
                
                // Queue for undo
                Log::info('[EMAIL_SEND] Queuing email for undo service', ['email_id' => $emailId]);
                try {
                    $undoService = new UndoSendService();
                    $undoResult = $undoService->queueEmailForSending($emailId, Auth::id() ?? 0);
                    
                    if ($undoResult['success']) {
                        $undoUntil = $undoResult['undo_until'];
                        $sent = true; // Consider it "sent" from user perspective
                        Log::info('[EMAIL_SEND] Email queued successfully for undo', [
                            'email_id' => $emailId,
                            'undo_until' => $undoUntil
                        ]);
                    } else {
                        Log::error('[EMAIL_SEND] Failed to queue email for undo', [
                            'email_id' => $emailId,
                            'error' => $undoResult['message'] ?? 'Unknown error'
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('[EMAIL_SEND] Exception in undo service', ['error' => $e->getMessage()]);
                    throw $e;
                }
            } else {
                Log::info('[EMAIL_SEND] Using immediate send mode (no undo)');
                
                // Send immediately (no undo)
                $from_email = $smtpConfig['username'] ?? config('mail.from.address', 'noreply@saccos.co.tz');
                Log::info('[EMAIL_SEND] Determined from_email address', ['from_email' => $from_email]);

                $emailData = [
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'from_name' => Auth::user() ? Auth::user()->name : ($data['from_name'] ?? 'SACCOS System'),
                    'from_email' => $from_email,
                ];
                Log::info('[EMAIL_SEND] Prepared email data for immediate send', [
                    'from_name' => $emailData['from_name'],
                    'from_email' => $emailData['from_email'],
                    'subject' => $emailData['subject']
                ]);
                
                try {
                    Log::info('[EMAIL_SEND] Preparing Laravel Mail message');
                    $message = Mail::to($data['to'])
                        ->cc($data['cc'] ?? [])
                        ->bcc($data['bcc'] ?? []);
                    
                    Log::info('[EMAIL_SEND] Mail message prepared, checking for receipt headers');
                    
                    // Add receipt headers if requested
                    if (($data['request_read_receipt'] ?? false) || ($data['request_delivery_receipt'] ?? false)) {
                        Log::info('[EMAIL_SEND] Adding receipt headers to message');
                        $receiptService = new EmailReceiptService();
                        $message->send(function($msg) use ($receiptService, $data, $emailData) {
                            $receiptService->addReceiptHeaders($msg->getSwiftMessage(), array_merge($data, [
                                'sender_email' => Auth::user() ? Auth::user()->email : ($data['from_email'] ?? config('mail.from.address'))
                            ]));
                            $msg->subject($emailData['subject'])
                                ->html($emailData['body']);
                        });
                        Log::info('[EMAIL_SEND] Message sent with receipt headers');
                    } else {
                        Log::info('[EMAIL_SEND] Sending message using GenericEmail mailable');
                        $message->send(new GenericEmail($emailData));
                        Log::info('[EMAIL_SEND] Message sent successfully');
                    }
                    $sent = true;
                    Log::info('[EMAIL_SEND] Email marked as sent successfully');
                    
                    // Also append to sent folder via IMAP
                    Log::info('[EMAIL_SEND] Attempting to append to sent folder via IMAP');
                    try {
                        $this->appendToSentFolder($data);
                        Log::info('[EMAIL_SEND] Successfully appended to sent folder via IMAP');
                    } catch (Exception $imapError) {
                        Log::warning('[EMAIL_SEND] Failed to append to sent folder via IMAP', ['error' => $imapError->getMessage()]);
                    }
                } catch (Exception $e) {
                    Log::error('[EMAIL_SEND] SMTP Email sending failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $error = $e->getMessage();
                }
                
                // Save to database
                Log::info('[EMAIL_SEND] Saving email to database after immediate send attempt');
                try {
                    $emailId = DB::table('emails')->insertGetId([
                        'sender_id' => Auth::id() ?? null,
                        'recipient_email' => $data['to'],
                        'cc' => $data['cc'] ?? null,
                        'bcc' => $data['bcc'] ?? null,
                        'subject' => $data['subject'],
                        'body' => $encryptedBody,
                        'folder' => 'sent',
                        'is_sent' => $sent,
                        'sent_at' => $sent ? now() : null,
                        'smtp_error' => $error,
                        'request_read_receipt' => $data['request_read_receipt'] ?? false,
                        'request_delivery_receipt' => $data['request_delivery_receipt'] ?? false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('[EMAIL_SEND] Email saved to database after immediate send', [
                        'email_id' => $emailId,
                        'sent' => $sent,
                        'error' => $error
                    ]);
                } catch (Exception $dbError) {
                    Log::error('[EMAIL_SEND] Failed to save email to database after send', ['error' => $dbError->getMessage()]);
                    throw $dbError;
                }
            }
            
            // If sent successfully, create copy in recipient's inbox
            if ($sent) {
                Log::info('[EMAIL_SEND] Email sent successfully, creating copy in recipient inbox');
                
                $recipientUser = DB::table('users')->where('email', $data['to'])->first();
                if ($recipientUser) {
                    Log::info('[EMAIL_SEND] Recipient found in system, creating inbox copy', [
                        'recipient_id' => $recipientUser->id,
                        'recipient_email' => $recipientUser->email
                    ]);
                    
                    try {
                        $inboxEmailId = DB::table('emails')->insertGetId([
                            'sender_id' => Auth::id() ?? null,
                            'recipient_id' => $recipientUser->id,
                            'recipient_email' => $data['to'],
                            'cc' => $data['cc'] ?? null,
                            'bcc' => $data['bcc'] ?? null,
                            'subject' => $data['subject'],
                            'body' => $encryptedBody,
                            'folder' => 'inbox',
                            'is_read' => false,
                            'request_read_receipt' => $data['request_read_receipt'] ?? false,
                            'request_delivery_receipt' => $data['request_delivery_receipt'] ?? false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        Log::info('[EMAIL_SEND] Recipient inbox copy created', ['inbox_email_id' => $inboxEmailId]);
                    } catch (Exception $inboxError) {
                        Log::error('[EMAIL_SEND] Failed to create recipient inbox copy', ['error' => $inboxError->getMessage()]);
                    }
                } else {
                    Log::info('[EMAIL_SEND] Recipient not found in system, skipping inbox copy', ['recipient_email' => $data['to']]);
                }
                
                // Log successful send
                Log::info('[EMAIL_SEND] Logging email activity');
                try {
                    $this->logEmailActivity('sent', $emailId, Auth::id() ?? null);
                    Log::info('[EMAIL_SEND] Email activity logged successfully');
                } catch (Exception $logError) {
                    Log::warning('[EMAIL_SEND] Failed to log email activity', ['error' => $logError->getMessage()]);
                }
            } else {
                Log::warning('[EMAIL_SEND] Email was not marked as sent, skipping recipient inbox copy');
            }
            
            $response = [
                'success' => $sent,
                'message' => $sent ? 'Email sent successfully' : 'Email saved but not sent: ' . $error,
                'email_id' => $emailId
            ];
            
            if ($undoUntil) {
                $response['undo_until'] = $undoUntil;
                $response['undo_seconds'] = (new UndoSendService())->getUndoWindowSeconds();
                $response['message'] = 'Email will be sent in ' . $response['undo_seconds'] . ' seconds';
                Log::info('[EMAIL_SEND] Email queued with undo capability', [
                    'undo_until' => $undoUntil,
                    'undo_seconds' => $response['undo_seconds']
                ]);
            }
            
            Log::info('[EMAIL_SEND] Email send process completed', $response);
            return $response;
            
        } catch (Exception $e) {
            Log::error('[EMAIL_SEND] Email service critical error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'to' => $data['to'] ?? 'unknown'
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check rate limiting for user
     */
    protected function checkRateLimit($userId)
    {
        $oneMinuteAgo = Carbon::now()->subMinute();
        $oneHourAgo = Carbon::now()->subHour();
        
        $minuteCount = DB::table('emails')
            ->where('sender_id', $userId)
            ->where('created_at', '>=', $oneMinuteAgo)
            ->count();
            
        $hourCount = DB::table('emails')
            ->where('sender_id', $userId)
            ->where('created_at', '>=', $oneHourAgo)
            ->count();
            
        return $minuteCount < $this->rateLimitPerMinute && $hourCount < $this->rateLimitPerHour;
    }
    
    /**
     * Encrypt sensitive data
     */
    protected function encryptData($data)
    {
        try {
            return Crypt::encryptString($data);
        } catch (Exception $e) {
            Log::error('Encryption failed: ' . $e->getMessage());
            return $data; // Fallback to unencrypted
        }
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decryptData($data)
    {
        try {
            return Crypt::decryptString($data);
        } catch (Exception $e) {
            Log::error('Decryption failed: ' . $e->getMessage());
            return $data; // Assume it's not encrypted
        }
    }
    
    /**
     * Log email activity
     */
    protected function logEmailActivity($action, $emailId, $userId)
    {
        DB::table('email_activity_logs')->insert([
            'email_id' => $emailId,
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
    
    /**
     * Basic spam detection
     */
    public function detectSpam($email)
    {
        $spamKeywords = [
            'viagra', 'casino', 'lottery', 'winner', 'prize', 
            'free money', 'click here', 'limited time', 'act now',
            'make money fast', 'work from home', 'earn extra cash'
        ];
        
        $body = strtolower($email['body'] ?? '');
        $subject = strtolower($email['subject'] ?? '');
        $content = $body . ' ' . $subject;
        
        $spamScore = 0;
        
        // Check for spam keywords
        foreach ($spamKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                $spamScore += 2;
            }
        }
        
        // Check for excessive caps
        $capsCount = preg_match_all('/[A-Z]/', $email['body'] ?? '', $matches);
        $totalChars = strlen($email['body'] ?? '');
        if ($totalChars > 0 && ($capsCount / $totalChars) > 0.5) {
            $spamScore += 3;
        }
        
        // Check for excessive exclamation marks
        if (substr_count($content, '!') > 5) {
            $spamScore += 2;
        }
        
        // Check for suspicious sender patterns
        if (preg_match('/\d{5,}/', $email['sender_email'] ?? '')) {
            $spamScore += 2;
        }
        
        return $spamScore >= 5;
    }
    
    /**
     * Get email statistics
     */
    public function getEmailStats($userId)
    {
        $stats = [
            'total_sent' => DB::table('emails')
                ->where('sender_id', $userId)
                ->where('folder', 'sent')
                ->count(),
                
            'total_received' => DB::table('emails')
                ->where('recipient_id', $userId)
                ->where('folder', 'inbox')
                ->count(),
                
            'unread_count' => DB::table('emails')
                ->where('recipient_id', $userId)
                ->where('folder', 'inbox')
                ->where('is_read', false)
                ->count(),
                
            'storage_used' => DB::table('emails')
                ->where(function($query) use ($userId) {
                    $query->where('recipient_id', $userId)
                          ->orWhere('sender_id', $userId);
                })
                ->sum(DB::raw('LENGTH(body) + LENGTH(subject)')),
                
            'emails_today' => DB::table('emails')
                ->where(function($query) use ($userId) {
                    $query->where('recipient_id', $userId)
                          ->orWhere('sender_id', $userId);
                })
                ->whereDate('created_at', Carbon::today())
                ->count(),
                
            'spam_count' => DB::table('emails')
                ->where('recipient_id', $userId)
                ->where('folder', 'spam')
                ->count()
        ];
        
        return $stats;
    }
    
    /**
     * Auto backup old emails
     */
    public function backupOldEmails($daysOld = 90)
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);
        
        $oldEmails = DB::table('emails')
            ->where('created_at', '<', $cutoffDate)
            ->get();
            
        foreach ($oldEmails as $email) {
            DB::table('email_archives')->insert([
                'original_id' => $email->id,
                'sender_id' => $email->sender_id,
                'recipient_id' => $email->recipient_id,
                'recipient_email' => $email->recipient_email,
                'subject' => $email->subject,
                'body' => $email->body,
                'folder' => $email->folder,
                'archived_at' => now(),
                'created_at' => $email->created_at,
            ]);
        }
        
        // Delete archived emails from main table
        DB::table('emails')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
            
        return count($oldEmails);
    }
    
    /**
     * Append email to sent folder via IMAP
     */
    protected function appendToSentFolder($emailData)
    {
        try {
            $imapService = new ImapService();
            $imapService->connect();
            
            $sentData = [
                'from' => (Auth::user() ? Auth::user()->name : 'SACCOS System') . ' <' . config('mail.from.address') . '>',
                'to' => $emailData['to'],
                'cc' => $emailData['cc'] ?? '',
                'bcc' => $emailData['bcc'] ?? '',
                'subject' => $emailData['subject'],
                'body' => $emailData['body']
            ];
            
            $imapService->appendToSent($sentData);
            $imapService->disconnect();
            
        } catch (Exception $e) {
            Log::channel('email')->warning('Failed to append to sent folder: ' . $e->getMessage());
        }
    }

    /**
     * Forward an email
     */
    public function forwardEmail($emailId, $forwardTo, $userId)
    {
        try {
            $email = DB::table('emails')->where('id', $emailId)->first();
            if (!$email) {
                return ['success' => false, 'message' => 'Email not found'];
            }

            // Create forwarded email
            $newEmailId = DB::table('emails')->insertGetId([
                'sender_id' => $userId,
                'recipient_email' => $forwardTo,
                'subject' => 'Fwd: ' . $email->subject,
                'body' => $this->encryptData("---------- Forwarded message ----------\n" .
                    "From: " . $email->sender_email . "\n" .
                    "Date: " . $email->created_at . "\n" .
                    "Subject: " . $email->subject . "\n\n" .
                    $this->decryptData($email->body)),
                'folder' => 'sent',
                'is_read' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Send via SMTP
            $this->sendViaSmtp([
                'to' => $forwardTo,
                'subject' => 'Fwd: ' . $email->subject,
                'body' => $this->decryptData($email->body)
            ]);

            return ['success' => true, 'email_id' => $newEmailId];
        } catch (\Exception $e) {
            Log::error('Failed to forward email: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function getZimaEmailConfig()
    {
        Log::info('[EMAIL_SEND] Loading email configuration');
        
        // Get email server configuration from database or use defaults
        $emailConfig = DB::table('email_servers')
            ->where('name', 'zima')
            ->where('is_active', true)
            ->first();
            
        if (!$emailConfig) {
            Log::warning('[EMAIL_SEND] Zima email config not found, using defaults');
            // Fallback to environment variables
            return [
                'host' => env('MAIL_HOST', 'server354.web-hosting.com'),
                'port' => env('MAIL_PORT', 465),
                'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
                'username' => env('MAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('MAIL_PASSWORD', ''),
                'from_address' => env('MAIL_FROM_ADDRESS', 'andrew.mashamba@zima.co.tz'),
                'from_name' => env('MAIL_FROM_NAME', 'SACCOS System')
            ];
        }
        
        Log::info('[EMAIL_SEND] Using email server', ['server' => $emailConfig->name]);
        
        return [
            'host' => $emailConfig->smtp_host,
            'port' => $emailConfig->smtp_port,
            'encryption' => $emailConfig->smtp_encryption,
            'username' => $emailConfig->smtp_username,
            'password' => $emailConfig->smtp_password,
            'from_address' => $emailConfig->from_address,
            'from_name' => $emailConfig->from_name
        ];
    }
}