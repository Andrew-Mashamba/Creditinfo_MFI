<?php

namespace App\Mail;

use Swift_SmtpTransport;

class PlainSmtpTransport extends Swift_SmtpTransport
{
    /**
     * Disable STARTTLS
     */
    public function setEncryption($encryption)
    {
        // Force no encryption
        return parent::setEncryption(null);
    }
}