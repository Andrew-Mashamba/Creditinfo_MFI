<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailRulesService
{
    protected $conditions = [
        'from_contains' => 'From contains',
        'from_equals' => 'From equals',
        'to_contains' => 'To contains',
        'subject_contains' => 'Subject contains',
        'subject_starts_with' => 'Subject starts with',
        'body_contains' => 'Body contains',
        'has_attachment' => 'Has attachment',
        'is_unread' => 'Is unread',
        'size_greater_than' => 'Size greater than (KB)',
        'older_than_days' => 'Older than days'
    ];
    
    protected $actions = [
        'move_to_folder' => 'Move to folder',
        'mark_as_read' => 'Mark as read',
        'mark_as_important' => 'Mark as important',
        'delete' => 'Delete',
        'forward_to' => 'Forward to',
        'add_label' => 'Add label',
        'flag' => 'Flag email',
        'pin' => 'Pin email',
        'mark_as_spam' => 'Mark as spam'
    ];
    
    /**
     * Get available conditions for rules
     */
    public function getAvailableConditions()
    {
        return $this->conditions;
    }
    
    /**
     * Get available actions for rules
     */
    public function getAvailableActions()
    {
        return $this->actions;
    }
    
    /**
     * Create a new rule
     */
    public function createRule($userId, $data)
    {
        try {
            // Validate conditions and actions
            $this->validateRule($data);
            
            $ruleId = DB::table('email_rules')->insertGetId([
                'user_id' => $userId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'priority' => $data['priority'] ?? 0,
                'conditions' => json_encode($data['conditions']),
                'condition_logic' => $data['condition_logic'] ?? 'all',
                'actions' => json_encode($data['actions']),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'rule_id' => $ruleId,
                'message' => 'Rule created successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create email rule: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create rule: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing rule
     */
    public function updateRule($ruleId, $userId, $data)
    {
        try {
            // Validate conditions and actions
            $this->validateRule($data);
            
            DB::table('email_rules')
                ->where('id', $ruleId)
                ->where('user_id', $userId)
                ->update([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                    'priority' => $data['priority'] ?? 0,
                    'conditions' => json_encode($data['conditions']),
                    'condition_logic' => $data['condition_logic'] ?? 'all',
                    'actions' => json_encode($data['actions']),
                    'updated_at' => now()
                ]);
            
            return [
                'success' => true,
                'message' => 'Rule updated successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update email rule: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update rule: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a rule
     */
    public function deleteRule($ruleId, $userId)
    {
        try {
            DB::table('email_rules')
                ->where('id', $ruleId)
                ->where('user_id', $userId)
                ->delete();
            
            return [
                'success' => true,
                'message' => 'Rule deleted successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete email rule: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete rule: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all rules for a user
     */
    public function getUserRules($userId)
    {
        $rules = DB::table('email_rules')
            ->where('user_id', $userId)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Decode JSON fields
        foreach ($rules as $rule) {
            $rule->conditions = json_decode($rule->conditions, true);
            $rule->actions = json_decode($rule->actions, true);
        }
        
        return $rules;
    }
    
    /**
     * Apply rules to an email
     */
    public function applyRulesToEmail($emailId, $userId)
    {
        try {
            // Get the email
            $email = DB::table('emails')->where('id', $emailId)->first();
            if (!$email) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            // Get active rules for the user
            $rules = DB::table('email_rules')
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->orderBy('priority', 'desc')
                ->get();
            
            $appliedRules = [];
            
            foreach ($rules as $rule) {
                $conditions = json_decode($rule->conditions, true);
                $actions = json_decode($rule->actions, true);
                
                // Check if rule matches
                if ($this->evaluateConditions($email, $conditions, $rule->condition_logic)) {
                    // Apply actions
                    $this->applyActions($email, $actions, $userId);
                    
                    // Update rule statistics
                    DB::table('email_rules')
                        ->where('id', $rule->id)
                        ->update([
                            'times_applied' => DB::raw('times_applied + 1'),
                            'last_applied_at' => now()
                        ]);
                    
                    $appliedRules[] = $rule->name;
                    
                    // If any action moves/deletes the email, stop processing
                    if ($this->isTerminalAction($actions)) {
                        break;
                    }
                }
            }
            
            return [
                'success' => true,
                'applied_rules' => $appliedRules,
                'message' => count($appliedRules) . ' rules applied'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to apply rules to email: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to apply rules: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Apply rules to all new emails for a user
     */
    public function processNewEmails($userId)
    {
        // Get unprocessed emails
        $emails = DB::table('emails')
            ->where('recipient_id', $userId)
            ->where('folder', 'inbox')
            ->where('rules_processed', false)
            ->get();
        
        $processed = 0;
        
        foreach ($emails as $email) {
            $result = $this->applyRulesToEmail($email->id, $userId);
            if ($result['success']) {
                // Mark as processed
                DB::table('emails')
                    ->where('id', $email->id)
                    ->update(['rules_processed' => true]);
                $processed++;
            }
        }
        
        return [
            'success' => true,
            'processed' => $processed,
            'message' => "$processed emails processed"
        ];
    }
    
    /**
     * Evaluate conditions for a rule
     */
    protected function evaluateConditions($email, $conditions, $logic)
    {
        if (empty($conditions)) {
            return false;
        }
        
        $results = [];
        
        foreach ($conditions as $condition) {
            $type = $condition['type'];
            $value = $condition['value'] ?? '';
            $result = false;
            
            switch ($type) {
                case 'from_contains':
                    $result = stripos($email->sender_email ?? '', $value) !== false;
                    break;
                    
                case 'from_equals':
                    $result = strcasecmp($email->sender_email ?? '', $value) === 0;
                    break;
                    
                case 'to_contains':
                    $result = stripos($email->recipient_email ?? '', $value) !== false;
                    break;
                    
                case 'subject_contains':
                    $result = stripos($email->subject ?? '', $value) !== false;
                    break;
                    
                case 'subject_starts_with':
                    $result = stripos($email->subject ?? '', $value) === 0;
                    break;
                    
                case 'body_contains':
                    $emailService = new EmailService();
                    $body = $emailService->decryptData($email->body);
                    $result = stripos($body, $value) !== false;
                    break;
                    
                case 'has_attachment':
                    $result = $email->has_attachments == ($value === 'true' || $value === true);
                    break;
                    
                case 'is_unread':
                    $result = !$email->is_read == ($value === 'true' || $value === true);
                    break;
                    
                case 'size_greater_than':
                    $size = strlen($email->body ?? '') + strlen($email->subject ?? '');
                    $result = $size > ($value * 1024); // Convert KB to bytes
                    break;
                    
                case 'older_than_days':
                    $createdAt = Carbon::parse($email->created_at);
                    $result = $createdAt->diffInDays(now()) > $value;
                    break;
            }
            
            $results[] = $result;
        }
        
        // Apply logic (all = AND, any = OR)
        if ($logic === 'all') {
            return !in_array(false, $results, true);
        } else {
            return in_array(true, $results, true);
        }
    }
    
    /**
     * Apply actions to an email
     */
    protected function applyActions($email, $actions, $userId)
    {
        foreach ($actions as $action) {
            $type = $action['type'];
            $value = $action['value'] ?? '';
            
            switch ($type) {
                case 'move_to_folder':
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update(['folder' => $value]);
                    break;
                    
                case 'mark_as_read':
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update(['is_read' => true]);
                    break;
                    
                case 'mark_as_important':
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update(['is_important' => true]);
                    break;
                    
                case 'delete':
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update([
                            'folder' => 'trash',
                            'deleted_at' => now()
                        ]);
                    break;
                    
                case 'forward_to':
                    // Create a forwarded copy
                    $emailService = new EmailService();
                    $emailService->forwardEmail($email->id, $value, $userId);
                    break;
                    
                case 'add_label':
                    // Add label (requires labels implementation)
                    $this->addLabelToEmail($email->id, $value, $userId);
                    break;
                    
                case 'flag':
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update([
                            'is_flagged' => true,
                            'flagged_at' => now()
                        ]);
                    break;
                    
                case 'pin':
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update([
                            'is_pinned' => true,
                            'pinned_at' => now()
                        ]);
                    break;
                    
                case 'mark_as_spam':
                    DB::table('emails')
                        ->where('id', $email->id)
                        ->update(['folder' => 'spam']);
                    break;
            }
        }
    }
    
    /**
     * Check if actions include terminal actions (move/delete)
     */
    protected function isTerminalAction($actions)
    {
        $terminalActions = ['move_to_folder', 'delete', 'mark_as_spam'];
        
        foreach ($actions as $action) {
            if (in_array($action['type'], $terminalActions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate rule data
     */
    protected function validateRule($data)
    {
        if (empty($data['name'])) {
            throw new \Exception('Rule name is required');
        }
        
        if (empty($data['conditions']) || !is_array($data['conditions'])) {
            throw new \Exception('At least one condition is required');
        }
        
        if (empty($data['actions']) || !is_array($data['actions'])) {
            throw new \Exception('At least one action is required');
        }
        
        // Validate conditions
        foreach ($data['conditions'] as $condition) {
            if (!isset($condition['type']) || !array_key_exists($condition['type'], $this->conditions)) {
                throw new \Exception('Invalid condition type');
            }
        }
        
        // Validate actions
        foreach ($data['actions'] as $action) {
            if (!isset($action['type']) || !array_key_exists($action['type'], $this->actions)) {
                throw new \Exception('Invalid action type');
            }
        }
    }
    
    /**
     * Add label to email (placeholder for labels implementation)
     */
    protected function addLabelToEmail($emailId, $label, $userId)
    {
        // This will be implemented when we add the labels feature
        Log::info("Would add label '$label' to email $emailId for user $userId");
    }
    
    /**
     * Get rule templates
     */
    public function getRuleTemplates()
    {
        return [
            [
                'name' => 'Newsletter to Folder',
                'description' => 'Move newsletters to a dedicated folder',
                'conditions' => [
                    ['type' => 'subject_contains', 'value' => 'newsletter']
                ],
                'condition_logic' => 'any',
                'actions' => [
                    ['type' => 'move_to_folder', 'value' => 'newsletters'],
                    ['type' => 'mark_as_read', 'value' => true]
                ]
            ],
            [
                'name' => 'Flag Important Contacts',
                'description' => 'Flag emails from important contacts',
                'conditions' => [
                    ['type' => 'from_contains', 'value' => '@important-domain.com']
                ],
                'condition_logic' => 'any',
                'actions' => [
                    ['type' => 'flag', 'value' => true],
                    ['type' => 'mark_as_important', 'value' => true]
                ]
            ],
            [
                'name' => 'Auto-delete Old Emails',
                'description' => 'Delete emails older than 90 days',
                'conditions' => [
                    ['type' => 'older_than_days', 'value' => 90]
                ],
                'condition_logic' => 'all',
                'actions' => [
                    ['type' => 'delete', 'value' => true]
                ]
            ],
            [
                'name' => 'Spam Filter',
                'description' => 'Move suspicious emails to spam',
                'conditions' => [
                    ['type' => 'subject_contains', 'value' => 'free money'],
                    ['type' => 'body_contains', 'value' => 'click here now']
                ],
                'condition_logic' => 'any',
                'actions' => [
                    ['type' => 'mark_as_spam', 'value' => true]
                ]
            ]
        ];
    }
}