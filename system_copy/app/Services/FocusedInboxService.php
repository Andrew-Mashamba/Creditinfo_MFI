<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FocusedInboxService
{
    protected $importanceFactors = [
        'from_domain' => 20,           // Email from same domain
        'direct_to' => 25,             // Directly addressed to user
        'cc_only' => -10,              // User is only in CC
        'marked_important' => 30,      // Sender marked as important
        'previous_interaction' => 15,  // Previous email exchange with sender
        'keywords' => 10,              // Important keywords in subject/body
        'attachments' => 5,            // Has attachments
        'calendar_invite' => 20,       // Contains calendar invite
        'reply_expected' => 25,        // Expects a reply
        'from_manager' => 30,          // From user's manager/superior
        'time_sensitive' => 20,        // Time-sensitive keywords
        'newsletter' => -20,           // Appears to be newsletter
        'automated' => -15,            // Automated/system email
        'unsubscribe_link' => -25,     // Contains unsubscribe link
        'bulk_recipient' => -15        // Many recipients
    ];
    
    protected $importantKeywords = [
        'urgent', 'important', 'asap', 'deadline', 'critical', 
        'meeting', 'appointment', 'review', 'approval', 'action required',
        'please respond', 'waiting for', 'follow up', 'reminder'
    ];
    
    protected $newsletterKeywords = [
        'newsletter', 'unsubscribe', 'update your preferences',
        'view in browser', 'bulk mail', 'no-reply', 'donotreply'
    ];
    
    /**
     * Calculate importance score for an email
     */
    public function calculateImportanceScore($email, $userId)
    {
        $score = 50; // Base score
        $factors = [];
        
        // Check if from same domain
        $userEmail = DB::table('users')->where('id', $userId)->value('email');
        $userDomain = substr(strrchr($userEmail, "@"), 1);
        $senderDomain = substr(strrchr($email->sender_email ?? '', "@"), 1);
        
        if ($userDomain && $senderDomain && $userDomain === $senderDomain) {
            $score += $this->importanceFactors['from_domain'];
            $factors[] = 'same_domain';
        }
        
        // Check if directly addressed
        if ($email->recipient_id == $userId && empty($email->cc)) {
            $score += $this->importanceFactors['direct_to'];
            $factors[] = 'direct_recipient';
        } elseif (strpos($email->cc ?? '', $userEmail) !== false) {
            $score += $this->importanceFactors['cc_only'];
            $factors[] = 'cc_only';
        }
        
        // Check if marked important
        if ($email->is_important) {
            $score += $this->importanceFactors['marked_important'];
            $factors[] = 'marked_important';
        }
        
        // Check previous interaction
        $previousEmails = DB::table('emails')
            ->where(function($query) use ($email, $userId) {
                $query->where('sender_email', $email->sender_email)
                      ->where('recipient_id', $userId);
            })
            ->orWhere(function($query) use ($email, $userId) {
                $query->where('recipient_email', $email->sender_email)
                      ->where('sender_id', $userId);
            })
            ->count();
            
        if ($previousEmails > 3) {
            $score += $this->importanceFactors['previous_interaction'];
            $factors[] = 'frequent_contact';
        }
        
        // Check for important keywords
        $subject = strtolower($email->subject ?? '');
        $emailService = new EmailService();
        $body = strtolower($emailService->decryptData($email->body ?? ''));
        
        foreach ($this->importantKeywords as $keyword) {
            if (strpos($subject, $keyword) !== false || strpos($body, $keyword) !== false) {
                $score += $this->importanceFactors['keywords'];
                $factors[] = 'important_keywords';
                break;
            }
        }
        
        // Check for time-sensitive keywords
        $timeSensitiveKeywords = ['today', 'tomorrow', 'deadline', 'expires', 'urgent'];
        foreach ($timeSensitiveKeywords as $keyword) {
            if (strpos($subject, $keyword) !== false || strpos($body, $keyword) !== false) {
                $score += $this->importanceFactors['time_sensitive'];
                $factors[] = 'time_sensitive';
                break;
            }
        }
        
        // Check for attachments
        if ($email->has_attachments) {
            $score += $this->importanceFactors['attachments'];
            $factors[] = 'has_attachments';
        }
        
        // Check for newsletter characteristics
        foreach ($this->newsletterKeywords as $keyword) {
            if (strpos($subject, $keyword) !== false || strpos($body, $keyword) !== false) {
                $score += $this->importanceFactors['newsletter'];
                $factors[] = 'newsletter';
                break;
            }
        }
        
        // Check if automated
        if (strpos($email->sender_email ?? '', 'noreply') !== false || 
            strpos($email->sender_email ?? '', 'no-reply') !== false ||
            strpos($email->sender_email ?? '', 'donotreply') !== false) {
            $score += $this->importanceFactors['automated'];
            $factors[] = 'automated';
        }
        
        // Check for unsubscribe link
        if (strpos($body, 'unsubscribe') !== false) {
            $score += $this->importanceFactors['unsubscribe_link'];
            $factors[] = 'has_unsubscribe';
        }
        
        // Check number of recipients
        $ccCount = $email->cc ? count(explode(',', $email->cc)) : 0;
        if ($ccCount > 5) {
            $score += $this->importanceFactors['bulk_recipient'];
            $factors[] = 'many_recipients';
        }
        
        // Normalize score to 0-100
        $score = max(0, min(100, $score));
        
        return [
            'score' => $score,
            'factors' => $factors,
            'is_focused' => $score >= 60 // Threshold for focused inbox
        ];
    }
    
    /**
     * Process email for focused inbox
     */
    public function processEmailForFocusedInbox($emailId, $userId)
    {
        try {
            $email = DB::table('emails')->where('id', $emailId)->first();
            if (!$email) {
                return false;
            }
            
            $result = $this->calculateImportanceScore($email, $userId);
            
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'is_focused' => $result['is_focused'],
                    'importance_score' => $result['score'],
                    'importance_factors' => json_encode($result['factors'])
                ]);
                
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to process email for focused inbox: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process all unprocessed emails for a user
     */
    public function processUserEmails($userId)
    {
        $emails = DB::table('emails')
            ->where('recipient_id', $userId)
            ->where('folder', 'inbox')
            ->whereNull('importance_score')
            ->get();
            
        $processed = 0;
        foreach ($emails as $email) {
            if ($this->processEmailForFocusedInbox($email->id, $userId)) {
                $processed++;
            }
        }
        
        return $processed;
    }
    
    /**
     * Get focused emails for a user
     */
    public function getFocusedEmails($userId, $page = 1, $perPage = 20)
    {
        return DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where('emails.recipient_id', $userId)
            ->where('emails.folder', 'inbox')
            ->where('emails.is_focused', true)
            ->select('emails.*', 'senders.name as sender_name', 'senders.email as sender_email')
            ->orderBy('emails.importance_score', 'desc')
            ->orderBy('emails.created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
    
    /**
     * Get other (non-focused) emails for a user
     */
    public function getOtherEmails($userId, $page = 1, $perPage = 20)
    {
        return DB::table('emails')
            ->leftJoin('users as senders', 'emails.sender_id', '=', 'senders.id')
            ->where('emails.recipient_id', $userId)
            ->where('emails.folder', 'inbox')
            ->where('emails.is_focused', false)
            ->select('emails.*', 'senders.name as sender_name', 'senders.email as sender_email')
            ->orderBy('emails.created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
    
    /**
     * Toggle focused status manually
     */
    public function toggleFocusedStatus($emailId, $userId)
    {
        $email = DB::table('emails')
            ->where('id', $emailId)
            ->where('recipient_id', $userId)
            ->first();
            
        if (!$email) {
            return false;
        }
        
        DB::table('emails')
            ->where('id', $emailId)
            ->update([
                'is_focused' => !$email->is_focused,
                'importance_score' => !$email->is_focused ? 80 : 40 // Adjust score based on manual action
            ]);
            
        // Learn from user action
        $this->learnFromUserAction($email, !$email->is_focused, $userId);
        
        return true;
    }
    
    /**
     * Learn from user actions to improve algorithm
     */
    protected function learnFromUserAction($email, $isFocused, $userId)
    {
        // In a production system, this would update ML models or adjust weights
        // For now, we'll log the action for future analysis
        Log::info('Focused inbox learning', [
            'user_id' => $userId,
            'email_id' => $email->id,
            'sender' => $email->sender_email,
            'subject' => $email->subject,
            'marked_focused' => $isFocused,
            'original_score' => $email->importance_score,
            'factors' => json_decode($email->importance_factors ?? '[]', true)
        ]);
    }
    
    /**
     * Get statistics for focused inbox
     */
    public function getFocusedInboxStats($userId)
    {
        $stats = DB::table('emails')
            ->where('recipient_id', $userId)
            ->where('folder', 'inbox')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_focused = true THEN 1 ELSE 0 END) as focused_count,
                SUM(CASE WHEN is_focused = false THEN 1 ELSE 0 END) as other_count,
                AVG(importance_score) as avg_score,
                SUM(CASE WHEN is_focused = true AND is_read = false THEN 1 ELSE 0 END) as focused_unread,
                SUM(CASE WHEN is_focused = false AND is_read = false THEN 1 ELSE 0 END) as other_unread
            ')
            ->first();
            
        return [
            'total' => $stats->total ?? 0,
            'focused_count' => $stats->focused_count ?? 0,
            'other_count' => $stats->other_count ?? 0,
            'average_score' => round($stats->avg_score ?? 0, 2),
            'focused_unread' => $stats->focused_unread ?? 0,
            'other_unread' => $stats->other_unread ?? 0,
            'focus_percentage' => $stats->total > 0 
                ? round(($stats->focused_count / $stats->total) * 100, 1) 
                : 0
        ];
    }
    
    /**
     * Retrain focused inbox for a user
     */
    public function retrainFocusedInbox($userId)
    {
        // Reset all scores and reprocess
        DB::table('emails')
            ->where('recipient_id', $userId)
            ->where('folder', 'inbox')
            ->update([
                'is_focused' => false,
                'importance_score' => null,
                'importance_factors' => null
            ]);
            
        return $this->processUserEmails($userId);
    }
}