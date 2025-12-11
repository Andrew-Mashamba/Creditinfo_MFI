<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRepaymentSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $member;
    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct($member)
    {
        $this->member = $member;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $member = $this->member;
            
            $message = "Dear {$member->first_name}, ";
            $message .= "TZS " . number_format($member->total_amount, 2) . " has been deducted from your account ";
            $message .= "for loan {$member->loan_id}. ";
            $message .= "Balance: TZS " . number_format($member->remaining_balance, 2) . ". ";
            $message .= "Thank you - SACCOS";
            
            // TODO: Integrate with SMS gateway
            // For now, just log the message
            Log::info("SMS queued for {$member->mobile_number}: {$message}");
            
            // Example SMS gateway integration:
            // $smsService = new \App\Services\SMSService();
            // $smsService->send($member->mobile_number, $message);
            
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$this->member->mobile_number}: " . $e->getMessage());
            throw $e;
        }
    }
}