<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
use App\Services\EmailSignatureService;
use App\Services\ScheduledEmailService;
use App\Services\EmailValidationService;
use App\Services\EmailAttachmentService;
use App\Services\EmailDraftService;
use Exception;

class NewComposePane extends Component
{
    use WithFileUploads;
    
    // Email fields with proper initialization
    public $to = '';
    public $cc = '';
    public $bcc = '';
    public $subject = '';
    public $body = '';
    
    // UI state
    public $showCc = false;
    public $showBcc = false;
    public $isScheduled = false;
    public $scheduledDate = '';
    public $scheduledTime = '';
    public $showAdvancedOptions = false;
    public $isMinimized = false;
    public $isFullscreen = false;
    
    // Email composition state
    public $replyToEmailId = null;
    public $isReply = false;
    public $isReplyAll = false;
    public $isForward = false;
    public $draftId = null;
    
    // Attachments
    public $attachments = [];
    public $attachmentErrors = [];
    public $maxAttachments = 10;
    public $maxAttachmentSize = 10; // MB
    
    // Signature
    public $selectedSignature = '';
    public $signatures = [];
    public $showSignatureSelector = false;
    
    // Priority and options
    public $priority = 'normal'; // low, normal, high
    public $requestReadReceipt = false;
    public $requestDeliveryReceipt = false;
    public $enableTracking = false;
    
    // Validation state
    public $validationErrors = [];
    public $isValidating = false;
    
    protected $listeners = [
        'composeReply' => 'composeReply',
        'composeReplyAll' => 'composeReplyAll', 
        'composeForward' => 'composeForward',
        'loadDraft' => 'loadDraft',
        'restoreDraft' => 'restoreDraft'
    ];
    
    // Rules for validation
    protected $rules = [
        'to' => 'required|string|max:255',
        'subject' => 'required|string|max:500',
        'body' => 'required|string|min:1',
        'cc' => 'nullable|string|max:1000',
        'bcc' => 'nullable|string|max:1000',
        'scheduledDate' => 'nullable|date|after:today',
        'scheduledTime' => 'nullable|date_format:H:i',
        'priority' => 'in:low,normal,high',
        'attachments.*' => 'nullable|file|max:10240' // 10MB max
    ];
    
    public function mount()
    {
        $this->loadSignatures();
        $this->loadDefaultSignature();
    }
    
    public function updated($propertyName)
    {
        // Real-time validation
        if (in_array($propertyName, ['to', 'subject', 'body'])) {
            $this->validateOnly($propertyName);
        }
    }
    
    public function updatedTo($value)
    {
        // Email validation with better error handling
        if (!empty($value)) {
            $this->validateOnly('to');
        }
    }
    
    public function updatedAttachments()
    {
        $this->validateAttachments();
    }
    
    protected function validateAttachments()
    {
        $this->attachmentErrors = [];
        
        if (count($this->attachments) > $this->maxAttachments) {
            $this->attachmentErrors[] = "Maximum {$this->maxAttachments} attachments allowed.";
        }
        
        foreach ($this->attachments as $index => $attachment) {
            if ($attachment && $attachment->getSize() > ($this->maxAttachmentSize * 1024 * 1024)) {
                $this->attachmentErrors[] = "File '{$attachment->getClientOriginalName()}' exceeds {$this->maxAttachmentSize}MB limit.";
            }
        }
    }
    
    public function composeReply($emailId)
    {
        $this->resetCompose();
        $this->replyToEmailId = $emailId;
        $this->isReply = true;
        $this->loadReplyData($emailId);
    }
    
    public function composeReplyAll($emailId)
    {
        $this->resetCompose();
        $this->replyToEmailId = $emailId;
        $this->isReplyAll = true;
        $this->loadReplyData($emailId, true);
    }
    
    public function composeForward($emailId)
    {
        $this->resetCompose();
        $this->replyToEmailId = $emailId;
        $this->isForward = true;
        $this->loadForwardData($emailId);
    }
    
    protected function loadReplyData($emailId, $replyAll = false)
    {
        $emailData = DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where('emails.id', $emailId)
            ->select('emails.*', 'senders.name as sender_name', 'senders.email as sender_email')
            ->first();
            
        if (!$emailData) return;
        
        $email = (array) $emailData;
        
        // Set recipient
        $this->to = $email['sender_email'];
        
        // Set subject
        $this->subject = 'Re: ' . preg_replace('/^Re:\s*/', '', $email['subject']);
        
        // Handle CC for reply all
        if ($replyAll && $email['cc']) {
            $this->cc = $email['cc'];
            $this->showCc = true;
        }
        
        // Quote original message
        $emailService = new EmailService();
        $originalBody = $emailService->decryptData($email['body']);
        
        $this->body = "\n\n--- Original Message ---\n";
        $this->body .= "From: " . ($email['sender_name'] ?? $email['sender_email']) . "\n";
        $this->body .= "Date: " . \Carbon\Carbon::parse($email['created_at'])->format('M j, Y \a\t g:i A') . "\n";
        $this->body .= "Subject: {$email['subject']}\n\n";
        $this->body .= $originalBody;
    }
    
