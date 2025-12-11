<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
use App\Services\FocusedInboxService;
use App\Services\EmailThreadingService;

class EmailOutlook extends Component
{
    use WithPagination, WithFileUploads;

    public $selectedMenuItem = 1; // Default to Inbox
    public $loading = false;
    public $search = '';
    public $currentEmailId = null;
    public $showComposePane = false;
    
    // Email stats
    public $unreadCount = 0;
    public $totalEmails = 0;
    
    // Focused inbox
    public $focusedInboxEnabled = false;
    public $focusedTab = 'focused';
    public $focusedStats = [];
    
    // Conversation view
    public $conversationView = false;
    
    protected $listeners = [
        'emailRead' => 'loadEmailStats',
        'emailDeleted' => 'loadEmailStats',
        'emailSent' => 'handleEmailSent',
        'composeDiscarded' => 'closeCompose',
        'composeReply' => 'composeReply',
        'composeReplyAll' => 'composeReplyAll',
        'composeForward' => 'composeForward'
    ];
    
    public function mount()
    {
        $this->loadEmailStats();
        $this->loadFocusedInboxStats();
    }
    
    public function loadEmailStats()
    {
        $userId = Auth::id();
        
        $this->unreadCount = DB::table('emails')
            ->where('recipient_id', $userId)
            ->where('folder', 'inbox')
            ->where('is_read', false)
            ->count();
            
        $this->totalEmails = DB::table('emails')
            ->where(function($query) use ($userId) {
                $query->where('recipient_id', $userId)
                      ->orWhere('sender_id', $userId);
            })
            ->count();
    }
    
    public function loadFocusedInboxStats()
    {
        if ($this->focusedInboxEnabled) {
            $focusedService = new FocusedInboxService();
            $this->focusedStats = $focusedService->getFocusedInboxStats(Auth::id());
        }
    }
    
    public function getEmailsProperty()
    {
        $userId = Auth::id();
        
        // Determine folder based on selected menu item
        $folder = match($this->selectedMenuItem) {
            1 => 'inbox',
            2 => 'sent',
            3 => 'drafts',
            4 => 'trash',
            5 => 'junk',
            6 => 'snoozed',
            7 => 'scheduled',
            default => 'inbox'
        };
        
        // Base query
        $query = DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id');
        
        // Apply folder filter
        if ($this->selectedMenuItem == 1) {
            // Inbox - show received emails
            $query->where('emails.recipient_id', $userId)
                  ->where('emails.folder', 'inbox');
                  
            // Apply focused inbox filter if enabled
            if ($this->focusedInboxEnabled) {
                $focusedService = new FocusedInboxService();
                if ($this->focusedTab === 'focused') {
                    return $focusedService->getFocusedEmails(Auth::id(), $this->page);
                } else {
                    return $focusedService->getOtherEmails(Auth::id(), $this->page);
                }
            }
        } elseif (in_array($this->selectedMenuItem, [2, 3, 7])) {
            // Sent, Drafts, Scheduled - show sent emails
            $query->where('emails.sender_id', $userId)
                  ->where('emails.folder', $folder);
        } else {
            // Other folders
            $query->where('emails.recipient_id', $userId)
                  ->where('emails.folder', $folder);
        }
        
        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('emails.subject', 'like', '%' . $this->search . '%')
                  ->orWhere('emails.body', 'like', '%' . $this->search . '%')
                  ->orWhere('emails.sender_email', 'like', '%' . $this->search . '%')
                  ->orWhere('emails.recipient_email', 'like', '%' . $this->search . '%');
            });
        }
        
        // Select fields
        $query->select(
            'emails.*',
            'senders.name as sender_name',
            'senders.email as sender_email'
        );
        
        // Order by creation date
        $query->orderBy('emails.created_at', 'desc');
        
        return $query->paginate(20);
    }
    
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
    
    public function toggleFocusedInbox()
    {
        $this->focusedInboxEnabled = !$this->focusedInboxEnabled;
        $this->loadFocusedInboxStats();
        $this->resetPage();
    }
    
    public function switchFocusedTab($tab)
    {
        $this->focusedTab = $tab;
        $this->resetPage();
    }
    
    public function toggleConversationView()
    {
        $this->conversationView = !$this->conversationView;
        $this->resetPage();
    }
    
    public function closeCompose()
    {
        $this->showComposePane = false;
        $this->currentEmailId = null;
    }
    
    public function handleEmailSent()
    {
        $this->showComposePane = false;
        $this->currentEmailId = null;
        $this->loadEmailStats();
        session()->flash('message', 'Email sent successfully!');
    }
    
    public function composeReply($emailId)
    {
        $this->showComposePane = true;
        $this->currentEmailId = null; // Hide reading pane
        $this->emit('composeReply', $emailId);
    }
    
    public function composeReplyAll($emailId)
    {
        $this->showComposePane = true;
        $this->currentEmailId = null; // Hide reading pane
        $this->emit('composeReplyAll', $emailId);
    }
    
    public function composeForward($emailId)
    {
        $this->showComposePane = true;
        $this->currentEmailId = null; // Hide reading pane
        $this->emit('composeForward', $emailId);
    }
    
    public function render()
    {
        return view('livewire.email.email-outlook', [
            'emails' => $this->emails
        ]);
    }
}
