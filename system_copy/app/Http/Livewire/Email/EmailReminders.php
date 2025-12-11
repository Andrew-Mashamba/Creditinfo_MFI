<?php

namespace App\Http\Livewire\Email;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailReminderService;
use Carbon\Carbon;

class EmailReminders extends Component
{
    public $reminders = [];
    public $upcomingReminders = [];
    public $overdueReminders = [];
    public $showCreateModal = false;
    public $showEditModal = false;
    public $selectedEmailId = null;
    public $editingReminder = null;
    
    // Form fields
    public $reminderType = 'follow_up';
    public $reminderNote = '';
    public $reminderDate = '';
    public $reminderTime = '';
    public $selectedSuggestedTime = null;
    
    // Snooze
    public $showSnoozeModal = false;
    public $snoozeReminderId = null;
    public $snoozeOptions = [];
    
    protected $reminderService;
    
    protected $rules = [
        'reminderType' => 'required|in:follow_up,reply_by,custom',
        'reminderDate' => 'required|date',
        'reminderTime' => 'required'
    ];
    
    protected $listeners = [
        'refreshReminders' => 'loadReminders',
        'createReminderForEmail' => 'showCreateForEmail'
    ];
    
    public function mount()
    {
        $this->reminderService = new EmailReminderService();
        $this->loadReminders();
        $this->reminderDate = now()->addDay()->format('Y-m-d');
        $this->reminderTime = '09:00';
    }
    
    public function loadReminders()
    {
        $this->reminders = $this->reminderService->getUserReminders(Auth::id());
        $this->upcomingReminders = $this->reminderService->getUpcomingReminders(Auth::id(), 24);
        $this->overdueReminders = $this->reminderService->getOverdueReminders(Auth::id());
    }
    
    public function showCreateForEmail($emailId)
    {
        $this->selectedEmailId = $emailId;
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function createReminder()
    {
        $this->validate();
        
        $remindAt = Carbon::parse($this->reminderDate . ' ' . $this->reminderTime);
        
        $result = $this->reminderService->createReminder(
            Auth::id(),
            $this->selectedEmailId,
            [
                'type' => $this->reminderType,
                'note' => $this->reminderNote,
                'remind_at' => $remindAt
            ]
        );
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showCreateModal = false;
            $this->resetForm();
            $this->loadReminders();
            $this->emit('reminderCreated');
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function editReminder($reminderId)
    {
        $reminder = collect($this->reminders)->firstWhere('id', $reminderId);
        if ($reminder) {
            $this->editingReminder = $reminder;
            $this->reminderType = $reminder->type;
            $this->reminderNote = $reminder->note;
            $this->reminderDate = Carbon::parse($reminder->remind_at)->format('Y-m-d');
            $this->reminderTime = Carbon::parse($reminder->remind_at)->format('H:i');
            $this->showEditModal = true;
        }
    }
    
    public function updateReminder()
    {
        $this->validate();
        
        $remindAt = Carbon::parse($this->reminderDate . ' ' . $this->reminderTime);
        
        $result = $this->reminderService->updateReminder(
            $this->editingReminder->id,
            Auth::id(),
            [
                'type' => $this->reminderType,
                'note' => $this->reminderNote,
                'remind_at' => $remindAt
            ]
        );
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showEditModal = false;
            $this->resetForm();
            $this->loadReminders();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function completeReminder($reminderId)
    {
        $result = $this->reminderService->completeReminder($reminderId, Auth::id());
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->loadReminders();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function showSnoozeOptions($reminderId)
    {
        $this->snoozeReminderId = $reminderId;
        $this->snoozeOptions = $this->reminderService->getSnoozeOptions();
        $this->showSnoozeModal = true;
    }
    
    public function snoozeReminder($snoozeUntil)
    {
        $result = $this->reminderService->snoozeReminder(
            $this->snoozeReminderId,
            Auth::id(),
            $snoozeUntil
        );
        
        if ($result['success']) {
            session()->flash('message', $result['message']);
            $this->showSnoozeModal = false;
            $this->loadReminders();
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function deleteReminder($reminderId)
    {
        if (confirm('Are you sure you want to delete this reminder?')) {
            $result = $this->reminderService->deleteReminder($reminderId, Auth::id());
            
            if ($result['success']) {
                session()->flash('message', $result['message']);
                $this->loadReminders();
            } else {
                session()->flash('error', $result['message']);
            }
        }
    }
    
    public function selectSuggestedTime($index)
    {
        $suggestedTimes = $this->reminderService->getSuggestedTimes();
        if (isset($suggestedTimes[$index])) {
            $time = $suggestedTimes[$index]['value'];
            $this->reminderDate = $time->format('Y-m-d');
            $this->reminderTime = $time->format('H:i');
            $this->selectedSuggestedTime = $index;
        }
    }
    
    protected function resetForm()
    {
        $this->reminderType = 'follow_up';
        $this->reminderNote = '';
        $this->reminderDate = now()->addDay()->format('Y-m-d');
        $this->reminderTime = '09:00';
        $this->selectedSuggestedTime = null;
        $this->editingReminder = null;
    }
    
    public function getReminderIcon($type)
    {
        return match($type) {
            'follow_up' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'reply_by' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            default => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
        };
    }
    
    public function getReminderColor($reminder)
    {
        $remindAt = Carbon::parse($reminder->remind_at);
        $now = Carbon::now();
        
        if ($reminder->is_completed) {
            return 'text-gray-400';
        } elseif ($remindAt->isPast()) {
            return 'text-red-600';
        } elseif ($remindAt->diffInHours($now) <= 24) {
            return 'text-yellow-600';
        } else {
            return 'text-blue-600';
        }
    }
    
    public function render()
    {
        return view('livewire.email.email-reminders', [
            'suggestedTimes' => $this->reminderService->getSuggestedTimes()
        ]);
    }
}