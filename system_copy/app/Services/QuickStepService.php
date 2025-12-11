<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\EmailLabelService;
use App\Services\EmailReminderService;

class QuickStepService
{
    protected $availableActions = [
        'move_to_folder' => ['folder' => 'string'],
        'mark_as_read' => [],
        'mark_as_unread' => [],
        'flag' => [],
        'unflag' => [],
        'apply_label' => ['label_id' => 'integer'],
        'forward_to' => ['email' => 'string'],
        'delete' => [],
        'archive' => [],
        'mark_as_important' => [],
        'create_task' => ['task_title' => 'string'],
        'set_reminder' => ['hours' => 'integer']
    ];
    
    protected $defaultQuickSteps = [
        [
            'name' => 'Archive & Mark Read',
            'description' => 'Move to archive and mark as read',
            'icon' => 'archive',
            'actions' => [
                ['type' => 'move_to_folder', 'params' => ['folder' => 'archive']],
                ['type' => 'mark_as_read', 'params' => []]
            ]
        ],
        [
            'name' => 'Flag & Reply',
            'description' => 'Flag for follow-up and open reply',
            'icon' => 'flag',
            'actions' => [
                ['type' => 'flag', 'params' => []],
                ['type' => 'open_reply', 'params' => []]
            ]
        ],
        [
            'name' => 'Important & Pin',
            'description' => 'Mark as important and pin to top',
            'icon' => 'star',
            'actions' => [
                ['type' => 'mark_as_important', 'params' => []],
                ['type' => 'pin', 'params' => []]
            ]
        ]
    ];
    
    /**
     * Get user's quick steps
     */
    public function getUserQuickSteps($userId)
    {
        return DB::table('quick_steps')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('order_index')
            ->orderBy('usage_count', 'desc')
            ->get()
            ->map(function ($step) {
                $step->actions = json_decode($step->actions, true);
                return $step;
            });
    }
    
    /**
     * Create a quick step
     */
    public function createQuickStep($userId, $data)
    {
        try {
            // Validate actions
            if (!$this->validateActions($data['actions'])) {
                throw new \Exception('Invalid actions provided');
            }
            
            $maxOrder = DB::table('quick_steps')
                ->where('user_id', $userId)
                ->max('order_index') ?? 0;
            
            $quickStepId = DB::table('quick_steps')->insertGetId([
                'user_id' => $userId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? 'lightning-bolt',
                'shortcut_key' => $data['shortcut_key'] ?? null,
                'actions' => json_encode($data['actions']),
                'order_index' => $maxOrder + 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'quick_step_id' => $quickStepId,
                'message' => 'Quick Step created successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create quick step: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute a quick step
     */
    public function executeQuickStep($quickStepId, $emailIds, $userId)
    {
        try {
            $quickStep = DB::table('quick_steps')
                ->where('id', $quickStepId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$quickStep) {
                throw new \Exception('Quick Step not found');
            }
            
            $actions = json_decode($quickStep->actions, true);
            $results = [];
            
            foreach ($actions as $action) {
                $result = $this->executeAction($action, $emailIds, $userId);
                $results[] = $result;
                
                if (!$result['success']) {
                    Log::warning('Quick Step action failed', [
                        'action' => $action,
                        'error' => $result['message']
                    ]);
                }
            }
            
            // Update usage count
            DB::table('quick_steps')
                ->where('id', $quickStepId)
                ->increment('usage_count');
            
            return [
                'success' => true,
                'results' => $results,
                'message' => 'Quick Step executed successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to execute quick step: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute a single action
     */
    protected function executeAction($action, $emailIds, $userId)
    {
        try {
            switch ($action['type']) {
                case 'move_to_folder':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->update(['folder' => $action['params']['folder']]);
                    break;
                    
                case 'mark_as_read':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->where('recipient_id', $userId)
                        ->update(['is_read' => true]);
                    break;
                    
                case 'mark_as_unread':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->where('recipient_id', $userId)
                        ->update(['is_read' => false]);
                    break;
                    
                case 'flag':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->update([
                            'is_flagged' => true,
                            'flagged_at' => now()
                        ]);
                    break;
                    
                case 'unflag':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->update([
                            'is_flagged' => false,
                            'flagged_at' => null
                        ]);
                    break;
                    
                case 'pin':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->update([
                            'is_pinned' => true,
                            'pinned_at' => now()
                        ]);
                    break;
                    
                case 'apply_label':
                    $labelService = new EmailLabelService();
                    foreach ($emailIds as $emailId) {
                        $labelService->applyLabel(
                            $emailId,
                            $action['params']['label_id'],
                            $userId
                        );
                    }
                    break;
                    
                case 'delete':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->update([
                            'folder' => 'trash',
                            'deleted_at' => now()
                        ]);
                    break;
                    
                case 'archive':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->update(['folder' => 'archive']);
                    break;
                    
                case 'mark_as_important':
                    DB::table('emails')
                        ->whereIn('id', $emailIds)
                        ->update(['is_focused' => true]);
                    break;
                    
                case 'set_reminder':
                    $reminderService = new EmailReminderService();
                    $remindAt = now()->addHours($action['params']['hours']);
                    foreach ($emailIds as $emailId) {
                        $reminderService->createReminder($userId, $emailId, [
                            'type' => 'follow_up',
                            'note' => 'Quick Step reminder',
                            'remind_at' => $remindAt
                        ]);
                    }
                    break;
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'Unknown action type: ' . $action['type']
                    ];
            }
            
            return [
                'success' => true,
                'action' => $action['type']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate actions array
     */
    protected function validateActions($actions)
    {
        if (!is_array($actions) || empty($actions)) {
            return false;
        }
        
        foreach ($actions as $action) {
            if (!isset($action['type']) || !isset($this->availableActions[$action['type']])) {
                return false;
            }
            
            // Validate required parameters
            $requiredParams = $this->availableActions[$action['type']];
            foreach ($requiredParams as $param => $type) {
                if (!isset($action['params'][$param])) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Update quick step
     */
    public function updateQuickStep($quickStepId, $userId, $data)
    {
        try {
            $quickStep = DB::table('quick_steps')
                ->where('id', $quickStepId)
                ->where('user_id', $userId)
                ->first();
                
            if (!$quickStep) {
                throw new \Exception('Quick Step not found');
            }
            
            $updateData = [];
            
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            
            if (isset($data['icon'])) {
                $updateData['icon'] = $data['icon'];
            }
            
            if (isset($data['shortcut_key'])) {
                $updateData['shortcut_key'] = $data['shortcut_key'];
            }
            
            if (isset($data['actions'])) {
                if (!$this->validateActions($data['actions'])) {
                    throw new \Exception('Invalid actions provided');
                }
                $updateData['actions'] = json_encode($data['actions']);
            }
            
            $updateData['updated_at'] = now();
            
            DB::table('quick_steps')
                ->where('id', $quickStepId)
                ->update($updateData);
                
            return [
                'success' => true,
                'message' => 'Quick Step updated successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update quick step: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete quick step
     */
    public function deleteQuickStep($quickStepId, $userId)
    {
        try {
            $deleted = DB::table('quick_steps')
                ->where('id', $quickStepId)
                ->where('user_id', $userId)
                ->delete();
                
            return [
                'success' => $deleted > 0,
                'message' => $deleted > 0 ? 'Quick Step deleted' : 'Quick Step not found'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete quick step: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create default quick steps for user
     */
    public function createDefaultQuickSteps($userId)
    {
        foreach ($this->defaultQuickSteps as $index => $step) {
            $this->createQuickStep($userId, array_merge($step, [
                'order_index' => $index
            ]));
        }
    }
}