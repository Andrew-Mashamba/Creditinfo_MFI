<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use App\Services\EmailService;
use App\Services\EmailSnoozeService;
use App\Services\ScheduledEmailService;
use App\Services\UndoSendService;
use App\Services\EmailThreadingService;
use App\Services\EmailRulesService;
use App\Services\FocusedInboxService;
use App\Services\EmailAttachmentService;
use App\Services\EmailSignatureService;
use App\Services\EmailTrackingService;
use App\Services\EmailLabelService;
use App\Services\SearchFolderService;
use Livewire\WithFileUploads;

class Email extends Component
{
    use WithPagination, WithFileUploads;

    public $selectedMenuItem = 1; // Default to Inbox
    public $loading = false;
    public $search = '';
    public $currentEmailId = null;
    public $showComposeModal = false;
    public $showComposePane = false;
    public $showAdvancedSearch = false;
    
    // Advanced search filters
    public $searchFrom = '';
    public $searchTo = '';
    public $searchDateFrom = '';
    public $searchDateTo = '';
    public $searchHasAttachment = false;
    
    // Email stats
    public $unreadCount = 0;
    public $totalEmails = 0;
    public $storageUsed = 0;
    public $storageLimit = 15728640; // 15MB in bytes

    // Compose email fields
    public $to = '';
    public $cc = '';
    public $bcc = '';
    public $subject = '';
    public $body = '';
    public $isDraft = false;
    
    // Attachments
    public $attachments = [];
    public $uploadedAttachments = [];
    public $attachmentErrors = [];
    
    // Smart Compose - AI service removed
    
    // Snooze
    public $showSnoozeModal = false;
    public $snoozeEmailId = null;
    public $snoozeOptions = [];
    public $customSnoozeDate = '';
    public $customSnoozeTime = '';
    
    // Schedule Send
    public $showScheduleModal = false;
    public $scheduleOptions = [];
    public $customScheduleDate = '';
    public $customScheduleTime = '';
    public $scheduledEmails = [];
    
    // Undo Send
    public $showUndoNotification = false;
    public $undoEmailId = null;
    public $undoSecondsRemaining = 0;
    
    // Conversation View
    public $conversationView = true;
    public $currentConversationId = null;
    public $expandedConversations = [];
    
    // Focused Inbox
    public $focusedInboxEnabled = true;
    public $focusedTab = 'focused'; // 'focused' or 'other'
    public $focusedStats = [];

    // Signature
    public $selectedSignatureId = null;
    public $signatures = [];
    public $showSignatureDropdown = false;
    
    // Tracking
    public $enableTracking = false;
    public $trackOpens = true;
    public $trackClicks = true;
    
    // Sweep/Bulk Actions
    public $selectedEmails = [];
    public $selectAll = false;
    public $showBulkActions = false;
    
    // Read/Delivery Receipts
    public $requestReadReceipt = false;
    public $requestDeliveryReceipt = false;
    
    // Search Folders
    public $searchFolders = [];
    public $currentSearchFolderId = null;
    public $showSearchFolderModal = false;
    public $searchFolderName = '';
    public $searchFolderDescription = '';
    public $searchFolderCriteria = [];
    
    protected $listeners = [
        'refreshComponent' => '$refresh',
        'emailSent' => 'handleEmailSent',
        'draftSaved' => 'handleDraftSaved',
        'templateSelected' => 'applyTemplate',
        'signatureSelected' => 'applySignature'
    ];

    public function mount()
    {
        $this->loadEmailStats();
        $this->loadFocusedInboxStats();
        $this->loadSearchFolders();
    }

    public function loadEmailStats()
    {
        $userId = Auth::id();
        
        // Get unread count
        $this->unreadCount = DB::table('emails')
            ->where('recipient_id', $userId)
            ->where('is_read', false)
            ->where('folder', 'inbox')
            ->count();
        
        // Get total emails
        $this->totalEmails = DB::table('emails')
            ->where(function($query) use ($userId) {
                $query->where('recipient_id', $userId)
                      ->orWhere('sender_id', $userId);
            })
            ->count();
        
        // Calculate storage used (simplified - count chars * average bytes)
        $this->storageUsed = DB::table('emails')
            ->where(function($query) use ($userId) {
                $query->where('recipient_id', $userId)
                      ->orWhere('sender_id', $userId);
            })
            ->sum(DB::raw('LENGTH(body) + LENGTH(subject)'));
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
        $this->currentEmailId = null;
        $this->resetPage();
    }

