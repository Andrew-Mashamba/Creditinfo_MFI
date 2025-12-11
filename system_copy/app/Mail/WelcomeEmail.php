<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $controlNumbers;
    public $paymentLink;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, array $controlNumbers, ?string $paymentLink = null)
    {
        Log::info('Welcome email data', [
            'name' => $name,
            'controlNumbers' => $controlNumbers,
            'paymentLink' => $paymentLink
        ]);

        $this->name = $name;
        $this->controlNumbers = $controlNumbers;
        $this->paymentLink = $paymentLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Welcome to NBC SACCOS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.welcome',
        );
    }
} 