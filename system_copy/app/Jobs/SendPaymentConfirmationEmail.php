<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendPaymentConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $receivable;
    protected $paymentAmount;
    protected $newBalance;
    protected $paymentDate;
    protected $paymentMethod;
    protected $referenceNumber;
    
    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($receivable, $paymentAmount, $newBalance, $paymentDate, $paymentMethod, $referenceNumber)
    {
        $this->receivable = $receivable;
        $this->paymentAmount = $paymentAmount;
        $this->newBalance = $newBalance;
        $this->paymentDate = $paymentDate;
        $this->paymentMethod = $paymentMethod;
        $this->referenceNumber = $referenceNumber;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $institution = DB::table('institutions')->where('id', 1)->first();
            
            // Build HTML email content
            $htmlContent = $this->buildEmailContent();
            
            // Send email
            Mail::send([], [], function ($message) use ($htmlContent) {
                $message->to($this->receivable->customer_email, $this->receivable->customer_name)
                        ->subject('Payment Confirmation - Invoice ' . $this->receivable->invoice_number)
                        ->html($htmlContent);
            });
            
            Log::info('Payment confirmation email sent successfully', [
                'email' => $this->receivable->customer_email,
                'invoice' => $this->receivable->invoice_number,
                'amount' => $this->paymentAmount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'error' => $e->getMessage(),
                'email' => $this->receivable->customer_email,
                'invoice' => $this->receivable->invoice_number
            ]);
            
            // Re-throw to trigger retry
            throw $e;
        }
    }
    
    /**
     * Build HTML email content
     */
    private function buildEmailContent()
    {
        $institution = DB::table('institutions')->where('id', 1)->first();
        $institutionName = $institution->name ?? 'SACCOS';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 20px auto; background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .info-table td { padding: 12px; border-bottom: 1px solid #e0e0e0; }
                .info-table td:first-child { font-weight: 600; color: #555; width: 40%; }
                .amount { font-size: 32px; font-weight: bold; color: #4CAF50; text-align: center; margin: 20px 0; }
                .status { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: 600; }
                .status.paid { background-color: #d4edda; color: #155724; }
                .status.partial { background-color: #fff3cd; color: #856404; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
                .button { display: inline-block; padding: 12px 24px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
                .button:hover { background-color: #764ba2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Payment Received</h1>
                    <p style='margin: 10px 0 0 0; font-size: 18px;'>Thank you for your payment!</p>
                </div>
                
                <div class='content'>
                    <p style='font-size: 16px; color: #333;'>Dear <strong>{$this->receivable->customer_name}</strong>,</p>
                    
                    <p style='color: #666; line-height: 1.6;'>
                        We have successfully received your payment for Invoice <strong>{$this->receivable->invoice_number}</strong>.
                    </p>
                    
                    <div class='amount'>
                        " . number_format($this->paymentAmount, 2) . " TZS
                    </div>
                    
                    <table class='info-table'>
                        <tr>
                            <td>Invoice Number:</td>
                            <td><strong>{$this->receivable->invoice_number}</strong></td>
                        </tr>
                        <tr>
                            <td>Payment Date:</td>
                            <td>" . date('F j, Y', strtotime($this->paymentDate)) . "</td>
                        </tr>
                        <tr>
                            <td>Payment Method:</td>
                            <td>" . ucwords(str_replace('_', ' ', $this->paymentMethod)) . "</td>
                        </tr>
                        <tr>
                            <td>Reference Number:</td>
                            <td>{$this->referenceNumber}</td>
                        </tr>
                        <tr>
                            <td>Amount Paid:</td>
                            <td><strong>" . number_format($this->paymentAmount, 2) . " TZS</strong></td>
                        </tr>
                        <tr>
                            <td>Remaining Balance:</td>
                            <td><strong>" . number_format($this->newBalance, 2) . " TZS</strong></td>
                        </tr>
                        <tr>
                            <td>Payment Status:</td>
                            <td>
                                " . ($this->newBalance <= 0 
                                    ? "<span class='status paid'>FULLY PAID</span>" 
                                    : "<span class='status partial'>PARTIAL PAYMENT</span>") . "
                            </td>
                        </tr>
                    </table>
                    
                    " . ($this->newBalance > 0 ? "
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='margin: 0; color: #666;'>
                            <strong>Note:</strong> You still have an outstanding balance of <strong>" . number_format($this->newBalance, 2) . " TZS</strong>. 
                            Please ensure timely payment to avoid any late fees.
                        </p>
                    </div>
                    " : "
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='margin: 0; color: #155724;'>
                            <strong>Thank you!</strong> This invoice has been fully paid. We appreciate your prompt payment.
                        </p>
                    </div>
                    ") . "
                    
                    <p style='color: #666; line-height: 1.6;'>
                        A receipt for this payment has been generated and attached to your account. 
                        You can access it from your customer portal or request a copy by contacting us.
                    </p>
                </div>
                
                <div class='footer'>
                    <p style='margin: 5px 0;'><strong>{$institutionName}</strong></p>
                    <p style='margin: 5px 0; color: #888;'>
                        This is an automated payment confirmation. Please do not reply to this email.
                    </p>
                    <p style='margin: 10px 0 5px 0; color: #888; font-size: 12px;'>
                        If you have any questions, please contact our support team.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
}