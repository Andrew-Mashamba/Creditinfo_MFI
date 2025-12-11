<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\approvals;

class ApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $approval;
    protected $user;

    public function __construct(approvals $approval, $user)
    {
        $this->approval = $approval;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $status = $this->approval->approval_status;
        $processName = $this->formatProcessName($this->approval->process_name);
        
        return (new MailMessage)
            ->subject("Approval Status Update: {$processName}")
            ->greeting("Hello {$this->user->name},")
            ->line("The {$processName} request has been {$status}.")
            ->line("Process Details: {$this->approval->process_description}")
            ->line("Status: {$status}")
            ->line("Processed by: " . auth()->user()->name)
            ->action('View Details', url('/approvals/' . $this->approval->id))
            ->line('Thank you for using our application!');
    }

    private function formatProcessName($processName)
    {
        // Convert camelCase to space-separated words
        $result = preg_replace('/(?<!^)([A-Z])/', ' $1', $processName);
        // Capitalize the first letter of each word
        return ucwords($result);
    }
} 