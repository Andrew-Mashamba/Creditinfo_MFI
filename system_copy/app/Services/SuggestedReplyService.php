<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuggestedReplyService
{
    protected $commonReplies = [
        'acknowledgment' => [
            "Thank you for your email. I'll review this and get back to you shortly.",
            "Thanks for reaching out. I've received your message and will respond soon.",
            "Got it, thanks! I'll look into this and follow up."
        ],
        'meeting_accept' => [
            "That works for me. See you then!",
            "Perfect, I've added it to my calendar. Looking forward to it.",
            "Sounds good. I'll be there."
        ],
        'meeting_decline' => [
            "Unfortunately, I have a conflict at that time. Could we reschedule?",
            "I'm not available then. What about [alternative time]?",
            "Sorry, I can't make that time. Are you free [alternative]?"
        ],
        'information_request' => [
            "Could you please provide more details about this?",
            "I'd need some additional information to proceed. Could you elaborate?",
            "Thanks for your message. Could you share more context?"
        ],
        'approval' => [
            "Approved. Please proceed.",
            "This looks good to me. You have my approval.",
            "I'm happy with this. Go ahead."
        ],
        'task_completion' => [
            "This has been completed. Please let me know if you need anything else.",
            "Done! Let me know if there's anything else you need.",
            "I've finished this task. Attached are the results."
        ]
    ];
    
    /**
     * Get suggested replies for an email
     */
    public function getSuggestedReplies($emailId, $userId)
    {
        try {
            $email = DB::table('emails')
                ->where('id', $emailId)
                ->first();
                
            if (!$email) {
                return [];
            }
            
            // Analyze email content
            $analysis = $this->analyzeEmail($email);
            
            // Get appropriate suggestions
            $suggestions = $this->generateSuggestions($analysis, $email);
            
            // Track suggestion generation
            $this->trackSuggestions($emailId, $userId, $suggestions);
            
            return $suggestions;
        } catch (\Exception $e) {
            Log::error('Failed to generate suggested replies: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analyze email content to determine context
     */
    protected function analyzeEmail($email)
    {
        $subject = strtolower($email->subject ?? '');
        $body = strtolower(strip_tags($email->body ?? ''));
        $content = $subject . ' ' . $body;
        
        $analysis = [
            'is_question' => $this->containsQuestion($content),
            'is_meeting_request' => $this->isMeetingRequest($content),
            'is_approval_request' => $this->isApprovalRequest($content),
            'is_information_request' => $this->isInformationRequest($content),
            'is_task_related' => $this->isTaskRelated($content),
            'sentiment' => $this->analyzeSentiment($content),
            'urgency' => $this->analyzeUrgency($content)
        ];
        
        return $analysis;
    }
    
    /**
     * Check if content contains a question
     */
    protected function containsQuestion($content)
    {
        $questionWords = ['what', 'when', 'where', 'why', 'how', 'could', 'would', 'can', 'will', '?'];
        
        foreach ($questionWords as $word) {
            if (str_contains($content, $word)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if it's a meeting request
     */
    protected function isMeetingRequest($content)
    {
        $meetingWords = ['meeting', 'schedule', 'calendar', 'appointment', 'call', 'discuss', 'available', 'time'];
        $matchCount = 0;
        
        foreach ($meetingWords as $word) {
            if (str_contains($content, $word)) {
                $matchCount++;
            }
        }
        
        return $matchCount >= 2;
    }
    
    /**
     * Check if it's an approval request
     */
    protected function isApprovalRequest($content)
    {
        $approvalWords = ['approve', 'approval', 'permission', 'authorize', 'consent', 'sign off', 'review'];
        
        foreach ($approvalWords as $word) {
            if (str_contains($content, $word)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if it's an information request
     */
    protected function isInformationRequest($content)
    {
        $infoWords = ['information', 'details', 'clarify', 'explain', 'provide', 'share', 'send me', 'need to know'];
        
        foreach ($infoWords as $word) {
            if (str_contains($content, $word)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if it's task related
     */
    protected function isTaskRelated($content)
    {
        $taskWords = ['task', 'assignment', 'project', 'deadline', 'complete', 'finish', 'deliver', 'submit'];
        
        foreach ($taskWords as $word) {
            if (str_contains($content, $word)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Analyze sentiment
     */
    protected function analyzeSentiment($content)
    {
        $positiveWords = ['thank', 'appreciate', 'great', 'excellent', 'good', 'happy', 'pleased'];
        $negativeWords = ['concern', 'issue', 'problem', 'disappointed', 'unhappy', 'complaint', 'urgent'];
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            if (str_contains($content, $word)) {
                $positiveCount++;
            }
        }
        
        foreach ($negativeWords as $word) {
            if (str_contains($content, $word)) {
                $negativeCount++;
            }
        }
        
        if ($negativeCount > $positiveCount) {
            return 'negative';
        } elseif ($positiveCount > $negativeCount) {
            return 'positive';
        }
        
        return 'neutral';
    }
    
    /**
     * Analyze urgency
     */
    protected function analyzeUrgency($content)
    {
        $urgentWords = ['urgent', 'asap', 'immediately', 'today', 'now', 'critical', 'important'];
        
        foreach ($urgentWords as $word) {
            if (str_contains($content, $word)) {
                return 'high';
            }
        }
        
        return 'normal';
    }
    
    /**
     * Generate suggestions based on analysis
     */
    protected function generateSuggestions($analysis, $email)
    {
        $suggestions = [];
        
        // Always include acknowledgment option
        $suggestions[] = [
            'type' => 'acknowledgment',
            'text' => $this->commonReplies['acknowledgment'][array_rand($this->commonReplies['acknowledgment'])],
            'confidence' => 0.9
        ];
        
        // Meeting requests
        if ($analysis['is_meeting_request']) {
            $suggestions[] = [
                'type' => 'meeting_accept',
                'text' => $this->commonReplies['meeting_accept'][array_rand($this->commonReplies['meeting_accept'])],
                'confidence' => 0.85
            ];
            
            $suggestions[] = [
                'type' => 'meeting_decline',
                'text' => $this->commonReplies['meeting_decline'][array_rand($this->commonReplies['meeting_decline'])],
                'confidence' => 0.85
            ];
        }
        
        // Approval requests
        if ($analysis['is_approval_request']) {
            $suggestions[] = [
                'type' => 'approval',
                'text' => $this->commonReplies['approval'][array_rand($this->commonReplies['approval'])],
                'confidence' => 0.8
            ];
        }
        
        // Information requests
        if ($analysis['is_information_request'] || $analysis['is_question']) {
            $suggestions[] = [
                'type' => 'information_request',
                'text' => $this->commonReplies['information_request'][array_rand($this->commonReplies['information_request'])],
                'confidence' => 0.75
            ];
        }
        
        // Task related
        if ($analysis['is_task_related']) {
            $suggestions[] = [
                'type' => 'task_completion',
                'text' => $this->commonReplies['task_completion'][array_rand($this->commonReplies['task_completion'])],
                'confidence' => 0.7
            ];
        }
        
        // Limit to top 3 suggestions
        usort($suggestions, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return array_slice($suggestions, 0, 3);
    }
    
    /**
     * Track suggestions for learning
     */
    protected function trackSuggestions($emailId, $userId, $suggestions)
    {
        try {
            DB::table('suggested_reply_history')->insert([
                'email_id' => $emailId,
                'user_id' => $userId,
                'suggestions' => json_encode($suggestions),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Silently fail - tracking is not critical
            Log::debug('Failed to track suggestions: ' . $e->getMessage());
        }
    }
    
    /**
     * Record suggestion usage
     */
    public function recordSuggestionUsage($emailId, $userId, $suggestionType, $accepted)
    {
        try {
            DB::table('suggested_reply_feedback')->insert([
                'email_id' => $emailId,
                'user_id' => $userId,
                'suggestion_type' => $suggestionType,
                'accepted' => $accepted,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::debug('Failed to record suggestion usage: ' . $e->getMessage());
        }
    }
    
    /**
     * Get personalized suggestions based on user history
     */
    public function getPersonalizedSuggestions($userId, $context)
    {
        // This would analyze user's past replies and preferences
        // For now, return standard suggestions
        return $this->commonReplies[$context] ?? [];
    }
}