    public function openComposeModal()
    {
        // Check if using Outlook-style interface
        if (session('email_interface') === 'outlook') {
            $this->showComposePane = true;
            $this->currentEmailId = null; // Hide reading pane
        } else {
            $this->showComposeModal = true;
        }
        $this->resetComposeFields();
        $this->loadSignatures();
    }
    
    public function switchToOutlookInterface()
    {
        session(['email_interface' => 'outlook']);
        return redirect()->to('/email-outlook');
    }
    
    public function switchToModalInterface()
    {
        session()->forget('email_interface');
        $this->showComposePane = false;
        $this->showComposeModal = false;
    }

    public function closeComposeModal()
    {
        $this->showComposeModal = false;
        $this->resetComposeFields();
    }

    public function resetComposeFields()
    {
        $this->to = '';
        $this->cc = '';
        $this->bcc = '';
        $this->subject = '';
        $this->body = '';
        $this->isDraft = false;
        $this->attachments = [];
        $this->uploadedAttachments = [];
        $this->attachmentErrors = [];
    }

    public function saveDraft()
    {
        // More flexible validation for drafts
        $this->validate([
            'to' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:500',
            'body' => 'nullable|string'
        ], [
            'to.string' => 'Recipient email must be a valid email address.',
            'to.max' => 'Recipient email cannot exceed 255 characters.',
            'subject.string' => 'Subject must be text.',
            'subject.max' => 'Subject cannot exceed 500 characters.',
            'body.string' => 'Email body must be text.',
        ]);
        
        // Optional email format validation for drafts
        if (!empty($this->to) && !filter_var(trim($this->to), FILTER_VALIDATE_EMAIL)) {
            $this->addError('to', 'Please enter a valid email address for the recipient.');
            return;
        }

        $emailId = DB::table('emails')->insertGetId([
            'sender_id' => Auth::id(),
            'recipient_email' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'subject' => $this->subject ?: '(No Subject)',
            'body' => $this->body,
            'folder' => 'drafts',
            'is_draft' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Process attachments for draft
        if (!empty($this->uploadedAttachments)) {
            try {
                $this->processAttachments($emailId);
            } catch (\Exception $e) {
                Log::error('Failed to attach files to draft: ' . $e->getMessage());
            }
        }

        $this->emit('draftSaved');
        session()->flash('message', 'Draft saved successfully');
        $this->closeComposeModal();
        $this->loadEmailStats();
    }

    public function sendEmail($scheduled = false, $scheduledAt = null)
    {
        // More flexible validation rules to prevent false positives
        $this->validate([
            'to' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string|min:1'
        ], [
            'to.required' => 'Recipient email is required.',
            'to.string' => 'Recipient email must be a valid email address.',
            'to.max' => 'Recipient email cannot exceed 255 characters.',
            'subject.required' => 'Email subject is required.',
            'subject.string' => 'Subject must be text.',
            'subject.max' => 'Subject cannot exceed 500 characters.',
            'body.required' => 'Email body is required.',
            'body.string' => 'Email body must be text.',
            'body.min' => 'Email body cannot be empty.',
        ]);
        
        // Additional email format validation
        if (!empty($this->to) && !filter_var(trim($this->to), FILTER_VALIDATE_EMAIL)) {
            $this->addError('to', 'Please enter a valid email address for the recipient.');
            return;
        }

        try {
            // Apply signature if selected and not already applied
            if ($this->selectedSignatureId && !str_contains($this->body, "\n\n--\n")) {
                $signatureService = new EmailSignatureService();
                $variables = [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'title' => 'Member',
                    'company' => 'SACCOS',
                    'phone' => '+1 234 567 8900'
                ];
                
                $this->body = $signatureService->applySignature(
                    $this->body,
                    $this->selectedSignatureId,
                    Auth::id(),
                    $variables
                );
            }
            
            $emailData = [
                'to' => $this->to,
                'cc' => $this->cc,
                'bcc' => $this->bcc,
                'subject' => $this->subject,
                'body' => $this->body,
                'request_read_receipt' => $this->requestReadReceipt,
                'request_delivery_receipt' => $this->requestDeliveryReceipt
            ];

            if ($scheduled && $scheduledAt) {
                // Schedule the email
                $scheduledEmailService = new ScheduledEmailService();
                $emailData['scheduled_at'] = $scheduledAt;
                $result = $scheduledEmailService->scheduleEmail($emailData);
                
                if ($result['success']) {
                    $this->closeComposeModal();
                    $this->closeScheduleModal();
                    $this->emit('emailScheduled');
                    session()->flash('message', "Email scheduled to be sent on {$result['scheduled_at']}");
                    $this->loadScheduledEmails();
                } else {
                    session()->flash('error', $result['message']);
                }
            } else {
                // Send immediately
                $emailService = new EmailService();
                $result = $emailService->sendEmail($emailData);

                if ($result['success']) {
                    // Process attachments
                    if (!empty($this->uploadedAttachments)) {
                        try {
                            $this->processAttachments($result['email_id']);
                        } catch (\Exception $e) {
                            Log::error('Failed to attach files: ' . $e->getMessage());
                        }
                    }
                    
                    // Enable tracking if requested
                    if ($this->enableTracking) {
                        $trackingService = new EmailTrackingService();
                        $trackingResult = $trackingService->enableTracking(
                            $result['email_id'],
                            $this->trackOpens,
                            $this->trackClicks
                        );
                        
                        if ($trackingResult['success']) {
                            // Update email body with tracking
                            $email = DB::table('emails')->where('id', $result['email_id'])->first();
                            $trackedBody = $email->body;
                            
                            if ($this->trackOpens) {
                                $trackedBody = $trackingService->addTrackingPixel($trackedBody, $trackingResult['pixel_id']);
                            }
                            
                            if ($this->trackClicks) {
                                $trackedBody = $trackingService->wrapLinksForTracking($trackedBody, $trackingResult['tracking_id']);
                            }
                            
                            // Update email with tracked content
                            DB::table('emails')
                                ->where('id', $result['email_id'])
                                ->update(['body' => $trackedBody]);
                        }
                    }
                    
                    $this->emit('emailSent');
                    $this->closeComposeModal();
                    $this->loadEmailStats();
                    
                    // Check if undo is available
                    if (isset($result['undo_until'])) {
                        $this->showUndoNotification = true;
                        $this->undoEmailId = $result['email_id'];
                        $this->undoSecondsRemaining = $result['undo_seconds'];
                        session()->flash('message', $result['message']);
                        
                        // Start countdown
                        $this->emit('startUndoCountdown', $result['undo_seconds']);
                    } else {
                        session()->flash('message', $result['message']);
                    }
                } else {
                    session()->flash('error', $result['message']);
                }
            }
            
            // Apply rules to sent email if it's in inbox (for testing)
            if ($result['success'] && !$scheduled) {
                $this->applyRulesToEmail($result['email_id']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to process email: ' . $e->getMessage());
        }
    }
    
    protected function applyRulesToEmail($emailId)
    {
        try {
            // Apply rules
            $rulesService = new EmailRulesService();
            $rulesService->applyRulesToEmail($emailId, Auth::id());
            
            // Process for focused inbox
            $focusedService = new FocusedInboxService();
            $focusedService->processEmailForFocusedInbox($emailId, Auth::id());
            
            // Reload stats if in inbox
            if ($this->selectedMenuItem == 1) {
                $this->loadFocusedInboxStats();
            }
        } catch (\Exception $e) {
            Log::error('Failed to apply rules to email: ' . $e->getMessage());
        }
    }

    public function markAsRead($emailId)
    {
        DB::table('emails')
            ->where('id', $emailId)
            ->where('recipient_id', Auth::id())
            ->update(['is_read' => true]);
        
        $this->loadEmailStats();
    }

    public function markAsUnread($emailId)
    {
        DB::table('emails')
            ->where('id', $emailId)
            ->where('recipient_id', Auth::id())
            ->update(['is_read' => false]);
        
        $this->loadEmailStats();
    }

    public function deleteEmail($emailId)
    {
        $email = DB::table('emails')->where('id', $emailId)->first();
        
        if ($email->folder == 'trash') {
            // Permanently delete
            DB::table('emails')->where('id', $emailId)->delete();
        } else {
            // Move to trash
            DB::table('emails')
                ->where('id', $emailId)
                ->update(['folder' => 'trash', 'deleted_at' => now()]);
        }
        
        $this->loadEmailStats();
    }

    public function moveToSpam($emailId)
    {
        DB::table('emails')
            ->where('id', $emailId)
            ->update(['folder' => 'spam']);
        
        $this->loadEmailStats();
    }

    public function resetSearchFilters()
    {
        $this->search = '';
        $this->searchFrom = '';
        $this->searchTo = '';
        $this->searchDateFrom = '';
        $this->searchDateTo = '';
        $this->searchHasAttachment = false;
        $this->resetPage();
    }

    public function getEmailsProperty()
    {
        // Handle search folders
        if ($this->currentSearchFolderId) {
            $searchFolderService = new SearchFolderService();
            return $searchFolderService->getSearchFolderEmails(
                $this->currentSearchFolderId,
                Auth::id(),
                $this->page
            );
        }
        
        // Handle snoozed emails separately
        if ($this->selectedMenuItem == 6) {
            return $this->snoozedEmails;
        }
        
        // Handle scheduled emails separately
        if ($this->selectedMenuItem == 7) {
            $this->loadScheduledEmails();
            return $this->scheduledEmails;
        }
        
        $folder = '';
        switch($this->selectedMenuItem) {
            case 1: $folder = 'inbox'; break;
            case 2: $folder = 'sent'; break;
            case 3: $folder = 'drafts'; break;
            case 4: $folder = 'spam'; break;
            case 5: $folder = 'trash'; break;
            default: $folder = 'inbox';
        }
        
        // Handle Focused Inbox for inbox folder
        if ($folder === 'inbox' && $this->focusedInboxEnabled) {
            $focusedService = new FocusedInboxService();
            
            if ($this->focusedTab === 'focused') {
                return $focusedService->getFocusedEmails(Auth::id(), $this->page);
            } else {
                return $focusedService->getOtherEmails(Auth::id(), $this->page);
            }
        }
        
        // If conversation view is enabled
        if ($this->conversationView && in_array($folder, ['inbox', 'sent'])) {
            $threadingService = new EmailThreadingService();
            $conversations = $threadingService->getConversations(
                Auth::id(), 
                $folder, 
                $this->page
            );
            
            // Convert to paginated format for compatibility
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect($conversations['conversations']),
                $conversations['total'],
                $conversations['per_page'],
                $conversations['current_page'],
                ['path' => request()->url()]
            );
        }
        
        // Regular email view
        return DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->select('emails.*', 'senders.name as sender_name', 'senders.email as sender_email')
            ->where('folder', $folder)
            ->where(function($query) {
                $query->where('recipient_id', Auth::id())
                      ->orWhere('sender_id', Auth::id());
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('subject', 'like', '%'.$this->search.'%')
                      ->orWhere('body', 'like', '%'.$this->search.'%')
                      ->orWhere('recipient_email', 'like', '%'.$this->search.'%')
                      ->orWhere('senders.name', 'like', '%'.$this->search.'%')
                      ->orWhere('senders.email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->searchFrom, function($query) {
                $query->where(function($q) {
                    $q->where('senders.email', 'like', '%'.$this->searchFrom.'%')
                      ->orWhere('senders.name', 'like', '%'.$this->searchFrom.'%');
                });
            })
            ->when($this->searchTo, function($query) {
                $query->where('recipient_email', 'like', '%'.$this->searchTo.'%');
            })
            ->when($this->searchDateFrom, function($query) {
                $query->whereDate('emails.created_at', '>=', $this->searchDateFrom);
            })
            ->when($this->searchDateTo, function($query) {
                $query->whereDate('emails.created_at', '<=', $this->searchDateTo);
            })
            ->when($this->searchHasAttachment, function($query) {
                $query->where('has_attachments', true);
            })
            ->orderByRaw('is_pinned DESC, created_at DESC')
            ->paginate(10);
    }
    
    // Smart Compose Methods - AI service removed
    public function updatedBody($value)
    {
        // AI service removed - no smart compose functionality
    }
    
    // Snooze Methods
    public function openSnoozeModal($emailId)
    {
        $this->snoozeEmailId = $emailId;
        $snoozeService = new EmailSnoozeService();
        $this->snoozeOptions = $snoozeService->getSnoozeOptions();
        $this->showSnoozeModal = true;
    }
    
    public function closeSnoozeModal()
    {
        $this->showSnoozeModal = false;
        $this->snoozeEmailId = null;
        $this->customSnoozeDate = '';
        $this->customSnoozeTime = '';
    }
    
    public function snoozeEmail($snoozeUntil = null)
    {
        if (!$this->snoozeEmailId) {
            return;
        }
        
        // If custom date/time provided
        if (!$snoozeUntil && $this->customSnoozeDate && $this->customSnoozeTime) {
            $snoozeUntil = $this->customSnoozeDate . ' ' . $this->customSnoozeTime . ':00';
        }
        
        if (!$snoozeUntil) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Please select a snooze time'
            ]);
            return;
        }
        
        $snoozeService = new EmailSnoozeService();
        $result = $snoozeService->snoozeEmail($this->snoozeEmailId, Auth::id(), $snoozeUntil);
        
        if ($result['success']) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Email snoozed successfully'
            ]);
            $this->closeSnoozeModal();
            $this->loadEmailStats();
        } else {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => $result['message']
            ]);
        }
    }
    
    public function getSnoozedEmailsProperty()
    {
        if ($this->selectedMenuItem == 6) { // Assuming 6 is for snoozed
            $snoozeService = new EmailSnoozeService();
            return $snoozeService->getSnoozedEmails(Auth::id());
        }
        return collect();
    }
    
    public function cancelSnooze($snoozeId)
    {
        $snoozeService = new EmailSnoozeService();
        $result = $snoozeService->cancelSnooze($snoozeId, Auth::id());
        
        if ($result['success']) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Email unsnoozed successfully'
            ]);
            $this->loadEmailStats();
        }
    }
    
    // Schedule Send Methods
    public function openScheduleModal()
    {
        $scheduledEmailService = new ScheduledEmailService();
        $this->scheduleOptions = $scheduledEmailService->getScheduleSuggestions();
        $this->showScheduleModal = true;
    }
    
    public function closeScheduleModal()
    {
        $this->showScheduleModal = false;
        $this->customScheduleDate = '';
        $this->customScheduleTime = '';
    }
    
    public function scheduleEmail($scheduledAt = null)
    {
        // If custom date/time provided
        if (!$scheduledAt && $this->customScheduleDate && $this->customScheduleTime) {
            $scheduledAt = $this->customScheduleDate . ' ' . $this->customScheduleTime . ':00';
        }
        
        if (!$scheduledAt) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Please select a schedule time'
            ]);
            return;
        }
        
        // Send email with schedule
        $this->sendEmail(true, $scheduledAt);
    }
    
    public function loadScheduledEmails()
    {
        $scheduledEmailService = new ScheduledEmailService();
        $this->scheduledEmails = $scheduledEmailService->getScheduledEmails(Auth::id());
    }
    
    public function cancelScheduledEmail($scheduledEmailId)
    {
        $scheduledEmailService = new ScheduledEmailService();
        $result = $scheduledEmailService->cancelScheduledEmail($scheduledEmailId, Auth::id());
        
        if ($result['success']) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Scheduled email cancelled successfully'
            ]);
            $this->loadScheduledEmails();
        }
    }
    
    public function getScheduledEmailsProperty()
    {
        if ($this->selectedMenuItem == 7) { // Assuming 7 is for scheduled
            return $this->scheduledEmails;
        }
        return collect();
    }
    
    // Undo Send Methods
    public function undoSend()
    {
        if (!$this->undoEmailId) {
            return;
        }
        
        $undoService = new UndoSendService();
        $result = $undoService->undoSend($this->undoEmailId, Auth::id());
        
        if ($result['success']) {
            $this->showUndoNotification = false;
            $this->undoEmailId = null;
            $this->undoSecondsRemaining = 0;
            
            session()->flash('message', 'Email moved back to drafts');
            $this->loadEmailStats();
            
            // Open compose modal with the email
            $email = DB::table('emails')->where('id', $this->undoEmailId)->first();
            if ($email) {
                $this->to = $email->recipient_email;
                $this->cc = $email->cc;
                $this->bcc = $email->bcc;
                $this->subject = $email->subject;
                $this->body = app(EmailService::class)->decryptData($email->body);
                $this->showComposeModal = true;
            }
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function dismissUndo()
    {
        $this->showUndoNotification = false;
        $this->undoEmailId = null;
        $this->undoSecondsRemaining = 0;
    }
    
    public function updateUndoCountdown($seconds)
    {
        $this->undoSecondsRemaining = $seconds;
        
        if ($seconds <= 0) {
            $this->dismissUndo();
        }
    }
    
    // Pin/Flag Methods
    public function togglePin($emailId)
    {
        $email = DB::table('emails')->where('id', $emailId)->first();
        
        if ($email) {
            $isPinned = !$email->is_pinned;
            
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'is_pinned' => $isPinned,
                    'pinned_at' => $isPinned ? now() : null,
                    'updated_at' => now()
                ]);
            
            $message = $isPinned ? 'Email pinned' : 'Email unpinned';
            session()->flash('message', $message);
        }
    }
    
    public function toggleFlag($emailId)
    {
        $email = DB::table('emails')->where('id', $emailId)->first();
        
        if ($email) {
            $isFlagged = !$email->is_flagged;
            
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'is_flagged' => $isFlagged,
                    'flagged_at' => $isFlagged ? now() : null,
                    'updated_at' => now()
                ]);
            
            $message = $isFlagged ? 'Email flagged' : 'Email unflagged';
            session()->flash('message', $message);
        }
    }
    
    // Conversation View Methods
    public function toggleConversationView()
    {
        $this->conversationView = !$this->conversationView;
        $this->resetPage();
    }
    
    public function toggleConversation($conversationId)
    {
        if (in_array($conversationId, $this->expandedConversations)) {
            $this->expandedConversations = array_diff($this->expandedConversations, [$conversationId]);
        } else {
            $this->expandedConversations[] = $conversationId;
        }
    }
    
    public function openConversation($conversationId)
    {
        $this->currentConversationId = $conversationId;
        
        // Mark conversation as read
        $threadingService = new EmailThreadingService();
        $threadingService->markConversationAsRead($conversationId, Auth::id());
        
        $this->loadEmailStats();
    }
    
    public function deleteConversation($conversationId)
    {
        $threadingService = new EmailThreadingService();
        $result = $threadingService->deleteConversation($conversationId, Auth::id());
        
        if ($result) {
            session()->flash('message', 'Conversation moved to trash');
            $this->loadEmailStats();
        }
    }
    
    // Focused Inbox Methods
    public function loadFocusedInboxStats()
    {
        if ($this->selectedMenuItem == 1) { // Inbox
            $focusedService = new FocusedInboxService();
            $this->focusedStats = $focusedService->getFocusedInboxStats(Auth::id());
        }
    }
    
    public function toggleFocusedInbox()
    {
        $this->focusedInboxEnabled = !$this->focusedInboxEnabled;
        $this->resetPage();
    }
    
    public function switchFocusedTab($tab)
    {
        $this->focusedTab = $tab;
        $this->resetPage();
    }
    
    public function toggleEmailFocus($emailId)
    {
        $focusedService = new FocusedInboxService();
        if ($focusedService->toggleFocusedStatus($emailId, Auth::id())) {
            $this->loadFocusedInboxStats();
            session()->flash('message', 'Email focus status updated');
        }
    }
    
    public function processAllForFocusedInbox()
    {
        $focusedService = new FocusedInboxService();
        $processed = $focusedService->processUserEmails(Auth::id());
        
        session()->flash('message', "$processed emails processed for focused inbox");
        $this->loadFocusedInboxStats();
    }
    
    public function retrainFocusedInbox()
    {
        if (confirm('This will reset and retrain your focused inbox. Continue?')) {
            $focusedService = new FocusedInboxService();
            $processed = $focusedService->retrainFocusedInbox(Auth::id());
            
            session()->flash('message', "Focused inbox retrained with $processed emails");
            $this->loadFocusedInboxStats();
        }
    }
    
    // Attachment Methods
    public function updatedAttachments()
    {
        $this->attachmentErrors = [];
        
        foreach ($this->attachments as $attachment) {
            try {
                // Basic validation
                if ($attachment->getSize() > 26214400) { // 25MB
                    $this->attachmentErrors[] = $attachment->getClientOriginalName() . ' exceeds 25MB limit';
                    continue;
                }
                
                // Add to uploaded list temporarily
                $this->uploadedAttachments[] = [
                    'name' => $attachment->getClientOriginalName(),
                    'size' => $attachment->getSize(),
                    'type' => $attachment->getMimeType(),
                    'file' => $attachment
                ];
            } catch (\Exception $e) {
                $this->attachmentErrors[] = 'Error processing ' . $attachment->getClientOriginalName();
            }
        }
    }
    
    public function removeAttachment($index)
    {
        if (isset($this->uploadedAttachments[$index])) {
            unset($this->uploadedAttachments[$index]);
            $this->uploadedAttachments = array_values($this->uploadedAttachments);
        }
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
    
    // Search Folder Methods
    public function loadSearchFolders()
    {
        $searchFolderService = new SearchFolderService();
        $this->searchFolders = $searchFolderService->getUserSearchFolders(Auth::id());
    }
    
    public function selectSearchFolder($folderId)
    {
        $this->currentSearchFolderId = $folderId;
        $this->selectedMenuItem = null; // Clear regular folder selection
        $this->resetPage();
    }
    
    public function openSearchFolderModal()
    {
        $this->showSearchFolderModal = true;
        $this->searchFolderName = '';
        $this->searchFolderDescription = '';
        $this->searchFolderCriteria = [
            'is_unread' => false,
            'is_flagged' => false,
            'has_attachments' => false,
            'date_range' => '',
            'search_text' => '',
            'from_email' => '',
            'folders' => []
        ];
    }
    
    public function closeSearchFolderModal()
    {
        $this->showSearchFolderModal = false;
    }
    
    public function createSearchFolder()
    {
        $this->validate([
            'searchFolderName' => 'required|max:255',
            'searchFolderDescription' => 'nullable|max:500'
        ]);
        
        // Build criteria from form inputs
        $criteria = [];
        
        if ($this->searchFolderCriteria['is_unread']) {
            $criteria['is_read'] = false;
        }
        
        if ($this->searchFolderCriteria['is_flagged']) {
            $criteria['is_flagged'] = true;
        }
        
        if ($this->searchFolderCriteria['has_attachments']) {
            $criteria['has_attachments'] = true;
        }
        
        if (!empty($this->searchFolderCriteria['date_range'])) {
            $criteria['date_range'] = $this->searchFolderCriteria['date_range'];
        }
        
        if (!empty($this->searchFolderCriteria['search_text'])) {
            $criteria['search_text'] = $this->searchFolderCriteria['search_text'];
        }
        
        if (!empty($this->searchFolderCriteria['from_email'])) {
            $criteria['from_email'] = $this->searchFolderCriteria['from_email'];
        }
        
        if (!empty($this->searchFolderCriteria['folders'])) {
            $criteria['folders'] = $this->searchFolderCriteria['folders'];
        }
        
        $searchFolderService = new SearchFolderService();
        $result = $searchFolderService->createSearchFolder(Auth::id(), [
            'name' => $this->searchFolderName,
            'description' => $this->searchFolderDescription,
            'search_criteria' => $criteria
        ]);
        
        if ($result['success']) {
            session()->flash('message', 'Search folder created successfully');
            $this->closeSearchFolderModal();
            $this->loadSearchFolders();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function deleteSearchFolder($folderId)
    {
        if (confirm('Are you sure you want to delete this search folder?')) {
            $searchFolderService = new SearchFolderService();
            $result = $searchFolderService->deleteSearchFolder($folderId, Auth::id());
            
            if ($result['success']) {
                session()->flash('message', 'Search folder deleted successfully');
                if ($this->currentSearchFolderId == $folderId) {
                    $this->currentSearchFolderId = null;
                    $this->selectedMenuItem = 1; // Go back to inbox
                }
                $this->loadSearchFolders();
            }
        }
    }
    
    protected function processAttachments($emailId)
    {
        if (empty($this->uploadedAttachments)) {
            return;
        }
        
        try {
            $attachmentService = new EmailAttachmentService();
            $files = array_column($this->uploadedAttachments, 'file');
            $attachmentService->handleUploads($emailId, $files);
        } catch (\Exception $e) {
            Log::error('Failed to process attachments: ' . $e->getMessage());
            throw $e;
        }
    }

    public function applyTemplate($templateData)
    {
        if (isset($templateData['subject'])) {
            $this->subject = $templateData['subject'];
        }
        
        if (isset($templateData['body'])) {
            $this->body = $templateData['body'];
        }
        
        // Show the compose modal if it's not already open
        $this->showComposeModal = true;
        
        session()->flash('message', 'Template applied successfully');
    }
    
    public function loadSignatures()
    {
        $signatureService = new EmailSignatureService();
        $this->signatures = $signatureService->getUserSignatures(Auth::id());
        
        // Set default signature if available
        $defaultSignature = $signatureService->getDefaultSignature(Auth::id());
        if ($defaultSignature) {
            $this->selectedSignatureId = $defaultSignature->id;
        }
    }
    
    public function applySignature($signatureData)
    {
        if (isset($signatureData['id'])) {
            $this->selectedSignatureId = $signatureData['id'];
            
            // Apply signature to body if needed
            if (isset($signatureData['content'])) {
                $signatureService = new EmailSignatureService();
                $variables = [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'title' => 'Member',
                    'company' => 'SACCOS',
                    'phone' => '+1 234 567 8900'
                ];
                
                // Remove existing signature if any (look for --\n pattern)
                $bodyParts = explode("\n\n--\n", $this->body);
                $this->body = $bodyParts[0];
                
                // Apply new signature
                $this->body = $signatureService->applySignature(
                    $this->body,
                    $this->selectedSignatureId,
                    Auth::id(),
                    $variables
                );
            }
        }
        
        session()->flash('message', 'Signature applied successfully');
    }

    // Bulk/Sweep Actions
    public function toggleEmailSelection($emailId)
    {
        if (in_array($emailId, $this->selectedEmails)) {
            $this->selectedEmails = array_diff($this->selectedEmails, [$emailId]);
        } else {
            $this->selectedEmails[] = $emailId;
        }
        
        $this->showBulkActions = count($this->selectedEmails) > 0;
    }
    
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Select all visible emails
            $emailIds = $this->emails->pluck('id')->toArray();
            $this->selectedEmails = array_unique(array_merge($this->selectedEmails, $emailIds));
        } else {
            // Deselect all visible emails
            $emailIds = $this->emails->pluck('id')->toArray();
            $this->selectedEmails = array_diff($this->selectedEmails, $emailIds);
        }
        
        $this->showBulkActions = count($this->selectedEmails) > 0;
    }
    
    public function bulkMarkAsRead()
    {
        DB::table('emails')
            ->whereIn('id', $this->selectedEmails)
            ->where('recipient_id', Auth::id())
            ->update(['is_read' => true]);
            
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
        $this->loadEmailStats();
        session()->flash('message', 'Selected emails marked as read');
    }
    
    public function bulkMarkAsUnread()
    {
        DB::table('emails')
            ->whereIn('id', $this->selectedEmails)
            ->where('recipient_id', Auth::id())
            ->update(['is_read' => false]);
            
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
        $this->loadEmailStats();
        session()->flash('message', 'Selected emails marked as unread');
    }
    
    public function bulkDelete()
    {
        $folder = $this->getSelectedFolder();
        
        if ($folder == 'trash') {
            // Permanently delete
            DB::table('emails')
                ->whereIn('id', $this->selectedEmails)
                ->delete();
        } else {
            // Move to trash
            DB::table('emails')
                ->whereIn('id', $this->selectedEmails)
                ->update(['folder' => 'trash', 'deleted_at' => now()]);
        }
        
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
        $this->loadEmailStats();
        session()->flash('message', 'Selected emails deleted');
    }
    
    public function bulkMoveToFolder($folder)
    {
        DB::table('emails')
            ->whereIn('id', $this->selectedEmails)
            ->update(['folder' => $folder]);
            
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
        $this->loadEmailStats();
        session()->flash('message', 'Selected emails moved to ' . ucfirst($folder));
    }
    
    public function bulkApplyLabel($labelId)
    {
        $labelService = new EmailLabelService();
        
        foreach ($this->selectedEmails as $emailId) {
            $labelService->applyLabel($emailId, $labelId, Auth::id());
        }
        
        $this->selectedEmails = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
        session()->flash('message', 'Label applied to selected emails');
    }
    
    public function sweepFromSender($emailId)
    {
        // Get the sender email
        $email = DB::table('emails')->where('id', $emailId)->first();
        if (!$email) return;
        
        // Delete all emails from this sender
        DB::table('emails')
            ->where('sender_email', $email->sender_email)
            ->where('recipient_id', Auth::id())
            ->update(['folder' => 'trash', 'deleted_at' => now()]);
            
        $this->loadEmailStats();
        session()->flash('message', 'All emails from ' . $email->sender_email . ' moved to trash');
    }
    
    public function sweepOlderThan($days)
    {
        $folder = $this->getSelectedFolder();
        
        DB::table('emails')
            ->where('folder', $folder)
            ->where('recipient_id', Auth::id())
            ->where('created_at', '<', now()->subDays($days))
            ->update(['folder' => 'trash', 'deleted_at' => now()]);
            
        $this->loadEmailStats();
        session()->flash('message', 'Emails older than ' . $days . ' days moved to trash');
    }

    // Outlook-style interface methods
    public function selectFolder($folderId)
    {
        $this->selectedMenuItem = $folderId;
        $this->currentEmailId = null;
        $this->showComposePane = false;
        $this->resetPage();
    }
    
    public function selectEmail($emailId)
    {
        $this->currentEmailId = $emailId;
        $this->showComposePane = false;
    }

    public function render()
    {
        // Check if we should use the new Outlook-style interface
        if (request()->get('outlook', false) || session('email_interface') === 'outlook') {
            session(['email_interface' => 'outlook']);
            return view('livewire.email.email-outlook', [
                'emails' => $this->emails
            ]);
        }
        
        return view('livewire.email.email', [
            'emails' => $this->emails
        ]);
    }
}
