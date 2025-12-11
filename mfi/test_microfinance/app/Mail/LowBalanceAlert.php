<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowBalanceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $account;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function build()
    {
        return $this->view('emails.low_balance_alert')
                    ->subject('Low Balance Alert');
    }
} 