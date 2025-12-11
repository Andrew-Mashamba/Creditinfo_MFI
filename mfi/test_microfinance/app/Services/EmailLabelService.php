<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailLabelService
{
    protected $defaultLabels = [
        ['name' => 'Important', 'color' => '#EF4444', 'icon' => 'star', 'is_system' => true],
        ['name' => 'Work', 'color' => '#3B82F6', 'icon' => 'briefcase', 'is_system' => false],
        ['name' => 'Personal', 'color' => '#10B981', 'icon' => 'user', 'is_system' => false],
        ['name' => 'Finance', 'color' => '#F59E0B', 'icon' => 'currency-dollar', 'is_system' => false],
        ['name' => 'Travel', 'color' => '#8B5CF6', 'icon' => 'globe', 'is_system' => false],
        ['name' => 'Shopping', 'color' => '#EC4899', 'icon' => 'shopping-cart', 'is_system' => false]
    ];
    
    /**
     * Get user labels
     */
    public function getUserLabels($userId)
    {
        return DB::table('email_labels')
            ->where('user_id', $userId)
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Create a new label
     */
    public function createLabel($userId, $data)
    {
        try {
            // Get max order index
            $maxOrder = DB::table('email_labels')
                ->where('user_id', $userId)
                ->max('order_index') ?? 0;
            
            $labelId = DB::table('email_labels')->insertGetId([
                'user_id' => $userId,
                'name' => $data['name'],
                'color' => $data['color'] ?? '#6B7280',
                'icon' => $data['icon'] ?? null,
                'order_index' => $maxOrder + 1,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'label_id' => $labelId,
                'message' => 'Label created successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create label: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create label: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update a label
     */
    public function updateLabel($labelId, $userId, $data)
    {
        try {
            $label = DB::table('email_labels')
                ->where('id', $labelId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$label) {
                throw new \Exception('Label not found');
            }
            
            if ($label->is_system && isset($data['name'])) {
                throw new \Exception('Cannot rename system labels');
            }
            
            DB::table('email_labels')
                ->where('id', $labelId)
                ->update([
                    'name' => $data['name'] ?? $label->name,
                    'color' => $data['color'] ?? $label->color,
                    'icon' => $data['icon'] ?? $label->icon,
                    'updated_at' => now()
                ]);
                
            return [
                'success' => true,
                'message' => 'Label updated successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update label: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a label
     */
    public function deleteLabel($labelId, $userId)
    {
        try {
            $label = DB::table('email_labels')
                ->where('id', $labelId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$label) {
                throw new \Exception('Label not found');
            }
            
            if ($label->is_system) {
                throw new \Exception('Cannot delete system labels');
            }
            
            DB::table('email_labels')
                ->where('id', $labelId)
                ->delete();
                
            return [
                'success' => true,
                'message' => 'Label deleted successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete label: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Apply label to email
     */
    public function applyLabel($emailId, $labelId, $userId)
    {
        try {
            // Verify label belongs to user
            $label = DB::table('email_labels')
                ->where('id', $labelId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$label) {
                throw new \Exception('Label not found');
            }
            
            // Check if already applied
            $exists = DB::table('email_label')
                ->where('email_id', $emailId)
                ->where('label_id', $labelId)
                ->exists();
                
            if (!$exists) {
                DB::table('email_label')->insert([
                    'email_id' => $emailId,
                    'label_id' => $labelId,
                    'applied_at' => now()
                ]);
            }
            
            return [
                'success' => true,
                'message' => 'Label applied successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to apply label: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to apply label'
            ];
        }
    }
    
    /**
     * Remove label from email
     */
    public function removeLabel($emailId, $labelId)
    {
        try {
            DB::table('email_label')
                ->where('email_id', $emailId)
                ->where('label_id', $labelId)
                ->delete();
                
            return [
                'success' => true,
                'message' => 'Label removed successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to remove label: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to remove label'
            ];
        }
    }
    
    /**
     * Get email labels
     */
    public function getEmailLabels($emailId)
    {
        return DB::table('email_label as el')
            ->join('email_labels as l', 'el.label_id', '=', 'l.id')
            ->where('el.email_id', $emailId)
            ->select('l.*', 'el.applied_at')
            ->orderBy('l.name')
            ->get();
    }
    
    /**
     * Get emails by label
     */
    public function getEmailsByLabel($labelId, $userId)
    {
        return DB::table('email_label as el')
            ->join('emails as e', 'el.email_id', '=', 'e.id')
            ->join('email_labels as l', 'el.label_id', '=', 'l.id')
            ->where('l.id', $labelId)
            ->where('l.user_id', $userId)
            ->select('e.*', 'el.applied_at as label_applied_at')
            ->orderBy('e.created_at', 'desc')
            ->get();
    }
    
    /**
     * Reorder labels
     */
    public function reorderLabels($userId, $labelOrder)
    {
        try {
            foreach ($labelOrder as $index => $labelId) {
                DB::table('email_labels')
                    ->where('id', $labelId)
                    ->where('user_id', $userId)
                    ->update(['order_index' => $index]);
            }
            
            return [
                'success' => true,
                'message' => 'Labels reordered successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to reorder labels: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to reorder labels'
            ];
        }
    }
    
    /**
     * Create default labels for user
     */
    public function createDefaultLabels($userId)
    {
        foreach ($this->defaultLabels as $index => $label) {
            $this->createLabel($userId, array_merge($label, [
                'order_index' => $index
            ]));
        }
    }
    
    /**
     * Apply multiple labels
     */
    public function applyMultipleLabels($emailId, $labelIds, $userId)
    {
        try {
            // Verify all labels belong to user
            $validLabels = DB::table('email_labels')
                ->whereIn('id', $labelIds)
                ->where('user_id', $userId)
                ->pluck('id');
                
            $inserts = [];
            foreach ($validLabels as $labelId) {
                $inserts[] = [
                    'email_id' => $emailId,
                    'label_id' => $labelId,
                    'applied_at' => now()
                ];
            }
            
            // Insert ignoring duplicates
            DB::table('email_label')->insertOrIgnore($inserts);
            
            return [
                'success' => true,
                'applied_count' => count($validLabels)
            ];
        } catch (\Exception $e) {
            Log::error('Failed to apply multiple labels: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to apply labels'
            ];
        }
    }
}