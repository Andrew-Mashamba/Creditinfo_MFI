<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $password;
    protected $department;
    protected $role;

    public function __construct($user, $password, $department = null, $role = null)
    {
        $this->user = $user;
        $this->password = $password;
        $this->department = $department;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $appName = config('app.name', 'SACCOS Management System');
        
        return (new MailMessage)
            ->subject('Your Account Has Been Created - ' . $appName)
            ->view('emails.user-credentials', [
                'user' => $this->user,
                'password' => $this->password,
                'department' => $this->department,
                'role' => $this->role,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'message' => 'Account credentials sent to ' . $this->user->email,
        ];
    }
}