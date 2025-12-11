<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class UndoSendService
{
    protected $undoWindowSeconds = 30; // 30 seconds undo window
    
    /**
     * Queue an email for delayed sending (with undo capability)
     */
    public function queueEmailForSending($emailId, $userId)
    {
        try {
            $undoUntil = Carbon::now()->addSeconds($this->undoWindowSeconds);
            
            // Update email with undo window
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'undo_send_until' => $undoUntil,
                    'updated_at' => now()
                ]);
            
            // Store in cache for quick access
            $cacheKey = "undo_send_{$emailId}";
            Cache::put($cacheKey, [
                'email_id' => $emailId,
                'user_id' => $userId,
                'send_at' => $undoUntil
            ], $this->undoWindowSeconds);
            
            Log::channel('email')->info('Email queued with undo capability', [
                'email_id' => $emailId,
                'undo_until' => $undoUntil->format('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'undo_until' => $undoUntil,
                'seconds_remaining' => $this->undoWindowSeconds
            ];
            
        } catch (Exception $e) {
            Log::error('Failed to queue email for undo: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to enable undo send: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Undo a sent email
     */
    public function undoSend($emailId, $userId)
    {
        try {
            DB::beginTransaction();
            
            // Check if email can be undone
            $email = DB::table('emails')
                ->where('id', $emailId)
                ->where('sender_id', $userId)
                ->first();
            
            if (!$email) {
                throw new Exception('Email not found');
            }
            
            if (!$email->undo_send_until || Carbon::parse($email->undo_send_until)->isPast()) {
                throw new Exception('Undo window has expired');
            }
            
            // Move email back to drafts
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'folder' => 'drafts',
                    'is_sent' => false,
                    'sent_at' => null,
                    'undo_send_until' => null,
                    'updated_at' => now()
                ]);
            
            // Delete recipient's copy if exists
            DB::table('emails')
                ->where('sender_id', $userId)
                ->where('recipient_email', $email->recipient_email)
                ->where('subject', $email->subject)
                ->where('folder', 'inbox')
                ->where('created_at', '>=', Carbon::now()->subMinutes(1))
                ->delete();
            
            // Clear cache
            Cache::forget("undo_send_{$emailId}");
            
            DB::commit();
            
            Log::channel('email')->info('Email send undone', [
                'email_id' => $emailId
            ]);
            
            return [
                'success' => true,
                'message' => 'Email moved back to drafts'
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Undo send failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process emails that have passed undo window
     */
    public function processUndoQueue()
    {
        $expiredEmails = DB::table('emails')
            ->whereNotNull('undo_send_until')
            ->where('undo_send_until', '<=', now())
            ->where('is_sent', false)
            ->get();
        
        $processedCount = 0;
        
        foreach ($expiredEmails as $email) {
            try {
                // Actually send the email now
                $emailService = new EmailService();
                
                $result = $emailService->sendEmail([
                    'to' => $email->recipient_email,
                    'cc' => $email->cc,
                    'bcc' => $email->bcc,
                    'subject' => $email->subject,
                    'body' => $email->body
                ]);
                
                if ($result['success']) {
                    // Update email status
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update([
                            'is_sent' => true,
                            'sent_at' => now(),
                            'undo_send_until' => null,
                            'updated_at' => now()
                        ]);
                    
                    $processedCount++;
                }
                
            } catch (Exception $e) {
                Log::error('Failed to send email after undo window: ' . $e->getMessage(), [
                    'email_id' => $email->id
                ]);
            }
        }
        
        if ($processedCount > 0) {
            Log::channel('email')->info("Processed {$processedCount} emails after undo window");
        }
        
        return $processedCount;
    }
    
    /**
     * Get emails currently in undo window
     */
    public function getUndoableEmails($userId)
    {
        return DB::table('emails')
            ->where('sender_id', $userId)
            ->whereNotNull('undo_send_until')
            ->where('undo_send_until', '>', now())
            ->orderBy('undo_send_until', 'asc')
            ->get()
            ->map(function ($email) {
                $email->seconds_remaining = Carbon::now()->diffInSeconds(Carbon::parse($email->undo_send_until));
                return $email;
            });
    }
    
    /**
     * Check if email is undoable
     */
    public function isUndoable($emailId)
    {
        $email = DB::table('emails')->where('id', $emailId)->first();
        
        if (!$email || !$email->undo_send_until) {
            return false;
        }
        
        return Carbon::parse($email->undo_send_until)->isFuture();
    }
    
    /**
     * Get undo window duration
     */
    public function getUndoWindowSeconds()
    {
        return $this->undoWindowSeconds;
    }
    
    /**
     * Set undo window duration
     */
    public function setUndoWindowSeconds($seconds)
    {
        $this->undoWindowSeconds = max(5, min(120, $seconds)); // Between 5 and 120 seconds
    }
}