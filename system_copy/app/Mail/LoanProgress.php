<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanProgress extends Mailable
{
    use Queueable, SerializesModels;
    public $officer_phone_number;
    public $name;
    public $loan_progress;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($officer_phone_number,$name,$loan_progress)
    {
        $this->loan_progress=$loan_progress;
        $this->name=$name;
        $this->officer_phone_number=$officer_phone_number;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function build()
    {
        $name=$this->name;
        $officer_phone_number=$this->officer_phone_number;
        $loan_progress= $this->loan_progress;



        return $this->view('emails.loanProgress')
            ->with(['name' => $name,'officer_phone_number'=>$officer_phone_number,'loan_progress'=>$loan_progress])
            ->subject('loan application');
    }

}
