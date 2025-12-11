<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ClientsModel;

class MemberPortalCredentialsNotification extends Notification
{

    protected $credentials;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
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
        return (new MailMessage)
            ->subject(isset($this->credentials['is_password_reset']) && $this->credentials['is_password_reset'] 
                ? 'Your SACCO Portal Password Has Been Reset' 
                : 'Welcome to Your SACCO Members Portal')
            ->view('emails.member-portal-credentials', [
                'member' => $notifiable,
                'credentials' => $this->credentials
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'member_id' => $notifiable->id,
            'member_number' => $this->credentials['member_number'] ?? null,
            'action' => isset($this->credentials['is_password_reset']) && $this->credentials['is_password_reset'] 
                ? 'password_reset' 
                : 'portal_access_enabled',
            'sent_at' => now(),
        ];
    }
}
