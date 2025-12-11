<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberPasswordResetNotification extends Notification
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('members.portal') . '?reset_token=' . $this->token;

        return (new MailMessage)
            ->subject('Reset Your SACCO Members Portal Password')
            ->greeting('Hello ' . $notifiable->getFullNameAttribute() . '!')
            ->line('You are receiving this email because we received a password reset request for your SACCO Members Portal account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in 24 hours.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Best regards, SACCO Management Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
            'type' => 'password_reset',
        ];
    }
} 