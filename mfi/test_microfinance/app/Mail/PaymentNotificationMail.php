<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class PaymentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $type;
    public $daysUntilDue;
    public $isOverdue;
    public $urgency;

    /**
     * Create a new message instance.
     */
    public function __construct($payment, $type)
    {
        $this->payment = $payment;
        $this->type = $type;
        
        // Calculate days until due/overdue
        $today = Carbon::today();
        $dueDate = Carbon::parse($payment->due_date);
        $this->daysUntilDue = $today->diffInDays($dueDate, false);
        
        // Determine if overdue
        $this->isOverdue = in_array($type, ['overdue', 'overdue_receivable']);
        
        // Determine urgency
        if ($this->isOverdue) {
            $this->urgency = abs($this->daysUntilDue) > 30 ? 'urgent' : 'high';
        } else {
            $this->urgency = $this->daysUntilDue <= 2 ? 'high' : 'medium';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'upcoming' => "Upcoming Payment Due - {$this->payment->vendor_name}",
            'overdue' => "OVERDUE Payment - {$this->payment->vendor_name}",
            'upcoming_receivable' => "Expected Payment - Customer Invoice",
            'overdue_receivable' => "OVERDUE Receivable - Customer Invoice",
            default => "Payment Notification"
        };
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-notification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}