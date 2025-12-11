<?php

namespace App\Notifications;

use App\Models\MeetingAttendance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StipendPaidNotification extends Notification
{
    use Queueable;

    public $attendance;

    public function __construct(MeetingAttendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $meeting = $this->attendance->meeting;
        return (new MailMessage)
            ->subject('Stipend Paid Notification')
            ->greeting('Hello ' . ($notifiable->full_name ?? ''))
            ->line('Your stipend for the meeting "' . ($meeting->title ?? '-') . '" held on ' . ($meeting->meeting_date ? \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y H:i') : '-') . ' has been marked as PAID.')
            ->line('Stipend Amount: ' . number_format($this->attendance->stipend_amount, 2))
            ->line('Thank you for your service!');
    }
} 