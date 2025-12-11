<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\EmailService;
use App\Services\EmailAttachmentService;
use App\Services\EmailReceiptService;
use App\Services\EmailTrackingService;

class EmailDetail extends Component
{
    public $emailId;
    public $email;
    public $attachments = [];
    public $showReplyForm = false;
    public $replyMode = 'reply'; // reply, reply-all, forward
    public $receiptStatus;
    public $trackingData;
    public $showReceiptDialog = false;
    
    // Reply fields
    public $replyTo = '';
    public $replyCc = '';
    public $replyBcc = '';
    public $replySubject = '';
    public $replyBody = '';

    protected $listeners = ['refreshEmail' => 'loadEmail'];

    public function mount($emailId)
    {
        $this->emailId = $emailId;
        $this->loadEmail();
        $this->checkReadReceipt();
    }

    public function loadEmail()
    {
        $emailData = DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->leftJoin('users as recipients', 'emails.recipient_id', '=', 'recipients.id')
            ->select(
                'emails.*', 
                'senders.name as sender_name', 
                'senders.email as sender_email',
                'recipients.name as recipient_name',
                'recipients.email as recipient_user_email'
            )
            ->where('emails.id', $this->emailId)
            ->first();

        if ($emailData) {
            // Convert to array for Livewire compatibility
            $this->email = (array) $emailData;

            // Mark as read if it's the recipient viewing
            if ($this->email['recipient_id'] == Auth::id() && !($this->email['is_read'] ?? false)) {
                DB::table('emails')
                    ->where('id', $this->emailId)
                    ->update(['is_read' => true]);
            }
            
            // Decrypt email body
            $emailService = new EmailService();
            $this->email['decrypted_body'] = $emailService->decryptData($this->email['body']);
            
            // Load attachments
            $this->loadAttachments();
            
            // Load receipt status
            $receiptService = new EmailReceiptService();
            $this->receiptStatus = $receiptService->getReceiptStatus($this->emailId);
            
            // Load tracking data if available
            if ($this->email['enable_tracking'] ?? false) {
                $trackingService = new EmailTrackingService();
                $this->trackingData = $trackingService->getEmailTrackingData($this->emailId);
            }
        }
    }
    
    public function loadAttachments()
    {
        $attachmentService = new EmailAttachmentService();
        $this->attachments = $attachmentService->getEmailAttachments($this->emailId);
    }
    
