<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LoanGuarantorEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $guarantorName;
    public $memberName;
    public $loanDetails;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $guarantorName, 
        string $memberName, 
        array $loanDetails = []
    ) {
        Log::info('Loan guarantor email data', [
            'guarantor_name' => $guarantorName,
            'member_name' => $memberName,
            'loan_details' => $loanDetails
        ]);

        $this->guarantorName = $guarantorName;
        $this->memberName = $memberName;
        $this->loanDetails = $loanDetails;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Loan Disbursement Notification - NBC SACCOS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.loan-guarantor',
        );
    }
} 