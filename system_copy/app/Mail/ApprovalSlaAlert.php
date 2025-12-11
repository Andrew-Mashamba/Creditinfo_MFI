<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Approval;
use Illuminate\Support\Collection;

class ApprovalSlaAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $approvals;
    public $alertType; // 'warning', 'breach', 'escalation'
    public $recipientName;
    public $recipientType; // 'checker', 'supervisor', 'escalation'
    public $reportTime;
    public $summary;

    /**
     * Create a new message instance.
     *
     * @param Collection $approvals - Collection of approvals with SLA issues
     * @param string $alertType - 'warning', 'breach', or 'escalation'
     * @param string $recipientName - Name of recipient
     * @param string $recipientType - 'checker', 'supervisor', 'escalation'
     */
    public function __construct(
        Collection $approvals,
        string $alertType = 'warning',
        string $recipientName = 'Team',
        string $recipientType = 'checker'
    ) {
        $this->approvals = $approvals;
        $this->alertType = $alertType;
        $this->recipientName = $recipientName;
        $this->recipientType = $recipientType;
        $this->reportTime = now()->format('Y-m-d H:i:s');

        // Calculate summary
        $this->summary = [
            'total' => $approvals->count(),
            'warnings' => $approvals->filter(fn($a) => $a->getSlaStatus() === 'warning')->count(),
            'breached' => $approvals->filter(fn($a) => $a->getSlaStatus() === 'breached')->count(),
            'oldest_hours' => $approvals->max(fn($a) => $a->getHoursAtCurrentLevel()),
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = match($this->alertType) {
            'warning' => "[WARNING] {$this->summary['total']} Approval(s) Approaching SLA Deadline",
            'breach' => "[URGENT] {$this->summary['breached']} Approval(s) Have Breached SLA",
            'escalation' => "[ESCALATION] {$this->summary['breached']} Approval(s) Require Immediate Attention",
            default => "[ALERT] Approval SLA Notification"
        };

        return $this->view('emails.approval-sla-alert')
                    ->subject($subject)
                    ->with([
                        'approvals' => $this->approvals,
                        'alertType' => $this->alertType,
                        'recipientName' => $this->recipientName,
                        'recipientType' => $this->recipientType,
                        'reportTime' => $this->reportTime,
                        'summary' => $this->summary,
                    ]);
    }
}
