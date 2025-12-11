<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class EmailSnoozeService
{
    /**
     * Snooze an email until a specific time
     */
    public function snoozeEmail($emailId, $userId, $snoozeUntil)
    {
        try {
            DB::beginTransaction();
            
            // Get the email
            $email = DB::table('emails')->where('id', $emailId)->first();
            
            if (!$email) {
                throw new Exception('Email not found');
            }
            
            // Store the original folder
            $originalFolder = $email->folder;
            
            // Create snooze record
            DB::table('email_snoozes')->insert([
                'email_id' => $emailId,
                'user_id' => $userId,
                'snooze_until' => $snoozeUntil,
                'original_folder' => $originalFolder,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Move email to snoozed state (remove from current folder)
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'folder' => 'snoozed',
                    'updated_at' => now()
                ]);
            
            DB::commit();
            
            Log::channel('email')->info('Email snoozed', [
                'email_id' => $emailId,
                'user_id' => $userId,
                'snooze_until' => $snoozeUntil
            ]);
            
            return [
                'success' => true,
                'message' => 'Email snoozed successfully'
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Email snooze failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to snooze email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get snoozed emails for a user
     */
    public function getSnoozedEmails($userId)
    {
        return DB::table('email_snoozes')
            ->join('emails', 'email_snoozes.email_id', '=', 'emails.id')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where('email_snoozes.user_id', $userId)
            ->where('email_snoozes.is_active', true)
            ->select(
                'emails.*',
                'senders.name as sender_name',
                'senders.email as sender_email',
                'email_snoozes.snooze_until',
                'email_snoozes.id as snooze_id'
            )
            ->orderBy('email_snoozes.snooze_until', 'asc')
            ->get();
    }
    
    /**
     * Unsnooze emails that have reached their snooze time
     */
    public function processExpiredSnoozes()
    {
        $expiredSnoozes = DB::table('email_snoozes')
            ->where('is_active', true)
            ->where('snooze_until', '<=', now())
            ->get();
        
        $processedCount = 0;
        
        foreach ($expiredSnoozes as $snooze) {
            try {
                DB::beginTransaction();
                
                // Restore email to original folder
                DB::table('emails')
                    ->where('id', $snooze->email_id)
                    ->update([
                        'folder' => $snooze->original_folder,
                        'updated_at' => now()
                    ]);
                
                // Mark snooze as inactive
                DB::table('email_snoozes')
                    ->where('id', $snooze->id)
                    ->update([
                        'is_active' => false,
                        'updated_at' => now()
                    ]);
                
                DB::commit();
                $processedCount++;
                
                // TODO: Send notification to user that email is unsnoozed
                
            } catch (Exception $e) {
                DB::rollback();
                Log::error('Failed to unsnooze email: ' . $e->getMessage());
            }
        }
        
        if ($processedCount > 0) {
            Log::channel('email')->info("Processed {$processedCount} expired snoozes");
        }
        
        return $processedCount;
    }
    
    /**
     * Cancel a snooze
     */
    public function cancelSnooze($snoozeId, $userId)
    {
        try {
            DB::beginTransaction();
            
            $snooze = DB::table('email_snoozes')
                ->where('id', $snoozeId)
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
            
            if (!$snooze) {
                throw new Exception('Snooze not found or already expired');
            }
            
            // Restore email to original folder
            DB::table('emails')
                ->where('id', $snooze->email_id)
                ->update([
                    'folder' => $snooze->original_folder,
                    'updated_at' => now()
                ]);
            
            // Mark snooze as inactive
            DB::table('email_snoozes')
                ->where('id', $snoozeId)
                ->update([
                    'is_active' => false,
                    'updated_at' => now()
                ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Snooze cancelled successfully'
            ];
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Cancel snooze failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to cancel snooze: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get predefined snooze options
     */
    public function getSnoozeOptions()
    {
        $now = Carbon::now();
        
        return [
            [
                'label' => 'Later today',
                'value' => $now->copy()->addHours(3)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->addHours(3)->format('g:i A')
            ],
            [
                'label' => 'Tomorrow',
                'value' => $now->copy()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->addDay()->setTime(9, 0)->format('D, g:i A')
            ],
            [
                'label' => 'This weekend',
                'value' => $now->copy()->next('Saturday')->setTime(9, 0)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->next('Saturday')->setTime(9, 0)->format('D, M j')
            ],
            [
                'label' => 'Next week',
                'value' => $now->copy()->next('Monday')->setTime(9, 0)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->next('Monday')->setTime(9, 0)->format('D, M j')
            ],
            [
                'label' => 'Next month',
                'value' => $now->copy()->addMonth()->startOfMonth()->setTime(9, 0)->format('Y-m-d H:i:s'),
                'time' => $now->copy()->addMonth()->startOfMonth()->setTime(9, 0)->format('M j')
            ]
        ];
    }
    
    /**
     * Check if email is snoozed
     */
    public function isEmailSnoozed($emailId, $userId)
    {
        return DB::table('email_snoozes')
            ->where('email_id', $emailId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }
}