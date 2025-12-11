<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\approvals;

class ApprovalStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $approval;

    public function __construct(approvals $approval)
    {
        $this->approval = $approval;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $processName = $this->formatProcessName($this->approval->process_name);
        
        return [
            'approval_id' => $this->approval->id,
            'process_name' => $processName,
            'status' => $this->approval->approval_status,
            'description' => $this->approval->process_description,
            'processed_by' => auth()->user()->name,
            'created_at' => now()
        ];
    }

    private function formatProcessName($processName)
    {
        // Convert camelCase to space-separated words
        $result = preg_replace('/(?<!^)([A-Z])/', ' $1', $processName);
        // Capitalize the first letter of each word
        return ucwords($result);
    }
} 