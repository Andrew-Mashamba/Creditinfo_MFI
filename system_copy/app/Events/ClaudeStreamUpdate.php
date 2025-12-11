<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaudeStreamUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;
    public $chunk;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(string $sessionId, string $chunk)
    {
        $this->sessionId = $sessionId;
        $this->chunk = $chunk;
        $this->timestamp = microtime(true);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('claude-stream.' . $this->sessionId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'stream.update';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'chunk' => $this->chunk,
            'session_id' => $this->sessionId,
            'timestamp' => $this->timestamp
        ];
    }
}