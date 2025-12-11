<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountStatusChange extends Mailable
{
    use Queueable, SerializesModels;

    public $change;

    public function __construct($change)
    {
        $this->change = $change;
    }

    public function build()
    {
        return $this->view('emails.status_change')
                    ->subject('Account Status Change');
    }
} 