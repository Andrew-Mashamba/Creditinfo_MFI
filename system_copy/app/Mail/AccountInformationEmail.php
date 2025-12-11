<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AccountInformationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $sharesAccount;
    public $savingsAccount;
    public $depositsAccount;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, $sharesAccount = null, $savingsAccount = null, $depositsAccount = null)
    {
        Log::info('Account information email data', [
            'name' => $name,
            'has_shares' => !is_null($sharesAccount),
            'has_savings' => !is_null($savingsAccount),
            'has_deposits' => !is_null($depositsAccount)
        ]);

        $this->name = $name;
        $this->sharesAccount = $sharesAccount;
        $this->savingsAccount = $savingsAccount;
        $this->depositsAccount = $depositsAccount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Your NBC SACCOS Account Information',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.account-information',
        );
    }
} 