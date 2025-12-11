<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LoanDisbursementEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $memberName;
    public $loanDetails;
    public $controlNumbers;
    public $paymentLink;
    public $repaymentSchedule;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $memberName, 
        array $loanDetails, 
        array $controlNumbers = [], 
        ?string $paymentLink = null,
        array $repaymentSchedule = []
    ) {
        Log::info('Loan disbursement email data', [
            'member_name' => $memberName,
            'loan_details' => $loanDetails,
            'control_numbers_count' => count($controlNumbers),
            'payment_link' => $paymentLink,
            'repayment_schedule_count' => count($repaymentSchedule)
        ]);

        $this->memberName = $memberName;
        $this->loanDetails = $loanDetails;
        $this->controlNumbers = $controlNumbers;
        $this->paymentLink = $paymentLink;
        $this->repaymentSchedule = $repaymentSchedule;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Loan Disbursement Confirmation - NBC SACCOS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.loan-disbursement',
        );
    }
} 