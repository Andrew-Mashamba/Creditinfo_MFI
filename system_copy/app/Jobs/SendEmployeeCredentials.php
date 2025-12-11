<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\User;
use App\Services\SmsService;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmployeeCredentials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $employee;
    protected $password;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(Employee $employee, string $password, User $user)
    {
        $this->employee = $employee;
        $this->password = $password;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('=== SEND EMPLOYEE CREDENTIALS JOB STARTED ===', [
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'email' => $this->employee->email,
            'phone' => $this->employee->phone,
            'timestamp' => now()->toDateTimeString()
        ]);

        $emailSent = false;
        $smsSent = false;

        // Send credentials via Email
        if ($this->employee->email) {
            try {
                Log::info('Attempting to send credentials via email', [
                    'employee_id' => $this->employee->id,
                    'email' => $this->employee->email,
                    'timestamp' => now()->toDateTimeString()
                ]);

                $this->sendCredentialsEmail();
                $emailSent = true;

                Log::info('✓ Credentials email sent successfully', [
                    'employee_id' => $this->employee->id,
                    'email' => $this->employee->email,
                    'timestamp' => now()->toDateTimeString()
                ]);
            } catch (\Exception $e) {
                Log::error('✗ Failed to send credentials email', [
                    'employee_id' => $this->employee->id,
                    'email' => $this->employee->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
        }

        // Send credentials via SMS
        if ($this->employee->phone) {
            try {
                Log::info('Attempting to send credentials via SMS', [
                    'employee_id' => $this->employee->id,
                    'phone' => $this->employee->phone,
                    'timestamp' => now()->toDateTimeString()
                ]);

                $this->sendCredentialsSms();
                $smsSent = true;

                Log::info('✓ Credentials SMS sent successfully', [
                    'employee_id' => $this->employee->id,
                    'phone' => $this->employee->phone,
                    'timestamp' => now()->toDateTimeString()
                ]);
            } catch (\Exception $e) {
                Log::error('✗ Failed to send credentials SMS', [
                    'employee_id' => $this->employee->id,
                    'phone' => $this->employee->phone,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
        }

        // If neither was sent, log for manual intervention
        if (!$emailSent && !$smsSent) {
            Log::critical('EMPLOYEE CREDENTIALS DELIVERY FAILED - Manual intervention required', [
                'employee_id' => $this->employee->id,
                'user_id' => $this->user->id,
                'employee_name' => $this->employee->first_name . ' ' . $this->employee->last_name,
                'employee_email' => $this->employee->email,
                'employee_phone' => $this->employee->phone,
                'credentials' => [
                    'email' => $this->employee->email,
                    'password' => $this->password,
                    'employee_number' => $this->employee->employee_number
                ],
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        Log::info('=== SEND EMPLOYEE CREDENTIALS JOB COMPLETED ===', [
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Send credentials via email
     */
    protected function sendCredentialsEmail()
    {
        $emailService = new EmailService();
        
        $subject = 'Welcome to SACCOS System - Your Login Credentials';
        
        $body = "
            <h2>Welcome {$this->employee->first_name} {$this->employee->last_name}!</h2>
            <p>Your account has been created in the SACCOS System.</p>
            <p><strong>Your login credentials are:</strong></p>
            <ul>
                <li><strong>Email:</strong> {$this->employee->email}</li>
                <li><strong>Password:</strong> {$this->password}</li>
                <li><strong>Employee Number:</strong> {$this->employee->employee_number}</li>
            </ul>
            <p><strong>Important:</strong></p>
            <ul>
                <li>Please change your password upon first login</li>
                <li>Keep your credentials secure and do not share them</li>
                <li>If you have any issues, please contact IT support</li>
            </ul>
            <p>Login URL: " . url('/login') . "</p>
            <br>
            <p>Best regards,<br>SACCOS System Administration</p>
        ";
        
        $emailData = [
            'to' => $this->employee->email,
            'subject' => $subject,
            'body' => $body,
            'from_name' => 'SACCOS System',
            'reply_to' => config('mail.from.address'),
        ];
        
        $emailService->sendEmail($emailData, false);
    }

    /**
     * Send credentials via SMS
     */
    protected function sendCredentialsSms()
    {
        $smsService = new SmsService();
        
        $message = "Welcome to SACCOS! Your login: Email: {$this->employee->email}, Password: {$this->password}. Please change password on first login. Login at: " . url('/login');
        
        // Format phone number (remove leading 0 if present and add country code)
        $phone = $this->employee->phone;
        if (substr($phone, 0, 1) === '0') {
            $phone = '255' . substr($phone, 1);
        } elseif (substr($phone, 0, 4) !== '255') {
            // If it doesn't start with country code, assume it's Tanzania
            $phone = '255' . $phone;
        }
        
        $smsService->send($phone, $message, $this->employee, [
            'smsType' => 'TRANSACTIONAL',
            'serviceName' => 'SACCOS',
            'language' => 'English'
        ]);
    }

    /**
     * The number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }

    /**
     * The number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Retry after 30 seconds, then 1 minute, then 2 minutes
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Employee credentials job failed after all retries', [
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}