<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GuarantorEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $memberName;
    public $controlNumbers;
    public $paymentLink;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, string $memberName)
    {

        Log::info('Guarantor email sent successfully xxxxxxxxxx', [
            'name' => $this->name,
            'memberName' => $this->memberName
        ]);


        $this->name = $name;
        $this->memberName = $memberName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Guarantor Notification - NBC SACCOS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.guarantor',
        );
    }
} 