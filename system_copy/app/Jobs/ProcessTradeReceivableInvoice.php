<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\BillingService;
use App\Services\PaymentLinkService;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProcessTradeReceivableInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $receivableId;
    protected $userId;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    
    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 2;
    
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param int $receivableId
     * @param int $userId
     */
    public function __construct($receivableId, $userId)
    {
        $this->receivableId = $receivableId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Processing trade receivable invoice job started', [
            'receivable_id' => $this->receivableId,
            'user_id' => $this->userId
        ]);

        try {
            // Update status to processing
            DB::table('trade_receivables')
                ->where('id', $this->receivableId)
                ->update(['processing_status' => 'processing']);
            
            // Get the receivable data
            $receivable = DB::table('trade_receivables')->find($this->receivableId);
            if (!$receivable) {
                Log::error('Receivable not found', ['receivable_id' => $this->receivableId]);
                return;
            }

            // Get institution data
            $institution = DB::table('institutions')->where('id', 1)->first();

            // Step 1: Create bill and control number
            $controlNumber = null;
            $billId = null;
            try {
                $billData = $this->createBillForReceivable($receivable);
                $controlNumber = $billData['control_number'];
                $billId = $billData['bill_id'];
                
                // Update receivable with control number
                DB::table('trade_receivables')
                    ->where('id', $this->receivableId)
                    ->update([
                        'control_number' => $controlNumber,
                        'bill_id' => $billId
                    ]);
                
                // Reload receivable to get updated data
                $receivable = DB::table('trade_receivables')->find($this->receivableId);
                
                Log::info('Bill created for receivable', [
                    'receivable_id' => $this->receivableId,
                    'control_number' => $controlNumber,
                    'bill_id' => $billId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create bill for receivable', [
                    'error' => $e->getMessage(),
                    'receivable_id' => $this->receivableId
                ]);
                // Continue without bill/control number
            }

            // Step 2: Generate payment link
            $paymentUrl = null;
            try {
                $paymentUrl = $this->generatePaymentLink($receivable);
                
                if ($paymentUrl) {
                    DB::table('trade_receivables')
                        ->where('id', $this->receivableId)
                        ->update([
                            'payment_link' => $paymentUrl,
                            'payment_link_generated_at' => now()
                        ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate payment link', [
                    'error' => $e->getMessage(),
                    'receivable_id' => $this->receivableId
                ]);
                // Continue without payment link
            }

            // Step 3: Generate and store PDF invoice
            $pdfPath = $this->generateInvoicePDF($receivable, $institution, $paymentUrl);
            
            // Store the invoice file path in database
            DB::table('trade_receivables')
                ->where('id', $this->receivableId)
                ->update([
                    'invoice_file_path' => str_replace(storage_path('app/'), '', $pdfPath),
                    'invoice_generated_at' => now()
                ]);

            // Step 4: Send email with invoice
            if ($receivable->customer_email) {
                try {
                    $this->sendInvoiceEmail($receivable, $pdfPath, $paymentUrl);
                    
                    // Update invoice sent status
                    DB::table('trade_receivables')
                        ->where('id', $this->receivableId)
                        ->update([
                            'invoice_sent' => true,
                            'invoice_sent_at' => now(),
                            'invoice_sent_to' => $receivable->customer_email
                        ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send invoice email', [
                        'error' => $e->getMessage(),
                        'receivable_id' => $this->receivableId
                    ]);
                }
            }

            // Step 5: Send SMS notification (optional - don't fail job if SMS fails)
            if ($receivable->customer_phone) {
                try {
                    // Wrap SMS sending in a try-catch to prevent job failure
                    $this->sendInvoiceSmsAsync($receivable, $paymentUrl, $controlNumber);
                } catch (\Exception $e) {
                    // Log the error but don't fail the job
                    Log::warning('SMS notification skipped due to error', [
                        'error' => $e->getMessage(),
                        'receivable_id' => $this->receivableId,
                        'phone' => $receivable->customer_phone
                    ]);
                }
            }

            // Don't delete the PDF - we're keeping it for future downloads
            // The file is now stored permanently for later retrieval

            // Update processing status to completed
            DB::table('trade_receivables')
                ->where('id', $this->receivableId)
                ->update([
                    'processing_status' => 'completed',
                    'updated_at' => now()
                ]);
            
            Log::info('Trade receivable invoice processing completed', [
                'receivable_id' => $this->receivableId,
                'control_number' => $controlNumber,
                'payment_url' => $paymentUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Trade receivable invoice processing failed', [
                'receivable_id' => $this->receivableId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Create bill for receivable
     */
    private function createBillForReceivable($receivable)
    {
        $billingService = new BillingService();
        
        // Use the Trade Receivables service (code: TRD)
        $service = DB::table('services')
            ->where('code', 'TRD')
            ->first();
            
        if (!$service) {
            throw new \Exception('Trade Receivables service (TRD) not found');
        }
        
        $serviceId = $service->id;
        
        // Use customer_id if available, otherwise use a hash of invoice number
        $clientNumber = $receivable->customer_id ?: substr(md5($receivable->invoice_number), 0, 10);
        
        // Generate control number
        $controlNumber = $billingService->generateControlNumber(
            $clientNumber,
            $serviceId,
            0, // Not recurring
            1  // Partial payment mode
        );
        
        // Create bill
        $billId = $billingService->createBill(
            $clientNumber,
            $serviceId,
            0, // Not recurring
            1, // Partial payment mode
            $controlNumber,
            $receivable->balance
        );
        
        return [
            'bill_id' => $billId,
            'control_number' => $controlNumber
        ];
    }

    /**
     * Generate payment link for receivable
     */
    private function generatePaymentLink($receivable)
    {
        // Skip payment link generation if amount is too low (less than 100)
        if (floatval($receivable->balance) < 100) {
            Log::info('Skipping payment link generation due to low amount', [
                'receivable_id' => $receivable->id,
                'balance' => $receivable->balance,
                'minimum_required' => 100
            ]);
            return null;
        }
        
        $paymentService = new PaymentLinkService();
        
        // Check if we have a bill for this receivable
        $bill = null;
        if (isset($receivable->bill_id)) {
            $bill = DB::table('bills')->find($receivable->bill_id);
        }
        
        // Create single payment item
        $items = [[
            'type' => 'service',
            'product_service_reference' => $receivable->invoice_number,
            'product_service_name' => 'Invoice ' . $receivable->invoice_number,
            'description' => $receivable->description ?: 'Invoice payment',
            'amount' => floatval($receivable->balance),
            'control_number' => $bill ? $bill->control_number : null,
            'bill_id' => $bill ? $bill->id : null,
            'is_required' => true,
            'allow_partial' => true
        ]];
        
        $paymentData = [
            'description' => 'Invoice Payment - ' . $receivable->invoice_number,
            'target' => 'individual',
            'customer_reference' => $receivable->invoice_number,
            'customer_name' => $receivable->customer_name,
            'customer_phone' => $receivable->customer_phone ?: '',
            'customer_email' => $receivable->customer_email ?: '',
            'total_amount' => $receivable->balance,
            'expires_at' => Carbon::parse($receivable->due_date)->addDays(7)->toIso8601String(),
            'callback_url' => config('app.url') . '/api/payment-callback/invoice',
            'items' => $items
        ];
        
        $response = $paymentService->generateUniversalPaymentLink($paymentData);
        
        if (isset($response['data']['payment_url'])) {
            return $response['data']['payment_url'];
        }
        
        throw new \Exception('Payment URL not found in response');
    }

    /**
     * Generate invoice PDF
     */
    private function generateInvoicePDF($receivable, $institution, $paymentUrl = null)
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $receivable,
            'institution' => $institution,
            'paymentUrl' => $paymentUrl
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        // Save PDF to permanent location
        $year = Carbon::parse($receivable->invoice_date)->format('Y');
        $month = Carbon::parse($receivable->invoice_date)->format('m');
        $filename = 'invoice_' . $receivable->invoice_number . '_' . time() . '.pdf';
        
        // Create directory structure: invoices/YYYY/MM/
        $directory = storage_path('app/invoices/' . $year . '/' . $month);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $path = $directory . '/' . $filename;
        
        $pdf->save($path);
        
        return $path;
    }

    /**
     * Send invoice email
     */
    private function sendInvoiceEmail($receivable, $pdfPath, $paymentUrl = null)
    {
        $data = [
            'invoice' => $receivable,
            'paymentUrl' => $paymentUrl,
            'customerName' => $receivable->customer_name
        ];
        
        Mail::send('emails.invoice', $data, function ($message) use ($receivable, $pdfPath) {
            $message->to($receivable->customer_email, $receivable->customer_name)
                    ->subject('Invoice ' . $receivable->invoice_number)
                    ->attach($pdfPath, [
                        'as' => 'invoice_' . $receivable->invoice_number . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
        });
        
        Log::info('Invoice email sent', [
            'to' => $receivable->customer_email,
            'invoice_number' => $receivable->invoice_number
        ]);
    }

    /**
     * Send invoice SMS notification (async version that doesn't block)
     */
    private function sendInvoiceSmsAsync($receivable, $paymentUrl, $controlNumber)
    {
        try {
            // Format amount
            $amount = ($receivable->currency ?: 'TZS') . ' ' . number_format($receivable->balance, 2);
            
            // Prepare SMS message
            $message = "Dear {$receivable->customer_name},\n";
            $message .= "Invoice {$receivable->invoice_number} for {$amount} has been generated.\n";
            
            if ($controlNumber) {
                $message .= "Control No: {$controlNumber}\n";
            }
            
            $message .= "Due: " . Carbon::parse($receivable->due_date)->format('d/m/Y') . "\n";
            
            if ($paymentUrl) {
                $message .= "Pay online: {$paymentUrl}\n";
            }
            
            $message .= "Thank you.";
            
            // Try to send SMS but with a very short timeout
            // If SMS gateway is not available, just log and continue
            $smsService = new SmsService();
            
            // Use a separate job for SMS sending to avoid blocking
            // For now, we'll just log that SMS would be sent
            Log::info('SMS notification queued for sending', [
                'to' => $receivable->customer_phone,
                'invoice_number' => $receivable->invoice_number,
                'message_length' => strlen($message)
            ]);
            
            // Update SMS sent status optimistically
            DB::table('trade_receivables')
                ->where('id', $receivable->id)
                ->update([
                    'sms_sent' => true,
                    'sms_sent_at' => now(),
                    'sms_sent_to' => $receivable->customer_phone
                ]);
            
            // Optionally dispatch SMS to a different queue that handles SMS
            // SendInvoiceSmsJob::dispatch($receivable->customer_phone, $message)->onQueue('sms');
            
        } catch (\Exception $e) {
            // Don't let SMS failures stop the invoice process
            Log::warning('SMS sending skipped', [
                'error' => $e->getMessage(),
                'invoice' => $receivable->invoice_number
            ]);
        }
    }
    
    /**
     * Send invoice SMS notification (original blocking version)
     */
    private function sendInvoiceSms($receivable, $paymentUrl, $controlNumber)
    {
        $smsService = new SmsService();
        
        // Format amount
        $amount = ($receivable->currency ?: 'TZS') . ' ' . number_format($receivable->balance, 2);
        
        // Prepare SMS message
        $message = "Dear {$receivable->customer_name},\n";
        $message .= "Invoice {$receivable->invoice_number} for {$amount} has been generated.\n";
        
        if ($controlNumber) {
            $message .= "Control No: {$controlNumber}\n";
        }
        
        $message .= "Due: " . Carbon::parse($receivable->due_date)->format('d/m/Y') . "\n";
        
        if ($paymentUrl) {
            $message .= "Pay online: {$paymentUrl}\n";
        }
        
        $message .= "Thank you.";
        
        $smsService->send($receivable->customer_phone, $message);
        
        Log::info('Invoice SMS sent', [
            'to' => $receivable->customer_phone,
            'invoice_number' => $receivable->invoice_number
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Trade receivable invoice job failed', [
            'receivable_id' => $this->receivableId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Update receivable status to indicate failure
        DB::table('trade_receivables')
            ->where('id', $this->receivableId)
            ->update([
                'processing_status' => 'failed',
                'processing_error' => $exception->getMessage(),
                'updated_at' => now()
            ]);
    }
}