<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailTemplateService
{
    protected $defaultTemplates = [
        [
            'name' => 'Welcome Email',
            'category' => 'onboarding',
            'subject' => 'Welcome to {{company_name}}!',
            'body' => "Dear {{recipient_name}},\n\nWelcome to {{company_name}}! We're excited to have you on board.\n\nIf you have any questions, please don't hesitate to reach out.\n\nBest regards,\n{{sender_name}}\n{{sender_title}}",
            'variables' => ['recipient_name', 'company_name', 'sender_name', 'sender_title']
        ],
        [
            'name' => 'Meeting Request',
            'category' => 'business',
            'subject' => 'Meeting Request: {{meeting_topic}}',
            'body' => "Hi {{recipient_name}},\n\nI would like to schedule a meeting to discuss {{meeting_topic}}.\n\nProposed times:\n- {{option_1}}\n- {{option_2}}\n- {{option_3}}\n\nPlease let me know which time works best for you.\n\nBest regards,\n{{sender_name}}",
            'variables' => ['recipient_name', 'meeting_topic', 'option_1', 'option_2', 'option_3', 'sender_name']
        ],
        [
            'name' => 'Follow-up',
            'category' => 'business',
            'subject' => 'Following up on {{topic}}',
            'body' => "Hi {{recipient_name}},\n\nI wanted to follow up on our discussion about {{topic}}.\n\n{{follow_up_content}}\n\nPlease let me know if you need any additional information.\n\nBest regards,\n{{sender_name}}",
            'variables' => ['recipient_name', 'topic', 'follow_up_content', 'sender_name']
        ],
        [
            'name' => 'Thank You',
            'category' => 'personal',
            'subject' => 'Thank you for {{reason}}',
            'body' => "Dear {{recipient_name}},\n\nI wanted to take a moment to thank you for {{reason}}.\n\n{{additional_message}}\n\nI really appreciate it!\n\nBest regards,\n{{sender_name}}",
            'variables' => ['recipient_name', 'reason', 'additional_message', 'sender_name']
        ],
        [
            'name' => 'Newsletter',
            'category' => 'marketing',
            'subject' => '{{newsletter_title}} - {{month}} {{year}}',
            'body' => "Dear {{recipient_name}},\n\nHere's what's new this month:\n\n{{content}}\n\nStay tuned for more updates!\n\nBest regards,\n{{company_name}} Team",
            'variables' => ['recipient_name', 'newsletter_title', 'month', 'year', 'content', 'company_name']
        ]
    ];
    
    /**
     * Get user templates
     */
    public function getUserTemplates($userId, $includeShared = true)
    {
        $query = DB::table('email_templates')
            ->where('is_active', true);
            
        if ($includeShared) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('is_shared', true);
            });
        } else {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('usage_count', 'desc')
                    ->orderBy('name', 'asc')
                    ->get();
    }
    
    /**
     * Get templates by category
     */
    public function getTemplatesByCategory($userId, $category)
    {
        return DB::table('email_templates')
            ->where('is_active', true)
            ->where('category', $category)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('is_shared', true);
            })
            ->orderBy('name', 'asc')
            ->get();
    }
    
    /**
     * Create a new template
     */
    public function createTemplate($userId, $data)
    {
        try {
            // Extract variables from template
            $variables = $this->extractVariables($data['subject'] . ' ' . $data['body']);
            
            $templateId = DB::table('email_templates')->insertGetId([
                'user_id' => $userId,
                'name' => $data['name'],
                'category' => $data['category'] ?? 'general',
                'description' => $data['description'] ?? null,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'variables' => json_encode($variables),
                'is_shared' => $data['is_shared'] ?? false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'template_id' => $templateId,
                'message' => 'Template created successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create email template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update a template
     */
    public function updateTemplate($templateId, $userId, $data)
    {
        try {
            $template = DB::table('email_templates')
                ->where('id', $templateId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$template) {
                throw new \Exception('Template not found or access denied');
            }
            
            // Extract variables from template
            $variables = $this->extractVariables($data['subject'] . ' ' . $data['body']);
            
            DB::table('email_templates')
                ->where('id', $templateId)
                ->update([
                    'name' => $data['name'],
                    'category' => $data['category'] ?? $template->category,
                    'description' => $data['description'] ?? $template->description,
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'variables' => json_encode($variables),
                    'is_shared' => $data['is_shared'] ?? $template->is_shared,
                    'updated_at' => now()
                ]);
                
            return [
                'success' => true,
                'message' => 'Template updated successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update email template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a template
     */
    public function deleteTemplate($templateId, $userId)
    {
        try {
            $deleted = DB::table('email_templates')
                ->where('id', $templateId)
                ->where('user_id', $userId)
                ->delete();
                
            if (!$deleted) {
                throw new \Exception('Template not found or access denied');
            }
            
            return [
                'success' => true,
                'message' => 'Template deleted successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete email template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Use a template
     */
    public function useTemplate($templateId, $userId, $variables = [])
    {
        try {
            $template = DB::table('email_templates')
                ->where('id', $templateId)
                ->where(function($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhere('is_shared', true);
                })
                ->first();
                
            if (!$template) {
                throw new \Exception('Template not found or access denied');
            }
            
            // Apply variables to template
            $subject = $this->applyVariables($template->subject, $variables);
            $body = $this->applyVariables($template->body, $variables);
            
            // Update usage statistics
            DB::table('email_templates')
                ->where('id', $templateId)
                ->update([
                    'usage_count' => DB::raw('usage_count + 1'),
                    'last_used_at' => now()
                ]);
                
            return [
                'success' => true,
                'subject' => $subject,
                'body' => $body,
                'template' => $template
            ];
        } catch (\Exception $e) {
            Log::error('Failed to use email template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to use template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract variables from template text
     */
    protected function extractVariables($text)
    {
        preg_match_all('/\{\{(\w+)\}\}/', $text, $matches);
        return array_unique($matches[1]);
    }
    
    /**
     * Apply variables to template text
     */
    protected function applyVariables($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        
        // Remove any remaining placeholders
        $text = preg_replace('/\{\{\w+\}\}/', '', $text);
        
        return $text;
    }
    
    /**
     * Get template categories
     */
    public function getCategories()
    {
        return [
            'general' => 'General',
            'business' => 'Business',
            'personal' => 'Personal',
            'marketing' => 'Marketing',
            'onboarding' => 'Onboarding',
            'support' => 'Customer Support',
            'sales' => 'Sales',
            'hr' => 'Human Resources',
            'finance' => 'Finance',
            'legal' => 'Legal'
        ];
    }
    
    /**
     * Clone a template
     */
    public function cloneTemplate($templateId, $userId)
    {
        try {
            $template = DB::table('email_templates')
                ->where('id', $templateId)
                ->where(function($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhere('is_shared', true);
                })
                ->first();
                
            if (!$template) {
                throw new \Exception('Template not found or access denied');
            }
            
            $newTemplateId = DB::table('email_templates')->insertGetId([
                'user_id' => $userId,
                'name' => $template->name . ' (Copy)',
                'category' => $template->category,
                'description' => $template->description,
                'subject' => $template->subject,
                'body' => $template->body,
                'variables' => $template->variables,
                'is_shared' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'template_id' => $newTemplateId,
                'message' => 'Template cloned successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to clone email template: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to clone template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create default templates for a user
     */
    public function createDefaultTemplates($userId)
    {
        foreach ($this->defaultTemplates as $template) {
            $this->createTemplate($userId, array_merge($template, [
                'is_shared' => false
            ]));
        }
    }
    
    /**
     * Search templates
     */
    public function searchTemplates($userId, $query)
    {
        return DB::table('email_templates')
            ->where('is_active', true)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('is_shared', true);
            })
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhere('subject', 'like', '%' . $query . '%')
                  ->orWhere('body', 'like', '%' . $query . '%');
            })
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();
    }
}