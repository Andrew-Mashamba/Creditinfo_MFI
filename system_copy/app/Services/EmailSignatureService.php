<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailSignatureService
{
    protected $defaultSignatures = [
        [
            'name' => 'Professional',
            'content' => '<div style="font-family: Arial, sans-serif; color: #333;">
                <p style="margin-top: 20px; margin-bottom: 5px;">Best regards,</p>
                <p style="margin: 0; font-weight: bold;">{{name}}</p>
                <p style="margin: 0; color: #666;">{{title}}</p>
                <p style="margin: 0; color: #666;">{{company}}</p>
                <p style="margin-top: 10px; margin-bottom: 0;">
                    <span style="color: #666;">Email:</span> {{email}} | 
                    <span style="color: #666;">Phone:</span> {{phone}}
                </p>
            </div>'
        ],
        [
            'name' => 'Simple',
            'content' => '<div style="font-family: Arial, sans-serif; color: #333;">
                <p style="margin-top: 20px;">Thanks,<br>{{name}}</p>
            </div>'
        ],
        [
            'name' => 'Corporate',
            'content' => '<div style="font-family: Arial, sans-serif; color: #333; border-top: 2px solid #e74c3c; padding-top: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: bold; color: #e74c3c;">{{name}}</p>
                <p style="margin: 0; color: #666;">{{title}}</p>
                <p style="margin: 5px 0; font-weight: bold;">{{company}}</p>
                <p style="margin: 5px 0; font-size: 0.9em; color: #666;">
                    {{address}}<br>
                    Email: {{email}} | Phone: {{phone}}<br>
                    Web: {{website}}
                </p>
                <p style="margin-top: 10px; font-size: 0.8em; color: #999;">
                    This email and any attachments are confidential and intended solely for the addressee.
                </p>
            </div>'
        ]
    ];
    
    /**
     * Get user signatures
     */
    public function getUserSignatures($userId)
    {
        return DB::table('email_signatures')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }
    
    /**
     * Get default signature
     */
    public function getDefaultSignature($userId)
    {
        return DB::table('email_signatures')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }
    
    /**
     * Create a new signature
     */
    public function createSignature($userId, $data)
    {
        try {
            // If this is set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                DB::table('email_signatures')
                    ->where('user_id', $userId)
                    ->update(['is_default' => false]);
            }
            
            $signatureId = DB::table('email_signatures')->insertGetId([
                'user_id' => $userId,
                'name' => $data['name'],
                'content' => $data['content'],
                'is_default' => $data['is_default'] ?? false,
                'is_active' => true,
                'include_in_replies' => $data['include_in_replies'] ?? true,
                'include_in_forwards' => $data['include_in_forwards'] ?? true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'signature_id' => $signatureId,
                'message' => 'Signature created successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create email signature: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create signature: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update a signature
     */
    public function updateSignature($signatureId, $userId, $data)
    {
        try {
            $signature = DB::table('email_signatures')
                ->where('id', $signatureId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$signature) {
                throw new \Exception('Signature not found or access denied');
            }
            
            // If this is set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default'] && !$signature->is_default) {
                DB::table('email_signatures')
                    ->where('user_id', $userId)
                    ->where('id', '!=', $signatureId)
                    ->update(['is_default' => false]);
            }
            
            DB::table('email_signatures')
                ->where('id', $signatureId)
                ->update([
                    'name' => $data['name'] ?? $signature->name,
                    'content' => $data['content'] ?? $signature->content,
                    'is_default' => $data['is_default'] ?? $signature->is_default,
                    'include_in_replies' => $data['include_in_replies'] ?? $signature->include_in_replies,
                    'include_in_forwards' => $data['include_in_forwards'] ?? $signature->include_in_forwards,
                    'updated_at' => now()
                ]);
                
            return [
                'success' => true,
                'message' => 'Signature updated successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update email signature: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update signature: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a signature
     */
    public function deleteSignature($signatureId, $userId)
    {
        try {
            $deleted = DB::table('email_signatures')
                ->where('id', $signatureId)
                ->where('user_id', $userId)
                ->delete();
                
            if (!$deleted) {
                throw new \Exception('Signature not found or access denied');
            }
            
            return [
                'success' => true,
                'message' => 'Signature deleted successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete email signature: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete signature: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Set default signature
     */
    public function setDefaultSignature($signatureId, $userId)
    {
        try {
            // First, unset all defaults
            DB::table('email_signatures')
                ->where('user_id', $userId)
                ->update(['is_default' => false]);
                
            // Then set the selected one as default
            $updated = DB::table('email_signatures')
                ->where('id', $signatureId)
                ->where('user_id', $userId)
                ->update(['is_default' => true]);
                
            if (!$updated) {
                throw new \Exception('Signature not found or access denied');
            }
            
            return [
                'success' => true,
                'message' => 'Default signature updated successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to set default signature: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to set default signature: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Apply signature to email body
     */
    public function applySignature($body, $signatureId, $userId, $variables = [])
    {
        try {
            $signature = DB::table('email_signatures')
                ->where('id', $signatureId)
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
                
            if (!$signature) {
                return $body;
            }
            
            // Apply variables to signature
            $signatureContent = $this->applyVariables($signature->content, $variables);
            
            // Update usage statistics
            DB::table('email_signatures')
                ->where('id', $signatureId)
                ->update([
                    'usage_count' => DB::raw('usage_count + 1'),
                    'last_used_at' => now()
                ]);
            
            // Append signature to body with separator
            return $body . "\n\n--\n" . $signatureContent;
        } catch (\Exception $e) {
            Log::error('Failed to apply signature: ' . $e->getMessage());
            return $body;
        }
    }
    
    /**
     * Apply variables to signature content
     */
    protected function applyVariables($content, $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        // Remove any remaining placeholders
        $content = preg_replace('/\{\{\w+\}\}/', '', $content);
        
        return $content;
    }
    
    /**
     * Create default signatures for a user
     */
    public function createDefaultSignatures($userId)
    {
        foreach ($this->defaultSignatures as $index => $signature) {
            $this->createSignature($userId, array_merge($signature, [
                'is_default' => $index === 0 // First one is default
            ]));
        }
    }
    
    /**
     * Get signature variables
     */
    public function getSignatureVariables()
    {
        return [
            'name' => 'Your Name',
            'title' => 'Your Title',
            'company' => 'Company Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'address' => 'Company Address',
            'website' => 'Website URL',
            'linkedin' => 'LinkedIn Profile',
            'twitter' => 'Twitter Handle'
        ];
    }
}