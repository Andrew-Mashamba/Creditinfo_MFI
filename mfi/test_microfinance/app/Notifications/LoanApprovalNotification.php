<?php

namespace App\Notifications;

use App\Models\LoansModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $loan;
    public $recipientType;

    public function __construct(LoansModel $loan, $recipientType = 'officer')
    {
        $this->loan = $loan;
        $this->recipientType = $recipientType;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->getSubject();
        $greeting = $this->getGreeting($notifiable);
        $content = $this->getContent();

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($content)
            ->line('Loan Details:')
            ->line('• Loan ID: ' . $this->loan->loan_id)
            ->line('• Amount: ' . number_format($this->loan->principle, 2) . ' TZS')
            ->line('• Term: ' . $this->loan->tenure . ' months')
            ->line('• Interest Rate: ' . $this->loan->interest . '%')
            ->action('View Loan Details', url('/loans/' . $this->loan->id))
            ->line('Thank you for using our loan management system!');
    }

    public function toArray($notifiable)
    {
        return [
            'loan_id' => $this->loan->id,
            'loan_number' => $this->loan->loan_id,
            'client_number' => $this->loan->client_number,
            'amount' => $this->loan->principle,
            'term' => $this->loan->tenure,
            'interest_rate' => $this->loan->interest,
            'status' => $this->loan->status,
            'recipient_type' => $this->recipientType,
            'message' => $this->getContent(),
            'action_url' => url('/loans/' . $this->loan->id)
        ];
    }

    protected function getSubject()
    {
        switch ($this->recipientType) {
            case 'officer':
                return 'Loan Approved - Action Required';
            case 'client':
                return 'Your Loan Has Been Approved';
            case 'management':
                return 'Loan Approval Notification';
            default:
                return 'Loan Status Update';
        }
    }

    protected function getGreeting($notifiable)
    {
        switch ($this->recipientType) {
            case 'officer':
                return 'Hello ' . $notifiable->name . ',';
            case 'client':
                return 'Dear ' . ($this->loan->client->first_name ?? 'Valued Customer') . ',';
            case 'management':
                return 'Hello Management Team,';
            default:
                return 'Hello,';
        }
    }

    protected function getContent()
    {
        switch ($this->recipientType) {
            case 'officer':
                return 'A loan application you processed has been approved. Please proceed with the disbursement process.';
            case 'client':
                return 'Congratulations! Your loan application has been approved. You will receive disbursement instructions shortly.';
            case 'management':
                return 'A new loan has been approved in the system. Please review the details below.';
            default:
                return 'A loan has been approved in the system.';
        }
    }
} 