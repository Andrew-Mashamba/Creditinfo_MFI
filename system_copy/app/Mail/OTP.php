<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OTP extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $otp;
    public $appUrl;
    private $logger;

    /**
     * Create a new message instance.
     */
    public function __construct($appUrl, $name, $otp)
    {
        $this->name = $name;
        $this->otp = $otp;
        $this->appUrl = $appUrl;
        $this->logger = Log::channel('otp');

        $this->logger->info('OTP Email Initialized', [
            'timestamp' => now()->toDateTimeString(),
            'recipient_name' => $name,
            'otp_length' => strlen($otp),
            'app_url' => $appUrl
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $this->logger->info('Preparing OTP Email Envelope', [
            'timestamp' => now()->toDateTimeString(),
            'subject' => 'Your OTP Code',
            'recipient_name' => $this->name
        ]);

        return new Envelope(
            subject: 'Your OTP Code',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->logger->info('Preparing OTP Email Content', [
            'timestamp' => now()->toDateTimeString(),
            'recipient_name' => $this->name,
            'otp_length' => strlen($this->otp),
            'view' => 'emails.otp'
        ]);

        return new Content(
            view: 'emails.otp',
            with: [
                'name' => $this->name,
                'otp' => $this->otp,
                'appUrl' => $this->appUrl
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
