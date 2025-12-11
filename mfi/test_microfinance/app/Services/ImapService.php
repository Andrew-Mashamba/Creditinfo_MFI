<?php

namespace App\Services;

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Exception;

class ImapService
{
    protected $client;
    protected $config;
    protected $emailService;
    
    public function __construct($server = null)
    {
        $this->emailService = new EmailService();
        $server = $server ?? config('email-servers.default');
        $this->config = config("email-servers.servers.{$server}");
        
        if (!$this->config) {
            throw new Exception("Email server configuration not found for: {$server}");
        }
    }
    
    /**
     * Connect to IMAP server
     */
    public function connect()
    {
        try {
            $cm = new ClientManager();
            
            $this->client = $cm->make([
                'host' => $this->config['imap']['host'],
                'port' => $this->config['imap']['port'],
                'encryption' => $this->config['imap']['encryption'],
                'validate_cert' => $this->config['imap']['validate_cert'] ?? true,
                'username' => $this->config['imap']['username'],
                'password' => $this->config['imap']['password'],
                'protocol' => $this->config['imap']['protocol'] ?? 'imap',
            ]);
            
            $this->client->connect();
            
            Log::channel('email')->info('Connected to IMAP server', [
                'host' => $this->config['imap']['host'],
                'username' => $this->config['imap']['username']
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::channel('email')->error('IMAP connection failed: ' . $e->getMessage());
            throw new Exception('Failed to connect to email server: ' . $e->getMessage());
        }
    }
    
    /**
     * Disconnect from IMAP server
     */
    public function disconnect()
    {
        if ($this->client) {
            $this->client->disconnect();
        }
    }
    
    /**
     * Sync emails from IMAP server
     */
    public function syncEmails($userId, $folders = null)
    {
        if (!$this->client) {
            $this->connect();
        }
        
        $folders = $folders ?? config('email-servers.sync.folders', ['INBOX']);
        $syncedCount = 0;
        
        foreach ($folders as $folderName) {
            try {
                $folder = $this->client->getFolder($folderName);
                if (!$folder) {
                    continue;
                }
                
                // Get messages from the last X days
                $daysToSync = config('email-servers.sync.days_to_sync', 30);
                $since = Carbon::now()->subDays($daysToSync);
                
                $messages = $folder->messages()
                    ->since($since->format('d-M-Y'))
                    ->limit(config('email-servers.sync.batch_size', 50))
                    ->get();
                
                foreach ($messages as $message) {
                    if ($this->importEmail($message, $userId, strtolower($folderName))) {
                        $syncedCount++;
                    }
                }
                
            } catch (Exception $e) {
                Log::channel('email')->error("Error syncing folder {$folderName}: " . $e->getMessage());
            }
        }
        
        Log::channel('email')->info("Email sync completed", [
            'user_id' => $userId,
            'synced_count' => $syncedCount
        ]);
        
        return $syncedCount;
    }
    
    /**
     * Import a single email message
     */
    protected function importEmail($message, $userId, $folder)
    {
        try {
            // Check if email already exists (by message ID)
            $messageId = $message->getMessageId();
            if ($messageId && DB::table('emails')->where('message_id', $messageId)->exists()) {
                return false;
            }
            
            // Extract email data
            $from = $message->getFrom()[0] ?? null;
            $to = $message->getTo()[0] ?? null;
            
            $emailData = [
                'message_id' => $messageId,
                'sender_email' => $from ? $from->mail : 'unknown@example.com',
                'sender_name' => $from ? $from->personal : null,
                'recipient_id' => $userId,
                'recipient_email' => $to ? $to->mail : $this->config['imap']['username'],
                'subject' => $message->getSubject() ?? '(No Subject)',
                'body' => $this->emailService->encryptData($message->getTextBody() ?? $message->getHTMLBody() ?? ''),
                'folder' => $this->mapFolder($folder),
                'is_read' => !$message->getFlags()->has('seen'),
                'is_important' => $message->getFlags()->has('flagged'),
                'has_attachments' => $message->hasAttachments(),
                'received_at' => $message->getDate(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Handle CC and BCC
            $cc = $message->getCc();
            if ($cc) {
                $emailData['cc'] = collect($cc)->pluck('mail')->implode(', ');
            }
            
            $bcc = $message->getBcc();
            if ($bcc) {
                $emailData['bcc'] = collect($bcc)->pluck('mail')->implode(', ');
            }
            
            // Insert email
            $emailId = DB::table('emails')->insertGetId($emailData);
            
            // Handle attachments
            if ($message->hasAttachments()) {
                $this->saveAttachments($message, $emailId);
            }
            
            // Check for spam
            if ($this->emailService->detectSpam($emailData)) {
                DB::table('emails')->where('id', $emailId)->update(['folder' => 'spam']);
            }
            
            return true;
            
        } catch (Exception $e) {
            Log::channel('email')->error('Error importing email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Map IMAP folder names to our folder structure
     */
    protected function mapFolder($imapFolder)
    {
        $folderMap = [
            'inbox' => 'inbox',
            'sent' => 'sent',
            'drafts' => 'drafts',
            'trash' => 'trash',
            'spam' => 'spam',
            'junk' => 'spam',
            'deleted' => 'trash',
        ];
        
        return $folderMap[strtolower($imapFolder)] ?? 'inbox';
    }
    
    /**
     * Save email attachments
     */
    protected function saveAttachments($message, $emailId)
    {
        $attachments = $message->getAttachments();
        
        foreach ($attachments as $attachment) {
            try {
                $filename = $attachment->getName();
                $content = $attachment->getContent();
                
                // Generate unique filename
                $path = 'emails/attachments/' . $emailId . '/' . uniqid() . '_' . $filename;
                
                // Save to storage
                \Storage::put($path, $content);
                
                // Save to database
                DB::table('email_attachments')->insert([
                    'email_id' => $emailId,
                    'filename' => $filename,
                    'mime_type' => $attachment->getMimeType(),
                    'size' => strlen($content),
                    'path' => $path,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
            } catch (Exception $e) {
                Log::channel('email')->error('Error saving attachment: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Get folder list from IMAP server
     */
    public function getFolders()
    {
        if (!$this->client) {
            $this->connect();
        }
        
        $folders = [];
        $folderList = $this->client->getFolders();
        
        foreach ($folderList as $folder) {
            $folders[] = [
                'name' => $folder->name,
                'full_name' => $folder->full_name,
                'path' => $folder->path,
                'messages' => $folder->messages()->count(),
                'unread' => $folder->messages()->unseen()->count(),
            ];
        }
        
        return $folders;
    }
    
    /**
     * Send email via IMAP (append to Sent folder)
     */
    public function appendToSent($emailData)
    {
        if (!$this->client) {
            $this->connect();
        }
        
        try {
            $sentFolder = $this->client->getFolder('Sent');
            if (!$sentFolder) {
                $sentFolder = $this->client->getFolder('INBOX.Sent');
            }
            
            if ($sentFolder) {
                // Create email message
                $message = "From: {$emailData['from']}\r\n";
                $message .= "To: {$emailData['to']}\r\n";
                if (!empty($emailData['cc'])) {
                    $message .= "Cc: {$emailData['cc']}\r\n";
                }
                if (!empty($emailData['bcc'])) {
                    $message .= "Bcc: {$emailData['bcc']}\r\n";
                }
                $message .= "Subject: {$emailData['subject']}\r\n";
                $message .= "Date: " . date('r') . "\r\n";
                $message .= "Message-ID: <" . uniqid() . "@{$this->config['domain']}>\r\n";
                $message .= "\r\n";
                $message .= $emailData['body'];
                
                // Append to sent folder
                $sentFolder->appendMessage($message, ['Seen']);
            }
        } catch (Exception $e) {
            Log::channel('email')->error('Error appending to sent folder: ' . $e->getMessage());
        }
    }
}