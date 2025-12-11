<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuarantorNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $type,
        protected array $member,
        protected object $guarantor
    ) {}

    public function build()
    {
        return $this->view("emails.{$this->type}")
            ->with([
                'member' => $this->member,
                'guarantor' => $this->guarantor
            ]);
    }
} 