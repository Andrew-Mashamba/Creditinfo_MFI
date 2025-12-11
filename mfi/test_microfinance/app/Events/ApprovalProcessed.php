<?php

namespace App\Events;

use App\Models\approvals;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $approval;
    public $action;
    public $processedBy;

    public function __construct(approvals $approval, string $action)
    {
        $this->approval = $approval;
        $this->action = $action;
        $this->processedBy = auth()->user();
    }

    public function broadcastOn()
    {
        return new PrivateChannel('approvals');
    }

    public function broadcastWith()
    {
        return [
            'approval_id' => $this->approval->id,
            'process_name' => $this->approval->process_name,
            'process_id' => $this->approval->process_id,
            'action' => $this->action,
            'processed_by' => $this->processedBy->name,
            'timestamp' => now()->toIso8601String()
        ];
    }
} 