    public function downloadAttachment($attachmentId)
    {
        try {
            $attachmentService = new EmailAttachmentService();
            $attachment = $attachmentService->downloadAttachment($attachmentId, Auth::id());
            
            return Storage::disk($attachment['disk'])->download(
                $attachment['path'],
                $attachment['filename']
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to download attachment: ' . $e->getMessage());
        }
    }
    
    public function previewAttachment($attachmentId)
    {
        try {
            $attachmentService = new EmailAttachmentService();
            $attachment = $attachmentService->previewAttachment($attachmentId, Auth::id());
            
            // For images and PDFs, we could open in a modal or new tab
            $this->emit('openAttachmentPreview', $attachment);
        } catch (\Exception $e) {
            session()->flash('error', 'Preview not available: ' . $e->getMessage());
        }
    }
    
    public function checkReadReceipt()
    {
        if ($this->email && 
            $this->email['recipient_id'] == Auth::id() && 
            ($this->email['request_read_receipt'] ?? false) && 
            !($this->email['read_receipt_sent_at'] ?? null)) {
            $this->showReceiptDialog = true;
        }
    }
    
    public function sendReadReceipt()
    {
        $receiptService = new EmailReceiptService();
        $result = $receiptService->processReadReceipt($this->emailId, Auth::id());
        
        if ($result['success']) {
            session()->flash('message', 'Read receipt sent');
            $this->showReceiptDialog = false;
            $this->loadEmail();
        }
    }
    
    public function declineReadReceipt()
    {
        $this->showReceiptDialog = false;
        session()->flash('message', 'Read receipt declined');
    }

    public function backToList()
    {
        $this->emitUp('$set', 'currentEmailId', null);
    }

    public function reply()
    {
        $this->replyMode = 'reply';
        $this->showReplyForm = true;
        $this->setupReplyForm();
    }

    public function replyAll()
    {
        $this->replyMode = 'reply-all';
        $this->showReplyForm = true;
        $this->setupReplyForm();
    }

    public function forward()
    {
        $this->replyMode = 'forward';
        $this->showReplyForm = true;
        $this->setupReplyForm();
    }

    public function setupReplyForm()
    {
        if ($this->replyMode == 'reply') {
            $this->replyTo = $this->email->sender_email ?? $this->email->recipient_email;
            $this->replyCc = '';
            $this->replyBcc = '';
            $this->replySubject = 'Re: ' . $this->email->subject;
        } elseif ($this->replyMode == 'reply-all') {
            $this->replyTo = $this->email->sender_email ?? $this->email->recipient_email;
            $this->replyCc = $this->email->cc;
            $this->replyBcc = '';
            $this->replySubject = 'Re: ' . $this->email->subject;
        } else { // forward
            $this->replyTo = '';
            $this->replyCc = '';
            $this->replyBcc = '';
            $this->replySubject = 'Fwd: ' . $this->email->subject;
        }

        // Quote original message
        $this->replyBody = "\n\n\n--- Original Message ---\n";
        $this->replyBody .= "From: " . ($this->email->sender_name ?? $this->email->sender_email ?? 'Unknown') . "\n";
        $this->replyBody .= "Date: " . \Carbon\Carbon::parse($this->email->created_at)->format('M d, Y H:i') . "\n";
        $this->replyBody .= "Subject: " . $this->email->subject . "\n\n";
        $this->replyBody .= strip_tags($this->email->body);
    }

    public function cancelReply()
    {
        $this->showReplyForm = false;
        $this->resetReplyFields();
    }

    public function resetReplyFields()
    {
        $this->replyTo = '';
        $this->replyCc = '';
        $this->replyBcc = '';
        $this->replySubject = '';
        $this->replyBody = '';
    }

    public function sendReply()
    {
        $this->validate([
            'replyTo' => 'required|email',
            'replySubject' => 'required|max:255',
            'replyBody' => 'required'
        ]);

        // Save to sent folder
        DB::table('emails')->insert([
            'sender_id' => Auth::id(),
            'recipient_email' => $this->replyTo,
            'cc' => $this->replyCc,
            'bcc' => $this->replyBcc,
            'subject' => $this->replySubject,
            'body' => $this->replyBody,
            'folder' => 'sent',
            'is_sent' => true,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Also create a copy in recipient's inbox (simulating email delivery)
        $recipientUser = DB::table('users')->where('email', $this->replyTo)->first();
        if ($recipientUser) {
            DB::table('emails')->insert([
                'sender_id' => Auth::id(),
                'recipient_id' => $recipientUser->id,
                'recipient_email' => $this->replyTo,
                'cc' => $this->replyCc,
                'bcc' => $this->replyBcc,
                'subject' => $this->replySubject,
                'body' => $this->replyBody,
                'folder' => 'inbox',
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        session()->flash('message', 'Reply sent successfully');
        $this->cancelReply();
        $this->emitUp('refreshComponent');
    }

    public function deleteEmail()
    {
        if ($this->email->folder == 'trash') {
            // Permanently delete
            DB::table('emails')->where('id', $this->emailId)->delete();
        } else {
            // Move to trash
            DB::table('emails')
                ->where('id', $this->emailId)
                ->update(['folder' => 'trash', 'deleted_at' => now()]);
        }
        
        session()->flash('message', 'Email deleted successfully');
        $this->backToList();
    }

    public function markAsSpam()
    {
        DB::table('emails')
            ->where('id', $this->emailId)
            ->update(['folder' => 'spam']);
        
        session()->flash('message', 'Email marked as spam');
        $this->backToList();
    }

    public function render()
    {
        return view('livewire.email.email-detail');
    }
}