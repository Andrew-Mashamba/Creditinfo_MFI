<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MandatorySavingsPaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $memberName;
    public $controlNumber;
    public $amount;
    public $dueDate;
    public $month;
    public $year;
    public $accountNumber;
    public $paymentInstructions;
    public $paymentLink;

    /**
     * Create a new message instance.
     */
    public function __construct($memberName, $controlNumber, $amount, $dueDate, $month, $year, $accountNumber, $paymentLink = null)
    {
        $this->memberName = $memberName;
        $this->controlNumber = $controlNumber;
        $this->amount = $amount;
        $this->dueDate = $dueDate;
        $this->month = $month;
        $this->year = $year;
        $this->accountNumber = $accountNumber;
        $this->paymentLink = $paymentLink;
        $this->paymentInstructions = $this->getDefaultPaymentInstructions();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Mandatory Savings Payment - {$this->month} {$this->year}")
                    ->view('emails.mandatory-savings-payment');
    }

    /**
     * Get default payment instructions
     */
    private function getDefaultPaymentInstructions()
    {
        return [
            'bank_transfer' => [
                'title' => 'Bank Transfer',
                'steps' => [
                    'Visit any NBC Bank branch',
                    'Provide your control number: ' . $this->controlNumber,
                    'Pay the amount: TZS ' . number_format($this->amount, 2),
                    'Keep your receipt for reference'
                ]
            ],
            'mobile_money' => [
                'title' => 'Mobile Money',
                'steps' => [
                    'Dial *150*00# for M-Pesa',
                    'Select "Pay Bill"',
                    'Enter Business Number: 123456',
                    'Enter Account Number: ' . $this->controlNumber,
                    'Enter Amount: ' . $this->amount,
                    'Enter your PIN and confirm'
                ]
            ],
            'online_banking' => [
                'title' => 'Online Banking',
                'steps' => [
                    'Log in to your NBC Online Banking',
                    'Go to "Payments" section',
                    'Select "SACCO Payments"',
                    'Enter control number: ' . $this->controlNumber,
                    'Enter amount: TZS ' . number_format($this->amount, 2),
                    'Confirm and complete payment'
                ]
            ]
        ];
    }
} 