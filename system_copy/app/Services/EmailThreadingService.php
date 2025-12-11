<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EmailThreadingService
{
    /**
     * Get email conversations for a user
     */
    public function getConversations($userId, $folder = 'inbox', $page = 1, $perPage = 20)
    {
        // Get all emails for the user in the specified folder
        $emails = DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where(function($query) use ($userId) {
                $query->where('emails.recipient_id', $userId)
                      ->orWhere('emails.sender_id', $userId);
            })
            ->where('folder', $folder)
            ->select(
                'emails.*',
                'senders.name as sender_name',
                'senders.email as sender_email'
            )
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group emails into conversations
        $conversations = $this->groupIntoConversations($emails);
        
        // Sort conversations by latest message
        $sortedConversations = $conversations->sortByDesc(function ($conversation) {
            return $conversation['latest_date'];
        })->values();
        
        // Paginate conversations
        $total = $sortedConversations->count();
        $conversations = $sortedConversations->slice(($page - 1) * $perPage, $perPage);
        
        return [
            'conversations' => $conversations,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Group emails into conversations based on subject and participants
     */
    protected function groupIntoConversations($emails)
    {
        $conversations = collect();
        $processedEmails = collect();
        
        foreach ($emails as $email) {
            // Skip if already processed
            if ($processedEmails->contains('id', $email->id)) {
                continue;
            }
            
            // Find related emails
            $threadEmails = $this->findRelatedEmails($email, $emails);
            
            // Mark all thread emails as processed
            foreach ($threadEmails as $threadEmail) {
                $processedEmails->push($threadEmail);
            }
            
            // Create conversation object
            $conversation = $this->createConversationObject($threadEmails);
            $conversations->push($conversation);
        }
        
        return $conversations;
    }
    
    /**
     * Find emails that belong to the same conversation
     */
    protected function findRelatedEmails($email, $allEmails)
    {
        $relatedEmails = collect([$email]);
        $baseSubject = $this->extractBaseSubject($email->subject);
        
        // Find emails with similar subjects
        foreach ($allEmails as $otherEmail) {
            if ($otherEmail->id === $email->id) {
                continue;
            }
            
            $otherBaseSubject = $this->extractBaseSubject($otherEmail->subject);
            
            // Check if subjects match and participants overlap
            if ($baseSubject === $otherBaseSubject && $this->participantsOverlap($email, $otherEmail)) {
                $relatedEmails->push($otherEmail);
            }
        }
        
        // Sort by date
        return $relatedEmails->sortBy('created_at');
    }
    
    /**
     * Extract base subject by removing Re:, Fwd:, etc.
     */
    protected function extractBaseSubject($subject)
    {
        // Remove common prefixes
        $subject = preg_replace('/^(Re:|RE:|Fwd:|FWD:|Fw:|FW:)\s*/i', '', $subject);
        $subject = trim($subject);
        
        // Remove trailing numbers in brackets (e.g., [2])
        $subject = preg_replace('/\s*\[\d+\]\s*$/', '', $subject);
        
        return strtolower($subject);
    }
    
    /**
     * Check if two emails have overlapping participants
     */
    protected function participantsOverlap($email1, $email2)
    {
        $participants1 = $this->getParticipants($email1);
        $participants2 = $this->getParticipants($email2);
        
        return !empty(array_intersect($participants1, $participants2));
    }
    
    /**
     * Get all participants in an email
     */
    protected function getParticipants($email)
    {
        $participants = [];
        
        if ($email->sender_email) {
            $participants[] = $email->sender_email;
        }
        
        if ($email->recipient_email) {
            $participants[] = $email->recipient_email;
        }
        
        if ($email->cc) {
            $ccEmails = array_map('trim', explode(',', $email->cc));
            $participants = array_merge($participants, $ccEmails);
        }
        
        return array_unique($participants);
    }
    
    /**
     * Create a conversation object from grouped emails
     */
    protected function createConversationObject($emails)
    {
        $latestEmail = $emails->last();
        $firstEmail = $emails->first();
        
        // Count unread messages in thread
        $unreadCount = $emails->where('is_read', false)->count();
        
        // Get all participants
        $allParticipants = [];
        foreach ($emails as $email) {
            $allParticipants = array_merge($allParticipants, $this->getParticipants($email));
        }
        $allParticipants = array_unique($allParticipants);
        
        return [
            'id' => 'conv_' . md5($firstEmail->subject . implode(',', $allParticipants)),
            'subject' => $this->getConversationSubject($emails),
            'preview' => $this->getConversationPreview($latestEmail),
            'message_count' => $emails->count(),
            'unread_count' => $unreadCount,
            'is_unread' => $unreadCount > 0,
            'participants' => $allParticipants,
            'latest_date' => $latestEmail->created_at,
            'latest_sender' => $latestEmail->sender_name ?? $latestEmail->sender_email,
            'emails' => $emails->map(function ($email) {
                return [
                    'id' => $email->id,
                    'subject' => $email->subject,
                    'sender_name' => $email->sender_name,
                    'sender_email' => $email->sender_email,
                    'recipient_email' => $email->recipient_email,
                    'created_at' => $email->created_at,
                    'is_read' => $email->is_read,
                    'has_attachments' => $email->has_attachments ?? false
                ];
            })->toArray(),
            'has_attachments' => $emails->where('has_attachments', true)->count() > 0,
            'is_flagged' => $emails->where('is_flagged', true)->count() > 0,
        ];
    }
    
    /**
     * Get conversation subject
     */
    protected function getConversationSubject($emails)
    {
        $latestEmail = $emails->last();
        $subject = $latestEmail->subject;
        
        // Remove Re: or Fwd: if it's the only one
        if ($emails->count() === 1) {
            return $subject;
        }
        
        // For conversations, show the base subject
        return $this->extractBaseSubject($subject);
    }
    
    /**
     * Get conversation preview text
     */
    protected function getConversationPreview($email)
    {
        $emailService = new EmailService();
        $decryptedBody = $emailService->decryptData($email->body);
        
        // Strip HTML and get preview
        $preview = strip_tags($decryptedBody);
        $preview = str_replace(["\r\n", "\n", "\r"], ' ', $preview);
        $preview = preg_replace('/\s+/', ' ', $preview);
        
        return substr($preview, 0, 100) . (strlen($preview) > 100 ? '...' : '');
    }
    
    /**
     * Get full conversation thread
     */
    public function getConversationThread($conversationId, $userId)
    {
        // Since conversation ID is generated, we need to reverse engineer it
        // In a production system, you'd store conversation IDs in the database
        
        // For now, get all emails and find the matching conversation
        $allConversations = $this->getConversations($userId, 'inbox', 1, 1000);
        
        foreach ($allConversations['conversations'] as $conversation) {
            if ($conversation['id'] === $conversationId) {
                // Get full email details for each email in the thread
                $emailIds = array_column($conversation['emails'], 'id');
                
                $emails = DB::table('emails')
                    ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
                    ->whereIn('emails.id', $emailIds)
                    ->select(
                        'emails.*',
                        'senders.name as sender_name',
                        'senders.email as sender_email'
                    )
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Decrypt email bodies
                $emailService = new EmailService();
                foreach ($emails as $email) {
                    $email->decrypted_body = $emailService->decryptData($email->body);
                }
                
                return [
                    'conversation' => $conversation,
                    'emails' => $emails
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Mark entire conversation as read
     */
    public function markConversationAsRead($conversationId, $userId)
    {
        $thread = $this->getConversationThread($conversationId, $userId);
        
        if ($thread) {
            $emailIds = $thread['emails']->pluck('id')->toArray();
            
            DB::table('emails')
                ->whereIn('id', $emailIds)
                ->where('recipient_id', $userId)
                ->update(['is_read' => true]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete entire conversation
     */
    public function deleteConversation($conversationId, $userId)
    {
        $thread = $this->getConversationThread($conversationId, $userId);
        
        if ($thread) {
            $emailIds = $thread['emails']->pluck('id')->toArray();
            
            DB::table('emails')
                ->whereIn('id', $emailIds)
                ->where(function($query) use ($userId) {
                    $query->where('recipient_id', $userId)
                          ->orWhere('sender_id', $userId);
                })
                ->update([
                    'folder' => 'trash',
                    'deleted_at' => now()
                ]);
            
            return true;
        }
        
        return false;
    }
}