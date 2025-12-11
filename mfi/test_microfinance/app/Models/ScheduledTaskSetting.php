<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledTaskSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'command',
        'is_enabled',
        'disabled_by',
        'disabled_at',
        'disabled_reason',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'disabled_at' => 'datetime',
    ];

    /**
     * Get the user who disabled the task
     */
    public function disabledByUser()
    {
        return $this->belongsTo(User::class, 'disabled_by');
    }

    /**
     * Scope for enabled tasks
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for disabled tasks
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Check if a task is enabled (by task_id)
     * Returns true if no setting exists (default enabled)
     */
    public static function isTaskEnabled($taskId)
    {
        $setting = self::where('task_id', $taskId)->first();
        return $setting ? $setting->is_enabled : true;
    }

    /**
     * Check if a command is enabled
     * Returns true if no setting exists (default enabled)
     */
    public static function isCommandEnabled($command)
    {
        $setting = self::where('command', $command)->first();
        return $setting ? $setting->is_enabled : true;
    }

    /**
     * Get all disabled task IDs
     */
    public static function getDisabledTaskIds()
    {
        return self::disabled()->pluck('task_id')->toArray();
    }

    /**
     * Get all disabled commands
     */
    public static function getDisabledCommands()
    {
        return self::disabled()->pluck('command')->toArray();
    }

    /**
     * Toggle task enabled status
     */
    public static function toggleTask($taskId, $command, $userId = null, $reason = null)
    {
        $setting = self::firstOrNew(['task_id' => $taskId]);

        $wasEnabled = $setting->exists ? $setting->is_enabled : true;
        $newEnabled = !$wasEnabled;

        $setting->task_id = $taskId;
        $setting->command = $command;
        $setting->is_enabled = $newEnabled;

        if (!$newEnabled) {
            // Task is being disabled
            $setting->disabled_by = $userId;
            $setting->disabled_at = now();
            $setting->disabled_reason = $reason;
        } else {
            // Task is being enabled
            $setting->disabled_by = null;
            $setting->disabled_at = null;
            $setting->disabled_reason = null;
        }

        $setting->save();

        return $setting;
    }

    /**
     * Enable a task
     */
    public static function enableTask($taskId, $command)
    {
        $setting = self::firstOrNew(['task_id' => $taskId]);
        $setting->task_id = $taskId;
        $setting->command = $command;
        $setting->is_enabled = true;
        $setting->disabled_by = null;
        $setting->disabled_at = null;
        $setting->disabled_reason = null;
        $setting->save();

        return $setting;
    }

    /**
     * Disable a task
     */
    public static function disableTask($taskId, $command, $userId = null, $reason = null)
    {
        $setting = self::firstOrNew(['task_id' => $taskId]);
        $setting->task_id = $taskId;
        $setting->command = $command;
        $setting->is_enabled = false;
        $setting->disabled_by = $userId;
        $setting->disabled_at = now();
        $setting->disabled_reason = $reason;
        $setting->save();

        return $setting;
    }

    /**
     * Get settings for all tasks (keyed by task_id)
     */
    public static function getAllSettings()
    {
        return self::all()->keyBy('task_id');
    }

    /**
     * Get enabled status map (task_id => is_enabled)
     */
    public static function getEnabledStatusMap()
    {
        return self::pluck('is_enabled', 'task_id')->toArray();
    }
}
