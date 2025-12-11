<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channelRef;
    public $status;
    public $paymentDetails;

    /**
     * Create a new event instance.
     *
     * @param string $channelRef
     * @param string $status
     * @param array $paymentDetails
     * @return void
     */
    public function __construct($channelRef, $status, $paymentDetails = [])
    {
        $this->channelRef = $channelRef;
        $this->status = $status;
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('payment-status.' . $this->channelRef);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'payment.status.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'channelRef' => $this->channelRef,
            'status' => $this->status,
            'paymentDetails' => $this->paymentDetails,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}