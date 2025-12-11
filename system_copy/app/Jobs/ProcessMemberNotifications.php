<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SmsService;
use App\Services\SmsTemplateService;
use App\Services\RawSmtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\NotificationLog;
use Throwable;
use Illuminate\Support\Str;


class ProcessMemberNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 60;

    protected $member;
    protected $controlNumbers;
    protected $paymentLink;
    protected $loanId;
    protected $processId;
    protected $smsService;
    protected $smsTemplateService;
    protected $saccoName;
    protected $saccoPhone;
    protected $saccoEmail;


        /**
     * Create a new job instance.
     */
    public function __construct($member, $controlNumbers = [], $paymentLink = null, $loanId = null)
    {
        $this->member = $member;
        $this->controlNumbers = $controlNumbers;
        $this->paymentLink = $paymentLink;
        $this->loanId = $loanId;
        $this->processId = Str::uuid()->toString();
        $this->smsService = new SmsService();
        $this->smsTemplateService = new SmsTemplateService();

        // Get SACCO information from institutions table
        $institution = DB::table('institutions')->where('id', 1)->first();
        $this->saccoName = $institution->name ?? 'NBC SACCOS';
        $this->saccoPhone = $institution->phone_number ?? $institution->contact_phone ?? '0800 110 022';
        $this->saccoEmail = $institution->email ?? $institution->contact_email ?? 'info@nbc.co.tz';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting member notification process', [
                'process_id' => $this->processId,
                'member_id' => $this->member->id,
                'control_numbers_count' => is_array($this->controlNumbers) ? count($this->controlNumbers) : 0,
                'control_numbers' => $this->controlNumbers
            ]);

            // Notify member only (removed guarantor notifications)
            $this->notifyMember();

            Log::info('Member notification process completed successfully', [
                'process_id' => $this->processId,
                'member_id' => $this->member->id
            ]);

        } catch (Throwable $e) {
            $this->handleError($e, [
                'process_id' => $this->processId,
                'member_id' => $this->member->id,
                'step' => 'main_process'
            ]);
        }
    }

    protected function notifyMember(): void
    {
        Log::info('Processing member notification', [
            'process_id' => $this->processId,
            'member_id' => $this->member->id ?? null,
            'email' => $this->member->email ?? null
        ]);

        $memberName = $this->member->first_name . ' ' . $this->member->last_name;
        $memberPhone = $this->formatPhoneNumber($this->member->phone_number);
        $email = $this->member->email ?? null;

        // Prepare control numbers with service details
        $controlNumbersWithDetails = [];

        if (!empty($this->controlNumbers)) {
            try {
                Log::info('Processing control numbers', [
                    'process_id' => $this->processId,
                    'count' => is_array($this->controlNumbers) ? count($this->controlNumbers) : 0,
                ]);

                $controlNumbersWithDetails = collect($this->controlNumbers)->map(function ($control, $index) {
                    // Convert stdClass to array if needed
                    if (is_object($control)) {
                        $control = (array) $control;
                    }
                    
                    if (!is_array($control)) {
                        $this->logControlError('Item is not an array or object', $control, $index);
                    }

                    $requiredKeys = ['service_code', 'control_number', 'amount'];
                    foreach ($requiredKeys as $key) {
                        if (!array_key_exists($key, $control)) {
                            $this->logControlError("Missing required key: {$key}", $control, $index);
                        }
                    }

                    return [
                        'service_code'     => $control['service_code'],
                        'service_name'     => $control['service_name'] ?? $control['service_code'],
                        'control_number'   => $control['control_number'],
                        'amount'           => $control['amount'],
                    ];
                })->toArray();

                Log::info('Finished processing control numbers', [
                    'process_id' => $this->processId,
                    'processed_count' => is_array($controlNumbersWithDetails) ? count($controlNumbersWithDetails) : 0,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to process control numbers', [
                    'process_id' => $this->processId,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        // Check if this is a loan disbursement notification
        $isLoanDisbursement = !empty($controlNumbersWithDetails) && 
                             collect($controlNumbersWithDetails)->contains('service_code', 'REP');

        if ($isLoanDisbursement) {
            // Send loan disbursement email using RawSmtpService
            try {
                // Get loan details from the member's latest loan
                $loanDetails = $this->getLoanDetails($this->member->client_number);

                // Get repayment schedule
                $repaymentSchedule = $this->getRepaymentSchedule($this->member->client_number);

                $rawSmtp = new RawSmtpService();
                $emailBody = $this->buildLoanDisbursementEmailBody($memberName, $loanDetails, $controlNumbersWithDetails, $repaymentSchedule);
                $rawSmtp->send($this->member->email, 'Loan Disbursement - ' . $this->saccoName, $emailBody, true);

                Log::info('Loan disbursement email sent successfully via RawSmtpService', [
                    'process_id' => $this->processId,
                    'member_id' => $this->member->id,
                    'loan_details' => $loanDetails
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send loan disbursement email', [
                    'process_id' => $this->processId,
                    'member_id' => $this->member->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't throw exception if email fails - continue with SMS attempt
            }
        } else {
            // Send welcome email to member using RawSmtpService
            try {
                $rawSmtp = new RawSmtpService();
                $emailBody = $this->buildWelcomeEmailBody($memberName, $controlNumbersWithDetails);
                $rawSmtp->send($this->member->email, 'Welcome to ' . $this->saccoName . ' - Membership Approved', $emailBody, true);

                Log::info('Welcome email sent successfully via RawSmtpService', [
                    'process_id' => $this->processId,
                    'member_id' => $this->member->id,
                    'sacco_name' => $this->saccoName
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email', [
                    'process_id' => $this->processId,
                    'member_id' => $this->member->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Send SMS Notification for Member
        if (!empty($memberPhone)) {
            // Check if SMS service is configured
            $smsConfigured = config('services.sms.enabled', false);

            if ($smsConfigured) {
                try {
                    $smsMessages = $this->generateMemberSMSMessage($memberName, $controlNumbersWithDetails, $isLoanDisbursement);

                    // Handle both single string and array of strings
                    if (!is_array($smsMessages)) {
                        $smsMessages = [$smsMessages];
                    }

                    Log::info('SMS messages generated', [
                        'process_id' => $this->processId,
                        'member_id' => $this->member->id,
                        'is_loan_disbursement' => $isLoanDisbursement,
                        'total_parts' => count($smsMessages),
                        'message_lengths' => array_map('strlen', $smsMessages),
                        'messages_preview' => array_map(function($msg) {
                            return strlen($msg) > 50 ? substr($msg, 0, 50) . '...' : $msg;
                        }, $smsMessages)
                    ]);

                    $sentCount = 0;
                    $notificationRefs = [];

                    // Send each SMS message
                    foreach ($smsMessages as $index => $smsMessage) {
                        try {
                            $result = $this->smsService->send($memberPhone, $smsMessage, $this->member, [
                                'smsType' => 'TRANSACTIONAL',
                                'serviceName' => 'SACCOSS',
                                'language' => 'English'
                            ]);

                            $sentCount++;
                            $notificationRefs[] = $result['notification_ref'] ?? null;

                            Log::info('Member SMS part sent successfully', [
                                'process_id' => $this->processId,
                                'member_id' => $this->member->id,
                                'phone' => $memberPhone,
                                'part' => $index + 1,
                                'total_parts' => count($smsMessages),
                                'message_length' => strlen($smsMessage),
                                'notification_ref' => $result['notification_ref'] ?? null,
                                'is_loan_disbursement' => $isLoanDisbursement
                            ]);

                            // Add small delay between messages to avoid rate limiting
                            if ($index < count($smsMessages) - 1) {
                                usleep(500000); // 0.5 second delay
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to send SMS part', [
                                'process_id' => $this->processId,
                                'member_id' => $this->member->id,
                                'phone' => $memberPhone,
                                'part' => $index + 1,
                                'total_parts' => count($smsMessages),
                                'error' => $e->getMessage()
                            ]);
                            // Continue trying to send remaining parts
                        }
                    }

                    if ($sentCount > 0) {
                        Log::info('Member SMS notification completed', [
                            'process_id' => $this->processId,
                            'member_id' => $this->member->id,
                            'phone' => $memberPhone,
                            'parts_sent' => $sentCount,
                            'total_parts' => count($smsMessages),
                            'notification_refs' => $notificationRefs,
                            'is_loan_disbursement' => $isLoanDisbursement
                        ]);
                    } else {
                        throw new \Exception('Failed to send any SMS parts');
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send member SMS, falling back to email only', [
                        'process_id' => $this->processId,
                        'member_id' => $this->member->id,
                        'phone' => $memberPhone,
                        'error' => $e->getMessage()
                    ]);
                    // Don't throw exception for SMS failure - email was already sent
                }
            } else {
                // Log SMS message that would have been sent
                $smsMessages = $this->generateMemberSMSMessage($memberName, $controlNumbersWithDetails, $isLoanDisbursement);

                if (!is_array($smsMessages)) {
                    $smsMessages = [$smsMessages];
                }

                Log::info('SMS service not configured - messages would have been:', [
                    'process_id' => $this->processId,
                    'member_id' => $this->member->id,
                    'phone' => $memberPhone,
                    'total_parts' => count($smsMessages),
                    'message_lengths' => array_map('strlen', $smsMessages),
                    'messages' => $smsMessages,
                    'payment_link' => $this->paymentLink,
                    'is_loan_disbursement' => $isLoanDisbursement
                ]);

                Log::info('Notification sent via email only (SMS not configured)', [
                    'process_id' => $this->processId,
                    'member_id' => $this->member->id,
                    'email' => $this->member->email
                ]);
            }
        } else {
            Log::warning('No phone number available for member SMS', [
                'process_id' => $this->processId,
                'member_id' => $this->member->id
            ]);
        }
    }

    /**
     * Generate SMS message for member - returns array of message parts
     */
    protected function generateMemberSMSMessage($memberName, $controlNumbers, $isLoanDisbursement)
    {
        if ($isLoanDisbursement) {
            // Loan disbursement SMS
            $loanDetails = $this->getLoanDetails($this->member->client_number);
            $loanAmount = $loanDetails['approved_amount'] ?? 0;
            $monthlyInstallment = $loanDetails['monthly_installment'] ?? 0;
            $firstPaymentDate = $loanDetails['first_payment_date'] ?? null;
            $controlNumber = !empty($controlNumbers) ? $controlNumbers[0]['control_number'] : null;

            return $this->smsTemplateService->generateLoanDisbursementMemberSMS(
                $memberName,
                $loanAmount,
                $monthlyInstallment,
                $controlNumber,
                $this->paymentLink,
                $firstPaymentDate,
                $this->saccoName,
                $this->saccoPhone
            );
        } else {
            // Regular member approval SMS - Split into multiple messages if needed
            // to avoid breaking control numbers or payment links

            // Get member number
            $memberNumber = $this->member->client_number ?? 'N/A';

            // Find REG (Registration fee) and SHC (Shares) control numbers
            $registrationControl = null;
            $sharesControl = null;

            foreach ($controlNumbers as $cn) {
                if ($cn['service_code'] === 'REG') {
                    $registrationControl = $cn['control_number'];
                } elseif ($cn['service_code'] === 'SHC') {
                    $sharesControl = $cn['control_number'];
                }
            }

            // Build messages as separate parts to ensure critical info isn't split
            $messages = [];

            // Message 1: Welcome and Member Number (most important)
            $msg1 = "Welcome to {$this->saccoName}! ";
            $msg1 .= "You are now a full member.\n";
            $msg1 .= "Member Number: {$memberNumber}";
            $messages[] = $msg1;

            // Message 2: Control Numbers (never split these)
            $msg2 = "Payment Control Numbers:\n";
            if ($registrationControl) {
                $msg2 .= "Registration fee: {$registrationControl}\n";
            }
            if ($sharesControl) {
                $msg2 .= "Shares: {$sharesControl}";
            }
            $messages[] = $msg2;

            // Message 3: Payment Link (if available, keep it intact)
            if ($this->paymentLink) {
                $msg3 = "Pay online now:\n{$this->paymentLink}";
                $messages[] = $msg3;
            }

            // Try to combine messages if they fit within SMS limit (150 chars for safety)
            return $this->optimizeSmsMessages($messages);
        }
    }

    /**
     * Optimize SMS messages by combining them if they fit within limits
     * Returns array of SMS messages ready to send
     */
    protected function optimizeSmsMessages($messages)
    {
        $smsLimit = 150; // Conservative limit for single SMS
        $optimized = [];
        $currentMessage = '';

        foreach ($messages as $index => $msg) {
            // Try to combine with current message
            $combined = $currentMessage ? $currentMessage . "\n\n" . $msg : $msg;

            if (strlen($combined) <= $smsLimit) {
                // Fits within limit, combine
                $currentMessage = $combined;
            } else {
                // Doesn't fit, save current and start new
                if ($currentMessage) {
                    $optimized[] = $currentMessage;
                }
                $currentMessage = $msg;
            }
        }

        // Add the last message
        if ($currentMessage) {
            $optimized[] = $currentMessage;
        }

        return $optimized;
    }

    protected function logControlError(string $message, mixed $control, int $index): void
    {
        Log::error($message, [
            'process_id' => $this->processId,
            'index' => $index,
            'control_data' => $control,
            'type' => gettype($control),
        ]);

        throw new \InvalidArgumentException("Invalid control number data at index {$index}: {$message}");
    }

    protected function handleError(Throwable $e, array $logContext)
    {
        Log::error('Error in member notification process', array_merge($logContext, [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]));

        // Log the error in notification_logs
        NotificationLog::logNotification([
            'process_id' => $this->processId,
            'recipient_type' => get_class($this->member),
            'recipient_id' => $this->member->id,
            'notification_type' => 'member_registration',
            'channel' => 'system',
            'status' => 'failed',
            'error_message' => $e->getMessage(),
            'error_details' => [
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ],
            'created_by' => auth()->id()
        ]);

        $this->fail($e);
    }

    public function failed(Throwable $exception)
    {
        Log::error('Member notification process failed permanently', [
            'process_id' => $this->processId,
            'member_id' => $this->member->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Log permanent failure
        NotificationLog::logNotification([
            'process_id' => $this->processId,
            'recipient_type' => get_class($this->member),
            'recipient_id' => $this->member->id,
            'notification_type' => 'member_registration',
            'channel' => 'system',
            'status' => 'failed_permanently',
            'error_message' => $exception->getMessage(),
            'error_details' => [
                'exception_class' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
                'final_attempt' => $this->attempts()
            ],
            'created_by' => auth()->id()
        ]);
    }

    protected function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure it starts with country code
        if (strlen($phone) === 9 && substr($phone, 0, 1) !== '0') {
            $phone = '255' . $phone;
        } elseif (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '255' . substr($phone, 1);
        }
        
        return $phone;
    }

    protected function getLoanDetails($clientNumber)
    {
        try {
            // If loan_id was provided (disbursement notification), use it directly
            if ($this->loanId) {
                $loan = DB::table('loans')->where('id', $this->loanId)->first();

                Log::info('Using specific loan ID for notification', [
                    'loan_id' => $this->loanId,
                    'client_number' => $clientNumber
                ]);
            } else {
                // Fallback: Get most recently disbursed loan (must have disbursement_date set)
                $loan = DB::table('loans')
                    ->where('client_number', $clientNumber)
                    ->whereIn('status', ['DISBURSED', 'ACTIVE'])
                    ->whereNotNull('disbursement_date') // Exclude loans without disbursement date
                    ->orderBy('disbursement_date', 'desc')
                    ->first();

                Log::info('No loan_id provided, querying for latest disbursed loan', [
                    'client_number' => $clientNumber,
                    'found_loan_id' => $loan->id ?? null
                ]);
            }

            if (!$loan) {
                return [];
            }

            // Get product details for interest rate
            $product = DB::table('loan_sub_products')
                ->where('sub_product_id', $loan->loan_sub_product)
                ->first();

            $interestRate = $product->interest_value ?? 0;

            // Calculate first payment date based on member's payroll date (MEMBERS PORTAL PATTERN)
            $disbursementDate = $loan->disbursement_date ? \Carbon\Carbon::parse($loan->disbursement_date) : \Carbon\Carbon::now();
            $firstPaymentDate = $disbursementDate->copy()->addMonth(); // Default fallback

            try {
                // Get member's payroll date from their group
                $client = DB::table('clients')->where('client_number', $clientNumber)->first();
                if ($client && $client->member_group) {
                    $memberGroup = DB::table('member_groups')->where('id', $client->member_group)->first();

                    if ($memberGroup && $memberGroup->payrol_date) {
                        $payrollDay = (int)$memberGroup->payrol_date;

                        // Calculate first payment date based on payroll day
                        if ($disbursementDate->day < $payrollDay) {
                            // Use this month's payroll date
                            $firstPaymentDate = $disbursementDate->copy()->day($payrollDay);
                        } else {
                            // Use next month's payroll date
                            $firstPaymentDate = $disbursementDate->copy()->addMonth()->day($payrollDay);
                        }

                        Log::info('First payment date calculated for notification', [
                            'client_number' => $clientNumber,
                            'payroll_day' => $payrollDay,
                            'disbursement_date' => $disbursementDate->format('Y-m-d'),
                            'first_payment_date' => $firstPaymentDate->format('Y-m-d')
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not calculate exact first payment date for notification, using default +1 month', [
                    'error' => $e->getMessage(),
                    'client_number' => $clientNumber
                ]);
            }

            return [
                'approved_amount' => $loan->approved_loan_value ?? $loan->principle,
                'tenure' => $loan->tenure ?? 12,
                'interest_rate' => $interestRate,
                'monthly_installment' => $loan->monthly_installment ?? 0,
                'disbursement_date' => $disbursementDate->format('d/m/Y'),
                'first_payment_date' => $firstPaymentDate->format('d/m/Y'),
                'loan_id' => $loan->id,
                'loan_type' => $loan->loan_type ?? 'PERSONAL_LOAN'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get loan details', [
                'client_number' => $clientNumber,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function getRepaymentSchedule($clientNumber)
    {
        try {
            // If loan_id was provided (disbursement notification), use it directly
            if ($this->loanId) {
                $loan = DB::table('loans')->where('id', $this->loanId)->first();
            } else {
                // Fallback: Get most recently disbursed loan (must have disbursement_date set)
                $loan = DB::table('loans')
                    ->where('client_number', $clientNumber)
                    ->whereIn('status', ['DISBURSED', 'ACTIVE'])
                    ->whereNotNull('disbursement_date') // Exclude loans without disbursement date
                    ->orderBy('disbursement_date', 'desc')
                    ->first();
            }

            if (!$loan) {
                return [];
            }

            // Try to get schedule - check if table exists and has data
            $schedule = DB::table('loans_schedules')
                ->where('loan_id', $loan->id)
                ->orderBy('installment_date', 'asc')
                ->get();

            if ($schedule->isEmpty()) {
                // If no schedule found, generate a simple one based on loan details
                return $this->generateSimpleSchedule($loan);
            }

            return $schedule->map(function ($installment) {
                return [
                    'due_date' => $installment->installment_date ? date('d/m/Y', strtotime($installment->installment_date)) : '',
                    'principal' => $installment->principle ?? 0,
                    'interest' => $installment->interest ?? 0,
                    'total' => $installment->installment ?? 0,
                    'balance' => $installment->closing_balance ?? 0
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get repayment schedule', [
                'client_number' => $clientNumber,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function generateSimpleSchedule($loan)
    {
        // Generate a simple repayment schedule if database schedule doesn't exist
        $principal = floatval($loan->principle ?? 0);
        $tenure = intval($loan->tenure ?? 12);
        $monthlyInstallment = $loan->monthly_installment ?? ($principal / $tenure);

        $schedule = [];
        $disbursementDate = $loan->disbursement_date ?? now();
        $balance = $principal;

        for ($i = 1; $i <= $tenure; $i++) {
            $dueDate = date('d/m/Y', strtotime($disbursementDate . " +{$i} month"));
            $principalPortion = $principal / $tenure;
            $interestPortion = $monthlyInstallment - $principalPortion;
            $balance -= $principalPortion;

            $schedule[] = [
                'due_date' => $dueDate,
                'principal' => $principalPortion,
                'interest' => max(0, $interestPortion),
                'total' => $monthlyInstallment,
                'balance' => max(0, $balance)
            ];
        }

        return $schedule;
    }

    protected function buildWelcomeEmailBody($memberName, $controlNumbers)
    {
        // Get member number
        $memberNumber = $this->member->client_number ?? 'N/A';

        // Find SHC, SAV, and DIP control numbers
        $sharesInfo = null;
        $savingsInfo = null;
        $depositsInfo = null;

        foreach ($controlNumbers as $cn) {
            if ($cn['service_code'] === 'SHC') {
                $sharesInfo = $cn;
            } elseif ($cn['service_code'] === 'SAV') {
                $savingsInfo = $cn;
            } elseif ($cn['service_code'] === 'DIP') {
                $depositsInfo = $cn;
            }
        }

        $sharesAmount = $sharesInfo ? number_format($sharesInfo['amount'], 0) : '0';
        $savingsAmount = $savingsInfo ? number_format($savingsInfo['amount'], 0) : '0';
        $depositsAmount = $depositsInfo ? number_format($depositsInfo['amount'], 0) : '0';

        $sharesControlNumber = $sharesInfo['control_number'] ?? 'N/A';
        $savingsControlNumber = $savingsInfo['control_number'] ?? 'N/A';
        $depositsControlNumber = $depositsInfo['control_number'] ?? 'N/A';

        $paymentButton = $this->paymentLink
            ? "<p style='margin-top: 25px; text-align: center;'><a href='{$this->paymentLink}' style='background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Pay Online Now</a></p>"
            : '';

        return "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background-color: #007bff; padding: 20px; border-radius: 5px 5px 0 0;'>
                        <h2 style='color: white; margin: 0;'>Welcome to {$this->saccoName}!</h2>
                    </div>

                    <div style='background-color: #f8f9fa; padding: 20px; border-left: 3px solid #007bff;'>
                        <p style='font-size: 16px; margin: 0;'>Dear <strong>{$memberName}</strong>,</p>
                    </div>

                    <div style='padding: 20px; background-color: white;'>
                        <p style='font-size: 15px; line-height: 1.8;'>
                            Congratulations! 🎉 Your membership application has been <strong style='color: #28a745;'>APPROVED</strong> and you are now a full member of <strong>{$this->saccoName}</strong>.
                        </p>

                        <div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff;'>
                            <p style='margin: 5px 0; font-size: 14px;'><strong>Member Number:</strong> <span style='color: #007bff; font-size: 16px;'>{$memberNumber}</span></p>
                        </div>

                        <h3 style='color: #007bff; font-size: 18px; border-bottom: 2px solid #007bff; padding-bottom: 10px;'>Payment Control Numbers</h3>

                        <p style='font-size: 14px; color: #666; margin-bottom: 20px;'>
                            To complete your registration and start enjoying member benefits, please make payments using the control numbers below. You can use any NBC payment channel (Mobile Money, Bank Transfer, or Online Payment).
                        </p>

                        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                            <thead>
                                <tr style='background-color: #007bff; color: white;'>
                                    <th style='padding: 12px; text-align: left; border: 1px solid #007bff;'>Service</th>
                                    <th style='padding: 12px; text-align: left; border: 1px solid #007bff;'>Control Number</th>
                                    <th style='padding: 12px; text-align: right; border: 1px solid #007bff;'>Amount (TZS)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style='background-color: #f8f9fa;'>
                                    <td style='padding: 12px; border: 1px solid #dee2e6;'>
                                        <strong>Buy Shares</strong><br>
                                        <small style='color: #666;'>Mandatory Share Capital</small>
                                    </td>
                                    <td style='padding: 12px; border: 1px solid #dee2e6;'>
                                        <code style='background-color: #e9ecef; padding: 4px 8px; border-radius: 3px; font-size: 14px;'>{$sharesControlNumber}</code>
                                    </td>
                                    <td style='padding: 12px; border: 1px solid #dee2e6; text-align: right;'><strong>{$sharesAmount}</strong></td>
                                </tr>
                                <tr>
                                    <td style='padding: 12px; border: 1px solid #dee2e6;'>
                                        <strong>Deposits</strong><br>
                                        <small style='color: #666;'>Member Deposit Account</small>
                                    </td>
                                    <td style='padding: 12px; border: 1px solid #dee2e6;'>
                                        <code style='background-color: #e9ecef; padding: 4px 8px; border-radius: 3px; font-size: 14px;'>{$depositsControlNumber}</code>
                                    </td>
                                    <td style='padding: 12px; border: 1px solid #dee2e6; text-align: right;'><strong>{$depositsAmount}</strong></td>
                                </tr>
                                <tr style='background-color: #f8f9fa;'>
                                    <td style='padding: 12px; border: 1px solid #dee2e6;'>
                                        <strong>Savings</strong><br>
                                        <small style='color: #666;'>Member Savings Account</small>
                                    </td>
                                    <td style='padding: 12px; border: 1px solid #dee2e6;'>
                                        <code style='background-color: #e9ecef; padding: 4px 8px; border-radius: 3px; font-size: 14px;'>{$savingsControlNumber}</code>
                                    </td>
                                    <td style='padding: 12px; border: 1px solid #dee2e6; text-align: right;'><strong>{$savingsAmount}</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        {$paymentButton}

                        <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 25px 0; border-left: 4px solid #ffc107;'>
                            <p style='margin: 0; font-size: 14px;'><strong>💡 Important Notes:</strong></p>
                            <ul style='margin: 10px 0; padding-left: 20px; font-size: 14px;'>
                                <li>Use the control numbers above to make payments through NBC channels</li>
                                <li>Shares and Deposits are mandatory for all members</li>
                                <li>Savings contributions can be made at any time to build your savings</li>
                                <li>Keep your member number safe for future transactions</li>
                            </ul>
                        </div>

                        <div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #17a2b8;'>
                            <p style='margin: 0; font-size: 14px;'><strong>📞 Need Help?</strong></p>
                            <p style='margin: 10px 0 0 0; font-size: 14px;'>
                                Contact our support team or visit your nearest NBC branch for assistance.
                            </p>
                        </div>
                    </div>

                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 3px solid #007bff;'>
                        <p style='margin: 5px 0; color: #666; font-size: 14px;'>
                            <strong>{$this->saccoName}</strong><br>
                            Your Trusted Financial Partner
                        </p>
                        <p style='margin: 10px 0 0 0; color: #999; font-size: 12px;'>
                            This is an automated email. Please do not reply.
                        </p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    protected function buildLoanDisbursementEmailBody($memberName, $loanDetails, $controlNumbers, $repaymentSchedule)
    {
        $loanAmount = number_format($loanDetails['approved_amount'] ?? 0, 2);
        $tenure = $loanDetails['tenure'] ?? 0;
        $interestRate = $loanDetails['interest_rate'] ?? 0;
        $monthlyInstallment = number_format($loanDetails['monthly_installment'] ?? 0, 2);
        $disbursementDate = $loanDetails['disbursement_date'] ?? date('d/m/Y');
        $firstPaymentDate = $loanDetails['first_payment_date'] ?? 'TBA';

        // Get control number for display
        $controlNumber = !empty($controlNumbers) ? $controlNumbers[0]['control_number'] : 'N/A';
        $controlAmount = !empty($controlNumbers) ? number_format($controlNumbers[0]['amount'], 2) : '0.00';

        // Build repayment schedule table
        $scheduleRows = '';
        $installmentNumber = 1;
        foreach ($repaymentSchedule as $installment) {
            $rowBg = $installmentNumber % 2 == 0 ? '#f8f9fa' : '#ffffff';
            $scheduleRows .= "<tr style='background-color: {$rowBg};'>
                <td style='padding: 10px; border: 1px solid #dee2e6; text-align: center;'>{$installmentNumber}</td>
                <td style='padding: 10px; border: 1px solid #dee2e6;'>{$installment['due_date']}</td>
                <td style='padding: 10px; border: 1px solid #dee2e6; text-align: right;'>TZS " . number_format($installment['principal'] ?? 0, 2) . "</td>
                <td style='padding: 10px; border: 1px solid #dee2e6; text-align: right;'>TZS " . number_format($installment['interest'] ?? 0, 2) . "</td>
                <td style='padding: 10px; border: 1px solid #dee2e6; text-align: right;'><strong>TZS " . number_format($installment['total'] ?? 0, 2) . "</strong></td>
            </tr>";
            $installmentNumber++;
        }

        // Payment button
        $paymentButton = $this->paymentLink
            ? "<div style='text-align: center; margin: 30px 0;'>
                    <a href='{$this->paymentLink}' style='background-color: #004c99; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-size: 16px; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                        💳 Pay Online Now
                    </a>
                </div>"
            : '';

        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            </head>
            <body style='margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; background-color: #f4f7fa;'>
                <div style='max-width: 650px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);'>

                    <!-- Header -->
                    <div style='background-color: #003366; padding: 30px 20px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;'>✅ Loan Disbursed Successfully</h1>
                        <p style='color: #cce0ff; margin: 8px 0 0 0; font-size: 14px;'>{$this->saccoName}</p>
                    </div>

                    <!-- Content -->
                    <div style='padding: 30px 25px;'>

                        <!-- Greeting -->
                        <p style='font-size: 16px; color: #333; margin: 0 0 20px 0;'>Dear <strong>{$memberName}</strong>,</p>

                        <p style='font-size: 15px; color: #555; line-height: 1.6; margin: 0 0 25px 0;'>
                            We are pleased to inform you that your loan has been <strong style='color: #28a745;'>disbursed successfully</strong>.
                            Below are the details of your loan and repayment schedule.
                        </p>

                        <!-- Loan Summary Card -->
                        <div style='background-color: #e6f0ff; border-left: 4px solid #004080; padding: 20px; border-radius: 8px; margin-bottom: 25px;'>
                            <h3 style='color: #004080; margin: 0 0 15px 0; font-size: 18px;'>📋 Loan Summary</h3>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-size: 14px;'>Loan Amount:</td>
                                    <td style='padding: 8px 0; color: #333; font-weight: bold; text-align: right; font-size: 14px;'>TZS {$loanAmount}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-size: 14px;'>Tenure:</td>
                                    <td style='padding: 8px 0; color: #333; font-weight: bold; text-align: right; font-size: 14px;'>{$tenure} months</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-size: 14px;'>Annual Interest Rate:</td>
                                    <td style='padding: 8px 0; color: #333; font-weight: bold; text-align: right; font-size: 14px;'>{$interestRate}%</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-size: 14px;'>Monthly Installment:</td>
                                    <td style='padding: 8px 0; color: #004080; font-weight: bold; text-align: right; font-size: 16px;'>TZS {$monthlyInstallment}</td>
                                </tr>
                                <tr style='border-top: 2px solid #004080;'>
                                    <td style='padding: 12px 0 8px 0; color: #666; font-size: 14px;'>Disbursement Date:</td>
                                    <td style='padding: 12px 0 8px 0; color: #333; font-weight: bold; text-align: right; font-size: 14px;'>{$disbursementDate}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-size: 14px;'>First Payment Due:</td>
                                    <td style='padding: 8px 0; color: #d9534f; font-weight: bold; text-align: right; font-size: 14px;'>{$firstPaymentDate}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Payment Control Number -->
                        <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 25px;'>
                            <h3 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>💳 Payment Control Number</h3>
                            <p style='margin: 0; color: #856404; font-size: 14px;'>Use this control number to make your monthly payments:</p>
                            <div style='background: #ffffff; padding: 15px; margin-top: 12px; border-radius: 6px; text-align: center;'>
                                <div style='font-size: 24px; font-weight: bold; color: #004080; letter-spacing: 2px;'>{$controlNumber}</div>
                                <div style='font-size: 14px; color: #666; margin-top: 8px;'>Amount: TZS {$controlAmount}</div>
                            </div>
                        </div>

                        {$paymentButton}

                        <!-- Payment Methods -->
                        <div style='background: #e7f5ff; border-left: 4px solid #17a2b8; padding: 15px; border-radius: 8px; margin-bottom: 25px;'>
                            <h4 style='color: #0c5460; margin: 0 0 10px 0; font-size: 15px;'>💰 Payment Methods</h4>
                            <ul style='margin: 0; padding-left: 20px; color: #0c5460; font-size: 14px; line-height: 1.8;'>
                                <li>NBC Kiganjani (Mobile Banking)</li>
                                <li>NBC Wakala (Agent Banking)</li>
                                <li>NBC Bank Branches</li>
                                <li>Online Payment Portal (link above)</li>
                            </ul>
                        </div>

                        <!-- Repayment Schedule -->
                        <div style='margin: 30px 0;'>
                            <h3 style='color: #333; margin: 0 0 15px 0; font-size: 18px; border-bottom: 2px solid #004080; padding-bottom: 10px;'>📅 Repayment Schedule</h3>
                            <div style='overflow-x: auto;'>
                                <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                                    <thead>
                                        <tr style='background-color: #003366; color: white;'>
                                            <th style='padding: 12px; text-align: center; border: 1px solid #002952;'>#</th>
                                            <th style='padding: 12px; text-align: left; border: 1px solid #002952;'>Due Date</th>
                                            <th style='padding: 12px; text-align: right; border: 1px solid #002952;'>Principal</th>
                                            <th style='padding: 12px; text-align: right; border: 1px solid #002952;'>Interest</th>
                                            <th style='padding: 12px; text-align: right; border: 1px solid #002952;'>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {$scheduleRows}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Important Notice -->
                        <div style='background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 8px; margin-top: 25px;'>
                            <h4 style='color: #721c24; margin: 0 0 10px 0; font-size: 15px;'>⚠️ Important Reminder</h4>
                            <p style='margin: 0; color: #721c24; font-size: 13px; line-height: 1.6;'>
                                Please ensure timely payment of your monthly installments to avoid penalties and maintain a good credit standing.
                                Late payments may attract additional charges.
                            </p>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div style='background-color: #f8f9fa; padding: 25px; text-align: center; border-top: 1px solid #dee2e6;'>
                        <p style='margin: 0 0 10px 0; color: #333; font-size: 14px; font-weight: 600;'>{$this->saccoName}</p>
                        <p style='margin: 0 0 8px 0; color: #666; font-size: 13px;'>
                            📞 Contact Center: <strong>{$this->saccoPhone}</strong>
                        </p>
                        <p style='margin: 0 0 15px 0; color: #666; font-size: 13px;'>
                            📧 Email: <a href='mailto:{$this->saccoEmail}' style='color: #004080; text-decoration: none;'>{$this->saccoEmail}</a>
                        </p>
                        <p style='margin: 0; color: #999; font-size: 12px;'>
                            This is an automated email. Please do not reply directly to this message.
                        </p>
                        <p style='margin: 8px 0 0 0; color: #999; font-size: 11px;'>
                            © " . date('Y') . " {$this->saccoName}. All rights reserved.
                        </p>
                    </div>

                </div>
            </body>
            </html>
        ";
    }

}
