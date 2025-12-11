<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyActivityStatus extends Model
{
    use HasFactory;

    protected $table = 'daily_activity_status';

    protected $fillable = [
        'activity_key',
        'activity_name',
        'status',
        'progress',
        'total_records',
        'processed_records',
        'failed_records',
        'metadata',
        'last_error',
        'started_at',
        'completed_at',
        'process_date',
        'execution_time_seconds',
        'triggered_by'
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'process_date' => 'date',
        'progress' => 'float'
    ];

    /**
     * Get all activities for today
     */
    public static function getTodayActivities()
    {
        return self::whereDate('process_date', Carbon::today())
            ->orderBy('id')
            ->get();
    }

    /**
     * Get or create activity for today
     */
    public static function getOrCreateActivity($key, $name)
    {
        return self::firstOrCreate(
            [
                'activity_key' => $key,
                'process_date' => Carbon::today()
            ],
            [
                'activity_name' => $name,
                'status' => 'pending',
                'progress' => 0
            ]
        );
    }

    /**
     * Start an activity
     */
    public function start($triggeredBy = 'system')
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
            'triggered_by' => $triggeredBy,
            'last_error' => null
        ]);
    }

    /**
     * Update progress
     */
    public function updateProgress($processed, $total, $failed = 0)
    {
        $progress = $total > 0 ? round(($processed / $total) * 100, 2) : 0;
        
        $this->update([
            'processed_records' => $processed,
            'total_records' => $total,
            'failed_records' => $failed,
            'progress' => $progress
        ]);
    }

    /**
     * Mark as completed
     */
    public function complete()
    {
        $executionTime = $this->started_at ? 
            Carbon::parse($this->started_at)->diffInSeconds(now()) : 0;

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress' => 100,
            'execution_time_seconds' => $executionTime
        ]);
    }

    /**
     * Mark as failed
     */
    public function fail($error = null)
    {
        $executionTime = $this->started_at ? 
            Carbon::parse($this->started_at)->diffInSeconds(now()) : 0;

        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'last_error' => $error,
            'execution_time_seconds' => $executionTime
        ]);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed' => 'green',
            'running' => 'blue',
            'failed' => 'red',
            'skipped' => 'gray',
            default => 'yellow'
        };
    }

    /**
     * Get formatted execution time
     */
    public function getFormattedExecutionTimeAttribute()
    {
        if (!$this->execution_time_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->execution_time_seconds / 60);
        $seconds = $this->execution_time_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }
}