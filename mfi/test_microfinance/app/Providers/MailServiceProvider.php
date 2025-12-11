<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkTransportFactory;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Mail::extend('plain-smtp', function (array $config) {
            // Create transport for plain SMTP without STARTTLS
            $transport = new EsmtpTransport(
                $config['host'] ?? 'localhost',
                $config['port'] ?? 25,
                false // Disable TLS
            );
            
            // Only set authentication if provided
            if (!empty($config['username'])) {
                $transport->setUsername($config['username']);
                $transport->setPassword($config['password']);
            }
            
            return $transport;
        });
    }
}