    protected function loadForwardData($emailId)
    {
        $emailData = DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where('emails.id', $emailId)
            ->select('emails.*', 'senders.name as sender_name', 'senders.email as sender_email')
            ->first();
            
        if (!$emailData) return;
        
        $email = (array) $emailData;
        
        $this->subject = 'Fwd: ' . $email['subject'];
        
        // Include original message
        $emailService = new EmailService();
        $originalBody = $emailService->decryptData($email['body']);
        
        $this->body = "\n\n--- Forwarded Message ---\n";
        $this->body .= "From: " . ($email['sender_name'] ?? $email['sender_email']) . "\n";
        $this->body .= "To: {$email['recipient_email']}\n";
        $this->body .= "Date: " . \Carbon\Carbon::parse($email['created_at'])->format('M j, Y \a\t g:i A') . "\n";
        $this->body .= "Subject: {$email['subject']}\n\n";
        $this->body .= $originalBody;
    }
    
    public function loadDraft($draftId)
    {
        try {
            $draftService = new EmailDraftService();
            $draft = $draftService->getDraft($draftId, Auth::id());
            
            if ($draft) {
                $this->draftId = $draft['id'];
                $this->to = $draft['to'] ?? '';
                $this->cc = $draft['cc'] ?? '';
                $this->bcc = $draft['bcc'] ?? '';
                $this->subject = $draft['subject'] ?? '';
                $this->body = $draft['body'] ?? '';
                $this->priority = $draft['priority'] ?? 'normal';
                $this->requestReadReceipt = $draft['request_read_receipt'] ?? false;
                $this->requestDeliveryReceipt = $draft['request_delivery_receipt'] ?? false;
                $this->enableTracking = $draft['enable_tracking'] ?? false;
                $this->isScheduled = $draft['is_scheduled'] ?? false;
                $this->scheduledDate = $draft['scheduled_date'] ?? '';
                $this->scheduledTime = $draft['scheduled_time'] ?? '';
                
                $this->showCc = !empty($this->cc);
                $this->showBcc = !empty($this->bcc);
                
                session()->flash('message', 'Draft loaded successfully!');
            }
        } catch (Exception $e) {
            Log::error('[NEW_COMPOSE_PANE] Failed to load draft', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to load draft: ' . $e->getMessage());
        }
    }
    
    public function loadSignatures()
    {
        try {
            $signatureService = new EmailSignatureService();
            $this->signatures = $signatureService->getUserSignatures(Auth::id());
        } catch (Exception $e) {
            Log::error('[NEW_COMPOSE_PANE] Failed to load signatures', ['error' => $e->getMessage()]);
        }
    }
    
    public function loadDefaultSignature()
    {
        try {
            $signatureService = new EmailSignatureService();
            $signature = $signatureService->getDefaultSignature(Auth::id());
            if ($signature) {
                $this->selectedSignature = $signature['content'];
            }
        } catch (Exception $e) {
            Log::error('[NEW_COMPOSE_PANE] Failed to load default signature', ['error' => $e->getMessage()]);
        }
    }
    
    public function selectSignature($signatureId)
    {
        $signature = collect($this->signatures)->firstWhere('id', $signatureId);
        if ($signature) {
            $this->selectedSignature = $signature['content'];
        }
        $this->showSignatureSelector = false;
    }
    
    public function removeSignature()
    {
        $this->selectedSignature = '';
    }
    
    public function toggleCc()
    {
        $this->showCc = !$this->showCc;
        if (!$this->showCc) {
            $this->cc = '';
        }
    }
    
    public function toggleBcc()
    {
        $this->showBcc = !$this->showBcc;
        if (!$this->showBcc) {
            $this->bcc = '';
        }
    }
    
    public function toggleAdvancedOptions()
    {
        $this->showAdvancedOptions = !$this->showAdvancedOptions;
    }
    
    public function toggleMinimize()
    {
        $this->isMinimized = !$this->isMinimized;
    }
    
    public function toggleFullscreen()
    {
        $this->isFullscreen = !$this->isFullscreen;
    }
    
    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
            $this->validateAttachments();
        }
    }
    
    public function sendEmail()
    {
        Log::info('[NEW_COMPOSE_PANE] Send email button clicked', [
            'user_id' => Auth::id(),
            'to' => $this->to,
            'subject' => $this->subject,
            'is_scheduled' => $this->isScheduled
        ]);
        
        $this->isValidating = true;
        
        try {
            // Validate input
            $this->validate();
            $this->validateAttachments();
            
            if (!empty($this->attachmentErrors)) {
                throw new Exception('Attachment validation failed: ' . implode(', ', $this->attachmentErrors));
            }
            
            // Prepare email data
            $emailData = [
                'to' => $this->to,
                'cc' => $this->cc,
                'bcc' => $this->bcc,
                'subject' => $this->subject,
                'body' => $this->body . ($this->selectedSignature ? "\n\n" . $this->selectedSignature : ''),
                'attachments' => $this->attachments,
                'priority' => $this->priority,
                'request_read_receipt' => $this->requestReadReceipt,
                'request_delivery_receipt' => $this->requestDeliveryReceipt,
                'enable_tracking' => $this->enableTracking,
                'reply_to_id' => $this->replyToEmailId,
                'is_reply' => $this->isReply,
                'is_forward' => $this->isForward
            ];
            
            $emailService = new EmailService();
            
            if ($this->isScheduled && $this->scheduledDate && $this->scheduledTime) {
                // Schedule email
                $scheduledEmailService = new ScheduledEmailService();
                $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $this->scheduledDate . ' ' . $this->scheduledTime);
                $result = $scheduledEmailService->scheduleEmail($emailData, $scheduledAt, Auth::id());
                
                if ($result['success']) {
                    session()->flash('message', 'Email scheduled successfully for ' . $scheduledAt->format('M j, Y \a\t g:i A'));
                } else {
                    session()->flash('error', 'Failed to schedule email: ' . $result['message']);
                }
            } else {
                // Send immediately
                $result = $emailService->sendEmail($emailData, Auth::id());
                
                if ($result['success']) {
                    session()->flash('message', 'Email sent successfully!');
                    
                    // Delete draft if it exists
                    if ($this->draftId) {
                        $draftService = new EmailDraftService();
                        $draftService->deleteDraft($this->draftId, Auth::id());
                    }
                } else {
                    session()->flash('error', 'Failed to send email: ' . $result['message']);
                }
            }
            
        } catch (Exception $e) {
            Log::error('[NEW_COMPOSE_PANE] Email send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error sending email: ' . $e->getMessage());
        } finally {
            $this->isValidating = false;
        }
        
        if (session()->has('message')) {
            $this->resetCompose();
            $this->emit('emailSent');
        }
    }
    
    public function saveDraft()
    {
        try {
            $draftService = new EmailDraftService();
            $draftData = [
                'to' => $this->to,
                'cc' => $this->cc,
                'bcc' => $this->bcc,
                'subject' => $this->subject,
                'body' => $this->body,
                'attachments' => $this->attachments,
                'priority' => $this->priority,
                'request_read_receipt' => $this->requestReadReceipt,
                'request_delivery_receipt' => $this->requestDeliveryReceipt,
                'enable_tracking' => $this->enableTracking,
                'is_scheduled' => $this->isScheduled,
                'scheduled_date' => $this->scheduledDate,
                'scheduled_time' => $this->scheduledTime
            ];
            
            $this->draftId = $draftService->saveDraft($draftData, Auth::id());
            session()->flash('message', 'Draft saved successfully!');
            
        } catch (Exception $e) {
            Log::error('[NEW_COMPOSE_PANE] Draft save failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to save draft: ' . $e->getMessage());
        }
    }
    
    public function discardEmail()
    {
        // Delete draft if it exists
        if ($this->draftId) {
            try {
                $draftService = new EmailDraftService();
                $draftService->deleteDraft($this->draftId, Auth::id());
            } catch (Exception $e) {
                Log::error('[NEW_COMPOSE_PANE] Failed to delete draft', ['error' => $e->getMessage()]);
            }
        }
        
        $this->resetCompose();
        $this->emit('composeDiscarded');
    }
    
    protected function resetCompose()
    {
        $this->to = '';
        $this->cc = '';
        $this->bcc = '';
        $this->subject = '';
        $this->body = '';
        $this->attachments = [];
        $this->attachmentErrors = [];
        $this->showCc = false;
        $this->showBcc = false;
        $this->isScheduled = false;
        $this->scheduledDate = '';
        $this->scheduledTime = '';
        $this->showAdvancedOptions = false;
        $this->isMinimized = false;
        $this->isFullscreen = false;
        $this->replyToEmailId = null;
        $this->isReply = false;
        $this->isReplyAll = false;
        $this->isForward = false;
        $this->draftId = null;
        $this->priority = 'normal';
        $this->requestReadReceipt = false;
        $this->requestDeliveryReceipt = false;
        $this->enableTracking = false;
        $this->validationErrors = [];
        $this->isValidating = false;
        $this->loadDefaultSignature();
    }
    
    public function render()
    {
        return view('livewire.email.new-compose-pane');
    }
}
