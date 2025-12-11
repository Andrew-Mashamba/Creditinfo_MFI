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
use App\Services\EmailService;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProcessTradePayableInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payableId;
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
     * @param int $payableId
     * @param int $userId
     */
    public function __construct($payableId, $userId)
    {
        $this->payableId = $payableId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Processing trade payable invoice job started', [
            'payable_id' => $this->payableId,
            'user_id' => $this->userId
        ]);

        try {
            // Update status to processing
            DB::table('trade_payables')
                ->where('id', $this->payableId)
                ->update(['processing_status' => 'processing']);
            
            // Get the payable data
            $payable = DB::table('trade_payables')->find($this->payableId);
            if (!$payable) {
                Log::error('Payable not found', ['payable_id' => $this->payableId]);
                return;
            }

            // Get institution data
            $institution = DB::table('institutions')->where('id', 1)->first();

            // Step 1: Generate PDF Invoice/Bill
            $pdfPath = null;
            try {
                $pdfPath = $this->generatePdfBill($payable, $institution);
                
                // Update payable with PDF path
                DB::table('trade_payables')
                    ->where('id', $this->payableId)
                    ->update([
                        'invoice_file_path' => $pdfPath,
                        'updated_at' => now()
                    ]);
                    
                Log::info('PDF bill generated', [
                    'payable_id' => $this->payableId,
                    'pdf_path' => $pdfPath
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to generate PDF for payable', [
                    'payable_id' => $this->payableId,
                    'error' => $e->getMessage()
                ]);
            }

            // Step 2: Send Email Notification (if vendor email exists)
            if ($payable->vendor_email) {
                try {
                    $this->sendEmailNotification($payable, $institution, $pdfPath);
                    
                    Log::info('Email notification sent for payable', [
                        'payable_id' => $this->payableId,
                        'vendor_email' => $payable->vendor_email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send email for payable', [
                        'payable_id' => $this->payableId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Step 3: Send SMS Notification (if vendor phone exists)
            if ($payable->vendor_phone) {
                try {
                    $this->sendSmsNotification($payable, $institution);
                    
                    Log::info('SMS notification sent for payable', [
                        'payable_id' => $this->payableId,
                        'vendor_phone' => $payable->vendor_phone
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS for payable', [
                        'payable_id' => $this->payableId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update status to completed
            DB::table('trade_payables')
                ->where('id', $this->payableId)
                ->update([
                    'processing_status' => 'completed',
                    'updated_at' => now()
                ]);

            Log::info('Trade payable invoice job completed successfully', [
                'payable_id' => $this->payableId
            ]);

        } catch (\Exception $e) {
            Log::error('Trade payable invoice job failed', [
                'payable_id' => $this->payableId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update status to failed
            DB::table('trade_payables')
                ->where('id', $this->payableId)
                ->update([
                    'processing_status' => 'failed',
                    'processing_error' => $e->getMessage(),
                    'updated_at' => now()
                ]);

            throw $e;
        }
    }

    /**
     * Generate PDF bill for the payable
     */
    private function generatePdfBill($payable, $institution)
    {
        // Prepare data for PDF
        $data = [
            'payable' => $payable,
            'institution' => $institution,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'due_date' => Carbon::parse($payable->due_date)->format('F d, Y'),
            'bill_date' => Carbon::parse($payable->bill_date)->format('F d, Y'),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.trade-payable-bill', $data);
        
        // Save PDF to storage
        $fileName = 'payable-bill-' . $payable->bill_number . '-' . time() . '.pdf';
        $path = 'payables/bills/' . $fileName;
        $fullPath = storage_path('app/public/' . $path);
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $pdf->save($fullPath);
        
        return $path;
    }

    /**
     * Send email notification with bill attachment
     */
    private function sendEmailNotification($payable, $institution, $pdfPath = null)
    {
        $emailData = [
            'vendor_name' => $payable->vendor_name,
            'bill_number' => $payable->bill_number,
            'amount' => number_format($payable->amount, 2),
            'due_date' => Carbon::parse($payable->due_date)->format('F d, Y'),
            'institution_name' => $institution->name ?? 'SACCOS',
            'description' => $payable->description,
        ];

        // Send email
        Mail::send('emails.trade-payable-notification', $emailData, function ($message) use ($payable, $pdfPath, $institution) {
            $message->to($payable->vendor_email, $payable->vendor_name)
                    ->subject('Payment Commitment - ' . $payable->bill_number)
                    ->from($institution->email ?? 'noreply@saccos.com', $institution->name ?? 'SACCOS');
            
            // Attach PDF if available
            if ($pdfPath) {
                $fullPath = storage_path('app/public/' . $pdfPath);
                if (file_exists($fullPath)) {
                    $message->attach($fullPath);
                }
            }
        });
    }

    /**
     * Send SMS notification for payment commitment
     */
    private function sendSmsNotification($payable, $institution)
    {
        $message = sprintf(
            "Dear %s, Payment Commitment: We confirm payment of %s %s for Ref %s will be made by %s. Thank you for your services. %s",
            $payable->vendor_name,
            $payable->currency ?? 'TZS',
            number_format($payable->amount, 2),
            $payable->bill_number,
            Carbon::parse($payable->due_date)->format('d/m/Y'),
            $institution->name ?? 'SACCOS'
        );

        // Use SMS service if available
        if (class_exists(SmsService::class)) {
            $smsService = new SmsService();
            $smsService->sendSms($payable->vendor_phone, $message);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Trade payable invoice job failed permanently', [
            'payable_id' => $this->payableId,
            'error' => $exception->getMessage()
        ]);

        // Update status to failed
        DB::table('trade_payables')
            ->where('id', $this->payableId)
            ->update([
                'processing_status' => 'failed',
                'processing_error' => $exception->getMessage(),
                'updated_at' => now()
            ]);
    }
}