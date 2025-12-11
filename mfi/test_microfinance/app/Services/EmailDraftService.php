<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class EmailDraftService
{
    public function saveDraft($draftData, $userId)
    {
        try {
            // Check if draft already exists
            $existingDraft = DB::table('email_drafts')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
            
            if ($existingDraft) {
                // Update existing draft
                DB::table('email_drafts')
                    ->where('id', $existingDraft->id)
                    ->update([
                        'to' => $draftData['to'] ?? '',
                        'cc' => $draftData['cc'] ?? '',
                        'bcc' => $draftData['bcc'] ?? '',
                        'subject' => $draftData['subject'] ?? '',
                        'body' => $draftData['body'] ?? '',
                        'attachments' => json_encode($draftData['attachments'] ?? []),
                        'priority' => $draftData['priority'] ?? 'normal',
                        'request_read_receipt' => $draftData['request_read_receipt'] ?? false,
                        'request_delivery_receipt' => $draftData['request_delivery_receipt'] ?? false,
                        'enable_tracking' => $draftData['enable_tracking'] ?? false,
                        'is_scheduled' => $draftData['is_scheduled'] ?? false,
                        'scheduled_date' => $draftData['scheduled_date'] ?? null,
                        'scheduled_time' => $draftData['scheduled_time'] ?? null,
                        'updated_at' => now()
                    ]);
                
                return $existingDraft->id;
            } else {
                // Create new draft
                $draftId = DB::table('email_drafts')->insertGetId([
                    'user_id' => $userId,
                    'to' => $draftData['to'] ?? '',
                    'cc' => $draftData['cc'] ?? '',
                    'bcc' => $draftData['bcc'] ?? '',
                    'subject' => $draftData['subject'] ?? '',
                    'body' => $draftData['body'] ?? '',
                    'attachments' => json_encode($draftData['attachments'] ?? []),
                    'priority' => $draftData['priority'] ?? 'normal',
                    'request_read_receipt' => $draftData['request_read_receipt'] ?? false,
                    'request_delivery_receipt' => $draftData['request_delivery_receipt'] ?? false,
                    'enable_tracking' => $draftData['enable_tracking'] ?? false,
                    'is_scheduled' => $draftData['is_scheduled'] ?? false,
                    'scheduled_date' => $draftData['scheduled_date'] ?? null,
                    'scheduled_time' => $draftData['scheduled_time'] ?? null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return $draftId;
            }
        } catch (Exception $e) {
            Log::error('[EMAIL_DRAFT_SERVICE] Failed to save draft', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function getDraft($draftId, $userId)
    {
        try {
            $draft = DB::table('email_drafts')
                ->where('id', $draftId)
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
            
            if ($draft) {
                return [
                    'id' => $draft->id,
                    'to' => $draft->to,
                    'cc' => $draft->cc,
                    'bcc' => $draft->bcc,
                    'subject' => $draft->subject,
                    'body' => $draft->body,
                    'attachments' => json_decode($draft->attachments, true) ?? [],
                    'priority' => $draft->priority,
                    'request_read_receipt' => $draft->request_read_receipt,
                    'request_delivery_receipt' => $draft->request_delivery_receipt,
                    'enable_tracking' => $draft->enable_tracking,
                    'is_scheduled' => $draft->is_scheduled,
                    'scheduled_date' => $draft->scheduled_date,
                    'scheduled_time' => $draft->scheduled_time,
                    'created_at' => $draft->created_at,
                    'updated_at' => $draft->updated_at
                ];
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('[EMAIL_DRAFT_SERVICE] Failed to get draft', [
                'draft_id' => $draftId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function getUserDrafts($userId, $limit = 10)
    {
        try {
            $drafts = DB::table('email_drafts')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();
            
            return $drafts->map(function ($draft) {
                return [
                    'id' => $draft->id,
                    'to' => $draft->to,
                    'subject' => $draft->subject,
                    'body' => $draft->body,
                    'updated_at' => $draft->updated_at
                ];
            });
        } catch (Exception $e) {
            Log::error('[EMAIL_DRAFT_SERVICE] Failed to get user drafts', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function deleteDraft($draftId, $userId)
    {
        try {
            DB::table('email_drafts')
                ->where('id', $draftId)
                ->where('user_id', $userId)
                ->update(['is_active' => false]);
            
            return true;
        } catch (Exception $e) {
            Log::error('[EMAIL_DRAFT_SERVICE] Failed to delete draft', [
                'draft_id' => $draftId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function cleanupOldDrafts($days = 30)
    {
        try {
            $cutoffDate = now()->subDays($days);
            
            DB::table('email_drafts')
                ->where('updated_at', '<', $cutoffDate)
                ->where('is_active', false)
                ->delete();
            
            Log::info('[EMAIL_DRAFT_SERVICE] Cleaned up old drafts', ['cutoff_date' => $cutoffDate]);
        } catch (Exception $e) {
            Log::error('[EMAIL_DRAFT_SERVICE] Failed to cleanup old drafts', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 