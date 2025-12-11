<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FailedTransactionReport extends Mailable
{
    use Queueable, SerializesModels;

    public $transactions;
    public $recipientType; // 'tech' or 'user'
    public $userName;
    public $reportTime;
    public $totalAmount;

    /**
     * Create a new message instance.
     *
     * @param Collection $transactions - Collection of failed transactions
     * @param string $recipientType - 'tech' for tech team, 'user' for the initiating user
     * @param string|null $userName - Name of the user (for user emails)
     */
    public function __construct(Collection $transactions, string $recipientType = 'tech', ?string $userName = null)
    {
        $this->transactions = $transactions;
        $this->recipientType = $recipientType;
        $this->userName = $userName;
        $this->reportTime = now()->format('Y-m-d H:i:s');
        $this->totalAmount = $transactions->sum('amount');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $count = $this->transactions->count();

        if ($this->recipientType === 'tech') {
            $subject = "[ALERT] {$count} Failed Transaction(s) Require Attention - " . now()->format('d M Y H:i');
        } else {
            $subject = "Transaction Failure Notification - " . now()->format('d M Y');
        }

        return $this->view('emails.failed-transaction-report')
                    ->subject($subject)
                    ->with([
                        'transactions' => $this->transactions,
                        'recipientType' => $this->recipientType,
                        'userName' => $this->userName,
                        'reportTime' => $this->reportTime,
                        'totalAmount' => $this->totalAmount,
                        'transactionCount' => $count,
                    ]);
    }
}
