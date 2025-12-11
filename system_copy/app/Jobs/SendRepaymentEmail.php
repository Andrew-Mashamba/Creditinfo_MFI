<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRepaymentEmail implements ShouldQueue
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
            
            $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .footer { text-align: center; padding: 10px; color: #666; }
                    .amount { font-size: 24px; font-weight: bold; color: #4CAF50; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Automatic Loan Repayment Notification</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$member->first_name} {$member->last_name},</p>
                        <p>We have successfully processed an automatic loan repayment from your deposit account.</p>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>Loan ID:</strong></td>
                                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>{$member->loan_id}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>Amount Deducted:</strong></td>
                                <td style='padding: 10px; border-bottom: 1px solid #ddd;' class='amount'>TZS " . number_format($member->total_amount, 2) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>Remaining Balance:</strong></td>
                                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>TZS " . number_format($member->remaining_balance, 2) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px;'><strong>Date:</strong></td>
                                <td style='padding: 10px;'>" . now()->format('F d, Y') . "</td>
                            </tr>
                        </table>
                        <p>Thank you for maintaining your loan repayments.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message. Please do not reply to this email.</p>
                        <p>&copy; " . date('Y') . " SACCOS System. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>";
            
            Mail::send([], [], function ($message) use ($member, $htmlContent) {
                $message->to($member->email)
                        ->subject('Automatic Loan Repayment - ' . now()->format('Y-m-d'))
                        ->html($htmlContent);
            });
            
            Log::info("Email sent to {$member->email} for loan {$member->loan_id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$this->member->email}: " . $e->getMessage());
            throw $e;
        }
    }
}