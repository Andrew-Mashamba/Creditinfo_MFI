<?php

namespace App\Services;

use App\Models\LoanWriteOff;
use App\Models\WriteoffMemberCommunication;
use App\Services\SmsService;
use App\Services\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MemberCommunicationService
{
    protected $smsService;
    protected $emailService;
    
    public function __construct(SmsService $smsService, EmailService $emailService)
    {
        $this->smsService = $smsService;
        $this->emailService = $emailService;
    }
    
    /**
     * Send writeoff notification to member
     */
    public function sendWriteoffNotification(LoanWriteOff $writeOff, string $stage = 'initiated')
    {
        try {
            Log::info("📧 Sending writeoff notification for loan {$writeOff->loan_id}, stage: {$stage}");
            
            // Get client information
            $client = $this->getClientInformation($writeOff->client_number);
            
            if (!$client) {
                Log::warning("Client information not found for writeoff {$writeOff->id}");
                return false;
            }
            
            // Generate personalized message
            $messageData = $this->prepareMessageData($writeOff, $client, $stage);
            
            $communicationResults = [];
            
            // Send SMS notification
            if ($client->mobile_phone_number) {
                $smsResult = $this->sendSmsNotification($writeOff, $client, $messageData, $stage);
                $communicationResults['sms'] = $smsResult;
            }
            
            // Send Email notification
            if ($client->email) {
                $emailResult = $this->sendEmailNotification($writeOff, $client, $messageData, $stage);
                $communicationResults['email'] = $emailResult;
            }
            
            // Generate formal letter
            $letterResult = $this->generateFormalLetter($writeOff, $client, $messageData, $stage);
            $communicationResults['letter'] = $letterResult;
            
            // Update writeoff with communication status
            $this->updateCommunicationStatus($writeOff, $communicationResults);
            
            Log::info("✅ Writeoff notification sent successfully for loan {$writeOff->loan_id}");
            return $communicationResults;
            
        } catch (\Exception $e) {
            Log::error("❌ Error sending writeoff notification: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get client information from database
     */
    private function getClientInformation($clientNumber)
    {
        return DB::table('clients')
            ->where('client_number', $clientNumber)
            ->select([
                'id', 'client_number', 'first_name', 'last_name', 
                'mobile_phone_number', 'email', 'address',
                'gender', 'date_of_birth'
            ])
            ->first();
    }
    
    /**
     * Prepare personalized message data
     */
    private function prepareMessageData(LoanWriteOff $writeOff, $client, string $stage)
    {
        $institution = DB::table('institutions')->first();
        $organizationName = $institution->name ?? 'SACCOS';
            
        $memberName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
        
        $baseData = [
            'organization_name' => $organizationName,
            'member_name' => $memberName,
            'client_number' => $client->client_number,
            'loan_id' => $writeOff->loan_id,
            'writeoff_amount' => number_format($writeOff->total_amount, 2),
            'writeoff_date' => $writeOff->write_off_date->format('d/m/Y'),
            'contact_number' => '+255 123 456 789', // From system config
            'today_date' => now()->format('d/m/Y')
        ];
        
        // Stage-specific data
        switch ($stage) {
            case 'initiated':
                $baseData['stage_title'] = 'Loan Write-off Notice';
                $baseData['stage_message'] = 'Your loan has been recommended for write-off due to prolonged non-payment.';
                $baseData['action_required'] = 'Contact us immediately to discuss payment arrangements or dispute this action.';
                $baseData['urgency'] = 'URGENT';
                break;
                
            case 'approved':
                $baseData['stage_title'] = 'Loan Write-off Confirmation';
                $baseData['stage_message'] = 'Your loan has been officially written off from our books.';
                $baseData['action_required'] = 'This does not relieve you of the obligation to pay. We may pursue recovery through available legal means.';
                $baseData['urgency'] = 'IMPORTANT';
                break;
                
            case 'recovery_initiated':
                $baseData['stage_title'] = 'Debt Recovery Notice';
                $baseData['stage_message'] = 'We are initiating recovery procedures for your written-off loan.';
                $baseData['action_required'] = 'Contact us to arrange payment and avoid legal action.';
                $baseData['urgency'] = 'FINAL NOTICE';
                break;
        }
        
        return $baseData;
    }
    
    /**
     * Send SMS notification
     */
    private function sendSmsNotification(LoanWriteOff $writeOff, $client, array $messageData, string $stage)
    {
        try {
            $template = $this->getSmsTemplate($stage);
            $message = $this->personalizeMessage($template, $messageData);
            
            // Send SMS
            $result = $this->smsService->sendSms(
                $client->mobile_phone_number,
                $message
            );
            
            // Log communication
            $this->logCommunication($writeOff, 'sms', $message, $result);
            
            return [
                'success' => $result['success'] ?? false,
                'message' => $message,
                'details' => $result
            ];
            
        } catch (\Exception $e) {
            Log::error("Error sending SMS notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification(LoanWriteOff $writeOff, $client, array $messageData, string $stage)
    {
        try {
            $template = $this->getEmailTemplate($stage);
            $subject = $messageData['stage_title'] . ' - Loan ' . $writeOff->loan_id;
            $body = $this->personalizeMessage($template, $messageData);
            
            // Send Email
            $result = $this->emailService->sendEmail(
                $client->email,
                $subject,
                $body,
                'loan_writeoff'
            );
            
            // Log communication
            $this->logCommunication($writeOff, 'email', $body, $result, $subject);
            
            return [
                'success' => $result['success'] ?? false,
                'subject' => $subject,
                'body' => $body,
                'details' => $result
            ];
            
        } catch (\Exception $e) {
            Log::error("Error sending email notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate formal letter for postal delivery
     */
    private function generateFormalLetter(LoanWriteOff $writeOff, $client, array $messageData, string $stage)
    {
        try {
            $template = $this->getLetterTemplate($stage);
            $letterContent = $this->personalizeMessage($template, $messageData);
            
            // Generate letter file
            $filename = "writeoff_letter_{$writeOff->loan_id}_{$stage}_" . now()->format('Y_m_d') . ".pdf";
            $filePath = storage_path("app/communications/letters/{$filename}");
            
            // Ensure directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            
            // Generate PDF (simplified - in real implementation would use PDF library)
            file_put_contents($filePath, $letterContent);
            
            // Log communication
            $this->logCommunication($writeOff, 'letter', $letterContent, [
                'success' => true,
                'file_path' => $filePath,
                'filename' => $filename
            ]);
            
            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => $filename,
                'content' => $letterContent
            ];
            
        } catch (\Exception $e) {
            Log::error("Error generating formal letter: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Log communication in database
     */
    private function logCommunication(LoanWriteOff $writeOff, string $type, string $content, array $result, string $subject = null)
    {
        try {
            WriteoffMemberCommunication::create([
                'writeoff_id' => $writeOff->id,
                'loan_id' => $writeOff->loan_id,
                'client_number' => $writeOff->client_number,
                'communication_type' => $type,
                'message_content' => $subject ? "{$subject}\n\n{$content}" : $content,
                'sent_date' => now(),
                'delivery_status' => $result['success'] ? 'delivered' : 'failed',
                'delivery_details' => json_encode($result),
                'template_used' => $type . '_writeoff_notification',
                'sent_by' => auth()->id() ?? 1
            ]);
        } catch (\Exception $e) {
            Log::error("Error logging communication: " . $e->getMessage());
        }
    }
    
    /**
     * Update writeoff with communication status
     */
    private function updateCommunicationStatus(LoanWriteOff $writeOff, array $results)
    {
        $notifications = [];
        foreach ($results as $type => $result) {
            $notifications[] = [
                'type' => $type,
                'sent' => $result['success'] ?? false,
                'timestamp' => now()->toISOString()
            ];
        }
        
        $writeOff->update([
            'member_notification_sent' => json_encode($notifications)
        ]);
    }
    
    /**
     * Get SMS template for stage
     */
    private function getSmsTemplate(string $stage): string
    {
        $templates = [
            'initiated' => "{{urgency}}: {{organization_name}} - {{stage_message}} Loan {{loan_id}} for TZS {{writeoff_amount}}. {{action_required}} Call {{contact_number}} by {{today_date}}.",
            
            'approved' => "{{organization_name}} NOTICE: {{stage_message}} Loan {{loan_id}} (TZS {{writeoff_amount}}) on {{writeoff_date}}. {{action_required}} Contact: {{contact_number}}",
            
            'recovery_initiated' => "{{urgency}} - {{organization_name}}: {{stage_message}} Loan {{loan_id}} (TZS {{writeoff_amount}}). {{action_required}} Call {{contact_number}} immediately."
        ];
        
        return $templates[$stage] ?? $templates['initiated'];
    }
    
    /**
     * Get email template for stage
     */
    private function getEmailTemplate(string $stage): string
    {
        $templates = [
            'initiated' => "
Dear {{member_name}},

RE: {{stage_title}} - Loan Account {{loan_id}}

We regret to inform you that your loan account {{loan_id}} in the amount of TZS {{writeoff_amount}} has been recommended for write-off due to prolonged non-payment despite our repeated collection efforts.

{{stage_message}}

DETAILS:
- Client Number: {{client_number}}
- Loan ID: {{loan_id}}
- Amount: TZS {{writeoff_amount}}
- Date: {{writeoff_date}}

{{action_required}}

Please contact us immediately at {{contact_number}} to discuss this matter and explore possible payment arrangements.

Failure to respond within 7 days will result in the loan being formally written off, which may affect your credit rating and future borrowing capacity.

Yours faithfully,
Credit Management Team
{{organization_name}}

Date: {{today_date}}
",

            'approved' => "
Dear {{member_name}},

RE: {{stage_title}} - Loan Account {{loan_id}}

This is to formally notify you that your loan account {{loan_id}} in the amount of TZS {{writeoff_amount}} has been officially written off from our books as of {{writeoff_date}}.

{{stage_message}}

LOAN DETAILS:
- Client Number: {{client_number}}
- Loan ID: {{loan_id}}
- Written Off Amount: TZS {{writeoff_amount}}
- Write-off Date: {{writeoff_date}}

IMPORTANT NOTICE:
{{action_required}}

We reserve the right to pursue recovery through:
- Legal proceedings
- Debt collection agencies
- Asset recovery where applicable
- Credit bureau reporting

For any queries or to discuss payment arrangements, please contact us at {{contact_number}}.

Yours faithfully,
Credit Management Team
{{organization_name}}

Date: {{today_date}}
",

            'recovery_initiated' => "
Dear {{member_name}},

RE: {{stage_title}} - Loan Account {{loan_id}}

Further to our previous communications regarding your written-off loan account {{loan_id}}, we are now initiating formal debt recovery procedures.

{{stage_message}}

LOAN DETAILS:
- Client Number: {{client_number}}
- Loan ID: {{loan_id}}
- Outstanding Amount: TZS {{writeoff_amount}}
- Write-off Date: {{writeoff_date}}

RECOVERY ACTIONS:
We will be pursuing recovery through all available legal means including but not limited to:
- Asset attachment and sale
- Garnishment orders
- Legal proceedings
- Credit bureau reporting

{{action_required}}

This is your final opportunity to settle this matter amicably before legal action commences.

Contact us immediately at {{contact_number}}.

Yours faithfully,
Legal & Recovery Team
{{organization_name}}

Date: {{today_date}}
"
        ];
        
        return $templates[$stage] ?? $templates['initiated'];
    }
    
    /**
     * Get letter template for stage
     */
    private function getLetterTemplate(string $stage): string
    {
        // Similar to email template but with formal letterhead formatting
        return $this->getEmailTemplate($stage);
    }
    
    /**
     * Personalize message with data
     */
    private function personalizeMessage(string $template, array $data): string
    {
        $message = $template;
        
        foreach ($data as $key => $value) {
            $message = str_replace('{{' . $key . '}}', $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Send SMS to a phone number
     * Public method for sending SMS from other services
     *
     * @param string $phoneNumber The recipient phone number
     * @param string $message The SMS message content
     * @param string $type Optional message type for logging
     * @return array Result with success status and details
     */
    public function sendSms(string $phoneNumber, string $message, string $type = 'general'): array
    {
        try {
            if (empty($phoneNumber)) {
                Log::warning("SMS not sent: Empty phone number provided");
                return ['success' => false, 'error' => 'Empty phone number'];
            }

            $result = $this->smsService->sendSms($phoneNumber, $message);

            Log::info("SMS sent to {$phoneNumber}", [
                'type' => $type,
                'success' => $result['success'] ?? false,
            ]);

            return [
                'success' => $result['success'] ?? false,
                'message' => $message,
                'details' => $result
            ];

        } catch (\Exception $e) {
            Log::error("Error sending SMS to {$phoneNumber}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send Email to an email address
     * Public method for sending emails from other services
     *
     * @param string $email The recipient email address
     * @param string $subject The email subject
     * @param string $body The email body content
     * @param string $type Optional message type for logging
     * @return array Result with success status and details
     */
    public function sendEmail(string $email, string $subject, string $body, string $type = 'general'): array
    {
        try {
            if (empty($email)) {
                Log::warning("Email not sent: Empty email address provided");
                return ['success' => false, 'error' => 'Empty email address'];
            }

            $result = $this->emailService->sendEmail($email, $subject, $body, $type);

            Log::info("Email sent to {$email}", [
                'type' => $type,
                'subject' => $subject,
                'success' => $result['success'] ?? false,
            ]);

            return [
                'success' => $result['success'] ?? false,
                'subject' => $subject,
                'details' => $result
            ];

        } catch (\Exception $e) {
            Log::error("Error sending email to {$email}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send recovery reminder notifications
     */
    public function sendRecoveryReminders()
    {
        try {
            Log::info("📧 Starting recovery reminder notifications");
            
            // Get written-off loans that need recovery reminders
            $writeOffs = LoanWriteOff::where('status', 'approved')
                ->where('recovery_status', '!=', 'full')
                ->where('write_off_date', '<=', now()->subMonths(1)) // At least 1 month old
                ->whereDoesntHave('memberCommunications', function($query) {
                    $query->where('communication_type', 'recovery_reminder')
                        ->where('sent_date', '>=', now()->subWeeks(2)); // No reminder in last 2 weeks
                })
                ->limit(50) // Process in batches
                ->get();
            
            $sent = 0;
            foreach ($writeOffs as $writeOff) {
                if ($this->sendWriteoffNotification($writeOff, 'recovery_initiated')) {
                    $sent++;
                }
            }
            
            Log::info("✅ Sent {$sent} recovery reminder notifications");
            return $sent;
            
        } catch (\Exception $e) {
            Log::error("❌ Error sending recovery reminders: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate member communication report
     */
    public function generateCommunicationReport($dateFrom, $dateTo)
    {
        return WriteoffMemberCommunication::whereBetween('sent_date', [$dateFrom, $dateTo])
            ->selectRaw('
                communication_type,
                COUNT(*) as total_sent,
                SUM(CASE WHEN delivery_status = "delivered" THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN delivery_status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN member_acknowledged = true THEN 1 ELSE 0 END) as acknowledged
            ')
            ->groupBy('communication_type')
            ->get()
            ->map(function($item) {
                $item->delivery_rate = $item->total_sent > 0 
                    ? round(($item->delivered / $item->total_sent) * 100, 2) : 0;
                $item->acknowledgment_rate = $item->delivered > 0 
                    ? round(($item->acknowledged / $item->delivered) * 100, 2) : 0;
                return $item;
            });
    }
}