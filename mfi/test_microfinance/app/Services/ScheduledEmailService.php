<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class ScheduledEmailService
{
    protected $emailService;
    
    public function __construct()
    {
        $this->emailService = new EmailService();
    }
    
    /**
     * Schedule an email to be sent later
     */
    public function scheduleEmail($data)
    {
        try {
            DB::beginTransaction();
            
            // Validate scheduled time is in the future
            $scheduledAt = Carbon::parse($data['scheduled_at']);
            if ($scheduledAt->isPast()) {
                throw new Exception('Scheduled time must be in the future');
            }
            
            // Create scheduled email record
            $scheduledEmailId = DB::table('scheduled_emails')->insertGetId([
                'user_id' => Auth::id(),
                'recipient_email' => $data['to'],
                'cc' => $data['cc'] ?? null,
                'bcc' => $data['bcc'] ?? null,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'scheduled_at' => $scheduledAt,
                'attachments' => isset($data['attachments']) ? json_encode($data['attachments']) : null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            
            Log::channel('email')->info('Email scheduled', [
                'scheduled_email_id' => $scheduledEmailId,
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'message' => 'Email scheduled successfully',
                'scheduled_at' => $scheduledAt->format('M j, Y \a\t g:i A'),
                'id' => $scheduledEmailId
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Email scheduling failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to schedule email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send scheduled emails that are due
     */
    public function processScheduledEmails()
    {
        $dueEmails = DB::table('scheduled_emails')
            ->where('is_sent', false)
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();
        
        $sentCount = 0;
        $failedCount = 0;
        
        foreach ($dueEmails as $scheduledEmail) {
            try {
                // Prepare email data
                $emailData = [
                    'to' => $scheduledEmail->recipient_email,
                    'cc' => $scheduledEmail->cc,
                    'bcc' => $scheduledEmail->bcc,
                    'subject' => $scheduledEmail->subject,
                    'body' => $scheduledEmail->body
                ];
                
                // Send the email
                $result = $this->emailService->sendEmail($emailData);
                
                if ($result['success']) {
                    // Mark as sent
                    DB::table('scheduled_emails')
                        ->where('id', $scheduledEmail->id)
                        ->update([
                            'is_sent' => true,
                            'sent_at' => now(),
                            'status' => 'sent',
                            'updated_at' => now()
                        ]);
                    
                    $sentCount++;
                    
                    Log::channel('email')->info('Scheduled email sent', [
                        'scheduled_email_id' => $scheduledEmail->id
                    ]);
                } else {
                    throw new Exception($result['message']);
                }
                
            } catch (Exception $e) {
                $failedCount++;
                
                // Mark as failed
                DB::table('scheduled_emails')
                    ->where('id', $scheduledEmail->id)
                    ->update([
                        'status' => 'failed',
                        'updated_at' => now()
                    ]);
                
                Log::error('Failed to send scheduled email: ' . $e->getMessage(), [
                    'scheduled_email_id' => $scheduledEmail->id
                ]);
            }
        }
        
        if ($sentCount > 0 || $failedCount > 0) {
            Log::channel('email')->info("Processed scheduled emails - Sent: {$sentCount}, Failed: {$failedCount}");
        }
        
        return [
            'sent' => $sentCount,
            'failed' => $failedCount
        ];
    }
    
    /**
     * Get scheduled emails for a user
     */
    public function getScheduledEmails($userId)
    {
        return DB::table('scheduled_emails')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }
    
    /**
     * Cancel a scheduled email
     */
    public function cancelScheduledEmail($scheduledEmailId, $userId)
    {
        try {
            $scheduledEmail = DB::table('scheduled_emails')
                ->where('id', $scheduledEmailId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();
            
            if (!$scheduledEmail) {
                throw new Exception('Scheduled email not found or already sent');
            }
            
            DB::table('scheduled_emails')
                ->where('id', $scheduledEmailId)
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now()
                ]);
            
            return [
                'success' => true,
                'message' => 'Scheduled email cancelled successfully'
            ];
            
        } catch (Exception $e) {
            Log::error('Cancel scheduled email failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to cancel scheduled email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update a scheduled email
     */
    public function updateScheduledEmail($scheduledEmailId, $userId, $data)
    {
        try {
            DB::beginTransaction();
            
            $scheduledEmail = DB::table('scheduled_emails')
                ->where('id', $scheduledEmailId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->first();
            
            if (!$scheduledEmail) {
                throw new Exception('Scheduled email not found or already sent');
            }
            
            // Validate new scheduled time if provided
            if (isset($data['scheduled_at'])) {
                $scheduledAt = Carbon::parse($data['scheduled_at']);
                if ($scheduledAt->isPast()) {
                    throw new Exception('Scheduled time must be in the future');
                }
                $data['scheduled_at'] = $scheduledAt;
            }
            
            // Update the scheduled email
            DB::table('scheduled_emails')
                ->where('id', $scheduledEmailId)
                ->update(array_merge($data, [
                    'updated_at' => now()
                ]));
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Scheduled email updated successfully'
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Update scheduled email failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to update scheduled email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get suggested scheduling times
     */
    public function getScheduleSuggestions()
    {
        $now = Carbon::now();
        
        return [
            [
                'label' => 'Tomorrow morning',
                'value' => $now->copy()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->addDay()->setTime(9, 0)->format('D, M j \a\t g:i A')
            ],
            [
                'label' => 'Tomorrow afternoon',
                'value' => $now->copy()->addDay()->setTime(14, 0)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->addDay()->setTime(14, 0)->format('D, M j \a\t g:i A')
            ],
            [
                'label' => 'Monday morning',
                'value' => $now->copy()->next('Monday')->setTime(9, 0)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->next('Monday')->setTime(9, 0)->format('D, M j \a\t g:i A')
            ],
            [
                'label' => 'In 1 hour',
                'value' => $now->copy()->addHour()->format('Y-m-d H:i:s'),
                'time' => $now->copy()->addHour()->format('g:i A')
            ],
            [
                'label' => 'In 3 hours',
                'value' => $now->copy()->addHours(3)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->addHours(3)->format('g:i A')
            ]
        ];
    }
}