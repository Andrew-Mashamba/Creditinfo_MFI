<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMemberWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $member;
    protected $controlNumber;
    protected $sharesAccount;
    protected $savingsAccount;
    protected $depositsAccount;
    protected $password;

    public function __construct($member, $controlNumber, $sharesAccount = null, $savingsAccount = null, $depositsAccount = null, $password = null)
    {
        $this->member = $member;
        $this->controlNumber = $controlNumber;
        $this->sharesAccount = $sharesAccount;
        $this->savingsAccount = $savingsAccount;
        $this->depositsAccount = $depositsAccount;
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Welcome to NBC SACCOS!')
            ->greeting('Dear ' . $this->member->first_name . ' ' . $this->member->last_name)
            ->line('Welcome to NBC SACCOS! We are delighted to have you as a member.');

        // Handle control numbers (could be array or string)
        if (is_array($this->controlNumber)) {
            $message->line('Your control numbers:');
            foreach ($this->controlNumber as $control) {
                $message->line('- ' . $control['service_code'] . ': ' . $control['control_number'] . ' (Amount: ' . $control['amount'] . ')');
            }
        } else {
            $message->line('Your control number is: ' . $this->controlNumber);
        }

        $message->line('Please make your payment to complete the registration process.');

        // Add account information if available
        if ($this->sharesAccount) {
            $message->line('Your Shares Account Number: ' . $this->sharesAccount->account_number);
        }
        if ($this->savingsAccount) {
            $message->line('Your Savings Account Number: ' . $this->savingsAccount->account_number);
        }
        if ($this->depositsAccount) {
            $message->line('Your Deposits Account Number: ' . $this->depositsAccount->account_number);
        }

        // Add login credentials if available
        if ($this->password) {
            $message->line('Your login credentials:')
                ->line('Username: ' . $this->member->email)
                ->line('Password: ' . $this->password)
                ->line('Please change your password after your first login.');
        }

        $message->line('For any assistance, please contact our support team.')
            ->line('Thank you for choosing NBC SACCOS!');

        return $message;
    }

    public function toArray($notifiable)
    {
        $controlNumberText = is_array($this->controlNumber) ?
            implode(', ', array_map(function($control) {
                return $control['service_code'] . ': ' . $control['control_number'] . ' (Amount: ' . $control['amount'] . ')';
            }, $this->controlNumber)) :
            $this->controlNumber;

        return [
            'member_id' => $this->member->id,
            'control_number' => $controlNumberText,
            'shares_account' => $this->sharesAccount ? $this->sharesAccount->account_number : null,
            'savings_account' => $this->savingsAccount ? $this->savingsAccount->account_number : null,
            'deposits_account' => $this->depositsAccount ? $this->depositsAccount->account_number : null,
            'type' => 'welcome_notification',
            'message' => 'Welcome to NBC SACCOS! Your control number is: ' . $controlNumberText
        ];
    }
} 