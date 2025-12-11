<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
use App\Services\EmailTrackingService;

class EmailReadingPane extends Component
{
    public $emailId;
    public $email;
    public $showThreads = false;
    public $conversationEmails = [];
    
    public function mount($emailId)
    {
        $this->emailId = $emailId;
        $this->loadEmail();
        $this->markAsRead();
    }
    
    public function loadEmail()
    {
        $emailData = DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where('emails.id', $this->emailId)
            ->select(
                'emails.*',
                'senders.name as sender_name',
                'senders.email as sender_email'
            )
            ->first();
            
        if ($emailData) {
            // Convert to array for Livewire compatibility
            $this->email = (array) $emailData;
            
            // Decrypt email body
            $emailService = new EmailService();
            $this->email['body'] = $emailService->decryptData($this->email['body']);
            
            // Load conversation if it exists
            $this->loadConversation();
        }
    }
    
    public function loadConversation()
    {
        if (!$this->email || empty($this->email['conversation_id'])) return;
        
        $this->conversationEmails = DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where('emails.conversation_id', $this->email['conversation_id'])
            ->where('emails.id', '!=', $this->emailId)
            ->select(
                'emails.*',
                'senders.name as sender_name',
                'senders.email as sender_email'
            )
            ->orderBy('emails.created_at', 'asc')
            ->get()
            ->map(function($email) {
                $emailService = new EmailService();
                $email->body = $emailService->decryptData($email->body);
                return $email;
            });
    }
    
    public function markAsRead()
    {
        if ($this->email && !($this->email['is_read'] ?? false)) {
            DB::table('emails')
                ->where('id', $this->emailId)
                ->update(['is_read' => true, 'read_at' => now()]);
                
            // Track read receipt if enabled
            if ($this->email['request_read_receipt'] ?? false) {
                $trackingService = new EmailTrackingService();
                $trackingService->trackReadReceipt($this->emailId, Auth::id());
            }
            
            $this->emit('emailRead');
        }
    }
    
    public function reply()
    {
        $this->emit('composeReply', $this->emailId);
    }
    
    public function replyAll()
    {
        $this->emit('composeReplyAll', $this->emailId);
    }
    
    public function forward()
    {
        $this->emit('composeForward', $this->emailId);
    }
    
    public function deleteEmail()
    {
        DB::table('emails')
            ->where('id', $this->emailId)
            ->update(['folder' => 'trash', 'deleted_at' => now()]);
            
        $this->emit('emailDeleted');
    }
    
    public function toggleFlag()
    {
        $newFlagStatus = !($this->email['is_flagged'] ?? false);
        
        DB::table('emails')
            ->where('id', $this->emailId)
            ->update(['is_flagged' => $newFlagStatus]);
            
        $this->email['is_flagged'] = $newFlagStatus;
    }
    
    public function togglePin()
    {
        $newPinStatus = !($this->email['is_pinned'] ?? false);
        
        DB::table('emails')
            ->where('id', $this->emailId)
            ->update([
                'is_pinned' => $newPinStatus,
                'pinned_at' => $newPinStatus ? now() : null
            ]);
            
        $this->email['is_pinned'] = $newPinStatus;
        $this->email['pinned_at'] = $newPinStatus ? now() : null;
    }
    
    public function moveToFolder($folder)
    {
        DB::table('emails')
            ->where('id', $this->emailId)
            ->update(['folder' => $folder]);
            
        $this->emit('emailMoved', $folder);
    }
    
    public function createRule()
    {
        // Emit event to parent to open rule creation modal
        $this->emit('openCreateRuleModal', $this->emailId);
    }
    
    public function downloadAttachment($attachmentId)
    {
        // Handle attachment download
        return redirect()->route('email.attachment.download', $attachmentId);
    }
    
    public function render()
    {
        return view('livewire.email.email-reading-pane');
    }
}