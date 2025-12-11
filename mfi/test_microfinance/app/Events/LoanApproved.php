<?php

namespace App\Events;

use App\Models\LoansModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $loan;
    public $approverId;
    public $approvalData;

    public function __construct(LoansModel $loan, $approverId, $approvalData = [])
    {
        $this->loan = $loan;
        $this->approverId = $approverId;
        $this->approvalData = $approvalData;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('loan-approvals');
    }

    public function broadcastAs()
    {
        return 'loan.approved';
    }

    public function broadcastWith()
    {
        return [
            'loan_id' => $this->loan->id,
            'client_number' => $this->loan->client_number,
            'principle' => $this->loan->principle,
            'tenure' => $this->loan->tenure,
            'approver_id' => $this->approverId,
            'approved_at' => now()->toISOString(),
            'approval_data' => $this->approvalData
        ];
    }
} 