<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShareDividendNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $dividend;

    public function __construct($dividend)
    {
        $this->dividend = $dividend;
    }

    public function build()
    {
        return $this->view('emails.share_dividend_notification')
                    ->subject('Share Dividend Notification');
    }
} 