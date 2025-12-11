<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuarantorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $member;
    protected $guarantor;

    public function __construct($member, $guarantor)
    {
        $this->member = $member;
        $this->guarantor = $guarantor;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Guarantor Registration - NBC SACCOS')
            ->greeting('Dear ' . $this->guarantor->name)
            ->line('You have been registered as a guarantor for a new member at NBC SACCOS.')
            ->line('Member Details:')
            ->line('Name: ' . $this->member->name)
            ->line('Member Number: ' . $this->member->client_number)
            ->line('If you did not agree to be a guarantor for this member, please contact us immediately.')
            ->line('Contact Information:')
            ->line('Phone: +255 22 219 7000')
            ->line('Email: support@nbcsaccos.co.tz')
            ->line('Thank you for your cooperation.');
    }

    public function toArray($notifiable)
    {
        return [
            'member_id' => $this->member->id,
            'guarantor_id' => $this->guarantor->id,
            'type' => 'guarantor_notification',
            'message' => 'You have been registered as a guarantor for ' . $this->member->name
        ];
    }
} 