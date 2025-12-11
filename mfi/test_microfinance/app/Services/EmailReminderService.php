<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailReminderService
{
    /**
     * Create a reminder for an email
     */
    public function createReminder($userId, $emailId, $data)
    {
        try {
            $reminderId = DB::table('email_reminders')->insertGetId([
                'user_id' => $userId,
                'email_id' => $emailId,
                'type' => $data['type'] ?? 'follow_up',
                'note' => $data['note'] ?? null,
                'remind_at' => $data['remind_at'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'reminder_id' => $reminderId,
                'message' => 'Reminder created successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create email reminder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create reminder'
            ];
        }
    }
    
    /**
     * Get user's reminders
     */
    public function getUserReminders($userId, $includeCompleted = false)
    {
        $query = DB::table('email_reminders as r')
            ->join('emails as e', 'r.email_id', '=', 'e.id')
            ->leftJoin('users as senders', 'e.sender_id', '=', 'senders.id')
            ->where('r.user_id', $userId)
            ->select(
                'r.*',
                'e.subject as email_subject',
                'e.sender_email',
                'e.created_at as email_date',
                'senders.name as sender_name'
            );
            
        if (!$includeCompleted) {
            $query->where('r.is_completed', false)
                  ->where(function($q) {
                      $q->where('r.is_snoozed', false)
                        ->orWhere('r.snoozed_until', '<=', now());
                  });
        }
        
        return $query->orderBy('r.remind_at', 'asc')->get();
    }
    
    /**
     * Get upcoming reminders
     */
    public function getUpcomingReminders($userId, $hours = 24)
    {
        return DB::table('email_reminders as r')
            ->join('emails as e', 'r.email_id', '=', 'e.id')
            ->where('r.user_id', $userId)
            ->where('r.is_completed', false)
            ->where('r.is_snoozed', false)
            ->whereBetween('r.remind_at', [now(), now()->addHours($hours)])
            ->select('r.*', 'e.subject as email_subject')
            ->orderBy('r.remind_at', 'asc')
            ->get();
    }
    
    /**
     * Get overdue reminders
     */
    public function getOverdueReminders($userId)
    {
        return DB::table('email_reminders as r')
            ->join('emails as e', 'r.email_id', '=', 'e.id')
            ->where('r.user_id', $userId)
            ->where('r.is_completed', false)
            ->where('r.is_snoozed', false)
            ->where('r.remind_at', '<', now())
            ->select('r.*', 'e.subject as email_subject')
            ->orderBy('r.remind_at', 'desc')
            ->get();
    }
    
    /**
     * Mark reminder as completed
     */
    public function completeReminder($reminderId, $userId)
    {
        try {
            $updated = DB::table('email_reminders')
                ->where('id', $reminderId)
                ->where('user_id', $userId)
                ->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                    'updated_at' => now()
                ]);
                
            return [
                'success' => $updated > 0,
                'message' => $updated > 0 ? 'Reminder completed' : 'Reminder not found'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to complete reminder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to complete reminder'
            ];
        }
    }
    
    /**
     * Snooze a reminder
     */
    public function snoozeReminder($reminderId, $userId, $snoozeUntil)
    {
        try {
            $reminder = DB::table('email_reminders')
                ->where('id', $reminderId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$reminder) {
                throw new \Exception('Reminder not found');
            }
            
            DB::table('email_reminders')
                ->where('id', $reminderId)
                ->update([
                    'is_snoozed' => true,
                    'snoozed_until' => $snoozeUntil,
                    'snooze_count' => DB::raw('snooze_count + 1'),
                    'updated_at' => now()
                ]);
                
            return [
                'success' => true,
                'message' => 'Reminder snoozed until ' . Carbon::parse($snoozeUntil)->format('M d, Y g:i A')
            ];
        } catch (\Exception $e) {
            Log::error('Failed to snooze reminder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to snooze reminder'
            ];
        }
    }
    
    /**
     * Update reminder
     */
    public function updateReminder($reminderId, $userId, $data)
    {
        try {
            $updated = DB::table('email_reminders')
                ->where('id', $reminderId)
                ->where('user_id', $userId)
                ->update([
                    'type' => $data['type'] ?? DB::raw('type'),
                    'note' => $data['note'] ?? DB::raw('note'),
                    'remind_at' => $data['remind_at'] ?? DB::raw('remind_at'),
                    'updated_at' => now()
                ]);
                
            return [
                'success' => $updated > 0,
                'message' => $updated > 0 ? 'Reminder updated' : 'Reminder not found'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update reminder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update reminder'
            ];
        }
    }
    
    /**
     * Delete reminder
     */
    public function deleteReminder($reminderId, $userId)
    {
        try {
            $deleted = DB::table('email_reminders')
                ->where('id', $reminderId)
                ->where('user_id', $userId)
                ->delete();
                
            return [
                'success' => $deleted > 0,
                'message' => $deleted > 0 ? 'Reminder deleted' : 'Reminder not found'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete reminder: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete reminder'
            ];
        }
    }
    
    /**
     * Get suggested reminder times
     */
    public function getSuggestedTimes()
    {
        $now = Carbon::now();
        
        return [
            [
                'label' => 'In 1 hour',
                'value' => $now->copy()->addHour()->startOfHour()
            ],
            [
                'label' => 'In 3 hours',
                'value' => $now->copy()->addHours(3)->startOfHour()
            ],
            [
                'label' => 'Tomorrow morning',
                'value' => $now->copy()->addDay()->setTime(9, 0)
            ],
            [
                'label' => 'Tomorrow afternoon',
                'value' => $now->copy()->addDay()->setTime(14, 0)
            ],
            [
                'label' => 'Next week',
                'value' => $now->copy()->addWeek()->setTime(9, 0)
            ],
            [
                'label' => 'In 2 weeks',
                'value' => $now->copy()->addWeeks(2)->setTime(9, 0)
            ],
            [
                'label' => 'Next month',
                'value' => $now->copy()->addMonth()->setTime(9, 0)
            ]
        ];
    }
    
    /**
     * Get snooze options
     */
    public function getSnoozeOptions()
    {
        $now = Carbon::now();
        
        return [
            [
                'label' => '10 minutes',
                'value' => $now->copy()->addMinutes(10)
            ],
            [
                'label' => '1 hour',
                'value' => $now->copy()->addHour()
            ],
            [
                'label' => '3 hours',
                'value' => $now->copy()->addHours(3)
            ],
            [
                'label' => 'Tomorrow',
                'value' => $now->copy()->addDay()->setTime(9, 0)
            ],
            [
                'label' => 'Next week',
                'value' => $now->copy()->addWeek()
            ]
        ];
    }
    
    /**
     * Check if email has reminders
     */
    public function hasReminders($emailId, $userId)
    {
        return DB::table('email_reminders')
            ->where('email_id', $emailId)
            ->where('user_id', $userId)
            ->where('is_completed', false)
            ->exists();
    }
    
    /**
     * Get email reminders
     */
    public function getEmailReminders($emailId, $userId)
    {
        return DB::table('email_reminders')
            ->where('email_id', $emailId)
            ->where('user_id', $userId)
            ->orderBy('remind_at', 'asc')
            ->get();
    }
}