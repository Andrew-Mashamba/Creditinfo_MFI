<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LoanSlaAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $loans;
    public $alertType; // 'warning', 'breach', 'escalation'
    public $recipientName;
    public $recipientType; // 'checker', 'supervisor', 'escalation'
    public $reportTime;
    public $summary;

    /**
     * Create a new message instance.
     *
     * @param Collection $loans - Collection of loans with SLA issues
     * @param string $alertType - 'warning', 'breach', or 'escalation'
     * @param string $recipientName - Name of recipient
     * @param string $recipientType - 'checker', 'supervisor', 'escalation'
     */
    public function __construct(
        Collection $loans,
        string $alertType = 'warning',
        string $recipientName = 'Team',
        string $recipientType = 'checker'
    ) {
        $this->loans = $loans;
        $this->alertType = $alertType;
        $this->recipientName = $recipientName;
        $this->recipientType = $recipientType;
        $this->reportTime = now()->format('Y-m-d H:i:s');

        // Calculate summary
        $this->summary = [
            'total' => $loans->count(),
            'warnings' => $loans->filter(fn($l) => $l->getSlaStatus() === 'warning')->count(),
            'breached' => $loans->filter(fn($l) => $l->getSlaStatus() === 'breached')->count(),
            'total_amount' => $loans->sum('principle'),
            'oldest_hours' => $loans->max(fn($l) => $l->getHoursAtCurrentStage()),
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
            'warning' => "[WARNING] {$this->summary['total']} Loan(s) Approaching SLA Deadline",
            'breach' => "[URGENT] {$this->summary['breached']} Loan(s) Have Breached SLA",
            'escalation' => "[ESCALATION] {$this->summary['breached']} Loan(s) Require Immediate Attention",
            default => "[ALERT] Loan Approval SLA Notification"
        };

        return $this->view('emails.loan-sla-alert')
                    ->subject($subject)
                    ->with([
                        'loans' => $this->loans,
                        'alertType' => $this->alertType,
                        'recipientName' => $this->recipientName,
                        'recipientType' => $this->recipientType,
                        'reportTime' => $this->reportTime,
                        'summary' => $this->summary,
                    ]);
    }
}
