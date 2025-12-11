<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $memberName;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct($memberName, $message)
    {
        $this->memberName = $memberName;
        $this->message = $message;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('NBC SACCOS Notification')
                    ->view('emails.generic-notification');
    }
} 