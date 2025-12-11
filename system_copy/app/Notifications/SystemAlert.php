<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SystemAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $alert;

    public function __construct(array $alert)
    {
        $this->alert = $alert;
    }

    public function via($notifiable)
    {
        // Always notify via all channels
        return ['mail', 'slack', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('System Alert: Resource Usage Warning')
            ->greeting('System Alert')
            ->line('The following system resources have exceeded their thresholds:');

        foreach ($this->alert as $resource => $value) {
            if ($resource !== 'signature') {
                $message->line("{$resource}: {$value}");
            }
        }

        return $message;
    }

    public function toSlack($notifiable)
    {
        $message = (new SlackMessage)
            ->error()
            ->content('System Alert: Resource Usage Warning')
            ->attachment(function ($attachment) {
                $attachment->title('Resource Usage Details')
                    ->fields($this->formatAlertData());
            });

        return $message;
    }

    public function toDatabase($notifiable)
    {
        // Implementation of toDatabase method
    }

    public function toArray($notifiable)
    {
        return [
            'alert' => $this->alert,
            'timestamp' => now(),
        ];
    }

    protected function formatAlertData()
    {
        $fields = [];
        foreach ($this->alert as $resource => $value) {
            if ($resource !== 'signature') {
                $fields[$resource] = $value;
            }
        }
        return $fields;
    }
} 